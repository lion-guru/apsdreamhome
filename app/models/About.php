<?php

namespace App\Models;

use App\Models\Model;

class About extends Model
{
    protected static $table = 'about';
    protected array $fillable = [
        'title',
        'content',
        'image',
        'created_at',
        'updated_at'
    ];
}
