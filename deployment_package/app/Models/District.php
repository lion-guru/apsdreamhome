<?php

namespace App\Models;

use App\Models\Model;

class District extends Model
{
    public static $table = 'districts';

    protected array $fillable = [
        'state_id',
        'name',
        'code',
        'status'
    ];
}
