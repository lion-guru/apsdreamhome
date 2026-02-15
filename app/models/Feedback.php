<?php
/**
 * Feedback Model
 */

namespace App\Models;

class Feedback extends Model {
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
