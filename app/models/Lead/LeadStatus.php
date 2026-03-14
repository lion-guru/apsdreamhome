<?php

namespace App\Models\Lead;

use App\Core\Database\Model;

/**
 * LeadStatus Model
 * 
 * @property string $name
 * @property string $color
 * @property bool $is_default
 * @property bool $is_active
 * @property string $description
 * @property int $sort_order
 */
class LeadStatus extends Model
{
    protected static $table = 'lead_statuses';

    protected array $fillable = [
        'name',
        'color',
        'is_default',
        'is_active',
        'description',
        'sort_order',
    ];

    protected array $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function active()
    {
        return static::where('is_active', '=', 1)->orderBy('sort_order', 'ASC')->get();
    }

    public static function getDefault()
    {
        return static::where('is_default', '=', 1)->first();
    }
}
