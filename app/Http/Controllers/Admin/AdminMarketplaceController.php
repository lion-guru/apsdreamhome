<?php

namespace App\Http\Controllers\Admin;

use \App\Traits\TenantAwareTrait;

class AdminMarketplaceController extends AdminController
{
    use TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        try {
            $activeListings = 0;
            $pendingApprovals = 0;
            $soldCount = 0;
            $avgPrice = 0;
            $totalViews = 0;
            $topLocations = [];
            $recentListings = [];
            $typeDistribution = [];
            $premiumStats = [];
            $featuredListings = [];
            $urgentListings = [];

            // Transaction & Revenue Stats
            $boostTransactions = 0;
            $boostRevenue = 0;
            $transactionFees = 0;
            $totalPlatformRevenue = 0;
            $recentBoostTransactions = [];

            try {
                $activeListings = (int)($this->db->query("SELECT COUNT(*) FROM user_properties WHERE listing_type = 'sell' AND status = 'approved'")->fetchColumn());
                $pendingApprovals = (int)($this->db->query("SELECT COUNT(*) FROM user_properties WHERE status = 'pending'")->fetchColumn());
                $soldCount = (int)($this->db->query("SELECT COUNT(*) FROM user_properties WHERE status = 'sold'")->fetchColumn());
                $avgPrice = (float)($this->db->query("SELECT COALESCE(AVG(price),0) FROM user_properties WHERE listing_type = 'sell' AND price > 0 AND status IN ('approved','sold')")->fetchColumn());
                $totalViews = (int)($this->db->query("SELECT COALESCE(SUM(views),0) FROM user_properties")->fetchColumn());
                $topLocations = $this->db->query("SELECT COALESCE(location, city_name, 'Unknown') as loc, COUNT(*) as cnt, AVG(price) as avg_price FROM user_properties WHERE listing_type = 'sell' AND status = 'approved' GROUP BY loc ORDER BY cnt DESC LIMIT 6")->fetchAll(\PDO::FETCH_ASSOC);
                $recentListings = $this->db->query("SELECT up.*, u.name as seller_name FROM user_properties up LEFT JOIN users u ON up.user_id = u.id WHERE up.listing_type = 'sell' ORDER BY up.created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
                $typeDistribution = $this->db->query("SELECT property_type, COUNT(*) as cnt FROM user_properties WHERE listing_type = 'sell' AND status = 'approved' GROUP BY property_type ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC);
                $premiumStats = [
                    'featured' => (int)($this->db->query("SELECT COUNT(*) FROM user_properties WHERE is_featured = 1 AND status = 'approved'")->fetchColumn()),
                    'urgent' => (int)($this->db->query("SELECT COUNT(*) FROM user_properties WHERE is_urgent = 1 AND status = 'approved'")->fetchColumn()),
                    'premium' => (int)($this->db->query("SELECT COUNT(*) FROM user_properties WHERE is_premium = 1 AND status = 'approved'")->fetchColumn()),
                    'packages_active' => (int)($this->db->query("SELECT COUNT(*) FROM user_packages WHERE status = 'active'")->fetchColumn()),
                    'package_revenue' => (float)($this->db->query("SELECT COALESCE(SUM(amount_paid),0) FROM user_packages WHERE status != 'cancelled'")->fetchColumn()),
                ];
                $featuredListings = $this->db->query("SELECT up.*, u.name as seller_name FROM user_properties up LEFT JOIN users u ON up.user_id = u.id WHERE up.is_featured = 1 AND up.status = 'approved' ORDER BY up.updated_at DESC LIMIT 5")->fetchAll(\PDO::FETCH_ASSOC);
                $urgentListings = $this->db->query("SELECT up.*, u.name as seller_name FROM user_properties up LEFT JOIN users u ON up.user_id = u.id WHERE up.is_urgent = 1 AND up.status = 'approved' ORDER BY up.updated_at DESC LIMIT 5")->fetchAll(\PDO::FETCH_ASSOC);

                // Transaction & Revenue Stats
                [$tWhere, $tParams] = $this->tenantWhere();
                $boostTransactions = (int)$this->db->prepare("SELECT COUNT(*) FROM platform_revenue WHERE source_type = 'boost' $tWhere")->execute(array_merge([], $tParams)) ? (int)$this->db->prepare("SELECT COUNT(*) FROM platform_revenue WHERE source_type = 'boost' $tWhere")->fetchColumn() : 0;
                $boostRevenue = (float)$this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM platform_revenue WHERE source_type = 'boost' $tWhere")->execute(array_merge([], $tParams)) ? (float)$this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM platform_revenue WHERE source_type = 'boost' $tWhere")->fetchColumn() : 0;
                $transactionFees = (float)$this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM platform_revenue WHERE source_type IN ('resell_transaction','user_property_transaction') $tWhere")->execute(array_merge([], $tParams)) ? (float)$this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM platform_revenue WHERE source_type IN ('resell_transaction','user_property_transaction') $tWhere")->fetchColumn() : 0;
                $totalPlatformRevenue = (float)$this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM platform_revenue $tWhere")->execute(array_merge([], $tParams)) ? (float)$this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM platform_revenue $tWhere")->fetchColumn() : 0;

                // Recent boost transactions
                $recentBoostTransactions = $this->db->prepare("SELECT pr.*, up.name as property_name, u.name as seller_name FROM platform_revenue pr LEFT JOIN user_properties up ON pr.source_id = up.id LEFT JOIN users u ON up.user_id = u.id WHERE pr.source_type = 'boost' $tWhere ORDER BY pr.recorded_at DESC LIMIT 10")->execute(array_merge([], $tParams)) ? $this->db->prepare("SELECT pr.*, up.name as property_name, u.name as seller_name FROM platform_revenue pr LEFT JOIN user_properties up ON pr.source_id = up.id LEFT JOIN users u ON up.user_id = u.id WHERE pr.source_type = 'boost' $tWhere ORDER BY pr.recorded_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) : [];

            } catch (\Exception $e) {
                error_log('AdminMarketplaceController::index() query error: ' . $e->getMessage());
            }

            // Builder Management Stats
            $builderStats = $this->getBuilderStats();

            $this->render('admin/marketplace/index', [
                'page_title' => 'Marketplace - APS Dream Home',
                'page_heading' => 'Marketplace',
                'activeListings' => $activeListings,
                'pendingApprovals' => $pendingApprovals,
                'soldCount' => $soldCount,
                'avgPrice' => $avgPrice,
                'totalViews' => $totalViews,
                'topLocations' => $topLocations,
                'recentListings' => $recentListings,
                'typeDistribution' => $typeDistribution,
                'premiumStats' => $premiumStats,
                'featuredListings' => $featuredListings,
                'urgentListings' => $urgentListings,
                // Transaction & Revenue
                'boostTransactions' => $boostTransactions,
                'boostRevenue' => $boostRevenue,
                'transactionFees' => $transactionFees,
                'totalPlatformRevenue' => $totalPlatformRevenue,
                'recentBoostTransactions' => $recentBoostTransactions,
                // Builder Stats
                'builderStats' => $builderStats,
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load marketplace');
            $this->redirect('/admin/dashboard');
        }
    }

    private function getBuilderStats(): array
    {
        $stats = [
            'total_builders' => 0,
            'active_subscriptions' => 0,
            'total_listings_by_builders' => 0,
            'subscription_revenue' => 0,
            'top_builders' => [],
        ];

        try {
            [$tWhere, $tParams] = $this->tenantWhere();
            
            $stats['total_builders'] = (int)$this->db->prepare("SELECT COUNT(DISTINCT user_id) FROM user_properties WHERE listing_type = 'sell' $tWhere")->execute(array_merge([], $tParams)) ? (int)$this->db->prepare("SELECT COUNT(DISTINCT user_id) FROM user_properties WHERE listing_type = 'sell' $tWhere")->fetchColumn() : 0;
            
            $stats['active_subscriptions'] = (int)$this->db->prepare("SELECT COUNT(*) FROM builder_subscriptions WHERE status = 'active' $tWhere")->execute(array_merge([], $tParams)) ? (int)$this->db->prepare("SELECT COUNT(*) FROM builder_subscriptions WHERE status = 'active' $tWhere")->fetchColumn() : 0;
            
            $stats['total_listings_by_builders'] = (int)$this->db->prepare("SELECT COUNT(*) FROM user_properties WHERE listing_type = 'sell' $tWhere")->execute(array_merge([], $tParams)) ? (int)$this->db->prepare("SELECT COUNT(*) FROM user_properties WHERE listing_type = 'sell' $tWhere")->fetchColumn() : 0;
            
            $stats['subscription_revenue'] = (float)$this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM platform_revenue WHERE source_type = 'builder_subscription' $tWhere")->execute(array_merge([], $tParams)) ? (float)$this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM platform_revenue WHERE source_type = 'builder_subscription' $tWhere")->fetchColumn() : 0;

            // Top builders by listing count
            $stats['top_builders'] = $this->db->prepare("SELECT u.name, u.email, COUNT(up.id) as listing_count, COALESCE(SUM(up.price),0) as total_value FROM users u JOIN user_properties up ON u.id = up.user_id WHERE up.listing_type = 'sell' $tWhere GROUP BY u.id ORDER BY listing_count DESC LIMIT 10")->execute(array_merge([], $tParams)) ? $this->db->prepare("SELECT u.name, u.email, COUNT(up.id) as listing_count, COALESCE(SUM(up.price),0) as total_value FROM users u JOIN user_properties up ON u.id = up.user_id WHERE up.listing_type = 'sell' $tWhere GROUP BY u.id ORDER BY listing_count DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) : [];

        } catch (\Exception $e) {
            error_log('AdminMarketplaceController::getBuilderStats() error: ' . $e->getMessage());
        }

        return $stats;
    }

    public function toggleFeatured()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            [$where, $params] = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT is_featured FROM user_properties WHERE id = ? $where");
            $stmt->execute(array_merge([$id], $params));
            $current = (int)$stmt->fetchColumn();
            $new = $current ? 0 : 1;
            $stmt = $this->db->prepare("UPDATE user_properties SET is_featured = ? WHERE id = ? $where");
            $stmt->execute(array_merge([$new, $id], $params));
            $this->setFlash('success', $new ? 'Property marked as featured' : 'Featured removed');
        }
        $this->redirect('/admin/marketplace');
    }

    public function toggleUrgent()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            [$where, $params] = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT is_urgent FROM user_properties WHERE id = ? $where");
            $stmt->execute(array_merge([$id], $params));
            $current = (int)$stmt->fetchColumn();
            $new = $current ? 0 : 1;
            $stmt = $this->db->prepare("UPDATE user_properties SET is_urgent = ? WHERE id = ? $where");
            $stmt->execute(array_merge([$new, $id], $params));
            $this->setFlash('success', $new ? 'Property marked as urgent' : 'Urgent removed');
        }
        $this->redirect('/admin/marketplace');
    }
}
