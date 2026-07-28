<?php

namespace App\Models;

class Lead extends Model
{
    protected static $table = 'leads';
    protected static $tenantScoped = true;
    
    public static function all()
    {
        return self::getDb()->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll();
    }
    
    public static function find($id)
    {
        return self::getDb()->query("SELECT * FROM leads WHERE id = ?", [$id])->fetch();
    }
    
    public static function create($data)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $sql = "INSERT INTO leads (" . implode(',', $fields) . ") VALUES (" . implode(',', array_fill(0, count($values), '?')) . ")";
        self::getDb()->query($sql, $values);
        return self::getDb()->lastInsertId();
    }
    
    public static function updateById($id, $data)
    {
        $sets = array_map(fn($k) => "$k = ?", array_keys($data));
        $values = array_values($data);
        $values[] = $id;
        $sql = "UPDATE leads SET " . implode(',', $sets) . " WHERE id = ?";
        return self::getDb()->query($sql, $values);
    }
    
    public static function delete($id)
    {
        return self::getDb()->query("UPDATE leads SET deleted_at = NOW() WHERE id = ?", [$id]);
    }
    
    public static function getByStatus($status)
    {
        return self::getDb()->query("SELECT * FROM leads WHERE status = ?", [$status])->fetchAll();
    }
    
    public static function countByStatus()
    {
        return self::getDb()->query("SELECT status, COUNT(*) as count FROM leads GROUP BY status")->fetchAll();
    }
}
