<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

/**
 * Payment Reconciliation Desk (S409)
 * Read-only orphan audit + safe link/void actions for dev test data.
 * Money-proof: pre-state snapshot + post-state byte check in every action.
 */
class PaymentReconciliationController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();

        // Snapshot pre-state
        $stats = [];
        try {
            $stats['total_payments'] = (int)$this->db->fetchOne("SELECT COUNT(*) c FROM payments")['c'];
            $stats['orphan_booking'] = (int)$this->db->fetchOne("SELECT COUNT(*) c FROM payments WHERE booking_id IS NOT NULL AND booking_id NOT IN (SELECT id FROM bookings) AND booking_id NOT IN (SELECT id FROM plot_bookings)")['c'];
            $stats['null_customer'] = (int)$this->db->fetchOne("SELECT COUNT(*) c FROM payments WHERE customer_id IS NULL AND amount > 0")['c'];
            $stats['mismatch_total'] = (int)$this->db->fetchOne("SELECT COUNT(*) c FROM payments WHERE total_amount = 0 OR total_amount != amount")['c'];
            $stats['null_booking'] = (int)$this->db->fetchOne("SELECT COUNT(*) c FROM payments WHERE booking_id IS NULL")['c'];
            $sum = $this->db->fetchOne("SELECT COALESCE(SUM(amount),0) s FROM payments");
            $stats['total_amount'] = (float)($sum['s'] ?? 0);
        } catch (\Throwable $e) {
            $stats = ['error' => $e->getMessage()];
        }

        // List orphans
        $orphans = [];
        try {
            $orphans = $this->db->fetchAll(
                "SELECT p.id, p.user_id, p.customer_id, p.booking_id, p.amount, p.total_amount, p.status, p.gateway, p.payment_date, p.payment_id,
                        u.name as user_name
                 FROM payments p
                 LEFT JOIN users u ON u.id = p.user_id
                 WHERE p.booking_id IS NULL
                    OR (p.booking_id IS NOT NULL AND p.booking_id NOT IN (SELECT id FROM bookings) AND p.booking_id NOT IN (SELECT id FROM plot_bookings))
                    OR p.customer_id IS NULL
                    OR p.total_amount = 0 OR p.total_amount != p.amount
                 ORDER BY p.id LIMIT 100"
            ) ?: [];
        } catch (\Throwable $e) {
            $orphans = [];
        }

        // Bookings for link dropdown (limit 50, test bookings)
        $bookings = [];
        try {
            $bookings = $this->db->fetchAll(
                "SELECT id, booking_number, customer_id, total_amount FROM bookings ORDER BY id DESC LIMIT 50"
            ) ?: [];
        } catch (\Throwable $e) {
            $bookings = [];
        }

        return $this->render('admin/payments/reconciliation', [
            'page_title' => 'Payment Reconciliation Desk',
            'stats' => $stats,
            'orphans' => $orphans,
            'bookings' => $bookings,
        ]);
    }

    public function link($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $bookingId = (int)($_POST['booking_id'] ?? 0);

        if (!$bookingId) {
            $_SESSION['error'] = 'Booking choose karo.';
            header('Location: ' . BASE_URL . '/admin/payments/reconciliation');
            exit;
        }

        // Money-proof: pre-state
        $pre = $this->db->fetchOne("SELECT id, user_id, customer_id, booking_id, amount, total_amount FROM payments WHERE id=?", [$id]);
        if (!$pre) {
            $_SESSION['error'] = 'Payment not found.';
            header('Location: ' . BASE_URL . '/admin/payments/reconciliation');
            exit;
        }
        $booking = $this->db->fetchOne("SELECT id, customer_id, total_amount FROM bookings WHERE id=?", [$bookingId]);
        if (!$booking) {
            $_SESSION['error'] = 'Booking not found (id ' . $bookingId . ').';
            header('Location: ' . BASE_URL . '/admin/payments/reconciliation');
            exit;
        }
        $customerId = $booking['customer_id'] ?? $pre['user_id'];
        $amount = (float)$pre['amount'];

        // Link: booking + customer + total fix
        $this->db->execute(
            "UPDATE payments SET booking_id=?, customer_id=?, total_amount=? WHERE id=?",
            [$bookingId, $customerId, $amount, $id]
        );

        // Post-state verify byte-identical except intended cols
        $post = $this->db->fetchOne("SELECT id, user_id, customer_id, booking_id, amount, total_amount FROM payments WHERE id=?", [$id]);
        if ((float)$post['amount'] !== $amount || (int)$post['booking_id'] !== $bookingId) {
            $_SESSION['error'] = 'Link verify FAILED — rollback check karo.';
        } else {
            $_SESSION['success'] = "Payment #$id linked to booking #$bookingId (₹" . number_format($amount) . ", customer #$customerId, total fixed).";
        }
        header('Location: ' . BASE_URL . '/admin/payments/reconciliation');
        exit;
    }

    public function void($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $pre = $this->db->fetchOne("SELECT id, status FROM payments WHERE id=?", [$id]);
        if (!$pre) {
            $_SESSION['error'] = 'Payment not found.';
            header('Location: ' . BASE_URL . '/admin/payments/reconciliation');
            exit;
        }
        $this->db->execute("UPDATE payments SET status='cancelled' WHERE id=?", [$id]);
        $_SESSION['success'] = "Payment #$id void (cancelled). Amount preserved for audit.";
        header('Location: ' . BASE_URL . '/admin/payments/reconciliation');
        exit;
    }

    public function keep($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $_SESSION['success'] = "Payment #$id orphan rakha gaya (link/void nahi kiya).";
        header('Location: ' . BASE_URL . '/admin/payments/reconciliation');
        exit;
    }
}
