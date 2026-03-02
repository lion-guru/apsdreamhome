<?php

namespace App\Services;

/**
 * RankService
 * Encapsulates rank thresholds, rewards, and progress calculations.
 */

class RankService
{
    private array $ranks = [
        [
            'label' => 'Site Manager',
            'min' => 50000000, // 5 Cr and above
            'reward' => 'Car',
            'color' => '#cc0000'
        ],
        [
            'label' => 'President',
            'min' => 30000000,
            'reward' => 'Bullet Bike',
            'color' => '#ff6600'
        ],
        [
            'label' => 'Vice President',
            'min' => 15000000,
            'reward' => 'Pulsar Bike',
            'color' => '#ff9900'
        ],
        [
            'label' => 'Sr. BDM',
            'min' => 7000000,
            'reward' => 'Domestic / Foreign Tour',
            'color' => '#00b894'
        ],
        [
            'label' => 'BDM',
            'min' => 3500000,
            'reward' => 'Laptop',
            'color' => '#0984e3'
        ],
        [
            'label' => 'Sr. Associate',
            'min' => 1000000,
            'reward' => 'Tablet',
            'color' => '#6c5ce7'
        ],
        [
            'label' => 'Associate',
            'min' => 0,
            'reward' => 'Mobile',
            'color' => '#a29bfe'
        ],
    ];

    /**
     * Determine rank information for a given business amount.
     */
    public function getRankInfo(float $businessAmount): array
    {
        foreach ($this->ranks as $index => $rank) {
            if ($businessAmount >= $rank['min']) {
                $next = $this->ranks[$index - 1] ?? null; // ranks are descending by min

                return [
                    'current_label' => $rank['label'],
                    'reward' => $rank['reward'],
                    'color' => $rank['color'],
                    'business' => $businessAmount,
                    'next' => $next ? [
                        'label' => $next['label'],
                        'required' => $next['min'],
                        'reward' => $next['reward'],
                    ] : null,
                    'progress_percent' => $this->calculateProgress($businessAmount, $rank, $next)
                ];
            }
        }

        // Fallback (should not happen)
        $fallback = end($this->ranks);
        return [
            'current_label' => $fallback['label'],
            'reward' => $fallback['reward'],
            'color' => $fallback['color'],
            'business' => $businessAmount,
            'next' => null,
            'progress_percent' => 100
        ];
    }

    public function getRanks(): array
    {
        return $this->ranks;
    }

    private function calculateProgress(float $amount, array $currentRank, ?array $nextRank): float
    {
        if (!$nextRank) {
            return 100.0;
        }

        $range = $nextRank['min'] - $currentRank['min'];
        if ($range <= 0) {
            return 0.0;
        }

        $progress = ($amount - $currentRank['min']) / $range * 100;
        return max(0, min(100, $progress));
    }
}
