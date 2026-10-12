<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class TownshipWaterService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS township_water_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                colony_id INT NOT NULL,
                log_date DATE NOT NULL,
                shift ENUM('morning', 'afternoon', 'evening', 'night') DEFAULT 'morning',
                pump_number VARCHAR(50) DEFAULT 'Borewell Pump #1',
                pump_start_time TIME NULL,
                pump_stop_time TIME NULL,
                run_hours DECIMAL(5,2) DEFAULT 0.00,
                power_units_kwh DECIMAL(8,2) DEFAULT 0.00,
                water_pumped_kl DECIMAL(8,2) DEFAULT 0.00,
                oht_level_pct INT DEFAULT 85,
                tds_ppm INT DEFAULT 240,
                ph_level DECIMAL(3,1) DEFAULT 7.2,
                chlorine_dose_gm DECIMAL(6,2) DEFAULT 50.00,
                line_pressure_bar DECIMAL(4,2) DEFAULT 2.50,
                operator_name VARCHAR(100) NOT NULL,
                operator_phone VARCHAR(20) NULL,
                pump_health ENUM('optimal', 'minor_vibration', 'low_yield', 'needs_servicing', 'breakdown') DEFAULT 'optimal',
                remarks TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_colony_date (colony_id, log_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function logPumpingRun(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township/colony'];
        }

        $logDate = !empty($data['log_date']) ? trim($data['log_date']) : date('Y-m-d');
        $shift = $data['shift'] ?? 'morning';
        $pumpNumber = trim($data['pump_number'] ?? 'Borewell Pump #1');
        $operatorName = trim($data['operator_name'] ?? '');
        if ($operatorName === '') {
            return ['success' => false, 'error' => 'Operator name is required'];
        }

        $startTime = $data['pump_start_time'] ?? null;
        $stopTime = $data['pump_stop_time'] ?? null;

        // Auto compute run hours if times are provided and run_hours not manually entered
        $runHours = (float)($data['run_hours'] ?? 0);
        if ($runHours <= 0 && $startTime && $stopTime) {
            $diffSeconds = strtotime($stopTime) - strtotime($startTime);
            if ($diffSeconds > 0) {
                $runHours = round($diffSeconds / 3600, 2);
            }
        }

        $powerUnits = (float)($data['power_units_kwh'] ?? 0.00);
        $waterPumpedKl = (float)($data['water_pumped_kl'] ?? 0.00);
        $ohtLevel = (int)($data['oht_level_pct'] ?? 85);
        $tds = (int)($data['tds_ppm'] ?? 240);
        $ph = (float)($data['ph_level'] ?? 7.2);
        $chlorineDose = (float)($data['chlorine_dose_gm'] ?? 50.00);
        $linePressure = (float)($data['line_pressure_bar'] ?? 2.50);
        $operatorPhone = trim($data['operator_phone'] ?? '');
        $pumpHealth = $data['pump_health'] ?? 'optimal';
        $remarks = trim($data['remarks'] ?? '');

        $stmt = $this->db->prepare("
            INSERT INTO township_water_logs (
                colony_id, log_date, shift, pump_number, pump_start_time, pump_stop_time,
                run_hours, power_units_kwh, water_pumped_kl, oht_level_pct, tds_ppm, ph_level,
                chlorine_dose_gm, line_pressure_bar, operator_name, operator_phone, pump_health, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $colonyId, $logDate, $shift, $pumpNumber, $startTime, $stopTime,
            $runHours, $powerUnits, $waterPumpedKl, $ohtLevel, $tds, $ph,
            $chlorineDose, $linePressure, $operatorName, $operatorPhone, $pumpHealth, $remarks
        ]);

        $logId = (int)$this->db->lastInsertId();

        // Evaluate water quality compliance
        $qualityAudit = $this->evaluatePotableQuality($tds, $ph);

        return [
            'success' => true,
            'log_id' => $logId,
            'water_pumped_kl' => $waterPumpedKl,
            'run_hours' => $runHours,
            'quality_audit' => $qualityAudit
        ];
    }

    public function evaluatePotableQuality(int $tds, float $ph): array
    {
        // BIS 10500 standard: TDS acceptable < 500 ppm, pH acceptable 6.5 - 8.5
        $tdsStatus = 'optimal';
        if ($tds < 150) {
            $tdsStatus = 'low_minerals';
        } elseif ($tds <= 300) {
            $tdsStatus = 'excellent';
        } elseif ($tds <= 500) {
            $tdsStatus = 'acceptable';
        } else {
            $tdsStatus = 'high_tds_alert';
        }

        $phStatus = ($ph >= 6.5 && $ph <= 8.5) ? 'compliant' : 'deviated';

        return [
            'tds_ppm' => $tds,
            'tds_status' => $tdsStatus,
            'ph_level' => $ph,
            'ph_status' => $phStatus,
            'is_potable' => ($tds <= 500 && $ph >= 6.5 && $ph <= 8.5)
        ];
    }

    public function getTodaySummary(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_logs,
                COALESCE(SUM(water_pumped_kl), 0) as total_water_kl,
                COALESCE(SUM(run_hours), 0) as total_run_hours,
                COALESCE(SUM(power_units_kwh), 0) as total_power_kwh,
                COALESCE(AVG(tds_ppm), 0) as avg_tds,
                COALESCE(AVG(ph_level), 0) as avg_ph,
                SUM(CASE WHEN pump_health != 'optimal' THEN 1 ELSE 0 END) as alerts_count
            FROM township_water_logs
            WHERE log_date = ?
        ");
        $stmt->execute([$today]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $summary['total_water_kl'] = round((float)($summary['total_water_kl'] ?? 0), 2);
        $summary['total_run_hours'] = round((float)($summary['total_run_hours'] ?? 0), 1);
        $summary['total_power_kwh'] = round((float)($summary['total_power_kwh'] ?? 0), 1);
        $summary['avg_tds'] = round((float)($summary['avg_tds'] ?? 0), 0);
        $summary['avg_ph'] = round((float)($summary['avg_ph'] ?? 0), 1);

        return $summary;
    }

    public function getLogs(?int $colonyId = null, int $limit = 50): array
    {
        $sql = "
            SELECT w.*, c.name as colony_name 
            FROM township_water_logs w
            LEFT JOIN colonies c ON w.colony_id = c.id
        ";
        $params = [];
        if ($colonyId && $colonyId > 0) {
            $sql .= " WHERE w.colony_id = ? ";
            $params[] = $colonyId;
        }
        $sql .= " ORDER BY w.log_date DESC, w.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLogById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT w.*, c.name as colony_name 
            FROM township_water_logs w
            LEFT JOIN colonies c ON w.colony_id = c.id
            WHERE w.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
