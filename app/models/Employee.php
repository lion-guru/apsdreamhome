<?php

namespace App\Models;

use PDO;
use Exception;

/**
 * Employee Model
 * Handles all employee-related database operations including management, roles, permissions, and activities
 */
class Employee extends Model
{
    protected static $table = 'employees';

    /**
     * Get PDO connection for raw queries
     */
    protected static function getPdo()
    {
        return static::getDb()->getConnection();
    }

    /**
     * Get employee by ID with complete details
     */
    public function getEmployeeById($id)
    {
        $table = static::$table;
        $sql = "
            SELECT e.*
            FROM {$table} e
            WHERE e.id = :id
        ";

        $stmt = static::getPdo()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee by user ID
     */
    public function getEmployeeByUserId($userId)
    {
        $table = static::$table;
        $sql = "
            SELECT e.*
            FROM {$table} e
            WHERE e.user_id = :user_id
        ";

        $stmt = static::getPdo()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee by email
     */
    public function getEmployeeByEmail($email)
    {
        $table = static::$table;
        $sql = "
            SELECT e.*
            FROM {$table} e
            WHERE e.email = :email
        ";

        $stmt = static::getPdo()->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all employees with filters
     */
    public function getAllEmployees($filters = [])
    {
        $table = static::$table;
        $sql = "
            SELECT e.*
            FROM {$table} e
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (e.name LIKE :search OR e.email LIKE :search OR e.phone LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['department'])) {
            $sql .= " AND e.department = :department";
            $params['department'] = $filters['department'];
        }

        if (!empty($filters['role'])) {
            $sql .= " AND e.role = :role";
            $params['role'] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY e.created_at DESC";

        $stmt = static::getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new employee
     */
    public function createEmployee($data)
    {
        $pdo = static::getPdo();
        $pdo->beginTransaction();

        try {
            // Create user account first
            $userSql = "
                INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
                VALUES (:name, :email, :phone, :password, 'employee', 'active', NOW(), NOW())
            ";

            $userStmt = $pdo->prepare($userSql);
            $userStmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => password_hash($data['password'] ?? 'default123', PASSWORD_DEFAULT)
            ]);

            $userId = $pdo->lastInsertId();

            // Insert employee record
            $table = static::$table;
            $employeeSql = "
                INSERT INTO {$table} (
                    user_id, role, department, designation, salary,
                    joining_date, status, address,
                    name, email, phone, created_at, updated_at
                ) VALUES (
                    :user_id, :role, :department, :designation, :salary,
                    :joining_date, 'active', :address,
                    :name, :email, :phone, NOW(), NOW()
                )
            ";

            $employeeStmt = $pdo->prepare($employeeSql);
            $employeeStmt->execute([
                'user_id' => $userId,
                'role' => $data['role'] ?? $data['designation'] ?? 'employee',
                'department' => $data['department'] ?? 'General',
                'designation' => $data['designation'] ?? 'Employee',
                'salary' => $data['salary'] ?? null,
                'joining_date' => $data['joining_date'] ?? date('Y-m-d'),
                'address' => $data['address'] ?? null,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null
            ]);

            $employeeId = $pdo->lastInsertId();

            $pdo->commit();
            return $employeeId;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get roles for dropdown
     */
    public function getRoles()
    {
        $pdo = static::getPdo();
        $stmt = $pdo->query("SELECT DISTINCT role as id, role as name FROM employees WHERE role IS NOT NULL ORDER BY role");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get departments for dropdown
     */
    public function getDepartments()
    {
        $pdo = static::getPdo();
        $stmt = $pdo->query("SELECT DISTINCT department as id, department as name FROM employees WHERE department IS NOT NULL ORDER BY department");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update employee details
     */
    public function updateEmployee($id, $data)
    {
        $pdo = static::getPdo();
        $pdo->beginTransaction();

        try {
            $table = static::$table;
            $employeeUpdates = [];
            $employeeParams = ['id' => $id];

            $allowedFields = [
                'role',
                'department',
                'designation',
                'salary',
                'joining_date',
                'status',
                'address',
                'emergency_contact',
                'name',
                'email',
                'phone'
            ];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $employeeUpdates[] = "{$field} = :{$field}";
                    $employeeParams[$field] = $data[$field];
                }
            }

            if (!empty($employeeUpdates)) {
                $sql = "UPDATE {$table} SET " . implode(', ', $employeeUpdates) . ", updated_at = NOW() WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($employeeParams);
            }

            // Also update users table (name, email, phone, password)
            $stmtUser = $pdo->prepare("SELECT user_id FROM {$table} WHERE id = :id");
            $stmtUser->execute(['id' => $id]);
            $employee = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($employee && !empty($employee['user_id'])) {
                $userUpdates = [];
                $userParams = ['uid' => $employee['user_id']];

                if (isset($data['name'])) {
                    $userUpdates[] = "name = :name";
                    $userParams['name'] = $data['name'];
                }
                if (isset($data['email'])) {
                    $userUpdates[] = "email = :email";
                    $userParams['email'] = $data['email'];
                }
                if (isset($data['phone'])) {
                    $userUpdates[] = "phone = :phone";
                    $userParams['phone'] = $data['phone'];
                }
                if (!empty($data['password'])) {
                    $userUpdates[] = "password = :password";
                    $userParams['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                }

                if (!empty($userUpdates)) {
                    $userSql = "UPDATE users SET " . implode(', ', $userUpdates) . ", updated_at = NOW() WHERE id = :uid";
                    $userStmt = $pdo->prepare($userSql);
                    $userStmt->execute($userParams);
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Assign role to employee
     */
    public function assignRole($employeeId, $role)
    {
        $table = static::$table;
        $pdo = static::getPdo();
        $stmt = $pdo->prepare("UPDATE {$table} SET role = :role WHERE id = :id");
        $stmt->execute(['role' => $role, 'id' => $employeeId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Soft delete employee
     */
    public function deleteEmployee($id)
    {
        $table = static::$table;
        $pdo = static::getPdo();
        $sql = "UPDATE {$table} SET status = 'inactive', updated_at = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Offboard employee
     */
    public function offboardEmployee($id)
    {
        $pdo = static::getPdo();
        $pdo->beginTransaction();
        try {
            $table = static::$table;
            $stmt = $pdo->prepare("UPDATE {$table} SET status = 'inactive', updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $stmtUser = $pdo->prepare("SELECT user_id FROM {$table} WHERE id = :id");
            $stmtUser->execute(['id' => $id]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user && !empty($user['user_id'])) {
                $stmtUserUpd = $pdo->prepare("UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = :uid");
                $stmtUserUpd->execute(['uid' => $user['user_id']]);
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get employees for admin with filters and pagination
     */
    public static function getAdminEmployees($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $table = static::$table;
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(name LIKE :search_name OR email LIKE :search_email OR phone LIKE :search_phone)";
                $term = '%' . $filters['search'] . '%';
                $params['search_name'] = $term;
                $params['search_email'] = $term;
                $params['search_phone'] = $term;
            }

            if (!empty($filters['status'])) {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['department'])) {
                $where[] = "department = :department";
                $params['department'] = $filters['department'];
            }

            if (!empty($filters['role'])) {
                $where[] = "role = :role";
                $params['role'] = $filters['role'];
            }

            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            $allowed_sorts = ['id', 'name', 'email', 'created_at', 'status', 'role', 'department'];
            $sort = in_array($filters['sort'] ?? '', $allowed_sorts) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';

            $limit = (int)($filters['per_page'] ?? 10);
            $offset = (int)((($filters['page'] ?? 1) - 1) * $limit);

            $sql = "SELECT * FROM {$table} {$where_clause} ORDER BY {$sort} {$order} LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Employee::getAdminEmployees error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total employees count for pagination
     */
    public static function getAdminTotalEmployees($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $table = static::$table;
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(name LIKE :search_name OR email LIKE :search_email OR phone LIKE :search_phone)";
                $term = '%' . $filters['search'] . '%';
                $params['search_name'] = $term;
                $params['search_email'] = $term;
                $params['search_phone'] = $term;
            }

            if (!empty($filters['status'])) {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['department'])) {
                $where[] = "department = :department";
                $params['department'] = $filters['department'];
            }

            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            $sql = "SELECT COUNT(*) FROM {$table} {$where_clause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log('Employee::getAdminTotalEmployees error: ' . $e->getMessage());
            return 0;
        }
    }
}
