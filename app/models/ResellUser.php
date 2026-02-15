<?php

namespace App\Models;

class ResellUser extends Model
{
    public static $table = 'resell_users';

    protected array $fillable = [
        'full_name',
        'mobile',
        'email',
        'registration_date'
    ];

    /**
     * Get user by mobile or email
     */
    public static function getByContact($mobile, $email)
    {
        return static::query()
            ->where('mobile', $mobile)
            ->orWhere('email', $email)
            ->first();
    }
}
