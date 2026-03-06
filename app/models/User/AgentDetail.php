<?php

namespace App\Models;

use App\Core\Database\Model;

class AgentDetail extends Model
{
    public static $table = 'agent_details';

    protected array $fillable = [
        'user_id',
        'license_number',
        'experience_years',
        'created_at',
        'updated_at'
    ];
}
