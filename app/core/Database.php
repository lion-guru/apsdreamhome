<?php


namespace App\Core;

class Database
{
    private static $instance = null;
    private $connection = null;
    private $inTransaction = false;

    private function __construct()
    {
        try {
            $host = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_NAME') ?: 'realestatephp';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASS') ?: '';

            $this->connection = new \PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $username,
                $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): \PDO
    {
        return $this->connection;
    }

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

    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            throw new \Exception("Query execution failed: " . $e->getMessage());
        }
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    private function __clone() {}
    private function __wakeup() {}
}
