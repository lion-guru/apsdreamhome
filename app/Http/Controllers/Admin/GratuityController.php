<?php
/**
 * GratuityController
 * Admin controller for Gratuity calculations and liability reports
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Traits\TenantAwareTrait;

class GratuityController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    /**
     * Gratuity Calculator - Single Employee
     */
    public function calculator()
    {
        $this->requireAdmin();
        
        $employeeId = (int)($_GET['employee_id'] ?? 0);
        $calculationDate = $_GET['calculation_date'] ?? date('Y-m-d');
        $result = null;

        if ($employeeId) {
            try {
                $service = new \App\Services\GratuityService();
                $result = $service->calculateGratuity($employeeId, $calculationDate);
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

        return $this->render('admin/gratuity/calculator', [
            'page_title' => 'Gratuity Calculator',
            'employees' => $employees,
            'selected_employee' => $employeeId,
            'calculation_date' => $calculationDate,
            'result' => $result,
        ]);
    }

    /**
     * Gratuity Eligibility Report - All Employees
     */
    public function eligibilityReport()
    {
        $this->requireAdmin();

        try {
            $service = new \App\Services\GratuityService();
            $report = $service->getLiabilityReport();
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $report = ['total_employees' => 0, 'eligible_employees' => 0, 'total_liability' => 0, 'details' => []];
        }

        return $this->render('admin/gratuity/eligibility_report', [
            'page_title' => 'Gratuity Eligibility & Liability Report',
            'report' => $report,
        ]);
    }

    /**
     * Individual Employee Gratuity Detail
     */
    public function detail($id)
    {
        $this->requireAdmin();

        try {
            $service = new \App\Services\GratuityService();
            $result = $service->calculateGratuity($id, date('Y-m-d'));
            
            if (!$result['success']) {
                $_SESSION['error'] = $result['message'];
                $this->redirect(BASE_URL . '/admin/gratuity/calculator');
                exit;
            }

            $employee = $this->db->fetch("
                SELECT e.*, u.name, u.email, u.phone, u.pan_number, e.employee_code
                FROM employees e
                JOIN users u ON e.user_id = u.id
                WHERE e.id=?
            ", [$id]);

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect(BASE_URL . '/admin/gratuity/calculator');
            exit;
        }

        return $this->render('admin/gratuity/detail', [
            'page_title' => 'Gratuity Detail - ' . ($employee['name'] ?? 'Employee'),
            'result' => $result,
            'employee' => $employee,
        ]);
    }
}