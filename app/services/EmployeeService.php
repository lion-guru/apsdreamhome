<?php
/**
 * EmployeeService - Unified service for Employee + User management
 * Ensures single source of truth between users and employees tables
 */

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class EmployeeService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    /**
     * Create employee with user account in single transaction
     */
    public function createEmployee(array $data): array
    {
        $this->pdo->beginTransaction();
        try {
            // 1. Create user account
            $userId = $this->createUser([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'] ?? 'employee@123',
                'role' => $data['role'] ?? 'employee',
                'department' => $data['department'] ?? 'General',
                'status' => 'active',
            ]);

            // 2. Create employee record
            $employeeCode = 'EMP' . str_pad($userId, 4, '0', STR_PAD_LEFT);
            $empId = $this->createEmployeeRecord([
                'user_id' => $userId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['designation'] ?? 'Employee',
                'department' => $data['department'] ?? 'General',
                'designation' => $data['designation'] ?? 'Employee',
                'employee_code' => $employeeCode,
                'salary' => $data['salary'] ?? null,
                'incentive_model' => $data['incentive_model'] ?? 'salary_only',
                'commission_rate' => $data['commission_rate'] ?? 0.00,
                'commission_type' => $data['commission_type'] ?? 'percentage',
                'joining_date' => $data['joining_date'] ?? date('Y-m-d'),
                'status' => 'active',
                'address' => $data['address'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'pan_number' => $data['pan_number'] ?? null,
                'aadhaar_number' => $data['aadhaar_number'] ?? null,
                'bank_account' => $data['bank_account'] ?? null,
                'bank_ifsc' => $data['bank_ifsc'] ?? null,
            ]);

            $this->pdo->commit();
            return ['success' => true, 'user_id' => $userId, 'employee_id' => $empId, 'employee_code' => $employeeCode];

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update employee + user in single transaction
     */
    public function updateEmployee(int $employeeId, array $data): array
    {
        $this->pdo->beginTransaction();
        try {
            // Get employee record to find user_id (profile-first, legacy fallback)
            $emp = $this->fetchOne("
                SELECT ep.legacy_id AS id, ep.*, u.id AS user_id
                FROM employee_profiles ep
                JOIN users u ON u.id = ep.user_id
                WHERE (ep.legacy_id = ? OR ep.user_id = ? OR ep.id = ?)
                  AND ep.tenant_id = ?
            ", [$employeeId, $employeeId, $employeeId, $this->tenantId()]);
            if (!$emp) {
                throw new \Exception('Employee not found');
            }

            $userId = (int)$emp['user_id'];

            // 1. Update users table (only allowed fields)
            $userUpdates = [];
            $userParams = ['uid' => $userId];
            $allowedUserFields = ['name', 'email', 'phone', 'department', 'status'];
            foreach ($allowedUserFields as $field) {
                if (array_key_exists($field, $data)) {
                    $userUpdates[] = "$field = :$field";
                    $userParams[$field] = $data[$field];
                }
            }
            if (!empty($userUpdates)) {
                $userParams['tid'] = $this->tenantId();
                $sql = "UPDATE users SET " . implode(', ', $userUpdates) . ", updated_at=NOW() WHERE id=:uid AND tenant_id=:tid";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($userParams);
            }

            // 2. Update employees table
            $empUpdates = [];
            $empParams = ['eid' => $employeeId];
            $allowedEmpFields = [
                'name', 'email', 'phone', 'role', 'department', 'designation',
                'salary', 'incentive_model', 'commission_rate', 'commission_type',
                'joining_date', 'status', 'address', 'emergency_contact',
                'date_of_birth', 'pan_number', 'aadhaar_number', 'bank_account', 'bank_ifsc'
            ];
            foreach ($allowedEmpFields as $field) {
                if (array_key_exists($field, $data)) {
                    $empUpdates[] = "$field = :$field";
                    $empParams[$field] = $data[$field];
                }
            }
            if (!empty($empUpdates)) {
                $empParams['tid'] = $this->tenantId();
                $sql = "UPDATE employees SET " . implode(', ', $empUpdates) . ", updated_at=NOW() WHERE id=:eid AND tenant_id=:tid";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($empParams);
            }

            // Mirror profile-supported fields into employee_profiles (best effort; 0 rows is valid)
            $profileData = [];
            foreach (['department', 'designation', 'salary', 'joining_date', 'pan_number', 'aadhaar_number', 'bank_account', 'bank_ifsc', 'status'] as $field) {
                if (array_key_exists($field, $data) && $data[$field] !== '' && $data[$field] !== null) {
                    $profileData[$field] = $data[$field];
                }
            }
            if (!empty($profileData)) {
                $profileSet = array_map(static fn($field) => $field . ' = ?', array_keys($profileData));
                $profileParams = array_values($profileData);
                $profileParams[] = $employeeId;
                $profileParams[] = $this->tenantId();
                $stmt = $this->pdo->prepare("UPDATE employee_profiles SET " . implode(', ', $profileSet) . ", updated_at=NOW() WHERE legacy_id = ? AND tenant_id = ?");
                $stmt->execute($profileParams);
            }

            // 3. Handle password change separately
            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("UPDATE users SET password=?, updated_at=NOW() WHERE id=? AND tenant_id=?");
                $stmt->execute([$hash, $userId, $this->tenantId()]);
            }

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Employee updated successfully'];

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Soft delete employee (marks both user and employee as inactive)
     */
    public function deleteEmployee(int $employeeId): array
    {
        $this->pdo->beginTransaction();
        try {
            // Phase 4/7: use IdentityService for legacy-id → user_id resolution
            $userId = (new \App\Services\IdentityService())->resolveEmployeeUserId($employeeId);
            if ($userId <= 0) {
                throw new \Exception('Employee not found');
            }

            // Update all identity representations (legacy remains functional)
            $this->execute("UPDATE employees SET status='inactive', updated_at=NOW() WHERE id=? AND tenant_id=?", [$employeeId, $this->tenantId()]);
            $this->execute("UPDATE users SET status='inactive', updated_at=NOW() WHERE id=? AND tenant_id=?", [$userId, $this->tenantId()]);
            $this->execute("UPDATE employee_profiles SET status='inactive', updated_at=NOW() WHERE (legacy_id = ? OR user_id = ? OR id = ?) AND tenant_id = ?", [$employeeId, $employeeId, $employeeId, $this->tenantId()]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Employee deactivated'];

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get employee with user data (joined)
     *
     * The identity unification (Wave 3+) moved employees into
     * `employee_profiles`, but the migration only covered a SUBSET of the
     * legacy `employees` rows (employees 1-5 in this DB are still only in
     * `employees`). Without the fallback below, getEmployeeWithUser() threw
     * "Employee not found" for those employees — the employee profile page
     * 500'd. Fall back to the legacy table, then to the users row.
     */
    public function getEmployeeWithUser(int $employeeId): ?array
    {
        $employee = $this->fetchOne("
            SELECT ep.legacy_id AS id, ep.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.created_at as user_created
            FROM employee_profiles ep
            LEFT JOIN users u ON u.id = ep.user_id
            WHERE (ep.legacy_id = ? OR ep.user_id = ? OR ep.id = ?)
              AND ep.tenant_id = ?
        ", [$employeeId, $employeeId, $employeeId, $this->tenantId()]);

        if ($employee) {
            return $employee;
        }

        // Fallback 1: legacy `employees` table (not yet migrated)
        try {
            $legacy = $this->fetchOne("
                SELECT e.id AS id, e.*, u.name as user_name, u.email as user_email,
                       u.phone as user_phone, u.created_at as user_created
                FROM employees e
                LEFT JOIN users u ON u.id = e.user_id
                WHERE e.id = ? OR e.user_id = ?
                LIMIT 1
            ", [$employeeId, $employeeId]);
            if ($legacy) {
                return $legacy;
            }
        } catch (\Throwable $e) {
            error_log('[EmployeeService::getEmployeeWithUser] legacy fallback: ' . $e->getMessage());
        }

        // Fallback 2: the id was a user id — resolve via users
        $user = $this->fetchOne("
            SELECT u.id AS id, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                   u.created_at AS user_created, u.role, u.status
            FROM users u WHERE u.id = ? LIMIT 1
        ", [$employeeId]);
        if ($user) {
            return $user;
        }

        throw new \Exception('Employee not found');
    }

    /**
     * Get all employees with user data (for admin listing)
     */
    public function getAllEmployees(array $filters = []): array
    {
        $sql = "
            SELECT ep.legacy_id AS id, u.name as user_name, u.email as user_email, u.phone as user_phone, u.status as user_status,
                   ep.*
            FROM employee_profiles ep
            JOIN users u ON u.id = ep.user_id
            WHERE ep.tenant_id=?
        ";
        $params = [$this->tenantId()];

        if (!empty($filters['search'])) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        if (!empty($filters['department'])) {
            $sql .= " AND ep.department=?";
            $params[] = $filters['department'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND ep.status=?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['role'])) {
            $sql .= " AND u.role=?";
            $params[] = $filters['role'];
        }

        $sql .= " ORDER BY ep.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'] . " OFFSET " . (int)($filters['offset'] ?? 0);
        }

        return $this->fetchAll($sql, $params);
    }

    /**
     * Sync employee data from user (when user updates profile)
     */
    public function syncFromUser(int $userId): bool
    {
        $user = $this->fetchOne("SELECT * FROM users WHERE id=?{$this->tenantSql()}", array_merge([$userId], $this->tVal()));
        if (!$user) return false;

        $this->execute("
            UPDATE employees SET 
                name=?, email=?, phone=?, department=?, status=?,
                updated_at=NOW()
            WHERE user_id=? AND tenant_id=?
        ", [$user['name'], $user['email'], $user['phone'], $user['department'] ?? 'General', $user['status'], $userId, $this->tenantId()]);

        $profileStatus = ($user['status'] ?? '') === 'active' ? 'active' : ($user['status'] ?? 'active');
        $this->execute("
            UPDATE employee_profiles SET
                department=?, status=?,
                updated_at=NOW()
            WHERE user_id=? AND tenant_id=?
        ", [$user['department'] ?? 'General', $profileStatus, $userId, $this->tenantId()]);

        return true;
    }

    /**
     * Sync user data from employee (when admin updates employee)
     */
    public function syncToUser(int $employeeId): bool
    {
        $emp = $this->fetchOne("
            SELECT ep.legacy_id AS id, ep.*, u.id AS user_id, u.name AS user_name, u.email AS user_email, u.phone AS user_phone
            FROM employee_profiles ep
            JOIN users u ON u.id = ep.user_id
            WHERE (ep.legacy_id = ? OR ep.user_id = ? OR ep.id = ?)
              AND ep.tenant_id = ?
        ", [$employeeId, $employeeId, $employeeId, $this->tenantId()]);
        if (!$emp) {
            return false;
        }
        if (!$emp || !$emp['user_id']) return false;

        $this->execute("
            UPDATE users SET 
                name=?, email=?, phone=?, department=?, status=?,
                updated_at=NOW()
            WHERE id=? AND tenant_id=?
        ", [$emp['user_name'] ?? $emp['name'], $emp['user_email'] ?? $emp['email'], $emp['user_phone'] ?? $emp['phone'], $emp['department'] ?? 'General', $emp['status'], $emp['user_id'], $this->tenantId()]);

        return true;
    }

    /**
     * Get employee statistics
     */
    public function getStats(): array
    {
        $total = $this->fetchOne("SELECT COUNT(*) as c FROM employee_profiles WHERE tenant_id=?", [$this->tenantId()]);
        $active = $this->fetchOne("SELECT COUNT(*) as c FROM employee_profiles WHERE status='active' AND tenant_id=?", [$this->tenantId()]);
        $inactive = $this->fetchOne("SELECT COUNT(*) as c FROM employee_profiles WHERE status='inactive' AND tenant_id=?", [$this->tenantId()]);
        $onLeave = ['c' => 0];

        $deptStats = $this->fetchAll("
            SELECT department, COUNT(*) as count
            FROM employee_profiles
            WHERE tenant_id=? AND department IS NOT NULL AND department!=''
            GROUP BY department ORDER BY count DESC
        ", [$this->tenantId()]);

        return [
            'total' => (int)($total['c'] ?? 0),
            'active' => (int)($active['c'] ?? 0),
            'inactive' => (int)($inactive['c'] ?? 0),
            'on_leave' => (int)($onLeave['c'] ?? 0),
            'by_department' => $deptStats,
        ];
    }

    // Private helpers
    private function createUser(array $data): int
    {
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, phone, password, role, department, status, tenant_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $data['name'], $data['email'], $data['phone'],
            $passwordHash, $data['role'], $data['department'], $data['status'],
            $this->tenantId()
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    private function createEmployeeRecord(array $data): int
    {
        $insertData = $this->tenantInsertData();
        $columns = "user_id,name,email,phone,role,department,designation,employee_code,salary,incentive_model,commission_rate,commission_type,joining_date,status,address,emergency_contact,date_of_birth,pan_number,aadhaar_number,bank_account,bank_ifsc";
        $values = str_repeat('?,', 21) . '?';
        $params = [
            $data['user_id'], $data['name'], $data['email'], $data['phone'],
            $data['role'], $data['department'], $data['designation'], $data['employee_code'],
            $data['salary'], $data['incentive_model'], $data['commission_rate'], $data['commission_type'],
            $data['joining_date'], $data['status'], $data['address'], $data['emergency_contact'],
            $data['date_of_birth'], $data['pan_number'], $data['aadhaar_number'], $data['bank_account'], $data['bank_ifsc']
        ];
        if (!empty($insertData)) {
            $columns .= ", " . implode(', ', array_keys($insertData));
            $values .= ", ?";
            $params = array_merge($params, array_values($insertData));
        }
        $stmt = $this->pdo->prepare("INSERT INTO employees ($columns) VALUES ($values)");
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('EmployeeService::fetchOne: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('EmployeeService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }

    private function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('EmployeeService::execute: ' . $e->getMessage());
            return 0;
        }
    }
}