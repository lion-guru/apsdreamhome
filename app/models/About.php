<?php

namespace App\Models;

use App\Models\Model;

class About extends Model
{
    protected static string $table = 'about';
    protected array $fillable = [
        'title',
        'content',
        'image',
        'created_at',
        'updated_at'
    ];
}
