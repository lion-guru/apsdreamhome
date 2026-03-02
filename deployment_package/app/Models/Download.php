<?php

namespace App\Models;

use App\Core\Database\Model;
use App\Core\Database;

class Download extends Model
{
    protected static $table = 'downloads';

    protected $fillable = [
        'title',
        'description',
        'category',
        'file_path',
        'file_size',
        'priority',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Get active downloads
     */
    public static function getActive($category = 'all', $limit = 12, $offset = 0)
    {
        $query = static::query()->where('status', 'active');

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        return $query->orderBy('priority', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Get active categories
     */
    public static function getCategories()
    {
        return static::query()
            ->select('category')
            ->distinct()
            ->where('status', 'active')
            ->orderBy('category', 'ASC')
            ->get();
    }

    /**
     * Count active downloads
     */
    public static function countActive($category = 'all')
    {
        // Use the DB connection directly for count to be efficient
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as total FROM " . static::getTable() . " WHERE status = 'active'";
        $params = [];

        if ($category !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    }
}
