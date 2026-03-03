<?php

namespace App\Models;

use App\Core\UnifiedModel;

class Career extends UnifiedModel
{
    public static $table = 'careers';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'title',
        'description',
        'type',
        'location',
        'salary_range',
        'status'
    ];
}
