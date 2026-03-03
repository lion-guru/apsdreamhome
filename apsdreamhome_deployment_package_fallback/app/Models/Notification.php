<?php
/**
 * Notification Model
 */

namespace App\Models;

class Notification extends Model {
    public static $table = 'notifications';
    
    protected array $fillable = [
        'user_id',
        'type',
        'message',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Get recent notifications for a user
     */
    public function getForUser($userId, $limit = 50, $offset = 0) {
        return static::query()
            ->select(['id', 'type', 'message', 'status', 'created_at'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->skip($offset)
            ->get();
    }
}
