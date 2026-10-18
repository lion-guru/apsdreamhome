<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;
use InvalidArgumentException;

class OrgChartService
{
    use ServiceTenantTrait;

    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    // ==================== ORG CHART NODES ====================

    public function getNodes(array $filters = []): array
    {
        $where = ['ocn.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['department'])) {
            $where[] = 'ocn.department = ?';
            $params[] = $filters['department'];
        }
        if (!empty($filters['level'])) {
            $where[] = 'ocn.level = ?';
            $params[] = (int)$filters['level'];
        }
        if (!empty($filters['is_active'])) {
            $where[] = 'ocn.is_active = ?';
            $params[] = (int)$filters['is_active'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(ocn.position_title LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT ocn.*, u.name as employee_name, u.email as employee_email, u.phone as employee_phone,
                    p.position_title as parent_position
                FROM org_chart_nodes ocn
                JOIN employees e ON ocn.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                LEFT JOIN org_chart_nodes p ON ocn.parent_id = p.id
                $whereSql
                ORDER BY ocn.level ASC, ocn.sort_order ASC, u.name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNode(int $nodeId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT ocn.*, u.name as employee_name, u.email as employee_email, u.phone as employee_phone,
                   p.position_title as parent_position
            FROM org_chart_nodes ocn
            JOIN employees e ON ocn.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            LEFT JOIN org_chart_nodes p ON ocn.parent_id = p.id
            WHERE ocn.id = ? AND ocn.tenant_id = ?
        ");
        $stmt->execute([$nodeId, $this->tenantId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getTree(): array
    {
        $nodes = $this->getNodes(['is_active' => 1]);
        return $this->buildTree($nodes);
    }

    private function buildTree(array $nodes, int $parentId = 0): array
    {
        $tree = [];
        $childrenMap = [];

        foreach ($nodes as $node) {
            $pid = $node['parent_id'] ?? 0;
            if (!isset($childrenMap[$pid])) {
                $childrenMap[$pid] = [];
            }
            $childrenMap[$pid][] = $node;
        }

        $rootNodes = $childrenMap[0] ?? [];

        foreach ($rootNodes as $root) {
            $tree[] = $this->buildNodeTree($root, $childrenMap);
        }

        return $tree;
    }

    private function buildNodeTree(array $node, array $childrenMap): array
    {
        $nodeId = $node['id'];
        $node['children'] = [];

        if (isset($childrenMap[$nodeId])) {
            foreach ($childrenMap[$nodeId] as $child) {
                $node['children'][] = $this->buildNodeTree($child, $childrenMap);
            }
        }

        return $node;
    }

    public function getNodeWithChildren(int $nodeId): ?array
    {
        $node = $this->getNode($nodeId);
        if (!$node) {
            return null;
        }

        $node['children'] = $this->getDirectChildren($nodeId);
        return $node;
    }

    public function getDirectChildren(int $parentId): array
    {
        $stmt = $this->conn->prepare("
            SELECT ocn.*, u.name as employee_name, u.email as employee_email, u.phone as employee_phone
            FROM org_chart_nodes ocn
            JOIN employees e ON ocn.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE ocn.parent_id = ? AND ocn.tenant_id = ? AND ocn.is_active = 1
            ORDER BY ocn.sort_order ASC, u.name ASC
        ");
        $stmt->execute([$parentId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAncestors(int $nodeId): array
    {
        $ancestors = [];
        $current = $this->getNode($nodeId);

        while ($current && $current['parent_id']) {
            $parent = $this->getNode($current['parent_id']);
            if ($parent) {
                $ancestors[] = $parent;
                $current = $parent;
            } else {
                break;
            }
        }

        return array_reverse($ancestors);
    }

    public function getDepartmentChart(string $department): array
    {
        $nodes = $this->getNodes(['department' => $department, 'is_active' => 1]);
        return $this->buildTree($nodes);
    }

    public function createNode(array $data): array
    {
        $required = ['employee_id', 'position_title'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("$field is required");
            }
        }

        // Check if employee already has a node
        $stmt = $this->conn->prepare('SELECT id FROM org_chart_nodes WHERE employee_id = ? AND tenant_id = ?');
        $stmt->execute([$data['employee_id'], $this->tenantId()]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Employee already has an org chart position'];
        }

        // Calculate level if parent_id provided
        $level = 0;
        if (!empty($data['parent_id'])) {
            $parent = $this->getNode((int)$data['parent_id']);
            $level = ($parent['level'] ?? 0) + 1;
        }

        $columns = array_merge(
            ['employee_id', 'parent_id', 'position_title', 'department', 'level', 'sort_order', 'is_vacant', 'vacant_since', 'custom_data', 'is_active', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['employee_id'],
            $data['parent_id'] ?? null,
            $data['position_title'],
            $data['department'] ?? null,
            $level,
            $data['sort_order'] ?? 0,
            $data['is_vacant'] ?? false,
            $data['vacant_since'] ?? null,
            !empty($data['custom_data']) ? json_encode($data['custom_data']) : null,
            $data['is_active'] ?? true,
            date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO org_chart_nodes (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        $nodeId = (int)$this->conn->lastInsertId();

        // Update descendants' levels
        $this->updateDescendantLevels($nodeId, $level);

        return ['success' => true, 'id' => $nodeId, 'message' => 'Org chart node created'];
    }

    public function updateNode(int $nodeId, array $data): array
    {
        $allowed = ['parent_id', 'position_title', 'department', 'sort_order', 'is_vacant', 'vacant_since', 'custom_data', 'is_active'];
        $fields = [];
        $params = [];

        $oldNode = $this->getNode($nodeId);
        if (!$oldNode) {
            return ['success' => false, 'message' => 'Node not found'];
        }

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }

        // Handle parent_id change - recalculate levels
        if (isset($data['parent_id']) && $data['parent_id'] != $oldNode['parent_id']) {
            $newLevel = 0;
            if ($data['parent_id']) {
                $parent = $this->getNode((int)$data['parent_id']);
                $newLevel = ($parent['level'] ?? 0) + 1;
            }
            $fields[] = 'level = ?';
            $params[] = $newLevel;

            // Update this node and all descendants
            $this->updateNodeAndDescendants($nodeId, $newLevel, $oldNode['level']);
        }

        $params[] = $nodeId;
        $params[] = $this->tenantId();

        $sql = "UPDATE org_chart_nodes SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Node updated' : 'No changes'];
    }

    private function updateNodeAndDescendants(int $nodeId, int $newLevel, int $oldLevel): void
    {
        $levelDiff = $newLevel - $oldLevel;

        // Update this node
        $this->conn->execute("UPDATE org_chart_nodes SET level = level + ? WHERE id = ? AND tenant_id = ?", [$levelDiff, $nodeId, $this->tenantId()]);

        // Update descendants recursively
        $this->updateDescendantLevels($nodeId, $newLevel);
    }

    private function updateDescendantLevels(int $parentId, int $parentLevel): void
    {
        $children = $this->getDirectChildren($parentId);
        foreach ($children as $child) {
            $newLevel = $parentLevel + 1;
            $this->conn->execute("UPDATE org_chart_nodes SET level = ? WHERE id = ? AND tenant_id = ?", [$newLevel, $child['id'], $this->tenantId()]);
            $this->updateDescendantLevels($child['id'], $newLevel);
        }
    }

    public function deleteNode(int $nodeId): array
    {
        // Check if node has children
        $children = $this->getDirectChildren($nodeId);
        if (!empty($children)) {
            return ['success' => false, 'message' => 'Cannot delete node with children. Reassign or delete children first.'];
        }

        $stmt = $this->conn->prepare('DELETE FROM org_chart_nodes WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$nodeId, $this->tenantId()]);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Node deleted' : 'Node not found'];
    }

    public function bulkReorder(array $nodes): array
    {
        $this->conn->beginTransaction();
        try {
            foreach ($nodes as $node) {
                $fields = ['sort_order = ?'];
                $params = [$node['sort_order'] ?? 0];

                if (!empty($node['parent_id'])) {
                    $fields[] = 'parent_id = ?';
                    $params[] = $node['parent_id'];

                    // Recalculate level
                    $parent = $this->getNode((int)$node['parent_id']);
                    $newLevel = ($parent['level'] ?? 0) + 1;
                    $fields[] = 'level = ?';
                    $params[] = $newLevel;
                } else {
                    $fields[] = 'parent_id = NULL';
                    $fields[] = 'level = 0';
                }

                $params[] = $node['id'];
                $params[] = $this->tenantId();

                $sql = "UPDATE org_chart_nodes SET " . implode(', ', $fields) . " WHERE id = ? AND tenant_id = ?";
                $this->conn->execute($sql, $params);
            }
            $this->conn->commit();
            return ['success' => true, 'message' => 'Org chart reordered'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getDepartments(): array
    {
        $stmt = $this->conn->prepare('SELECT DISTINCT department FROM org_chart_nodes WHERE tenant_id = ? AND department IS NOT NULL AND department != "" AND is_active = 1 ORDER BY department');
        $stmt->execute([$this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getVacantPositions(): array
    {
        $stmt = $this->conn->prepare("
            SELECT ocn.* FROM org_chart_nodes ocn
            WHERE ocn.tenant_id = ? AND ocn.is_vacant = 1 AND ocn.is_active = 1
            ORDER BY ocn.level ASC, ocn.position_title ASC
        ");
        $stmt->execute([$this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $stats = [];

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM org_chart_nodes WHERE tenant_id = ? AND is_active = 1');
        $stmt->execute([$this->tenantId()]);
        $stats['total_positions'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM org_chart_nodes WHERE tenant_id = ? AND is_vacant = 1 AND is_active = 1');
        $stmt->execute([$this->tenantId()]);
        $stats['vacant_positions'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(DISTINCT department) as c FROM org_chart_nodes WHERE tenant_id = ? AND is_active = 1 AND department IS NOT NULL');
        $stmt->execute([$this->tenantId()]);
        $stats['departments'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT MAX(level) as max_level FROM org_chart_nodes WHERE tenant_id = ? AND is_active = 1');
        $stmt->execute([$this->tenantId()]);
        $stats['max_level'] = $stmt->fetchColumn() ?? 0;

        return $stats;
    }

    public function syncFromEmployees(): array
    {
        // Sync employees table to org_chart_nodes
        $employees = $this->conn->fetchAll("
            SELECT e.id as employee_id, u.name, e.designation, e.department, e.manager_id
            FROM employees e
            JOIN users u ON e.user_id = u.id
            WHERE e.status = 'active' AND u.status = 'active'
        ");

        $created = 0;
        $updated = 0;

        foreach ($employees as $emp) {
            $existing = $this->conn->fetch('SELECT * FROM org_chart_nodes WHERE employee_id = ? AND tenant_id = ?', [$emp['employee_id'], $this->tenantId()]);

            $parentId = null;
            if (!empty($emp['manager_id'])) {
                $parent = $this->conn->fetch('SELECT id FROM org_chart_nodes WHERE employee_id = ? AND tenant_id = ?', [$emp['manager_id'], $this->tenantId()]);
                if ($parent) {
                    $parentId = $parent['id'];
                }
            }

            $level = 0;
            if ($parentId) {
                $parent = $this->getNode($parentId);
                $level = ($parent['level'] ?? 0) + 1;
            }

            if ($existing) {
                $this->conn->execute("
                    UPDATE org_chart_nodes 
                    SET position_title = ?, department = ?, parent_id = ?, level = ?, updated_at = NOW()
                    WHERE id = ? AND tenant_id = ?
                ", [$emp['designation'], $emp['department'], $parentId, $level, $existing['id'], $this->tenantId()]);
                $updated++;
            } else {
                $columns = array_merge(
                    ['employee_id', 'parent_id', 'position_title', 'department', 'level', 'sort_order', 'is_active', 'created_at'],
                    array_keys($this->tenantInsertData())
                );

                $values = array_merge([
                    $emp['employee_id'],
                    $parentId,
                    $emp['designation'],
                    $emp['department'],
                    $level,
                    0,
                    true,
                    date('Y-m-d H:i:s')
                ], array_values($this->tenantInsertData()));

                $placeholders = str_repeat('?,', count($columns) - 1) . '?';
                $sql = "INSERT INTO org_chart_nodes (" . implode(', ', $columns) . ") VALUES ($placeholders)";
                $this->conn->execute($sql, $values);
                $created++;
            }
        }

        return ['success' => true, 'created' => $created, 'updated' => $updated, 'message' => "Synced: $created created, $updated updated"];
    }

    public function exportChart(): array
    {
        $tree = $this->getTree();
        return [
            'exported_at' => date('Y-m-d H:i:s'),
            'tenant_id' => $this->tenantId(),
            'chart' => $tree
        ];
    }

    public function importChart(array $data): array
    {
        $this->conn->beginTransaction();
        try {
            // Clear existing
            $this->conn->execute("DELETE FROM org_chart_nodes WHERE tenant_id = ?", [$this->tenantId()]);

            $imported = 0;
            foreach ($data['chart'] as $node) {
                $this->importNode($node, null);
                $imported++;
            }
            $this->conn->commit();
            return ['success' => true, 'imported' => $imported, 'message' => "Imported $imported nodes"];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    private function importNode(array $node, ?int $parentId): void
    {
        $columns = array_merge(
            ['employee_id', 'parent_id', 'position_title', 'department', 'level', 'sort_order', 'is_vacant', 'vacant_since', 'custom_data', 'is_active', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $node['employee_id'],
            $parentId,
            $node['position_title'],
            $node['department'] ?? null,
            $node['level'] ?? 0,
            $node['sort_order'] ?? 0,
            $node['is_vacant'] ?? false,
            $node['vacant_since'] ?? null,
            isset($node['custom_data']) ? json_encode($node['custom_data']) : null,
            $node['is_active'] ?? true,
            date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO org_chart_nodes (" . implode(', ', $columns) . ") VALUES ($placeholders)";
        $this->conn->execute($sql, $values);

        $newId = (int)$this->conn->lastInsertId();

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->importNode($child, $newId);
            }
        }
    }
}