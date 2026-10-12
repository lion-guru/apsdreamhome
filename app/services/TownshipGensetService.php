<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class TownshipGensetService
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
            CREATE TABLE IF NOT EXISTS township_genset_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                colony_id INT NOT NULL,
                log_date DATE NOT NULL,
                genset_number VARCHAR(50) DEFAULT 'Central DG Set #1 (125 kVA)',
                start_time TIME NULL,
                stop_time TIME NULL,
                run_hours DECIMAL(5,2) DEFAULT 0.00,
                diesel_consumed_litres DECIMAL(7,2) NOT NULL,
                diesel_added_litres DECIMAL(7,2) DEFAULT 0.00,
                diesel_closing_stock_litres DECIMAL(7,2) DEFAULT 0.00,
                power_generated_kwh DECIMAL(8,2) DEFAULT 0.00,
                fuel_efficiency_kwh_per_litre DECIMAL(5,2) DEFAULT 0.00,
                diesel_rate_per_litre DECIMAL(6,2) DEFAULT 90.50,
                diesel_expense_inr DECIMAL(9,2) DEFAULT 0.00,
                outage_reason ENUM('discom_grid_failure', 'transformer_overload', 'storm_feeder_breakdown', 'scheduled_load_shedding', 'preventive_routine_trial') DEFAULT 'discom_grid_failure',
                operator_name VARCHAR(100) NOT NULL,
                operator_phone VARCHAR(20) NULL,
                mechanical_status ENUM('optimal', 'lube_oil_service_due', 'low_coolant', 'battery_charger_fault', 'breakdown') DEFAULT 'optimal',
                remarks TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_colony_genset_date (colony_id, log_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function logGensetRun(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township/colony'];
        }

        $operatorName = trim($data['operator_name'] ?? '');
        if ($operatorName === '') {
            return ['success' => false, 'error' => 'Genset operator name is required'];
        }

        $logDate = !empty($data['log_date']) ? trim($data['log_date']) : date('Y-m-d');
        $gensetNumber = trim($data['genset_number'] ?? 'Central DG Set #1 (125 kVA)');
        $startTime = $data['start_time'] ?? null;
        $stopTime = $data['stop_time'] ?? null;

        $runHours = (float)($data['run_hours'] ?? 0);
        if ($runHours <= 0 && $startTime && $stopTime) {
            $diffSeconds = strtotime($stopTime) - strtotime($startTime);
            if ($diffSeconds > 0) {
                $runHours = round($diffSeconds / 3600, 2);
            }
        }

        $dieselConsumed = (float)($data['diesel_consumed_litres'] ?? 0.00);
        if ($dieselConsumed <= 0) {
            return ['success' => false, 'error' => 'Diesel consumed in litres is required'];
        }

        $dieselAdded = (float)($data['diesel_added_litres'] ?? 0.00);
        $closingStock = (float)($data['diesel_closing_stock_litres'] ?? 0.00);
        $powerGenerated = (float)($data['power_generated_kwh'] ?? 0.00);

        // Auto calculate fuel efficiency (kWh / Litre)
        $efficiency = ($dieselConsumed > 0 && $powerGenerated > 0)
            ? round($powerGenerated / $dieselConsumed, 2)
            : 0.00;

        $dieselRate = (float)($data['diesel_rate_per_litre'] ?? 90.50);
        $dieselExpense = round($dieselConsumed * $dieselRate, 2);

        $outageReason = $data['outage_reason'] ?? 'discom_grid_failure';
        $operatorPhone = trim($data['operator_phone'] ?? '');
        $mechanicalStatus = $data['mechanical_status'] ?? 'optimal';
        $remarks = trim($data['remarks'] ?? '');

        $stmt = $this->db->prepare("
            INSERT INTO township_genset_logs (
                colony_id, log_date, genset_number, start_time, stop_time,
                run_hours, diesel_consumed_litres, diesel_added_litres, diesel_closing_stock_litres,
                power_generated_kwh, fuel_efficiency_kwh_per_litre, diesel_rate_per_litre, diesel_expense_inr,
                outage_reason, operator_name, operator_phone, mechanical_status, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $colonyId, $logDate, $gensetNumber, $startTime, $stopTime,
            $runHours, $dieselConsumed, $dieselAdded, $closingStock,
            $powerGenerated, $efficiency, $dieselRate, $dieselExpense,
            $outageReason, $operatorName, $operatorPhone, $mechanicalStatus, $remarks
        ]);

        $logId = (int)$this->db->lastInsertId();

        return [
            'success' => true,
            'log_id' => $logId,
            'run_hours' => $runHours,
            'diesel_consumed' => $dieselConsumed,
            'diesel_expense_inr' => $dieselExpense,
            'fuel_efficiency' => $efficiency
        ];
    }

    public function getTodaySummary(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_runs,
                COALESCE(SUM(run_hours), 0) as total_run_hours,
                COALESCE(SUM(diesel_consumed_litres), 0) as total_diesel_litres,
                COALESCE(SUM(diesel_expense_inr), 0) as total_diesel_expense,
                COALESCE(SUM(power_generated_kwh), 0) as total_power_kwh,
                COALESCE(AVG(fuel_efficiency_kwh_per_litre), 0) as avg_efficiency,
                SUM(CASE WHEN mechanical_status != 'optimal' THEN 1 ELSE 0 END) as alerts_count
            FROM township_genset_logs
            WHERE log_date = ?
        ");
        $stmt->execute([$today]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $summary['total_run_hours'] = round((float)($summary['total_run_hours'] ?? 0), 1);
        $summary['total_diesel_litres'] = round((float)($summary['total_diesel_litres'] ?? 0), 1);
        $summary['total_diesel_expense'] = round((float)($summary['total_diesel_expense'] ?? 0), 2);
        $summary['total_power_kwh'] = round((float)($summary['total_power_kwh'] ?? 0), 1);
        $summary['avg_efficiency'] = round((float)($summary['avg_efficiency'] ?? 0), 2);

        return $summary;
    }

    public function getLogs(?int $colonyId = null, int $limit = 50): array
    {
        $sql = "
            SELECT g.*, c.name as colony_name 
            FROM township_genset_logs g
            LEFT JOIN colonies c ON g.colony_id = c.id
        ";
        $params = [];
        if ($colonyId && $colonyId > 0) {
            $sql .= " WHERE g.colony_id = ? ";
            $params[] = $colonyId;
        }
        $sql .= " ORDER BY g.log_date DESC, g.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLogById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT g.*, c.name as colony_name 
            FROM township_genset_logs g
            LEFT JOIN colonies c ON g.colony_id = c.id
            WHERE g.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
