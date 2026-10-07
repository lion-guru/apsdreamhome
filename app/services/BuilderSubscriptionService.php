<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;
use Exception;

class BuilderSubscriptionService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;

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
     * Get all active builder packages
     */
    public function getActivePackages(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM builder_packages WHERE is_active = 1 ORDER BY sort_order ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get package by slug
     */
    public function getPackageBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM builder_packages WHERE slug = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get user's active subscription
     */
    public function getUserSubscription(int $userId): ?array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND bs.tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        try {
            $stmt = $this->pdo->prepare("
                SELECT bs.*, bp.name as package_name, bp.slug as package_slug, 
                       bp.max_listings, bp.max_featured, bp.max_urgent, bp.logo_on_listing,
                       bp.priority_support, bp.analytics_access, bp.lead_export, bp.api_access
                FROM builder_subscriptions bs
                JOIN builder_packages bp ON bs.package_id = bp.id
                WHERE bs.builder_id = ? AND bs.status = 'active' AND bs.ends_at > NOW()
                {$tenantWhere}
                ORDER BY bs.created_at DESC LIMIT 1
            ");
            $stmt->execute(array_merge([$userId], $tenantParams));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Subscribe builder to a package
     */
    public function subscribe(int $userId, string $packageSlug, string $billingCycle = 'monthly', string $paymentMethod = 'razorpay', string $paymentReference = ''): array
    {
        $package = $this->getPackageBySlug($packageSlug);
        if (!$package) {
            return ['success' => false, 'message' => 'Invalid package'];
        }

        $amount = $billingCycle === 'yearly' ? $package['price_yearly'] : $package['price_monthly'];
        $durationDays = $billingCycle === 'yearly' ? 365 : 30;

        $tid = $this->getTenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';

        try {
            $this->pdo->beginTransaction();

            // Cancel existing subscription
            $this->pdo->prepare("
                UPDATE builder_subscriptions SET status = 'cancelled', updated_at = NOW()
                WHERE builder_id = ? AND status = 'active'
            ")->execute([$userId]);

            // Create new subscription
            $stmt = $this->pdo->prepare("
                INSERT INTO builder_subscriptions 
                (builder_id, package_id, status, starts_at, ends_at, auto_renew, payment_method, payment_reference, amount_paid, created_at{$extraCol})
                VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 1, ?, ?, ?, NOW(){$extraVal})
            ");
            $params = [$userId, $package['id'], $durationDays, $paymentMethod, $paymentReference, $amount];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $subscriptionId = (int)$this->pdo->lastInsertId();

            // Track revenue
            $this->pdo->prepare("
                INSERT INTO platform_revenue (source_type, source_id, amount, description, recorded_at)
                VALUES ('builder_subscription', ?, ?, ?, NOW())
            ")->execute([$subscriptionId, $amount, "Builder subscription: {$package['name']} ({$billingCycle})"]);

            $this->pdo->commit();

            return [
                'success' => true,
                'subscription_id' => $subscriptionId,
                'package_name' => $package['name'],
                'amount' => $amount,
                'expires_at' => date('Y-m-d H:i:s', strtotime("+{$durationDays} days")),
            ];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            error_log("BuilderSubscriptionService::subscribe: " . $e->getMessage());
            return ['success' => false, 'message' => 'Subscription failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get builder's listing count vs package limit
     */
    public function getListingUsage(int $userId): array
    {
        $subscription = $this->getUserSubscription($userId);
        $maxListings = $subscription['max_listings'] ?? 5;

        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM user_properties WHERE user_id = ? AND status NOT IN ('sold', 'rejected')");
            $stmt->execute([$userId]);
            $current = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];
        } catch (\Throwable $e) {
            $current = 0;
        }

        return [
            'current' => $current,
            'max' => $maxListings,
            'remaining' => max(0, $maxListings - $current),
            'has_subscription' => !empty($subscription),
            'package_name' => $subscription['package_name'] ?? 'Free',
        ];
    }

    /**
     * Check if builder can create more listings
     */
    public function canCreateListing(int $userId): bool
    {
        $usage = $this->getListingUsage($userId);
        return $usage['remaining'] > 0 || !$usage['has_subscription'];
    }

    /**
     * Get builder dashboard stats
     */
    public function getBuilderDashboard(int $userId): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        $subscription = $this->getUserSubscription($userId);
        $usage = $this->getListingUsage($userId);

        // Total listings
        try {
            $stmt = $this->pdo->prepare("SELECT status, COUNT(*) as count FROM user_properties WHERE user_id = ?{$tenantWhere} GROUP BY status");
            $stmt->execute(array_merge([$userId], $tenantParams));
            $listingsByStatus = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $listingsByStatus = [];
        }

        // Total views
        try {
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(views), 0) as total_views FROM user_properties WHERE user_id = ?{$tenantWhere}");
            $stmt->execute(array_merge([$userId], $tenantParams));
            $totalViews = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total_views'];
        } catch (\Throwable $e) {
            $totalViews = 0;
        }

        // Total inquiries
        try {
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(inquiries), 0) as total_inquiries FROM user_properties WHERE user_id = ?{$tenantWhere}");
            $stmt->execute(array_merge([$userId], $tenantParams));
            $totalInquiries = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total_inquiries'];
        } catch (\Throwable $e) {
            $totalInquiries = 0;
        }

        // Leads assigned
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM leads WHERE assigned_to = ?{$tenantWhere}");
            $stmt->execute(array_merge([$userId], $tenantParams));
            $totalLeads = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];
        } catch (\Throwable $e) {
            $totalLeads = 0;
        }

        return [
            'subscription' => $subscription,
            'usage' => $usage,
            'listings_by_status' => $listingsByStatus,
            'total_views' => $totalViews,
            'total_inquiries' => $totalInquiries,
            'total_leads' => $totalLeads,
        ];
    }
}