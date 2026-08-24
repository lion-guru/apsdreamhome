<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * TDS Service
 * Handles TDS register (auto-calc, deposit, Form 16A)
 */
class TdsService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function recordTDS(array $data): int
    {
        $tid = TenantContext::getId();

        $section = $data['tds_section'] ?? $this->autoDetectTdsSection($data['entity_type'] ?? '');
        $rate = (float)($data['tds_rate'] ?? $this->getTdsRateForSection($section, $data['entity_type'] ?? ''));
        $amount = (float)($data['amount'] ?? 0);
        $tdsAmount = round($amount * $rate / 100, 2);

        $payload = [
            'deductee_type'        => $data['deductee_type'] ?? 'vendor',
            'deductee_user_id'     => !empty($data['deductee_user_id']) ? (int)$data['deductee_user_id'] : null,
            'deductee_name'        => $data['deductee_name'] ?? null,
            'deductee_pan'         => strtoupper($data['deductee_pan'] ?? ''),
            'tds_section'          => $section,
            'amount_paid'          => $amount,
            'tds_rate'             => $rate,
            'tds_amount'           => $tdsAmount,
            'payment_date'         => $data['payment_date'] ?? date('Y-m-d'),
            'deduction_date'       => $data['deduction_date'] ?? date('Y-m-d'),
            'challan_number'       => $data['challan_number'] ?? null,
            'challan_date'         => $data['challan_date'] ?? null,
            'bsr_code'             => $data['bsr_code'] ?? null,
            'deposit_status'       => $data['deposit_status'] ?? 'pending',
            'deposit_date'         => $data['deposit_date'] ?? null,
            'quarter'              => $data['quarter'] ?? $this->getCurrentQuarter(),
            'financial_year'       => $data['financial_year'] ?? $this->getCurrentFinancialYear(),
            'certificate_number'   => null,
            'certificate_issued_at'=> null,
            'tenant_id'            => TenantContext::getId(),
        ];
        $this->db->insert('tds_register', $payload);
        return (int)$this->db->lastInsertId();
    }

    public function recordTdsProxy(array $data): int
    {
        return $this->recordTDS($data);
    }

    public function autoDetectTdsSection(string $entityType): string
    {
        $map = [
            'vendor'     => '194C',
            'contractor' => '194C',
            'professional'=> '194J',
            'rent'       => '194I',
            'salary'     => '192',
            'interest'   => '194A',
            'commission' => '194H',
            'brokerage'  => '194H',
            'sale_property'=> '194IA',
        ];
        return $map[$entityType] ?? '194C';
    }

    public function getTdsRateForSection(string $section, string $entityType): float
    {
        $rates = [
            '192'   => 0,      // Salary - as per slab
            '194A'  => 10.0,   // Interest
            '194C'  => 1.0,    // Contractor (1% individual, 2% others)
            '194H'  => 5.0,    // Commission/Brokerage
            '194I'  => 10.0,   // Rent
            '194J'  => 10.0,   // Professional/Technical
            '194IA' => 1.0,    // Sale of Property
            '194IB' => 5.0,    // Rent by Individual/HUF
        ];
        return $rates[$section] ?? 10.0;
    }

    public function getTdsRegister(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];

        if (!empty($filters['financial_year'])) {
            $where .= " AND financial_year = ?";
            $params[] = $filters['financial_year'];
        }
        if (!empty($filters['quarter'])) {
            $where .= " AND quarter = ?";
            $params[] = $filters['quarter'];
        }
        if (!empty($filters['deductee_type'])) {
            $where .= " AND deductee_type = ?";
            $params[] = $filters['deductee_type'];
        }
        if (!empty($filters['deposit_status'])) {
            $where .= " AND deposit_status = ?";
            $params[] = $filters['deposit_status'];
        }
        if (!empty($filters['from_date'])) {
            $where .= " AND payment_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where .= " AND payment_date <= ?";
            $params[] = $filters['to_date'];
        }
        if (!empty($filters['deductee_pan'])) {
            $where .= " AND deductee_pan = ?";
            $params[] = $filters['deductee_pan'];
        }

        $where .= " ORDER BY payment_date DESC, id DESC";
        return $this->db->fetchAll("SELECT * FROM tds_register $where", $params) ?: [];
    }

    public function getTdsSummary(string $fy): array
    {
        $tid = TenantContext::getId();
        $params = [$fy];
        if ($tid > 1) $params[] = $tid;
        $where = "WHERE financial_year = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");

        $stmt = $this->db->fetchOne("SELECT COALESCE(SUM(amount_paid), 0) AS total_paid, COALESCE(SUM(tds_amount), 0) AS total_tds, COALESCE(SUM(CASE WHEN deposit_status = 'deposited' THEN tds_amount ELSE 0 END), 0) AS deposited, COALESCE(SUM(CASE WHEN deposit_status = 'pending' THEN tds_amount ELSE 0 END), 0) AS pending FROM tds_register $where", $params);
        return $stmt ?: ['total_paid' => 0, 'total_tds' => 0, 'deposited' => 0, 'pending' => 0];
    }

    public function generateTdsCertificate(int $deducteeUserId, string $fy, string $quarter): int
    {
        $tid = TenantContext::getId();
        $certNumber = 'TDS-' . strtoupper(substr(md5($fy . $quarter . $deducteeUserId), 0, 10));

        $stmt = $this->db->execute("
            UPDATE tds_register
            SET certificate_number = ?, certificate_issued_at = NOW()
            WHERE deductee_user_id = ? AND financial_year = ? AND quarter = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$certNumber, $deducteeUserId, $fy, $quarter], $tid > 1 ? [$tid] : [])
        );

        return (int)$this->db->lastInsertId();
    }

    public function getTdsCertificatesIssued(string $fy = ''): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE certificate_number IS NOT NULL" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];
        if ($fy) {
            $where .= " AND financial_year = ?";
            $params[] = $fy;
        }
        return $this->db->fetchAll("SELECT * FROM tds_register $where ORDER BY certificate_issued_at DESC", $params) ?: [];
    }

    private function getCurrentQuarter(): string
    {
        $month = (int)date('m');
        if ($month <= 3) return 'Q4';
        if ($month <= 6) return 'Q1';
        if ($month <= 9) return 'Q2';
        return 'Q3';
    }

    private function getCurrentFinancialYear(): string
    {
        $year = (int)date('Y');
        $month = (int)date('m');
        if ($month > 3) return $year . '-' . ($year + 1);
        return ($year - 1) . '-' . $year;
    }
}