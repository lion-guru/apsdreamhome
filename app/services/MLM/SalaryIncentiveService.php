<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Salary Incentive Service
 * Handles salary grants, career rewards, monthly maintenance checks
 */
class SalaryIncentiveService
{
    use ServiceTenantTrait;

    /** Default booking / token amount per plot. */
    private const DEFAULT_BOOKING_AMOUNT = 51000;

    /** Target Achievement Incentive Tiers */
    private const SALARY_TIERS = [
        ['volume_threshold' =>  1500000, 'window_days' =>  60, 'monthly_grant' =>  5000, 'months' =>  6],
        ['volume_threshold' =>  3000000, 'window_days' => 100, 'monthly_grant' =>  5000, 'months' => 12],
        ['volume_threshold' =>  5000000, 'window_days' => 150, 'monthly_grant' =>  8000, 'months' => 12],
        ['volume_threshold' =>  7500000, 'window_days' => 200, 'monthly_grant' => 12000, 'months' => 12],
        ['volume_threshold' => 10000000, 'window_days' => 300, 'monthly_grant' => 20000, 'months' => 12],
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
        $this->ledgerService = $ledgerService ?? new \App\Services\MLM\CommissionLedgerService();
    }

    /**
     * Check if an agent is eligible for salary incentive grant
     */
    public function checkSalaryIncentiveEligibility(int $agentId): array
    {
        try {
            // Get agent's total sales volume
            $gbv = $this->getAgentGbv($agentId);
            $currentRank = $this->rankService->resolveRank($agentId);

            // Check which tiers the agent qualifies for
            $eligibleTiers = [];
            foreach (self::SALARY_TIERS as $tier) {
                if ($gbv >= $tier['volume_threshold']) {
                    $eligibleTiers[] = $tier;
                }
            }

            if (empty($eligibleTiers)) {
                return [
                    'success' => true,
                    'eligible' => false,
                    'gbv' => $gbv,
                    'current_rank' => $currentRank,
                    'message' => 'Agent does not meet any salary tier threshold'
                ];
            }

            // Get the highest qualified tier
            $highestTier = end($eligibleTiers);

            // Check if grant already active for this tier
            $stmt = $this->pdo->prepare("
                SELECT id FROM mlm_salary_grants
                WHERE user_id = ? AND volume_threshold = ? AND status = 'active' AND tenant_id = ?
                LIMIT 1
            ");
            $stmt->execute([$agentId, $highestTier['volume_threshold'], $this->getTenantId()]);
            if ($stmt->fetch()) {
                return [
                    'success' => true,
                    'eligible' => true,
                    'already_active' => true,
                    'gbv' => $gbv,
                    'current_rank' => $currentRank,
                    'tier' => $highestTier
                ];
            }

            return [
                'success' => true,
                'eligible' => true,
                'already_active' => false,
                'gbv' => $gbv,
                'current_rank' => $currentRank,
                'tier' => $highestTier,
                'all_eligible_tiers' => $eligibleTiers
            ];
        } catch (\Throwable $e) {
            error_log("[SalaryIncentiveService] checkSalaryIncentiveEligibility FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Activate salary grant for eligible agent
     */
    public function activateSalaryGrant(int $agentId): bool
    {
        try {
            $eligibility = $this->checkSalaryIncentiveEligibility($agentId);
            if (!$eligibility['success'] || !$eligibility['eligible'] || $eligibility['already_active']) {
                return false;
            }

            $tier = $eligibility['tier'];
            // Map tier to real schema columns (monthly_amount/months_total/activated_at).
            $tierIndex = 0;
            foreach (self::SALARY_TIERS as $i => $t) {
                if ($t['volume_threshold'] === $tier['volume_threshold']) { $tierIndex = $i; break; }
            }

            // NOTE: DDL (CREATE TABLE) implicitly commits in MySQL — the log-table
            // ensure must run OUTSIDE the transaction or it destroys it.
            try {
                $this->pdo->exec("CREATE TABLE IF NOT EXISTS mlm_salary_grant_logs (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
                    user_id BIGINT UNSIGNED NOT NULL,
                    grant_id INT UNSIGNED NOT NULL,
                    action VARCHAR(30) NOT NULL DEFAULT 'activated',
                    details TEXT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_grant (grant_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } catch (\Throwable $logEx) {
                error_log("[SalaryIncentiveService] grant log table ensure skipped: " . $logEx->getMessage());
            }

            $this->pdo->beginTransaction();
            try {
                // Create salary grant record
                $stmt = $this->pdo->prepare("
                    INSERT INTO mlm_salary_grants (tenant_id, user_id, tier_index, volume_threshold, monthly_amount, months_total, months_paid, status, activated_at, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 0, 'active', NOW(), NOW())
                ");
                $stmt->execute([
                    $this->getTenantId(),
                    $agentId,
                    $tierIndex,
                    $tier['volume_threshold'],
                    $tier['monthly_grant'],
                    $tier['months']
                ]);
                $grantId = (int)$this->pdo->lastInsertId();

                // Log the activation (best-effort; table ensured above, outside txn)
                try {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO mlm_salary_grant_logs (tenant_id, user_id, grant_id, action, details, created_at)
                        VALUES (?, ?, ?, 'activated', ?, NOW())
                    ");
                    $stmt->execute([
                        $this->getTenantId(),
                        $agentId,
                        $grantId,
                        "Salary grant activated for volume ≥ ₹" . number_format($tier['volume_threshold']) . " — ₹{$tier['monthly_grant']}/month for {$tier['months']} months"
                    ]);
                } catch (\Throwable $logEx) {
                    error_log("[SalaryIncentiveService] grant log skipped: " . $logEx->getMessage());
                }

                $this->pdo->commit();
                return true;
            } catch (\Throwable $e) {
                error_log("[SalaryIncentiveService] activateSalaryGrant FAILED: " . $e->getMessage());
                try {
                    if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                } catch (\Throwable $rbEx) {
                    error_log("[SalaryIncentiveService] rollback skipped: " . $rbEx->getMessage());
                }
                return false;
            }
        } catch (\Throwable $e) {
            error_log("[SalaryIncentiveService] activateSalaryGrant FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process monthly salary grants for all active grants
     * Implements pause/resume logic: if monthly maintenance fails, salary is held (not forfeited)
     * and released in the next month when the agent becomes active again.
     */
    public function processMonthlyGrants(string $monthYear): array
    {
        try {
            // Active window derived from activated_at + months_total (no end_date column).
            $stmt = $this->pdo->prepare("
                SELECT id, user_id, monthly_amount, months_total, months_paid, activated_at
                FROM mlm_salary_grants
                WHERE status IN ('active', 'paused')
                AND tenant_id = ?
                AND activated_at <= NOW()
                AND DATE_ADD(activated_at, INTERVAL months_total MONTH) > NOW()
                AND months_paid < months_total
            ");
            $stmt->execute([$this->getTenantId()]);
            $grants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $results = ['processed' => 0, 'amount' => 0, 'paused' => 0, 'resumed' => 0, 'errors' => []];

            foreach ($grants as $grant) {
                // Check if agent is still active
                $status = (new \App\Services\IdentityService())->getAssociateStatus((int)$grant['user_id']);

                if ($status !== 'active') {
                    $results['errors'][] = "User {$grant['user_id']} is not active (status: $status)";
                    continue;
                }

                // Check monthly maintenance eligibility
                $maintenance = $this->checkMonthlyMaintenance($grant['user_id'], $monthYear);

                if (!$maintenance['success'] || !$maintenance['eligible']) {
                    // Maintenance failed: PAUSE the grant (do not forfeit).
                    // Mark as paused so it can resume next month when active again.
                    $this->pdo->prepare("UPDATE mlm_salary_grants SET status = 'paused' WHERE id = ?")->execute([$grant['id']]);

                    // Log the pause
                    try {
                        $this->pdo->prepare("INSERT INTO mlm_salary_grant_logs (tenant_id, user_id, grant_id, action, payout_month, details, created_at) VALUES (?, ?, ?, 'paused', ?, ?, NOW())")->execute([
                            $this->getTenantId(), $grant['user_id'], $grant['id'], $monthYear, "Salary grant PAUSED for $monthYear — " . ($maintenance['message'] ?? 'maintenance failed')
                        ]);
                    } catch (\Throwable $logEx) {
                        error_log("[SalaryIncentiveService] pause log skipped: " . $logEx->getMessage());
                    }

                    $results['paused']++;
                    $results['errors'][] = "User {$grant['user_id']} salary paused for $monthYear: " . ($maintenance['message'] ?? 'maintenance failed');
                    continue;
                }

                // Maintenance passed: if grant was previously paused, resume it.
                if ($grant['status'] === 'paused') {
                    $this->pdo->prepare("UPDATE mlm_salary_grants SET status = 'active' WHERE id = ?")->execute([$grant['id']]);

                    try {
                        $this->pdo->prepare("INSERT INTO mlm_salary_grant_logs (tenant_id, user_id, grant_id, action, payout_month, details, created_at) VALUES (?, ?, ?, 'resumed', ?, ?, NOW())")->execute([
                            $this->getTenantId(), $grant['user_id'], $grant['id'], $monthYear, "Salary grant RESUMED for $monthYear — maintenance cleared"
                        ]);
                    } catch (\Throwable $logEx) {
                        error_log("[SalaryIncentiveService] resume log skipped: " . $logEx->getMessage());
                    }

                    $results['resumed']++;
                }

                // Idempotency: never pay the same grant twice for one month.
                $dup = $this->pdo->prepare("SELECT COUNT(*) FROM mlm_salary_grant_logs WHERE grant_id = ? AND action = 'paid' AND payout_month = ?");
                $dup->execute([$grant['id'], $monthYear]);
                if ((int)$dup->fetchColumn() > 0) {
                    $results['errors'][] = "Grant {$grant['id']} already paid for $monthYear — skipped";
                    continue;
                }

                // Credit the monthly grant
                $this->ledgerService->writeLedger(
                    $grant['user_id'],
                    $grant['user_id'],
                    0, // no sale amount for salary grant
                    0,
                    (float)$grant['monthly_amount'],
                    'salary_grant',
                    0,
                    0,
                    0,
                    "Monthly salary grant — $monthYear",
                    false
                );

                // Mark paid for this month (idempotency record)
                $this->pdo->prepare("INSERT INTO mlm_salary_grant_logs (tenant_id, user_id, grant_id, action, payout_month, details, created_at) VALUES (?, ?, ?, 'paid', ?, ?, NOW())")->execute([
                    $this->getTenantId(), $grant['user_id'], $grant['id'], $monthYear, "Monthly salary grant paid — $monthYear (₹{$grant['monthly_amount']})"
                ]);

                // Advance schedule; complete the grant when fully paid.
                $paid = (int)$grant['months_paid'] + 1;
                $done = $paid >= (int)$grant['months_total'] ? ", status='completed'" : "";
                $this->pdo->prepare("UPDATE mlm_salary_grants SET months_paid=? $done WHERE id=?")->execute([$paid, $grant['id']]);

                $results['processed']++;
                $results['amount'] += (float)$grant['monthly_amount'];
            }

            return ['success' => true, ...$results];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[SalaryIncentiveService] processMonthlyGrants FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check monthly maintenance eligibility (agent must have ≥₹50K side volume AND ≥2 site visits OR ≥₹50K booking)
     * Implements 40:60 leg balance rule: max 60% from any single downline leg.
     */
    public function checkMonthlyMaintenance(int $agentId, string $monthYear): array
    {
        try {
            [$year, $month] = explode('-', $monthYear);

            // ── 1. Count verified site visits this month ──
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) AS site_visit_count
                FROM site_visits sv
                WHERE sv.user_id = ?
                  AND YEAR(sv.visit_date) = ?
                  AND MONTH(sv.visit_date) = ?
                  AND sv.status = 'completed'
                  AND sv.tenant_id = ?
            ");
            $stmt->execute([$agentId, (int)$year, (int)$month, $this->getTenantId()]);
            $siteVisitCount = (int)$stmt->fetchColumn();

            // ── 2. Count plot bookings this month ──
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(pb.booking_amount), 0) AS booking_volume
                FROM plot_bookings pb
                WHERE pb.associate_id = ?
                  AND YEAR(pb.created_at) = ?
                  AND MONTH(pb.created_at) = ?
                  AND pb.status NOT IN ('cancelled', 'defaulted', 'transferred')
                  AND pb.tenant_id = ?
            ");
            $stmt->execute([$agentId, (int)$year, (int)$month, $this->getTenantId()]);
            $bookingVolume = (float)$stmt->fetchColumn();

            // ── Eligibility gate: 2 site visits OR ₹50k booking ──
            $hasVisits = $siteVisitCount >= 2;
            $hasBooking = $bookingVolume >= 50000;
            $activityMet = $hasVisits || $hasBooking;

            // ── 3. 40:60 Leg Balance Rule ──
            $legCheck = $this->getLegBalanceCheck($agentId, (int)$year, (int)$month);
            $legBalanceOk = $legCheck['balanced'];

            // ── Final decision ──
            $eligible = $activityMet && $legBalanceOk;

            $details = [
                'site_visit_count' => $siteVisitCount,
                'required_visits' => 2,
                'booking_volume' => $bookingVolume,
                'required_booking' => 50000,
                'activity_met' => $activityMet,
                'leg_balanced' => $legBalanceOk,
                'leg_check' => $legCheck,
            ];

            if (!$activityMet) {
                return [
                    'success' => true,
                    'eligible' => false,
                    'details' => $details,
                    'message' => "Insufficient activity: {$siteVisitCount} site visits (need ≥2) and ₹" . number_format($bookingVolume) . " booking (need ≥₹50,000)"
                ];
            }

            if (!$legBalanceOk) {
                return [
                    'success' => true,
                    'eligible' => false,
                    'details' => $details,
                    'message' => "Leg balance rule violated: {$legCheck['message']}"
                ];
            }

            return [
                'success' => true,
                'eligible' => true,
                'volume' => $bookingVolume,
                'details' => $details,
                'message' => "Monthly maintenance cleared: {$siteVisitCount} visit(s) + ₹" . number_format($bookingVolume) . " booking, legs balanced"
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 40:60 Leg Balance Check
     * Returns true only if no single downline leg contributes more than 60%
     * of the agent's total team volume for the given month.
     */
    private function getLegBalanceCheck(int $agentId, int $year, int $month): array
    {
        try {
            // Get all direct downline members (level 1 in network tree)
            $stmt = $this->pdo->prepare("
                SELECT nt.associate_id AS downline_id
                FROM mlm_network_tree nt
                WHERE nt.parent_id = ?
                  AND nt.level = 1
                  AND nt.tenant_id = ?
            ");
            $stmt->execute([$agentId, $this->getTenantId()]);
            $downlines = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($downlines)) {
                // No downline → agent's own personal sales count as 100%, which is balanced
                return [
                    'balanced' => true,
                    'total_legs' => 0,
                    'legs' => [],
                    'message' => 'No downline legs – personal sales count as 100%'
                ];
            }

            // Compute volume per leg for the given month
            $legVolumes = [];
            $totalVolume = 0;

            foreach ($downlines as $downlineId) {
                $stmt = $this->pdo->prepare("
                    SELECT COALESCE(SUM(pb.booking_amount), 0) AS leg_volume
                    FROM plot_bookings pb
                    WHERE pb.associate_id = ?
                      AND YEAR(pb.created_at) = ?
                      AND MONTH(pb.created_at) = ?
                      AND pb.status NOT IN ('cancelled', 'defaulted', 'transferred')
                      AND pb.tenant_id = ?
                ");
                $stmt->execute([$downlineId, $year, $month, $this->getTenantId()]);
                $legVol = (float)$stmt->fetchColumn();
                $legVolumes[$downlineId] = $legVol;
                $totalVolume += $legVol;
            }

            if ($totalVolume <= 0) {
                return [
                    'balanced' => true,
                    'total_legs' => count($downlines),
                    'legs' => [],
                    'message' => 'No team volume this month – leg balance not applicable'
                ];
            }

            // Check: no single leg > 60%
            $maxLegPct = 0;
            $maxLegId = null;
            $legDetails = [];

            foreach ($legVolumes as $legId => $vol) {
                $pct = $totalVolume > 0 ? ($vol / $totalVolume) * 100 : 0;
                if ($pct > $maxLegPct) {
                    $maxLegPct = $pct;
                    $maxLegId = $legId;
                }
                $legDetails[] = [
                    'leg_id' => $legId,
                    'volume' => $vol,
                    'percentage' => round($pct, 2)
                ];
            }

            $balanced = $maxLegPct <= 60.0;

            return [
                'balanced' => $balanced,
                'total_legs' => count($downlines),
                'max_leg_percentage' => round($maxLegPct, 2),
                'max_leg_id' => $maxLegId,
                'legs' => $legDetails,
                'total_volume' => $totalVolume,
                'message' => $balanced
                    ? "Leg balance OK: max leg {$maxLegPct}% (≤60%)"
                    : "Leg balance VIOLATED: leg #{$maxLegId} contributes {$maxLegPct}% (>60%)"
            ];
        } catch (\Throwable $e) {
            return [
                'balanced' => false,
                'error' => $e->getMessage(),
                'message' => 'Leg balance check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Peer Rank Mentorship Royalty (1%)
     * When a junior reaches the same rank as the senior, the senior earns
     * a 1% override on that junior's team volume for the month.
     * Prevents seniors from blocking juniors' growth.
     */
    public function processPeerRankRoyalties(string $monthYear): array
    {
        return $this->processPeerRankRoyalty($monthYear);
    }

    public function processPeerRankRoyalty(string $monthYear): array
    {
        $results = [
            'success' => false,
            'processed' => 0,
            'royalties_paid' => 0,
            'total_amount' => 0.0,
            'errors' => []
        ];

        try {
            [$year, $month] = explode('-', $monthYear);
            $startDate = "$year-$month-01";
            $endDate = date('Y-m-t', strtotime($startDate));

            // Get all active MLM ranks with their volume thresholds
            $rankSlabs = $this->rankService->loadRankSlabsFromDb();

            // Get all agents with their current rank and GBV for this month
            $stmt = $this->pdo->prepare("
                SELECT u.id AS user_id, u.name,
                       COALESCE(u.mlm_rank, ap.level, 'associate') AS current_rank,
                       COALESCE(mp.lifetime_sales, 0) AS gbv
                FROM users u
                LEFT JOIN associate_profiles ap ON ap.user_id = u.id
                LEFT JOIN mlm_profiles mp ON mp.user_id = u.id
                WHERE u.role IN ('associate', 'agent') AND u.status = 'active' AND u.tenant_id = ?
            ");
            $stmt->execute([$this->getTenantId()]);
            $agents = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($agents as $agent) {
                $seniorId = (int)$agent['user_id'];
                $seniorRank = $agent['current_rank'] ?? '';
                $seniorGbv = (float)($agent['gbv'] ?? 0);

                if (empty($seniorRank)) {
                    continue;
                }

                $seniorRankSlug = $this->normalizeRankSlug($seniorRank);

                // Find juniors in this senior's downline with SAME rank
                $stmt = $this->pdo->prepare("
                    SELECT u.id AS junior_id, u.name AS junior_name,
                           COALESCE(u.mlm_rank, ap.level, 'associate') AS junior_rank,
                           COALESCE(mp.lifetime_sales, 0) AS junior_gbv
                    FROM users u
                    LEFT JOIN associate_profiles ap ON ap.user_id = u.id
                    LEFT JOIN mlm_profiles mp ON mp.user_id = u.id
                    WHERE (u.referred_by = ? OR ap.sponsor_id = ? OR u.id IN (SELECT associate_id FROM mlm_network_tree WHERE parent_id = ? AND level = 1))
                      AND u.role IN ('associate', 'agent')
                      AND u.tenant_id = ?
                ");
                $stmt->execute([$seniorId, $seniorId, $seniorId, $this->getTenantId()]);
                $juniors = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($juniors as $junior) {
                    $juniorId = (int)$junior['junior_id'];
                    $juniorRank = $junior['junior_rank'] ?? '';
                    $juniorGbv = (float)($junior['junior_gbv'] ?? 0);

                    $juniorRankSlug = $this->normalizeRankSlug($juniorRank);

                    // Only if same rank
                    if ($juniorRankSlug === '' || $seniorRankSlug === '' || $juniorRankSlug !== $seniorRankSlug) {
                        continue;
                    }

                    // Check if royalty already paid for this junior this month
                    $stmt = $this->pdo->prepare("
                        SELECT COUNT(*) FROM mlm_peer_rank_royalties
                        WHERE senior_id = ? AND junior_id = ? AND month_year = ? AND tenant_id = ?
                    ");
                    $stmt->execute([$seniorId, $juniorId, $monthYear, $this->getTenantId()]);
                    if ((int)$stmt->fetchColumn() > 0) {
                        continue; // Already paid
                    }

                    // 1% royalty on junior's team volume
                    $royaltyAmount = round($juniorGbv * 0.01, 2);

                    if ($royaltyAmount <= 0) {
                        continue;
                    }

                    // Record royalty
                    try {
                        $this->pdo->beginTransaction();

                        $stmt = $this->pdo->prepare("
                            INSERT INTO mlm_peer_rank_royalties
                                (tenant_id, senior_id, junior_id, junior_rank, junior_gbv, royalty_amount, month_year, status, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', NOW())
                        ");
                        $stmt->execute([
                            $this->getTenantId(),
                            $seniorId,
                            $juniorId,
                            $juniorRankSlug,
                            $juniorGbv,
                            $royaltyAmount,
                            $monthYear
                        ]);

                        $this->pdo->commit();

                        // Credit to senior's wallet outside parent transaction (WalletService manages its own transaction)
                        $walletService = new \App\Services\WalletService();
                        $walletService->credit(
                            $seniorId,
                            $royaltyAmount,
                            'commission',
                            "Peer rank mentorship royalty (1%) from junior {$junior['junior_name']} ({$juniorRank}) - month $monthYear",
                            $juniorId,
                            'user'
                        );

                        $results['processed']++;
                        $results['royalties_paid']++;
                        $results['total_amount'] += $royaltyAmount;
                    } catch (\Throwable $e) {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        $results['errors'][] = "Royalty failed for senior {$seniorId} ← junior {$juniorId}: " . $e->getMessage();
                    }
                }
            }

            $results['success'] = true;
            return $results;
        } catch (\Throwable $e) {
            $results['errors'][] = $e->getMessage();
            return $results;
        }
    }

    public function normalizeRankSlug(string $rank): string
    {
        $r = strtolower(trim($rank));
        $r = str_replace(['.', ' ', '-'], '_', $r);
        $r = (string)preg_replace('/_+/', '_', $r);
        if (in_array($r, ['ass', 'associate', 'ass_'], true)) return 'associate';
        if (in_array($r, ['sr_ass', 'sr_associate', 'senior_associate'], true)) return 'sr_associate';
        if (in_array($r, ['bdm'], true)) return 'bdm';
        if (in_array($r, ['sr_bdm', 'senior_bdm'], true)) return 'sr_bdm';
        if (in_array($r, ['vice_president', 'vp'], true)) return 'vice_president';
        if (in_array($r, ['president'], true)) return 'president';
        if (in_array($r, ['site_manager', 'sm', 'director'], true)) return 'site_manager';
        return $r;
    }

    /**
     * Check if agent crossed a new rank threshold and award career reward
     */
    public function checkCareerRewards(int $agentId): array
    {
        try {
            $currentRank = $this->rankService->resolveRank($agentId);
            $gbv = $this->getAgentGbv($agentId);

            // Check if reward already awarded for this rank
            $stmt = $this->pdo->prepare("
                SELECT id FROM mlm_career_rewards
                WHERE user_id = ? AND rank_slug = ? AND status != 'cancelled' AND tenant_id = ?
                LIMIT 1
            ");
            $stmt->execute([$agentId, $currentRank, $this->getTenantId()]);
            if ($stmt->fetch()) {
                return ['success' => true, 'new_rank' => false, 'reward' => null, 'message' => 'Reward already awarded for ' . $currentRank];
            }

            // Load rank details from DB (fallback to hardcoded)
            $rankSlabs = $this->rankService->loadRankSlabsFromDb();
            $slab = $rankSlabs[$currentRank] ?? null;

            if (!$slab || empty($slab['reward_name'])) {
                return ['success' => true, 'new_rank' => false, 'reward' => null, 'message' => 'No reward defined for ' . $currentRank];
            }

            // Award the reward
            $stmt = $this->pdo->prepare("
                INSERT INTO mlm_career_rewards (tenant_id, user_id, rank_slug, reward_name, reward_value, gbv_at_award, status, awarded_at)
                VALUES (?, ?, ?, ?, ?, ?, 'awarded', NOW())
            ");
            $stmt->execute([
                $this->getTenantId(),
                $agentId,
                $currentRank,
                $slab['reward_name'],
                $slab['reward_value'] ?? 0,
                $gbv
            ]);

            return [
                'success'  => true,
                'new_rank' => true,
                'reward'   => [
                    'rank'         => $currentRank,
                    'rank_name'    => $slab['rank_name'],
                    'reward_name'  => $slab['reward_name'],
                    'reward_value' => $slab['reward_value'],
                    'gbv'          => $gbv,
                ],
            ];
        } catch (\Throwable $e) {
            error_log("[SalaryIncentiveService] checkCareerRewards FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get salary incentive tiers configuration
     */
    public function getSalaryTiers(): array
    {
        return self::SALARY_TIERS;
    }

    /**
     * Get agent's cumulative Group Business Volume
     */
    private function getAgentGbv(int $agentId): float
    {
        try {
            $stmt = $this->pdo->prepare("SELECT lifetime_sales FROM mlm_profiles WHERE user_id = ?");
            $stmt->execute([$agentId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (float) $row['lifetime_sales'] : 0.0;
        } catch (\Throwable $e) {
            return 0.0;
        }
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