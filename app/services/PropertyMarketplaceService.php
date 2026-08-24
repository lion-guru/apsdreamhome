<?php
namespace App\Services;

use PDO;

/**
 * PropertyMarketplaceService - maintenance tracking + maintenance scheduler
 */
class PropertyMarketplaceService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function scheduleMaintenance(int $propertyId, string $type, string $description, string $scheduledDate, float $estimatedCost, int $assignedTo = 0): array
    {
        $insertData = $this->tenantInsertData();
        $extraCols = $insertData ? ', ' . implode(', ', array_keys($insertData)) : '';
        $extraVals = $insertData ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '';
        $st = $this->db->prepare("INSERT INTO property_maintenance (property_id, issue_type, description, assigned_to, status, created_at{$extraCols}) VALUES (:p, :t, :d, :a, 'scheduled', NOW(){$extraVals})");
        $st->execute(array_merge([':p' => $propertyId, ':t' => $type, ':d' => $description, ':a' => $assignedTo], array_values($insertData)));
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function completeMaintenance(int $id, float $actualCost, string $notes = ''): array
    {
        $st = $this->db->prepare("UPDATE property_maintenance SET status = 'completed', completed_at = NOW() WHERE id = :id" . $this->tenantSql());
        $st->execute([':id' => $id]);
        return ['ok' => true];
    }

    public function listMaintenance(int $propertyId = 0, string $status = ''): array
    {
        try {
            $sql = "SELECT m.*, p.plot_number as property_title, u.name as assignee_name FROM property_maintenance m LEFT JOIN plots p ON m.property_id = p.id LEFT JOIN users u ON m.assigned_to = u.id WHERE 1=1" . $this->tenantSql();
            $params = [];
            if ($propertyId) { $sql .= " AND m.property_id = :p"; $params[':p'] = $propertyId; }
            if ($status) { $sql .= " AND m.status = :s"; $params[':s'] = $status; }
            $sql .= " ORDER BY m.created_at ASC LIMIT 100";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getMarketAnalytics(int $days = 90): array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM property_market_data WHERE data_date > DATE_SUB(CURDATE(), INTERVAL :d DAY)" . $this->tenantSql() . " ORDER BY data_date DESC");
            $st->execute([':d' => $days]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
