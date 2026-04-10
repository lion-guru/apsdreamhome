<?php

namespace App\Models;

/**
 * Colony Model
 * Handles colony-related database operations
 */
class Colony extends UnifiedModel
{
    protected $table = 'colonies';
    
    protected $fillable = [
        'district_id',
        'name',
        'code',
        'description',
        'amenities',
        'map_link',
        'total_plots',
        'available_plots',
        'starting_price',
        'image_path',
        'brochure_path',
        'is_featured',
        'is_active'
    ];

    /**
     * Get active colonies
     */
    public static function getActive($columns = ['*'])
    {
        $db = \App\Core\Database::getInstance();
        $colList = is_array($columns) ? implode(', ', $columns) : $columns;
        
        $sql = "SELECT {$colList} FROM colonies WHERE is_active = 1 ORDER BY name";
        $stmt = $db->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get colonies for select dropdown
     */
    public static function getForSelect($columns = ['id', 'name'], $activeOnly = true)
    {
        $db = \App\Core\Database::getInstance();
        $colList = is_array($columns) ? implode(', ', $columns) : $columns;
        
        $sql = "SELECT {$colList} FROM colonies";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY name";
        
        $stmt = $db->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get colonies with district and state names
     */
    public static function getWithDistrictAndStateName($columns = ['c.*', 'd.name as district_name', 's.name as state_name'], $activeOnly = true)
    {
        $db = \App\Core\Database::getInstance();
        $colList = is_array($columns) ? implode(', ', $columns) : $columns;
        
        $sql = "SELECT {$colList} 
                FROM colonies c 
                LEFT JOIN districts d ON c.district_id = d.id 
                LEFT JOIN states s ON d.state_id = s.id";
        
        if ($activeOnly) {
            $sql .= " WHERE c.is_active = 1";
        }
        
        $sql .= " ORDER BY s.name, d.name, c.name";
        
        $stmt = $db->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get colonies by district
     */
    public static function getByDistrict($districtId, $columns = ['*'], $activeOnly = true)
    {
        $db = \App\Core\Database::getInstance();
        $colList = is_array($columns) ? implode(', ', $columns) : $columns;
        
        $sql = "SELECT {$colList} FROM colonies WHERE district_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY name";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$districtId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all colonies
     */
    public static function getAll($columns = ['*'])
    {
        $db = \App\Core\Database::getInstance();
        $colList = is_array($columns) ? implode(', ', $columns) : $columns;
        
        $sql = "SELECT {$colList} FROM colonies ORDER BY name";
        $stmt = $db->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
