<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadSource extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'color',
        'icon',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the leads associated with this source.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'source_id');
    }

    /**
     * Scope a query to only include active sources.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default icon if none is set.
     *
     * @param  string|null  $value
     * @return string
     */
    public function getIconAttribute($value)
    {
        return $value ?? 'fa fa-question-circle';
    }

    /**
     * Get the default color if none is set.
     *
     * @param  string|null  $value
     * @return string
     */
    public function getColorAttribute($value)
    {
        return $value ?? '#6c757d';
    }
}
