<?php

namespace App\Http\Controllers\Admin;

use App\Services\DeleteApprovalService;

/**
 * Super-admin delete approval inbox.
 * GET  /admin/delete-approvals          → pending queue
 * POST /admin/delete-approvals/{id}/approve → execute delete
 * POST /admin/delete-approvals/{id}/reject  → drop request
 */
class DeleteApprovalController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        $svc = new DeleteApprovalService();
        $this->render('admin/delete-approvals/index', [
            'page_title' => 'Delete Approvals',
            'approvals' => $svc->pendingList(),
            'is_super' => DeleteApprovalService::isSuperAdmin(),
        ]);
    }

    public function approve($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $svc = new DeleteApprovalService();
        $res = $svc->approve((int)$id, (string)($_POST['review_notes'] ?? ''));
        $this->setFlash(!empty($res['success']) ? 'success' : 'error',
            !empty($res['success']) ? 'Delete approved and executed.' : ($res['error'] ?? 'Approve failed'));
        return $this->redirect('/admin/delete-approvals');
    }

    public function reject($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $svc = new DeleteApprovalService();
        $res = $svc->reject((int)$id, (string)($_POST['review_notes'] ?? ''));
        $this->setFlash(!empty($res['success']) ? 'success' : 'error',
            !empty($res['success']) ? 'Delete request rejected.' : ($res['error'] ?? 'Reject failed'));
        return $this->redirect('/admin/delete-approvals');
    }
}
