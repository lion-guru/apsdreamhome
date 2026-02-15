<?php

namespace App\Models;

use App\Core\Database\Model;

class PasswordResetToken extends Model
{
    public static $table = 'password_reset_tokens';

    protected array $fillable = [
        'user_id',
        'token',
        'expires_at',
        'ip_address',
        'user_agent'
    ];
}
