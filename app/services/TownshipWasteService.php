<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class TownshipWasteService
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
            CREATE TABLE IF NOT EXISTS township_waste_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                colony_id INT NOT NULL,
                collection_date DATE NOT NULL,
                shift ENUM('morning_sweep', 'evening_sweep') DEFAULT 'morning_sweep',
                plots_serviced_count INT NOT NULL,
                wet_waste_kg DECIMAL(8,2) NOT NULL,
                dry_waste_kg DECIMAL(8,2) NOT NULL,
                total_waste_kg DECIMAL(8,2) NOT NULL,
                compost_pit_diverted_kg DECIMAL(8,2) DEFAULT 0.00,
                disposal_destination ENUM('society_compost_pit', 'nagar_nigam_designated_dump', 'recycling_vendor') DEFAULT 'society_compost_pit',
                tractor_trips_count INT DEFAULT 1,
                sanitation_headcount INT DEFAULT 4,
                sanitation_supervisor VARCHAR(100) NOT NULL,
                supervisor_contact VARCHAR(20) NULL,
                drain_cleaning_status ENUM('completed', 'in_progress', 'scheduled', 'clogged_alert') DEFAULT 'completed',
                remarks TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_colony_waste_date (colony_id, collection_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function recordCollection(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township/colony'];
        }

        $plotsServiced = (int)($data['plots_serviced_count'] ?? 0);
        if ($plotsServiced <= 0) {
            return ['success' => false, 'error' => 'Plots / houses serviced count must be greater than 0'];
        }

        $supervisor = trim($data['sanitation_supervisor'] ?? '');
        if ($supervisor === '') {
            return ['success' => false, 'error' => 'Sanitation supervisor name is required'];
        }

        $collectionDate = !empty($data['collection_date']) ? trim($data['collection_date']) : date('Y-m-d');
        $shift = $data['shift'] ?? 'morning_sweep';
        $wetWaste = (float)($data['wet_waste_kg'] ?? 0.00);
        $dryWaste = (float)($data['dry_waste_kg'] ?? 0.00);
        $totalWaste = round($wetWaste + $dryWaste, 2);
        $compostDiverted = (float)($data['compost_pit_diverted_kg'] ?? 0.00);
        $destination = $data['disposal_destination'] ?? 'society_compost_pit';
        $trips = (int)($data['tractor_trips_count'] ?? 1);
        $headcount = (int)($data['sanitation_headcount'] ?? 4);
        $contact = trim($data['supervisor_contact'] ?? '');
        $drainStatus = $data['drain_cleaning_status'] ?? 'completed';
        $remarks = trim($data['remarks'] ?? '');

        $stmt = $this->db->prepare("
            INSERT INTO township_waste_logs (
                colony_id, collection_date, shift, plots_serviced_count, wet_waste_kg,
                dry_waste_kg, total_waste_kg, compost_pit_diverted_kg, disposal_destination,
                tractor_trips_count, sanitation_headcount, sanitation_supervisor,
                supervisor_contact, drain_cleaning_status, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $colonyId, $collectionDate, $shift, $plotsServiced, $wetWaste,
            $dryWaste, $totalWaste, $compostDiverted, $destination,
            $trips, $headcount, $supervisor, $contact, $drainStatus, $remarks
        ]);

        return [
            'success' => true,
            'log_id' => (int)$this->db->lastInsertId(),
            'total_waste_kg' => $totalWaste
        ];
    }

    public function getWasteSummary(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_collection_shifts,
                COALESCE(SUM(total_waste_kg), 0) as total_waste_kg,
                COALESCE(SUM(wet_waste_kg), 0) as total_wet_kg,
                COALESCE(SUM(dry_waste_kg), 0) as total_dry_kg,
                COALESCE(SUM(compost_pit_diverted_kg), 0) as total_compost_kg,
                COALESCE(AVG(plots_serviced_count), 0) as avg_plots_serviced,
                COALESCE(SUM(tractor_trips_count), 0) as total_tractor_trips
            FROM township_waste_logs
        ");
        $stmt->execute();
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $summary['total_waste_kg'] = round((float)($summary['total_waste_kg'] ?? 0), 1);
        $summary['total_wet_kg'] = round((float)($summary['total_wet_kg'] ?? 0), 1);
        $summary['total_dry_kg'] = round((float)($summary['total_dry_kg'] ?? 0), 1);
        $summary['total_compost_kg'] = round((float)($summary['total_compost_kg'] ?? 0), 1);
        $summary['avg_plots_serviced'] = round((float)($summary['avg_plots_serviced'] ?? 0), 0);
        $summary['total_tractor_trips'] = (int)($summary['total_tractor_trips'] ?? 0);

        return $summary;
    }

    public function getLogs(?int $colonyId = null, int $limit = 50): array
    {
        $sql = "
            SELECT w.*, c.name as colony_name 
            FROM township_waste_logs w
            LEFT JOIN colonies c ON w.colony_id = c.id
        ";
        $params = [];
        if ($colonyId && $colonyId > 0) {
            $sql .= " WHERE w.colony_id = ? ";
            $params[] = $colonyId;
        }
        $sql .= " ORDER BY w.collection_date DESC, w.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
