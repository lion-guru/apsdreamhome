<?php

namespace App\Http\Controllers\Api;

require_once __DIR__ . '/../BaseController.php';

/**
 * AdminMobileController — JSON API endpoints for Flutter admin pages.
 * Returns JSON data for: bookings, commissions, plots, users.
 */
class AdminMobileController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * GET /api/v2/mobile/admin/bookings
     * Returns all plot bookings for the admin approvals page.
     */
    public function bookings()
    {
        try {
            $bookings = $this->db->fetchAll(
                "SELECT pb.id, pb.booking_number, pb.status, pb.total_amount, pb.token_amount,
                        pb.created_at, pb.updated_at,
                        COALESCE(u.name, 'N/A') as customer_name,
                        p.plot_number,
                        c.name as colony_name
                 FROM plot_bookings pb
                 LEFT JOIN users u ON pb.user_id = u.id
                 LEFT JOIN plots p ON pb.plot_id = p.id
                 LEFT JOIN colonies c ON p.colony_id = c.id
                 ORDER BY pb.created_at DESC"
            );

            return $this->jsonResponse(['success' => true, 'data' => $bookings]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::bookings error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v2/mobile/admin/bookings/{id}/status
     * Update booking status (approve/reject).
     */
    public function updateBookingStatus($id)
    {
        try {
            $status = $_POST['status'] ?? '';
            $validStatuses = ['token_paid', 'agreement_signed', 'emi_active', 'partially_paid', 'fully_paid', 'cancelled', 'registration_done'];
            if (!in_array($status, $validStatuses)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Invalid status'], 400);
            }

            $this->db->execute("UPDATE plot_bookings SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);

            return $this->jsonResponse(['success' => true, 'message' => 'Booking status updated']);
        } catch (\Exception $e) {
            error_log('AdminMobileController::updateBookingStatus error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/commissions
     * Returns commission ledger entries for the admin approvals page.
     */
    public function commissions()
    {
        try {
            $commissions = $this->db->fetchAll(
                "SELECT l.id, l.commission_type, l.amount, l.commission_percentage,
                        l.status, l.notes, l.created_at,
                        COALESCE(u.name, 'N/A') as agent_name,
                        l.source_user_name,
                        l.rank_at_time, l.level
                 FROM mlm_commission_ledger l
                 LEFT JOIN users u ON l.beneficiary_user_id = u.id
                 ORDER BY l.created_at DESC"
            );

            return $this->jsonResponse(['success' => true, 'data' => $commissions]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::commissions error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v2/mobile/admin/commissions/{id}/action
     * Approve or reject a commission entry.
     */
    public function commissionAction($id)
    {
        try {
            $action = $_POST['action'] ?? '';
            if (!in_array($action, ['approve', 'reject'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
            }

            $newStatus = $action === 'approve' ? 'approved' : 'cancelled';
            $this->db->execute(
                "UPDATE mlm_commission_ledger SET status = ? WHERE id = ?",
                [$newStatus, $id]
            );

            return $this->jsonResponse(['success' => true, 'message' => "Commission $action'd"]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::commissionAction error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/plots
     * Returns all plots for the admin plot management page.
     */
    public function plots()
    {
        try {
            $plots = $this->db->fetchAll(
                "SELECT p.id, p.plot_number, p.status, p.area_sqft, p.total_price,
                        p.width_ft, p.length_ft,
                        c.name as colony_name,
                        b.name as block_name
                 FROM plots p
                 LEFT JOIN colonies c ON p.colony_id = c.id
                 LEFT JOIN blocks b ON p.block_id = b.id
                 ORDER BY p.created_at DESC"
            );

            return $this->jsonResponse(['success' => true, 'data' => $plots]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::plots error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v2/mobile/admin/users
     * Returns all users for the admin user management page.
     */
    public function users()
    {
        try {
            $users = $this->db->fetchAll(
                "SELECT id, name, email, phone, role, status, created_at
                 FROM users
                 WHERE role IS NOT NULL AND role != ''
                 ORDER BY created_at DESC"
            );

            return $this->jsonResponse(['success' => true, 'data' => $users]);
        } catch (\Exception $e) {
            error_log('AdminMobileController::users error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => [], 'error' => $e->getMessage()], 500);
        }
    }
}
