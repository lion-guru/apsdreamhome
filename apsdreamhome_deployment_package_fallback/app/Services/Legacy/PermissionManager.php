<?php

namespace App\Services\Legacy;
/**
 * Permission Manager System
 *
 * Handles backend-only permission checks, role validation,
 * audit logging, and graceful degradation for API/UI contexts.
 */

require_once __DIR__ . '/SessionHelpers.php';
// Database is autoloaded via PSR-4


class PermissionManager {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = \App\Core\App::database();
        $this->ensureAuditLogTable();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ensure audit_log table has required structure
     */
    private function ensureAuditLogTable() {
        // Check if ip_address exists
        $result = $this->db->query("SHOW COLUMNS FROM audit_log LIKE 'ip_address'");
        if (!$result || ($result instanceof \PDOStatement ? $result->rowCount() : $result->num_rows) === 0) {
            $this->db->execute("ALTER TABLE audit_log ADD COLUMN ip_address VARCHAR(45) AFTER details");
        }

        // Check if role exists (useful for filtering)
        $result = $this->db->query("SHOW COLUMNS FROM audit_log LIKE 'user_role'");
        if (!$result || ($result instanceof \PDOStatement ? $result->rowCount() : $result->num_rows) === 0) {
            $this->db->execute("ALTER TABLE audit_log ADD COLUMN user_role VARCHAR(50) AFTER user_id");
        }
    }

    /**
     * Log an action to the audit log
     */
    public function log($action, $details = '', $user_id = null, $role = null) {
        ensureSessionStarted();
        $user_id = $user_id ?? getAuthUserId();
        $role = $role ?? (isset($_SESSION['auth']['role']) ? $_SESSION['auth']['role'] : null);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $sql = "INSERT INTO audit_log (user_id, user_role, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
        $this->db->execute($sql, [$user_id, $role, $action, $details, $ip]);
        return $this->db->lastInsertId();
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role_name, $die_on_fail = false) {
        ensureSessionStarted();
        if (!isAuthenticated()) {
            if ($die_on_fail) $this->handleFailure("Authentication required");
            return false;
        }

        $user_id = getAuthUserId();
        $sql = "SELECT COUNT(*) as c FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id=? AND r.name=?";
        $res = $this->db->fetch($sql, [$user_id, $role_name]);

        if (!$res || $res['c'] == 0) {
            $this->log('Access Denied', "Role required: $role_name");
            if ($die_on_fail) $this->handleFailure("Access denied for role: $role_name");
            return false;
        }

        return true;
    }

    /**
     * Check if user has a specific permission/action
     */
    public function hasPermission($action, $die_on_fail = false) {
        ensureSessionStarted();
        if (!isAuthenticated()) {
            if ($die_on_fail) $this->handleFailure("Authentication required");
            return false;
        }

        $user_id = getAuthUserId();
        $sql = "SELECT COUNT(*) as c FROM user_roles ur
                JOIN role_permissions rp ON ur.role_id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE ur.user_id=? AND p.action=?";
        $res = $this->db->fetch($sql, [$user_id, $action]);

        if (!$res || $res['c'] == 0) {
            $this->log('Permission Denied', "Action required: $action");
            if ($die_on_fail) $this->handleFailure("Permission denied for action: $action");
            return false;
        }

        return true;
    }

    /**
     * Handle permission failure based on context (API vs UI)
     */
    private function handleFailure($message) {
        $is_api = (strpos($_SERVER['REQUEST_URI'], '/api/') !== false ||
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false));

        if ($is_api) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => $message]);
        } else {
            echo "<div class='alert alert-danger'>$message</div>";
        }
        exit;
    }
}

/**
 * Global helper functions for easy access
 */
function check_role($role, $die = true) {
    return PermissionManager::getInstance()->hasRole($role, $die);
}

function check_permission($action, $die = true) {
    return PermissionManager::getInstance()->hasPermission($action, $die);
}

function hasPermission($role, $action) {
    // For unified manager, role is usually session-based, but we accept it for compatibility
    return PermissionManager::getInstance()->hasPermission($action, false);
}

/**
 * Global helper function for namespaced audit logging
 */
if (!function_exists('App\Services\Legacy\audit_log')) {
    function audit_log($action, $details = '', $user_id = null, $role = null) {
        return PermissionManager::getInstance()->log($action, $details, $user_id, $role);
    }
}
