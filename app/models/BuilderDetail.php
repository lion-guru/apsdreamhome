<?php

namespace App\Models;

use App\Core\Database\Model;

class BuilderDetail extends Model
{
    public static $table = 'builder_details';

    protected array $fillable = [
        'user_id',
        'company_name',
        'rera_registration',
        'created_at',
        'updated_at'
    ];
}
