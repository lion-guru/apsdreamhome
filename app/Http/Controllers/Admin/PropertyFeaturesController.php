<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Traits\TenantAwareTrait;

class PropertyFeaturesController extends AdminController
{
    use TenantAwareTrait;

    public function ratings()
    {
        $this->requireAdmin();

        try {
            $ratings = $this->db->fetchAll("
                SELECT r.*, u.name as user_name, u.email as user_email, p.title as property_title
                FROM property_ratings r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN properties p ON r.property_id = p.id
                ORDER BY r.created_at DESC
            ") ?: [];
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        $reviews = $this->db->fetchAll("
            SELECT r.*, u.name as customer_name, u.email as customer_email, p.title as property_title
            FROM property_reviews r
            LEFT JOIN users u ON r.customer_id = u.id
            LEFT JOIN properties p ON r.property_id = p.id
            ORDER BY r.created_at DESC
        ") ?: [];

        $totalRatings = $this->db->fetch("SELECT COUNT(*) as c, COALESCE(AVG(rating), 0) as avg FROM property_ratings") ?: ['c' => 0, 'avg' => 0];
        $pendingReviews = $this->db->fetch("SELECT COUNT(*) as c FROM property_reviews WHERE status = 'pending'") ?: ['c' => 0];

        return $this->render('admin/property-features/ratings', [
            'page_title' => 'Ratings & Reviews',
            'ratings' => $ratings,
            'reviews' => $reviews,
            'total_ratings' => $totalRatings['c'] ?? 0,
            'avg_rating' => $totalRatings['avg'] ?? 0,
            'pending_reviews' => $pendingReviews['c'] ?? 0,
        ]);
    }

    public function updateReviewStatus($id)
    {
        $this->requireAdmin();

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['approved', 'rejected'])) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('/admin/property-features/ratings');
        }

        $tid = (int)$this->tenantId();
        $this->db->update('property_reviews', ['status' => $status], ['id' => (int)$id, 'tenant_id' => $tid]);
        $this->setFlash('success', 'Review status updated to ' . $status);
        $this->redirect('/admin/property-features/ratings');
    }

    public function favorites()
    {
        $this->requireAdmin();

        $search = $_GET['search'] ?? '';
        $params = [];
        $where = '';
        if (!empty($search)) {
            $where = "WHERE (p.title LIKE ? OR u.name LIKE ?)";
            $params = ["%$search%", "%$search%"];
        }

        $favorites = $this->db->fetchAll("
            SELECT f.*, u.name as user_name, u.email as user_email, p.title as property_title, p.price as property_price
            FROM property_favorites f
            LEFT JOIN users u ON f.user_id = u.id
            LEFT JOIN properties p ON f.property_id = p.id
            $where
            ORDER BY f.created_at DESC
        ", $params) ?: [];

        return $this->render('admin/property-features/favorites', [
            'page_title' => 'Property Favorites',
            'favorites' => $favorites,
            'search' => $search,
        ]);
    }

    public function maintenance()
    {
        $this->requireAdmin();

        $requests = $this->db->fetchAll("
            SELECT m.*, p.title as property_title, p.location as property_location, u.name as assigned_name
            FROM property_maintenance m
            LEFT JOIN properties p ON m.property_id = p.id
            LEFT JOIN users u ON m.assigned_to = u.id
            ORDER BY m.created_at DESC
        ") ?: [];

        $openCount = $this->db->fetch("SELECT COUNT(*) as c FROM property_maintenance WHERE status = 'open'") ?: ['c' => 0];
        $inProgressCount = $this->db->fetch("SELECT COUNT(*) as c FROM property_maintenance WHERE status = 'in_progress'") ?: ['c' => 0];
        $completedCount = $this->db->fetch("SELECT COUNT(*) as c FROM property_maintenance WHERE status = 'completed'") ?: ['c' => 0];
        $totalCount = $this->db->fetch("SELECT COUNT(*) as c FROM property_maintenance") ?: ['c' => 0];

        return $this->render('admin/property-features/maintenance', [
            'page_title' => 'Property Maintenance',
            'requests' => $requests,
            'total_count' => $totalCount['c'] ?? 0,
            'open_count' => $openCount['c'] ?? 0,
            'in_progress_count' => $inProgressCount['c'] ?? 0,
            'completed_count' => $completedCount['c'] ?? 0,
        ]);
    }

    public function showMaintenance($id)
    {
        $this->requireAdmin();

        $request = $this->db->fetch("
            SELECT m.*, p.title as property_title, p.location as property_location, u.name as assigned_name
            FROM property_maintenance m
            LEFT JOIN properties p ON m.property_id = p.id
            LEFT JOIN users u ON m.assigned_to = u.id
            WHERE m.id = ?
        ", [(int)$id]);

        if (!$request) {
            $this->setFlash('error', 'Maintenance request not found');
            $this->redirect('/admin/property-features/maintenance');
        }

        [$tidSql, $tidParams] = $this->tenantWhere();
        $staff = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('employee', 'admin'){$tidSql} ORDER BY name", $tidParams) ?: [];

        return $this->render('admin/property-features/maintenance-show', [
            'page_title' => 'Maintenance #' . $id,
            'request' => $request,
            'staff' => $staff,
        ]);
    }

    public function updateMaintenanceStatus($id)
    {
        $this->requireAdmin();

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['open', 'in_progress', 'completed'])) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('/admin/property-features/maintenance/' . $id);
        }

        $data = ['status' => $status];
        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        $tid = (int)$this->tenantId();
        $this->db->update('property_maintenance', $data, ['id' => (int)$id, 'tenant_id' => $tid]);
        $this->setFlash('success', 'Maintenance status updated');
        $this->redirect('/admin/property-features/maintenance/' . $id);
    }

    public function assignMaintenance($id)
    {
        $this->requireAdmin();

        $assignedTo = (int)($_POST['assigned_to'] ?? 0);
        if ($assignedTo <= 0) {
            $this->setFlash('error', 'Please select a staff member');
            $this->redirect('/admin/property-features/maintenance/' . $id);
        }

        $tid = (int)$this->tenantId();
        $this->db->update('property_maintenance', ['assigned_to' => $assignedTo], ['id' => (int)$id, 'tenant_id' => $tid]);
        $this->setFlash('success', 'Maintenance request assigned');
        $this->redirect('/admin/property-features/maintenance/' . $id);
    }

    public function marketData()
    {
        $this->requireAdmin();

        $filterLocation = $_GET['location'] ?? '';
        $filterType = $_GET['type'] ?? '';

        $where = [];
        $params = [];
        if (!empty($filterLocation)) {
            $where[] = "m.location = ?";
            $params[] = $filterLocation;
        }
        if (!empty($filterType)) {
            $where[] = "m.property_type = ?";
            $params[] = $filterType;
        }
        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : '';

        $entries = $this->db->fetchAll("
            SELECT m.* FROM property_market_data m
            $whereClause
            ORDER BY m.data_date DESC
        ", $params) ?: [];

        $locations = $this->db->fetchAll("SELECT DISTINCT location FROM property_market_data ORDER BY location") ?: [];
        $types = $this->db->fetchAll("SELECT DISTINCT property_type FROM property_market_data ORDER BY property_type") ?: [];

        return $this->render('admin/property-features/market-data', [
            'page_title' => 'Market Data',
            'entries' => $entries,
            'locations' => $locations,
            'types' => $types,
            'filter_location' => $filterLocation,
            'filter_type' => $filterType,
        ]);
    }

    public function storeMarketData()
    {
        $this->requireAdmin();

        $data = [
            'location' => $_POST['location'] ?? '',
            'property_type' => $_POST['property_type'] ?? '',
            'data_date' => $_POST['data_date'] ?? date('Y-m-d'),
            'avg_price_per_sqft' => $_POST['avg_price_per_sqft'] ?? null,
            'median_price' => $_POST['median_price'] ?? null,
            'price_trend_percentage' => $_POST['price_trend_percentage'] ?? null,
            'days_on_market_avg' => $_POST['days_on_market_avg'] ?? null,
            'inventory_count' => $_POST['inventory_count'] ?? null,
            'sales_volume' => $_POST['sales_volume'] ?? null,
            'rental_yield_avg' => $_POST['rental_yield_avg'] ?? null,
            'market_sentiment' => $_POST['market_sentiment'] ?? 'neutral',
            'confidence_score' => $_POST['confidence_score'] ?? 75.0,
            'data_source' => $_POST['data_source'] ?? 'internal',
        ];

        if (empty($data['location']) || empty($data['property_type'])) {
            $this->setFlash('error', 'Location and property type are required');
            $this->redirect('/admin/property-features/market-data');
        }

        $tid = (int)$this->tenantId();
        $data['tenant_id'] = $tid;
        $this->db->insert('property_market_data', $data);
        $this->setFlash('success', 'Market data entry added');
        $this->redirect('/admin/property-features/market-data');
    }

    public function analytics()
    {
        $this->requireAdmin();

        $analytics = $this->db->fetchAll("
            SELECT a.*, p.title as property_title, p.type as property_type, p.location as property_location, p.price as property_price
            FROM property_analytics a
            LEFT JOIN properties p ON a.property_id = p.id
            ORDER BY a.views DESC
        ") ?: [];

        $totalViews = $this->db->fetch("SELECT COALESCE(SUM(views), 0) as c FROM property_analytics") ?: ['c' => 0];
        $totalInquiries = $this->db->fetch("SELECT COALESCE(SUM(inquiries), 0) as c FROM property_analytics") ?: ['c' => 0];
        $totalFavorites = $this->db->fetch("SELECT COALESCE(SUM(favorites), 0) as c FROM property_analytics") ?: ['c' => 0];
        $totalShares = $this->db->fetch("SELECT COALESCE(SUM(shares), 0) as c FROM property_analytics") ?: ['c' => 0];

        $topFavorited = $this->db->fetchAll("
            SELECT p.id, p.title, COUNT(f.id) as total
            FROM property_favorites f
            JOIN properties p ON f.property_id = p.id
            GROUP BY p.id, p.title
            ORDER BY total DESC
            LIMIT 10
        ") ?: [];

        $mostViewed = $this->db->fetchAll("
            SELECT p.id, p.title, a.views
            FROM property_analytics a
            JOIN properties p ON a.property_id = p.id
            ORDER BY a.views DESC
            LIMIT 10
        ") ?: [];

        return $this->render('admin/property-features/analytics', [
            'page_title' => 'Property Analytics',
            'analytics' => $analytics,
            'total_views' => $totalViews['c'] ?? 0,
            'total_inquiries' => $totalInquiries['c'] ?? 0,
            'total_favorites' => $totalFavorites['c'] ?? 0,
            'total_shares' => $totalShares['c'] ?? 0,
            'top_favorited' => $topFavorited,
            'most_viewed' => $mostViewed,
        ]);
    }
}
