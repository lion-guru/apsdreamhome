<?php

namespace App\Core;

use App\Config\DatabaseConfig;

/**
 * Enhanced Database Connection Manager
 * Unified database layer combining PDO performance with reliability
 */
class DatabaseManager
{
    private static $instance = null;
    private $connection = null;
    private $config = null;
    private $inTransaction = false;
    
    // Performance optimization features
    private $queryCache = [];
    private $preparedStatements = [];
    private $performanceLog = [];
    private $cacheHits = 0;
    private $totalQueries = 0;
    
    private function __construct()
    {
        $this->config = DatabaseConfig::getInstance();
        $this->connect();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $dsn = $this->config->getDsn();
            $username = $this->config->get('username');
            $password = $this->config->get('password');
            $options = $this->config->get('pdo_options', []);
            
            $this->connection = new \PDO($dsn, $username, $password, $options);
            
            // Enable query profiling for performance monitoring
            $this->connection->exec("SET profiling = 1");
            
            $this->logConnection('SUCCESS');
            
        } catch (\PDOException $e) {
            $this->logConnection('FAILED', $e->getMessage());
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get PDO connection instance
     */
    public function getConnection(): \PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        
        // Test connection health
        try {
            $this->connection->query("SELECT 1");
        } catch (\PDOException $e) {
            // Connection lost, reconnect
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Execute query with caching and performance monitoring
     */
    public function query(string $sql, array $params = [], bool $useCache = null): \PDOStatement
    {
        $startTime = microtime(true);
        $this->totalQueries++;
        
        // Determine cache usage
        if ($useCache === null) {
            $useCache = $this->config->get('query_cache_enabled', false) && $this->isReadQuery($sql);
        }
        
        // Generate cache key
        $cacheKey = null;
        if ($useCache) {
            $cacheKey = $this->generateCacheKey($sql, $params);
            if (isset($this->queryCache[$cacheKey])) {
                $this->cacheHits++;
                $this->logPerformance('CACHE_HIT', $sql, microtime(true) - $startTime);
                return $this->queryCache[$cacheKey];
            }
        }
        
        try {
            // Use prepared statement for security
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            // Cache successful read queries
            if ($useCache && $this->isReadQuery($sql)) {
                $this->queryCache[$cacheKey] = $stmt;
                $this->maintainCacheSize();
            }
            
            $executionTime = microtime(true) - $startTime;
            $this->logPerformance($executionTime > $this->config->get('slow_query_threshold', 1.0) ? 'SLOW_QUERY' : 'FAST_QUERY', $sql, $executionTime);
            
            return $stmt;
            
        } catch (\PDOException $e) {
            $executionTime = microtime(true) - $startTime;
            $this->logPerformance('ERROR', $sql, $executionTime, $e->getMessage());
            throw new \Exception("Query execution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute SELECT query and return all results
     */
    public function select(string $sql, array $params = [], bool $useCache = null): array
    {
        $stmt = $this->query($sql, $params, $useCache);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute SELECT query and return single result
     */
    public function selectOne(string $sql, array $params = [], bool $useCache = null): ?array
    {
        $stmt = $this->query($sql, $params, $useCache);
        $result = $stmt->fetch();
        return $result === false ? null : $result;
    }
    
    /**
     * Execute INSERT query
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data, false);
        return (int) $this->getConnection()->lastInsertId();
    }
    
    /**
     * Execute UPDATE query
     */
    public function update(string $table, array $data, array $where): int
    {
        $setClause = [];
        $whereClause = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = :set_{$column}";
            $params[":set_{$column}"] = $value;
        }
        
        foreach ($where as $column => $value) {
            $whereClause[] = "{$column} = :where_{$column}";
            $params[":where_{$column}"] = $value;
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->query($sql, $params, false);
        return $stmt->rowCount();
    }
    
    /**
     * Execute DELETE query
     */
    public function delete(string $table, array $where): int
    {
        $whereClause = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $whereClause[] = "{$column} = :{$column}";
            $params[":{$column}"] = $value;
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->query($sql, $params, false);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        if ($this->inTransaction) {
            return true; // Already in transaction
        }
        
        $this->inTransaction = $this->getConnection()->beginTransaction();
        return $this->inTransaction;
    }
    
    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        
        $result = $this->getConnection()->commit();
        $this->inTransaction = !$result;
        return $result;
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        
        $result = $this->getConnection()->rollBack();
        $this->inTransaction = !$result;
        return $result;
    }
    
    /**
     * Check if currently in transaction
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId(): int
    {
        return (int) $this->getConnection()->lastInsertId();
    }
    
    /**
     * Clear query cache
     */
    public function clearCache(): void
    {
        $this->queryCache = [];
        $this->cacheHits = 0;
    }
    
    /**
     * Get performance statistics
     */
    public function getPerformanceStats(): array
    {
        $slowQueries = array_filter($this->performanceLog, function($log) {
            return $log['type'] === 'SLOW_QUERY';
        });
        
        $totalTime = array_sum(array_column($this->performanceLog, 'execution_time'));
        
        return [
            'total_queries' => $this->totalQueries,
            'cache_hits' => $this->cacheHits,
            'cache_hit_rate' => $this->totalQueries > 0 ? round(($this->cacheHits / $this->totalQueries) * 100, 2) : 0,
            'slow_queries' => count($slowQueries),
            'error_queries' => count(array_filter($this->performanceLog, function($log) {
                return $log['type'] === 'ERROR';
            })),
            'avg_execution_time' => $this->totalQueries > 0 ? round($totalTime / $this->totalQueries, 4) : 0,
            'total_execution_time' => round($totalTime, 4),
            'cache_size' => count($this->queryCache),
            'in_transaction' => $this->inTransaction
        ];
    }
    
    /**
     * Get slow queries for analysis
     */
    public function getSlowQueries(int $limit = 10): array
    {
        $slowQueries = array_filter($this->performanceLog, function($log) {
            return $log['type'] === 'SLOW_QUERY';
        });
        
        return array_slice($slowQueries, -$limit);
    }
    
    // Private helper methods
    
    private function isReadQuery(string $sql): bool
    {
        $sql = strtolower(trim($sql));
        return strpos($sql, 'select') === 0 || strpos($sql, 'show') === 0 || strpos($sql, 'describe') === 0;
    }
    
    private function generateCacheKey(string $sql, array $params): string
    {
        return 'query_' . md5($sql . serialize($params));
    }
    
    private function maintainCacheSize(): void
    {
        $maxSize = $this->config->get('max_cache_size', 1000);
        if (count($this->queryCache) > $maxSize) {
            // Remove oldest entries
            $this->queryCache = array_slice($this->queryCache, -$maxSize, null, true);
        }
    }
    
    private function logPerformance(string $type, string $sql, float $executionTime, string $error = null): void
    {
        $this->performanceLog[] = [
            'type' => $type,
            'sql' => substr($sql, 0, 200) . (strlen($sql) > 200 ? '...' : ''),
            'execution_time' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => $error,
            'memory_usage' => memory_get_usage(true)
        ];
        
        // Keep only last 1000 entries to prevent memory issues
        if (count($this->performanceLog) > 1000) {
            array_shift($this->performanceLog);
        }
    }
    
    private function logConnection(string $status, string $error = null): void
    {
        $logData = [
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s'),
            'config_source' => $this->config->getEnvironmentInfo()['config_source'],
            'host' => $this->config->get('host'),
            'database' => $this->config->get('database')
        ];
        
        if ($error) {
            $logData['error'] = $error;
        }
        
        // Log to file for debugging
        error_log("Database Connection: " . json_encode($logData));
    }
}