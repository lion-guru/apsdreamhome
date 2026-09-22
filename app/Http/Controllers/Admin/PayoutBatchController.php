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
     * GET /admin/payout-batches/{id}/export-bank-csv?format=generic|icici|hdfc
     *
     * Bank Bulk Payout Engine — streams a Corporate NetBanking bulk-upload CSV:
     * SrNo, BeneficiaryName, AccountNo, IFSC, Amount, PaymentType(NEFT/RTGS), Remarks.
     *
     * Real-schema mapping (verified via DESCRIBE):
     * - Beneficiary: users.name
     * - Account/IFSC: user_bank_accounts (is_primary=1 first) → payout_entries
     *   beneficiary_account/beneficiary_ifsc snapshot fallback
     * - Net amount: payout_entries.net_amount (post 194H TDS via TdsConfigService)
     * - Reference: APS-COMM-{batch}-{entry} (persisted to payment_reference so the
     *   later UTR import can match on it)
     */
    public function exportBankCsv(int $id): void
    {
        $this->requireAdmin();
        $id = (int)$id;
        $format = strtolower(trim($_GET['format'] ?? 'generic'));
        if (!in_array($format, ['generic', 'icici', 'hdfc', 'sbi'], true)) {
            $format = 'generic';
        }

        $batch = $this->service->getBatch($id);
        if (!$batch) {
            $_SESSION['flash_error'] = 'Batch not found.';
            $this->redirect('/admin/payout-batches');
            return;
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::exportBankCsv db: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Database unavailable.';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }

        $tid = (int)$this->tenantId();
        $tWhere = $tid > 1 ? ' AND pe.tenant_id = ?' : '';
        $tParams = $tid > 1 ? [$tid] : [];

        try {
            $stmt = $pdo->prepare(
                "SELECT pe.*, u.name AS user_name, u.phone AS user_phone,
                        COALESCE(uba.account_holder_name, pe.beneficiary_name, u.name, '') AS bene_name,
                        COALESCE(uba.account_number, pe.beneficiary_account, '') AS account_no,
                        COALESCE(uba.ifsc_code, pe.beneficiary_ifsc, '') AS ifsc_code
                 FROM payout_entries pe
                 LEFT JOIN users u ON u.id = pe.beneficiary_user_id
                 LEFT JOIN user_bank_accounts uba
                       ON uba.user_id = pe.beneficiary_user_id AND uba.is_primary = 1
                 WHERE pe.batch_id = ? AND pe.status != 'cancelled'{$tWhere}
                 ORDER BY pe.id ASC"
            );
            $stmt->execute(array_merge([$id], $tParams));
            $entries = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::exportBankCsv load: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Could not load batch entries.';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }

        // Fallback: any verified bank account when no primary exists.
        $fallbackStmt = null;
        try {
            $fallbackStmt = $pdo->prepare(
                "SELECT account_holder_name, account_number, ifsc_code
                 FROM user_bank_accounts
                 WHERE user_id = ? ORDER BY is_verified DESC, is_primary DESC, id DESC LIMIT 1"
            );
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::exportBankCsv fallback prepare: ' . $e->getMessage());
        }

        try {
            $persistRef = $pdo->prepare(
                "UPDATE payout_entries SET payment_reference = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "")
            );
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::exportBankCsv ref prepare: ' . $e->getMessage());
            $persistRef = null;
        }

        $rows = [];
        $sr = 0;
        foreach ($entries as $e) {
            $entryId = (int)$e['id'];
            $net = round((float)($e['net_amount'] ?? 0), 2);

            $account = trim((string)($e['account_no'] ?? ''));
            $ifsc = trim((string)($e['ifsc_code'] ?? ''));
            if (($account === '' || $ifsc === '') && $fallbackStmt) {
                try {
                    $fallbackStmt->execute([(int)$e['beneficiary_user_id']]);
                    if ($fb = $fallbackStmt->fetch(\PDO::FETCH_ASSOC)) {
                        if ($account === '') $account = trim((string)($fb['account_number'] ?? ''));
                        if ($ifsc === '') $ifsc = trim((string)($fb['ifsc_code'] ?? ''));
                    }
                } catch (\Throwable $ex) {
                    error_log('PayoutBatchController::exportBankCsv fallback: ' . $ex->getMessage());
                }
            }

            $ref = trim((string)($e['payment_reference'] ?? ''));
            if ($ref === '') {
                $ref = 'APS-COMM-' . $id . '-' . $entryId;
                if ($persistRef) {
                    try {
                        $persistRef->execute($tid > 1 ? [$ref, $entryId, $tid] : [$ref, $entryId]);
                    } catch (\Throwable $ex) {
                        error_log('PayoutBatchController::exportBankCsv persist ref: ' . $ex->getMessage());
                    }
                }
            }

            $sr++;
            $rows[] = [
                'sr'      => $sr,
                'name'    => trim((string)($e['bene_name'] ?? '')),
                'account' => $account,
                'ifsc'    => strtoupper($ifsc),
                'amount'  => $net,
                'type'    => $net >= 200000 ? 'RTGS' : 'NEFT',
                'ref'     => $ref,
                'remarks' => 'APS Commission ' . ($batch['batch_name'] ?? ('Batch ' . $id)),
            ];
        }

        if ($format === 'sbi') {
            $header = ['Payment Type', 'Beneficiary Account No', 'Beneficiary IFSC', 'Amount', 'Beneficiary Name', 'Customer Reference', 'Narration'];
            $csvRows = [$header];
            foreach ($rows as $r) {
                $csvRows[] = [$r['type'], $r['account'], $r['ifsc'], number_format($r['amount'], 2, '.', ''), $r['name'], $r['ref'], $r['remarks']];
            }
        } elseif ($format === 'icici') {
            $header = ['Beneficiary Name', 'Account Number', 'IFSC Code', 'Amount', 'Payment Type(NEFT/RTGS/IMPS)', 'Customer Reference', 'Remarks'];
            $csvRows = [$header];
            foreach ($rows as $r) {
                $csvRows[] = [$r['name'], $r['account'], $r['ifsc'], number_format($r['amount'], 2, '.', ''), $r['type'], $r['ref'], $r['remarks']];
            }
        } elseif ($format === 'hdfc') {
            $header = ['Sr No', 'Beneficiary Name', 'Beneficiary Account No', 'IFSC', 'Amount', 'Payment Type', 'Reference No', 'Narration'];
            $csvRows = [$header];
            foreach ($rows as $r) {
                $csvRows[] = [$r['sr'], $r['name'], $r['account'], $r['ifsc'], number_format($r['amount'], 2, '.', ''), $r['type'], $r['ref'], $r['remarks']];
            }
        } else {
            $header = ['SrNo', 'BeneficiaryName', 'AccountNo', 'IFSC', 'Amount', 'PaymentType', 'Remarks'];
            $csvRows = [$header];
            foreach ($rows as $r) {
                $csvRows[] = [$r['sr'], $r['name'], $r['account'], $r['ifsc'], number_format($r['amount'], 2, '.', ''), $r['type'], $r['ref'] . ' | ' . $r['remarks']];
            }
        }

        $filename = 'payout_batch_' . $id . '_' . $format . '_' . date('Y-m-d_His') . '.csv';
        try {
            $upd = $pdo->prepare("UPDATE payout_batches SET bank_export_file = ?, updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $upd->execute($tid > 1 ? [$filename, $id, $tid] : [$filename, $id]);
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::exportBankCsv audit: ' . $e->getMessage());
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        $fp = fopen('php://output', 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel
        foreach ($csvRows as $csvRow) {
            fputcsv($fp, $csvRow);
        }
        fclose($fp);
        exit;
    }

    /**
     * POST /admin/payout-batches/{id}/import-utr
     *
     * Bank UTR Reconciliation — matches an uploaded bank report CSV against
     * this batch by Payment Reference (APS-COMM-{batch}-{entry}) or by
     * Account Number + Amount, marks entries completed, stores the UTR,
     * fires SMS/WhatsApp/push alerts, and auto-completes the batch.
     */
    public function importUtrCsv(int $id): void
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;

        $batch = $this->service->getBatch($id);
        if (!$batch) {
            $_SESSION['flash_error'] = 'Batch not found.';
            $this->redirect('/admin/payout-batches');
            return;
        }
        if (!in_array(($batch['status'] ?? ''), ['approved', 'processing', 'completed'], true)) {
            $_SESSION['flash_error'] = 'UTR import needs an approved batch (current: ' . ($batch['status'] ?? '?') . ').';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }

        if (empty($_FILES['utr_file']) || (int)$_FILES['utr_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Please choose a bank UTR CSV file to upload.';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }
        $tmpPath = (string)$_FILES['utr_file']['tmp_name'];
        $origName = (string)$_FILES['utr_file']['name'];
        if (!preg_match('/\.csv$/i', $origName) || (int)$_FILES['utr_file']['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'Only CSV files up to 10MB are accepted.';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::importUtrCsv db: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Database unavailable.';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }

        $this->ensureUtrColumns($pdo);
        $hasUtrCols = $this->payoutEntriesHasUtrColumns($pdo);

        // Load open entries of this batch for matching.
        $tid = (int)$this->tenantId();
        $tWhere = $tid > 1 ? ' AND pe.tenant_id = ?' : '';
        $tParams = $tid > 1 ? [$tid] : [];
        try {
            $stmt = $pdo->prepare(
                "SELECT pe.*, u.name AS user_name, u.phone AS user_phone,
                        COALESCE(uba.account_number, pe.beneficiary_account, '') AS account_no
                 FROM payout_entries pe
                 LEFT JOIN users u ON u.id = pe.beneficiary_user_id
                 LEFT JOIN user_bank_accounts uba
                       ON uba.user_id = pe.beneficiary_user_id AND uba.is_primary = 1
                 WHERE pe.batch_id = ? AND pe.status IN ('pending','processing','failed'){$tWhere}"
            );
            $stmt->execute(array_merge([$id], $tParams));
            $open = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::importUtrCsv load: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Could not load batch entries.';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }

        $byRef = [];
        $byAcctAmt = [];
        foreach ($open as $e) {
            $ref = strtoupper(trim((string)($e['payment_reference'] ?? '')));
            if ($ref !== '') $byRef[$ref] = $e;
            // Also index the canonical generated reference even before first export.
            $byRef['APS-COMM-' . $id . '-' . (int)$e['id']] = $e;
            $digits = preg_replace('/[^0-9]/', '', (string)($e['account_no'] ?? ''));
            if ($digits !== '') {
                $byAcctAmt[$digits . '|' . number_format((float)$e['net_amount'], 2, '.', '')] = $e;
            }
        }

        $norm = function ($s) {
            return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$s));
        };
        $refKeys = ['paymentreference', 'reference', 'ref', 'referenceno', 'customerreference', 'remarks', 'narration'];
        $utrKeys = ['utr', 'utrnumber', 'utrno', 'transactionid', 'rrn', 'utrnoref', 'bankref'];
        $amtKeys = ['amount', 'creditamount', 'paidamount', 'transactionamount', 'netamount'];
        $acctKeys = ['account', 'accountno', 'accountnumber', 'beneficiaryaccount', 'creditaccount', 'beneficiaryaccountno'];

        $updated = 0;
        $errors = [];
        $seenEntry = [];

        $fh = @fopen($tmpPath, 'r');
        if (!$fh) {
            $_SESSION['flash_error'] = 'Could not read the uploaded file.';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }
        // Strip UTF-8 BOM from the first header cell.
        $headers = fgetcsv($fh);
        if (!$headers) {
            fclose($fh);
            $_SESSION['flash_error'] = 'The CSV file is empty.';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$headers[0]);
        $colIdx = ['ref' => -1, 'utr' => -1, 'amt' => -1, 'acct' => -1];
        foreach ($headers as $i => $h) {
            $k = $norm($h);
            if ($colIdx['ref'] === -1 && in_array($k, $refKeys, true)) $colIdx['ref'] = $i;
            if ($colIdx['utr'] === -1 && in_array($k, $utrKeys, true)) $colIdx['utr'] = $i;
            if ($colIdx['amt'] === -1 && in_array($k, $amtKeys, true)) $colIdx['amt'] = $i;
            if ($colIdx['acct'] === -1 && in_array($k, $acctKeys, true)) $colIdx['acct'] = $i;
        }
        if ($colIdx['ref'] === -1 && $colIdx['acct'] === -1) {
            fclose($fh);
            $_SESSION['flash_error'] = 'CSV needs a Reference column (or Account + Amount columns).';
            $this->redirect('/admin/payout-batches/' . $id);
            return;
        }

        $lineNo = 1;
        while (($row = fgetcsv($fh)) !== false) {
            $lineNo++;
            if (count(array_filter($row, function ($v) { return trim((string)$v) !== ''; })) === 0) continue;

            $refVal = $colIdx['ref'] >= 0 ? strtoupper(trim((string)($row[$colIdx['ref']] ?? ''))) : '';
            $utrVal = $colIdx['utr'] >= 0 ? trim((string)($row[$colIdx['utr']] ?? '')) : '';
            $amtVal = $colIdx['amt'] >= 0 ? (float)preg_replace('/[^0-9.\-]/', '', (string)($row[$colIdx['amt']] ?? '')) : 0.0;
            $acctVal = $colIdx['acct'] >= 0 ? preg_replace('/[^0-9]/', '', (string)($row[$colIdx['acct']] ?? '')) : '';

            // Reference may sit inside a remarks/narration cell — extract APS-COMM pattern.
            if ($refVal !== '' && !isset($byRef[$refVal]) && preg_match('/APS-COMM-\d+-\d+/i', $refVal, $m)) {
                $refVal = strtoupper($m[0]);
            }

            $entry = null;
            if ($refVal !== '' && isset($byRef[$refVal])) {
                $entry = $byRef[$refVal];
            } elseif ($acctVal !== '' && $amtVal > 0 && isset($byAcctAmt[$acctVal . '|' . number_format($amtVal, 2, '.', '')])) {
                $entry = $byAcctAmt[$acctVal . '|' . number_format($amtVal, 2, '.', '')];
            }

            if (!$entry) {
                $errors[] = 'Line ' . $lineNo . ': no open payout matches.';
                continue;
            }
            $entryId = (int)$entry['id'];
            if (isset($seenEntry[$entryId])) {
                $errors[] = 'Line ' . $lineNo . ': duplicate row for entry #' . $entryId . '.';
                continue;
            }
            $seenEntry[$entryId] = true;
            $utr = $utrVal !== '' ? $utrVal : $refVal;

            try {
                if ($hasUtrCols) {
                    $upd = $pdo->prepare(
                        "UPDATE payout_entries
                         SET status = 'completed', payment_reference = ?, utr_number = ?, paid_at = NOW(), processed_at = NOW()
                         WHERE id = ? AND status IN ('pending','processing','failed')" . ($tid > 1 ? " AND tenant_id = ?" : "")
                    );
                    $upd->execute($tid > 1 ? [$utr, $utr, $entryId, $tid] : [$utr, $utr, $entryId]);
                } else {
                    $upd = $pdo->prepare(
                        "UPDATE payout_entries
                         SET status = 'completed', payment_reference = ?, processed_at = NOW()
                         WHERE id = ? AND status IN ('pending','processing','failed')" . ($tid > 1 ? " AND tenant_id = ?" : "")
                    );
                    $upd->execute($tid > 1 ? [$utr, $entryId, $tid] : [$utr, $entryId]);
                }
                if ($upd->rowCount() === 0) {
                    $errors[] = 'Line ' . $lineNo . ': entry #' . $entryId . ' already processed.';
                    continue;
                }
                if (!empty($entry['ledger_id'])) {
                    $lg = $pdo->prepare("UPDATE mlm_commission_ledger SET status = 'paid' WHERE id = ? AND status IN ('pending','processing') AND tenant_id = ?");
                    $lg->execute([(int)$entry['ledger_id'], $tid > 0 ? $tid : 1]);
                }
                $updated++;
            } catch (\Throwable $e) {
                error_log('PayoutBatchController::importUtrCsv update: ' . $e->getMessage());
                $errors[] = 'Line ' . $lineNo . ': database error.';
                continue;
            }

            $this->notifyPayoutCredited((int)$entry['beneficiary_user_id'], (string)($entry['user_name'] ?? $entry['beneficiary_name'] ?? ''), (string)($entry['user_phone'] ?? ''), (float)$entry['net_amount'], $utr);
        }
        fclose($fh);

        // Auto-complete the batch when nothing open remains.
        try {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM payout_entries WHERE batch_id = ? AND status IN ('pending','processing','failed')" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $chk->execute($tid > 1 ? [$id, $tid] : [$id]);
            if ((int)$chk->fetchColumn() === 0) {
                $cb = $pdo->prepare("UPDATE payout_batches SET status = 'completed', updated_at = NOW() WHERE id = ? AND status IN ('approved','processing')" . ($tid > 1 ? " AND tenant_id = ?" : ""));
                $cb->execute($tid > 1 ? [$id, $tid] : [$id]);
            }
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::importUtrCsv complete: ' . $e->getMessage());
        }

        $errCount = count($errors);
        if ($errCount > 0) {
            error_log('PayoutBatchController::importUtrCsv batch ' . $id . ' errors: ' . implode(' | ', array_slice($errors, 0, 10)));
        }
        $_SESSION['flash_' . ($errCount > 0 && $updated === 0 ? 'error' : 'success')] =
            'Successfully updated ' . $updated . ' associate payout' . ($updated === 1 ? '' : 's') . '. ' . $errCount . ' error' . ($errCount === 1 ? '' : 's') . '.';
        $this->redirect('/admin/payout-batches/' . $id);
    }

    /**
     * Best-effort payout-credited alerts: in-app notification + SMS + WhatsApp.
     * Every channel fails independently and never blocks the import.
     */
    private function notifyPayoutCredited(int $userId, string $name, string $phone, float $amount, string $utr): void
    {
        $displayName = $name !== '' ? $name : ('Associate #' . $userId);
        $formatted = 'Rs.' . number_format($amount, 2);
        try {
            $ns = new \App\Services\Communication\NotificationService();
            $ns->sendNotification($userId, 'in_app', 'Commission payout credited',
                'Namaste ' . $displayName . '! Your commission payout of ' . $formatted . ' has been credited. UTR: ' . $utr . '. Team APS Dream Home.',
                ['type' => 'payout_credited', 'amount' => $amount, 'utr' => $utr]);
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::notifyPayoutCredited in-app: ' . $e->getMessage());
        }
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if ($digits === '') {
            return;
        }
        if (strlen($digits) === 10) $digits = '91' . $digits;
        try {
            $sms = new \App\Services\Communication\SmsService();
            $sms->sendPayoutSMS($digits, $amount, $utr);
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::notifyPayoutCredited sms: ' . $e->getMessage());
        }
        try {
            $wa = new \App\Services\Communication\WhatsAppTemplateService();
            $wa->sendTemplate([
                'to' => $digits,
                'template_name' => 'payout_credited',
                'language' => 'en',
                'components' => [[
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $displayName],
                        ['type' => 'text', 'text' => $formatted],
                        ['type' => 'text', 'text' => $utr],
                    ],
                ]],
            ]);
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::notifyPayoutCredited whatsapp: ' . $e->getMessage());
        }
    }

    /**
     * Idempotent schema guard: the mission-spec UTR columns
     * (payout_entries.utr_number / paid_at) do not exist in the live schema,
     * where payment_reference + processed_at + status='completed' are canonical.
     * Adds them when missing; import always writes the canonical columns too.
     */
    private function ensureUtrColumns(\PDO $pdo): void
    {
        try {
            $dbName = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
            $chk = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'payout_entries' AND COLUMN_NAME IN ('utr_number','paid_at')");
            $chk->execute([$dbName]);
            $have = $chk->fetchAll(\PDO::FETCH_COLUMN) ?: [];
            if (!in_array('utr_number', $have, true)) {
                $pdo->exec("ALTER TABLE payout_entries ADD COLUMN utr_number VARCHAR(100) NULL AFTER payment_reference");
            }
            if (!in_array('paid_at', $have, true)) {
                $pdo->exec("ALTER TABLE payout_entries ADD COLUMN paid_at DATETIME NULL AFTER utr_number");
            }
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::ensureUtrColumns: ' . $e->getMessage());
        }
    }

    private function payoutEntriesHasUtrColumns(\PDO $pdo): bool
    {
        try {
            $dbName = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
            $chk = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'payout_entries' AND COLUMN_NAME IN ('utr_number','paid_at')");
            $chk->execute([$dbName]);
            return ((int)$chk->fetchColumn()) === 2;
        } catch (\Throwable $e) {
            error_log('PayoutBatchController::payoutEntriesHasUtrColumns: ' . $e->getMessage());
            return false;
        }
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
