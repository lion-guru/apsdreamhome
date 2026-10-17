<?php
/**
 * GRNController — Goods Receipt Note management (Gap 6).
 *
 * UI module on top of the existing grn_records table + new grn_items table.
 * Routes: /admin/grn (index/create/store/show/edit/update/delete/verify/accept/reject)
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\GRNService;
use Exception;

class GRNController extends AdminController
{
    protected $grnService;

    public function __construct()
    {
        parent::__construct();
        $this->grnService = new GRNService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $grns = $this->grnService->getAll($status ?: null);
            $stats = $this->grnService->getStats();
            return $this->render('admin/grn/index', [
                'page_title' => 'Goods Receipt Notes - APS Dream Home',
                'active_page' => 'grn',
                'grns' => $grns,
                'stats' => $stats,
                'filters' => ['status' => $status],
                'statuses' => ['draft', 'verified', 'accepted', 'partial', 'rejected'],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load GRNs: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        try {
            // Approved/received POs that don't yet have a GRN
            $pos = $this->db->fetchAll(
                "SELECT po.id, po.order_number, po.vendor_name, po.total_amount
                 FROM purchase_orders po
                 WHERE po.tenant_id = ? AND po.status IN ('approved','received')
                   AND po.grn_number IS NULL
                 ORDER BY po.created_at DESC",
                [$this->tenantId()]
            ) ?: [];
            return $this->render('admin/grn/create', [
                'page_title' => 'Create GRN',
                'active_page' => 'grn',
                'pos' => $pos,
                'today' => date('Y-m-d'),
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/grn');
        }
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            if (empty($_POST['purchase_order_id'])) {
                return $this->jsonError('Purchase order is required', 400);
            }
            $grnId = $this->grnService->createFromPo((int)$_POST['purchase_order_id'], $_POST);
            $this->logActivity('grn_created', ['grn_id' => $grnId, 'number' => $_POST['grn_number'] ?? '']);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'GRN created',
                'grn_id' => $grnId,
                'redirect' => BASE_URL . '/admin/grn/' . $grnId,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $grn = $this->grnService->getById((int)$id);
            if (!$grn) {
                $this->setFlash('error', 'GRN not found');
                return $this->redirect('/admin/grn');
            }
            return $this->render('admin/grn/show', [
                'page_title' => 'GRN: ' . $grn['grn_number'],
                'active_page' => 'grn',
                'grn' => $grn,
                'items' => $grn['items'] ?? [],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/grn');
        }
    }

    public function updateStatus($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $status = $_POST['status'] ?? '';
            if (!in_array($status, ['verified', 'accepted', 'partial', 'rejected'], true)) {
                return $this->jsonError('Invalid status', 400);
            }
            $ok = $this->grnService->updateStatus((int)$id, $status, $_SESSION['user_id'] ?? 1);
            if (!$ok) {
                return $this->jsonError('GRN not found', 404);
            }
            $this->logActivity('grn_status_changed', ['grn_id' => $id, 'status' => $status]);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'GRN status updated to ' . ucfirst($status),
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $tid = $this->tenantId();
            $grn = $this->db->fetchOne("SELECT id, status FROM grn_records WHERE id = ? AND tenant_id = ?", [(int)$id, $tid]);
            if (!$grn) {
                return $this->jsonError('GRN not found', 404);
            }
            if ($grn['status'] !== 'draft') {
                return $this->jsonError('Only draft GRNs can be deleted', 400);
            }
            $this->db->delete('grn_items', ['grn_id' => $id]);
            $this->db->delete('grn_records', ['id' => $id]);
            $this->logActivity('grn_deleted', ['grn_id' => $id]);
            return $this->jsonResponse(['success' => true, 'message' => 'GRN deleted']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    protected function tenantId(): int
    {
        return (int)($_SESSION['tenant_id'] ?? 1);
    }
}
