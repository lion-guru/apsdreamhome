<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Payment Controller - Custom MVC Implementation
 * Handles payment-related operations in the Admin panel
 */
class PaymentController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'processPayment', 'refundPayment']]);
    }

    /**
     * Display payments list
     */
    public function index()
    {
        $this->requireAdmin();
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $method = $_GET['method'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT p.*, b.booking_number, c.name as customer_name, c.email as customer_email, pr.title as property_title"
                 . " FROM booking_payments p"
                 . " LEFT JOIN bookings b ON p.booking_id = b.id"
                 . " LEFT JOIN users c ON b.customer_id = c.id"
                 . " LEFT JOIN properties pr ON b.property_id = pr.id"
                 . " WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (b.booking_number LIKE ? OR c.name LIKE ? OR c.email LIKE ? OR p.transaction_id LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($method)) {
                $sql .= " AND p.payment_method = ?";
                $params[] = $method;
            }

            $sql .= " ORDER BY p.payment_date DESC";

            // Count total
            $countSql = str_replace("SELECT p.*, b.booking_number, c.name as customer_name, c.email as customer_email, pr.title as property_title", "SELECT COUNT(DISTINCT p.payment_id) as total", $sql);
            $countSql = str_replace("ORDER BY p.payment_date DESC", "", $countSql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'] ?? 0;

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $payments = $stmt->fetchAll();

            $data = [
                'page_title' => 'Payments - APS Dream Home',
                'active_page' => 'payments',
                'payments' => $payments,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'method' => $method
                ]
            ];

            return $this->render('admin/payments/index', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Payment Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load payments');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Get dashboard statistics for accounting (AJAX)
     */
    public function dashboardStats()
    {
        $this->requireAdmin();
        try {
            $stats = $this->getDashboardStats();
            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Payment Dashboard Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * Display the specified payment
     */
    public function show($id)
    {
        $this->requireAdmin();
        try {
            $paymentId = intval($id);
            if ($paymentId <= 0) {
                $this->setFlash('error', 'Invalid payment ID');
                return $this->redirect('admin/payments');
            }

            // Get payment details
            $sql = "SELECT p.*, 
                           b.booking_number,
                           b.total_amount as booking_total,
                           c.name as customer_name,
                           c.email as customer_email,
                           c.phone as customer_phone,
                           pr.title as property_title,
                           pr.location as property_location
                    FROM booking_payments p
                    LEFT JOIN bookings b ON p.booking_id = b.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN properties pr ON b.property_id = pr.id
                    WHERE p.payment_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch();

            if (!$payment) {
                $this->setFlash('error', 'Payment not found');
                return $this->redirect('admin/payments');
            }

            $history = [];
            try {
                // Get payment history
                $sql = "SELECT * FROM payment_history 
                        WHERE payment_id = ? 
                        ORDER BY created_at DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$paymentId]);
                $history = $stmt->fetchAll();
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
                error_log($e->getMessage());
            }

            $data = [
                'page_title' => 'Payment Details - APS Dream Home',
                'active_page' => 'payments',
                'payment' => $payment,
                'history' => $history
            ];

            return $this->render('admin/payments/show', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Payment Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load payment details');
            return $this->redirect('admin/payments');
        }
    }

    /**
     * Process payment
     */
    public function processPayment($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $paymentId = intval($id);
            $amount = (float)($_POST['amount'] ?? 0);
            $method = $_POST['payment_method'] ?? '';
            $transactionId = $_POST['transaction_id'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if ($paymentId <= 0 || $amount <= 0 || empty($method)) {
                return $this->jsonError('Invalid payment details', 400);
            }

            $this->db->beginTransaction();

            try {
                // Get payment details (booking_payments has no status column)
                $sql = "SELECT * FROM booking_payments WHERE payment_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$paymentId]);
                $payment = $stmt->fetch();

                if (!$payment) {
                    $this->db->rollBack();
                    return $this->jsonError('Payment not found or already processed', 404);
                }

                // Update payment status
                $sql = "UPDATE booking_payments 
                        SET payment_amount = ?, payment_method = ?, 
                            transaction_id = ?, payment_date = NOW(), payment_notes = ?
                        WHERE payment_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$amount, $method, $transactionId, $notes, $paymentId]);

                try {
                    // Create payment history record (best-effort; never fail the payment)
                    $sql = "INSERT INTO payment_history 
                            (payment_id, action, amount, method, transaction_id, notes, created_by, created_at)
                            VALUES (?, 'processed', ?, ?, ?, ?, ?, NOW())";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$paymentId, $amount, $method, $transactionId, $notes, $_SESSION['user_id'] ?? 0]);
                } catch (\Throwable $e) {
                    // Gracefully handle dropped table ref
                    error_log($e->getMessage());
                }

                // Update booking payment status
                $this->updateBookingPaymentStatus($payment['booking_id']);

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'payment_processed', [
                    'payment_id' => $paymentId,
                    'amount' => $amount,
                    'method' => $method
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Payment processed successfully'
                ]);
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->loggingService->error("Process Payment error: " . $e->getMessage());
            return $this->jsonError('Failed to process payment', 500);
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $paymentId = intval($id);
            $refundAmount = (float)($_POST['refund_amount'] ?? 0);
            $reason = $_POST['refund_reason'] ?? '';

            if ($paymentId <= 0 || $refundAmount <= 0 || empty($reason)) {
                return $this->jsonError('Invalid refund details', 400);
            }

            $this->db->beginTransaction();

            try {
                // Get payment details
                $sql = "SELECT * FROM booking_payments WHERE payment_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$paymentId]);
                $payment = $stmt->fetch();

                if (!$payment) {
                    $this->db->rollBack();
                    return $this->jsonError('Payment not found or not completed', 404);
                }

                if ($refundAmount > $payment['payment_amount']) {
                    $this->db->rollBack();
                    return $this->jsonError('Refund amount cannot exceed payment amount', 400);
                }

                // Update payment status (refund: record in payment_notes, no refund columns exist)
                $sql = "UPDATE booking_payments 
                        SET payment_notes = CONCAT(COALESCE(payment_notes, ''), ' Refund: ', ?, ' - ', ?)
                        WHERE payment_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$refundAmount, $reason, $paymentId]);

                try {
                    // Create payment history record (best-effort; never fail the refund)
                    $sql = "INSERT INTO payment_history 
                            (payment_id, action, amount, method, notes, created_by, created_at)
                            VALUES (?, 'refunded', ?, ?, ?, ?, NOW())";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$paymentId, $refundAmount, $payment['payment_method'], $reason, $_SESSION['user_id'] ?? 0]);
                } catch (\Throwable $e) {
                    // Gracefully handle dropped table ref
                    error_log($e->getMessage());
                }

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'payment_refunded', [
                    'payment_id' => $paymentId,
                    'refund_amount' => $refundAmount,
                    'reason' => $reason
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Payment refunded successfully'
                ]);
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->loggingService->error("Refund Payment error: " . $e->getMessage());
            return $this->jsonError('Failed to refund payment', 500);
        }
    }

    /**
     * Display payment analytics
     */
    public function analytics()
    {
        $this->requireAdmin();
        try {
            $data = [
                'page_title' => 'Payment Analytics - APS Dream Home',
                'active_page' => 'payments',
                'analytics_data' => $this->getPaymentAnalytics()
            ];

            return $this->render('admin/payments/analytics', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Payment Analytics error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load payment analytics');
            return $this->redirect('admin/payments');
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        try {
            $stats = [];

            // Total payments (booking_payments has no status column; all rows are recorded payments)
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(payment_amount), 0) as total_amount
                    FROM booking_payments";
            $result = $this->db->fetchOne($sql);
            $stats['total_payments'] = (int)($result['total'] ?? 0);
            $stats['total_amount'] = (float)($result['total_amount'] ?? 0);

            // Today's payments
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(payment_amount), 0) as total_amount
                    FROM booking_payments 
                    WHERE DATE(payment_date) = CURDATE()";
            $result = $this->db->fetchOne($sql);
            $stats['today_payments'] = (int)($result['total'] ?? 0);
            $stats['today_amount'] = (float)($result['total_amount'] ?? 0);

            // This month's payments
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(payment_amount), 0) as total_amount
                    FROM booking_payments 
                    WHERE MONTH(payment_date) = MONTH(CURRENT_DATE) 
                    AND YEAR(payment_date) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['monthly_payments'] = (int)($result['total'] ?? 0);
            $stats['monthly_amount'] = (float)($result['total_amount'] ?? 0);

            // Pending payments (no status column tracked; always zero)
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(payment_amount), 0) as total_amount
                    FROM booking_payments WHERE 1=0";
            $result = $this->db->fetchOne($sql);
            $stats['pending_payments'] = (int)($result['total'] ?? 0);
            $stats['pending_amount'] = (float)($result['total_amount'] ?? 0);

            return $stats;
        } catch (\Exception $e) {
            $this->loggingService->error("Get Dashboard Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update booking payment status
     */
    private function updateBookingPaymentStatus(int $bookingId): void
    {
        try {
            // Get total paid amount for booking
            $sql = "SELECT COALESCE(SUM(payment_amount), 0) as total_paid
                    FROM booking_payments 
                    WHERE booking_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bookingId]);
            $totalPaid = $stmt->fetch()['total_paid'];

            // Get booking total amount
            $sql = "SELECT total_amount FROM bookings WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bookingId]);
            $bookingTotal = $stmt->fetch()['total_amount'];

            // Update booking payment status
            if ($totalPaid >= $bookingTotal) {
                $paymentStatus = 'paid';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            $sql = "UPDATE bookings SET payment_status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$paymentStatus, $bookingId]);
        } catch (\Exception $e) {
            $this->loggingService->error("Update Booking Payment Status error: " . $e->getMessage());
        }
    }

    /**
     * Get payment analytics
     */
    private function getPaymentAnalytics(): array
    {
        try {
            $analytics = [];

            // Payment trends (last 30 days)
            $sql = "SELECT DATE(payment_date) as date, COUNT(*) as count, COALESCE(SUM(payment_amount), 0) as total
                    FROM booking_payments
                    WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(payment_date)
                    ORDER BY date DESC";
            $analytics['trends'] = $this->db->fetchAll($sql) ?: [];

            // Payment methods distribution
            $sql = "SELECT payment_method, COUNT(*) as count, COALESCE(SUM(payment_amount), 0) as total
                    FROM booking_payments
                    WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY payment_method
                    ORDER BY total DESC";
            $analytics['methods'] = $this->db->fetchAll($sql) ?: [];

            // Top paying users
            $sql = "SELECT c.name, c.email, COUNT(p.payment_id) as payment_count, COALESCE(SUM(p.payment_amount), 0) as total_paid
                    FROM booking_payments p
                    JOIN bookings b ON p.booking_id = b.id
                    JOIN users c ON b.customer_id = c.id
                    GROUP BY c.id
                    ORDER BY total_paid DESC
                    LIMIT 10";
            $analytics['top_customers'] = $this->db->fetchAll($sql) ?: [];

            // Payment status distribution (no status column; report recorded payments as completed)
            $sql = "SELECT 'completed' as status, COUNT(*) as count, COALESCE(SUM(payment_amount), 0) as total
                    FROM booking_payments";
            $analytics['status_distribution'] = $this->db->fetchAll($sql) ?: [];

            return $analytics;
        } catch (\Exception $e) {
            $this->loggingService->error("Get Payment Analytics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export payment data
     */
    public function export()
    {
        $this->requireAdmin();
        try {
            $format = $_GET['format'] ?? 'csv';
            $type = $_GET['type'] ?? 'all';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');

            switch ($type) {
                case 'completed':
                    $data = $this->getCompletedPayments($startDate, $endDate);
                    break;
                case 'pending':
                    $data = $this->getPendingPayments($startDate, $endDate);
                    break;
                case 'refunded':
                    $data = $this->getRefundedPayments($startDate, $endDate);
                    break;
                default:
                    $data = $this->getAllPayments($startDate, $endDate);
            }

            if ($format === 'csv') {
                return $this->exportCSV($data, $type, $startDate, $endDate);
            } elseif ($format === 'json') {
                return $this->exportJSON($data, $type, $startDate, $endDate);
            }

            $this->setFlash('error', 'Invalid export format');
            return $this->redirect('admin/payments');
        } catch (\Exception $e) {
            $this->loggingService->error("Payment Export error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to export data');
            return $this->redirect('admin/payments');
        }
    }

    /**
     * Get all payments for export
     */
    private function getAllPayments(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT p.*, b.booking_number, c.name as customer_name, c.email as customer_email,
                           pr.title as property_title
                    FROM booking_payments p
                    LEFT JOIN bookings b ON p.booking_id = b.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN properties pr ON b.property_id = pr.id
                    WHERE p.payment_date BETWEEN ? AND ?
                    ORDER BY p.payment_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            $this->loggingService->error("Get All Payments error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get completed payments for export
     */
    private function getCompletedPayments(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT p.*, b.booking_number, c.name as customer_name, c.email as customer_email,
                           pr.title as property_title
                    FROM booking_payments p
                    LEFT JOIN bookings b ON p.booking_id = b.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN properties pr ON b.property_id = pr.id
                    WHERE p.payment_date BETWEEN ? AND ?
                    ORDER BY p.payment_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            $this->loggingService->error("Get Completed Payments error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending payments for export
     */
    private function getPendingPayments(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT p.*, b.booking_number, c.name as customer_name, c.email as customer_email,
                           pr.title as property_title
                    FROM booking_payments p
                    LEFT JOIN bookings b ON p.booking_id = b.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN properties pr ON b.property_id = pr.id
                    WHERE 1=0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            $this->loggingService->error("Get Pending Payments error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get refunded payments for export
     */
    private function getRefundedPayments(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT p.*, b.booking_number, c.name as customer_name, c.email as customer_email,
                           pr.title as property_title
                    FROM booking_payments p
                    LEFT JOIN bookings b ON p.booking_id = b.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN properties pr ON b.property_id = pr.id
                    WHERE 1=0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            $this->loggingService->error("Get Refunded Payments error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export data as CSV
     */
    private function exportCSV(array $data, string $type, string $startDate, string $endDate): void
    {
        $filename = "payments_{$type}_{$startDate}_to_{$endDate}.csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            // Header row
            fputcsv($output, array_keys($data[0]));

            // Data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Export data as JSON
     */
    private function exportJSON(array $data, string $type, string $startDate, string $endDate): void
    {
        $filename = "payments_{$type}_{$startDate}_to_{$endDate}.json";

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo json_encode([
            'type' => $type,
            'period' => ['start' => $startDate, 'end' => $endDate],
            'data' => $data,
            'exported_at' => date('Y-m-d H:i:s')
        ]);

        exit;
    }

    /**
     * Printable A4 Payment Receipt
     * Works for both admin and customer portals
     */
    public function printReceipt($id)
    {
        $this->requireAdmin();
        try {
            $paymentId = intval($id);
            if ($paymentId <= 0) {
                $this->setFlash('error', 'Invalid payment ID');
                return $this->redirect('admin/payments');
            }

            // Get payment details with booking and customer info
            $sql = "SELECT p.*, 
                           b.booking_number,
                           b.total_amount as booking_total,
                           b.plot_id,
                           c.name as customer_name,
                           c.email as customer_email,
                           c.phone as customer_phone,
                           c.address as customer_address,
                           pr.title as property_title,
                           pr.location as property_location,
                           pl.plot_number,
                           pl.area_sqft
                    FROM booking_payments p
                    LEFT JOIN bookings b ON p.booking_id = b.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN properties pr ON b.property_id = pr.id
                    LEFT JOIN plots pl ON b.plot_id = pl.id
                    WHERE p.payment_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch();

            if (!$payment) {
                $this->setFlash('error', 'Payment not found');
                return $this->redirect('admin/payments');
            }

            // Get company details
            $company = [
                'name' => 'APS Dream Home',
                'tagline' => 'Real Estate & Developers',
                'address' => 'Gorakhpur, Uttar Pradesh, India',
                'gstin' => '09AABCA1234A1Z5',
                'phone' => '+91-9876543210',
                'email' => 'info@apsdreamhome.com',
                'website' => 'apsdreamhome.com'
            ];

            // Get receipt number (use transaction_id or generate)
            $receiptNo = $payment['transaction_id'] ?? 'RC-' . str_pad($paymentId, 8, '0', STR_PAD_LEFT);

            // Get admin who processed
            $processedBy = $_SESSION['user_name'] ?? 'System';

            // Calculate amount in words
            $amountInWords = $this->numberToWords($payment['payment_amount']);

            $data = [
                'page_title' => 'Payment Receipt - APS Dream Home',
                'active_page' => 'payments',
                'payment' => $payment,
                'company' => $company,
                'receipt_no' => $receiptNo,
                'processed_by' => $processedBy,
                'amount_in_words' => $amountInWords,
                'print_mode' => true
            ];

            return $this->render('admin/payments/receipt_a4', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Print Receipt error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to generate receipt');
            return $this->redirect('admin/payments');
        }
    }

    /**
     * Convert number to Indian rupees words
     */
    private function numberToWords(float $number): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        $num = (int)round($number);
        if ($num == 0) return 'Zero Rupees Only';
        
        $result = '';
        
        // Crores
        if ($num >= 10000000) {
            $crore = (int)($num / 10000000);
            $result .= $this->convertHundreds($crore) . ' Crore ';
            $num %= 10000000;
        }
        
        // Lakhs
        if ($num >= 100000) {
            $lakh = (int)($num / 100000);
            $result .= $this->convertHundreds($lakh) . ' Lakh ';
            $num %= 100000;
        }
        
        // Thousands
        if ($num >= 1000) {
            $thousand = (int)($num / 1000);
            $result .= $this->convertHundreds($thousand) . ' Thousand ';
            $num %= 1000;
        }
        
        // Hundreds
        if ($num >= 100) {
            $hundred = (int)($num / 100);
            $result .= $ones[$hundred] . ' Hundred ';
            $num %= 100;
        }
        
        // Tens and ones
        if ($num > 0) {
            if ($num < 20) {
                $result .= $ones[$num] . ' ';
            } else {
                $result .= $tens[(int)($num / 10)] . ' ';
                $result .= $ones[$num % 10] . ' ';
            }
        }
        
        return trim($result) . ' Rupees Only';
    }
    
    private function convertHundreds(int $num): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        $result = '';
        if ($num >= 100) {
            $result .= $ones[(int)($num / 100)] . ' Hundred ';
            $num %= 100;
        }
        if ($num < 20) {
            $result .= $ones[$num] . ' ';
        } else {
            $result .= $tens[(int)($num / 10)] . ' ';
            $result .= $ones[$num % 10] . ' ';
        }
        return trim($result);
    }
}
