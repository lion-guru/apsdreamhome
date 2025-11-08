<?php
/**
 * APS Dream Home - Enhanced Database Optimization Script
 *
 * This script provides comprehensive database optimization including:
 * - Index analysis and creation
 * - Query performance optimization
 * - Table structure improvements
 * - Performance monitoring setup
 */

require_once __DIR__ . '/../config.php';

class EnhancedDatabaseOptimizer {

    private $conn;
    private $logFile;

    public function __construct() {
        $this->conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
        $this->logFile = __DIR__ . '/logs/enhanced_optimization.log';

        if (!$this->conn) {
            throw new Exception('Database connection not available');
        }
    }

    /**
     * Run complete database optimization
     */
    public function runOptimization() {
        $this->log('Starting enhanced database optimization...');

        $optimizations = [
            'add_performance_indexes' => 'Adding performance indexes',
            'create_query_indexes' => 'Creating query optimization indexes',
            'optimize_table_engines' => 'Optimizing table engines',
            'setup_performance_schema' => 'Setting up performance monitoring',
            'analyze_table_sizes' => 'Analyzing table sizes and growth',
            'create_maintenance_jobs' => 'Creating maintenance procedures',
        ];

        $results = [];
        $totalSteps = count($optimizations);
        $currentStep = 0;

        echo "<div class='optimization-progress'>";
        echo "<h2>🔧 Database Optimization Progress</h2>";
        echo "<div class='progress-bar'><div class='progress' style='width: 0%'></div></div>";
        echo "<div class='step-info'>Initializing...</div>";
        echo "</div>";

        foreach ($optimizations as $method => $description) {
            $currentStep++;
            $progress = ($currentStep / $totalSteps) * 100;

            echo "<script>document.querySelector('.progress').style.width='{$progress}%'; document.querySelector('.step-info').textContent='{$description}...';</script>";
            if (ob_get_level()) ob_flush();
            flush();

            try {
                $this->log("Running: {$description}");
                $result = $this->$method();
                $results[$method] = $result;
                $this->log("Completed: {$method} - " . json_encode($result));
            } catch (Exception $e) {
                $this->log("Error in {$method}: " . $e->getMessage());
                $results[$method] = ['error' => $e->getMessage()];
            }
        }

        echo "<script>document.querySelector('.step-info').textContent='Optimization completed!';</script>";
        if (ob_get_level()) ob_flush();
        flush();

        $this->log('Enhanced database optimization completed');
        return $results;
    }

    /**
     * Add performance indexes for frequently queried columns
     */
    private function addPerformanceIndexes() {
        $indexes = [
            // Property table optimizations
            'property' => [
                'idx_property_status_type' => 'CREATE INDEX idx_property_status_type ON property (status, type)',
                'idx_property_price_range' => 'CREATE INDEX idx_property_price_range ON property (price, bedroom, bathroom)',
                'idx_property_location_search' => 'CREATE INDEX idx_property_location_search ON property (city, state, location(50))',
                'idx_property_featured_date' => 'CREATE INDEX idx_property_featured_date ON property (isFeatured, date DESC)',
                'idx_property_user_status' => 'CREATE INDEX idx_property_user_status ON property (uid, status)',
            ],
            // User table optimizations
            'users' => [
                'idx_users_status_type' => 'CREATE INDEX idx_users_status_type ON users (status, utype)',
                'idx_users_email_login' => 'CREATE INDEX idx_users_email_login ON users (email, status, last_login)',
                'idx_users_created_recent' => 'CREATE INDEX idx_users_created_recent ON users (created_at DESC)',
                'idx_users_login_activity' => 'CREATE INDEX idx_users_login_activity ON users (last_login DESC, status)',
            ],
            // Bookings table optimizations
            'bookings' => [
                'idx_bookings_status_date' => 'CREATE INDEX idx_bookings_status_date ON bookings (status, booking_date DESC)',
                'idx_bookings_payment_status' => 'CREATE INDEX idx_bookings_payment_status ON bookings (payment_status, status)',
                'idx_bookings_customer_search' => 'CREATE INDEX idx_bookings_customer_search ON bookings (customer_name, customer_email)',
                'idx_bookings_property_date' => 'CREATE INDEX idx_bookings_property_date ON bookings (property_id, booking_date DESC)',
                'idx_bookings_amount_range' => 'CREATE INDEX idx_bookings_amount_range ON bookings (amount, paid_amount)',
            ],
            // Associates table optimizations
            'associates' => [
                'idx_associates_level_business' => 'CREATE INDEX idx_associates_level_business ON associates (level, total_business DESC)',
                'idx_associates_sponsor_hierarchy' => 'CREATE INDEX idx_associates_sponsor_hierarchy ON associates (sponsor_id, level)',
                'idx_associates_referral_code' => 'CREATE INDEX idx_associates_referral_code ON associates (referral_code)',
                'idx_associates_monthly_business' => 'CREATE INDEX idx_associates_monthly_business ON associates (current_month_business DESC, level)',
            ],
            // Commission transactions optimizations
            'commission_transactions' => [
                'idx_commission_associate_date' => 'CREATE INDEX idx_commission_associate_date ON commission_transactions (associate_id, transaction_date DESC)',
                'idx_commission_status_amount' => 'CREATE INDEX idx_commission_status_amount ON commission_transactions (status, commission_amount DESC)',
                'idx_commission_upline_hierarchy' => 'CREATE INDEX idx_commission_upline_hierarchy ON commission_transactions (upline_id, commission_amount DESC)',
                'idx_commission_date_range' => 'CREATE INDEX idx_commission_date_range ON commission_transactions (transaction_date DESC, status)',
            ]
        ];

        $addedIndexes = [];

        foreach ($indexes as $table => $tableIndexes) {
            if ($this->tableExists($table)) {
                foreach ($tableIndexes as $indexName => $sql) {
                    try {
                        if (!$this->indexExists($table, $indexName)) {
                            $this->conn->query($sql);
                            $addedIndexes[] = "Added index {$indexName} on {$table}";
                        }
                    } catch (Exception $e) {
                        $this->log("Error adding index {$indexName} on {$table}: " . $e->getMessage());
                    }
                }
            }
        }

        return $addedIndexes;
    }

    /**
     * Create specialized indexes for complex queries
     */
    private function createQueryIndexes() {
        $queryIndexes = [
            // Full-text search indexes
            'property' => [
                'idx_property_fulltext_search' => 'CREATE FULLTEXT INDEX idx_property_fulltext_search ON property (title, pcontent, feature, location)',
            ],
            'property_location' => [
                'idx_property_location' => 'CREATE SPATIAL INDEX idx_property_location ON property (location)',
            ]
        ];

        $createdIndexes = [];

        foreach ($queryIndexes as $table => $tableIndexes) {
            if ($this->tableExists($table)) {
                foreach ($tableIndexes as $indexName => $sql) {
                    try {
                        if (!$this->indexExists($table, $indexName)) {
                            $this->conn->query($sql);
                            $createdIndexes[] = "Added query index {$indexName} on {$table}";
                        }
                    } catch (Exception $e) {
                        $this->log("Error adding query index {$indexName}: " . $e->getMessage());
                    }
                }
            }
        }

        return $createdIndexes;
    }

    /**
     * Optimize table engines and structure
     */
    private function optimizeTableEngines() {
        $optimizations = [];

        // Convert tables to InnoDB if not already
        $innodbConversions = [
            'property', 'bookings', 'users', 'associates', 'commission_transactions',
            'contact_backup', 'career_applications', 'feedback'
        ];

        foreach ($innodbConversions as $table) {
            if ($this->tableExists($table)) {
                try {
                    $result = $this->conn->query("SHOW TABLE STATUS LIKE '{$table}'");
                    if ($result && $row = $result->fetch_assoc()) {
                        if ($row['Engine'] !== 'InnoDB') {
                            $this->conn->query("ALTER TABLE {$table} ENGINE=InnoDB");
                            $optimizations[] = "Converted {$table} to InnoDB engine";
                        }
                    }
                } catch (Exception $e) {
                    $this->log("Error converting {$table} to InnoDB: " . $e->getMessage());
                }
            }
        }

        // Optimize character sets
        $charsetOptimizations = [
            'property' => 'ALTER TABLE property CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            'bookings' => 'ALTER TABLE bookings CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            'users' => 'ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            'associates' => 'ALTER TABLE associates CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
        ];

        foreach ($charsetOptimizations as $table => $sql) {
            if ($this->tableExists($table)) {
                try {
                    $this->conn->query($sql);
                    $optimizations[] = "Optimized charset for {$table}";
                } catch (Exception $e) {
                    $this->log("Error optimizing charset for {$table}: " . $e->getMessage());
                }
            }
        }

        return $optimizations;
    }

    /**
     * Set up performance monitoring
     */
    private function setupPerformanceSchema() {
        $monitoringSetup = [];

        // Enable performance schema if not enabled
        try {
            $result = $this->conn->query("SHOW VARIABLES LIKE 'performance_schema'");
            if ($result && $row = $result->fetch_assoc()) {
                if ($row['Value'] === 'OFF') {
                    $this->conn->query("SET GLOBAL performance_schema = ON");
                    $monitoringSetup[] = "Enabled Performance Schema";
                }
            }
        } catch (Exception $e) {
            $this->log("Error setting up performance schema: " . $e->getMessage());
        }

        // Create performance monitoring table
        $createMonitoringTable = "
            CREATE TABLE IF NOT EXISTS performance_metrics (
                id INT PRIMARY KEY AUTO_INCREMENT,
                metric_name VARCHAR(100) NOT NULL,
                metric_value DECIMAL(15,4),
                metric_type ENUM('counter', 'gauge', 'histogram') DEFAULT 'gauge',
                recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_metric_name (metric_name),
                INDEX idx_metric_time (recorded_at),
                INDEX idx_metric_type (metric_type)
            )
        ";

        try {
            $this->conn->query($createMonitoringTable);
            $monitoringSetup[] = "Created performance_metrics table";
        } catch (Exception $e) {
            $this->log("Error creating performance_metrics table: " . $e->getMessage());
        }

        return $monitoringSetup;
    }

    /**
     * Analyze table sizes and growth patterns
     */
    private function analyzeTableSizes() {
        $analysis = [];

        $tables = $this->getAllTables();
        $totalSize = 0;

        foreach ($tables as $table) {
            $result = $this->conn->query("SHOW TABLE STATUS LIKE '{$table}'");
            if ($result && $row = $result->fetch_assoc()) {
                $tableSize = $row['Data_length'] + $row['Index_length'];
                $totalSize += $tableSize;

                $analysis[$table] = [
                    'rows' => $row['Rows'],
                    'data_size' => $row['Data_length'],
                    'index_size' => $row['Index_length'],
                    'total_size' => $tableSize,
                    'engine' => $row['Engine'],
                    'collation' => $row['Collation'],
                ];
            }
        }

        $analysis['total_database_size'] = $totalSize;
        $analysis['largest_tables'] = $this->getLargestTables($analysis);
        $analysis['growth_recommendations'] = $this->getGrowthRecommendations($analysis);

        return $analysis;
    }

    /**
     * Create database maintenance procedures
     */
    private function createMaintenanceJobs() {
        $procedures = [];

        // Create table optimization procedure
        $optimizeProcedure = "
            CREATE PROCEDURE IF NOT EXISTS optimize_all_tables()
            BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE table_name VARCHAR(255);

                DECLARE table_cursor CURSOR FOR
                    SELECT TABLE_NAME FROM information_schema.TABLES
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE';

                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                OPEN table_cursor;

                read_loop: LOOP
                    FETCH table_cursor INTO table_name;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;

                    SET @sql = CONCAT('OPTIMIZE TABLE ', table_name);
                    PREPARE stmt FROM @sql;
                    EXECUTE stmt;
                    DEALLOCATE PREPARE stmt;
                END LOOP;

                CLOSE table_cursor;

                SELECT 'All tables optimized successfully' AS result;
            END
        ";

        try {
            $this->conn->query("DROP PROCEDURE IF EXISTS optimize_all_tables");
            $this->conn->query($optimizeProcedure);
            $procedures[] = "Created optimize_all_tables procedure";
        } catch (Exception $e) {
            $this->log("Error creating optimize_all_tables procedure: " . $e->getMessage());
        }

        return $procedures;
    }

    /**
     * Get largest tables by size
     */
    private function getLargestTables($analysis) {
        arsort($analysis); // Sort by total_size descending
        return array_slice($analysis, 0, 10, true);
    }

    /**
     * Get growth recommendations based on analysis
     */
    private function getGrowthRecommendations($analysis) {
        $recommendations = [];

        foreach ($analysis as $table => $data) {
            if ($table === 'total_database_size') continue;

            // Check for tables with high row counts
            if ($data['rows'] > 100000) {
                $recommendations[] = "Consider partitioning table {$table} (current rows: {$data['rows']})";
            }

            // Check for large index sizes
            if ($data['index_size'] > $data['data_size']) {
                $recommendations[] = "Review indexes for table {$table} (index size larger than data size)";
            }
        }

        return $recommendations;
    }

    // Helper methods (same as before)
    private function getAllTables() {
        $result = $this->conn->query("SHOW TABLES");
        $tables = [];
        if ($result) {
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
        }
        return $tables;
    }

    private function tableExists($table) {
        $result = $this->conn->query("SHOW TABLES LIKE '{$table}'");
        return $result && $result->num_rows > 0;
    }

    private function indexExists($table, $indexName) {
        $indexes = $this->getTableIndexes($table);
        return in_array($indexName, $indexes);
    }

    private function getTableIndexes($table) {
        $result = $this->conn->query("SHOW INDEX FROM {$table}");
        $indexes = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $indexes[] = $row['Key_name'];
            }
        }
        return array_unique($indexes);
    }

    public function getLogFile() {
        return $this->logFile;
    }
}

// Run optimization if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $optimizer = new EnhancedDatabaseOptimizer();
        $results = $optimizer->runOptimization();

        echo "<div class='section'>";
        echo "<h2>✅ Database Optimization Complete!</h2>";
        echo "<p>Optimization results have been logged to: " . $optimizer->getLogFile() . "</p>";
        echo "<pre>" . json_encode($results, JSON_PRETTY_PRINT) . "</pre>";
        echo "</div>";

    } catch (Exception $e) {
        echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
    }
}
?>
