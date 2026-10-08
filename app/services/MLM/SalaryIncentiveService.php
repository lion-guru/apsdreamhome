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
     */
    public function processMonthlyGrants(string $monthYear): array
    {
        try {
            // Active window derived from activated_at + months_total (no end_date column).
            $stmt = $this->pdo->prepare("
                SELECT id, user_id, monthly_amount, months_total, months_paid, activated_at
                FROM mlm_salary_grants
                WHERE status = 'active'
                AND tenant_id = ?
                AND activated_at <= NOW()
                AND DATE_ADD(activated_at, INTERVAL months_total MONTH) > NOW()
                AND months_paid < months_total
            ");
            $stmt->execute([$this->getTenantId()]);
            $grants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $results = ['processed' => 0, 'amount' => 0, 'errors' => []];

            foreach ($grants as $grant) {
                // Check if agent is still active
                $stmt = $this->pdo->prepare("SELECT status FROM associates WHERE user_id = ? LIMIT 1");
                $stmt->execute([$grant['user_id']]);
                $status = $stmt->fetchColumn();

                if ($status !== 'active') {
                    $results['errors'][] = "User {$grant['user_id']} is not active (status: $status)";
                    continue;
                }

                // Check monthly maintenance eligibility
                $maintenance = $this->checkMonthlyMaintenance($grant['user_id'], $monthYear);
                if (!$maintenance['success'] || !$maintenance['eligible']) {
                    $results['errors'][] = "User {$grant['user_id']} failed monthly maintenance: " . ($maintenance['error'] ?? $maintenance['message']);
                    continue;
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
     * Check monthly maintenance eligibility (agent must have ≥₹50K side volume)
     */
    public function checkMonthlyMaintenance(int $agentId, string $monthYear): array
    {
        try {
            // plot_bookings.associate_id stores users.id (not associates.id).
            // Statuses are the live ENUM — cancelled/defaulted/transferred
            // bookings do not count toward maintenance volume.
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(pb.booking_amount), 0) AS monthly_volume
                FROM plot_bookings pb
                WHERE pb.associate_id = ?
                AND YEAR(pb.created_at) = YEAR(CONCAT(?, '-01'))
                AND MONTH(pb.created_at) = MONTH(CONCAT(?, '-01'))
                AND pb.status NOT IN ('cancelled', 'defaulted', 'transferred')
            ");
            $stmt->execute([$agentId, $monthYear, $monthYear]);
            $volume = (float) $stmt->fetchColumn();

            if ($volume >= 50000) {
                return ['success' => true, 'eligible' => true, 'volume' => $volume];
            }

            return [
                'success' => true,
                'eligible' => false,
                'volume' => $volume,
                'required' => 50000,
                'message' => "Monthly volume ₹" . number_format($volume) . " below ₹50,000 minimum"
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
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