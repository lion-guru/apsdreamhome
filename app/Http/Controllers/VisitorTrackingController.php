<?php

namespace App\Http\Controllers;

/**
 * Visitor Tracking Controller
 * Handles visitor tracking and lead capture
 */
class VisitorTrackingController
{
    private $trackingService;

    public function __construct()
    {
        // Lazy load service to avoid database connection issues
        $this->trackingService = null;
    }

    private function getTrackingService()
    {
        if ($this->trackingService === null) {
            require_once __DIR__ . '/../../Services/VisitorTrackingService.php';
            $this->trackingService = new \App\Services\VisitorTrackingService();
        }
        return $this->trackingService;
    }

    /**
     * Track page view (AJAX)
     */
    public function trackPageView()
    {
        $pageUrl = $_POST['page_url'] ?? $_SERVER['REQUEST_URI'] ?? '';
        $pageTitle = $_POST['page_title'] ?? '';

        try {
            $this->getTrackingService()->trackPageView($pageUrl, $pageTitle);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            error_log("Page view tracking error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Track incomplete registration (AJAX)
     */
    public function trackIncompleteRegistration()
    {
        $data = $_POST;

        try {
            $this->getTrackingService()->trackIncompleteRegistration($data);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            error_log("Incomplete registration tracking error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Track visitor interest (AJAX)
     */
    public function trackInterest()
    {
        $data = $_POST;
        $data['interest_type'] = $data['interest_type'] ?? 'general';

        try {
            // This will create a lead in visitor_leads table
            $this->getTrackingService()->trackInterest($data);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            error_log("Interest tracking error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get visitor stats (for admin)
     */
    public function getVisitorStats()
    {
        $db = \App\Core\Database\Database::getInstance();

        try {
            $stats = $db->fetchOne("
                SELECT 
                    COUNT(DISTINCT session_id) as total_visitors,
                    COUNT(DISTINCT CASE WHEN is_converted = 1 THEN session_id END) as converted_visitors,
                    SUM(page_views) as total_page_views,
                    AVG(time_on_site) as avg_time_on_site
                FROM visitor_sessions
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");

            $incompleteRegs = $db->fetchOne("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_converted = 1 THEN 1 END) as converted
                FROM incomplete_registrations
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");

            $leads = $db->fetchOne("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN lead_status = 'new' THEN 1 END) as new_leads,
                    COUNT(CASE WHEN lead_status = 'converted' THEN 1 END) as converted_leads
                FROM visitor_leads
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");

            echo json_encode([
                'success' => true,
                'data' => [
                    'visitors' => $stats,
                    'incomplete_registrations' => $incompleteRegs,
                    'leads' => $leads
                ]
            ]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
