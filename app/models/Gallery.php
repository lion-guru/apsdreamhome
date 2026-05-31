<?php

namespace App\Models;

use App\Core\UnifiedModel;

class Gallery extends UnifiedModel
{
    public static $table = 'gallery_images';
    public static $primaryKey = 'id';
    
    protected $fillable = [
        'category',
        'image_path',
        'caption',
        'status'
    ];
}
