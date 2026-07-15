<?php

namespace App\Http\Controllers\Admin;

use App\Services\RetroactiveRecalculationService;

/**
 * Admin Controller for Retroactive Commission Recalculations
 * ──────────────────────────────────────────────────────────
 * Lists pending requests, approves/rejects, and shows history.
 * All routes under /admin/commission/recalculations/*
 */
class RecalculationController extends AdminController
{
    /** @var RetroactiveRecalculationService */
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new RetroactiveRecalculationService();
    }

    /**
     * GET /admin/commission/recalculations
     * Dashboard with stats + paginated requests list.
     */
    public function index(): void
    {
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));

        $stats = $this->service->getStats();
        $result = $this->service->getRequests($status, $page, 25);

        $data = [
            'title'   => 'Commission Recalculations',
            'stats'   => $stats,
            'items'   => $result['items'],
            'total'   => $result['total'],
            'page'    => $result['page'],
            'total_pages' => $result['total_pages'],
            'status_filter' => $status,
        ];

        $this->render('admin/commission/recalculations/index', $data);
    }

    /**
     * GET /admin/commission/recalculations/{id}
     * Detail view of a single recalculation request with original entry diff.
     */
    public function detail(int $id): void
    {
        global $__recalc_detail_result;

        // Fetch the specific recalculation
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("
            SELECT cr.*,
                   ml.commission_type as orig_type, ml.amount as orig_calc_amount,
                   ml.beneficiary_user_id, ml.source_user_id, ml.booking_id,
                   ml.commission_percentage as orig_rate, ml.sale_amount,
                   ml.plan_id as orig_plan_id, ml.plan_version as orig_plan_version,
                   ml.plan_snapshot,
                   u.name as beneficiary_name, s.name as source_name,
                   a.name as requested_by_name, b.name as approved_by_name
            FROM commission_recalculations cr
            LEFT JOIN mlm_commission_ledger ml ON ml.id = cr.original_ledger_id
            LEFT JOIN users u ON u.id = ml.beneficiary_user_id
            LEFT JOIN users s ON s.id = ml.source_user_id
            LEFT JOIN users a ON a.id = cr.requested_by
            LEFT JOIN users b ON b.id = cr.approved_by
            WHERE cr.id = ?
        ");
        $stmt->execute([$id]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$item) {
            $_SESSION['flash_error'] = 'Recalculation request not found.';
            $this->redirect('/admin/commission/recalculations');
            return;
        }

        $data = [
            'title' => 'Recalculation Detail #' . $id,
            'item'  => $item,
        ];

        $this->render('admin/commission/recalculations/detail', $data);
    }

    /**
     * POST /admin/commission/recalculations/request
     * Request recalculation for a single ledger entry.
     */
    public function request(): void
    {
        $ledgerId = (int)($_POST['ledger_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $requestedBy = (int)($_SESSION['admin_id'] ?? 0);

        if ($ledgerId <= 0 || empty($reason)) {
            $_SESSION['flash_error'] = 'Ledger ID and reason are required.';
            $this->redirect('/admin/commission/recalculations');
            return;
        }

        $result = $this->service->requestRecalculation($ledgerId, $reason, $requestedBy);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Recalculation requested. Original: ₹" .
                number_format($result['original']['amount']) .
                " → New: ₹" . number_format($result['new_amount']) .
                " (Diff: ₹" . number_format($result['diff']) . ")";
        } else {
            $_SESSION['flash_error'] = $result['error'];
        }

        $this->redirect('/admin/commission/recalculations');
    }

    /**
     * POST /admin/commission/recalculations/approve
     * Approve a pending recalculation — creates new ledger entry, superseded original.
     */
    public function approve(): void
    {
        $recalcId = (int)($_POST['recalc_id'] ?? 0);
        $approvedBy = (int)($_SESSION['admin_id'] ?? 0);
        $notes = trim($_POST['admin_notes'] ?? '');

        $result = $this->service->approveRecalculation($recalcId, $approvedBy, $notes);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Approved. New ledger entry #{$result['new_ledger_id']} created. " .
                "Original amount: ₹" . number_format($result['original_amount']) .
                " → New: ₹" . number_format($result['new_amount']);
        } else {
            $_SESSION['flash_error'] = $result['error'];
        }

        $this->redirect('/admin/commission/recalculations/' . $recalcId);
    }

    /**
     * POST /admin/commission/recalculations/reject
     * Reject a pending recalculation.
     */
    public function reject(): void
    {
        $recalcId = (int)($_POST['recalc_id'] ?? 0);
        $rejectedBy = (int)($_SESSION['admin_id'] ?? 0);
        $notes = trim($_POST['admin_notes'] ?? '');

        $result = $this->service->rejectRecalculation($recalcId, $rejectedBy, $notes);

        if ($result['success']) {
            $_SESSION['flash_success'] = 'Recalculation request rejected.';
        } else {
            $_SESSION['flash_error'] = $result['error'];
        }

        $this->redirect('/admin/commission/recalculations/' . $recalcId);
    }

    /**
     * POST /admin/commission/recalculations/bulk-request
     * Bulk request recalculation for a commission type within date range.
     */
    public function bulkRequest(): void
    {
        $type = trim($_POST['commission_type'] ?? '');
        $from = $_POST['date_from'] ?? '';
        $to = $_POST['date_to'] ?? '';
        $reason = trim($_POST['reason'] ?? '');
        $requestedBy = (int)($_SESSION['admin_id'] ?? 0);

        if (empty($type) || empty($from) || empty($to) || empty($reason)) {
            $_SESSION['flash_error'] = 'All fields are required.';
            $this->redirect('/admin/commission/recalculations');
            return;
        }

        $result = $this->service->bulkRequestRecalculation($type, $from, $to, $reason, $requestedBy);

        $_SESSION['flash_success'] = "Bulk request: {$result['requested']} requested, " .
            "{$result['skipped']} skipped, {$result['errors']} errors (out of {$result['total']})";

        $this->redirect('/admin/commission/recalculations');
    }

    /**
     * Helper: get PDO connection.
     */
    private function getPdo(): \PDO
    {
        $config = require dirname(__DIR__, 3) . '/config/database.php';
        return new \PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
            $config['username'],
            $config['password'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }
}
