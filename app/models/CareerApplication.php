<?php

namespace App\Models;

use App\Core\UnifiedModel;

class CareerApplication extends UnifiedModel
{
    public static $table = 'career_applications';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'career_id',
        'name',
        'email',
        'phone',
        'resume_path',
        'cover_letter',
        'status'
    ];
}
