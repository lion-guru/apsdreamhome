<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\PfEsiService;
use Exception;

class PfEsiController extends AdminController
{
    protected $pfEsiService;

    public function __construct()
    {
        parent::__construct();
        $this->pfEsiService = new PfEsiService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $monthYear = $_GET['month_year'] ?? date('Y-m');
            $config = $this->pfEsiService->getConfig();
            $stats = $this->pfEsiService->getStats($monthYear);
            $monthYears = $this->pfEsiService->getMonthYears();
            return $this->render('admin/pf_esi/index', [
                'page_title' => 'PF/ESI Compliance - APS Dream Home',
                'active_page' => 'pf_esi',
                'config' => $config,
                'stats' => $stats,
                'month_year' => $monthYear,
                'month_years' => $monthYears,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load PF/ESI: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function register()
    {
        $this->requireAdmin();
        try {
            $monthYear = $_GET['month_year'] ?? date('Y-m');
            $status = $_GET['status'] ?? '';
            $deductions = $this->pfEsiService->getDeductions($monthYear, $status ?: null);
            $monthYears = $this->pfEsiService->getMonthYears();
            return $this->render('admin/pf_esi/register', [
                'page_title' => 'PF/ESI Register - APS Dream Home',
                'active_page' => 'pf_esi',
                'deductions' => $deductions,
                'month_year' => $monthYear,
                'month_years' => $monthYears,
                'status' => $status,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load register: ' . $e->getMessage());
            return $this->redirect('/admin/pf-esi');
        }
    }

    public function process()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $monthYear = $_POST['month_year'] ?? date('Y-m');
            if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) return $this->jsonError('Invalid month format (YYYY-MM)', 400);
            $count = $this->pfEsiService->processMonth($monthYear, $_SESSION['user_id'] ?? 1);
            return $this->jsonResponse(['success' => true, 'message' => "Processed $count employees for $monthYear"]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function ecr()
    {
        $this->requireAdmin();
        try {
            $monthYear = $_GET['month_year'] ?? date('Y-m');
            if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
                $monthYear = date('Y-m');
            }
            $ecr = $this->pfEsiService->generateECR($monthYear);
            $monthYears = $this->pfEsiService->getMonthYears();
            return $this->render('admin/pf_esi/ecr', [
                'page_title' => 'ECR Filing - APS Dream Home',
                'active_page' => 'pf_esi',
                'ecr' => $ecr,
                'month_year' => $monthYear,
                'month_years' => $monthYears,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to generate ECR: ' . $e->getMessage());
            return $this->redirect('/admin/pf-esi');
        }
    }

    public function ecrDownload()
    {
        $this->requireAdmin();
        try {
            $monthYear = $_GET['month_year'] ?? date('Y-m');
            if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
                $monthYear = date('Y-m');
            }
            $ecr = $this->pfEsiService->generateECR($monthYear);
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="ECR_' . $monthYear . '.txt"');
            header('Content-Length: ' . strlen($ecr['content']));
            echo $ecr['content'];
            exit;
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to download ECR: ' . $e->getMessage());
            return $this->redirect('/admin/pf-esi');
        }
    }

    public function challan()
    {
        $this->requireAdmin();
        try {
            $monthYear = $_GET['month_year'] ?? date('Y-m');
            if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
                $monthYear = date('Y-m');
            }
            $data = $this->pfEsiService->getChallanData($monthYear);
            $monthYears = $this->pfEsiService->getMonthYears();
            return $this->render('admin/pf_esi/challan', [
                'page_title' => 'PF/ESI Challan - APS Dream Home',
                'active_page' => 'pf_esi',
                'data' => $data,
                'month_year' => $monthYear,
                'month_years' => $monthYears,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load challan: ' . $e->getMessage());
            return $this->redirect('/admin/pf-esi');
        }
    }

    public function ledger()
    {
        $this->requireAdmin();
        try {
            $employeeId = (int)($_GET['employee_id'] ?? 0);
            $data = $this->pfEsiService->getLedger($employeeId);
            $employees = $this->db->fetchAll(
                "SELECT id, name, employee_code, department FROM employees WHERE status = 'active'" . ($this->tenantId() > 1 ? " AND tenant_id = ?" : ""),
                $this->tenantId() > 1 ? [$this->tenantId()] : []
            ) ?: [];
            return $this->render('admin/pf_esi/ledger', [
                'page_title' => 'PF Ledger - APS Dream Home',
                'active_page' => 'pf_esi',
                'data' => $data,
                'employees' => $employees,
                'employee_id' => $employeeId,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load ledger: ' . $e->getMessage());
            return $this->redirect('/admin/pf-esi');
        }
    }

    public function markPaid()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $ids = $_POST['ids'] ?? [];
            $paymentRef = $_POST['payment_ref'] ?? ('PAY-' . date('YmdHis'));
            if (empty($ids)) return $this->jsonError('No records selected', 400);
            $count = $this->pfEsiService->markPaid($ids, $paymentRef, $_SESSION['user_id'] ?? 1);
            return $this->jsonResponse(['success' => true, 'message' => "Marked $count records as paid"]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }
}