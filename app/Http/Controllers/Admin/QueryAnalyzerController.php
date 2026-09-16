<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

class QueryAnalyzerController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    public function index()
    {
        $this->render('admin/query-analyzer/index', [
            'page_title' => 'Query Analyzer',
            'page_description' => 'Analyze slow queries, missing indexes, and table statistics',
        ]);
    }

    public function slowQueries()
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tenantId = $this->tenantId();
            $tidSql = $tenantId > 1 ? " AND tenant_id = $tenantId" : "";

            $queries = $db->fetchAll("
                SELECT 
                    QUERY_TIME, 
                    LOCK_TIME, 
                    ROWS_SENT, 
                    ROWS_EXAMINED,
                    SQL_TEXT,
                    DB,
                    USER_HOST,
                    EXEC_COUNT,
                    AVG_TIMER_WAIT,
                    MAX_TIMER_WAIT,
                    SUM_TIMER_WAIT
                FROM performance_schema.events_statements_summary_by_digest
                WHERE SCHEMA_NAME = DATABASE() $tidSql
                ORDER BY AVG_TIMER_WAIT DESC
                LIMIT 50
            ");

            return $this->jsonResponse(['success' => true, 'data' => $queries]);
        } catch (\Throwable $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function tableStats()
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tenantId = $this->tenantId();
            $tidSql = $tenantId > 1 ? " AND TABLE_SCHEMA = DATABASE()" : "";

            $tables = $db->fetchAll("
                SELECT 
                    TABLE_NAME,
                    TABLE_ROWS,
                    DATA_LENGTH,
                    INDEX_LENGTH,
                    (DATA_LENGTH + INDEX_LENGTH) as TOTAL_SIZE,
                    ENGINE,
                    TABLE_COLLATION,
                    CREATE_TIME,
                    UPDATE_TIME,
                    CHECK_TIME
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() $tidSql
                ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
            ");

            return $this->jsonResponse(['success' => true, 'data' => $tables]);
        } catch (\Throwable $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function indexStats()
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tenantId = $this->tenantId();
            $tidSql = $tenantId > 1 ? " AND TABLE_SCHEMA = DATABASE()" : "";

            $indexes = $db->fetchAll("
                SELECT 
                    TABLE_NAME,
                    INDEX_NAME,
                    COLUMN_NAME,
                    SEQ_IN_INDEX,
                    NON_UNIQUE,
                    INDEX_TYPE,
                    CARDINALITY
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() $tidSql
                ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX
            ");

            return $this->jsonResponse(['success' => true, 'data' => $indexes]);
        } catch (\Throwable $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function missingIndexes()
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tenantId = $this->tenantId();
            $tidSql = $tenantId > 1 ? " AND TABLE_SCHEMA = DATABASE()" : "";

            $suggestions = [];

            // Check foreign keys without indexes
            $fks = $db->fetchAll("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    CONSTRAINT_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE() 
                AND REFERENCED_TABLE_NAME IS NOT NULL $tidSql
            ");

            foreach ($fks as $fk) {
                $hasIndex = $db->fetchOne("
                    SELECT 1 FROM information_schema.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND COLUMN_NAME = ?
                    AND SEQ_IN_INDEX = 1
                ", [$fk['TABLE_NAME'], $fk['COLUMN_NAME']]);

                if (!$hasIndex) {
                    $suggestions[] = [
                        'type' => 'missing_fk_index',
                        'table' => $fk['TABLE_NAME'],
                        'column' => $fk['COLUMN_NAME'],
                        'message' => "Foreign key {$fk['CONSTRAINT_NAME']} on {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} missing index",
                        'sql' => "CREATE INDEX idx_{$fk['TABLE_NAME']}_{$fk['COLUMN_NAME']} ON {$fk['TABLE_NAME']} ({$fk['COLUMN_NAME']})"
                    ];
                }
            }

            return $this->jsonResponse(['success' => true, 'data' => $suggestions]);
        } catch (\Throwable $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function processList()
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            $processes = $db->fetchAll("
                SELECT 
                    ID,
                    USER,
                    HOST,
                    DB,
                    COMMAND,
                    TIME,
                    STATE,
                    INFO
                FROM information_schema.PROCESSLIST
                WHERE COMMAND != 'Sleep'
                ORDER BY TIME DESC
            ");

            return $this->jsonResponse(['success' => true, 'data' => $processes]);
        } catch (\Throwable $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function killProcess($id)
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $db->exec("KILL " . (int)$id);
            return $this->jsonResponse(['success' => true, 'message' => "Process $id killed"]);
        } catch (\Throwable $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function explainQuery()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $query = $input['query'] ?? '';

        if (!$query) {
            return $this->jsonError('Query required', 400);
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $rawPdo = $db->getPdo();
            $explained = $rawPdo->query("EXPLAIN " . $query)->fetchAll(\PDO::FETCH_ASSOC);
            return $this->jsonResponse(['success' => true, 'data' => $explained]);
        } catch (\Throwable $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }
}