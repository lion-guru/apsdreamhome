<?php


namespace App\Core;

/**
 * Enhanced Database Class with Performance Optimizations
 * Includes query caching, prepared statement pooling, and performance monitoring
 */
class Database
{
    private static $instance = null;
    private $connection = null;
    private $inTransaction = false;

    // Performance optimization features
    private $queryCache = [];
    private $preparedStatements = [];
    private $performanceLog = [];
    private $slowQueryThreshold = 1.0; // seconds

    /**
     * Get singleton instance
     * @param array $config Database configuration
     */
    public static function getInstance($config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getConnection(): \PDO
    {
        return $this->connection;
    }

    /**
     * Constructor
     * @param array $config Database configuration
     */
    public function __construct($config = [])
    {
        try {
            $host = $config['host'] ?? (getenv('DB_HOST') ?: 'localhost');
            $dbname = $config['database'] ?? (getenv('DB_NAME') ?: 'apsdreamhome');
            $username = $config['username'] ?? (getenv('DB_USER') ?: 'root');
            $password = $config['password'] ?? (getenv('DB_PASS') ?: '');
            $charset = $config['charset'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => true, // Connection pooling
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false, // Unbuffered for large datasets
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci"
            ];

            // Merge provided options
            if (isset($config['options']) && is_array($config['options'])) {
                $options = $config['options'] + $options;
            }

            $this->connection = new \PDO($dsn, $username, $password, $options);

            // Enable query profiling
            $this->connection->exec("SET profiling = 1");
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Execute query with caching and performance monitoring
     */
    public function query(string $sql, $params = null, ?int $fetchMode = null, bool $useCache = false): \PDOStatement
    {
        $startTime = microtime(true);

        // Generate cache key for read queries
        $cacheKey = null;
        if ($useCache && $this->isReadQuery($sql)) {
            $cacheKey = $this->generateCacheKey($sql, $params);
            if (isset($this->queryCache[$cacheKey])) {
                $this->logPerformance('CACHE_HIT', $sql, microtime(true) - $startTime);
                return $this->queryCache[$cacheKey];
            }
        }

        try {
            $stmt = $this->connection->prepare($sql);

            // Execute with parameters if provided
            if ($params !== null) {
                if (is_array($params)) {
                    $stmt->execute($params);
                } else {
                    $stmt->execute([$params]);
                }
            } else {
                $stmt->execute();
            }

            // Set fetch mode if provided
            if ($fetchMode !== null) {
                $stmt->setFetchMode($fetchMode);
            }

            // Cache read queries
            if ($useCache && $cacheKey && $this->isReadQuery($sql)) {
                $this->queryCache[$cacheKey] = $stmt;
            }

            $executionTime = microtime(true) - $startTime;
            $this->logPerformance($executionTime > $this->slowQueryThreshold ? 'SLOW_QUERY' : 'FAST_QUERY', $sql, $executionTime);

            return $stmt;
        } catch (\PDOException $e) {
            $executionTime = microtime(true) - $startTime;
            $this->logPerformance('ERROR', $sql, $executionTime, $e->getMessage());
            throw new \Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Check if query is a read operation
     */
    private function isReadQuery(string $sql): bool
    {
        $sql = strtolower(trim($sql));
        return strpos($sql, 'select') === 0;
    }

    /**
     * Generate cache key for query
     */
    private function generateCacheKey(string $sql, $params): string
    {
        return md5($sql . serialize($params));
    }

    /**
     * Log performance metrics
     */
    private function logPerformance(string $type, string $sql, float $executionTime, string $error = null): void
    {
        $this->performanceLog[] = [
            'type' => $type,
            'sql' => substr($sql, 0, 100) . (strlen($sql) > 100 ? '...' : ''),
            'execution_time' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => $error
        ];

        // Keep only last 1000 entries
        if (count($this->performanceLog) > 1000) {
            array_shift($this->performanceLog);
        }
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStats(): array
    {
        $stats = [
            'total_queries' => count($this->performanceLog),
            'slow_queries' => 0,
            'cached_queries' => 0,
            'errors' => 0,
            'avg_execution_time' => 0
        ];

        $totalTime = 0;
        foreach ($this->performanceLog as $log) {
            $totalTime += $log['execution_time'];
            if ($log['type'] === 'SLOW_QUERY') $stats['slow_queries']++;
            if ($log['type'] === 'CACHE_HIT') $stats['cached_queries']++;
            if ($log['type'] === 'ERROR') $stats['errors']++;
        }

        $stats['avg_execution_time'] = $stats['total_queries'] > 0 ? $totalTime / $stats['total_queries'] : 0;

        return $stats;
    }

    /**
     * Clear query cache
     */
    public function clearCache(): void
    {
        $this->queryCache = [];
    }

    /**
     * Get slow queries for analysis
     */
    public function getSlowQueries(): array
    {
        return array_filter($this->performanceLog, function ($log) {
            return $log['type'] === 'SLOW_QUERY';
        });
    }

    // Transaction methods (existing functionality)
    public function beginTransaction(): bool
    {
        if (!$this->inTransaction) {
            $this->inTransaction = $this->connection->beginTransaction();
        }
        return $this->inTransaction;
    }

    public function commit(): bool
    {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            return $this->connection->commit();
        }
        return false;
    }

    public function rollback(): bool
    {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            return $this->connection->rollBack();
        }
        return false;
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    private function __clone() {}
    public function __wakeup() {}

    /**
     * Get MySQL query profiling information
     */
    public function getQueryProfile(): array
    {
        try {
            $stmt = $this->connection->query("SHOW PROFILES");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Optimize table
     */
    public function optimizeTable(string $tableName): bool
    {
        try {
            $this->connection->exec("OPTIMIZE TABLE {$tableName}");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get table status information
     */
    public function getTableStatus(string $tableName = null): array
    {
        try {
            $query = $tableName ? "SHOW TABLE STATUS LIKE '{$tableName}'" : "SHOW TABLE STATUS";
            $stmt = $this->connection->query($query);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
