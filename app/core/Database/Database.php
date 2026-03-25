<?php

namespace App\Core\Database;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    protected $pdo;
    protected $config;

    // Performance optimization features
    private $query_count = 0;
    private $query_log = [];
    private $slow_query_threshold = 1.0; // seconds
    private $performance_log = [];

    public static function getInstance(array $config = [])
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'apsdreamhome',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'port' => 3306,
            'socket' => null,
            'options' => extension_loaded('pdo') ? [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ] : [],
        ], $config);

        $this->connect();
    }

    protected function connect()
    {
        try {
            $dsn = sprintf(
                '%s:host=%s;dbname=%s;charset=%s',
                $this->config['driver'],
                $this->config['host'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function query($sql, $params = [])
    {
        $startTime = microtime(true);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $this->logQuery($sql, $params, $executionTime);

            return $stmt;
        } catch (PDOException $e) {
            throw new \RuntimeException("Query failed: " . $e->getMessage());
        }
    }

    public function execute($sql, $params = [])
    {
        return $this->query($sql, $params);
    }

    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchOne($sql, $params = [])
    {
        return $this->fetch($sql, $params);
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Alias for fetchAll for compatibility with some services
     */
    public function select($sql, $params = [])
    {
        return $this->fetchAll($sql, $params);
    }

    /**
     * Alias for fetch for compatibility with some services
     */
    public function selectOne($sql, $params = [])
    {
        return $this->fetch($sql, $params);
    }

    public function fetchColumn($sql, $params = [], $column = 0)
    {
        return $this->query($sql, $params)->fetchColumn($column);
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($key) => ":$key", array_keys($data)));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        $this->execute($sql, $data);
        return $this->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = [])
    {
        $set = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        $params = array_merge($data, $whereParams);
        return $this->execute($sql, $params)->rowCount();
    }

    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->execute($sql, $params)->rowCount();
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function table($table)
    {
        return new QueryBuilder($this, $table);
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Log query performance
     */
    private function logQuery($sql, $params = [], $executionTime = 0)
    {
        $this->query_count++;

        $this->query_log[] = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Log slow queries
        if ($executionTime > $this->slow_query_threshold) {
            $this->performance_log[] = [
                'type' => 'slow_query',
                'sql' => $sql,
                'execution_time' => $executionTime,
                'threshold' => $this->slow_query_threshold,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function logError($sql, $errorMessage)
    {
        $this->performance_log[] = [
            'type' => 'error',
            'sql' => $sql,
            'error_message' => $errorMessage,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get query performance statistics
     */
    public function prepare($sql)
    {
        $startTime = microtime(true);

        try {
            $stmt = $this->pdo->prepare($sql);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $this->logQuery($sql, [], $executionTime);

            return $stmt;
        } catch (PDOException $e) {
            $this->logError($sql, $e->getMessage());
            throw $e;
        }
    }

    public function getPerformanceStats()
    {
        return [
            'query_count' => $this->query_count,
            'slow_queries' => count($this->performance_log),
            'average_time' => $this->query_count > 0 ?
                array_sum(array_column($this->query_log, 'execution_time')) / $this->query_count : 0,
            'performance_log' => $this->performance_log
        ];
    }

    /**
     * Get the last inserted ID
     */
    public function getLastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
