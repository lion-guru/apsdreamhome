<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class TownshipHorticultureService
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
            CREATE TABLE IF NOT EXISTS township_horticulture_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                colony_id INT NOT NULL,
                census_date DATE NOT NULL,
                park_name VARCHAR(100) DEFAULT 'Central Amrit Sarovar Park',
                green_area_sqft DECIMAL(10,2) DEFAULT 15000.00,
                trees_planted_count INT NOT NULL,
                tree_species_summary VARCHAR(255) DEFAULT 'Neem, Gulmohar, Peepal, Amaltas, Bougainvillea',
                survival_rate_pct DECIMAL(5,2) DEFAULT 92.50,
                watering_method ENUM('automated_sprinkler', 'tractor_tanker', 'manual_hose', 'drip_irrigation') DEFAULT 'automated_sprinkler',
                fertilizer_used_kg DECIMAL(6,2) DEFAULT 25.00,
                gardener_headcount INT DEFAULT 4,
                maintenance_tasks ENUM('routine_watering', 'grass_cutting_mowing', 'pest_control_spray', 'sapling_plantation', 'hedges_pruning') DEFAULT 'routine_watering',
                horticulture_supervisor VARCHAR(100) NOT NULL,
                supervisor_contact VARCHAR(20) NULL,
                remarks TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_colony_horticulture (colony_id, census_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function recordLog(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township/colony'];
        }

        $treesCount = (int)($data['trees_planted_count'] ?? 0);
        if ($treesCount <= 0) {
            return ['success' => false, 'error' => 'Trees planted / audited count must be greater than 0'];
        }

        $supervisor = trim($data['horticulture_supervisor'] ?? '');
        if ($supervisor === '') {
            return ['success' => false, 'error' => 'Horticulture supervisor name is required'];
        }

        $censusDate = !empty($data['census_date']) ? trim($data['census_date']) : date('Y-m-d');
        $parkName = trim($data['park_name'] ?? 'Central Amrit Sarovar Park');
        $greenAreaSqft = (float)($data['green_area_sqft'] ?? 15000.00);
        $speciesSummary = trim($data['tree_species_summary'] ?? 'Neem, Gulmohar, Peepal, Amaltas, Bougainvillea');
        $survivalRate = (float)($data['survival_rate_pct'] ?? 92.50);
        $wateringMethod = $data['watering_method'] ?? 'automated_sprinkler';
        $fertilizerKg = (float)($data['fertilizer_used_kg'] ?? 25.00);
        $gardenerCount = (int)($data['gardener_headcount'] ?? 4);
        $tasks = $data['maintenance_tasks'] ?? 'routine_watering';
        $supervisorContact = trim($data['supervisor_contact'] ?? '');
        $remarks = trim($data['remarks'] ?? '');

        $stmt = $this->db->prepare("
            INSERT INTO township_horticulture_logs (
                colony_id, census_date, park_name, green_area_sqft, trees_planted_count,
                tree_species_summary, survival_rate_pct, watering_method, fertilizer_used_kg,
                gardener_headcount, maintenance_tasks, horticulture_supervisor, supervisor_contact, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $colonyId, $censusDate, $parkName, $greenAreaSqft, $treesCount,
            $speciesSummary, $survivalRate, $wateringMethod, $fertilizerKg,
            $gardenerCount, $tasks, $supervisor, $supervisorContact, $remarks
        ]);

        return [
            'success' => true,
            'log_id' => (int)$this->db->lastInsertId(),
            'trees_count' => $treesCount
        ];
    }

    public function getGreenSummary(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_parks_audited,
                COALESCE(SUM(trees_planted_count), 0) as total_trees_planted,
                COALESCE(SUM(green_area_sqft), 0) as total_green_area_sqft,
                COALESCE(AVG(survival_rate_pct), 0) as avg_survival_rate,
                COALESCE(SUM(gardener_headcount), 0) as total_gardeners
            FROM township_horticulture_logs
        ");
        $stmt->execute();
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $summary['total_trees_planted'] = (int)($summary['total_trees_planted'] ?? 0);
        $summary['total_green_area_sqft'] = round((float)($summary['total_green_area_sqft'] ?? 0), 0);
        $summary['avg_survival_rate'] = round((float)($summary['avg_survival_rate'] ?? 0), 1);
        $summary['total_gardeners'] = (int)($summary['total_gardeners'] ?? 0);

        return $summary;
    }

    public function getLogs(?int $colonyId = null, int $limit = 50): array
    {
        $sql = "
            SELECT h.*, c.name as colony_name 
            FROM township_horticulture_logs h
            LEFT JOIN colonies c ON h.colony_id = c.id
        ";
        $params = [];
        if ($colonyId && $colonyId > 0) {
            $sql .= " WHERE h.colony_id = ? ";
            $params[] = $colonyId;
        }
        $sql .= " ORDER BY h.census_date DESC, h.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
