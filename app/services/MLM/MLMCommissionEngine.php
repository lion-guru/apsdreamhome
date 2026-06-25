<?php
/**
 * Module 4: MLM Commission Engine + Rank System
 *
 * MLMCommissionEngine
 *
 * Owns the full MLM commission lifecycle:
 *   - Upline walk via users.referred_by
 *   - Per-booking commission calculation (direct sale + L1/L2/L3 override)
 *   - Rank progression (Associate → Bronze → Silver → Gold → Platinum → Diamond)
 *   - Monthly payout batch creation (TDS 5% on brokerage per sec 194H)
 *   - Clawback when EMI defaults > 30 days
 *   - Cron-driven daily rank auto-promotion
 *
 * Companion tables (created by migrate_module4_mlm_engine.php):
 *   mlm_payout_batches, mlm_payouts, mlm_rank_benefits, mlm_rank_history,
 *   mlm_clawback_log, mlm_cron_log
 *
 * Reads / writes (existing, untouched):
 *   users, associates, mlm_profiles, mlm_network_tree, mlm_commission_ledger,
 *   booking_commissions, booking_payment_schedules, user_wallets
 *
 * Every public method is wrapped in try/catch and returns a sensible default
 * (empty array, null, 0, or false) on failure so callers can rely on shape.
 */

namespace App\Services\MLM;

use PDO;
use Exception;

class MLMCommissionEngine
{
    /** @var PDO|null */
    protected $db;

    /** TDS rate for brokerage / commission income (section 194H). */
    public const TDS_RATE_BROKERAGE = 5.0;

    /** Default minimum days overdue before a clawback is triggered. */
    public const DEFAULT_CLAWBACK_DAYS = 30;

    /** Rank list in promotion order. */
    public const RANK_ORDER = ['associate', 'bronze', 'silver', 'gold', 'platinum', 'diamond'];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                error_log('[MLMCommissionEngine] Database init failed: ' . $e->getMessage());
                $pdo = null;
            }
        }
        if (!$pdo instanceof PDO) {
            $pdo = null;
        }
        $this->db = $pdo;
    }

    /* =============================================================
     *  UPLINE WALK
     * ============================================================= */

    /**
     * Walk users.referred_by chain. Returns associative array [1=>user,2=>user,3=>user]
     * with missing levels omitted. Each user row has at minimum: id, name, role.
     */
    public function getUpline(int $userId, int $maxLevels = 3): array
    {
        if (!$this->db || $userId <= 0) {
            return [];
        }
        $upline = [];
        $current = $userId;
        $stmt = $this->db->prepare("SELECT id, name, role, referred_by FROM users WHERE id = ? LIMIT 1");
        for ($level = 1; $level <= $maxLevels; $level++) {
            if (!$stmt->execute([$current])) {
                break;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || empty($row['referred_by'])) {
                break;
            }
            $parentId = (int)$row['referred_by'];
            $parentStmt = $this->db->prepare("SELECT id, name, role, referred_by FROM users WHERE id = ? LIMIT 1");
            $parentStmt->execute([$parentId]);
            $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);
            if (!$parent) {
                break;
            }
            $upline[$level] = $parent;
            $current = $parentId;
        }
        return $upline;
    }

    /* =============================================================
     *  RANK BENEFITS
     * ============================================================= */

    public function getRankBenefits(): array
    {
        if (!$this->db) {
            return $this->defaultRankBenefits();
        }
        try {
            $rows = $this->db->query("SELECT * FROM mlm_rank_benefits WHERE is_active = 1 ORDER BY rank_order ASC")->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) {
                return $this->defaultRankBenefits();
            }
            return array_map(function ($r) {
                if (isset($r['perks']) && is_string($r['perks'])) {
                    $decoded = json_decode($r['perks'], true);
                    $r['perks'] = is_array($decoded) ? $decoded : [];
                }
                return $r;
            }, $rows);
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return $this->defaultRankBenefits();
        }
    }

    protected function defaultRankBenefits(): array
    {
        return [
            ['rank_name' => 'associate', 'rank_order' => 1, 'min_leg_count' => 0, 'min_qualifying_volume' => 0,       'direct_sale_pct' => 1.0, 'l1_pct' => 2.0, 'l2_pct' => 1.0, 'l3_pct' => 0.5, 'color_code' => '#94a3b8', 'badge_icon' => 'fa-user',     'perks' => []],
            ['rank_name' => 'bronze',    'rank_order' => 2, 'min_leg_count' => 2, 'min_qualifying_volume' => 50000,   'direct_sale_pct' => 2.0, 'l1_pct' => 3.0, 'l2_pct' => 1.5, 'l3_pct' => 0.5, 'color_code' => '#a16207', 'badge_icon' => 'fa-medal',    'perks' => []],
            ['rank_name' => 'silver',    'rank_order' => 3, 'min_leg_count' => 3, 'min_qualifying_volume' => 200000,  'direct_sale_pct' => 2.5, 'l1_pct' => 3.0, 'l2_pct' => 1.5, 'l3_pct' => 1.0, 'color_code' => '#94a3b8', 'badge_icon' => 'fa-award',    'perks' => []],
            ['rank_name' => 'gold',      'rank_order' => 4, 'min_leg_count' => 4, 'min_qualifying_volume' => 500000,  'direct_sale_pct' => 3.0, 'l1_pct' => 3.5, 'l2_pct' => 2.0, 'l3_pct' => 1.0, 'color_code' => '#ca8a04', 'badge_icon' => 'fa-trophy',   'perks' => []],
            ['rank_name' => 'platinum',  'rank_order' => 5, 'min_leg_count' => 5, 'min_qualifying_volume' => 1000000, 'direct_sale_pct' => 3.5, 'l1_pct' => 4.0, 'l2_pct' => 2.5, 'l3_pct' => 1.5, 'color_code' => '#0891b2', 'badge_icon' => 'fa-gem',      'perks' => []],
            ['rank_name' => 'diamond',   'rank_order' => 6, 'min_leg_count' => 6, 'min_qualifying_volume' => 2500000, 'direct_sale_pct' => 4.0, 'l1_pct' => 5.0, 'l2_pct' => 3.0, 'l3_pct' => 2.0, 'color_code' => '#7c3aed', 'badge_icon' => 'fa-crown',    'perks' => []],
        ];
    }

    protected function getRankBenefitByName(string $rankName): ?array
    {
        foreach ($this->getRankBenefits() as $r) {
            if ($r['rank_name'] === $rankName) {
                return $r;
            }
        }
        return null;
    }

    /* =============================================================
     *  ASSOCIATE STATS (volume + leg count)
     * ============================================================= */

    /**
     * Get an associate's leg count + lifetime sales + current rank.
     */
    public function getAssociateStats(int $associateId): array
    {
        $empty = [
            'associate_id'   => $associateId,
            'user_id'        => null,
            'leg_count'      => 0,
            'lifetime_sales' => 0.0,
            'current_rank'   => 'associate',
        ];
        if (!$this->db) {
            return $empty;
        }
        try {
            $stmt = $this->db->prepare("SELECT id, user_id, level, status FROM associates WHERE id = ? LIMIT 1");
            $stmt->execute([$associateId]);
            $a = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$a) {
                return $empty;
            }
            $userId = (int)$a['user_id'];

            // Lifetime sales from mlm_profiles (preferred) or zero
            $sales = 0.0;
            if ($userId > 0) {
                $pStmt = $this->db->prepare("SELECT lifetime_sales FROM mlm_profiles WHERE user_id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $p = $pStmt->fetch(PDO::FETCH_ASSOC);
                if ($p && isset($p['lifetime_sales'])) {
                    $sales = (float)$p['lifetime_sales'];
                }
            }

            // Leg count = distinct direct sponsor children in mlm_network_tree
            $legs = 0;
            try {
                $lStmt = $this->db->prepare("SELECT COUNT(DISTINCT associate_id) FROM mlm_network_tree WHERE parent_id = ? OR sponsor_id = ?");
                $lStmt->execute([$userId, $userId]);
                $legs = (int)$lStmt->fetchColumn();
            } catch (Exception $e) {
                error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

                $legs = 0;
            }
            if ($legs === 0 && $userId > 0) {
                // Fallback: count direct referrals via users.referred_by
                try {
                    $rStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?");
                    $rStmt->execute([$userId]);
                    $legs = (int)$rStmt->fetchColumn();
                } catch (Exception $e) {
                    error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

                    $legs = 0;
                }
            }

            $currentRank = $a['level'] ?? 'associate';
            // Normalise to our 6-tier scheme; map legacy ranks to 'associate'
            $known = self::RANK_ORDER;
            if (!in_array($currentRank, $known, true)) {
                $currentRank = 'associate';
            }

            return [
                'associate_id'   => $associateId,
                'user_id'        => $userId,
                'leg_count'      => $legs,
                'lifetime_sales' => $sales,
                'current_rank'   => $currentRank,
                'status'         => $a['status'] ?? 'active',
            ];
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return $empty;
        }
    }

    /* =============================================================
     *  RANK EVALUATION + PROMOTION
     * ============================================================= */

    public function getAssociateRank(int $associateId): array
    {
        $stats = $this->getAssociateStats($associateId);
        $current = $stats['current_rank'];
        $currentIdx = array_search($current, self::RANK_ORDER, true);
        if ($currentIdx === false) {
            $currentIdx = 0;
        }
        $nextIdx = min($currentIdx + 1, count(self::RANK_ORDER) - 1);
        $nextRank = self::RANK_ORDER[$nextIdx];
        $nextDef = $this->getRankBenefitByName($nextRank) ?? [];
        $nextLegsReq = (int)($nextDef['min_leg_count'] ?? 0);
        $nextVolReq  = (float)($nextDef['min_qualifying_volume'] ?? 0);

        $legs = (int)$stats['leg_count'];
        $vol  = (float)$stats['lifetime_sales'];
        $progressPct = 0.0;
        if ($nextIdx === $currentIdx) {
            $progressPct = 100.0; // top rank
        } else {
            $legPct = $nextLegsReq > 0 ? min(100.0, ($legs / $nextLegsReq) * 100.0) : 100.0;
            $volPct  = $nextVolReq  > 0 ? min(100.0, ($vol  / $nextVolReq)  * 100.0) : 100.0;
            $progressPct = round(($legPct + $volPct) / 2, 2);
        }

        return [
            'current_rank'    => $current,
            'next_rank'       => $nextIdx === $currentIdx ? null : $nextRank,
            'next_legs_req'   => $nextLegsReq,
            'next_vol_req'    => $nextVolReq,
            'leg_count'       => $legs,
            'lifetime_sales'  => $vol,
            'progress_pct'    => $progressPct,
            'current_benefit' => $this->getRankBenefitByName($current) ?? [],
            'next_benefit'    => $nextDef,
        ];
    }

    /**
     * Returns the highest rank this associate qualifies for, or NULL if no change.
     */
    public function evaluateRankPromotion(int $associateId): ?string
    {
        $stats = $this->getAssociateStats($associateId);
        if (($stats['status'] ?? 'active') !== 'active') {
            return null;
        }
        $legs = (int)$stats['leg_count'];
        $vol  = (float)$stats['lifetime_sales'];
        $currentIdx = array_search($stats['current_rank'], self::RANK_ORDER, true);
        if ($currentIdx === false) {
            $currentIdx = 0;
        }
        $bestIdx = $currentIdx;
        foreach ($this->getRankBenefits() as $rb) {
            $idx = array_search($rb['rank_name'], self::RANK_ORDER, true);
            if ($idx === false || $idx <= $currentIdx) {
                continue;
            }
            if ($legs >= (int)$rb['min_leg_count'] && $vol >= (float)$rb['min_qualifying_volume']) {
                if ($idx > $bestIdx) {
                    $bestIdx = $idx;
                }
            }
        }
        if ($bestIdx === $currentIdx) {
            return null;
        }
        return self::RANK_ORDER[$bestIdx];
    }

    public function applyRankPromotion(int $associateId, ?int $promotedBy = null): bool
    {
        $newRank = $this->evaluateRankPromotion($associateId);
        if ($newRank === null) {
            return false;
        }
        $stats = $this->getAssociateStats($associateId);
        $oldRank = $stats['current_rank'];
        if (!$this->db) {
            return false;
        }
        try {
            $this->db->beginTransaction();

            // Update associates.level (preserves legacy values that aren't in our 6-tier set;
            // we only update if the value already conforms to one of the 6 ranks OR is null/legacy)
            $safeOldRank = in_array($oldRank, self::RANK_ORDER, true) ? $oldRank : 'associate';
            $upd = $this->db->prepare("UPDATE associates SET level = ? WHERE id = ?");
            $upd->execute([$newRank, $associateId]);

            // Sync mlm_profiles.current_level (GamificationService reads this column)
            $syncStmt = $this->db->prepare("
                UPDATE mlm_profiles SET current_level = ?, updated_at = NOW() WHERE user_id = (
                    SELECT user_id FROM associates WHERE id = ?
                )
            ");
            $syncStmt->execute([$newRank, $associateId]);

            $isManual = $promotedBy !== null ? 1 : 0;
            $ins = $this->db->prepare("
                INSERT INTO mlm_rank_history
                    (associate_id, from_rank, to_rank, qualifying_volume_at_promotion, leg_count_at_promotion, promoted_by, is_manual, reason, promoted_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $ins->execute([
                $associateId,
                $safeOldRank,
                $newRank,
                (float)$stats['lifetime_sales'],
                (int)$stats['leg_count'],
                $promotedBy,
                $isManual,
                $isManual ? 'Manual promotion by admin' : 'Auto promotion by cron',
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            try { $this->db->rollBack(); } catch (Exception $e2) {
                error_log("[MLMCommissionEngine] applyRankPromotion() rollback exception: " . $e2->getMessage());
            }
            return false;
        }
    }

    public function runRankPromotions(): array
    {
        $promoted = 0;
        $unchanged = 0;
        $errors = [];
        if (!$this->db) {
            return ['promoted' => 0, 'unchanged' => 0, 'errors' => ['DB unavailable']];
        }
        try {
            $rows = $this->db->query("SELECT id FROM associates WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $aid = (int)$r['id'];
                try {
                    $ok = $this->applyRankPromotion($aid, null);
                    if ($ok) {
                        $promoted++;
                    } else {
                        $unchanged++;
                    }
                } catch (Exception $e) {
                    error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

                    $errors[] = "Assoc {$aid}: " . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            $errors[] = $e->getMessage();
        }
        return ['promoted' => $promoted, 'unchanged' => $unchanged, 'errors' => $errors];
    }

    public function getRankHistory(int $associateId): array
    {
        if (!$this->db) {
            return [];
        }
        try {
            $stmt = $this->db->prepare("SELECT * FROM mlm_rank_history WHERE associate_id = ? ORDER BY promoted_at DESC LIMIT 50");
            $stmt->execute([$associateId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return [];
        }
    }

    /* =============================================================
     *  CANONICAL RATE HELPER
     * ============================================================= */

    /**
     * Returns the canonical commission rates for a given rank.
     * Uses mlm_rank_benefits DB table first, falls back to hardcoded defaults.
     *
     * @return array{direct: float, l1: float, l2: float, l3: float}
     */
    public static function getCanonicalRates(string $rank): array
    {
        $defaults = [
            'associate' => ['direct' => 1.0, 'l1' => 2.0, 'l2' => 1.0, 'l3' => 0.5],
            'bronze'    => ['direct' => 2.0, 'l1' => 3.0, 'l2' => 1.5, 'l3' => 0.5],
            'silver'    => ['direct' => 2.5, 'l1' => 3.0, 'l2' => 1.5, 'l3' => 1.0],
            'gold'      => ['direct' => 3.0, 'l1' => 3.5, 'l2' => 2.0, 'l3' => 1.0],
            'platinum'  => ['direct' => 3.5, 'l1' => 4.0, 'l2' => 2.5, 'l3' => 1.5],
            'diamond'   => ['direct' => 4.0, 'l1' => 5.0, 'l2' => 3.0, 'l3' => 2.0],
        ];

        $fallback = $defaults[$rank] ?? $defaults['associate'];

        try {
            $pdo = \App\Core\Database\Database::getInstance();
            if (method_exists($pdo, 'getPdo')) {
                $pdo = $pdo->getPdo();
            }
            if (!$pdo instanceof PDO) {
                return $fallback;
            }
            $stmt = $pdo->prepare(
                "SELECT direct_sale_pct, l1_pct, l2_pct, l3_pct
                 FROM mlm_rank_benefits
                 WHERE rank_name = ? AND is_active = 1
                 LIMIT 1"
            );
            $stmt->execute([$rank]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'direct' => (float)$row['direct_sale_pct'],
                    'l1'     => (float)$row['l1_pct'],
                    'l2'     => (float)$row['l2_pct'],
                    'l3'     => (float)$row['l3_pct'],
                ];
            }
        } catch (\Throwable $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            // fall through to defaults
        }

        return $fallback;
    }

    /**
     * Resolve canonical rates for a given user_id (looks up their associate rank).
     * Falls back to 'associate' rank if user is not found or has no rank.
     */
    public static function getRatesForUser(int $userId): array
    {
        $rank = 'associate';
        try {
            $pdo = \App\Core\Database\Database::getInstance();
            if (method_exists($pdo, 'getPdo')) {
                $pdo = $pdo->getPdo();
            }
            if ($pdo instanceof PDO) {
                $stmt = $pdo->prepare(
                    "SELECT level FROM associates WHERE user_id = ? AND status = 'active' LIMIT 1"
                );
                $stmt->execute([$userId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['level'])) {
                    $rank = $row['level'];
                }
            }
        } catch (\Throwable $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            // fall through
        }
        return self::getCanonicalRates($rank);
    }

    /* =============================================================
     *  BOOKING COMMISSION CALCULATION
     * ============================================================= */

    /**
     * Walks the upline from the booking's sales_manager_id (or associate_id → user)
     * and creates one mlm_commission_ledger row per level.
     *
     * Returns associative array with breakdown + created ledger IDs.
     */
    public function calculateBookingCommission(int $bookingId): array
    {
        $result = [
            'booking_id'  => $bookingId,
            'entries'     => [],
            'total'       => 0.0,
            'created_ids' => [],
        ];
        if (!$this->db) {
            return $result;
        }
        try {
            $bStmt = $this->db->prepare("
                SELECT id, customer_id, sales_manager_id, associate_id, channel,
                       COALESCE(agreement_value, total_plot_value, 0) AS sale_value
                FROM plot_bookings
                WHERE id = ?
                LIMIT 1
            ");
            $bStmt->execute([$bookingId]);
            $booking = $bStmt->fetch(PDO::FETCH_ASSOC);
            if (!$booking) {
                return $result;
            }

            $saleValue = (float)$booking['sale_value'];
            if ($saleValue <= 0) {
                return $result;
            }

            // Resolve the source user_id for upline walk.
            // Priority: sales_manager_id > associate's user_id > customer_id
            $sourceUserId = 0;
            if (!empty($booking['sales_manager_id'])) {
                $sourceUserId = (int)$booking['sales_manager_id'];
            } elseif (!empty($booking['associate_id'])) {
                // booking.associate_id stores the user_id, look up associates by user_id
                $aStmt = $this->db->prepare("SELECT user_id FROM associates WHERE user_id = ? LIMIT 1");
                $aStmt->execute([$booking['associate_id']]);
                $ar = $aStmt->fetch(PDO::FETCH_ASSOC);
                if ($ar && !empty($ar['user_id'])) {
                    $sourceUserId = (int)$ar['user_id'];
                }
            }
            if ($sourceUserId <= 0) {
                $sourceUserId = (int)$booking['customer_id'];
            }

            // If source user is itself a referred associate, use the source user's upline
            $upline = $this->getUpline($sourceUserId, 3);

            // Direct sale commission: source user gets a direct commission (1-4% by rank)
            $directBenefit = $this->resolveDirectBenefit($sourceUserId);
            $entries = [];

            // Direct sale entry: sales_manager or booking associate
            if ($sourceUserId > 0) {
                $directAmt = round($saleValue * ((float)$directBenefit['direct_sale_pct'] / 100.0), 2);
                $entries[] = [
                    'beneficiary_user_id' => $sourceUserId,
                    'source_user_id'      => $sourceUserId,
                    'commission_type'     => 'direct_sale',
                    'level'               => 0,
                    'pct'                 => (float)$directBenefit['direct_sale_pct'],
                    'amount'              => $directAmt,
                ];
            }

            // L1/L2/L3 upline
            $levelKeys = [1 => 'l1_pct', 2 => 'l2_pct', 3 => 'l3_pct'];
            foreach ($levelKeys as $lvl => $key) {
                if (!isset($upline[$lvl])) {
                    continue;
                }
                $ben = (int)$upline[$lvl]['id'];
                $benBenefit = $this->resolveDirectBenefit($ben);
                $pct = (float)($benBenefit[$key] ?? 0.0);
                if ($pct <= 0) {
                    continue;
                }
                $amt = round($saleValue * ($pct / 100.0), 2);
                $entries[] = [
                    'beneficiary_user_id' => $ben,
                    'source_user_id'      => $sourceUserId,
                    'commission_type'     => 'level_bonus',
                    'level'               => $lvl,
                    'pct'                 => $pct,
                    'amount'              => $amt,
                ];
            }

            // Insert each entry into mlm_commission_ledger
            // property_id is FK to properties table - use NULL for plot bookings
            $ins = $this->db->prepare("
                INSERT INTO mlm_commission_ledger
                    (beneficiary_user_id, source_user_id, commission_type, level, amount, status, property_id, sale_amount, commission_percentage, notes, booking_id, receipt_id, hold_until, created_at)
                VALUES
                    (?, ?, ?, ?, ?, 'pending', NULL, ?, ?, ?, ?, 0, DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())
            ");
            foreach ($entries as &$e) {
                try {
                    $ins->execute([
                        $e['beneficiary_user_id'],
                        $e['source_user_id'],
                        $e['commission_type'],
                        $e['level'],
                        $e['amount'],
                        $saleValue,
                        $e['pct'],
                        'Module 4 auto-calc from booking #' . $bookingId,
                        $bookingId,
                    ]);
                    $e['id'] = (int)$this->db->lastInsertId();
                    $result['created_ids'][] = $e['id'];
                    $result['total'] += $e['amount'];
                } catch (Exception $ee) {
                    error_log("[__CLASS__] __METHOD__() exception: " . $ee->getMessage());

                    // continue to next entry
                }
            }
            unset($e);
            $result['entries'] = $entries;
            return $result;
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return $result;
        }
    }

    /**
     * Resolve a beneficiary's rank benefit. They might be a customer (default = associate tier)
     * or an associate/agent (use their current rank from associates table).
     */
    protected function resolveDirectBenefit(int $userId): array
    {
        if (!$this->db || $userId <= 0) {
            return $this->getRankBenefitByName('associate') ?? $this->defaultRankBenefits()[0];
        }
        try {
            $stmt = $this->db->prepare("SELECT level FROM associates WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $rank = $row['level'] ?? null;
            if ($rank && in_array($rank, self::RANK_ORDER, true)) {
                $benefit = $this->getRankBenefitByName($rank);
                if ($benefit) {
                    return $benefit;
                }
            }
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            // fall through
        }
        return $this->getRankBenefitByName('associate') ?? $this->defaultRankBenefits()[0];
    }

    /* =============================================================
     *  CLAWBACK
     * ============================================================= */

    public function getDefaultersList(int $minDaysOverdue = self::DEFAULT_CLAWBACK_DAYS): array
    {
        if (!$this->db) {
            return [];
        }
        try {
            $sql = "
                SELECT
                    bps.id AS installment_id,
                    bps.booking_id,
                    bps.due_date,
                    bps.amount,
                    bps.paid_amount,
                    b.customer_id,
                    b.associate_id,
                    b.sales_manager_id,
                    DATEDIFF(CURDATE(), bps.due_date) AS days_overdue
                FROM booking_payment_schedules bps
                INNER JOIN plot_bookings b ON bps.booking_id = b.id
                WHERE bps.status = 'overdue'
                  AND bps.due_date < DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ORDER BY bps.due_date ASC
                LIMIT 200
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$minDaysOverdue]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return [];
        }
    }

    public function processClawbacks(int $minDaysOverdue = self::DEFAULT_CLAWBACK_DAYS): array
    {
        $result = ['processed' => 0, 'amount' => 0.0, 'errors' => []];
        if (!$this->db) {
            return $result;
        }
        try {
            $defaulters = $this->getDefaultersList($minDaysOverdue);
            foreach ($defaulters as $d) {
                $bookingId = (int)$d['booking_id'];
                $installmentId = (int)$d['installment_id'];
                $defaultDate = $d['due_date'];
                $daysOverdue = (int)$d['days_overdue'];
                // For each commission already paid for this booking, create a clawback log
                $paidStmt = $this->db->prepare("
                    SELECT id, beneficiary_user_id, source_user_id, amount
                    FROM mlm_commission_ledger
                    WHERE status = 'paid'
                      AND property_id = ?
                ");
                $paidStmt->execute([$bookingId]);
                $paidRows = $paidStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($paidRows as $pr) {
                    try {
                        $existing = $this->db->prepare("
                            SELECT id FROM mlm_clawback_log
                            WHERE original_ledger_id = ? AND status IN ('pending','debited')
                            LIMIT 1
                        ");
                        $existing->execute([$pr['id']]);
                        if ($existing->fetchColumn()) {
                            continue; // already clawback in flight
                        }
                        $cbAmt = (float)$pr['amount'];
                        if ($cbAmt <= 0) {
                            continue;
                        }
                        $insCb = $this->db->prepare("
                            INSERT INTO mlm_clawback_log
                                (original_ledger_id, beneficiary_user_id, source_user_id,
                                 emi_installment_id, default_date, original_amount, clawback_amount,
                                 reason, status, created_at)
                            VALUES
                                (?, ?, ?, ?, ?, ?, ?, ?, 'debited', NOW())
                        ");
                        $insCb->execute([
                            $pr['id'],
                            $pr['beneficiary_user_id'],
                            $pr['source_user_id'],
                            $installmentId,
                            $defaultDate,
                            $cbAmt,
                            $cbAmt,
                            "EMI default {$daysOverdue}d on booking #{$bookingId}",
                        ]);
                        $cbId = (int)$this->db->lastInsertId();

                        // Debit user_wallets if a wallet row exists
                        try {
                            $wStmt = $this->db->prepare("SELECT id, balance FROM user_wallets WHERE user_id = ? LIMIT 1");
                            $wStmt->execute([$pr['beneficiary_user_id']]);
                            $w = $wStmt->fetch(PDO::FETCH_ASSOC);
                            if ($w) {
                                $this->db->prepare("
                                    UPDATE user_wallets
                                    SET balance = balance - ?,
                                        total_debited = total_debited + ?,
                                        updated_at = NOW()
                                    WHERE id = ?
                                ")->execute([$cbAmt, $cbAmt, $w['id']]);
                            }
                        } catch (Exception $we) {
                            error_log("[__CLASS__] __METHOD__() exception: " . $we->getMessage());

                            // wallet may not exist
                        }

                        $result['processed']++;
                        $result['amount'] += $cbAmt;
                    } catch (Exception $ie) {
                        error_log("[__CLASS__] __METHOD__() exception: " . $ie->getMessage());

                        $result['errors'][] = "Ledger {$pr['id']}: " . $ie->getMessage();
                    }
                }
                // Also check booking_commissions (Module 2 table)
                try {
                    $bcStmt = $this->db->prepare("
                        SELECT id, beneficiary_user_id, source_user_id, amount
                        FROM booking_commissions
                        WHERE status = 'paid'
                          AND booking_id = ?
                    ");
                    $bcStmt->execute([$bookingId]);
                    $bcRows = $bcStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($bcRows as $bc) {
                        try {
                            $existingBc = $this->db->prepare("
                                SELECT id FROM mlm_clawback_log
                                WHERE original_ledger_id = ? AND status IN ('pending','debited')
                                LIMIT 1
                            ");
                            $existingBc->execute([(int)$bc['id']]);
                            if ($existingBc->fetchColumn()) {
                                continue;
                            }
                            $cbAmt = (float)$bc['amount'];
                            if ($cbAmt <= 0) {
                                continue;
                            }
                            $this->db->prepare("
                                INSERT INTO mlm_clawback_log
                                    (original_ledger_id, beneficiary_user_id, source_user_id,
                                     emi_installment_id, default_date, original_amount, clawback_amount,
                                     reason, status, created_at)
                                VALUES
                                    (?, ?, ?, ?, ?, ?, ?, ?, 'debited', NOW())
                            ")->execute([
                                (int)$bc['id'],
                                $bc['beneficiary_user_id'],
                                $bc['source_user_id'],
                                $installmentId,
                                $defaultDate,
                                $cbAmt,
                                $cbAmt,
                                "EMI default {$daysOverdue}d on booking #{$bookingId} (booking_commissions)",
                            ]);
                            try {
                                $wStmt2 = $this->db->prepare("SELECT id, balance FROM user_wallets WHERE user_id = ? LIMIT 1");
                                $wStmt2->execute([$bc['beneficiary_user_id']]);
                                $w2 = $wStmt2->fetch(PDO::FETCH_ASSOC);
                                if ($w2) {
                                    $this->db->prepare("
                                        UPDATE user_wallets SET balance = balance - ?, total_debited = total_debited + ?, updated_at = NOW() WHERE id = ?
                                    ")->execute([$cbAmt, $cbAmt, $w2['id']]);
                                }
                            } catch (Exception $we2) {}
                            $result['processed']++;
                            $result['amount'] += $cbAmt;
                        } catch (Exception $ie2) {
                            $result['errors'][] = "BookingCommission {$bc['id']}: " . $ie2->getMessage();
                        }
                    }
                } catch (Exception $bce) {}
            }
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            $result['errors'][] = $e->getMessage();
        }
        return $result;
    }

    public function getClawbackLog(int $associateId, int $limit = 50): array
    {
        if (!$this->db) {
            return [];
        }
        try {
            // resolve associate's user_id
            $aStmt = $this->db->prepare("SELECT user_id FROM associates WHERE id = ? LIMIT 1");
            $aStmt->execute([$associateId]);
            $ar = $aStmt->fetch(PDO::FETCH_ASSOC);
            $userId = (int)($ar['user_id'] ?? 0);
            if ($userId <= 0) {
                return [];
            }
            $stmt = $this->db->prepare("
                SELECT * FROM mlm_clawback_log
                WHERE beneficiary_user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return [];
        }
    }

    /* =============================================================
     *  PAYOUT BATCHES
     * ============================================================= */

    public function createPayoutBatch(int $year, int $month, int $preparedBy): int
    {
        if (!$this->db) {
            return 0;
        }
        $month = max(1, min(12, $month));
        $year = max(2000, min(2100, $year));
        $periodStart = sprintf('%04d-%02d-01', $year, $month);
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        try {
            // Avoid duplicate batch for the same period
            $dup = $this->db->prepare("SELECT id FROM mlm_payout_batches WHERE period_year = ? AND period_month = ? LIMIT 1");
            $dup->execute([$year, $month]);
            $existingId = (int)$dup->fetchColumn();
            if ($existingId > 0) {
                return $existingId;
            }

            $batchNumber = sprintf('APS-MPB-%04d%02d-%04d', $year, $month, random_int(1000, 9999));

            $this->db->beginTransaction();

            $insB = $this->db->prepare("
                INSERT INTO mlm_payout_batches
                    (batch_number, period_year, period_month, period_start, period_end, status, prepared_by, notes, created_at)
                VALUES
                    (?, ?, ?, ?, ?, 'draft', ?, ?, NOW())
            ");
            $insB->execute([
                $batchNumber, $year, $month, $periodStart, $periodEnd, $preparedBy,
                "Auto-created batch for {$year}-{$month}",
            ]);
            $batchId = (int)$this->db->lastInsertId();

            // Aggregate paid commissions for the period, grouped by beneficiary
            $agg = $this->db->prepare("
                SELECT beneficiary_user_id, COALESCE(SUM(amount), 0) AS total
                FROM mlm_commission_ledger
                WHERE status IN ('paid', 'approved')
                  AND created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)
                GROUP BY beneficiary_user_id
                HAVING total > 0
            ");
            $agg->execute([$periodStart, $periodEnd]);
            $rows = $agg->fetchAll(PDO::FETCH_ASSOC);

            $insP = $this->db->prepare("
                INSERT INTO mlm_payouts
                    (batch_id, associate_id, associate_user_id, gross_amount, tds_amount, other_deductions, net_amount, status, created_at)
                VALUES
                    (?, ?, ?, ?, ?, 0, ?, 'pending', NOW())
            ");

            $resolveAssoc = $this->db->prepare("SELECT id FROM associates WHERE user_id = ? LIMIT 1");

            $count = 0;
            $totalGross = 0.0;
            $totalTds = 0.0;
            $totalNet = 0.0;

            foreach ($rows as $r) {
                $benUserId = (int)$r['beneficiary_user_id'];
                $gross = (float)$r['total'];
                $tds = round($gross * (self::TDS_RATE_BROKERAGE / 100.0), 2);
                $net = $gross - $tds;
                $resolveAssoc->execute([$benUserId]);
                $assocId = (int)$resolveAssoc->fetchColumn();
                $insP->execute([$batchId, $assocId, $benUserId, $gross, $tds, $net]);
                $count++;
                $totalGross += $gross;
                $totalTds += $tds;
                $totalNet += $net;

                // Mark included commissions as paid (link to batch)
                $this->db->prepare("
                    UPDATE mlm_commission_ledger
                    SET status = 'paid', payout_batch_id = ?, updated_at = NOW()
                    WHERE beneficiary_user_id = ?
                      AND status IN ('paid', 'approved')
                      AND created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)
                ")->execute([$batchId, $benUserId, $periodStart, $periodEnd]);
            }

            $this->db->prepare("
                UPDATE mlm_payout_batches
                SET total_associates = ?, total_gross_amount = ?, total_tds_amount = ?, total_net_amount = ?, status = 'pending_approval'
                WHERE id = ?
            ")->execute([$count, $totalGross, $totalTds, $totalNet, $batchId]);

            $this->db->commit();
            return $batchId;
        } catch (Exception $e) {
            try { $this->db->rollBack(); } catch (Exception $e2) {
                error_log("[__CLASS__] __METHOD__() exception: " . $e2->getMessage());
}
            error_log('[MLMCommissionEngine] createPayoutBatch error: ' . $e->getMessage());
            return 0;
        }
    }

    public function approvePayoutBatch(int $batchId, int $approverId): bool
    {
        if (!$this->db) {
            return false;
        }
        try {
            $stmt = $this->db->prepare("
                UPDATE mlm_payout_batches
                SET status = 'approved', approved_by = ?, payment_date = CURDATE()
                WHERE id = ? AND status IN ('draft', 'pending_approval')
            ");
            $stmt->execute([$approverId, $batchId]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return false;
        }
    }

    public function markPayoutPaid(int $payoutId, string $mode, array $txnMeta, int $processedBy): bool
    {
        $allowedModes = ['bank_transfer', 'upi', 'cheque', 'cash', 'wallet'];
        if (!$this->db || !in_array($mode, $allowedModes, true)) {
            return false;
        }
        try {
            $stmt = $this->db->prepare("
                UPDATE mlm_payouts
                SET status = 'paid',
                    payment_mode = ?,
                    bank_account = ?,
                    ifsc = ?,
                    upi_id = ?,
                    transaction_ref = ?,
                    cheque_number = ?,
                    paid_date = CURDATE(),
                    processed_by = ?
                WHERE id = ? AND status IN ('pending', 'processing')
            ");
            $stmt->execute([
                $mode,
                $txnMeta['bank_account'] ?? null,
                $txnMeta['ifsc'] ?? null,
                $txnMeta['upi_id'] ?? null,
                $txnMeta['transaction_ref'] ?? null,
                $txnMeta['cheque_number'] ?? null,
                $processedBy,
                $payoutId,
            ]);
            if ($stmt->rowCount() === 0) {
                return false;
            }
            // Recalculate batch totals based on paid status
            $this->refreshBatchTotals((int)$this->db->query("SELECT batch_id FROM mlm_payouts WHERE id = {$payoutId}")->fetchColumn());
            return true;
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return false;
        }
    }

    protected function refreshBatchTotals(int $batchId): void
    {
        if (!$this->db || $batchId <= 0) {
            return;
        }
        try {
            $row = $this->db->prepare("
                SELECT
                    COUNT(*) AS total_associates,
                    COALESCE(SUM(gross_amount), 0) AS total_gross,
                    COALESCE(SUM(tds_amount), 0) AS total_tds,
                    COALESCE(SUM(net_amount), 0) AS total_net
                FROM mlm_payouts WHERE batch_id = ?
            ");
            $row->execute([$batchId]);
            $r = $row->fetch(PDO::FETCH_ASSOC);
            if ($r) {
                $this->db->prepare("
                    UPDATE mlm_payout_batches
                    SET total_associates = ?, total_gross_amount = ?, total_tds_amount = ?, total_net_amount = ?
                    WHERE id = ?
                ")->execute([
                    (int)$r['total_associates'],
                    (float)$r['total_gross'],
                    (float)$r['total_tds'],
                    (float)$r['total_net'],
                    $batchId,
                ]);
            }
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            // non-fatal
        }
    }

    public function getPayoutBatch(int $batchId): array
    {
        if (!$this->db) {
            return ['batch' => null, 'payouts' => []];
        }
        try {
            $b = $this->db->prepare("SELECT * FROM mlm_payout_batches WHERE id = ? LIMIT 1");
            $b->execute([$batchId]);
            $batch = $b->fetch(PDO::FETCH_ASSOC);
            if (!$batch) {
                return ['batch' => null, 'payouts' => []];
            }
            $p = $this->db->prepare("
                SELECT p.*, u.name AS associate_name, u.email AS associate_email, a.level AS associate_rank
                FROM mlm_payouts p
                LEFT JOIN users u ON u.id = p.associate_user_id
                LEFT JOIN associates a ON a.id = p.associate_id
                WHERE p.batch_id = ?
                ORDER BY p.gross_amount DESC
            ");
            $p->execute([$batchId]);
            $payouts = $p->fetchAll(PDO::FETCH_ASSOC) ?: [];
            return ['batch' => $batch, 'payouts' => $payouts];
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return ['batch' => null, 'payouts' => []];
        }
    }

    public function getAssociatePayouts(int $associateId, int $limit = 12): array
    {
        if (!$this->db) {
            return [];
        }
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, b.batch_number, b.period_year, b.period_month, b.status AS batch_status
                FROM mlm_payouts p
                INNER JOIN mlm_payout_batches b ON b.id = p.batch_id
                WHERE p.associate_id = ?
                ORDER BY b.period_year DESC, b.period_month DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $associateId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return [];
        }
    }

    /* =============================================================
     *  DASHBOARD STATS
     * ============================================================= */

    public function getDashboardStats(): array
    {
        $empty = [
            'active_associates'     => 0,
            'commission_this_month' => 0.0,
            'total_clawback'        => 0.0,
            'pending_payouts'       => 0,
            'pending_payout_amount' => 0.0,
            'rank_distribution'     => [],
            'recent_cron'           => null,
        ];
        if (!$this->db) {
            return $empty;
        }
        try {
            $stats = $empty;

            $row = $this->db->query("SELECT COUNT(*) AS c FROM associates WHERE status = 'active'")->fetch(PDO::FETCH_ASSOC);
            $stats['active_associates'] = (int)($row['c'] ?? 0);

            $row = $this->db->query("SELECT COALESCE(SUM(amount), 0) AS s FROM mlm_commission_ledger WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetch(PDO::FETCH_ASSOC);
            $stats['commission_this_month'] = (float)($row['s'] ?? 0);

            $row = $this->db->query("SELECT COALESCE(SUM(clawback_amount), 0) AS s FROM mlm_clawback_log WHERE status IN ('debited', 'pending')")->fetch(PDO::FETCH_ASSOC);
            $stats['total_clawback'] = (float)($row['s'] ?? 0);

            $row = $this->db->query("SELECT COUNT(*) AS c, COALESCE(SUM(net_amount), 0) AS s FROM mlm_payouts WHERE status IN ('pending', 'processing')")->fetch(PDO::FETCH_ASSOC);
            $stats['pending_payouts'] = (int)($row['c'] ?? 0);
            $stats['pending_payout_amount'] = (float)($row['s'] ?? 0);

            // Rank distribution (from associates table, with sane fallback)
            $stats['rank_distribution'] = $this->getRankDistribution();

            $cronStmt = $this->db->query("SELECT * FROM mlm_cron_log ORDER BY started_at DESC LIMIT 1");
            $stats['recent_cron'] = $cronStmt ? ($cronStmt->fetch(PDO::FETCH_ASSOC) ?: null) : null;

            return $stats;
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return $empty;
        }
    }

    public function getRankDistribution(): array
    {
        $buckets = array_fill_keys(self::RANK_ORDER, 0);
        if (!$this->db) {
            return $buckets;
        }
        try {
            $rows = $this->db->query("SELECT COALESCE(level, 'associate') AS lvl, COUNT(*) AS c FROM associates WHERE status = 'active' GROUP BY lvl")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $lvl = $r['lvl'];
                if (!in_array($lvl, self::RANK_ORDER, true)) {
                    $lvl = 'associate';
                }
                $buckets[$lvl] = (int)$r['c'];
            }
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            // return default buckets
        }
        return $buckets;
    }

    /* =============================================================
     *  CRON LOG
     * ============================================================= */

    public function startCronLog(string $cronName): int
    {
        if (!$this->db) {
            return 0;
        }
        try {
            $this->db->prepare("
                INSERT INTO mlm_cron_log (cron_name, run_date, started_at, status)
                VALUES (?, CURDATE(), NOW(), 'running')
            ")->execute([$cronName]);
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return 0;
        }
    }

    public function finishCronLog(int $logId, string $status, int $itemsProcessed, int $errorsCount, string $errorLog = ''): bool
    {
        if (!$this->db || $logId <= 0) {
            return false;
        }
        try {
            $this->db->prepare("
                UPDATE mlm_cron_log
                SET finished_at = NOW(), status = ?, items_processed = ?, errors_count = ?, error_log = ?
                WHERE id = ?
            ")->execute([$status, $itemsProcessed, $errorsCount, $errorLog, $logId]);
            return true;
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return false;
        }
    }

    public function getRecentCronRuns(int $limit = 30): array
    {
        if (!$this->db) {
            return [];
        }
        try {
            $stmt = $this->db->prepare("SELECT * FROM mlm_cron_log ORDER BY started_at DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("[__CLASS__] __METHOD__() exception: " . $e->getMessage());

            return [];
        }
    }
}
