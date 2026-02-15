<?php

namespace App\Models;

use App\Models\Model;
use PDO;

/**
 * Employee Model
 * Handles all employee-related database operations including management, roles, permissions, and activities
 */
class Employee extends Model
{
    protected static string $table = 'employees';
    protected $primaryKey = 'employee_id';

    /**
     * Get employee by ID with complete details
     */
    public function getEmployeeById($id)
    {
        $sql = "
            SELECT e.*, u.name, u.email, u.phone, u.profile_image, u.status as user_status,
                   r.name as role_name, r.permissions, r.level as role_level,
                   d.name as department_name, d.description as department_description,
                   e.created_at as joining_date, e.updated_at as last_updated,
                   (SELECT COUNT(*) FROM employee_activities ea WHERE ea.employee_id = e.employee_id) as total_activities,
                   (SELECT COUNT(*) FROM employee_tasks et WHERE et.assigned_to = e.employee_id AND et.status != 'completed') as pending_tasks,
                   (SELECT COUNT(*) FROM employee_attendance ea2 WHERE ea2.employee_id = e.employee_id AND DATE(ea2.check_in) = CURDATE()) as today_attendance
            FROM {$this->table} e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN roles r ON e.role_id = r.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.employee_id = :id
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
            SELECT e.*, u.name, u.email, u.phone, u.profile_image,
                   r.name as role_name, r.permissions, r.level as role_level,
                   d.name as department_name, d.description as department_description
            FROM {$this->table} e
            JOIN users u ON e.user_id = u.id
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
            SELECT e.*, u.name, u.email, u.phone, u.profile_image,
                   r.name as role_name, r.permissions, r.level as role_level
            FROM {$this->table} e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN roles r ON e.role_id = r.id
            WHERE u.email = :email
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
                INSERT INTO users (name, email, password, phone, role, status, created_at, updated_at)
                VALUES (:name, :email, :password, :phone, 'employee', 'active', NOW(), NOW())
            ";

            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'phone' => $data['phone']
            ]);

            $userId = $this->db->lastInsertId();

            // Create employee record
            $employeeSql = "
                INSERT INTO {$this->table} (
                    user_id, employee_code, role_id, department_id, designation, salary,
                    joining_date, reporting_manager_id, status, created_at, updated_at
                ) VALUES (
                    :user_id, :employee_code, :role_id, :department_id, :designation, :salary,
                    :joining_date, :reporting_manager_id, 'active', NOW(), NOW()
                )
            ";

            $employeeStmt = $this->db->prepare($employeeSql);
            $employeeStmt->execute([
                'user_id' => $userId,
                'employee_code' => $this->generateEmployeeCode(),
                'role_id' => $data['role_id'],
                'department_id' => $data['department_id'],
                'designation' => $data['designation'],
                'salary' => $data['salary'],
                'joining_date' => $data['joining_date'],
                'reporting_manager_id' => $data['reporting_manager_id'] ?? null
            ]);

            $employeeId = $this->db->lastInsertId();

            // Create initial activity
            $activitySql = "
                INSERT INTO employee_activities (employee_id, activity_type, description, created_at)
                VALUES (:employee_id, 'joined_company', 'Employee joined the company', NOW())
            ";
            $activityStmt = $this->db->prepare($activitySql);
            $activityStmt->execute(['employee_id' => $employeeId]);

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
     * Update employee details
     */
    public function updateEmployee($employeeId, $data)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Update employee table
            $employeeUpdates = [];
            $employeeParams = ['employee_id' => $employeeId];

            $employeeFields = [
                'role_id', 'department_id', 'designation', 'salary', 'status',
                'reporting_manager_id', 'emergency_contact', 'blood_group',
                'address', 'city', 'state', 'pincode'
            ];

            foreach ($employeeFields as $field) {
                if (isset($data[$field])) {
                    $employeeUpdates[] = "{$field} = :{$field}";
                    $employeeParams[$field] = $data[$field];
                }
            }

            if (!empty($employeeUpdates)) {
                $employeeSql = "UPDATE {$this->table} SET " . implode(', ', $employeeUpdates) . ", updated_at = NOW() WHERE employee_id = :employee_id";
                $employeeStmt = $this->db->prepare($employeeSql);
                $employeeStmt->execute($employeeParams);
            }

            // Update user table if needed
            if (isset($data['name']) || isset($data['email']) || isset($data['phone'])) {
                $userUpdates = [];
                $userParams = [];

                if (isset($data['name'])) {
                    $userUpdates[] = 'name = :name';
                    $userParams['name'] = $data['name'];
                }

                if (isset($data['email'])) {
                    $userUpdates[] = 'email = :email';
                    $userParams['email'] = $data['email'];
                }

                if (isset($data['phone'])) {
                    $userUpdates[] = 'phone = :phone';
                    $userParams['phone'] = $data['phone'];
                }

                if (!empty($userUpdates)) {
                    $userSql = "UPDATE users SET " . implode(', ', $userUpdates) . ", updated_at = NOW() WHERE id = (SELECT user_id FROM {$this->table} WHERE employee_id = :employee_id)";
                    $userStmt = $this->db->prepare($userSql);
                    $userStmt->execute(array_merge($userParams, ['employee_id' => $employeeId]));
                }
            }

            // Create activity log
            $activitySql = "
                INSERT INTO employee_activities (employee_id, activity_type, description, created_at)
                VALUES (:employee_id, 'profile_updated', 'Employee profile updated', NOW())
            ";
            $activityStmt = $this->db->prepare($activitySql);
            $activityStmt->execute(['employee_id' => $employeeId]);

            // Commit transaction
            $this->db->commit();

            return true;

        } catch (Exception $e) {
            // Rollback transaction
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get all employees with filters
     */
    public function getAllEmployees($filters = [])
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['department_id'])) {
            $conditions[] = "e.department_id = :department_id";
            $params['department_id'] = $filters['department_id'];
        }

        if (!empty($filters['role_id'])) {
            $conditions[] = "e.role_id = :role_id";
            $params['role_id'] = $filters['role_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "e.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR e.employee_code LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['joining_date_from'])) {
            $conditions[] = "e.joining_date >= :joining_date_from";
            $params['joining_date_from'] = $filters['joining_date_from'];
        }

        if (!empty($filters['joining_date_to'])) {
            $conditions[] = "e.joining_date <= :joining_date_to";
            $params['joining_date_to'] = $filters['joining_date_to'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $offset = (int)(($filters['page'] ?? 1) - 1) * (int)($filters['per_page'] ?? 10);
        $limit = (int)($filters['per_page'] ?? 10);

        $sql = "
            SELECT e.*, u.name, u.email, u.phone, u.profile_image, u.status as user_status,
                   r.name as role_name, r.level as role_level,
                   d.name as department_name,
                   e.created_at as joining_date
            FROM {$this->table} e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN roles r ON e.role_id = r.id
            LEFT JOIN departments d ON e.department_id = d.id
            {$whereClause}
            ORDER BY e.joining_date DESC
            LIMIT :offset, :limit
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee statistics
     */
    public function getEmployeeStats()
    {
        $stats = [];

        // Total employees
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table}");
        $stmt->execute();
        $stats['total_employees'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Employees by status
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as count
            FROM {$this->table}
            GROUP BY status
        ");
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Employees by department
        $stmt = $this->db->prepare("
            SELECT d.name as department, COUNT(e.employee_id) as count
            FROM {$this->table} e
            LEFT JOIN departments d ON e.department_id = d.id
            GROUP BY d.id, d.name
        ");
        $stmt->execute();
        $stats['by_department'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Employees by role
        $stmt = $this->db->prepare("
            SELECT r.name as role, COUNT(e.employee_id) as count
            FROM {$this->table} e
            LEFT JOIN roles r ON e.role_id = r.id
            GROUP BY r.id, r.name
        ");
        $stmt->execute();
        $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // New joiners this month
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as new_joiners
            FROM {$this->table}
            WHERE joining_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ");
        $stmt->execute();
        $stats['new_joiners'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['new_joiners'];

        // Average salary
        $stmt = $this->db->prepare("
            SELECT AVG(salary) as avg_salary, SUM(salary) as total_salary_cost
            FROM {$this->table}
            WHERE status = 'active'
        ");
        $stmt->execute();
        $salaryStats = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['avg_salary'] = (float)($salaryStats['avg_salary'] ?? 0);
        $stats['total_salary_cost'] = (float)($salaryStats['total_salary_cost'] ?? 0);

        return $stats;
    }

    /**
     * Get employee's activities
     */
    public function getEmployeeActivities($employeeId, $filters = [])
    {
        $conditions = ["ea.employee_id = :employee_id"];
        $params = ['employee_id' => $employeeId];

        if (!empty($filters['activity_type'])) {
            $conditions[] = "ea.activity_type = :activity_type";
            $params['activity_type'] = $filters['activity_type'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "ea.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "ea.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT ea.*, u.name as performed_by_name
            FROM employee_activities ea
            LEFT JOIN users u ON ea.performed_by = u.id
            {$whereClause}
            ORDER BY ea.created_at DESC
            LIMIT {$offset}, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee's tasks
     */
    public function getEmployeeTasks($employeeId, $filters = [])
    {
        $conditions = ["et.assigned_to = :employee_id"];
        $params = ['employee_id' => $employeeId];

        if (!empty($filters['status'])) {
            $conditions[] = "et.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $conditions[] = "et.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "et.due_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "et.due_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT et.*, u1.name as assigned_by_name, u2.name as assigned_to_name,
                   p.title as project_name, pt.name as task_type_name
            FROM employee_tasks et
            LEFT JOIN users u1 ON et.assigned_by = u1.id
            LEFT JOIN users u2 ON et.assigned_to = u2.id
            LEFT JOIN projects p ON et.project_id = p.id
            LEFT JOIN project_task_types pt ON et.task_type_id = pt.id
            {$whereClause}
            ORDER BY et.due_date ASC, et.priority DESC
            LIMIT {$offset}, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee's attendance
     */
    public function getEmployeeAttendance($employeeId, $filters = [])
    {
        $conditions = ["ea.employee_id = :employee_id"];
        $params = ['employee_id' => $employeeId];

        if (!empty($filters['month'])) {
            $conditions[] = "DATE_FORMAT(ea.check_in, '%Y-%m') = :month";
            $params['month'] = $filters['month'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "ea.check_in >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "ea.check_in <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT ea.*, TIMESTAMPDIFF(HOUR, ea.check_in, ea.check_out) as hours_worked,
                   TIMEDIFF(ea.check_out, ea.check_in) as total_time
            FROM employee_attendance ea
            {$whereClause}
            ORDER BY ea.check_in DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee's performance metrics
     */
    public function getEmployeePerformance($employeeId, $period = 'monthly')
    {
        $performance = [];

        // Tasks completed
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as tasks_completed,
                   COUNT(CASE WHEN status = 'completed' AND completed_at <= due_date THEN 1 END) as on_time_completions,
                   AVG(TIMESTAMPDIFF(DAY, created_at, completed_at)) as avg_completion_time
            FROM employee_tasks
            WHERE assigned_to = :employee_id AND status = 'completed'
            AND completed_at >= DATE_SUB(NOW(), INTERVAL 1 {$period})
        ");
        $stmt->execute(['employee_id' => $employeeId]);
        $performance['tasks'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Attendance record
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_days,
                   COUNT(CASE WHEN status = 'present' THEN 1 END) as present_days,
                   COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_days,
                   COUNT(CASE WHEN status = 'late' THEN 1 END) as late_days,
                   AVG(TIMESTAMPDIFF(MINUTE, shift_start, check_in)) as avg_late_minutes
            FROM employee_attendance
            WHERE employee_id = :employee_id
            AND check_in >= DATE_SUB(NOW(), INTERVAL 1 {$period})
        ");
        $stmt->execute(['employee_id' => $employeeId]);
        $performance['attendance'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Customer satisfaction (if applicable)
        $stmt = $this->db->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
            FROM employee_reviews
            WHERE employee_id = :employee_id
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 {$period})
        ");
        $stmt->execute(['employee_id' => $employeeId]);
        $performance['satisfaction'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate overall performance score
        $taskScore = $performance['tasks']['tasks_completed'] > 0 ?
            min(($performance['tasks']['on_time_completions'] / $performance['tasks']['tasks_completed']) * 100, 100) : 0;

        $attendanceScore = $performance['attendance']['total_days'] > 0 ?
            ($performance['attendance']['present_days'] / $performance['attendance']['total_days']) * 100 : 0;

        $satisfactionScore = $performance['satisfaction']['avg_rating'] ?? 0;

        $performance['overall_score'] = round(($taskScore * 0.4) + ($attendanceScore * 0.3) + ($satisfactionScore * 0.3));

        return $performance;
    }

    /**
     * Get employee's leave records
     */
    public function getEmployeeLeaves($employeeId, $filters = [])
    {
        $conditions = ["el.employee_id = :employee_id"];
        $params = ['employee_id' => $employeeId];

        if (!empty($filters['status'])) {
            $conditions[] = "el.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['leave_type'])) {
            $conditions[] = "el.leave_type = :leave_type";
            $params['leave_type'] = $filters['leave_type'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "el.start_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "el.end_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT el.*, lt.name as leave_type_name, lt.max_days as leave_type_max_days,
                   u.name as approved_by_name
            FROM employee_leaves el
            LEFT JOIN leave_types lt ON el.leave_type_id = lt.id
            LEFT JOIN users u ON el.approved_by = u.id
            {$whereClause}
            ORDER BY el.start_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee's salary history
     */
    public function getEmployeeSalaryHistory($employeeId)
    {
        $sql = "
            SELECT esh.*, u.name as approved_by_name
            FROM employee_salary_history esh
            LEFT JOIN users u ON esh.approved_by = u.id
            WHERE esh.employee_id = :employee_id
            ORDER BY esh.effective_from DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee's documents
     */
    public function getEmployeeDocuments($employeeId, $filters = [])
    {
        $conditions = ["ed.employee_id = :employee_id"];
        $params = ['employee_id' => $employeeId];

        if (!empty($filters['document_type'])) {
            $conditions[] = "ed.document_type = :document_type";
            $params['document_type'] = $filters['document_type'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "ed.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT ed.*, dt.name as document_type_name, u.name as uploaded_by_name
            FROM employee_documents ed
            LEFT JOIN document_types dt ON ed.document_type_id = dt.id
            LEFT JOIN users u ON ed.uploaded_by = u.id
            {$whereClause}
            ORDER BY ed.upload_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create employee task
     */
    public function createEmployeeTask($data)
    {
        $sql = "
            INSERT INTO employee_tasks (
                title, description, assigned_to, assigned_by, project_id, task_type_id,
                priority, status, due_date, estimated_hours, created_at, updated_at
            ) VALUES (
                :title, :description, :assigned_to, :assigned_by, :project_id, :task_type_id,
                :priority, :status, :due_date, :estimated_hours, NOW(), NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'],
            'assigned_to' => $data['assigned_to'],
            'assigned_by' => $data['assigned_by'],
            'project_id' => $data['project_id'] ?? null,
            'task_type_id' => $data['task_type_id'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'status' => $data['status'] ?? 'pending',
            'due_date' => $data['due_date'] ?? null,
            'estimated_hours' => $data['estimated_hours'] ?? null
        ]);
    }

    /**
     * Update employee task
     */
    public function updateEmployeeTask($taskId, $data)
    {
        $updates = [];
        $params = ['task_id' => $taskId];

        $fields = [
            'title', 'description', 'status', 'priority', 'due_date',
            'estimated_hours', 'actual_hours', 'completion_notes'
        ];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (!empty($updates)) {
            $sql = "UPDATE employee_tasks SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :task_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }

        return false;
    }

    /**
     * Record employee attendance
     */
    public function recordAttendance($employeeId, $data)
    {
        $sql = "
            INSERT INTO employee_attendance (
                employee_id, check_in, check_out, status, location, notes, created_at
            ) VALUES (
                :employee_id, :check_in, :check_out, :status, :location, :notes, NOW()
            )
            ON DUPLICATE KEY UPDATE
                check_out = VALUES(check_out),
                status = VALUES(status),
                location = VALUES(location),
                notes = VALUES(notes)
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'employee_id' => $employeeId,
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'] ?? null,
            'status' => $data['status'],
            'location' => $data['location'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
    }

    /**
     * Get roles for employee management
     */
    public function getRoles()
    {
        $sql = "
            SELECT * FROM roles
            WHERE status = 'active'
            ORDER BY level DESC, name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get departments for employee management
     */
    public function getDepartments()
    {
        $sql = "
            SELECT d.*, COUNT(e.employee_id) as employee_count
            FROM departments d
            LEFT JOIN {$this->table} e ON d.id = e.department_id AND e.status = 'active'
            WHERE d.status = 'active'
            GROUP BY d.id
            ORDER BY d.name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get leave types
     */
    public function getLeaveTypes()
    {
        $sql = "
            SELECT * FROM leave_types
            WHERE status = 'active'
            ORDER BY name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get document types
     */
    public function getDocumentTypes()
    {
        $sql = "
            SELECT * FROM document_types
            WHERE status = 'active'
            ORDER BY name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate unique employee code
     */
    private function generateEmployeeCode()
    {
        do {
            $code = 'EMP' . strtoupper(substr(md5(uniqid()), 0, 8));

            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE employee_code = :code");
            $stmt->execute(['code' => $code]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } while ($result['count'] > 0);

        return $code;
    }

    /**
     * Get employees for admin panel
     */
    public function getEmployeesForAdmin($filters = [])
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR e.employee_code LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['department_id'])) {
            $conditions[] = "e.department_id = :department_id";
            $params['department_id'] = $filters['department_id'];
        }

        if (!empty($filters['role_id'])) {
            $conditions[] = "e.role_id = :role_id";
            $params['role_id'] = $filters['role_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "e.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT e.*, u.name, u.email, u.phone, u.profile_image,
                   r.name as role_name, r.level as role_level,
                   d.name as department_name,
                   COUNT(CASE WHEN et.status != 'completed' THEN 1 END) as pending_tasks,
                   (SELECT status FROM employee_attendance ea WHERE ea.employee_id = e.employee_id AND DATE(ea.check_in) = CURDATE() LIMIT 1) as today_attendance_status
            FROM {$this->table} e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN roles r ON e.role_id = r.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN employee_tasks et ON e.employee_id = et.assigned_to
            {$whereClause}
            GROUP BY e.employee_id
            ORDER BY e.joining_date DESC
            LIMIT {$offset}, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee dashboard data
     */
    public function getEmployeeDashboardData($employeeId)
    {
        $data = [];

        // Basic employee info
        $employee = $this->getEmployeeById($employeeId);
        $data['employee'] = $employee;

        // Today's tasks
        $data['today_tasks'] = $this->getEmployeeTasks($employeeId, [
            'status' => 'pending',
            'date_to' => date('Y-m-d')
        ]);

        // Today's attendance
        $data['today_attendance'] = $this->getEmployeeAttendance($employeeId, [
            'date_from' => date('Y-m-d'),
            'date_to' => date('Y-m-d')
        ]);

        // Recent activities
        $data['recent_activities'] = $this->getEmployeeActivities($employeeId, [
            'per_page' => 5
        ]);

        // Pending leaves (if any)
        $data['pending_leaves'] = $this->getEmployeeLeaves($employeeId, [
            'status' => 'pending'
        ]);

        // Performance metrics
        $data['performance'] = $this->getEmployeePerformance($employeeId);

        return $data;
    }

    /**
     * Update employee password
     */
    public function updateEmployeePassword($employeeId, $newPassword)
    {
        $sql = "
            UPDATE users
            SET password = :password, updated_at = NOW()
            WHERE id = (SELECT user_id FROM {$this->table} WHERE employee_id = :employee_id)
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'employee_id' => $employeeId,
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Deactivate employee
     */
    public function deactivateEmployee($employeeId, $reason = null)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Update employee status
            $sql = "UPDATE {$this->table} SET status = 'inactive', updated_at = NOW() WHERE employee_id = :employee_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['employee_id' => $employeeId]);

            // Update user status
            $userSql = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = (SELECT user_id FROM {$this->table} WHERE employee_id = :employee_id)";
            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute(['employee_id' => $employeeId]);

            // Create activity log
            $activitySql = "
                INSERT INTO employee_activities (employee_id, activity_type, description, created_at)
                VALUES (:employee_id, 'deactivated', :reason, NOW())
            ";
            $activityStmt = $this->db->prepare($activitySql);
            $activityStmt->execute([
                'employee_id' => $employeeId,
                'reason' => $reason ?? 'Employee deactivated'
            ]);

            // Commit transaction
            $this->db->commit();

            return true;

        } catch (Exception $e) {
            // Rollback transaction
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Reactivate employee
     */
    public function reactivateEmployee($employeeId)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Update employee status
            $sql = "UPDATE {$this->table} SET status = 'active', updated_at = NOW() WHERE employee_id = :employee_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['employee_id' => $employeeId]);

            // Update user status
            $userSql = "UPDATE users SET status = 'active', updated_at = NOW() WHERE id = (SELECT user_id FROM {$this->table} WHERE employee_id = :employee_id)";
            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute(['employee_id' => $employeeId]);

            // Create activity log
            $activitySql = "
                INSERT INTO employee_activities (employee_id, activity_type, description, created_at)
                VALUES (:employee_id, 'reactivated', 'Employee reactivated', NOW())
            ";
            $activityStmt = $this->db->prepare($activitySql);
            $activityStmt->execute(['employee_id' => $employeeId]);

            // Commit transaction
            $this->db->commit();

            return true;

        } catch (Exception $e) {
            // Rollback transaction
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get employees by department
     */
    public function getEmployeesByDepartment($departmentId)
    {
        $sql = "
            SELECT e.*, u.name, u.email, u.phone, u.profile_image,
                   r.name as role_name, r.level as role_level
            FROM {$this->table} e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN roles r ON e.role_id = r.id
            WHERE e.department_id = :department_id AND e.status = 'active'
            ORDER BY r.level DESC, u.name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['department_id' => $departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employees by role
     */
    public function getEmployeesByRole($roleId)
    {
        $sql = "
            SELECT e.*, u.name, u.email, u.phone, u.profile_image,
                   d.name as department_name
            FROM {$this->table} e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.role_id = :role_id AND e.status = 'active'
            ORDER BY u.name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if employee has permission for specific action
     */
    public function hasPermission($employeeId, $permission)
    {
        $sql = "
            SELECT r.permissions
            FROM {$this->table} e
            JOIN roles r ON e.role_id = r.id
            WHERE e.employee_id = :employee_id AND e.status = 'active'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['permissions']) {
            $permissions = json_decode($result['permissions'], true);
            return in_array($permission, $permissions);
        }

        return false;
    }

    /**
     * Get employee's reporting structure
     */
    public function getReportingStructure($employeeId)
    {
        $structure = [];

        // Get direct reports
        $sql = "
            SELECT e.*, u.name, u.email, u.phone
            FROM {$this->table} e
            JOIN users u ON e.user_id = u.id
            WHERE e.reporting_manager_id = :employee_id AND e.status = 'active'
            ORDER BY u.name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);
        $structure['direct_reports'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get manager
        $sql = "
            SELECT e.*, u.name, u.email, u.phone
            FROM {$this->table} e
            JOIN users u ON e.user_id = u.id
            WHERE e.employee_id = (SELECT reporting_manager_id FROM {$this->table} WHERE employee_id = :employee_id)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);
        $structure['manager'] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $structure;
    }
}
