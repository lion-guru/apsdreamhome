<?php

namespace App\Models;

use App\Core\Database\Model;

class MlmProfile extends Model
{
    public static $table = 'mlm_profiles';

    protected array $fillable = [
        'user_id',
        'referral_code',
        'sponsor_user_id',
        'sponsor_code',
        'user_type',
        'verification_status',
        'status',
        'direct_referrals'
    ];

    public static function getByReferralCode($code)
    {
        return static::query()
            ->where('referral_code', $code)
            ->where('status', 'active')
            ->first();
    }
}
