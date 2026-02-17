<?php
namespace Admin\Models;

class Component {
    private $db;
    private $table = 'components';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, type, content, is_active, created_by) 
                VALUES (:name, :type, :content, :is_active, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':type' => $data['type'],
            ':content' => $data['content'],
            ':is_active' => $data['is_active'] ?? true,
            ':created_by' => $data['user_id']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                name = :name,
                type = :type,
                content = :content,
                is_active = :is_active
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':type' => $data['type'],
            ':content' => $data['content'],
            ':is_active' => $data['is_active'],
            ':id' => $id
        ]);
    }

    public function delete($id) {
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

    public function getAll($type = null, $activeOnly = true) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if ($type && $activeOnly) {
            $sql .= " WHERE type = :type AND is_active = true";
            $params[':type'] = $type;
        } elseif ($type) {
            $sql .= " WHERE type = :type";
            $params[':type'] = $type;
        } elseif ($activeOnly) {
            $sql .= " WHERE is_active = true";
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTypes() {
        $sql = "SELECT DISTINCT type FROM {$this->table} ORDER BY type ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function toggleActive($id) {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function search($query) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE :query 
                OR type LIKE :query 
                OR content LIKE :query 
                ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':query' => '%' . $query . '%']);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function duplicate($id) {
        $component = $this->getById($id);
        if (!$component) return false;

        $component['name'] = $component['name'] . ' (Copy)';
        unset($component['id']);
        unset($component['created_at']);
        unset($component['updated_at']);

        return $this->create($component);
    }
}
