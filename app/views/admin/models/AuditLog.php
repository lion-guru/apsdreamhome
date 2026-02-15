<?php
namespace Admin\Models;

class AuditLog {
    private $db;
    private $table = 'audit_log';

    public function __construct($db) {
        $this->db = $db;
    }

    public function log($userId, $action, $entityType, $entityId, $changes = null) {
        $sql = "INSERT INTO {$this->table} (user_id, action, entity_type, entity_id, changes, ip_address) 
                VALUES (:user_id, :action, :entity_type, :entity_id, :changes, :ip_address)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':changes' => $changes ? json_encode($changes) : null,
            ':ip_address' => $this->getClientIp()
        ]);
    }

    public function getAll($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT al.*, u.auser as user_name 
                FROM {$this->table} al 
                LEFT JOIN admin u ON al.user_id = u.id";
        $params = [];

        // Apply filters
        $whereClauses = [];
        if (!empty($filters['user_id'])) {
            $whereClauses[] = "al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $whereClauses[] = "al.action = :action";
            $params[':action'] = $filters['action'];
        }
        if (!empty($filters['entity_type'])) {
            $whereClauses[] = "al.entity_type = :entity_type";
            $params[':entity_type'] = $filters['entity_type'];
        }
        if (!empty($filters['entity_id'])) {
            $whereClauses[] = "al.entity_id = :entity_id";
            $params[':entity_id'] = $filters['entity_id'];
        }
        if (!empty($filters['date_from'])) {
            $whereClauses[] = "al.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereClauses[] = "al.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => &$value) {
            if ($key == ':limit' || $key == ':offset') {
                $stmt->bindValue($key, $value, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getEntityHistory($entityType, $entityId) {
        $sql = "SELECT al.*, u.auser as user_name 
                FROM {$this->table} al 
                LEFT JOIN admin u ON al.user_id = u.id 
                WHERE al.entity_type = :entity_type 
                AND al.entity_id = :entity_id 
                ORDER BY al.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':entity_type' => $entityType,
            ':entity_id' => $entityId
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getUserActivity($userId, $limit = 50) {
        $sql = "SELECT al.*, u.auser as user_name 
                FROM {$this->table} al 
                LEFT JOIN admin u ON al.user_id = u.id 
                WHERE al.user_id = :user_id 
                ORDER BY al.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getActionTypes() {
        $sql = "SELECT DISTINCT action FROM {$this->table} ORDER BY action ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getEntityTypes() {
        $sql = "SELECT DISTINCT entity_type FROM {$this->table} ORDER BY entity_type ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getClientIp() {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? null;
        }
    }
}
