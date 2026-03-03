<?php

namespace App\Models;

use App\Core\Database\Model;

class MlmNetworkTree extends Model
{
    public static $table = 'mlm_network_tree';

    protected array $fillable = [
        'ancestor_user_id',
        'descendant_user_id',
        'level',
        'created_at'
    ];
}
