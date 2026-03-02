<?php

namespace App\Models;

use App\Core\UnifiedModel;

class Faq extends UnifiedModel
{
    public static $table = 'faqs';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'question',
        'answer',
        'category',
        'display_order',
        'status'
    ];
}
