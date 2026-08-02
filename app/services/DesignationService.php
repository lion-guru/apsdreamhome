<?php
/**
 * DesignationService
 * CRUD for designation definitions (role templates) linked to departments.
 * Each designation defines: name, level (1-5), salary band, sub_role, dashboard route.
 */
namespace App\Services;

use App\Traits\ServiceTenantTrait;

class DesignationService
{
    use ServiceTenantTrait;

    private ?\PDO $pdo = null;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    // ─── LIST ────────────────────────────────────
    public function getAll(?int $departmentId = null, ?string $status = null): array
    {
        $sql = "SELECT des.*, d.name AS department_name, d.code AS department_code
                FROM designations des
                JOIN departments d ON des.department_id = d.id WHERE 1=1" . $this->tenantSql();
        $conditions = [];
        $params = [];
        if ($departmentId) {
            $conditions[] = "des.department_id = ?";
            $params[] = $departmentId;
        }
        if ($status) {
            $conditions[] = "des.status = ?";
            $params[] = $status;
        }
        if ($conditions) {
            $sql .= " AND " . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY d.code ASC, des.level DESC, des.name ASC";
        return $this->fetchAll($sql, $params);
    }

    public function getByDepartment(int $departmentId): array
    {
        return $this->getAll($departmentId);
    }

    // ─── GET BY ID ──────────────────────────────
    public function getById(int $id): ?array
    {
        return $this->fetch(
            "SELECT des.*, d.name AS department_name, d.code AS department_code
             FROM designations des
             JOIN departments d ON des.department_id = d.id
             WHERE des.id = ?" . $this->tenantSql(),
            [$id]
        );
    }

    // ─── CREATE ──────────────────────────────────
    public function create(array $data): int
    {
        $this->checkUnique($data['name'], $data['department_id']);
        $tenantData = $this->tenantInsertData();
        $columns = ['name', 'department_id', 'level', 'min_salary', 'max_salary', 'sub_role', 'dashboard_view', 'status'];
        $placeholders = str_repeat('?, ', 8);
        $placeholders = rtrim($placeholders, ', ');
        $values = [
            $data['name'],
            $data['department_id'],
            $data['level'] ?? 1,
            $data['min_salary'] ?? 0,
            $data['max_salary'] ?? 0,
            $data['sub_role'],
            $data['dashboard_view'] ?? null,
            $data['status'] ?? 'active',
        ];
        if (!empty($tenantData)) {
            $columns = array_merge($columns, array_keys($tenantData));
            $placeholders .= ', ' . str_repeat('?, ', count($tenantData));
            $placeholders = rtrim($placeholders, ', ');
            $values = array_merge($values, array_values($tenantData));
        }
        $stmt = $this->pdo->prepare("
            INSERT INTO designations (" . implode(', ', $columns) . ")
            VALUES ($placeholders)
        ");
        $stmt->execute($values);
        return (int) $this->pdo->lastInsertId();
    }

    // ─── UPDATE ──────────────────────────────────
    public function update(int $id, array $data): bool
    {
        if (isset($data['name']) && isset($data['department_id'])) {
            $existing = $this->getById($id);
            if ($existing && ($existing['name'] !== $data['name'] || $existing['department_id'] != $data['department_id'])) {
                $this->checkUnique($data['name'], $data['department_id'], $id);
            }
        }
        $fields = [];
        $params = [];
        $allowed = ['name', 'department_id', 'level', 'min_salary', 'max_salary', 'sub_role', 'dashboard_view', 'status'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "`$f` = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $stmt = $this->pdo->prepare("UPDATE designations SET " . implode(', ', $fields) . " WHERE id = ?" . $this->tenantSql());
        return $stmt->execute($params);
    }

    // ─── DELETE ──────────────────────────────────
    public function delete(int $id): bool
    {
        return $this->pdo->prepare("DELETE FROM designations WHERE id = ?" . $this->tenantSql())->execute([$id]);
    }

    // ─── UNIQUE CHECK ────────────────────────────
    private function checkUnique(string $name, int $departmentId, ?int $excludeId = null): void
    {
        $sql = "SELECT COUNT(*) FROM designations WHERE name = ? AND department_id = ?" . $this->tenantSql();
        $params = [$name, $departmentId];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        if ($this->fetchColumn($sql, $params) > 0) {
            throw new \RuntimeException("Designation '$name' already exists in this department.");
        }
    }

    // ─── STATS ───────────────────────────────────
    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tfilter = $tid > 1 ? " WHERE tenant_id = $tid" : "";
        $tfilterNamed = $this->tenantSql();
        return [
            'total'      => $this->fetchColumn("SELECT COUNT(*) FROM designations$tfilter") ?? 0,
            'active'     => $this->fetchColumn("SELECT COUNT(*) FROM designations WHERE status='active'" . $tfilterNamed) ?? 0,
            'by_level'   => $this->fetchAll("SELECT level, COUNT(*) as cnt FROM designations" . $tfilter . " GROUP BY level ORDER BY level"),
            'by_dept'    => $this->fetchAll(
                "SELECT d.code, d.name, COUNT(des.id) as cnt
                 FROM departments d LEFT JOIN designations des ON des.department_id = d.id" . $tfilterNamed . "
                 GROUP BY d.id ORDER BY d.code"
            ),
        ];
    }

    // ─── SUB-ROLE LIST (for dropdowns) ───────────
    public function getSubRoles(): array
    {
        return $this->fetchAll(
            "SELECT DISTINCT sub_role FROM designations WHERE sub_role IS NOT NULL" . $this->tenantSql() . " ORDER BY sub_role"
        );
    }

    // ─── Helpers ─────────────────────────────────
    private function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchColumn(string $sql, array $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
