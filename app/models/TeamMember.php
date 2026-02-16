<?php

namespace App\Models;

use App\Core\UnifiedModel;

class TeamMember extends UnifiedModel
{
    public static $table = 'team_members';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'name',
        'position',
        'bio',
        'photo',
        'email',
        'phone',
        'linkedin',
        'expertise',
        'experience',
        'display_order',
        'status'
    ];
}
