<?php

namespace App\Models;

use App\Core\Database\Model;

class MlmLevel extends Model
{
    public static $table = 'mlm_levels';

    protected array $fillable = [
        'level_name',
        'level_order',
        'commission_percentage',
        'requirements'
    ];
}
