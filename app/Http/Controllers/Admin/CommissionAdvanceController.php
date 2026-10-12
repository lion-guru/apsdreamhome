<?php

namespace App\Http\Controllers\Admin;

use App\Services\CommissionAdvanceService;
use App\Core\Database\Database;

/**
 * Admin Commission Advances & Loan Management Desk
 */
class CommissionAdvanceController extends AdminController
{
    private CommissionAdvanceService $advanceService;

    public function __construct()
    {
        parent::__construct();
        $this->advanceService = new CommissionAdvanceService();
    }

    public function index()
    {
        $this->requireAdmin();
        $status = trim((string)($_GET['status'] ?? 'all'));
        $advances = $this->advanceService->getAllAdvances($status);

        $pdo = Database::getInstance()->getConnection();
        $stats = [
            'total_disbursed' => (float)$pdo->query("SELECT COALESCE(SUM(approved_amount),0) FROM commission_advances WHERE status IN ('disbursed', 'repaid')")->fetchColumn(),
            'total_recovered' => (float)$pdo->query("SELECT COALESCE(SUM(recovered_amount),0) FROM commission_advances")->fetchColumn(),
            'pending_review' => (int)$pdo->query("SELECT COUNT(*) FROM commission_advances WHERE status = 'pending'")->fetchColumn(),
        ];
        $stats['outstanding'] = max(0, $stats['total_disbursed'] - $stats['total_recovered']);

        $this->render('admin/finance/commission_advances', [
            'page_title' => 'Commission Advances & Loans',
            'advances' => $advances,
            'stats' => $stats,
            'current_status' => $status,
        ]);
    }

    public function approve($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $approvedAmount = (float)($_POST['approved_amount'] ?? 0);
        $deductionPct = (float)($_POST['deduction_pct'] ?? 25.0);
        $remarks = trim((string)($_POST['admin_remarks'] ?? ''));
        $adminId = (int)($_SESSION['user_id'] ?? 1);

        $res = $this->advanceService->approveAdvance((int)$id, $adminId, $approvedAmount, $deductionPct, $remarks);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect('/admin/commission-advances');
    }

    public function disburse($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $disbursementRef = trim((string)($_POST['disbursement_ref'] ?? ''));
        $adminId = (int)($_SESSION['user_id'] ?? 1);

        $res = $this->advanceService->disburseAdvance((int)$id, $adminId, $disbursementRef);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect('/admin/commission-advances');
    }

    public function reject($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $remarks = trim((string)($_POST['admin_remarks'] ?? ''));
        $adminId = (int)($_SESSION['user_id'] ?? 1);

        $res = $this->advanceService->rejectAdvance((int)$id, $adminId, $remarks);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect('/admin/commission-advances');
    }
}
