<?php
/**
 * Audit Logging - Track admin actions and user activities
 */
class AuditLogger {
    private static $db;

    /**
     * Log an action
     */
    public static function log($action, $user_id = null, $details = [], $ip = null) {
        global $pdo;

        if (!$pdo) {
            // Fallback to file logging
            $log_entry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => $action,
                'user_id' => $user_id ?? ($_SESSION['user_id'] ?? 'guest'),
                'details' => is_array($details) ? json_encode($details) : $details,
                'ip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'url' => $_SERVER['REQUEST_URI'] ?? '',
            ];
            file_put_contents('logs/audit.log', json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
            return true;
        }

        $stmt = $pdo->prepare("
            INSERT INTO audit_log (action, user_id, details, ip_address, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $action,
            $user_id ?? ($_SESSION['user_id'] ?? 'guest'),
            is_array($details) ? json_encode($details) : $details,
            $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
        ]);
    }

    /**
     * Log admin action
     */
    public static function adminLog($action, $details = []) {
        self::log('admin_' . $action, $_SESSION['admin_id'] ?? null, $details);
    }

    /**
     * Log login attempt
     */
    public static function loginAttempt($username, $success, $ip = null) {
        self::log('login_' . ($success ? 'success' : 'failed'), null, [
            'username' => $username,
            'ip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
        ]);
    }
}

// Usage examples in controllers:
// AuditLogger::log('product_view', $user_id, ['product_id' => 123]);
// AuditLogger::adminLog('product_update', ['product_id' => 123, 'fields' => ['price']]);
// AuditLogger::loginAttempt($username, true);
?>
