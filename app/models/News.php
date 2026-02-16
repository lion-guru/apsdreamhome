<?php

namespace App\Models;

use App\Core\UnifiedModel;

class News extends UnifiedModel
{
    public static $table = 'news';
    public static $primaryKey = 'id';

    protected array $fillable = [
        'title',
        'date',
        'summary',
        'image',
        'content'
    ];

    /**
     * Get published news with pagination and filtering
     */
    public static function getPublished($limit = 10, $offset = 0, $category = 'all')
    {
        $db = static::getConnection();
        $params = [];

        // Since status and category columns don't exist in the current schema,
        // we select all news items ordered by date/created_at.
        $sql = "SELECT * FROM news ORDER BY created_at DESC LIMIT ? OFFSET ?";

        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Count published news for pagination
     */
    public static function countPublished($category = 'all')
    {
        $db = static::getConnection();

        // Count all news items
        $sql = "SELECT COUNT(*) as total FROM news";

        $stmt = $db->query($sql);

        return $stmt->fetch(\PDO::FETCH_OBJ)->total;
    }

    /**
     * Get distinct categories
     */
    public static function getCategories()
    {
        // Category column doesn't exist, return empty array
        return [];
    }
}
