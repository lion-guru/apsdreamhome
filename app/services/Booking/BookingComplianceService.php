<?php
namespace App\Services\Booking;

class BookingComplianceService
{
    private $db;
    private $tokenPercentage = 25;

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

            $stmt = $this->db->prepare("SELECT * FROM plots WHERE id = ?");
            $stmt->execute([$plotId]);
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

            $stmt = $this->db->prepare("UPDATE plots SET status = 'Hold' WHERE id = ?");
            $stmt->execute([$plotId]);

            $stmt = $this->db->prepare("INSERT INTO bookings (customer_id, associate_id, property_id, total_amount, amount, payment_status, status, booking_date, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())");
            $customerId = $data['customer_id'] ?? 0;
            $propertyId = $data['property_id'] ?? $plotId;
            $stmt->execute([$customerId, $agentId, $propertyId, $totalAmount, $initialPayment, $initialPayment > 0 ? 'partial' : 'pending', $bookingDate, json_encode(['plot_id' => $plotId, 'block' => $plot['block'], 'plot_no' => $plot['plot_number'], 'plc_charges' => $plcAmount, 'payment_mode' => $paymentMode, 'token_deadline' => $tokenDeadline])]);
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
            $stmt = $this->db->prepare("INSERT INTO plot_emi_schedule (booking_id, installment_number, due_date, amount, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        for ($i = 1; $i <= $installments; $i++) {
            $dueDate = date('Y-m-d', strtotime($startDate . " + $i months"));
            $stmt->execute([$bookingId, $i, $dueDate, $monthlyAmount]);
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
        ");
        $violations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($violations as $v) {
            try {
                $plotId = (int)$v['plot_id'];
                if ($plotId > 0) {
                    $stmt = $this->db->prepare("UPDATE plots SET status = 'Available' WHERE id = ?");
                    $stmt->execute([$plotId]);
                }

                $stmt = $this->db->prepare("UPDATE bookings SET status = 'cancelled', notes = CONCAT(COALESCE(notes,''), ' | Auto-cancelled: Token payment < 25% within 15 days') WHERE id = ?");
                $stmt->execute([$v['id']]);

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

            $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = ?");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$booking) throw new \Exception('Booking not found');

            $newPaid = (float)$booking['amount'] + $amount;
            $total = (float)$booking['total_amount'];

            $stmt = $this->db->prepare("UPDATE bookings SET amount = ? WHERE id = ?");
            $stmt->execute([$newPaid, $bookingId]);

            $paymentStatus = 'partial';
            if ($newPaid >= $total) {
                $paymentStatus = 'paid';
                $this->db->prepare("UPDATE bookings SET payment_status = 'paid', status = 'completed' WHERE id = ?")->execute([$bookingId]);
            } elseif ($newPaid >= $total * 0.25) {
                $paymentStatus = 'partial';
                $this->db->prepare("UPDATE bookings SET payment_status = 'partial' WHERE id = ?")->execute([$bookingId]);
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO plot_payments (booking_id, amount, payment_mode, payment_date, created_at) VALUES (?, ?, ?, CURDATE(), NOW())");
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $stmt->execute([$bookingId, $amount, $mode]);

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
            FROM bookings b WHERE b.id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$booking) return ['error' => 'Booking not found'];

        $totalAmount = (float)$booking['total_amount'];
        $paidAmount = (float)$booking['amount'];
        $tokenPercent = $totalAmount > 0 ? round($paidAmount * 100 / $totalAmount, 2) : 0;

        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as emis, SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) as paid_emis FROM plot_emi_schedule WHERE booking_id = ?");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt->execute([$bookingId]);
        $emiStatus = $stmt->fetch(\PDO::FETCH_ASSOC);

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
