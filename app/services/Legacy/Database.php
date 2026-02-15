<?php

namespace App\Services\Legacy;
class Database {
    private $db;

    public function __construct() {
        require_once __DIR__ . '/../vendor/autoload.php';
        $this->db = \App\Core\App::database();
    }

    public function getConnection() {
        return $this->db->getConnection();
    }

    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    public function commit() {
        return $this->db->commit();
    }

    public function rollback() {
        return $this->db->rollBack();
    }

    public function prepare($sql) {
        return $this->db->prepare($sql);
    }

    public function executeQuery($sql, $params = [], $types = '') {
        // App\Core\Database::query handles both select and non-select
        return $this->db->query($sql, $params);
    }

    public function fetchAll($sql, $params = [], $types = '') {
        return $this->db->fetchAll($sql, $params);
    }

    public function fetchOne($sql, $params = [], $types = '') {
        return $this->db->fetchOne($sql, $params);
    }

    public function insert($table, $data) {
        return $this->db->insert($table, $data);
    }

    public function update($table, $data, $where, $whereParams = []) {
        return $this->db->update($table, $data, $where, $whereParams);
    }

    public function delete($table, $where, $params = []) {
        return $this->db->delete($table, $where, $params);
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    public function query($sql, $params = [], $types = '') {
        $result = $this->db->query($sql, $params);
        
        if (stripos(trim($sql), 'INSERT') === 0) {
            return $this->db->lastInsertId();
        }
        
        if ($result instanceof \PDOStatement) {
            return $result->rowCount();
        }
        
        return $result;
    }

    public function affectedRows() {
        return $this->db->affectedRows();
    }

    public function escapeString($value) {
        return $this->db->escapeString($value);
    }
}

