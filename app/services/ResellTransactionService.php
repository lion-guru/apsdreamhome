<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;
use Exception;

class ResellTransactionService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;
    private $referralService;
    private $walletService;

    public function __construct($db = null)
    {
        $this->db = $db ?? Database::getInstance();
        if (is_object($this->db) && method_exists($this->db, "getPdo")) {
            $this->pdo = $this->db->getPdo();
        } elseif ($this->db instanceof \PDO) {
            $this->pdo = $this->db;
        } else {
            $this->pdo = $this->db;
        }
        $this->referralService = new ReferralService();
        $this->walletService = new WalletService();
    }

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Close a resale deal - called when seller marks property as sold
     * 
     * @param array $data {
     *     property_id: resell_properties.id,
     *     buyer_id: user_id of buyer,
     *     final_price: agreed price,
     *     payment_method: string,
     *     payment_reference: string,
     *     notes: string,
     *     created_by: admin/user id who closed the deal
     * }
     * @return array
     */
    public function closeResellDeal(array $data): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        // Get property details
        $propStmt = $this->pdo->prepare("SELECT * FROM resell_properties WHERE id = ?{$tenantWhere}");
        $propStmt->execute(array_merge([$data['property_id']], $tenantParams));
        $property = $propStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$property) {
            return ['success' => false, 'message' => 'Property not found'];
        }

        if (!in_array($property['status'], ['active', 'approved', 'available'], true)) {
            return ['success' => false, 'message' => 'Property is not available for sale'];
        }

        $sellerId = (int)$property['user_id'];
        $buyerId = (int)$data['buyer_id'];

        if ($sellerId === $buyerId) {
            return ['success' => false, 'message' => 'Seller cannot buy their own property'];
        }

        // Get buyer's referrer (if any) - from users.referred_by
        $buyerStmt = $this->pdo->prepare("SELECT referred_by, referral_code FROM users WHERE id = ?{$tenantWhere}");
        $buyerStmt->execute(array_merge([$buyerId], $tenantParams));
        $buyer = $buyerStmt->fetch(\PDO::FETCH_ASSOC);

        $buyerReferrerId = $buyer['referred_by'] ?? null;
        $buyerReferralCode = $buyer['referral_code'] ?? null;

        // Calculate platform fee (from commission structure or default 1%)
        $platformFeePct = $this->getPlatformFeePct($property['property_type'], $data['final_price']);
        $platformFee = round($data['final_price'] * $platformFeePct / 100, 2);

        // Calculate referral commission (only for buyer's referrer, not seller's)
        $referralCommission = 0;
        $commissionRate = 0;
        
        if ($buyerReferrerId) {
            // Get commission rate from structure
            $commissionRate = $this->getReferralCommissionRate($property['property_type'], $data['final_price']);
            $referralCommission = round($data['final_price'] * $commissionRate / 100, 2);
        }

        // Start transaction
        $this->pdo->beginTransaction();

        try {
            // 1. Create transaction record
            $extraCol = $tid > 1 ? ', tenant_id' : '';
            $extraVal = $tid > 1 ? ', ?' : '';
            
            $txnStmt = $this->pdo->prepare("
                INSERT INTO resell_transactions 
                (property_id, seller_id, buyer_id, buyer_referrer_id, final_price, platform_fee, referral_commission, commission_rate, status, deal_closed_at, payment_method, payment_reference, notes, created_by, created_at{$extraCol})
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', NOW(), ?, ?, ?, ?, NOW(){$extraVal})
            ");
            
            $txnParams = [
                $data['property_id'],
                $sellerId,
                $buyerId,
                $buyerReferrerId,
                $data['final_price'],
                $platformFee,
                $referralCommission,
                $commissionRate,
                $data['payment_method'] ?? 'bank_transfer',
                $data['payment_reference'] ?? '',
                $data['notes'] ?? '',
                $data['created_by'] ?? $buyerId,
            ];
            if ($tid > 1) $txnParams[] = $tid;
            
            $txnStmt->execute($txnParams);
            $transactionId = (int)$this->pdo->lastInsertId();

            // 2. Update property status to sold
            $updStmt = $this->pdo->prepare("
                UPDATE resell_properties 
                SET status = 'sold', sold_at = NOW(), transaction_id = ?, updated_at = NOW()
                WHERE id = ?{$tenantWhere}
            ");
            $updStmt->execute(array_merge([$transactionId, $data['property_id']], $tenantParams));

            // 3. Pay referral commission to buyer's referrer (if any)
            if ($buyerReferrerId && $referralCommission > 0) {
                $this->payReferralCommission($buyerReferrerId, $buyerId, $referralCommission, $buyerReferralCode, $transactionId, 'resell');
                $this->recordAssociateCommission($buyerReferrerId, $transactionId, 'resell', 'referral_buyer', $referralCommission, $commissionRate);
            }

            // 4. Credit platform fee to company wallet (or track for admin)
            $this->trackPlatformFee($platformFee, $transactionId, 'resell');

            // 5. Notify parties
            $this->notifyDealClosed($property, $sellerId, $buyerId, $data['final_price'], $transactionId);

            $this->pdo->commit();

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'platform_fee' => $platformFee,
                'referral_commission' => $referralCommission,
                'referrer_id' => $buyerReferrerId,
                'message' => 'Deal closed successfully'
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("ResellTransactionService::closeResellDeal error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to close deal: ' . $e->getMessage()];
        }
    }

    /**
     * Close a user_properties marketplace deal
     */
    public function closeUserPropertyDeal(array $data): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        $propStmt = $this->pdo->prepare("SELECT * FROM user_properties WHERE id = ?{$tenantWhere}");
        $propStmt->execute(array_merge([$data['property_id']], $tenantParams));
        $property = $propStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$property) {
            return ['success' => false, 'message' => 'Property not found'];
        }

        if (!in_array($property['status'], ['approved', 'verified', 'active'], true)) {
            return ['success' => false, 'message' => 'Property is not available for sale'];
        }

        $sellerId = (int)$property['user_id'];
        $buyerId = (int)$data['buyer_id'];

        if ($sellerId === $buyerId) {
            return ['success' => false, 'message' => 'Seller cannot buy their own property'];
        }

        // Get buyer's referrer
        $buyerStmt = $this->pdo->prepare("SELECT referred_by, referral_code FROM users WHERE id = ?{$tenantWhere}");
        $buyerStmt->execute(array_merge([$buyerId], $tenantParams));
        $buyer = $buyerStmt->fetch(\PDO::FETCH_ASSOC);

        $buyerReferrerId = $buyer['referred_by'] ?? null;
        $buyerReferralCode = $buyer['referral_code'] ?? null;

        // Platform fee (default 1% for user properties)
        $platformFeePct = 1.00;
        $platformFee = round($data['final_price'] * $platformFeePct / 100, 2);

        // Referral commission (only buyer's referrer)
        $referralCommission = 0;
        $commissionRate = 0;
        
        if ($buyerReferrerId) {
            $commissionRate = 1.00; // 1% referral commission for user properties
            $referralCommission = round($data['final_price'] * $commissionRate / 100, 2);
        }

        $this->pdo->beginTransaction();

        try {
            $extraCol = $tid > 1 ? ', tenant_id' : '';
            $extraVal = $tid > 1 ? ', ?' : '';
            
            $txnStmt = $this->pdo->prepare("
                INSERT INTO property_transactions 
                (property_id, listing_type, seller_id, buyer_id, buyer_referrer_id, final_price, platform_fee, referral_commission, commission_rate, status, deal_closed_at, payment_method, payment_reference, notes, created_by, created_at{$extraCol})
                VALUES (?, 'user', ?, ?, ?, ?, ?, ?, ?, 'completed', NOW(), ?, ?, ?, ?, NOW(){$extraVal})
            ");
            
            $txnParams = [
                $data['property_id'],
                $sellerId,
                $buyerId,
                $buyerReferrerId,
                $data['final_price'],
                $platformFee,
                $referralCommission,
                $commissionRate,
                $data['payment_method'] ?? 'bank_transfer',
                $data['payment_reference'] ?? '',
                $data['notes'] ?? '',
                $data['created_by'] ?? $buyerId,
            ];
            if ($tid > 1) $txnParams[] = $tid;
            
            $txnStmt->execute($txnParams);
            $transactionId = (int)$this->pdo->lastInsertId();

            // Update property status
            $updStmt = $this->pdo->prepare("
                UPDATE user_properties 
                SET status = 'sold', sold_at = NOW(), transaction_id = ?, updated_at = NOW()
                WHERE id = ?{$tenantWhere}
            ");
            $updStmt->execute(array_merge([$transactionId, $data['property_id']], $tenantParams));

            // Pay referral commission
            if ($buyerReferrerId && $referralCommission > 0) {
                $this->payReferralCommission($buyerReferrerId, $buyerId, $referralCommission, $buyerReferralCode, $transactionId, 'user_property');
                $this->recordAssociateCommission($buyerReferrerId, $transactionId, 'user_property', 'referral_buyer', $referralCommission, $commissionRate);
            }

            // Track platform fee
            $this->trackPlatformFee($platformFee, $transactionId, 'user_property');

            // Notify
            $this->notifyUserPropertyDealClosed($property, $sellerId, $buyerId, $data['final_price'], $transactionId);

            $this->pdo->commit();

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'platform_fee' => $platformFee,
                'referral_commission' => $referralCommission,
                'referrer_id' => $buyerReferrerId,
                'message' => 'Deal closed successfully'
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("ResellTransactionService::closeUserPropertyDeal error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to close deal: ' . $e->getMessage()];
        }
    }

    /**
     * Get platform fee percentage from commission structure
     */
    private function getPlatformFeePct(string $propertyType, float $price): float
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT platform_fee_pct FROM resell_commission_structure 
                WHERE property_type = ? AND min_price <= ? AND (max_price IS NULL OR max_price >= ?) AND is_active = 1
                ORDER BY min_price DESC LIMIT 1
            ");
            $stmt->execute([$propertyType, $price, $price]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (float)$row['platform_fee_pct'] : 1.00;
        } catch (\Throwable $e) {
            return 1.00; // Default 1%
        }
    }

    /**
     * Get referral commission rate from commission structure
     */
    private function getReferralCommissionRate(string $propertyType, float $price): float
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT commission_pct FROM resell_commission_structure 
                WHERE property_type = ? AND min_price <= ? AND (max_price IS NULL OR max_price >= ?) AND is_active = 1
                ORDER BY min_price DESC LIMIT 1
            ");
            $stmt->execute([$propertyType, $price, $price]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (float)$row['commission_pct'] : 1.00;
        } catch (\Throwable $e) {
            return 1.00; // Default 1%
        }
    }

    /**
     * Pay referral commission to buyer's referrer
     */
    private function payReferralCommission(int $referrerId, int $buyerId, float $amount, string $referralCode, int $transactionId, string $source): void
    {
        try {
            // Use wallet service to credit points
            $this->walletService->credit($referrerId, $amount, 'referral', [
                'description' => "Referral commission from {$source} deal #{$transactionId}",
                'reference_id' => $transactionId,
                'reference_type' => $source,
                'related_user_id' => $buyerId,
                'referral_code' => $referralCode
            ]);

            // Log in referral_rewards
            $tid = $this->getTenantId();
            $stmt = $this->pdo->prepare("
                INSERT INTO referral_rewards 
                (referrer_id, referred_id, reward_amount, reward_type, reward_percentage, referral_code, status, tenant_id, credited_at, created_at)
                VALUES (?, ?, ?, 'wallet_points', 0, ?, 'credited', ?, NOW(), NOW())
            ");
            $params = [$referrerId, $buyerId, $amount, $referralCode, $tid];
            $stmt->execute($params);

        } catch (\Throwable $e) {
            error_log("ResellTransactionService::payReferralCommission error: " . $e->getMessage());
        }
    }

    /**
     * Record associate/agent commission for resale deal
     */
    private function recordAssociateCommission(int $associateUserId, int $transactionId, string $txnType, string $commissionType, float $amount, float $rate): void
    {
        try {
            $tid = $this->getTenantId();
            $extraCol = $tid > 1 ? ', tenant_id' : '';
            $extraVal = $tid > 1 ? ', ?' : '';

            // Check if this user is an associate or agent
            $roleStmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
            $roleStmt->execute([$associateUserId]);
            $role = $roleStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$role || !in_array($role['role'], ['associate', 'agent'], true)) {
                return;
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO associate_resale_commissions 
                (associate_id, transaction_id, transaction_type, commission_type, commission_amount, commission_rate, status, created_at{$extraCol})
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(){$extraVal})
            ");
            $params = [$associateUserId, $transactionId, $txnType, $commissionType, $amount, $rate];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log("ResellTransactionService::recordAssociateCommission: " . $e->getMessage());
        }
    }

    /**
     * Track platform fee revenue
     */
    private function trackPlatformFee(float $amount, int $transactionId, string $source): void
    {
        try {
            $tid = $this->getTenantId();
            $stmt = $this->pdo->prepare("
                INSERT INTO platform_revenue 
                (source_type, transaction_id, amount, tenant_id, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$source, $transactionId, $amount, $tid]);
        } catch (\Throwable $e) {
            error_log("ResellTransactionService::trackPlatformFee error: " . $e->getMessage());
        }
    }

    /**
     * Notify parties when resell deal closes
     */
    private function notifyDealClosed(array $property, int $sellerId, int $buyerId, float $price, int $transactionId): void
    {
        try {
            $notif = new NotificationService();
            
            // Notify seller
            $notif->send($sellerId, 'email', 'Your Property is Sold!', 
                "Congratulations! Your property '{$property['title']}' has been sold for ₹" . number_format($price) . ". Transaction ID: #{$transactionId}");
            
            // Notify buyer
            $notif->send($buyerId, 'email', 'Purchase Confirmed',
                "Your purchase of '{$property['title']}' for ₹" . number_format($price) . " is confirmed. Transaction ID: #{$transactionId}");
                
        } catch (\Throwable $e) {
            error_log("ResellTransactionService::notifyDealClosed error: " . $e->getMessage());
        }
    }

    /**
     * Notify parties when user_property deal closes
     */
    private function notifyUserPropertyDealClosed(array $property, int $sellerId, int $buyerId, float $price, int $transactionId): void
    {
        try {
            $notif = new NotificationService();
            
            $notif->send($sellerId, 'email', 'Your Property is Sold!',
                "Your listed property '{$property['name']}' has been sold for ₹" . number_format($price) . ". Transaction ID: #{$transactionId}");
            
            $notif->send($buyerId, 'email', 'Purchase Confirmed',
                "Your purchase of '{$property['name']}' for ₹" . number_format($price) . " is confirmed. Transaction ID: #{$transactionId}");
                
        } catch (\Throwable $e) {
            error_log("ResellTransactionService::notifyUserPropertyDealClosed error: " . $e->getMessage());
        }
    }

    /**
     * Get transaction history for a user
     */
    public function getUserTransactions(int $userId, string $role = 'all', int $limit = 50): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        $whereRole = '';
        if ($role === 'seller') $whereRole = 'AND seller_id = ?';
        elseif ($role === 'buyer') $whereRole = 'AND buyer_id = ?';
        elseif ($role === 'referrer') $whereRole = 'AND buyer_referrer_id = ?';

        $params = array_merge([$userId], $tenantParams);

        $sql = "SELECT rt.*, rp.title as property_title, rp.property_type,
                       u_seller.name as seller_name, u_buyer.name as buyer_name, u_ref.name as referrer_name
                FROM resell_transactions rt
                LEFT JOIN resell_properties rp ON rt.property_id = rp.id
                LEFT JOIN users u_seller ON rt.seller_id = u_seller.id
                LEFT JOIN users u_buyer ON rt.buyer_id = u_buyer.id
                LEFT JOIN users u_ref ON rt.buyer_referrer_id = u_ref.id
                WHERE (rt.seller_id = ? OR rt.buyer_id = ? OR rt.buyer_referrer_id = ?)
                {$tenantWhere}
                ORDER BY rt.deal_closed_at DESC
                LIMIT {$limit}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get platform revenue stats
     */
    public function getPlatformRevenue(int $days = 30): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                source_type,
                COUNT(*) as transactions,
                SUM(amount) as revenue
            FROM platform_revenue
            WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
            {$tenantWhere}
            GROUP BY DATE(created_at), source_type
            ORDER BY date DESC
        ");
        $params = array_merge([$days], $tenantParams);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}