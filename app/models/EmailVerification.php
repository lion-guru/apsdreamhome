<?php

namespace App\Models;

class EmailVerification extends Model {
    protected static string $table = 'email_verifications';
    protected array $fillable = [
        'user_id',
        'token',
        'expires_at',
        'created_at'
    ];

    public static function createToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $verification = new self();
        $verification->user_id = $userId;
        $verification->token = $token;
        $verification->expires_at = $expiresAt;

        if ($verification->save()) {
            return $token;
        }

        return false;
    }

    public static function isValidToken($token) {
        // Get all records matching the token
        $verifications = self::where('token', '=', $token);

        foreach ($verifications as $verification) {
            // Check if token is not expired
            if ($verification->expires_at > date('Y-m-d H:i:s')) {
                return $verification;
            }
        }

        return false;
    }
}
