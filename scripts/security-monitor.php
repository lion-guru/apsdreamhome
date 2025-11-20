<?php
// scripts/security-monitor.php

class SecurityMonitor {
    private $conn;
    private $logFile;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->logFile = __DIR__ . '/../storage/logs/security.log';
        $this->ensureLogDirectory();
    }

    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public function logSecurityEvent($type, $message, $data = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'session_id' => session_id(),
            'data' => $data
        ];

        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);

        // Also log to database if table exists
        $this->logToDatabase($logEntry);

        return true;
    }

    private function logToDatabase($logEntry) {
        try {
            // Check if security_logs table exists
            $result = $this->conn->query("SHOW TABLES LIKE 'security_logs'");
            if ($result->num_rows > 0) {
                $stmt = $this->conn->prepare("INSERT INTO security_logs (timestamp, type, message, ip_address, user_agent, session_id, data) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss",
                    $logEntry['timestamp'],
                    $logEntry['type'],
                    $logEntry['message'],
                    $logEntry['ip_address'],
                    $logEntry['user_agent'],
                    $logEntry['session_id'],
                    json_encode($logEntry['data'])
                );
                $stmt->execute();
                $stmt->close();
            }
        } catch (Exception $e) {
            // Database logging failed, but file logging succeeded
            error_log("Security database logging failed: " . $e->getMessage());
        }
    }

    public function monitorFailedLogins() {
        $this->logSecurityEvent('monitor', 'Checking failed login attempts');

        // Check for recent failed login attempts
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM security_logs WHERE type = 'login_failed' AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute();
        $failedLogins = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        if ($failedLogins > 10) {
            $this->logSecurityEvent('alert', 'High number of failed login attempts', ['count' => $failedLogins]);
        }

        return $failedLogins;
    }

    public function monitorSqlInjectionAttempts() {
        $this->logSecurityEvent('monitor', 'Checking for SQL injection patterns');

        // Check for suspicious patterns in security logs
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM security_logs WHERE type = 'sql_injection_attempt' AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute();
        $sqlAttempts = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        if ($sqlAttempts > 5) {
            $this->logSecurityEvent('alert', 'Potential SQL injection attempts detected', ['count' => $sqlAttempts]);
        }

        return $sqlAttempts;
    }

    public function monitorFileUploads() {
        $this->logSecurityEvent('monitor', 'Checking file upload activities');

        // Check for recent file uploads
        $uploadDir = __DIR__ . '/../storage/uploads/';
        if (is_dir($uploadDir)) {
            $files = glob($uploadDir . '*');
            $recentUploads = 0;

            foreach ($files as $file) {
                if (filemtime($file) > (time() - 3600)) { // Last hour
                    $recentUploads++;
                }
            }

            if ($recentUploads > 20) {
                $this->logSecurityEvent('alert', 'High number of file uploads', ['count' => $recentUploads]);
            }

            return $recentUploads;
        }

        return 0;
    }

    public function monitorAdminAccess() {
        $this->logSecurityEvent('monitor', 'Checking admin access patterns');

        // Check for admin login frequency
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM security_logs WHERE type = 'admin_login' AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute();
        $adminLogins = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        if ($adminLogins > 5) {
            $this->logSecurityEvent('info', 'Multiple admin logins detected', ['count' => $adminLogins]);
        }

        return $adminLogins;
    }

    public function generateSecurityReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [],
            'alerts' => [],
            'recommendations' => []
        ];

        // Get security statistics
        $stmt = $this->conn->prepare("SELECT type, COUNT(*) as count FROM security_logs WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR) GROUP BY type");
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $report['summary'] = $stats;

        // Check for critical issues
        $criticalTypes = ['sql_injection_attempt', 'login_failed', 'unauthorized_access'];
        foreach ($criticalTypes as $type) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM security_logs WHERE type = ? AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $stmt->bind_param("s", $type);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();

            if ($count > 0) {
                $report['alerts'][] = [
                    'type' => $type,
                    'count' => $count,
                    'level' => 'high'
                ];
            }
        }

        // Generate recommendations
        $recommendations = [];
        foreach ($report['alerts'] as $alert) {
            switch ($alert['type']) {
                case 'login_failed':
                    $recommendations[] = 'Consider implementing rate limiting for login attempts';
                    break;
                case 'sql_injection_attempt':
                    $recommendations[] = 'Review input validation and consider implementing WAF';
                    break;
                case 'unauthorized_access':
                    $recommendations[] = 'Check access control policies and user permissions';
                    break;
            }
        }

        $report['recommendations'] = array_unique($recommendations);

        return $report;
    }

    public function cleanupOldLogs($days = 30) {
        $this->logSecurityEvent('maintenance', 'Cleaning up old security logs', ['days' => $days]);

        // Clean file logs
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Read existing logs
        if (file_exists($this->logFile)) {
            $logs = file($this->logFile, FILE_IGNORE_NEW_LINES);
            $keptLogs = [];

            foreach ($logs as $log) {
                $logData = json_decode($log, true);
                if ($logData && $logData['timestamp'] >= $cutoffDate) {
                    $keptLogs[] = $log;
                }
            }

            // Write back kept logs
            file_put_contents($this->logFile, implode("\n", $keptLogs) . "\n");
        }

        // Clean database logs if table exists
        try {
            $stmt = $this->conn->prepare("DELETE FROM security_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Failed to cleanup database security logs: " . $e->getMessage());
        }

        return true;
    }
}

// Usage example
try {
    require_once __DIR__ . '/../includes/db_connection.php';
        global $con;
    $conn = $con;

    $monitor = new SecurityMonitor($conn);

    // Monitor various security aspects
    $failedLogins = $monitor->monitorFailedLogins();
    $sqlAttempts = $monitor->monitorSqlInjectionAttempts();
    $fileUploads = $monitor->monitorFileUploads();
    $adminAccess = $monitor->monitorAdminAccess();

    // Generate security report
    $report = $monitor->generateSecurityReport();

    echo "🛡️  Security Monitoring Report\n";
    echo "=============================\n\n";

    echo "📊 Summary (Last 24 Hours):\n";
    foreach ($report['summary'] as $stat) {
        echo "  • {$stat['type']}: {$stat['count']}\n";
    }

    echo "\n🚨 Alerts (Last Hour):\n";
    if (empty($report['alerts'])) {
        echo "  • No critical alerts detected\n";
    } else {
        foreach ($report['alerts'] as $alert) {
            echo "  • {$alert['type']}: {$alert['count']} occurrences (Level: {$alert['level']})\n";
        }
    }

    echo "\n💡 Recommendations:\n";
    if (empty($report['recommendations'])) {
        echo "  • System security looks good\n";
    } else {
        foreach ($report['recommendations'] as $recommendation) {
            echo "  • $recommendation\n";
        }
    }

    // Clean up old logs (older than 30 days)
    $monitor->cleanupOldLogs(30);

    echo "\n✅ Security monitoring completed successfully\n";

} catch (Exception $e) {
    echo "❌ Security monitoring failed: " . $e->getMessage() . "\n";
}
?>
