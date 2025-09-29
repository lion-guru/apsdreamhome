<?php
/**
 * Log Aggregator
 * Collects and analyzes logs from multiple sources
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../security_logger.php';
require_once __DIR__ . '/../notification/email_manager.php';

class LogAggregator {
    private $con;
    private $logger;
    private $emailManager;
    private $sources = [];
    private $patterns = [];
    private $alertThresholds = [];

    public function __construct($database_connection = null, $security_logger = null, $email_manager = null) {
        $this->con = $database_connection ?? getDbConnection();
        $this->logger = $security_logger ?? new SecurityLogger();
        $this->emailManager = $email_manager ?? new EmailManager();
        $this->initializeDatabase();
        $this->loadPatterns();
        $this->loadAlertThresholds();
    }

    /**
     * Initialize database tables
     */
    private function initializeDatabase() {
        // Create aggregated_logs table
        $query = "CREATE TABLE IF NOT EXISTS aggregated_logs (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            source VARCHAR(50) NOT NULL,
            level VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            context JSON,
            pattern_id INT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_source (source),
            INDEX idx_level (level),
            INDEX idx_pattern (pattern_id),
            INDEX idx_timestamp (timestamp)
        )";
        $this->con->query($query);

        // Create log_patterns table
        $query = "CREATE TABLE IF NOT EXISTS log_patterns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            pattern TEXT NOT NULL,
            severity VARCHAR(20) NOT NULL,
            description TEXT,
            alert_threshold INT DEFAULT 0,
            alert_window INT DEFAULT 3600,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->con->query($query);

        // Create log_alerts table
        $query = "CREATE TABLE IF NOT EXISTS log_alerts (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            pattern_id INT NOT NULL,
            occurrence_count INT NOT NULL,
            first_seen TIMESTAMP NOT NULL,
            last_seen TIMESTAMP NOT NULL,
            status VARCHAR(20) DEFAULT 'new',
            notified BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (pattern_id) REFERENCES log_patterns(id),
            INDEX idx_status (status),
            INDEX idx_pattern_status (pattern_id, status)
        )";
        $this->con->query($query);
    }

    /**
     * Load log patterns from database
     */
    private function loadPatterns() {
        $query = "SELECT * FROM log_patterns";
        $result = $this->con->query($query);
        
        while ($row = $result->fetch_assoc()) {
            $this->patterns[$row['id']] = $row;
        }
    }

    /**
     * Load alert thresholds
     */
    private function loadAlertThresholds() {
        $this->alertThresholds = [
            'error' => [
                'count' => 10,
                'window' => 300  // 5 minutes
            ],
            'warning' => [
                'count' => 50,
                'window' => 3600  // 1 hour
            ],
            'security' => [
                'count' => 5,
                'window' => 300  // 5 minutes
            ]
        ];
    }

    /**
     * Register a log source
     */
    public function registerSource($name, $path, $format = 'standard') {
        $this->sources[$name] = [
            'path' => $path,
            'format' => $format,
            'last_position' => 0
        ];
    }

    /**
     * Aggregate logs from all sources
     */
    public function aggregate() {
        foreach ($this->sources as $name => $source) {
            $this->processSource($name, $source);
        }
        
        $this->analyzePatterns();
        $this->checkThresholds();
        $this->generateReport();
    }

    /**
     * Process a single log source
     */
    private function processSource($name, $source) {
        $handle = fopen($source['path'], 'r');
        if ($handle === false) {
            $this->logger->error("Failed to open log file", [
                'source' => $name,
                'path' => $source['path']
            ]);
            return;
        }

        // Seek to last position
        fseek($handle, $source['last_position']);

        while (($line = fgets($handle)) !== false) {
            $entry = $this->parseLine($line, $source['format']);
            if ($entry) {
                $this->storeLine($name, $entry);
            }
        }

        // Update last position
        $this->sources[$name]['last_position'] = ftell($handle);
        fclose($handle);
    }

    /**
     * Parse a log line based on format
     */
    private function parseLine($line, $format) {
        switch ($format) {
            case 'standard':
                return $this->parseStandardFormat($line);
            case 'apache':
                return $this->parseApacheFormat($line);
            case 'nginx':
                return $this->parseNginxFormat($line);
            default:
                return null;
        }
    }

    /**
     * Parse standard log format
     */
    private function parseStandardFormat($line) {
        $pattern = '/// SECURITY: Removed potentially dangerous code(.*?)\] // SECURITY: Removed potentially dangerous code(.*?)\] (.*?)(?:\s+({.*})?)?$/';
        if (preg_match($pattern, $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => $matches[2],
                'message' => $matches[3],
                'context' => isset($matches[4]) ? json_decode($matches[4], true) : null
            ];
        }
        return null;
    }

    /**
     * Parse Apache log format
     */
    private function parseApacheFormat($line) {
        // Implement Apache log parsing
        return null;
    }

    /**
     * Parse Nginx log format
     */
    private function parseNginxFormat($line) {
        // Implement Nginx log parsing
        return null;
    }

    /**
     * Store log line in database
     */
    private function storeLine($source, $entry) {
        $query = "INSERT INTO aggregated_logs 
                 (source, level, message, context, pattern_id, timestamp) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->con->prepare($query);
        $context = $entry['context'] ? json_encode($entry['context']) : null;
        $patternId = $this->findMatchingPattern($entry['message']);
        $timestamp = date('Y-m-d H:i:s', strtotime($entry['timestamp']));
        
        $stmt->bind_param("ssssss", 
            $source, 
            $entry['level'], 
            $entry['message'], 
            $context,
            $patternId,
            $timestamp
        );
        
        $stmt->execute();
    }

    /**
     * Find matching pattern for a log message
     */
    private function findMatchingPattern($message) {
        foreach ($this->patterns as $id => $pattern) {
            if (preg_match($pattern['pattern'], $message)) {
                return $id;
            }
        }
        return null;
    }

    /**
     * Analyze log patterns
     */
    private function analyzePatterns() {
        $query = "SELECT 
                    pattern_id,
                    COUNT(*) as count,
                    MIN(timestamp) as first_seen,
                    MAX(timestamp) as last_seen
                 FROM aggregated_logs
                 WHERE pattern_id IS NOT NULL
                 AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 GROUP BY pattern_id
                 HAVING count >= (
                    SELECT alert_threshold 
                    FROM log_patterns 
                    WHERE id = pattern_id
                 )";
        
        $result = $this->con->query($query);
        
        while ($row = $result->fetch_assoc()) {
            $this->createAlert($row);
        }
    }

    /**
     * Create an alert for pattern occurrence
     */
    private function createAlert($data) {
        $query = "INSERT INTO log_alerts 
                 (pattern_id, occurrence_count, first_seen, last_seen) 
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 occurrence_count = VALUES(occurrence_count),
                 last_seen = VALUES(last_seen)";
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("iiss", 
            $data['pattern_id'],
            $data['count'],
            $data['first_seen'],
            $data['last_seen']
        );
        
        if ($stmt->execute()) {
            $this->notifyAlert($stmt->insert_id, $data);
        }
    }

    /**
     * Check alert thresholds
     */
    private function checkThresholds() {
        foreach ($this->alertThresholds as $level => $threshold) {
            $query = "SELECT COUNT(*) as count 
                     FROM aggregated_logs 
                     WHERE level = ? 
                     AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)";
            
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("si", $level, $threshold['window']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result['count'] >= $threshold['count']) {
                $this->notifyThresholdExceeded($level, $result['count'], $threshold);
            }
        }
    }

    /**
     * Notify about threshold exceeded
     */
    private function notifyThresholdExceeded($level, $count, $threshold) {
        $this->emailManager->sendSecurityAlert(
            "Log Threshold Exceeded for {$level}",
            "Received {$count} {$level} logs in the last " . 
            ($threshold['window'] / 60) . " minutes.",
            'high'
        );
    }

    /**
     * Notify about pattern alert
     */
    private function notifyAlert($alertId, $data) {
        $pattern = $this->patterns[$data['pattern_id']];
        
        $this->emailManager->sendSecurityAlert(
            "Log Pattern Alert: {$pattern['name']}",
            "Pattern occurred {$data['count']} times between {$data['first_seen']} and {$data['last_seen']}",
            $pattern['severity']
        );
        
        // Mark alert as notified
        $query = "UPDATE log_alerts SET notified = TRUE WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $alertId);
        $stmt->execute();
    }

    /**
     * Generate aggregation report
     */
    private function generateReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [],
            'alerts' => []
        ];

        // Get summary by source and level
        $query = "SELECT 
                    source,
                    level,
                    COUNT(*) as count
                 FROM aggregated_logs
                 WHERE timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 GROUP BY source, level";
        
        $result = $this->con->query($query);
        while ($row = $result->fetch_assoc()) {
            $report['summary'][] = $row;
        }

        // Get active alerts
        $query = "SELECT 
                    a.*,
                    p.name as pattern_name,
                    p.severity
                 FROM log_alerts a
                 JOIN log_patterns p ON a.pattern_id = p.id
                 WHERE a.status = 'new'";
        
        $result = $this->con->query($query);
        while ($row = $result->fetch_assoc()) {
            $report['alerts'][] = $row;
        }

        // Save report
        $reportFile = __DIR__ . '/../../logs/aggregation_' . date('Y-m-d_H') . '.json';
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->logger->info("Generated log aggregation report", [
            'report_file' => $reportFile
        ]);
    }

    /**
     * Add a new log pattern
     */
    public function addPattern($name, $pattern, $severity, $description, $threshold = 0, $window = 3600) {
        $query = "INSERT INTO log_patterns 
                 (name, pattern, severity, description, alert_threshold, alert_window) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("ssssii", 
            $name,
            $pattern,
            $severity,
            $description,
            $threshold,
            $window
        );
        
        if ($stmt->execute()) {
            $this->patterns[$stmt->insert_id] = [
                'id' => $stmt->insert_id,
                'name' => $name,
                'pattern' => $pattern,
                'severity' => $severity,
                'description' => $description,
                'alert_threshold' => $threshold,
                'alert_window' => $window
            ];
            return true;
        }
        
        return false;
    }
}

// Create global log aggregator instance
$logAggregator = new LogAggregator($con ?? null, $securityLogger ?? null, $emailManager ?? null);

