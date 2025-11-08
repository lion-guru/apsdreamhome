<?php

namespace App\Models;

class LeadDeal extends Model
{
    protected static string $table = 'lead_deals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
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
    protected array $casts = [
        'deal_value' => 'float',
        'probability' => 'integer',
        'expected_close_date' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    /**
     * Deal stages with their labels and colors.
     *
     * @var array
     */
    protected static array $dealStages = [
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
    protected static array $statuses = [
        'open' => ['label' => 'Open', 'color' => '#17a2b8'],
        'in_progress' => ['label' => 'In Progress', 'color' => '#007bff'],
        'on_hold' => ['label' => 'On Hold', 'color' => '#6c757d'],
        'closed' => ['label' => 'Closed', 'color' => '#28a745'],
        'cancelled' => ['label' => 'Cancelled', 'color' => '#dc3545'],
    ];

    /**
     * Get the lead that owns the deal.
     */
    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    /**
     * Get the user who created the deal.
     */
    public function creator()
    {
        return User::find($this->created_by);
    }

    /**
     * Get the user who last updated the deal.
     */
    public function updater()
    {
        return User::find($this->updated_by);
    }

    /**
     * Get the activities for the deal.
     */
    public function activities()
    {
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_activities WHERE related_id = ? AND related_type = 'deal'");
        $stmt->execute([$this->id]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $activities = [];
        foreach ($results as $result) {
            $activities[] = new LeadActivity($result);
        }

        return $activities;
    }

    /**
     * Get the formatted deal value with currency symbol.
     */
    public function getFormattedDealValueAttribute()
    {
        $currency = $this->currency ?: 'INR'; // Default to INR
        $symbol = $this->getCurrencySymbol($currency);

        return $symbol . number_format($this->deal_value, 2);
    }

    /**
     * Get the deal stage label.
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
     */
    public static function getDealStages()
    {
        $stages = [];
        foreach (self::$dealStages as $key => $stage) {
            $stages[] = [
                'id' => $key,
                'name' => $stage['label'],
                'color' => $stage['color'],
            ];
        }
        return $stages;
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses()
    {
        $statuses = [];
        foreach (self::$statuses as $key => $status) {
            $statuses[] = [
                'id' => $key,
                'name' => $status['label'],
                'color' => $status['color'],
            ];
        }
        return $statuses;
    }

    /**
     * Get currency symbol.
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
     * Get deals with a specific status.
     */
    public static function status($status)
    {
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_deals WHERE status = ?");
        $stmt->execute([$status]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $deals = [];
        foreach ($results as $result) {
            $deals[] = new LeadDeal($result);
        }

        return $deals;
    }

    /**
     * Get deals in a specific stage.
     */
    public static function stage($stage)
    {
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_deals WHERE deal_stage = ?");
        $stmt->execute([$stage]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $deals = [];
        foreach ($results as $result) {
            $deals[] = new LeadDeal($result);
        }

        return $deals;
    }

    /**
     * Get deals with an expected close date in the future.
     */
    public static function upcoming()
    {
        $today = date('Y-m-d');
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_deals WHERE expected_close_date >= ? ORDER BY expected_close_date ASC");
        $stmt->execute([$today]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $deals = [];
        foreach ($results as $result) {
            $deals[] = new LeadDeal($result);
        }

        return $deals;
    }

    /**
     * Get deals with an expected close date in the past.
     */
    public static function overdue()
    {
        $today = date('Y-m-d');
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_deals WHERE expected_close_date < ? AND status NOT IN ('won', 'lost', 'cancelled') ORDER BY expected_close_date ASC");
        $stmt->execute([$today]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $deals = [];
        foreach ($results as $result) {
            $deals[] = new LeadDeal($result);
        }

        return $deals;
    }

    /**
     * Check if the deal is won.
     */
    public function isWon()
    {
        return $this->status === 'won' || $this->deal_stage === 'won';
    }

    /**
     * Check if the deal is lost.
     */
    public function isLost()
    {
        return $this->status === 'lost' || $this->deal_stage === 'lost';
    }

    /**
     * Check if the deal is closed.
     */
    public function isClosed()
    {
        return $this->isWon() || $this->isLost();
    }
}
