<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Traits\TenantAwareTrait;

class ListingSettingsController extends AdminController {
    use TenantAwareTrait;

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();

        $settings = $this->db->query(
            "SELECT * FROM listing_settings WHERE tenant_id = ? ORDER BY id ASC",
            [$tid]
        )->fetchAll();

        $packages = $this->db->query(
            "SELECT * FROM listing_packages WHERE tenant_id = ? ORDER BY price ASC",
            [$tid]
        )->fetchAll();

        $totalListings = $this->db->query("SELECT COUNT(*) as cnt FROM user_properties WHERE tenant_id = ?", [$tid])->fetch()['cnt'];
        $featuredListings = $this->db->query("SELECT COUNT(*) as cnt FROM user_properties WHERE is_featured = 1 AND tenant_id = ?", [$tid])->fetch()['cnt'];
        $premiumListings = $this->db->query("SELECT COUNT(*) as cnt FROM user_properties WHERE is_premium = 1 AND tenant_id = ?", [$tid])->fetch()['cnt'];
        $totalInquiries = $this->db->query("SELECT COUNT(*) as cnt FROM property_inquiries WHERE tenant_id = ?", [$tid])->fetch()['cnt'];
        $totalMessages = $this->db->query("SELECT COUNT(*) as cnt FROM property_messages WHERE tenant_id = ?", [$tid])->fetch()['cnt'];

        $this->render('admin.listing-settings.index', compact('settings', 'packages', 'totalListings', 'featuredListings', 'premiumListings', 'totalInquiries', 'totalMessages'));
    }

    public function updateSettings() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $settings = $_POST['settings'] ?? [];

        foreach ($settings as $key => $value) {
            $key = preg_replace('/[^a-z0-9_]/', '', $key);
            $this->db->query(
                "INSERT INTO listing_settings (setting_key, setting_value, tenant_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                [$key, trim($value), $tid]
            );
        }

        $_SESSION['flash_success'] = 'Settings updated successfully';
        header('Location: /admin/listing-settings');
        exit;
    }

    public function updatePackage() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $duration = (int)($_POST['duration_days'] ?? 30);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isPremium = isset($_POST['is_premium']) ? 1 : 0;
        $isUrgent = isset($_POST['is_urgent']) ? 1 : 0;
        $boostScore = (int)($_POST['boost_score'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if ($id > 0) {
            $this->db->query(
                "UPDATE listing_packages SET name=?, description=?, price=?, duration_days=?, is_featured=?, is_premium=?, is_urgent=?, boost_score=?, status=? WHERE id=? AND tenant_id=?",
                [$name, $description, $price, $duration, $isFeatured, $isPremium, $isUrgent, $boostScore, $status, $id, $tid]
            );
        } else {
            $this->db->query(
                "INSERT INTO listing_packages (name, description, price, duration_days, is_featured, is_premium, is_urgent, boost_score, status, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$name, $description, $price, $duration, $isFeatured, $isPremium, $isUrgent, $boostScore, $status, $tid]
            );
        }

        $_SESSION['flash_success'] = 'Package updated successfully';
        header('Location: /admin/listing-settings');
        exit;
    }

    public function inquiries() {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $total = $this->db->query("SELECT COUNT(*) as cnt FROM property_inquiries WHERE tenant_id = ?", [$tid])->fetch()['cnt'];
        $inquiries = $this->db->query(
            "SELECT pi.*, up.name as property_name, up.location as property_location
             FROM property_inquiries pi
             LEFT JOIN user_properties up ON pi.property_id = up.id
             WHERE pi.tenant_id = ?
             ORDER BY pi.created_at DESC LIMIT ? OFFSET ?",
            [$tid, $perPage, $offset]
        )->fetchAll();

        $totalPages = ceil($total / $perPage);

        $this->render('admin.listing-settings.inquiries', compact('inquiries', 'total', 'page', 'totalPages'));
    }
}
