<?php
namespace Admin\Models;

class UserPermission {
    private $db;
    private $table = 'user_permissions';

    public function __construct($db) {
        $this->db = $db;
    }

    public function grantPermission($userId, $permissionKey) {
        $sql = "INSERT INTO {$this->table} (user_id, permission_key, permission_value) 
                VALUES (:user_id, :permission_key, true)
                ON DUPLICATE KEY UPDATE permission_value = true";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':permission_key' => $permissionKey
        ]);
    }

    public function revokePermission($userId, $permissionKey) {
        $sql = "UPDATE {$this->table} SET permission_value = false 
                WHERE user_id = :user_id AND permission_key = :permission_key";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':permission_key' => $permissionKey
        ]);
    }

    public function hasPermission($userId, $permissionKey) {
        $sql = "SELECT permission_value FROM {$this->table} 
                WHERE user_id = :user_id AND permission_key = :permission_key";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':permission_key' => $permissionKey
        ]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? (bool)$result['permission_value'] : false;
    }

    public function getUserPermissions($userId) {
        $sql = "SELECT permission_key, permission_value FROM {$this->table} 
                WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function setPermissions($userId, array $permissions) {
        $this->db->beginTransaction();
        
        try {
            foreach ($permissions as $key => $value) {
                $sql = "INSERT INTO {$this->table} (user_id, permission_key, permission_value) 
                        VALUES (:user_id, :permission_key, :permission_value)
                        ON DUPLICATE KEY UPDATE permission_value = :permission_value";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':user_id' => $userId,
                    ':permission_key' => $key,
                    ':permission_value' => (bool)$value
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteUserPermissions($userId) {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }

    public function getUsersByPermission($permissionKey, $value = true) {
        $sql = "SELECT u.* FROM admin u 
                INNER JOIN {$this->table} up ON u.id = up.user_id 
                WHERE up.permission_key = :permission_key 
                AND up.permission_value = :value";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':permission_key' => $permissionKey,
            ':value' => $value
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
