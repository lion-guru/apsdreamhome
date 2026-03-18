<?php

namespace App\Models;

use App\Core\UnifiedModel;

/**
 * Placeholder AssociateMLM Model
 */
class AssociateMLM extends UnifiedModel
{
    public static $table = 'associate_mlm';
    public static $primaryKey = 'id';

    protected array $fillable = [
        'id',
        'user_id',
        'mlm_level',
        'created_at'
    ];
}
