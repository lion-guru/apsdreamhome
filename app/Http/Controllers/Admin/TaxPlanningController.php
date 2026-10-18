<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\TaxPlanningService;
use Exception;

class TaxPlanningController extends AdminController
{
    protected $taxService;

    public function __construct()
    {
        parent::__construct();
        $this->taxService = new TaxPlanningService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $fy = $_GET['fy'] ?? $this->taxService->currentFinancialYear();
            $summary = $this->taxService->getProfitSummary($fy);
            $schedule = $this->taxService->getSchedule($fy);
            $stats = $this->taxService->getStats($fy);
            $tds = $this->taxService->getTdsSummary($fy);
            return $this->render('admin/tax_planning/index', [
                'page_title' => 'Tax Planning - APS Dream Home',
                'active_page' => 'tax_planning',
                'fy' => $fy,
                'summary' => $summary,
                'schedule' => $schedule,
                'stats' => $stats,
                'tds' => $tds,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function generate()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $fy = $_POST['financial_year'] ?? $this->taxService->currentFinancialYear();
            $result = $this->taxService->generateSchedule($fy);
            return $this->jsonResponse(['success' => true, 'message' => 'Schedule generated', 'created' => $result['created'], 'redirect' => BASE_URL . '/admin/tax-planning?fy=' . urlencode($fy)]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function recordPayment($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $amount = (float)($_POST['amount'] ?? 0);
            $challan = trim($_POST['challan_ref'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            $ok = $this->taxService->recordPayment((int)$id, $amount, $challan, $notes);
            if (!$ok) return $this->jsonError('Invalid payment details', 400);
            return $this->jsonResponse(['success' => true, 'message' => 'Payment recorded', 'redirect' => BASE_URL . '/admin/tax-planning']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }
}
