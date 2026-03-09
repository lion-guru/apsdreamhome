<?php

namespace App\Models;

use App\Core\UnifiedModel;

class MarketingLead extends UnifiedModel
{
    public static $table = 'marketing_leads';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'position',
        'source',
        'status',
        'status_reason',
        'score'
    ];

    /**
     * Get the full name of the lead
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the tags associated with this lead
     */
    public function tags()
    {
        return $this->hasMany(LeadTag::class, 'lead_id');
    }

    /**
     * Get the activities for this lead
     */
    public function activities()
    {
        return $this->hasMany(LeadActivity::class, 'lead_id');
    }

    /**
     * Get the campaigns this lead is part of
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_leads', 'lead_id', 'campaign_id');
    }

    /**
     * Check if lead can be updated to new status
     */
    public function canUpdateStatus(string $newStatus): bool
    {
        $currentStatus = $this->status;
        
        // Define allowed status transitions
        $allowedTransitions = [
            'new' => ['contacted', 'lost'],
            'contacted' => ['interested', 'lost'],
            'interested' => ['qualified', 'lost'],
            'qualified' => ['converted', 'lost'],
            'converted' => [], // Final status
            'lost' => ['new', 'contacted'] // Can be reactivated
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    /**
     * Get lead age in days
     */
    public function getAgeInDays(): int
    {
        $created = new \DateTime($this->created_at);
        $now = new \DateTime();
        return $created->diff($now)->days;
    }

    /**
     * Check if lead is stale (older than 90 days and not converted)
     */
    public function isStale(): bool
    {
        return $this->getAgeInDays() > 90 && $this->status !== 'converted';
    }

    /**
     * Get lead priority level based on score and status
     */
    public function getPriorityLevel(): string
    {
        if ($this->score >= 80 && in_array($this->status, ['interested', 'qualified'])) {
            return 'high';
        } elseif ($this->score >= 60 && $this->status === 'contacted') {
            return 'medium';
        } elseif ($this->score >= 40) {
            return 'normal';
        } elseif ($this->isStale()) {
            return 'low';
        }
        
        return 'normal';
    }

    /**
     * Get lead conversion probability (mock calculation)
     */
    public function getConversionProbability(): float
    {
        $probability = 0.0;

        // Base probability based on status
        $statusProbabilities = [
            'new' => 0.1,
            'contacted' => 0.2,
            'interested' => 0.4,
            'qualified' => 0.7,
            'converted' => 1.0,
            'lost' => 0.05
        ];

        $probability = $statusProbabilities[$this->status] ?? 0.1;

        // Adjust based on score
        $probability += ($this->score / 100) * 0.3;

        // Adjust based on age (newer leads have higher probability)
        $age = $this->getAgeInDays();
        if ($age <= 7) {
            $probability += 0.1;
        } elseif ($age <= 30) {
            $probability += 0.05;
        }

        return min(1.0, max(0.0, $probability));
    }

    /**
     * Get lead engagement level based on activities
     */
    public function getEngagementLevel(): string
    {
        $activities = $this->activities();
        $recentActivities = $activities->filter(function($activity) {
            return strtotime($activity->created_at) > strtotime('-30 days');
        });

        $activityCount = count($recentActivities);

        if ($activityCount >= 10) {
            return 'high';
        } elseif ($activityCount >= 5) {
            return 'medium';
        } elseif ($activityCount >= 2) {
            return 'low';
        }

        return 'none';
    }

    /**
     * Get lead value (mock calculation)
     */
    public function getEstimatedValue(): float
    {
        $baseValue = 10000; // Base value per lead

        // Adjust based on score
        $value = $baseValue * ($this->score / 100);

        // Adjust based on status
        $statusMultipliers = [
            'new' => 1.0,
            'contacted' => 1.2,
            'interested' => 1.5,
            'qualified' => 2.0,
            'converted' => 3.0,
            'lost' => 0.2
        ];

        $value *= $statusMultipliers[$this->status] ?? 1.0;

        // Adjust based on company (if B2B)
        if (!empty($this->company)) {
            $value *= 1.5;
        }

        return round($value, 2);
    }

    /**
     * Get next best action for this lead
     */
    public function getNextBestAction(): string
    {
        switch ($this->status) {
            case 'new':
                return 'Send welcome email and make initial contact';
            
            case 'contacted':
                $lastActivity = $this->activities()->first();
                if ($lastActivity && strtotime($lastActivity->created_at) > strtotime('-7 days')) {
                    return 'Wait for response or send follow-up';
                }
                return 'Send follow-up email or make call';
            
            case 'interested':
                if ($this->score >= 70) {
                    return 'Schedule demo or meeting';
                }
                return 'Send detailed information and case studies';
            
            case 'qualified':
                return 'Send proposal and pricing information';
            
            case 'converted':
                return 'Send onboarding materials and schedule follow-up';
            
            case 'lost':
                if ($this->getAgeInDays() > 60) {
                    return 'Re-engagement campaign';
                }
                return 'No action needed';
            
            default:
                return 'Review lead and determine appropriate action';
        }
    }

    /**
     * Check if lead is high priority
     */
    public function isHighPriority(): bool
    {
        return $this->getPriorityLevel() === 'high';
    }

    /**
     * Check if lead needs immediate attention
     */
    public function needsImmediateAttention(): bool
    {
        return $this->isHighPriority() || 
               ($this->status === 'qualified' && $this->score >= 80) ||
               ($this->status === 'interested' && $this->getAgeInDays() > 14);
    }

    /**
     * Get lead lifecycle stage
     */
    public function getLifecycleStage(): string
    {
        switch ($this->status) {
            case 'new':
                return 'Lead';
            case 'contacted':
            case 'interested':
                return 'Marketing Qualified Lead';
            case 'qualified':
                return 'Sales Qualified Lead';
            case 'converted':
                return 'Customer';
            case 'lost':
                return 'Lost Lead';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get lead source category
     */
    public function getSourceCategory(): string
    {
        $sourceCategories = [
            'website' => 'Digital',
            'social_media' => 'Digital',
            'email' => 'Digital',
            'referral' => 'Referral',
            'cold_call' => 'Outbound',
            'event' => 'Event',
            'partner' => 'Partner',
            'advertisement' => 'Paid',
            'organic' => 'Organic',
            'direct' => 'Direct',
            'manual' => 'Manual'
        ];

        return $sourceCategories[$this->source] ?? 'Other';
    }

    /**
     * Get lead communication preferences (mock)
     */
    public function getCommunicationPreferences(): array
    {
        return [
            'email' => true,
            'phone' => !empty($this->phone),
            'sms' => !empty($this->phone) && $this->status === 'interested',
            'newsletter' => in_array($this->status, ['new', 'contacted', 'interested']),
            'marketing' => $this->status !== 'lost'
        ];
    }

    /**
     * Add tag to lead
     */
    public function addTag(string $tag): void
    {
        if (!$this->tags()->where('tag', $tag)->exists()) {
            $this->tags()->create(['tag' => $tag]);
        }
    }

    /**
     * Remove tag from lead
     */
    public function removeTag(string $tag): void
    {
        $this->tags()->where('tag', $tag)->delete();
    }

    /**
     * Check if lead has tag
     */
    public function hasTag(string $tag): bool
    {
        return $this->tags()->where('tag', $tag)->exists();
    }

    /**
     * Get lead summary for dashboard
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->getFullNameAttribute(),
            'email' => $this->email,
            'company' => $this->company,
            'status' => $this->status,
            'score' => $this->score,
            'priority_level' => $this->getPriorityLevel(),
            'conversion_probability' => $this->getConversionProbability(),
            'engagement_level' => $this->getEngagementLevel(),
            'estimated_value' => $this->getEstimatedValue(),
            'age_in_days' => $this->getAgeInDays(),
            'next_best_action' => $this->getNextBestAction(),
            'lifecycle_stage' => $this->getLifecycleStage(),
            'tags' => $this->tags()->pluck('tag')->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
