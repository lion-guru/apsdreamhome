<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

class CommercialLeaseService
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Create a new commercial shop or office lease
     */
    public function createLease(array $data): array
    {
        $tenantId = (int)($data['tenant_id'] ?? 1);
        $colonyId = (int)($data['colony_id'] ?? 0);
        $unitNumber = trim((string)($data['unit_number'] ?? ''));
        $tenantName = trim((string)($data['tenant_name'] ?? ''));
        $tenantPhone = trim((string)($data['tenant_phone'] ?? ''));

        if ($colonyId <= 0 || empty($unitNumber) || empty($tenantName) || empty($tenantPhone)) {
            return ['success' => false, 'message' => 'Colony, unit number, tenant name, and phone are required.'];
        }

        $randSeq = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $token = 'APS-COMM-GOR-' . date('Y') . '-' . $randSeq;

        $unitType = $data['unit_type'] ?? 'retail_shop';
        $carpetArea = (float)($data['carpet_area_sqft'] ?? 0.0);
        $businessName = trim((string)($data['tenant_business_name'] ?? ''));
        $email = trim((string)($data['tenant_email'] ?? ''));
        $gstin = trim((string)($data['tenant_gstin'] ?? ''));
        $monthlyRent = (float)($data['monthly_rent'] ?? 0.0);
        $deposit = (float)($data['security_deposit'] ?? ($monthlyRent * 3)); // 3 months default deposit
        $escalation = (float)($data['annual_escalation_pct'] ?? 5.0); // 5% UP standard escalation
        $lockIn = (int)($data['lock_in_months'] ?? 12);
        $startDate = !empty($data['lease_start_date']) ? $data['lease_start_date'] : date('Y-m-d');
        $endDate = !empty($data['lease_end_date']) ? $data['lease_end_date'] : date('Y-m-d', strtotime('+11 months', strtotime($startDate))); // 11-month agreement
        $submeter = (float)($data['electricity_submeter_reading'] ?? 0.0);

        $sql = "INSERT INTO commercial_leases (
                    tenant_id, lease_token, colony_id, unit_number, unit_type,
                    carpet_area_sqft, tenant_name, tenant_business_name, tenant_phone,
                    tenant_email, tenant_gstin, monthly_rent, security_deposit,
                    annual_escalation_pct, lock_in_months, lease_start_date, lease_end_date,
                    electricity_submeter_reading, status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, 'active', NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId, $token, $colonyId, $unitNumber, $unitType,
            $carpetArea, $tenantName, $businessName, $tenantPhone,
            $email, $gstin, $monthlyRent, $deposit,
            $escalation, $lockIn, $startDate, $endDate,
            $submeter
        ]);

        $id = (int)$this->db->getConnection()->lastInsertId();

        return [
            'success' => true,
            'id' => $id,
            'lease_token' => $token,
            'message' => 'Commercial lease created successfully.'
        ];
    }

    /**
     * Generate monthly rent invoice for a lease
     */
    public function generateInvoice(int $leaseId, string $billingMonth, array $options = []): array
    {
        $lease = $this->getLeaseDetails($leaseId);
        if (!$lease) {
            return ['success' => false, 'message' => 'Lease not found.'];
        }

        // Check if invoice already exists for this billing month
        $chk = $this->db->fetch("SELECT id, invoice_no FROM lease_rent_invoices WHERE lease_id = ? AND billing_month = ? LIMIT 1", [$leaseId, $billingMonth]);
        if ($chk) {
            return ['success' => false, 'message' => "Invoice for {$billingMonth} already exists: {$chk['invoice_no']}"];
        }

        $baseRent = (float)$lease['monthly_rent'];
        $elecUnits = (float)($options['electricity_units'] ?? 0.0);
        $elecRate = (float)($options['electricity_rate'] ?? 9.50);
        $elecAmount = round($elecUnits * $elecRate, 2);
        $maintFee = (float)($options['maintenance_fee'] ?? 0.0);
        $totalDue = round($baseRent + $elecAmount + $maintFee, 2);

        $randSeq = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $invNo = 'APS-RENT-GOR-' . str_replace('-', '', $billingMonth) . '-' . $randSeq;

        $sql = "INSERT INTO lease_rent_invoices (
                    tenant_id, lease_id, invoice_no, billing_month,
                    base_rent, electricity_units, electricity_rate, electricity_amount,
                    maintenance_fee, total_due, paid_amount, status, created_at
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, 0.00, 'unpaid', NOW()
                )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $lease['tenant_id'], $leaseId, $invNo, $billingMonth,
            $baseRent, $elecUnits, $elecRate, $elecAmount,
            $maintFee, $totalDue
        ]);

        $invId = (int)$this->db->getConnection()->lastInsertId();

        return [
            'success' => true,
            'id' => $invId,
            'invoice_no' => $invNo,
            'total_due' => $totalDue,
            'message' => "Invoice generated for {$billingMonth}: ₹" . number_format($totalDue, 2)
        ];
    }

    /**
     * Record payment against a lease rent invoice
     */
    public function recordPayment(int $invoiceId, float $amount, string $mode, ?string $ref = null, ?string $notes = null): bool
    {
        $inv = $this->db->fetch("SELECT * FROM lease_rent_invoices WHERE id = ? LIMIT 1", [$invoiceId]);
        if (!$inv) {
            return false;
        }

        $newPaid = round((float)$inv['paid_amount'] + $amount, 2);
        $totalDue = (float)$inv['total_due'];

        $status = ($newPaid >= $totalDue) ? 'paid' : ($newPaid > 0 ? 'partially_paid' : 'unpaid');

        $sql = "UPDATE lease_rent_invoices 
                SET paid_amount = ?, 
                    payment_date = CURDATE(), 
                    payment_mode = ?, 
                    payment_ref = ?, 
                    notes = ?, 
                    status = ?, 
                    updated_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$newPaid, $mode, $ref, $notes, $status, $invoiceId]);
    }

    /**
     * Fetch complete lease details
     */
    public function getLeaseDetails(int $id): ?array
    {
        $sql = "SELECT cl.*, c.name AS colony_name, c.location AS colony_address
                FROM commercial_leases cl
                LEFT JOIN colonies c ON cl.colony_id = c.id
                WHERE cl.id = ?
                LIMIT 1";
        return $this->db->fetch($sql, [$id]) ?: null;
    }

    /**
     * Fetch complete invoice details
     */
    public function getInvoiceDetails(int $id): ?array
    {
        $sql = "SELECT lri.*, cl.unit_number, cl.tenant_name, cl.tenant_phone, cl.tenant_email,
                       cl.tenant_business_name, cl.tenant_gstin, cl.lease_token,
                       c.name AS colony_name, c.location AS colony_address
                FROM lease_rent_invoices lri
                JOIN commercial_leases cl ON lri.lease_id = cl.id
                LEFT JOIN colonies c ON cl.colony_id = c.id
                WHERE lri.id = ?
                LIMIT 1";
        return $this->db->fetch($sql, [$id]) ?: null;
    }

    /**
     * Fetch all invoices for a lease
     */
    public function getLeaseInvoices(int $leaseId): array
    {
        $sql = "SELECT * FROM lease_rent_invoices WHERE lease_id = ? ORDER BY billing_month DESC";
        return $this->db->fetchAll($sql, [$leaseId]) ?: [];
    }

    /**
     * Get aggregate statistics for Commercial Lease Rent Roll
     */
    public function getRentRollStats(): array
    {
        $leases = $this->db->fetch("SELECT 
                    COUNT(*) AS total_leases,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
                    COALESCE(SUM(CASE WHEN status = 'active' THEN monthly_rent ELSE 0 END), 0) AS monthly_run_rate,
                    COALESCE(SUM(security_deposit), 0) AS total_deposits_held
                FROM commercial_leases");

        $invoices = $this->db->fetch("SELECT 
                    COUNT(*) AS total_invoices,
                    COALESCE(SUM(total_due), 0) AS total_billed,
                    COALESCE(SUM(paid_amount), 0) AS total_collected,
                    COALESCE(SUM(total_due - paid_amount), 0) AS total_outstanding
                FROM lease_rent_invoices");

        return [
            'total_leases' => (int)($leases['total_leases'] ?? 0),
            'active_leases' => (int)($leases['active_count'] ?? 0),
            'monthly_run_rate' => (float)($leases['monthly_run_rate'] ?? 0.0),
            'deposits_held' => (float)($leases['total_deposits_held'] ?? 0.0),
            'total_billed' => (float)($invoices['total_billed'] ?? 0.0),
            'total_collected' => (float)($invoices['total_collected'] ?? 0.0),
            'total_outstanding' => (float)($invoices['total_outstanding'] ?? 0.0)
        ];
    }
}
