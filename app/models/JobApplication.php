<?php

namespace App\Models;

use App\Core\UnifiedModel;

class JobApplication extends UnifiedModel
{
    public static $table = 'job_applications';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'job_id',
        'full_name',
        'email',
        'phone',
        'cover_letter',
        'resume_path',
        'status',
        'status_reason'
    ];

    /**
     * Get the job posting associated with this application
     */
    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }

    /**
     * Get interviews for this application
     */
    public function interviews()
    {
        return $this->hasMany(ApplicationInterview::class, 'application_id');
    }

    /**
     * Get notes for this application
     */
    public function notes()
    {
        return $this->hasMany(ApplicationNote::class, 'application_id');
    }

    /**
     * Get attachments for this application
     */
    public function attachments()
    {
        return $this->hasMany(ApplicationAttachment::class, 'application_id');
    }

    /**
     * Check if application can be updated
     */
    public function canUpdateStatus(string $newStatus): bool
    {
        $currentStatus = $this->status;
        
        // Define allowed status transitions
        $allowedTransitions = [
            'received' => ['under_review', 'rejected', 'withdrawn'],
            'under_review' => ['shortlisted', 'rejected', 'withdrawn'],
            'shortlisted' => ['interview_scheduled', 'rejected', 'withdrawn'],
            'interview_scheduled' => ['interview_completed', 'rejected', 'withdrawn'],
            'interview_completed' => ['offered', 'rejected', 'withdrawn'],
            'offered' => ['rejected', 'withdrawn'],
            'rejected' => ['withdrawn'],
            'withdrawn' => []
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    /**
     * Get application timeline
     */
    public function getTimeline(): array
    {
        $timeline = [
            [
                'event' => 'Application Submitted',
                'date' => $this->created_at,
                'status' => $this->status
            ]
        ];

        // Add interview events
        foreach ($this->interviews() as $interview) {
            $timeline[] = [
                'event' => 'Interview ' . ucfirst($interview->type) . ' - ' . ucfirst($interview->status),
                'date' => $interview->created_at,
                'details' => $interview->notes
            ];
        }

        // Add notes
        foreach ($this->notes() as $note) {
            $timeline[] = [
                'event' => 'Note Added',
                'date' => $note->created_at,
                'details' => $note->note,
                'author' => $note->created_by
            ];
        }

        // Sort by date
        usort($timeline, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $timeline;
    }

    /**
     * Get application score for ranking
     */
    public function getScore(): int
    {
        $score = 0;

        // Base score for having complete application
        if ($this->full_name && $this->email && $this->phone) {
            $score += 20;
        }

        // Cover letter bonus
        if (!empty($this->cover_letter)) {
            $score += 15;
        }

        // Resume bonus
        if (!empty($this->resume_path)) {
            $score += 15;
        }

        // Status-based scoring
        switch ($this->status) {
            case 'shortlisted':
                $score += 25;
                break;
            case 'interview_scheduled':
            case 'interview_completed':
                $score += 20;
                break;
            case 'offered':
                $score += 30;
                break;
            case 'rejected':
                $score -= 10;
                break;
        }

        return max(0, min(100, $score));
    }

    /**
     * Get application age in days
     */
    public function getAgeInDays(): int
    {
        $created = new \DateTime($this->created_at);
        $now = new \DateTime();
        return $created->diff($now)->days;
    }

    /**
     * Check if application is stale (older than 90 days)
     */
    public function isStale(): bool
    {
        return $this->getAgeInDays() > 90;
    }

    /**
     * Get application priority level
     */
    public function getPriorityLevel(): string
    {
        $age = $this->getAgeInDays();
        
        if ($age <= 7 && in_array($this->status, ['received', 'under_review'])) {
            return 'high';
        } elseif ($age <= 30 && in_array($this->status, ['received', 'under_review', 'shortlisted'])) {
            return 'medium';
        } elseif ($this->status === 'interview_scheduled') {
            return 'high';
        } elseif ($this->isStale()) {
            return 'low';
        }
        
        return 'normal';
    }
}
