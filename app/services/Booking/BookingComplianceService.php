<?php
namespace App\Services\Booking;

use App\Traits\ServiceTenantTrait;

class BookingComplianceService
{
    use ServiceTenantTrait;

    private $db;
    private $tokenPercentage = 25;
    private $tokenDueDays = 15;

    /**
     * Token percentage of deal price (percent units, e.g. 25).
     * Single source of truth consumed by the Front plot flow
     * (PlotBaseController and its PlotIndex/PlotBooking/PlotPayment children).
     */
    public function getTokenPercentage(): float
    {
        return (float)$this->tokenPercentage;
    }

    /**
     * Days given to pay the token amount after booking.
     */
    public function getTokenDueDays(): int
    {
        return (int)$this->tokenDueDays;
    }

    /**
     * Token amount for a deal price.
     */
    public function calculateTokenAmount(float $dealPrice): float
    {
        return round($dealPrice * ($this->tokenPercentage / 100), 2);
    }

    /**
     * Create the initial token payment schedule for a booking.
     *
     * This is the configurable payment-plan entry point consumed by
     * Front\PlotBookingController::storeBooking(). Plan overrides allow
     * per-booking customization without code changes:
     *   ['token_pct' => 25, 'due_days' => 15]
     *
     * MUST be called inside the caller's DB transaction (this method never
     * begins/commits — it shares the singleton PDO connection, so it
     * participates in the caller's transaction). Throws on DB failure so the
     * caller can roll back.
     *
     * @return array ['emi_id'=>int, 'token_amount'=>float, 'due_date'=>string,
     *                'token_pct'=>float, 'due_days'=>int]
     */
    public function createTokenSchedule(int $bookingId, float $dealPrice, array $plan = []): array
    {
        $tokenPct = isset($plan['token_pct']) ? (float)$plan['token_pct'] : (float)$this->tokenPercentage;
        $dueDays = isset($plan['due_days']) ? (int)$plan['due_days'] : (int)$this->tokenDueDays;
        if ($tokenPct <= 0 || $tokenPct > 100) $tokenPct = (float)$this->tokenPercentage;
        if ($dueDays < 0 || $dueDays > 365) $dueDays = (int)$this->tokenDueDays;

        $tokenAmount = round($dealPrice * ($tokenPct / 100), 2);
        $dueDate = date('Y-m-d', strtotime('+' . $dueDays . ' days'));

        $stmt = $this->db->prepare(
            "INSERT INTO booking_emis (booking_id, installment_no, due_date, amount, status, tenant_id, created_at) VALUES (?, 1, ?, ?, 'pending', ?, ?)"
        );
        $stmt->execute([$bookingId, $dueDate, $tokenAmount, $this->tenantId(), date('Y-m-d H:i:s')]);

        return [
            'emi_id' => (int)$this->db->lastInsertId(),
            'token_amount' => $tokenAmount,
            'due_date' => $dueDate,
            'token_pct' => $tokenPct,
            'due_days' => $dueDays,
        ];
    }

    /** Default balance-installment count (monthly, 0% interest). */
    public const DEFAULT_BALANCE_INSTALLMENTS = 24;
    /** Allowed balance frequencies. */
    public const BALANCE_FREQUENCIES = ['monthly', 'quarterly'];

    /**
     * Create the post-token balance schedule for a booking.
     *
     * Business rules (Session 105):
     * - Balance = deal price − token amount, split into N EQUAL monthly
     *   (or quarterly) installments at 0% interest — the standard
     *   interest-free developer scheme (cf. company-loan 12/36-mo offers).
     * - Token row stays installment_no = 1; balance rows are 2..N+1; paid
     *   receipts use 0 — no number collisions, payment idempotency untouched.
     * - Rounding remainder goes on the LAST installment so the total is
     *   paisa-exact (no leakage across EMIs).
     * - First balance due = one frequency-step after the token due date.
     * - Defensive no-op: if balance rows already exist for the booking,
     *   nothing is inserted (safe to call twice).
     *
     * Plan overrides: ['installments' => 24, 'frequency' => 'monthly',
     *   'anchor_date' => 'Y-m-d']. installments = 0 means token-only.
     *
     * MUST be called inside the caller's DB transaction (same pattern as
     * createTokenSchedule). Throws on DB failure so the caller can roll back.
     *
     * @return array ['generated'=>int, 'balance'=>float, 'per_emi'=>float,
     *                'frequency'=>string, 'first_due'=>?string, 'last_due'=>?string]
     */
    public function createBalanceSchedule(int $bookingId, float $dealPrice, float $tokenAmount, array $plan = []): array
    {
        $n = isset($plan['installments']) ? (int)$plan['installments'] : self::DEFAULT_BALANCE_INSTALLMENTS;
        if ($n < 0) $n = 0;
        if ($n > 120) $n = 120;
        $frequency = $plan['frequency'] ?? 'monthly';
        if (!in_array($frequency, self::BALANCE_FREQUENCIES, true)) $frequency = 'monthly';

        $balance = round($dealPrice - $tokenAmount, 2);
        if ($n === 0 || $balance <= 0) {
            return ['generated' => 0, 'balance' => max(0.0, $balance), 'per_emi' => 0.0,
                    'frequency' => $frequency, 'first_due' => null, 'last_due' => null];
        }

        // Defensive: never double-generate a schedule for one booking.
        $existingStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM booking_emis WHERE booking_id = ? AND installment_no >= 2"
        );
        $existingStmt->execute([$bookingId]);
        $existing = (int)$existingStmt->fetchColumn();
        if ($existing > 0) {
            return ['generated' => 0, 'balance' => $balance, 'per_emi' => 0.0,
                    'frequency' => $frequency, 'first_due' => null, 'last_due' => null];
        }

        $anchor = $plan['anchor_date'] ?? null;
        if (!is_string($anchor) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $anchor)) {
            $anchor = date('Y-m-d', strtotime('+' . (int)$this->tokenDueDays . ' days'));
        }
        $stepMonths = $frequency === 'quarterly' ? 3 : 1;

        // Equal splits, remainder absorbed by the last installment.
        $base = floor(($balance / $n) * 100) / 100;
        $last = round($balance - ($base * ($n - 1)), 2);

        $stmt = $this->db->prepare(
            "INSERT INTO booking_emis (booking_id, installment_no, due_date, amount, status, tenant_id, created_at) VALUES (?, ?, ?, ?, 'pending', ?, ?)"
        );
        $now = date('Y-m-d H:i:s');
        $tid = $this->tenantId();
        $firstDue = null; $lastDue = null;
        for ($i = 1; $i <= $n; $i++) {
            $due = date('Y-m-d', strtotime($anchor . ' + ' . ($i * $stepMonths) . ' months'));
            $amt = ($i === $n) ? $last : $base;
            $stmt->execute([$bookingId, $i + 1, $due, $amt, $tid, $now]);
            if ($i === 1) $firstDue = $due;
            $lastDue = $due;
        }

        return ['generated' => $n, 'balance' => $balance, 'per_emi' => $base,
                'frequency' => $frequency, 'first_due' => $firstDue, 'last_due' => $lastDue];
    }

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
    }

    public function createBooking(array $data): array
    {
        try {
            $this->db->beginTransaction();

            $plotId = (int)$data['plot_id'];
            $customerName = $data['customer_name'];
            $agentId = (int)($data['agent_id'] ?? 0);
            $paymentMode = $data['payment_mode'] ?? 'Full';
            $bookingDate = $data['booking_date'] ?? date('Y-m-d');

            $stmt = $this->db->prepare("SELECT * FROM plots WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$plotId, $this->tenantId()]);
            $plot = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$plot) throw new \Exception('Plot not found');
            if ($plot['status'] !== 'Available') throw new \Exception('Plot is not available');

            $plcAmount = $this->calculatePLC($plot);
            $baseAmount = (float)$plot['total_price'];
            $totalAmount = $baseAmount + $plcAmount;

            $tokenAmount = $totalAmount * ($this->tokenPercentage / 100);
            $tokenDeadline = date('Y-m-d', strtotime($bookingDate . ' + 15 days'));

            $initialPayment = (float)($data['initial_payment'] ?? 0);
            if (in_array($paymentMode, ['EMI', 'Offer']) && $initialPayment < $tokenAmount) {
                throw new \Exception("Initial payment must be at least 25% (₹" . number_format($tokenAmount, 2) . ") for $paymentMode bookings");
            }

            $stmt = $this->db->prepare("UPDATE plots SET status = 'Hold' WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$plotId, $this->tenantId()]);

            $tenantData = $this->tenantInsertData();
            $customerId = $data['customer_id'] ?? 0;
            $propertyId = $data['property_id'] ?? $plotId;
            $columns = array_merge(['customer_id', 'associate_id', 'property_id', 'total_amount', 'amount', 'payment_status', 'status', 'booking_date', 'notes'], array_keys($tenantData));
            $values = array_merge([$customerId, $agentId, $propertyId, $totalAmount, $initialPayment, $initialPayment > 0 ? 'partial' : 'pending', $bookingDate, json_encode(['plot_id' => $plotId, 'block' => $plot['block'], 'plot_no' => $plot['plot_number'], 'plc_charges' => $plcAmount, 'payment_mode' => $paymentMode, 'token_deadline' => $tokenDeadline])], array_values($tenantData));
            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $sql = "INSERT INTO bookings ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            $bookingId = $this->db->lastInsertId();

            if (in_array($paymentMode, ['EMI', 'Offer'])) {
                $this->generateEMISchedule($bookingId, $totalAmount - $initialPayment, $bookingDate, $paymentMode);
            }

            $this->db->commit();

            return [
                'success' => true,
                'booking_id' => $bookingId,
                'total_amount' => $totalAmount,
                'plc_charges' => $plcAmount,
                'initial_payment_required' => $tokenAmount,
                'token_deadline' => $tokenDeadline,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function calculatePLC(array $plot): float
    {
        $basePrice = (float)$plot['total_price'];
        $plcCharges = 0;

        if (!empty($plot['corner_plot'])) {
            $plcCharges += $basePrice * 0.08;
        }

        $plotNum = (int)$plot['plot_number'];
        $cornerPlots = [1, 2, 3, 4, 5, 6, 7, 8, 96, 97, 98, 99, 100, 101, 102, 103];
        if (in_array($plotNum, $cornerPlots)) {
            $plcCharges += $basePrice * 0.12;
        } elseif (in_array($plotNum, range(50, 55))) {
            $plcCharges += $basePrice * 0.12;
        }

        $plcCharges += $basePrice * 0.05;

        return round($plcCharges, 2);
    }

    private function generateEMISchedule(int $bookingId, float $remainingAmount, string $startDate, string $mode): void
    {
        $installments = ($mode === 'EMI') ? 60 : 24;
        $monthlyAmount = round($remainingAmount / $installments, 2);

        try {
            $stmt = $this->db->prepare("INSERT INTO plot_emi_schedule (booking_id, installment_number, due_date, amount, status, tenant_id, created_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        for ($i = 1; $i <= $installments; $i++) {
            $dueDate = date('Y-m-d', strtotime($startDate . " + $i months"));
            $stmt->execute([$bookingId, $i, $dueDate, $monthlyAmount, $this->tenantId()]);
        }
    }

    public function enforceTokenRule(): array
    {
        $released = 0;
        $warnings = 0;

        $stmt = $this->db->query("
            SELECT b.id, b.total_amount, b.amount as paid_amount, b.notes,
                   JSON_UNQUOTE(JSON_EXTRACT(b.notes, '$.plot_id')) as plot_id
            FROM bookings b
            WHERE b.status = 'pending'
              AND b.created_at <= DATE_SUB(CURDATE(), INTERVAL 16 DAY)
              AND (b.amount / NULLIF(b.total_amount, 0)) < 0.25
              " . $this->tenantSql());
        $violations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($violations as $v) {
            try {
                $plotId = (int)$v['plot_id'];
                if ($plotId > 0) {
                    $stmt = $this->db->prepare("UPDATE plots SET status = 'Available' WHERE id = ? AND tenant_id = ?");
                    $stmt->execute([$plotId, $this->tenantId()]);
                }

$stmt = $this->db->prepare("UPDATE bookings SET status = 'cancelled', notes = CONCAT(COALESCE(notes,''), ' | Auto-cancelled: Token payment < 25% within 15 days') WHERE id = ? AND tenant_id = ?");
                    $stmt->execute([$v['id'], $this->tenantId()]);

                error_log("BookingCompliance: Booking #{$v['id']} auto-cancelled. Plot #$plotId released back to Available.");
                $released++;

            } catch (\Exception $e) {
                error_log("BookingCompliance: Error processing booking #{$v['id']}: " . $e->getMessage());
                $warnings++;
            }
        }

        return ['released_plots' => $released, 'warnings' => $warnings];
    }

    public function recordPayment(int $bookingId, float $amount, string $mode = 'cash'): array
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$bookingId, $this->tenantId()]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$booking) throw new \Exception('Booking not found');

            $newPaid = (float)$booking['amount'] + $amount;
            $total = (float)$booking['total_amount'];

            $stmt = $this->db->prepare("UPDATE bookings SET amount = ? WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$newPaid, $bookingId, $this->tenantId()]);

            $paymentStatus = 'partial';
            if ($newPaid >= $total) {
                $paymentStatus = 'paid';
                $this->db->prepare("UPDATE bookings SET payment_status = 'paid', status = 'completed' WHERE id = ? AND tenant_id = " . $this->tenantId())->execute([$bookingId]);
            } elseif ($newPaid >= $total * 0.25) {
                $paymentStatus = 'partial';
                $this->db->prepare("UPDATE bookings SET payment_status = 'partial' WHERE id = ? AND tenant_id = " . $this->tenantId())->execute([$bookingId]);
            }

try {
                $stmt = $this->db->prepare("INSERT INTO plot_payments (booking_id, amount, payment_mode, payment_date, tenant_id, created_at) VALUES (?, ?, ?, CURDATE(), ?, NOW())");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt->execute([$bookingId, $amount, $mode, $this->tenantId()]);

            $this->db->commit();

            return [
                'success' => true,
                'booking_id' => $bookingId,
                'paid_amount' => $newPaid,
                'total_amount' => $total,
                'payment_status' => $paymentStatus,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getBookingStatus(int $bookingId): array
    {
        $stmt = $this->db->prepare("SELECT b.*,
            JSON_UNQUOTE(JSON_EXTRACT(b.notes, '$.plot_id')) as plot_id,
            JSON_UNQUOTE(JSON_EXTRACT(b.notes, '$.token_deadline')) as token_deadline,
            JSON_UNQUOTE(JSON_EXTRACT(b.notes, '$.payment_mode')) as payment_mode
            FROM bookings b WHERE b.id = ?" . $this->tenantSql());
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$booking) return ['error' => 'Booking not found'];

        $totalAmount = (float)$booking['total_amount'];
        $paidAmount = (float)$booking['amount'];
        $tokenPercent = $totalAmount > 0 ? round($paidAmount * 100 / $totalAmount, 2) : 0;

try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as emis, SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) as paid_emis FROM plot_emi_schedule WHERE booking_id = ?" . $this->tenantSql());
            $stmt->execute([$bookingId]);
            $emiStatus = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $emiStatus = ['emis' => 0, 'paid_emis' => 0];
        }

        return [
            'booking' => $booking,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'token_percentage' => $tokenPercent,
            'token_met' => $tokenPercent >= $this->tokenPercentage,
            'token_deadline' => $booking['token_deadline'] ?? 'N/A',
            'emi_count' => $emiStatus['emis'] ?? 0,
            'paid_emis' => $emiStatus['paid_emis'] ?? 0,
        ];
    }
}
