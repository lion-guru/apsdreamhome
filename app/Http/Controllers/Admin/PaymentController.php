<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Exception;

/**
 * PaymentController
 * Handles payment-related operations in the Admin panel
 */
class PaymentController extends AdminController
{
    protected $paymentModel;

    public function __construct()
    {
        parent::__construct();
        $this->paymentModel = $this->model('Payment');
    }

    /**
     * Get dashboard statistics for accounting (AJAX)
     */
    public function dashboardStats()
    {
        try {
            $stats = $this->paymentModel->getDashboardStats();
            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to fetch dashboard statistics: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of payments
     */
    public function index()
    {
        // View handles data loading via AJAX
        $this->render('admin/payments/index', [
            'page_title' => $this->mlSupport->translate('Payment Management')
        ]);
    }

    /**
     * DataTables AJAX data source
     */
    public function data()
    {
        try {
            $start = $this->request->get('start', 0);
            $length = $this->request->get('length', 10);
            $search = $this->request->get('search', [])['value'] ?? '';
            $order = $this->request->get('order', [])[0] ?? [];

            $filters = [
                'dateRange' => $this->request->get('dateRange', ''),
                'status' => $this->request->get('status', ''),
                'type' => $this->request->get('type', '')
            ];

            $payments = $this->paymentModel->getPaginatedPayments($start, $length, $search, $order, $filters);
            $totalFiltered = $this->paymentModel->getTotalPaymentsCount($search, $filters);
            $totalRecords = $this->paymentModel->getTotalPaymentsCount();

            $data = [];
            foreach ($payments as $payment) {
                $actions = '<div class="btn-group">';
                $actions .= '<button type="button" class="btn btn-sm btn-info" onclick="viewPayment(' . $payment['id'] . ')" title="View"><i class="fas fa-eye"></i></button>';
                $actions .= '<a href="/admin/payments/edit/' . $payment['id'] . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                $actions .= '<a href="/admin/payments/receipt/' . $payment['id'] . '" class="btn btn-sm btn-secondary" title="Receipt" target="_blank"><i class="fas fa-file-invoice"></i></a>';
                $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="deletePayment(' . $payment['id'] . ')" title="Delete"><i class="fas fa-trash"></i></button>';
                $actions .= '</div>';

                $statusBadge = match ($payment['status']) {
                    'completed', 'success' => '<span class="badge bg-success">Completed</span>',
                    'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                    'failed' => '<span class="badge bg-danger">Failed</span>',
                    'cancelled' => '<span class="badge bg-secondary">Cancelled</span>',
                    default => '<span class="badge bg-secondary">' . ucfirst($payment['status']) . '</span>'
                };

                $data[] = [
                    'payment_date' => date('d M Y, h:i A', strtotime($payment['payment_date'])),
                    'transaction_id' => $payment['transaction_id'] ?? 'N/A',
                    'customer_name' => $payment['customer_name'] ?? 'Unknown',
                    'payment_type' => ucfirst(str_replace('_', ' ', $payment['payment_type'])),
                    'amount' => '₹' . number_format($payment['amount'], 2),
                    'status' => $statusBadge,
                    'actions' => $actions
                ];
            }

            return $this->jsonResponse([
                'draw' => intval($this->request->get('draw', 1)),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to fetch payment data: ' . $e->getMessage());
        }
    }

    /**
     * Show payment details (AJAX)
     */
    public function show($id)
    {
        try {
            $payment = $this->paymentModel->getPaymentById($id);

            if ($payment) {
                return $this->jsonResponse([
                    'success' => true,
                    'data' => $payment
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Payment not found'
                ]);
            }
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Generate payment receipt
     */
    public function receipt($id)
    {
        $payment = $this->paymentModel->getPaymentById($id);

        if (!$payment) {
            $this->notFound();
            return;
        }

        $this->render('admin/payments/receipt', [
            'payment' => $payment,
            'title' => 'Payment Receipt #' . ($payment['transaction_id'] ?? $id)
        ]);
    }

    /**
     * Delete payment
     */
    public function destroy($id)
    {
        if ($this->request->method() !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        if (!$this->validateCsrfToken()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }

        try {
            // Check if payment exists
            $payment = $this->paymentModel->getPaymentById($id);
            if (!$payment) {
                throw new Exception('Payment not found');
            }

            $result = $this->paymentModel->deletePayment($id);

            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Payment deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete payment');
            }
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Show the form for editing a payment
     */
    public function edit($id)
    {
        $payment = $this->paymentModel->getPaymentById($id);

        if (!$payment) {
            $this->notFound();
            return;
        }

        $this->render('admin/payments/edit', [
            'payment' => $payment,
            'title' => 'Edit Payment: ' . ($payment['transaction_id'] ?? $id)
        ]);
    }

    /**
     * Update the specified payment
     */
    public function update($id)
    {
        if ($this->request->method() !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        if (!$this->validateCsrfToken()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }

        try {
            $data = $this->request->post();

            // Remove csrf_token and other non-db fields if necessary
            unset($data['csrf_token']);

            // Validate required fields
            $requiredFields = ['customer_id', 'amount', 'payment_type', 'payment_method'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . " is required");
                }
            }

            $result = $this->paymentModel->updatePayment($id, $data);

            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Payment updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update payment');
            }
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Search customers for Select2
     */
    public function customers()
    {
        $search = $this->request->get('search', '');
        $page = $this->request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $customerModel = $this->model('Customer');
        $result = $customerModel->searchCustomers($search, $limit, $offset);

        return $this->jsonResponse([
            'items' => $result['items'],
            'more' => ($offset + $limit) < $result['total']
        ]);
    }

    /**
     * Store a newly created payment
     */
    public function store()
    {
        if (!$this->validateCsrfToken()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }

        if ($this->request->method() !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        try {
            $data = $this->request->post();

            // Validate required fields
            $requiredFields = ['customer_id', 'amount', 'payment_type', 'payment_method'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . " is required");
                }
            }

            $paymentId = $this->paymentModel->recordPayment($data);

            if ($paymentId) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'payment_id' => $paymentId
                ]);
            } else {
                throw new Exception('Failed to record payment');
            }
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show create payment form (unused but kept for structure)
     */
    public function create()
    {
        $this->render('admin/payments/create', [
            'page_title' => 'Add New Payment'
        ]);
    }
}
