<?php

namespace App\Models;

use App\Core\UnifiedModel;

class Inquiry extends UnifiedModel
{
    public static $table = 'inquiries';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'property_id',
        'project_id',
        'type',
        'status',
        'priority',
        'assigned_to'
    ];
}
