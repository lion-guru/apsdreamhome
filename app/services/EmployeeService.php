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

        } catch (\Exception $e) {
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
            // Get employee record to find user_id
            $emp = $this->fetchOne("SELECT * FROM employees WHERE id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
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

            // 3. Handle password change separately
            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("UPDATE users SET password=?, updated_at=NOW() WHERE id=? AND tenant_id=?");
                $stmt->execute([$hash, $userId, $this->tenantId()]);
            }

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Employee updated successfully'];

        } catch (\Exception $e) {
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
            $emp = $this->fetchOne("SELECT user_id FROM employees WHERE id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
            if (!$emp) {
                throw new \Exception('Employee not found');
            }

            $userId = (int)$emp['user_id'];

            // Update both tables
            $this->execute("UPDATE employees SET status='inactive', updated_at=NOW() WHERE id=? AND tenant_id=?", [$employeeId, $this->tenantId()]);
            $this->execute("UPDATE users SET status='inactive', updated_at=NOW() WHERE id=? AND tenant_id=?", [$userId, $this->tenantId()]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Employee deactivated'];

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get employee with user data (joined)
     */
    public function getEmployeeWithUser(int $employeeId): ?array
    {
        return $this->fetchOne("
            SELECT e.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.created_at as user_created
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id{$this->tJoin('u')}
            WHERE e.id=? AND e.tenant_id=?
        ", array_merge([$employeeId], $this->tVal()));
    }

    /**
     * Get all employees with user data (for admin listing)
     */
    public function getAllEmployees(array $filters = []): array
    {
        $sql = "
            SELECT e.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.status as user_status
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id{$this->tJoin('u')}
            WHERE e.tenant_id=?
        ";
        $params = [$this->tenantId()];

        if (!empty($filters['search'])) {
            $sql .= " AND (e.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        if (!empty($filters['department'])) {
            $sql .= " AND e.department=?";
            $params[] = $filters['department'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.status=?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['role'])) {
            $sql .= " AND e.role=?";
            $params[] = $filters['role'];
        }

        $sql .= " ORDER BY e.created_at DESC";

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

        return true;
    }

    /**
     * Sync user data from employee (when admin updates employee)
     */
    public function syncToUser(int $employeeId): bool
    {
        $emp = $this->fetchOne("SELECT * FROM employees WHERE id=?{$this->tenantSql()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp || !$emp['user_id']) return false;

        $this->execute("
            UPDATE users SET 
                name=?, email=?, phone=?, department=?, status=?,
                updated_at=NOW()
            WHERE id=? AND tenant_id=?
        ", [$emp['name'], $emp['email'], $emp['phone'], $emp['department'] ?? 'General', $emp['status'], $emp['user_id'], $this->tenantId()]);

        return true;
    }

    /**
     * Get employee statistics
     */
    public function getStats(): array
    {
        $total = $this->fetchOne("SELECT COUNT(*) as c FROM employees WHERE tenant_id=?", [$this->tenantId()]);
        $active = $this->fetchOne("SELECT COUNT(*) as c FROM employees WHERE status='active' AND tenant_id=?", [$this->tenantId()]);
        $inactive = $this->fetchOne("SELECT COUNT(*) as c FROM employees WHERE status='inactive' AND tenant_id=?", [$this->tenantId()]);
        $onLeave = $this->fetchOne("SELECT COUNT(*) as c FROM employees WHERE status='on_leave' AND tenant_id=?", [$this->tenantId()]);

        $deptStats = $this->fetchAll("
            SELECT department, COUNT(*) as count 
            FROM employees 
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