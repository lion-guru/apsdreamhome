<?php

namespace App\Services\AI\VoiceAgents;

use App\Services\AI\Agents\BaseAgent;
use App\Services\Voice\VoiceCallService;
use App\Services\Voice\TwilioVoiceService;
use Exception;

class PropertyInquiryAgent extends BaseAgent
{
    private $property_fields = ['id', 'name', 'phone', 'email', 'property_type', 'listing_type', 'address', 'location', 'area_sqft', 'price', 'description', 'status', 'image'];
    private $amenities_categories = ['nearby_schools', 'hospitals', 'markets', 'transport', 'parks', 'banks'];

    public function __construct()
    {
        parent::__construct(11, 'Property Inquiry Agent');
    }

    public function process($input, $context = [])
    {
        $this->status = 'processing';

        $action = $input['action'] ?? 'property_details';
        $property_id = $input['property_id'] ?? null;
        $lead_id = $input['lead_id'] ?? null;
        $query = $input['query'] ?? '';

        if (!$property_id) {
            return $this->handleError('Property ID is required');
        }

        $this->logActivity('INQUIRY_PROCESSING', "Action: $action, Property: $property_id");

        switch ($action) {
            case 'property_details':
                $result = $this->getPropertyDetails($property_id);
                break;
            case 'pricing':
                $result = $this->getPricing($property_id);
                break;
            case 'location':
                $result = $this->getLocation($property_id);
                break;
            case 'qualify_lead':
                $result = $this->qualifyLead(
                    $input['interest'] ?? '',
                    $input['budget'] ?? 0,
                    $input['timeline'] ?? ''
                );
                if ($result['qualified'] && $lead_id) {
                    $this->scheduleFollowUpCall($lead_id, $input['phone'] ?? '');
                }
                break;
            case 'schedule_viewing':
                $result = $this->scheduleViewing(
                    $property_id,
                    $lead_id,
                    $input['preferred_date'] ?? null,
                    $input['preferred_time'] ?? null
                );
                if ($result['success'] && $lead_id) {
                    $this->scheduleConfirmationCall($lead_id, $input['phone'] ?? '', $result['visit_date'] ?? null);
                }
                break;
            case 'send_info':
                $result = $this->sendPropertyInfo($lead_id, $property_id);
                if ($lead_id) {
                    $this->scheduleFollowUpCall($lead_id, $input['phone'] ?? '');
                }
                break;
            default:
                $result = $this->handleError("Unknown action: $action");
        }

        $this->status = 'ready';
        return $result;
    }

    protected function scheduleFollowUpCall($leadId, $phone = '')
    {
        if (!$leadId) return;
        try {
            $followUpDate = date('Y-m-d', strtotime('+2 days'));
            $agent = $this->db->fetch("SELECT agent_id FROM ai_calling_agents WHERE agent_type LIKE '%inquiry%' OR agent_id = 'agent_11' AND status = 'active' LIMIT 1");
            $agentId = $agent['agent_id'] ?? 'agent_11';
            $svc = new VoiceCallService();
            $svc->scheduleCall($leadId, $phone, $agentId, $followUpDate, '11:00:00', 'property_inquiry_followup', 'medium');
        } catch (\Exception $e) {
                    error_log("PropertyInquiryAgent.php: " . $e->getMessage());
        }
    }

    protected function scheduleConfirmationCall($leadId, $phone = '', $visitDate = null)
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
                    error_log("PropertyInquiryAgent.php: " . $e->getMessage());
        }
    }

    public function getPropertyDetails($propertyId)
    {
        $property = $this->db->fetch(
            "SELECT * FROM user_properties WHERE id = ? AND status = 'approved'",
            [$propertyId]
        );

        if (!$property) {
            $property = $this->db->fetch("SELECT * FROM properties WHERE id = ?", [$propertyId]);
        }

        if (!$property) {
            return ['success' => false, 'error' => 'Property not found'];
        }

        return [
            'success' => true,
            'property' => [
                'id' => $property['id'],
                'type' => $property['property_type'] ?? $property['type'] ?? 'N/A',
                'listing_type' => $property['listing_type'] ?? 'sell',
                'title' => $property['name'] ?? $property['title'] ?? 'Property',
                'description' => $property['description'] ?? '',
                'location' => $property['location'] ?? '',
                'address' => $property['address'] ?? '',
                'area_sqft' => $property['area_sqft'] ?? 0,
                'price' => $property['price'] ?? 0,
                'status' => $property['status'] ?? 'available',
                'image' => $property['image'] ?? ''
            ]
        ];
    }

    public function getPricing($propertyId)
    {
        $property = $this->db->fetch("SELECT id, price, property_type, area_sqft, listing_type FROM user_properties WHERE id = ?", [$propertyId]);
        if (!$property) {
            return ['success' => false, 'error' => 'Property not found'];
        }

        $price = floatval($property['price']);
        $area = intval($property['area_sqft']);

        $emi_options = [];
        $interest_rates = [8.5, 9.0, 9.5];

        $down_payment_20 = $price * 0.2;
        $loan_amount_80 = $price * 0.8;

        foreach ($interest_rates as $rate) {
            $monthly = $this->calculateEMI($loan_amount_80, $rate, 20);
            $emi_options[] = [
                'interest_rate' => $rate . '%',
                'loan_amount' => round($loan_amount_80, 2),
                'down_payment' => round($down_payment_20, 2),
                'tenure_years' => 20,
                'monthly_emi' => round($monthly, 2)
            ];
        }

        return [
            'success' => true,
            'property_id' => $propertyId,
            'total_price' => $price,
            'price_per_sqft' => $area > 0 ? round($price / $area, 2) : 0,
            'listing_type' => $property['listing_type'],
            'down_payment_20' => round($down_payment_20, 2),
            'emi_options' => $emi_options
        ];
    }

    public function getLocation($propertyId)
    {
        $property = $this->db->fetch("SELECT id, location, address, city_id, district_id, state_id FROM user_properties WHERE id = ?", [$propertyId]);
        if (!$property) {
            return ['success' => false, 'error' => 'Property not found'];
        }

        $location_data = [
            'address' => $property['address'] ?? '',
            'location' => $property['location'] ?? '',
            'nearby_amenities' => []
        ];

        if ($property['city_id']) {
            $city = $this->db->fetch("SELECT name FROM cities WHERE id = ?", [$property['city_id']]);
            $location_data['city'] = $city['name'] ?? '';
        }

        if ($property['district_id']) {
            $district = $this->db->fetch("SELECT name FROM districts WHERE id = ?", [$property['district_id']]);
            $location_data['district'] = $district['name'] ?? '';
        }

        if ($property['state_id']) {
            $state = $this->db->fetch("SELECT name FROM states WHERE id = ?", [$property['state_id']]);
            $location_data['state'] = $state['name'] ?? '';
        }

        return [
            'success' => true,
            'property_id' => $propertyId,
            'location' => $location_data
        ];
    }

    public function qualifyLead($interest, $budget, $timeline)
    {
        $score = 0;

        if (!empty($interest)) {
            $score += 20;
        }
        if ($budget > 0) {
            $score += 30;
        }
        if ($timeline) {
            $score += 25;
            if (in_array($timeline, ['immediate', '1_month'])) {
                $score += 15;
            }
        }

        return [
            'qualified' => $score >= 50,
            'score' => $score,
            'level' => $score >= 70 ? 'hot' : ($score >= 50 ? 'warm' : 'cold'),
            'suggestion' => $score >= 50 ? 'Schedule immediate follow-up call' : 'Nurture with property updates'
        ];
    }

    public function scheduleViewing($propertyId, $leadId, $preferredDate = null, $preferredTime = null)
    {
        if (!$preferredDate) {
            $preferredDate = date('Y-m-d', strtotime('+1 day'));
        }
        if (!$preferredTime) {
            $preferredTime = '10:00';
        }

        $slot_check = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM bookings WHERE property_id = ? AND visit_date = ? AND visit_time = ? AND status IN ('pending', 'confirmed')",
            [$propertyId, $preferredDate, $preferredTime]
        );

        if ($slot_check && $slot_check['cnt'] > 0) {
            return [
                'success' => false,
                'message' => "Time slot $preferredTime on $preferredDate is already booked",
                'suggested_action' => 'choose_alternate_slot'
            ];
        }

        $booking_data = [
            'property_id' => $propertyId,
            'booking_type' => 'site_visit',
            'visit_date' => $preferredDate,
            'visit_time' => $preferredTime,
            'booking_date' => date('Y-m-d'),
            'status' => 'pending'
        ];

        if ($leadId) {
            $booking_data['customer_id'] = $leadId;
        }

        $this->db->insert('bookings', $booking_data);
        $booking_id = $this->db->lastInsertId();

        $this->logActivity('VIEWING_SCHEDULED', "Property #$propertyId, Lead #$leadId, Date: $preferredDate $preferredTime");

        return [
            'success' => true,
            'booking_id' => $booking_id,
            'visit_date' => $preferredDate,
            'visit_time' => $preferredTime,
            'message' => "Viewing scheduled for $preferredDate at $preferredTime"
        ];
    }

    public function sendPropertyInfo($leadId, $propertyId)
    {
        $property = $this->getPropertyDetails($propertyId);
        if (!$property['success']) {
            return $property;
        }

        $pricing = $this->getPricing($propertyId);

        $info_data = [
            'lead_id' => $leadId,
            'property_id' => $propertyId,
            'generated_at' => date('Y-m-d H:i:s'),
            'property_details' => $property['property'] ?? [],
            'pricing' => $pricing['emi_options'] ?? []
        ];

        $this->logActivity('PROPERTY_INFO_SENT', "Property #$propertyId sent to Lead #$leadId");

        return [
            'success' => true,
            'lead_id' => $leadId,
            'property_id' => $propertyId,
            'message' => 'Property information prepared',
            'property_info' => $info_data
        ];
    }

    private function calculateEMI($principal, $annualRate, $years)
    {
        $monthlyRate = ($annualRate / 100) / 12;
        $months = $years * 12;
        $factor = pow(1 + $monthlyRate, $months);
        return $principal * $monthlyRate * $factor / ($factor - 1);
    }

    /**
     * Execute a real outbound Twilio call to answer a property inquiry.
     * Wires this AI agent into TwilioVoiceService (Cluster 2 - 2026-06-05).
     *
     * @param int    $leadId
     * @param string $phone        E.164 phone
     * @param array  $context      { leadName, property }
     * @return array{success:bool,sid:?string,error:?string}
     */
    public function executeCall($leadId, $phone, array $context = [])
    {
        try {
            $voice = new TwilioVoiceService();
            $property = $context['property'] ?? [];
            $leadName = $context['leadName'] ?? 'Customer';

            $baseUrl = $this->resolveBaseUrl();
            $twimlUrl = $baseUrl . '/api/twilio/voice?type=property_inquiry&property_id=' . ($property['id'] ?? 0);

            $result = $voice->makeCall($phone, $twimlUrl, null, [
                'record'      => true,
                'leadId'      => $leadId,
                'agentId'     => $this->agentId,
                'sessionMeta' => [
                    'agent'    => $this->agentName,
                    'kind'     => 'property_inquiry',
                    'property' => $property,
                ],
                'statusCallback' => $baseUrl . '/api/twilio/voice/status',
            ]);

            $this->logActivity('OUTBOUND_CALL_INITIATED', "Property inquiry call to $phone (SID: " . ($result['sid'] ?? 'none') . ")");
            return $result;
        } catch (\Throwable $e) {
            error_log("PropertyInquiryAgent::executeCall failed: " . $e->getMessage());
            return ['success' => false, 'sid' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Resolve the public base URL Twilio should call back to.
     */
    protected function resolveBaseUrl()
    {
        if (!empty($_ENV['APP_URL'])) return rtrim($_ENV['APP_URL'], '/');
        if (!empty($_ENV['BASE_URL_PUBLIC'])) return rtrim($_ENV['BASE_URL_PUBLIC'], '/');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
