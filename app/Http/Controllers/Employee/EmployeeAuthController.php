<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

/**
 * Employee Authentication Controller
 * Handles employee login, session management, and role-based access
 */
class EmployeeAuthController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Show employee login page
     */
    public function employeeLogin()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
        }

        // Generate CSRF token
        $csrf_token = $this->getCsrfToken();

        // Include employee login view
        include_once __DIR__ . '/../../../views/auth/employee_login.php';
    }

    /**
     * Handle employee login authentication
     */
    public function authenticateEmployee()
    {
        try {
            // Start session if not started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Validate CSRF token
            $submittedToken = $_POST['csrf_token'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';

            if (empty($submittedToken) || empty($sessionToken) || !hash_equals($sessionToken, $submittedToken)) {
                throw new \Exception('Invalid CSRF token');
            }

            // Get credentials
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($email) || empty($password)) {
                throw new \Exception('Please fill in all fields');
            }

            // Authenticate against database
            $query = "SELECT * FROM employees WHERE email = ? AND status = 'active' LIMIT 1";
            $employee = $this->db->fetchOne($query, [$email]);

            if ($employee && password_verify($password, $employee['password'])) {
                // Store employee data in session
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_email'] = $employee['email'];
                $_SESSION['employee_role'] = $employee['role'];
                $_SESSION['employee_name'] = $employee['name'];
                $_SESSION['employee_department'] = $employee['department'];
                $_SESSION['login_time'] = time();
                $_SESSION['csrf_token'] = $this->getCsrfToken();

                // Log successful login
                $this->logLoginAttempt($email, true);

                // Redirect to dashboard
                header('Location: ' . BASE_URL . '/employee/dashboard');
                exit;
            } else {
                throw new \Exception('Invalid email or password');
            }
        } catch (\Exception $e) {
            // Log failed attempt
            $this->logLoginAttempt($_POST['email'] ?? '', false, $e->getMessage());

            // Show error and reload login page
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/employee/login');
            exit;
        }
    }

    /**
     * Employee logout
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Log logout
        if (isset($_SESSION['employee_email'])) {
            $this->logLoginAttempt($_SESSION['employee_email'], true, 'logout');
        }

        // Destroy session
        session_destroy();

        // Redirect to login page
        header('Location: ' . BASE_URL . '/employee/login');
        exit;
    }

    /**
     * Check if employee is logged in
     */
    protected function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['employee_id']) && !empty($_SESSION['employee_id']);
    }

    /**
     * Redirect to appropriate dashboard based on role
     */
    protected function redirectToDashboard()
    {
        $role = $_SESSION['employee_role'] ?? 'employee';
        
        switch ($role) {
            case 'telecalling_executive':
                header('Location: ' . BASE_URL . '/employee/telecalling');
                break;
            case 'hr_manager':
                header('Location: ' . BASE_URL . '/employee/hr');
                break;
            case 'legal_advisor':
                header('Location: ' . BASE_URL . '/employee/legal');
                break;
            case 'ca':
                header('Location: ' . BASE_URL . '/employee/ca');
                break;
            case 'land_manager':
                header('Location: ' . BASE_URL . '/employee/land');
                break;
            case 'operations_manager':
                header('Location: ' . BASE_URL . '/employee/operations');
                break;
            case 'marketing_executive':
                header('Location: ' . BASE_URL . '/employee/marketing');
                break;
            default:
                header('Location: ' . BASE_URL . '/employee/dashboard');
                break;
        }
        exit;
    }

    /**
     * Get CSRF token
     */
    protected function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Log login attempt
     */
    protected function logLoginAttempt($email, $success, $details = '')
    {
        $query = "INSERT INTO employee_login_logs (
                    email, success, ip_address, user_agent, details, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())";
        
        $this->db->execute($query, [
            $email,
            $success,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $details
        ]);
    }

    /**
     * Check if employee has permission for specific action
     */
    public function hasPermission($permission): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $role = $_SESSION['employee_role'] ?? '';
        
        // Define role permissions
        $permissions = [
            'employee' => ['view_dashboard', 'view_tasks', 'update_tasks'],
            'telecalling_executive' => ['view_dashboard', 'manage_leads', 'log_calls', 'view_scripts'],
            'hr_manager' => ['view_dashboard', 'manage_employees', 'process_payroll', 'schedule_reviews'],
            'legal_advisor' => ['view_dashboard', 'review_documents', 'handle_disputes', 'manage_compliance'],
            'ca' => ['view_dashboard', 'manage_finances', 'process_invoices', 'generate_reports'],
            'land_manager' => ['view_dashboard', 'manage_properties', 'schedule_visits', 'handle_acquisitions'],
            'operations_manager' => ['view_dashboard', 'manage_operations', 'approve_requests'],
            'marketing_executive' => ['view_dashboard', 'manage_campaigns', 'view_analytics']
        ];

        return in_array($permission, $permissions[$role] ?? []);
    }

    /**
     * Get current employee information
     */
    public function getCurrentEmployee()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $query = "SELECT id, name, email, role, department, profile_image 
                 FROM employees 
                 WHERE id = ? LIMIT 1";
        
        return $this->db->fetchOne($query, [$_SESSION['employee_id']]);
    }

    /**
     * Update employee profile
     */
    public function updateProfile($profileData)
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new \Exception('Not logged in');
            }

            $query = "UPDATE employees 
                      SET name = ?, phone = ?, address = ?, updated_at = NOW()
                      WHERE id = ?";
            
            $this->db->execute($query, [
                $profileData['name'],
                $profileData['phone'] ?? '',
                $profileData['address'] ?? '',
                $_SESSION['employee_id']
            ]);

            // Update session
            $_SESSION['employee_name'] = $profileData['name'];

            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Change password
     */
    public function changePassword($currentPassword, $newPassword)
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new \Exception('Not logged in');
            }

            // Get current employee
            $query = "SELECT password FROM employees WHERE id = ? LIMIT 1";
            $employee = $this->db->fetchOne($query, [$_SESSION['employee_id']]);

            if (!$employee || !password_verify($currentPassword, $employee['password'])) {
                throw new \Exception('Current password is incorrect');
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE employees SET password = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($updateQuery, [$hashedPassword, $_SESSION['employee_id']]);

            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
