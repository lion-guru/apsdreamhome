<?php
/**
 * Mobile Device Model
 */

namespace App\Models;

class MobileDevice extends Model {
    public static $table = 'mobile_devices';
    
    protected array $fillable = [
        'device_user',
        'push_token',
        'platform',
        'created_at',
        'updated_at'
    ];

    /**
     * Find a device by token and user email
     */
    public function findDevice($token, $userEmail) {
        return static::query()
            ->select(['id'])
            ->where('push_token', $token)
            ->where('device_user', $userEmail)
            ->first();
    }

    /**
     * Unregister a device
     */
    public function unregisterDevice($token, $userEmail) {
        return static::query()
            ->where('push_token', $token)
            ->where('device_user', $userEmail)
            ->delete();
    }
}
