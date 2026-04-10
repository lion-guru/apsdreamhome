<?php

namespace App\Models;

use App\Models\Model;

class State extends Model
{
    public static $table = 'states';

    protected array $fillable = [
        'name',
        'code',
        'status'
    ];

    /**
     * Get active states
     * 
     * @param array $columns Columns to select (default: id, name)
     * @return array
     */
    public static function getActive($columns = ['id', 'name'])
    {
        try {
            $columnList = implode(', ', $columns);
            $sql = "SELECT {$columnList} FROM states WHERE is_active = 1 ORDER BY name ASC";

            $db = \App\Core\Database::getInstance();
            return $db->fetchAll($sql);
        } catch (\Exception $e) {
            error_log('Error in State::getActive: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get states for select dropdown
     * 
     * @param array $columns Columns to select (default: id, name)
     * @param bool $activeOnly Filter by active status (default: true)
     * @return array
     */
    public static function getForSelect($columns = ['id', 'name'], $activeOnly = true)
    {
        try {
            $columnList = implode(', ', $columns);
            $where = $activeOnly ? "WHERE is_active = 1" : '';
            $sql = "SELECT {$columnList} FROM states {$where} ORDER BY name ASC";

            $db = \App\Core\Database::getInstance();
            return $db->fetchAll($sql);
        } catch (\Exception $e) {
            error_log('Error in State::getForSelect: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all states
     * 
     * @param array $columns Columns to select (default: *)
     * @return array
     */
    public static function getAll($columns = ['*'])
    {
        try {
            $columnList = implode(', ', $columns);
            $sql = "SELECT {$columnList} FROM states ORDER BY name ASC";

            $db = \App\Core\Database::getInstance();
            return $db->fetchAll($sql);
        } catch (\Exception $e) {
            error_log('Error in State::getAll: ' . $e->getMessage());
            return [];
        }
    }
}
