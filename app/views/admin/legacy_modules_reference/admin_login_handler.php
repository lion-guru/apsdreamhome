<?php
/**
 * Legacy Proxy for AdminLoginHandler.php
 * Delegates login requests to the modern AdminAuthController.
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Http\Controllers\Auth\AdminAuthController;

class AdminLoginHandler {
    /**
     * Proxied login method
     */
    public static function login($username, $password) {
        require_once __DIR__ . '/../../../includes/auth/LegacyAuthBridge.php';

        if (\LegacyAuthBridge::loginAdmin($username, $password)) {
            $admin = \LegacyAuthBridge::getCurrentAdmin();
            return [
                'status' => 'success',
                'message' => 'Logged in successfully',
                'redirect' => self::getDashboardForRole($admin['role'] ?? 'admin')
            ];
        }

        return ['status' => 'error', 'message' => 'Invalid username or password'];
    }

    private static function getDashboardForRole($role) {
        $role_dashboard_map = [
            'superadmin' => 'superadmin_dashboard.php',
            'admin' => 'dashboard.php',
            'manager' => 'manager_dashboard.php',
            'sales' => 'sales_dashboard.php',
            'hr' => 'hr_dashboard.php',
            'finance' => 'finance_dashboard.php'
        ];

        return $role_dashboard_map[$role] ?? 'dashboard.php';
    }
}
