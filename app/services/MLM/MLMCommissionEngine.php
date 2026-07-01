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
 *   mlm_commission_ledger, booking_payment_schedules, user_wallets
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

    /** Rank list in promotion order (7 tiers matching mlm_rank_benefits DB ENUM). */
    public const RANK_ORDER = ['associate', 'senior_associate', 'bdm', 'sr_bdm', 'vice_president', 'president', 'site_manager'];

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
        // Rates: 5%-20% differential model
        // direct_sale_pct = rank's own commission rate
        // l1_pct/l2_pct/l3_pct = NOT USED for differential calculation
        //   (differentials are computed on-the-fly by walking the upline chain)
        //   These columns exist for backward compatibility only.
        return [
            ['rank_name' => 'associate',        'rank_order' => 1, 'min_leg_count' => 0, 'min_qualifying_volume' => 0,       'direct_sale_pct' => 5.0, 'l1_pct' => 0.0, 'l2_pct' => 0.0, 'l3_pct' => 0.0, 'color_code' => '#94a3b8', 'badge_icon' => 'fa-user',        'perks' => []],
            ['rank_name' => 'senior_associate',  'rank_order' => 2, 'min_leg_count' => 1, 'min_qualifying_volume' => 25000,   'direct_sale_pct' => 7.0, 'l1_pct' => 0.0, 'l2_pct' => 0.0, 'l3_pct' => 0.0, 'color_code' => '#94a3b8', 'badge_icon' => 'fa-user-plus',  'perks' => []],
            ['rank_name' => 'bdm',               'rank_order' => 3, 'min_leg_count' => 2, 'min_qualifying_volume' => 100000,  'direct_sale_pct' => 10.0, 'l1_pct' => 0.0, 'l2_pct' => 0.0, 'l3_pct' => 0.0, 'color_code' => '#a16207', 'badge_icon' => 'fa-briefcase',  'perks' => []],
            ['rank_name' => 'sr_bdm',            'rank_order' => 4, 'min_leg_count' => 3, 'min_qualifying_volume' => 300000,  'direct_sale_pct' => 12.0, 'l1_pct' => 0.0, 'l2_pct' => 0.0, 'l3_pct' => 0.0, 'color_code' => '#ca8a04', 'badge_icon' => 'fa-medal',      'perks' => []],
            ['rank_name' => 'vice_president',    'rank_order' => 5, 'min_leg_count' => 4, 'min_qualifying_volume' => 800000,  'direct_sale_pct' => 15.0, 'l1_pct' => 0.0, 'l2_pct' => 0.0, 'l3_pct' => 0.0, 'color_code' => '#0891b2', 'badge_icon' => 'fa-gem',        'perks' => []],
            ['rank_name' => 'president',         'rank_order' => 6, 'min_leg_count' => 5, 'min_qualifying_volume' => 2000000, 'direct_sale_pct' => 18.0, 'l1_pct' => 0.0, 'l2_pct' => 0.0, 'l3_pct' => 0.0, 'color_code' => '#0f766e', 'badge_icon' => 'fa-trophy',     'perks' => []],
            ['rank_name' => 'site_manager',      'rank_order' => 7, 'min_leg_count' => 6, 'min_qualifying_volume' => 5000000, 'direct_sale_pct' => 20.0, 'l1_pct' => 0.0, 'l2_pct' => 0.0, 'l3_pct' => 0.0, 'color_code' => '#dc2626', 'badge_icon' => 'fa-crown',      'perks' => []],
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

            // ── RANK ADVANCEMENT BONUS ──
            // One-time bonus when rank is promoted
            $this->awardRankBonus($associateId, $safeOldRank, $newRank, (int)$stats['user_id'] ?? 0);

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
        // Rates: 5%-20% differential model
        // direct = rank's own rate, l1/l2/l3 = differentials from previous level
        $defaults = [
            'associate'       => ['direct' => 5.0,  'l1' => 2.0, 'l2' => 3.0, 'l3' => 2.0],
            'senior_associate'=> ['direct' => 7.0,  'l1' => 3.0, 'l2' => 2.0, 'l3' => 3.0],
            'bdm'             => ['direct' => 10.0, 'l1' => 2.0, 'l2' => 3.0, 'l3' => 3.0],
            'sr_bdm'          => ['direct' => 12.0, 'l1' => 3.0, 'l2' => 3.0, 'l3' => 3.0],
            'vice_president'  => ['direct' => 15.0, 'l1' => 3.0, 'l2' => 3.0, 'l3' => 2.0],
            'president'       => ['direct' => 18.0, 'l1' => 2.0, 'l2' => 3.0, 'l3' => 3.0],
            'site_manager'    => ['direct' => 20.0, 'l1' => 0.0, 'l2' => 0.0, 'l3' => 0.0],
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
                       total_plot_value, booking_amount
                FROM plot_bookings
                WHERE id = ?
                LIMIT 1
            ");
            $bStmt->execute([$bookingId]);
            $booking = $bStmt->fetch(PDO::FETCH_ASSOC);
            if (!$booking) {
                return $result;
            }

            // ── FIX: Use ACTUAL payment received, not full plot value ──
            // Sum all paid installments + token to get real money received
            $paidStmt = $this->db->prepare("
                SELECT 
                    COALESCE((SELECT SUM(paid_amount) FROM booking_payment_schedules WHERE booking_id = ? AND status = 'paid'), 0) 
                    + ? AS total_received
            ");
            $paidStmt->execute([$bookingId, (float)$booking['booking_amount']]);
            $saleValue = (float)$paidStmt->fetchColumn();

            if ($saleValue <= 0) {
                return $result;
            }

            // ── IDEMPOTENCY: Skip if commissions already exist for this booking ──
            $existsStmt = $this->db->prepare("
                SELECT COUNT(*) FROM mlm_commission_ledger 
                WHERE booking_id = ? AND commission_type IN ('direct_sale','level_bonus')
            ");
            $existsStmt->execute([$bookingId]);
            if ((int)$existsStmt->fetchColumn() > 0) {
                $result['entries'] = [];
                $result['total'] = 0.0;
                $result['skipped'] = true;
                $result['reason'] = 'commissions_already_exist';
                return $result;
            }

            // ── QUALIFICATION GATE ──
            // If qualification_required setting is enabled, check that the source
            // associate has met minimum monthly qualifying volume before earning.
            $qualRequired = $this->db->query("SELECT setting_value FROM mlm_settings WHERE setting_key = 'qualification_required'")->fetchColumn();
            $minVolume = (float)($this->db->query("SELECT setting_value FROM mlm_settings WHERE setting_key = 'min_qualifying_volume'")->fetchColumn() ?: 50000);
            if ($qualRequired === '1') {
                // Resolve source associate's user_id
                $qUserId = 0;
                if (!empty($booking['associate_id'])) {
                    $qa = $this->db->prepare("SELECT user_id FROM associates WHERE id = ? AND status = 'active' LIMIT 1");
                    $qa->execute([(int)$booking['associate_id']]);
                    $qr = $qa->fetch(PDO::FETCH_ASSOC);
                    if ($qr) $qUserId = (int)$qr['user_id'];
                }
                if ($qUserId > 0) {
                    // Sum this month's qualifying volume from mlm_commission_ledger (direct_sale only)
                    $qStmt = $this->db->prepare("
                        SELECT COALESCE(SUM(sale_amount), 0) FROM mlm_commission_ledger
                        WHERE source_user_id = ? AND commission_type = 'direct_sale'
                          AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                    ");
                    $qStmt->execute([$qUserId]);
                    $monthlyVolume = (float)$qStmt->fetchColumn();
                    if ($monthlyVolume < $minVolume) {
                        $result['entries'] = [];
                        $result['total'] = 0.0;
                        $result['qualification_skipped'] = true;
                        $result['qualification_reason'] = "Monthly volume Rs" . number_format($monthlyVolume, 0) . " < Rs" . number_format($minVolume, 0) . " minimum";
                        return $result;
                    }
                }
            }

            // Resolve the source user_id for upline walk.
            // Priority: sales_manager_id > associate's user_id > customer_id
            $sourceUserId = 0;
            if (!empty($booking['sales_manager_id'])) {
                $sourceUserId = (int)$booking['sales_manager_id'];
            } elseif (!empty($booking['associate_id'])) {
                // plot_bookings.associate_id references associates.id (table PK), not users.id
                $aStmt = $this->db->prepare("SELECT user_id FROM associates WHERE id = ? LIMIT 1");
                $aStmt->execute([$booking['associate_id']]);
                $ar = $aStmt->fetch(PDO::FETCH_ASSOC);
                if ($ar && !empty($ar['user_id'])) {
                    $sourceUserId = (int)$ar['user_id'];
                }
            }
            if ($sourceUserId <= 0) {
                $sourceUserId = (int)$booking['customer_id'];
            }

            // ── DIFFERENTIAL MODEL ──
            // Load rank rates from mlm_rank_benefits (or defaults)
            $rankRates = $this->loadRankRates();

            // Get source user's rank and rate
            $sourceRank = $this->getUserRank($sourceUserId);
            $sourceRate = $rankRates[$sourceRank] ?? $rankRates['associate'];

            // Build upline chain - 7 levels for differential model
            $upline = $this->getUpline($sourceUserId, 7);
            $entries = [];

            // Direct sale: source user gets their full rank rate
            if ($sourceUserId > 0 && $sourceRate > 0) {
                $directAmt = round($saleValue * ($sourceRate / 100.0), 2);
                $entries[] = [
                    'beneficiary_user_id' => $sourceUserId,
                    'source_user_id'      => $sourceUserId,
                    'commission_type'     => 'direct_sale',
                    'level'               => 0,
                    'pct'                 => $sourceRate,
                    'amount'              => $directAmt,
                ];
            }

            // Upline differential overrides
            // Each upline gets: (their_rate − previous_level_rate)
            $prevRate = $sourceRate;
            $sameRankCount = 0;
            foreach ($upline as $lvl => $up) {
                $upUserId = (int)$up['id'];
                $upRank = $this->getUserRank($upUserId);
                $upRate = $rankRates[$upRank] ?? $rankRates['associate'];

                // Same-rank breakaway safeguard
                if ($upRate === $prevRate) {
                    $sameRankCount++;
                    $overridePct = ($sameRankCount === 1) ? 2.0 : (($sameRankCount === 2) ? 1.0 : 0.0);
                    if ($overridePct > 0) {
                        $amt = round($saleValue * ($overridePct / 100.0), 2);
                        $entries[] = [
                            'beneficiary_user_id' => $upUserId,
                            'source_user_id'      => $sourceUserId,
                            'commission_type'     => 'level_bonus',
                            'level'               => $lvl,
                            'pct'                 => $overridePct,
                            'amount'              => $amt,
                        ];
                    }
                    continue;
                }

                // Standard differential: upline_rate − rate of level below
                $differential = $upRate - $prevRate;
                if ($differential > 0) {
                    $amt = round($saleValue * ($differential / 100.0), 2);
                    $entries[] = [
                        'beneficiary_user_id' => $upUserId,
                        'source_user_id'      => $sourceUserId,
                        'commission_type'     => 'level_bonus',
                        'level'               => $lvl,
                        'pct'                 => $differential,
                        'amount'              => $amt,
                    ];
                }
                $prevRate = $upRate;
            }

            // ── 20% GLOBAL CAP ENFORCEMENT ──
            $totalPct = 0.0;
            foreach ($entries as $e) {
                $totalPct += (float)$e['pct'];
            }
            $maxCapPct = 20.0; // Hard cap from mlm_settings.global_cap_pct
            if ($totalPct > $maxCapPct) {
                // Scale down proportionally
                $scale = $maxCapPct / $totalPct;
                foreach ($entries as &$e) {
                    $e['pct'] = round($e['pct'] * $scale, 4);
                    $e['amount'] = round($saleValue * ($e['pct'] / 100.0), 2);
                }
                unset($e);
            }

            // Insert each entry into mlm_commission_ledger
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
     * Load rank rates from mlm_rank_benefits (or defaults).
     * Returns [rank_name => rate] map.
     */
    protected function loadRankRates(): array
    {
        $rates = [
            'associate'        => 5.0,
            'senior_associate' => 7.0,
            'bdm'              => 10.0,
            'sr_bdm'           => 12.0,
            'vice_president'   => 15.0,
            'president'        => 18.0,
            'site_manager'     => 20.0,
        ];
        if (!$this->db) {
            return $rates;
        }
        try {
            $rows = $this->db->query("SELECT rank_name, direct_sale_pct FROM mlm_rank_benefits WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
            if ($rows) {
                foreach ($rows as $r) {
                    $rates[$r['rank_name']] = (float)$r['direct_sale_pct'];
                }
            }
        } catch (\Throwable $e) {
            // fall through to defaults
        }
        return $rates;
    }

    /**
     * Get a user's rank slug from associates table.
     */
    protected function getUserRank(int $userId): string
    {
        if (!$this->db || $userId <= 0) {
            return 'associate';
        }
        try {
            $stmt = $this->db->prepare("SELECT level FROM associates WHERE user_id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['level']) && in_array($row['level'], self::RANK_ORDER, true)) {
                return $row['level'];
            }
        } catch (\Throwable $e) {
            // fall through
        }
        return 'associate';
    }

    /* =============================================================
     *  RANK ADVANCEMENT BONUS
     * ============================================================= */

    /**
     * Award a one-time rank bonus when an associate is promoted.
     * Reads rank_bonus_amounts JSON from mlm_settings, checks for
     * duplicate payment, inserts into mlm_rank_bonuses + mlm_commission_ledger.
     */
    protected function awardRankBonus(int $associateId, string $fromRank, string $toRank, int $userId): void
    {
        if (!$this->db || $userId <= 0) {
            return;
        }
        try {
            $enabled = $this->db->query("SELECT setting_value FROM mlm_settings WHERE setting_key = 'rank_bonus_enabled'")->fetchColumn();
            if ($enabled !== '1') {
                return;
            }

            $json = $this->db->query("SELECT setting_value FROM mlm_settings WHERE setting_key = 'rank_bonus_amounts'")->fetchColumn();
            if (!$json) {
                return;
            }
            $amounts = json_decode($json, true);
            if (!is_array($amounts)) {
                return;
            }

            $bonus = (float)($amounts[$toRank] ?? 0);
            if ($bonus <= 0) {
                return;
            }

            $dup = $this->db->prepare("SELECT COUNT(*) FROM mlm_rank_bonuses WHERE user_id = ? AND to_rank = ?");
            $dup->execute([$userId, $toRank]);
            if ((int)$dup->fetchColumn() > 0) {
                return;
            }

            $ins = $this->db->prepare("
                INSERT INTO mlm_rank_bonuses (user_id, from_rank, to_rank, bonus_amount, status, created_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            $ins->execute([$userId, $fromRank, $toRank, $bonus]);

            $this->db->prepare("
                INSERT INTO mlm_commission_ledger
                    (beneficiary_user_id, source_user_id, commission_type, amount, status, notes, booking_id, created_at)
                VALUES (?, ?, 'rank_bonus', ?, 'pending', ?, NULL, NOW())
            ")->execute([
                $userId,
                $userId,
                $bonus,
                "Rank promotion bonus: $fromRank → $toRank",
            ]);
        } catch (\Throwable $e) {
            error_log("[MLMCommissionEngine] awardRankBonus() error: " . $e->getMessage());
        }
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
                // mlm_commission_ledger is the single source of truth — no legacy table to check
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

            // Commission by type breakdown (for the dashboard)
            $typeRows = $this->db->query("
                SELECT commission_type, COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS total
                FROM mlm_commission_ledger
                GROUP BY commission_type
                ORDER BY total DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            $stats['commission_by_type'] = [];
            foreach ($typeRows as $tr) {
                $stats['commission_by_type'][$tr['commission_type']] = [
                    'count' => (int)$tr['cnt'],
                    'total' => (float)$tr['total'],
                ];
            }

            // This month's commission by type
            $mTypeRows = $this->db->query("
                SELECT commission_type, COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS total
                FROM mlm_commission_ledger
                WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                GROUP BY commission_type
                ORDER BY total DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            $stats['commission_this_month_by_type'] = [];
            foreach ($mTypeRows as $tr) {
                $stats['commission_this_month_by_type'][$tr['commission_type']] = [
                    'count' => (int)$tr['cnt'],
                    'total' => (float)$tr['total'],
                ];
            }

            // New streams this month (generation, infinity, matching)
            $stats['generation_bonus_this_month'] = (float)($this->db->query(
                "SELECT COALESCE(SUM(commission_amount), 0) FROM mlm_generation_commissions WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
            )->fetchColumn() ?: 0);
            $stats['infinity_override_this_month'] = (float)($this->db->query(
                "SELECT COALESCE(SUM(commission_amount), 0) FROM mlm_infinity_overrides WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
            )->fetchColumn() ?: 0);
            $stats['matching_bonus_this_month'] = (float)($this->db->query(
                "SELECT COALESCE(SUM(bonus_amount), 0) FROM mlm_matching_bonuses WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
            )->fetchColumn() ?: 0);

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
