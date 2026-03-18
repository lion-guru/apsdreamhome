<?php

namespace App\Models;

use App\Core\UnifiedModel;

/**
 * Placeholder MLMAdvancedAnalytics Model
 */
class MLMAdvancedAnalytics extends UnifiedModel
{
    public static $table = 'mlm_advanced_analytics';
    public static $primaryKey = 'id';

    protected array $fillable = [
        'id',
        'user_id',
        'mlm_level',
        'commission_data',
        'performance_metrics',
        'created_at'
    ];
}
