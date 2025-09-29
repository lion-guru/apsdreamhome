<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStatus extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'color',
        'is_default',
        'is_active',
        'description',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->is_default) {
                static::where('is_default', true)->update(['is_default' => false]);
            }
        });

        static::updating(function ($model) {
            if ($model->is_default) {
                static::where('id', '!=', $model->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get the leads with this status.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'status_id');
    }

    /**
     * Get the status history entries for this status.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(LeadStatusHistory::class, 'status_id');
    }

    /**
     * Scope a query to only include active statuses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default status.
     *
     * @return \App\Models\LeadStatus|null
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }
}
