<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;

/**
 * Festival & Promotional Offer Engine (Session 460)
 *
 * Handles both:
 * 1. Associate Festival Campaigns & Targets:
 *    - Sales volume, booking count, and site visit criteria during festival windows.
 *    - Rewards: Cash reward (auto-credited to wallet), physical gift items (Gold/Silver coin, LED TV, Fridge, Tour, etc.).
 *    - Real-time progress calculation, claim submission, admin review, and fulfillment.
 * 2. Customer Festival Booking Discounts & Promo Codes:
 *    - Percentage discounts, flat amount reductions, per-sqft concessions, festive gift hampers, cashback to wallet.
 *    - Colony scoping, minimum booking value, promo code validation, and plot booking integration.
 */
class FestivalOfferService
{
    use ServiceTenantTrait;

    private ?PDO $db;
    private WalletService $walletService;

    /**
     * @param  PDO|\App\Core\Database\Database|null  $pdo  A raw PDO, the
     *         Database wrapper, or null to resolve one.
     *
     * NOTE (S460): the constructor used to be `__construct(?PDO $pdo = null)`
     * but Admin\AssociateOfferController passes `$this->db` - which is the
     * Database WRAPPER, not a PDO.  The type declaration rejected it:
     * "FestivalOfferService::__construct(): Argument #1 ($pdo) must be of
     * type ?PDO, App\Core\Database\Database given" -> /admin/associate-offers
     * was a hard 500 on every request.
     */
    public function __construct($pdo = null)
    {
        if ($pdo instanceof \PDO) {
            $this->db = $pdo;
        } else {
            try {
                $db = Database::getInstance();
                $this->db = method_exists($db, 'getPdo') ? $db->getPdo() : (method_exists($db, 'getConnection') ? $db->getConnection() : null);
            } catch (\Throwable $e) {
                error_log('[FestivalOfferService] DB connection failed: ' . $e->getMessage());
                $this->db = null;
            }
        }

        // NOTE (S460): WalletService::__construct() takes NO arguments - it
        // resolves the Database itself.  Passing $this->db was an
        // ArgumentCountError at construction time.
        $this->walletService = new WalletService();
    }

    /* =========================================================================
       SECTION 1: ASSOCIATE FESTIVAL CAMPAIGNS & REWARDS
       ========================================================================= */

    /**
     * Get active and upcoming festival campaigns visible to an associate with their real-time progress.
     */
    public function getAssociateFestivalDashboard(int $userId): array
    {
        if (!$this->db) {
            return ['offers' => [], 'active_count' => 0, 'eligible_count' => 0, 'claims' => []];
        }

        $tid = (int)$this->tenantId();
        $stmt = $this->db->prepare("
            SELECT o.*, c.name AS colony_name
            FROM associate_offers o
            LEFT JOIN colonies c ON c.id = o.colony_id
            WHERE o.tenant_id = ?
              AND o.status = 'active'
              AND o.ends_at >= CURDATE()
            ORDER BY o.ends_at ASC, o.id DESC
        ");
        $stmt->execute([$tid]);
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Fetch existing claims by this user
        $claimStmt = $this->db->prepare("
            SELECT *
            FROM associate_offer_claims
            WHERE user_id = ? AND tenant_id = ?
        ");
        $claimStmt->execute([$userId, $tid]);
        $existingClaims = $claimStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $claimsByOfferId = [];
        foreach ($existingClaims as $cl) {
            $claimsByOfferId[(int)$cl['offer_id']] = $cl;
        }

        $enrichedOffers = [];
        $eligibleCount = 0;

        foreach ($offers as $off) {
            $offerId = (int)$off['id'];
            $progress = $this->calculateAssociateOfferProgress($userId, $off);
            $claim = $claimsByOfferId[$offerId] ?? null;

            $isTargetMet = $progress['is_eligible'];
            $status = 'in_progress';

            if ($claim) {
                $status = $claim['claim_status']; // claimed, approved, wallet_credited, delivered, rejected
            } elseif ($isTargetMet) {
                $status = 'eligible';
                $eligibleCount++;
            }

            $off['progress'] = $progress;
            $off['claim'] = $claim;
            $off['user_status'] = $status;
            $enrichedOffers[] = $off;
        }

        // Fetch user's claim history
        $historyStmt = $this->db->prepare("
            SELECT c.*, o.title AS offer_title, o.festival_name, o.reward_item, o.reward_value, o.reward_type
            FROM associate_offer_claims c
            JOIN associate_offers o ON o.id = c.offer_id
            WHERE c.user_id = ? AND c.tenant_id = ?
            ORDER BY c.id DESC
        ");
        $historyStmt->execute([$userId, $tid]);
        $claimHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'offers'         => $enrichedOffers,
            'active_count'   => count($enrichedOffers),
            'eligible_count' => $eligibleCount,
            'claims'         => $claimHistory,
        ];
    }

    /**
     * Compute an associate's progress toward a specific festival offer from plot bookings and site visits.
     */
    public function calculateAssociateOfferProgress(int $userId, array $offer): array
    {
        if (!$this->db) {
            return ['current_volume' => 0.0, 'current_bookings' => 0, 'current_visits' => 0, 'percent' => 0.0, 'is_eligible' => false];
        }

        $startDate = $offer['starts_at'] ?? '2026-01-01';
        $endDate = $offer['ends_at'] ?? '2026-12-31';
        $colonyId = !empty($offer['colony_id']) ? (int)$offer['colony_id'] : null;

        // Query plot bookings closed in date range
        $colonySql = $colonyId ? " AND pb.colony_id = $colonyId" : "";
        $bookingStmt = $this->db->prepare("
            SELECT 
                COUNT(*) AS total_bookings,
                COALESCE(SUM(pb.total_plot_value), 0) AS total_sales_volume
            FROM plot_bookings pb
            WHERE pb.associate_id = ?
              AND pb.status NOT IN ('cancelled', 'rejected')
              AND pb.booking_date BETWEEN ? AND ?
              $colonySql
        ");
        $bookingStmt->execute([$userId, $startDate, $endDate]);
        $bookingData = $bookingStmt->fetch(PDO::FETCH_ASSOC) ?: ['total_bookings' => 0, 'total_sales_volume' => 0];

        $currentVolume = (float)$bookingData['total_sales_volume'];
        $currentBookings = (int)$bookingData['total_bookings'];

        // Query verified site visits in date range
        $visitStmt = $this->db->prepare("
            SELECT COUNT(*) AS total_visits
            FROM site_visits sv
            WHERE (sv.user_id = ? OR sv.agent_id = ? OR sv.assigned_to = ?)
              AND sv.visit_date BETWEEN ? AND ?
              AND sv.status IN ('completed', 'verified')
        ");
        $visitStmt->execute([$userId, $userId, $userId, $startDate, $endDate]);
        $currentVisits = (int)$visitStmt->fetchColumn();

        $criteriaType = $offer['criteria_type'] ?? 'sale_volume';
        $targetValue = (float)($offer['criteria_value'] ?? 0);
        $minVisits = (int)($offer['min_site_visits'] ?? 0);

        $percent = 0.0;
        $isEligible = false;

        if ($criteriaType === 'sale_volume') {
            $percent = $targetValue > 0 ? min(100.0, round(($currentVolume / $targetValue) * 100, 1)) : 100.0;
            $isEligible = ($currentVolume >= $targetValue) && ($currentVisits >= $minVisits);
        } elseif ($criteriaType === 'booking_count') {
            $percent = $targetValue > 0 ? min(100.0, round(($currentBookings / $targetValue) * 100, 1)) : 100.0;
            $isEligible = ($currentBookings >= $targetValue) && ($currentVisits >= $minVisits);
        }

        return [
            'criteria_type'    => $criteriaType,
            'target_value'     => $targetValue,
            'current_volume'   => $currentVolume,
            'current_bookings' => $currentBookings,
            'current_visits'   => $currentVisits,
            'min_visits'       => $minVisits,
            'percent'          => $percent,
            'is_eligible'      => $isEligible,
        ];
    }

    /**
     * Submit an associate's claim for a festival offer.
     */
    public function claimAssociateOffer(int $userId, int $offerId, array $data = []): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database connection unavailable'];
        }

        $tid = (int)$this->tenantId();

        // 1. Fetch offer
        $stmt = $this->db->prepare("SELECT * FROM associate_offers WHERE id = ? AND tenant_id = ? LIMIT 1");
        $stmt->execute([$offerId, $tid]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) {
            return ['success' => false, 'message' => 'Festival offer not found'];
        }

        if ($offer['status'] !== 'active') {
            return ['success' => false, 'message' => 'This offer campaign is no longer active'];
        }

        // 2. Check existing claim
        $chk = $this->db->prepare("SELECT id, claim_status FROM associate_offer_claims WHERE offer_id = ? AND user_id = ? LIMIT 1");
        $chk->execute([$offerId, $userId]);
        $existing = $chk->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return ['success' => false, 'message' => 'You have already submitted a claim for this offer (Status: ' . $existing['claim_status'] . ')'];
        }

        // 3. Verify real-time progress eligibility
        $progress = $this->calculateAssociateOfferProgress($userId, $offer);
        if (!$progress['is_eligible']) {
            return ['success' => false, 'message' => 'You have not yet reached the required target for this festival offer'];
        }

        // 4. Determine claim mode & reward value
        $claimMode = ($data['claim_mode'] ?? 'wallet_cash') === 'physical_gift' ? 'physical_gift' : 'wallet_cash';
        $rewardValue = (float)($offer['reward_value'] ?? 0);
        $rewardItem = $offer['reward_item'] ?? ($offer['title'] . ' Festival Reward');

        $shippingAddress = trim($data['shipping_address'] ?? '');
        $contactPhone = trim($data['contact_phone'] ?? '');

        // 5. Insert claim
        $achievedValue = $progress['criteria_type'] === 'sale_volume' ? $progress['current_volume'] : $progress['current_bookings'];

        $ins = $this->db->prepare("
            INSERT INTO associate_offer_claims (
                tenant_id, offer_id, user_id, qualification_metric, achieved_value, target_value,
                claim_status, claim_mode, cash_amount, shipping_address, contact_phone, claimed_at, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                'claimed', ?, ?, ?, ?, NOW(), NOW(), NOW()
            )
        ");
        $ins->execute([
            $tid, $offerId, $userId, $progress['criteria_type'], $achievedValue, $progress['target_value'],
            $claimMode, $rewardValue, $shippingAddress ?: null, $contactPhone ?: null
        ]);

        $claimId = (int)$this->db->lastInsertId();

        return [
            'success'   => true,
            'claim_id'  => $claimId,
            'message'   => 'Congratulations! Your festival reward claim has been submitted successfully for verification.',
        ];
    }

    /**
     * Admin: Get all associate festival campaigns and claims.
     */
    public function getAdminOffersOverview(): array
    {
        if (!$this->db) {
            return ['campaigns' => [], 'claims' => [], 'stats' => []];
        }

        $tid = (int)$this->tenantId();

        // 1. All associate campaigns
        $cStmt = $this->db->prepare("
            SELECT o.*, c.name AS colony_name,
                   (SELECT COUNT(*) FROM associate_offer_claims WHERE offer_id = o.id) AS total_claims,
                   (SELECT COUNT(*) FROM associate_offer_claims WHERE offer_id = o.id AND claim_status = 'approved') AS approved_claims,
                   (SELECT COUNT(*) FROM associate_offer_claims WHERE offer_id = o.id AND claim_status IN ('delivered', 'wallet_credited')) AS fulfilled_claims
            FROM associate_offers o
            LEFT JOIN colonies c ON c.id = o.colony_id
            WHERE o.tenant_id = ?
            ORDER BY o.id DESC
        ");
        $cStmt->execute([$tid]);
        $campaigns = $cStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 2. All submitted claims
        $clStmt = $this->db->prepare("
            SELECT cl.*, u.name AS associate_name, u.email AS associate_email, u.phone AS associate_phone,
                   o.title AS offer_title, o.festival_name, o.reward_item, o.reward_value, o.reward_type
            FROM associate_offer_claims cl
            JOIN users u ON u.id = cl.user_id
            JOIN associate_offers o ON o.id = cl.offer_id
            WHERE cl.tenant_id = ?
            ORDER BY cl.id DESC
        ");
        $clStmt->execute([$tid]);
        $claims = $clStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 3. Stats summary
        $stats = [
            'total_campaigns' => count($campaigns),
            'pending_claims'  => 0,
            'approved_claims' => 0,
            'fulfilled_claims'=> 0,
            'total_paid_out'  => 0.0,
        ];

        foreach ($claims as $c) {
            if ($c['claim_status'] === 'claimed') $stats['pending_claims']++;
            if ($c['claim_status'] === 'approved') $stats['approved_claims']++;
            if (in_array($c['claim_status'], ['delivered', 'wallet_credited'], true)) {
                $stats['fulfilled_claims']++;
                $stats['total_paid_out'] += (float)$c['cash_amount'];
            }
        }

        return [
            'campaigns' => $campaigns,
            'claims'    => $claims,
            'stats'     => $stats,
        ];
    }

    /**
     * Admin: Approve an associate festival claim.
     */
    public function approveAssociateOfferClaim(int $claimId, int $adminId, ?string $adminNotes = null): array
    {
        if (!$this->db) return ['success' => false, 'message' => 'No database connection'];

        $stmt = $this->db->prepare("
            UPDATE associate_offer_claims
            SET claim_status = 'approved',
                approved_by = ?,
                approved_at = NOW(),
                admin_notes = COALESCE(?, admin_notes),
                updated_at = NOW()
            WHERE id = ? AND claim_status = 'claimed'
        ");
        $stmt->execute([$adminId, $adminNotes, $claimId]);

        return [
            'success' => $stmt->rowCount() > 0,
            'message' => $stmt->rowCount() > 0 ? 'Claim approved successfully' : 'Claim not found or not in pending state',
        ];
    }

    /**
     * Admin: Disburse cash reward directly to Associate Wallet.
     */
    public function disburseAssociateOfferCash(int $claimId, int $adminId): array
    {
        if (!$this->db) return ['success' => false, 'message' => 'No database connection'];

        $stmt = $this->db->prepare("
            SELECT cl.*, o.title AS offer_title
            FROM associate_offer_claims cl
            JOIN associate_offers o ON o.id = cl.offer_id
            WHERE cl.id = ? LIMIT 1
        ");
        $stmt->execute([$claimId]);
        $claim = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$claim) {
            return ['success' => false, 'message' => 'Claim not found'];
        }

        if ($claim['claim_status'] === 'wallet_credited') {
            return ['success' => false, 'message' => 'Reward has already been credited to associate wallet'];
        }

        $userId = (int)$claim['user_id'];
        $amount = (float)$claim['cash_amount'];

        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Cash reward amount must be greater than zero'];
        }

        // Credit to wallet
        $creditSuccess = $this->walletService->credit(
            $userId,
            $amount,
            'bonus',
            "Festival Campaign Reward: {$claim['offer_title']}",
            $claimId,
            'festival_offer'
        );

        if (!$creditSuccess) {
            return ['success' => false, 'message' => 'Wallet credit transaction failed'];
        }

        // Update claim record
        $upd = $this->db->prepare("
            UPDATE associate_offer_claims
            SET claim_status = 'wallet_credited',
                delivered_at = NOW(),
                admin_notes = CONCAT(COALESCE(admin_notes, ''), ' | Wallet credited: ₹', ?),
                updated_at = NOW()
            WHERE id = ?
        ");
        $upd->execute([number_format($amount, 2), $claimId]);

        return [
            'success' => true,
            'message' => "Successfully disbursed ₹" . number_format($amount, 2) . " directly into associate's wallet.",
        ];
    }

    /**
     * Admin: Mark physical gift delivered (Ceremony / Handover).
     */
    public function markAssociateOfferDelivered(int $claimId, int $adminId, ?string $notes = null): array
    {
        if (!$this->db) return ['success' => false, 'message' => 'No database connection'];

        $stmt = $this->db->prepare("
            UPDATE associate_offer_claims
            SET claim_status = 'delivered',
                delivered_at = NOW(),
                admin_notes = COALESCE(?, admin_notes),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$notes, $claimId]);

        return [
            'success' => $stmt->rowCount() > 0,
            'message' => $stmt->rowCount() > 0 ? 'Reward handover recorded as delivered' : 'Claim not found',
        ];
    }

    /* =========================================================================
       SECTION 2: CUSTOMER FESTIVAL BOOKING OFFERS & PROMO CODES
       ========================================================================= */

    /**
     * List active customer festival offers (optionally scoped to a colony).
     */
    public function getActiveCustomerOffers(?int $colonyId = null): array
    {
        if (!$this->db) return [];

        $tid = (int)$this->tenantId();
        $colonySql = $colonyId ? " AND (colony_id IS NULL OR colony_id = $colonyId)" : "";

        $stmt = $this->db->prepare("
            SELECT *
            FROM promotional_offers
            WHERE tenant_id = ?
              AND status = 'active'
              AND (valid_until IS NULL OR valid_until >= CURDATE())
              AND (valid_from IS NULL OR valid_from <= CURDATE())
              AND (max_redemptions = 0 OR times_redeemed < max_redemptions)
              $colonySql
            ORDER BY discount_value DESC, discount_percentage DESC, id DESC
        ");
        $stmt->execute([$tid]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Validate a promo code or offer ID and calculate customer discount for a plot booking.
     */
    public function validateCustomerPromo(string $promoCode, float $plotValue, ?int $colonyId = null): array
    {
        if (!$this->db) {
            return ['valid' => false, 'message' => 'No database connection'];
        }

        $code = strtoupper(trim($promoCode));
        if ($code === '') {
            return ['valid' => false, 'message' => 'Please enter a promo code'];
        }

        $tid = (int)$this->tenantId();
        $stmt = $this->db->prepare("
            SELECT *
            FROM promotional_offers
            WHERE tenant_id = ?
              AND UPPER(promo_code) = ?
              AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$tid, $code]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) {
            return ['valid' => false, 'message' => 'Invalid or expired promotional code'];
        }

        // Check date validity
        $today = date('Y-m-d');
        if (!empty($offer['valid_until']) && $offer['valid_until'] < $today) {
            return ['valid' => false, 'message' => 'This promo code has expired on ' . date('d M Y', strtotime($offer['valid_until']))];
        }
        if (!empty($offer['valid_from']) && $offer['valid_from'] > $today) {
            return ['valid' => false, 'message' => 'This promo code will be active starting ' . date('d M Y', strtotime($offer['valid_from']))];
        }

        // Check max redemptions
        $maxRedemptions = (int)($offer['max_redemptions'] ?? 0);
        $timesRedeemed = (int)($offer['times_redeemed'] ?? 0);
        if ($maxRedemptions > 0 && $timesRedeemed >= $maxRedemptions) {
            return ['valid' => false, 'message' => 'This promo code has reached its maximum redemption limit'];
        }

        // Check colony scope
        if (!empty($offer['colony_id']) && $colonyId !== null && (int)$offer['colony_id'] !== $colonyId) {
            return ['valid' => false, 'message' => 'This offer is not applicable to the selected colony/township'];
        }

        // Check minimum booking value
        $minVal = (float)($offer['min_booking_amount'] ?? 0);
        if ($minVal > 0 && $plotValue < $minVal) {
            return ['valid' => false, 'message' => 'Minimum booking value of ₹' . number_format($minVal) . ' required to use this promo code'];
        }

        // Calculate discount
        $discountType = $offer['discount_type'] ?? 'percentage';
        $discountAmount = 0.0;

        if ($discountType === 'percentage') {
            $pct = (float)($offer['discount_percentage'] ?? 0);
            $discountAmount = round(($plotValue * $pct) / 100.0, 2);
        } elseif ($discountType === 'flat_amount') {
            $val = (float)($offer['discount_value'] ?? 0);
            $discountAmount = min($plotValue, $val);
        } elseif ($discountType === 'per_sqft') {
            // If sqft not passed separately, estimate from value or default
            $discountAmount = min($plotValue, (float)($offer['discount_value'] ?? 0));
        }

        $netPayable = max(0.0, $plotValue - $discountAmount);

        return [
            'valid'           => true,
            'offer_id'        => (int)$offer['id'],
            'title'           => $offer['title'],
            'promo_code'      => $offer['promo_code'],
            'festival_name'   => $offer['festival_name'] ?? 'Festival Special',
            'discount_type'   => $discountType,
            'discount_amount' => $discountAmount,
            'net_payable'     => $netPayable,
            'offer'           => $offer,
            'message'         => 'Festive promo code applied successfully! You save ₹' . number_format($discountAmount, 2),
        ];
    }

    /**
     * Interlink: Apply promo discount to a created plot booking.
     */
    public function applyOfferToBooking(int $bookingId, int $offerId, float $discountAmount, string $promoCode): bool
    {
        if (!$this->db) return false;

        $stmt = $this->db->prepare("
            UPDATE plot_bookings
            SET discount_amount = ?,
                promotional_offer_id = ?,
                promo_code_applied = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$discountAmount, $offerId, $promoCode, $bookingId]);

        // Increment times_redeemed on promotional_offers
        $this->db->prepare("
            UPDATE promotional_offers
            SET times_redeemed = times_redeemed + 1,
                updated_at = NOW()
            WHERE id = ?
        ")->execute([$offerId]);

        return true;
    }
}
