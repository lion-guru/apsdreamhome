<?php

namespace App\Services\MLM;

use App\Core\Database\Database;
use App\Services\WalletService;
use App\Traits\ServiceTenantTrait;
use PDO;

/**
 * Career Reward Service
 *
 * Grounded in APS Dream Home Official Rank Rewards Matrix:
 * 1. Associate (₹0 volume): Mobile Phone (₹15,000)
 * 2. Sr. Associate (₹10L volume + 5 Direct Associates): Tablet (₹30,000)
 * 3. BDM (₹35L volume + 3 Sr. Associates): Laptop (₹50,000)
 * 4. Sr. BDM (₹70L volume + 3 BDMs): Domestic / Foreign Tour Package (₹100,000)
 * 5. Vice President (₹1.50 Cr volume + 3 Sr. BDMs): Pulsar Bike (₹175,000)
 * 6. President (₹3.00 Cr volume + 3 VPs): Royal Enfield Bullet Bike (₹275,000)
 * 7. Site Manager (₹5.00 Cr volume + 3 Presidents): Luxury Car (₹800,000)
 *
 * Full Lifecycle:
 * Eligible -> Claimed -> Approved -> Dispatched (Courier / Serial) -> Delivered (Office Ceremony Handover)
 * Optional: Cash Equivalent Wallet Disbursement
 */
class CareerRewardService
{
    use ServiceTenantTrait;

    private ?PDO $db;

    public const CAREER_REWARDS = [
        'associate' => [
            'rank_slug'     => 'associate',
            'rank_name'     => 'Ass.',
            'rank_title'    => 'Associate',
            'reward_name'   => 'Mobile Phone',
            'reward_value'  => 15000.00,
            'volume_target' => 0.00,
            'legs_required' => 0,
            'required_rank' => null,
            'icon'          => 'fas fa-mobile-alt',
            'color'         => '#64748b',
            'description'   => 'Smart 5G Mobile Phone upon starting your career with APS Dream Home.',
        ],
        'sr_associate' => [
            'rank_slug'     => 'sr_associate',
            'rank_name'     => 'Sr. Ass.',
            'rank_title'    => 'Senior Associate',
            'reward_name'   => 'Tablet',
            'reward_value'  => 30000.00,
            'volume_target' => 1000000.00,
            'legs_required' => 5,
            'required_rank' => 'Ass.',
            'icon'          => 'fas fa-tablet-alt',
            'color'         => '#0284c7',
            'description'   => 'High-Performance Presentation Tablet for digital township customer presentations.',
        ],
        'bdm' => [
            'rank_slug'     => 'bdm',
            'rank_name'     => 'BDM',
            'rank_title'    => 'Business Development Manager',
            'reward_name'   => 'Laptop',
            'reward_value'  => 50000.00,
            'volume_target' => 3500000.00,
            'legs_required' => 3,
            'required_rank' => 'Sr. Ass.',
            'icon'          => 'fas fa-laptop',
            'color'         => '#2563eb',
            'description'   => 'Executive Fast-Processing Core i5 Laptop for colony CRM, CAD layout and team management.',
        ],
        'sr_bdm' => [
            'rank_slug'     => 'sr_bdm',
            'rank_name'     => 'Sr. BDM',
            'rank_title'    => 'Senior BDM',
            'reward_name'   => 'Domestic / Foreign Tour',
            'reward_value'  => 100000.00,
            'volume_target' => 7000000.00,
            'legs_required' => 3,
            'required_rank' => 'BDM',
            'icon'          => 'fas fa-plane-departure',
            'color'         => '#059669',
            'description'   => 'All-Expense Paid Luxury Holiday Tour Package with 5-Star resort stay.',
        ],
        'vice_president' => [
            'rank_slug'     => 'vice_president',
            'rank_name'     => 'V.P.',
            'rank_title'    => 'Vice President',
            'reward_name'   => 'Pulsar Bike',
            'reward_value'  => 175000.00,
            'volume_target' => 15000000.00,
            'legs_required' => 3,
            'required_rank' => 'Sr. BDM',
            'icon'          => 'fas fa-motorcycle',
            'color'         => '#d97706',
            'description'   => 'Brand-New Bajaj Pulsar Sports Bike for leadership mobility across Purvanchal projects.',
        ],
        'president' => [
            'rank_slug'     => 'president',
            'rank_name'     => 'President',
            'rank_title'    => 'President',
            'reward_name'   => 'Bullet Bike',
            'reward_value'  => 275000.00,
            'volume_target' => 30000000.00,
            'legs_required' => 3,
            'required_rank' => 'V.P.',
            'icon'          => 'fas fa-motorcycle',
            'color'         => '#7c3aed',
            'description'   => 'Iconic Royal Enfield Bullet 350cc Classic Cruiser Motorcycle.',
        ],
        'site_manager' => [
            'rank_slug'     => 'site_manager',
            'rank_name'     => 'Site Manager',
            'rank_title'    => 'Site Manager',
            'reward_name'   => 'Luxury Car',
            'reward_value'  => 800000.00,
            'volume_target' => 50000000.00,
            'legs_required' => 3,
            'required_rank' => 'President',
            'icon'          => 'fas fa-car-side',
            'color'         => '#dc2626',
            'description'   => 'Brand-New Premium Sedan Luxury Car officially handed over at Annual Leadership Gala.',
        ],
    ];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $db = Database::getInstance();
                $this->db = method_exists($db, 'getPdo') ? $db->getPdo() : (method_exists($db, 'getConnection') ? $db->getConnection() : null);
            } catch (\Throwable $e) {
                error_log('[CareerRewardService] DB connection failed: ' . $e->getMessage());
                $this->db = null;
            }
        } else {
            $this->db = $pdo;
        }
    }

    /**
     * Map rank name (e.g. 'Ass.', 'Sr. Ass.') to slug.
     */
    public static function rankNameToSlug(string $rankName): string
    {
        $map = [
            'Ass.'          => 'associate',
            'Associate'     => 'associate',
            'Sr. Ass.'      => 'sr_associate',
            'Sr. Associate' => 'sr_associate',
            'Senior Associate' => 'sr_associate',
            'BDM'           => 'bdm',
            'Sr. BDM'       => 'sr_bdm',
            'V.P.'          => 'vice_president',
            'Vice President'=> 'vice_president',
            'President'     => 'president',
            'Site Manager'  => 'site_manager',
        ];
        return $map[trim($rankName)] ?? strtolower(str_replace([' ', '.'], ['_', ''], trim($rankName)));
    }

    /**
     * Get all direct and indirect downline user IDs.
     *
     * @return int[]
     */
    public function getDownlineUserIds(int $userId): array
    {
        if (!$this->db) return [];
        $downline = [];
        $queue = [$userId];
        while (!empty($queue)) {
            $currentId = array_shift($queue);
            $stmt = $this->db->prepare("SELECT id FROM users WHERE referred_by = ?");
            $stmt->execute([$currentId]);
            $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($children as $cId) {
                $cId = (int)$cId;
                if (!in_array($cId, $downline, true)) {
                    $downline[] = $cId;
                    $queue[] = $cId;
                }
            }
        }
        return $downline;
    }

    /**
     * Get complete reward dashboard data for an associate.
     *
     * @return array<string, mixed>
     */
    public function getAssociateRewardDashboard(int $userId): array
    {
        if (!$this->db) {
            return ['rewards' => [], 'user' => null, 'stats' => []];
        }

        // 1. Fetch user rank & info
        $stmt = $this->db->prepare("SELECT id, name, email, phone, role, mlm_rank, address FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['rewards' => [], 'user' => null, 'stats' => []];
        }

        $currentRankName = !empty($user['mlm_rank']) ? (string)$user['mlm_rank'] : 'Ass.';
        $currentRankSlug = self::rankNameToSlug($currentRankName);

        // 2. Fetch cumulative team volume
        $engine = new MLMCommissionEngine($this->db);
        $teamUserIds = $this->getDownlineUserIds($userId);
        $allUserIds = array_unique(array_merge([$userId], $teamUserIds));

        $inClause = implode(',', array_fill(0, count($allUserIds), '?'));
        $volStmt = $this->db->prepare("
            SELECT COALESCE(SUM(total_plot_value), 0) AS total_vol
            FROM plot_bookings
            WHERE associate_id IN ($inClause)
              AND status NOT IN ('cancelled', 'rejected')
        ");
        $volStmt->execute($allUserIds);
        $totalTeamVolume = (float)$volStmt->fetchColumn();

        // Also check mlm_commission_ledger or cumulative_sales
        $cumStmt = $this->db->prepare("SELECT cumulative_sales FROM users WHERE id = ?");
        $cumStmt->execute([$userId]);
        $cumSales = (float)$cumStmt->fetchColumn();
        $effectiveVolume = max($totalTeamVolume, $cumSales);

        // 3. Ensure eligible rewards exist in database
        $this->syncEligibleRewards($userId, $currentRankSlug, $effectiveVolume, $engine);

        // 4. Fetch existing career reward records for user
        $crStmt = $this->db->prepare("
            SELECT *
            FROM mlm_career_rewards
            WHERE user_id = ?
            ORDER BY id ASC
        ");
        $crStmt->execute([$userId]);
        $existingRows = $crStmt->fetchAll(PDO::FETCH_ASSOC);

        $dbRewardsBySlug = [];
        foreach ($existingRows as $row) {
            $dbRewardsBySlug[$row['rank_slug']] = $row;
        }

        // 5. Build presentation structure for all 7 tiers
        $rewardsList = [];
        $rankOrder = array_keys(self::CAREER_REWARDS);
        $currentRankIndex = array_search($currentRankSlug, $rankOrder, true);
        if ($currentRankIndex === false) {
            $currentRankIndex = 0;
        }

        $stats = [
            'total_achieved' => 0,
            'total_claimed'  => 0,
            'total_delivered'=> 0,
            'next_reward'    => null,
        ];

        foreach (self::CAREER_REWARDS as $slug => $tier) {
            $tierIndex = array_search($slug, $rankOrder, true);
            $dbRow = $dbRewardsBySlug[$slug] ?? null;

            $isRankReached = ($tierIndex <= $currentRankIndex);
            $isVolumeReached = ($effectiveVolume >= $tier['volume_target']);

            // Count direct legs qualifying
            $legCount = 0;
            if (!empty($tier['required_rank'])) {
                $legCount = $engine->countLegsAtOrAboveRank($userId, $tier['required_rank']);
            }
            $isLegsReached = ($tier['legs_required'] === 0 || $legCount >= $tier['legs_required']);

            $isEligible = $isRankReached || ($isVolumeReached && $isLegsReached);

            $status = 'locked';
            if ($dbRow) {
                $status = $dbRow['claim_status'] ?? 'eligible';
            } elseif ($isEligible) {
                $status = 'eligible';
            }

            if ($status !== 'locked') {
                $stats['total_achieved']++;
            }
            if (in_array($status, ['claimed', 'approved', 'dispatched'], true)) {
                $stats['total_claimed']++;
            }
            if (in_array($status, ['delivered', 'converted_to_wallet'], true)) {
                $stats['total_delivered']++;
            }

            if ($status === 'locked' && $stats['next_reward'] === null) {
                $stats['next_reward'] = $tier['reward_name'] . ' (' . $tier['rank_title'] . ')';
            }

            $volProgress = $tier['volume_target'] > 0
                ? min(100.0, round(($effectiveVolume / $tier['volume_target']) * 100, 1))
                : 100.0;
            $legProgress = $tier['legs_required'] > 0
                ? min(100.0, round(($legCount / $tier['legs_required']) * 100, 1))
                : 100.0;

            $rewardsList[] = [
                'slug'              => $slug,
                'rank_name'         => $tier['rank_name'],
                'rank_title'        => $tier['rank_title'],
                'reward_name'       => $tier['reward_name'],
                'reward_value'      => $tier['reward_value'],
                'volume_target'     => $tier['volume_target'],
                'legs_required'     => $tier['legs_required'],
                'required_rank'     => $tier['required_rank'],
                'icon'              => $tier['icon'],
                'color'             => $tier['color'],
                'description'       => $tier['description'],
                'effective_volume'  => $effectiveVolume,
                'leg_count'         => $legCount,
                'vol_progress'      => $volProgress,
                'leg_progress'      => $legProgress,
                'status'            => $status,
                'db_record'         => $dbRow,
                'is_eligible'       => $isEligible,
            ];
        }

        return [
            'user'               => $user,
            'current_rank_name'  => $currentRankName,
            'current_rank_slug'  => $currentRankSlug,
            'effective_volume'   => $effectiveVolume,
            'rewards'            => $rewardsList,
            'stats'              => $stats,
        ];
    }

    /**
     * Auto-seed/synchronize eligible rewards for associate.
     */
    public function syncEligibleRewards(int $userId, string $currentRankSlug, float $effectiveVolume, MLMCommissionEngine $engine): void
    {
        if (!$this->db) return;

        $rankOrder = array_keys(self::CAREER_REWARDS);
        $currentRankIndex = array_search($currentRankSlug, $rankOrder, true);
        if ($currentRankIndex === false) {
            $currentRankIndex = 0;
        }

        foreach (self::CAREER_REWARDS as $slug => $tier) {
            $tierIndex = array_search($slug, $rankOrder, true);
            $isRankReached = ($tierIndex <= $currentRankIndex);
            $isVolumeReached = ($effectiveVolume >= $tier['volume_target']);

            $legCount = 0;
            if (!empty($tier['required_rank'])) {
                $legCount = $engine->countLegsAtOrAboveRank($userId, $tier['required_rank']);
            }
            $isLegsReached = ($tier['legs_required'] === 0 || $legCount >= $tier['legs_required']);

            if ($isRankReached || ($isVolumeReached && $isLegsReached)) {
                // Check if already in mlm_career_rewards
                $check = $this->db->prepare("SELECT id FROM mlm_career_rewards WHERE user_id = ? AND rank_slug = ? LIMIT 1");
                $check->execute([$userId, $slug]);
                $exists = $check->fetchColumn();

                if (!$exists) {
                    $ins = $this->db->prepare("
                        INSERT INTO mlm_career_rewards
                            (tenant_id, user_id, rank_slug, reward_name, reward_value, gbv_at_award, status, claim_status, created_at)
                        VALUES
                            (?, ?, ?, ?, ?, ?, 'pending', 'eligible', NOW())
                    ");
                    $ins->execute([
                        $this->tenantId(),
                        $userId,
                        $slug,
                        $tier['reward_name'],
                        $tier['reward_value'],
                        $effectiveVolume,
                    ]);
                }
            }
        }
    }

    /**
     * Associate submits a claim for an eligible reward.
     *
     * @param array<string, mixed> $claimData
     * @return array<string, mixed>
     */
    public function claimReward(int $userId, int $rewardId, array $claimData): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database not connected'];
        }

        $stmt = $this->db->prepare("SELECT * FROM mlm_career_rewards WHERE id = ? AND user_id = ?");
        $stmt->execute([$rewardId, $userId]);
        $reward = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reward) {
            return ['success' => false, 'message' => 'Reward record not found or access denied'];
        }

        if (!in_array($reward['claim_status'], ['eligible', 'rejected'], true)) {
            return ['success' => false, 'message' => 'This reward has already been claimed or processed (Current Status: ' . $reward['claim_status'] . ')'];
        }

        $deliveryType = trim((string)($claimData['delivery_type'] ?? 'physical_handover'));
        $validDelivery = ['physical_handover', 'courier_dispatch', 'wallet_cash_equivalent', 'tour_voucher'];
        if (!in_array($deliveryType, $validDelivery, true)) {
            $deliveryType = 'physical_handover';
        }

        $shippingAddress = trim((string)($claimData['shipping_address'] ?? ''));
        $contactPhone = trim((string)($claimData['contact_phone'] ?? ''));
        $associateNotes = trim((string)($claimData['notes'] ?? ''));

        $upd = $this->db->prepare("
            UPDATE mlm_career_rewards
            SET claim_status = 'claimed',
                delivery_type = ?,
                shipping_address = ?,
                contact_phone = ?,
                admin_notes = ?,
                claimed_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $upd->execute([
            $deliveryType,
            $shippingAddress ?: null,
            $contactPhone ?: null,
            $associateNotes ?: null,
            $rewardId,
            $userId,
        ]);

        return [
            'success' => true,
            'message' => 'Reward claim submitted successfully! Admin desk will verify your achievement and process fulfillment.',
        ];
    }

    /**
     * Admin: Retrieve all reward claims with user info.
     *
     * @return array<string, mixed>
     */
    public function getAllRewardClaims(?string $statusFilter = null): array
    {
        if (!$this->db) {
            return ['claims' => [], 'stats' => []];
        }

        $where = "WHERE 1=1";
        $params = [];

        if ($statusFilter && $statusFilter !== 'all') {
            $where .= " AND mcr.claim_status = ?";
            $params[] = $statusFilter;
        }

        $sql = "
            SELECT mcr.*,
                   u.name AS associate_name,
                   u.email AS associate_email,
                   u.phone AS associate_phone,
                   u.mlm_rank AS associate_rank,
                   admin_u.name AS approver_name
            FROM mlm_career_rewards mcr
            JOIN users u ON u.id = mcr.user_id
            LEFT JOIN users admin_u ON admin_u.id = mcr.approved_by
            $where
            ORDER BY FIELD(mcr.claim_status, 'claimed', 'approved', 'dispatched', 'eligible', 'delivered', 'converted_to_wallet', 'rejected'),
                     mcr.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats summary
        $statsStmt = $this->db->query("
            SELECT
                COUNT(*) AS total_records,
                SUM(CASE WHEN claim_status = 'eligible' THEN 1 ELSE 0 END) AS count_eligible,
                SUM(CASE WHEN claim_status = 'claimed' THEN 1 ELSE 0 END) AS count_claimed,
                SUM(CASE WHEN claim_status = 'approved' THEN 1 ELSE 0 END) AS count_approved,
                SUM(CASE WHEN claim_status = 'dispatched' THEN 1 ELSE 0 END) AS count_dispatched,
                SUM(CASE WHEN claim_status = 'delivered' THEN 1 ELSE 0 END) AS count_delivered,
                SUM(CASE WHEN claim_status = 'converted_to_wallet' THEN 1 ELSE 0 END) AS count_wallet,
                COALESCE(SUM(reward_value), 0) AS total_reward_value
            FROM mlm_career_rewards
        ");
        $stats = $statsStmt ? ($statsStmt->fetch(PDO::FETCH_ASSOC) ?: []) : [];

        return [
            'claims' => $claims,
            'stats'  => $stats,
        ];
    }

    /**
     * Admin: Approve reward claim.
     *
     * @return array<string, mixed>
     */
    public function approveReward(int $rewardId, int $adminId, ?string $adminNotes = null): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database not connected'];
        }

        $stmt = $this->db->prepare("SELECT * FROM mlm_career_rewards WHERE id = ?");
        $stmt->execute([$rewardId]);
        $reward = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reward) {
            return ['success' => false, 'message' => 'Reward record not found'];
        }

        $upd = $this->db->prepare("
            UPDATE mlm_career_rewards
            SET claim_status = 'approved',
                approved_by = ?,
                approved_at = NOW(),
                admin_notes = COALESCE(?, admin_notes)
            WHERE id = ?
        ");
        $upd->execute([$adminId, $adminNotes, $rewardId]);

        return [
            'success' => true,
            'message' => 'Reward claim approved. It is now ready for physical handover, courier dispatch, or cash conversion.',
        ];
    }

    /**
     * Admin: Dispatch reward via Courier / Logistics with tracking details.
     *
     * @param array<string, mixed> $dispatchData
     * @return array<string, mixed>
     */
    public function dispatchReward(int $rewardId, int $adminId, array $dispatchData): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database not connected'];
        }

        $trackingNumber = trim((string)($dispatchData['tracking_number'] ?? ''));
        $courierPartner = trim((string)($dispatchData['courier_partner'] ?? ''));
        $serialNumber   = trim((string)($dispatchData['serial_number'] ?? ''));
        $notes          = trim((string)($dispatchData['admin_notes'] ?? ''));

        $upd = $this->db->prepare("
            UPDATE mlm_career_rewards
            SET claim_status = 'dispatched',
                tracking_number = ?,
                courier_partner = ?,
                serial_number = ?,
                admin_notes = ?,
                approved_by = COALESCE(approved_by, ?),
                dispatched_at = NOW()
            WHERE id = ?
        ");
        $upd->execute([
            $trackingNumber ?: null,
            $courierPartner ?: null,
            $serialNumber ?: null,
            $notes ?: null,
            $adminId,
            $rewardId,
        ]);

        return [
            'success' => true,
            'message' => 'Reward marked as Dispatched with tracking and serial number recorded.',
        ];
    }

    /**
     * Admin: Mark reward officially Delivered / Handed Over.
     *
     * @param array<string, mixed> $deliveryData
     * @return array<string, mixed>
     */
    public function markDelivered(int $rewardId, int $adminId, array $deliveryData): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database not connected'];
        }

        $handoverEvent = trim((string)($deliveryData['handover_event'] ?? 'Head Office Handover'));
        $handoverPhoto = trim((string)($deliveryData['handover_photo'] ?? ''));
        $notes         = trim((string)($deliveryData['admin_notes'] ?? ''));

        $upd = $this->db->prepare("
            UPDATE mlm_career_rewards
            SET claim_status = 'delivered',
                status = 'awarded',
                handover_event = ?,
                handover_photo = ?,
                admin_notes = ?,
                approved_by = COALESCE(approved_by, ?),
                delivered_at = NOW(),
                awarded_at = COALESCE(awarded_at, NOW())
            WHERE id = ?
        ");
        $upd->execute([
            $handoverEvent,
            $handoverPhoto ?: null,
            $notes ?: null,
            $adminId,
            $rewardId,
        ]);

        return [
            'success' => true,
            'message' => 'Reward marked as officially Handed Over / Delivered!',
        ];
    }

    /**
     * Admin: Convert reward to Cash Equivalent and disburse directly to Associate Wallet.
     *
     * @return array<string, mixed>
     */
    public function convertToCashWallet(int $rewardId, int $adminId, ?float $customAmount = null, ?string $notes = null): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database not connected'];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM mlm_career_rewards WHERE id = ?");
            $stmt->execute([$rewardId]);
            $reward = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reward) {
                return ['success' => false, 'message' => 'Reward not found'];
            }

            if ($reward['claim_status'] === 'converted_to_wallet') {
                return ['success' => false, 'message' => 'This reward has already been converted to cash'];
            }

            $userId = (int)$reward['user_id'];
            $amount = ($customAmount !== null && $customAmount > 0) ? $customAmount : (float)$reward['reward_value'];

            if ($amount <= 0) {
                return ['success' => false, 'message' => 'Invalid cash conversion amount: ' . $amount];
            }

            // Disburse to wallet (WalletService manages its own atomic transaction)
            $wallet = new WalletService();
            $memo = $notes ?: ("Cash equivalent payout for Career Reward: " . $reward['reward_name'] . " (" . $reward['rank_slug'] . ")");
            $credited = $wallet->credit($userId, $amount, 'bonus', $memo, $rewardId, 'career_reward');

            if (!$credited) {
                return ['success' => false, 'message' => 'Failed to credit associate wallet'];
            }

            $upd = $this->db->prepare("
                UPDATE mlm_career_rewards
                SET claim_status = 'converted_to_wallet',
                    status = 'awarded',
                    delivery_type = 'wallet_cash_equivalent',
                    cash_converted_amount = ?,
                    approved_by = ?,
                    approved_at = NOW(),
                    delivered_at = NOW(),
                    admin_notes = ?,
                    awarded_at = COALESCE(awarded_at, NOW())
                WHERE id = ?
            ");
            $upd->execute([
                $amount,
                $adminId,
                $memo,
                $rewardId,
            ]);

            return [
                'success' => true,
                'message' => "Successfully disbursed ₹" . number_format($amount, 2) . " cash equivalent into associate's wallet!",
                'amount'  => $amount,
            ];
        } catch (\Throwable $e) {
            error_log('[CareerRewardService] convertToCashWallet error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error processing cash conversion: ' . $e->getMessage()];
        }
    }

    /**
     * Admin: Reject reward claim.
     *
     * @return array<string, mixed>
     */
    public function rejectReward(int $rewardId, string $reason, int $adminId): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database not connected'];
        }

        $upd = $this->db->prepare("
            UPDATE mlm_career_rewards
            SET claim_status = 'rejected',
                admin_notes = ?,
                approved_by = ?
            WHERE id = ?
        ");
        $upd->execute([$reason, $adminId, $rewardId]);

        return [
            'success' => true,
            'message' => 'Reward claim rejected.',
        ];
    }
}
