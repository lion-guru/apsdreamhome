<?php

namespace App\Models;

class ResellProperty extends Model
{
    public static $table = 'resell_properties';

    protected array $fillable = [
        'user_id',
        'title',
        'property_type',
        'price',
        'bedrooms',
        'bathrooms',
        'area',
        'address',
        'city',
        'state',
        'description',
        'features',
        'status',
        'created_at',
        'is_featured'
    ];

    /**
     * Get active resell properties with user info
     */
    public static function getActiveWithUser($filters = [])
    {
        $query = static::query()
            ->select('resell_properties.*', 'resell_users.full_name', 'resell_users.mobile', 'resell_users.email as user_email')
            ->join('resell_users', 'resell_properties.user_id', '=', 'resell_users.id')
            ->where('resell_properties.status', 'approved');

        if (!empty($filters['search'])) {
            $searchTerm = "%{$filters['search']}%";
            $query->where(function($q) use ($searchTerm) {
                $q->where('resell_properties.title', 'LIKE', $searchTerm)
                  ->orWhere('resell_properties.address', 'LIKE', $searchTerm)
                  ->orWhere('resell_properties.description', 'LIKE', $searchTerm);
            });
        }

        if (!empty($filters['city'])) {
            $query->where('resell_properties.city', $filters['city']);
        }

        if (!empty($filters['type'])) {
            $query->where('resell_properties.property_type', $filters['type']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('resell_properties.price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('resell_properties.price', '<=', $filters['max_price']);
        }

        if (!empty($filters['bedrooms'])) {
            $query->where('resell_properties.bedrooms', (int)$filters['bedrooms']);
        }

        return $query->orderBy('resell_properties.is_featured', 'DESC')
            ->orderBy('resell_properties.created_at', 'DESC')
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

    /**
     * Get price range for approved properties
     */
    public static function getPriceRange($status = 'approved')
    {
        $query = static::query()->where('status', $status);
        return [
            'min_price' => $query->min('price'),
            'max_price' => $query->max('price')
        ];
    }
}
