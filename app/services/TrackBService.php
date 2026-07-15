<?php

namespace App\Services;

use App\Core\Database\Database;

class TrackBService
{
    private $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();
    }

    public function calculateTrackB(int $bookingId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT pb.*, u.id as user_id, u.rank, u.referred_by
            FROM plot_bookings pb
            JOIN users u ON pb.user_id = u.id
            WHERE pb.id = ?
        ");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$booking) {
            return ['success' => false, 'message' => 'Booking not found'];
        }

        $uplineId = $booking['referred_by'];
        $breakdown = [];
        $totalCommission = 0;

        while ($uplineId) {
            $stmt = $this->pdo->prepare("SELECT id, rank, referred_by, gbv FROM users WHERE id = ?");
            $stmt->execute([$uplineId]);
            $upline = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$upline) break;

            // Performance rollup: 3% of payment, distributed by downline GBV
            $commission = ($booking['total_price'] * 3) / 100;
            $breakdown[] = [
                'upline_id' => $uplineId,
                'upline_rank' => $upline['rank'] ?? 'associate',
                'rollup_type' => 'performance',
                'commission' => $commission,
            ];
            $totalCommission += $commission;

            $uplineId = $upline['referred_by'];
        }

        // Cap at 20% total (shared with Track A)
        $maxCommission = $booking['total_price'] * 0.20;

        return [
            'success' => true,
            'track' => 'B',
            'booking_id' => $bookingId,
            'total_commission' => min($totalCommission, $maxCommission),
            'breakdown' => $breakdown,
        ];
    }
}