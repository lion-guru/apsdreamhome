<?php
namespace App\Services;

use PDO;

/**
 * PropertyMarketplaceService - maintenance tracking + maintenance scheduler
 */
class PropertyMarketplaceService
{
    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function scheduleMaintenance(int $propertyId, string $type, string $description, string $scheduledDate, float $estimatedCost, int $assignedTo = 0): array
    {
        $st = $this->db->prepare("INSERT INTO property_maintenance (property_id, maintenance_type, description, scheduled_date, estimated_cost, assigned_to, status, created_at) VALUES (:p, :t, :d, :dt, :c, :a, 'scheduled', NOW())");
        $st->execute([':p' => $propertyId, ':t' => $type, ':d' => $description, ':dt' => $scheduledDate, ':c' => $estimatedCost, ':a' => $assignedTo]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function completeMaintenance(int $id, float $actualCost, string $notes = ''): array
    {
        $st = $this->db->prepare("UPDATE property_maintenance SET status = 'completed', actual_cost = :c, completion_notes = :n, completed_at = NOW() WHERE id = :id");
        $st->execute([':c' => $actualCost, ':n' => $notes, ':id' => $id]);
        return ['ok' => true];
    }

    public function listMaintenance(int $propertyId = 0, string $status = ''): array
    {
        $sql = "SELECT m.*, p.title as property_title, u.name as assignee_name FROM property_maintenance m LEFT JOIN plots p ON m.property_id = p.id LEFT JOIN users u ON m.assigned_to = u.id WHERE 1=1";
        $params = [];
        if ($propertyId) { $sql .= " AND m.property_id = :p"; $params[':p'] = $propertyId; }
        if ($status) { $sql .= " AND m.status = :s"; $params[':s'] = $status; }
        $sql .= " ORDER BY m.scheduled_date ASC LIMIT 100";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMarketAnalytics(int $days = 90): array
    {
        $st = $this->db->prepare("SELECT * FROM property_market_data WHERE created_at > DATE_SUB(NOW(), INTERVAL :d DAY) ORDER BY created_at DESC");
        $st->execute([':d' => $days]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
