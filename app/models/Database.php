<?php

namespace App\Models;

use App\Core\Database as CoreDatabase;

/**
 * Database Wrapper for Models
 * Extends the Core Database class to provide backward compatibility
 * for models using App\Models\Database directly.
 *
 * @deprecated Use App\Core\Database instead.
 */
class Database extends CoreDatabase
{
    private static $instance = null;

    public function __construct($config = [])
    {
        if (empty($config)) {
            // Support both $_ENV and getenv for maximum compatibility
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
            $dbname = $_ENV['DB_DATABASE'] ?? getenv('DB_NAME') ?: 'apsdreamhome';
            $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USER') ?: 'root';
            $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASS') ?: '';

            $config = [
                'host' => $host,
                'database' => $dbname,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8mb4'
            ];
        }
        parent::__construct($config);
    }

    public static function getInstance($config = []): CoreDatabase
    {
        return CoreDatabase::getInstance($config);
    }

    public function prepare(string $sql): \PDOStatement
    {
        return $this->getConnection()->prepare($sql);
    }

    // Legacy method support
    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit()
    {
        return $this->getConnection()->commit();
    }

    public function rollBack()
    {
        return $this->getConnection()->rollBack();
    }

    public function lastInsertId()
    {
        return $this->getConnection()->lastInsertId();
    }

    private function __clone() {}
    public function __wakeup() {}
}
