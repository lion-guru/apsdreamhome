<?php
/**
 * Database Performance Optimizer
 * Analyzes and optimizes database queries and indexes
 */

namespace App\Core;

class DatabaseOptimizer
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Analyze database performance and suggest optimizations
     */
    public function analyzePerformance()
    {
        $analysis = [
            'tables' => $this->analyzeTables(),
            'indexes' => $this->analyzeIndexes(),
            'slow_queries' => $this->getSlowQueries(),
            'recommendations' => []
        ];

        $analysis['recommendations'] = $this->generateRecommendations($analysis);

        return $analysis;
    }

    /**
     * Analyze table structures and suggest optimizations
     */
    private function analyzeTables()
    {
        $tables = [];
        $stmt = $this->pdo->query("SHOW TABLES");
        $tableNames = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tableNames as $tableName) {
            $tableInfo = $this->analyzeTable($tableName);
            $tables[$tableName] = $tableInfo;
        }

        return $tables;
    }

    /**
     * Analyze a specific table
     */
    private function analyzeTable($tableName)
    {
        $info = [];

        // Get table structure
        $stmt = $this->pdo->query("DESCRIBE `$tableName`");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get table status
        $stmt = $this->pdo->query("SHOW TABLE STATUS LIKE '$tableName'");
        $status = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get row count
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `$tableName`");
        $rowCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

        $info = [
            'name' => $tableName,
            'columns' => $columns,
            'row_count' => $rowCount,
            'data_length' => $status['Data_length'] ?? 0,
            'index_length' => $status['Index_length'] ?? 0,
            'engine' => $status['Engine'] ?? 'InnoDB',
            'collation' => $status['Collation'] ?? 'utf8mb4_general_ci'
        ];

        return $info;
    }

    /**
     * Analyze existing indexes
     */
    private function analyzeIndexes()
    {
        $indexes = [];
        $stmt = $this->pdo->query("SHOW TABLES");
        $tableNames = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tableNames as $tableName) {
            $stmt = $this->pdo->query("SHOW INDEX FROM `$tableName`");
            $tableIndexes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($tableIndexes)) {
                $indexes[$tableName] = $tableIndexes;
            }
        }

        return $indexes;
    }

    /**
     * Get slow queries from MySQL slow query log (if enabled)
     */
    private function getSlowQueries()
    {
        // This would require slow query log to be enabled
        // For now, return empty array as this requires server configuration
        return [];
    }

    /**
     * Generate optimization recommendations
     */
    private function generateRecommendations($analysis)
    {
        $recommendations = [];

        // Check for missing indexes on frequently queried columns
        foreach ($analysis['tables'] as $tableName => $tableInfo) {
            $recommendations = array_merge($recommendations, $this->suggestIndexes($tableName, $tableInfo));
        }

        // Check for table optimization opportunities
        foreach ($analysis['tables'] as $tableName => $tableInfo) {
            $recommendations = array_merge($recommendations, $this->suggestTableOptimizations($tableName, $tableInfo));
        }

        return $recommendations;
    }

    /**
     * Suggest indexes based on table structure and common query patterns
     */
    private function suggestIndexes($tableName, $tableInfo)
    {
        $suggestions = [];
        $existingIndexes = $this->getExistingIndexes($tableName);

        // Common columns that should be indexed
        $indexableColumns = [
            'id', 'user_id', 'property_id', 'associate_id', 'customer_id',
            'email', 'phone', 'status', 'created_at', 'updated_at',
            'type', 'category', 'location', 'city', 'state'
        ];

        foreach ($tableInfo['columns'] as $column) {
            $columnName = $column['Field'];

            // Skip if already indexed
            if (isset($existingIndexes[$columnName])) {
                continue;
            }

            // Suggest index for common query columns
            if (in_array($columnName, $indexableColumns) ||
                strpos($columnName, '_id') !== false ||
                $columnName === 'status' ||
                $columnName === 'type') {

                $suggestions[] = [
                    'type' => 'index',
                    'table' => $tableName,
                    'column' => $columnName,
                    'suggestion' => "Add index on {$columnName} for better query performance",
                    'priority' => 'medium'
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Suggest table-level optimizations
     */
    private function suggestTableOptimizations($tableName, $tableInfo)
    {
        $suggestions = [];

        // Check if table needs optimization (high fragmentation)
        if ($tableInfo['data_length'] > 0 && $tableInfo['index_length'] > 0) {
            $fragmentation = ($tableInfo['data_length'] / ($tableInfo['data_length'] + $tableInfo['index_length'])) * 100;

            if ($fragmentation > 30) {
                $suggestions[] = [
                    'type' => 'optimization',
                    'table' => $tableName,
                    'suggestion' => "Run OPTIMIZE TABLE to reduce fragmentation",
                    'priority' => 'low'
                ];
            }
        }

        // Check for large tables that might benefit from partitioning
        if ($tableInfo['row_count'] > 1000000) {
            $suggestions[] = [
                'type' => 'partitioning',
                'table' => $tableName,
                'suggestion' => "Consider partitioning for better performance on large datasets",
                'priority' => 'low'
            ];
        }

        return $suggestions;
    }

    /**
     * Get existing indexes for a table
     */
    private function getExistingIndexes($tableName)
    {
        $indexes = [];
        $stmt = $this->pdo->query("SHOW INDEX FROM `$tableName`");
        $indexData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($indexData as $index) {
            $indexes[$index['Column_name']] = $index;
        }

        return $indexes;
    }

    /**
     * Apply optimization recommendations
     */
    public function applyOptimizations($recommendations = null)
    {
        if ($recommendations === null) {
            $analysis = $this->analyzePerformance();
            $recommendations = $analysis['recommendations'];
        }

        $results = [
            'applied' => 0,
            'failed' => 0,
            'skipped' => 0,
            'details' => []
        ];

        foreach ($recommendations as $rec) {
            try {
                switch ($rec['type']) {
                    case 'index':
                        $success = $this->addIndex($rec['table'], $rec['column']);
                        break;
                    case 'optimization':
                        $success = $this->optimizeTable($rec['table']);
                        break;
                    case 'partitioning':
                        $success = false; // Partitioning requires manual setup
                        break;
                    default:
                        $success = false;
                }

                if ($success) {
                    $results['applied']++;
                    $results['details'][] = [
                        'recommendation' => $rec['suggestion'],
                        'status' => 'applied'
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'recommendation' => $rec['suggestion'],
                        'status' => 'failed'
                    ];
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'recommendation' => $rec['suggestion'],
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Add index to a column
     */
    private function addIndex($tableName, $columnName)
    {
        $indexName = "idx_{$tableName}_{$columnName}";
        $sql = "CREATE INDEX `{$indexName}` ON `{$tableName}` (`{$columnName}`)";

        try {
            $this->pdo->exec($sql);
            return true;
        } catch (\Exception $e) {
            // Index might already exist
            return false;
        }
    }

    /**
     * Optimize table
     */
    private function optimizeTable($tableName)
    {
        try {
            $this->pdo->exec("OPTIMIZE TABLE `{$tableName}`");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get query performance statistics
     */
    public function getQueryStats()
    {
        // This would require enabling MySQL query logging
        // For now, return basic stats
        return [
            'total_queries' => 'N/A (enable slow query log for details)',
            'slow_queries' => 'N/A',
            'average_execution_time' => 'N/A'
        ];
    }

    /**
     * Generate database maintenance script
     */
    public function generateMaintenanceScript()
    {
        $script = "-- Database Maintenance Script\n";
        $script .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

        // Add common maintenance operations
        $script .= "-- 1. Check and repair tables\n";
        $script .= "CHECK TABLE " . implode(", ", array_keys($this->analyzeTables())) . ";\n\n";

        $script .= "-- 2. Optimize tables with high fragmentation\n";
        $tables = $this->analyzeTables();
        foreach ($tables as $tableName => $tableInfo) {
            if ($tableInfo['row_count'] > 1000) {
                $script .= "OPTIMIZE TABLE `{$tableName}`;\n";
            }
        }

        $script .= "\n-- 3. Update table statistics\n";
        $script .= "ANALYZE TABLE " . implode(", ", array_keys($tables)) . ";\n";

        return $script;
    }
}

/**
 * Global database optimization functions
 */
function db_optimizer()
{
    return DatabaseOptimizer::getInstance();
}

function analyze_database()
{
    return DatabaseOptimizer::getInstance()->analyzePerformance();
}

function optimize_database()
{
    return DatabaseOptimizer::getInstance()->applyOptimizations();
}

?>
