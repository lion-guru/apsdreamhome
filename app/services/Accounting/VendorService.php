<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Vendor Service
 * Handles vendor management, KYC, payments
 */
class VendorService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function createVendor(array $data): int
    {
        $tid = TenantContext::getId();

        $payload = [
            'vendor_name'        => trim($data['vendor_name'] ?? ''),
            'vendor_code'        => $data['vendor_code'] ?? $this->generateVendorCode(),
            'contact_person'     => $data['contact_person'] ?? null,
            'email'              => $data['email'] ?? null,
            'phone'              => $data['phone'] ?? null,
            'address'            => $data['address'] ?? null,
            'gstin'              => strtoupper($data['gstin'] ?? ''),
            'pan'                => strtoupper($data['pan'] ?? ''),
            'bank_account_no'    => $data['bank_account_no'] ?? null,
            'bank_ifsc'          => strtoupper($data['bank_ifsc'] ?? ''),
            'bank_name'          => $data['bank_name'] ?? null,
            'tds_section'        => $data['tds_section'] ?? '194C',
            'tds_rate'           => (float)($data['tds_rate'] ?? 1.0),
            'payment_terms'      => $data['payment_terms'] ?? '30_days',
            'status'             => 'active',
            'kyc_status'         => 'pending',
            'kyc_verified_at'    => null,
            'kyc_verified_by'    => null,
            'tenant_id'          => TenantContext::getId(),
        ];
        $this->db->insert('vendors', $payload);
        return (int)$this->db->lastInsertId();
    }

    private function generateVendorCode(): string
    {
        $tid = TenantContext::getId();
        $prefix = 'VND';
        $year = date('y');
        $count = $this->db->fetchOne("SELECT COUNT(*) as c FROM vendors" . ($tid > 1 ? " WHERE tenant_id = ?" : ""), $tid > 1 ? [$tid] : []);
        $next = ($count['c'] ?? 0) + 1;
        return $prefix . $year . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function getVendor(int $id): ?array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM vendors WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$id, $tid] : [$id]) ?: null;
    }

    public function listVendors(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];

        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['kyc_status'])) {
            $where .= " AND kyc_status = ?";
            $params[] = $filters['kyc_status'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (vendor_name LIKE ? OR vendor_code LIKE ? OR gstin LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search; $params[] = $search; $params[] = $search;
        }

        $where .= " ORDER BY vendor_name";
        return $this->db->fetchAll("SELECT * FROM vendors $where", $params) ?: [];
    }

    public function verifyVendorKyc(int $vendorId): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE vendors SET kyc_status = 'verified', kyc_verified_at = NOW(), kyc_verified_by = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([TenantContext::getId(), $id], $tid > 1 ? [$tid] : []));
    }

    public function rejectVendorKyc(int $vendorId, string $reason = ''): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE vendors SET kyc_status = 'rejected', kyc_rejection_reason = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$reason, $id], $tid > 1 ? [$tid] : []));
    }

    public function payVendor(array $data): int
    {
        $tid = TenantContext::getId();

        $vendor = $this->getVendor($data['vendor_id'] ?? 0);
        if (!$vendor) throw new Exception('Vendor not found');

        $payload = [
            'vendor_id'          => $data['vendor_id'],
            'amount'             => (float)($data['amount'] ?? 0),
            'payment_date'       => $data['payment_date'] ?? date('Y-m-d'),
            'payment_mode'       => $data['payment_mode'] ?? 'bank_transfer',
            'reference_number'   => $data['reference_number'] ?? null,
            'narration'          => $data['narration'] ?? '',
            'tds_deducted'       => (float)($data['tds_deducted'] ?? 0),
            'gst_amount'         => (float)($data['gst_amount'] ?? 0),
            'status'             => 'paid',
            'paid_by'            => $data['paid_by'] ?? null,
            'tenant_id'          => $tid,
        ];
        $this->db->insert('vendor_payments', $payload);
        return (int)$this->db->lastInsertId();
    }

    public function recordVendorPayment(array $data): int
    {
        return $this->payVendor($data);
    }

    public function getVendorPayments(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];

        if (!empty($filters['vendor_id'])) {
            $where .= " AND vendor_id = ?";
            $params[] = $filters['vendor_id'];
        }
        if (!empty($filters['from_date'])) {
            $where .= " AND payment_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where .= " AND payment_date <= ?";
            $params[] = $filters['to_date'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $where .= " ORDER BY payment_date DESC, id DESC";
        return $this->db->fetchAll("SELECT * FROM vendor_payments $where", $params) ?: [];
    }

    public function getVendorOutstanding(): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT v.*, COALESCE(SUM(vp.amount), 0) as total_paid
                FROM vendors v
                LEFT JOIN vendor_payments vp ON vp.vendor_id = v.id AND vp.status = 'paid' " . ($tid > 1 ? " AND vp.tenant_id = ?" : "")
                . " WHERE v.status = 'active'" . ($tid > 1 ? " AND v.tenant_id = ?" : "")
                . " GROUP BY v.id
                HAVING COALESCE(SUM(vp.amount), 0) < v.outstanding_amount
                ORDER BY v.vendor_name";
        $params = $tid > 1 ? [$tid, $tid] : [$tid];
        return $this->db->fetchAll($sql, $params) ?: [];
    }
}