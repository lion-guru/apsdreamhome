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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        $stmt = $this->db->prepare("SELECT id, punch_in_time, punch_out_time, punch_in_location, date FROM attendance WHERE user_id = ? ORDER BY date DESC LIMIT 30");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $this->db->prepare("SELECT c.id, c.amount, c.status, c.created_at, u.name as customer_name FROM commissions c LEFT JOIN users u ON c.source_user_id = u.id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function processCommissionAction($commissionId, $action)
    {
        if ($action === 'release') {
            $stmt = $this->db->prepare("UPDATE commissions SET status = 'released' WHERE id = ?");
            $stmt->execute([$commissionId]);
            return true;
        } elseif ($action === 'hold') {
            $stmt = $this->db->prepare("UPDATE commissions SET status = 'on_hold' WHERE id = ?");
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }
}
