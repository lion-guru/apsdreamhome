<?php

namespace App\Services\Commission;

use App\Models\Booking;
use App\Models\User;

/**
 * CommissionStrategy Interface
 * Defines the contract for all commission calculation strategies.
 */
interface CommissionStrategy
{
    /**
     * Calculate commission for a booking.
     *
     * @param Booking $booking
     * @param User $user
     * @return CommissionResult
     */
    public function calculate(Booking $booking, User $user): CommissionResult;

    /**
     * Check if this strategy supports the given track.
     *
     * @param string $track
     * @return bool
     */
    public function supports(string $track): bool;
}