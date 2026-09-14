<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\Pdf\PdfService;

class CustomerPassbookController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ensure customer is logged in. Redirect to login if not.
     */
    private function requireCustomer(): void
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }

        $allowedRoles = ['', 'customer', 'user'];
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, $allowedRoles, true)) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    /**
     * Get the PDO connection.
     */
    private function pdo(): \PDO
    {
        return $this->db->getConnection();
    }

    /**
     * Format amount in Indian currency style with ₹ symbol.
     */
    private function formatCurrency(float $amount): string
    {
        $decimals = ($amount == (int)$amount) ? 0 : 2;
        return '₹' . number_format($amount, $decimals, '.', ',');
    }

    /**
     * GET /customer/passbook
     *
     * Display the customer passbook with booking details, payment ledger,
     * and summary statistics.
     */
    public function passbook(): void
    {
        $this->requireCustomer();

        $customerId = (int)($_SESSION['user_id']);
        $pdo = $this->pdo();
        [$tWhere, $tParams] = $this->tenantWhere();

        $bookings = [];
        $ledger = [];
        $stats = [
            'total_investment'  => 0,
            'total_paid'        => 0,
            'total_outstanding' => 0,
            'next_emi_date'     => null,
        ];
        $paymentSchedules = [];
        $activeBookingId = (int)($_GET['booking_id'] ?? 0);

        try {
            // Fetch all active bookings for this customer
            $sql = "SELECT pb.*, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                           p.facing, p.corner_plot,
                           c.name as colony_name, c.slug as colony_slug
                    FROM plot_bookings pb
                    JOIN plots p ON pb.plot_id = p.id
                    JOIN colonies c ON pb.colony_id = c.id
                    WHERE pb.customer_id = ?
                    AND pb.status NOT IN ('cancelled')
                    {$tWhere}
                    ORDER BY pb.created_at DESC";

            $params = array_merge([$customerId], $tParams);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Compute payment summary for each booking
            foreach ($bookings as &$booking) {
                $bookingId = (int)$booking['id'];

                // Payment aggregates
                $payStmt = $pdo->prepare(
                    "SELECT
                        COALESCE(SUM(paid_amount), 0) as total_paid,
                        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_installments,
                        COUNT(*) as total_installments,
                        MIN(CASE WHEN status = 'pending' THEN due_date END) as next_due,
                        MAX(CASE WHEN status = 'overdue' THEN DATEDIFF(NOW(), due_date) ELSE 0 END) as overdue_days
                     FROM booking_payment_schedules
                     WHERE booking_id = ?"
                );
                $payStmt->execute([$bookingId]);
                $paySummary = $payStmt->fetch(\PDO::FETCH_ASSOC);

                $booking['total_paid'] = (float)($paySummary['total_paid'] ?? 0);
                $booking['paid_installments'] = (int)($paySummary['paid_installments'] ?? 0);
                $booking['total_installments'] = (int)($paySummary['total_installments'] ?? 0);
                $booking['pending_installments'] = $booking['total_installments'] - $booking['paid_installments'];
                $booking['next_due'] = $paySummary['next_due'] ?? null;
                $booking['overdue_days'] = (int)($paySummary['overdue_days'] ?? 0);
                $booking['plot_price'] = (float)($booking['total_plot_value'] ?? $booking['total_amount'] ?? 0);
                $booking['outstanding'] = $booking['plot_price'] - $booking['total_paid'];

                // Accumulate global stats
                $stats['total_investment'] += $booking['plot_price'];
                $stats['total_paid'] += $booking['total_paid'];
                $stats['total_outstanding'] += max(0, $booking['outstanding']);

                if ($booking['next_due'] && ($stats['next_emi_date'] === null || $booking['next_due'] < $stats['next_emi_date'])) {
                    $stats['next_emi_date'] = $booking['next_due'];
                }
            }
            unset($booking);

            // If no active booking selected, pick the first one
            if ($activeBookingId <= 0 && !empty($bookings)) {
                $activeBookingId = (int)$bookings[0]['id'];
            }

            // Fetch payment schedules for the active booking
            if ($activeBookingId > 0) {
                $schedStmt = $pdo->prepare(
                    "SELECT * FROM booking_payment_schedules WHERE booking_id = ? ORDER BY installment_no ASC"
                );
                $schedStmt->execute([$activeBookingId]);
                $paymentSchedules = $schedStmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Build the ledger from payment schedules + payments table
            if ($activeBookingId > 0) {
                // Fetch paid schedule entries for descriptions
                $paidStmt = $pdo->prepare(
                    "SELECT * FROM booking_payment_schedules
                     WHERE booking_id = ? AND status = 'paid'
                     ORDER BY due_date ASC"
                );
                $paidStmt->execute([$activeBookingId]);
                $paidEntries = $paidStmt->fetchAll(\PDO::FETCH_ASSOC);

                // Fetch payment records linked to this booking
                $paymentSql = "SELECT p.*, p.emi_month
                     FROM payments p
                     WHERE p.booking_id = ?{$tWhere}
                     ORDER BY p.created_at ASC";
                $paymentParams = array_merge([$activeBookingId], $tParams);
                $paymentStmt = $pdo->prepare($paymentSql);
                $paymentStmt->execute($paymentParams);
                $paymentRecords = $paymentStmt->fetchAll(\PDO::FETCH_ASSOC);

                // Merge payment records and schedule entries into a unified ledger
                $rawLedger = [];

                foreach ($paidEntries as $entry) {
                    $rawLedger[] = [
                        'date'        => $entry['paid_date'] ?? $entry['due_date'],
                        'description' => 'EMI Installment #' . $entry['installment_no'],
                        'debit'       => (float)($entry['paid_amount'] ?? 0),
                        'credit'      => 0,
                        'status'      => 'paid',
                        'sort_date'   => $entry['paid_date'] ?? $entry['due_date'],
                        'type'        => 'schedule',
                    ];
                }

                foreach ($paymentRecords as $rec) {
                    $gatewayLabel = $rec['gateway'] ?? 'N/A';
                    $emiLabel = !empty($rec['emi_month']) ? ' (EMI #' . $rec['emi_month'] . ')' : '';
                    $rawLedger[] = [
                        'date'        => $rec['created_at'],
                        'description' => 'Payment via ' . $gatewayLabel . $emiLabel,
                        'debit'       => (float)($rec['total_amount'] ?? $rec['amount'] ?? 0),
                        'credit'      => 0,
                        'status'      => $rec['status'] ?? 'completed',
                        'sort_date'   => $rec['created_at'],
                        'type'        => 'payment',
                        'receipt_available' => true,
                        'payment_id'  => (int)$rec['id'],
                    ];
                }

                // Sort by date ascending
                usort($rawLedger, function ($a, $b) {
                    return strtotime($a['sort_date']) - strtotime($b['sort_date']);
                });

                // Compute running balance
                $runningBalance = 0;
                foreach ($rawLedger as &$row) {
                    $runningBalance += $row['debit'];
                    $row['running_balance'] = $runningBalance;
                    if (!isset($row['receipt_available'])) {
                        $row['receipt_available'] = ($row['debit'] > 0);
                    }
                }
                unset($row);

                $ledger = $rawLedger;
            }
        } catch (\Throwable $e) {
            error_log('CustomerPassbookController::passbook error: ' . $e->getMessage());
        }

        $this->render('pages/user/passbook', [
            'page_title'       => 'My Passbook',
            'bookings'         => $bookings,
            'activeBookingId'  => $activeBookingId,
            'ledger'           => $ledger,
            'stats'            => $stats,
            'paymentSchedules' => $paymentSchedules,
        ]);
    }

    /**
     * POST /customer/pay-emi/upi-qr (AJAX/JSON)
     *
     * Generate a UPI QR code for EMI payment.
     */
    public function generateUpiQr(): void
    {
        $this->requireCustomer();

        $customerId = (int)($_SESSION['user_id']);
        $pdo = $this->pdo();
        [$tWhere, $tParams] = $this->tenantWhere();

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $installmentNo = (int)($_POST['installment_no'] ?? 0);

        // Validate inputs
        if ($bookingId <= 0 || $amount <= 0 || $installmentNo <= 0) {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Invalid booking, amount, or installment number.',
            ], 400);
            return;
        }

        try {
            // Verify booking belongs to this customer
            $bookStmt = $pdo->prepare(
                "SELECT pb.id, p.plot_number
                 FROM plot_bookings pb
                 JOIN plots p ON pb.plot_id = p.id
                 WHERE pb.id = ? AND pb.customer_id = ? AND pb.status NOT IN ('cancelled')
                 {$tWhere}"
            );
            $bookParams = array_merge([$bookingId, $customerId], $tParams);
            $bookStmt->execute($bookParams);
            $booking = $bookStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->jsonResponse([
                    'success' => false,
                    'error'   => 'Booking not found or does not belong to you.',
                ], 404);
                return;
            }

            // Verify installment exists and is pending
            $instStmt = $pdo->prepare(
                "SELECT * FROM booking_payment_schedules
                 WHERE booking_id = ? AND installment_no = ? AND status = 'pending'"
            );
            $instStmt->execute([$bookingId, $installmentNo]);
            $installment = $instStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$installment) {
                $this->jsonResponse([
                    'success' => false,
                    'error'   => 'Installment not found or already paid.',
                ], 404);
                return;
            }

            $plotNumber = $booking['plot_number'] ?? 'N/A';
            $timestamp = date('YmdHis');
            $txnRef = 'TXN' . $timestamp;

            // Build UPI string
            $upiString = sprintf(
                'upi://pay?pa=%s&pn=%s&am=%.2f&cu=INR&tn=%s&tr=%s',
                'apsdreamhome@icici',
                'APS Dream Home',
                $amount,
                'EMI-' . $plotNumber . '-Inst-' . $installmentNo,
                $txnRef
            );

            // Generate QR code via qrserver.com (free, no API key)
            $upiVpa = 'apsdreamhome@icici';
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($upiString);

            $this->jsonResponse([
                'success'        => true,
                'qr_image'       => $qrUrl,
                'upi_link'       => $upiString,
                'upi_id'         => $upiVpa,
                'amount'         => $amount,
                'formatted_amount' => $this->formatCurrency($amount),
                'installment_no' => $installmentNo,
                'plot_number'    => $plotNumber,
                'txn_ref'        => $txnRef,
                'due_date'       => $installment['due_date'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('CustomerPassbookController::generateUpiQr error: ' . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error'   => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * GET /customer/receipt/{id}
     *
     * Download or display a payment receipt as PDF (or HTML fallback).
     */
    public function downloadReceipt(int $paymentId): void
    {
        $this->requireCustomer();

        $customerId = (int)($_SESSION['user_id']);
        $pdo = $this->pdo();
        $paymentId = (int)$paymentId;

        if ($paymentId <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid payment ID.'], 400);
            return;
        }

        try {
            // Fetch payment record and verify it belongs to the customer
            $payStmt = $pdo->prepare(
                "SELECT p.*, bps.installment_no, bps.booking_id,
                        pb.customer_id, pb.plot_id, pb.colony_id,
                        p2.plot_number, p2.block,
                        c.name as colony_name
                 FROM payments p
                 LEFT JOIN booking_payment_schedules bps
                     ON bps.booking_id = p.booking_id AND bps.installment_no = p.emi_month
                 JOIN plot_bookings pb ON pb.id = p.booking_id
                 JOIN plots p2 ON pb.plot_id = p2.id
                 JOIN colonies c ON pb.colony_id = c.id
                 WHERE p.id = ? AND pb.customer_id = ?"
            );
            $payStmt->execute([$paymentId, $customerId]);
            $payment = $payStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$payment) {
                // Try fetching from booking_payment_schedules if payments table has no record
                $schedStmt = $pdo->prepare(
                    "SELECT bps.*, pb.customer_id, pb.plot_id, pb.colony_id,
                            p2.plot_number, p2.block,
                            c.name as colony_name
                     FROM booking_payment_schedules bps
                     JOIN plot_bookings pb ON pb.id = bps.booking_id
                     JOIN plots p2 ON pb.plot_id = p2.id
                     JOIN colonies c ON pb.colony_id = c.id
                     WHERE bps.id = ? AND pb.customer_id = ? AND bps.status = 'paid'"
                );
                $schedStmt->execute([$paymentId, $customerId]);
                $payment = $schedStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$payment) {
                    $this->jsonResponse(['success' => false, 'error' => 'Payment record not found.'], 404);
                    return;
                }
            }

            // Fetch customer name
            $userStmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
            $userStmt->execute([$customerId]);
            $user = $userStmt->fetch(\PDO::FETCH_ASSOC);
            $customerName = $user['name'] ?? 'Customer';

            // Build receipt data
            $receiptNumber = 'REC-' . str_pad($paymentId, 6, '0', STR_PAD_LEFT) . '-' . date('Ym');
            $receiptData = [
                'receipt_number' => $receiptNumber,
                'date'           => $payment['paid_date'] ?? $payment['created_at'] ?? date('Y-m-d'),
                'customer_name'  => $customerName,
                'plot_number'    => $payment['plot_number'] ?? 'N/A',
                'block'          => $payment['block'] ?? '',
                'colony_name'    => $payment['colony_name'] ?? 'N/A',
                'amount'         => (float)($payment['paid_amount'] ?? $payment['amount'] ?? 0),
                'formatted_amount' => $this->formatCurrency((float)($payment['paid_amount'] ?? $payment['amount'] ?? 0)),
                'payment_mode'   => $payment['gateway'] ?? $payment['payment_mode'] ?? 'N/A',
                'transaction_ref' => $payment['gateway_transaction_id'] ?? $payment['transaction_ref'] ?? '',
                'booking_number' => 'BK-' . str_pad((int)($payment['booking_id'] ?? 0), 6, '0', STR_PAD_LEFT),
                'installment_no' => $payment['installment_no'] ?? $payment['emi_month'] ?? '',
            ];

            // Try to generate PDF via PdfService
            try {
                $pdfService = new PdfService();
                $result = $pdfService->generate(PdfService::TYPE_RECEIPT, $paymentId);

                if ($result['success'] && !empty($result['data']['path']) && file_exists($result['data']['path'])) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="' . $receiptNumber . '.pdf"');
                    header('Content-Length: ' . filesize($result['data']['path']));
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    readfile($result['data']['path']);
                    exit;
                }
            } catch (\Throwable $e) {
                error_log('CustomerPassbookController::downloadReceipt PDF error: ' . $e->getMessage());
                // Fall through to HTML receipt
            }

            // HTML fallback receipt
            $this->renderReceiptHtml($receiptData);
        } catch (\Throwable $e) {
            error_log('CustomerPassbookController::downloadReceipt error: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Failed to generate receipt.'], 500);
        }
    }

    /**
     * GET /customer/registry/{bookingId}
     *
     * Customer live registry & handover tracker — 7-stage timeline.
     * Resolves the canonical `bookings` registry row (falls back to the
     * customer's `plot_bookings` row when the registry file is not yet opened).
     */
    public function registryTimeline(int $bookingId): void
    {
        $this->requireCustomer();

        $customerId = (int)($_SESSION['user_id'] ?? 0);
        $bookingId = (int)$bookingId;
        if ($bookingId <= 0 || $customerId <= 0) {
            $_SESSION['flash_error'] = 'Invalid booking reference.';
            $this->redirect('/customer/passbook');
            return;
        }

        $resolved = $this->resolveRegistryBooking($bookingId, $customerId);
        if (!$resolved) {
            $_SESSION['flash_error'] = 'Booking not found or does not belong to you.';
            $this->redirect('/customer/passbook');
            return;
        }

        $stages = $this->buildRegistryStages($resolved);
        $completed = 0;
        $currentIndex = -1;
        foreach ($stages as $i => $s) {
            if (($s['status'] ?? '') === 'completed') {
                $completed++;
            } elseif ($currentIndex === -1) {
                $currentIndex = $i;
            }
        }
        $totalStages = count($stages) > 0 ? count($stages) : 1;
        $progressPct = round(($completed / $totalStages) * 100, 1);

        $this->render('customer/registry_timeline', [
            'page_title'      => 'Track Registry Status',
            'booking'         => $resolved['booking'],
            'registry_source' => $resolved['source'],
            'canonical_id'    => (int)$resolved['canonical_id'],
            'stages'          => $stages,
            'completed_count' => $completed,
            'current_index'   => $currentIndex,
            'progress_pct'    => $progressPct,
            'deed'            => $resolved['deed'] ?? null,
            'can_download'    => (bool)($resolved['can_download'] ?? false),
        ]);
    }

    /**
     * GET /customer/possession-certificate/{bookingId}
     *
     * Streams the branded Plot Possession & Handover Certificate PDF.
     * Gated on handed_over possession OR completed registry.
     */
    public function downloadPossessionCertificate(int $bookingId): void
    {
        $this->requireCustomer();

        $customerId = (int)($_SESSION['user_id'] ?? 0);
        $bookingId = (int)$bookingId;
        if ($bookingId <= 0 || $customerId <= 0) {
            $_SESSION['flash_error'] = 'Invalid booking reference.';
            $this->redirect('/customer/passbook');
            return;
        }

        $resolved = $this->resolveRegistryBooking($bookingId, $customerId);
        if (!$resolved || ($resolved['source'] ?? '') !== 'bookings') {
            $_SESSION['flash_error'] = 'Possession certificate is not available for this booking yet.';
            $this->redirect('/customer/passbook');
            return;
        }

        if (empty($resolved['can_download'])) {
            $_SESSION['flash_error'] = 'Possession certificate unlocks after physical handover (Stage 7).';
            $this->redirect('/customer/registry/' . (int)$resolved['canonical_id']);
            return;
        }

        try {
            $pdfService = new PdfService();
            $result = $pdfService->generate(PdfService::TYPE_POSSESSION, (int)$resolved['canonical_id']);
            if (!empty($result['success']) && !empty($result['data']['path']) && file_exists($result['data']['path'])) {
                $filename = 'Possession-Certificate-' . (int)$resolved['canonical_id'] . '.pdf';
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($result['data']['path']));
                header('Cache-Control: private, max-age=0, must-revalidate');
                readfile($result['data']['path']);
                exit;
            }
            error_log('CustomerPassbookController::downloadPossessionCertificate PDF failed: ' . json_encode($result['error'] ?? 'unknown'));
        } catch (\Throwable $e) {
            error_log('CustomerPassbookController::downloadPossessionCertificate error: ' . $e->getMessage());
        }

        $_SESSION['flash_error'] = 'Could not generate the certificate right now. Please try again.';
        $this->redirect('/customer/registry/' . (int)$resolved['canonical_id']);
    }

    /**
     * Resolve a customer-visible booking id to its canonical registry row.
     *
     * The passbook works on `plot_bookings` ids, while the legal registry
     * journey lives on `bookings` rows (registry_status / possession_status).
     * Returns ['source' => 'bookings'|'plot_bookings', 'booking' => row,
     * 'canonical_id' => bookings.id, 'deed' => row|null, 'can_download' => bool]
     * or null when the id belongs to nobody / nobody's customer.
     */
    private function resolveRegistryBooking(int $bookingId, int $customerId): ?array
    {
        $pdo = $this->pdo();
        $tid = (int)$this->tenantId();
        $tSql = $tid > 1 ? ' AND b.tenant_id = ?' : '';
        $tParams = $tid > 1 ? [$tid] : [];

        $booking = null;
        $source = 'bookings';

        // 1) Direct hit on the canonical registry table (customer_id OR user_id).
        try {
            $stmt = $pdo->prepare(
                "SELECT b.*, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                        p.facing, c.name AS colony_name, c.slug AS colony_slug, c.location AS colony_location
                 FROM bookings b
                 LEFT JOIN plots p ON p.id = b.plot_id
                 LEFT JOIN colonies c ON c.id = COALESCE(b.colony_id, p.colony_id)
                 WHERE b.id = ? AND (b.customer_id = ? OR b.user_id = ?){$tSql}
                 LIMIT 1"
            );
            $stmt->execute(array_merge([$bookingId, $customerId, $customerId], $tParams));
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('CustomerPassbookController::resolveRegistryBooking bookings error: ' . $e->getMessage());
        }

        // 2) Fallback: a plot_bookings id from the passbook — map to its registry row.
        $plotBooking = null;
        if (!$booking) {
            try {
                $pbT = $tid > 1 ? ' AND pb.tenant_id = ?' : '';
                $stmt = $pdo->prepare(
                    "SELECT pb.*, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                            p.facing, c.name AS colony_name, c.slug AS colony_slug, c.location AS colony_location
                     FROM plot_bookings pb
                     LEFT JOIN plots p ON p.id = pb.plot_id
                     LEFT JOIN colonies c ON c.id = COALESCE(pb.colony_id, p.colony_id)
                     WHERE pb.id = ? AND pb.customer_id = ?{$pbT}
                     LIMIT 1"
                );
                $stmt->execute(array_merge([$bookingId, $customerId], $tParams));
                $plotBooking = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            } catch (\Throwable $e) {
                error_log('CustomerPassbookController::resolveRegistryBooking plot_bookings error: ' . $e->getMessage());
            }

            if (!$plotBooking) {
                return null;
            }

            // Same plot + same customer on the registry table?
            try {
                $stmt = $pdo->prepare(
                    "SELECT b.*, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                            p.facing, c.name AS colony_name, c.slug AS colony_slug, c.location AS colony_location
                     FROM bookings b
                     LEFT JOIN plots p ON p.id = b.plot_id
                     LEFT JOIN colonies c ON c.id = COALESCE(b.colony_id, p.colony_id)
                     WHERE b.plot_id = ? AND (b.customer_id = ? OR b.user_id = ?)
                       AND b.status NOT IN ('cancelled'){$tSql}
                     ORDER BY b.id DESC LIMIT 1"
                );
                $stmt->execute(array_merge([(int)$plotBooking['plot_id'], $customerId, $customerId], $tParams));
                $booking = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            } catch (\Throwable $e) {
                error_log('CustomerPassbookController::resolveRegistryBooking map error: ' . $e->getMessage());
            }

            if (!$booking) {
                $booking = $plotBooking;
                $source = 'plot_bookings';
            }
        }

        if (!$booking) {
            return null;
        }

        $canonicalId = $source === 'bookings' ? (int)$booking['id'] : (int)$bookingId;
        $extras = $source === 'bookings'
            ? $this->loadRegistryExtras((int)$booking['id'])
            : ['agreement' => null, 'noc' => null, 'deed' => null, 'possession' => null];

        $canDownload = false;
        if ($source === 'bookings') {
            $canDownload = (($booking['possession_status'] ?? '') === 'handed_over')
                || (($booking['registry_status'] ?? '') === 'completed')
                || ((($extras['possession'] ?? [])['status'] ?? '') === 'completed');
        }

        return [
            'source'       => $source,
            'booking'      => array_merge($booking, $extras),
            'canonical_id' => $canonicalId,
            'deed'         => $extras['deed'] ?? null,
            'can_download' => $canDownload,
        ];
    }

    /**
     * Load agreement / NOC / registry-deed / possession rows for a bookings id.
     * Every lookup is optional (tables may be empty) — failures degrade to null.
     */
    private function loadRegistryExtras(int $bookingsId): array
    {
        $pdo = $this->pdo();
        $out = ['agreement' => null, 'noc' => null, 'deed' => null, 'possession' => null];

        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM booking_agreements WHERE booking_id = ? ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([$bookingsId]);
            $out['agreement'] = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('CustomerPassbookController::loadRegistryExtras agreement: ' . $e->getMessage());
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM noc_requests WHERE booking_id = ? ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([$bookingsId]);
            $out['noc'] = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('CustomerPassbookController::loadRegistryExtras noc: ' . $e->getMessage());
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM booking_documents WHERE booking_id = ? AND document_type = 'registry_deed' ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([$bookingsId]);
            $out['deed'] = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('CustomerPassbookController::loadRegistryExtras deed: ' . $e->getMessage());
        }

        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM possession_records WHERE booking_id = ? ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([$bookingsId]);
            $out['possession'] = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('CustomerPassbookController::loadRegistryExtras possession: ' . $e->getMessage());
        }

        return $out;
    }

    /**
     * Build the 7-stage registry progress array from real booking signals.
     * A later completed stage back-fills earlier gaps so the stepper always
     * reads sequentially (data-gap tolerant).
     */
    private function buildRegistryStages(array $resolved): array
    {
        $b = $resolved['booking'];

        $fmtDate = function ($v) {
            if (empty($v) || $v === '0000-00-00' || $v === '0000-00-00 00:00:00') return '';
            $ts = strtotime($v);
            return $ts ? date('d M Y', $ts) : '';
        };

        $stages = [];

        // Stage 1 — Token & Allotment (a visible booking row IS the allotment).
        $s1Done = !in_array(($b['status'] ?? ''), ['pending', 'cancelled'], true);
        $stages[] = [
            'key'    => 'allotment_done',
            'label'  => 'Token & Allotment',
            'desc'   => 'Plot blocked in your name with booking number.',
            'status' => $s1Done ? 'completed' : 'in_progress',
            'meta'   => array_values(array_filter([
                !empty($b['booking_number']) ? 'Booking No: ' . $b['booking_number'] : '',
                $fmtDate($b['booking_date'] ?? $b['created_at'] ?? '') !== '' ? 'Dated: ' . $fmtDate($b['booking_date'] ?? $b['created_at']) : '',
            ])),
        ];

        // Stage 2 — Agreement for Sale.
        $ag = $b['agreement'] ?? null;
        $agStatus = $ag['status'] ?? '';
        if ($agStatus === 'signed' || (($b['esign_status'] ?? '') === 'signed') || (($b['status'] ?? '') === 'agreement_signed')) {
            $s2 = 'completed';
        } elseif ($agStatus === 'draft' || $agStatus === 'pending' || !empty($ag)) {
            $s2 = 'in_progress';
        } else {
            $s2 = 'pending';
        }
        $stages[] = [
            'key'    => 'agreement_signed',
            'label'  => 'Agreement for Sale',
            'desc'   => 'Tripartite / sale agreement executed with APS Dream Home.',
            'status' => $s2,
            'meta'   => array_values(array_filter([
                !empty($ag['agreement_type']) ? 'Type: ' . $ag['agreement_type'] : '',
                !empty($ag['signed_at']) ? 'Signed: ' . $fmtDate($ag['signed_at']) : '',
            ])),
        ];

        // Stage 3 — NOC Verification.
        $noc = $b['noc'] ?? null;
        $nocStatus = $noc['status'] ?? '';
        if ($nocStatus === 'approved') {
            $s3 = 'completed';
        } elseif (in_array($nocStatus, ['pending', 'processing', 'rejected', 'blocked'], true)) {
            $s3 = 'in_progress';
        } else {
            $s3 = 'pending';
        }
        $stages[] = [
            'key'    => 'noc_cleared',
            'label'  => 'NOC Verification',
            'desc'   => 'No-objection clearance from authority / developer.',
            'status' => $s3,
            'meta'   => array_values(array_filter([
                $nocStatus !== '' ? 'Status: ' . ucfirst($nocStatus) : '',
                !empty($noc['purpose']) ? 'Purpose: ' . $noc['purpose'] : '',
            ])),
        ];

        // Stage 4 — Stamp Duty & Valuation.
        $stamp = (float)($b['stamp_duty_amount'] ?? 0);
        $regFee = (float)($b['registration_fees'] ?? 0);
        $regStatus = $b['registry_status'] ?? '';
        if ($stamp > 0) {
            $s4 = 'completed';
        } elseif (in_array($regStatus, ['appointment_scheduled', 'registered', 'mutation_pending', 'completed'], true)) {
            $s4 = 'completed'; // Implied — later registry milestones cannot precede duty payment.
        } elseif ($regStatus === 'stamp_duty_pending') {
            $s4 = 'in_progress';
        } else {
            $s4 = 'pending';
        }
        $stages[] = [
            'key'    => 'stamp_duty_paid',
            'label'  => 'Stamp Duty & Valuation',
            'desc'   => 'Circle-rate valuation with stamp duty + registration fee.',
            'status' => $s4,
            'meta'   => array_values(array_filter([
                $stamp > 0 ? 'Stamp Duty: Rs.' . number_format($stamp, 2) : '',
                $regFee > 0 ? 'Reg. Fee: Rs.' . number_format($regFee, 2) : '',
            ])),
        ];

        // Stage 5 — Sub-Registrar Appointment.
        $appt = $b['appointment_date'] ?? '';
        if ($appt !== '' && $appt !== null) {
            $s5 = 'completed';
        } elseif (($b['registry_status'] ?? '') === 'appointment_scheduled') {
            $s5 = 'in_progress';
        } else {
            $s5 = 'pending';
        }
        $stages[] = [
            'key'    => 'appointment_scheduled',
            'label'  => 'Sub-Registrar Appointment',
            'desc'   => 'Slot at the Sub-Registrar office for execution.',
            'status' => $s5,
            'meta'   => array_values(array_filter([
                $appt !== '' && $appt !== null ? 'On: ' . date('d M Y, h:i A', strtotime($appt)) : '',
                !empty($b['sub_registrar_office']) ? 'Venue: ' . $b['sub_registrar_office'] : '',
            ])),
        ];

        // Stage 6 — Registry Executed.
        $regNo = $b['registry_number'] ?? '';
        $stages[] = [
            'key'    => 'registered',
            'label'  => 'Registry Executed',
            'desc'   => 'Sale deed registered and deed number issued.',
            'status' => ($regNo !== '' && $regNo !== null) ? 'completed' : 'pending',
            'meta'   => array_values(array_filter([
                ($regNo !== '' && $regNo !== null) ? 'Deed No: ' . $regNo : '',
                !empty($b['registry_date']) ? 'Dated: ' . $fmtDate($b['registry_date']) : '',
                !empty($resolved['deed']['file_path']) ? 'Deed copy uploaded' : '',
            ])),
        ];

        // Stage 7 — Mutation & Possession Handover.
        $pos = $b['possession'] ?? null;
        if ((($b['possession_status'] ?? '') === 'handed_over')
            || (($b['registry_status'] ?? '') === 'completed')
            || ((($pos ?? [])['status'] ?? '') === 'completed')) {
            $s7 = 'completed';
        } elseif (in_array(($b['possession_status'] ?? ''), ['ready', 'scheduled'], true)
            || ((($pos ?? [])['status'] ?? '') === 'scheduled')) {
            $s7 = 'in_progress';
        } else {
            $s7 = 'pending';
        }
        $stages[] = [
            'key'    => 'completed',
            'label'  => 'Mutation & Possession',
            'desc'   => 'Dakhil-Kharij mutation with physical plot handover.',
            'status' => $s7,
            'meta'   => array_values(array_filter([
                !empty($b['mutation_number']) ? 'Mutation No: ' . $b['mutation_number'] : '',
                !empty($b['possession_date']) ? 'Handover: ' . $fmtDate($b['possession_date']) : '',
                !empty($b['possession_letter_number']) ? 'Letter: ' . $b['possession_letter_number'] : '',
            ])),
        ];

        // Sequential normalization: a completed later stage back-fills earlier gaps.
        $maxCompleted = -1;
        foreach ($stages as $i => $s) {
            if (($s['status'] ?? '') === 'completed') $maxCompleted = $i;
        }
        for ($i = 0; $i <= $maxCompleted; $i++) {
            if (($stages[$i]['status'] ?? '') !== 'completed') {
                $stages[$i]['status'] = 'completed';
            }
        }
        // First non-completed stage becomes the pulsing current stage.
        $foundCurrent = false;
        foreach ($stages as $i => $s) {
            if (($s['status'] ?? '') !== 'completed' && !$foundCurrent) {
                $stages[$i]['status'] = 'in_progress';
                $foundCurrent = true;
            } elseif (($s['status'] ?? '') === 'in_progress' && $foundCurrent) {
                $stages[$i]['status'] = 'pending';
            }
        }

        return $stages;
    }

    /**
     * Render a styled HTML receipt as fallback when PDF generation fails.
      */
    private function renderReceiptHtml(array $data): void
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - <?= htmlspecialchars($data['receipt_number']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 20px; }
        .receipt { max-width: 700px; margin: 0 auto; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #0a192f, #1e3a5f); color: #fff; padding: 30px; text-align: center; }
        .header h1 { font-size: 22px; margin-bottom: 4px; }
        .header p { font-size: 13px; opacity: 0.85; }
        .badge { display: inline-block; background: #10b981; color: #fff; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-top: 10px; }
        .body { padding: 30px; }
        .section-title { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
        .row:last-child { border-bottom: none; }
        .label { color: #6b7280; font-size: 14px; }
        .value { font-weight: 600; font-size: 14px; color: #111827; }
        .amount-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
        .amount-box .amount { font-size: 32px; font-weight: 700; color: #059669; }
        .amount-box .label { font-size: 13px; color: #6b7280; margin-top: 4px; }
        .footer { background: #f9fafb; padding: 20px 30px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        @media print { body { background: #fff; padding: 0; } .receipt { border: none; box-shadow: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>APS Dream Home</h1>
            <p>Premium Real Estate — Payment Receipt</p>
            <div class="badge">PAID</div>
        </div>
        <div class="body">
            <div class="section-title">Receipt Details</div>
            <div class="row">
                <span class="label">Receipt Number</span>
                <span class="value"><?= htmlspecialchars($data['receipt_number']) ?></span>
            </div>
            <div class="row">
                <span class="label">Date</span>
                <span class="value"><?= htmlspecialchars($data['date']) ?></span>
            </div>
            <div class="row">
                <span class="label">Booking Number</span>
                <span class="value"><?= htmlspecialchars($data['booking_number']) ?></span>
            </div>

            <div class="section-title" style="margin-top: 20px;">Customer & Property</div>
            <div class="row">
                <span class="label">Customer Name</span>
                <span class="value"><?= htmlspecialchars($data['customer_name']) ?></span>
            </div>
            <div class="row">
                <span class="label">Colony</span>
                <span class="value"><?= htmlspecialchars($data['colony_name']) ?></span>
            </div>
            <div class="row">
                <span class="label">Plot</span>
                <span class="value"><?= htmlspecialchars($data['plot_number']) ?><?= $data['block'] ? ' (' . htmlspecialchars($data['block']) . ')' : '' ?></span>
            </div>
            <?php if (!empty($data['installment_no'])): ?>
            <div class="row">
                <span class="label">Installment</span>
                <span class="value">#<?= (int)$data['installment_no'] ?></span>
            </div>
            <?php endif; ?>

            <div class="amount-box">
                <div class="amount"><?= htmlspecialchars($data['formatted_amount']) ?></div>
                <div class="label">Amount Paid</div>
            </div>

            <div class="section-title">Payment Information</div>
            <div class="row">
                <span class="label">Payment Mode</span>
                <span class="value"><?= htmlspecialchars($data['payment_mode']) ?></span>
            </div>
            <?php if (!empty($data['transaction_ref'])): ?>
            <div class="row">
                <span class="label">Transaction Ref</span>
                <span class="value"><?= htmlspecialchars($data['transaction_ref']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="footer">
            <p>This is a computer-generated receipt. For queries, contact support@apsdreamhome.com or call +91-7007444842.</p>
            <p style="margin-top: 4px;">APS Dream Home &copy; <?= date('Y') ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
        <?php
    }
}
