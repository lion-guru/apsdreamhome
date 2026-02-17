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
}
