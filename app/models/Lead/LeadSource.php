<?php

namespace App\Models\Lead;

use App\Core\Database\Model;

class LeadSource extends Model
{
    protected static $table = 'lead_sources';

    protected array $fillable = [
        'name',
        'is_active',
        'description',
    ];

    public static function active()
    {
        return static::where('is_active', '=', 1)->get();
    }
}
