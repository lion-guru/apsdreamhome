<?php
/**
 * DepartmentService
 * CRUD + tree logic for the real estate company organizational departments.
 * 11 departments across 3 tiers: Executive, Functional, Support.
 */
namespace App\Services;

class DepartmentService
{
    private ?\PDO $pdo = null;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    // ─── LIST ────────────────────────────────────
    public function getAll(?string $status = null): array
    {
        $sql = "SELECT d.*, u.name AS head_name,
                       (SELECT COUNT(*) FROM designations des WHERE des.department_id = d.id) AS designation_count,
                       (SELECT COUNT(*) FROM employees e WHERE e.department COLLATE utf8mb4_unicode_ci = d.code) AS employee_count
                FROM departments d
                LEFT JOIN users u ON d.head_user_id = u.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE d.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY d.code ASC";
        return $this->fetchAll($sql, $params);
    }

    public function getActive(): array
    {
        return $this->getAll('active');
    }

    // ─── GET BY ID ──────────────────────────────
    public function getById(int $id): ?array
    {
        return $this->fetch(
            "SELECT d.*, u.name AS head_name
             FROM departments d
             LEFT JOIN users u ON d.head_user_id = u.id
             WHERE d.id = ?",
            [$id]
        );
    }

    public function getByCode(string $code): ?array
    {
        return $this->fetch(
            "SELECT * FROM departments WHERE code = ?",
            [$code]
        );
    }

    // ─── CREATE ──────────────────────────────────
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO departments (name, code, description, head_user_id, parent_dept_id, dept_budget, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            strtoupper(trim($data['code'])),
            $data['description'] ?? null,
            $data['head_user_id'] ?? null,
            $data['parent_dept_id'] ?? null,
            $data['dept_budget'] ?? 0,
            $data['status'] ?? 'active',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    // ─── UPDATE ──────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        $allowed = ['name', 'code', 'description', 'head_user_id', 'parent_dept_id', 'dept_budget', 'status'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "`$f` = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $stmt = $this->pdo->prepare("UPDATE departments SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    // ─── DELETE ──────────────────────────────────
    public function delete(int $id): bool
    {
        $desigCount = $this->fetchColumn(
            "SELECT COUNT(*) FROM designations WHERE department_id = ?", [$id]
        );
        if ($desigCount > 0) {
            throw new \RuntimeException("Cannot delete department with $desigCount active designations. Reassign or remove them first.");
        }
        return $this->pdo->prepare("DELETE FROM departments WHERE id = ?")->execute([$id]);
    }

    // ─── TREE (parent → children) ────────────────
    public function getTree(): array
    {
        $all = $this->getAll();
        $map = [];
        $roots = [];
        foreach ($all as $d) {
            $d['children'] = [];
            $map[$d['id']] = $d;
        }
        foreach ($all as $d) {
            if ($d['parent_dept_id'] && isset($map[$d['parent_dept_id']])) {
                $map[$d['parent_dept_id']]['children'][] = &$map[$d['id']];
            } else {
                $roots[] = &$map[$d['id']];
            }
        }
        return $roots;
    }

    // ─── STATS ───────────────────────────────────
    public function getStats(): array
    {
        return [
            'total'        => $this->fetchColumn("SELECT COUNT(*) FROM departments") ?? 0,
            'active'       => $this->fetchColumn("SELECT COUNT(*) FROM departments WHERE status='active'") ?? 0,
            'total_desig'  => $this->fetchColumn("SELECT COUNT(*) FROM designations") ?? 0,
            'total_emp'    => $this->fetchColumn("SELECT COUNT(*) FROM employees") ?? 0,
        ];
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
