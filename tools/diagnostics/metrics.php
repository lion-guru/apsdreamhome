
        <?php
        /**
         * Metrics Collection Endpoint
         * Collects and returns system metrics
         */

        header('Content-Type: application/json');

        $metrics = [
            'timestamp' => date('c'),
            'uptime' => time() - $_SERVER['REQUEST_TIME'],
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
            $conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
            if ($conn) {
                $result = $conn->query('SHOW PROCESSLIST');
                $metrics['database']['connections'] = $result ? $result->num_rows : 0;

                $result = $conn->query('SHOW STATUS LIKE "Threads_connected"');
                if ($result && $row = $result->fetch_assoc()) {
                    $metrics['database']['threads_connected'] = (int)$row['Value'];
                }
            }
        } catch (Exception $e) {
            $metrics['database']['error'] = $e->getMessage();
        }

        echo json_encode($metrics);
        ?>