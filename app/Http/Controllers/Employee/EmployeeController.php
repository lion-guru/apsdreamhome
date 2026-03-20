<?php

namespace App\Http\Controllers\Employee;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use Exception;

/**
 * Employee Controller
 * Handles employee dashboard, authentication, and related operations.
 */
class EmployeeController extends BaseController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Show employee login page
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->isEmployeeLoggedIn()) {
            $this->redirect('/employee/dashboard');
        }

        // Include employee login view
        require_once __DIR__ . '/../../../views/employees/login.php';
    }

    /**
     * Handle employee login authentication
     */
    public function authenticate()
    {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                throw new Exception('Please fill in all fields');
            }

            // Authenticate against database
            $query = "SELECT * FROM users WHERE email = ? AND role = 'employee' LIMIT 1";
            $employee = $this->db->fetchOne($query, [$email]);

            if ($employee && password_verify($password, $employee['password'])) {
                // Set session
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_email'] = $employee['email'];
                $_SESSION['employee_name'] = $employee['name'];
                $_SESSION['login_time'] = time();

                $this->redirect('/employee/dashboard');
            } else {
                throw new Exception('Invalid email or password');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/employee/login');
        }
    }

    /**
     * Show employee dashboard
     */
    public function dashboard()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $dashboardData = $this->getEmployeeDashboardData($employeeId);

        // Include dashboard view
        require_once __DIR__ . '/../../../views/employees/dashboard.php';
    }

    /**
     * Get employee dashboard data
     */
    private function getEmployeeDashboardData($employeeId)
    {
        try {
            $data = [];

            // Get employee info
            $employeeQuery = "SELECT name, email, created_at FROM users WHERE id = ?";
            $employee = $this->db->fetchOne($employeeQuery, [$employeeId]);
            $data['employee'] = $employee;

            // Get tasks
            $data['tasks'] = $this->getEmployeeTasks($employeeId);

            // Get performance
            $data['performance'] = $this->getEmployeePerformance($employeeId);

            // Get attendance
            $data['attendance'] = $this->getEmployeeAttendance($employeeId);

            // Get activities
            $data['activities'] = $this->getEmployeeActivities($employeeId);

            return $data;
        } catch (Exception $e) {
            error_log("Dashboard data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employee tasks
     */
    private function getEmployeeTasks($employeeId)
    {
        $query = "SELECT * FROM tasks WHERE assigned_to = ? ORDER BY created_at DESC LIMIT 10";
        return $this->db->fetchAll($query, [$employeeId]);
    }

    /**
     * Get employee performance metrics
     */
    private function getEmployeePerformance($employeeId, $period = 'month')
    {
        try {
            $performance = [];

            // Get completed tasks count
            $completedQuery = "SELECT COUNT(*) as completed FROM tasks WHERE assigned_to = ? AND status = 'completed'";
            if ($period === 'month') {
                $completedQuery .= " AND MONTH(created_at) = MONTH(CURRENT_DATE)";
            }
            $completed = $this->db->fetchOne($completedQuery, [$employeeId]);
            $performance['completed_tasks'] = $completed['completed'] ?? 0;

            // Get pending tasks count
            $pendingQuery = "SELECT COUNT(*) as pending FROM tasks WHERE assigned_to = ? AND status = 'pending'";
            if ($period === 'month') {
                $pendingQuery .= " AND MONTH(created_at) = MONTH(CURRENT_DATE)";
            }
            $pending = $this->db->fetchOne($pendingQuery, [$employeeId]);
            $performance['pending_tasks'] = $pending['pending'] ?? 0;

            return $performance;
        } catch (Exception $e) {
            error_log("Performance data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employee attendance records
     */
    private function getEmployeeAttendance($employeeId, $filters = [])
    {
        try {
            $query = "SELECT * FROM attendance WHERE employee_id = ?";
            $params = [$employeeId];

            if (!empty($filters['month'])) {
                $query .= " AND MONTH(check_in) = ?";
                $params[] = $filters['month'];
            }

            $query .= " ORDER BY check_in DESC LIMIT 30";

            return $this->db->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Attendance data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employee activities
     */
    private function getEmployeeActivities($employeeId)
    {
        try {
            $query = "SELECT * FROM employee_activities WHERE employee_id = ? ORDER BY created_at DESC LIMIT 10";
            return $this->db->fetchAll($query, [$employeeId]);
        } catch (Exception $e) {
            error_log("Activities data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Record employee attendance (check-in)
     */
    public function checkIn()
    {
        $this->middleware('employee.auth');

        try {
            $employeeId = $_SESSION['employee_id'];
            $checkInTime = date('Y-m-d H:i:s');

            // Check if already checked in today
            $checkQuery = "SELECT id FROM attendance WHERE employee_id = ? AND DATE(check_in) = CURDATE()";
            $existing = $this->db->fetchOne($checkQuery, [$employeeId]);

            if ($existing) {
                throw new Exception('Already checked in today');
            }

            // Insert attendance record
            $query = "INSERT INTO attendance (employee_id, check_in, status) VALUES (?, ?, 'present')";
            $this->db->execute($query, [$employeeId, $checkInTime]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Checked in successfully'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Record employee attendance (check-out)
     */
    public function checkOut()
    {
        $this->middleware('employee.auth');

        try {
            $employeeId = $_SESSION['employee_id'];
            $checkOutTime = date('Y-m-d H:i:s');

            // Update today's attendance record
            $query = "SELECT id FROM attendance WHERE employee_id = ? AND DATE(check_in) = CURDATE()";
            $attendance = $this->db->fetchOne($query, [$employeeId]);

            if (!$attendance) {
                throw new Exception('No check-in record found for today');
            }

            $updateQuery = "UPDATE attendance SET check_out = ? WHERE id = ?";
            $this->db->execute($updateQuery, [$checkOutTime, $attendance['id']]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Checked out successfully'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update task status
     */
    public function updateTask()
    {
        $this->middleware('employee.auth');

        try {
            $taskId = $_POST['task_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            $employeeId = $_SESSION['employee_id'];

            if (empty($taskId) || empty($status)) {
                throw new Exception('Invalid request');
            }

            // Update task
            $query = "UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ? AND assigned_to = ?";
            $this->db->execute($query, [$status, $taskId, $employeeId]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get employee profile
     */
    public function profile()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];

        // Get employee details
        $query = "SELECT id, name, email, phone, created_at FROM users WHERE id = ?";
        $employee = $this->db->fetchOne($query, [$employeeId]);

        // Include profile view
        require_once __DIR__ . '/../../../views/employees/profile.php';
    }

    /**
     * Update employee profile
     */
    public function updateProfile()
    {
        $this->middleware('employee.auth');

        try {
            $employeeId = $_SESSION['employee_id'];
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';

            if (empty($name)) {
                throw new Exception('Name is required');
            }

            // Update profile
            $query = "UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($query, [$name, $phone, $employeeId]);

            // Update session
            $_SESSION['employee_name'] = $name;

            $this->jsonResponse([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Logout employee
     */
    public function logout()
    {
        session_destroy();
        $this->redirect('/employee/login');
    }

    /**
     * Check if employee is logged in
     */
    private function isEmployeeLoggedIn()
    {
        return isset($_SESSION['employee_id']) && !empty($_SESSION['employee_id']);
    }

    /**
     * Middleware to check employee authentication
     */
    protected function middleware($name, $options = [])
    {
        if ($name === 'employee.auth' && !$this->isEmployeeLoggedIn()) {
            $this->redirect('/employee/login');
        }

        // Call parent middleware
        parent::middleware($name, $options);
    }

    /**
     * Send JSON response
     */
    public function jsonResponse($data, int $status = 200)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url)
    {
        header("Location: " . BASE_URL . $url);
        exit;
    }
}
