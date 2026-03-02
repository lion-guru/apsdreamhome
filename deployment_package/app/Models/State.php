<?php

namespace App\Models;

use App\Models\Model;

class State extends Model
{
    public static $table = 'states';

    protected array $fillable = [
        'name',
        'code',
        'status'
    ];
}
