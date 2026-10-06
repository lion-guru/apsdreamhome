<?php
/**
 * FnfSettlementController
 * Admin controller for Full & Final Settlement management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Traits\TenantAwareTrait;

class FnfSettlementController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    /**
     * Settlement Calculator - Preview
     */
    public function calculator()
    {
        $this->requireAdmin();
        
        $employeeId = (int)($_GET['employee_id'] ?? 0);
        $settlement = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $params = [
                'last_working_day' => $_POST['last_working_day'] ?? date('Y-m-d'),
                'resignation_date' => $_POST['resignation_date'] ?? date('Y-m-d'),
                'notice_period_days' => (int)($_POST['notice_period_days'] ?? 30),
                'notice_served_days' => (int)($_POST['notice_served_days'] ?? 0),
                'exit_type' => $_POST['exit_type'] ?? 'resignation',
            ];
            
            try {
                $service = new \App\Services\FullAndFinalSettlementService();
                $result = $service->calculateSettlement($employeeId, $params);
                if ($result['success']) {
                    $settlement = $result['settlement'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }

        // Get employees for dropdown
        $employees = $this->db->fetchAll("
            SELECT e.id, e.employee_code, u.name, e.designation, e.department, e.joining_date
            FROM employees e
            JOIN users u ON e.user_id = u.id
            WHERE e.status='active' AND e.tenant_id=?
            ORDER BY u.name
        ", [$this->tenantId()]);

        return $this->render('admin/fnf/calculator', [
            'page_title' => 'Full & Final Settlement Calculator',
            'employees' => $employees,
            'selected_employee' => $employeeId,
            'settlement' => $settlement,
        ]);
    }

    /**
     * Process Settlement
     */
    public function process()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/fnf/calculator');
            exit;
        }

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $params = [
            'last_working_day' => $_POST['last_working_day'] ?? date('Y-m-d'),
            'resignation_date' => $_POST['resignation_date'] ?? date('Y-m-d'),
            'notice_period_days' => (int)($_POST['notice_period_days'] ?? 30),
            'notice_served_days' => (int)($_POST['notice_served_days'] ?? 0),
            'exit_type' => $_POST['exit_type'] ?? 'resignation',
        ];

        try {
            $service = new \App\Services\FullAndFinalSettlementService();
            $result = $service->processSettlement($employeeId, $params);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'] . " | Net Payable: ₹" . number_format($result['net_payable'], 2) . " | Ref: " . $result['settlement_no'];
            } else {
                $_SESSION['error'] = $result['message'];
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect(BASE_URL . '/admin/fnf/calculator?employee_id=' . $employeeId);
        exit;
    }

    /**
     * Settlement History
     */
    public function history()
    {
        $this->requireAdmin();
        
        $employeeId = (int)($_GET['employee_id'] ?? 0);
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE s.tenant_id=?";
        $params = [$this->tenantId()];
        
        if ($employeeId) {
            $where .= " AND s.employee_id=?";
            $params[] = $employeeId;
        }
        if ($status) {
            $where .= " AND s.status=?";
            $params[] = $status;
        }

        $total = $this->db->fetch("SELECT COUNT(*) as c FROM employee_fnf_settlements s $where", $params)['c'] ?? 0;
        $settlements = $this->db->fetchAll("
            SELECT s.*, u.name as employee_name, e.employee_code
            FROM employee_fnf_settlements s
            JOIN employees e ON s.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            $where
            ORDER BY s.created_at DESC
            LIMIT $perPage OFFSET $offset
        ", $params);

        $totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
        $employees = $this->db->fetchAll("SELECT e.id, e.employee_code, u.name FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' AND e.tenant_id=? ORDER BY u.name", [$this->tenantId()]);

        return $this->render('admin/fnf/history', [
            'page_title' => 'F&F Settlement History',
            'settlements' => $settlements,
            'employees' => $employees,
            'employee_id' => $employeeId,
            'status' => $status,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    /**
     * View Settlement Detail
     */
    public function viewSettlement($id)
    {
        $this->requireAdmin();
        
        $settlement = $this->db->fetch("
            SELECT s.*, u.name as employee_name, u.email, u.phone, e.employee_code, e.designation, e.department, e.joining_date
            FROM employee_fnf_settlements s
            JOIN employees e ON s.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE s.id=? AND s.tenant_id=?
        ", [$id, $this->tenantId()]);

        if (!$settlement) {
            $_SESSION['error'] = 'Settlement not found';
            $this->redirect(BASE_URL . '/admin/fnf/history');
            exit;
        }

        $components = json_decode($settlement['settlement_details'] ?? '[]', true);
        
        return $this->render('admin/fnf/detail', [
            'page_title' => 'Settlement: ' . $settlement['settlement_no'],
            'settlement' => $settlement,
            'components' => $components,
        ]);
    }

    /**
     * Approve Settlement
     */
    public function approve($id)
    {
        $this->requireAdmin();
        
        try {
            $this->db->execute("
                UPDATE employee_fnf_settlements 
                SET status='approved', approved_by=?, approved_at=NOW() 
                WHERE id=? AND tenant_id=?
            ", [$_SESSION['admin_id'] ?? 0, $id, $this->tenantId()]);
            
            $_SESSION['success'] = 'Settlement approved';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect(BASE_URL . "/admin/fnf/view/$id");
        exit;
    }

    /**
     * Mark as Paid
     */
    public function markPaid($id)
    {
        $this->requireAdmin();
        $ref = $_POST['payment_reference'] ?? 'PAID-' . date('YmdHis');
        
        try {
            $this->db->execute("
                UPDATE employee_fnf_settlements 
                SET status='paid', paid_at=NOW(), payment_reference=? 
                WHERE id=? AND tenant_id=?
            ", [$ref, $id, $this->tenantId()]);
            
            // Also update related salary_payment
            $this->db->execute("
                UPDATE salary_payments 
                SET payment_status='paid', payment_processed_at=NOW(), payment_processed_by=?
                WHERE payment_type='fnf_settlement' AND remarks LIKE ? AND tenant_id=?
            ", [$_SESSION['admin_id'] ?? 0, '%' . $id . '%', $this->tenantId()]);
            
            $_SESSION['success'] = 'Settlement marked as paid';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect(BASE_URL . "/admin/fnf/view/$id");
        exit;
    }

    /**
     * Asset Management
     */
    public function assets()
    {
        $this->requireAdmin();
        
        // Assign form POSTs employee_id; filter links pass it via GET.
        $employeeId = (int)($_POST['employee_id'] ?? $_GET['employee_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $assetId = (int)($_POST['asset_id'] ?? 0);
            
            if ($action === 'assign') {
                $this->db->execute("
                    INSERT INTO employee_assets (employee_id, asset_name, asset_category, serial_number, purchase_value, current_value, assigned_date, tenant_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ", [
                    $employeeId, $_POST['asset_name'], $_POST['asset_category'],
                    $_POST['serial_number'], $_POST['purchase_value'], $_POST['current_value'],
                    $_POST['assigned_date'], $this->tenantId()
                ]);
                $_SESSION['success'] = 'Asset assigned';
            } elseif ($action === 'return') {
                $this->db->execute("
                    UPDATE employee_assets 
                    SET returned_at=NOW(), condition_on_return=?, remarks=?
                    WHERE id=? AND tenant_id=?
                ", [$_POST['condition'] ?? 'good', $_POST['remarks'] ?? '', $assetId, $this->tenantId()]);
                $_SESSION['success'] = 'Asset returned';
            }
            $this->redirect(BASE_URL . "/admin/fnf/assets?employee_id=$employeeId");
            exit;
        }

        $where = "WHERE a.tenant_id=?";
        $params = [$this->tenantId()];
        if ($employeeId) {
            $where .= " AND a.employee_id=?";
            $params[] = $employeeId;
        }

        $assets = $this->db->fetchAll("
            SELECT a.*, u.name as employee_name, e.employee_code
            FROM employee_assets a
            JOIN employees e ON a.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            $where
            ORDER BY a.assigned_date DESC
        ", $params);

        $employees = $this->db->fetchAll("SELECT e.id, e.employee_code, u.name FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' AND e.tenant_id=? ORDER BY u.name", [$this->tenantId()]);

        return $this->render('admin/fnf/assets', [
            'page_title' => 'Employee Asset Management',
            'assets' => $assets,
            'employees' => $employees,
            'employee_id' => $employeeId,
        ]);
    }
}