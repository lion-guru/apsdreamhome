<?php

namespace App\Services\Commission\Strategies;

use App\Models\Booking;
use App\Models\User;
use App\Services\Commission\CommissionStrategy;
use App\Services\Commission\CommissionResult;

/**
 * PayoutStrategy
 * Handles payout batch processing for approved commissions
 * Replaces: PayoutBatchService
 */
class PayoutStrategy implements CommissionStrategy
{
    public const SUPPORTS_TRACKS = ['payout', 'payout_batch', 'commission_payout'];

    /**
     * @inheritDoc
     */
    public function calculate(object $booking, object $user): \App\Services\Commission\CommissionResult
    {
        // Payouts are not calculated per booking - they are batch processed
        // This method returns the payout eligibility for a user's pending commissions
        
        $ledgerService = new \App\Services\MLM\CommissionLedgerService();
        
        $pendingCommissions = $ledgerService->getPendingCommissionsForUser($booking->user_id ?? $booking->user_id ?? 0);
        
        $totalPending = array_sum(array_column($pendingCommissions, 'amount'));
        $tdsRate = 0.10; // 10% TDS
        $tdsAmount = $totalPending * $tdsRate;
        $netPayout = $totalPending - $tdsAmount;

        $breakdown = [
            'pending_commissions' => count($pendingCommissions),
            'total_pending' => round($totalPending, 2),
            'tds_rate' => $tdsRate * 100 . '%',
            'tds_amount' => round($tdsAmount, 2),
            'net_payout' => round($totalPending - $tdsAmount, 2),
            'note' => 'Payouts processed in batches via cron. TDS deducted at 10%.',
        ];

        return new \App\Services\Commission\CommissionResult(
            track: 'payout',
            strategyClass: static::class,
            amount: round($totalPending, 2),
            breakdown: $breakdown,
            metadata: [
                'user_id' => $booking->user_id ?? 0,
                'pending_count' => count($pendingCommissions),
            ]
        );
    }

    /**
     * Create payout batch for multiple users.
     */
    public function createPayoutBatch(array $userIds): array
    {
        $ledgerService = app(\App\Services\Commission\CommissionLedgerService::class);
        $batch = $ledgerService->createPayoutBatch($userIds);
        
        return [
            'batch_id' => $batch->id,
            'total_users' => count($userIds),
            'total_amount' => $batch->total_amount,
            'tds_amount' => $batch->tds_amount,
            'net_amount' => $batch->net_amount,
            'status' => $batch->status,
        ];
    }

    /**
     * @inheritDoc
     */
    public function supports(string $track): bool
    {
        return in_array($track, self::SUPPORTS_TRACKS);
    }
}