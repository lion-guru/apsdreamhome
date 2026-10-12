<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

class TownshipStpService
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log daily STP effluent treatment run & lab test
     */
    public function logStpRun(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        $logDate = !empty($data['log_date']) ? trim($data['log_date']) : date('Y-m-d');
        $plantCode = trim($data['stp_plant_code'] ?? '');
        $rawInflow = (float)($data['raw_sewage_inflow_kl'] ?? 0.0);
        $treatedOutput = (float)($data['treated_water_output_kl'] ?? 0.0);
        
        $inletBod = !empty($data['inlet_bod_mg_l']) ? (float)$data['inlet_bod_mg_l'] : null;
        $outletBod = !empty($data['outlet_bod_mg_l']) ? (float)$data['outlet_bod_mg_l'] : null;
        $inletCod = !empty($data['inlet_cod_mg_l']) ? (float)$data['inlet_cod_mg_l'] : null;
        $outletCod = !empty($data['outlet_cod_mg_l']) ? (float)$data['outlet_cod_mg_l'] : null;
        $outletTss = !empty($data['outlet_tss_mg_l']) ? (float)$data['outlet_tss_mg_l'] : null;
        $outletPh = !empty($data['outlet_ph']) ? (float)$data['outlet_ph'] : null;
        $chlorine = !empty($data['chlorine_dosing_ppm']) ? (float)$data['chlorine_dosing_ppm'] : null;
        $blowerHours = (float)($data['aeration_blower_hours'] ?? 0.0);

        $validReuse = ['gardening_horticulture', 'toilet_dual_flush', 'road_washing_dust_suppression', 'groundwater_recharge', 'overspill_drain'];
        $reusePurpose = in_array($data['water_reuse_purpose'] ?? '', $validReuse, true) ? $data['water_reuse_purpose'] : 'gardening_horticulture';

        $sludgeCake = (float)($data['sludge_cake_disposed_kg'] ?? 0.0);

        // Auto-detect UPPCB compliance status based on statutory limits:
        // BOD <= 10.0 mg/L, COD <= 50.0 mg/L, TSS <= 20.0 mg/L, pH between 6.5 and 8.5
        $autoCompliance = 'fully_compliant';
        if ($outletBod !== null && $outletBod > 10.0) {
            $autoCompliance = ($outletBod > 30.0) ? 'critical_violation' : 'minor_deviation';
        }
        if ($outletCod !== null && $outletCod > 50.0 && $autoCompliance !== 'critical_violation') {
            $autoCompliance = ($outletCod > 100.0) ? 'critical_violation' : 'minor_deviation';
        }
        if ($outletPh !== null && ($outletPh < 6.5 || $outletPh > 8.5)) {
            $autoCompliance = 'minor_deviation';
        }

        $validCompliance = ['fully_compliant', 'minor_deviation', 'critical_violation', 'sample_sent_lab'];
        $complianceStatus = in_array($data['uppcb_compliance_status'] ?? '', $validCompliance, true) ? $data['uppcb_compliance_status'] : $autoCompliance;

        $operator = trim($data['operator_name'] ?? '');
        $phone = trim($data['operator_phone'] ?? '');
        $remarks = trim($data['remarks'] ?? '');

        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township colony.'];
        }
        if (empty($plantCode)) {
            return ['success' => false, 'error' => 'STP Plant code / identifier is required.'];
        }

        // Calculate BOD removal efficiency %
        $bodEfficiency = 0.0;
        if ($inletBod !== null && $inletBod > 0 && $outletBod !== null) {
            $bodEfficiency = round((($inletBod - $outletBod) / $inletBod) * 100, 2);
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO township_stp_logs
                (colony_id, log_date, stp_plant_code, raw_sewage_inflow_kl, treated_water_output_kl, inlet_bod_mg_l, outlet_bod_mg_l, inlet_cod_mg_l, outlet_cod_mg_l, outlet_tss_mg_l, outlet_ph, chlorine_dosing_ppm, aeration_blower_hours, water_reuse_purpose, sludge_cake_disposed_kg, uppcb_compliance_status, operator_name, operator_phone, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $colonyId, $logDate, $plantCode, $rawInflow, $treatedOutput,
                $inletBod, $outletBod, $inletCod, $outletCod, $outletTss, $outletPh,
                $chlorine, $blowerHours, $reusePurpose, $sludgeCake, $complianceStatus,
                $operator, $phone, $remarks
            ]);

            return [
                'success' => true,
                'log_id' => (int)$this->db->lastInsertId(),
                'treated_water_output_kl' => $treatedOutput,
                'bod_efficiency_pct' => $bodEfficiency,
                'compliance_status' => $complianceStatus
            ];
        } catch (\Throwable $e) {
            error_log("TownshipStpService::logStpRun error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error recording STP effluent log.'];
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
                FROM township_stp_logs l
                JOIN colonies c ON l.colony_id = c.id
            ";
            $params = [];
            if ($colonyId) {
                $sql .= " WHERE l.colony_id = ?";
                $params[] = $colonyId;
            }
            $sql .= " ORDER BY l.log_date DESC, l.id DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("TownshipStpService::getLogs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Today's summary
     */
    public function getTodaySummary(?int $colonyId = null): array
    {
        try {
            $today = date('Y-m-d');
            $sql = "
                SELECT 
                    COUNT(*) as total_runs,
                    COALESCE(SUM(raw_sewage_inflow_kl), 0) as total_inflow_kl,
                    COALESCE(SUM(treated_water_output_kl), 0) as total_treated_kl,
                    COUNT(CASE WHEN uppcb_compliance_status = 'fully_compliant' THEN 1 END) as compliant_runs,
                    COALESCE(AVG(outlet_bod_mg_l), 0) as avg_outlet_bod
                FROM township_stp_logs
                WHERE log_date = ?
            ";
            $params = [$today];
            if ($colonyId) {
                $sql .= " AND colony_id = ?";
                $params[] = $colonyId;
            }

            $row = $this->db->fetchOne($sql, $params);

            return [
                'total_runs' => (int)($row['total_runs'] ?? 0),
                'total_inflow_kl' => round((float)($row['total_inflow_kl'] ?? 0.0), 2),
                'total_treated_kl' => round((float)($row['total_treated_kl'] ?? 0.0), 2),
                'compliant_runs' => (int)($row['compliant_runs'] ?? 0),
                'avg_outlet_bod' => round((float)($row['avg_outlet_bod'] ?? 0.0), 2)
            ];
        } catch (\Throwable $e) {
            error_log("TownshipStpService::getTodaySummary error: " . $e->getMessage());
            return [
                'total_runs' => 0,
                'total_inflow_kl' => 0.0,
                'total_treated_kl' => 0.0,
                'compliant_runs' => 0,
                'avg_outlet_bod' => 0.0
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
                SELECT l.*, c.name AS colony_name, c.address AS colony_address
                FROM township_stp_logs l
                JOIN colonies c ON l.colony_id = c.id
                WHERE l.id = ?
            ", [$id]);
        } catch (\Throwable $e) {
            error_log("TownshipStpService::getLogById error: " . $e->getMessage());
            return null;
        }
    }
}
