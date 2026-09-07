<?php

namespace App\Services\Commission;

use App\Models\Booking;
use App\Models\User;

/**
 * CommissionResult
 * Value object representing the result of a commission calculation.
 */
class CommissionResult
{
    public function __construct(
        public readonly string $track,
        public readonly string $strategyClass,
        public readonly float $amount,
        public readonly array $breakdown = [],
        public readonly array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'track' => $this->track,
            'strategy_class' => $this->strategyClass,
            'amount' => $this->amount,
            'breakdown' => $this->breakdown,
            'metadata' => $this->metadata,
        ];
    }
}