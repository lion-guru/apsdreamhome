<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use Exception;

/**
 * Employee Model
 * Handles all employee-related database operations including management, roles, permissions, and activities
 */
class Employee extends Model
{
    protected static $table = 'employees';
    // protected static $primaryKey = 'id'; // Inherited from Model


    /**
     * Get employee by ID with complete details
     */
    public function getEmployeeById($id)
    {
        $sql = "
            SELECT e.*, 
                   r.name as role_name,
                   d.name as department_name,
                   e.created_at as joining_date, e.updated_at as last_updated,
                   (SELECT COUNT(*) FROM employee_activities ea WHERE ea.employee_id = e.id) as total_activities,
                   (SELECT COUNT(*) FROM employee_tasks et WHERE et.employee_id = e.id AND et.status != 'completed') as pending_tasks,
                   (SELECT COUNT(*) FROM employee_attendance ea2 WHERE ea2.employee_id = e.id AND ea2.attendance_date = CURDATE()) as today_attendance
            FROM " . static::$table . " e
            LEFT JOIN roles r ON e.role_id = r.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.id = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee by user ID
     */
    public function getEmployeeByUserId($userId)
    {
        $sql = "
            SELECT e.*,
                   r.name as role_name,
                   d.name as department_name
            FROM " . static::$table . " e
            LEFT JOIN roles r ON e.role_id = r.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.user_id = :user_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee by email
     */
    public function getEmployeeByEmail($email)
    {
        $sql = "
            SELECT e.*,
                   r.name as role_name
            FROM " . static::$table . " e
            LEFT JOIN roles r ON e.role_id = r.id
            WHERE e.email = :email
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all employees with filters
     */
    public function getAllEmployees($filters = [])
    {
        $sql = "
            SELECT e.*,
                   r.name as role_name, d.name as department_name
            FROM " . static::$table . " e
            LEFT JOIN roles r ON e.role_id = r.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (e.name LIKE :search OR e.email LIKE :search OR e.phone LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['department'])) {
            $sql .= " AND e.department_id = :department";
            $params['department'] = $filters['department'];
        }

        $sql .= " ORDER BY e.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new employee
     */
    public function createEmployee($data)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Create user account first
            $userSql = "
                INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
                VALUES (:name, :email, :phone, :password, 'employee', 'active', NOW(), NOW())
            ";

            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            $userId = $this->db->lastInsertId();

            // Create employee record
            // Populate name, email, phone, password in employees table as well
            $employeeSql = "
                INSERT INTO " . static::$table . " (
                    user_id, role_id, department_id, designation, salary,
                    join_date, reporting_manager_id, status, address, notes,
                    name, email, phone, password, role, created_at, updated_at
                ) VALUES (
                    :user_id, :role_id, :department_id, :designation, :salary,
                    :join_date, :reporting_manager_id, 'active', :address, :notes,
                    :name, :email, :phone, :password, 'employee', NOW(), NOW()
                )
            ";

            $employeeStmt = $this->db->prepare($employeeSql);
            $employeeStmt->execute([
                'user_id' => $userId,
                'role_id' => $data['role_id'],
                'department_id' => $data['department_id'],
                'designation' => $data['designation'],
                'salary' => $data['salary'],
                'join_date' => $data['joining_date'] ?? date('Y-m-d'),
                'reporting_manager_id' => $data['reporting_manager_id'] ?? null,
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            $employeeId = $this->db->lastInsertId();

            // Create initial activity (if table exists)
            try {
                $activitySql = "
                    INSERT INTO employee_activities (employee_id, activity_type, description, created_at)
                    VALUES (:employee_id, 'joined_company', 'Employee joined the company', NOW())
                ";
                $activityStmt = $this->db->prepare($activitySql);
                $activityStmt->execute(['employee_id' => $employeeId]);
            } catch (Exception $e) {
                // Ignore activity log error if table missing
            }

            // Assign role to user_roles
            if (!empty($data['role_id'])) {
                // Manually insert into user_roles to avoid redundant update in assignRole
                try {
                    $stmtRole = $this->db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)");
                    $stmtRole->execute(['user_id' => $userId, 'role_id' => $data['role_id']]);
                } catch (Exception $e) {
                    // Ignore if user_roles table issue
                }
            }

            // Commit transaction
            $this->db->commit();

            return $employeeId;
        } catch (Exception $e) {
            // Rollback transaction
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get roles for dropdown
     */
    public function getRoles()
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get departments for dropdown
     */
    public function getDepartments()
    {
        $stmt = $this->db->query("SELECT * FROM departments ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update employee details
     */
    public function updateEmployee($id, $data)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // 1. Update employees table (Primary for phone, role_id, department_id, etc.)
            // Also update name, email, phone, role in employees table to keep sync
            $employeeUpdates = [];
            $employeeParams = ['id' => $id];

            $allowedFields = [
                'role_id',
                'department_id',
                'designation',
                'salary',
                'join_date',
                'status',
                'reporting_manager_id',
                'address',
                'notes',
                'name',
                'email',
                'phone',
                'role'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $employeeUpdates[] = "{$field} = :{$field}";
                    $employeeParams[$field] = $data[$field];
                }
            }

            // Handle password update if provided
            if (!empty($data['password'])) {
                $employeeUpdates[] = "password = :password";
                $employeeParams['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (!empty($employeeUpdates)) {
                $sql = "UPDATE " . static::$table . " SET " . implode(', ', $employeeUpdates) . ", updated_at = NOW() WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($employeeParams);
            }

            // 2. Update users table (Primary for authentication: name, email, password, role)
            // Get user_id associated with employee
            $stmtUser = $this->db->prepare("SELECT user_id FROM " . static::$table . " WHERE id = :id");
            $stmtUser->execute(['id' => $id]);
            $employee = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($employee && $employee['user_id']) {
                $userUpdates = [];
                $userParams = ['id' => $employee['user_id']];

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

                // Update password in users table too if changed
                if (!empty($data['password'])) {
                    $userUpdates[] = "password = :password";
                    $userParams['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                }

                if (!empty($userUpdates)) {
                    $userSql = "UPDATE users SET " . implode(', ', $userUpdates) . ", updated_at = NOW() WHERE id = :id";
                    $userStmt = $this->db->prepare($userSql);
                    $userStmt->execute($userParams);
                }

                // Update user_roles if role_id changed
                if (isset($data['role_id'])) {
                    // Check if exists, update or insert
                    $stmtCheck = $this->db->prepare("SELECT * FROM user_roles WHERE user_id = :user_id");
                    $stmtCheck->execute(['user_id' => $employee['user_id']]);
                    if ($stmtCheck->fetch()) {
                        $stmtRole = $this->db->prepare("UPDATE user_roles SET role_id = :role_id WHERE user_id = :user_id");
                        $stmtRole->execute(['user_id' => $employee['user_id'], 'role_id' => $data['role_id']]);
                    } else {
                        $stmtRole = $this->db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)");
                        $stmtRole->execute(['user_id' => $employee['user_id'], 'role_id' => $data['role_id']]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Log error or rethrow
            return false;
        }
    }

    /**
     * Assign role to employee (updates employees table and user_roles)
     */
    public function assignRole($employeeId, $roleId)
    {
        // Update employees table
        $stmt = $this->db->prepare("UPDATE " . static::$table . " SET role_id = :role_id WHERE id = :id");
        $stmt->execute(['role_id' => $roleId, 'id' => $employeeId]);

        // Get user_id
        $stmtUser = $this->db->prepare("SELECT user_id FROM " . static::$table . " WHERE id = :id");
        $stmtUser->execute(['id' => $employeeId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['user_id']) {
            // Delete existing roles
            $stmtDel = $this->db->prepare("DELETE FROM user_roles WHERE user_id = :user_id");
            $stmtDel->execute(['user_id' => $user['user_id']]);

            // Insert new role
            $stmtIns = $this->db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)");
            $stmtIns->execute(['user_id' => $user['user_id'], 'role_id' => $roleId]);
        }
    }

    /**
     * Soft delete employee
     */
    public function deleteEmployee($id)
    {
        $sql = "UPDATE " . static::$table . " SET status = 'deleted', updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Offboard employee
     */
    public function offboardEmployee($id)
    {
        $this->db->beginTransaction();
        try {
            // Deactivate employee
            $stmt = $this->db->prepare("UPDATE " . static::$table . " SET status = 'inactive', updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);

            // Get user_id
            $stmtUser = $this->db->prepare("SELECT user_id FROM " . static::$table . " WHERE id = :id");
            $stmtUser->execute(['id' => $id]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['user_id']) {
                // Deactivate user
                $stmtUserUpd = $this->db->prepare("UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = :id");
                $stmtUserUpd->execute(['id' => $user['user_id']]);

                // Remove roles
                $stmtRoles = $this->db->prepare("DELETE FROM user_roles WHERE user_id = :user_id");
                $stmtRoles->execute(['user_id' => $user['user_id']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
