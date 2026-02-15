<?php

namespace App\Models;

use App\Core\UnifiedModel;

class VirtualTour extends UnifiedModel
{
    public static $table = 'virtual_tours';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'title',
        'description',
        'property_id',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Get assets for this tour
     */
    public function assets()
    {
        return VirtualTourAsset::where('tour_id', $this->id)
            ->orderBy('sort_order', 'ASC')
            ->get();
    }
}
