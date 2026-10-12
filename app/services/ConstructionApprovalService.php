<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

class ConstructionApprovalService
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Submit an architectural map approval & construction permit application
     */
    public function submitApplication(array $data): array
    {
        $tenantId = (int)($data['tenant_id'] ?? 1);
        $customerId = (int)($data['customer_id'] ?? 0);
        $plotId = (int)($data['plot_id'] ?? 0);
        $bookingId = !empty($data['booking_id']) ? (int)$data['booking_id'] : null;

        if ($customerId <= 0 || $plotId <= 0) {
            return ['success' => false, 'message' => 'Valid Customer ID and Plot ID are required.'];
        }

        // Generate token
        $randSeq = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $token = 'APS-CNOC-GOR-' . date('Y') . '-' . $randSeq;

        $buildingType = $data['building_type'] ?? 'residential_g1';
        $groundCoverage = (float)($data['ground_coverage_sqft'] ?? 0.0);
        $totalBuiltup = (float)($data['total_builtup_sqft'] ?? 0.0);
        $frontSetback = (float)($data['front_setback_ft'] ?? 0.0);
        $rearSetback = (float)($data['rear_setback_ft'] ?? 0.0);
        $sideLeft = (float)($data['side_setback_left_ft'] ?? 0.0);
        $sideRight = (float)($data['side_setback_right_ft'] ?? 0.0);
        $rainwater = !empty($data['rainwater_harvesting_pit']) ? 1 : 0;
        $architectName = trim((string)($data['architect_name'] ?? ''));
        $architectReg = trim((string)($data['architect_council_reg'] ?? ''));

        // Basic setback compliance guideline check
        $setbackWarnings = [];
        if ($frontSetback < 10.0) {
            $setbackWarnings[] = 'Front setback is under standard 10.0 ft recommendation.';
        }
        if ($rearSetback < 5.0) {
            $setbackWarnings[] = 'Rear setback is under standard 5.0 ft recommendation.';
        }
        if ($sideLeft < 3.0 || $sideRight < 3.0) {
            $setbackWarnings[] = 'Side setbacks should be at least 3.0 ft for ventilation.';
        }
        if ($totalBuiltup >= 1000.0 && $rainwater === 0) {
            $setbackWarnings[] = 'Rainwater harvesting pit is mandatory for built-up area >= 1000 sq.ft under UP bylaws.';
        }

        $sql = "INSERT INTO construction_approvals (
                    tenant_id, customer_id, booking_id, plot_id, permit_token,
                    building_type, ground_coverage_sqft, total_builtup_sqft,
                    front_setback_ft, rear_setback_ft, side_setback_left_ft, side_setback_right_ft,
                    rainwater_harvesting_pit, architect_name, architect_council_reg,
                    status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    'submitted', NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId, $customerId, $bookingId, $plotId, $token,
            $buildingType, $groundCoverage, $totalBuiltup,
            $frontSetback, $rearSetback, $sideLeft, $sideRight,
            $rainwater, $architectName, $architectReg
        ]);

        $id = (int)$this->db->getConnection()->lastInsertId();

        return [
            'success' => true,
            'id' => $id,
            'permit_token' => $token,
            'setback_warnings' => $setbackWarnings,
            'message' => 'Construction clearance application submitted successfully.'
        ];
    }

    /**
     * Record site engineer's physical layout inspection
     */
    public function recordSiteInspection(int $id, array $data): bool
    {
        $engineer = trim((string)($data['site_engineer_name'] ?? 'APS Town Planning Cell'));
        $inspDate = $data['inspection_date'] ?? date('Y-m-d');
        $notes = trim((string)($data['inspection_notes'] ?? ''));

        $sql = "UPDATE construction_approvals 
                SET site_engineer_name = ?, 
                    inspection_date = ?, 
                    inspection_notes = ?, 
                    status = 'site_inspected', 
                    updated_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$engineer, $inspDate, $notes, $id]);
    }

    /**
     * Approve architectural plan & issue permit NOC
     */
    public function approvePermit(int $id, string $engineerName, ?string $notes = null): array
    {
        $app = $this->getApprovalDetails($id);
        if (!$app) {
            return ['success' => false, 'message' => 'Application not found.'];
        }

        $validityDate = date('Y-m-d', strtotime('+1 year')); // 1-year permit validity

        $sql = "UPDATE construction_approvals 
                SET status = 'approved',
                    approval_date = CURDATE(),
                    permit_validity_date = ?,
                    site_engineer_name = COALESCE(NULLIF(?, ''), site_engineer_name),
                    inspection_notes = COALESCE(NULLIF(?, ''), inspection_notes),
                    updated_at = NOW()
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$validityDate, $engineerName, $notes, $id]);

        return [
            'success' => true,
            'permit_token' => $app['permit_token'],
            'validity_date' => $validityDate,
            'message' => 'Architectural clearance and construction excavation permit approved.'
        ];
    }

    /**
     * Reject construction application
     */
    public function rejectApplication(int $id, string $reason): bool
    {
        $sql = "UPDATE construction_approvals 
                SET status = 'rejected',
                    rejection_reason = ?,
                    updated_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reason, $id]);
    }

    /**
     * Fetch complete application details with plot, colony, and customer data
     */
    public function getApprovalDetails(int $id): ?array
    {
        $sql = "SELECT ca.*,
                       p.plot_number, p.area_sqft, p.price_per_sqft, p.facing,
                       c.name AS colony_name, c.location AS colony_address,
                       u.name AS customer_name, u.phone AS customer_phone, u.email AS customer_email
                FROM construction_approvals ca
                JOIN plots p ON ca.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                JOIN users u ON ca.customer_id = u.id
                WHERE ca.id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Customer applications
     */
    public function getCustomerApplications(int $customerId): array
    {
        $sql = "SELECT ca.*, p.plot_number, p.area_sqft, c.name AS colony_name
                FROM construction_approvals ca
                JOIN plots p ON ca.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE ca.customer_id = ?
                ORDER BY ca.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Summary stats for admin dashboard
     */
    public function getSummaryStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) AS total_applications,
                    SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted_count,
                    SUM(CASE WHEN status = 'site_inspected' THEN 1 ELSE 0 END) AS inspected_count,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
                    COALESCE(SUM(total_builtup_sqft), 0) AS total_sanctioned_sqft
                FROM construction_approvals";
        $row = $this->db->fetch($sql);
        return [
            'total' => (int)($row['total_applications'] ?? 0),
            'submitted' => (int)($row['submitted_count'] ?? 0),
            'inspected' => (int)($row['inspected_count'] ?? 0),
            'approved' => (int)($row['approved_count'] ?? 0),
            'rejected' => (int)($row['rejected_count'] ?? 0),
            'total_sqft' => (float)($row['total_sanctioned_sqft'] ?? 0.0)
        ];
    }
}
