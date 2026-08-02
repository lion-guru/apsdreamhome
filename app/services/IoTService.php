<?php

namespace App\Services;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

class IoTService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ==================== DEVICE CATALOG ====================

    public function getCatalog(array $filters = []): array
    {
        $sql = "SELECT * FROM iot_device_catalog WHERE 1=1";
        $params = [];
        if (!empty($filters['category'])) { $sql .= " AND category = ?"; $params[] = $filters['category']; }
        if (isset($filters['is_active'])) { $sql .= " AND is_active = ?"; $params[] = $filters['is_active']; }
        $sql .= " ORDER BY category, name";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getCatalogItem(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM iot_device_catalog WHERE id = ?", [$id]);
    }

    public function saveCatalogItem(array $data): int
    {
        if (!empty($data['id'])) {
            $this->db->execute(
                "UPDATE iot_device_catalog SET name=?, category=?, manufacturer=?, model=?, protocol=?, description=?, specs=?, is_active=?, updated_at=NOW() WHERE id=?",
                [$data['name'], $data['category'], $data['manufacturer'], $data['model'], $data['protocol'], $data['description'], isset($data['specs']) ? json_encode($data['specs']) : null, $data['is_active'] ?? 1, $data['id']]
            );
            return (int)$data['id'];
        }
        $this->db->execute(
            "INSERT INTO iot_device_catalog (name, category, manufacturer, model, protocol, description, specs, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$data['name'], $data['category'], $data['manufacturer'], $data['model'], $data['protocol'], $data['description'], isset($data['specs']) ? json_encode($data['specs']) : null, $data['is_active'] ?? 1]
        );
        return (int)$this->db->lastInsertId();
    }

    public function deleteCatalogItem(int $id): void
    {
        $this->db->execute("DELETE FROM iot_device_catalog WHERE id = ?", [$id]);
    }

    // ==================== DEVICES ====================

    public function getDevices(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $sql = "SELECT d.*, c.name as catalog_name, COALESCE(p.name, '') as property_name
                FROM iot_devices d
                LEFT JOIN iot_device_catalog c ON c.id = d.catalog_id
                LEFT JOIN user_properties p ON p.id = d.property_id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) { $sql .= " AND d.status = ?"; $params[] = $filters['status']; }
        if (!empty($filters['category'])) { $sql .= " AND d.category = ?"; $params[] = $filters['category']; }
        if (!empty($filters['colony_id'])) { $sql .= " AND d.colony_id = ?"; $params[] = $filters['colony_id']; }
        $sql .= " ORDER BY d.created_at DESC LIMIT $limit OFFSET " . (($page - 1) * $limit);
        $data = $this->db->fetchAll($sql, $params) ?: [];
        $total = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM iot_devices")['c'] ?? 0);
        return ['data' => $data, 'total' => $total, 'page' => $page, 'limit' => $limit];
    }

    public function getDevice(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT d.*, c.name as catalog_name FROM iot_devices d LEFT JOIN iot_device_catalog c ON c.id = d.catalog_id WHERE d.id = ?",
            [$id]
        );
    }

    public function saveDevice(array $data): int
    {
        if (!empty($data['id'])) {
            $this->db->execute(
                "UPDATE iot_devices SET catalog_id=?, property_id=?, colony_id=?, name=?, device_uid=?, category=?, status=?, firmware_version=?, location=?, meta=?, updated_at=NOW() WHERE id=?",
                [$data['catalog_id'], $data['property_id'], $data['colony_id'], $data['name'], $data['device_uid'], $data['category'], $data['status'], $data['firmware_version'], $data['location'], isset($data['meta']) ? json_encode($data['meta']) : null, $data['id']]
            );
            return (int)$data['id'];
        }
        $this->db->execute(
            "INSERT INTO iot_devices (catalog_id, property_id, colony_id, name, device_uid, category, status, firmware_version, location, meta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$data['catalog_id'], $data['property_id'], $data['colony_id'], $data['name'], $data['device_uid'], $data['category'], $data['status'], $data['firmware_version'], $data['location'], isset($data['meta']) ? json_encode($data['meta']) : null]
        );
        return (int)$this->db->lastInsertId();
    }

    public function deleteDevice(int $id): void
    {
        $this->db->execute("DELETE FROM iot_devices WHERE id = ?", [$id]);
    }

    public function recordReading(int $deviceId, string $metric, float $value, ?string $unit = null, ?string $recordedAt = null): int
    {
        $this->db->execute(
            "INSERT INTO iot_readings (device_id, metric, value, unit, recorded_at) VALUES (?, ?, ?, ?, ?)",
            [$deviceId, $metric, $value, $unit, $recordedAt ?? date('Y-m-d H:i:s')]
        );
        $this->db->execute("UPDATE iot_devices SET last_seen_at = ? WHERE id = ?", [date('Y-m-d H:i:s'), $deviceId]);
        return (int)$this->db->lastInsertId();
    }

    public function getLatestReadings(int $deviceId): array
    {
        return $this->db->fetchAll(
            "SELECT r.* FROM iot_readings r
             INNER JOIN (SELECT metric, MAX(recorded_at) as mx FROM iot_readings WHERE device_id = ? GROUP BY metric) m
             ON m.metric = r.metric AND m.mx = r.recorded_at
             WHERE r.device_id = ? ORDER BY r.metric",
            [$deviceId, $deviceId]
        ) ?: [];
    }

    public function getReadingHistory(int $deviceId, string $metric, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM iot_readings WHERE device_id = ? AND metric = ? ORDER BY recorded_at DESC LIMIT ?",
            [$deviceId, $metric, $limit]
        ) ?: [];
    }

    // ==================== AUTOMATIONS ====================

    public function getAutomations(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $sql = "SELECT a.*, d.name as device_name FROM iot_automations a LEFT JOIN iot_devices d ON d.id = a.device_id WHERE 1=1";
        $params = [];
        if (isset($filters['is_active'])) { $sql .= " AND a.is_active = ?"; $params[] = $filters['is_active']; }
        $sql .= " ORDER BY a.created_at DESC LIMIT $limit OFFSET " . (($page - 1) * $limit);
        $data = $this->db->fetchAll($sql, $params) ?: [];
        $total = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM iot_automations")['c'] ?? 0);
        return ['data' => $data, 'total' => $total, 'page' => $page, 'limit' => $limit];
    }

    public function getAutomation(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM iot_automations WHERE id = ?", [$id]);
    }

    public function saveAutomation(array $data): int
    {
        if (!empty($data['id'])) {
            $this->db->execute(
                "UPDATE iot_automations SET name=?, property_id=?, device_id=?, trigger_type=?, trigger_config=?, action_type=?, action_config=?, is_active=?, updated_at=NOW() WHERE id=?",
                [$data['name'], $data['property_id'], $data['device_id'], $data['trigger_type'], json_encode($data['trigger_config']), $data['action_type'], json_encode($data['action_config']), $data['is_active'] ?? 1, $data['id']]
            );
            return (int)$data['id'];
        }
        $this->db->execute(
            "INSERT INTO iot_automations (name, property_id, device_id, trigger_type, trigger_config, action_type, action_config, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$data['name'], $data['property_id'], $data['device_id'], $data['trigger_type'], json_encode($data['trigger_config']), $data['action_type'], json_encode($data['action_config']), $data['is_active'] ?? 1]
        );
        return (int)$this->db->lastInsertId();
    }

    public function deleteAutomation(int $id): void
    {
        $this->db->execute("DELETE FROM iot_automations WHERE id = ?", [$id]);
    }

    public function markAutomationTriggered(int $id): void
    {
        $this->db->execute("UPDATE iot_automations SET last_triggered_at = NOW() WHERE id = ?", [$id]);
    }

    // ==================== DASHBOARD STATS ====================

    public function getStats(): array
    {
        return [
            'total_devices' => (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM iot_devices")['c'] ?? 0),
            'online_devices' => (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM iot_devices WHERE status = 'online'")['c'] ?? 0),
            'offline_devices' => (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM iot_devices WHERE status = 'offline'")['c'] ?? 0),
            'fault_devices' => (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM iot_devices WHERE status = 'fault'")['c'] ?? 0),
            'total_automations' => (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM iot_automations WHERE is_active = 1")['c'] ?? 0),
            'total_readings' => (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM iot_readings")['c'] ?? 0),
            'catalog_count' => (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM iot_device_catalog")['c'] ?? 0),
        ];
    }
}
