<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

class FarmerAcquisitionService
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Standard conversion factor: 1 Pucca Bigha (Gorakhpur / Purvanchal) = 27,225 sq.ft
     */
    public const SQFT_PER_BIGHA = 27225.0;

    /**
     * Create a new agricultural land acquisition agreement
     */
    public function createAcquisition(array $data): array
    {
        $tenantId = (int)($data['tenant_id'] ?? 1);
        $colonyId = !empty($data['target_colony_id']) ? (int)$data['target_colony_id'] : null;
        $farmerName = trim((string)($data['farmer_name'] ?? ''));
        $farmerPhone = trim((string)($data['farmer_phone'] ?? ''));
        $gataNumber = trim((string)($data['gata_number'] ?? ''));
        $mauzaVillage = trim((string)($data['mauza_village'] ?? ''));

        if (empty($farmerName) || empty($farmerPhone) || empty($gataNumber) || empty($mauzaVillage)) {
            return ['success' => false, 'message' => 'Farmer name, phone, Mauza village, and Gata number are required.'];
        }

        $randSeq = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $dealToken = 'APS-LAND-GOR-' . date('Y') . '-' . $randSeq;

        $areaBigha = (float)($data['area_bigha'] ?? 0.0);
        $areaSqft = (float)($data['area_sqft'] ?? ($areaBigha * self::SQFT_PER_BIGHA));
        $ratePerBigha = (float)($data['rate_per_bigha'] ?? 0.0);
        $totalDeal = (float)($data['total_deal_amount'] ?? ($areaBigha * $ratePerBigha));

        $tehsil = trim((string)($data['tehsil'] ?? 'Sadar'));
        $district = trim((string)($data['district'] ?? 'Gorakhpur'));
        $fasliYear = trim((string)($data['fasli_year'] ?? '1431-1436 Fasli'));
        $khatauniVerified = !empty($data['khatauni_verified']) ? 1 : 0;
        $aadhaar = trim((string)($data['farmer_aadhaar'] ?? ''));
        $pan = trim((string)($data['farmer_pan'] ?? ''));
        $bankAcc = trim((string)($data['bank_account_no'] ?? ''));
        $bankIfsc = trim((string)($data['bank_ifsc'] ?? ''));
        $bankName = trim((string)($data['bank_name'] ?? ''));
        $agreementDate = !empty($data['agreement_date']) ? $data['agreement_date'] : date('Y-m-d');
        $targetRegistry = !empty($data['target_registry_date']) ? $data['target_registry_date'] : date('Y-m-d', strtotime('+6 months'));
        $remarks = trim((string)($data['remarks'] ?? ''));

        $sql = "INSERT INTO farmer_land_acquisitions (
                    tenant_id, deal_token, target_colony_id, farmer_name, farmer_phone,
                    farmer_aadhaar, farmer_pan, bank_account_no, bank_ifsc, bank_name,
                    mauza_village, tehsil, district, gata_number, fasli_year,
                    khatauni_verified, area_bigha, area_sqft, rate_per_bigha,
                    total_deal_amount, paid_amount, status, agreement_date,
                    target_registry_date, remarks, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, 0.00, 'bayana_agreement', ?,
                    ?, ?, NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId, $dealToken, $colonyId, $farmerName, $farmerPhone,
            $aadhaar, $pan, $bankAcc, $bankIfsc, $bankName,
            $mauzaVillage, $tehsil, $district, $gataNumber, $fasliYear,
            $khatauniVerified, $areaBigha, $areaSqft, $ratePerBigha,
            $totalDeal, $agreementDate,
            $targetRegistry, $remarks
        ]);

        $acqId = (int)$this->db->getConnection()->lastInsertId();

        // Automatically setup standard 4 compensation milestones
        $this->createDefaultMilestones($acqId, $totalDeal);

        return [
            'success' => true,
            'id' => $acqId,
            'deal_token' => $dealToken,
            'total_deal_amount' => $totalDeal,
            'message' => 'Land acquisition agreement registered with standard compensation milestones.'
        ];
    }

    /**
     * Create standard 4 compensation milestones: Bayana 15%, 143 Mutation 35%, Demarcation 25%, Final Bainama 25%
     */
    private function createDefaultMilestones(int $acquisitionId, float $totalAmount): void
    {
        $milestones = [
            ['name' => 'Bayana Earnest Agreement Advance', 'pct' => 15.0],
            ['name' => 'UP Revenue Code Sec 143 Non-Agri Mutation Clearance', 'pct' => 35.0],
            ['name' => 'Physical Demarcation & Boundary Pegs Possession', 'pct' => 25.0],
            ['name' => 'Sub-Registrar SRO Final Deed Registry (Bainama)', 'pct' => 25.0]
        ];

        $stmt = $this->db->prepare("INSERT INTO farmer_compensation_milestones (
                    tenant_id, acquisition_id, milestone_name, percentage, amount, status, created_at
                ) VALUES (1, ?, ?, ?, ?, 'pending', NOW())");

        foreach ($milestones as $m) {
            $amt = round(($totalAmount * $m['pct']) / 100.0, 2);
            $stmt->execute([$acquisitionId, $m['name'], $m['pct'], $amt]);
        }
    }

    /**
     * Disburse a milestone payment with RTGS/NEFT transaction voucher
     */
    public function disburseMilestone(int $milestoneId, array $payoutData): array
    {
        $m = $this->db->fetch("SELECT * FROM farmer_compensation_milestones WHERE id = ? LIMIT 1", [$milestoneId]);
        if (!$m) {
            return ['success' => false, 'message' => 'Milestone not found.'];
        }

        $randSeq = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $voucherToken = 'APS-VOUCH-GOR-' . date('Y') . '-' . $randSeq;

        $mode = $payoutData['payment_mode'] ?? 'rtgs';
        $ref = trim((string)($payoutData['payment_ref'] ?? ''));
        $tds = (float)($payoutData['tds_deducted'] ?? 0.0);
        $disbursedAt = !empty($payoutData['disbursed_at']) ? $payoutData['disbursed_at'] : date('Y-m-d');

        $sql = "UPDATE farmer_compensation_milestones 
                SET status = 'disbursed',
                    disbursed_at = ?,
                    payment_mode = ?,
                    payment_ref = ?,
                    tds_deducted = ?,
                    voucher_token = ?,
                    updated_at = NOW()
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$disbursedAt, $mode, $ref, $tds, $voucherToken, $milestoneId]);

        // Update paid amount on parent acquisition
        $acqId = (int)$m['acquisition_id'];
        $sumPaid = $this->db->fetch("SELECT COALESCE(SUM(amount), 0) AS total_paid 
                                    FROM farmer_compensation_milestones 
                                    WHERE acquisition_id = ? AND status = 'disbursed'", [$acqId]);
        $paidTotal = (float)($sumPaid['total_paid'] ?? 0.0);

        $this->db->prepare("UPDATE farmer_land_acquisitions SET paid_amount = ?, updated_at = NOW() WHERE id = ?")
                 ->execute([$paidTotal, $acqId]);

        return [
            'success' => true,
            'voucher_token' => $voucherToken,
            'amount' => (float)$m['amount'],
            'total_paid' => $paidTotal,
            'message' => 'Milestone compensation disbursed successfully.'
        ];
    }

    /**
     * Update acquisition lifecycle status
     */
    public function updateStatus(int $id, string $status, ?string $remarks = null): bool
    {
        $sql = "UPDATE farmer_land_acquisitions 
                SET status = ?, 
                    remarks = COALESCE(?, remarks), 
                    updated_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $remarks, $id]);
    }

    /**
     * Get complete details of land acquisition
     */
    public function getAcquisitionDetails(int $id): ?array
    {
        $sql = "SELECT fla.*, c.name AS colony_name, c.location AS colony_location
                FROM farmer_land_acquisitions fla
                LEFT JOIN colonies c ON fla.target_colony_id = c.id
                WHERE fla.id = ?
                LIMIT 1";
        return $this->db->fetch($sql, [$id]) ?: null;
    }

    /**
     * Get all milestones for an acquisition
     */
    public function getMilestones(int $acquisitionId): array
    {
        $sql = "SELECT * FROM farmer_compensation_milestones WHERE acquisition_id = ? ORDER BY id ASC";
        return $this->db->fetchAll($sql, [$acquisitionId]) ?: [];
    }

    /**
     * Summary stats for Land Acquisition Desk
     */
    public function getSummaryStats(): array
    {
        $row = $this->db->fetch("SELECT 
                    COUNT(*) AS total_deals,
                    COALESCE(SUM(area_bigha), 0) AS total_bigha,
                    COALESCE(SUM(area_sqft), 0) AS total_sqft,
                    COALESCE(SUM(total_deal_amount), 0) AS total_committed,
                    COALESCE(SUM(paid_amount), 0) AS total_disbursed,
                    COALESCE(SUM(total_deal_amount - paid_amount), 0) AS total_pending,
                    SUM(CASE WHEN status = 'registry_completed' THEN 1 ELSE 0 END) AS registry_done_count
                FROM farmer_land_acquisitions");

        return [
            'total_deals' => (int)($row['total_deals'] ?? 0),
            'total_bigha' => (float)($row['total_bigha'] ?? 0.0),
            'total_sqft' => (float)($row['total_sqft'] ?? 0.0),
            'total_committed' => (float)($row['total_committed'] ?? 0.0),
            'total_disbursed' => (float)($row['total_disbursed'] ?? 0.0),
            'total_pending' => (float)($row['total_pending'] ?? 0.0),
            'completed' => (int)($row['registry_done_count'] ?? 0)
        ];
    }
}
