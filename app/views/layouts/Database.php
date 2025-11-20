<?php
class Database {
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        // Prefer root config.php which defines $con/$conn and constants
        $rootConfig = dirname(__DIR__) . '/config.php';
        if (file_exists($rootConfig)) {
            require_once $rootConfig;
        } else {
            // Fallback to includes/config.php (AppConfig); may expose $db_connection
            $includesConfig = __DIR__ . '/config.php';
            if (file_exists($includesConfig)) {
                require_once $includesConfig;
            }
        }

        // Resolve connection from various sources
        global $con;
        $this->conn = $con;

        if (!$this->conn && isset($con) && $con instanceof \mysqli) {
            $this->conn = $con;
        }

        if (!$this->conn && isset($db_connection) && $db_connection instanceof \mysqli) {
            $this->conn = $db_connection;
        }

        // Final fallback: build connection from constants if available
        if (!$this->conn) {
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $user = defined('DB_USER') ? DB_USER : 'root';
            $pass = defined('DB_PASS') ? DB_PASS : (defined('DB_PASSWORD') ? DB_PASSWORD : '');
            $name = defined('DB_NAME') ? DB_NAME : 'apsdreamhome';
            $this->conn = new \mysqli($host, $user, $pass, $name);
        }

        if (!$this->conn || $this->conn->connect_error) {
            throw new \Exception('Database connection failed: ' . ($this->conn ? $this->conn->connect_error : 'no connection'));
        }

        // Ensure charset
        if (!$this->conn->set_charset('utf8mb4')) {
            throw new \Exception('Failed to set charset: ' . $this->conn->error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollback() {
        $this->conn->rollback();
    }

    public function prepare($sql) {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $this->conn->error);
        }
        return $stmt;
    }

    public function executeQuery($sql, $params = [], $types = '') {
        $stmt = $this->prepare($sql);
        
        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat('s', count($params));
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception('Query execution failed: ' . $stmt->error);
        }

        return $stmt->get_result();
    }

    public function fetchAll($sql, $params = [], $types = '') {
        $result = $this->executeQuery($sql, $params, $types);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne($sql, $params = [], $types = '') {
        $result = $this->executeQuery($sql, $params, $types);
        return $result->fetch_assoc();
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
        
        return $this->executeQuery($sql, array_values($data));
    }

    public function update($table, $data, $where, $whereParams = []) {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        return $this->executeQuery($sql, $params);
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->executeQuery($sql, $params);
    }

    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    public function affectedRows() {
        return $this->conn->affected_rows;
    }

    public function escapeString($value) {
        return $this->conn->real_escape_string($value);
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}