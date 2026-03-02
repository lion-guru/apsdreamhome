<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
use App\Models\Lead;
use App\Models\User;

class LeadController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Store quick lead from visitor (Progressive Profiling Step 1)
     */
    public function storeQuick()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid request method'], 405);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $source = trim($_POST['source'] ?? 'website_quick_register');

        if (empty($name) || empty($phone)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Name and Phone are required'], 400);
            return;
        }

        // Check if lead already exists
        $existingLead = Lead::where('phone', $phone)->first();

        if ($existingLead) {
            // Update source if needed or just return success
            // We might want to update the 'last_contacted' or similar
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Welcome back! We have your details.',
                'lead_id' => $existingLead['id'],
                'is_new' => false
            ]);
            return;
        }

        try {
            // Create new lead
            $leadId = Lead::create([
                'first_name' => $name,
                'last_name' => '', // Split name if needed
                'phone' => $phone,
                'source' => $source,
                'status' => 'new',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Set a cookie to track this lead
            setcookie('visitor_lead_id', $leadId, time() + (86400 * 30), "/"); // 30 days

            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Thank you! We will contact you shortly.',
                'lead_id' => $leadId,
                'is_new' => true
            ]);
        } catch (\Exception $e) {
            error_log("Error creating quick lead: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    /**
     * Update lead with more details (Progressive Profiling Step 2+)
     */
    public function updateProgressive()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid request method'], 405);
            return;
        }

        $leadId = $_POST['lead_id'] ?? $_COOKIE['visitor_lead_id'] ?? null;

        if (!$leadId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Lead not identified'], 400);
            return;
        }

        $data = [];
        if (!empty($_POST['email'])) $data['email'] = $_POST['email'];
        if (!empty($_POST['property_type'])) $data['property_interest'] = $_POST['property_type'];
        if (!empty($_POST['budget'])) $data['budget_range'] = $_POST['budget'];
        if (!empty($_POST['location'])) $data['preferred_location'] = $_POST['location'];
        if (!empty($_POST['role'])) $data['type'] = $_POST['role']; // Buyer/Seller

        if (empty($data)) {
            $this->jsonResponse(['status' => 'success', 'message' => 'No new data to update']);
            return;
        }

        try {
            Lead::update($leadId, $data);

            // If email is provided and they want to register as User
            if (!empty($_POST['create_account']) && !empty($_POST['email'])) {
                // Check if user exists
                $existingUser = User::where('email', $_POST['email'])->orWhere('phone', $_POST['phone'])->first();
                if (!$existingUser) {
                    // We can't create a user without password easily in standard auth, 
                    // but we can prompt them to complete registration on the next screen.
                    // For now, just return success.
                }
            }

            $this->jsonResponse(['status' => 'success', 'message' => 'Details updated successfully']);
        } catch (\Exception $e) {
            logger()->error("Error updating lead: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Update failed'], 500);
        }
    }
}
