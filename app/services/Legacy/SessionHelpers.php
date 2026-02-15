<?php

namespace App\Services\Legacy {

use App\Helpers\AuthHelper;
use App\Helpers\SecurityHelper;

/**
 * Unified Session Management Helpers
 *
 * Purpose: Standardize session key usage across the application
 * Provides backward compatibility during migration period
 *
 * @version 1.0.0
 * @date 2025-12-18
 */

class SessionHelpers {
    /**
     * Initialize session if not already started
     */
    public static function ensureSessionStarted() {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Set authentication session with unified schema
     *
     * @param array $userData User data from database
     * @param string $role Primary role (admin|associate|agent|employee|customer|user)
     * @param string|null $subRole Sub-role for admins (superadmin|director|manager|etc)
     * @return bool Success status
     */
    public static function setAuthSession($userData, $role, $subRole = null) {
        self::ensureSessionStarted();

        // Set new unified schema
        $_SESSION['auth'] = [
            'authenticated' => true,
            'user_id' => $userData['id'] ?? $userData['uid'] ?? null,
            'email' => $userData['email'] ?? $userData['uemail'] ?? null,
            'username' => $userData['username'] ?? $userData['uname'] ?? null,
            'name' => $userData['name'] ?? $userData['uname'] ?? null,
            'role' => $role,
            'sub_role' => $subRole,
            'login_time' => time(),
            'last_activity' => time(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        if ($role === 'associate' && isset($userData['associate_id'])) {
            $_SESSION['auth']['associate_id'] = $userData['associate_id'];
        }
        if ($role === 'customer' && isset($userData['customer_id'])) {
            $_SESSION['auth']['customer_id'] = $userData['customer_id'];
        }
        if ($role === 'agent' && isset($userData['agent_id'])) {
            $_SESSION['auth']['agent_id'] = $userData['agent_id'];
        }

        // MLM specific data
        if (isset($userData['sponsor_id'])) {
            $_SESSION['auth']['sponsor_id'] = $userData['sponsor_id'];
        }
        if (isset($userData['referral_code'])) {
            $_SESSION['auth']['referral_code'] = $userData['referral_code'];
        }
        if (isset($userData['commission_rate'])) {
            $_SESSION['auth']['commission_rate'] = $userData['commission_rate'];
        }

        // BACKWARD COMPATIBILITY: Set legacy keys during migration
        $_SESSION['user_id'] = $_SESSION['auth']['user_id'];
        $_SESSION['uid'] = $_SESSION['auth']['user_id'];
        $_SESSION['user_email'] = $_SESSION['auth']['email'];
        $_SESSION['uemail'] = $_SESSION['auth']['email'];
        $_SESSION['user_name'] = $_SESSION['auth']['name'];
        $_SESSION['user_role'] = $role;
        $_SESSION['login_time'] = $_SESSION['auth']['login_time'];
        $_SESSION['last_activity'] = $_SESSION['auth']['last_activity'];

        if ($role === 'admin') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $_SESSION['auth']['user_id'];
            $_SESSION['admin_username'] = $_SESSION['auth']['username'];
            $_SESSION['admin_role'] = $subRole;
            $_SESSION['auser'] = $_SESSION['auth']['username'];
            $_SESSION['role'] = $subRole;

            // Admin session array (some files expect this)
            $_SESSION['admin_session'] = [
                'is_authenticated' => true,
                'user_id' => $_SESSION['auth']['user_id'],
                'username' => $_SESSION['auth']['username'],
                'role' => $subRole,
            ];
        } else {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['utype'] = $role;
            $_SESSION['usertype'] = $role;
            $_SESSION['user_type'] = $role;
        }

        if ($role === 'associate') {
            $_SESSION['associate_id'] = $_SESSION['auth']['associate_id'] ?? null;
            $_SESSION['is_associate'] = true;
        }

        if ($role === 'agent') {
            $_SESSION['agent_id'] = $_SESSION['auth']['agent_id'] ?? null;
            $_SESSION['aid'] = $_SESSION['auth']['agent_id'] ?? null;
        }

        if ($role === 'customer') {
            $_SESSION['customer_id'] = $_SESSION['auth']['customer_id'] ?? null;
            $_SESSION['customer_logged_in'] = true;
        }

        if ($role === 'investor') {
            $_SESSION['investor_id'] = $_SESSION['auth']['user_id'] ?? null;
            $_SESSION['investor_name'] = $_SESSION['auth']['name'] ?? null;
            $_SESSION['investor_email'] = $_SESSION['auth']['email'] ?? null;
            $_SESSION['investor_logged_in'] = true;
        }

        if ($role === 'employee') {
            $_SESSION['employee_id'] = $_SESSION['auth']['user_id'] ?? null;
            $_SESSION['employee_name'] = $_SESSION['auth']['name'] ?? null;
            $_SESSION['employee_email'] = $_SESSION['auth']['email'] ?? null;
            $_SESSION['employee_department'] = $userData['department'] ?? null;
            $_SESSION['employee_logged_in'] = true;
        }

        if ($role === 'builder') {
            $_SESSION['builder_id'] = $_SESSION['auth']['user_id'] ?? null;
            $_SESSION['builder_name'] = $_SESSION['auth']['name'] ?? null;
            $_SESSION['builder_logged_in'] = true;
        }

        // Update last activity
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * Get authenticated user ID
     * Checks new schema first, falls back to legacy keys
     *
     * @return int|null User ID or null if not authenticated
     */
    public static function getAuthUserId() {
        self::ensureSessionStarted();

        // Check new schema
        if (isset($_SESSION['auth']['user_id'])) {
            return $_SESSION['auth']['user_id'];
        }

        // Fallback to legacy keys
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        if (isset($_SESSION['uid'])) {
            return $_SESSION['uid'];
        }
        if (isset($_SESSION['admin_id'])) {
            return $_SESSION['admin_id'];
        }

        return null;
    }

    /**
     * Get authenticated user's primary role.
     *
     * @return string|null Primary role (e.g., 'admin', 'associate', 'customer') or null.
     */
    public static function getAuthUserRole() {
        self::ensureSessionStarted();
        return $_SESSION['auth']['role'] ?? null;
    }

    /**
     * Get authenticated user's sub-role (if applicable).
     *
     * @return string|null Sub-role (e.g., 'superadmin', 'manager') or null.
     */
    public static function getAuthUserSubRole() {
        self::ensureSessionStarted();
        return $_SESSION['auth']['sub_role'] ?? null;
    }

    /**
     * Get authenticated user's username
     *
     * @return string|null Username or null
     */
    public static function getAuthUsername() {
        self::ensureSessionStarted();
        return $_SESSION['auth']['username'] ?? $_SESSION['admin_username'] ?? $_SESSION['username'] ?? null;
    }

    /**
     * Get authenticated user email
     *
     * @return string|null Email or null
     */
    public static function getAuthUserEmail() {
        self::ensureSessionStarted();

        if (isset($_SESSION['auth']['email'])) {
            return $_SESSION['auth']['email'];
        }
        if (isset($_SESSION['uemail'])) {
            return $_SESSION['uemail'];
        }
        if (isset($_SESSION['email'])) {
            return $_SESSION['email'];
        }

        return null;
    }

    /**
     * Get authenticated user's full name
     *
     * @return string|null Full name or null if not available
     */
    public static function getAuthFullName() {
        self::ensureSessionStarted();

        if (isset($_SESSION['auth']['name'])) {
            return $_SESSION['auth']['name'];
        }
        if (isset($_SESSION['user_name'])) {
            return $_SESSION['user_name'];
        }
        if (isset($_SESSION['name'])) {
            return $_SESSION['name'];
        }

        // Fallback to username if no full name is available
        if (isset($_SESSION['username'])) {
            return $_SESSION['username'];
        }

        return null;
    }

    /**
     * Destroy current authentication session
     */
    public static function destroyAuthSession() {
        self::ensureSessionStarted();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        return AuthHelper::isLoggedIn();
    }

    /**
     * Get CSRF token
     */
    public static function getCsrfToken() {
        return SecurityHelper::generateCsrfToken();
    }

    /**
     * Validate CSRF token
     *
     * @param string|null $token Token to validate (defaults to POST/GET)
     * @return bool
     */
    public static function validateCsrfToken($token = null) {
        return SecurityHelper::validateCsrfToken($token);
    }

    /**
     * Set session flash message
     */
    public static function setSessionFlash($key, $message) {
        self::ensureSessionStarted();
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Get session flash message
     */
    public static function getSessionFlash($key) {
        self::ensureSessionStarted();
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }

    /**
     * Role checks
     */
    public static function isAdmin() { return AuthHelper::isLoggedIn('admin'); }
    public static function isAssociate() { return AuthHelper::isLoggedIn('associate'); }
    public static function isAgent() { return AuthHelper::isLoggedIn('agent'); }
    public static function isEmployee() { return AuthHelper::isLoggedIn('employee'); }
    public static function isCustomer() { return AuthHelper::isLoggedIn('customer'); }

    public static function hasRole($role) { return AuthHelper::isLoggedIn($role); }
    public static function hasSubRole($subRole) {
        self::ensureSessionStarted();
        return (isset($_SESSION['auth']['sub_role']) && $_SESSION['auth']['sub_role'] === $subRole);
    }

    /**
     * Specialized IDs
     */
    public static function getAssociateId() {
        self::ensureSessionStarted();
        return $_SESSION['auth']['associate_id'] ?? $_SESSION['associate_id'] ?? null;
    }

    public static function getCustomerId() {
        self::ensureSessionStarted();
        return $_SESSION['auth']['customer_id'] ?? $_SESSION['customer_id'] ?? null;
    }

    public static function getAgentId() {
        self::ensureSessionStarted();
        return $_SESSION['auth']['agent_id'] ?? $_SESSION['agent_id'] ?? null;
    }

    /**
     * Get arbitrary auth data
     */
    public static function getAuthData($key) {
        self::ensureSessionStarted();
        return $_SESSION['auth'][$key] ?? null;
    }

    /**
     * Session activity tracking
     */
    public static function updateLastActivity() {
        self::ensureSessionStarted();
        $_SESSION['auth']['last_activity'] = time();
        $_SESSION['last_activity'] = time();
    }

    public static function isSessionTimedOut($timeout = 1800) {
        self::ensureSessionStarted();
        $last = $_SESSION['auth']['last_activity'] ?? $_SESSION['last_activity'] ?? 0;
        return (time() - $last) > $timeout;
    }
}

}

/**
 * Global delegators for procedural calls
 */
namespace {
    use App\Services\Legacy\SessionHelpers;

    function ensureSessionStarted() {
        SessionHelpers::ensureSessionStarted();
    }

    function setAuthSession($userData, $role, $subRole = null) {
        return SessionHelpers::setAuthSession($userData, $role, $subRole);
    }

    function destroyAuthSession() {
        SessionHelpers::destroyAuthSession();
    }

    function getAuthUserId() {
        return SessionHelpers::getAuthUserId();
    }

    function getAuthUserRole() {
        return SessionHelpers::getAuthUserRole();
    }

    function getAuthUserSubRole() {
        return SessionHelpers::getAuthUserSubRole();
    }

    function getAuthUserEmail() {
        return SessionHelpers::getAuthUserEmail();
    }

    function getAuthUsername() {
        return SessionHelpers::getAuthUsername();
    }

    function getAuthFullName() {
        return SessionHelpers::getAuthFullName();
    }

    function getAuthName() {
        return SessionHelpers::getAuthFullName();
    }

    function getAuthRole() {
        return SessionHelpers::getAuthUserRole();
    }

    function getAuthSubRole() {
        return SessionHelpers::getAuthUserSubRole();
    }

    function isAuthenticated() {
        return SessionHelpers::isAuthenticated();
    }

    function getCsrfToken() {
        return SessionHelpers::getCsrfToken();
    }

    function setSessionFlash($key, $message) {
        SessionHelpers::setSessionFlash($key, $message);
    }

    function getSessionFlash($key) {
        return SessionHelpers::getSessionFlash($key);
    }

    function isAdmin() { return SessionHelpers::isAdmin(); }
    function isAssociate() { return SessionHelpers::isAssociate(); }
    function isAgent() { return SessionHelpers::isAgent(); }
    function isEmployee() { return SessionHelpers::isEmployee(); }
    function isCustomer() { return SessionHelpers::isCustomer(); }

    function hasRole($role) { return SessionHelpers::hasRole($role); }
    function hasSubRole($subRole) { return SessionHelpers::hasSubRole($subRole); }

    function getAssociateId() { return SessionHelpers::getAssociateId(); }
    function getCustomerId() { return SessionHelpers::getCustomerId(); }
    function getAgentId() { return SessionHelpers::getAgentId(); }

    function getAuthData($key) { return SessionHelpers::getAuthData($key); }

    function updateLastActivity() { SessionHelpers::updateLastActivity(); }

    function isSessionTimedOut($timeout = 1800) { return SessionHelpers::isSessionTimedOut($timeout); }
}
