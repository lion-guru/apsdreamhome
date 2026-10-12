<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Services\Accounting\RegistryNocService;

/**
 * Enterprise Plot Ownership Transfer & Resale NOC Service
 * Handles eligibility verification, transfer application, NOC issuance,
 * and ownership handover execution.
 */
class PlotTransferService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Check if a plot/booking is eligible for ownership transfer or resale NOC.
     */
    public function checkTransferEligibility(int $bookingId): array
    {
        $booking = $this->db->fetchRow(
            "SELECT b.*, p.plot_number, p.colony_id, p.area_sqft, p.status AS plot_status,
                    c.name AS colony_name, u.name AS customer_name, u.phone AS customer_phone
             FROM plot_bookings b
             JOIN plots p ON p.id = b.plot_id
             JOIN colonies c ON c.id = p.colony_id
             JOIN users u ON u.id = b.customer_id
             WHERE b.id = ?",
            [$bookingId]
        );

        if (!$booking) {
            return [
                'eligible' => false,
                'reasons' => ['Booking record not found.'],
                'booking' => null,
            ];
        }

        $reasons = [];

        // 1. Check for existing active transfer request
        $activeRequest = $this->db->fetchRow(
            "SELECT id, status, noc_number FROM plot_transfers
             WHERE (booking_id = ? OR plot_id = ?) AND status IN ('requested', 'under_review', 'approved')
             LIMIT 1",
            [$bookingId, (int)$booking['plot_id']]
        );

        if ($activeRequest) {
            $reasons[] = "An active transfer request (#{$activeRequest['id']}) is already in '{$activeRequest['status']}' state.";
        }

        // 2. Financial & Registry No-Dues check
        try {
            $nocService = new RegistryNocService();
            $noDues = $nocService->checkNoDues($bookingId);
            if (!($noDues['no_dues'] ?? false)) {
                $unpaid = number_format((float)($noDues['total_outstanding'] ?? 0), 2);
                $reasons[] = "Outstanding financial dues of ₹{$unpaid} must be cleared prior to ownership transfer.";
            }
        } catch (\Throwable $e) {
            error_log("[PlotTransferService::checkTransferEligibility] NoDues check: " . $e->getMessage());
        }

        // 3. Check for active legal litigation stay orders
        try {
            $litigation = $this->db->fetchRow(
                "SELECT COUNT(*) AS c FROM land_litigations
                 WHERE colony_id = ? AND status = 'active' AND stay_order_granted = 1",
                [(int)$booking['colony_id']]
            );
            if (($litigation['c'] ?? 0) > 0) {
                $reasons[] = "Colony has active stay order/injunction in legal litigation records.";
            }
        } catch (\Throwable $e) {
            // land_litigations table might be empty or optional
        }

        return [
            'eligible' => empty($reasons),
            'reasons' => $reasons,
            'booking' => $booking,
        ];
    }

    /**
     * Submit a customer-requested plot transfer / resale NOC application.
     */
    public function applyTransfer(array $data): array
    {
        $bookingId = (int)($data['booking_id'] ?? 0);
        $eligibility = $this->checkTransferEligibility($bookingId);

        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'message' => implode(' ', $eligibility['reasons']),
            ];
        }

        $booking = $eligibility['booking'];

        $newBuyerName = trim((string)($data['new_buyer_name'] ?? ''));
        $newBuyerPhone = trim((string)($data['new_buyer_phone'] ?? ''));
        $transferType = in_array($data['transfer_type'] ?? '', ['resale', 'family_gift', 'legal_heir', 'direct_transfer'], true)
            ? $data['transfer_type'] : 'resale';

        if ($newBuyerName === '' || $newBuyerPhone === '') {
            return [
                'success' => false,
                'message' => 'New buyer name and contact phone number are mandatory.',
            ];
        }

        $tid = (int)($booking['tenant_id'] ?? 1);
        $transferAmount = max(0, (float)($data['transfer_amount'] ?? ($booking['total_plot_value'] ?? $booking['total_amount'] ?? 0)));
        $transferFee = max(0, (float)($data['transfer_fee'] ?? 5000.00));

        $id = $this->db->insert('plot_transfers', [
            'tenant_id' => $tid,
            'plot_id' => (int)$booking['plot_id'],
            'booking_id' => $bookingId,
            'from_customer_id' => (int)$booking['customer_id'],
            'to_customer_id' => null,
            'new_buyer_name' => $newBuyerName,
            'new_buyer_phone' => $newBuyerPhone,
            'new_buyer_aadhaar' => trim((string)($data['new_buyer_aadhaar'] ?? '')),
            'new_buyer_pan' => strtoupper(trim((string)($data['new_buyer_pan'] ?? ''))),
            'new_buyer_address' => trim((string)($data['new_buyer_address'] ?? '')),
            'transfer_reason' => trim((string)($data['transfer_reason'] ?? "Application for {$transferType}")),
            'transfer_type' => $transferType,
            'transfer_amount' => $transferAmount,
            'transfer_fee' => $transferFee,
            'transfer_fee_status' => 'pending',
            'status' => 'requested',
            'old_status' => (string)($booking['plot_status'] ?? 'booked'),
            'new_status' => 'transferred',
            'documents_json' => json_encode($data['documents'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'transfer_id' => $id,
            'message' => "Plot transfer application #{$id} submitted successfully.",
        ];
    }

    /**
     * Admin approves the transfer request and generates an official Transfer NOC number.
     */
    public function approveTransfer(int $transferId, int $adminId, float $transferFee = 0.0, string $remarks = ''): array
    {
        $transfer = $this->db->fetchRow("SELECT * FROM plot_transfers WHERE id = ?", [$transferId]);
        if (!$transfer) {
            return ['success' => false, 'message' => 'Transfer record not found.'];
        }

        if ($transfer['status'] === 'approved' || $transfer['status'] === 'completed') {
            return ['success' => true, 'noc_number' => $transfer['noc_number'], 'message' => 'Transfer already approved.'];
        }

        $nocNumber = 'NOC-TRF-' . date('Y') . '-' . str_pad((string)$transferId, 5, '0', STR_PAD_LEFT);
        $feeStatus = ($transferFee <= 0 || $transfer['transfer_fee_status'] === 'paid') ? 'paid' : 'pending';

        $this->db->execute(
            "UPDATE plot_transfers
             SET status = 'approved',
                 noc_number = ?,
                 noc_issued_at = NOW(),
                 transfer_fee = ?,
                 transfer_fee_status = ?,
                 processed_by = ?,
                 remarks = CONCAT(COALESCE(remarks,''), '\nApproved by admin #', ?, ' at ', NOW(), ': ', ?),
                 updated_at = NOW()
             WHERE id = ?",
            [$nocNumber, $transferFee, $feeStatus, $adminId, $adminId, $remarks, $transferId]
        );

        return [
            'success' => true,
            'noc_number' => $nocNumber,
            'message' => "Transfer approved and NOC issued ({$nocNumber}).",
        ];
    }

    /**
     * Admin executes the full ownership transfer:
     * 1. Finds or creates user for the new buyer.
     * 2. Re-assigns plot customer_id and plot_bookings customer_id.
     * 3. Sets transfer status to 'completed'.
     */
    public function executeTransfer(int $transferId, int $adminId): array
    {
        $transfer = $this->db->fetchRow("SELECT * FROM plot_transfers WHERE id = ?", [$transferId]);
        if (!$transfer) {
            return ['success' => false, 'message' => 'Transfer record not found.'];
        }

        if ($transfer['status'] === 'completed') {
            return ['success' => false, 'message' => 'Transfer has already been completed.'];
        }

        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();

        try {
            $tid = (int)($transfer['tenant_id'] ?? 1);

            // 1. Resolve or create new buyer account
            $newBuyerPhone = trim((string)$transfer['new_buyer_phone']);
            $newBuyerName = trim((string)$transfer['new_buyer_name']);
            $toCustomerId = (int)($transfer['to_customer_id'] ?? 0);

            if ($toCustomerId <= 0 && $newBuyerPhone !== '') {
                $existingUser = $this->db->fetchRow("SELECT id FROM users WHERE phone = ? LIMIT 1", [$newBuyerPhone]);
                if ($existingUser) {
                    $toCustomerId = (int)$existingUser['id'];
                } else {
                    $dummyEmail = 'buyer.' . preg_replace('/[^0-9]/', '', $newBuyerPhone) . '@apsdreamhome.com';
                    $toCustomerId = (int)$this->db->insert('users', [
                        'name' => $newBuyerName,
                        'phone' => $newBuyerPhone,
                        'email' => $dummyEmail,
                        'role' => 'customer',
                        'status' => 'active',
                        'tenant_id' => $tid,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // 2. Update plot customer ownership
            $this->db->execute(
                "UPDATE plots SET customer_id = ?, updated_at = NOW() WHERE id = ?",
                [$toCustomerId, (int)$transfer['plot_id']]
            );

            // 3. Update plot_bookings customer ownership if linked
            if (!empty($transfer['booking_id'])) {
                $this->db->execute(
                    "UPDATE plot_bookings SET customer_id = ?, updated_at = NOW() WHERE id = ?",
                    [$toCustomerId, (int)$transfer['booking_id']]
                );
            }

            // 4. Mark transfer as completed
            $this->db->execute(
                "UPDATE plot_transfers
                 SET to_customer_id = ?,
                     status = 'completed',
                     processed_by = ?,
                     transfer_date = CURDATE(),
                     updated_at = NOW()
                 WHERE id = ?",
                [$toCustomerId, $adminId, $transferId]
            );

            $pdo->commit();

            return [
                'success' => true,
                'message' => "Ownership transfer completed. Plot reassigned to {$newBuyerName} (User #{$toCustomerId}).",
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log("[PlotTransferService::executeTransfer] " . $e->getMessage());
            return ['success' => false, 'message' => 'Transfer execution failed: ' . $e->getMessage()];
        }
    }

    /**
     * Admin rejects a transfer request with reason.
     */
    public function rejectTransfer(int $transferId, int $adminId, string $reason): array
    {
        $this->db->execute(
            "UPDATE plot_transfers
             SET status = 'rejected',
                 rejection_reason = ?,
                 processed_by = ?,
                 updated_at = NOW()
             WHERE id = ?",
            [$reason, $adminId, $transferId]
        );

        return ['success' => true, 'message' => 'Transfer request rejected.'];
    }
}
