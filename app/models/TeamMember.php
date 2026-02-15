<?php

namespace App\Models;

use App\Core\UnifiedModel;

class TeamMember extends UnifiedModel
{
    public static $table = 'team_members';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'name',
        'designation',
        'department',
        'image_path',
        'bio',
        'email',
        'phone',
        'linkedin_url',
        'twitter_url',
        'display_order',
        'status'
    ];
}
