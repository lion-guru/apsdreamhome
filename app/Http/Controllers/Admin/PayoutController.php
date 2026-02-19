<?php

namespace App\Http\Controllers\Admin;

use App\Services\PayoutService;
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

        $this->payoutService = new PayoutService();
    }

    public function index(): void
    {
        $this->data['page_title'] = 'MLM Payout Management';
        $this->render('admin/mlm_payouts');
    }

    public function list()
    {
        try {
            $filters = $this->parseFilters($this->request->all());
            $limit = max(1, (int)($this->request->get('limit') ?? 20));
            $offset = max(0, (int)($this->request->get('offset') ?? 0));
            $batches = $this->payoutService->listBatches($filters, $limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'records' => $batches,
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function create()
    {
        if ($this->request->method() !== 'POST') {
            return $this->jsonError('Invalid request method', 405);
        }

        if (!$this->validateCsrfToken()) {
            return $this->jsonError('Invalid CSRF token', 403);
        }

        try {
            $data = $this->request->all();
            $filters = $this->parseFilters($data);
            $filters['max_items'] = isset($data['max_items']) ? (int)$data['max_items'] : null;
            $filters['min_amount'] = isset($data['min_amount']) ? (float)$data['min_amount'] : null;
            $filters['batch_reference'] = $data['batch_reference'] ?? null;
            if (!empty($data['required_approvals'])) {
                $filters['required_approvals'] = (int) $data['required_approvals'];
            }

            $result = $this->payoutService->createBatch($filters);
            return $this->jsonResponse($result);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function approve()
    {
        if ($this->request->method() !== 'POST') {
            return $this->jsonError('Invalid request method', 405);
        }

        if (!$this->validateCsrfToken()) {
            return $this->jsonError('Invalid CSRF token', 403);
        }

        $batchId = (int)($this->request->post('batch_id') ?? 0);
        if (!$batchId) {
            return $this->jsonError('Batch ID required', 400);
        }

        $userId = (int)($this->session->get('admin_id') ?? $this->session->get('user_id') ?? 0);
        $decision = $this->request->post('decision') ?? 'approved';
        $notes = $this->request->post('notes') ?? null;

        try {
            $result = $this->payoutService->approveBatch($batchId, $userId, $decision, $notes);

            if ($result['success'] ?? false) {
                return $this->jsonResponse($result);
            } else {
                return $this->jsonError($result['message'] ?? 'Approval failed', 400);
            }
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function disburse()
    {
        if ($this->request->method() !== 'POST') {
            return $this->jsonError('Invalid request method', 405);
        }

        if (!$this->validateCsrfToken()) {
            return $this->jsonError('Invalid CSRF token', 403);
        }

        $batchId = (int)($this->request->post('batch_id') ?? 0);
        if (!$batchId) {
            return $this->jsonError('Batch ID required', 400);
        }

        try {
            $result = $this->payoutService->disburseBatch($batchId);
            return $this->jsonResponse($result);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function cancel()
    {
        if ($this->request->method() !== 'POST') {
            return $this->jsonError('Invalid request method', 405);
        }

        if (!$this->validateCsrfToken()) {
            return $this->jsonError('Invalid CSRF token', 403);
        }

        $batchId = (int)($this->request->post('batch_id') ?? 0);
        if (!$batchId) {
            return $this->jsonError('Batch ID required', 400);
        }

        try {
            $result = $this->payoutService->cancelBatch($batchId);
            return $this->jsonResponse($result);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function items()
    {
        $batchId = (int)($this->request->get('batch_id') ?? 0);
        if (!$batchId) {
            return $this->jsonError('Batch ID required', 400);
        }

        try {
            $items = $this->payoutService->listBatchItems($batchId);
            return $this->jsonResponse(['success' => true, 'records' => $items]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function export()
    {
        $batchId = (int)($this->request->get('batch_id') ?? 0);
        if (!$batchId) {
            $this->redirect('admin/mlm-payouts');
            return;
        }

        try {
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
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('admin/mlm-payouts');
            return;
        }
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
