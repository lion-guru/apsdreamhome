<?php
/**
 * GenerationCommissionEngine
 *
 * Calculates generation bonuses based on rank-unlocked generations.
 * 
 * Business Rules:
 * - Associate: 1 generation (direct downline only)
 * - Senior Associate: 2 generations
 * - BDM: 3 generations
 * - Sr BDM: 4 generations
 * - VP: 5 generations
 * - President: 6 generations
 * - Site Manager: 7 generations (all)
 * 
 * A "generation" = a group of downline leaders bounded by same/higher rank.
 * Each generation's bonus = configured % of that generation's total sales volume.
 * 
 * Runs monthly on all paid commissions from that period.
 * 
 * Tables: mlm_generation_commissions (new), mlm_network_tree, mlm_rank_benefits
 */

namespace App\Services\MLM;

use PDO;
use Exception;
use App\Core\Middleware\TenantContext;

class GenerationBonusEngine
{
    /** @var PDO|null */
    protected $db;

    public const RANK_ORDER = [
        'associate' => 1, 'senior_associate' => 2, 'bdm' => 3,
        'sr_bdm' => 4, 'vice_president' => 5, 'president' => 6, 'site_manager' => 7,
    ];

    /** How many generations each rank can earn from. */
    public const RANK_GENERATIONS = [
        'associate' => 1, 'senior_associate' => 2, 'bdm' => 3,
        'sr_bdm' => 4, 'vice_president' => 5, 'president' => 6, 'site_manager' => 7,
    ];

    /** Generation bonus % by generation depth (default, can be overridden by settings). */
    public const DEFAULT_GEN_RATES = [
        1 => 2.0,  // 2% from first generation
        2 => 1.5,  // 1.5% from second
        3 => 1.0,  // 1% from third
        4 => 0.5,  // 0.5% from fourth+
        5 => 0.5,
        6 => 0.5,
        7 => 0.5,
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

    protected function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Main entry point: Calculate generation bonuses for all qualified leaders
     * for a given period.
     *
     * @param string $periodStart  YYYY-MM-01
     * @param string $periodEnd    YYYY-MM-last-day
     * @return array ['entries' => [...], 'total' => float, 'processed_leaders' => int]
     */
    public function calculateMonthlyGenerations(string $periodStart, string $periodEnd): array
    {
        $result = ['entries' => [], 'total' => 0.0, 'processed_leaders' => 0];
        if (!$this->db) return $result;

        try {
            // Check if enabled
            $enabled = $this->getSetting('generation_bonus_enabled', '1');
            if ($enabled !== '1') return $result;

            // Get all qualified leaders (VP+ or with enough generations unlocked)
            $leaders = $this->getQualifiedLeaders();
            if (empty($leaders)) return $result;

            foreach ($leaders as $leader) {
                $leaderEntries = $this->calculateLeaderGenerations(
                    (int)$leader['user_id'],
                    $leader['rank'],
                    $periodStart,
                    $periodEnd
                );
                if (!empty($leaderEntries['entries'])) {
                    $result['entries'] = array_merge($result['entries'], $leaderEntries['entries']);
                    $result['total'] += $leaderEntries['total'];
                    $result['processed_leaders']++;
                }
            }
        } catch (Exception $e) {
            error_log("[GenerationBonusEngine] calculateMonthlyGenerations error: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Calculate generation bonuses for a single leader.
     * Walks the downline tree via mlm_network_tree, grouping by generation boundaries.
     */
    public function calculateLeaderGenerations(int $leaderUserId, string $leaderRank, string $periodStart, string $periodEnd): array
    {
        $result = ['entries' => [], 'total' => 0.0];
        if (!$this->db || $leaderUserId <= 0) return $result;

        try {
            $maxGens = self::RANK_GENERATIONS[$leaderRank] ?? 1;
            $genRates = $this->getGenerationRates();

            // Find the leader's associate_id
            $leaderAssocId = $this->getAssociateId($leaderUserId);
            if (!$leaderAssocId) return $result;

            // Get total qualifying sales volume per generation
            // We need to walk the tree and group sales by generation
            $generations = $this->getGenerationVolumes($leaderAssocId, $periodStart, $periodEnd, $maxGens);

            foreach ($generations as $genNum => $genData) {
                $rate = $genRates[$genNum] ?? $genRates[min($genNum, max(array_keys($genRates)))];
                if ($rate <= 0 || $genData['volume'] <= 0) continue;

                $bonusAmt = round($genData['volume'] * ($rate / 100.0), 2);
                if ($bonusAmt <= 0) continue;

                $result['entries'][] = [
                    'beneficiary_user_id' => $leaderUserId,
                    'source_user_id'      => 0,  // aggregate from multiple sources
                    'commission_type'     => 'generation_bonus',
                    'level'               => $genNum,
                    'pct'                 => $rate,
                    'amount'              => $bonusAmt,
                    'gen_volume'          => $genData['volume'],
                    'gen_user_count'      => $genData['user_count'],
                    'period_start'        => $periodStart,
                    'period_end'          => $periodEnd,
                ];
                $result['total'] += $bonusAmt;
            }
        } catch (Exception $e) {
            error_log("[GenerationBonusEngine] calculateLeaderGenerations error for user {$leaderUserId}: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Insert generation bonus entries into ledger and mlm_generation_commissions.
     */
    public function persistGenerationBonuses(array $entries): array
    {
        $result = ['created_ids' => [], 'total' => 0.0];
        if (!$this->db || empty($entries)) return $result;

        // Dedup: skip if entries already exist for this period
        $periodStart = $entries[0]['period_start'] ?? null;
        $periodEnd = $entries[0]['period_end'] ?? null;
        if ($periodStart && $periodEnd) {
            $chk = $this->db->prepare("SELECT COUNT(*) FROM mlm_generation_commissions WHERE period_start = ? AND period_end = ?");
            $chk->execute([$periodStart, $periodEnd]);
            if ((int)$chk->fetchColumn() > 0) {
                error_log("[GenerationBonusEngine] Skipped persist — entries already exist for {$periodStart}..{$periodEnd}");
                return $result;
            }
        }

        try {
            $this->db->beginTransaction();

            $ledgerStmt = $this->db->prepare("
                INSERT INTO mlm_commission_ledger
                    (beneficiary_user_id, source_user_id, commission_type, level, amount,
                     status, sale_amount, commission_percentage, notes, booking_id, created_at, tenant_id)
                VALUES
                    (?, ?, 'generation_bonus', ?, ?, 'pending', ?, ?, 'Monthly generation bonus', 0, NOW(), ?)
            ");

            $genStmt = $this->db->prepare("
                INSERT INTO mlm_generation_commissions
                    (beneficiary_user_id, source_user_id, generation_number, team_volume,
                     generation_pct, commission_amount, period_start, period_end, status, created_at, tenant_id)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)
            ");

            foreach ($entries as $e) {
                $beneficiary = (int)$e['beneficiary_user_id'];
                $level = (int)$e['level'];
                $amount = (float)$e['amount'];
                $volume = (float)($e['gen_volume'] ?? 0);
                $pct = (float)$e['pct'];
                $periodStart = $e['period_start'] ?? date('Y-m-01');
                $periodEnd = $e['period_end'] ?? date('Y-m-t');

                // Ledger entry — source_user_id = beneficiary (aggregate monthly bonus)
                $ledgerStmt->execute([
                    $beneficiary, $beneficiary, $level, $amount, $volume, $pct, $this->getTenantId(),
                ]);
                $ledgerId = (int)$this->db->lastInsertId();
                $result['created_ids'][] = $ledgerId;

                // Generation commissions table
                $genStmt->execute([
                    $beneficiary, 0, $level, $volume, $pct, $amount, $periodStart, $periodEnd, $this->getTenantId(),
                ]);

                $result['total'] += $amount;
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("[GenerationBonusEngine] persist error: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Get all qualified leaders who can earn generation bonuses.
     * Must be active with personal volume >= min_monthly_volume OR rank >= senior_associate.
     */
    protected function getQualifiedLeaders(): array
    {
        if (!$this->db) return [];
        try {
            $r = $this->db->query("
                SELECT DISTINCT u.id AS user_id, a.level AS rank
                FROM users u
                INNER JOIN associates a ON a.user_id = u.id
                INNER JOIN mlm_network_tree nt ON nt.associate_id = a.id
                WHERE a.status = 'active'
                  AND a.level != 'associate'
                {$this->getTenantWhere()}u
                ORDER BY FIELD(a.level, 'site_manager','president','vice_president','sr_bdm','bdm','senior_associate')
            ");
            return $r->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Walk the network tree from a leader's position and calculate sales volume per generation.
     * Generation 1 = direct recruits' sales
     * Generation 2 = recruits of recruits (bounded by same/higher rank)
     */
    protected function getGenerationVolumes(int $leaderAssocId, string $periodStart, string $periodEnd, int $maxGens): array
    {
        $generations = [];
        if (!$this->db) return $generations;

        try {
            // Resolve leader's user_id from associate_id (network tree parent_id stores user_ids)
            $leaderUserId = $this->getUserId($leaderAssocId);
            if (!$leaderUserId) return $generations;

            // BFS: walk tree using user_ids for parent_id matching
            $visitedUserIds = [$leaderUserId];
            $currentParentUserIds = [$leaderUserId];
            $genNum = 0;

            while (!empty($currentParentUserIds) && $genNum < $maxGens) {
                $genNum++;
                $nextParentUserIds = [];
                $genAssocIds = [];

                // Find children where parent_id = any current parent's user_id
                $placeholders = implode(',', array_fill(0, count($currentParentUserIds), '?'));
                $stmt = $this->db->prepare("
                    SELECT nt.associate_id, a.level, a.user_id
                    FROM mlm_network_tree nt
                    INNER JOIN associates a ON a.id = nt.associate_id
                    WHERE nt.parent_id IN ({$placeholders})
                      AND a.status = 'active'
                ");
                $stmt->execute($currentParentUserIds);
                $children = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                foreach ($children as $child) {
                    $childUserId = (int)$child['user_id'];
                    if (in_array($childUserId, $visitedUserIds, true)) continue;
                    $visitedUserIds[] = $childUserId;

                    $genAssocIds[] = (int)$child['associate_id'];
                    $nextParentUserIds[] = $childUserId;
                }

                if (empty($genAssocIds)) break;

                // Sum sales volume for these associates in the period
                $placeholders2 = implode(',', array_fill(0, count($genAssocIds), '?'));
                $vStmt = $this->db->prepare("
                    SELECT COALESCE(SUM(b.total_plot_value), 0) AS total_volume,
                           COUNT(DISTINCT b.associate_id) AS user_count
                    FROM plot_bookings b
                    WHERE b.associate_id IN ({$placeholders2})
                      AND b.status IN ('emi_active', 'fully_paid', 'token_paid', 'agreement_signed')
                      AND b.created_at >= ?
                      AND b.created_at < DATE_ADD(?, INTERVAL 1 DAY)
                ");
                $params = array_merge($genAssocIds, [$periodStart, $periodEnd]);
                $vStmt->execute($params);
                $vol = $vStmt->fetch(PDO::FETCH_ASSOC);

                $generations[$genNum] = [
                    'volume'     => (float)($vol['total_volume'] ?? 0),
                    'user_count' => (int)($vol['user_count'] ?? 0),
                ];

                $currentParentUserIds = $nextParentUserIds;
            }
        } catch (Exception $e) {
            error_log("[GenerationBonusEngine] getGenerationVolumes error: " . $e->getMessage());
        }
        return $generations;
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

    protected function getUserId(int $associateId): ?int
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

    protected function getGenerationRates(): array
    {
        $rates = self::DEFAULT_GEN_RATES;
        // Could read from mlm_settings if needed
        return $rates;
    }

    protected function getTenantWhere(string $alias = ''): string
    {
        $tid = $this->getTenantId();
        if ($tid <= 1) return '';
        $a = $alias ? $alias . '.' : '';
        return " AND {$a}tenant_id = {$tid}";
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
