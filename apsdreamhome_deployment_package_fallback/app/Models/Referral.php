<?php
/**
 * Referral Model
 */

namespace App\Models;

class Referral extends Model {
    public static $table = 'referrals';
    
    protected array $fillable = [
        'referrer_id',
        'referred_email',
        'referral_code',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Generate a unique referral code
     */
    public static function generateCode(): string {
        return \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(8));
    }
}
