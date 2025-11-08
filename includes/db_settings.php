<?php
/**
 * Database Settings
 * Central configuration for database connections
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'apsdreamhome');

// Database connection function
function get_db_connection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        $conn->set_charset("utf8");
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return false;
    }
}

// Database query function with error handling
function db_query($sql, $params = []) {
    $conn = get_db_connection();
    if (!$conn) {
        return false;
    }

    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $stmt->close();
        $conn->close();

        return $result;
    } catch (Exception $e) {
        error_log("Database query error: " . $e->getMessage());
        return false;
    }
}

// Database insert/update function
function db_execute($sql, $params = []) {
    $conn = get_db_connection();
    if (!$conn) {
        return false;
    }

    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        $result = $stmt->execute();

        if ($result && strpos(strtoupper($sql), 'INSERT') === 0) {
            $insert_id = $conn->insert_id;
        } else {
            $insert_id = null;
        }

        $stmt->close();
        $conn->close();

        return $result ? ($insert_id ?? true) : false;
    } catch (Exception $e) {
        error_log("Database execute error: " . $e->getMessage());
        return false;
    }
}

// Get database table prefix
function get_table_prefix() {
    return 'aps_';
}

// Get full table name with prefix
function get_table_name($table_name) {
    return get_table_prefix() . $table_name;
}
?>
