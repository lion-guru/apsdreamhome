<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

class TrackCService extends ServiceTenantTrait
{
    private $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();
    }

    public function calculateTrackC(int $bookingId): array
    {
        $sql = "
            SELECT pb.*, u.id as user_id, u.rank, u.referred_by
            FROM plot_bookings pb
            JOIN users u ON pb.customer_id = u.id
            WHERE pb.id = ?
        ";
        $params = [$bookingId];
        $this->tenantWhere($sql, $params);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$booking) {
            return ['success' => false, 'message' => 'Booking not found'];
        }

        // Milestone escrow: 2% released on registry completion
        $escrowAmount = ($booking['total_plot_value'] * 2) / 100;
        $uplineId = $booking['referred_by'];
        $breakdown = [];

        while ($uplineId) {
            $sql = "SELECT id, rank, referred_by FROM users WHERE id = ?";
            $params = [$uplineId];
            $this->tenantWhere($sql, $params);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $upline = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$upline) break;

            $breakdown[] = [
                'upline_id' => $uplineId,
                'upline_rank' => $upline['rank'] ?? 'associate',
                'escrow_type' => 'milestone_escrow',
                'amount' => $escrowAmount,
                'released' => false,
                'condition' => 'registry_completion',
            ];

            $uplineId = $upline['referred_by'];
        }

        return [
            'success' => true,
            'track' => 'C',
            'booking_id' => $bookingId,
            'escrow_amount' => $escrowAmount,
            'breakdown' => $breakdown,
        ];
    }

    public function releaseEscrow(int $bookingId): array
    {
        // Release escrow on registry completion
        $sql = "
            UPDATE mlm_commission_ledger
            SET status = 'released', released_at = NOW()
            WHERE booking_id = ? AND commission_type = 'milestone_escrow'
        ";
        $params = [$bookingId];
        $this->tenantWhere($sql, $params);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return ['success' => true, 'released_count' => $stmt->rowCount()];
    }
}