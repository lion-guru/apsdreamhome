<?php
namespace Admin\Models;

class Media {
    private $db;
    private $table = 'media';
    private $uploadDir = 'uploads/media/';

    public function __construct($db) {
        $this->db = $db;
    }

    public function upload($file, $userId) {
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $sql = "INSERT INTO {$this->table} (filename, original_filename, type, size, path, uploaded_by) 
                    VALUES (:filename, :original_filename, :type, :size, :path, :uploaded_by)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':filename' => $filename,
                ':original_filename' => $file['name'],
                ':type' => $file['type'],
                ':size' => $file['size'],
                ':path' => $filepath,
                ':uploaded_by' => $userId
            ]);
        }
        return false;
    }

    public function delete($id) {
        // Get file info before deleting
        $file = $this->getById($id);
        if ($file && file_exists($file['path'])) {
            unlink($file['path']);
        }

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAll($type = null, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if ($type) {
            $sql .= " WHERE type LIKE :type";
            $params[':type'] = $type . '%';
        }

        $sql .= " ORDER BY uploaded_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }

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

    public function getByUser($userId, $type = null) {
        $sql = "SELECT * FROM {$this->table} WHERE uploaded_by = :user_id";
        $params = [':user_id' => $userId];

        if ($type) {
            $sql .= " AND type LIKE :type";
            $params[':type'] = $type . '%';
        }

        $sql .= " ORDER BY uploaded_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotalSize($userId = null) {
        $sql = "SELECT SUM(size) as total_size FROM {$this->table}";
        $params = [];

        if ($userId) {
            $sql .= " WHERE uploaded_by = :user_id";
            $params[':user_id'] = $userId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total_size'] ?? 0;
    }

    public function search($query) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE filename LIKE :query 
                OR original_filename LIKE :query 
                OR type LIKE :query 
                ORDER BY uploaded_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':query' => '%' . $query . '%']);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}