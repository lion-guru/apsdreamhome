<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

/**
 * Township Rainwater Harvesting & Groundwater Recharge Desk Service
 */
class TownshipRwhService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log RWH recharge pit audit & monsoon readiness check
     */
    public function logRwhAudit(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        $auditDate = !empty($data['audit_date']) ? trim($data['audit_date']) : date('Y-m-d');
        $pitId = trim($data['pit_identifier'] ?? '');
        $validTypes = ['borewell_recharge_shaft', 'percolation_tank', 'injection_well', 'catchment_swale'];
        $rechargeType = in_array($data['recharge_type'] ?? '', $validTypes, true) ? $data['recharge_type'] : 'borewell_recharge_shaft';

        $catchmentArea = (float)($data['catchment_area_sqft'] ?? 0.0);

        $validMedia = ['clean_gravel_sand', 'needs_flushing', 'replaced_charcoal', 'clogged'];
        $filtrationMedia = in_array($data['filtration_media_status'] ?? '', $validMedia, true) ? $data['filtration_media_status'] : 'clean_gravel_sand';

        $desiltingCleared = !empty($data['desilting_chambers_cleared']) ? 1 : 0;
        $rechargeCapacity = (float)($data['recharge_capacity_kl'] ?? 0.0);
        $waterTableDepth = !empty($data['water_table_depth_ft']) ? (float)$data['water_table_depth_ft'] : null;

        $validReadiness = ['optimal_ready', 'needs_cleaning', 'urgent_civil_repair', 'decommissioned'];
        $readiness = in_array($data['monsoon_readiness_score'] ?? '', $validReadiness, true) ? $data['monsoon_readiness_score'] : 'optimal_ready';

        $validNoc = ['compliant_valid', 'inspection_due', 'renewal_applied', 'exempt'];
        $nocStatus = in_array($data['up_groundwater_noc_status'] ?? '', $validNoc, true) ? $data['up_groundwater_noc_status'] : 'compliant_valid';

        $officer = trim($data['environmental_officer_name'] ?? '');
        $contact = trim($data['officer_contact'] ?? '');
        $maintenance = trim($data['maintenance_actions_done'] ?? '');
        $remarks = trim($data['remarks'] ?? '');

        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township colony.'];
        }
        if (empty($pitId)) {
            return ['success' => false, 'error' => 'Pit / Recharge shaft identifier is required.'];
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO township_rwh_logs
                (colony_id, audit_date, pit_identifier, recharge_type, catchment_area_sqft, filtration_media_status, desilting_chambers_cleared, recharge_capacity_kl, water_table_depth_ft, monsoon_readiness_score, up_groundwater_noc_status, environmental_officer_name, officer_contact, maintenance_actions_done, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $colonyId, $auditDate, $pitId, $rechargeType, $catchmentArea, $filtrationMedia, $desiltingCleared, $rechargeCapacity, $waterTableDepth, $readiness, $nocStatus, $officer, $contact, $maintenance, $remarks
            ]);

            return [
                'success' => true,
                'log_id' => (int)$this->db->lastInsertId(),
                'recharge_capacity_kl' => $rechargeCapacity,
                'readiness' => $readiness
            ];
        } catch (\Throwable $e) {
            error_log("TownshipRwhService::logRwhAudit error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error recording RWH audit.'];
        }
    }

    /**
     * Get recent logs with optional colony filtering
     */
    public function getLogs(?int $colonyId = null, int $limit = 50): array
    {
        try {
            $sql = "
                SELECT l.*, c.name AS colony_name
                FROM township_rwh_logs l
                JOIN colonies c ON l.colony_id = c.id
            ";
            $params = [];
            if ($colonyId) {
                $sql .= " WHERE l.colony_id = ?";
                $params[] = $colonyId;
            }
            $sql .= " ORDER BY l.audit_date DESC, l.id DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("TownshipRwhService::getLogs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Today's summary
     */
    public function getTodaySummary(): array
    {
        try {
            $today = date('Y-m-d');
            $row = $this->db->fetchOne("
                SELECT 
                    COUNT(*) as total_pits_audited,
                    COALESCE(SUM(recharge_capacity_kl), 0) as total_recharge_kl,
                    COALESCE(SUM(catchment_area_sqft), 0) as total_catchment_sqft,
                    COUNT(CASE WHEN monsoon_readiness_score = 'optimal_ready' THEN 1 END) as ready_pits
                FROM township_rwh_logs
                WHERE audit_date = ?
            ", [$today]);

            return [
                'total_pits_audited' => (int)($row['total_pits_audited'] ?? 0),
                'total_recharge_kl' => round((float)($row['total_recharge_kl'] ?? 0.0), 2),
                'total_catchment_sqft' => round((float)($row['total_catchment_sqft'] ?? 0.0), 2),
                'ready_pits' => (int)($row['ready_pits'] ?? 0)
            ];
        } catch (\Throwable $e) {
            error_log("TownshipRwhService::getTodaySummary error: " . $e->getMessage());
            return [
                'total_pits_audited' => 0,
                'total_recharge_kl' => 0.0,
                'total_catchment_sqft' => 0.0,
                'ready_pits' => 0
            ];
        }
    }

    /**
     * Get single log for official certificate print
     */
    public function getLogById(int $id): ?array
    {
        try {
            return $this->db->fetchOne("
                SELECT l.*, c.name as colony_name, c.district_id, d.name as district_name
                FROM township_rwh_logs l
                JOIN colonies c ON l.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                WHERE l.id = ?
            ", [$id]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
