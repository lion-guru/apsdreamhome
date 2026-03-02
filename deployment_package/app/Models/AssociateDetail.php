<?php

namespace App\Models;

use App\Core\Database\Model;

class AssociateDetail extends Model
{
    public static $table = 'associate_details';

    protected array $fillable = [
        'user_id',
        'pan_number',
        'aadhar_number',
        'created_at',
        'updated_at'
    ];
}
