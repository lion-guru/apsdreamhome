<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class RoyaltyService
{
    use ServiceTenantTrait;
    private $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();
    }

    public function calculateRoyaltyPool(string $period): array
    {
        // 2% of all plot sales goes to royalty pool
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_price), 0) as total_sales
            FROM plot_bookings
            WHERE status IN ('booked', 'registered')
            AND DATE_FORMAT(booking_date, '%Y-%m') = ?" . $this->tenantSql() . "
        ");
        $stmt->execute([$period]);
        $totalSales = (float)$stmt->fetchColumn();

        $royaltyPool = $totalSales * 0.02;

        return [
            'period' => $period,
            'total_sales' => $totalSales,
            'royalty_pool' => $royaltyPool,
        ];
    }

    public function distributeRoyalty(string $period): array
    {
        $pool = $this->calculateRoyaltyPool($period);
        $poolAmount = $pool['royalty_pool'];

        if ($poolAmount <= 0) {
            return ['success' => true, 'distributed' => 0, 'message' => 'No royalty pool for period'];
        }

        // Get site managers with ≥₹50L GBV
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.name, u.gbv
            FROM users u
            WHERE u.rank = 'site_manager'
            AND u.gbv >= 5000000
            AND u.status = 'active'" . $this->tenantSql() . "
        ");
        $stmt->execute();
        $siteManagers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($siteManagers)) {
            return ['success' => true, 'distributed' => 0, 'message' => 'No eligible site managers'];
        }

        // Distribute proportionally by GBV
        $totalGBV = array_sum(array_column($siteManagers, 'gbv'));
        $distributed = 0;

        foreach ($siteManagers as $manager) {
            $share = ($manager['gbv'] / $totalGBV) * $poolAmount;

            $insertData = $this->tenantInsertData();
            $extraCols = $insertData ? ', ' . implode(', ', array_keys($insertData)) : '';
            $extraVals = $insertData ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '';
            $stmt = $this->pdo->prepare("
                INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, notes, status, created_at{$extraCols})
                VALUES (?, ?, 'royalty_pool', ?, ?, 'pending', NOW(){$extraVals})
            ");
            $stmt->execute(array_merge([
                $manager['id'],
                $manager['id'],
                $share,
                "Royalty pool share for {$period}"
            ], array_values($insertData)));
            $distributed += $share;
        }

        return [
            'success' => true,
            'period' => $period,
            'pool_amount' => $poolAmount,
            'distributed' => $distributed,
            'recipients' => count($siteManagers),
        ];
    }
}