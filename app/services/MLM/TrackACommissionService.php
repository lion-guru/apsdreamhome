<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Track A Commission Service — Slab Differential (15% cap)
 * Calculates commission based on rank slab differentials upline
 */
class TrackACommissionService
{
    use ServiceTenantTrait;

    /** Leadership same-level override rates (Breakaway Safeguard). */
    private const SAME_LEVEL_OVERRIDES = [
        1 => 2.0,   // Immediate upline — 2.0%
        2 => 1.0,   // Second identical rank upline — 1.0%
    ];

    private \PDO $pdo;
    private \App\Services\MLM\RankService $rankService;
    private \App\Services\MLM\CommissionLedgerService $ledgerService;

    public function __construct(
        ?\PDO $pdo = null, 
        ?\App\Services\MLM\RankService $rankService = null,
        ?\App\Services\MLM\CommissionLedgerService $ledgerService = null
    ) {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
        $this->rankService = $rankService ?? new \App\Services\MLM\RankService();
        $this->ledgerService = $ledgerService ?? new \App\Services\MLM\CommissionLedgerService($pdo, $rankService);
    }

    /**
     * Compute Track A (Slab Differential) commission
     *
     * @param int     $agentId       The executing agent's user_id
     * @param float   $amountReceived Payment amount received
     * @param float   $budgetCap      Budget cap for this track (15% of payment)
     * @param int     $bookingId      Booking ID
     * @param int     $receiptId      Receipt ID
     * @param array   $uplineChain    Pre-fetched upline chain (optional, will fetch if not provided)
     * @return array Track A result with distributed amount, remaining, ledger_ids
     */
    public function compute(
        int $agentId,
        float $amountReceived,
        float $budgetCap,
        int $bookingId,
        int $receiptId,
        ?array $uplineChain = null
    ): array {
        $ledgerIds = [];
        $distributed = 0.0;

        // Load rank slabs from DB (falls back to hardcoded if empty)
        $rankSlabs = $this->rankService->loadRankSlabsFromDb();

        // Resolve the executing agent's own rank and rate
        $agentRank = $this->rankService->resolveRank($agentId);
        $agentRate = $rankSlabs[$agentRank]['rate'] ?? 0;

        // Build upline chain (up to 7 levels deep) if not provided
        $upline = $uplineChain ?? $this->getUplineChain($agentId, 7);

        // The agent gets the first slice: their own rank rate × amountReceived
        $agentSlice = $amountReceived * ($agentRate / 100);
        if ($agentSlice > 0) {
            $alloc = min($agentSlice, max(0.0, $budgetCap - $distributed));
            if ($alloc > 0.01) {
                $ledgerId = $this->ledgerService->writeLedger(
                    $agentId, $agentId, $amountReceived, $agentRate,
                    round($alloc, 2), 'direct_sale', 1, $bookingId, $receiptId,
                    'Track A — Direct agent slab commission',
                    false
                );
                $ledgerIds[] = $ledgerId;
                $distributed += $alloc;
            }
        }

        // Traverse upline, computing differential gap at each generation
        $prevRate = $agentRate;
        foreach ($upline as $gen) {
            $uplineRank = $gen['rank'];
            $uplineRate = $rankSlabs[$uplineRank]['rate'] ?? 0;
            $userId = $gen['user_id'];

            // Cap reached — stop
            if ($distributed >= $budgetCap) {
                break;
            }

            $remaining = max(0.0, $budgetCap - $distributed);

            // ── BREAKAWAY SAFEGUARD ──
            // If senior's rate equals immediate downline's rate (same rank),
            // apply Leadership Same-Level Override instead of differential.
            if ($uplineRate === $prevRate) {
                // Fetch booking details for date
                $booking = $this->fetchBooking($bookingId);
                $bookingMonth = date('Y-m');
                if ($booking && !empty($booking['created_at'])) {
                    $bookingMonth = date('Y-m', strtotime($booking['created_at']));
                }

                // Verify upline meets ₹50,000 monthly side-volume requirement
                if ($this->verifyUplineSideVolume($userId, $bookingMonth)) {
                    $overridePct = self::SAME_LEVEL_OVERRIDES[$gen['level']] ?? 0;
                    $overrideAmt = $amountReceived * ($overridePct / 100);

                    if ($overrideAmt > 0) {
                        $alloc = min($overrideAmt, $remaining);
                        if ($alloc > 0.01) {
                            $ledgerId = $this->ledgerService->writeLedger(
                                $userId, $agentId, $amountReceived, $overridePct,
                                round($alloc, 2), 'level_bonus', $gen['level'], $bookingId, $receiptId,
                                "Track A — Same-level override ({$this->rankService->getRankName($uplineRank)}, Gen {$gen['level']})",
                                false
                            );
                            $ledgerIds[] = $ledgerId;
                            $distributed += $alloc;
                        }
                    }
                }
                // Do NOT update $prevRate — keep it same so Gen 2+ also triggers
                // the same-level path if they share the same rank.
                continue;
            }

            // Standard differential: senior rate minus the rate immediately below them
            $differential = $uplineRate - $prevRate;
            if ($differential > 0) {
                $diffAmt = $amountReceived * ($differential / 100);
                $alloc = min($diffAmt, $remaining);
                if ($alloc > 0.01) {
                    $ledgerId = $this->ledgerService->writeLedger(
                        $userId, $agentId, $amountReceived, $differential,
                        round($alloc, 2), 'level_bonus', $gen['level'], $bookingId, $receiptId,
                        "Track A — Differential ({$this->rankService->getRankName($uplineRank)} {$uplineRate}% − {$prevRate}%)",
                        false
                    );
                    $ledgerIds[] = $ledgerId;
                    $distributed += $alloc;
                }
            }

            $prevRate = $uplineRate;
        }

        return [
            'track'       => 'A',
            'label'       => 'Slab Differential',
            'budget'      => $budgetCap,
            'distributed' => round($distributed, 2),
            'remaining'   => round($budgetCap - $distributed, 2),
            'ledger_ids'  => $ledgerIds,
            'entries'     => count($ledgerIds),
        ];
    }

    /**
     * Build upline chain (up to $maxLevels deep) from mlm_network_tree
     */
    private function getUplineChain(int $agentId, int $maxLevels = 7): array
    {
        $upline = [];
        $current = $agentId;
        $level = 0;

        while ($level < $maxLevels) {
            $stmt = $this->pdo->prepare("
                SELECT parent_id, level FROM mlm_network_tree
                WHERE associate_id = ? LIMIT 1
            ");
            $stmt->execute([$current]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row || !$row['parent_id']) {
                break;
            }

            $level++;
            $parentId = (int) $row['parent_id'];
            $rank = $this->rankService->resolveRank($parentId);

            $upline[] = [
                'user_id'   => $parentId,
                'rank'      => $rank,
                'level'     => $level,
            ];

            $current = $parentId;
        }

        return $upline;
    }

    /**
     * Verify upline meets ₹50,000 monthly side-volume requirement
     */
    private function verifyUplineSideVolume(int $uplineUserId, string $month): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(pb.booking_amount), 0) AS side_volume
                FROM plot_bookings pb
                JOIN plots p ON p.id = pb.plot_id
                WHERE pb.associate_id = ? 
                AND DATE_FORMAT(pb.created_at, '%Y-%m') = ?
                AND pb.status IN ('confirmed', 'completed')
            ");
            $stmt->execute([$uplineUserId, $month]);
            $sideVolume = (float) $stmt->fetchColumn();
            return $sideVolume >= 50000;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Fetch a booking row from plot_bookings
     */
    private function fetchBooking(int $bookingId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM plot_bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
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