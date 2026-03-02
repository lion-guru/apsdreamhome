<?php
/**
 * Feedback Model
 */

namespace App\Models;

use App\Core\UnifiedModel;

class Feedback extends UnifiedModel {
    public static $table = 'feedback';
    
    protected array $fillable = [
        'name',
        'rating',
        'message',
        'status',
        'created_at',
        'updated_at'
    ];
}
