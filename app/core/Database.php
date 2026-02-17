<?php

namespace App\Core;

use App\Core\Database\Database as BaseDatabase;

/**
 * Enhanced Database Class Wrapper
 * Extends the core Database class to provide backward compatibility
 * and singleton access for legacy components.
 */
class Database extends BaseDatabase
{
    private static $instance = null;

    // Performance optimization features (kept for backward compatibility if accessed directly)
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
            if (empty($config)) {
                // Use global constants or environment variables if config is not provided
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

    /**
     * Get the PDO connection
     * Alias for getPdo() for backward compatibility
     */
    public function getConnection(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Execute query with caching and performance monitoring support
     * Overrides parent query to support legacy arguments
     */
    public function query($sql, $params = [], $fetchMode = null, $useCache = false)
    {
        $stmt = parent::query($sql, $params ?? []);

        if ($fetchMode !== null) {
            $stmt->setFetchMode($fetchMode);
        }

        return $stmt;
    }

    /**
     * Get performance statistics (Placeholder for compatibility)
     */
    public function getPerformanceStats(): array
    {
        return [
            'total_queries' => 0,
            'slow_queries' => 0,
            'cached_queries' => 0,
            'errors' => 0,
            'avg_execution_time' => 0
        ];
    }

    /**
     * Prepare a statement
     */
    public function prepare(string $sql): \PDOStatement
    {
        return $this->getConnection()->prepare($sql);
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit()
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollBack()
    {
        return $this->getConnection()->rollBack();
    }
}
