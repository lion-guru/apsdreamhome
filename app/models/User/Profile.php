<?php

namespace App\Models;

use App\Core\Database\Model;

class CustomerProfile extends Model
{
    public static $table = 'users';

    protected $fillable = [
        'customer_number',
        'user_id',
        'name',
        'email',
        'phone',
        'created_at',
        'updated_at'
    ];
}
