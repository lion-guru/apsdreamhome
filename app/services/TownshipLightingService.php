<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

/**
 * Township Central Street Lighting & Feeder Pillar Uptime Desk Service
 */
class TownshipLightingService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log daily street light audit & feeder pillar run
     */
    public function logLightingAudit(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        $auditDate = !empty($data['audit_date']) ? trim($data['audit_date']) : date('Y-m-d');
        $feederPillar = trim($data['feeder_pillar_code'] ?? '');
        $totalPoles = (int)($data['total_poles_connected'] ?? 0);
        $polesFunctional = (int)($data['poles_functional'] ?? 0);
        $polesFaulty = (int)($data['poles_faulty'] ?? 0);
        $validTimers = ['astronomical_timer', 'photocell_sensor', 'manual_mcb_switch', 'smart_iot'];
        $timerMode = in_array($data['timer_mode'] ?? '', $validTimers, true) ? $data['timer_mode'] : 'astronomical_timer';

        $onTime = !empty($data['on_time']) ? trim($data['on_time']) : null;
        $offTime = !empty($data['off_time']) ? trim($data['off_time']) : null;

        $validPhases = ['balanced_ryb', 'phase_r_trip', 'phase_y_trip', 'phase_b_trip', 'voltage_fluctuation'];
        $phaseBalance = in_array($data['phase_balance_status'] ?? '', $validPhases, true) ? $data['phase_balance_status'] : 'balanced_ryb';

        $validCables = ['healthy_underground', 'minor_joint_fault', 'cut_repair_needed'];
        $cableHealth = in_array($data['cable_health'] ?? '', $validCables, true) ? $data['cable_health'] : 'healthy_underground';

        $faultyPoles = trim($data['faulty_pole_numbers'] ?? '');
        $electrician = trim($data['electrician_name'] ?? '');
        $phone = trim($data['electrician_phone'] ?? '');
        $remarks = trim($data['remarks'] ?? '');

        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township colony.'];
        }
        if (empty($feederPillar)) {
            return ['success' => false, 'error' => 'Feeder pillar code is required.'];
        }
        if ($totalPoles <= 0) {
            return ['success' => false, 'error' => 'Total connected poles must be greater than zero.'];
        }

        // Auto-reconcile faulty poles if not provided
        if ($polesFaulty === 0 && $polesFunctional < $totalPoles) {
            $polesFaulty = $totalPoles - $polesFunctional;
        } elseif ($polesFunctional === 0 && $polesFaulty < $totalPoles) {
            $polesFunctional = $totalPoles - $polesFaulty;
        }

        // Calculate uptime percentage
        $uptime = ($totalPoles > 0) ? round(($polesFunctional / $totalPoles) * 100, 2) : 0.00;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO township_lighting_logs 
                (colony_id, audit_date, feeder_pillar_code, total_poles_connected, poles_functional, poles_faulty, uptime_percent, timer_mode, on_time, off_time, phase_balance_status, cable_health, faulty_pole_numbers, electrician_name, electrician_phone, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $colonyId, $auditDate, $feederPillar, $totalPoles, $polesFunctional, $polesFaulty, $uptime, $timerMode, $onTime, $offTime, $phaseBalance, $cableHealth, $faultyPoles, $electrician, $phone, $remarks
            ]);

            return [
                'success' => true,
                'log_id' => (int)$this->db->lastInsertId(),
                'uptime_percent' => $uptime,
                'poles_functional' => $polesFunctional,
                'poles_faulty' => $polesFaulty
            ];
        } catch (\Throwable $e) {
            error_log("TownshipLightingService::logLightingAudit error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error recording lighting audit.'];
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
                FROM township_lighting_logs l
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
            error_log("TownshipLightingService::getLogs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get today's aggregated summary
     */
    public function getTodaySummary(): array
    {
        try {
            $today = date('Y-m-d');
            $row = $this->db->fetchOne("
                SELECT 
                    COUNT(*) as total_audits,
                    COALESCE(SUM(total_poles_connected), 0) as total_poles,
                    COALESCE(SUM(poles_functional), 0) as total_functional,
                    COALESCE(SUM(poles_faulty), 0) as total_faulty,
                    COALESCE(AVG(uptime_percent), 100.0) as avg_uptime
                FROM township_lighting_logs
                WHERE audit_date = ?
            ", [$today]);

            return [
                'total_audits' => (int)($row['total_audits'] ?? 0),
                'total_poles' => (int)($row['total_poles'] ?? 0),
                'total_functional' => (int)($row['total_functional'] ?? 0),
                'total_faulty' => (int)($row['total_faulty'] ?? 0),
                'avg_uptime' => round((float)($row['avg_uptime'] ?? 100.0), 2)
            ];
        } catch (\Throwable $e) {
            error_log("TownshipLightingService::getTodaySummary error: " . $e->getMessage());
            return [
                'total_audits' => 0,
                'total_poles' => 0,
                'total_functional' => 0,
                'total_faulty' => 0,
                'avg_uptime' => 100.0
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
                FROM township_lighting_logs l
                JOIN colonies c ON l.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                WHERE l.id = ?
            ", [$id]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
