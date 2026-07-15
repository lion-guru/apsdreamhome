<?php
/**
 * DesignationService
 * CRUD for designation definitions (role templates) linked to departments.
 * Each designation defines: name, level (1-5), salary band, sub_role, dashboard route.
 */
namespace App\Services;

class DesignationService
{
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
                JOIN departments d ON des.department_id = d.id";
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
            $sql .= " WHERE " . implode(' AND ', $conditions);
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
             WHERE des.id = ?",
            [$id]
        );
    }

    // ─── CREATE ──────────────────────────────────
    public function create(array $data): int
    {
        $this->checkUnique($data['name'], $data['department_id']);
        $stmt = $this->pdo->prepare("
            INSERT INTO designations (name, department_id, level, min_salary, max_salary, sub_role, dashboard_view, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['department_id'],
            $data['level'] ?? 1,
            $data['min_salary'] ?? 0,
            $data['max_salary'] ?? 0,
            $data['sub_role'],
            $data['dashboard_view'] ?? null,
            $data['status'] ?? 'active',
        ]);
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
        $stmt = $this->pdo->prepare("UPDATE designations SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    // ─── DELETE ──────────────────────────────────
    public function delete(int $id): bool
    {
        return $this->pdo->prepare("DELETE FROM designations WHERE id = ?")->execute([$id]);
    }

    // ─── UNIQUE CHECK ────────────────────────────
    private function checkUnique(string $name, int $departmentId, ?int $excludeId = null): void
    {
        $sql = "SELECT COUNT(*) FROM designations WHERE name = ? AND department_id = ?";
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
        return [
            'total'      => $this->fetchColumn("SELECT COUNT(*) FROM designations") ?? 0,
            'active'     => $this->fetchColumn("SELECT COUNT(*) FROM designations WHERE status='active'") ?? 0,
            'by_level'   => $this->fetchAll("SELECT level, COUNT(*) as cnt FROM designations GROUP BY level ORDER BY level"),
            'by_dept'    => $this->fetchAll(
                "SELECT d.code, d.name, COUNT(des.id) as cnt
                 FROM departments d LEFT JOIN designations des ON des.department_id = d.id
                 GROUP BY d.id ORDER BY d.code"
            ),
        ];
    }

    // ─── SUB-ROLE LIST (for dropdowns) ───────────
    public function getSubRoles(): array
    {
        return $this->fetchAll(
            "SELECT DISTINCT sub_role FROM designations WHERE sub_role IS NOT NULL ORDER BY sub_role"
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
