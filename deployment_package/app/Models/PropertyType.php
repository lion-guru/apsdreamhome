<?php

namespace App\Models;

use App\Core\UnifiedModel;

class PropertyType extends UnifiedModel {
    public static $table = 'property_types';
    protected array $fillable = ['name', 'description', 'purpose', 'icon', 'sort_order'];

    public function getAllOrdered() {
        return static::query()
            ->select(['id', 'name', 'description', 'purpose'])
            ->from(static::$table)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get property types for select dropdowns
     */
    public static function getForSelect() {
        return static::query()
            ->select(['id', 'name'])
            ->from(static::$table)
            ->orderBy('name')
            ->get();
    }
}
