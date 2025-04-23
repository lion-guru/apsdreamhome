<?php
namespace Database;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $config = require dirname(__DIR__) . '/config/database.php';
            $this->connection = new \mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['database']
            );
            
            if ($this->connection->connect_error) {
                throw new \Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset($config['charset']);
        } catch (\Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }

    public function query($sql, $params = []) {
        if (!empty($params)) {
            $stmt = $this->connection->prepare($sql);
            if ($stmt === false) {
                throw new \Exception("Prepare failed: " . $this->connection->error);
            }

            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) $types .= 'i';
                elseif (is_float($param)) $types .= 'd';
                elseif (is_string($param)) $types .= 's';
                else $types .= 'b';
            }

            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else {
            $result = $this->connection->query($sql);
            if ($result === false) {
                throw new \Exception("Query failed: " . $this->connection->error);
            }
        }

        return $result;
    }

    public function fetch($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $result->fetch_assoc();
    }

    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = implode(", ", array_fill(0, count($data), "?"));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
        
        $this->query($sql, array_values($data));
        return $this->connection->insert_id;
    }

    public function update($table, $data, $where) {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
        }
        $sql = "UPDATE {$table} SET " . implode(", ", $set) . " WHERE {$where}";
        
        return $this->query($sql, array_values($data));
    }

    public function delete($table, $where) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql);
    }

    public function beginTransaction() {
        $this->connection->begin_transaction();
    }

    public function commit() {
        $this->connection->commit();
    }

    public function rollback() {
        $this->connection->rollback();
    }

    private function __clone() {}
    private function __wakeup() {}
}