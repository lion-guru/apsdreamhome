<?php
/**
 * AI Workflow Model
 */

namespace App\Models;

class AIWorkflow extends Model {
    public static $table = 'ai_workflows';
    
    protected array $fillable = [
        'name',
        'description',
        'trigger_type',
        'trigger_config',
        'actions',
        'is_active',
        'created_by',
        'created_at',
        'updated_at'
    ];

    /**
     * Get all workflows ordered by creation date
     */
    public function getAllWorkflows()
    {
        return static::query()
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
