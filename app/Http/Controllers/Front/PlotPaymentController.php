<?php
namespace App\Http\Controllers\Front;

use App\Services\Notification\BookingNotificationService;

/**
 * PlotPaymentController — booking token-payment flow.
 * Routes: /booking/{id}/pay (GET form + POST processing)
 */
class PlotPaymentController extends PlotBaseController
{
    /**
     * Show payment form for booking token amount
     */
    public function payBooking($bookingId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $booking = $this->getBookingWithDetails((int)$bookingId, (int)$user['id']);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/user/dashboard');
        }

        $requiredToken = $this->calculateTokenAmount((float)$booking['total_amount']);
        $paidSoFar = (float)$booking['amount'];
        $tokenDue = max(0, $requiredToken - $paidSoFar);
        $tokenPercent = $requiredToken > 0 ? min(100, round(($paidSoFar / $requiredToken) * 100)) : 0;

        $this->layout = 'layouts/customer';
        $this->render('pages/booking_pay', [
            'page_title' => 'Pay Token - Booking #' . $bookingId,
            'booking' => $booking,
            'requiredToken' => $requiredToken,
            'tokenDue' => $tokenDue,
            'tokenPercent' => $tokenPercent,
            'user' => $user,
            'idempotency_key' => bin2hex(random_bytes(16)),
        ]);
    }

    /**
     * Process token payment from customer (POST, CSRF enforced by
     * BaseController). Tenant-scoped, validated, throttled, idempotent.
     */
    public function processPayment($bookingId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $bookingId = (int)$bookingId;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/booking/' . $bookingId . '/pay');
        }

        // Tenant-scoped + ownership-checked fetch
        $booking = $this->getBookingWithDetails($bookingId, (int)$user['id']);
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/user/dashboard');
        }

        // Per-booking throttle (abuse prevention)
        if (!$this->throttlePayment($bookingId, (int)$user['id'])) {
            $this->setFlash('error', 'Too many payment attempts. Please wait a few minutes and try again.');
            return $this->redirect('/booking/' . $bookingId . '/pay');
        }

        // Validated input
        $amount = (float)($_POST['amount'] ?? 0);
        $mode = $_POST['mode'] ?? 'online';
        if (!in_array($mode, self::ALLOWED_PAYMENT_MODES, true)) {
            $mode = 'online';
        }
        $reference = $this->sanitizeReference((string)($_POST['reference'] ?? ''));
        $idempotencyKey = preg_replace('/[^a-f0-9]/i', '', (string)($_POST['idempotency_key'] ?? ''));
        if (strlen($idempotencyKey) > 64) $idempotencyKey = substr($idempotencyKey, 0, 64);
        $tid = (int)$this->tenantId();

        if ($amount <= 0) {
            $this->setFlash('error', 'Invalid payment amount');
            return $this->redirect('/booking/' . $bookingId . '/pay');
        }

        $requiredToken = $this->calculateTokenAmount((float)$booking['total_amount']);
        $paidSoFar = (float)$booking['amount'];
        if (($paidSoFar + $amount) > $requiredToken) {
            $this->setFlash('error', 'Amount exceeds required token. Maximum: ₹' . number_format($requiredToken - $paidSoFar, 2));
            return $this->redirect('/booking/' . $bookingId . '/pay');
        }

        // DDL guard runs BEFORE the transaction: MySQL ALTER TABLE causes an
        // implicit commit, which would silently commit a half-done payment.
        $this->ensureIdempotencyColumn();

        $this->db->beginTransaction();
        try {
            // Idempotency: same key OR same reference+amount already recorded
            // => treat as duplicate submit, do not double-charge.
            if ($idempotencyKey !== '') {
                try {
                    $dup = $this->db->fetchRow(
                        "SELECT id FROM booking_emis WHERE booking_id = ? AND (transaction_id = ? OR notes LIKE ?) LIMIT 1",
                        [$bookingId, 'IDEM:' . $idempotencyKey, '%IDEM:' . $idempotencyKey . '%']
                    );
                    if ($dup) {
                        $this->db->commit();
                        $this->setFlash('success', 'Payment already recorded. No duplicate charge was made.');
                        return $this->redirect('/booking/' . $bookingId . '/confirmation');
                    }
                } catch (\Throwable $e) {
                    error_log('PlotPaymentController::processPayment idem-check: ' . $e->getMessage());
                }
            }
            if ($reference !== '') {
                $dupRef = $this->db->fetchRow(
                    "SELECT id FROM booking_emis WHERE booking_id = ? AND transaction_id = ? AND amount = ? LIMIT 1",
                    [$bookingId, $reference, $amount]
                );
                if ($dupRef) {
                    $this->db->commit();
                    $this->setFlash('success', 'Payment already recorded. No duplicate charge was made.');
                    return $this->redirect('/booking/' . $bookingId . '/confirmation');
                }
            }

            $newPaid = $paidSoFar + $amount;

            if ($newPaid >= $requiredToken) {
                $paymentStatus = 'paid';
            } elseif ($newPaid > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            $updSql = "UPDATE bookings SET amount = ?, payment_status = ? WHERE id = ? AND customer_id = ?"
                . ($tid > 1 ? ' AND tenant_id = ?' : '');
            $updParams = $tid > 1 ? [$newPaid, $paymentStatus, $bookingId, $user['id'], $tid] : [$newPaid, $paymentStatus, $bookingId, $user['id']];
            $affected = $this->db->execute($updSql, $updParams)->rowCount();
            if ($affected === 0) {
                throw new \RuntimeException('Booking update conflict — please retry.');
            }

            // NOTE: column is transaction_id (booking_emis has no transaction_ref).
            $this->db->insert('booking_emis', [
                'booking_id' => $bookingId,
                'installment_no' => 0,
                'due_date' => date('Y-m-d'),
                'amount' => $amount,
                'paid_amount' => $amount,
                'paid_date' => date('Y-m-d'),
                'payment_method' => $mode,
                'transaction_id' => $reference !== '' ? $reference : ($idempotencyKey !== '' ? 'IDEM:' . $idempotencyKey : null),
                'notes' => $idempotencyKey !== '' ? 'IDEM:' . $idempotencyKey : null,
                'status' => 'paid',
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->commit();

            try {
                $notifService = new BookingNotificationService();
                $notifService->ensureNotificationsTable();
                $notifService->notifyPaymentReceived($bookingId, $booking, $amount, $mode);
            } catch (\Throwable $e) {
                error_log('PlotPaymentController::processPayment notify: ' . $e->getMessage());
            }

            $this->setFlash('success', 'Payment of ₹' . number_format($amount, 2) . ' received successfully!');
            return $this->redirect('/booking/' . $bookingId . '/confirmation');
        } catch (\Throwable $e) {
            try { $this->db->rollBack(); } catch (\Throwable $ignored) { error_log($ignored->getMessage()); }
            error_log('PlotPaymentController::processPayment error: ' . $e->getMessage());
            $this->setFlash('error', 'We could not process your payment. Please try again.');
            return $this->redirect('/booking/' . $bookingId . '/pay');
        }
    }
}
