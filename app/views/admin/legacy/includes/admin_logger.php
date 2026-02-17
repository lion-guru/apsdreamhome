<?php
/**
 * Universal Admin Logging Function
 * Provides consistent logging across all admin functions
 */

if (!function_exists('log_admin_activity')) {
    /**
     * Log admin activity with standardized format
     * 
     * @param string $action The action being performed
     * @param string $description Description of the action
     * @param array $additional_data Optional additional data to log
     * @return bool Success status
     */
    function log_admin_activity($action, $description, $additional_data = []) {
        try {
            $db = \App\Core\App::database();
            
            // Get admin ID from session
            $admin_id = $_SESSION['admin_id'] ?? null;
            $admin_username = $_SESSION['admin_username'] ?? 'Unknown';
            
            // Get client IP address
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            // Create log data
            $log_data = [
                'action' => $action,
                'description' => $description,
                'admin_id' => $admin_id,
                'admin_username' => $admin_username,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'timestamp' => date('Y-m-d H:i:s'),
                'additional_data' => $additional_data
            ];
            
            // Use the centralized ORM to log activity
            $db->execute("INSERT INTO admin_logs (admin_id, action, description, ip_address, user_agent, created_at) VALUES (:admin_id, :action, :description, :ip, :ua, NOW())", [
                'admin_id' => $admin_id,
                'action' => $action,
                'description' => $description,
                'ip' => $ip_address,
                'ua' => $user_agent
            ]);
            
            // Also log to file for backup
            $log_file = __DIR__ . '/../logs/admin_activity.log';
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            $log_entry = date('Y-m-d H:i:s') . ' | ' . $admin_username . ' | ' . $ip_address . ' | ' . $action . ' | ' . $description . ' | ' . json_encode($additional_data) . PHP_EOL;
            file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
            
            return true;
            
        } catch (Exception $e) {
            // If logging fails, we don't want to break the application
            error_log("Admin logging failed: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('log_admin_action_db')) {
    /**
     * Legacy function for backward compatibility
     * Maps to the new log_admin_activity function
     */
    function log_admin_action_db($action, $description, $additional_data = []) {
        return log_admin_activity($action, $description, $additional_data);
    }
}

if (!function_exists('AdminLogger')) {
    /**
     * Class-based logger for object-oriented code
     */
    class AdminLogger {
        /**
         * Log an admin action
         */
        public static function log($action, $data = []) {
            $description = $data['description'] ?? 'Admin action performed';
            return log_admin_activity($action, $description, $data);
        }
        
        /**
         * Log unauthorized access attempt
         */
        public static function logUnauthorizedAccess($details = []) {
            return log_admin_activity('UNAUTHORIZED_ACCESS', 'Unauthorized access attempt', $details);
        }
        
        /**
         * Log security event
         */
        public static function logSecurityEvent($event_type, $details = []) {
            return log_admin_activity('SECURITY_' . $event_type, 'Security event: ' . $event_type, $details);
        }
    }
}

?>
