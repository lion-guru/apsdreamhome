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
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $bookings = $db->fetchAll("
                SELECT pb.*, pl.plot_number, pl.area_sqft, c.name as customer_name, c.email, c.phone,
                       col.name as colony_name
                FROM plot_bookings pb
                JOIN plots pl ON pl.id = pb.plot_id
                JOIN customers c ON c.id = pb.customer_id
                JOIN colonies col ON col.id = pl.colony_id
                WHERE pb.associate_id = ?{$tidSql}
                ORDER BY pb.created_at DESC
            ", $params) ?: [];

            $this->render('associate/my_bookings', [
                'page_title' => 'My Bookings - Associate Portal',
                'page_description' => 'View your bookings',
                'bookings' => $bookings,
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
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $customers = $db->fetchAll("
                SELECT c.*, COUNT(pb.id) as booking_count
                FROM customers c
                LEFT JOIN plot_bookings pb ON pb.customer_id = c.id AND pb.associate_id = ?
                WHERE c.associate_id = ?{$tidSql}
                GROUP BY c.id
                ORDER BY c.created_at DESC
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
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ? AND associate_id = ?{$tidSql} LIMIT 1", $params);

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
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $emis = $db->fetchAll("
                SELECT bps.*, pb.booking_number, c.name as customer_name, pl.plot_number
                FROM booking_payment_schedules bps
                JOIN plot_bookings pb ON pb.id = bps.booking_id
                JOIN customers c ON c.id = pb.customer_id
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
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $payments = $db->fetchAll("
                SELECT bp.*, pb.booking_number, c.name as customer_name, pl.plot_number
                FROM booking_payments bp
                JOIN plot_bookings pb ON pb.id = bp.booking_id
                JOIN customers c ON c.id = pb.customer_id
                JOIN plots pl ON pl.id = pb.plot_id
                WHERE pb.associate_id = ?{$tidSql}
                ORDER BY bp.created_at DESC
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
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $booking = $db->fetchOne("
                SELECT pb.*, c.name as customer_name, c.email, c.phone,
                       pl.plot_number, pl.area_sqft, col.name as colony_name
                FROM plot_bookings pb
                JOIN customers c ON c.id = pb.customer_id
                JOIN plots pl ON pl.id = pb.plot_id
                JOIN colonies col ON col.id = pl.colony_id
                WHERE pb.id = ? AND pb.associate_id = ?{$tidSql} LIMIT 1
            ", $params);

            if (!$booking) {
                $_SESSION['error'] = 'Booking not found or access denied';
                $this->redirect('/associate/bookings');
                return;
            }

            $payments = $db->fetchAll("
                SELECT * FROM booking_payments
                WHERE booking_id = ?{$tidSql} ORDER BY created_at DESC
            ", array_merge([$id], TenantContext::getId() > 1 ? [TenantContext::getId()] : [])) ?: [];

            $schedule = $db->fetchAll("
                SELECT * FROM booking_payment_schedules
                WHERE booking_id = ?{$tidSql} ORDER BY due_date ASC
            ", array_merge([$id], TenantContext::getId() > 1 ? [TenantContext::getId()] : [])) ?: [];

            $this->render('associate/booking_receipt', [
                'page_title' => 'Booking Receipt - Associate Portal',
                'page_description' => 'View booking receipt',
                'booking' => $booking,
                'payments' => $payments,
                'schedule' => $schedule,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateBookingController::bookingReceipt error: ' . $e->getMessage());
        }
    }
}

