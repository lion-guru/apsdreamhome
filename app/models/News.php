<?php

namespace App\Models;

use App\Core\UnifiedModel;

class News extends UnifiedModel
{
    public static $table = 'news';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'title',
        'date',
        'summary',
        'image',
        'content'
    ];
}
