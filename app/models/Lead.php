<?php

namespace App\Models;

class Lead extends Model
{
    protected static $table = 'leads';
    protected static $tenantScoped = true;
    
    public static function all()
    {
        [$tSql, $tParams] = static::tenantClause();
        return self::getDb()->query("SELECT * FROM leads WHERE 1=1{$tSql} ORDER BY created_at DESC", $tParams)->fetchAll();
    }
    
    public static function find($id)
    {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge([(int)$id], $tParams);
        return self::getDb()->query("SELECT * FROM leads WHERE id = ?{$tSql}", $params)->fetch();
    }
    
    public static function create($data)
    {
        ['columns' => $fields, 'values' => $vals] = static::tenantInsertData($data);
        $sql = "INSERT INTO leads (" . implode(',', $fields) . ") VALUES (" . implode(',', array_fill(0, count($vals), '?')) . ")";
        self::getDb()->query($sql, $vals);
        return self::getDb()->lastInsertId();
    }
    
    public static function updateById($id, $data)
    {
        $sets = array_map(fn($k) => "$k = ?", array_keys($data));
        $values = array_values($data);
        [$tSql, $tParams] = static::tenantClause();
        $values[] = $id;
        $values = array_merge($values, $tParams);
        $sql = "UPDATE leads SET " . implode(',', $sets) . " WHERE id = ?{$tSql}";
        return self::getDb()->query($sql, $values);
    }
    
    public static function delete($id)
    {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge([(int)$id], $tParams);
        return self::getDb()->query("UPDATE leads SET deleted_at = NOW() WHERE id = ?{$tSql}", $params);
    }
    
    public static function getByStatus($status)
    {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge([$status], $tParams);
        return self::getDb()->query("SELECT * FROM leads WHERE status = ?{$tSql}", $params)->fetchAll();
    }
    
    public static function countByStatus()
    {
        [$tSql, $tParams] = static::tenantClause();
        return self::getDb()->query("SELECT status, COUNT(*) as count FROM leads WHERE 1=1{$tSql} GROUP BY status", $tParams)->fetchAll();
    }
}
