<?php

namespace App\Models;

use App\Core\UnifiedModel;

class Gallery extends UnifiedModel
{
    public static $table = 'gallery';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'image_path',
        'caption',
        'status'
    ];
}
