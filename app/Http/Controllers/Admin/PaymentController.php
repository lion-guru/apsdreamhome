<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Exception;

/**
 * PaymentController
 * Handles payment-related operations in the Admin panel
 */
class PaymentController extends BaseController
{
    protected $paymentModel;

    public function __construct()
    {
        parent::__construct();

        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->paymentModel = $this->model('Payment');
    }

    /**
     * Get dashboard statistics for accounting (AJAX)
     */
    public function dashboardStats()
    {
        header('Content-Type: application/json');

        try {
            $stats = $this->paymentModel->getDashboardStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display a listing of payments
     */
    public function index()
    {
        // View handles data loading via AJAX
        $this->render('admin/payments/index', [
            'page_title' => 'Payment Management'
        ]);
    }

    /**
     * DataTables AJAX data source
     */
    public function data()
    {
        header('Content-Type: application/json');

        try {
            $start = $_GET['start'] ?? 0;
            $length = $_GET['length'] ?? 10;
            $search = $_GET['search']['value'] ?? '';
            $order = $_GET['order'][0] ?? [];

            $filters = [
                'dateRange' => $_GET['dateRange'] ?? '',
                'status' => $_GET['status'] ?? '',
                'type' => $_GET['type'] ?? ''
            ];

            $payments = $this->paymentModel->getPaginatedPayments($start, $length, $search, $order, $filters);
            $totalFiltered = $this->paymentModel->getTotalPaymentsCount($search, $filters);
            // Ideally we should get total count without filters for recordsTotal, but using filtered count is acceptable for now
            // or we can call getTotalPaymentsCount with empty args
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

            echo json_encode([
                'draw' => intval($_GET['draw'] ?? 1),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Search customers for Select2
     */
    public function customers()
    {
        header('Content-Type: application/json');

        $search = $_GET['search'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $customerModel = $this->model('Customer');
        $result = $customerModel->searchCustomers($search, $limit, $offset);

        echo json_encode([
            'items' => $result['items'],
            'more' => ($offset + $limit) < $result['total']
        ]);
    }

    /**
     * Show create payment form (if not using modal)
     */
    public function create()
    {
        // Not used currently as we use modal, but could render a dedicated page
        $this->render('admin/payments/create', [
            'page_title' => 'Add New Payment'
        ]);
    }

    /**
     * Store a newly created payment
     */
    public function store()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $data = $_POST;

            // Validate required fields
            $requiredFields = ['customer_id', 'amount', 'payment_type', 'payment_method'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . " is required");
                }
            }

            $paymentId = $this->paymentModel->recordPayment($data);

            if ($paymentId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'payment_id' => $paymentId
                ]);
            } else {
                throw new Exception('Failed to record payment');
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show edit payment form
     */
    public function edit($id)
    {
        $payment = $this->paymentModel->getPaymentById($id);
        if (!$payment) {
            $this->redirect('payments'); // Or show 404
            return;
        }

        $this->render('admin/payments/edit', [
            'payment' => $payment,
            'page_title' => 'Edit Payment'
        ]);
    }

    /**
     * Update payment
     */
    public function update($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $data = $_POST;

            // Remove csrf_token and other non-db fields
            unset($data['csrf_token']);

            // Validate required fields
            $requiredFields = ['customer_id', 'amount', 'payment_type', 'payment_method'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . " is required");
                }
            }

            if ($this->paymentModel->updatePayment($id, $data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update payment');
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete payment
     */
    public function destroy($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        if ($this->paymentModel->deletePayment($id)) {
            echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete payment']);
        }
    }

    /**
     * Show payment details
     */
    public function show($id)
    {
        $payment = $this->paymentModel->getPaymentById($id);

        if (!$payment) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Payment not found']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $payment]);
        exit;
    }

    /**
     * Generate payment receipt PDF (HTML View for now)
     */
    public function receipt($id)
    {
        $payment = $this->paymentModel->getPaymentById($id);
        if (!$payment) {
            die('Payment not found');
        }

        // Render receipt HTML
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt #' . ($payment['transaction_id'] ?? $id) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .receipt-box { border: 1px solid #ddd; padding: 20px; max-width: 600px; margin: 0 auto; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #333; }
                .header p { margin: 5px 0; color: #666; }
                .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .details-table td { padding: 10px; border-bottom: 1px solid #eee; }
                .details-table tr:last-child td { border-bottom: none; }
                .label { font-weight: bold; color: #555; width: 40%; }
                .value { color: #333; }
                .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }
                .print-btn { display: block; margin: 20px auto; padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; text-decoration: none; width: fit-content; }
                @media print { .print-btn { display: none; } .receipt-box { border: none; } }
            </style>
        </head>
        <body>
            <div class="receipt-box">
                <div class="header">
                    <h1>APS Dream Home</h1>
                    <p>Payment Receipt</p>
                </div>
                
                <table class="details-table">
                    <tr>
                        <td class="label">Receipt No:</td>
                        <td class="value">' . ($payment['transaction_id'] ?? 'N/A') . '</td>
                    </tr>
                    <tr>
                        <td class="label">Date:</td>
                        <td class="value">' . date('d M Y, h:i A', strtotime($payment['payment_date'])) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Customer:</td>
                        <td class="value">' . htmlspecialchars($payment['customer_name'] ?? 'Unknown') . '</td>
                    </tr>
                    <tr>
                        <td class="label">Amount:</td>
                        <td class="value">₹' . number_format($payment['amount'], 2) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Payment Type:</td>
                        <td class="value">' . ucfirst(str_replace('_', ' ', $payment['payment_type'])) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Payment Method:</td>
                        <td class="value">' . ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? 'N/A')) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td class="value">' . ucfirst($payment['status']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Notes:</td>
                        <td class="value">' . htmlspecialchars($payment['notes'] ?? '') . '</td>
                    </tr>
                </table>

                <div class="footer">
                    <p>This is a computer generated receipt.</p>
                    <p>Generated on ' . date('d M Y, h:i A') . '</p>
                </div>

                <a href="#" class="print-btn" onclick="window.print(); return false;">Print Receipt</a>
            </div>
        </body>
        </html>
        ';

        echo $html;
    }
}
