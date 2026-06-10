<?php

namespace App\Http\Controllers\Admin;

use App\Services\BankImportService;
use Exception;
use UploadValidator;

class BankImportController extends AdminController
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->service = new BankImportService(
                $this->db instanceof \PDO ? $this->db : null
            );
        } catch (Exception $e) {
            try {
                $this->service = new BankImportService();
            } catch (Exception $e2) {
                $this->service = null;
            }
        }
    }

    private function safe(callable $fn, $fallback = null)
    {
        try {
            return $fn();
        } catch (Exception $e) {
            error_log('BankImportController error: ' . $e->getMessage());
            return $fallback;
        }
    }

    public function index()
    {
        $this->requireAdmin();
        $imports = $this->safe(fn() => $this->service->getImports(), []);

        // Compute summary stats
        $totalImports = count($imports);
        $totalTransactions = array_sum(array_column($imports, 'total_rows'));
        $totalMatched = array_sum(array_column($imports, 'matched_rows'));
        $totalUnmatched = array_sum(array_column($imports, 'unmatched_rows'));
        $matchRate = $totalTransactions > 0 ? round(($totalMatched / $totalTransactions) * 100, 1) : 0;

        return $this->render('admin/bank-import/index', [
            'page_title'      => 'Bank Statement Import',
            'page_heading'    => 'Bank Statement Import',
            'imports'         => $imports,
            'total_imports'   => $totalImports,
            'total_transactions' => $totalTransactions,
            'total_matched'   => $totalMatched,
            'total_unmatched' => $totalUnmatched,
            'match_rate'      => $matchRate,
        ]);
    }

    public function upload()
    {
        $this->requireAdmin();
        $banks = $this->safe(fn() => $this->service->getBankAccounts(), []);

        return $this->render('admin/bank-import/upload', [
            'page_title'   => 'Upload Bank Statement',
            'page_heading' => 'Upload Bank Statement',
            'banks'        => $banks,
        ]);
    }

    public function process()
    {
        $this->requireAdmin();

        // CSRF check
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        if (empty($_FILES['csv_file'])) {
            $this->json(['success' => false, 'error' => 'No file uploaded'], 400);
            return;
        }

        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'error' => 'Upload error: ' . $file['error']], 400);
            return;
        }

        $v = UploadValidator::validate($file, ['types' => 'csv', 'max_size' => 10]);
        if (!$v['valid']) {
            $this->json(['success' => false, 'error' => $v['error']], 400);
            return;
        }

        // Move to temp location
        $safeName = UploadValidator::safeFilename($file['name']);
        $uploadDir = sys_get_temp_dir();
        $destPath = $uploadDir . '/bank_import_' . time() . '_' . bin2hex(random_bytes(4)) . '.csv';
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->json(['success' => false, 'error' => 'Failed to save uploaded file'], 500);
            return;
        }

        $importedBy = (int)($_SESSION['admin_id'] ?? 0);
        $bankAccountId = !empty($_POST['bank_account_id']) ? (int)$_POST['bank_account_id'] : null;

        $result = $this->service->importCsv($destPath, $importedBy);

        // Set bank account on import if provided
        if ($result['success'] && $bankAccountId) {
            try {
                $pdo = $this->db instanceof \PDO ? $this->db : \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) $pdo = $pdo->getPdo();
                $stmt = $pdo->prepare("UPDATE bank_statement_imports SET bank_account_id = ? WHERE id = ?");
                $stmt->execute([$bankAccountId, $result['import_id']]);
                // Also set on individual transactions
                $stmt2 = $pdo->prepare("UPDATE bank_transactions SET bank_account_id = ? WHERE import_id = ?");
                $stmt2->execute([$bankAccountId, $result['import_id']]);
            } catch (Exception $e) {
                // non-fatal
            }
        }

        // Clean up temp file
        @unlink($destPath);

        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->json($result, $result['success'] ? 200 : 400);
        } else {
            if ($result['success']) {
                $_SESSION['flash_success'] = "Imported {$result['imported']} transactions from {$file['name']}";
                $this->redirect(BASE_URL . '/admin/bank-import/' . $result['import_id']);
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? 'Import failed';
                $this->redirect(BASE_URL . '/admin/bank-import/upload');
            }
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $import = $this->service->getImport($id);
        if (!$import) {
            $_SESSION['flash_error'] = 'Import not found';
            $this->redirect(BASE_URL . '/admin/bank-import');
            return;
        }

        $summary = $this->service->getReconciliationSummary($id);
        $matchedTxns = $this->service->getImportTransactions($id, '1');
        $unmatchedTxns = $this->service->getImportTransactions($id, '0');

        return $this->render('admin/bank-import/show', [
            'page_title'      => 'Import #' . $id . ' Detail',
            'page_heading'    => 'Import Detail',
            'import'          => $import,
            'summary'         => $summary,
            'matched_txns'    => $matchedTxns,
            'unmatched_txns'  => $unmatchedTxns,
        ]);
    }

    public function match($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $result = $this->service->autoMatch($id);

        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->json($result, $result['success'] ? 200 : 500);
        } else {
            if ($result['success']) {
                $_SESSION['flash_success'] = "Auto-matched {$result['matched']} transactions, {$result['unmatched']} remain unmatched";
            } else {
                $_SESSION['flash_error'] = $result['error'] ?? 'Match failed';
            }
            $this->redirect(BASE_URL . '/admin/bank-import/' . $id);
        }
    }

    public function manualMatch()
    {
        $this->requireAdmin();

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $bankTxnId = (int)($_POST['bank_txn_id'] ?? 0);
        $internalTxnId = (int)($_POST['internal_txn_id'] ?? 0);

        if ($bankTxnId <= 0 || $internalTxnId <= 0) {
            $this->json(['success' => false, 'error' => 'Missing transaction IDs'], 400);
            return;
        }

        $ok = $this->service->manualMatch($bankTxnId, $internalTxnId);

        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->json(['success' => $ok], $ok ? 200 : 500);
        } else {
            $_SESSION['flash_success'] = $ok ? 'Transaction matched successfully' : 'Match failed';
            $bankTxn = $this->safe(fn() => $this->service->getBankTransaction($bankTxnId) ?? []);
            $importId = $bankTxn['import_id'] ?? 0;
            $this->redirect(BASE_URL . '/admin/bank-import/' . $importId);
        }
    }

    public function unmatch($txnId)
    {
        $this->requireAdmin();
        $txnId = (int)$txnId;

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        // Get the bank transaction to find import_id for redirect
        $bankTxn = $this->safe(fn() => $this->service->getBankTransaction($txnId) ?? []);
        $importId = $bankTxn['import_id'] ?? 0;

        $ok = $this->service->unmatch($txnId);

        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->json(['success' => $ok], $ok ? 200 : 500);
        } else {
            $_SESSION['flash_success'] = $ok ? 'Transaction unmatch removed' : 'Unmatch failed';
            $this->redirect(BASE_URL . '/admin/bank-import/' . $importId);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $ok = $this->service->deleteImport($id);

        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->json(['success' => $ok], $ok ? 200 : 500);
        } else {
            $_SESSION['flash_success'] = $ok ? 'Import deleted' : 'Delete failed';
            $this->redirect(BASE_URL . '/admin/bank-import');
        }
    }

    public function export($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $unmatched = $this->service->getUnmatchedTransactions($id);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=unmatched_bank_' . $id . '_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['Date', 'Description', 'Debit', 'Credit', 'Balance', 'Cheque/Ref', 'Reference']);

        foreach ($unmatched as $row) {
            fputcsv($output, [
                $row['transaction_date'],
                $row['description'],
                $row['debit'] > 0 ? number_format($row['debit'], 2, '.', '') : '',
                $row['credit'] > 0 ? number_format($row['credit'], 2, '.', '') : '',
                number_format($row['balance'], 2, '.', ''),
                $row['cheque_number'] ?? '',
                $row['reference_number'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    public function searchInternal()
    {
        $this->requireAdmin();

        $query = $_GET['q'] ?? '';
        $amount = (float)($_GET['amount'] ?? 0);

        $results = $this->service->searchInternalTransactions($query, $amount);

        $this->json(['success' => true, 'data' => $results]);
    }
}
