<?php
/**
 * Authentication Middleware
 * Handles authentication for different user types (admin, employee, customer, associate)
 */

class AuthMiddleware {
    /**
     * Check if user is authenticated as admin
     */
    public static function adminAuth() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Admin authentication required']);
                exit;
            } else {
                header('Location: /admin/login');
                exit;
            }
        }
    }

    /**
     * Check if user is authenticated as employee
     */
    public static function employeeAuth() {
        if (!isset($_SESSION['employee_id'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Employee authentication required']);
                exit;
            } else {
                header('Location: /employee/login');
                exit;
            }
        }
    }

    /**
     * Check if user is authenticated as customer
     */
    public static function customerAuth() {
        if (!isset($_SESSION['customer_id'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Customer authentication required']);
                exit;
            } else {
                header('Location: /customer/login');
                exit;
            }
        }
    }

    /**
     * Check if user is authenticated as associate
     */
    public static function associateAuth() {
        if (!isset($_SESSION['associate_id'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Associate authentication required']);
                exit;
            } else {
                header('Location: /associate/login');
                exit;
            }
        }
    }

    /**
     * Check if any user is authenticated
     */
    public static function auth() {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in']) &&
            !isset($_SESSION['employee_id']) && !isset($_SESSION['customer_id']) &&
            !isset($_SESSION['associate_id'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            } else {
                header('Location: /login');
                exit;
            }
        }
    }
}
