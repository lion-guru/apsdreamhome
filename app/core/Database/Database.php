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
    private $queryCount = 0;
    private $queryLog = [];
    private $slowQueryThreshold = 1.0; // seconds
    private $performanceLog = [];

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
        $this->queryCount++;

        $this->queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Log slow queries
        if ($executionTime > $this->slowQueryThreshold) {
            $this->performanceLog[] = [
                'type' => 'slow_query',
                'sql' => $sql,
                'execution_time' => $executionTime,
                'threshold' => $this->slowQueryThreshold,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Log query error
     */
    private function logError($sql, $errorMessage)
    {
        $this->performanceLog[] = [
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

            $this->logQuery($sql, $executionTime);

            return $stmt;
        } catch (PDOException $e) {
            $this->logError($sql, $e->getMessage());
            throw $e;
        }
    }

    public function getPerformanceStats()
    {
        return [
            'query_count' => $this->queryCount,
            'slow_queries' => count($this->performanceLog),
            'average_time' => $this->queryCount > 0 ?
                array_sum(array_column($this->queryLog, 'execution_time')) / $this->queryCount : 0,
            'performance_log' => $this->performanceLog
        ];
    }
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Services\Legacy\Database.php

function getConnection()
{
    return $this->db->getConnection();
}
function rollback()
{
    return $this->db->rollBack();
}
function prepare($sql)
{
    return $this->db->prepare($sql);
}
function executeQuery($sql, $params = [], $types = '')
{
    // App\Core\Database::query handles both select and non-select
    return $this->db->query($sql, $params);
}
function affectedRows()
{
    return $this->db->affectedRows();
}
function escapeString($value)
{
    return $this->db->escapeString($value);
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Models\Database.php

function getId()
{
    return $this->id;
}
function getName()
{
    return $this->name;
}
function getHost()
{
    return $this->host;
}
function getUsername()
{
    return $this->username;
}
function getPassword()
{
    return $this->password;
}
function getCreatedat()
{
    return $this->created_at;
}
function getUpdatedat()
{
    return $this->updated_at;
}
