<?php

namespace App\Models;

use App\Core\Database\Model;

class users extends Model
{
    public static $table = 'users';

    protected $fillable = [
        'user_id',
        'agent_license_number',
        'agent_experience_years',
        'created_at',
        'updated_at'
    ];
}
