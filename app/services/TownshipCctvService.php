<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class TownshipCctvService
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
            CREATE TABLE IF NOT EXISTS township_cctv_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                colony_id INT NOT NULL,
                audit_date DATE NOT NULL,
                nvr_identifier VARCHAR(50) DEFAULT 'Main Control Room NVR-1 (32 Ch)',
                total_cameras_installed INT NOT NULL,
                cameras_online_count INT NOT NULL,
                cameras_offline_count INT NOT NULL DEFAULT 0,
                uptime_percentage DECIMAL(5,2) DEFAULT 100.00,
                recording_retention_days INT DEFAULT 30,
                hdd_storage_status ENUM('healthy', 'warning_space_low', 'hdd_fault', 'overwriting_normal') DEFAULT 'healthy',
                night_vision_ir_status ENUM('all_functional', 'minor_led_failure', 'maintenance_needed') DEFAULT 'all_functional',
                footage_exported_count INT DEFAULT 0,
                cctv_technician_name VARCHAR(100) NOT NULL,
                technician_phone VARCHAR(20) NULL,
                offline_camera_locations TEXT NULL,
                remarks TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_colony_cctv_date (colony_id, audit_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function logAudit(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township/colony'];
        }

        $totalCameras = (int)($data['total_cameras_installed'] ?? 0);
        if ($totalCameras <= 0) {
            return ['success' => false, 'error' => 'Total cameras installed must be greater than 0'];
        }

        $onlineCameras = (int)($data['cameras_online_count'] ?? 0);
        if ($onlineCameras < 0 || $onlineCameras > $totalCameras) {
            return ['success' => false, 'error' => 'Online cameras count cannot exceed total cameras'];
        }

        $technicianName = trim($data['cctv_technician_name'] ?? '');
        if ($technicianName === '') {
            return ['success' => false, 'error' => 'CCTV technician / operator name is required'];
        }

        $auditDate = !empty($data['audit_date']) ? trim($data['audit_date']) : date('Y-m-d');
        $nvrIdentifier = trim($data['nvr_identifier'] ?? 'Main Control Room NVR-1 (32 Ch)');
        $offlineCameras = $totalCameras - $onlineCameras;
        $uptimePct = round(($onlineCameras / $totalCameras) * 100, 2);
        $retentionDays = (int)($data['recording_retention_days'] ?? 30);
        $hddStatus = $data['hdd_storage_status'] ?? 'healthy';
        $irStatus = $data['night_vision_ir_status'] ?? 'all_functional';
        $exportedCount = (int)($data['footage_exported_count'] ?? 0);
        $techPhone = trim($data['technician_phone'] ?? '');
        $offlineLocs = trim($data['offline_camera_locations'] ?? '');
        $remarks = trim($data['remarks'] ?? '');

        $stmt = $this->db->prepare("
            INSERT INTO township_cctv_logs (
                colony_id, audit_date, nvr_identifier, total_cameras_installed, cameras_online_count,
                cameras_offline_count, uptime_percentage, recording_retention_days, hdd_storage_status,
                night_vision_ir_status, footage_exported_count, cctv_technician_name, technician_phone,
                offline_camera_locations, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $colonyId, $auditDate, $nvrIdentifier, $totalCameras, $onlineCameras,
            $offlineCameras, $uptimePct, $retentionDays, $hddStatus,
            $irStatus, $exportedCount, $technicianName, $techPhone,
            $offlineLocs, $remarks
        ]);

        $logId = (int)$this->db->lastInsertId();

        return [
            'success' => true,
            'log_id' => $logId,
            'uptime_percentage' => $uptimePct,
            'offline_count' => $offlineCameras
        ];
    }

    public function getTodaySummary(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_nvrs_audited,
                COALESCE(SUM(total_cameras_installed), 0) as total_cameras,
                COALESCE(SUM(cameras_online_count), 0) as total_online,
                COALESCE(SUM(cameras_offline_count), 0) as total_offline,
                COALESCE(AVG(uptime_percentage), 0) as avg_uptime_pct,
                COALESCE(SUM(footage_exported_count), 0) as total_footage_requests,
                SUM(CASE WHEN hdd_storage_status = 'hdd_fault' OR night_vision_ir_status = 'maintenance_needed' THEN 1 ELSE 0 END) as alerts_count
            FROM township_cctv_logs
            WHERE audit_date = ?
        ");
        $stmt->execute([$today]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $summary['total_cameras'] = (int)($summary['total_cameras'] ?? 0);
        $summary['total_online'] = (int)($summary['total_online'] ?? 0);
        $summary['total_offline'] = (int)($summary['total_offline'] ?? 0);
        $summary['avg_uptime_pct'] = round((float)($summary['avg_uptime_pct'] ?? 100.0), 1);
        $summary['total_footage_requests'] = (int)($summary['total_footage_requests'] ?? 0);

        return $summary;
    }

    public function getLogs(?int $colonyId = null, int $limit = 50): array
    {
        $sql = "
            SELECT v.*, c.name as colony_name 
            FROM township_cctv_logs v
            LEFT JOIN colonies c ON v.colony_id = c.id
        ";
        $params = [];
        if ($colonyId && $colonyId > 0) {
            $sql .= " WHERE v.colony_id = ? ";
            $params[] = $colonyId;
        }
        $sql .= " ORDER BY v.audit_date DESC, v.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLogById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT v.*, c.name as colony_name 
            FROM township_cctv_logs v
            LEFT JOIN colonies c ON v.colony_id = c.id
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
