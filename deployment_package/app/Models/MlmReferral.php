<?php

namespace App\Models;

use App\Core\Database\Model;

class MlmReferral extends Model
{
    public static $table = 'mlm_referrals';

    protected array $fillable = [
        'referrer_user_id',
        'referred_user_id',
        'referral_type',
        'created_at'
    ];
}
