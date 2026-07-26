<?php
/**
 * Assignment Approval Controller
 * Phase 6: Lead assignment approval workflow
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\AssignmentApprovalService;

class AssignmentApprovalController extends AdminController
{
    private $approval;

    public function __construct() {
        parent::__construct();
        $this->approval = new AssignmentApprovalService();
    }

    /**
     * GET /admin/crm/assignments — Pending approvals + history
     */
    public function index()
    {
        $this->requireAdmin();
        $data = [
            'page_title' => 'Assignment Approvals',
            'pending' => $this->approval->getPendingRequests(),
            'history' => $this->approval->getHistory(30),
            'stats' => $this->approval->getStats(),
        ];
        return $this->render('admin/crm/assignment_approvals', $data);
    }

    /**
     * POST /admin/crm/assignments/{id}/approve — Approve
     */
    public function approve(int $id)
    {
        $this->requireAdmin();
        $adminId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        $result = $this->approval->approveRequest($id, 0, 0, $adminId, $notes);

        if ($result['success']) {
            $this->setFlash('success', 'Assignment approved');
        } else {
            $this->setFlash('error', 'Failed: ' . ($result['error'] ?? 'Unknown'));
        }
        return $this->redirect('/admin/crm/assignments');
    }

    /**
     * POST /admin/crm/assignments/{id}/reject — Reject
     */
    public function reject(int $id)
    {
        $this->requireAdmin();
        $adminId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        $result = $this->approval->rejectRequest($id, $adminId, $reason);

        if ($result['success']) {
            $this->setFlash('success', 'Assignment request rejected');
        } else {
            $this->setFlash('error', 'Failed: ' . ($result['error'] ?? 'Unknown'));
        }
        return $this->redirect('/admin/crm/assignments');
    }

    /**
     * POST /admin/crm/assignments/bulk — Bulk approve/reject
     */
    public function bulkAction()
    {
        $this->requireAdmin();
        $adminId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $action = $_POST['bulk_action'] ?? '';
        $ids = $_POST['approval_ids'] ?? [];

        if (empty($ids)) {
            $this->setFlash('error', 'No requests selected');
            return $this->redirect('/admin/crm/assignments');
        }

        $approved = 0;
        $rejected = 0;
        foreach ($ids as $id) {
            if ($action === 'approve') {
                $r = $this->approval->approveRequest((int)$id, 0, 0, $adminId, 'Bulk approved');
                if ($r['success']) $approved++;
            } elseif ($action === 'reject') {
                $r = $this->approval->rejectRequest((int)$id, $adminId, 'Bulk rejected');
                if ($r['success']) $rejected++;
            }
        }

        $this->setFlash('success', "Bulk action complete: $approved approved, $rejected rejected");
        return $this->redirect('/admin/crm/assignments');
    }
}
