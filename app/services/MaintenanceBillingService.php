<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Middleware\TenantContext;

class MaintenanceBillingService
{
    protected Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Batch generate monthly maintenance bills for all active bookings in a colony.
     *
     * @return array [generated => int, skipped => int, total_amount => float]
     */
    public function batchGenerate(
        int $colonyId,
        string $billMonth,
        float $ratePerSqft = 1.00,
        float $waterCharges = 0.00,
        float $securityCharges = 0.00,
        ?string $dueDate = null
    ): array {
        $tid = TenantContext::getId();
        if (!$dueDate) {
            $dueDate = date('Y-m-15', strtotime($billMonth . '-01 +1 month'));
        }

        // Fetch bookings for this colony that have an assigned plot
        $tSql = ($tid > 1) ? " AND pb.tenant_id = " . (int)$tid : "";
        $sql = "SELECT pb.id AS booking_id, pb.customer_id, pb.plot_id, 
                       p.colony_id, COALESCE(p.area_sqft, 0) AS area_sqft, p.plot_number
                FROM plot_bookings pb
                INNER JOIN plots p ON pb.plot_id = p.id
                WHERE p.colony_id = ? AND pb.status IN ('booked', 'token_paid', 'active', 'confirmed')
                $tSql";

        $bookings = $this->db->fetchAll($sql, [$colonyId]) ?: [];
        $generated = 0;
        $skipped = 0;
        $totalAmount = 0.00;

        foreach ($bookings as $b) {
            $bookingId = (int)$b['booking_id'];
            $existing = $this->db->fetchOne(
                "SELECT id FROM maintenance_dues WHERE booking_id = ? AND bill_month = ?" .
                ($tid > 1 ? " AND tenant_id = " . (int)$tid : ""),
                [$bookingId, $billMonth]
            );

            if ($existing) {
                $skipped++;
                continue;
            }

            $area = (float)$b['area_sqft'];
            $baseAmount = round($area * $ratePerSqft, 2);
            $totalDue = round($baseAmount + $waterCharges + $securityCharges, 2);

            if ($totalDue <= 0) {
                $skipped++;
                continue;
            }

            $billYear = (int)substr($billMonth, 0, 4) ?: (int)date('Y');
            $this->db->execute(
                "INSERT INTO maintenance_dues 
                 (tenant_id, booking_id, colony_id, customer_id, plot_id, bill_month, bill_year,
                  rate_per_sqft, area_sqft, base_amount, water_charges, security_charges, 
                  amount_due, paid_amount, status, due_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, 'pending', ?)",
                [
                    $tid,
                    $bookingId,
                    (int)$b['colony_id'],
                    !empty($b['customer_id']) ? (int)$b['customer_id'] : null,
                    !empty($b['plot_id']) ? (int)$b['plot_id'] : null,
                    $billMonth,
                    $billYear,
                    $ratePerSqft,
                    $area,
                    $baseAmount,
                    $waterCharges,
                    $securityCharges,
                    $totalDue,
                    $dueDate
                ]
            );

            $generated++;
            $totalAmount += $totalDue;
        }

        return [
            'generated' => $generated,
            'skipped' => $skipped,
            'total_amount' => $totalAmount
        ];
    }

    /**
     * Collect payment for a maintenance due.
     */
    public function collectPayment(
        int $dueId,
        float $amount,
        string $paymentMode = 'upi',
        string $transactionRef = '',
        string $notes = ''
    ): array {
        $tid = TenantContext::getId();
        $due = $this->db->fetchOne(
            "SELECT * FROM maintenance_dues WHERE id = ?" . ($tid > 1 ? " AND tenant_id = " . (int)$tid : ""),
            [$dueId]
        );

        if (!$due) {
            return ['success' => false, 'message' => 'Maintenance due record not found.'];
        }

        $receiptNumber = 'MNT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $newPaid = round((float)$due['paid_amount'] + $amount, 2);
        $totalDue = (float)$due['amount_due'];

        $newStatus = ($newPaid >= $totalDue) ? 'paid' : 'partially_paid';

        $this->db->execute(
            "UPDATE maintenance_dues 
             SET paid_amount = ?, status = ?, payment_mode = ?, transaction_ref = ?, 
                 receipt_number = ?, notes = ?, paid_at = NOW()
             WHERE id = ?",
            [
                $newPaid,
                $newStatus,
                $paymentMode,
                $transactionRef,
                $receiptNumber,
                $notes,
                $dueId
            ]
        );

        return [
            'success' => true,
            'receipt_number' => $receiptNumber,
            'paid_amount' => $newPaid,
            'status' => $newStatus
        ];
    }

    /**
     * Get pending maintenance amount for a booking.
     */
    public function getPendingForBooking(int $bookingId): float
    {
        $tid = TenantContext::getId();
        $r = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount_due - paid_amount), 0) AS pending 
             FROM maintenance_dues 
             WHERE booking_id = ? AND status IN ('pending', 'partially_paid')" .
            ($tid > 1 ? " AND tenant_id = " . (int)$tid : ""),
            [$bookingId]
        );

        return max(0.0, (float)($r['pending'] ?? 0));
    }

    /**
     * Get maintenance dues for customer portal.
     */
    public function getDuesForCustomer(int $customerId): array
    {
        $tid = TenantContext::getId();
        $tSql = ($tid > 1) ? " AND md.tenant_id = " . (int)$tid : "";

        return $this->db->fetchAll(
            "SELECT md.*, c.name AS colony_name, p.plot_number
             FROM maintenance_dues md
             LEFT JOIN colonies c ON md.colony_id = c.id
             LEFT JOIN plots p ON md.plot_id = p.id
             WHERE md.customer_id = ? $tSql
             ORDER BY md.bill_month DESC, md.id DESC",
            [$customerId]
        ) ?: [];
    }

    /**
     * List dues for admin with filters.
     */
    public function listDues(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $tid = TenantContext::getId();
        $where = ["1=1"];
        $params = [];

        if ($tid > 1) {
            $where[] = "md.tenant_id = ?";
            $params[] = $tid;
        }

        if (!empty($filters['colony_id'])) {
            $where[] = "md.colony_id = ?";
            $params[] = (int)$filters['colony_id'];
        }

        if (!empty($filters['bill_month'])) {
            $where[] = "md.bill_month = ?";
            $params[] = $filters['bill_month'];
        }

        if (!empty($filters['status'])) {
            $where[] = "md.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(p.plot_number LIKE ? OR u.name LIKE ? OR u.phone LIKE ?)";
            $s = '%' . trim($filters['search']) . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT md.*, c.name AS colony_name, p.plot_number, 
                       u.name AS customer_name, u.phone AS customer_phone
                FROM maintenance_dues md
                LEFT JOIN colonies c ON md.colony_id = c.id
                LEFT JOIN plots p ON md.plot_id = p.id
                LEFT JOIN users u ON md.customer_id = u.id
                WHERE $whereClause
                ORDER BY md.id DESC
                LIMIT $limit OFFSET $offset";

        return $this->db->fetchAll($sql, $params) ?: [];
    }

    /**
     * Aggregated statistics.
     */
    public function getStats(?int $colonyId = null): array
    {
        $tid = TenantContext::getId();
        $where = ["1=1"];
        $params = [];

        if ($tid > 1) {
            $where[] = "tenant_id = ?";
            $params[] = $tid;
        }

        if ($colonyId) {
            $where[] = "colony_id = ?";
            $params[] = $colonyId;
        }

        $w = implode(" AND ", $where);

        $row = $this->db->fetchOne(
            "SELECT 
                COUNT(*) AS total_bills,
                COALESCE(SUM(amount_due), 0) AS total_billed,
                COALESCE(SUM(paid_amount), 0) AS total_collected,
                COALESCE(SUM(CASE WHEN status IN ('pending', 'partially_paid') THEN amount_due - paid_amount ELSE 0 END), 0) AS total_pending,
                COUNT(CASE WHEN status IN ('pending', 'partially_paid') THEN 1 END) AS pending_count
             FROM maintenance_dues
             WHERE $w",
            $params
        );

        return [
            'total_bills' => (int)($row['total_bills'] ?? 0),
            'total_billed' => (float)($row['total_billed'] ?? 0),
            'total_collected' => (float)($row['total_collected'] ?? 0),
            'total_pending' => (float)($row['total_pending'] ?? 0),
            'pending_count' => (int)($row['pending_count'] ?? 0)
        ];
    }
}
