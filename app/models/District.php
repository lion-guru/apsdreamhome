<?php

namespace App\Models;

use App\Models\Model;

class District extends Model
{
    public static $table = 'districts';

    protected array $fillable = [
        'state_id',
        'name',
        'code',
        'status'
    ];

    /**
     * Get active districts
     * 
     * @param array $columns Columns to select (default: id, name)
     * @return array
     */
    public static function getActive($columns = ['id', 'name'])
    {
        try {
            $columnList = implode(', ', array_map(fn($col) => "d.$col", $columns));
            $sql = "SELECT {$columnList} FROM districts d WHERE d.is_active = 1 ORDER BY d.name ASC";

            $db = \App\Core\Database::getInstance();
            return $db->fetchAll($sql);
        } catch (\Exception $e) {
            error_log('Error in District::getActive: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get districts for select dropdown
     * 
     * @param array $columns Columns to select (default: id, name)
     * @param bool $activeOnly Filter by active status (default: true)
     * @return array
     */
    public static function getForSelect($columns = ['id', 'name'], $activeOnly = true)
    {
        try {
            $columnList = implode(', ', array_map(fn($col) => "d.$col", $columns));
            $where = $activeOnly ? "WHERE d.is_active = 1" : '';
            $sql = "SELECT {$columnList} FROM districts d {$where} ORDER BY d.name ASC";

            $db = \App\Core\Database::getInstance();
            return $db->fetchAll($sql);
        } catch (\Exception $e) {
            error_log('Error in District::getForSelect: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get districts with state name
     * 
     * @param array $columns Columns to select (default: id, name)
     * @param bool $activeOnly Filter by active status (default: true)
     * @return array
     */
    public static function getWithStateName($columns = ['id', 'name'], $activeOnly = true)
    {
        try {
            $columnList = 'd.id, d.name, d.state_id, s.name as state_name';
            $where = $activeOnly ? "WHERE d.is_active = 1" : '';
            $sql = "SELECT {$columnList} 
                    FROM districts d 
                    LEFT JOIN states s ON d.state_id = s.id 
                    {$where} 
                    ORDER BY s.name, d.name ASC";

            $db = \App\Core\Database::getInstance();
            return $db->fetchAll($sql);
        } catch (\Exception $e) {
            error_log('Error in District::getWithStateName: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get districts by state
     * 
     * @param int $stateId State ID
     * @param array $columns Columns to select (default: id, name)
     * @param bool $activeOnly Filter by active status (default: true)
     * @return array
     */
    public static function getByState($stateId, $columns = ['id', 'name'], $activeOnly = true)
    {
        try {
            $columnList = implode(', ', array_map(fn($col) => "d.$col", $columns));
            $where = ["d.state_id = :state_id"];
            $params = ['state_id' => $stateId];

            if ($activeOnly) {
                $where[] = "d.is_active = 1";
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT {$columnList} FROM districts d {$whereClause} ORDER BY d.name ASC";

            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':state_id', $stateId);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in District::getByState: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all districts
     * 
     * @param array $columns Columns to select (default: *)
     * @return array
     */
    public static function getAll($columns = ['*'])
    {
        try {
            $columnList = implode(', ', array_map(fn($col) => "d.$col", $columns));
            $sql = "SELECT {$columnList} FROM districts d ORDER BY d.name ASC";

            $db = \App\Core\Database::getInstance();
            return $db->fetchAll($sql);
        } catch (\Exception $e) {
            error_log('Error in District::getAll: ' . $e->getMessage());
            return [];
        }
    }
}
