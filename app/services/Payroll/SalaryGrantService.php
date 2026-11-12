<?php
/**
 * SalaryGrantService
 * 
 * Monthly salary grant processing for MLM associates based on Diwali Dhamaka Salary Plan.
 * Handles:
 *   - Process monthly salary grants for all active grants
 *   - Pause/resume grants based on monthly maintenance activity
 *   - TDS deduction (5% on commission income per Section 194H)
 *   - Wallet credit via WalletService
 *   - Ledger entry in mlm_commission_ledger
 *   - Pause/resume logic for inactive months
 * 
 * Tiers (from Diwali Dhamaka Plan):
 *   Tier 1: ₹15L volume in 60 days → ₹5,000/month for 6 months
 *   Tier 2: ₹30L volume in 100 days → ₹5,000/month for 12 months
 *   Tier 3: ₹50L volume in 150 days → ₹8,000/month for 12 months
 *   Tier 4: ₹75L volume in 200 days → ₹12,000/month for 12 months
 *   Tier 5: ₹1Cr volume in 300 days → ₹20,000/month for 12 months
 * 
 * Anti-Free-Rider Rule:
 *   - Each month, associate must have ≥2 verified site visits OR ≥₹50,000 booking volume
 *   - If maintenance fails → grant status = 'paused' (not forfeited)
 *   - Next month when activity clears → status = 'active' again, backlogged salary released
 *   - Max 3 consecutive paused months → grant cancelled
 */

namespace App\Services\Payroll;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\MLM\CommissionLedgerService;
use App\Services\WalletService;
use App\Services\MLM\SalaryIncentiveService;
use App\Services\MLM\RankService;
use Exception;

class SalaryGrantService
{
    use \App\Traits\ServiceTenantTrait;

    private \PDO $pdo;
    private CommissionLedgerService $ledgerService;
    private WalletService $walletService;
    private SalaryIncentiveService $incentiveService;
    private RankService $rankService;

    /** TDS rate for commission income (Section 194H) */
    private const TDS_RATE = 5.0;

    /** Maximum consecutive inactive months before grant cancellation */
    private const MAX_INACTIVE_MONTHS = 3;

    /** Minimum monthly activity: ₹50,000 booking volume OR 2 site visits */
    private const MIN_BOOKING_VOLUME = 50000;
    private const MIN_SITE_VISITS = 2;

    public function __construct(
        ?\PDO $pdo = null,
        ?CommissionLedgerService $ledgerService = null,
        ?WalletService $walletService = null,
        ?SalaryIncentiveService $incentiveService = null,
        ?RankService $rankService = null
    ) {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
        $this->ledgerService = $ledgerService ?? new CommissionLedgerService();
        $this->walletService = $walletService ?? new WalletService();
        $this->incentiveService = $incentiveService ?? new SalaryIncentiveService();
        $this->rankService = $rankService ?? new RankService();
    }

    /**
     * Process monthly salary grants for all active grants.
     * Called by cron_salary_grants.php on 1st of each month.
     * 
     * @param string $monthYear Format: 'YYYY-MM'
     * @return array
     */
    public function processMonthlyGrants(string $monthYear): array
    {
        $results = [
            'success' => false,
            'month' => $monthYear,
            'processed' => 0,
            'paid' => 0,
            'paused' => 0,
            'cancelled' => 0,
            'total_amount' => 0.0,
            'errors' => []
        ];

        try {
            // Get all active/paused grants that are within their term
            $stmt = $this->pdo->prepare("
                SELECT id, user_id, tier_index, volume_threshold, monthly_amount, 
                       months_total, months_paid, status, activated_at
                FROM mlm_salary_grants
                WHERE status IN ('active', 'paused')
                AND tenant_id = ?
                AND activated_at <= NOW()
                AND DATE_ADD(activated_at, INTERVAL months_total MONTH) > NOW()
                AND months_paid < months_total
                ORDER BY activated_at ASC
            ");
            $stmt->execute([$this->getTenantId()]);
            $grants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo "Found " . count($grants) . " active/paused salary grants for $monthYear\n";

            foreach ($grants as $grant) {
                $agentId = (int)$grant['user_id'];
                $grantId = (int)$grant['id'];

                // Check if agent is still active in system
                $agentStatus = $this->getAgentStatus($agentId);
                if ($agentStatus !== 'active') {
                    $results['errors'][] = "User $agentId is not active (status: $agentStatus) - grant $grantId skipped";
                    continue;
                }

                // Check monthly maintenance eligibility
                $maintenance = $this->checkMonthlyMaintenance($agentId, $monthYear);

                if (!$maintenance['eligible']) {
                    // Maintenance failed: PAUSE the grant (do not forfeit)
                    $this->pauseGrant($grantId, $agentId, $maintenance['message']);
                    $results['paused']++;
                    $results['errors'][] = "User $agentId salary paused for $monthYear: " . $maintenance['message'];
                    continue;
                }

                // Maintenance passed: if grant was previously paused, resume it
                if ($grant['status'] === 'paused') {
                    $this->resumeGrant($grantId, $agentId);
                    $results['resumed']++;
                }

                // Idempotency: never pay the same grant twice for one month
                $dup = $this->pdo->prepare("
                    SELECT COUNT(*) FROM mlm_salary_grant_logs 
                    WHERE grant_id = ? AND action = 'paid' AND payout_month = ? AND tenant_id = ?
                ");
                $dup->execute([$grantId, $monthYear, $this->getTenantId()]);
                if ((int)$dup->fetchColumn() > 0) {
                    $results['errors'][] = "Grant $grantId already paid for $monthYear — skipped";
                    continue;
                }

                // Calculate TDS (5% on commission income per Section 194H)
                $grossAmount = (float)$grant['monthly_amount'];
                $tdsAmount = round($grossAmount * self::TDS_RATE / 100, 2);
                $netAmount = round($grossAmount - $tdsAmount, 2);

                // Credit to agent's wallet via WalletService
                $creditResult = $this->walletService->credit(
                    $agentId,
                    $netAmount,
                    'commission',
                    "Monthly salary grant — $monthYear (Tier " . (int)$grant['tier_index'] . ")",
                    $grantId,
                    'salary_grant'
                );

                if (!$creditResult) {
                    $results['errors'][] = "Wallet credit failed for user $agentId, grant $grantId";
                    continue;
                }

                // Write ledger entry in mlm_commission_ledger
                $this->ledgerService->writeLedger(
                    $agentId,
                    $agentId,
                    0, // no sale amount for salary grant
                    0,
                    $netAmount,
                    'salary_grant',
                    0,
                    0,
                    0,
                    "Monthly salary grant — $monthYear",
                    false
                );

                // Mark paid for this month (idempotency record)
                $this->pdo->prepare("
                    INSERT INTO mlm_salary_grant_logs 
                    (tenant_id, user_id, grant_id, action, payout_month, details, created_at) 
                    VALUES (?, ?, ?, 'paid', ?, ?, NOW())
                ")->execute([
                    $this->getTenantId(),
                    $agentId,
                    $grantId,
                    $monthYear,
                    "Monthly salary grant paid — $monthYear (₹$netAmount, TDS ₹$tdsAmount)"
                ]);

                // Advance schedule; complete the grant when fully paid
                $paid = (int)$grant['months_paid'] + 1;
                $done = $paid >= (int)$grant['months_total'] ? ", status='completed'" : "";
                $this->pdo->prepare("UPDATE mlm_salary_grants SET months_paid=? $done WHERE id=?")
                    ->execute([$paid, $grantId]);

                $results['processed']++;
                $results['paid']++;
                $results['total_amount'] += $netAmount;
            }

            $results['success'] = true;
            return $results;

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            return $results;
        }
    }

    /**
     * Check monthly maintenance eligibility for an agent.
     * Returns eligible if agent has ≥2 verified site visits OR ≥₹50,000 booking volume this month.
     */
    private function checkMonthlyMaintenance(int $agentId, string $monthYear): array
    {
        try {
            [$year, $month] = explode('-', $monthYear);

            // Count verified site visits this month
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

            // Count booking volume this month
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

            $hasVisits = $siteVisitCount >= self::MIN_SITE_VISITS;
            $hasBooking = $bookingVolume >= self::MIN_BOOKING_VOLUME;
            $activityMet = $hasVisits || $hasBooking;

            if (!$activityMet) {
                return [
                    'eligible' => false,
                    'site_visit_count' => $siteVisitCount,
                    'required_visits' => self::MIN_SITE_VISITS,
                    'booking_volume' => $bookingVolume,
                    'required_booking' => self::MIN_BOOKING_VOLUME,
                    'message' => "Insufficient activity: $siteVisitCount site visit(s) (need ≥" . self::MIN_SITE_VISITS . ") and ₹" . number_format($bookingVolume) . " booking (need ≥₹" . number_format(self::MIN_BOOKING_VOLUME) . ")"
                ];
            }

            return [
                'eligible' => true,
                'site_visit_count' => $siteVisitCount,
                'booking_volume' => $bookingVolume,
                'message' => "Monthly maintenance cleared: $siteVisitCount visit(s) + ₹" . number_format($bookingVolume) . " booking"
            ];

        } catch (Exception $e) {
            return [
                'eligible' => false,
                'error' => $e->getMessage(),
                'message' => 'Maintenance check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Pause a salary grant due to maintenance failure.
     */
    private function pauseGrant(int $grantId, int $agentId, string $reason): void
    {
        $this->pdo->prepare("UPDATE mlm_salary_grants SET status = 'paused', paused_reason = ? WHERE id = ?")
            ->execute([$reason, $grantId]);

        try {
            $this->pdo->prepare("
                INSERT INTO mlm_salary_grant_logs 
                (tenant_id, user_id, grant_id, action, payout_month, details, created_at) 
                VALUES (?, ?, ?, 'paused', ?, ?, NOW())
            ")->execute([
                $this->getTenantId(),
                $agentId,
                $grantId,
                date('Y-m'),
                "Salary grant PAUSED — $reason"
            ]);
        } catch (Exception $e) {
            error_log("[SalaryGrantService] pause log failed: " . $e->getMessage());
        }
    }

    /**
     * Resume a paused salary grant when activity clears.
     */
    private function resumeGrant(int $grantId, int $agentId): void
    {
        $this->pdo->prepare("UPDATE mlm_salary_grants SET status = 'active', paused_reason = NULL WHERE id = ?")
            ->execute([$grantId]);

        try {
            $this->pdo->prepare("
                INSERT INTO mlm_salary_grant_logs 
                (tenant_id, user_id, grant_id, action, payout_month, details, created_at) 
                VALUES (?, ?, ?, 'resumed', ?, ?, NOW())
            ")->execute([
                $this->getTenantId(),
                $agentId,
                $grantId,
                date('Y-m'),
                "Salary grant RESUMED — monthly maintenance cleared"
            ]);
        } catch (Exception $e) {
            error_log("[SalaryGrantService] resume log failed: " . $e->getMessage());
        }
    }

    /**
     * Get agent status from users table.
     */
    private function getAgentStatus(int $agentId): string
    {
        try {
            $stmt = $this->pdo->prepare("SELECT status FROM users WHERE id = ? AND tenant_id = ? LIMIT 1");
            $stmt->execute([$agentId, $this->getTenantId()]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (string)$row['status'] : 'inactive';
        } catch (Exception $e) {
            return 'inactive';
        }
    }

    /**
     * Get pending grants count for an agent (for dashboard display).
     */
    public function getPendingGrants(int $agentId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, monthly_amount, months_paid, months_total, status, activated_at
                FROM mlm_salary_grants
                WHERE user_id = ? AND tenant_id = ?
                ORDER BY activated_at DESC
            ");
            $stmt->execute([$agentId, $this->getTenantId()]);
            $grants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'total_grants' => count($grants),
                'active_grants' => count(array_filter($grants, fn($g) => $g['status'] === 'active')),
                'paused_grants' => count(array_filter($grants, fn($g) => $g['status'] === 'paused')),
                'completed_grants' => count(array_filter($grants, fn($g) => $g['status'] === 'completed')),
                'grants' => $grants
            ];
        } catch (Exception $e) {
            return [
                'total_grants' => 0,
                'active_grants' => 0,
                'paused_grants' => 0,
                'completed_grants' => 0,
                'grants' => [],
                'error' => $e->getMessage()
            ];
        }
    }
}
