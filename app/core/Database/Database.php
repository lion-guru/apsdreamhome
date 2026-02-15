<?php

namespace App\Core\Database;

use PDO;
use PDOException;

class Database
{
    protected $pdo;
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => '',
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

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
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
}
