<?php

namespace App\Models;

use App\Core\Database\Model;

class InvestorDetail extends Model
{
    public static $table = 'investor_details';

    protected array $fillable = [
        'user_id',
        'investment_range',
        'investment_type',
        'created_at',
        'updated_at'
    ];
}
