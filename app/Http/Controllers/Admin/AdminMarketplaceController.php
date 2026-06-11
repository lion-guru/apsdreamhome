<?php

namespace App\Http\Controllers\Admin;

class AdminMarketplaceController extends AdminController
{
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

            try {
                $activeListings = (int)($this->db->query("SELECT COUNT(*) FROM user_properties WHERE listing_type = 'sell' AND status = 'approved'")->fetchColumn());
                $pendingApprovals = (int)($this->db->query("SELECT COUNT(*) FROM user_properties WHERE status = 'pending'")->fetchColumn());
                $soldCount = (int)($this->db->query("SELECT COUNT(*) FROM user_properties WHERE status = 'sold'")->fetchColumn());
                $avgPrice = (float)($this->db->query("SELECT COALESCE(AVG(price),0) FROM user_properties WHERE listing_type = 'sell' AND price > 0 AND status IN ('approved','sold')")->fetchColumn());
                $totalViews = (int)($this->db->query("SELECT COALESCE(SUM(views),0) FROM user_properties")->fetchColumn());
                $topLocations = $this->db->query("SELECT COALESCE(location, city_name, 'Unknown') as loc, COUNT(*) as cnt, AVG(price) as avg_price FROM user_properties WHERE listing_type = 'sell' AND status = 'approved' GROUP BY loc ORDER BY cnt DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
                $recentListings = $this->db->query("SELECT up.*, u.name as seller_name FROM user_properties up LEFT JOIN users u ON up.user_id = u.id WHERE up.listing_type = 'sell' ORDER BY up.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                $typeDistribution = $this->db->query("SELECT property_type, COUNT(*) as cnt FROM user_properties WHERE listing_type = 'sell' AND status = 'approved' GROUP BY property_type ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log('AdminMarketplaceController::index() query error: ' . $e->getMessage());
            }

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
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load marketplace');
            $this->redirect('/admin/dashboard');
        }
    }
}
