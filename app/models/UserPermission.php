<?php
namespace App\Models;

use App\Models\Model;

class UserPermission extends Model {
    public static $table = 'user_permissions';

    public function grantPermission($userId, $permissionKey) {
        return static::query()
            ->getConnection()
            ->execute("INSERT INTO " . static::$table . " (user_id, permission_key, permission_value) 
                VALUES (?, ?, true)
                ON DUPLICATE KEY UPDATE permission_value = true", 
                [$userId, $permissionKey]
            );
    }

    public function revokePermission($userId, $permissionKey) {
        return static::query()
            ->where('user_id', '=', $userId)
            ->where('permission_key', '=', $permissionKey)
            ->update(['permission_value' => false]);
    }

    public function hasPermission($userId, $permissionKey) {
        $result = static::query()
            ->select(['permission_value'])
            ->where('user_id', '=', $userId)
            ->where('permission_key', '=', $permissionKey)
            ->first();
        
        return $result ? (bool)$result['permission_value'] : false;
    }

    public function getUserPermissions($userId) {
        return static::query()
            ->select(['permission_key', 'permission_value'])
            ->where('user_id', '=', $userId)
            ->get();
    }

    public function setPermissions($userId, array $permissions) {
        $db = static::query()->getConnection();
        $db->beginTransaction();
        
        try {
            foreach ($permissions as $key => $value) {
                $db->execute("INSERT INTO " . static::$table . " (user_id, permission_key, permission_value) 
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE permission_value = ?",
                    [$userId, $key, (bool)$value, (bool)$value]
                );
            }
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    public function deleteUserPermissions($userId) {
        return static::query()
            ->where('user_id', '=', $userId)
            ->delete();
    }

    public function getUsersByPermission($permissionKey, $value = true) {
        return static::query()
            ->select(['u.*'])
            ->from('admin as u')
            ->join(static::$table . ' as up', 'u.id', '=', 'up.user_id')
            ->where('up.permission_key', '=', $permissionKey)
            ->where('up.permission_value', '=', $value)
            ->get();
    }
}