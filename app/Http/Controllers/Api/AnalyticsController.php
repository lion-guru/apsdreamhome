<?php
namespace App\Http\Controllers\Api;

use App\Core\Database;

class AnalyticsController extends BaseApiController
{
    public function getRealTimeMetrics()
    {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance();
            $activeUsers = 0;
            $todayLeads = 0;
            $todayVisits = 0;
            try { $activeUsers = $db->fetch("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")['count'] ?? 0; } catch (\Exception $e) { error_log('AnalyticsController exception: ' . $e->getMessage()); }
            try { $todayLeads = $db->fetch("SELECT COUNT(*) as count FROM leads WHERE DATE(created_at) = CURDATE()")['count'] ?? 0; } catch (\Exception $e) { error_log('AnalyticsController exception: ' . $e->getMessage()); }
            try { $todayVisits = $db->fetch("SELECT COUNT(*) as count FROM visitor_page_views WHERE DATE(visited_at) = CURDATE()")['count'] ?? 0; } catch (\Exception $e) { error_log('AnalyticsController exception: ' . $e->getMessage()); }
            echo json_encode(['success' => true, 'data' => ['active_users' => (int)$activeUsers, 'today_leads' => (int)$todayLeads, 'today_visits' => (int)$todayVisits]]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Metrics temporarily unavailable']);
        }
        exit;
    }

    public function exportData()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Export via UI dashboard']);
        exit;
    }

    public function getPropertyAnalytics()
    {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance();
            $total = $db->fetch("SELECT COUNT(*) as count FROM user_properties")['count'] ?? 0;
            $byType = $db->fetchAll("SELECT property_type, COUNT(*) as count FROM user_properties GROUP BY property_type");
            echo json_encode(['success' => true, 'data' => ['total' => (int)$total, 'by_type' => $byType]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function getUserAnalytics()
    {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance();
            $total = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
            $recent = $db->fetchAll("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 10");
            echo json_encode(['success' => true, 'data' => ['total' => (int)$total, 'recent' => $recent]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
