<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Traits\ServiceTenantTrait;

class QueryAnalyzerController extends BaseController
{
    use ServiceTenantTrait;

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function slowQueries()
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND SCHEMA_NAME = DATABASE()" : "";

            $queries = $this->db->fetchAll("
                SELECT 
                    DIGEST_TEXT as sql_text,
                    AVG_TIMER_WAIT / 1000000 as avg_time_ms,
                    MAX_TIMER_WAIT / 1000000 as max_time_ms,
                    COUNT_STAR as exec_count,
                    SUM_ROWS_SENT as rows_sent,
                    SUM_ROWS_EXAMINED as rows_examined,
                    SCHEMA_NAME as db
                FROM performance_schema.events_statements_summary_by_digest
                WHERE SCHEMA_NAME = DATABASE() $tidSql
                ORDER BY AVG_TIMER_WAIT DESC
                LIMIT 50
            ");

            return $this->jsonResponse(['success' => true, 'data' => $queries]);
        } catch (\Throwable $e) {
            error_log("QueryAnalyzer::slowQueries error: " . $e->getMessage());
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function tableStats()
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tables = $this->db->fetchAll("
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
                WHERE TABLE_SCHEMA = DATABASE()
                ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
            ");

            return $this->jsonResponse(['success' => true, 'data' => $tables]);
        } catch (\Throwable $e) {
            error_log("QueryAnalyzer::tableStats error: " . $e->getMessage());
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function indexStats()
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $indexes = $this->db->fetchAll("
                SELECT 
                    TABLE_NAME,
                    INDEX_NAME,
                    COLUMN_NAME,
                    SEQ_IN_INDEX,
                    NON_UNIQUE,
                    INDEX_TYPE,
                    CARDINALITY
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX
            ");

            return $this->jsonResponse(['success' => true, 'data' => $indexes]);
        } catch (\Throwable $e) {
            error_log("QueryAnalyzer::indexStats error: " . $e->getMessage());
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function missingIndexes()
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $suggestions = [];

            $fks = $this->db->fetchAll("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    CONSTRAINT_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE() 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($fks as $fk) {
                $hasIndex = $this->db->fetchOne("
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
            error_log("QueryAnalyzer::missingIndexes error: " . $e->getMessage());
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function processList()
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $processes = $this->db->fetchAll("
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
            error_log("QueryAnalyzer::processList error: " . $e->getMessage());
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function killProcess($id)
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $this->db->exec("KILL " . (int)$id);
            return $this->jsonResponse(['success' => true, 'message' => "Process $id killed"]);
        } catch (\Throwable $e) {
            error_log("QueryAnalyzer::killProcess error: " . $e->getMessage());
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function explainQuery()
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $query = $input['query'] ?? '';
            $params = $input['params'] ?? [];

            if (!$query) {
                return $this->jsonError('Query required', 400);
            }

            // Count placeholders in query
            $placeholderCount = substr_count($query, '?');
            $paramCount = count($params);

            // Replace placeholders with params if they match
            if ($placeholderCount > 0 && $placeholderCount === $paramCount) {
                $finalQuery = $query;
                foreach ($params as $param) {
                    $finalQuery = preg_replace('/\?/', $this->db->getConnection()->quote($param), $finalQuery, 1);
                }
                $explained = $this->db->getConnection()->query("EXPLAIN " . $finalQuery)->fetchAll();
            } elseif ($placeholderCount > 0) {
                // Placeholders exist but no params provided - use placeholder defaults or quote empty
                $finalQuery = $query;
                for ($i = 0; $i < $placeholderCount; $i++) {
                    $finalQuery = preg_replace('/\?/', "'?'", $finalQuery, 1);
                }
                $explained = $this->db->getConnection()->query("EXPLAIN " . $finalQuery)->fetchAll();
            } else {
                // No placeholders
                $explained = $this->db->getConnection()->query("EXPLAIN " . $query)->fetchAll();
            }
            return $this->jsonResponse(['success' => true, 'data' => $explained]);
        } catch (\Throwable $e) {
            error_log("QueryAnalyzer::explainQuery error: " . $e->getMessage());
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }
}