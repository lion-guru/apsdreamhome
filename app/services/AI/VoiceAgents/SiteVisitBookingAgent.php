<?php

namespace App\Services\AI\VoiceAgents;

use App\Services\AI\users\BaseAgent;
use App\Services\Voice\VoiceCallService;
use Exception;

class SiteVisitBookingAgent extends BaseAgent
{
    private $booking_types = ['site_visit', 'online_consultation', 'direct_booking'];
    private $business_hours = ['start' => '09:00', 'end' => '18:00'];
    private $slot_duration = 60;

    public function __construct()
    {
        parent::__construct(10, 'Site Visit Booking Agent');
    }

    public function process($input, $context = [])
    {
        $this->status = 'processing';

        $lead_id = $input['lead_id'] ?? null;
        $property_id = $input['property_id'] ?? null;
        $preferred_date = $input['preferred_date'] ?? null;
        $preferred_time = $input['preferred_time'] ?? null;
        $customer_name = $input['customer_name'] ?? '';
        $phone = $input['phone'] ?? '';
        $email = $input['email'] ?? '';

        if (!$property_id || !$preferred_date) {
            return $this->handleError('Property ID and preferred date are required');
        }

        $property = $this->db->fetch("SELECT id, property_type, address, location, price FROM user_properties WHERE id = ? AND status = 'approved'", [$property_id]);
        if (!$property) {
            $property = $this->db->fetch("SELECT id, name as property_type, description as address, location, price FROM properties WHERE id = ?", [$property_id]);
        }
        if (!$property) {
            return $this->handleError('Property not found or not available');
        }

        $available_slots = $this->getAvailableSlots($property_id, $preferred_date);
        $booked = false;
        $booking_id = null;
        $assigned_slot = null;

        if ($preferred_time) {
            foreach ($available_slots as $slot) {
                if ($slot['time'] === $preferred_time && $slot['available']) {
                    $assigned_slot = $preferred_time;
                    $booked = true;
                    break;
                }
            }
        }

        if (!$booked && !empty($available_slots)) {
            foreach ($available_slots as $slot) {
                if ($slot['available']) {
                    $assigned_slot = $slot['time'];
                    $booked = true;
                    break;
                }
            }
        }

        if (!$booked) {
            return [
                'success' => false,
                'message' => 'No available slots on this date',
                'available_slots' => $available_slots,
                'suggested_action' => 'choose_alternate_date'
            ];
        }

        $booking_data = [
            'property_id' => $property_id,
            'booking_type' => 'site_visit',
            'visit_date' => $preferred_date,
            'visit_time' => $assigned_slot,
            'booking_date' => date('Y-m-d'),
            'status' => 'pending',
            'payment_status' => 'pending',
            'amount' => 0,
            'notes' => "Booked by SiteVisitBookingAgent. Customer: $customer_name, Phone: $phone"
        ];

        if ($lead_id) {
            $booking_data['customer_id'] = $lead_id;
        }

        $this->db->insert('bookings', $booking_data);
        $booking_id = $this->db->lastInsertId();

        if ($lead_id) {
            $this->db->execute(
                "UPDATE leads SET status = 'contacted', next_activity_date = DATE_ADD(NOW(), INTERVAL 1 DAY) WHERE id = ?",
                [$lead_id]
            );
        }

        $confirmation = $this->sendConfirmation($booking_id);

        $this->logActivity('SITE_VISIT_BOOKED', "Booking #$booking_id for property #$property_id on $preferred_date at $assigned_slot");

        $this->scheduleConfirmationCall($lead_id, $phone, $preferred_date);

        $this->status = 'ready';

        return [
            'success' => true,
            'booking_id' => $booking_id,
            'property_id' => $property_id,
            'visit_date' => $preferred_date,
            'visit_time' => $assigned_slot,
            'message' => "Site visit scheduled for $preferred_date at $assigned_slot",
            'confirmation' => $confirmation
        ];
    }

    protected function scheduleConfirmationCall($leadId = null, $phone = '', $visitDate = null)
    {
        if (!$leadId || !$visitDate) return;
        try {
            $reminderDate = date('Y-m-d', strtotime($visitDate . ' -1 day'));
            if ($reminderDate < date('Y-m-d')) return;
            $agent = $this->db->fetch("SELECT agent_id FROM ai_calling_agents WHERE agent_type LIKE '%visit%' OR agent_id = 'agent_10' AND status = 'active' LIMIT 1");
            $agentId = $agent['agent_id'] ?? 'agent_10';
            $svc = new VoiceCallService();
            $svc->scheduleCall($leadId, $phone, $agentId, $reminderDate, '10:00:00', 'site_visit_booking', 'high');
        } catch (\Exception $e) {
        }
    }

    public function initialize($config = [])
    {
        if (isset($config['business_hours'])) {
            $this->business_hours = $config['business_hours'];
        }
        if (isset($config['slot_duration'])) {
            $this->slot_duration = $config['slot_duration'];
        }
        return parent::initialize($config);
    }

    public function qualifyBuyer($budget, $timeline, $requirements)
    {
        $this->logActivity('BUYER_QUALIFICATION', "Budget: $budget, Timeline: $timeline");

        $score = 0;
        $reasons = [];

        if ($budget > 0) {
            $score += 30;
            $reasons[] = 'Budget specified';
        }
        if ($timeline && in_array($timeline, ['immediate', '1_month', '3_months'])) {
            $score += 40;
            $reasons[] = 'Short timeline';
        }
        if (!empty($requirements)) {
            $score += 30;
            $reasons[] = 'Requirements defined';
        }

        return [
            'qualified' => $score >= 50,
            'score' => $score,
            'reason' => implode(', ', $reasons),
            'match_level' => $score >= 80 ? 'high' : ($score >= 50 ? 'medium' : 'low')
        ];
    }

    public function getAvailableSlots($propertyId, $date)
    {
        $day_of_week = date('w', strtotime($date));
        if ($day_of_week == 0) {
            return [['time' => 'closed', 'available' => false, 'label' => 'Sunday - Closed']];
        }

        $existing_bookings = $this->db->fetchAll(
            "SELECT visit_time FROM bookings WHERE property_id = ? AND visit_date = ? AND status IN ('pending', 'confirmed')",
            [$propertyId, $date]
        );

        $booked_times = [];
        foreach ($existing_bookings as $b) {
            $booked_times[] = $b['visit_time'];
        }

        $slots = [];
        $start = strtotime($this->business_hours['start']);
        $end = strtotime($this->business_hours['end']);
        $minutes = $this->slot_duration;

        for ($t = $start; $t < $end; $t += $minutes * 60) {
            $time_str = date('H:i', $t);
            $slots[] = [
                'time' => $time_str,
                'label' => date('h:i A', $t),
                'available' => !in_array($time_str, $booked_times)
            ];
        }

        return $slots;
    }

    public function sendConfirmation($bookingId)
    {
        $booking = $this->db->fetch(
            "SELECT b.*, up.address as property_address, up.location, up.name as property_name
             FROM bookings b
             LEFT JOIN user_properties up ON b.property_id = up.id
             WHERE b.id = ?",
            [$bookingId]
        );

        if (!$booking) {
            return ['error' => 'Booking not found'];
        }

        return [
            'booking_id' => $bookingId,
            'property_name' => $booking['property_name'] ?? 'Property',
            'property_address' => $booking['property_address'] ?? '',
            'location' => $booking['location'] ?? '',
            'visit_date' => $booking['visit_date'],
            'visit_time' => $booking['visit_time'],
            'message' => 'Your site visit has been scheduled. Our representative will meet you at the property.'
        ];
    }

    public function sendReminder($bookingId)
    {
        $booking = $this->db->fetch(
            "SELECT b.*, up.name as property_name, up.location, up.address
             FROM bookings b
             LEFT JOIN user_properties up ON b.property_id = up.id
             WHERE b.id = ?",
            [$bookingId]
        );

        if (!$booking) {
            return ['error' => 'Booking not found'];
        }

        $visit_date = date('d M Y', strtotime($booking['visit_date']));
        $visit_time = date('h:i A', strtotime($booking['visit_time']));

        return [
            'message' => "Reminder: Your site visit to {$booking['property_name']} at {$booking['location']} is scheduled for $visit_date at $visit_time. Please carry valid ID proof.",
            'booking_id' => $bookingId,
            'visit_date' => $booking['visit_date'],
            'visit_time' => $booking['visit_time']
        ];
    }
}
