<?php

namespace App\Services\Custom;

/**
 * Custom Logging Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class LoggingService
{
    private $database;
    private $logger;
    private $config;
    private $session;
    private $logDir;
    
    // Log levels
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    
    // Log categories
    const CATEGORY_API = 'api';
    const CATEGORY_AUTH = 'auth';
    const CATEGORY_DATABASE = 'database';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_PERFORMANCE = 'performance';
    const CATEGORY_SYSTEM = 'system';
    
    public function __construct()
    {
        $this->database = \App\Core\Database::getInstance();
        $this->logger = new \App\Core\Logger();
        $this->config = \App\Core\Config::getInstance();
        $this->session = new \App\Core\Session();
        
        // Initialize log directory
        $this->logDir = STORAGE_PATH . '/logs/';
        $this->ensureLogDirectory();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory()
    {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    /**
     * Log general message
     */
    public function log($message, $level = self::LEVEL_INFO, $category = self::CATEGORY_SYSTEM, array $context = [])
    {
        try {
            // Prepare log entry
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'level' => $level,
                'category' => $category,
                'message' => $message,
                'context' => $context,
                'ip' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'user_id' => $this->session->get('user_id'),
                'memory_usage' => memory_get_usage(true),
                'request_id' => $this->generateRequestId()
            ];
            
            // Write to file
            $this->writeToFile($logEntry, $category);
            
            // Store in database for critical logs
            if (in_array($level, [self::LEVEL_ERROR, self::LEVEL_CRITICAL])) {
                $this->storeInDatabase($logEntry);
            }
            
            // Check for security events
            if ($category === self::CATEGORY_SECURITY) {
                $this->handleSecurityEvent($logEntry);
            }
            
        } catch (Exception $e) {
            // Fallback logging if main logging fails
            error_log("Logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Log debug message
     */
    public function debug($message, array $context = [])
    {
        $this->log($message, self::LEVEL_DEBUG, self::CATEGORY_SYSTEM, $context);
    }
    
    /**
     * Log info message
     */
    public function info($message, array $context = [])
    {
        $this->log($message, self::LEVEL_INFO, self::CATEGORY_SYSTEM, $context);
    }
    
    /**
     * Log warning message
     */
    public function warning($message, array $context = [])
    {
        $this->log($message, self::LEVEL_WARNING, self::CATEGORY_SYSTEM, $context);
    }
    
    /**
     * Log error message
     */
    public function error($message, array $context = [])
    {
        $this->log($message, self::LEVEL_ERROR, self::CATEGORY_SYSTEM, $context);
    }
    
    /**
     * Log critical message
     */
    public function critical($message, array $context = [])
    {
        $this->log($message, self::LEVEL_CRITICAL, self::CATEGORY_SYSTEM, $context);
    }
    
    /**
     * Log API request
     */
    public function logApiRequest($method, $endpoint, $requestData, $response, $responseTime, $statusCode = 200)
    {
        $this->log("API Request: $method $endpoint", self::LEVEL_INFO, self::CATEGORY_API, [
            'method' => $method,
            'endpoint' => $endpoint,
            'request_data' => $requestData,
            'response_status' => $statusCode,
            'response_time' => $responseTime,
            'response_size' => strlen(json_encode($response))
        ]);
    }
    
    /**
     * Log authentication event
     */
    public function logAuth($event, $email, $success = true, $details = [])
    {
        $this->log("Auth Event: $event", $success ? self::LEVEL_INFO : self::LEVEL_WARNING, self::CATEGORY_AUTH, [
            'event' => $event,
            'email' => $email,
            'success' => $success,
            'details' => $details
        ]);
    }
    
    /**
     * Log database query
     */
    public function logQuery($query, $params, $executionTime, $affectedRows = 0)
    {
        $level = $executionTime > 1.0 ? self::LEVEL_WARNING : self::LEVEL_DEBUG;
        
        $this->log("Database Query", $level, self::CATEGORY_DATABASE, [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime,
            'affected_rows' => $affectedRows
        ]);
    }
    
    /**
     * Log security event
     */
    public function logSecurity($event, $severity = 'medium', $details = [])
    {
        $level = $severity === 'high' ? self::LEVEL_CRITICAL : self::LEVEL_ERROR;
        
        $this->log("Security Event: $event", $level, self::CATEGORY_SECURITY, [
            'event' => $event,
            'severity' => $severity,
            'details' => $details
        ]);
    }
    
    /**
     * Log performance metrics
     */
    public function logPerformance($metric, $value, $unit = 'ms', $context = [])
    {
        $this->log("Performance: $metric", self::LEVEL_INFO, self::CATEGORY_PERFORMANCE, [
            'metric' => $metric,
            'value' => $value,
            'unit' => $unit,
            'context' => $context
        ]);
    }
    
    /**
     * Log user activity
     */
    public function logUserActivity($action, $resource = null, $details = [])
    {
        $userId = $this->session->get('user_id');
        
        if ($userId) {
            $this->log("User Activity: $action", self::LEVEL_INFO, self::CATEGORY_SYSTEM, [
                'user_id' => $userId,
                'action' => $action,
                'resource' => $resource,
                'details' => $details
            ]);
        }
    }
    
    /**
     * Log system error with exception
     */
    public function logException(Exception $exception, $context = [])
    {
        $this->log("Exception: " . $exception->getMessage(), self::LEVEL_ERROR, self::CATEGORY_SYSTEM, array_merge([
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ], $context));
    }
    
    /**
     * Write log entry to file
     */
    private function writeToFile($logEntry, $category)
    {
        $logFile = $this->logDir . $category . '_' . date('Y-m-d') . '.log';
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Store critical log entry in database
     */
    private function storeInDatabase($logEntry)
    {
        try {
            $this->database->insert('system_logs', [
                'level' => $logEntry['level'],
                'category' => $logEntry['category'],
                'message' => $logEntry['message'],
                'context' => json_encode($logEntry['context']),
                'ip_address' => $logEntry['ip'],
                'user_id' => $logEntry['user_id'],
                'user_agent' => $logEntry['user_agent'],
                'request_id' => $logEntry['request_id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Fallback to file if database fails
            error_log("Failed to store log in database: " . $e->getMessage());
        }
    }
    
    /**
     * Handle security events
     */
    private function handleSecurityEvent($logEntry)
    {
        // Check for suspicious patterns
        $suspiciousPatterns = [
            'multiple_failed_logins',
            'sql_injection_attempt',
            'xss_attempt',
            'csrf_token_missing',
            'unauthorized_access'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (strpos($logEntry['message'], $pattern) !== false) {
                $this->triggerSecurityAlert($logEntry);
                break;
            }
        }
    }
    
    /**
     * Trigger security alert
     */
    private function triggerSecurityAlert($logEntry)
    {
        // Store security alert
        $this->database->insert('security_alerts', [
            'alert_type' => 'suspicious_activity',
            'message' => $logEntry['message'],
            'ip_address' => $logEntry['ip'],
            'user_agent' => $logEntry['user_agent'],
            'context' => json_encode($logEntry['context']),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log to separate security file
        $securityLogFile = $this->logDir . 'security_alerts_' . date('Y-m-d') . '.log';
        $alertEntry = json_encode([
            'timestamp' => $logEntry['timestamp'],
            'alert' => $logEntry['message'],
            'ip' => $logEntry['ip'],
            'context' => $logEntry['context']
        ]) . PHP_EOL;
        
        file_put_contents($securityLogFile, $alertEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Generate unique request ID
     */
    private function generateRequestId()
    {
        return uniqid('req_', true);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp()
    {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
    
    /**
     * Clean old log files
     */
    public function cleanOldLogs($days = 30)
    {
        $files = glob($this->logDir . '*.log');
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
        
        // Clean database logs
        $this->database->query(
            "DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
        
        $this->info("Old logs cleaned", ['days' => $days]);
    }
    
    /**
     * Get log statistics
     */
    public function getLogStats($hours = 24)
    {
        $stats = [
            'total_logs' => 0,
            'error_count' => 0,
            'warning_count' => 0,
            'api_requests' => 0,
            'auth_events' => 0,
            'security_events' => 0
        ];
        
        // Get from database
        $logs = $this->database->select(
            "SELECT level, category, COUNT(*) as count 
             FROM system_logs 
             WHERE created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
             GROUP BY level, category",
            [$hours]
        );
        
        foreach ($logs as $log) {
            $stats['total_logs'] += $log['count'];
            
            if ($log['level'] === 'error') {
                $stats['error_count'] += $log['count'];
            } elseif ($log['level'] === 'warning') {
                $stats['warning_count'] += $log['count'];
            }
            
            if ($log['category'] === 'api') {
                $stats['api_requests'] += $log['count'];
            } elseif ($log['category'] === 'auth') {
                $stats['auth_events'] += $log['count'];
            } elseif ($log['category'] === 'security') {
                $stats['security_events'] += $log['count'];
            }
        }
        
        return $stats;
    }
    
    /**
     * Export logs to CSV
     */
    public function exportLogs($category = null, $startDate = null, $endDate = null)
    {
        $where = [];
        $params = [];
        
        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }
        
        if ($startDate) {
            $where[] = "created_at >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $where[] = "created_at <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
        
        $logs = $this->database->select(
            "SELECT * FROM system_logs $whereClause ORDER BY created_at DESC",
            $params
        );
        
        $csvFile = $this->logDir . 'export_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen($csvFile, 'w');
        
        // Header
        fputcsv($handle, ['timestamp', 'level', 'category', 'message', 'ip_address', 'user_id', 'created_at']);
        
        // Data
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log['timestamp'],
                $log['level'],
                $log['category'],
                $log['message'],
                $log['ip_address'],
                $log['user_id'],
                $log['created_at']
            ]);
        }
        
        fclose($handle);
        
        return $csvFile;
    }
}
