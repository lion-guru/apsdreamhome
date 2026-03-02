<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Simple Database Class
 * Provides basic database connection and query methods
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $config;

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
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
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

    public static function getInstance($config = []): self
    {
        if (self::$instance === null) {
            if (empty($config)) {
                // Use default configuration
                $host = defined('DB_HOST') ? DB_HOST : ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
                $dbname = defined('DB_NAME') ? DB_NAME : ($_ENV['DB_DATABASE'] ?? getenv('DB_NAME') ?: 'apsdreamhome');
                $user = defined('DB_USER') ? DB_USER : ($_ENV['DB_USERNAME'] ?? getenv('DB_USER') ?: 'root');
                $pass = defined('DB_PASS') ? DB_PASS : ($_ENV['DB_PASSWORD'] ?? getenv('DB_PASS') ?: '');

                $config = [
                    'host' => $host,
                    'database' => $dbname,
                    'username' => $user,
                    'password' => $pass,
                ];
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
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

        $this->query($sql, $data);
        return $this->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = [])
    {
        $set = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params)->rowCount();
    }

    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }

    public function prepare(string $sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
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

    public function getPdo()
    {
        return $this->pdo;
    }
}
