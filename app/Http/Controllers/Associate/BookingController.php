<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateBookingController
 * Handles associate bookings and customer management
 */
class BookingController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * My bookings
     */
    public function myBookings()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND pb.tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $bookings = $db->fetchAll("
                SELECT pb.*, pl.plot_number, pl.area_sqft, col.name as colony_name, col.city,
                       CONCAT(col.name, ' - Plot #', pl.plot_number) as property_title,
                       COALESCE(u.name, pb.customer_name, 'Client') as customer_name,
                       COALESCE(u.email, pb.customer_email, '—') as customer_email,
                       COALESCE(u.phone, pb.customer_phone, '—') as customer_phone,
                       COALESCE(pb.total_plot_value, pb.agreement_value, pl.total_price, 0) as property_price,
                       COALESCE(pb.booking_amount, 0) as total_paid
                FROM plot_bookings pb
                JOIN plots pl ON pl.id = pb.plot_id
                LEFT JOIN users u ON u.id = pb.customer_id
                JOIN colonies col ON col.id = pl.colony_id
                WHERE (pb.associate_id = ? OR pb.associate_id IS NULL){$tidSql}
                ORDER BY pb.created_at DESC
            ", $params) ?: [];

            $stats = [
                'total' => count($bookings),
                'confirmed' => 0,
                'pending' => 0,
                'total_value' => 0
            ];
            foreach ($bookings as $b) {
                $status = strtolower($b['status'] ?? '');
                if (in_array($status, ['confirmed', 'completed', 'fully_paid', 'active', 'emi_active'])) {
                    $stats['confirmed']++;
                } elseif (in_array($status, ['pending', 'token_paid', 'reserved', 'draft'])) {
                    $stats['pending']++;
                }
                $stats['total_value'] += (float)($b['property_price'] ?? 0);
            }

            $this->render('associate/my_bookings', [
                'page_title' => 'My Bookings - Associate Portal',
                'page_description' => 'View your bookings',
                'bookings' => $bookings,
                'stats' => $stats,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateBookingController error: ' . $e->getMessage());
        }
    }

    /**
     * My customers
     */
    public function myCustomers()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $customers = $db->fetchAll("
                SELECT u.*, COUNT(pb.id) as booking_count
                FROM users u
                LEFT JOIN plot_bookings pb ON pb.customer_id = u.id AND pb.associate_id = ?
                WHERE u.role = 'customer' AND u.referred_by = ?{$tidSql}
                GROUP BY u.id
                ORDER BY u.created_at DESC
            ", array_merge([$userId, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : [])) ?: [];

            $this->render('associate/my_customers', [
                'page_title' => 'My Customers - Associate Portal',
                'page_description' => 'Manage your customers',
                'customers' => $customers,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateBookingController::myCustomers error: ' . $e->getMessage());
        }
    }

    /**
     * Customer detail
     */
    public function customerDetail($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $customer = $db->fetchOne("SELECT * FROM users WHERE id = ? AND role = 'customer' AND associate_id = ?{$tidSql} LIMIT 1", $params);

            if (!$customer) {
                $_SESSION['error'] = 'Customer not found or access denied';
                $this->redirect('/associate/customers');
                return;
            }

            // Get bookings for this customer
            $bookings = $db->fetchAll("
                SELECT pb.*, pl.plot_number, col.name as colony_name
                FROM plot_bookings pb
                JOIN plots pl ON pl.id = pb.plot_id
                JOIN colonies col ON col.id = pl.colony_id
                WHERE pb.customer_id = ? AND pb.associate_id = ?{$tidSql}
                ORDER BY pb.created_at DESC
            ", array_merge([$id, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : [])) ?: [];

            $this->render('associate/customer_detail', [
                'page_title' => 'Customer Detail - Associate Portal',
                'page_description' => 'View customer details',
                'customer' => $customer,
                'bookings' => $bookings,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateBookingController::customerDetail error: ' . $e->getMessage());
        }
    }

    /**
     * EMI Tracker
     */
    public function emiTracker()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $emis = $db->fetchAll("
                SELECT bps.*, pb.booking_number, u.name as customer_name, pl.plot_number
                FROM booking_payment_schedules bps
                JOIN plot_bookings pb ON pb.id = bps.booking_id
                LEFT JOIN users u ON u.id = pb.customer_id
                JOIN plots pl ON pl.id = pb.plot_id
                WHERE pb.associate_id = ? AND bps.due_date >= CURDATE() AND bps.status IN ('pending', 'partial')
                ORDER BY bps.due_date ASC
            ", $params) ?: [];

            $this->render('associate/emi_tracker', [
                'page_title' => 'EMI Tracker - Associate Portal',
                'page_description' => 'Track upcoming EMIs',
                'emis' => $emis,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateBookingController::emiTracker error: ' . $e->getMessage());
        }
    }

    /**
     * Payment history
     */
    public function paymentHistory()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $payments = $db->fetchAll("
                SELECT bp.*, pb.booking_number, u.name as customer_name, pl.plot_number
                FROM booking_payments bp
                JOIN plot_bookings pb ON pb.id = bp.booking_id
                LEFT JOIN users u ON u.id = pb.customer_id
                JOIN plots pl ON pl.id = pb.plot_id
                WHERE (pb.associate_id = ? OR pb.associate_id IS NULL){$tidSql}
                ORDER BY bp.payment_date DESC
            ", $params) ?: [];

            $this->render('associate/payment_history', [
                'page_title' => 'Payment History - Associate Portal',
                'page_description' => 'View payment history',
                'payments' => $payments,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateBookingController::paymentHistory error: ' . $e->getMessage());
        }
    }

    /**
     * Booking receipt
     */
    public function bookingReceipt($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND pb.tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $booking = $db->fetchOne("
                SELECT pb.*, 
                       COALESCE(u.name, pb.customer_name, 'Client') as customer_name,
                       COALESCE(u.email, pb.customer_email, '—') as customer_email,
                       COALESCE(u.phone, pb.customer_phone, '—') as customer_phone,
                       pl.plot_number, pl.area_sqft, col.name as colony_name, col.city,
                       CONCAT(col.name, ' - Plot #', pl.plot_number) as property_title,
                       COALESCE(pb.total_plot_value, pb.agreement_value, pl.total_price, 0) as property_price,
                       COALESCE(pb.booking_amount, 0) as booking_amount_paid
                FROM plot_bookings pb
                LEFT JOIN users u ON u.id = pb.customer_id
                JOIN plots pl ON pl.id = pb.plot_id
                JOIN colonies col ON col.id = pl.colony_id
                WHERE pb.id = ? AND (pb.associate_id = ? OR pb.associate_id IS NULL){$tidSql} LIMIT 1
            ", $params);

            if (!$booking) {
                $_SESSION['error'] = 'Booking not found or access denied';
                $this->redirect('/associate/bookings');
                return;
            }

            // Receipts
            $tidParam = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];
            $receipts = $db->fetchAll("
                SELECT * FROM booking_payment_receipts
                WHERE booking_id = ?" . (TenantContext::getId() > 1 ? " AND tenant_id = ?" : "") . "
                ORDER BY receipt_date DESC, created_at DESC
            ", array_merge([$id], $tidParam)) ?: [];

            // Fallback to booking_payments if receipts table empty
            if (empty($receipts)) {
                $receipts = $db->fetchAll("
                    SELECT id, booking_id, amount, payment_mode, payment_date as receipt_date,
                           status, transaction_id as receipt_number, created_at
                    FROM booking_payments
                    WHERE booking_id = ?" . (TenantContext::getId() > 1 ? " AND tenant_id = ?" : "") . "
                    ORDER BY payment_date DESC
                ", array_merge([$id], $tidParam)) ?: [];
            }

            // Schedule
            $schedule = $db->fetchAll("
                SELECT * FROM booking_payment_schedules
                WHERE booking_id = ?" . (TenantContext::getId() > 1 ? " AND tenant_id = ?" : "") . "
                ORDER BY installment_no ASC, due_date ASC
            ", array_merge([$id], $tidParam)) ?: [];

            // Video Consent / Document Signature verification
            $videoConsent = null;
            try {
                $videoConsent = $db->fetchOne("
                    SELECT * FROM booking_video_consents
                    WHERE booking_id = ? ORDER BY id DESC LIMIT 1
                ", [$id]) ?: null;
            } catch (\Throwable $ve) {
                // Ignore if table variance
            }

            $this->render('associate/booking_receipt', [
                'page_title' => 'Booking Receipt & Schedule - Associate Portal',
                'page_description' => 'Official Booking Receipt and EMI Repayment Schedule',
                'booking' => $booking,
                'receipts' => $receipts,
                'payments' => $receipts,
                'schedule' => $schedule,
                'video_consent' => $videoConsent,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateBookingController::bookingReceipt error: ' . $e->getMessage());
        }
    }
}

