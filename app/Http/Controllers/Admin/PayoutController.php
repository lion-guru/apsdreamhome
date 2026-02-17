<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Exception;
use Throwable;

/**
 * PayoutController
 * Admin UI endpoints for MLM payout automation.
 */
class PayoutController extends AdminController
{
    private $payoutService;

    public function __construct()
    {
        parent::__construct();

        // Load legacy service
        require_once dirname(__DIR__, 3) . '/services/PayoutService.php';
        $this->payoutService = new \PayoutService();
    }

    public function index(): void
    {
        $this->data['page_title'] = 'MLM Payout Management';
        $this->render('admin/mlm_payouts');
    }

    public function list(): void
    {
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
        header('Content-Type: application/json');

        $batchId = (int)($_POST['batch_id'] ?? 0);
        if (!$batchId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            return;
        }

        $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
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
        header('Content-Type: application/json');

        $batchId = (int)($_POST['batch_id'] ?? 0);
        if (!$batchId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            return;
        }

        try {
            $result = $this->payoutService->disburseBatch($batchId);
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cancel(): void
    {
        header('Content-Type: application/json');

        $batchId = (int)($_POST['batch_id'] ?? 0);
        if (!$batchId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            return;
        }

        $result = $this->payoutService->cancelBatch($batchId);
        echo json_encode($result);
    }

    public function items(): void
    {
        header('Content-Type: application/json');

        $batchId = (int)($_GET['batch_id'] ?? 0);
        if (!$batchId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            return;
        }

        $items = $this->payoutService->listBatchItems($batchId);
        echo json_encode(['success' => true, 'records' => $items]);
    }

    public function export(): void
    {
        $batchId = (int)($_GET['batch_id'] ?? 0);
        if (!$batchId) {
            $this->redirect('admin/mlm-payouts');
            return;
        }

        $items = $this->payoutService->listBatchItems($batchId);
        $filename = 'payout_batch_' . $batchId . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $filename);

        $out = fopen('php://output', 'w');
        if (!empty($items)) {
            fputcsv($out, array_keys($items[0]));
            foreach ($items as $item) {
                fputcsv($out, $item);
            }
        }
        fclose($out);
        exit;
    }

    private function parseFilters(array $input): array
    {
        return [
            'status' => $input['status'] ?? null,
            'date_from' => $input['date_from'] ?? null,
            'date_to' => $input['date_to'] ?? null,
            'batch_reference' => $input['batch_reference'] ?? null,
        ];
    }
}
