<?php

namespace App\Services\Legacy;

use PDO;
use Exception;

/**
 * Database utility functions for APS Dream Home
 * Centralized helpers for common database operations
 */

/**
 * Execute a query using the centralized ORM
 */
if (!function_exists('db_query')) {
    function db_query($sql, $params = []) {
        return \App\Core\App::database()->query($sql, $params);
    }
}

/**
 * Fetch a single row as an associative array
 */
if (!function_exists('db_fetch')) {
    function db_fetch($result) {
        if (!$result) return null;
        if (is_array($result)) return $result;
        if ($result instanceof \PDOStatement) {
            return $result->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
}

/**
 * Get the last inserted ID
 */
if (!function_exists('db_insert_id')) {
    function db_insert_id() {
        return (int)\App\Core\App::database()->lastInsertId();
    }
}

/**
 * Get the number of rows in a result set
 */
if (!function_exists('db_num_rows')) {
    function db_num_rows($result) {
        if (!$result) return 0;
        if ($result instanceof \PDOStatement) {
            return $result->rowCount();
        }
        if (is_numeric($result)) {
            return (int)$result;
        }
        return 0;
    }
}

/**
 * Fetch all rows from a result set
 */
if (!function_exists('db_fetch_all')) {
    function db_fetch_all($result) {
        if (!$result) return [];
        if (is_array($result)) return $result;
        if ($result instanceof \PDOStatement) {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }
}

/**
 * Count user by their type (agent, builder, etc.)
 *
 * @param string $type User type to count
 * @return int Number of user of the specified type
 */
function countUsersByType($type) {
    try {
        $db = \App\Core\App::database();
        
        // Map legacy numeric types
        if ($type == '1') $type = 'admin';
        if ($type == '4') $type = 'customer';
        
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = ?";
        $row = $db->fetchOne($sql, [$type]);
        return (int)($row['count'] ?? 0);
    } catch (Exception $e) {
        error_log("Error counting user by type: " . $e->getMessage());
    }
    return 0;
}

/**
 * Legacy class wrapper for db_utils
 */
class DbUtils {
    public function __construct() {
        // Class exists for backward compatibility
    }
}

/**
 * Count properties by their type (apartment, house, etc.)
 *
 * @param string $type Property type to count (empty string for all properties)
 * @return int Number of properties of the specified type
 */
function countPropertiesByType($type) {
    try {
        $db = \App\Core\App::database();
        if (empty($type)) {
            $sql = "SELECT COUNT(*) as count FROM property";
            $row = $db->fetchOne($sql);
        } else {
            $sql = "SELECT COUNT(*) as count FROM property WHERE type = ?";
            $row = $db->fetchOne($sql, [$type]);
        }
        return (int)($row['count'] ?? 0);
    } catch (Exception $e) {
        error_log("Error counting properties by type: " . $e->getMessage());
    }
    return 0;
}
