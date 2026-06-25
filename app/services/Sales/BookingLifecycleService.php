<?php
/**
 * Module 2: Customer Sales + Allotment + Registry
 *
 * BookingLifecycleService
 *
 * Owns the plot-booking → payment-schedule → demand-letter → registration
 * lifecycle. All money math is done with explicit float casts and PDO
 * prepared statements. Every public method is wrapped in try/catch and
 * returns a sensible default (empty array, null, or false) on failure so
 * the controller stays thin and predictable.
 *
 * Status state machine (plot_bookings.status):
 *   token_paid → agreement_signed → emi_active → partially_paid → fully_paid
 *                                                    ↓
 *                                              registration_done
 *   any → cancelled | transferred
 *
 * The MLM commission flow (booking_commissions):
 *   - commission_type=direct_sale: sales manager receives the headline %
 *   - commission_type=associate_referral: linked associate (channel='associate')
 *   - commission_type=mlm_level_1/2/3: walk the sponsor chain up to 3 levels
 *
 * Companion tables: booking_payment_schedules, booking_payment_receipts,
 *   booking_demand_letters, booking_documents, booking_status_history,
 *   booking_refunds, booking_transfers, booking_commissions, rera_compliance_log
 */

namespace App\Services\Sales;

use PDO;
use Exception;
use RuntimeException;
use InvalidArgumentException;

class BookingLifecycleService
{
    /** @var PDO */
    protected $db;

    /** Valid booking status set (mirrors plot_bookings.status ENUM). */
    public const STATUSES = [
        'token_paid','agreement_signed','emi_active','partially_paid',
        'fully_paid','cancelled','transferred','registration_done',
    ];

    /**
     * Commission rates are now sourced from MLMCommissionEngine::getCanonicalRates()
     * which reads from mlm_rank_benefits DB table. These constants are retained
     * only as documentation of the old hardcoded values. DO NOT use directly.
     *
     * @deprecated Use \App\Services\MLM\MLMCommissionEngine::getCanonicalRates($rank)
     */
    public const MLM_LEVEL_PCT = [1 => 3.0, 2 => 1.5, 3 => 1.0];

    /**
     * @deprecated Use \App\Services\MLM\MLMCommissionEngine::getCanonicalRates($rank)['direct']
     */
    public const DEFAULT_DIRECT_SALE_PCT = 2.0;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                error_log('[BookingLifecycleService] DB init failed: ' . $e->getMessage());
                $pdo = null;
            }
        }
        if (!$pdo instanceof PDO) {
            // soft-init; queries will fail individually and be caught
            $pdo = null;
        }
        $this->db = $pdo;
    }

    /* ====================================================================
     *  1. createBooking
     * ================================================================== */

    /**
     * Create a new plot_booking.
     *
     * Required $data keys: plot_id, customer_id, total_plot_value
     * Optional: booking_amount (token), agreement_value, channel,
     *           associate_id, sales_manager_id, commission_pct, notes
     *
     * @param array $data
     * @return array  { success, id, booking_number, commission_amount } | { success:false, error }
     */
    public function createBooking(array $data): array
    {
        try {
            $plotId     = (int)($data['plot_id'] ?? 0);
            $customerId = (int)($data['customer_id'] ?? 0);
            $totalValue = (float)($data['total_plot_value'] ?? 0);

            if ($plotId <= 0)     return ['success' => false, 'error' => 'plot_id is required'];
            if ($customerId <= 0) return ['success' => false, 'error' => 'customer_id is required'];
            if ($totalValue <= 0) return ['success' => false, 'error' => 'total_plot_value must be > 0'];

            $channel        = (string)($data['channel'] ?? 'direct');
            if (!in_array($channel, ['direct','associate','agent','walk_in'], true)) {
                $channel = 'direct';
            }
            $commissionPct  = (float)($data['commission_pct'] ?? 0.0);
            $commissionAmt  = round($totalValue * $commissionPct / 100, 2);
            $bookingNumber  = $this->generateBookingNumber();

            $sql = "INSERT INTO plot_bookings
                (plot_id, customer_id, booking_number, booking_date,
                 total_plot_value, booking_amount, agreement_value,
                 status, sales_manager_id, channel, associate_id,
                 commission_pct, commission_amount, notes)
                VALUES (?, ?, ?, CURDATE(), ?, ?, ?, 'token_paid', ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $plotId,
                $customerId,
                $bookingNumber,
                $totalValue,
                (float)($data['booking_amount'] ?? 0.0),
                (float)($data['agreement_value'] ?? $totalValue),
                !empty($data['sales_manager_id']) ? (int)$data['sales_manager_id'] : null,
                $channel,
                !empty($data['associate_id']) ? (int)$data['associate_id'] : null,
                $commissionPct,
                $commissionAmt,
                (string)($data['notes'] ?? ''),
            ]);
            $bookingId = (int)$this->db->lastInsertId();

            // Audit trail
            $this->logStatusHistory($bookingId, null, 'token_paid', null, 'Booking created');

            // Send push notification to customer
            try {
                $pushSvc = new \App\Services\Communication\PushNotificationService();
                $pushSvc->sendToUser((int)$customerId, [
                    'title' => 'Booking Confirmed!',
                    'body' => "Your booking {$bookingNumber} for plot has been confirmed. Token amount received.",
                    'data' => [
                        'type' => 'booking_confirmed',
                        'booking_id' => $bookingId,
                        'booking_number' => $bookingNumber,
                    ],
                ]);
            } catch (\Throwable $e) {
                error_log('[BookingLifecycleService] Push notification failed: ' . $e->getMessage());
            }

            return [
                'success'           => true,
                'id'                => $bookingId,
                'booking_number'    => $bookingNumber,
                'commission_amount' => $commissionAmt,
            ];
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::createBooking] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ====================================================================
     *  2. generatePaymentSchedule
     * ================================================================== */

    /**
     * Generate an EMI schedule for a booking using the reducing-balance
     * method. Returns an array of installments. Optionally persists them
     * to booking_payment_schedules (pass persist=true via 4th argument
     * is not in spec — we always persist if $bookingId is given).
     *
     * @param int    $bookingId
     * @param int    $tenureMonths
     * @param float  $ratePerAnnum  e.g. 9.5 for 9.5% p.a.
     * @return array  ['success'=>true, 'installments'=>[...], 'summary'=>[...]] | ['success'=>false, 'error']
     */
    public function generatePaymentSchedule(int $bookingId, int $tenureMonths, float $ratePerAnnum): array
    {
        try {
            $booking = $this->getBookingById($bookingId);
            if (!$booking) {
                return ['success' => false, 'error' => 'Booking not found'];
            }

            // Net principal = agreement_value - token already paid
            $principal = (float)$booking['total_plot_value']
                       - (float)$booking['booking_amount'];
            if ($principal <= 0) {
                return ['success' => false, 'error' => 'No principal left to schedule (booking already covers full value)'];
            }
            if ($tenureMonths <= 0) {
                return ['success' => false, 'error' => 'tenureMonths must be > 0'];
            }

            $monthlyRate = ($ratePerAnnum / 100) / 12;
            $emi = 0.0;
            if ($monthlyRate > 0) {
                $pow = pow(1 + $monthlyRate, $tenureMonths);
                $emi = round($principal * $monthlyRate * $pow / ($pow - 1), 2);
            } else {
                $emi = round($principal / $tenureMonths, 2);
            }

            $balance = $principal;
            $firstDue = date('Y-m-d', strtotime('+1 month'));
            $installments = [];
            for ($i = 1; $i <= $tenureMonths; $i++) {
                $interest = round($balance * $monthlyRate, 2);
                $principalComp = round($emi - $interest, 2);
                $opening = $balance;
                $balance = round($balance - $principalComp, 2);
                if ($i === $tenureMonths) {
                    // Adjust last installment to close out exactly
                    $principalComp += $balance;
                    $emi = $principalComp + $interest;
                    $balance = 0.0;
                }
                $installments[] = [
                    'installment_no'  => $i,
                    'due_date'        => date('Y-m-d', strtotime($firstDue . ' + ' . ($i - 1) . ' months')),
                    'amount'          => $emi,
                    'principal'       => $principalComp,
                    'interest'        => $interest,
                    'opening_balance' => $opening,
                    'closing_balance' => $balance,
                ];
            }

            // Persist: clear any existing unpaid installments first
            $this->db->prepare("DELETE FROM booking_payment_schedules WHERE booking_id = ? AND status IN ('pending','overdue')")
                     ->execute([$bookingId]);

            $ins = $this->db->prepare(
                "INSERT INTO booking_payment_schedules
                 (booking_id, installment_no, due_date, amount, principal, interest,
                  opening_balance, closing_balance, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );
            foreach ($installments as $row) {
                $ins->execute([
                    $bookingId, $row['installment_no'], $row['due_date'], $row['amount'],
                    $row['principal'], $row['interest'], $row['opening_balance'], $row['closing_balance']
                ]);
            }

            // Bump booking to emi_active (if not already beyond)
            $cur = (string)$booking['status'];
            if (in_array($cur, ['token_paid','agreement_signed'], true)) {
                $this->updateBookingStatus($bookingId, $cur, 'emi_active', null, 'EMI schedule generated');
            }

            return [
                'success'      => true,
                'installments' => $installments,
                'summary'      => [
                    'principal'      => $principal,
                    'rate_pa'        => $ratePerAnnum,
                    'tenure_months'  => $tenureMonths,
                    'emi'            => $emi,
                    'total_payable'  => round($emi * $tenureMonths, 2),
                    'total_interest' => round($emi * $tenureMonths - $principal, 2),
                ],
            ];
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::generatePaymentSchedule] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ====================================================================
     *  3. getBookingById
     * ================================================================== */

    /**
     * Fetch a booking with joined plot + customer + colony context.
     * Returns null if not found.
     */
    public function getBookingById(int $id): ?array
    {
        try {
            $sql = "SELECT pb.*,
                           p.plot_number, p.plot_code, p.area_sqft, p.total_price AS plot_total_price,
                           c.name AS colony_name, c.slug AS colony_slug,
                           u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                           sm.name AS sales_manager_name,
                           a.name AS associate_name, a.referral_code
                    FROM plot_bookings pb
                    LEFT JOIN plots p ON p.id = pb.plot_id
                    LEFT JOIN colonies c ON c.id = p.colony_id
                    LEFT JOIN users u ON u.id = pb.customer_id
                    LEFT JOIN users sm ON sm.id = pb.sales_manager_id
                    LEFT JOIN associates a ON a.id = pb.associate_id
                    WHERE pb.id = ?
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::getBookingById] ' . $e->getMessage());
            return null;
        }
    }

    /* ====================================================================
     *  4. listBookings
     * ================================================================== */

    /**
     * Paginated list with optional filters:
     *   - status         (string)
     *   - search         (booking_number / customer name)
     *   - date_from      (Y-m-d)
     *   - date_to        (Y-m-d)
     *   - page           (1-based, default 1)
     *   - per_page       (default 20)
     *
     * @return array ['data'=>[...], 'total'=>int, 'page'=>int, 'per_page'=>int, 'pages'=>int]
     */
    public function listBookings(array $filters = []): array
    {
        $out = ['data' => [], 'total' => 0, 'page' => 1, 'per_page' => 20, 'pages' => 0];
        try {
            $where = [];
            $params = [];

            if (!empty($filters['status'])) {
                $where[] = 'pb.status = ?';
                $params[] = $filters['status'];
            }
            if (!empty($filters['search'])) {
                $where[] = '(pb.booking_number LIKE ? OR u.name LIKE ?)';
                $s = '%' . $filters['search'] . '%';
                $params[] = $s;
                $params[] = $s;
            }
            if (!empty($filters['date_from'])) {
                $where[] = 'pb.booking_date >= ?';
                $params[] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = 'pb.booking_date <= ?';
                $params[] = $filters['date_to'];
            }

            $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $page     = max(1, (int)($filters['page'] ?? 1));
            $perPage  = max(1, min(100, (int)($filters['per_page'] ?? 20)));
            $offset   = ($page - 1) * $perPage;

            // total
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM plot_bookings pb LEFT JOIN users u ON u.id = pb.customer_id $whereSql");
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();
            $pages = $perPage > 0 ? (int)ceil($total / $perPage) : 0;

            // data
            $sql = "SELECT pb.*,
                           p.plot_number, p.area_sqft,
                           c.name AS colony_name,
                           u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
                    FROM plot_bookings pb
                    LEFT JOIN plots p ON p.id = pb.plot_id
                    LEFT JOIN colonies c ON c.id = p.colony_id
                    LEFT JOIN users u ON u.id = pb.customer_id
                    $whereSql
                    ORDER BY pb.id DESC
                    LIMIT $perPage OFFSET $offset";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $out['data']    = $data;
            $out['total']   = $total;
            $out['page']    = $page;
            $out['per_page']= $perPage;
            $out['pages']   = $pages;
            return $out;
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::listBookings] ' . $e->getMessage());
            return $out;
        }
    }

    /* ====================================================================
     *  5. getPaymentSchedule
     * ================================================================== */

    /**
     * @return array list of installments for a booking (empty if none)
     */
    public function getPaymentSchedule(int $bookingId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM booking_payment_schedules
                 WHERE booking_id = ?
                 ORDER BY installment_no ASC"
            );
            $stmt->execute([$bookingId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::getPaymentSchedule] ' . $e->getMessage());
            return [];
        }
    }

    /* ====================================================================
     *  6. recordPayment
     * ================================================================== */

    /**
     * Create a booking_payment_receipts row, mark the installment as paid
     * (or partial), and auto-advance the parent booking's status.
     *
     * Required $paymentData: amount, payment_mode
     * Optional: cheque_number, cheque_date, bank_name, transaction_ref,
     *           notes, status (default 'cleared'), paid_date (default today)
     *
     * @return array ['success'=>true, 'receipt_id'=>N, 'receipt_number'=>'...'] | ['success'=>false,'error']
     */
    public function recordPayment(int $installmentId, array $paymentData): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT bps.*, pb.id AS pb_id, pb.booking_amount, pb.total_plot_value
                 FROM booking_payment_schedules bps
                 JOIN plot_bookings pb ON pb.id = bps.booking_id
                 WHERE bps.id = ?"
            );
            $stmt->execute([$installmentId]);
            $inst = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$inst) {
                return ['success' => false, 'error' => 'Installment not found'];
            }

            $amount     = (float)($paymentData['amount'] ?? 0);
            if ($amount <= 0) {
                return ['success' => false, 'error' => 'amount must be > 0'];
            }
            $mode       = (string)($paymentData['payment_mode'] ?? 'cash');
            if (!in_array($mode, ['cash','cheque','dd','neft','rtgs','upi','card','bank_transfer'], true)) {
                $mode = 'cash';
            }
            $status     = (string)($paymentData['status'] ?? 'cleared');
            $paidDate   = (string)($paymentData['paid_date'] ?? date('Y-m-d'));
            $collectedBy= !empty($paymentData['collected_by']) ? (int)$paymentData['collected_by'] : null;

            $receiptNumber = $this->generateReceiptNumber();

            $this->db->beginTransaction();
            $ins = $this->db->prepare(
                "INSERT INTO booking_payment_receipts
                 (booking_id, installment_id, receipt_number, receipt_date, amount, payment_mode,
                  cheque_number, cheque_date, bank_name, transaction_ref, collected_by, status, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $ins->execute([
                (int)$inst['booking_id'],
                $installmentId,
                $receiptNumber,
                $paidDate,
                $amount,
                $mode,
                (string)($paymentData['cheque_number'] ?? ''),
                (string)($paymentData['cheque_date'] ?? '') ?: null,
                (string)($paymentData['bank_name'] ?? ''),
                (string)($paymentData['transaction_ref'] ?? ''),
                $collectedBy,
                $status,
                (string)($paymentData['notes'] ?? ''),
            ]);
            $receiptId = (int)$this->db->lastInsertId();

            // Update installment
            $totalReceived = (float)$inst['paid_amount'] + $amount;
            $instStatus = ($totalReceived >= (float)$inst['amount']) ? 'paid' : 'partial';

            // --- EARLY PAYMENT DISCOUNT ---
            // If paid 3+ days before due date, apply 0.5% discount on interest component
            $discountApplied = 0;
            if (!empty($inst['due_date']) && !empty($inst['interest_amount']) && $instStatus === 'paid') {
                $dueDate = new \DateTime($inst['due_date']);
                $payDate = new \DateTime($paidDate);
                $daysEarly = (int)$dueDate->diff($payDate)->days;
                $isEarly = $payDate < $dueDate; // must be before due date

                if ($isEarly && $daysEarly >= 3) {
                    $discountPct = 0.5; // 0.5% discount on interest
                    $discountApplied = round((float)$inst['interest_amount'] * $discountPct / 100, 2);
                    if ($discountApplied > 0) {
                        $this->db->prepare(
                            "UPDATE booking_payment_schedules 
                             SET early_payment_discount = ?, discount_applied = 1 
                             WHERE id = ?"
                        )->execute([$discountApplied, $installmentId]);
                        // Reduce the effective interest — discount is a credit to customer
                        $totalReceived += $discountApplied;
                        error_log("[BookingLifecycleService] Early payment discount ₹$discountApplied applied to installment #$installmentId ($daysEarly days early)");
                    }
                }
            }

            $upd = $this->db->prepare(
                "UPDATE booking_payment_schedules
                 SET paid_amount = ?, paid_date = ?, status = ?
                 WHERE id = ?"
            );
            $upd->execute([$totalReceived, $paidDate, $instStatus, $installmentId]);

            // Auto-advance booking status
            $this->maybeAdvanceBookingStatus((int)$inst['booking_id']);
            $this->db->commit();

            // Send payment receipt (email + SMS) via BookingNotificationService
            try {
                $booking = $this->getBookingById((int)$inst['booking_id']);
                if (!empty($booking['customer_id'])) {
                    $user = $this->db->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
                    $user->execute([(int)$booking['customer_id']]);
                    $userData = $user->fetch(PDO::FETCH_ASSOC);

                    if ($userData) {
                        $notifSvc = new \App\Services\BookingNotificationService();
                        $notifSvc->sendPaymentReceipt(
                            $booking,
                            $userData,
                            $amount,
                            $receiptNumber
                        );
                    }
                }
            } catch (\Throwable $e) {
                error_log("[BookingLifecycleService::recordPayment] notification failed: " . $e->getMessage());
            }

            // Broadcast payment event via WebSocket + Push
            try {
                $customerId = $this->getBookingCustomerId((int)$inst['booking_id']);
                $payload = [
                    'event'         => 'payment_received',
                    'booking_id'    => (int)$inst['booking_id'],
                    'installment_id'=> $installmentId,
                    'receipt_id'    => $receiptId,
                    'receipt_number'=> $receiptNumber,
                    'amount'        => $amount,
                    'payment_mode'  => $mode,
                    'status'        => $instStatus,
                    'paid_date'     => $paidDate,
                    'created_at'    => date('Y-m-d H:i:s'),
                ];
                if ($customerId) {
                    \App\Services\WebSocketBroadcaster::broadcastToUser($customerId, $payload);
                    $pushSvc = new \App\Services\Communication\PushNotificationService();
                    $pushSvc->sendToUser((int)$customerId, [
                        'title' => 'Payment Received',
                        'body'  => '₹' . number_format($amount) . ' received for installment #' . $installmentId,
                        'data'  => $payload,
                    ]);
                }
            } catch (\Throwable $e) {
                error_log("[BookingLifecycleService::recordPayment] broadcast failed: " . $e->getMessage());
            }

            return [
                'success'        => true,
                'receipt_id'     => $receiptId,
                'receipt_number' => $receiptNumber,
                'installment_status' => $instStatus,
            ];
        } catch (Exception $e) {
            if ($this->db && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[BookingLifecycleService::recordPayment] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ====================================================================
     *  7. getOverdueInstallments
     * ================================================================== */

    /**
     * @return array list of overdue + past-due-pending installments
     */
    public function getOverdueInstallments(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT bps.*, pb.booking_number, u.name AS customer_name, u.phone AS customer_phone
                 FROM booking_payment_schedules bps
                 JOIN plot_bookings pb ON pb.id = bps.booking_id
                 LEFT JOIN users u ON u.id = pb.customer_id
                 WHERE (bps.status = 'overdue')
                    OR (bps.status = 'pending' AND bps.due_date < CURDATE())
                 ORDER BY bps.due_date ASC
                 LIMIT 200"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::getOverdueInstallments] ' . $e->getMessage());
            return [];
        }
    }

    /* ====================================================================
     *  8. generateDemandLetter
     * ================================================================== */

    /**
     * Create a booking_demand_letters row and a stub PDF path.
     * PDF generation is stubbed — the path is recorded so downstream
     * processes can render the actual PDF.
     *
     * @return array ['success'=>true, 'letter_id'=>N, 'letter_number'=>'...', 'pdf_path'=>'...']
     */
    public function generateDemandLetter(int $installmentId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT bps.*, pb.booking_number, pb.id AS pb_id,
                        u.name AS customer_name, u.email AS customer_email
                 FROM booking_payment_schedules bps
                 JOIN plot_bookings pb ON pb.id = bps.booking_id
                 LEFT JOIN users u ON u.id = pb.customer_id
                 WHERE bps.id = ?"
            );
            $stmt->execute([$installmentId]);
            $inst = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$inst) {
                return ['success' => false, 'error' => 'Installment not found'];
            }
            $letterNumber = $this->generateLetterNumber();
            $pdfPath = '/storage/demand_letters/' . $letterNumber . '.pdf';

            $ins = $this->db->prepare(
                "INSERT INTO booking_demand_letters
                 (booking_id, installment_id, letter_number, generated_date, due_date, amount,
                  status, pdf_path)
                 VALUES (?, ?, ?, CURDATE(), ?, ?, 'drafted', ?)"
            );
            $ins->execute([
                (int)$inst['pb_id'],
                $installmentId,
                $letterNumber,
                $inst['due_date'],
                $inst['amount'],
                $pdfPath,
            ]);
            $letterId = (int)$this->db->lastInsertId();

            return [
                'success'       => true,
                'letter_id'     => $letterId,
                'letter_number' => $letterNumber,
                'pdf_path'      => $pdfPath,
            ];
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::generateDemandLetter] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ====================================================================
     *  9. cancelBooking
     * ================================================================== */

    /**
     * Mark booking as cancelled and create a booking_refunds record.
     *
     * @return array ['success'=>true, 'refund_id'=>N, 'status'=>...]
     */
    public function cancelBooking(int $bookingId, string $reason, float $cancellationCharge): array
    {
        try {
            $booking = $this->getBookingById($bookingId);
            if (!$booking) {
                return ['success' => false, 'error' => 'Booking not found'];
            }

            if ($cancellationCharge <= 0) {
                // Auto-calculate based on RERA rules
                $bookingDate = new \DateTime($booking['booking_date'] ?? $booking['created_at'] ?? 'now');
                $now = new \DateTime();
                $interval = $bookingDate->diff($now);
                $daysElapsed = $interval->days;

                if ($booking['status'] === 'token_paid') {
                    if ($daysElapsed <= 15) {
                        $cancellationCharge = (float)$booking['booking_amount'] * 0.10;
                    } else {
                        $cancellationCharge = (float)$booking['booking_amount'];
                    }
                } else {
                    $cancellationCharge = (float)$booking['total_plot_value'] * 0.10;
                }
            }

            $paidSoFar = $this->totalPaid($bookingId);
            $refundAmt = max(0.0, $paidSoFar - $cancellationCharge);

            $this->db->beginTransaction();
            $prevStatus = (string)$booking['status'];
            $upd = $this->db->prepare(
                "UPDATE plot_bookings SET status='cancelled' WHERE id = ?"
            );
            $upd->execute([$bookingId]);
            $this->logStatusHistory($bookingId, $prevStatus, 'cancelled', null, $reason);

            $ins = $this->db->prepare(
                "INSERT INTO booking_refunds
                 (booking_id, refund_amount, cancellation_charge, deduction_reason, refund_mode, status)
                 VALUES (?, ?, ?, ?, 'neft', 'pending')"
            );
            $ins->execute([$bookingId, $refundAmt, $cancellationCharge, $reason]);
            $refundId = (int)$this->db->lastInsertId();

            // Claw back all commissions associated with this booking
            $this->clawbackBookingCommissions($bookingId);

            $this->db->commit();

            // Send cancellation notification (email + SMS + push)
            try {
                $emailSvc = new \App\Services\EmailTemplateService();
                $emailSvc->sendBookingCancellation((int)$booking['customer_id'], [
                    'booking_number' => $booking['booking_number'] ?? '',
                    'plot_number' => $booking['plot_number'] ?? '',
                    'colony_name' => $booking['colony_name'] ?? '',
                    'cancellation_reason' => $reason,
                    'cancellation_charge' => number_format($cancellationCharge, 2),
                    'refund_amount' => number_format($refundAmt, 2),
                ]);

                $user = $this->db->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
                $user->execute([(int)$booking['customer_id']]);
                $userData = $user->fetch(PDO::FETCH_ASSOC);
                if ($userData) {
                    $notifSvc = new \App\Services\BookingNotificationService();
                    $notifSvc->sendStatusChange($booking, $userData, $prevStatus, 'cancelled');
                }

                // Push notification for cancellation
                $pushSvc = new \App\Services\Communication\PushNotificationService();
                $pushSvc->sendToUser((int)$booking['customer_id'], [
                    'title' => 'Booking Cancelled',
                    'body'  => "Booking {$booking['booking_number']} has been cancelled. Refund of ₹" . number_format($refundAmt) . " will be processed.",
                    'data'  => [
                        'type' => 'booking_cancelled',
                        'booking_id' => $bookingId,
                        'refund_amount' => $refundAmt,
                    ],
                ]);
            } catch (\Throwable $e) {
                error_log("[BookingLifecycleService] cancelBooking notification failed: " . $e->getMessage());
            }

            return [
                'success'    => true,
                'refund_id'  => $refundId,
                'refund_amount' => $refundAmt,
                'status'     => 'cancelled',
            ];
        } catch (Exception $e) {
            if ($this->db && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[BookingLifecycleService::cancelBooking] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ====================================================================
     * 10. transferBooking
     * ================================================================== */

    /**
     * Record a booking transfer to another customer.
     */
    public function transferBooking(int $bookingId, int $newCustomerId, string $reason, float $transferCharge): array
    {
        try {
            $booking = $this->getBookingById($bookingId);
            if (!$booking) {
                return ['success' => false, 'error' => 'Booking not found'];
            }
            if ($newCustomerId <= 0) {
                return ['success' => false, 'error' => 'new_customer_id is required'];
            }
            $prevStatus = (string)$booking['status'];
            $this->db->beginTransaction();
            $ins = $this->db->prepare(
                "INSERT INTO booking_transfers
                 (original_booking_id, new_customer_id, transfer_reason, transfer_date, transfer_charge, status)
                 VALUES (?, ?, ?, CURDATE(), ?, 'initiated')"
            );
            $ins->execute([$bookingId, $newCustomerId, $reason, $transferCharge]);
            $transferId = (int)$this->db->lastInsertId();

            $this->db->prepare("UPDATE plot_bookings SET status='transferred' WHERE id = ?")
                     ->execute([$bookingId]);
            $this->logStatusHistory($bookingId, $prevStatus, 'transferred', null, $reason);
            $this->db->commit();

            return [
                'success'     => true,
                'transfer_id' => $transferId,
                'status'      => 'transferred',
            ];
        } catch (Exception $e) {
            if ($this->db && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[BookingLifecycleService::transferBooking] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ====================================================================
     * 11. calculateCommission
     * ================================================================== */

    /**
     * Compute and create commission rows for a booking.
     *
     * DELEGATES to MLMCommissionEngine::calculateBookingCommission() which:
     *   - Reads rates from mlm_rank_benefits DB table via getCanonicalRates()
     *   - Walks the upline via users.referred_by
     *   - Writes to mlm_commission_ledger (canonical table)
     *
     * This method also writes a backward-compat row to booking_commissions
     * for any legacy code that reads from that table.
     *
     * @return array ['success'=>true, 'created'=>N, 'total_amount'=>X, 'rows'=>[...]]
     */
    public function calculateCommission(int $bookingId): array
    {
        $out = ['success' => false, 'created' => 0, 'total_amount' => 0.0, 'rows' => []];
        try {
            $booking = $this->getBookingById($bookingId);
            if (!$booking) {
                return ['success' => false, 'error' => 'Booking not found'];
            }

            // Delegate to the canonical engine
            $engine = new \App\Services\MLM\MLMCommissionEngine($this->db);
            $engineResult = $engine->calculateBookingCommission($bookingId);

            $rows = $engineResult['entries'] ?? [];
            $totalAmt = (float)($engineResult['total'] ?? 0.0);

            // mlm_commission_ledger is now the single source of truth
            // booking_commissions table is deprecated (migrated)
            $created = count($rows);

            $out = [
                'success'       => true,
                'created'       => $created,
                'total_amount'  => round($totalAmt, 2),
                'rows'          => $rows,
            ];

            // Apply daily capping for each beneficiary on level-type commissions
            try {
                $capService = new \App\Services\MLM\DailyCappingService();
                foreach ($rows as $r) {
                    if (strpos($r['commission_type'], 'mlm_level_') === 0 || $r['commission_type'] === 'level_bonus') {
                        $capStatus = $capService->getCapStatus((int)$r['beneficiary_user_id']);
                        $dailyCap = (float)($capStatus['daily_cap'] ?? 0);
                        if ($dailyCap > 0) {
                            $capService->applyDailyCap((int)$r['beneficiary_user_id'], $r['amount'], $dailyCap);
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log('[BookingLifecycleService::calculateCommission] DailyCappingService error: ' . $e->getMessage());
            }

            return $out;
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::calculateCommission] ' . $e->getMessage());
            $out['error'] = $e->getMessage();
            return $out;
        }
    }

    /* ====================================================================
     * 12. updateReraCompliance
     * ================================================================== */

    /**
     * Insert or update a rera_compliance_log row for a colony/year/quarter.
     */
    public function updateReraCompliance(int $colonyId, int $year, string $quarter, float $progress, float $withdrawn): array
    {
        try {
            if (!in_array($quarter, ['Q1','Q2','Q3','Q4'], true)) {
                return ['success' => false, 'error' => 'quarter must be one of Q1,Q2,Q3,Q4'];
            }
            $sql = "INSERT INTO rera_compliance_log
                     (project_colony_id, quarter, year, progress_percent, amount_withdrawn, status, submitted_at)
                    VALUES (?, ?, ?, ?, ?, 'submitted', NOW())
                    ON DUPLICATE KEY UPDATE
                      progress_percent   = VALUES(progress_percent),
                      amount_withdrawn   = VALUES(amount_withdrawn),
                      status             = 'submitted',
                      submitted_at       = NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$colonyId, $quarter, $year, $progress, $withdrawn]);
            $id = (int)$this->db->lastInsertId();
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::updateReraCompliance] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ====================================================================
     * 13. getReraCompliance
     * ================================================================== */

    /**
     * @return array list of compliance rows for a colony (empty on failure)
     */
    public function getReraCompliance(int $colonyId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM rera_compliance_log
                 WHERE project_colony_id = ?
                 ORDER BY year DESC, quarter DESC"
            );
            $stmt->execute([$colonyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::getReraCompliance] ' . $e->getMessage());
            return [];
        }
    }

    /* ====================================================================
     * 14. getDashboardStats
     * ================================================================== */

    /**
     * Top-level dashboard counters.
     *
     * @return array
     *   total_bookings, active_emi, overdue_count, commission_earned, refund_pending,
     *   total_revenue, by_status (assoc status=>count)
     */
    public function getDashboardStats(): array
    {
        $defaults = [
            'total_bookings'    => 0,
            'active_emi'        => 0,
            'overdue_count'     => 0,
            'commission_earned' => 0.0,
            'refund_pending'    => 0,
            'total_revenue'     => 0.0,
            'by_status'         => [],
        ];
        try {
            $totalBookings = (int)$this->db->query("SELECT COUNT(*) FROM plot_bookings")->fetchColumn();
            $activeEmi = (int)$this->db->query(
                "SELECT COUNT(DISTINCT booking_id) FROM booking_payment_schedules
                 WHERE status IN ('pending','overdue','partial')"
            )->fetchColumn();
            $overdueCount = (int)$this->db->query(
                "SELECT COUNT(*) FROM booking_payment_schedules
                 WHERE status = 'overdue'
                    OR (status = 'pending' AND due_date < CURDATE())"
            )->fetchColumn();
            $commissionEarned = (float)$this->db->query(
                "SELECT COALESCE(SUM(amount),0) FROM booking_commissions
                 WHERE status IN ('approved','paid')"
            )->fetchColumn();
            $refundPending = (int)$this->db->query(
                "SELECT COUNT(*) FROM booking_refunds WHERE status = 'pending'"
            )->fetchColumn();
            $totalRevenue = (float)$this->db->query(
                "SELECT COALESCE(SUM(amount),0) FROM booking_payment_receipts
                 WHERE status = 'cleared'"
            )->fetchColumn();
            $byStatus = [];
            $rows = $this->db->query(
                "SELECT status, COUNT(*) c FROM plot_bookings GROUP BY status"
            )->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $byStatus[$r['status']] = (int)$r['c'];
            }
            return [
                'total_bookings'    => $totalBookings,
                'active_emi'        => $activeEmi,
                'overdue_count'     => $overdueCount,
                'commission_earned' => round($commissionEarned, 2),
                'refund_pending'    => $refundPending,
                'total_revenue'     => round($totalRevenue, 2),
                'by_status'         => $byStatus,
            ];
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::getDashboardStats] ' . $e->getMessage());
            return $defaults;
        }
    }

    /* ====================================================================
     *  Helpers (private)
     * ================================================================== */

    private function generateBookingNumber(): string
    {
        return 'APS-BK-' . date('Ymd') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function generateReceiptNumber(): string
    {
        return 'APS-RCP-' . str_pad((string)random_int(1, 9999999), 7, '0', STR_PAD_LEFT);
    }

    private function generateLetterNumber(): string
    {
        return 'APS-DL-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the customer (user) ID for a booking.
     */
    private function getBookingCustomerId(int $bookingId): ?int
    {
        try {
            $stmt = $this->db->prepare("SELECT customer_id FROM plot_bookings WHERE id = ?");
            $stmt->execute([$bookingId]);
            $id = $stmt->fetchColumn();
            return $id ? (int)$id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function totalPaid(int $bookingId): float
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(amount),0) FROM booking_payment_receipts
                 WHERE booking_id = ? AND status = 'cleared'"
            );
            $stmt->execute([$bookingId]);
            return (float)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0.0;
        }
    }

    private function maybeAdvanceBookingStatus(int $bookingId): void
    {
        try {
            $row = $this->getBookingById($bookingId);
            if (!$row) return;
            $cur = (string)$row['status'];
            if (in_array($cur, ['cancelled','transferred','registration_done'], true)) return;

            $totalValue = (float)$row['total_plot_value'];
            $paid       = $this->totalPaid($bookingId);
            $pending    = (int)$this->db->prepare(
                "SELECT COUNT(*) FROM booking_payment_schedules
                 WHERE booking_id = ? AND status IN ('pending','overdue','partial')"
            );
            $pending->execute([$bookingId]);
            $pendingCount = (int)$pending->fetchColumn();

            $new = $cur;
            if ($pendingCount === 0 && $paid >= $totalValue) {
                $new = 'fully_paid';
            } elseif ($paid > 0 && $paid < $totalValue) {
                $new = 'partially_paid';
            } elseif ($cur === 'token_paid' && $pendingCount > 0) {
                $new = 'emi_active';
            }
            if ($new !== $cur) {
                $this->updateBookingStatus($bookingId, $cur, $new, null, 'Auto-advance on payment');
            }
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::maybeAdvanceBookingStatus] ' . $e->getMessage());
        }
    }

    private function updateBookingStatus(int $bookingId, ?string $from, string $to, ?int $changedBy, ?string $reason): void
    {
        try {
            $this->db->prepare("UPDATE plot_bookings SET status = ? WHERE id = ?")
                     ->execute([$to, $bookingId]);
            $this->logStatusHistory($bookingId, $from, $to, $changedBy, $reason);
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::updateBookingStatus] ' . $e->getMessage());
        }
    }

    private function logStatusHistory(int $bookingId, ?string $from, string $to, ?int $changedBy, ?string $reason): void
    {
        try {
            $this->db->prepare(
                "INSERT INTO booking_status_history
                 (booking_id, from_status, to_status, changed_by, reason, ip_address, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            )->execute([
                $bookingId,
                $from,
                $to,
                $changedBy,
                $reason,
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
            ]);
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::logStatusHistory] ' . $e->getMessage());
        }
    }

    /**
     * Walk up to 3 sponsor levels from $userId and append MLM commission rows.
     */
    private function appendMlmUpline(int $startUserId, int $sourceUserId, float $basis, array &$rows): void
    {
        try {
            $current = $startUserId;
            for ($level = 1; $level <= 3; $level++) {
                $stmt = $this->db->prepare(
                    "SELECT u.id, u.referred_by
                     FROM users u
                     WHERE u.id = ?"
                );
                $stmt->execute([$current]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$user || empty($user['referred_by'])) {
                    break;
                }
                $sponsorId = (int)$user['referred_by'];
                $pct = self::MLM_LEVEL_PCT[$level] ?? 0.0;
                $amt = round($basis * $pct / 100, 2);
                $rows[] = [
                    'beneficiary_user_id' => $sponsorId,
                    'source_user_id'      => $sourceUserId,
                    'commission_type'     => 'mlm_level_' . $level,
                    'amount'              => $amt,
                    'percent'             => $pct,
                    'level'               => $level,
                ];
                $current = $sponsorId;
            }
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::appendMlmUpline] ' . $e->getMessage());
        }
    }

    /**
     * Claw back all commissions associated with a booking when it is cancelled.
     */
    public function clawbackBookingCommissions(int $bookingId): void
    {
        try {
            // 1. Mark pending commissions as cancelled in booking_commissions
            $stmt = $this->db->prepare("
                UPDATE booking_commissions 
                SET status = 'cancelled' 
                WHERE booking_id = ? AND status = 'pending'
            ");
            $stmt->execute([$bookingId]);

            // 2. Query all commissions related to this booking that are approved or paid
            $stmt = $this->db->prepare("
                SELECT * FROM booking_commissions 
                WHERE booking_id = ? AND status IN ('approved', 'paid')
            ");
            $stmt->execute([$bookingId]);
            $commissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($commissions as $comm) {
                $beneficiary = (int)$comm['beneficiary_user_id'];
                $amount = (float)$comm['amount'];

                if ($amount <= 0) continue;

                // Create a debit entry in mlm_commission_ledger
                $ledgerStmt = $this->db->prepare("
                    INSERT INTO mlm_commission_ledger 
                    (beneficiary_user_id, source_user_id, commission_type, amount, level, sale_amount, commission_percentage, status, notes, created_at)
                    VALUES (?, ?, 'clawback', ?, ?, 0.00, 0.00, 'approved', ?, NOW())
                ");
                $note = "Clawback - Booking #" . $bookingId . " Cancelled";
                $ledgerStmt->execute([$beneficiary, $comm['source_user_id'], -$amount, $comm['level'], $note]);

                // Deduct from wallet balance (allows negative balance)
                $walletStmt = $this->db->prepare("
                    UPDATE user_wallets 
                    SET balance = balance - ?, 
                        updated_at = NOW()
                    WHERE user_id = ? AND user_type = 'associate'
                ");
                $walletStmt->execute([$amount, $beneficiary]);
            }
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::clawbackBookingCommissions] Error: ' . $e->getMessage());
        }
    }

    /* ====================================================================
     * 14. NACH Mandate Management (EMI Auto-Debit)
     * ================================================================== */

    /**
     * Register a NACH mandate for auto-debit of EMIs.
     */
    public function registerNachMandate(int $bookingId, int $customerId, array $mandateData): array
    {
        try {
            $required = ['bank_name', 'bank_account_number', 'ifsc_code', 'account_holder_name', 'mandate_amount'];
            foreach ($required as $field) {
                if (empty($mandateData[$field])) {
                    return ['success' => false, 'error' => "Missing required field: $field"];
                }
            }

            // Get booking to determine start/end dates
            $booking = $this->getBookingById($bookingId);
            if (!$booking) {
                return ['success' => false, 'error' => 'Booking not found'];
            }

            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime('+5 years'));

            $stmt = $this->db->prepare("
                INSERT INTO nach_mandates
                (booking_id, customer_id, mandate_type, bank_name, bank_account_number, 
                 ifsc_code, account_holder_name, mandate_amount, frequency, start_date, end_date, 
                 next_debit_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'monthly', ?, ?, ?, 'submitted')
            ");
            $stmt->execute([
                $bookingId,
                $customerId,
                $mandateData['mandate_type'] ?? 'emandate',
                $mandateData['bank_name'],
                $mandateData['bank_account_number'],
                $mandateData['ifsc_code'],
                $mandateData['account_holder_name'],
                $mandateData['mandate_amount'],
                $startDate,
                $endDate,
                $startDate, // first debit on registration day
            ]);

            $mandateId = (int)$this->db->lastInsertId();
            return ['success' => true, 'mandate_id' => $mandateId, 'status' => 'submitted'];
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::registerNachMandate] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process all due NACH auto-debits for today.
     * Called by cron: php scripts/run_nach_auto_debit.php
     */
    public function processNachAutoDebits(): array
    {
        $processed = 0;
        $failed = 0;
        $results = [];

        try {
            // Find all active mandates with next_debit_date <= today
            $stmt = $this->db->query("
                SELECT nm.*, pb.id AS pb_id
                FROM nach_mandates nm
                JOIN plot_bookings pb ON pb.id = nm.booking_id
                WHERE nm.status = 'approved' 
                  AND nm.next_debit_date <= CURDATE()
                  AND nm.end_date >= CURDATE()
            ");
            $mandates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($mandates as $mandate) {
                // Find the next unpaid installment for this booking
                $instStmt = $this->db->prepare("
                    SELECT * FROM booking_payment_schedules 
                    WHERE booking_id = ? AND status IN ('pending', 'partial') 
                    ORDER BY installment_number ASC LIMIT 1
                ");
                $instStmt->execute([$mandate['booking_id']]);
                $installment = $instStmt->fetch(PDO::FETCH_ASSOC);

                if (!$installment) {
                    // No more installments — cancel mandate
                    $this->db->prepare("UPDATE nach_mandates SET status = 'expired' WHERE id = ?")
                             ->execute([$mandate['id']]);
                    continue;
                }

                $debitAmount = min(
                    (float)$mandate['mandate_amount'],
                    (float)$installment['amount'] - (float)$installment['paid_amount']
                );

                if ($debitAmount <= 0) continue;

                // Log the debit attempt
                $logStmt = $this->db->prepare("
                    INSERT INTO nach_debit_log 
                    (mandate_id_ref, installment_id, debit_amount, debit_date, status)
                    VALUES (?, ?, ?, CURDATE(), 'initiated')
                ");
                $logStmt->execute([$mandate['id'], $installment['id'], $debitAmount]);
                $logId = (int)$this->db->lastInsertId();

                // In production, this would call the bank's NACH API.
                // For now, we mark as 'success' since mandate is approved.
                // The webhook/callback from the bank will confirm or reject.
                $debitSuccess = true; // placeholder — bank webhook will update

                if ($debitSuccess) {
                    // Record the payment
                    $this->recordPayment($installment['id'], [
                        'amount' => $debitAmount,
                        'payment_mode' => 'nach',
                        'status' => 'cleared',
                        'notes' => "NACH Auto-Debit — Mandate #{$mandate['id']}",
                    ]);

                    // Update debit log
                    $this->db->prepare("UPDATE nach_debit_log SET status = 'success' WHERE id = ?")
                             ->execute([$logId]);

                    // Update mandate
                    $this->db->prepare("
                        UPDATE nach_mandates 
                        SET last_debit_date = CURDATE(), 
                            total_debits = total_debits + 1,
                            total_debit_amount = total_debit_amount + ?,
                            next_debit_date = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
                        WHERE id = ?
                    ")->execute([$debitAmount, $mandate['id']]);

                    $processed++;
                    $results[] = ['mandate_id' => $mandate['id'], 'amount' => $debitAmount, 'status' => 'success'];
                } else {
                    $this->db->prepare("UPDATE nach_debit_log SET status = 'failed', failure_reason = 'Bank rejection' WHERE id = ?")
                             ->execute([$logId]);
                    $failed++;
                    $results[] = ['mandate_id' => $mandate['id'], 'amount' => $debitAmount, 'status' => 'failed'];
                }
            }
        } catch (Exception $e) {
            error_log('[BookingLifecycleService::processNachAutoDebits] ' . $e->getMessage());
        }

        return ['processed' => $processed, 'failed' => $failed, 'results' => $results];
    }

    /**
     * Get NACH mandate status for a booking.
     */
    public function getNachMandate(int $bookingId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM nach_mandates WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get penalty + NACH summary for customer dashboard.
     */
    public function getCustomerPaymentSummary(int $customerId): array
    {
        $out = [
            'total_overdue' => 0,
            'total_accrued_penalties' => 0,
            'worst_overdue_days' => 0,
            'overdue_installments' => [],
            'nach_mandate' => null,
            'next_debit_date' => null,
            'upcoming_installments' => [],
        ];

        try {
            // Overdue installments with penalties
            $stmt = $this->db->prepare("
                SELECT bps.*, pb.booking_number, p.plot_number, c.name AS colony_name,
                       DATEDIFF(CURDATE(), bps.due_date) AS days_overdue
                FROM booking_payment_schedules bps
                JOIN plot_bookings pb ON pb.id = bps.booking_id
                LEFT JOIN plots p ON p.id = pb.plot_id
                LEFT JOIN colonies c ON c.id = p.colony_id
                WHERE pb.customer_id = ? 
                  AND bps.status IN ('pending', 'partial')
                  AND bps.due_date < CURDATE()
                ORDER BY bps.due_date ASC
            ");
            $stmt->execute([$customerId]);
            $overdue = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($overdue as $inst) {
                $out['total_overdue'] += (float)$inst['amount'] - (float)$inst['paid_amount'];
                $out['total_accrued_penalties'] += (float)($inst['accrued_penalty'] ?? 0);
                $out['worst_overdue_days'] = max($out['worst_overdue_days'], (int)$inst['days_overdue']);
            }
            $out['overdue_installments'] = $overdue;

            // NACH mandate status
            $stmt = $this->db->prepare("
                SELECT * FROM nach_mandates 
                WHERE customer_id = ? AND status IN ('approved', 'submitted') 
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$customerId]);
            $out['nach_mandate'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($out['nach_mandate']) {
                $out['next_debit_date'] = $out['nach_mandate']['next_debit_date'];
            }

            // Upcoming installments (next 30 days)
            $stmt = $this->db->prepare("
                SELECT bps.*, pb.booking_number, p.plot_number, c.name AS colony_name
                FROM booking_payment_schedules bps
                JOIN plot_bookings pb ON pb.id = bps.booking_id
                LEFT JOIN plots p ON p.id = pb.plot_id
                LEFT JOIN colonies c ON c.id = p.colony_id
                WHERE pb.customer_id = ? 
                  AND bps.status IN ('pending', 'partial')
                  AND bps.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                ORDER BY bps.due_date ASC
            ");
            $stmt->execute([$customerId]);
            $out['upcoming_installments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('[BookingLifecycleService::getCustomerPaymentSummary] ' . $e->getMessage());
        }

        return $out;
    }
}
