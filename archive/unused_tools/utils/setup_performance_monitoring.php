<?php
/**
 * APS Dream Home - Performance Monitoring Setup
 *
 * This script sets up comprehensive performance monitoring including:
 * - Application Performance Monitoring (APM)
 * - Database query monitoring
 * - API response time tracking
 * - Error rate monitoring
 * - Resource usage monitoring
 */

class PerformanceMonitor {

    private $logDir;
    private $metricsFile;
    private $conn;

    public function __construct() {
        $this->logDir = __DIR__ . '/../logs';
        $this->metricsFile = $this->logDir . '/performance_metrics.json';
        $this->conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function getLogDir() {
        return $this->logDir;
    }

    /**
     * Setup complete performance monitoring system
     */
    public function setupMonitoring() {
        $this->log('Setting up performance monitoring system...');

        $setupTasks = [
            'createMetricsTables' => 'Creating metrics tables',
            'createAlertSystem' => 'Creating alert system',
            'createMonitoringEndpoints' => 'Setting up monitoring dashboard',
        ];

        $results = [];

        foreach ($setupTasks as $method => $description) {
            try {
                $this->log("Running: {$description}");
                $result = $this->$method();
                $results[$method] = $result;
                $this->log("Completed: {$method}");
            } catch (Exception $e) {
                $this->log("Error in {$method}: " . $e->getMessage());
                $results[$method] = ['error' => $e->getMessage()];
            }
        }

        $this->log('Performance monitoring setup completed');
        return $results;
    }

    /**
     * Create performance metrics tables
     */
    private function createMetricsTables() {
        $tables = [];

        // Application metrics table
        $appMetricsTable = "
            CREATE TABLE IF NOT EXISTS app_metrics (
                id INT PRIMARY KEY AUTO_INCREMENT,
                metric_name VARCHAR(100) NOT NULL,
                metric_value DECIMAL(15,4),
                metric_type ENUM('counter', 'gauge', 'histogram', 'timer') DEFAULT 'gauge',
                tags JSON,
                recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_metric_name (metric_name),
                INDEX idx_metric_time (recorded_at),
                INDEX idx_metric_type (metric_type),
                INDEX idx_metric_tags (tags(255))
            ) ENGINE=InnoDB
        ";

        try {
            if ($this->conn) {
                $this->conn->query($appMetricsTable);
                $tables[] = 'app_metrics table created';
            }
        } catch (Exception $e) {
            $this->log("Error creating app_metrics table: " . $e->getMessage());
        }

        // API performance table
        $apiMetricsTable = "
            CREATE TABLE IF NOT EXISTS api_metrics (
                id INT PRIMARY KEY AUTO_INCREMENT,
                endpoint VARCHAR(255) NOT NULL,
                method VARCHAR(10) NOT NULL,
                response_time_ms INT NOT NULL,
                status_code INT NOT NULL,
                request_size_bytes INT,
                response_size_bytes INT,
                user_agent VARCHAR(500),
                ip_address VARCHAR(45),
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_endpoint (endpoint),
                INDEX idx_method (method),
                INDEX idx_timestamp (timestamp),
                INDEX idx_status_code (status_code),
                INDEX idx_response_time (response_time_ms)
            ) ENGINE=InnoDB
        ";

        try {
            if ($this->conn) {
                $this->conn->query($apiMetricsTable);
                $tables[] = 'api_metrics table created';
            }
        } catch (Exception $e) {
            $this->log("Error creating api_metrics table: " . $e->getMessage());
        }

        // Database performance table
        $dbMetricsTable = "
            CREATE TABLE IF NOT EXISTS db_metrics (
                id INT PRIMARY KEY AUTO_INCREMENT,
                query_type ENUM('SELECT', 'INSERT', 'UPDATE', 'DELETE') NOT NULL,
                query_table VARCHAR(100),
                execution_time_ms DECIMAL(10,3) NOT NULL,
                rows_affected INT,
                query_hash VARCHAR(64),
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_query_type (query_type),
                INDEX idx_execution_time (execution_time_ms),
                INDEX idx_timestamp (timestamp),
                INDEX idx_query_hash (query_hash)
            ) ENGINE=InnoDB
        ";

        try {
            if ($this->conn) {
                $this->conn->query($dbMetricsTable);
                $tables[] = 'db_metrics table created';
            }
        } catch (Exception $e) {
            $this->log("Error creating db_metrics table: " . $e->getMessage());
        }

        return $tables;
    }

    /**
     * Setup APM configuration
     */
    private function setupApmConfig() {
        $config = [
            'apm' => [
                'enabled' => true,
                'sample_rate' => 1.0, // Sample 100% of requests in development
                'slow_query_threshold' => 100, // ms
                'error_rate_threshold' => 0.05, // 5%
                'memory_limit' => '128M',
                'max_execution_time' => 30, // seconds
            ],
            'monitoring' => [
                'database' => true,
                'api' => true,
                'performance' => true,
                'errors' => true,
                'resources' => true,
            ],
            'alerts' => [
                'email' => 'admin@apsdreamhome.com',
                'slack_webhook' => '',
                'thresholds' => [
                    'response_time' => 1000, // ms
                    'error_rate' => 0.1, // 10%
                    'memory_usage' => 0.8, // 80%
                    'database_connections' => 10,
                ]
            ]
        ];

        $configFile = __DIR__ . '/../config/apm.php';
        $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";

        file_put_contents($configFile, $configContent);

        return ['APM configuration created at: ' . $configFile];
    }

    /**
     * Create monitoring endpoints
     */
    private function createMonitoringEndpoints() {
        $endpoints = [];

        // Health check endpoint
        $healthEndpoint = "
        <?php
        /**
         * Health Check Endpoint
         * Returns system health status
         */

        header('Content-Type: application/json');

        \$health = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'services' => []
        ];

        // Check database
        try {
            \$conn = \$GLOBALS['conn'] ?? \$GLOBALS['con'] ?? null;
            if (\$conn && \$conn->ping()) {
                \$health['services']['database'] = 'healthy';
            } else {
                \$health['services']['database'] = 'unhealthy';
                \$health['status'] = 'degraded';
            }
        } catch (Exception \$e) {
            \$health['services']['database'] = 'unhealthy';
            \$health['status'] = 'degraded';
        }

        // Check file system
        if (is_writable(__DIR__ . '/../logs')) {
            \$health['services']['filesystem'] = 'healthy';
        } else {
            \$health['services']['filesystem'] = 'unhealthy';
            \$health['status'] = 'degraded';
        }

        // Check memory usage
        \$memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
        \$health['services']['memory'] = \$memoryUsage < 100 ? 'healthy' : 'warning';
        \$health['memory_usage_mb'] = round(\$memoryUsage, 2);

        http_response_code(\$health['status'] === 'healthy' ? 200 : 503);
        echo json_encode(\$health);
        ?>";

        file_put_contents(__DIR__ . '/health_check.php', $healthEndpoint);
        $endpoints[] = 'Health check endpoint created';

        // Metrics endpoint
        $metricsEndpoint = "
        <?php
        /**
         * Metrics Collection Endpoint
         * Collects and returns system metrics
         */

        header('Content-Type: application/json');

        \$metrics = [
            'timestamp' => date('c'),
            'uptime' => time() - \$_SERVER['REQUEST_TIME'],
            'memory' => [
                'usage' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit')
            ],
            'cpu' => [
                'load' => sys_getloadavg()
            ],
            'database' => []
        ];

        // Database metrics
        try {
            \$conn = \$GLOBALS['conn'] ?? \$GLOBALS['con'] ?? null;
            if (\$conn) {
                \$result = \$conn->query('SHOW PROCESSLIST');
                \$metrics['database']['connections'] = \$result ? \$result->num_rows : 0;

                \$result = \$conn->query('SHOW STATUS LIKE \"Threads_connected\"');
                if (\$result && \$row = \$result->fetch_assoc()) {
                    \$metrics['database']['threads_connected'] = (int)\$row['Value'];
                }
            }
        } catch (Exception \$e) {
            \$metrics['database']['error'] = \$e->getMessage();
        }

        echo json_encode(\$metrics);
        ?>";

        file_put_contents(__DIR__ . '/metrics.php', $metricsEndpoint);
        $endpoints[] = 'Metrics endpoint created';

        return $endpoints;
    }

    /**
     * Setup cron jobs for monitoring
     */
    private function setupCronJobs() {
        $cronJobs = [];

        // Performance metrics collection cron
        $metricsCron = "
        <?php
        /**
         * Performance Metrics Collection Cron Job
         * Runs every 5 minutes to collect system metrics
         */

        require_once __DIR__ . '/../config.php';

        \$conn = \$GLOBALS['conn'] ?? \$GLOBALS['con'] ?? null;
        if (!\$conn) {
            die('Database connection not available');
        }

        // Collect basic metrics
        \$metrics = [
            'response_time' => rand(50, 200), // Simulated
            'memory_usage' => memory_get_usage(true) / 1024 / 1024,
            'error_rate' => rand(0, 5) / 100,
            'active_users' => rand(10, 100),
        ];

        foreach (\$metrics as \$name => \$value) {
            \$stmt = \$conn->prepare('INSERT INTO app_metrics (metric_name, metric_value, metric_type) VALUES (?, ?, ?)');
            \$stmt->bind_param('sds', \$name, \$value, \$name === 'error_rate' ? 'counter' : 'gauge');
            \$stmt->execute();
        }

        echo 'Metrics collected successfully';
        ?>";

        file_put_contents(__DIR__ . '/cron/collect_metrics.php', $metricsCron);
        $cronJobs[] = 'Metrics collection cron job created';

        // Database optimization cron
        $optimizeCron = "
        <?php
        /**
         * Database Optimization Cron Job
         * Runs daily to optimize database performance
         */

        require_once __DIR__ . '/../config.php';

        \$conn = \$GLOBALS['conn'] ?? \$GLOBALS['con'] ?? null;
        if (!\$conn) {
            die('Database connection not available');
        }

        // Optimize tables
        \$result = \$conn->query('SHOW TABLES');
        while (\$result && \$row = \$result->fetch_array()) {
            \$table = \$row[0];
            \$conn->query(\"OPTIMIZE TABLE {\$table}\");
        }

        // Clean old metrics (keep last 30 days)
        \$conn->query('DELETE FROM app_metrics WHERE recorded_at < DATE_SUB(NOW(), INTERVAL 30 DAY)');
        \$conn->query('DELETE FROM api_metrics WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)');
        \$conn->query('DELETE FROM db_metrics WHERE timestamp < DATE_SUB(NOW(), INTERVAL 7 DAY)');

        echo 'Database optimization completed';
        ?>";

        file_put_contents(__DIR__ . '/cron/optimize_db.php', $optimizeCron);
        $cronJobs[] = 'Database optimization cron job created';

        return $cronJobs;
    }

    /**
     * Create alert system
     */
    private function createAlertSystem() {
        $alertSystem = "
        <?php
        /**
         * Performance Alert System
         * Monitors metrics and sends alerts when thresholds are exceeded
         */

        class PerformanceAlerts {

            private \$conn;
            private \$thresholds = [
                'response_time' => 1000, // ms
                'error_rate' => 0.1, // 10%
                'memory_usage' => 0.8, // 80%
                'database_connections' => 20,
            ];

            public function __construct(\$conn) {
                \$this->conn = \$conn;
            }

            public function checkAlerts() {
                \$alerts = [];

                // Check response time
                \$alerts = array_merge(\$alerts, \$this->checkResponseTime());

                // Check error rate
                \$alerts = array_merge(\$alerts, \$this->checkErrorRate());

                // Check memory usage
                \$alerts = array_merge(\$alerts, \$this->checkMemoryUsage());

                // Check database connections
                \$alerts = array_merge(\$alerts, \$this->checkDatabaseConnections());

                if (!empty(\$alerts)) {
                    \$this->sendAlerts(\$alerts);
                }

                return \$alerts;
            }

            private function checkResponseTime() {
                \$alerts = [];
                \$result = \$this->conn->query(\"
                    SELECT AVG(response_time_ms) as avg_time
                    FROM api_metrics
                    WHERE timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                \");

                if (\$result && \$row = \$result->fetch_assoc()) {
                    if (\$row['avg_time'] > \$this->thresholds['response_time']) {
                        \$alerts[] = [
                            'type' => 'warning',
                            'metric' => 'response_time',
                            'value' => round(\$row['avg_time'], 2),
                            'threshold' => \$this->thresholds['response_time'],
                            'message' => 'Average response time exceeded threshold'
                        ];
                    }
                }

                return \$alerts;
            }

            private function checkErrorRate() {
                \$alerts = [];
                \$result = \$this->conn->query(\"
                    SELECT
                        COUNT(CASE WHEN status_code >= 400 THEN 1 END) as errors,
                        COUNT(*) as total
                    FROM api_metrics
                    WHERE timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                \");

                if (\$result && \$row = \$result->fetch_assoc()) {
                    \$errorRate = \$row['total'] > 0 ? \$row['errors'] / \$row['total'] : 0;

                    if (\$errorRate > \$this->thresholds['error_rate']) {
                        \$alerts[] = [
                            'type' => 'error',
                            'metric' => 'error_rate',
                            'value' => round(\$errorRate, 3),
                            'threshold' => \$this->thresholds['error_rate'],
                            'message' => 'Error rate exceeded threshold'
                        ];
                    }
                }

                return \$alerts;
            }

            private function checkMemoryUsage() {
                \$alerts = [];
                \$memoryUsage = memory_get_usage(true) / 1024 / 1024 / 1024; // GB

                if (\$memoryUsage > \$this->thresholds['memory_usage'] * 2) { // Assuming 2GB limit
                    \$alerts[] = [
                        'type' => 'warning',
                        'metric' => 'memory_usage',
                        'value' => round(\$memoryUsage, 2),
                        'threshold' => \$this->thresholds['memory_usage'] * 2,
                        'message' => 'Memory usage is high'
                    ];
                }

                return \$alerts;
            }

            private function checkDatabaseConnections() {
                \$alerts = [];
                \$result = \$this->conn->query('SHOW STATUS LIKE \"Threads_connected\"');

                if (\$result && \$row = \$result->fetch_assoc()) {
                    \$connections = (int)\$row['Value'];

                    if (\$connections > \$this->thresholds['database_connections']) {
                        \$alerts[] = [
                            'type' => 'warning',
                            'metric' => 'database_connections',
                            'value' => \$connections,
                            'threshold' => \$this->thresholds['database_connections'],
                            'message' => 'Database connections exceeded threshold'
                        ];
                    }
                }

                return \$alerts;
            }

            private function sendAlerts(\$alerts) {
                foreach (\$alerts as \$alert) {
                    // Log alert
                    error_log('Performance Alert: ' . json_encode(\$alert));

                    // TODO: Send email/Slack notification
                    // \$this->sendEmailAlert(\$alert);
                    // \$this->sendSlackAlert(\$alert);
                }
            }
        }

        // Run alerts check
        \$conn = \$GLOBALS['conn'] ?? \$GLOBALS['con'] ?? null;
        if (\$conn) {
            \$alerts = new PerformanceAlerts(\$conn);
            \$alerts->checkAlerts();
        }

        echo 'Alert check completed';
        ?>";

        file_put_contents(__DIR__ . '/cron/check_alerts.php', $alertSystem);

        return ['Alert system created'];
    }

    /**
     * Setup monitoring dashboard
     */
    private function setupDashboard() {
        $dashboard = "
        <?php
        /**
         * Performance Monitoring Dashboard
         * Displays real-time performance metrics
         */

        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Performance Dashboard - APS Dream Home</title>
            <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
                .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .metric { text-align: center; margin: 10px 0; }
                .metric-value { font-size: 2em; font-weight: bold; color: #3498db; }
                .metric-label { color: #666; font-size: 0.9em; }
                .status-healthy { color: #27ae60; }
                .status-warning { color: #f39c12; }
                .status-error { color: #e74c3c; }
                .refresh-btn { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
            </style>
        </head>
        <body>
            <h1>🚀 Performance Monitoring Dashboard</h1>
            <button class='refresh-btn' onclick='location.reload()'>Refresh</button>
            <div class='dashboard' id='dashboard'></div>

            <script>
                async function loadMetrics() {
                    try {
                        const [healthRes, metricsRes] = await Promise.all([
                            fetch('/api/health_check.php'),
                            fetch('/api/metrics.php')
                        ]);

                        const health = await healthRes.json();
                        const metrics = await metricsRes.json();

                        displayHealth(health);
                        displayMetrics(metrics);
                    } catch (error) {
                        console.error('Error loading metrics:', error);
                    }
                }

                function displayHealth(health) {
                    const dashboard = document.getElementById('dashboard');

                    const healthCard = document.createElement('div');
                    healthCard.className = 'card';
                    healthCard.innerHTML = \`
                        <h3>System Health</h3>
                        <div class='metric'>
                            <div class='metric-value status-\${health.status}'>\${health.status.toUpperCase()}</div>
                            <div class='metric-label'>Overall Status</div>
                        </div>
                        <div class='services'>
                            \${Object.entries(health.services).map(([service, status]) =>
                                \`<div>🟢 \${service}: \${status}</div>\`
                            ).join('')}
                        </div>
                    \`;

                    dashboard.appendChild(healthCard);
                }

                function displayMetrics(metrics) {
                    const dashboard = document.getElementById('dashboard');

                    // Memory usage chart
                    const memoryCard = document.createElement('div');
                    memoryCard.className = 'card';
                    memoryCard.innerHTML = \`
                        <h3>Memory Usage</h3>
                        <div class='metric'>
                            <div class='metric-value'>\${Math.round(metrics.memory.usage / 1024 / 1024)} MB</div>
                            <div class='metric-label'>Current Usage</div>
                        </div>
                        <canvas id='memoryChart' width='300' height='200'></canvas>
                    \`;

                    dashboard.appendChild(memoryCard);

                    // Create memory chart
                    const ctx = document.getElementById('memoryChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Used', 'Available'],
                            datasets: [{
                                data: [
                                    Math.round(metrics.memory.usage / 1024 / 1024),
                                    Math.max(0, 128 - Math.round(metrics.memory.usage / 1024 / 1024)) // Assuming 128MB limit
                                ],
                                backgroundColor: ['#3498db', '#ecf0f1']
                            }]
                        }
                    });
                }

                // Load metrics on page load and refresh every 30 seconds
                loadMetrics();
                setInterval(loadMetrics, 30000);
            </script>
        </body>
        </html>
        ?>";

        file_put_contents(__DIR__ . '/dashboard.php', $dashboard);

        return ['Performance dashboard created'];
    }

    /**
     * Log messages
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($this->logDir . '/performance_setup.log', $logMessage, FILE_APPEND);
        error_log($logMessage);
    }
}

// Run setup if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $monitor = new PerformanceMonitor();
        $results = $monitor->setupMonitoring();

        echo "<h1>✅ Performance Monitoring Setup Complete!</h1>";
        echo "<pre>" . json_encode($results, JSON_PRETTY_PRINT) . "</pre>";
        echo "<p>Setup log: " . $monitor->getLogDir() . "/performance_setup.log</p>";

    } catch (Exception $e) {
        echo "<h1>❌ Setup Error</h1>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}
?>
