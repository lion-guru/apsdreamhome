<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Config;
use Exception;

/**
 * Custom Logging Service
 * Pure PHP implementation for APS Dream Home Custom MVC
 */
class LoggingService
{
    private $db;
    private $logFile;
    private $logLevel;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->logFile = 'logs/application.log';
        $this->logLevel = 'INFO';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log emergency message
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }
    
    /**
     * Log alert message
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }
    
    /**
     * Log critical message
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }
    
    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Log notice message
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }
    
    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * Core logging method
     */
    private function log(string $level, string $message, array $context = []): void
    {
        // Check if we should log this level
        if (!$this->shouldLog($level)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        $logEntry = "[$timestamp] $level: $message$contextStr" . PHP_EOL;
        
        // Write to file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also store in database if enabled
        if (Config::get('log_to_database', false)) {
            $this->logToDatabase($level, $message, $context);
        }
        
        // Send to external monitoring if critical
        if (in_array($level, ['EMERGENCY', 'ALERT', 'CRITICAL'])) {
            $this->sendAlert($level, $message, $context);
        }
    }
    
    /**
     * Check if we should log this level
     */
    private function shouldLog(string $level): bool
    {
        $levels = [
            'DEBUG' => 0,
            'INFO' => 1,
            'NOTICE' => 2,
            'WARNING' => 3,
            'ERROR' => 4,
            'CRITICAL' => 5,
            'ALERT' => 6,
            'EMERGENCY' => 7
        ];
        
        return $levels[$level] >= $levels[$this->logLevel];
    }
    
    /**
     * Log to database
     */
    private function logToDatabase(string $level, string $message, array $context): void
    {
        try {
            $sql = "INSERT INTO system_logs (level, message, context, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $level,
                $message,
                json_encode($context),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Fallback to file logging if database fails
            error_log("Failed to log to database: " . $e->getMessage());
        }
    }
    
    /**
     * Send alert for critical errors
     */
    private function sendAlert(string $level, string $message, array $context): void
    {
        try {
            // TODO: Implement external alert system (email, Slack, etc.)
            // For now, just log to a separate alert file
            $alertFile = 'logs/alerts.log';
            $timestamp = date('Y-m-d H:i:s');
            $alertEntry = "[$timestamp] $level ALERT: $message " . json_encode($context) . PHP_EOL;
            
            file_put_contents($alertFile, $alertEntry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log("Failed to send alert: " . $e->getMessage());
        }
    }
    
    /**
     * Log user activity
     */
    public function logUserActivity(int $userId, string $action, array $details = []): void
    {
        try {
            $sql = "INSERT INTO user_activity_log (user_id, action, details, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $action,
                json_encode($details),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
            // Also log to main log
            $this->info("User activity: $action", array_merge(['user_id' => $userId], $details));
            
        } catch (Exception $e) {
            $this->error("Failed to log user activity: " . $e->getMessage());
        }
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $details = []): void
    {
        try {
            $sql = "INSERT INTO security_log (event, details, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $event,
                json_encode($details),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
            // Also log to main log
            $this->warning("Security event: $event", $details);
            
        } catch (Exception $e) {
            $this->error("Failed to log security event: " . $e->getMessage());
        }
    }
    
    /**
     * Log performance metrics
     */
    public function logPerformance(string $action, float $duration, array $details = []): void
    {
        try {
            $sql = "INSERT INTO performance_log (action, duration, details, created_at) 
                    VALUES (?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $action,
                $duration,
                json_encode($details)
            ]);
            
            // Log slow queries
            if ($duration > 1.0) { // More than 1 second
                $this->warning("Slow operation detected: $action took {$duration}s", $details);
            }
            
        } catch (Exception $e) {
            $this->error("Failed to log performance: " . $e->getMessage());
        }
    }
    
    /**
     * Get recent logs
     */
    public function getRecentLogs(int $limit = 100, string $level = null): array
    {
        try {
            $sql = "SELECT * FROM system_logs";
            $params = [];
            
            if ($level) {
                $sql .= " WHERE level = ?";
                $params[] = $level;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            $this->error("Failed to get recent logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get log statistics
     */
    public function getLogStats(string $startDate = null, string $endDate = null): array
    {
        try {
            $sql = "SELECT level, COUNT(*) as count FROM system_logs";
            $params = [];
            
            if ($startDate || $endDate) {
                $sql .= " WHERE";
                $conditions = [];
                
                if ($startDate) {
                    $conditions[] = " created_at >= ?";
                    $params[] = $startDate;
                }
                
                if ($endDate) {
                    $conditions[] = " created_at <= ?";
                    $params[] = $endDate;
                }
                
                $sql .= " " . implode(" AND", $conditions);
            }
            
            $sql .= " GROUP BY level ORDER BY count DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $stats = [];
            foreach ($stmt->fetchAll() as $row) {
                $stats[$row['level']] = (int)$row['count'];
            }
            
            return $stats;
            
        } catch (Exception $e) {
            $this->error("Failed to get log stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean old logs
     */
    public function cleanOldLogs(int $daysToKeep = 30): bool
    {
        try {
            // Clean database logs
            $sql = "DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$daysToKeep]);
            
            // Clean log files
            $logFiles = ['logs/application.log', 'logs/alerts.log'];
            foreach ($logFiles as $file) {
                if (file_exists($file)) {
                    $lines = file($file);
                    $cutoffDate = date('Y-m-d H:i:s', strtotime("-$daysToKeep days"));
                    
                    $newLines = [];
                    foreach ($lines as $line) {
                        if (preg_match('/\[([\d-]+ [\d:]+)\]/', $line, $matches)) {
                            if ($matches[1] >= $cutoffDate) {
                                $newLines[] = $line;
                            }
                        }
                    }
                    
                    file_put_contents($file, implode('', $newLines));
                }
            }
            
            $this->info("Old logs cleaned successfully", ['days_kept' => $daysToKeep]);
            return true;
            
        } catch (Exception $e) {
            $this->error("Failed to clean old logs: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set log level
     */
    public function setLogLevel(string $level): void
    {
        $this->logLevel = $level;
    }
    
    /**
     * Get log level
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }
}