<?php

namespace App\Models;

use App\Core\Database\Database;
use App\Core\Database\QueryBuilder;
use Exception;

/**
 * Base Model Class
 * Provides common database operations for all models
 */
class Model
{
    protected static $table;
    protected static $primaryKey = 'id';

    /**
     * Get database instance
     * @return Database Database instance
     */
    protected static function getDb()
    {
        return Database::getInstance();
    }

    /**
     * Find single record by ID
     * @param int $id Record ID
     * @return array|null Record data
     */
    public static function find($id)
    {
        try {
            $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?";
            return static::getDb()->fetch($sql, [$id]);
        } catch (Exception $e) {
            error_log("Model find error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find single record by conditions
     * @param array $conditions WHERE conditions
     * @return array|null Record data
     */
    public static function findOne($conditions = [])
    {
        try {
            $sql = "SELECT * FROM " . static::$table . " WHERE 1=1";
            $params = [];

            foreach ($conditions as $field => $value) {
                $sql .= " AND {$field} = ?";
                $params[] = $value;
            }

            return static::getDb()->fetch($sql, $params);
        } catch (Exception $e) {
            error_log("Model findOne error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find multiple records by conditions
     * @param array $conditions WHERE conditions
     * @param string $orderBy Order by clause
     * @param int $limit Limit records
     * @return array Records data
     */
    public static function findMany($conditions = [], $orderBy = null, $limit = null)
    {
        try {
            $sql = "SELECT * FROM " . static::$table . " WHERE 1=1";
            $params = [];

            foreach ($conditions as $field => $value) {
                $sql .= " AND {$field} = ?";
                $params[] = $value;
            }

            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy}";
            }

            if ($limit) {
                $sql .= " LIMIT {$limit}";
            }

            return static::getDb()->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Model findMany error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new record
     * @param array $data Record data
     * @return int|false New record ID
     */
    public static function create($data)
    {
        try {
            $fields = array_keys($data);
            $placeholders = implode(', ', array_fill(0, count($fields), '?'));

            $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $fields) . ") VALUES ({$placeholders})";

            $result = static::getDb()->query($sql, array_values($data));

            if ($result) {
                return static::getDb()->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Model create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all records with optional pagination
     * @param array $conditions WHERE conditions
     * @param string $orderBy Order by clause
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return array Records with pagination
     */
    public static function getAll($conditions = [], $orderBy = null, $page = 1, $perPage = 20)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT * FROM " . static::$table . " WHERE 1=1";
            $params = [];

            foreach ($conditions as $field => $value) {
                $sql .= " AND {$field} = ?";
                $params[] = $value;
            }

            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy}";
            }

            $sql .= " LIMIT {$perPage} OFFSET {$offset}";

            $records = static::getDb()->fetchAll($sql, $params);

            // Get total count for pagination
            $countSql = "SELECT COUNT(*) as total FROM " . static::$table . " WHERE 1=1";
            $totalResult = static::getDb()->fetch($countSql, $params);
            $total = $totalResult['total'] ?? 0;

            return [
                'records' => $records,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage),
                    'has_next' => $page < ceil($total / $perPage),
                    'has_previous' => $page > 1
                ]
            ];
        } catch (Exception $e) {
            error_log("Model getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new query builder instance
     * @return QueryBuilder Query builder instance
     */
    public static function query()
    {
        return new QueryBuilder(static::getDb(), static::$table);
    }

    /**
     * Insert new record
     * @param array $data Record data
     * @return int|bool Inserted ID or false on failure
     */
    public static function insert($data)
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = str_repeat('?,', count($data));
            $values = array_values($data);

            $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";

            $result = static::getDb()->query($sql, $values);

            if ($result) {
                return static::getDb()->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Model insert error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert into specific table
     * @param string $table Table name
     * @param array $data Record data
     * @return int|bool Inserted ID or false on failure
     */
    public static function insertInto($table, $data)
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = str_repeat('?,', count($data));
            $values = array_values($data);

            $sql = "INSERT INTO {$table} ($columns) VALUES ($placeholders)";

            $result = static::getDb()->query($sql, $values);

            if ($result) {
                return static::getDb()->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Model insertInto error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update record
     * @param int $id Record ID
     * @param array $data Record data
     * @return bool Success status
     */
    public static function update($id, $data)
    {
        try {
            $setParts = [];
            $values = [];

            foreach ($data as $column => $value) {
                $setParts[] = "$column = ?";
                $values[] = $value;
            }

            $setClause = implode(', ', $setParts);
            $sql = "UPDATE " . static::$table . " SET $setClause WHERE " . static::$primaryKey . " = ?";
            $values[] = $id;

            return static::getDb()->query($sql, $values);
        } catch (Exception $e) {
            error_log("Model update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete record
     * @param int $id Record ID
     * @return bool Success status
     */
    public static function delete($id)
    {
        try {
            $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?";
            return static::getDb()->query($sql, [$id]);
        } catch (Exception $e) {
            error_log("Model delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get device information for logging
     * @return array Device information
     */
    public static function getDeviceInfo()
    {
        return [
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'platform' => self::getDevicePlatform(),
            'browser' => self::getBrowserInfo(),
            'is_mobile' => self::isMobileDevice(),
            'screen_resolution' => $_COOKIE['screen_resolution'] ?? 'Unknown'
        ];
    }

    /**
     * Get device platform
     * @return string Platform name
     */
    private static function getDevicePlatform()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (stripos($userAgent, 'Windows') !== false) {
            return 'Windows';
        } elseif (stripos($userAgent, 'Mac') !== false) {
            return 'MacOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            return 'Linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            return 'Android';
        } elseif (stripos($userAgent, 'iOS') !== false) {
            return 'iOS';
        }

        return 'Unknown';
    }

    /**
     * Get browser information
     * @return array Browser info
     */
    private static function getBrowserInfo()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $browsers = [
            'Chrome' => 'Chrome',
            'Firefox' => 'Firefox',
            'Safari' => 'Safari',
            'Edge' => 'Edge',
            'Opera' => 'Opera'
        ];

        foreach ($browsers as $browser => $name) {
            if (stripos($userAgent, $browser) !== false) {
                return ['name' => $name, 'detected' => true];
            }
        }

        return ['name' => 'Unknown', 'detected' => false];
    }

    /**
     * Check if device is mobile
     * @return bool Is mobile device
     */
    private static function isMobileDevice()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $mobileKeywords = ['Mobile', 'Android', 'iPhone', 'iPad', 'Tablet'];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
