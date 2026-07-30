<?php

namespace App\Services\Legal;

use App\Core\Middleware\TenantContext;
use PDO;
use Exception;

/**
 * Registry/NOC Eligibility Service
 *
 * Checks whether a plot booking is clear of outstanding financial
 * obligations — unpaid installments, accrued penalties — before
 * allowing registry or NOC to proceed.
 *
 * Tables read (read-only):
 *   - plot_bookings          booking master record
 *   - booking_payment_schedules  installment-level payment tracking
 *   - registries             existing registry records (for dedup)
 *   - noc_requests           existing NOC records (for dedup)
 */
class RegistryEligibilityService
{
    protected PDO $db;
    protected int $tenantId;

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?? (\App\Core\Database::getInstance()->getConnection() ?? $GLOBALS['db']);
        $this->tenantId = TenantContext::getId();
    }

    /**
     * Main eligibility check.
     *
     * Returns an array with:
     *   eligible      bool   true if all financial obligations are clear
     *   booking       array  booking summary
     *   dues          array  list of outstanding installment details
     *   total_due     float  sum of unpaid amount across all installments
     *   total_penalty float  sum of accrued_penalty across all installments
     *   has_registry  bool   true if a registry record already exists
     *   has_noc       bool   true if a NOC request already exists
     *   blocks        array  human-readable list of blocking reasons
     */
    public function checkEligibility(int $bookingId): array
    {
        $out = [
            'eligible' => false,
            'booking' => null,
            'dues' => [],
            'total_due' => 0.0,
            'total_penalty' => 0.0,
            'has_registry' => false,
            'has_noc' => false,
            'blocks' => [],
        ];

        try {
            $booking = $this->getBookingSummary($bookingId);
            if (!$booking) {
                $out['blocks'][] = 'Booking not found.';
                return $out;
            }
            $out['booking'] = $booking;

            // Check booking status
            $blockedStatuses = ['cancelled', 'transferred'];
            if (in_array($booking['status'], $blockedStatuses, true)) {
                $out['blocks'][] = "Booking is {$booking['status']}. Cannot proceed with registry/NOC.";
                return $out;
            }

            // Check outstanding dues
            $dues = $this->getOutstandingDues($bookingId);
            $out['dues'] = $dues;
            $totalDue = 0.0;
            $totalPenalty = 0.0;

            foreach ($dues as $d) {
                $shortfall = max(0.0, (float)$d['amount'] - (float)$d['paid_amount']);
                if ($shortfall > 0) {
                    $totalDue += $shortfall;
                }
                $penalty = (float)($d['accrued_penalty'] ?? 0);
                if ($penalty > 0) {
                    $totalPenalty += $penalty;
                }
            }

            $out['total_due'] = round($totalDue, 2);
            $out['total_penalty'] = round($totalPenalty, 2);

            if ($totalDue > 0) {
                $out['blocks'][] = "Outstanding installment dues: ₹" . number_format($totalDue, 2);
            }
            if ($totalPenalty > 0) {
                $out['blocks'][] = "Accrued penalties: ₹" . number_format($totalPenalty, 2);
            }

            // Check for existing registry / NOC
            $out['has_registry'] = $this->hasRegistry($bookingId);
            $out['has_noc'] = $this->hasNoc($bookingId);

            if ($out['has_registry']) {
                $out['blocks'][] = 'A registry record already exists for this booking.';
            }
            if ($out['has_noc']) {
                $out['blocks'][] = 'A NOC request already exists for this booking.';
            }

            // Final verdict
            if (empty($out['blocks'])) {
                $out['eligible'] = true;
            }

            return $out;
        } catch (Exception $e) {
            error_log('[RegistryEligibilityService::checkEligibility] ' . $e->getMessage());
            $out['blocks'][] = 'Internal error checking eligibility.';
            return $out;
        }
    }

    /**
     * Returns installment-level outstanding dues for a booking.
     *
     * @return array of rows with: id, installment_number, due_date, amount,
     *               paid_amount, status, accrued_penalty, days_overdue
     */
    public function getOutstandingDues(int $bookingId): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT id, installment_no, due_date, amount, paid_amount,
                        status, accrued_penalty, late_fee,
                        DATEDIFF(CURDATE(), due_date) AS days_overdue
                 FROM booking_payment_schedules
                 WHERE booking_id = ?";
            $params = [$bookingId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " AND (status IN ('pending', 'overdue', 'partial')
                        OR (accrued_penalty > 0)
                        OR (paid_amount < amount))
                 ORDER BY due_date ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[RegistryEligibilityService::getOutstandingDues] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns the full payment schedule for a booking (all installments).
     */
    public function getPaymentSchedule(int $bookingId): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT id, installment_no, due_date, amount, paid_amount, paid_date,
                        status, accrued_penalty, late_fee
                 FROM booking_payment_schedules
                 WHERE booking_id = ?";
            $params = [$bookingId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY due_date ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[RegistryEligibilityService::getPaymentSchedule] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * NOC-specific eligibility.
     *
     * Same financial checks as registry, but also checks that
     * no NOC has already been issued for this booking.
     */
    public function checkNocEligibility(int $bookingId): array
    {
        $result = $this->checkEligibility($bookingId);

        // Additional NOC-specific: NOC already completed?
        $nocCompleted = $this->hasNoc($bookingId, 'approved');
        if ($nocCompleted) {
            $result['eligible'] = false;
            $result['blocks'][] = 'NOC has already been approved for this booking.';
        }

        return $result;
    }

    /**
     * Returns existing registry status for a booking, if any.
     */
    public function getRegistryStatus(int $bookingId): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT id, registration_no, sub_registrar_office,
                        registration_date, status, rejection_reason
                 FROM registries
                 WHERE booking_id = ?";
            $params = [$bookingId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY id DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            error_log('[RegistryEligibilityService::getRegistryStatus] ' . $e->getMessage());
            return null;
        }
    }

    public function getNocStatus(int $bookingId): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT id, requested_by, approved_by, status, rejection_reason
                 FROM noc_requests
                 WHERE booking_id = ?";
            $params = [$bookingId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY id DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            error_log('[RegistryEligibilityService::getNocStatus] ' . $e->getMessage());
            return null;
        }
    }

    // ---------------------------------------------------------------
    //  Private helpers
    // ---------------------------------------------------------------

    private function getBookingSummary(int $bookingId): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT pb.id, pb.booking_number, pb.customer_id, pb.plot_id,
                        pb.total_plot_value AS total_amount, pb.status AS booking_status,
                        u.name AS customer_name, u.phone AS customer_phone,
                        p.plot_number, c.name AS colony_name
                 FROM plot_bookings pb
                 LEFT JOIN users u ON u.id = pb.customer_id
                 LEFT JOIN plots p ON p.id = pb.plot_id
                 LEFT JOIN colonies c ON c.id = p.colony_id
                 WHERE pb.id = ?";
            $params = [$bookingId];
            if ($tid > 1) { $sql .= " AND pb.tenant_id = ?"; $params[] = $tid; }
            $sql .= " LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            return [
                'id' => $row['id'],
                'booking_number' => $row['booking_number'],
                'customer_name' => $row['customer_name'] ?? 'N/A',
                'customer_phone' => $row['customer_phone'] ?? '',
                'plot_number' => $row['plot_number'] ?? 'N/A',
                'colony_name' => $row['colony_name'] ?? 'N/A',
                'total_amount' => (float)$row['total_amount'],
                'status' => $row['booking_status'],
            ];
        } catch (Exception $e) {
            error_log('[RegistryEligibilityService::getBookingSummary] ' . $e->getMessage());
            return null;
        }
    }

    private function hasRegistry(int $bookingId): bool
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT COUNT(*) FROM registries WHERE booking_id = ?";
            $params = [$bookingId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function hasNoc(int $bookingId, ?string $statusFilter = null): bool
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT COUNT(*) FROM noc_requests WHERE booking_id = ?";
            $params = [$bookingId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            if ($statusFilter) {
                $sql .= " AND status = ?";
                $params[] = $statusFilter;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
