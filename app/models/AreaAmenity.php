<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Models;

use App\Core\UnifiedModel;

class AreaAmenity extends UnifiedModel {
    public static $table = 'area_amenities';
    protected array $fillable = ['city', 'name', 'type', 'latitude', 'longitude', 'address', 'rating'];

    public function getByCity($city, $type = 'all') {
        $query = static::query()
            ->from(static::$table)
            ->where('city', '=', $city);

        if ($type !== 'all') {
            $query->where('type', '=', $type);
        }

        return $query->get();
    }
}
