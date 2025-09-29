<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'company',
        'job_title',
        'website',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'source',
        'status',
        'rating',
        'estimated_value',
        'description',
        'last_contact_date',
        'next_followup_date',
        'assigned_to',
        'created_by',
        'updated_by',
        'custom_fields',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'estimated_value' => 'decimal:2',
        'rating' => 'integer',
        'last_contact_date' => 'datetime',
        'next_followup_date' => 'datetime',
        'custom_fields' => 'array',
        'is_deleted' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_name'];

    /**
     * Get the user assigned to this lead.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this lead.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this lead.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all activities for the lead.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all notes for the lead.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all files for the lead.
     */
    public function files(): HasMany
    {
        return $this->hasMany(LeadFile::class)->orderBy('created_at', 'desc');
    }

    /**
     * The tags that belong to the lead.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(LeadTag::class, 'lead_tag_mapping', 'lead_id', 'tag_id')
            ->withTimestamps()
            ->withPivot('created_by')
            ->using(LeadTagMapping::class);
    }

    /**
     * Get the status history for the lead.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(LeadStatusHistory::class)->orderBy('changed_at', 'desc');
    }

    /**
     * Get the assignment history for the lead.
     */
    public function assignmentHistory(): HasMany
    {
        return $this->hasMany(LeadAssignmentHistory::class)->orderBy('assigned_at', 'desc');
    }

    /**
     * Get the custom field values for the lead.
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(LeadCustomFieldValue::class);
    }

    /**
     * Get the full name of the lead.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Scope a query to only include leads assigned to the given user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to only include leads with the given status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include leads from the given source.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $source
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromSource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope a query to only include leads created in the given date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $from
     * @param  string  $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope a query to only include leads that match the search term.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('company', 'like', "%{$search}%");
        });
    }

    /**
     * Get the status name with color.
     *
     * @return array
     */
    public function getStatusAttribute($value): array
    {
        $status = LeadStatus::where('name', $value)->first();
        
        return [
            'name' => $value,
            'label' => $status ? $status->label : ucfirst(str_replace('_', ' ', $value)),
            'color' => $status ? $status->color : '#6c757d',
        ];
    }

    /**
     * Get the source name with label.
     *
     * @return array
     */
    public function getSourceAttribute($value): array
    {
        $source = LeadSource::where('name', $value)->first();
        
        return [
            'name' => $value,
            'label' => $source ? $source->label : ucfirst(str_replace('_', ' ', $value)),
        ];
    }
}
