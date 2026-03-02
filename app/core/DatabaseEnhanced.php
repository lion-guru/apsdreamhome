<?php

namespace App\Core;

/**
 * Enhanced Database Class
 * Provides database connection and advanced query methods
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $config;
    private $queryCount = 0;
    private $queryLog = [];
    private $slowQueryThreshold = 1.0; // seconds

    // Performance optimization features
    private $queryCache = [];
    private $preparedStatements = [];
    private $performanceLog = [];

    /**
     * Private constructor
     * @param array $config Database configuration
     */
    private function __construct(array $config = [])
    {
        $this->config = $config;
        $this->connect();
    }

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

    /**
     * Connect to database
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4";
            $this->pdo = new \PDO($dsn, $this->config['username'], $this->config['password']);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Execute query and return results
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array
     */
    public function query($sql, $params = [])
    {
        $startTime = microtime(true);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            $this->logQuery($sql, $params, $executionTime);
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute query and return single row
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|null
     */
    public function queryOne($sql, $params = [])
    {
        $startTime = microtime(true);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            $this->logQuery($sql, $params, $executionTime);
            
            return $stmt->fetch();
        } catch (\PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute query and return single column
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param int $column Column number
     * @return mixed
     */
    public function queryColumn($sql, $params = [], $column = 0)
    {
        $startTime = microtime(true);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            $this->logQuery($sql, $params, $executionTime);
            
            return $stmt->fetchColumn($column);
        } catch (\PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute non-select query (INSERT, UPDATE, DELETE)
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return int Number of affected rows
     */
    public function execute($sql, $params = [])
    {
        $startTime = microtime(true);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            $this->logQuery($sql, $params, $executionTime);
            
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Get last inserted ID
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * Check if table exists
     * @param string $tableName Table name
     * @return bool
     */
    public function tableExists($tableName)
    {
        $sql = "SELECT COUNT(*) FROM information_schema.tables 
                WHERE table_schema = ? AND table_name = ?";
        $result = $this->queryColumn($sql, [$this->config['database'], $tableName]);
        return $result > 0;
    }

    /**
     * Get table columns
     * @param string $tableName Table name
     * @return array
     */
    public function getTableColumns($tableName)
    {
        $sql = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
                FROM information_schema.columns 
                WHERE table_schema = ? AND table_name = ?
                ORDER BY ORDINAL_POSITION";
        return $this->query($sql, [$this->config['database'], $tableName]);
    }

    /**
     * Get table count
     * @return int
     */
    public function getTableCount()
    {
        $sql = "SELECT COUNT(*) as count FROM information_schema.tables 
                WHERE table_schema = ?";
        $result = $this->queryOne($sql, [$this->config['database']]);
        return (int)$result['count'];
    }

    /**
     * Log query for performance monitoring
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param float $executionTime Execution time
     */
    private function logQuery($sql, $params, $executionTime)
    {
        $this->queryCount++;
        
        $logEntry = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->queryLog[] = $logEntry;
        
        // Log slow queries
        if ($executionTime > $this->slowQueryThreshold) {
            error_log("Slow Query ({$executionTime}s): {$sql}");
        }
    }

    /**
     * Get query statistics
     * @return array
     */
    public function getQueryStats()
    {
        return [
            'total_queries' => $this->queryCount,
            'query_log' => $this->queryLog,
            'slow_query_threshold' => $this->slowQueryThreshold
        ];
    }

    /**
     * Clear query log
     */
    public function clearQueryLog()
    {
        $this->queryLog = [];
        $this->queryCount = 0;
    }

    /**
     * Get PDO instance for advanced operations
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Test database connection
     * @return array
     */
    public function testConnection()
    {
        try {
            $version = $this->queryColumn("SELECT VERSION()");
            $database = $this->queryColumn("SELECT DATABASE()");
            $tableCount = $this->getTableCount();
            
            return [
                'status' => 'connected',
                'version' => $version,
                'database' => $database,
                'table_count' => $tableCount,
                'config' => [
                    'host' => $this->config['host'],
                    'database' => $this->config['database'],
                    'charset' => $this->config['charset'] ?? 'utf8mb4'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup()
    {
    }
}
