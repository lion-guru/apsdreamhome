<?php

namespace App\Models;

class Property extends Model {
    protected static string $table = 'properties';
    protected array $fillable = [
        'title',
        'description',
        'price',
        'location',
        'property_type',
        'bedrooms',
        'bathrooms',
        'area',
        'is_featured',
        'status',
        'image_path',
        'created_at',
        'updated_at'
    ];

    public function getFeaturedProperties($limit = 6) {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->query(
                "SELECT * FROM properties
                 WHERE is_featured = 1
                 AND status = 'active'
                 ORDER BY created_at DESC
                 LIMIT ?",
                [$limit]
            );

            $results = $stmt->fetchAll();
            return array_map(fn($result) => new static($result), $results);
        } catch (\Exception $e) {
            error_log("Error in getFeaturedProperties: " . $e->getMessage());
            return [];
        }
    }

    public function getPropertyById($id) {
        try {
            return static::find($id);
        } catch (\Exception $e) {
            error_log("Error in getPropertyById: " . $e->getMessage());
            return null;
        }
    }
}
