<?php
/**
 * Database Connection Wrapper
 * This file now acts as a wrapper for the main db_config.php
 */

// Include the main database configuration
require_once __DIR__ . '/db_config.php';

// Keep only the helper functions that aren't in db_config.php
// Remove getDbConnection() and DB constant definitions

/**
 * Execute a query with proper error handling
 * @param string $query The SQL query to execute
 * @param array $params Optional parameters for prepared statements
 * @param string $types Optional parameter types for prepared statements
 * @return mysqli_result|bool|null Query result, true/false for success/failure, or null on error
 */
function executeQuery($query, $params = [], $types = '') {
    $connection = getDbConnection();
    
    if (!$connection) {
        return null;
    }
    
    try {
        // If no parameters, execute direct query
        if (empty($params)) {
            $result = $connection->query($query);
            
            if ($result === false) {
                error_log("Query execution failed: " . $connection->error . " for query: " . $query);
            }
            
            return $result;
        }
        
        // Prepare statement for parameterized query
        $stmt = $connection->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare statement failed: " . $connection->error . " for query: " . $query);
            return null;
        }
        
        // If types not provided, generate them
        if (empty($types)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
            }
        }
        
        // Bind parameters
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        // Execute statement
        $result = $stmt->execute();
        
        if ($result === false) {
            error_log("Statement execution failed: " . $stmt->error . " for query: " . $query);
            $stmt->close();
            return null;
        }
        
        // Get result for SELECT queries
        if (stripos(trim($query), 'SELECT') === 0) {
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        }
        
        // For non-SELECT queries, return success/failure
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    } catch (Exception $e) {
        error_log("Query execution exception: " . $e->getMessage() . " for query: " . $query);
        return null;
    }
}

/**
 * Get a single row from the database
 * @param string $query The SQL query to execute
 * @param array $params Optional parameters for prepared statements
 * @param string $types Optional parameter types for prepared statements
 * @return array|null Single row as associative array or null if not found/error
 */
function getRow($query, $params = [], $types = '') {
    $result = executeQuery($query, $params, $types);
    
    if (!$result || !($result instanceof mysqli_result)) {
        return null;
    }
    
    $row = $result->fetch_assoc();
    $result->free();
    
    return $row ?: null;
}

/**
 * Get multiple rows from the database
 * @param string $query The SQL query to execute
 * @param array $params Optional parameters for prepared statements
 * @param string $types Optional parameter types for prepared statements
 * @return array Array of rows as associative arrays or empty array if not found/error
 */
function getRows($query, $params = [], $types = '') {
    $result = executeQuery($query, $params, $types);
    
    if (!$result || !($result instanceof mysqli_result)) {
        return [];
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    $result->free();
    return $rows;
}

/**
 * Insert a record into the database
 * @param string $table The table name
 * @param array $data Associative array of column => value pairs
 * @return int|null The inserted ID or null on failure
 */
function insertRecord($table, $data) {
    $connection = getDbConnection();
    
    if (!$connection || empty($data)) {
        return null;
    }
    
    $columns = array_keys($data);
    $values = array_values($data);
    $placeholders = array_fill(0, count($values), '?');
    
    $query = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
    
    $result = executeQuery($query, $values);
    
    if ($result === null || $result === false) {
        return null;
    }
    
    return $connection->insert_id;
}

/**
 * Update a record in the database
 * @param string $table The table name
 * @param array $data Associative array of column => value pairs to update
 * @param string $whereClause The WHERE clause without the 'WHERE' keyword
 * @param array $whereParams Parameters for the WHERE clause
 * @return bool True on success, false on failure
 */
function updateRecord($table, $data, $whereClause, $whereParams = []) {
    if (empty($data)) {
        return false;
    }
    
    $setParts = [];
    $params = [];
    
    foreach ($data as $column => $value) {
        $setParts[] = "`$column` = ?";
        $params[] = $value;
    }
    
    $query = "UPDATE `$table` SET " . implode(', ', $setParts);
    
    if (!empty($whereClause)) {
        $query .= " WHERE $whereClause";
        $params = array_merge($params, $whereParams);
    }
    
    $result = executeQuery($query, $params);
    
    return $result !== null && $result !== false;
}

/**
 * Delete a record from the database
 * @param string $table The table name
 * @param string $whereClause The WHERE clause without the 'WHERE' keyword
 * @param array $whereParams Parameters for the WHERE clause
 * @return bool True on success, false on failure
 */
function deleteRecord($table, $whereClause, $whereParams = []) {
    $query = "DELETE FROM `$table`";
    
    if (!empty($whereClause)) {
        $query .= " WHERE $whereClause";
    }
    
    $result = executeQuery($query, $whereParams);
    
    return $result !== null && $result !== false;
}