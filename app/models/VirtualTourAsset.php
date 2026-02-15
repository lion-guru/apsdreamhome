<?php

namespace App\Models;

use App\Core\UnifiedModel;

class VirtualTourAsset extends UnifiedModel
{
    public static $table = 'virtual_tour_assets';
    public static $primaryKey = 'id';

    protected array $fillable = [
        'tour_id',
        'file_path',
        'title',
        'description',
        'sort_order',
        'created_at'
    ];
}
