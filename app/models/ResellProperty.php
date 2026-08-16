<?php

namespace App\Models;

class ResellProperty extends Model
{
    public static $table = 'resell_properties';
    protected static $tenantScoped = true;

    protected $fillable = [
        'user_id',
        'title',
        'property_type',
        'asking_price',
        'bedrooms',
        'bathrooms',
        'area_sqft',
        'location',
        'district_id',
        'description',
        'amenities',
        'status',
        'created_at',
    ];

    /**
     * Get active resell properties with user info
     */
    public static function getActiveWithUser($filters = [])
    {
        $query = static::query()
            ->select('resell_properties.*', 'users.name as full_name', 'users.phone as user_phone', 'users.email as user_email')
            ->join('users', 'resell_properties.user_id', '=', 'users.id')
            ->where('resell_properties.status', 'active');

        if (!empty($filters['search'])) {
            $searchTerm = "%{$filters['search']}%";
            $query->where(function($q) use ($searchTerm) {
                $q->where('resell_properties.title', 'LIKE', $searchTerm)
                  ->orWhere('resell_properties.location', 'LIKE', $searchTerm)
                  ->orWhere('resell_properties.description', 'LIKE', $searchTerm);
            });
        }

        if (!empty($filters['type'])) {
            $query->where('resell_properties.property_type', $filters['type']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('resell_properties.asking_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('resell_properties.asking_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['bedrooms'])) {
            $query->where('resell_properties.bedrooms', (int)$filters['bedrooms']);
        }

        return $query->orderBy('resell_properties.created_at', 'DESC')
            ->get();
    }

    /**
     * Get distinct values for a column
     */
    public static function getDistinct($column, $where = [])
    {
        $query = static::query()->select($column)->groupBy($column);
        foreach ($where as $key => $value) {
            $query->where($key, $value);
        }
        return $query->pluck($column);
    }
}
