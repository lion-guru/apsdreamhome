<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadDeal extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'deal_name',
        'deal_value',
        'currency',
        'expected_close_date',
        'probability',
        'deal_stage',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'deal_value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expected_close_date',
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_deal_value',
        'deal_stage_label',
        'status_label',
    ];

    /**
     * Deal stages with their labels and colors.
     *
     * @var array
     */
    protected static $dealStages = [
        'prospect' => ['label' => 'Prospect', 'color' => '#6c757d'],
        'qualification' => ['label' => 'Qualification', 'color' => '#17a2b8'],
        'needs_analysis' => ['label' => 'Needs Analysis', 'color' => '#007bff'],
        'proposal' => ['label' => 'Proposal', 'color' => '#6610f2'],
        'negotiation' => ['label' => 'Negotiation', 'color' => '#fd7e14'],
        'won' => ['label' => 'Won', 'color' => '#28a745'],
        'lost' => ['label' => 'Lost', 'color' => '#dc3545'],
    ];

    /**
     * Status options with their labels and colors.
     *
     * @var array
     */
    protected static $statuses = [
        'open' => ['label' => 'Open', 'color' => '#17a2b8'],
        'in_progress' => ['label' => 'In Progress', 'color' => '#007bff'],
        'on_hold' => ['label' => 'On Hold', 'color' => '#6c757d'],
        'closed' => ['label' => 'Closed', 'color' => '#28a745'],
        'cancelled' => ['label' => 'Cancelled', 'color' => '#dc3545'],
    ];

    /**
     * Get the lead that owns the deal.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who created the deal.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the deal.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the activities for the deal.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class, 'related_id')
            ->where('related_type', 'deal');
    }

    /**
     * Get the formatted deal value with currency symbol.
     *
     * @return string
     */
    public function getFormattedDealValueAttribute()
    {
        $currency = $this->currency ?: config('app.currency', 'USD');
        $symbol = $this->getCurrencySymbol($currency);
        
        return $symbol . number_format($this->deal_value, 2);
    }

    /**
     * Get the deal stage label.
     *
     * @return string
     */
    public function getDealStageLabelAttribute()
    {
        $stage = $this->deal_stage;
        
        if (isset(self::$dealStages[$stage])) {
            return self::$dealStages[$stage]['label'];
        }
        
        return ucfirst(str_replace('_', ' ', $stage));
    }

    /**
     * Get the status label.
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        $status = $this->status;
        
        if (isset(self::$statuses[$status])) {
            return self::$statuses[$status]['label'];
        }
        
        return ucfirst($status);
    }

    /**
     * Get the deal stage color.
     *
     * @return string
     */
    public function getDealStageColor()
    {
        $stage = $this->deal_stage;
        
        if (isset(self::$dealStages[$stage])) {
            return self::$dealStages[$stage]['color'];
        }
        
        return '#6c757d';
    }

    /**
     * Get the status color.
     *
     * @return string
     */
    public function getStatusColor()
    {
        $status = $this->status;
        
        if (isset(self::$statuses[$status])) {
            return self::$statuses[$status]['color'];
        }
        
        return '#6c757d';
    }

    /**
     * Get all available deal stages.
     *
     * @return array
     */
    public static function getDealStages()
    {
        return collect(self::$dealStages)->map(function ($stage, $key) {
            return [
                'id' => $key,
                'name' => $stage['label'],
                'color' => $stage['color'],
            ];
        })->values()->toArray();
    }

    /**
     * Get all available statuses.
     *
     * @return array
     */
    public static function getStatuses()
    {
        return collect(self::$statuses)->map(function ($status, $key) {
            return [
                'id' => $key,
                'name' => $status['label'],
                'color' => $status['color'],
            ];
        })->values()->toArray();
    }

    /**
     * Get currency symbol.
     *
     * @param  string  $currency
     * @return string
     */
    protected function getCurrencySymbol($currency)
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CHF' => 'CHF ',
            'CNY' => '¥',
            'SEK' => 'kr',
            'NZD' => 'NZ$',
            'MXN' => 'MX$',
            'SGD' => 'S$',
            'HKD' => 'HK$',
            'NOK' => 'kr',
            'KRW' => '₩',
            'TRY' => '₺',
            'RUB' => '₽',
            'BRL' => 'R$',
            'ZAR' => 'R',
        ];
        
        return $symbols[strtoupper($currency)] ?? $currency . ' ';
    }

    /**
     * Scope a query to only include deals with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include deals in a specific stage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $stage
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStage($query, $stage)
    {
        return $query->where('deal_stage', $stage);
    }

    /**
     * Scope a query to only include deals with an expected close date in the future.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('expected_close_date', '>=', now())
            ->orderBy('expected_close_date');
    }

    /**
     * Scope a query to only include deals with an expected close date in the past.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->where('expected_close_date', '<', now())
            ->whereNotIn('status', ['won', 'lost', 'cancelled'])
            ->orderBy('expected_close_date');
    }

    /**
     * Check if the deal is won.
     *
     * @return bool
     */
    public function isWon()
    {
        return $this->status === 'won' || $this->deal_stage === 'won';
    }

    /**
     * Check if the deal is lost.
     *
     * @return bool
     */
    public function isLost()
    {
        return $this->status === 'lost' || $this->deal_stage === 'lost';
    }

    /**
     * Check if the deal is closed.
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->isWon() || $this->isLost();
    }
}
