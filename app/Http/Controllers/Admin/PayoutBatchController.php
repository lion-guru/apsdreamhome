<?php

namespace App\Http\Controllers\Admin;

use App\Services\PayoutBatchService;

/**
 * Payout Batch Controller
 * ───────────────────────
 * Admin payout batch management with approval workflow.
 * Routes under /admin/payout-batches/*
 */
class PayoutBatchController extends AdminController
{
    /** @var PayoutBatchService */
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PayoutBatchService();
    }

    /**
     * GET /admin/payout-batches
     * Dashboard with stats + batch list.
     */
    public function index(): void
    {
        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));

        $stats = $this->service->getStats();
        $result = $this->service->getBatches($status, $type, $page);

        $data = [
            'title'     => 'Payout Batches',
            'stats'     => $stats,
            'items'     => $result['items'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'total_pages' => $result['total_pages'],
            'status_filter' => $status,
            'type_filter'   => $type,
        ];

        $this->render('admin/payout-batches/index', $data);
    }

    /**
     * GET /admin/payout-batches/create
     * Create form.
     */
    public function create(): void
    {
        $data = [
            'title' => 'Create Payout Batch',
        ];
        $this->render('admin/payout-batches/create', $data);
    }

    /**
     * POST /admin/payout-batches/store
     * Create new batch.
     */
    public function store(): void
    {
        $name = trim($_POST['batch_name'] ?? '');
        $type = $_POST['batch_type'] ?? 'commission';
        $from = $_POST['period_from'] ?? null;
        $to = $_POST['period_to'] ?? null;
        $notes = trim($_POST['notes'] ?? '');
        $createdBy = (int)($_SESSION['admin_id'] ?? 0);

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Batch name is required.';
            $this->redirect('/admin/payout-batches/create');
            return;
        }

        $result = $this->service->createBatch([
            'batch_name' => $name,
            'batch_type' => $type,
            'period_from' => $from,
            'period_to' => $to,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);

        if ($result['success']) {
            // Auto-populate if requested
            if (!empty($_POST['auto_populate'])) {
                $popResult = $this->service->autoPopulateBatch(
                    $result['batch_id'],
                    $_POST['populate_type'] ?? '',
                    $_POST['populate_from'] ?? '',
                    $_POST['populate_to'] ?? ''
                );
                $_SESSION['flash_success'] = "Batch created. " .
                    ($popResult['success'] ? "{$popResult['entries_added']} entries added (₹" . number_format($popResult['total_amount']) . ")" : $popResult['error']);
            } else {
                $_SESSION['flash_success'] = 'Batch created. Add entries manually or use auto-populate.';
            }
            $this->redirect('/admin/payout-batches/' . $result['batch_id']);
        } else {
            $_SESSION['flash_error'] = $result['error'];
            $this->redirect('/admin/payout-batches/create');
        }
    }

    /**
     * GET /admin/payout-batches/{id}
     * Detail view with entries.
     */
    public function detail(int $id): void
    {
        $batch = $this->service->getBatch($id);
        if (!$batch) {
            $_SESSION['flash_error'] = 'Batch not found.';
            $this->redirect('/admin/payout-batches');
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $entries = $this->service->getBatchEntries($id, $page, 50);

        $data = [
            'title'   => 'Batch: ' . $batch['batch_name'],
            'batch'   => $batch,
            'entries' => $entries['items'],
            'total_entries' => $entries['total'],
            'entry_page' => $entries['page'],
            'entry_total_pages' => $entries['total_pages'],
        ];

        $this->render('admin/payout-batches/detail', $data);
    }

    /**
     * POST /admin/payout-batches/populate
     * Auto-populate batch with pending entries.
     */
    public function populate(int $id): void
    {
        $type = $_POST['populate_type'] ?? '';
        $from = $_POST['populate_from'] ?? '';
        $to = $_POST['populate_to'] ?? '';

        $result = $this->service->autoPopulateBatch($id, $type, $from, $to);

        if ($result['success']) {
            $_SESSION['flash_success'] = "{$result['entries_added']} entries added. ₹" .
                number_format($result['total_amount']) . " total. {$result['skipped']} skipped (already in other batches).";
        } else {
            $_SESSION['flash_error'] = $result['error'];
        }

        $this->redirect('/admin/payout-batches/' . $id);
    }

    /**
     * POST /admin/payout-batches/submit
     * Submit batch for approval.
     */
    public function submit(int $id): void
    {
        $result = $this->service->submitForApproval($id);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['success'] ? 'Batch submitted for approval.' : $result['error'];
        $this->redirect('/admin/payout-batches/' . $id);
    }

    /**
     * POST /admin/payout-batches/approve
     * Approve batch.
     */
    public function approve(int $id): void
    {
        $approvedBy = (int)($_SESSION['admin_id'] ?? 0);
        $result = $this->service->approveBatch($id, $approvedBy);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['success'] ? 'Batch approved.' : $result['error'];
        $this->redirect('/admin/payout-batches/' . $id);
    }

    /**
     * POST /admin/payout-batches/reject
     * Reject batch.
     */
    public function reject(int $id): void
    {
        $rejectedBy = (int)($_SESSION['admin_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $result = $this->service->rejectBatch($id, $rejectedBy, $reason);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['success'] ? 'Batch rejected.' : $result['error'];
        $this->redirect('/admin/payout-batches/' . $id);
    }

    /**
     * POST /admin/payout-batches/process
     * Start processing batch.
     */
    public function process(int $id): void
    {
        $processedBy = (int)($_SESSION['admin_id'] ?? 0);
        $result = $this->service->startProcessing($id, $processedBy);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['success'] ? 'Batch processing started.' : $result['error'];
        $this->redirect('/admin/payout-batches/' . $id);
    }

    /**
     * POST /admin/payout-batches/complete-entry
     * Mark single entry as completed.
     */
    public function completeEntry(): void
    {
        $entryId = (int)($_POST['entry_id'] ?? 0);
        $ref = trim($_POST['payment_ref'] ?? '');
        $result = $this->service->completeEntry($entryId, $ref);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['success'] ? 'Entry marked as completed.' : $result['error'];

        $batchId = (int)($_POST['batch_id'] ?? 0);
        $this->redirect('/admin/payout-batches/' . $batchId);
    }

    /**
     * POST /admin/payout-batches/export
     * Generate bank export file.
     */
    public function export(int $id): void
    {
        $result = $this->service->generateBankExport($id);
        if ($result['success']) {
            $_SESSION['flash_success'] = "Export generated: {$result['file']} ({$result['entries']} entries)";
            // Trigger download
            $filepath = $result['path'];
            if (file_exists($filepath)) {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $result['file'] . '"');
                header('Content-Length: ' . filesize($filepath));
                readfile($filepath);
                exit;
            }
        } else {
            $_SESSION['flash_error'] = $result['error'];
            $this->redirect('/admin/payout-batches/' . $id);
        }
    }
}
