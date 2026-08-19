<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\ProgressiveRegistrationService;
use App\Services\PayrollService;
use App\Services\ResellPropertyService;
use App\Services\CommissionService;
use App\Services\NotificationService;
use App\Services\SecurityService;
use App\Services\FinanceService;
use App\Services\AnalyticsService;
use App\Services\AgentOrchestrator;
use App\Services\OcrService;
use App\Services\PropertyMarketplaceService;
use \App\Traits\TenantAwareTrait;

class NewFeaturesApiController extends BaseController
{
    use TenantAwareTrait;
    public function __construct() { parent::__construct(); }

    protected function skipCsrfProtection(): bool { return true; }

    private function requireAuth(): int
    {
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }
        return $userId;
    }

    private function requireAdmin(): int
    {
        $userId = $this->requireAuth();
        $role = $GLOBALS['api_user_role'] ?? '';
        if (!in_array($role, ['admin', 'employee', 'superadmin'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Admin access required']);
            exit;
        }
        return $userId;
    }

    private function reg(): ProgressiveRegistrationService { return new ProgressiveRegistrationService($this->db); }
    private function pay(): PayrollService { return new PayrollService($this->db); }
    private function resell(): ResellPropertyService { return new ResellPropertyService($this->db); }
    private function comm(): CommissionService { return new CommissionService($this->db); }
    private function notif(): NotificationService { return new NotificationService($this->db); }
    private function sec(): SecurityService { return new SecurityService($this->db); }
    private function fin(): FinanceService { return new FinanceService($this->db); }
    private function analytics(): AnalyticsService { return new AnalyticsService($this->db); }
    private function agent(): AgentOrchestrator { return new AgentOrchestrator($this->db); }
    private function ocr(): OcrService { return new OcrService($this->db); }
    private function mkt(): PropertyMarketplaceService { return new PropertyMarketplaceService($this->db); }

    public function regStart()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = $this->reg()->startStep($data);
        return $this->jsonResponse($result);
    }

    public function regProgress($token)
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = $this->reg()->progress($token, (int)($data['step'] ?? 0), $data);
        return $this->jsonResponse($result);
    }

    public function regComplete($token)
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = $this->reg()->complete($token, $data);
        return $this->jsonResponse($result);
    }

    public function payrollGenerate()
    {
        $this->requireAdmin();
        $month = (int)($_POST['month'] ?? date('n'));
        $year = (int)($_POST['year'] ?? date('Y'));
        $result = $this->pay()->generatePayroll($month, $year);
        return $this->jsonResponse($result);
    }

    public function resellList()
    {
        $allowed = ['status', 'city', 'property_type', 'min_price', 'max_price', 'bedrooms', 'sort', 'order'];
        $filters = array_intersect_key($_GET, array_flip($allowed));
        $result = $this->resell()->listProperties($filters, min(100, max(1, (int)($_GET['limit'] ?? 50))));
        return $this->jsonResponse(['ok' => true, 'data' => $result]);
    }

    public function resellCreate()
    {
        $userId = $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $stringFields = ['title', 'description', 'location', 'amenities', 'property_type', 'facing', 'possession_status'];
        foreach ($stringFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = \App\Core\Security::sanitize($data[$field]);
            }
        }
        $floatFields = ['asking_price', 'area_sqft', 'plot_area', 'built_up_area'];
        foreach ($floatFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (float)$data[$field];
            }
        }
        $intFields = ['bedrooms', 'bathrooms', 'floors', 'year_built', 'age_of_property'];
        foreach ($intFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (int)$data[$field];
            }
        }
        $result = $this->resell()->createProperty($data);
        return $this->jsonResponse($result);
    }

    public function resellValuate($id)
    {
        $this->requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $value = (float)($data['estimated_value'] ?? 0);
        $source = $data['source'] ?? 'manual';
        $result = $this->resell()->recordValuation((int)$id, $value, $source, $data);
        return $this->jsonResponse($result);
    }

    public function commissionCalculate()
    {
        $this->requireAdmin();
        $agentId = (int)($_POST['agent_id'] ?? 0);
        $amount = (float)($_POST['sale_amount'] ?? 0);
        $tier = $_POST['tier'] ?? 'standard';
        $commission = $this->comm()->calculateAgentCommission($agentId, $amount, $tier);
        return $this->jsonResponse(['ok' => true, 'commission' => $commission, 'rate_pct' => ($commission / max(1, $amount)) * 100]);
    }

    public function commissionRecord()
    {
        $this->requireAdmin();
        $agentId = (int)($_POST['agent_id'] ?? 0);
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $amount = (float)($_POST['sale_amount'] ?? 0);
        $tier = $_POST['tier'] ?? 'standard';
        $result = $this->comm()->recordAgentCommission($agentId, $bookingId, $amount, $tier);
        return $this->jsonResponse($result);
    }

    public function mlmRanks()
    {
        $result = $this->comm()->getMlmRankRates($_GET['rank'] ?? '');
        return $this->jsonResponse(['ok' => true, 'data' => $result]);
    }

    public function sendNotification()
    {
        $this->requireAdmin();
        $userId = (int)($_POST['user_id'] ?? 0);
        $channel = \App\Core\Security::sanitize($_POST['channel'] ?? 'email');
        $subject = \App\Core\Security::sanitize($_POST['subject'] ?? '');
        $message = \App\Core\Security::sanitize($_POST['message'] ?? '');
        $data = $_POST;
        unset($data['user_id'], $data['channel'], $data['subject'], $data['message']);
        // Sanitize remaining data values
        $data = array_map(function($v) { return is_string($v) ? \App\Core\Security::sanitize($v) : $v; }, $data);
        $result = $this->notif()->send($userId, $channel, $subject, $message, $data);
        return $this->jsonResponse($result);
    }

    public function renderTemplate()
    {
        $this->requireAdmin();
        $code = \App\Core\Security::sanitize($_POST['template_code'] ?? '');
        $vars = $_POST;
        unset($vars['template_code']);
        // Sanitize all template variables to prevent XSS
        $vars = array_map(function($v) { return is_string($v) ? \App\Core\Security::sanitize($v) : $v; }, $vars);
        $result = $this->notif()->render($code, $vars);
        return $this->jsonResponse($result);
    }

    public function generate2fa()
    {
        $this->requireAdmin();
        $userId = (int)($_POST['user_id'] ?? 0);
        $result = $this->sec()->generate2FAToken($userId);
        return $this->jsonResponse($result);
    }

    public function verify2fa()
    {
        $this->requireAuth();
        $userId = (int)($_POST['user_id'] ?? 0);
        $code = $_POST['code'] ?? '';
        $result = $this->sec()->verify2FA($userId, $code);
        return $this->jsonResponse($result);
    }

    public function passwordReset()
    {
        $this->requireAdmin();
        $userId = (int)($_POST['user_id'] ?? 0);
        $result = $this->sec()->generatePasswordReset($userId);
        return $this->jsonResponse($result);
    }

    public function passwordResetConfirm()
    {
        $token = $_POST['token'] ?? '';
        $newPassword = $_POST['password'] ?? '';
        if (strlen($token) < 10 || strlen($token) > 255) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid reset token'], 400);
        }
        if (strlen($newPassword) < 8) {
            return $this->jsonResponse(['success' => false, 'error' => 'Password must be at least 8 characters'], 400);
        }
        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Password must contain uppercase, lowercase, and a number'], 400);
        }
        $result = $this->sec()->usePasswordReset($token, $newPassword);
        return $this->jsonResponse($result);
    }

    public function blockIp()
    {
        $this->requireAdmin();
        $ip = $_POST['ip'] ?? '';
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid IP address'], 400);
        }
        $duration = max(1, min(1440, (int)($_POST['duration'] ?? 60)));
        $reason = \App\Core\Security::sanitize($_POST['reason'] ?? 'manual');
        $result = $this->sec()->blockIp($ip, 'manual', $reason, $duration);
        return $this->jsonResponse($result);
    }

    public function unblockIp()
    {
        $this->requireAdmin();
        $ip = $_POST['ip'] ?? '';
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid IP address'], 400);
        }
        $result = $this->sec()->unblockIp($ip);
        return $this->jsonResponse($result);
    }

    public function calculateGst()
    {
        $amount = (float)($_POST['amount'] ?? 0);
        $interstate = (bool)($_POST['interstate'] ?? false);
        $state = $_POST['state_code'] ?? '';
        $result = $this->fin()->calculateGst($amount, $interstate, $state);
        return $this->jsonResponse($result);
    }

    public function calculateTax()
    {
        $type = $_POST['tax_type'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);
        $state = $_POST['state_code'] ?? '';
        $tax = $this->fin()->calculateTax($type, $amount, $state);
        return $this->jsonResponse(['ok' => true, 'tax' => $tax]);
    }

    public function createBudget()
    {
        $this->requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = $this->fin()->createBudget(
            (int)($data['department_id'] ?? 0),
            $data['category'] ?? '',
            (float)($data['allocated_amount'] ?? 0),
            $data['period'] ?? 'monthly',
            (int)($data['fiscal_year'] ?? date('Y'))
        );
        return $this->jsonResponse($result);
    }

    public function recordKpi()
    {
        $this->requireAdmin();
        $kpiId = (int)($_POST['kpi_id'] ?? 0);
        $actual = (float)($_POST['actual'] ?? 0);
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $period = $_POST['period'] ?? null;
        $result = $this->analytics()->recordKpi($kpiId, $actual, $employeeId ?: null, $period);
        return $this->jsonResponse($result);
    }

    public function generateForecast()
    {
        $metric = $_POST['metric'] ?? 'revenue';
        $periods = (int)($_POST['periods'] ?? 6);
        $result = $this->analytics()->generateForecast($metric, $periods);
        return $this->jsonResponse($result);
    }

    public function createTask()
    {
        $this->requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = $this->agent()->createTask(
            (int)($data['agent_id'] ?? 0),
            $data['task_type'] ?? 'lead_score',
            $data['payload'] ?? [],
            (int)($data['priority'] ?? 5)
        );
        return $this->jsonResponse($result);
    }

    public function executeTask($id)
    {
        $result = $this->agent()->executeTask((int)$id);
        return $this->jsonResponse($result);
    }

    public function processPendingTasks()
    {
        $this->requireAdmin();
        $result = $this->agent()->processPendingTasks((int)($_POST['max'] ?? 50));
        return $this->jsonResponse($result);
    }

    public function triggerWorkflow()
    {
        $this->requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $trigger = $data['trigger'] ?? '';
        $context = $data['context'] ?? [];
        $result = $this->agent()->triggerWorkflow($trigger, $context);
        return $this->jsonResponse($result);
    }

    public function classifyDocument()
    {
        $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $docId = (int)($data['document_id'] ?? 0);
        $fileName = $data['file_name'] ?? '';
        $content = $data['content'] ?? '';
        $result = $this->ocr()->autoClassify($docId, $fileName, $content);
        return $this->jsonResponse($result);
    }

    public function executeReport()
    {
        $this->requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $reportType = $data['report_type'] ?? 'leads';
        $result = $this->generateReportData($reportType, $data);
        return $this->jsonResponse($result);
    }

    private function generateReportData(string $type, array $params): array
    {
        $tid = (int)$this->tenantId();
        $tidFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $tidParam = $tid > 1 ? [$tid] : [];

        switch ($type) {
            case 'leads':
                $st = $this->db->query(
                    "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as won FROM leads WHERE 1=1{$tidFilter}",
                    $tid > 1 ? [$tid] : []
                );
                $row = $st->fetch(\PDO::FETCH_ASSOC);
                return ['ok' => true, 'type' => 'leads', 'total' => (int)($row['total'] ?? 0), 'won' => (int)($row['won'] ?? 0)];
            case 'bookings':
                $st = $this->db->query(
                    "SELECT COUNT(*) as total, COALESCE(SUM(total_amount), 0) as revenue FROM bookings WHERE 1=1{$tidFilter}",
                    $tid > 1 ? [$tid] : []
                );
                $row = $st->fetch(\PDO::FETCH_ASSOC);
                return ['ok' => true, 'type' => 'bookings', 'total' => (int)($row['total'] ?? 0), 'revenue' => (float)($row['revenue'] ?? 0)];
            case 'payments':
                $st = $this->db->query(
                    "SELECT COUNT(*) as total, COALESCE(SUM(amount), 0) as collected FROM payments WHERE status = 'completed'{$tidFilter}",
                    $tid > 1 ? [$tid] : []
                );
                $row = $st->fetch(\PDO::FETCH_ASSOC);
                return ['ok' => true, 'type' => 'payments', 'total' => (int)($row['total'] ?? 0), 'collected' => (float)($row['collected'] ?? 0)];
            default:
                return ['ok' => false, 'error' => 'Unknown report type'];
        }
    }

    public function scheduleMaintenance()
    {
        $this->requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = $this->mkt()->scheduleMaintenance(
            (int)($data['property_id'] ?? 0),
            $data['type'] ?? 'general',
            $data['description'] ?? '',
            $data['scheduled_date'] ?? date('Y-m-d'),
            (float)($data['estimated_cost'] ?? 0),
            (int)($data['assigned_to'] ?? 0)
        );
        return $this->jsonResponse($result);
    }

    public function resellListPublic()
    {
        $allowed = ['status', 'city', 'property_type', 'min_price', 'max_price', 'bedrooms', 'sort', 'order'];
        $filters = array_intersect_key($_GET, array_flip($allowed));
        $properties = $this->resell()->listProperties($filters, min(50, max(1, (int)($_GET['limit'] ?? 20))));
        return $this->jsonResponse(['ok' => true, 'data' => $properties]);
    }

    public function analyticsDashboard()
    {
        $data = [];

        try {
            $st = $this->db->query("SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $data['leads_30d'] = (int)$st->fetchColumn();
        } catch (\Throwable $e) { $data['leads_30d'] = 0; }

        try {
            $st = $this->db->query("SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $prevLeads = (int)$st->fetchColumn();
            $data['leads_delta'] = $prevLeads > 0 ? round((($data['leads_30d'] - $prevLeads) / $prevLeads) * 100, 1) : 0;
        } catch (\Throwable $e) { $data['leads_delta'] = 0; }

        try {
            $st = $this->db->query("SELECT COUNT(*) FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $data['bookings_30d'] = (int)$st->fetchColumn();
        } catch (\Throwable $e) { $data['bookings_30d'] = 0; }

        try {
            $st = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $data['revenue_30d'] = (float)$st->fetchColumn();
        } catch (\Throwable $e) { $data['revenue_30d'] = 0; }

        try {
            $tid = (int)$this->tenantId();
            $tidFilter = $tid > 1 ? " AND tenant_id = $tid" : '';
            $st = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'{$tidFilter}");
            $data['total_customers'] = (int)$st->fetchColumn();
        } catch (\Throwable $e) { $data['total_customers'] = 0; }

        try {
            $st = $this->db->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date");
            $data['leads_by_day'] = $st->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { $data['leads_by_day'] = []; }

        try {
            $st = $this->db->query("SELECT source, COUNT(*) as count FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY source");
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
            $data['leads_by_source'] = [];
            foreach ($rows as $r) { $data['leads_by_source'][$r['source'] ?: 'unknown'] = (int)$r['count']; }
        } catch (\Throwable $e) { $data['leads_by_source'] = []; }

        try {
            $st = $this->db->query("SELECT status, COUNT(*) as count FROM leads GROUP BY status");
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
            $data['pipeline'] = [];
            foreach ($rows as $r) { $data['pipeline'][$r['status'] ?: 'new'] = (int)$r['count']; }
        } catch (\Throwable $e) { $data['pipeline'] = []; }

        try {
            $st = $this->db->query("SELECT type, COUNT(*) as count FROM plots GROUP BY type");
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
            $data['property_types'] = [];
            foreach ($rows as $r) { $data['property_types'][$r['type'] ?: 'unknown'] = (int)$r['count']; }
        } catch (\Throwable $e) { $data['property_types'] = []; }

        return $this->jsonResponse($data);
    }

    public function analyticsInsights()
    {
        $insights = [];

        try {
            $st = $this->db->query("SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $weekLeads = (int)$st->fetchColumn();
            $st = $this->db->query("SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $prevWeek = (int)$st->fetchColumn();
            if ($weekLeads > $prevWeek * 1.2) {
                $insights[] = ['type' => 'success', 'title' => 'Lead Surge', 'message' => "Lead volume up " . round((($weekLeads - $prevWeek) / max(1, $prevWeek)) * 100, 1) . "% this week vs last"];
            } elseif ($weekLeads < $prevWeek * 0.8) {
                $insights[] = ['type' => 'warning', 'title' => 'Lead Decline', 'message' => "Lead volume down " . round((($prevWeek - $weekLeads) / max(1, $prevWeek)) * 100, 1) . "% this week vs last"];
            }
        } catch (\Throwable $e) { error_log('NewFeaturesApiController::analyticsInsights error: ' . $e->getMessage()); }

        try {
            $st = $this->db->query("SELECT type, COUNT(*) as cnt FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY type ORDER BY cnt DESC LIMIT 1");
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $insights[] = ['type' => 'info', 'title' => 'Top Property Type', 'message' => "Most inquiries for " . ($row['type'] ?: 'general') . " ({$row['cnt']} leads)"];
            }
        } catch (\Throwable $e) { error_log('NewFeaturesApiController::analyticsInsights error: ' . $e->getMessage()); }

        try {
            $st = $this->db->query("SELECT id, amount FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY amount DESC LIMIT 1");
            $top = $st->fetch(\PDO::FETCH_ASSOC);
            if ($top) {
                $insights[] = ['type' => 'primary', 'title' => 'Top Booking', 'message' => "Highest booking this month: ₹" . number_format($top['amount'])];
            }
        } catch (\Throwable $e) { error_log('NewFeaturesApiController::analyticsInsights error: ' . $e->getMessage()); }

        try {
            $tid = (int)$this->tenantId();
            $tidFilter = $tid > 1 ? " AND tenant_id = $tid" : '';
            $st = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY){$tidFilter}");
            $newUsers = (int)$st->fetchColumn();
            if ($newUsers > 0) {
                $insights[] = ['type' => 'success', 'title' => 'New Customers', 'message' => "{$newUsers} new customers this week"];
            }
        } catch (\Throwable $e) { error_log('NewFeaturesApiController::analyticsInsights error: ' . $e->getMessage()); }

        if (empty($insights)) {
            $insights[] = ['type' => 'secondary', 'title' => 'No Insights', 'message' => 'Need more data to generate insights'];
        }

        return $this->jsonResponse(['insights' => $insights]);
    }

    public function calculateEmi()
    {
        $principal = (float)($_POST['principal'] ?? $_GET['principal'] ?? 0);
        $rate = (float)($_POST['rate'] ?? $_GET['rate'] ?? 0);
        $years = (int)($_POST['years'] ?? $_GET['years'] ?? 0);
        
        $emiService = new \App\Services\Finance\EMICalculatorService();
        $result = $emiService->calculateEMI($principal, $rate, $years);
        
        return $this->jsonResponse($result);
    }
}
