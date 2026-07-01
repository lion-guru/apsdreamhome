<?php
/**
 * MatchingBonusService
 *
 * Matches a percentage of downline leaders' earned commissions.
 * The matching bonus incentivizes leaders to develop other leaders.
 *
 * Business Rules:
 * - Only BDM+ rank qualifies
 * - Matches direct generation leaders' commission earnings (direct_sale + level_bonus)
 * - Gen 1 leaders: 100% match (1:1)
 * - Gen 2 leaders: 50% match
 * - Gen 3 leaders: 25% match
 * - Must be active (≥₹10K monthly volume)
 * - Runs monthly
 *
 * Tables: mlm_matching_bonuses, mlm_network_tree, mlm_commission_ledger
 */

namespace App\Services\MLM;

use PDO;
use Exception;

class MatchingBonusService
{
    /** @var PDO|null */
    protected $db;

    public const QUALIFYING_RANKS = ['bdm', 'sr_bdm', 'vice_president', 'president', 'site_manager'];

    /**
     * Match percentage by generation depth.
     * Gen1=100% means the upline earns 100% of Gen1 downline leader's commissions as a bonus.
     * Source: AGENTS.md Matching Bonus Rates.
     */
    public const DEFAULT_MATCH_RATES = [
        1 => 100.0,  // 100% of Gen 1 leaders' earnings
        2 => 50.0,   // 50% of Gen 2
        3 => 25.0,   // 25% of Gen 3
    ];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
            } catch (\Throwable $e) {
                $pdo = null;
            }
        }
        $this->db = $pdo;
    }

    /**
     * Main entry: Calculate matching bonuses for all qualified leaders.
     *
     * @param string $periodStart  YYYY-MM-01
     * @param string $periodEnd    YYYY-MM-last-day
     * @return array ['entries' => [...], 'total' => float, 'processed_leaders' => int]
     */
    public function calculateMonthlyMatching(string $periodStart, string $periodEnd): array
    {
        $result = ['entries' => [], 'total' => 0.0, 'processed_leaders' => 0];
        
        // Strategic Decision: Matching bonus is completely disabled to protect company margins from unsustainable payouts.
        return $result;
        
        if (!$this->db) return $result;

        try {
            $enabled = $this->getSetting('matching_bonus_enabled', '1');
            if ($enabled !== '1') return $result;

            $leaders = $this->getQualifiedLeaders();
            if (empty($leaders)) return $result;

            foreach ($leaders as $leader) {
                $leaderResult = $this->calculateLeaderMatching(
                    (int)$leader['user_id'],
                    $periodStart,
                    $periodEnd
                );
                if (!empty($leaderResult['entries'])) {
                    $result['entries'] = array_merge($result['entries'], $leaderResult['entries']);
                    $result['total'] += $leaderResult['total'];
                    $result['processed_leaders']++;
                }
            }
        } catch (Exception $e) {
            error_log("[MatchingBonusService] calculateMonthlyMatching error: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Calculate matching bonus for a single leader.
     * Finds direct downline leaders and matches their earnings.
     */
    public function calculateLeaderMatching(int $leaderUserId, string $periodStart, string $periodEnd): array
    {
        $result = ['entries' => [], 'total' => 0.0];
        if (!$this->db || $leaderUserId <= 0) return $result;

        try {
            $maxLevels = (int)$this->getSetting('matching_max_levels', '3');
            $matchRates = $this->getMatchRates();

            $leaderAssocId = $this->getAssociateId($leaderUserId);
            if (!$leaderAssocId) return $result;

            // Walk generations and match earnings
            for ($gen = 1; $gen <= $maxLevels; $gen++) {
                $matchPct = $matchRates[$gen] ?? 0;
                if ($matchPct <= 0) break;

                // Get leaders in this generation
                $genLeaders = $this->getGenerationLeaders($leaderAssocId, $gen);
                if (empty($genLeaders)) continue;

                foreach ($genLeaders as $genLeader) {
                    $genLeaderUserId = (int)$genLeader['user_id'];

                    // Skip self-match
                    if ($genLeaderUserId === $leaderUserId) continue;

                    // Sum their commissions from ledger in the period
                    $leaderEarnings = $this->getLeaderEarnings($genLeaderUserId, $periodStart, $periodEnd);
                    if ($leaderEarnings <= 0) continue;

                    $bonusAmount = round($leaderEarnings * ($matchPct / 100.0), 2);
                    if ($bonusAmount <= 0) continue;

                    $result['entries'][] = [
                        'beneficiary_user_id' => $leaderUserId,
                        'source_user_id'      => $genLeaderUserId,
                        'commission_type'     => 'matching_bonus',
                        'level'               => $gen,
                        'pct'                 => $matchPct,
                        'amount'              => $bonusAmount,
                        'matched_amount'      => $leaderEarnings,
                        'matched_user_rank'   => $genLeader['rank'] ?? 'associate',
                        'period_start'        => $periodStart,
                        'period_end'          => $periodEnd,
                    ];
                    $result['total'] += $bonusAmount;
                }
            }
        } catch (Exception $e) {
            error_log("[MatchingBonusService] calculateLeaderMatching error for user {$leaderUserId}: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Persist matching bonus entries.
     */
    public function persistMatchingBonuses(array $entries): array
    {
        $result = ['created_ids' => [], 'total' => 0.0];
        if (!$this->db || empty($entries)) return $result;

        try {
            $this->db->beginTransaction();

            $ledgerStmt = $this->db->prepare("
                INSERT INTO mlm_commission_ledger
                    (beneficiary_user_id, source_user_id, commission_type, level, amount,
                     status, sale_amount, commission_percentage, notes, booking_id, created_at)
                VALUES
                    (?, ?, 'matching_bonus', ?, ?, 'pending', ?, ?, ?, 0, NOW())
            ");

            $matchStmt = $this->db->prepare("
                INSERT INTO mlm_matching_bonuses
                    (beneficiary_user_id, matched_user_id, match_level, matched_commission_type,
                     matched_amount, match_pct, bonus_amount, period_start, period_end, status, created_at)
                VALUES
                    (?, ?, ?, 'combined', ?, ?, ?, ?, ?, 'pending', NOW())
            ");

            // Per-entry dedup check
            $dedupStmt = $this->db->prepare("SELECT COUNT(*) FROM mlm_matching_bonuses WHERE beneficiary_user_id = ? AND match_level = ? AND period_start = ? AND period_end = ?");

            foreach ($entries as $e) {
                $beneficiary = (int)$e['beneficiary_user_id'];
                $matched = (int)$e['source_user_id'];
                $level = (int)$e['level'];
                $amount = (float)$e['amount'];
                $matchedAmount = (float)($e['matched_amount'] ?? 0);
                $pct = (float)$e['pct'];
                $periodStart = $e['period_start'] ?? date('Y-m-01');
                $periodEnd = $e['period_end'] ?? date('Y-m-t');

                // Skip self-match
                if ($beneficiary === $matched) continue;

                // Per-entry dedup
                $dedupStmt->execute([$beneficiary, $level, $periodStart, $periodEnd]);
                if ((int)$dedupStmt->fetchColumn() > 0) continue;

                $notes = "Match of user #{$matched} at {$pct}% (Gen {$level})";

                $ledgerStmt->execute([$beneficiary, $matched, $level, $amount, $matchedAmount, $pct, $notes]);
                $result['created_ids'][] = (int)$this->db->lastInsertId();

                $matchStmt->execute([$beneficiary, $matched, $level, $matchedAmount, $pct, $amount, $periodStart, $periodEnd]);

                $result['total'] += $amount;
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("[MatchingBonusService] persist error: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Get leaders in a specific generation from a leader's downline.
     * Gen 1 = direct children, Gen 2 = children of children, etc.
     * NOTE: network tree parent_id stores user_ids, not associate_ids.
     */
    protected function getGenerationLeaders(int $leaderAssocId, int $generation): array
    {
        if (!$this->db) return [];

        try {
            // Resolve leader's user_id for parent_id matching
            $leaderUserId = $this->getUserIdFromAssoc($leaderAssocId);
            if (!$leaderUserId) return [];

            $currentParentUserIds = [$leaderUserId];

            for ($gen = 1; $gen <= $generation; $gen++) {
                $placeholders = implode(',', array_fill(0, count($currentParentUserIds), '?'));
                $stmt = $this->db->prepare("
                    SELECT nt.associate_id, a.user_id, a.level
                    FROM mlm_network_tree nt
                    INNER JOIN associates a ON a.id = nt.associate_id
                    WHERE nt.parent_id IN ({$placeholders})
                      AND a.status = 'active'
                ");
                $stmt->execute($currentParentUserIds);
                $children = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                if ($gen === $generation) {
                    // Filter to only leaders (non-associate rank)
                    $leaders = [];
                    foreach ($children as $child) {
                        if (($child['level'] ?? 'associate') !== 'associate') {
                            $leaders[] = $child;
                        }
                    }
                    return $leaders;
                }

                // Continue walking down — use user_ids for next parent_id match
                $currentParentUserIds = array_map(fn($c) => (int)$c['user_id'], $children);
                if (empty($currentParentUserIds)) break;
            }
        } catch (\Throwable $e) {
            error_log("[MatchingBonusService] getGenerationLeaders error: " . $e->getMessage());
        }
        return [];
    }

    protected function getUserIdFromAssoc(int $associateId): ?int
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare("SELECT user_id FROM associates WHERE id = ? LIMIT 1");
            $stmt->execute([$associateId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['user_id'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Sum a leader's earned commissions from ledger in a period.
     * Counts: direct_sale, level_bonus (differential overrides), performance_bonus.
     */
    protected function getLeaderEarnings(int $userId, string $periodStart, string $periodEnd): float
    {
        if (!$this->db) return 0.0;
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) AS total
                FROM mlm_commission_ledger
                WHERE beneficiary_user_id = ?
                  AND commission_type IN ('direct_sale', 'level_bonus', 'performance_bonus', 'team_bonus')
                  AND status IN ('pending', 'approved', 'paid')
                  AND created_at >= ?
                  AND created_at < DATE_ADD(?, INTERVAL 1 DAY)
            ");
            $stmt->execute([$userId, $periodStart, $periodEnd]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($row['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    protected function getQualifiedLeaders(): array
    {
        if (!$this->db) return [];
        try {
            $placeholders = implode(',', array_fill(0, count(self::QUALIFYING_RANKS), '?'));
            $stmt = $this->db->prepare("
                SELECT u.id AS user_id, a.level AS rank
                FROM users u
                INNER JOIN associates a ON a.user_id = u.id
                WHERE a.status = 'active'
                  AND a.level IN ({$placeholders})
            ");
            $stmt->execute(self::QUALIFYING_RANKS);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getAssociateId(int $userId): ?int
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare("SELECT id FROM associates WHERE user_id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['id'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function getMatchRates(): array
    {
        return [
            1 => (float)$this->getSetting('gen1_match_pct', '10.0'),
            2 => (float)$this->getSetting('gen2_match_pct', '5.0'),
            3 => (float)$this->getSetting('gen3_match_pct', '2.0'),
        ];
    }

    protected function getSetting(string $key, string $default = ''): string
    {
        if (!$this->db) return $default;
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM mlm_settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['setting_value'] : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
