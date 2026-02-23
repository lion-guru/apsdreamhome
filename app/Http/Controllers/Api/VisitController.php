<?php

namespace App\Http\Controllers\Api;

use \DateTime;
use \Exception;

class VisitController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('csrf', ['only' => ['schedule']]);
    }

    /**
     * Schedule a property visit
     */
    public function schedule()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Invalid request method.', 405);
        }

        // Validate required fields
        $required_fields = [
            'property_id' => 'Property ID',
            'visit_date' => 'Visit date',
            'visit_time' => 'Visit time',
            'visitor_name' => 'Your name',
            'visitor_email' => 'Email address',
            'visitor_phone' => 'Phone number'
        ];

        $errors = [];
        $input = [];

        foreach ($required_fields as $field => $label) {
            $value = \trim($this->request()->input($field, ''));
            if (empty($value)) {
                $errors[$field] = "$label is required.";
            } else {
                // XSS Protection - Sanitize input
                if (in_array($field, ['visitor_name', 'visitor_phone'])) {
                    $input[$field] = strip_tags($value);
                } else {
                    $input[$field] = $value;
                }
            }
        }

        // Validate email
        if (isset($input['visitor_email']) && !\filter_var($input['visitor_email'], \FILTER_VALIDATE_EMAIL)) {
            $errors['visitor_email'] = 'Please enter a valid email address.';
        }

        // Validate phone number
        if (isset($input['visitor_phone']) && !\preg_match('/^[0-9\-\+\(\)\s]{10,20}$/', $input['visitor_phone'])) {
            $errors['visitor_phone'] = 'Please enter a valid phone number.';
        }

        // Validate date
        if (isset($input['visit_date'])) {
            $visit_date = \DateTime::createFromFormat('Y-m-d', $input['visit_date']);
            $today = new \DateTime('today');
            if (!$visit_date) {
                $errors['visit_date'] = 'Invalid date format.';
            } elseif ($visit_date < $today) {
                $errors['visit_date'] = 'Visit date must be today or in the future.';
            }
        }

        // Validate time
        if (isset($input['visit_time'])) {
            $visit_time = \DateTime::createFromFormat('H:i', $input['visit_time']);
            if (!$visit_time) {
                $errors['visit_time'] = 'Invalid time format.';
            }
        }

        if (!empty($errors)) {
            return $this->jsonError('Validation failed', 422, $errors);
        }

        try {
            $this->db->beginTransaction();

            // Use PublicCustomer model
            $customerModel = $this->model('PublicCustomer');
            $customer_id = $customerModel->findOrCreate([
                'name' => $input['visitor_name'],
                'email' => $input['visitor_email'],
                'phone' => $input['visitor_phone']
            ]);

            if (!$customer_id) {
                throw new \Exception('Failed to process customer information.');
            }

            // Get property details for notification
            $propertyModel = $this->model('Property');
            $property = $propertyModel->find($input['property_id']);

            if (!$property) {
                throw new \Exception('Property not found.');
            }

            $property_title = $property->title;
            $agent_id = $property->owner_id;

            // Use PropertyVisit model to check availability
            $visitModel = $this->model('PropertyVisit');
            if (!$visitModel->isSlotAvailable($input['property_id'], $input['visit_date'], $input['visit_time'])) {
                throw new \Exception('This time slot is already booked. Please select another time.');
            }

            // Insert into property_visits using model
            $message = strip_tags(\trim($this->request()->input('message', '')));
            $visit = new \App\Models\PropertyVisit([
                'customer_id' => $customer_id,
                'property_id' => $input['property_id'],
                'visitor_name' => $input['visitor_name'],
                'visitor_email' => $input['visitor_email'],
                'visitor_phone' => $input['visitor_phone'],
                'visit_date' => $input['visit_date'],
                'visit_time' => $input['visit_time'],
                'message' => $message,
                'status' => 'scheduled'
            ]);

            if (!$visit->save()) {
                throw new \Exception('Failed to schedule visit.');
            }
            $visit_id = $visit->id;

            // Create a lead for this visit using Lead model
            $lead_notes = "Visit scheduled for {$input['visit_date']} at {$input['visit_time']}. " . ($message ? "Notes: $message" : "");
            $leadModel = $this->model('Lead');
            $lead = new \App\Models\Lead([
                'customer_id' => $customer_id,
                'property_id' => $input['property_id'],
                'source' => 'visit_schedule',
                'status' => 'new',
                'notes' => $lead_notes
            ]);

            if (!$lead->save()) {
                throw new \Exception('Failed to create lead for visit.');
            }
            $lead_id = $lead->id;

            // Link visit to lead
            $visit->update(['lead_id' => $lead_id]);

            // Notification
            if ($agent_id && \class_exists('\App\Services\NotificationService')) {
                try {
                    $notification = new \App\Services\NotificationService();
                    $notification->notifyUser(
                        (int)$agent_id,
                        'New Visit Scheduled',
                        "Visit scheduled by {$input['visitor_name']} for $property_title on {$input['visit_date']} at {$input['visit_time']}",
                        'visit_scheduled',
                        ['link' => "/admin/visits.php?id=$visit_id"]
                    );
                } catch (\Exception $e) {
                    // Log notification error but don't fail the visit scheduling
                    logger()->error('Notification error: ' . $e->getMessage());
                }
            }

            $this->db->commit();
            return $this->jsonSuccess(['message' => 'Your visit has been scheduled successfully! We will contact you shortly to confirm.']);

        } catch (\Exception $e) {
            $this->db->rollBack();
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
