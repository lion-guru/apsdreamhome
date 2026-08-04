<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;

class TrackAService extends ServiceTenantTrait
{
    private $pdo;
    private $slabDifferentials;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();
        $this->loadSlabDifferentials();
    }

    private function loadSlabDifferentials(): void
    {
        $this->slabDifferentials = [];
        try {
            $stmt = $this->pdo->query("
                SELECT rank_name, commission_rate
                FROM mlm_rank_slabs
                WHERE is_active = 1
                ORDER BY min_gbv ASC
            ");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $this->slabDifferentials[$row['rank_name']] = (float)$row['commission_rate'];
            }
        } catch (\Exception $e) {
            // Fallback to defaults
            $this->slabDifferentials = [
                'associate' => 5, 'sr_associate' => 7, 'bdm' => 10,
                'sr_bdm' => 12, 'vice_president' => 15, 'president' => 18, 'site_manager' => 20,
            ];
        }
    }

    public function calculateTrackA(int $bookingId): array
    {
        // Get booking details
        $sql = "
            SELECT pb.*, u.id as user_id, u.name, u.rank, u.referred_by
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

        // Calculate commission based on slab differential
        $userRank = $booking['rank'] ?? 'associate';
        $userRate = $this->slabDifferentials[$userRank] ?? 5;

        // Get upline
        $uplineId = $booking['referred_by'];
        $totalCommission = 0;
        $breakdown = [];

        while ($uplineId) {
            $sql = "SELECT id, rank, referred_by FROM users WHERE id = ?";
            $params = [$uplineId];
            $this->tenantWhere($sql, $params);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $upline = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$upline) break;

            $uplineRank = $upline['rank'] ?? 'associate';
            $uplineRate = $this->slabDifferentials[$uplineRank] ?? 5;

            // Differential = upline_rate - user_rate (if positive)
            $differential = max(0, $uplineRate - $userRate);
            $commission = ($booking['total_price'] * $differential) / 100;

            if ($commission > 0) {
                $breakdown[] = [
                    'upline_id' => $uplineId,
                    'upline_rank' => $uplineRank,
                    'differential_rate' => $differential,
                    'commission' => $commission,
                ];
                $totalCommission += $commission;
            }

            // Move up the chain
            $uplineId = $upline['referred_by'];
            $userRate = $uplineRate;
        }

        // Cap at 20% of booking value
        $maxCommission = $booking['total_price'] * 0.20;
        if ($totalCommission > $maxCommission) {
            $scale = $maxCommission / $totalCommission;
            foreach ($breakdown as &$b) {
                $b['commission'] *= $scale;
            }
            $totalCommission = $maxCommission;
        }

        return [
            'success' => true,
            'track' => 'A',
            'booking_id' => $bookingId,
            'total_commission' => $totalCommission,
            'breakdown' => $breakdown,
        ];
    }
}