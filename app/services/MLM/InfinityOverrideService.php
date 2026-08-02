<?php
/**
 * InfinityOverrideService
 *
 * Pays a small % override on ALL sales below a qualified leader,
 * regardless of depth (unlimited depth). Only VP+ ranks qualify.
 *
 * Business Rules:
 * - Only VP, President, Site Manager qualify
 * - Must be "Active" (≥₹10K personal volume/month)
 * - Rate: 1% of sale value (configurable via mlm_settings.infinity_override_pct)
 * - Applies to ALL bookings in their downline at any depth
 * - Runs monthly alongside generation bonuses
 *
 * Tables: mlm_infinity_overrides, mlm_network_tree, associates, plot_bookings
 */

namespace App\Services\MLM;

use PDO;
use Exception;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

class InfinityOverrideService
{
    use \App\Traits\ServiceTenantTrait;
    /** @var PDO|null */
    protected $db;

    public const QUALIFYING_RANKS = ['vice_president', 'president', 'site_manager'];

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
     * Main entry: Calculate infinity overrides for all qualified leaders.
     *
     * @param string $periodStart  YYYY-MM-01
     * @param string $periodEnd    YYYY-MM-last-day
     * @return array ['entries' => [...], 'total' => float, 'processed_leaders' => int]
     */
    public function calculateMonthlyOverrides(string $periodStart, string $periodEnd): array
    {
        $result = ['entries' => [], 'total' => 0.0, 'processed_leaders' => 0];
        if (!$this->db) return $result;

        try {
            $enabled = $this->getSetting('infinity_override_enabled', '1');
            if ($enabled !== '1') return $result;

            $overridePct = (float)$this->getSetting('infinity_override_pct', '1.0');
            if ($overridePct <= 0) return $result;

            // Get qualified leaders
            $leaders = $this->getQualifiedLeaders();
            if (empty($leaders)) return $result;

            foreach ($leaders as $leader) {
                $leaderResult = $this->calculateLeaderOverride(
                    (int)$leader['user_id'],
                    $overridePct,
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
            error_log("[InfinityOverrideService] calculateMonthlyOverrides error: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Calculate infinity override for a single leader.
     * Finds all bookings in their downline at any depth.
     */
    public function calculateLeaderOverride(int $leaderUserId, float $overridePct, string $periodStart, string $periodEnd): array
    {
        $result = ['entries' => [], 'total' => 0.0];
        if (!$this->db || $leaderUserId <= 0) return $result;

        try {
            $leaderAssocId = $this->getAssociateId($leaderUserId);
            if (!$leaderAssocId) return $result;

            // Get ALL downline associate IDs (unlimited depth via BFS)
            $downlineAssocIds = $this->getAllDownline($leaderAssocId);
            if (empty($downlineAssocIds)) return $result;

            // Sum all qualifying bookings from downline in the period
            $placeholders = implode(',', array_fill(0, count($downlineAssocIds), '?'));
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(b.total_plot_value), 0) AS total_volume,
                       COUNT(*) AS booking_count
                FROM plot_bookings b
                WHERE b.associate_id IN ({$placeholders})
                  AND b.status IN ('emi_active', 'fully_paid', 'token_paid', 'agreement_signed')
                  AND b.created_at >= ?
                  AND b.created_at < DATE_ADD(?, INTERVAL 1 DAY)
            ");
            $params = array_merge($downlineAssocIds, [$periodStart, $periodEnd]);
            $stmt->execute($params);
            $vol = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalVolume = (float)($vol['total_volume'] ?? 0);
            $bookingCount = (int)($vol['booking_count'] ?? 0);

            if ($totalVolume <= 0) return $result;

            $overrideAmount = round($totalVolume * ($overridePct / 100.0), 2);
            if ($overrideAmount <= 0) return $result;

            $result['entries'][] = [
                'beneficiary_user_id' => $leaderUserId,
                'source_user_id'      => 0,  // aggregate
                'commission_type'     => 'infinity_override',
                'level'               => 0,
                'pct'                 => $overridePct,
                'amount'              => $overrideAmount,
                'sale_amount'         => $totalVolume,
                'depth_count'         => count($downlineAssocIds),
                'booking_count'       => $bookingCount,
                'period_start'        => $periodStart,
                'period_end'          => $periodEnd,
            ];
            $result['total'] = $overrideAmount;
        } catch (Exception $e) {
            error_log("[InfinityOverrideService] calculateLeaderOverride error for user {$leaderUserId}: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Persist infinity override entries.
     */
    public function persistOverrides(array $entries): array
    {
        $result = ['created_ids' => [], 'total' => 0.0];
        if (!$this->db || empty($entries)) return $result;

        // Dedup: skip if entries already exist for this period
        $periodStart = $entries[0]['period_start'] ?? null;
        $periodEnd = $entries[0]['period_end'] ?? null;
        if ($periodStart && $periodEnd) {
            $chk = $this->db->prepare("SELECT COUNT(*) FROM mlm_infinity_overrides WHERE period_start = ? AND period_end = ?");
            $chk->execute([$periodStart, $periodEnd]);
            if ((int)$chk->fetchColumn() > 0) {
                error_log("[InfinityOverrideService] Skipped persist — entries already exist for {$periodStart}..{$periodEnd}");
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
                    (?, ?, 'infinity_override', 0, ?, 'pending', ?, ?, 'Monthly infinity override', 0, NOW(), ?)
            ");

            $infStmt = $this->db->prepare("
                INSERT INTO mlm_infinity_overrides
                    (beneficiary_user_id, source_user_id, depth_level, sale_amount,
                     override_pct, commission_amount, period_start, period_end, status, created_at, tenant_id)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)
            ");

            foreach ($entries as $e) {
                $beneficiary = (int)$e['beneficiary_user_id'];
                $amount = (float)$e['amount'];
                $volume = (float)($e['sale_amount'] ?? 0);
                $pct = (float)$e['pct'];
                $depth = (int)($e['depth_count'] ?? 0);
                $periodStart = $e['period_start'] ?? date('Y-m-01');
                $periodEnd = $e['period_end'] ?? date('Y-m-t');

                // Ledger entry — source_user_id = beneficiary (aggregate monthly bonus)
                $ledgerStmt->execute([$beneficiary, $beneficiary, $amount, $volume, $pct, $this->getTenantId()]);
                $result['created_ids'][] = (int)$this->db->lastInsertId();

                $infStmt->execute([$beneficiary, 0, $depth, $volume, $pct, $amount, $periodStart, $periodEnd, $this->getTenantId()]);

                $result['total'] += $amount;
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("[InfinityOverrideService] persist error: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Get all qualified VP+ leaders who are active.
     */
    protected function getQualifiedLeaders(): array
    {
        if (!$this->db) return [];
        try {
            $minVolume = (float)$this->getSetting('min_monthly_volume', '10000');
            $placeholders = implode(',', array_fill(0, count(self::QUALIFYING_RANKS), '?'));

            $tid = $this->getTenantId();
            $tenantSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
            $stmt = $this->db->prepare("
                SELECT u.id AS user_id, a.level AS rank
                FROM users u
                INNER JOIN associates a ON a.user_id = u.id
                WHERE a.status = 'active'
                  AND a.level IN ({$placeholders}){$tenantSql}
            ");
            $params = self::QUALIFYING_RANKS;
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * BFS to get ALL downline associate IDs at any depth.
     * NOTE: network tree parent_id stores user_ids, not associate_ids.
     */
    protected function getAllDownline(int $leaderAssocId): array
    {
        if (!$this->db) return [];

        // Resolve leader's user_id for parent_id matching
        $leaderUserId = $this->getUserId($leaderAssocId);
        if (!$leaderUserId) return [];

        $visitedAssocIds = [];
        $currentParentUserIds = [$leaderUserId];

        while (!empty($currentParentUserIds)) {
            $placeholders = implode(',', array_fill(0, count($currentParentUserIds), '?'));
            $stmt = $this->db->prepare("
                SELECT nt.associate_id, a.user_id
                FROM mlm_network_tree nt
                INNER JOIN associates a ON a.id = nt.associate_id
                WHERE nt.parent_id IN ({$placeholders})
                  AND a.status = 'active'
            ");
            $stmt->execute($currentParentUserIds);
            $children = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $currentParentUserIds = [];
            foreach ($children as $child) {
                $childAssocId = (int)$child['associate_id'];
                if (!in_array($childAssocId, $visitedAssocIds, true)) {
                    $visitedAssocIds[] = $childAssocId;
                    $currentParentUserIds[] = (int)$child['user_id'];
                }
            }
        }
        return $visitedAssocIds;
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
