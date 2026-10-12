<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class TownshipSecurityService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        $this->ensureTablesExist();
    }

    private function ensureTablesExist(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS township_security_rosters (
                id INT AUTO_INCREMENT PRIMARY KEY,
                colony_id INT NOT NULL,
                roster_date DATE NOT NULL,
                shift ENUM('morning_06_14', 'evening_14_22', 'night_22_06') NOT NULL,
                gate_name VARCHAR(50) DEFAULT 'Main Entrance Gate #1',
                guard_name VARCHAR(100) NOT NULL,
                guard_aadhaar_last4 VARCHAR(4) NOT NULL,
                guard_phone VARCHAR(20) NOT NULL,
                agency_name VARCHAR(100) DEFAULT 'Purvanchal Security Services',
                supervisor_name VARCHAR(100) DEFAULT 'Dharmendra Singh',
                supervisor_contact VARCHAR(20) NULL,
                status ENUM('scheduled', 'checked_in', 'relieved', 'absent') DEFAULT 'checked_in',
                checkin_time TIME NULL,
                checkout_time TIME NULL,
                boom_barrier_status ENUM('functional', 'manual_override', 'under_maintenance') DEFAULT 'functional',
                visitor_vehicles_logged INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_colony_roster (colony_id, roster_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS township_security_incidents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                incident_token VARCHAR(30) UNIQUE NOT NULL,
                colony_id INT NOT NULL,
                roster_id INT NULL,
                incident_date DATE NOT NULL,
                incident_time TIME NOT NULL,
                category ENUM('unauthorized_trespass', 'vehicle_parking_dispute', 'street_light_blackout', 'stray_cattle_intrusion', 'gate_boom_barrier_hit', 'suspicious_activity', 'other') NOT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                location_detail VARCHAR(150) NOT NULL,
                description TEXT NOT NULL,
                action_taken TEXT NULL,
                police_informed TINYINT(1) DEFAULT 0,
                status ENUM('logged', 'under_review', 'resolved') DEFAULT 'logged',
                resolved_by VARCHAR(100) NULL,
                resolution_notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_colony_status (colony_id, status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function generateIncidentToken(): string
    {
        $year = date('Y');
        $random = strtoupper(bin2hex(random_bytes(2)));
        return "APS-SEC-GOR-{$year}-{$random}";
    }

    public function assignRoster(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid township/colony'];
        }

        $guardName = trim($data['guard_name'] ?? '');
        if ($guardName === '') {
            return ['success' => false, 'error' => 'Security Guard name is required'];
        }

        $rosterDate = !empty($data['roster_date']) ? trim($data['roster_date']) : date('Y-m-d');
        $shift = $data['shift'] ?? 'morning_06_14';
        $gateName = trim($data['gate_name'] ?? 'Main Entrance Gate #1');
        $aadhaarLast4 = substr(preg_replace('/\D/', '', $data['guard_aadhaar_last4'] ?? '0000'), -4);
        $phone = trim($data['guard_phone'] ?? '');
        $agency = trim($data['agency_name'] ?? 'Purvanchal Security Services');
        $supervisor = trim($data['supervisor_name'] ?? 'Dharmendra Singh');
        $supervisorContact = trim($data['supervisor_contact'] ?? '');
        $status = $data['status'] ?? 'checked_in';
        $checkinTime = $data['checkin_time'] ?? date('H:i:s');
        $barrierStatus = $data['boom_barrier_status'] ?? 'functional';
        $visitorsLogged = (int)($data['visitor_vehicles_logged'] ?? 0);

        $stmt = $this->db->prepare("
            INSERT INTO township_security_rosters (
                colony_id, roster_date, shift, gate_name, guard_name, guard_aadhaar_last4,
                guard_phone, agency_name, supervisor_name, supervisor_contact, status,
                checkin_time, boom_barrier_status, visitor_vehicles_logged
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $colonyId, $rosterDate, $shift, $gateName, $guardName, $aadhaarLast4,
            $phone, $agency, $supervisor, $supervisorContact, $status,
            $checkinTime, $barrierStatus, $visitorsLogged
        ]);

        return [
            'success' => true,
            'roster_id' => (int)$this->db->lastInsertId()
        ];
    }

    public function logIncident(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            return ['success' => false, 'error' => 'Colony ID is required'];
        }

        $description = trim($data['description'] ?? '');
        if ($description === '') {
            return ['success' => false, 'error' => 'Incident description is required'];
        }

        $token = $this->generateIncidentToken();
        $rosterId = !empty($data['roster_id']) ? (int)$data['roster_id'] : null;
        $incidentDate = !empty($data['incident_date']) ? trim($data['incident_date']) : date('Y-m-d');
        $incidentTime = !empty($data['incident_time']) ? trim($data['incident_time']) : date('H:i:s');
        $category = $data['category'] ?? 'suspicious_activity';
        $severity = $data['severity'] ?? 'medium';
        $location = trim($data['location_detail'] ?? 'Main Gate Perimeter');
        $actionTaken = trim($data['action_taken'] ?? '');
        $policeInformed = !empty($data['police_informed']) ? 1 : 0;

        $stmt = $this->db->prepare("
            INSERT INTO township_security_incidents (
                incident_token, colony_id, roster_id, incident_date, incident_time,
                category, severity, location_detail, description, action_taken,
                police_informed, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'logged')
        ");

        $stmt->execute([
            $token, $colonyId, $rosterId, $incidentDate, $incidentTime,
            $category, $severity, $location, $description, $actionTaken,
            $policeInformed
        ]);

        return [
            'success' => true,
            'incident_id' => (int)$this->db->lastInsertId(),
            'incident_token' => $token
        ];
    }

    public function resolveIncident(int $id, string $notes, string $adminName): bool
    {
        $stmt = $this->db->prepare("
            UPDATE township_security_incidents
            SET status = 'resolved', resolution_notes = ?, resolved_by = ?
            WHERE id = ?
        ");
        return $stmt->execute([$notes, $adminName, $id]);
    }

    public function getTodaySecurityMetrics(): array
    {
        $today = date('Y-m-d');

        $stmtRoster = $this->db->prepare("
            SELECT 
                COUNT(*) as active_guards,
                COALESCE(SUM(visitor_vehicles_logged), 0) as total_visitors
            FROM township_security_rosters
            WHERE roster_date = ? AND status = 'checked_in'
        ");
        $stmtRoster->execute([$today]);
        $rosterStats = $stmtRoster->fetch(PDO::FETCH_ASSOC) ?: ['active_guards' => 0, 'total_visitors' => 0];

        $stmtInc = $this->db->prepare("
            SELECT 
                COUNT(*) as total_incidents,
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical_count,
                SUM(CASE WHEN status != 'resolved' THEN 1 ELSE 0 END) as open_incidents
            FROM township_security_incidents
            WHERE incident_date = ?
        ");
        $stmtInc->execute([$today]);
        $incStats = $stmtInc->fetch(PDO::FETCH_ASSOC) ?: ['total_incidents' => 0, 'critical_count' => 0, 'open_incidents' => 0];

        return array_merge($rosterStats, $incStats);
    }

    public function getRosters(?int $colonyId = null, int $limit = 30): array
    {
        $sql = "
            SELECT r.*, c.name as colony_name 
            FROM township_security_rosters r
            LEFT JOIN colonies c ON r.colony_id = c.id
        ";
        $params = [];
        if ($colonyId && $colonyId > 0) {
            $sql .= " WHERE r.colony_id = ? ";
            $params[] = $colonyId;
        }
        $sql .= " ORDER BY r.roster_date DESC, r.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIncidents(?int $colonyId = null, int $limit = 30): array
    {
        $sql = "
            SELECT i.*, c.name as colony_name 
            FROM township_security_incidents i
            LEFT JOIN colonies c ON i.colony_id = c.id
        ";
        $params = [];
        if ($colonyId && $colonyId > 0) {
            $sql .= " WHERE i.colony_id = ? ";
            $params[] = $colonyId;
        }
        $sql .= " ORDER BY i.incident_date DESC, i.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
