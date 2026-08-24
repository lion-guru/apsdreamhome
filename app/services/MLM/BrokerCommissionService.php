<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Broker Commission Service
 * Handles freelance broker commission calculations and cascading commissions
 */
class BrokerCommissionService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * Get the active custom rate a parent broker has set for a specific child.
     *
     * @return array|null  ['commission_type'=>'percentage'|'per_sqft', 'commission_value'=>float] or null
     */
    public function getBrokerDownlineRate(int $parentUserId, int $childUserId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT commission_type, commission_value
                FROM associate_downline_rates
                WHERE parent_user_id = ?
                  AND child_user_id  = ?
                  AND effective_from <= CURDATE()
                  AND (effective_to IS NULL OR effective_to >= CURDATE())
                ORDER BY effective_from DESC
                LIMIT 1
            ");
            $stmt->execute([$parentUserId, $childUserId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[BrokerCommissionService] getBrokerDownlineRate: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the broker's own commission rate (from associates table).
     *
     * @return array|null  ['commission_type'=>..., 'commission_value'=>float, 'agent_type'=>...]
     */
    public function getBrokerOwnRate(int $userId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT commission_type, commission_value, agent_type
                FROM associates
                WHERE user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[BrokerCommissionService] getBrokerOwnRate: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate the commission amount for a single plot sale based on
     * custom broker rates (percentage or per_sqft) with area support.
     *
     * @param float  $plotValue    Total sale value of the plot in Rs.
     * @param float  $areaSqft     Area of the plot in SqFt.
     * @param string $commType     'percentage' or 'per_sqft'
     * @param float  $commValue    The rate value (% or Rs/SqFt)
     * @param float|null $amountReceived  Amount received so far (for proportional per_sqft)
     * @return float               Gross commission amount in Rs.
     */
    public function calcBrokerCommission(
        float $plotValue,
        float $areaSqft,
        string $commType,
        float $commValue,
        float $amountReceived = null
    ): float {
        if ($commType === 'per_sqft') {
            $totalComm = $commValue * $areaSqft;
            if ($amountReceived !== null && $plotValue > 0) {
                return round($totalComm * ($amountReceived / $plotValue), 2);
            }
            return round($totalComm, 2);
        }
        // percentage
        if ($amountReceived !== null) {
            return round(($commValue / 100.0) * $amountReceived, 2);
        }
        return round(($commValue / 100.0) * $plotValue, 2);
    }

    /**
     * Process the full cascading commission chain for a freelance broker network.
     *
     * Walk up the mlm_network_tree from the selling agent.
     * At each level, look for a custom downline rate set by the parent for that child.
     * Broker margin at each level = parent_rate − child_rate (differential).
     * If no custom rate exists at any level, falls back to RANK_SLABS differential.
     *
     * Does NOT write to DB — returns a breakdown array for the caller to persist.
     *
     * @param int   $sellingUserId  The associate who made the plot sale
     * @param float $plotValue      Total plot sale value in Rs.
     * @param float $areaSqft       Plot area in SqFt.
     * @param int   $maxLevels      How many upline levels to walk (default 10)
     * @return array [
     *   'success'     => bool,
     *   'chain'       => [['user_id'=>int, 'gross'=>float, 'net'=>float, 'type'=>string], ...],
     *   'total_paid'  => float
     * ]
     */
    public function processFreelanceBrokerCommission(
        int $sellingUserId,
        float $plotValue,
        float $areaSqft,
        int $maxLevels = 10
    ): array {
        $chain = [];
        $totalPaid = 0.0;
        $currentUserId = $sellingUserId;

        // Get seller's own rate
        $childRate = $this->getBrokerOwnRate($currentUserId);

        for ($level = 1; $level <= $maxLevels; $level++) {
            // Get parent (upline) in MLM tree
            $stmt = $this->pdo->prepare("
                SELECT parent_id FROM mlm_network_tree WHERE associate_id = ? LIMIT 1
            ");
            $stmt->execute([$currentUserId]);
            $parent = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$parent || !$parent['parent_id']) {
                break; // Reached top of tree
            }

            $parentUserId = (int) $parent['parent_id'];

            // Get parent's custom rate for this child
            $parentRateForChild = $this->getBrokerDownlineRate($parentUserId, $currentUserId);

            if ($parentRateForChild && $childRate) {
                // Compute differential: parent_rate - child_rate
                $parentComm = $this->calcBrokerCommission(
                    $plotValue, $areaSqft, $parentRateForChild['commission_type'], $parentRateForChild['commission_value']
                );
                $childComm = $this->calcBrokerCommission(
                    $plotValue, $areaSqft, $childRate['commission_type'], $childRate['commission_value']
                );
                $margin = round($parentComm - $childComm, 2);

                if ($margin > 0) {
                    $chain[] = [
                        'user_id' => $parentUserId,
                        'level'   => count($chain) + 1,
                        'gross'   => $parentComm,
                        'child_gross' => $childComm,
                        'net'     => $margin,
                        'type'    => 'broker_margin',
                        'parent_rate' => $parentRateForChild,
                        'child_rate'  => $childRate,
                    ];
                    $totalPaid += $margin;
                }
            }

            // Move up
            $childRate = $this->getBrokerOwnRate($parentUserId);
            $currentUserId = $parentUserId;
        }

        return [
            'success'   => true,
            'chain'     => $chain,
            'total_paid' => round($totalPaid, 2),
        ];
    }

    protected function getTenantId(): int
    {
        try {
            return \App\Core\Middleware\TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }
}