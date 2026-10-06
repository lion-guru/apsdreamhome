<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;
use PDO;

class WalletActivationService
{
    use ServiceTenantTrait;

    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getActivePackages(): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $stmt = $this->db->prepare(
            "SELECT * FROM wallet_activation_packages WHERE is_active = 1" . $tenantWhere . " ORDER BY sort_order, price"
        );
        $stmt->execute($params);
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($packages as &$pkg) {
            $pkg['features'] = json_decode($pkg['features'] ?? '{}', true);
            $pkg = $this->withComputedRewards($pkg);
        }
        unset($pkg);

        return $packages;
    }

    public function getPackageById(int $id): ?array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = array_merge([$id], $tid > 1 ? [$tid] : []);

        $stmt = $this->db->prepare(
            "SELECT * FROM wallet_activation_packages WHERE id = ?" . $tenantWhere . " LIMIT 1"
        );
        $stmt->execute($params);
        $pkg = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pkg) {
            $pkg['features'] = json_decode($pkg['features'] ?? '{}', true);
            $pkg = $this->withComputedRewards($pkg);
        }

        return $pkg ?: null;
    }

    public function getPackageBySlug(string $slug): ?array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = array_merge([$slug], $tid > 1 ? [$tid] : []);

        $stmt = $this->db->prepare(
            "SELECT * FROM wallet_activation_packages WHERE slug = ? AND is_active = 1" . $tenantWhere . " LIMIT 1"
        );
        $stmt->execute($params);
        $pkg = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pkg) {
            $pkg['features'] = json_decode($pkg['features'] ?? '{}', true);
            $pkg = $this->withComputedRewards($pkg);
        }

        return $pkg ?: null;
    }

    /**
     * Attach computed display values: per-level % (package row, else global
     * defaults) clamped so L1+L2 <= 30, plus L1 rupee amount for display.
     */
    private function withComputedRewards(array $pkg): array
    {
        $l1 = isset($pkg['referral_pct_l1']) ? (float)$pkg['referral_pct_l1'] : (float)\App\Services\ServiceConfigService::getVal('referral', 'wallet_l1_default_pct', 20.00);
        $l2 = isset($pkg['referral_pct_l2']) ? (float)$pkg['referral_pct_l2'] : (float)\App\Services\ServiceConfigService::getVal('referral', 'wallet_l2_default_pct', 5.00);
        $l1 = min(max($l1, 0.0), 30.0);
        $l2 = min(max($l2, 0.0), max(0.0, 30.0 - $l1));
        $pkg['referral_pct_l1'] = $l1;
        $pkg['referral_pct_l2'] = $l2;
        // Backward-compatible display key: L1 rupee reward at current price.
        $pkg['referral_reward'] = round((float)($pkg['price'] ?? 0) * $l1 / 100, 2);
        return $pkg;
    }

    public function purchasePackage(int $userId, int $packageId, string $paymentMode = 'razorpay', ?string $paymentRef = null): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        // Get package
        $params = array_merge([$packageId], $tenantParams);
        $stmt = $this->db->prepare(
            "SELECT * FROM wallet_activation_packages WHERE id = ? AND is_active = 1" . $tenantWhere . " LIMIT 1"
        );
        $stmt->execute($params);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$package) {
            return ['success' => false, 'message' => 'Package not found or inactive'];
        }

        $features = json_decode($package['features'] ?? '{}', true);

        try {
            $this->db->beginTransaction();

            // Create purchase record
            $purchaseId = $this->db->insert('wallet_activation_purchases', array_merge([
                'user_id' => $userId,
                'package_id' => $packageId,
                'amount_paid' => $package['price'],
                'payment_mode' => $paymentMode,
                'payment_ref' => $paymentRef,
                'status' => 'pending',
            ], $this->tenantInsertData()));

            // For Razorpay, create gateway order (canonical Gateway\RazorpayService)
            $orderId = $paymentRef;
            $keyId = '';
            if ($paymentMode === 'razorpay') {
                try {
                    $razorpay = new \App\Services\Gateway\RazorpayService();
                    $keyId = $razorpay->getKeyId();
                    $order = $razorpay->createOrder(
                        (float)$package['price'],
                        'INR',
                        'WALLET_' . $purchaseId,
                        [
                            'user_id' => $userId,
                            'package_id' => (int)$packageId,
                            'package_name' => $package['name'] ?? '',
                            'description' => 'Wallet Activation: ' . ($package['name'] ?? ('#' . $packageId)),
                        ]
                    );
                    if (!empty($order['success']) && isset($order['data']['id'])) {
                        $orderId = $order['data']['id'];
                        $this->db->query(
                            "UPDATE wallet_activation_purchases SET payment_ref = ? WHERE id = ?",
                            [$orderId, $purchaseId]
                        );
                    }
                } catch (\Throwable $e) {
                    error_log("Razorpay order creation failed: " . $e->getMessage());
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'purchase_id' => $purchaseId,
                'package' => $package,
                'amount' => $package['price'],
                'payment_ref' => $orderId,
                'order_id' => $orderId,
                'key_id' => $keyId,
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log("WalletActivationService::purchasePackage error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Purchase failed'];
        }
    }

    public function activatePurchase(int $purchaseId, int $userId): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        try {
            $this->db->beginTransaction();

            // Get purchase with package
            $params = array_merge([$purchaseId], $tenantParams);
            $stmt = $this->db->prepare(
                "SELECT wap.*, wapk.price, wapk.referral_pct_l1, wapk.referral_pct_l2, wapk.features, wapk.name AS package_name
                 FROM wallet_activation_purchases wap
                 JOIN wallet_activation_packages wapk ON wap.package_id = wapk.id
                 WHERE wap.id = ? AND wap.user_id = ?" . $tenantWhere . " LIMIT 1"
            );
            $params = array_merge([$purchaseId, $userId], $tenantParams);
            $stmt->execute($params);
            $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$purchase) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Purchase not found'];
            }

            if ($purchase['status'] === 'completed') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Already activated'];
            }

            // Update purchase status
            $this->db->query(
                "UPDATE wallet_activation_purchases SET status = 'completed', activated_at = NOW() WHERE id = ?",
                [$purchaseId]
            );

            // Update user's wallet_points
            $features = json_decode($purchase['features'] ?? '{}', true);
            $this->db->query(
                "UPDATE wallet_points 
                 SET wallet_activated = 1, 
                      activation_package_id = ?, 
                      activation_expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR),
                      updated_at = NOW()
                 WHERE user_id = ?" . $tenantWhere,
                array_merge([(int)($purchase['package_id'] ?? 0), $userId], $tenantParams)
            );

            // Ensure wallet exists
            try {
                $walletService = new WalletService();
                $walletService->ensureWallet($userId);
            } catch (\Throwable $e) {
                error_log("Wallet ensure failed: " . $e->getMessage());
            }

            // Pay L1 (+L2 chain) referral commission on package price
            $this->payReferralCommission($userId, (float)($purchase['price'] ?? 0), $purchaseId, $purchase);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Wallet activated successfully',
                'package' => $purchase['name'] ?? 'Package',
                'features' => $features,
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log("WalletActivationService::activatePurchase error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Activation failed'];
        }
    }

    /**
     * Pay L1 (+L2 chain) referral reward as % of the package price.
     * L1 → direct referrer, L2 → referrer's sponsor. Self-funding: total ≤30%.
     */
    private function payReferralCommission(int $userId, float $price, int $purchaseId, array $purchase): void
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        // Percents: package row first, global defaults as fallback; clamp L1+L2 ≤ 30.
        $l1 = isset($purchase['referral_pct_l1']) ? (float)$purchase['referral_pct_l1'] : (float)\App\Services\ServiceConfigService::getVal('referral', 'wallet_l1_default_pct', 20.00);
        $l2 = isset($purchase['referral_pct_l2']) ? (float)$purchase['referral_pct_l2'] : (float)\App\Services\ServiceConfigService::getVal('referral', 'wallet_l2_default_pct', 5.00);
        $l1 = min(max($l1, 0.0), 30.0);
        $l2 = min(max($l2, 0.0), max(0.0, 30.0 - $l1));

        // L1 referrer
        $params = array_merge([$userId], $tenantParams);
        $stmt = $this->db->prepare("SELECT referred_by FROM users WHERE id = ?" . $tenantWhere . " LIMIT 1");
        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || empty($user['referred_by'])) {
            return;
        }
        $referrerId = (int)$user['referred_by'];
        if ($referrerId === $userId) {
            return;
        }

        $l1Amount = round($price * $l1 / 100, 2);
        if ($l1Amount > 0) {
            $this->payLevelReward($referrerId, $userId, 'wallet_activation_referral', $l1Amount, $l1, $price, $purchaseId, $tid, $tenantWhere);
        }

        // L2: referrer's own sponsor (one level up the users.referred_by chain)
        if ($l2 > 0) {
            $params = array_merge([$referrerId], $tenantParams);
            $stmt = $this->db->prepare("SELECT referred_by FROM users WHERE id = ?" . $tenantWhere . " LIMIT 1");
            $stmt->execute($params);
            $sponsor = $stmt->fetch(PDO::FETCH_ASSOC);
            $uplineId = (int)($sponsor['referred_by'] ?? 0);
            if ($uplineId > 0 && $uplineId !== $referrerId && $uplineId !== $userId) {
                $l2Amount = round($price * $l2 / 100, 2);
                if ($l2Amount > 0) {
                    $this->payLevelReward($uplineId, $userId, 'wallet_activation_referral_l2', $l2Amount, $l2, $price, $purchaseId, $tid, $tenantWhere);
                }
            }
        }

        // Mark purchase so re-activation attempts don't reprocess
        $this->db->query(
            "UPDATE wallet_activation_purchases SET referral_commission_paid = 1 WHERE id = ?",
            [$purchaseId]
        );
    }

    /**
     * Single idempotent level payout: ledger row + wallet credit.
     */
    private function payLevelReward(int $beneficiaryId, int $sourceUserId, string $ledgerType, float $amount, float $pct, float $price, int $purchaseId, int $tid, string $tenantWhere): void
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM mlm_commission_ledger
             WHERE beneficiary_user_id = ? AND source_user_id = ?
             AND commission_type = ?" . $tenantWhere . " LIMIT 1"
        );
        $params = [$beneficiaryId, $sourceUserId, $ledgerType];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        if ($stmt->fetch()) {
            return; // Already paid
        }

        // commission_type is free-form varchar; context rides in notes.
        $this->db->insert('mlm_commission_ledger', array_merge([
            'beneficiary_user_id' => $beneficiaryId,
            'source_user_id' => $sourceUserId,
            'commission_type' => $ledgerType,
            'amount' => $amount,
            'status' => 'approved',
            'notes' => "Wallet activation {$pct}% of ₹{$price} for user #{$sourceUserId} (purchase #{$purchaseId})",
            'created_at' => date('Y-m-d H:i:s'),
        ], $this->tenantInsertData()));

        // Wallet category must be a valid wallet_transactions enum value.
        try {
            $walletService = new WalletService();
            $walletService->credit(
                $beneficiaryId,
                $amount,
                'referral',
                "Wallet activation {$pct}% reward (user #{$sourceUserId}, purchase #{$purchaseId})",
                $purchaseId,
                'wallet_activation_purchase'
            );
        } catch (\Throwable $e) {
            error_log("Wallet credit failed for referral: " . $e->getMessage());
        }
    }

    public function getUserPurchase(int $userId): ?array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND wap.tenant_id = ?" : "";
        $params = array_merge([$userId], $tid > 1 ? [$tid] : []);

        $stmt = $this->db->prepare(
            "SELECT wap.*, wapk.name as package_name, wapk.slug, wapk.features
             FROM wallet_activation_purchases wap
             JOIN wallet_activation_packages wapk ON wap.package_id = wapk.id
             WHERE wap.user_id = ? AND wap.status = 'completed'" . $tenantWhere . "
             ORDER BY wap.activated_at DESC LIMIT 1"
        );
        $stmt->execute($params);
        $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($purchase) {
            $purchase['features'] = json_decode($purchase['features'] ?? '{}', true);
        }

        return $purchase ?: null;
    }

    /**
     * Check if user's wallet is activated (supports both wallet_points and user_wallets)
     */
    public function isWalletActivated(int $userId): bool
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = array_merge([$userId], $tid > 1 ? [$tid] : []);

        // Try wallet_points first (customers)
        $stmt = $this->db->prepare(
            "SELECT wallet_activated FROM wallet_points WHERE user_id = ?" . $tenantWhere . " LIMIT 1"
        );
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && (int)$row['wallet_activated'] === 1) {
            return true;
        }

        // Try user_wallets (associates/agents/employees)
        $stmt = $this->db->prepare(
            "SELECT is_active FROM user_wallets WHERE user_id = ?" . $tenantWhere . " LIMIT 1"
        );
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && (int)$row['is_active'] === 1) {
            return true;
        }

        return false;
    }

    /**
     * Get user's wallet type (wallet_points | user_wallets | none)
     */
    public function getWalletType(int $userId): string
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = array_merge([$userId], $tid > 1 ? [$tid] : []);

        $stmt = $this->db->prepare("SELECT 1 FROM wallet_points WHERE user_id = ?" . $tenantWhere . " LIMIT 1");
        $stmt->execute($params);
        if ($stmt->fetch()) return 'wallet_points';

        $stmt = $this->db->prepare("SELECT 1 FROM user_wallets WHERE user_id = ?" . $tenantWhere . " LIMIT 1");
        $stmt->execute($params);
        if ($stmt->fetch()) return 'user_wallets';

        return 'none';
    }

    /**
     * Activate purchase for user_wallets system (associates/agents/employees)
     */
    public function activatePurchaseUserWallet(int $purchaseId, int $userId): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        try {
            $this->db->beginTransaction();

            // Get purchase with package
            $params = array_merge([$purchaseId], $tenantParams);
            $stmt = $this->db->prepare(
                "SELECT wap.*, wapk.price, wapk.referral_pct_l1, wapk.referral_pct_l2, wapk.features, wapk.name AS package_name
                 FROM wallet_activation_purchases wap
                 JOIN wallet_activation_packages wapk ON wap.package_id = wapk.id
                 WHERE wap.id = ? AND wap.user_id = ?" . $tenantWhere . " LIMIT 1"
            );
            $params = array_merge([$purchaseId, $userId], $tenantParams);
            $stmt->execute($params);
            $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$purchase) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Purchase not found'];
            }

            if ($purchase['status'] === 'completed') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Already activated'];
            }

            // Update purchase status
            $this->db->query(
                "UPDATE wallet_activation_purchases SET status = 'completed', activated_at = NOW() WHERE id = ?",
                [$purchaseId]
            );

            // Update user_wallets
            $features = json_decode($purchase['features'] ?? '{}', true);
            $this->db->query(
                "UPDATE user_wallets 
                 SET is_active = 1, 
                     activation_package_id = ?, 
                     activation_expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR),
                     updated_at = NOW()
                 WHERE user_id = ?" . $tenantWhere,
                array_merge([(int)($purchase['package_id'] ?? 0), $userId], $tenantParams)
            );

            // Ensure wallet exists
            try {
                $walletService = new WalletService();
                $walletService->ensureWallet($userId);
            } catch (\Throwable $e) {
                error_log("Wallet ensure failed: " . $e->getMessage());
            }

            // Pay L1 (+L2 chain) referral commission on package price
            $this->payReferralCommission($userId, (float)($purchase['price'] ?? 0), $purchaseId, $purchase);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Wallet activated successfully',
                'package' => $purchase['name'] ?? 'Package',
                'features' => $features,
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log("WalletActivationService::activatePurchaseUserWallet error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Activation failed'];
        }
    }

    public function checkFeatureAccess(int $userId, string $feature): bool
    {
        $purchase = $this->getUserPurchase($userId);
        if (!$purchase) {
            return false;
        }

        $features = $purchase['features'] ?? [];
        return $features[$feature] === true;
    }
}