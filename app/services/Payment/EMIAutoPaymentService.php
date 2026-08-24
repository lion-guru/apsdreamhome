<?php

namespace App\Services\Payment;

use App\Core\Database\Database;
use App\Services\Gateway\RazorpayService;
use PDO;
use Exception;

/**
 * EMI Auto-Payment Service
 *
 * Manages Razorpay recurring mandates for automatic EMI debit.
 * - Processes due installments via mandate auto-debit
 * - Sets up / cancels mandates
 * - Tracks failed payments for manual follow-up
 *
 * All methods return ['success' => bool, ...] — never throws.
 * Test mode (RAZORPAY_TEST_MODE=true) returns mock mandate/payment IDs.
 */
class EMIAutoPaymentService
{
    use \App\Traits\ServiceTenantTrait;

    private ?PDO $db;
    private RazorpayService $razorpay;
    private bool $testMode;

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?? Database::getInstance()->getConnection();
        $this->razorpay = new RazorpayService($this->db);
        $this->testMode = $this->razorpay->isTestMode();
    }

    /* ------------------------------------------------------------------
     *  MAIN CRON METHOD
     * ------------------------------------------------------------------ */

    /**
     * Process all due EMI payments via mandates.
     *
     * @return array{success: bool, processed: int, failed: int, skipped: int, results: array}
     */
    public function processDueEmiPayments(): array
    {
        $result = ['success' => true, 'processed' => 0, 'failed' => 0, 'skipped' => 0, 'results' => []];

        try {
            $dueInstallments = $this->getDueInstallments();

            if (empty($dueInstallments)) {
                return $result;
            }

            foreach ($dueInstallments as $installment) {
                $subResult = $this->processSingleInstallment($installment);
                $result['results'][] = $subResult;

                if ($subResult['status'] === 'processed') {
                    $result['processed']++;
                } elseif ($subResult['status'] === 'failed') {
                    $result['failed']++;
                } else {
                    $result['skipped']++;
                }
            }

            $result['success'] = $result['failed'] === 0;
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::processDueEmiPayments: " . $e->getMessage());
            $result['success'] = false;
        }

        return $result;
    }

    /* ------------------------------------------------------------------
     *  MANDATE SETUP
     * ------------------------------------------------------------------ */

    /**
     * Create a Razorpay subscription (mandate) for a booking.
     *
     * @param int    $bookingId
     * @param int    $customerId
     * @param float  $amount       monthly EMI amount
     * @param string $planName     e.g. "Plot EMI - Booking APS-BK-..."
     * @return array{success: bool, subscription_id: ?string, mandate_id: ?string, error: ?string}
     */
    public function setupMandate(int $bookingId, int $customerId, float $amount, string $planName = ''): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'subscription_id' => null, 'mandate_id' => null, 'error' => 'Amount must be positive'];
        }

        try {
            // 1. Get or create Razorpay customer
            $customer = $this->getOrCreateRazorpayCustomer($customerId);
            if (!$customer['success']) {
                return ['success' => false, 'subscription_id' => null, 'mandate_id' => null, 'error' => 'Customer setup failed: ' . $customer['error']];
            }
            $razorpayCustomerId = $customer['customer_id'];

            // 2. Create plan
            $planName = $planName ?: "EMI Plan - Booking #{$bookingId}";
            $planResult = $this->razorpay->createPlan(
                ['name' => $planName, 'amount' => $amount, 'currency' => 'INR'],
                'monthly',
                1,
                ['booking_id' => $bookingId, 'customer_id' => $customerId]
            );
            if (!$planResult['success']) {
                return ['success' => false, 'subscription_id' => null, 'mandate_id' => null, 'error' => 'Plan creation failed: ' . $planResult['error']];
            }
            $planId = $planResult['data']['id'];

            // 3. Create subscription (mandate)
            $subResult = $this->razorpay->createSubscription($planId, $razorpayCustomerId, null, [
                'booking_id'   => $bookingId,
                'customer_id'  => $customerId,
                'auto_debit'   => 1,
            ]);
            if (!$subResult['success']) {
                return ['success' => false, 'subscription_id' => null, 'mandate_id' => null, 'error' => 'Subscription failed: ' . $subResult['error']];
            }

            $subscriptionId = $subResult['data']['id'] ?? null;
            $mandateId = $subResult['data']['mandate_id'] ?? $subscriptionId;

            // 4. Store mandate in DB
            $this->saveMandateRecord($bookingId, $customerId, $subscriptionId, $mandateId, $amount, $razorpayCustomerId);

            $this->logPaymentAttempt(null, 'mandate_setup', [
                'booking_id'      => $bookingId,
                'customer_id'     => $customerId,
                'subscription_id' => $subscriptionId,
                'mandate_id'      => $mandateId,
                'amount'          => $amount,
            ]);

            return ['success' => true, 'subscription_id' => $subscriptionId, 'mandate_id' => $mandateId, 'error' => null];
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::setupMandate: " . $e->getMessage());
            return ['success' => false, 'subscription_id' => null, 'mandate_id' => null, 'error' => $e->getMessage()];
        }
    }

    /* ------------------------------------------------------------------
     *  MANDATE CAPTURE (AUTO-DEBIT)
     * ------------------------------------------------------------------ */

    /**
     * Attempt auto-debit for a single subscription.
     *
     * @param string $subscriptionId
     * @param float  $amount
     * @return array{success: bool, payment_id: ?string, status: string, error: ?string}
     */
    public function captureMandatePayment(string $subscriptionId, float $amount): array
    {
        if (!$subscriptionId || $amount <= 0) {
            return ['success' => false, 'payment_id' => null, 'status' => 'invalid', 'error' => 'Invalid subscription ID or amount'];
        }

        try {
            // Razorpay subscriptions don't have a direct "capture" endpoint —
            // auto-debit is triggered by the subscription itself on renewal.
            // For manual trigger, we create an order + authorize via the mandate.
            // In test mode, simulate a successful capture.

            if ($this->testMode) {
                $paymentId = 'pay_' . bin2hex(random_bytes(7));
                $this->logPaymentAttempt(null, 'auto_debit_test', [
                    'subscription_id' => $subscriptionId,
                    'amount'          => $amount,
                    'payment_id'      => $paymentId,
                ]);
                return ['success' => true, 'payment_id' => $paymentId, 'status' => 'captured', 'error' => null];
            }

            // Real mode: create an order and attempt mandate-based authorization
            $orderResult = $this->razorpay->createOrder($amount, 'INR', 'EMI_' . $subscriptionId, [
                'subscription_id' => $subscriptionId,
                'auto_debit'      => 1,
            ]);
            if (!$orderResult['success']) {
                return ['success' => false, 'payment_id' => null, 'status' => 'order_failed', 'error' => $orderResult['error']];
            }

            // The actual mandate debit happens via Razorpay's subscription engine.
            // We log the order for tracking; the webhook handles status updates.
            $this->logPaymentAttempt(null, 'auto_debit_order', [
                'subscription_id' => $subscriptionId,
                'order_id'        => $orderResult['data']['id'] ?? null,
                'amount'          => $amount,
            ]);

            return [
                'success'    => true,
                'payment_id' => $orderResult['data']['id'] ?? null,
                'status'     => 'order_created',
                'error'      => null,
            ];
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::captureMandatePayment: " . $e->getMessage());
            return ['success' => false, 'payment_id' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }

    /* ------------------------------------------------------------------
     *  MANDATE CANCEL
     * ------------------------------------------------------------------ */

    /**
     * Cancel a Razorpay subscription/mandate.
     *
     * @param string $subscriptionId
     * @param bool   $atCycleEnd   cancel at end of current billing cycle
     * @return array{success: bool, error: ?string}
     */
    public function cancelMandate(string $subscriptionId, bool $atCycleEnd = false): array
    {
        if (!$subscriptionId) {
            return ['success' => false, 'error' => 'subscription_id required'];
        }

        try {
            $result = $this->razorpay->cancelSubscription($subscriptionId, $atCycleEnd);

            if ($result['success']) {
                // Update local DB status
                $this->db->prepare(
                    "UPDATE customer_mandates SET status = 'cancelled', updated_at = NOW() WHERE subscription_id = ?" . $this->tenantSql()
                )->execute($this->tenantId() > 1 ? [$subscriptionId, $this->tenantId()] : [$subscriptionId]);

                $this->logPaymentAttempt(null, 'mandate_cancelled', [
                    'subscription_id' => $subscriptionId,
                    'at_cycle_end'    => $atCycleEnd ? 1 : 0,
                ]);
            }

            return ['success' => $result['success'], 'error' => $result['error'] ?? null];
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::cancelMandate: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ------------------------------------------------------------------
     *  MANDATE STATUS
     * ------------------------------------------------------------------ */

    /**
     * Get mandate status from DB + Razorpay.
     *
     * @param string $subscriptionId
     * @return array{success: bool, mandate: ?array, error: ?string}
     */
    public function getMandateStatus(string $subscriptionId): array
    {
        if (!$subscriptionId) {
            return ['success' => false, 'mandate' => null, 'error' => 'subscription_id required'];
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM customer_mandates WHERE subscription_id = ?" . $this->tenantSql() . " LIMIT 1"
            );
            $stmt->execute($this->tenantId() > 1 ? [$subscriptionId, $this->tenantId()] : [$subscriptionId]);
            $mandate = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$mandate) {
                return ['success' => false, 'mandate' => null, 'error' => 'Mandate not found'];
            }

            return ['success' => true, 'mandate' => $mandate, 'error' => null];
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::getMandateStatus: " . $e->getMessage());
            return ['success' => false, 'mandate' => null, 'error' => $e->getMessage()];
        }
    }

    /* ------------------------------------------------------------------
     *  UPCOMING EMIs
     * ------------------------------------------------------------------ */

    /**
     * List next N upcoming installments for a booking.
     *
     * @param int $bookingId
     * @param int $limit
     * @return array
     */
    public function getUpcomingEmis(int $bookingId, int $limit = 3): array
    {
        try {
            $tid = $this->tenantId();
            $bpsTenant = $tid > 1 ? " AND bps.tenant_id = {$tid}" : "";
            $stmt = $this->db->prepare(
                "SELECT bps.*, pb.booking_number,
                        u.name AS customer_name, u.email AS customer_email,
                        p.plot_number
                 FROM booking_payment_schedules bps
                 JOIN plot_bookings pb ON pb.id = bps.booking_id
                 JOIN users u ON u.id = pb.customer_id
                 JOIN plots p ON p.id = pb.plot_id
                 WHERE bps.booking_id = ? AND bps.status IN ('pending','upcoming')
                 {$bpsTenant}
                 ORDER BY bps.due_date ASC
                 LIMIT ?"
            );
            $params = [$bookingId];
            if ($tid > 1) $params[] = $tid;
            $params[] = $limit;
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::getUpcomingEmis: " . $e->getMessage());
            return [];
        }
    }

    /* ------------------------------------------------------------------
     *  FAILED PAYMENTS
     * ------------------------------------------------------------------ */

    /**
     * List all failed auto-payment attempts for manual follow-up.
     *
     * @param int $limit
     * @return array
     */
    public function getFailedPayments(int $limit = 100): array
    {
        try {
            $tid = $this->tenantId();
            $glTenant = $tid > 1 ? " AND gl.tenant_id = {$tid}" : "";
            $stmt = $this->db->prepare(
                "SELECT gl.*, cm.booking_id, cm.customer_id,
                        pb.booking_number,
                        u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
                 FROM gateway_logs gl
                 LEFT JOIN customer_mandates cm ON cm.subscription_id = gl.transaction_id
                 LEFT JOIN plot_bookings pb ON pb.id = cm.booking_id
                 LEFT JOIN users u ON u.id = cm.customer_id
                 WHERE gl.gateway = 'razorpay'
                   AND gl.status = 'failed'
                   AND (gl.endpoint LIKE '%subscription%' OR gl.endpoint LIKE '%auto_debit%')
                   {$glTenant}
                 ORDER BY gl.created_at DESC
                 LIMIT ?"
            );
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::getFailedPayments: " . $e->getMessage());
            return [];
        }
    }

    /* ------------------------------------------------------------------
     *  ADMIN STATS
     * ------------------------------------------------------------------ */

    /**
     * Dashboard stats for admin view.
     */
    public function getDashboardStats(): array
    {
        $stats = [
            'total_mandates'   => 0,
            'active_mandates'  => 0,
            'failed_mandates'  => 0,
            'upcoming_emis'    => 0,
            'total_due_amount' => 0,
            'today_due'        => 0,
            'today_collected'  => 0,
        ];

        try {
            $row = $this->db->query(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed
                 FROM customer_mandates WHERE 1=1" . $this->tenantSql()
            )->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $stats['total_mandates']  = (int)$row['total'];
                $stats['active_mandates'] = (int)$row['active'];
                $stats['failed_mandates'] = (int)$row['failed'];
            }
        } catch (Exception $e) {
        // table may not exist
        error_log($e->getMessage());
        }

        try {
            $row = $this->db->query(
                "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS total_due
                 FROM booking_payment_schedules
                 WHERE status IN ('pending','upcoming') AND due_date >= CURDATE()" . $this->tenantSql()
            )->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $stats['upcoming_emis']    = (int)$row['cnt'];
                $stats['total_due_amount'] = (float)$row['total_due'];
            }
        } catch (Exception $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->query(
                "SELECT COUNT(*) AS cnt FROM booking_payment_schedules
                 WHERE status IN ('pending','upcoming') AND due_date = CURDATE()" . $this->tenantSql()
            )->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $stats['today_due'] = (int)$row['cnt'];
            }
        } catch (Exception $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->query(
                "SELECT COUNT(*) AS cnt FROM booking_payment_schedules
                 WHERE status = 'paid' AND due_date = CURDATE()" . $this->tenantSql()
            )->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $stats['today_collected'] = (int)$row['cnt'];
            }
        } catch (Exception $e) { error_log($e->getMessage()); }

        return $stats;
    }

    /**
     * List all mandates with booking/customer info for the admin table.
     */
    public function listMandates(): array
    {
        try {
            $tid = $this->tenantId();
            $whereTenant = $tid > 1 ? " WHERE cm.tenant_id = {$tid}" : "";
            $stmt = $this->db->query(
                "SELECT cm.*, pb.booking_number, pb.customer_id,
                        u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                        p.plot_number, col.name AS colony_name,
(SELECT MIN(bps.due_date) FROM booking_payment_schedules bps
                          WHERE bps.booking_id = cm.booking_id AND bps.status IN ('pending','upcoming')" . ($tid > 1 ? " AND bps.tenant_id = {$tid}" : "") . "
                         ) AS next_payment_date
                 FROM customer_mandates cm
                 LEFT JOIN plot_bookings pb ON pb.id = cm.booking_id
                 LEFT JOIN users u ON u.id = pb.customer_id
                 LEFT JOIN plots p ON p.id = pb.plot_id
                 LEFT JOIN colonies col ON col.id = p.colony_id
                 {$whereTenant}
                 ORDER BY cm.created_at DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::listMandates: " . $e->getMessage());
            return [];
        }
    }

    /* ------------------------------------------------------------------
     *  PRIVATE HELPERS
     * ------------------------------------------------------------------ */

    /**
     * Query due installments (pending, due_date <= today).
     */
    private function getDueInstallments(): array
    {
        try {
            $tid = $this->tenantId();
            $bpsTenant = $tid > 1 ? " AND bps.tenant_id = {$tid}" : "";
            $stmt = $this->db->query(
                "SELECT bps.id, bps.booking_id, bps.installment_no, bps.amount, bps.due_date,
                        bps.status,
                        pb.booking_number, pb.customer_id,
                        u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                        p.plot_number,
                        cm.subscription_id, cm.mandate_id, cm.status AS mandate_status
                 FROM booking_payment_schedules bps
                 JOIN plot_bookings pb ON pb.id = bps.booking_id
                 JOIN users u ON u.id = pb.customer_id
                 JOIN plots p ON p.id = pb.plot_id
                 LEFT JOIN customer_mandates cm ON cm.booking_id = bps.booking_id AND cm.status = 'active'
                 WHERE bps.status IN ('pending','upcoming')
                   AND bps.due_date <= CURDATE()
                   {$bpsTenant}
                 ORDER BY bps.due_date ASC, bps.booking_id ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::getDueInstallments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process a single due installment.
     */
    private function processSingleInstallment(array $installment): array
    {
        $base = [
            'installment_id' => (int)$installment['id'],
            'booking_id'     => (int)$installment['booking_id'],
            'booking_number' => $installment['booking_number'] ?? '',
            'customer_name'  => $installment['customer_name'] ?? '',
            'amount'         => (float)$installment['amount'],
            'due_date'       => $installment['due_date'] ?? '',
        ];

        // No mandate → skip, send manual reminder
        if (empty($installment['subscription_id'])) {
            $this->sendEmiReminder((int)$installment['id'], 'sms');
            $this->logPaymentAttempt((int)$installment['id'], 'skipped_no_mandate', $base);
            return array_merge($base, ['status' => 'skipped', 'reason' => 'No mandate linked']);
        }

        // Mandate is not active → skip
        if (($installment['mandate_status'] ?? '') !== 'active') {
            $this->sendEmiReminder((int)$installment['id'], 'sms');
            $this->logPaymentAttempt((int)$installment['id'], 'skipped_mandate_inactive', $base);
            return array_merge($base, ['status' => 'skipped', 'reason' => 'Mandate inactive (' . ($installment['mandate_status'] ?? 'unknown') . ')']);
        }

        // Attempt auto-debit
        $captureResult = $this->captureMandatePayment($installment['subscription_id'], (float)$installment['amount']);

        if ($captureResult['success']) {
            // Mark installment as paid
            $this->db->prepare(
                "UPDATE booking_payment_schedules SET status = 'paid', paid_amount = amount, paid_date = CURDATE(), updated_at = NOW() WHERE id = ?" . $this->tenantSql()
            )->execute($this->tenantId() > 1 ? [(int)$installment['id'], $this->tenantId()] : [(int)$installment['id']]);

            $this->logPaymentAttempt((int)$installment['id'], 'processed', array_merge($base, [
                'payment_id' => $captureResult['payment_id'],
                'razorpay_status' => $captureResult['status'],
            ]));

            return array_merge($base, [
                'status'     => 'processed',
                'payment_id' => $captureResult['payment_id'],
            ]);
        } else {
            // Auto-debit failed → send reminder + log
            $this->sendEmiReminder((int)$installment['id'], 'email');
            $this->logPaymentAttempt((int)$installment['id'], 'failed', array_merge($base, [
                'error' => $captureResult['error'] ?? 'Unknown',
            ]));

            return array_merge($base, [
                'status' => 'failed',
                'error'  => $captureResult['error'] ?? 'Auto-debit failed',
            ]);
        }
    }

    /**
     * Get or create Razorpay customer from local user_id.
     */
    private function getOrCreateRazorpayCustomer(int $userId): array
    {
        try {
            // Check if we already have a Razorpay customer ID stored
            $stmt = $this->db->prepare(
                "SELECT razorpay_customer_id FROM customer_mandates WHERE customer_id = ? AND razorpay_customer_id IS NOT NULL LIMIT 1"
            );
            $stmt->execute([$userId]);
            $existing = $stmt->fetchColumn();
            if ($existing) {
                return ['success' => true, 'customer_id' => $existing, 'error' => null];
            }

            // Fetch user details
            $stmt = $this->db->prepare("SELECT name, email, phone FROM users WHERE id = ?" . $this->tenantSql());
            $stmt->execute($this->tenantId() > 1 ? [$userId, $this->tenantId()] : [$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                return ['success' => false, 'customer_id' => null, 'error' => 'User not found'];
            }

            // Create on Razorpay
            $result = $this->razorpay->createCustomer(
                $user['name'] ?? 'Customer',
                $user['email'] ?? "user_{$userId}@apsdreamhome.com",
                $user['phone'] ?? '',
                ['user_id' => $userId]
            );

            if ($result['success'] && isset($result['data']['id'])) {
                return ['success' => true, 'customer_id' => $result['data']['id'], 'error' => null];
            }

            return ['success' => false, 'customer_id' => null, 'error' => $result['error'] ?? 'Failed to create Razorpay customer'];
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::getOrCreateRazorpayCustomer: " . $e->getMessage());
            return ['success' => false, 'customer_id' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Save mandate record to customer_mandates table.
     */
    private function saveMandateRecord(int $bookingId, int $customerId, string $subscriptionId, string $mandateId, float $amount, string $razorpayCustomerId): void
    {
        try {
            $columns = "booking_id, customer_id, subscription_id, mandate_id, razorpay_customer_id, amount, status, created_at, updated_at";
            $placeholders = "?, ?, ?, ?, ?, ?, 'active', NOW(), NOW()";
            $values = [$bookingId, $customerId, $subscriptionId, $mandateId, $razorpayCustomerId, $amount];
            if ($this->tenantId() > 1) {
                $columns .= ", tenant_id";
                $placeholders .= ", ?";
                $values[] = $this->tenantId();
            }
            $this->db->prepare(
                "INSERT INTO customer_mandates ({$columns}) VALUES ({$placeholders})"
            )->execute($values);
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::saveMandateRecord: " . $e->getMessage());
        }
    }

    /**
     * Send EMI reminder via SMS/email.
     */
    private function sendEmiReminder(int $installmentId, string $method = 'sms'): void
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT bps.amount, bps.due_date, bps.installment_no,
                        pb.booking_number, u.name AS customer_name, u.email, u.phone
                 FROM booking_payment_schedules bps
                 JOIN plot_bookings pb ON pb.id = bps.booking_id
                 JOIN users u ON u.id = pb.customer_id
                 WHERE bps.id = ?"
            );
            $stmt->execute([$installmentId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) return;

            $amount = number_format((float)$data['amount'], 2);
            $message = "Dear {$data['customer_name']}, your EMI installment #{$data['installment_no']} of ₹{$amount} for booking {$data['booking_number']} was due on {$data['due_date']}. Please pay immediately to avoid penalties. - APS Dream Home";

            if ($method === 'email' && !empty($data['email'])) {
                $from = $_ENV['SMTP_FROM_EMAIL'] ?? 'payments@apsdreamhome.com';
                $headers = "From: APS Dream Home <{$from}>\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $html = "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
                @mail($data['email'], "EMI Payment Reminder - {$data['booking_number']}", $html, $headers);
            }

            // Increment reminder count
            $this->db->prepare(
                "UPDATE booking_payment_schedules SET reminder_count = reminder_count + 1, last_reminder_at = NOW() WHERE id = ?" . $this->tenantSql()
            )->execute($this->tenantId() > 1 ? [$installmentId, $this->tenantId()] : [$installmentId]);
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::sendEmiReminder: " . $e->getMessage());
        }
    }

    /**
     * Log a payment attempt to gateway_logs.
     */
    private function logPaymentAttempt(?int $installmentId, string $status, array $details = []): void
    {
        try {
            $columns = "gateway, method, endpoint, request_payload, response_payload, response_code, status, amount_paise, transaction_id, duration_ms, retry_count, error_message, created_at";
            $placeholders = "'razorpay', 'EMI_AUTO', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()";
            $values = [
                '/emi-auto-pay/' . $status,
                json_encode(array_merge(['installment_id' => $installmentId], $details)),
                json_encode(['status' => $status]),
                $status === 'processed' ? 200 : ($status === 'failed' ? 500 : 200),
                $status,
                isset($details['amount']) ? (int)round($details['amount'] * 100) : null,
                $details['subscription_id'] ?? $details['payment_id'] ?? null,
                0,
                $details['error'] ?? null,
            ];
            if ($this->tenantId() > 1) {
                $columns .= ", tenant_id";
                $placeholders .= ", ?";
                $values[] = $this->tenantId();
            }
            $this->db->prepare(
                "INSERT INTO gateway_logs ({$columns}) VALUES ({$placeholders})"
            )->execute($values);
        } catch (Exception $e) {
            error_log("EMIAutoPaymentService::logPaymentAttempt: " . $e->getMessage());
        }
    }
}
