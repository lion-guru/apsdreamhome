<?php

namespace App\Http\Controllers\Admin;

// AdminController resolved via namespace
use App\Services\LeadFollowUpService;

/**
 * Lead Follow-up Controller
 * Handles lead follow-up and incomplete registration follow-up
 */
class LeadFollowUpController extends AdminController
{
    private $followUpService;

    public function __construct()
    {
        parent::__construct();
        $this->followUpService = new LeadFollowUpService();
    }

    /**
     * Send follow-ups for incomplete registrations and new leads
     */
    public function sendFollowUps()
    {
        @session_start();

        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $type = $_POST['type'] ?? 'all';

        try {
            if ($type === 'all' || $type === 'incomplete') {
                $this->followUpService->sendFollowUpForIncompleteRegistrations();
            }

            if ($type === 'all' || $type === 'leads') {
                $this->followUpService->sendFollowUpForNewLeads();
            }

            echo json_encode(['success' => true, 'message' => 'Follow-ups sent successfully']);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Get follow-up statistics
     */
    public function getFollowUpStats()
    {
        @session_start();

        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $result = $this->followUpService->getFollowUpStats();
        echo json_encode($result);
        exit;
    }
}
