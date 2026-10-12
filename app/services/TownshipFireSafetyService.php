<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

class TownshipFireSafetyService
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log fire hydrant line pressure, pump readiness & UP Fire NOC audit
     */
    public function logFireInspection(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        $inspectionDate = !empty($data['inspection_date']) ? trim($data['inspection_date']) : date('Y-m-d');
        $fireZone = trim($data['fire_zone_identifier'] ?? '');
        $pressureBar = (float)($data['static_line_pressure_bar'] ?? 7.00);

        $validJockey = ['auto_standby', 'running_pressurizing', 'manual_mode', 'fault_tripped'];
        $jockeyPump = in_array($data['jockey_pump_status'] ?? '', $validJockey, true) ? $data['jockey_pump_status'] : 'auto_standby';

        $validElectric = ['operational_ready', 'service_due', 'phase_trip', 'breakdown'];
        $electricPump = in_array($data['main_electric_pump_status'] ?? '', $validElectric, true) ? $data['main_electric_pump_status'] : 'operational_ready';

        $validDiesel = ['operational_ready', 'battery_weak', 'fuel_low', 'cranking_failure'];
        $dieselPump = in_array($data['diesel_engine_backup_status'] ?? '', $validDiesel, true) ? $data['diesel_engine_backup_status'] : 'operational_ready';

        $fireStorageKl = (float)($data['fire_water_storage_kl'] ?? 100.00);
        $valvesTested = (int)($data['hydrant_valves_tested'] ?? 0);
        $valvesHealthy = (int)($data['hydrant_valves_healthy'] ?? 0);

        // Auto-reconcile healthy valves if not provided
        if ($valvesHealthy === 0 && $valvesTested > 0) {
            $valvesHealthy = $valvesTested;
        }

        $hoseBoxes = (int)($data['hose_boxes_equipped'] ?? 0);
        $extinguishers = (int)($data['extinguishers_checked'] ?? 0);
        $fireDrill = !empty($data['fire_drill_conducted']) ? 1 : 0;

        $validNoc = ['valid_compliant', 'renewal_due_30d', 'renewal_applied', 'provisional_noc', 'expired'];
        $nocStatus = in_array($data['up_fire_noc_status'] ?? '', $validNoc, true) ? $data['up_fire_noc_status'] : 'valid_compliant';

        $nocNumber = trim($data['noc_certificate_number'] ?? '');
        $nocExpiry = !empty($data['noc_expiry_date']) ? trim($data['noc_expiry_date']) : null;
        $fireOfficer = trim($data['fire_officer_name'] ?? '');
        $contact = trim($data['fire_officer_contact'] ?? '');
        $defects = trim($data['defects_observed'] ?? '');
        $remarks = trim($data['remarks'] ?? '');

        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township colony.'];
        }
        if (empty($fireZone)) {
            return ['success' => false, 'error' => 'Fire zone identifier is required.'];
        }

        // Valve health %
        $valveHealthPct = ($valvesTested > 0) ? round(($valvesHealthy / $valvesTested) * 100, 2) : 100.00;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO township_fire_safety_logs
                (colony_id, inspection_date, fire_zone_identifier, static_line_pressure_bar, jockey_pump_status, main_electric_pump_status, diesel_engine_backup_status, fire_water_storage_kl, hydrant_valves_tested, hydrant_valves_healthy, hose_boxes_equipped, extinguishers_checked, fire_drill_conducted, up_fire_noc_status, noc_certificate_number, noc_expiry_date, fire_officer_name, fire_officer_contact, defects_observed, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $colonyId, $inspectionDate, $fireZone, $pressureBar,
                $jockeyPump, $electricPump, $dieselPump, $fireStorageKl,
                $valvesTested, $valvesHealthy, $hoseBoxes, $extinguishers,
                $fireDrill, $nocStatus, $nocNumber, $nocExpiry,
                $fireOfficer, $contact, $defects, $remarks
            ]);

            return [
                'success' => true,
                'log_id' => (int)$this->db->lastInsertId(),
                'pressure_bar' => $pressureBar,
                'valve_health_pct' => $valveHealthPct,
                'noc_status' => $nocStatus
            ];
        } catch (\Throwable $e) {
            error_log("TownshipFireSafetyService::logFireInspection error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error recording Fire Safety inspection.'];
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
                FROM township_fire_safety_logs l
                JOIN colonies c ON l.colony_id = c.id
            ";
            $params = [];
            if ($colonyId) {
                $sql .= " WHERE l.colony_id = ?";
                $params[] = $colonyId;
            }
            $sql .= " ORDER BY l.inspection_date DESC, l.id DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("TownshipFireSafetyService::getLogs error: " . $e->getMessage());
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
                    COUNT(*) as total_inspections,
                    COALESCE(AVG(static_line_pressure_bar), 0) as avg_line_pressure,
                    COALESCE(SUM(hydrant_valves_tested), 0) as total_valves_tested,
                    COALESCE(SUM(hydrant_valves_healthy), 0) as total_valves_healthy,
                    COUNT(CASE WHEN up_fire_noc_status = 'valid_compliant' THEN 1 END) as compliant_zones
                FROM township_fire_safety_logs
                WHERE inspection_date = ?
            ";
            $params = [$today];
            if ($colonyId) {
                $sql .= " AND colony_id = ?";
                $params[] = $colonyId;
            }

            $row = $this->db->fetchOne($sql, $params);

            return [
                'total_inspections' => (int)($row['total_inspections'] ?? 0),
                'avg_line_pressure' => round((float)($row['avg_line_pressure'] ?? 0.0), 2),
                'total_valves_tested' => (int)($row['total_valves_tested'] ?? 0),
                'total_valves_healthy' => (int)($row['total_valves_healthy'] ?? 0),
                'compliant_zones' => (int)($row['compliant_zones'] ?? 0)
            ];
        } catch (\Throwable $e) {
            error_log("TownshipFireSafetyService::getTodaySummary error: " . $e->getMessage());
            return [
                'total_inspections' => 0,
                'avg_line_pressure' => 0.0,
                'total_valves_tested' => 0,
                'total_valves_healthy' => 0,
                'compliant_zones' => 0
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
                FROM township_fire_safety_logs l
                JOIN colonies c ON l.colony_id = c.id
                WHERE l.id = ?
            ", [$id]);
        } catch (\Throwable $e) {
            error_log("TownshipFireSafetyService::getLogById error: " . $e->getMessage());
            return null;
        }
    }
}
