<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use PDO;
use App\Traits\TenantAwareTrait;

class MobileAdminApiController extends BaseController
{
    use TenantAwareTrait;
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function batchSyncLeads()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $leads = $input['leads'] ?? [];
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);

        if (!$userId) {
            return $this->errorResponse('Authentication required', 401);
        }

        if (empty($leads)) {
            return $this->errorResponse('No leads provided', 400);
        }

        try {
            $this->db->beginTransaction();
            $cols = "name, email, phone, source, assigned_to, created_by, status, created_at";
            $vals = "?, ?, ?, ?, ?, ?, 'new', NOW()";
            $insParams = [];
            $insertExtra = $this->tenantInsertData();
            if (!empty($insertExtra)) {
                $cols .= ", tenant_id";
                $vals .= ", ?";
                $insParams[] = $insertExtra['tenant_id'];
            }
            $stmt = $this->db->prepare("INSERT INTO leads ({$cols}) VALUES ({$vals})");

            foreach ($leads as $lead) {
                $rowParams = [
                    \App\Core\Security::sanitize($lead['name'] ?? ''),
                    filter_var($lead['email'] ?? '', FILTER_SANITIZE_EMAIL),
                    preg_replace('/[^0-9+]/', '', $lead['phone'] ?? ''),
                    \App\Core\Security::sanitize($lead['source'] ?? 'mobile'),
                    (int)($lead['assigned_to'] ?? 0),
                    $userId,
                ];
                $stmt->execute(array_merge($rowParams, $insParams));
            }
            $this->db->commit();
            return $this->jsonResponse(['success' => true, 'message' => 'Leads synced successfully']);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('MobileAdminApiController::batchSyncLeads failed: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }

    public function submitLead()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            [$validated, $errors] = $this->validateInput($input, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'property_id' => 'nullable|integer',
                'source' => 'nullable|string',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            $input = $validated;
            $leadId = $this->createLead($input);
            echo json_encode(['success' => true, 'lead_id' => $leadId]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Submit Lead API error');
        }
    }

    public function employeeDashboard()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $dashboard = $this->getEmployeeDashboardData($userId);
            echo json_encode(['success' => true, 'data' => $dashboard]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Employee Dashboard API error');
        }
    }

    public function employeeTasks()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $tasks = $this->getEmployeeTasksData($userId);
            echo json_encode(['success' => true, 'data' => $tasks]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Employee Tasks API error');
        }
    }

    public function employeeAttendance()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $attendance = $this->getEmployeeAttendanceData($userId);
            echo json_encode(['success' => true, 'data' => $attendance]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Employee Attendance API error');
        }
    }

    public function bookings()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $bookings = $this->getAdminBookingsData($userId);
            echo json_encode(['success' => true, 'data' => $bookings]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Admin Bookings API error');
        }
    }

    public function updateBookingStatus($id)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $status = \App\Core\Security::sanitize($input['status'] ?? 'pending');
            $this->updateBookingStatusData((int)$id, $status);
            echo json_encode(['success' => true, 'message' => 'Booking status updated']);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Update Booking Status API error');
        }
    }

    public function commissions()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $commissions = $this->getCommissionsData($userId);
            echo json_encode(['success' => true, 'data' => $commissions]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Commissions API error');
        }
    }

    public function commissionAction($id)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $action = \App\Core\Security::sanitize($input['action'] ?? '');
            $result = $this->processCommissionAction((int)$id, $action);
            echo json_encode(['success' => true, 'message' => 'Commission action processed', 'result' => $result]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Commission Action API error');
        }
    }

    public function plots()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $plots = $this->getPlotsData($userId);
            echo json_encode(['success' => true, 'data' => $plots]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Plots API error');
        }
    }

    public function users()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $users = $this->getUsersData($userId);
            echo json_encode(['success' => true, 'data' => $users]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Users API error');
        }
    }

    public function reports()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $reports = $this->getReportsData($userId);
            echo json_encode(['success' => true, 'data' => $reports]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Reports API error');
        }
    }

    public function telecallerDashboard()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $dashboard = $this->getTelecallerDashboardData($userId);
            echo json_encode(['success' => true, 'data' => $dashboard]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Telecaller Dashboard API error');
        }
    }

    public function dashboardStats()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stats = $this->getDashboardStatsData($userId);
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Dashboard Stats API error');
        }
    }

    public function salesTrend()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $trend = $this->getSalesTrendData($userId);
            echo json_encode(['success' => true, 'data' => $trend]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Sales Trend API error');
        }
    }

    public function topAssociates()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $associates = $this->getTopAssociatesData($userId);
            echo json_encode(['success' => true, 'data' => $associates]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Top Associates API error');
        }
    }

    public function colonyPerformance()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $performance = $this->getColonyPerformanceData($userId);
            echo json_encode(['success' => true, 'data' => $performance]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Performance API error');
        }
    }

    public function emiCollection()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $collection = $this->getEmiCollectionData($userId);
            echo json_encode(['success' => true, 'data' => $collection]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'EMI Collection API error');
        }
    }

    public function leadConversion()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $conversion = $this->getLeadConversionData($userId);
            echo json_encode(['success' => true, 'data' => $conversion]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Lead Conversion API error');
        }
    }

    public function dailySales()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $sales = $this->getDailySalesData($userId);
            echo json_encode(['success' => true, 'data' => $sales]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Daily Sales API error');
        }
    }

    private function getEmployeeDashboardData($userId)
    {
        $data = [];
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookings WHERE assigned_to = ? AND DATE(created_at) = CURDATE()");
        $stmt->execute([$userId]);
        $data['todays_bookings'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to = ? AND status = 'new'");
        $stmt->execute([$userId]);
        $data['new_leads'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM site_visits WHERE agent_id = ? AND DATE(visit_date) = CURDATE()");
        $stmt->execute([$userId]);
        $data['todays_visits'] = (int)$stmt->fetchColumn();

        return $data;
    }

    private function getEmployeeTasksData($userId)
    {
        $stmt = $this->db->prepare("SELECT id, title, description, status, due_date, created_at FROM tasks WHERE assignee_id = ? AND status != 'completed' ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEmployeeAttendanceData($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT id, check_in, check_out, attendance_date as date, status FROM attendance_records WHERE user_id = ? ORDER BY attendance_date DESC LIMIT 30");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('MobileAdminApiController::getEmployeeAttendanceData error: ' . $e->getMessage());
            return [];
        }
    }

    private function getAdminBookingsData($userId)
    {
        $stmt = $this->db->prepare("SELECT b.id, b.booking_date, b.amount, b.status, b.created_at, p.title as property_title, u.name as customer_name FROM bookings b LEFT JOIN properties p ON b.property_id = p.id LEFT JOIN users u ON b.customer_id = u.id ORDER BY b.created_at DESC LIMIT 50");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function updateBookingStatusData($bookingId, $status)
    {
        $stmt = $this->db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $bookingId]);
    }

    private function getCommissionsData($userId)
    {
        $stmt = $this->db->prepare("SELECT c.id, c.amount, c.status, c.created_at, u.name as customer_name FROM mlm_commission_ledger c LEFT JOIN users u ON c.source_user_id = u.id WHERE c.beneficiary_user_id = ? ORDER BY c.created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function processCommissionAction($commissionId, $action)
    {
        if ($action === 'release') {
            $stmt = $this->db->prepare("UPDATE mlm_commission_ledger SET status = 'released' WHERE id = ?");
            $stmt->execute([$commissionId]);
            return true;
        } elseif ($action === 'hold') {
            $stmt = $this->db->prepare("UPDATE mlm_commission_ledger SET status = 'on_hold' WHERE id = ?");
            $stmt->execute([$commissionId]);
            return true;
        }
        return false;
    }

    private function getPlotsData($userId)
    {
        $stmt = $this->db->prepare("SELECT p.id, p.plot_number as title, p.total_price as price, p.status, p.created_at, c.name as colony_name FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id ORDER BY p.created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUsersData($userId)
    {
        $stmt = $this->db->prepare("SELECT id, name, email, phone, status, role, created_at FROM users ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getReportsData($userId)
    {
        $data = [];
        $stmt = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM bookings WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date DESC");
        $stmt->execute([]);
        $data['bookings_30days'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM leads WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date DESC");
        $stmt->execute([]);
        $data['leads_30days'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    private function getTelecallerDashboardData($userId)
    {
        $data = [];
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to = ? AND DATE(created_at) = CURDATE()");
        $stmt->execute([$userId]);
        $data['todays_leads'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM activities WHERE user_id = ? AND DATE(activity_date) = CURDATE()");
        $stmt->execute([$userId]);
        $data['todays_activities'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to = ? AND status = 'new'");
        $stmt->execute([$userId]);
        $data['new_leads'] = (int)$stmt->fetchColumn();

        return $data;
    }

    private function getDashboardStatsData($userId)
    {
        $data = [];
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute([]);
        $data['bookings_7days'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute([]);
        $data['leads_7days'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM bookings WHERE status = 'confirmed'");
        $stmt->execute([]);
        $data['total_revenue'] = (float)$stmt->fetchColumn();

        return $data;
    }

    private function getSalesTrendData($userId)
    {
        $stmt = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as bookings, COALESCE(SUM(amount), 0) as revenue FROM bookings WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date ASC");
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTopAssociatesData($userId)
    {
        $stmt = $this->db->prepare("SELECT u.id, u.name, u.email, COUNT(b.id) as bookings, COALESCE(SUM(b.amount), 0) as revenue FROM users u LEFT JOIN bookings b ON u.id = b.associate_id WHERE u.role IN ('agent', 'associate') GROUP BY u.id ORDER BY revenue DESC LIMIT 10");
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getColonyPerformanceData($userId)
    {
        $stmt = $this->db->prepare("SELECT c.id, c.name, COUNT(p.id) as plots, COUNT(b.id) as bookings, COALESCE(SUM(b.amount), 0) as revenue FROM colonies c LEFT JOIN plots p ON c.id = p.colony_id LEFT JOIN bookings b ON p.id = b.property_id GROUP BY c.id ORDER BY revenue DESC LIMIT 10");
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEmiCollectionData($userId)
    {
        $stmt = $this->db->prepare("SELECT DATE(p.created_at) as date, COUNT(*) as payments, COALESCE(SUM(p.amount), 0) as total FROM payments p LEFT JOIN bookings b ON p.booking_id = b.id WHERE DATE(p.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(p.created_at) ORDER BY date DESC");
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getLeadConversionData($userId)
    {
        $stmt = $this->db->prepare("SELECT DATE(l.created_at) as date, COUNT(*) as leads, SUM(CASE WHEN l.is_converted = 1 THEN 1 ELSE 0 END) as conversions, ROUND(SUM(CASE WHEN l.is_converted = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as conversion_rate FROM leads l WHERE DATE(l.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(l.created_at) ORDER BY date DESC");
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getDailySalesData($userId)
    {
        $stmt = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as sales, COALESCE(SUM(amount), 0) as total FROM bookings WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date DESC");
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================
    // COLONY PIPELINE (routes existed, actions did not)
    // Layout -> plots -> pricing -> costs for a colony, driven from mobile.
    // ============================================================

    private function colonyPipelineDb()
    {
        return \App\Core\Database\Database::getInstance();
    }

    private function colonyPipelineTenantId(): int
    {
        return (int) (\App\Core\Middleware\TenantContext::getId() ?: 1);
    }

    /** GET /api/v2/mobile/colony-pipeline/dashboard */
    public function colonyPipelineDashboard()
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();

            $colonies = $db->fetchAll(
                "SELECT c.id, c.name, c.status,
                        (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.deleted_at IS NULL) AS total_plots,
                        (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'available' AND p.deleted_at IS NULL) AS available_plots,
                        (SELECT COUNT(*) FROM colony_layouts l WHERE l.colony_id = c.id AND l.is_current = 1 AND l.deleted_at IS NULL) AS has_layout,
                        (SELECT COUNT(*) FROM colony_development_costs dc WHERE dc.colony_id = c.id AND dc.deleted_at IS NULL) AS cost_entries,
                        (SELECT COUNT(*) FROM colony_pricing_feasibility pf WHERE pf.colony_id = c.id) AS pricing_rows
                 FROM colonies c
                 WHERE c.tenant_id = ?
                 ORDER BY c.name",
                [$tid]
            ) ?: [];

            $totals = [
                'colonies'     => count($colonies),
                'total_plots'  => array_sum(array_map(fn($c) => (int)($c['total_plots'] ?? 0), $colonies)),
                'available'    => array_sum(array_map(fn($c) => (int)($c['available_plots'] ?? 0), $colonies)),
                'with_layout'  => count(array_filter($colonies, fn($c) => (int)($c['has_layout'] ?? 0) > 0)),
                'with_pricing' => count(array_filter($colonies, fn($c) => (int)($c['pricing_rows'] ?? 0) > 0)),
            ];

            $this->jsonResponse(['success' => true, 'data' => ['colonies' => $colonies, 'totals' => $totals]]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Pipeline Dashboard');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/detail/{colonyId} */
    public function colonyPipelineDetail($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            $colony = $db->fetchOne(
                "SELECT id, name, status, total_plots, available_plots, starting_price, description
                 FROM colonies WHERE id = ? AND tenant_id = ? LIMIT 1",
                [$cid, $tid]
            );
            if (!$colony) {
                $this->jsonError('Colony not found', 404);
                return;
            }

            $layout = $db->fetchOne(
                "SELECT * FROM colony_layouts
                 WHERE colony_id = ? AND tenant_id = ? AND is_current = 1 AND deleted_at IS NULL
                 ORDER BY id DESC LIMIT 1",
                [$cid, $tid]
            );

            $plotStats = $db->fetchOne(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
                        SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) AS booked,
                        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) AS sold
                 FROM plots WHERE colony_id = ? AND deleted_at IS NULL",
                [$cid]
            ) ?: [];

            $costs = $db->fetchAll(
                "SELECT * FROM colony_development_costs
                 WHERE colony_id = ? AND tenant_id = ? AND deleted_at IS NULL
                 ORDER BY id DESC LIMIT 200",
                [$cid, $tid]
            ) ?: [];

            $pricing = $db->fetchOne(
                "SELECT * FROM colony_pricing_feasibility
                 WHERE colony_id = ? AND tenant_id = ? ORDER BY id DESC LIMIT 1",
                [$cid, $tid]
            );

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'colony'     => $colony,
                    'layout'    => $layout,
                    'plot_stats' => $plotStats,
                    'costs'     => $costs,
                    'pricing'   => $pricing,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Pipeline Detail');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/layout/{colonyId} */
    public function colonyPipelineLayout($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            $layout = $db->fetchOne(
                "SELECT * FROM colony_layouts
                 WHERE colony_id = ? AND tenant_id = ? AND is_current = 1 AND deleted_at IS NULL
                 ORDER BY id DESC LIMIT 1",
                [$cid, $tid]
            );

            if (!$layout) {
                $this->jsonError('No current layout for this colony', 404);
                return;
            }

            $map = json_decode((string)($layout['plot_map_json'] ?? '{}'), true);
            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'layout'  => $layout,
                    'plot_map' => is_array($map) ? $map : [],
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Pipeline Layout');
        }
    }

    /** POST /api/v2/mobile/colony-pipeline/generate-plots/{colonyId} */
    public function colonyPipelineGeneratePlots($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;
            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            $layout = $db->fetchOne(
                "SELECT * FROM colony_layouts
                 WHERE colony_id = ? AND tenant_id = ? AND is_current = 1 AND deleted_at IS NULL
                 ORDER BY id DESC LIMIT 1",
                [$cid, $tid]
            );
            if (!$layout) {
                $this->jsonError('Save a layout before generating plots', 422);
                return;
            }

            $map = json_decode((string)($layout['plot_map_json'] ?? '{}'), true);
            $plots = is_array($map) ? ($map['plots'] ?? $map) : [];
            if (!is_array($plots) || empty($plots)) {
                $this->jsonError('The layout does not contain any plots', 422);
                return;
            }

            $basePrice = (float)($input['base_price'] ?? 0);
            $created = 0;
            $skipped = 0;

            foreach ($plots as $p) {
                if (!is_array($p)) {
                    continue;
                }
                $number = (string)($p['number'] ?? $p['plot_number'] ?? '');
                if ($number === '') {
                    continue;
                }
                $exists = $db->fetchOne(
                    "SELECT id FROM plots WHERE colony_id = ? AND plot_number = ? LIMIT 1",
                    [$cid, $number]
                );
                if ($exists) {
                    $skipped++;
                    continue;
                }
                $area = (float)($p['area'] ?? $p['area_sqft'] ?? 0);
                $rate = (float)($p['rate'] ?? $basePrice);
                $total = $area > 0 && $rate > 0 ? $area * $rate : 0;

                $db->insert('plots', [
                    'tenant_id'    => $tid,
                    'colony_id'    => $cid,
                    'plot_number'  => $number,
                    'plot_type'    => (string)($p['type'] ?? 'residential'),
                    'area_sqft'    => $area,
                    'dimension_label' => (string)($p['dimensions'] ?? ''),
                    'facing'       => (string)($p['facing'] ?? ''),
                    'base_price_per_sqft' => $rate,
                    'final_price_per_sqft' => $rate,
                    'total_price'  => $total,
                    'status'       => 'available',
                    'is_active'    => 1,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
                $created++;
            }

            $this->jsonResponse([
                'success' => true,
                'message' => "Generated {$created} plots ({$skipped} already existed)",
                'data'    => ['created' => $created, 'skipped' => $skipped],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Generate Plots');
        }
    }

    /** POST /api/v2/mobile/colony-pipeline/save-layout/{colonyId} */
    public function colonyPipelineSaveLayout($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;
            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            $name = trim((string)($input['layout_name'] ?? 'Mobile Layout'));
            $mapJson = isset($input['plot_map'])
                ? (is_string($input['plot_map']) ? $input['plot_map'] : json_encode($input['plot_map']))
                : '{}';

            // version bump: previous current layout is archived
            $db->execute(
                "UPDATE colony_layouts SET is_current = 0, updated_at = NOW()
                 WHERE colony_id = ? AND tenant_id = ? AND is_current = 1",
                [$cid, $tid]
            );

            $version = 1 + (int)($db->fetchOne(
                "SELECT COALESCE(MAX(version), 0) v FROM colony_layouts WHERE colony_id = ? AND tenant_id = ?",
                [$cid, $tid]
            )['v'] ?? 0);

            $db->insert('colony_layouts', [
                'tenant_id'     => $tid,
                'colony_id'     => $cid,
                'layout_name'   => $name,
                'version'       => $version,
                'layout_type'   => (string)($input['layout_type'] ?? 'residential'),
                'road_area_pct' => (float)($input['road_area_pct'] ?? 0),
                'common_area_pct' => (float)($input['common_area_pct'] ?? 0),
                'is_current'    => 1,
                'plot_map_json' => $mapJson,
                'total_plots'   => (int)($input['total_plots'] ?? 0),
                'total_area_sqft' => (float)($input['total_area_sqft'] ?? 0),
                'notes'         => (string)($input['notes'] ?? ''),
                'status'        => 'draft',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Layout saved',
                'data'    => ['version' => $version],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Save Layout');
        }
    }

    /** POST /api/v2/mobile/colony-pipeline/delete-plots/{colonyId} */
    public function colonyPipelineDeletePlots($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;
            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            // Only soft-delete plots that are still available — never touch a
            // booked/sold plot (money + customer records are attached).
            $ids = array_values(array_filter(array_map('intval', (array)($input['plot_ids'] ?? []))));
            if (empty($ids)) {
                $this->jsonError('No plot ids supplied');
                return;
            }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$cid, $tid], $ids);
            $stmt = $db->prepare(
                "UPDATE plots SET deleted_at = NOW(), updated_at = NOW()
                 WHERE colony_id = ? AND tenant_id = ? AND status = 'available'
                   AND id IN ($placeholders)"
            );
            $stmt->execute($params);
            $deleted = $stmt->rowCount();

            $this->jsonResponse([
                'success' => true,
                'message' => "{$deleted} plot(s) removed",
                'data'    => ['deleted' => $deleted],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Delete Plots');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/pricing/{colonyId} */
    public function colonyPipelinePricing($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            $row = $db->fetchOne(
                "SELECT * FROM colony_pricing_feasibility WHERE colony_id = ? AND tenant_id = ? ORDER BY id DESC LIMIT 1",
                [$cid, $tid]
            );

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'pricing'   => $row,
                    'revisions' => $db->fetchAll(
                        "SELECT * FROM colony_price_revisions WHERE colony_id = ? AND tenant_id = ? ORDER BY id DESC LIMIT 20",
                        [$cid, $tid]
                    ) ?: [],
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Pipeline Pricing');
        }
    }

    /** POST /api/v2/mobile/colony-pipeline/calculate-pricing/{colonyId} */
    public function colonyPipelineCalculatePricing($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;
            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            $rawArea = (float)($input['total_raw_area_sqft'] ?? 0);
            $yieldPct = (float)($input['saleable_area_yield_pct'] ?? 70);
            if ($rawArea <= 0 || $yieldPct <= 0) {
                $this->jsonError('total_raw_area_sqft and saleable_area_yield_pct are required', 422);
                return;
            }

            $saleable = $rawArea * ($yieldPct / 100);

            $landCost = (float)($input['land_cost_total'] ?? 0);
            $registryCost = (float)($input['registry_cost_total'] ?? 0);
            $devCost = (float)($input['development_cost_total'] ?? $this->colonyPipelineCostTotal($db, $cid, $tid));
            $approvalsCost = (float)($input['approvals_cost_total'] ?? 0);

            $totalCost = $landCost + $registryCost + $devCost + $approvalsCost;
            $rawBasis = $totalCost / max($rawArea, 1);

            $profitPct = (float)($input['target_profit_pct'] ?? 15);
            $overheadPct = (float)($input['office_overhead_pct'] ?? 3);
            $mlmPct = (float)($input['mlm_budget_pct'] ?? 5);
            $markup = (float)($input['markup_factor'] ?? 1);

            $multiplier = (1 + $profitPct / 100) * (1 + $overheadPct / 100) * (1 + $mlmPct / 100) * $markup;
            $recommended = round($rawBasis * $multiplier, 2);

            $db->insert('colony_pricing_feasibility', [
                'tenant_id'                => $tid,
                'colony_id'                => $cid,
                'total_raw_area_sqft'      => $rawArea,
                'saleable_area_yield_pct'  => $yieldPct,
                'saleable_area_sqft'       => round($saleable, 2),
                'land_cost_total'          => $landCost,
                'registry_cost_total'      => $registryCost,
                'development_cost_total'   => $devCost,
                'approvals_cost_total'     => $approvalsCost,
                'raw_cost_basis_ppsf'      => round($rawBasis, 2),
                'target_profit_pct'        => $profitPct,
                'office_overhead_pct'      => $overheadPct,
                'mlm_budget_pct'           => $mlmPct,
                'markup_factor'            => $markup,
                'recommended_price_ppsf'   => $recommended,
                'notes'                    => (string)($input['notes'] ?? ''),
                'created_by'               => (int)($GLOBALS['api_user_id'] ?? 0),
                'created_at'               => date('Y-m-d H:i:s'),
            ]);

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'total_cost'          => round($totalCost, 2),
                    'raw_cost_basis_ppsf' => round($rawBasis, 2),
                    'recommended_price_ppsf' => $recommended,
                    'saleable_area_sqft'  => round($saleable, 2),
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Calculate Pricing');
        }
    }

    /** POST /api/v2/mobile/colony-pipeline/apply-pricing/{colonyId} */
    public function colonyPipelineApplyPricing($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;
            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            $pricePpsf = (float)($input['price_ppsf'] ?? 0);
            if ($pricePpsf <= 0) {
                $this->jsonError('price_ppsf must be greater than zero', 422);
                return;
            }

            // Only AVAILABLE plots are repriced — booked/sold plots keep the
            // price the customer agreed on.
            $stmt = $db->prepare(
                "SELECT id, area_sqft FROM plots
                 WHERE colony_id = ? AND tenant_id = ? AND status = 'available' AND deleted_at IS NULL"
            );
            $stmt->execute([$cid, $tid]);
            $plots = $stmt->fetchAll() ?: [];

            $updated = 0;
            foreach ($plots as $p) {
                $area = (float)($p['area_sqft'] ?? 0);
                $total = round($area * $pricePpsf, 2);
                $db->execute(
                    "UPDATE plots SET base_price_per_sqft = ?, final_price_per_sqft = ?, total_price = ?, updated_at = NOW() WHERE id = ?",
                    [$pricePpsf, $pricePpsf, $total, (int)$p['id']]
                );
                $updated++;
            }

            $db->execute(
                "UPDATE colony_pricing_feasibility SET applied_price_ppsf = ? WHERE colony_id = ? AND tenant_id = ?",
                [$pricePpsf, $cid, $tid]
            );

            $this->jsonResponse([
                'success' => true,
                'message' => "Repriced {$updated} available plot(s)",
                'data'    => ['updated' => $updated, 'price_ppsf' => $pricePpsf],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Apply Pricing');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/costs/{colonyId} */
    public function colonyPipelineCosts($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            $costs = $db->fetchAll(
                "SELECT * FROM colony_development_costs
                 WHERE colony_id = ? AND tenant_id = ? AND deleted_at IS NULL
                 ORDER BY id DESC LIMIT 300",
                [$cid, $tid]
            ) ?: [];

            $summary = [
                'entries'  => count($costs),
                'total'    => array_sum(array_map(fn($c) => (float)($c['amount'] ?? 0), $costs)),
                'paid'     => array_sum(array_map(fn($c) => (float)($c['paid_amount'] ?? 0), $costs)),
                'balance'  => array_sum(array_map(fn($c) => (float)($c['balance_amount'] ?? 0), $costs)),
            ];

            $this->jsonResponse(['success' => true, 'data' => ['costs' => $costs, 'summary' => $summary]]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Pipeline Costs');
        }
    }

    /** POST /api/v2/mobile/colony-pipeline/costs/store/{colonyId} */
    public function colonyPipelineStoreCost($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;
            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            $amount = (float)($input['amount'] ?? 0);
            if ($amount <= 0) {
                $this->jsonError('amount must be greater than zero', 422);
                return;
            }

            $gstRate = (float)($input['gst_rate'] ?? 0);
            $gstAmount = (float)($input['gst_amount'] ?? round($amount * $gstRate / 100, 2));
            $paid = (float)($input['paid_amount'] ?? 0);

            $db->insert('colony_development_costs', [
                'tenant_id'          => $tid,
                'colony_id'          => $cid,
                'cost_type'          => (string)($input['cost_type'] ?? 'other'),
                'vendor_id'          => (int)($input['vendor_id'] ?? 0) ?: null,
                'vendor_name'        => (string)($input['vendor_name'] ?? ''),
                'work_description'   => (string)($input['work_description'] ?? ''),
                'invoice_number'     => (string)($input['invoice_number'] ?? ''),
                'invoice_date'       => (string)($input['invoice_date'] ?? date('Y-m-d')),
                'amount'             => $amount,
                'gst_amount'         => $gstAmount,
                'gst_rate'           => $gstRate,
                'tds_section'        => (string)($input['tds_section'] ?? ''),
                'tds_amount'         => (float)($input['tds_amount'] ?? 0),
                'payment_status'     => (string)($input['payment_status'] ?? 'pending'),
                'paid_amount'        => $paid,
                'balance_amount'     => max(0, $amount - $paid),
                'completion_date'    => (string)($input['completion_date'] ?? ''),
                'status'             => (string)($input['status'] ?? 'active'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);

            $this->jsonResponse(['success' => true, 'message' => 'Cost entry saved']);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Store Colony Cost');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/plots/{colonyId} */
    public function colonyPipelinePlots($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));
            $offset = max(0, (int)($_GET['offset'] ?? 0));

            $plots = $db->fetchAll(
                "SELECT id, plot_number, plot_type, area_sqft, dimension_label, facing,
                        base_price_per_sqft, total_price, status, is_active
                 FROM plots
                 WHERE colony_id = ? AND tenant_id = ? AND deleted_at IS NULL
                 ORDER BY id DESC LIMIT $limit OFFSET $offset",
                [$cid, $tid]
            ) ?: [];

            $this->jsonResponse(['success' => true, 'data' => ['plots' => $plots]]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Pipeline Plots');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/plots/stats/{colonyId} */
    public function colonyPipelinePlotsStats($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            $stats = $db->fetchOne(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
                        SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) AS booked,
                        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) AS sold,
                        SUM(CASE WHEN status = 'hold' THEN 1 ELSE 0 END) AS hold,
                        COALESCE(SUM(total_price), 0) AS inventory_value,
                        COALESCE(AVG(area_sqft), 0) AS avg_area,
                        COALESCE(AVG(total_price), 0) AS avg_price
                 FROM plots WHERE colony_id = ? AND tenant_id = ? AND deleted_at IS NULL",
                [$cid, $tid]
            ) ?: [];

            $this->jsonResponse(['success' => true, 'data' => $stats]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Pipeline Plots Stats');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/map/{colonyId} */
    public function colonyPipelineMap($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            $layout = $db->fetchOne(
                "SELECT id, layout_name, layout_file_url, layout_plan_image, plot_map_json, total_plots, total_area_sqft
                 FROM colony_layouts
                 WHERE colony_id = ? AND tenant_id = ? AND is_current = 1 AND deleted_at IS NULL
                 ORDER BY id DESC LIMIT 1",
                [$cid, $tid]
            );

            if (!$layout) {
                $this->jsonError('No current layout for this colony', 404);
                return;
            }

            $map = json_decode((string)($layout['plot_map_json'] ?? '{}'), true);

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'layout'   => $layout,
                    'plot_map' => is_array($map) ? $map : [],
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Pipeline Map');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/map/geojson/{colonyId} */
    public function colonyPipelineMapGeoJson($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            $rows = $db->fetchAll(
                "SELECT id, plot_number, plot_type, area_sqft, dimension_label, facing, base_price_per_sqft, total_price, status
                 FROM plots WHERE colony_id = ? AND tenant_id = ? AND deleted_at IS NULL",
                [$cid, $tid]
            ) ?: [];

            $features = [];
            foreach ($rows as $r) {
                $geometry = null;
                // plot_map_json may carry WKT/GeoJSON per plot
                $features[] = [
                    'type'       => 'Feature',
                    'id'         => (string)$r['id'],
                    'geometry'   => $geometry,
                    'properties' => [
                        'plot_number' => $r['plot_number'],
                        'plot_type'   => $r['plot_type'],
                        'area_sqft'   => $r['area_sqft'],
                        'facing'      => $r['facing'],
                        'base_price'  => $r['base_price_per_sqft'],
                        'total_price' => $r['total_price'],
                        'status'      => $r['status'],
                    ],
                ];
            }

            $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'type'     => 'FeatureCollection',
                    'features' => $features,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Colony Pipeline GeoJSON');
        }
    }

    /** POST /api/v2/mobile/colony-pipeline/pricing-plan/save/{colonyId} */
    public function colonyPipelineSavePricingPlan($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;
            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            $name = trim((string)($input['name'] ?? 'Pricing Plan'));
            $rows = $input['rows'] ?? [];
            if (!is_array($rows) || empty($rows)) {
                $this->jsonError('rows are required', 422);
                return;
            }

            $total = 0.0;
            foreach ($rows as $r) {
                $total += (float)(is_array($r) ? ($r['price'] ?? 0) : 0);
            }

            $db->insert('colony_price_revisions', [
                'tenant_id'         => $tid,
                'colony_id'         => $cid,
                'current_price'     => (float)($input['current_price'] ?? 0),
                'new_price'         => $total,
                'effective_date'    => (string)($input['effective_date'] ?? date('Y-m-d')),
                'announcement_title' => $name,
                'is_active'         => 0,
                'created_by'        => (int)($GLOBALS['api_user_id'] ?? 0),
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            $this->jsonResponse(['success' => true, 'message' => 'Pricing plan saved']);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Save Pricing Plan');
        }
    }

    /** POST /api/v2/mobile/colony-pipeline/pricing-plan/activate/{colonyId}/{planId} */
    public function colonyPipelineActivatePricingPlan($colonyId = null, $planId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;
            $pid = (int)$planId;

            $plan = $db->fetchOne(
                "SELECT * FROM colony_price_revisions WHERE id = ? AND colony_id = ? AND tenant_id = ? LIMIT 1",
                [$pid, $cid, $tid]
            );
            if (!$plan) {
                $this->jsonError('Pricing plan not found', 404);
                return;
            }

            $db->execute(
                "UPDATE colony_price_revisions SET is_active = 0 WHERE colony_id = ? AND tenant_id = ?",
                [$cid, $tid]
            );
            $db->execute(
                "UPDATE colony_price_revisions SET is_active = 1 WHERE id = ?",
                [$pid]
            );

            $this->jsonResponse(['success' => true, 'message' => 'Pricing plan activated']);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Activate Pricing Plan');
        }
    }

    /** POST /api/v2/mobile/colony-pipeline/pricing-plan/apply/{colonyId}/{planId} */
    public function colonyPipelineApplyPricingPlan($colonyId = null, $planId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;
            $pid = (int)$planId;

            $plan = $db->fetchOne(
                "SELECT * FROM colony_price_revisions WHERE id = ? AND colony_id = ? AND tenant_id = ? LIMIT 1",
                [$pid, $cid, $tid]
            );
            if (!$plan) {
                $this->jsonError('Pricing plan not found', 404);
                return;
            }

            $ppsf = (float)($plan['new_price'] ?? 0);
            if ($ppsf <= 0) {
                $this->jsonError('The pricing plan has no usable price', 422);
                return;
            }

            $stmt = $db->prepare(
                "SELECT id, area_sqft FROM plots
                 WHERE colony_id = ? AND tenant_id = ? AND status = 'available' AND deleted_at IS NULL"
            );
            $stmt->execute([$cid, $tid]);
            $plots = $stmt->fetchAll() ?: [];

            $updated = 0;
            foreach ($plots as $p) {
                $total = round((float)($p['area_sqft'] ?? 0) * $ppsf, 2);
                $db->execute(
                    "UPDATE plots SET base_price_per_sqft = ?, final_price_per_sqft = ?, total_price = ?, updated_at = NOW() WHERE id = ?",
                    [$ppsf, $ppsf, $total, (int)$p['id']]
                );
                $updated++;
            }

            $this->jsonResponse([
                'success' => true,
                'message' => "Applied plan to {$updated} available plot(s)",
                'data'    => ['updated' => $updated, 'price_ppsf' => $ppsf],
            ]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Apply Pricing Plan');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/pricing-plan/history/{colonyId} */
    public function colonyPipelinePricingPlanHistory($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            $history = $db->fetchAll(
                "SELECT id, current_price, new_price, effective_date, announcement_title, is_active, created_by, created_at
                 FROM colony_price_revisions
                 WHERE colony_id = ? AND tenant_id = ?
                 ORDER BY id DESC LIMIT 100",
                [$cid, $tid]
            ) ?: [];

            $this->jsonResponse(['success' => true, 'data' => ['history' => $history]]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Pricing Plan History');
        }
    }

    /** GET /api/v2/mobile/colony-pipeline/pricing-plan/applications/{colonyId} */
    public function colonyPipelinePricingPlanApplications($colonyId = null)
    {
        $this->setCorsHeaders();
        try {
            $db = $this->colonyPipelineDb();
            $tid = $this->colonyPipelineTenantId();
            $cid = (int)$colonyId;

            // Which plots currently carry a price that came from a plan, and
            // which of those already have a customer attached.
            $applications = $db->fetchAll(
                "SELECT p.id AS plot_id, p.plot_number, p.area_sqft, p.base_price_per_sqft, p.total_price,
                        p.status AS plot_status, p.updated_at AS priced_at,
                        (SELECT COUNT(*) FROM plot_bookings pb WHERE pb.plot_id = p.id) AS bookings
                 FROM plots p
                 WHERE p.colony_id = ? AND p.tenant_id = ? AND p.deleted_at IS NULL
                   AND p.base_price_per_sqft > 0
                 ORDER BY p.updated_at DESC LIMIT 200",
                [$cid, $tid]
            ) ?: [];

            $this->jsonResponse(['success' => true, 'data' => ['applications' => $applications]]);
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Pricing Plan Applications');
        }
    }

    private function colonyPipelineCostTotal($db, int $colonyId, int $tid): float
    {
        $row = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) t FROM colony_development_costs
             WHERE colony_id = ? AND tenant_id = ? AND deleted_at IS NULL",
            [$colonyId, $tid]
        );
        return (float)($row['t'] ?? 0);
    }

    public function submitProperty()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $userId = (int)($GLOBALS['api_user_id'] ?? $input['user_id'] ?? 0);
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User identification required']);
            return;
        }

        // Validate required fields
        if (empty($input['title'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Title is required']);
            return;
        }

        try {
            // Determine submitter type based on user rank
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $type = ($user && $user['role'] != 'customer' && $user['role'] != '') ? 'agent' : 'customer';

            $submissionService = new \App\Services\PropertySubmissionService();
            $data = [
                'submitter_id' => $userId,
                'submitter_type' => $type,
                'title' => Security::sanitize($input['title'] ?? ''),
                'description' => Security::sanitize($input['description'] ?? ''),
                'price' => Security::sanitize($input['price'] ?? ''),
                'property_type' => Security::sanitize($input['property_type'] ?? 'Plot'),
                'location' => Security::sanitize($input['location'] ?? ''),
                'images' => array_map(function($img) {
                    if (is_string($img)) return \App\Core\Security::sanitize($img);
                    return $img;
                }, $input['images'] ?? [])
            ];

            $result = $submissionService->submitProperty($data);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log("[MobileAdminApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Get user's own submissions (Phase 5)
     */
    public function getSubmissions()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);

        try {
            $submissionService = new \App\Services\PropertySubmissionService();
            $data = $submissionService->getUserSubmissions($userId);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            error_log("[MobileAdminApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    public function leads()
    {
        header('Content-Type: application/json');
        $db = \App\Core\Database::getInstance();
        list($tSql, $tParams) = $this->tenantWhere();
        if (!empty($tSql)) {
            $leads = $db->fetchAll("SELECT * FROM leads WHERE 1=1 {$tSql} ORDER BY created_at DESC LIMIT 20", $tParams);
        } else {
            $leads = $db->fetchAll("SELECT * FROM leads ORDER BY created_at DESC LIMIT 20");
        }
        echo json_encode(['success' => true, 'data' => $leads]);
        exit;
    }

    public function changeLeadStatus($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate status against allowed values
        $allowedStatuses = ['new','contacted','qualified','proposal','negotiation','closed_won','closed_lost'];
        $status = $input['status'] ?? '';
        $status = \App\Core\Security::sanitize($status);
        if (!in_array($status, $allowedStatuses)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid status value']); return;
        }
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$status, $id, $tid]);
            echo json_encode(['success'=>true,'message'=>'Status updated']);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function scheduleLeadFollowup($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $followupDate = \App\Core\Security::sanitize($input['followup_date'] ?? '');
        $notes = \App\Core\Security::sanitize($input['notes'] ?? '');
        if (empty($followupDate)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Follow-up date required']); return; }
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("UPDATE leads SET next_activity_date = ?, notes = CONCAT(COALESCE(notes,''), ?), updated_at = NOW() WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$followupDate, "\n[Follow-up: $notes]", $id, $tid]);
            echo json_encode(['success'=>true,'message'=>'Follow-up scheduled']);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function addLeadActivity($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate activity type
        $allowedTypes = ['note','call','email','meeting','task','conversion'];
        $type = $input['type'] ?? 'note';
        $type = \App\Core\Security::sanitize($type);
        if (!in_array($type, $allowedTypes)) {
            $type = 'note';
        }
        $description = \App\Core\Security::sanitize($input['description'] ?? '');
        if (empty($description)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Description required']); return; }
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO lead_activities (lead_id, created_by, activity_type, description, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$id, $userId, $type, $description, $tid]);
            echo json_encode(['success'=>true,'message'=>'Activity logged']);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function convertLead($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $dealValue = (float)($input['deal_value'] ?? 0);
        if ($dealValue < 0) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid deal value']); return;
        }
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("UPDATE leads SET status = 'closed_won', converted_at = NOW(), estimated_value = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$dealValue, $id, $tid]);
            $this->db->prepare("INSERT INTO lead_activities (lead_id, created_by, activity_type, description, tenant_id, created_at) VALUES (?, ?, 'conversion', 'Lead converted to deal', ?, NOW())")->execute([$id, $userId, $tid]);
            echo json_encode(['success'=>true,'message'=>'Lead converted']);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function markLeadLost($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $reason = \App\Core\Security::sanitize($input['reason'] ?? '');
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("UPDATE leads SET status = 'closed_lost', notes = CONCAT(COALESCE(notes,''), ?), updated_at = NOW() WHERE id = ? AND tenant_id = ?");
            $stmt->execute(["\n[Lost: $reason]", $id, $tid]);
            $this->db->prepare("INSERT INTO lead_activities (lead_id, created_by, activity_type, description, tenant_id, created_at) VALUES (?, ?, 'lost', ?, ?, NOW())")->execute([$id, $userId, 'Lead lost: ' . $reason, $tid]);
            echo json_encode(['success'=>true,'message'=>'Lead marked as lost']);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function getLeadStatistics() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM leads GROUP BY status");
            $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            echo json_encode(['success'=>true,'data'=>[
                'new' => (int)($stats['new'] ?? 0),
                'contacted' => (int)($stats['contacted'] ?? 0),
                'qualified' => (int)($stats['qualified'] ?? 0),
                'proposal' => (int)($stats['proposal'] ?? 0),
                'negotiation' => (int)($stats['negotiation'] ?? 0),
                'closed_won' => (int)($stats['closed_won'] ?? 0),
                'closed_lost' => (int)($stats['closed_lost'] ?? 0),
                'total' => array_sum($stats),
            ]]);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function logLeadCall($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $duration = (int)($input['duration'] ?? 0);
        $notes = \App\Core\Security::sanitize($input['notes'] ?? '');
        $callType = in_array(($input['call_type'] ?? ''), ['inbound', 'outbound']) ? $input['call_type'] : 'outbound';
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO lead_activities (lead_id, created_by, activity_type, description, tenant_id, created_at) VALUES (?, ?, 'call', ?, ?, NOW())");
            $desc = "Call ($callType, " . ($duration ? "{$duration}s" : 'no duration') . "): $notes";
            $stmt->execute([$id, $userId, $desc, $tid]);
            $this->db->prepare("UPDATE leads SET updated_at = NOW() WHERE id = ? AND tenant_id = ?")->execute([$id, $tid]);
            echo json_encode(['success'=>true,'message'=>'Call logged']);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }
}
