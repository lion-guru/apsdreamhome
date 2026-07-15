<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class AnalyticsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function getRealTimeMetrics()
    {
        header('Content-Type: application/json');
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM properties WHERE status = 'active'");
            $properties = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()");
            $newUsers = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()");
            $newLeads = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COUNT(*) FROM plot_bookings WHERE DATE(created_at) = CURDATE()");
            $newBookings = (int)$stmt->fetchColumn();

            echo json_encode(['success' => true, 'data' => [
                'active_properties' => $properties,
                'new_users_today' => $newUsers,
                'new_leads_today' => $newLeads,
                'new_bookings_today' => $newBookings,
                'timestamp' => date('Y-m-d H:i:s')
            ]]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function exportData()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $format = $data['format'] ?? 'csv';
        $type = $data['type'] ?? 'properties';
        echo json_encode(['success' => true, 'message' => "Export $type as $format queued"]);
    }

    public function getPropertyAnalytics()
    {
        header('Content-Type: application/json');
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM properties");
            $total = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM properties GROUP BY status");
            $byStatus = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => [
                'total' => $total,
                'by_status' => $byStatus
            ]]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getUserAnalytics()
    {
        header('Content-Type: application/json');
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            $total = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
            $byRole = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => [
                'total' => $total,
                'by_role' => $byRole
            ]]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
