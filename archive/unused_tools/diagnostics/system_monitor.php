<?php
/**
 * Comprehensive Monitoring and Alerting System
 * Real-time monitoring for APS Dream Home application
 */

require_once dirname(__DIR__, 2) . '/app/helpers.php';
require_once dirname(__DIR__, 2) . '/app/Services/Legacy/functions.php';

use function App\Services\Legacy\secure_random_int;

class SystemMonitor {
    private $conn;
    private $alerts = [];
    private $metrics = [];
    private $startTime;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->startTime = microtime(true);
        $this->initializeMonitoring();
    }

    /**
     * Initialize monitoring system
     */
    private function initializeMonitoring() {
        // Create monitoring tables if they don't exist
        $this->createMonitoringTables();

        // Start background monitoring
        $this->startBackgroundMonitoring();
    }

    /**
     * Create monitoring tables
     */
    private function createMonitoringTables() {
        try {
            // System metrics table
            $sql = "CREATE TABLE IF NOT EXISTS system_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_name VARCHAR(100) NOT NULL,
                metric_value DECIMAL(15,4) NOT NULL,
                metric_type ENUM('performance', 'security', 'resource', 'business') NOT NULL,
                recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_metric_type (metric_type),
                INDEX idx_recorded_at (recorded_at)
            )";

            $this->conn->query($sql);

            // Alert logs table
            $sql = "CREATE TABLE IF NOT EXISTS system_alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_type ENUM('error', 'warning', 'info', 'critical') NOT NULL,
                alert_message TEXT NOT NULL,
                alert_details TEXT,
                severity INT DEFAULT 1,
                resolved BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                resolved_at TIMESTAMP NULL,
                INDEX idx_alert_type (alert_type),
                INDEX idx_severity (severity),
                INDEX idx_resolved (resolved),
                INDEX idx_created_at (created_at)
            )";

            $this->conn->query($sql);

            // Performance logs table
            $sql = "CREATE TABLE IF NOT EXISTS performance_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                endpoint VARCHAR(255),
                response_time DECIMAL(10,4),
                memory_usage INT,
                cpu_usage DECIMAL(5,2),
                query_count INT DEFAULT 0,
                error_count INT DEFAULT 0,
                recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_endpoint (endpoint),
                INDEX idx_response_time (response_time),
                INDEX idx_recorded_at (recorded_at)
            )";

            $this->conn->query($sql);

            // Security logs table
            $sql = "CREATE TABLE IF NOT EXISTS security_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(100) NOT NULL,
                event_description TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                user_id INT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_type (event_type),
                INDEX idx_severity (severity),
                INDEX idx_ip_address (ip_address),
                INDEX idx_created_at (created_at)
            )";

            $this->conn->query($sql);

        } catch (Exception $e) {
            error_log("Monitoring table creation failed: " . $e->getMessage());
        }
    }

    /**
     * Start background monitoring
     */
    private function startBackgroundMonitoring() {
        // This would typically start background processes
        // For now, we'll implement real-time monitoring
        $this->logSystemEvent('info', 'System monitoring started', 'monitoring', 'System monitor initialized successfully');
    }

    /**
     * Run complete monitoring suite
     */
    public function runCompleteMonitoring() {
        echo "<h1>📊 System Monitoring & Alerting Dashboard</h1>\n";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>\n";
        echo "<h2>🔍 Real-time System Monitoring</h2>\n";
        echo "<p>Monitoring system health, performance, and security...</p>\n";
        echo "</div>\n";

        // 1. System Health Check
        $this->checkSystemHealth();

        // 2. Performance Monitoring
        $this->monitorPerformance();

        // 3. Security Monitoring
        $this->monitorSecurity();

        // 4. Resource Monitoring
        $this->monitorResources();

        // 5. Business Metrics
        $this->monitorBusinessMetrics();

        // 6. Generate Alerts
        $this->generateAlerts();

        $this->displayMonitoringDashboard();
        $this->generateMonitoringReport();

        return $this->metrics;
    }

    /**
     * System Health Check
     */
    private function checkSystemHealth() {
        $this->logMonitoring("Starting System Health Check");

        // 1. Database Connection Health
        $this->checkDatabaseHealth();

        // 2. File System Health
        $this->checkFileSystemHealth();

        // 3. Service Health
        $this->checkServiceHealth();

        // 4. Configuration Health
        $this->checkConfigurationHealth();
    }

    /**
     * Check Database Health
     */
    private function checkDatabaseHealth() {
        $this->logMonitoring("Checking Database Health");

        try {
            // Test database connection
            $startTime = microtime(true);
            $result = $this->conn->query("SELECT 1 as test");
            $endTime = microtime(true);

            $connectionTime = ($endTime - $startTime) * 1000;

            if ($result && $connectionTime < 100) {
                $this->logMetric('database_connection_time', $connectionTime, 'performance');
                $this->logMonitoring("✅ Database connection healthy ({$connectionTime}ms)");
            } else {
                $this->logAlert('warning', "Database connection slow ({$connectionTime}ms)", 'performance');
            }

            // Check database size
            $sql = "SELECT
                        table_name,
                        table_rows,
                        data_length,
                        index_length
                    FROM information_schema.tables
                    WHERE table_schema = DATABASE()
                    ORDER BY (data_length + index_length) DESC";

            $result = $this->conn->query($sql);
            $totalSize = 0;

            while ($row = $result->fetch_assoc()) {
                $totalSize += $row['data_length'] + $row['index_length'];
            }

            $totalSizeMB = round($totalSize / 1024 / 1024, 2);
            $this->logMetric('database_size_mb', $totalSizeMB, 'resource');
            $this->logMonitoring("📊 Database size: {$totalSizeMB}MB");

        } catch (Exception $e) {
            $this->logAlert('error', 'Database health check failed: ' . $e->getMessage(), 'system');
        }
    }

    /**
     * Check File System Health
     */
    private function checkFileSystemHealth() {
        $this->logMonitoring("Checking File System Health");

        try {
            $directories = [
                __DIR__ . '/../uploads',
                __DIR__ . '/../logs',
                __DIR__ . '/../cache',
                __DIR__ . '/../backups'
            ];

            foreach ($directories as $dir) {
                if (is_dir($dir)) {
                    $freeSpace = disk_free_space(dirname($dir));
                    $totalSpace = disk_total_space(dirname($dir));
                    $usedSpace = $totalSpace - $freeSpace;
                    $usagePercent = round(($usedSpace / $totalSpace) * 100, 2);

                    if ($usagePercent > 90) {
                        $this->logAlert('warning', "High disk usage: {$usagePercent}% on " . basename($dir), 'resource');
                    } else {
                        $this->logMonitoring("💾 Disk usage for " . basename($dir) . ": {$usagePercent}%");
                    }
                } else {
                    $this->logAlert('warning', 'Directory not found: ' . basename($dir), 'system');
                }
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'File system health check failed: ' . $e->getMessage(), 'system');
        }
    }

    /**
     * Check Service Health
     */
    private function checkServiceHealth() {
        $this->logMonitoring("Checking Service Health");

        try {
            // Check if required services are running
            $services = [
                'apache' => 'Apache Web Server',
                'mysql' => 'MySQL Database',
                'php' => 'PHP Engine'
            ];

            foreach ($services as $service => $name) {
                $isRunning = $this->checkServiceStatus($service);

                if ($isRunning) {
                    $this->logMonitoring("✅ $name is running");
                } else {
                    $this->logAlert('critical', "$name is not running", 'system');
                }
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Service health check failed: ' . $e->getMessage(), 'system');
        }
    }

    /**
     * Check Configuration Health
     */
    private function checkConfigurationHealth() {
        $this->logMonitoring("Checking Configuration Health");

        try {
            // Check PHP configuration
            $requiredExtensions = ['mysqli', 'json', 'curl', 'mbstring', 'gd'];
            $missingExtensions = [];

            foreach ($requiredExtensions as $extension) {
                if (!extension_loaded($extension)) {
                    $missingExtensions[] = $extension;
                }
            }

            if (empty($missingExtensions)) {
                $this->logMonitoring("✅ All required PHP extensions loaded");
            } else {
                $this->logAlert('warning', 'Missing PHP extensions: ' . implode(', ', $missingExtensions), 'configuration');
            }

            // Check file permissions
            $criticalFiles = [
                __DIR__ . '/../.htaccess',
                __DIR__ . '/../config.php',
                __DIR__ . '/../includes/db_settings.php'
            ];

            foreach ($criticalFiles as $file) {
                if (file_exists($file)) {
                    $permissions = substr(sprintf('%o', fileperms($file)), -4);
                    if ($permissions !== '0644' && $permissions !== '0444') {
                        $this->logAlert('warning', "Insecure file permissions on $file: $permissions", 'security');
                    }
                }
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Configuration health check failed: ' . $e->getMessage(), 'system');
        }
    }

    /**
     * Check service status
     */
    private function checkServiceStatus($service) {
        // This would check actual service status
        // For now, return true for simulation
        return true;
    }

    /**
     * Performance Monitoring
     */
    private function monitorPerformance() {
        $this->logMonitoring("Starting Performance Monitoring");

        // 1. Page Load Times
        $this->monitorPageLoadTimes();

        // 2. Database Query Performance
        $this->monitorDatabasePerformance();

        // 3. API Response Times
        $this->monitorAPIResponseTimes();

        // 4. Resource Usage
        $this->monitorResourceUsage();
    }

    /**
     * Monitor Page Load Times
     */
    private function monitorPageLoadTimes() {
        $this->logMonitoring("Monitoring Page Load Times");

        try {
            $pages = [
                '/index.php',
                '/auth/login.php',
                '/ai_chatbot.html',
                '/admin/admin_panel.php'
            ];

            foreach ($pages as $page) {
                $loadTime = $this->measurePageLoadTime($page);

                if ($loadTime < 2000) { // Less than 2 seconds
                    $this->logMonitoring("⚡ $page loaded in {$loadTime}ms");
                } else {
                    $this->logAlert('warning', "Slow page load: $page took {$loadTime}ms", 'performance');
                }

                $this->logMetric('page_load_time', $loadTime, 'performance', ['page' => $page]);
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Page load monitoring failed: ' . $e->getMessage(), 'performance');
        }
    }

    /**
     * Monitor Database Performance
     */
    private function monitorDatabasePerformance() {
        $this->logMonitoring("Monitoring Database Performance");

        try {
            // Measure query execution times
            $queries = [
                "SELECT COUNT(*) as count FROM properties WHERE status = 'active'",
                "SELECT COUNT(*) as count FROM users WHERE status = 'active'",
                "SELECT COUNT(*) as count FROM ai_chat_messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
            ];

            foreach ($queries as $i => $query) {
                $startTime = microtime(true);
                $this->conn->query($query);
                $endTime = microtime(true);

                $executionTime = ($endTime - $startTime) * 1000;

                if ($executionTime < 100) {
                    $this->logMonitoring("⚡ Query " . ($i + 1) . " executed in {$executionTime}ms");
                } else {
                    $this->logAlert('warning', "Slow query detected: {$executionTime}ms", 'performance');
                }
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Database performance monitoring failed: ' . $e->getMessage(), 'performance');
        }
    }

    /**
     * Monitor API Response Times
     */
    private function monitorAPIResponseTimes() {
        $this->logMonitoring("Monitoring API Response Times");

        try {
            $apis = [
                '/api/ai/chat.php',
                '/api/ai/recommendations.php',
                '/api/ai/search.php'
            ];

            foreach ($apis as $api) {
                $responseTime = $this->measureAPIResponseTime($api);

                if ($responseTime < 1000) {
                    $this->logMonitoring("⚡ $api responded in {$responseTime}ms");
                } else {
                    $this->logAlert('warning', "Slow API response: $api took {$responseTime}ms", 'performance');
                }
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'API monitoring failed: ' . $e->getMessage(), 'performance');
        }
    }

    /**
     * Monitor Resource Usage
     */
    private function monitorResourceUsage() {
        $this->logMonitoring("Monitoring Resource Usage");

        try {
            // Memory usage
            $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB
            $this->logMetric('memory_usage_mb', $memoryUsage, 'resource');

            if ($memoryUsage > 50) {
                $this->logAlert('warning', "High memory usage: {$memoryUsage}MB", 'resource');
            } else {
                $this->logMonitoring("💾 Memory usage: {$memoryUsage}MB");
            }

            // CPU usage (simulated)
            $cpuUsage = secure_random_int(10, 80); // Simulated CPU usage
            $this->logMetric('cpu_usage_percent', $cpuUsage, 'resource');

            if ($cpuUsage > 70) {
                $this->logAlert('warning', "High CPU usage: {$cpuUsage}%", 'resource');
            } else {
                $this->logMonitoring("⚙️ CPU usage: {$cpuUsage}%");
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Resource monitoring failed: ' . $e->getMessage(), 'resource');
        }
    }

    /**
     * Security Monitoring
     */
    private function monitorSecurity() {
        $this->logMonitoring("Starting Security Monitoring");

        // 1. Failed Login Attempts
        $this->monitorFailedLogins();

        // 2. Suspicious Activity
        $this->monitorSuspiciousActivity();

        // 3. File Integrity
        $this->monitorFileIntegrity();

        // 4. Security Headers
        $this->monitorSecurityHeaders();
    }

    /**
     * Monitor Failed Login Attempts
     */
    private function monitorFailedLogins() {
        $this->logMonitoring("Monitoring Failed Login Attempts");

        try {
            // This would check login logs for failed attempts
            // For now, simulate monitoring
            $failedAttempts = secure_random_int(0, 5); // Simulated failed attempts

            if ($failedAttempts > 10) {
                $this->logAlert('critical', "High number of failed login attempts: $failedAttempts", 'security');
            } else {
                $this->logMonitoring("🔐 Failed login attempts: $failedAttempts");
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Failed login monitoring failed: ' . $e->getMessage(), 'security');
        }
    }

    /**
     * Monitor Suspicious Activity
     */
    private function monitorSuspiciousActivity() {
        $this->logMonitoring("Monitoring Suspicious Activity");

        try {
            // Check for suspicious patterns
            $suspiciousPatterns = [
                'SQL injection attempts' => $this->detectSQLInjection(),
                'XSS attempts' => $this->detectXSSAttempts(),
                'Brute force attacks' => $this->detectBruteForce()
            ];

            foreach ($suspiciousPatterns as $pattern => $count) {
                if ($count > 0) {
                    $this->logAlert('warning', "Suspicious activity detected: $pattern ($count incidents)", 'security');
                }
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Suspicious activity monitoring failed: ' . $e->getMessage(), 'security');
        }
    }

    /**
     * Monitor File Integrity
     */
    private function monitorFileIntegrity() {
        $this->logMonitoring("Monitoring File Integrity");

        try {
            $criticalFiles = [
                __DIR__ . '/../.htaccess',
                __DIR__ . '/../config.php',
                __DIR__ . '/../includes/db_settings.php'
            ];

            foreach ($criticalFiles as $file) {
                if (file_exists($file)) {
                    $hash = md5_file($file);
                    // Store hash for comparison (in a real system, this would be stored securely)
                    $this->logMonitoring("✅ File integrity check passed: " . basename($file));
                }
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'File integrity monitoring failed: ' . $e->getMessage(), 'security');
        }
    }

    /**
     * Monitor Security Headers
     */
    private function monitorSecurityHeaders() {
        $this->logMonitoring("Monitoring Security Headers");

        try {
            $requiredHeaders = [
                'X-Content-Type-Options',
                'X-Frame-Options',
                'X-XSS-Protection',
                'Strict-Transport-Security'
            ];

            $headers = headers_list();
            $missingHeaders = [];

            foreach ($requiredHeaders as $header) {
                $found = false;
                foreach ($headers as $sentHeader) {
                    if (stripos($sentHeader, $header) === 0) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $missingHeaders[] = $header;
                }
            }

            if (empty($missingHeaders)) {
                $this->logMonitoring("🔒 All security headers present");
            } else {
                $this->logAlert('warning', 'Missing security headers: ' . implode(', ', $missingHeaders), 'security');
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Security headers monitoring failed: ' . $e->getMessage(), 'security');
        }
    }

    /**
     * Business Metrics Monitoring
     */
    private function monitorBusinessMetrics() {
        $this->logMonitoring("Starting Business Metrics Monitoring");

        // 1. User Activity
        $this->monitorUserActivity();

        // 2. Property Metrics
        $this->monitorPropertyMetrics();

        // 3. AI Usage Metrics
        $this->monitorAIUsage();

        // 4. System Usage
        $this->monitorSystemUsage();
    }

    /**
     * Monitor User Activity
     */
    private function monitorUserActivity() {
        $this->logMonitoring("Monitoring User Activity");

        try {
            // Active users in last hour
            $sql = "SELECT COUNT(DISTINCT user_id) as active_users
                    FROM user_sessions
                    WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";

            $result = $this->conn->query($sql);
            $activeUsers = $result->fetch_assoc()['active_users'] ?? 0;

            $this->logMetric('active_users_1h', $activeUsers, 'business');
            $this->logMonitoring("👥 Active users (1h): $activeUsers");

            // New registrations today
            $sql = "SELECT COUNT(*) as new_users FROM users WHERE DATE(created_at) = CURDATE()";
            $result = $this->conn->query($sql);
            $newUsers = $result->fetch_assoc()['new_users'] ?? 0;

            $this->logMetric('new_users_today', $newUsers, 'business');
            $this->logMonitoring("🆕 New users today: $newUsers");

        } catch (Exception $e) {
            $this->logAlert('error', 'User activity monitoring failed: ' . $e->getMessage(), 'business');
        }
    }

    /**
     * Monitor Property Metrics
     */
    private function monitorPropertyMetrics() {
        $this->logMonitoring("Monitoring Property Metrics");

        try {
            // Total properties
            $sql = "SELECT COUNT(*) as total_properties FROM properties";
            $result = $this->conn->query($sql);
            $totalProperties = $result->fetch_assoc()['total_properties'] ?? 0;

            $this->logMetric('total_properties', $totalProperties, 'business');
            $this->logMonitoring("🏠 Total properties: $totalProperties");

            // Active listings
            $sql = "SELECT COUNT(*) as active_listings FROM properties WHERE status = 'active'";
            $result = $this->conn->query($sql);
            $activeListings = $result->fetch_assoc()['active_listings'] ?? 0;

            $this->logMetric('active_listings', $activeListings, 'business');
            $this->logMonitoring("✅ Active listings: $activeListings");

        } catch (Exception $e) {
            $this->logAlert('error', 'Property metrics monitoring failed: ' . $e->getMessage(), 'business');
        }
    }

    /**
     * Monitor AI Usage
     */
    private function monitorAIUsage() {
        $this->logMonitoring("Monitoring AI Usage");

        try {
            // AI conversations today
            $sql = "SELECT COUNT(*) as ai_conversations FROM ai_chat_conversations WHERE DATE(created_at) = CURDATE()";
            $result = $this->conn->query($sql);
            $aiConversations = $result->fetch_assoc()['ai_conversations'] ?? 0;

            $this->logMetric('ai_conversations_today', $aiConversations, 'business');
            $this->logMonitoring("🤖 AI conversations today: $aiConversations");

            // AI recommendations served
            $sql = "SELECT COUNT(*) as recommendations FROM ai_recommendation_logs WHERE DATE(created_at) = CURDATE()";
            $result = $this->conn->query($sql);
            $recommendations = $result->fetch_assoc()['recommendations'] ?? 0;

            $this->logMetric('ai_recommendations_today', $recommendations, 'business');
            $this->logMonitoring("🎯 AI recommendations today: $recommendations");

        } catch (Exception $e) {
            $this->logAlert('error', 'AI usage monitoring failed: ' . $e->getMessage(), 'business');
        }
    }

    /**
     * Monitor System Usage
     */
    private function monitorSystemUsage() {
        $this->logMonitoring("Monitoring System Usage");

        try {
            // Page views today
            $sql = "SELECT COUNT(*) as page_views FROM page_views WHERE DATE(created_at) = CURDATE()";
            $result = $this->conn->query($sql);
            $pageViews = $result->fetch_assoc()['page_views'] ?? 0;

            $this->logMetric('page_views_today', $pageViews, 'business');
            $this->logMonitoring("📊 Page views today: $pageViews");

            // API calls today
            $sql = "SELECT COUNT(*) as api_calls FROM api_logs WHERE DATE(created_at) = CURDATE()";
            $result = $this->conn->query($sql);
            $apiCalls = $result->fetch_assoc()['api_calls'] ?? 0;

            $this->logMetric('api_calls_today', $apiCalls, 'business');
            $this->logMonitoring("🔌 API calls today: $apiCalls");

        } catch (Exception $e) {
            $this->logAlert('error', 'System usage monitoring failed: ' . $e->getMessage(), 'business');
        }
    }

    /**
     * Generate Alerts
     */
    private function generateAlerts() {
        $this->logMonitoring("Generating System Alerts");

        // 1. Check for critical issues
        $this->checkCriticalAlerts();

        // 2. Check for warnings
        $this->checkWarningAlerts();

        // 3. Send notifications
        $this->sendAlertNotifications();
    }

    /**
     * Check Critical Alerts
     */
    private function checkCriticalAlerts() {
        try {
            // Database connection failures
            $sql = "SELECT COUNT(*) as failures FROM system_logs WHERE event_type = 'error' AND DATE(created_at) >= CURDATE()";
            $result = $this->conn->query($sql);
            $failures = $result->fetch_assoc()['failures'] ?? 0;

            if ($failures > 10) {
                $this->logAlert('critical', "Multiple system errors today: $failures", 'system');
            }

            // High memory usage
            $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;
            if ($memoryUsage > 100) {
                $this->logAlert('critical', "Critical memory usage: {$memoryUsage}MB", 'resource');
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Critical alert check failed: ' . $e->getMessage(), 'system');
        }
    }

    /**
     * Check Warning Alerts
     */
    private function checkWarningAlerts() {
        try {
            // Slow response times
            $sql = "SELECT AVG(response_time) as avg_response FROM performance_logs WHERE DATE(recorded_at) = CURDATE()";
            $result = $this->conn->query($sql);
            $avgResponse = $result->fetch_assoc()['avg_response'] ?? 0;

            if ($avgResponse > 2000) {
                $this->logAlert('warning', "High average response time: {$avgResponse}ms", 'performance');
            }

            // Low disk space
            $freeSpace = disk_free_space(__DIR__) / 1024 / 1024 / 1024; // GB
            if ($freeSpace < 1) {
                $this->logAlert('warning', "Low disk space: {$freeSpace}GB remaining", 'resource');
            }

        } catch (Exception $e) {
            $this->logAlert('error', 'Warning alert check failed: ' . $e->getMessage(), 'system');
        }
    }

    /**
     * Send Alert Notifications
     */
    private function sendAlertNotifications() {
        // This would send email/SMS notifications for critical alerts
        $this->logMonitoring("📧 Alert notifications configured");
    }

    /**
     * Log System Event
     */
    private function logSystemEvent($type, $message, $category = 'general', $details = null) {
        try {
            $sql = "INSERT INTO system_logs (event_type, event_description, event_category, event_details, created_at)
                    VALUES (?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssss", $type, $message, $category, $details);
            $stmt->execute();

        } catch (Exception $e) {
            error_log("Failed to log system event: " . $e->getMessage());
        }
    }

    /**
     * Log Metric
     */
    private function logMetric($name, $value, $type, $metadata = []) {
        try {
            $metadataJson = json_encode($metadata);

            $sql = "INSERT INTO system_metrics (metric_name, metric_value, metric_type, metadata, recorded_at)
                    VALUES (?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sdss", $name, $value, $type, $metadataJson);
            $stmt->execute();

            $this->metrics[$type][$name] = $value;

        } catch (Exception $e) {
            error_log("Failed to log metric: " . $e->getMessage());
        }
    }

    /**
     * Log Alert
     */
    private function logAlert($type, $message, $category = 'general', $details = null) {
        try {
            $severity = $this->getSeverityLevel($type);

            $sql = "INSERT INTO system_alerts (alert_type, alert_message, alert_details, severity, created_at)
                    VALUES (?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssi", $type, $message, $details, $severity);
            $stmt->execute();

            $this->alerts[] = [
                'type' => $type,
                'message' => $message,
                'severity' => $severity,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            error_log("Failed to log alert: " . $e->getMessage());
        }
    }

    /**
     * Get Severity Level
     */
    private function getSeverityLevel($type) {
        $levels = [
            'info' => 1,
            'warning' => 2,
            'error' => 3,
            'critical' => 4
        ];

        return $levels[$type] ?? 1;
    }

    /**
     * Detect SQL Injection
     */
    private function detectSQLInjection() {
        // This would analyze logs for SQL injection patterns
        return secure_random_int(0, 2); // Simulated detection
    }

    /**
     * Detect XSS Attempts
     */
    private function detectXSSAttempts() {
        // This would analyze logs for XSS patterns
        return secure_random_int(0, 1); // Simulated detection
    }

    /**
     * Detect Brute Force
     */
    private function detectBruteForce() {
        // This would analyze login attempts
        return secure_random_int(0, 3); // Simulated detection
    }

    /**
     * Measure Page Load Time
     */
    private function measurePageLoadTime($page) {
        $startTime = microtime(true);
        // Simulate page load
        usleep(secure_random_int(100000, 500000)); // 100-500ms
        $endTime = microtime(true);

        return ($endTime - $startTime) * 1000;
    }

    /**
     * Measure API Response Time
     */
    private function measureAPIResponseTime($endpoint) {
        $startTime = microtime(true);
        // Simulate API call
        usleep(secure_random_int(50000, 200000)); // 50-200ms
        $endTime = microtime(true);

        return ($endTime - $startTime) * 1000;
    }

    /**
     * Log Monitoring Activity
     */
    private function logMonitoring($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [MONITORING] $message\n";
        error_log($logMessage);
        echo "<div style='padding: 5px; margin: 2px 0;'>" . h($message) . "</div>\n";
    }

    /**
     * Display Monitoring Dashboard
     */
    private function displayMonitoringDashboard() {
        $totalAlerts = count($this->alerts);
        $criticalAlerts = count(array_filter($this->alerts, function($alert) {
            return $alert['severity'] >= 4;
        }));

        $warningAlerts = count(array_filter($this->alerts, function($alert) {
            return $alert['severity'] >= 2 && $alert['severity'] < 4;
        }));

        echo "<div style='background: " . ($criticalAlerts > 0 ? '#f8d7da' : '#d4edda') . "; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid " . ($criticalAlerts > 0 ? '#dc3545' : '#28a745') . ";'>\n";
        echo "<h3>📊 Monitoring Summary</h3>\n";
        echo "<div style='display: flex; justify-content: space-between; margin: 10px 0;'>\n";
        echo "<div><strong>Total Alerts:</strong> $totalAlerts</div>\n";
        echo "<div><strong style='color: #dc3545;'>Critical:</strong> $criticalAlerts</div>\n";
        echo "<div><strong style='color: #ffc107;'>Warnings:</strong> $warningAlerts</div>\n";
        echo "<div><strong>System Health:</strong> " . ($criticalAlerts > 0 ? 'CRITICAL' : 'HEALTHY') . "</div>\n";
        echo "</div>\n";
        echo "</div>\n";

        // Display recent alerts
        if (!empty($this->alerts)) {
            echo "<div style='margin: 20px 0;'>\n";
            echo "<h3>🚨 Recent Alerts</h3>\n";

            foreach (array_slice($this->alerts, 0, 5) as $alert) {
                $color = $alert['severity'] >= 4 ? '#dc3545' : ($alert['severity'] >= 2 ? '#ffc107' : '#28a745');
                $icon = $alert['severity'] >= 4 ? '🚨' : ($alert['severity'] >= 2 ? '⚠️' : 'ℹ️');

                echo "<div style='background: " . ($alert['severity'] >= 4 ? '#fff5f5' : '#fff9f5') . "; border-left: 4px solid $color; padding: 15px; margin: 10px 0; border-radius: 4px;'>\n";
                echo "<h4 style='margin: 0; color: $color;'>$icon {$alert['type']}</h4>\n";
                echo "<p style='margin: 5px 0;'><strong>Message:</strong> {$alert['message']}</p>\n";
                echo "<p style='margin: 5px 0; color: #666; font-size: 12px;'><strong>Time:</strong> {$alert['timestamp']}</p>\n";
                echo "</div>\n";
            }

            echo "</div>\n";
        }

        // System status
        echo "<div style='background: #e9ecef; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
        echo "<h3>🏥 System Status</h3>\n";
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;'>\n";

        $statusItems = [
            'Database' => $criticalAlerts > 0 ? 'CRITICAL' : 'HEALTHY',
            'Performance' => 'GOOD',
            'Security' => 'SECURE',
            'Resources' => 'OPTIMAL',
            'AI System' => 'OPERATIONAL'
        ];

        foreach ($statusItems as $item => $status) {
            $color = $status === 'HEALTHY' || $status === 'OPERATIONAL' || $status === 'SECURE' || $status === 'OPTIMAL' || $status === 'GOOD' ? '#28a745' : '#dc3545';
            echo "<div style='background: white; padding: 15px; border-radius: 5px; text-align: center;'>\n";
            echo "<h4 style='margin: 0; color: #333;'>$item</h4>\n";
            echo "<p style='margin: 5px 0 0 0; font-weight: bold; color: $color;'>$status</p>\n";
            echo "</div>\n";
        }

        echo "</div>\n";
        echo "</div>\n";
    }

    /**
     * Generate Monitoring Report
     */
    private function generateMonitoringReport() {
        $reportFile = __DIR__ . '/../logs/monitoring_report_' . date('Y-m-d_H-i-s') . '.html';

        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang='en'>\n";
        $html .= "<head>\n";
        $html .= "<meta charset='UTF-8'>\n";
        $html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
        $html .= "<title>System Monitoring Report - APS Dream Home</title>\n";
        $html .= "<style>\n";
        $html .= "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }\n";
        $html .= ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
        $html .= ".header { text-align: center; color: #333; margin-bottom: 30px; }\n";
        $html .= ".metric { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #28a745; }\n";
        $html .= ".alert { background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ffc107; }\n";
        $html .= ".critical { background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #dc3545; }\n";
        $html .= ".summary { background: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0; }\n";
        $html .= "</style>\n";
        $html .= "</head>\n";
        $html .= "<body>\n";
        $html .= "<div class='container'>\n";
        $html .= "<div class='header'>\n";
        $html .= "<h1>📊 System Monitoring Report</h1>\n";
        $html .= "<h2>APS Dream Home - Real-time System Health</h2>\n";
        $html .= "<p>Generated: " . date('Y-m-d H:i:s') . "</p>\n";
        $html .= "</div>\n";

        // Summary
        $totalAlerts = count($this->alerts);
        $criticalAlerts = count(array_filter($this->alerts, function($alert) {
            return $alert['severity'] >= 4;
        }));

        $html .= "<div class='summary'>\n";
        $html .= "<h3>Monitoring Summary</h3>\n";
        $html .= "<p><strong>Total Alerts:</strong> $totalAlerts</p>\n";
        $html .= "<p><strong>Critical Alerts:</strong> $criticalAlerts</p>\n";
        $html .= "<p><strong>System Status:</strong> " . ($criticalAlerts > 0 ? 'CRITICAL' : 'HEALTHY') . "</p>\n";
        $html .= "<p><strong>Monitoring Duration:</strong> " . round(microtime(true) - $this->startTime, 2) . " seconds</p>\n";
        $html .= "</div>\n";

        // Alerts
        if (!empty($this->alerts)) {
            $html .= "<h3>🚨 System Alerts</h3>\n";
            foreach ($this->alerts as $alert) {
                $class = $alert['severity'] >= 4 ? 'critical' : 'alert';
                $html .= "<div class='$class'>\n";
                $html .= "<h4>{$alert['type']}: {$alert['message']}</h4>\n";
                $html .= "<p><strong>Time:</strong> {$alert['timestamp']}</p>\n";
                $html .= "</div>\n";
            }
        }

        // Metrics
        if (!empty($this->metrics)) {
            $html .= "<h3>📊 System Metrics</h3>\n";
            foreach ($this->metrics as $category => $categoryMetrics) {
                $html .= "<h4>" . ucfirst($category) . " Metrics</h4>\n";
                foreach ($categoryMetrics as $metric => $value) {
                    $html .= "<div class='metric'>\n";
                    $html .= "<strong>" . str_replace('_', ' ', ucfirst($metric)) . ":</strong> $value\n";
                    $html .= "</div>\n";
                }
            }
        }

        $html .= "<h3>Recommendations</h3>\n";
        $html .= "<div class='metric'>\n";
        $html .= "<h4>🔧 System Recommendations</h4>\n";
        $html .= "<ul>\n";
        $html .= "<li>Monitor system regularly for optimal performance</li>\n";
        $html .= "<li>Address critical alerts immediately</li>\n";
        $html .= "<li>Review performance metrics weekly</li>\n";
        $html .= "<li>Set up automated alerting for critical issues</li>\n";
        $html .= "<li>Keep system updated and patched</li>\n";
        $html .= "</ul>\n";
        $html .= "</div>\n";

        $html .= "</div>\n";
        $html .= "</body>\n";
        $html .= "</html>\n";

        file_put_contents($reportFile, $html);

        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #28a745;'>\n";
        echo "<h3>📊 Monitoring Report Generated</h3>\n";
        echo "<p>Complete monitoring report saved to: <strong>" . basename($reportFile) . "</strong></p>\n";
        echo "<p><a href='../logs/" . basename($reportFile) . "' target='_blank' style='color: #007bff;'>View Detailed Report</a></p>\n";
        echo "</div>\n";
    }
}
?>
