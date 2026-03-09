<?php

namespace App\Models;

use App\Core\UnifiedModel;

class LandProject extends UnifiedModel
{
    public static $table = 'land_projects';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'name',
        'location',
        'description',
        'total_area',
        'project_type',
        'developer_name',
        'approval_number',
        'rera_number',
        'status',
        'start_date',
        'completion_date',
        'estimated_cost'
    ];

    /**
     * Get the plots associated with this project
     */
    public function plots()
    {
        return $this->hasMany(Plot::class, 'project_id');
    }

    /**
     * Get the subdivisions for this project
     */
    public function subdivisions()
    {
        return $this->hasMany(LandSubdivision::class, 'project_id');
    }

    /**
     * Get the project stages
     */
    public function stages()
    {
        return $this->hasMany(ProjectStage::class, 'project_id');
    }

    /**
     * Get the project documents
     */
    public function documents()
    {
        return $this->hasMany(ProjectDocument::class, 'project_id');
    }

    /**
     * Get total number of plots
     */
    public function getTotalPlotsAttribute(): int
    {
        return $this->plots()->count();
    }

    /**
     * Get number of sold plots
     */
    public function getSoldPlotsAttribute(): int
    {
        return $this->plots()->where('status', 'sold')->count();
    }

    /**
     * Get number of available plots
     */
    public function getAvailablePlotsAttribute(): int
    {
        return $this->plots()->where('status', 'available')->count();
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute(): float
    {
        return $this->stages()->avg('progress_percentage') ?? 0.0;
    }

    /**
     * Get total revenue from sold plots
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->plots()
            ->where('status', 'sold')
            ->join('plot_sales', 'plots.id', '=', 'plot_sales.plot_id')
            ->sum('plot_sales.total_amount') ?? 0.0;
    }

    /**
     * Get project duration in days
     */
    public function getDurationInDaysAttribute(): int
    {
        if (!$this->start_date) return 0;
        
        $endDate = $this->completion_date ?: date('Y-m-d');
        $start = new \DateTime($this->start_date);
        $end = new \DateTime($endDate);
        
        return $start->diff($end)->days;
    }

    /**
     * Check if project is delayed
     */
    public function isDelayed(): bool
    {
        if (!$this->completion_date) return false;
        
        return date('Y-m-d') > $this->completion_date && $this->status !== 'handover';
    }

    /**
     * Get project health status
     */
    public function getHealthStatusAttribute(): string
    {
        $completion = $this->completion_percentage;
        
        if ($this->isDelayed()) {
            return 'delayed';
        } elseif ($completion >= 80) {
            return 'excellent';
        } elseif ($completion >= 60) {
            return 'good';
        } elseif ($completion >= 40) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get average plot price
     */
    public function getAveragePlotPriceAttribute(): float
    {
        return $this->plots()->avg('price_per_sq_meter') ?? 0.0;
    }

    /**
     * Get project statistics summary
     */
    public function getStatisticsSummary(): array
    {
        return [
            'total_plots' => $this->total_plots,
            'sold_plots' => $this->sold_plots,
            'available_plots' => $this->available_plots,
            'completion_percentage' => $this->completion_percentage,
            'total_revenue' => $this->total_revenue,
            'average_plot_price' => $this->average_plot_price,
            'duration_in_days' => $this->duration_in_days,
            'health_status' => $this->health_status,
            'is_delayed' => $this->isDelayed()
        ];
    }

    /**
     * Get next milestone
     */
    public function getNextMilestone(): ?array
    {
        $nextStage = $this->stages()
            ->where('status', '!=', 'completed')
            ->orderBy('id')
            ->first();
        
        if (!$nextStage) {
            return null;
        }

        return [
            'stage_name' => $nextStage->stage_name,
            'status' => $nextStage->status,
            'progress_percentage' => $nextStage->progress_percentage,
            'estimated_completion' => $nextStage->estimated_completion_date
        ];
    }

    /**
     * Check if project can accept new bookings
     */
    public function canAcceptBookings(): bool
    {
        return in_array($this->status, ['development', 'completion']) && $this->available_plots > 0;
    }

    /**
     * Get project valuation (estimated total value)
     */
    public function getEstimatedValuationAttribute(): float
    {
        return $this->plots()->sum('total_price') ?? 0.0;
    }

    /**
     * Get ROI percentage
     */
    public function getRoiPercentageAttribute(): float
    {
        if (!$this->estimated_cost || $this->estimated_cost <= 0) {
            return 0.0;
        }

        $revenue = $this->total_revenue;
        return (($revenue - $this->estimated_cost) / $this->estimated_cost) * 100;
    }

    /**
     * Get plot distribution by type
     */
    public function getPlotDistributionByType(): array
    {
        return $this->plots()
            ->join('plot_sales', 'plots.id', '=', 'plot_sales.plot_id', 'left outer')
            ->groupBy('plots.plot_type')
            ->selectRaw('plots.plot_type, COUNT(*) as total, COUNT(plot_sales.id) as sold')
            ->get()
            ->toArray();
    }

    /**
     * Get monthly sales trend
     */
    public function getMonthlySalesTrend(): array
    {
        return $this->plots()
            ->join('plot_sales', 'plots.id', '=', 'plot_sales.plot_id')
            ->where('plot_sales.status', 'completed')
            ->selectRaw('DATE_FORMAT(plot_sales.sale_date, "%Y-%m") as month, COUNT(*) as sales, SUM(plot_sales.total_amount) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    /**
     * Get top performing plot types
     */
    public function getTopPerformingPlotTypes(): array
    {
        return $this->plots()
            ->join('plot_sales', 'plots.id', '=', 'plot_sales.plot_id')
            ->where('plot_sales.status', 'completed')
            ->selectRaw('plots.plot_type, COUNT(*) as sales_count, AVG(plot_sales.total_amount) as avg_price')
            ->groupBy('plots.plot_type')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Check if project has any pending approvals
     */
    public function hasPendingApprovals(): bool
    {
        return $this->stages()->where('status', 'pending')->exists();
    }

    /**
     * Get critical issues
     */
    public function getCriticalIssues(): array
    {
        $issues = [];

        if ($this->isDelayed()) {
            $issues[] = [
                'type' => 'delay',
                'description' => 'Project is behind schedule',
                'severity' => 'high'
            ];
        }

        if ($this->hasPendingApprovals()) {
            $issues[] = [
                'type' => 'approval',
                'description' => 'Pending approvals required',
                'severity' => 'medium'
            ];
        }

        if ($this->completion_percentage < 50 && $this->duration_in_days > 180) {
            $issues[] = [
                'type' => 'progress',
                'description' => 'Slow progress detected',
                'severity' => 'medium'
            ];
        }

        return $issues;
    }

    /**
     * Get project recommendations
     */
    public function getRecommendations(): array
    {
        $recommendations = [];

        if ($this->available_plots > 0 && $this->completion_percentage > 80) {
            $recommendations[] = 'Consider launching marketing campaign for remaining plots';
        }

        if ($this->isDelayed()) {
            $recommendations[] = 'Review project timeline and resource allocation';
        }

        if ($this->roi_percentage < 10) {
            $recommendations[] = 'Review pricing strategy to improve profitability';
        }

        if ($this->sold_plots / max($this->total_plots, 1) > 0.8) {
            $recommendations[] = 'Plan for project handover and completion';
        }

        return $recommendations;
    }

    /**
     * Get project summary for dashboard
     */
    public function getDashboardSummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'status' => $this->status,
            'total_plots' => $this->total_plots,
            'sold_plots' => $this->sold_plots,
            'available_plots' => $this->available_plots,
            'completion_percentage' => round($this->completion_percentage, 2),
            'total_revenue' => $this->total_revenue,
            'estimated_valuation' => $this->estimated_valuation,
            'roi_percentage' => round($this->roi_percentage, 2),
            'health_status' => $this->health_status,
            'is_delayed' => $this->isDelayed(),
            'duration_in_days' => $this->duration_in_days,
            'can_accept_bookings' => $this->canAcceptBookings(),
            'next_milestone' => $this->next_milestone,
            'critical_issues' => $this->critical_issues,
            'recommendations' => $this->recommendations,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
