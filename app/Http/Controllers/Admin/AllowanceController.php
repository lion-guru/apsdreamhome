<?php

namespace App\Http\Controllers\Admin;

use App\Services\MLM\LeadershipExpenseFundService;
use Exception;

/**
 * Admin Allowance Controller
 * Review, approve, and disburse Office Support & Site Visit Allowance claims.
 */
class AllowanceController extends AdminController
{
    private LeadershipExpenseFundService $fundService;

    public function __construct()
    {
        parent::__construct();
        $this->fundService = new LeadershipExpenseFundService();
    }

    public function index()
    {
        $this->requireAdmin();
        $typeFilter = !empty($_GET['type']) ? (string)$_GET['type'] : null;
        $claims = $this->fundService->getPendingAllowances($typeFilter);

        $this->render('admin/mlm-realestate/allowances', [
            'page_title'   => 'Leadership & Site Visit Allowances — Admin',
            'page_heading' => 'Leadership Expense Fund & Site Visit Claims',
            'claims'       => $claims,
            'current_type' => $typeFilter,
        ]);
    }

    public function approve($id)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int)$id;
        $adminId = (int)($this->getAdminId() ?: 1);

        $res = $this->fundService->approveAllowance($id, $adminId);
        if (!empty($res['success'])) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('error', $res['message']);
        }
        return $this->redirect('/admin/mlm-realestate/allowances');
    }

    public function reject($id)
    {
        $this->requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int)$id;
        $reason = trim($_POST['reason'] ?? 'Rejected by Administrator');
        $adminId = (int)($this->getAdminId() ?: 1);

        $res = $this->fundService->rejectAllowance($id, $reason, $adminId);
        if (!empty($res['success'])) {
            $this->setFlash('success', 'Claim rejected.');
        } else {
            $this->setFlash('error', $res['message']);
        }
        return $this->redirect('/admin/mlm-realestate/allowances');
    }
}
