<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class MarketplaceController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE up.status = 'approved'";
        $params = [];

        if (!empty($_GET['type'])) {
            $where .= " AND up.property_type = ?";
            $params[] = $_GET['type'];
        }
        if (!empty($_GET['listing_type'])) {
            $where .= " AND up.listing_type = ?";
            $params[] = $_GET['listing_type'];
        }
        if (!empty($_GET['min_price'])) {
            $where .= " AND up.price >= ?";
            $params[] = (float)$_GET['min_price'];
        }
        if (!empty($_GET['max_price'])) {
            $where .= " AND up.price <= ?";
            $params[] = (float)$_GET['max_price'];
        }
        if (!empty($_GET['location'])) {
            $loc = '%' . $_GET['location'] . '%';
            $where .= " AND (up.city_name LIKE ? OR up.location LIKE ? OR up.address LIKE ?)";
            $params[] = $loc;
            $params[] = $loc;
            $params[] = $loc;
        }

        try {
            // Premium listings (premium/featured/urgent) — shown in separate section, no pagination
            $premiumWhere = $where . " AND (up.is_premium = 1 OR up.is_featured = 1 OR up.is_urgent = 1)";
            $premiumListings = $this->db->fetchAll(
                "SELECT up.*, s.name as state_name, d.name as district_name
                 FROM user_properties up
                 LEFT JOIN states s ON up.state_id = s.id
                 LEFT JOIN districts d ON up.district_id = d.id
                 $premiumWhere
                 ORDER BY up.is_premium DESC, up.is_featured DESC, up.created_at DESC
                 LIMIT 6",
                $params
            );

            // Regular listings (non-premium) — paginated
            $regularWhere = $where . " AND (up.is_premium = 0 AND up.is_featured = 0 AND up.is_urgent = 0)";
            $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM user_properties up $regularWhere", $params);
            $totalPages = max(1, ceil($total / $perPage));

            $regularListings = $this->db->fetchAll(
                "SELECT up.*, s.name as state_name, d.name as district_name
                 FROM user_properties up
                 LEFT JOIN states s ON up.state_id = s.id
                 LEFT JOIN districts d ON up.district_id = d.id
                 $regularWhere
                 ORDER BY up.created_at DESC
                 LIMIT $perPage OFFSET $offset",
                $params
            );

            // Get active premium packages for info banner
            $packages = $this->db->fetchAll(
                "SELECT * FROM premium_packages WHERE is_active = 1 ORDER BY priority_order ASC"
            );
        } catch (\Exception $e) {
            $premiumListings = [];
            $regularListings = [];
            $packages = [];
            $total = 0;
            $totalPages = 1;
            error_log('MarketplaceController error: ' . $e->getMessage());
        }

        $data = [
            'page_title' => 'Property Marketplace - APS Dream Home',
            'page_description' => 'Browse properties listed by owners. Plots, houses, flats, shops for sale, rent, or lease.',
            'premiumListings' => $premiumListings,
            'listings' => $regularListings,
            'packages' => $packages,
            'filters' => $_GET,
            'total' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ];

        $this->render('pages/marketplace', $data);
    }

    public function detail($id)
    {
        try {
            $prop = $this->db->fetch(
                "SELECT up.*, s.name as state_name, d.name as district_name
                 FROM user_properties up
                 LEFT JOIN states s ON up.state_id = s.id
                 LEFT JOIN districts d ON up.district_id = d.id
                 WHERE up.id = ? AND up.status = 'approved'",
                [$id]
            );
            if (!$prop) {
                header('HTTP/1.0 404 Not Found');
                echo '<h2>Listing not found</h2>';
                exit;
            }
            $this->db->execute("UPDATE user_properties SET views = views + 1 WHERE id = ?", [$prop['tenant_id'] ?? $id]);
        } catch (\Exception $e) {
            error_log('Marketplace detail error: ' . $e->getMessage());
            header('HTTP/1.0 404 Not Found');
            echo '<h2>Listing not found</h2>';
            exit;
        }

        $this->render('pages/marketplace-detail', [
            'page_title' => $prop['name'] . ' - APS Dream Home',
            'property' => $prop,
        ]);
    }
}
