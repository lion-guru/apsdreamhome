<?php

namespace App\Models;

use App\Core\UnifiedModel;

class Service extends UnifiedModel
{
    public static $table = 'services';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'title',
        'description',
        'icon',
        'color',
        'display_order',
        'status'
    ];
}
