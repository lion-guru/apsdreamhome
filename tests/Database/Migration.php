<?php

namespace Tests\Database;

use mysqli;
use RuntimeException;

/**
 * Base migration class for database migrations
 */
abstract class Migration
{
    protected mysqli $db;
    protected string $tableName;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Run the migration
     */
    abstract public function up(): void;

    /**
     * Rollback the migration
     */
    abstract public function down(): void;

    /**
     * Execute a SQL query
     */
    protected function execute(string $sql): void
    {
        if (!$this->db->query($sql)) {
            throw new RuntimeException("Migration failed: " . $this->db->error);
        }
    }

    /**
     * Check if a table exists
     */
    protected function tableExists(string $table): bool
    {
        $result = $this->db->query("SHOW TABLES LIKE '$table'");
        return $result && $result->num_rows > 0;
    }
}
