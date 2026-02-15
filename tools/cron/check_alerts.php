
        <?php
        /**
         * Performance Alert System
         * Monitors metrics and sends alerts when thresholds are exceeded
         */

        class PerformanceAlerts {

            private $conn;
            private $thresholds = [
                'response_time' => 1000, // ms
                'error_rate' => 0.1, // 10%
                'memory_usage' => 0.8, // 80%
                'database_connections' => 20,
            ];

            public function __construct($conn) {
                $this->conn = $conn;
            }

            public function checkAlerts() {
                $alerts = [];

                // Check response time
                $alerts = array_merge($alerts, $this->checkResponseTime());

                // Check error rate
                $alerts = array_merge($alerts, $this->checkErrorRate());

                // Check memory usage
                $alerts = array_merge($alerts, $this->checkMemoryUsage());

                // Check database connections
                $alerts = array_merge($alerts, $this->checkDatabaseConnections());

                if (!empty($alerts)) {
                    $this->sendAlerts($alerts);
                }

                return $alerts;
            }

            private function checkResponseTime() {
                $alerts = [];
                $result = $this->conn->query("
                    SELECT AVG(response_time_ms) as avg_time
                    FROM api_metrics
                    WHERE timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ");

                if ($result && $row = $result->fetch_assoc()) {
                    if ($row['avg_time'] > $this->thresholds['response_time']) {
                        $alerts[] = [
                            'type' => 'warning',
                            'metric' => 'response_time',
                            'value' => round($row['avg_time'], 2),
                            'threshold' => $this->thresholds['response_time'],
                            'message' => 'Average response time exceeded threshold'
                        ];
                    }
                }

                return $alerts;
            }

            private function checkErrorRate() {
                $alerts = [];
                $result = $this->conn->query("
                    SELECT
                        COUNT(CASE WHEN status_code >= 400 THEN 1 END) as errors,
                        COUNT(*) as total
                    FROM api_metrics
                    WHERE timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ");

                if ($result && $row = $result->fetch_assoc()) {
                    $errorRate = $row['total'] > 0 ? $row['errors'] / $row['total'] : 0;

                    if ($errorRate > $this->thresholds['error_rate']) {
                        $alerts[] = [
                            'type' => 'error',
                            'metric' => 'error_rate',
                            'value' => round($errorRate, 3),
                            'threshold' => $this->thresholds['error_rate'],
                            'message' => 'Error rate exceeded threshold'
                        ];
                    }
                }

                return $alerts;
            }

            private function checkMemoryUsage() {
                $alerts = [];
                $memoryUsage = memory_get_usage(true) / 1024 / 1024 / 1024; // GB

                if ($memoryUsage > $this->thresholds['memory_usage'] * 2) { // Assuming 2GB limit
                    $alerts[] = [
                        'type' => 'warning',
                        'metric' => 'memory_usage',
                        'value' => round($memoryUsage, 2),
                        'threshold' => $this->thresholds['memory_usage'] * 2,
                        'message' => 'Memory usage is high'
                    ];
                }

                return $alerts;
            }

            private function checkDatabaseConnections() {
                $alerts = [];
                $result = $this->conn->query('SHOW STATUS LIKE "Threads_connected"');

                if ($result && $row = $result->fetch_assoc()) {
                    $connections = (int)$row['Value'];

                    if ($connections > $this->thresholds['database_connections']) {
                        $alerts[] = [
                            'type' => 'warning',
                            'metric' => 'database_connections',
                            'value' => $connections,
                            'threshold' => $this->thresholds['database_connections'],
                            'message' => 'Database connections exceeded threshold'
                        ];
                    }
                }

                return $alerts;
            }

            private function sendAlerts($alerts) {
                foreach ($alerts as $alert) {
                    // Log alert
                    error_log('Performance Alert: ' . json_encode($alert));

                    // TODO: Send email/Slack notification
                    // $this->sendEmailAlert($alert);
                    // $this->sendSlackAlert($alert);
                }
            }
        }

        // Run alerts check
        $conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
        if ($conn) {
            $alerts = new PerformanceAlerts($conn);
            $alerts->checkAlerts();
        }

        echo 'Alert check completed';
        ?>