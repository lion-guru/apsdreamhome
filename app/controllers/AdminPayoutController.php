<?php
/**
 * AdminPayoutController
 * Admin UI endpoints for MLM payout automation (Phase 3).
 */

require_once __DIR__ . '/../services/PayoutService.php';

class AdminPayoutController
{
    private PayoutService $payoutService;

    public function __construct()
    {
        $this->payoutService = new PayoutService();
    }

    public function index(): void
    {
        $this->ensureAdmin();
        require __DIR__ . '/../views/admin/mlm_payouts.php';
    }

    public function list(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        try {
            $filters = $this->parseFilters($_GET);
            $limit = max(1, (int)($_GET['limit'] ?? 20));
            $offset = max(0, (int)($_GET['offset'] ?? 0));
            $batches = $this->payoutService->listBatches($filters, $limit, $offset);

            echo json_encode([
                'success' => true,
                'records' => $batches,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function create(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        try {
            $filters = $this->parseFilters($_POST);
            $filters['max_items'] = isset($_POST['max_items']) ? (int)$_POST['max_items'] : null;
            $filters['min_amount'] = isset($_POST['min_amount']) ? (float)$_POST['min_amount'] : null;
            $filters['batch_reference'] = $_POST['batch_reference'] ?? null;
            if (!empty($_POST['required_approvals'])) {
                $filters['required_approvals'] = (int) $_POST['required_approvals'];
            }

            $result = $this->payoutService->createBatch($filters);
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function approve(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $batchId = (int)($_POST['batch_id'] ?? 0);
        if (!$batchId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            return;
        }

        $userId = (int)($_SESSION['admin_id'] ?? 0);
        $decision = $_POST['decision'] ?? 'approved';
        $notes = $_POST['notes'] ?? null;
        $result = $this->payoutService->approveBatch($batchId, $userId, $decision, $notes);

        if ($result['success'] ?? false) {
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    }

    public function disburse(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $batchId = (int)($_POST['batch_id'] ?? 0);
        if (!$batchId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            return;
        }

        $reference = $_POST['reference'] ?? null;
        $notes = $_POST['notes'] ?? null;
        $userId = (int)($_SESSION['admin_id'] ?? 0);

        $success = $this->payoutService->recordDisbursement($batchId, $userId, $reference, $notes);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Payout recorded and commissions marked as paid.']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unable to record disbursement.']);
        }
    }

    public function cancel(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $batchId = (int)($_POST['batch_id'] ?? 0);
        if (!$batchId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            return;
        }

        $reason = $_POST['reason'] ?? null;
        $success = $this->payoutService->cancelBatch($batchId, $reason);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Batch cancelled.']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unable to cancel batch.']);
        }
    }

    public function items(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $batchId = (int)($_GET['batch_id'] ?? 0);
        if (!$batchId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            return;
        }

        $items = $this->payoutService->getBatchItems($batchId);
        $summary = $this->payoutService->getBatchSummary($batchId);

        echo json_encode([
            'success' => true,
            'records' => $items,
            'batch' => $summary,
        ]);
    }

    public function export(): void
    {
        $this->ensureAdmin();

        $batchId = (int)($_GET['batch_id'] ?? 0);
        if ($batchId <= 0) {
            http_response_code(400);
            echo 'batch_id required';
            return;
        }

        $format = strtolower((string)($_GET['format'] ?? 'csv'));
        if ($format !== 'csv') {
            http_response_code(400);
            echo 'Unsupported format';
            return;
        }

        $export = $this->payoutService->getBatchExportData($batchId);
        if (!$export) {
            http_response_code(404);
            echo 'Batch not found';
            return;
        }

        $batch = $export['batch'];
        $items = $export['items'];
        $reference = $batch['batch_reference'] ?? ('batch-' . $batchId);
        $filename = sprintf('payout-batch-%s.csv', preg_replace('/[^A-Za-z0-9_-]+/', '-', $reference));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            http_response_code(500);
            echo 'Unable to open output stream';
            return;
        }

        fputcsv($output, ['Batch Reference', $reference]);
        fputcsv($output, ['Status', $batch['status'] ?? '']);
        fputcsv($output, ['Required Approvals', $batch['required_approvals'] ?? '']);
        fputcsv($output, ['Approvals Recorded', $batch['approval_count'] ?? '']);
        fputcsv($output, ['Total Amount (INR)', $batch['total_amount'] ?? '']);
        fputcsv($output, ['Total Records', $batch['total_records'] ?? '']);
        fputcsv($output, ['Approved At', $batch['approved_at'] ?? '']);
        fputcsv($output, ['Processed At', $batch['processed_at'] ?? '']);
        fputcsv($output, ['Disbursement Reference', $batch['disbursement_reference'] ?? '']);
        $notes = isset($batch['processed_notes']) ? preg_replace("/\r?\n/", ' ', (string)$batch['processed_notes']) : '';
        fputcsv($output, ['Processed Notes', $notes]);

        fputcsv($output, []);
        fputcsv($output, ['Approvals']);
        fputcsv($output, ['Approver Name', 'Approver Email', 'Decision', 'Notes', 'Updated At']);
        if (!empty($batch['approvals'])) {
            foreach ($batch['approvals'] as $approval) {
                $approvalNotes = isset($approval['notes']) ? preg_replace("/\r?\n/", ' ', (string)$approval['notes']) : '';
                fputcsv($output, [
                    $approval['approver_name'] ?? ('User #' . ($approval['approver_user_id'] ?? '')),
                    $approval['approver_email'] ?? '',
                    $approval['status'] ?? '',
                    $approvalNotes,
                    $approval['updated_at'] ?? $approval['created_at'] ?? '',
                ]);
            }
        }

        fputcsv($output, []);
        fputcsv($output, ['Commission Items']);
        fputcsv($output, ['Commission ID', 'Beneficiary Name', 'Beneficiary Email', 'Amount (INR)', 'Status', 'Created At']);
        foreach ($items as $item) {
            fputcsv($output, [
                $item['commission_id'] ?? '',
                $item['beneficiary_name'] ?? '',
                $item['beneficiary_email'] ?? '',
                $item['amount'] ?? '',
                $item['status'] ?? '',
                $item['created_at'] ?? '',
            ]);
        }

        fclose($output);
    }

    private function ensureAdmin(): void
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . BASE_URL . 'admin/');
            exit();
        }
    }

    private function parseFilters(array $input): array
    {
        $filters = [];

        if (!empty($input['status'])) {
            $filters['status'] = $input['status'];
        }
        if (!empty($input['date_from'])) {
            $filters['date_from'] = $input['date_from'];
        }
        if (!empty($input['date_to'])) {
            $filters['date_to'] = $input['date_to'];
        }

        return $filters;
    }
}
