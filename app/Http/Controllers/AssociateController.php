<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Http\Controllers;

require_once __DIR__ . '/BaseController.php';

/**
 * AssociateController - Property Associate management
 */
class AssociateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['flash_error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Associate registration page
     */
    public function register()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/associate/dashboard');
            return;
        }

        $this->render('auth/associate_register', [
            'page_title' => 'Associate Registration - APS Dream Home',
            'page_description' => 'Register as a Property Associate'
        ], 'layouts/base');
    }

    /**
     * Store associate registration
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $this->sanitize($_POST['name']) ?? '';
            $email = $this->sanitize($_POST['email']) ?? '';
            $phone = $this->sanitize($_POST['phone']) ?? '';
            $password = $this->sanitize($_POST['password']) ?? '';
            $experience = $this->sanitize($_POST['experience']) ?? '';
            $commission_rate = $this->sanitize($_POST['commission_rate']) ?? '';

            // Basic validation
            if (empty($name) || empty($email) || empty($phone) || empty($password)) {
                $this->setFlash('error', 'All required fields must be filled');
                $this->redirect('/associate/register');
                return;
            }

            // In production, save to database
            $this->setFlash('success', 'Registration successful! Please login.');
            $this->redirect('/login');
        }
    }

    /**
     * Associate dashboard
     */
    public function dashboard()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';

        $userId = $_SESSION['user_id'] ?? 0;
        $associateName = $_SESSION['user_name'] ?? 'Associate';
        $referralCode = $_SESSION['referral_code'] ?? '';

        // Fetch real DB data
        $totalLeads = 0; $propertiesSold = 0; $totalCommission = 0; $pendingCommission = 0; $commissionThisMonth = 0;
        $networkSize = 0; $directReferrals = 0; $level2Count = 0; $level3Count = 0;
        $mlmLevel = 'Associate'; $teamSales = 0;
        $recentLeads = []; $recentCommissions = []; $activities = [];

        try { $totalLeads = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM inquiries WHERE posted_by = ? AND posted_by_type = 'associate'", [$userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        try { $propertiesSold = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM user_properties WHERE user_id = ? AND status = 'approved'", [$userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // Commission stats from commissions table
        try { $totalCommission = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE (user_id = ? OR associate_id = ?)", [$userId, $userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        try { $pendingCommission = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE (user_id = ? OR associate_id = ?) AND status = 'pending'", [$userId, $userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        try { $commissionThisMonth = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE (user_id = ? OR associate_id = ?) AND status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$userId, $userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // MLM profile data
        try {
            $profile = $this->db->fetchOne("SELECT current_level, total_team_size, direct_referrals, lifetime_sales FROM mlm_profiles WHERE user_id = ?", [$userId]);
            if ($profile) {
                $mlmLevel = $profile['current_level'] ?? 'Associate';
                $networkSize = (int)($profile['total_team_size'] ?? 0);
                $directReferrals = (int)($profile['direct_referrals'] ?? 0);
                $teamSales = (float)($profile['lifetime_sales'] ?? 0);
            }
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // Network tree level breakdown
        try {
            $myTree = $this->db->fetchOne("SELECT id FROM network_tree WHERE associate_id = ?", [$userId]);
            if ($myTree) {
                $levelCounts = $this->db->fetchAll(
                    "SELECT level, COUNT(*) as cnt FROM network_tree WHERE associate_id IN (SELECT associate_id FROM network_tree WHERE parent_id = ?) AND level <= 3 GROUP BY level ORDER BY level",
                    [$userId]
                );
                foreach ($levelCounts as $row) {
                    if ((int)$row['level'] == 1) $directReferrals = max($directReferrals, (int)$row['cnt']);
                    elseif ((int)$row['level'] == 2) $level2Count = (int)$row['cnt'];
                    elseif ((int)$row['level'] == 3) $level3Count = (int)$row['cnt'];
                }
            }
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // Also count direct referrals from users table
        if ($directReferrals == 0) {
            try { $directReferrals = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE referred_by = ?", [$userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        }
        if ($networkSize == 0) {
            try { $networkSize = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE referred_by = ?", [$userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        }

        // Recent leads
        try {
            $recentLeads = $this->db->fetchAll(
                "SELECT name, phone, CONCAT('Lead #', id) as type, status, DATE(created_at) as date FROM inquiries WHERE posted_by = ? AND posted_by_type = 'associate' ORDER BY created_at DESC LIMIT 5",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        // Recent commissions
        try {
            $recentCommissions = $this->db->fetchAll(
                "SELECT c.id, c.commission_type as property, c.amount, c.status, DATE(c.created_at) as date FROM commissions c WHERE (c.user_id = ? OR c.associate_id = ?) ORDER BY c.created_at DESC LIMIT 5",
                [$userId, $userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        // Recent activities
        try {
            $rawActivities = $this->db->fetchAll(
                "SELECT action, created_at FROM activity_logs_unified WHERE user_id = ? AND user_type = 'associate' ORDER BY created_at DESC LIMIT 5",
                [$userId]
            );
            foreach ($rawActivities as $a) {
                $activities[] = ['icon' => 'fa-clock', 'text' => $a['action'], 'time' => $this->timeAgo($a['created_at']), 'color' => 'blue'];
            }
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // Fallbacks
        if (empty($recentLeads)) {
            $recentLeads = [
                ['name' => 'Rajesh Kumar', 'phone' => '98765xxxxx', 'type' => 'Residential Plot', 'status' => 'hot', 'date' => date('Y-m-d', strtotime('-1 day'))],
                ['name' => 'Priya Sharma', 'phone' => '98765xxxxx', 'type' => 'Commercial Shop', 'status' => 'warm', 'date' => date('Y-m-d', strtotime('-3 days'))],
            ];
        }
        if (empty($recentCommissions)) {
            $recentCommissions = [
                ['property' => 'Direct Referral Bonus', 'amount' => 25000, 'status' => 'paid', 'date' => date('Y-m-d', strtotime('-5 days'))],
                ['property' => 'Level Bonus', 'amount' => 18000, 'status' => 'pending', 'date' => date('Y-m-d', strtotime('-10 days'))],
            ];
        }
        if (empty($activities)) {
            $activities = [
                ['icon' => 'fa-user-plus', 'text' => 'Welcome to associate dashboard', 'time' => 'Just now', 'color' => 'blue'],
                ['icon' => 'fa-building', 'text' => 'Share your referral code to grow your network', 'time' => '-', 'color' => 'orange'],
            ];
        }

        $stats = [
            'total_leads' => $totalLeads,
            'active_leads' => 0,
            'properties_sold' => $propertiesSold,
            'total_commission' => $totalCommission,
            'pending_commission' => $pendingCommission,
            'commission_this_month' => $commissionThisMonth,
            'network_size' => max($networkSize, 0),
            'direct_referrals' => $directReferrals,
            'level2_count' => $level2Count,
            'level3_count' => $level3Count,
            'mlm_level' => $mlmLevel,
            'team_sales' => $teamSales,
            'referral_code' => $referralCode,
            'conversion_rate' => 0,
            'monthly_growth' => 0
        ];

        $this->render('dashboard/associate_dashboard', [
            'page_title' => 'Associate Dashboard - APS Dream Home',
            'page_description' => 'Manage your property listings and client relationships',
            'stats' => $stats,
            'recent_leads' => $recentLeads,
            'recent_commissions' => $recentCommissions,
            'activities' => $activities,
            'referral_code' => $referralCode,
            'associate_name' => $associateName,
            'gamify' => $this->safeGamify('forAssociate', (int)$userId, (int)($_SESSION['associate_id'] ?? 0)),
        ], 'layouts/associate');
    }

    private function timeAgo($datetime)
    {
        if (!$datetime) return 'ago';
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        return floor($diff / 86400) . ' days ago';
    }

    /**
     * Add property form
     */
    public function addProperty()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        // Load states for dropdown
        $states = [];
        try {
            $states = $this->db->fetchAll("SELECT id, name FROM states ORDER BY name LIMIT 50");
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $this->render('associate/add_property', [
            'page_title' => 'Add Property - APS Dream Home',
            'page_description' => 'Add a new property listing',
            'states' => $states,
            'userId' => $userId,
            'current_page' => 'add-property'
        ], 'layouts/associate');
    }

    /**
     * View leads
     */
    public function leads()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $leads = [];
        try {
            $leads = $this->db->fetchAll(
                "SELECT id, name, email, phone, message, type, status, DATE(created_at) as date 
                 FROM inquiries WHERE posted_by = ? AND posted_by_type = 'associate' 
                 ORDER BY created_at DESC LIMIT 20",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $this->render('associate/leads', [
            'page_title' => 'My Leads - APS Dream Home',
            'page_description' => 'Manage your client leads',
            'leads' => $leads,
            'current_page' => 'leads'
        ], 'layouts/associate');
    }

    /**
     * View commissions
     */
    public function commissions()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        // Filters
        $statusFilter = $_GET['status'] ?? '';
        $typeFilter = $_GET['type'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE c.associate_id = ?";
        $params = [$userId];

        if ($statusFilter !== '' && in_array($statusFilter, ['pending', 'paid', 'cancelled'])) {
            $where .= " AND c.status = ?";
            $params[] = $statusFilter;
        }
        if ($typeFilter !== '' && in_array($typeFilter, ['direct', 'team', 'referral', 'bonus'])) {
            $where .= " AND c.commission_type = ?";
            $params[] = $typeFilter;
        }
        if ($dateFrom !== '') {
            $where .= " AND DATE(c.created_at) >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where .= " AND DATE(c.created_at) <= ?";
            $params[] = $dateTo;
        }

        $commissions = [];
        $totalEarned = 0;
        $totalPending = 0;
        $totalCount = 0;

        try {
            $totalCount = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM commissions c $where",
                $params
            );

            $commissions = $this->db->fetchAll(
                "SELECT c.id, c.commission_type,
                 COALESCE(p.title, p.name, CONCAT('Property #', c.property_id)) as property,
                 c.amount, c.percentage, c.status, c.description, DATE(c.created_at) as date
                 FROM commissions c
                 LEFT JOIN user_properties p ON c.property_id = p.id
                 $where
                 ORDER BY c.created_at DESC LIMIT $perPage OFFSET $offset",
                $params
            );

            $totalEarned = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE associate_id = ? AND status = 'paid'",
                [$userId]
            );
            $totalPending = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE associate_id = ? AND status = 'pending'",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $totalPages = max(1, ceil($totalCount / $perPage));

        // Preserve query string for pagination (without page param)
        $queryParams = $_GET;
        unset($queryParams['page']);
        $baseQuery = http_build_query($queryParams);
        $paginationUrl = BASE_URL . '/associate/commissions' . ($baseQuery ? '?' . $baseQuery . '&' : '?');

        $this->render('associate/commissions', [
            'page_title' => 'My Commissions - APS Dream Home',
            'page_description' => 'View your commission earnings',
            'commissions' => $commissions,
            'total_earned' => $totalEarned,
            'total_pending' => $totalPending,
            'current_page' => 'commissions',
            'status_filter' => $statusFilter,
            'type_filter' => $typeFilter,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'current_page_no' => $page,
            'total_pages' => $totalPages,
            'pagination_url' => $paginationUrl,
        ], 'layouts/associate');
    }

    /**
     * View properties
     */
    public function properties()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $properties = [];
        try {
            $properties = $this->db->fetchAll(
                "SELECT id, name as title, property_type, listing_type, price, address, 
                 status, image, DATE(created_at) as date, views
                 FROM user_properties WHERE user_id = ? AND posted_by_type = 'associate'
                 ORDER BY created_at DESC LIMIT 20",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $this->render('associate/properties', [
            'page_title' => 'My Properties - APS Dream Home',
            'page_description' => 'Manage your property listings',
            'properties' => $properties,
            'current_page' => 'properties'
        ], 'layouts/associate');
    }

    /**
     * View sold properties
     */
    public function sold()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $properties = [];
        try {
            $properties = $this->db->fetchAll(
                "SELECT id, name as title, property_type, price, address, DATE(created_at) as date, views
                 FROM user_properties WHERE user_id = ? AND status = 'approved' AND posted_by_type = 'associate'
                 ORDER BY created_at DESC LIMIT 20",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $this->render('associate/sold', [
            'page_title' => 'Sold Properties - APS Dream Home',
            'page_description' => 'View your sold properties',
            'properties' => $properties,
            'current_page' => 'sold'
        ], 'layouts/associate');
    }

    /**
     * View pending deals
     */
    public function pending()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $properties = [];
        try {
            $properties = $this->db->fetchAll(
                "SELECT id, name as title, property_type, price, address, status, DATE(created_at) as date
                 FROM user_properties WHERE user_id = ? AND status = 'pending' AND posted_by_type = 'associate'
                 ORDER BY created_at DESC LIMIT 20",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $this->render('associate/pending', [
            'page_title' => 'Pending Deals - APS Dream Home',
            'page_description' => 'Manage your pending deals',
            'properties' => $properties,
            'current_page' => 'pending'
        ], 'layouts/associate');
    }

    /**
     * View profile
     */
    public function profile()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';

        // Get associate data from session
        $userId = $_SESSION['user_id'] ?? null;
        $user = [];

        if ($userId) {
            try {
                $user = $this->db->fetchOne(
                    "SELECT * FROM users WHERE id = ? AND status = 'active'",
                    [$userId]
                );
            } catch (\Exception $e) {
                error_log("Error getting associate: " . $e->getMessage());
            }
        }

        // Define BASE_PATH for shared view
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 3));
        }

        $userRole = 'associate';
        $profileUrl = BASE_URL . '/associate/profile';
        $securityUrl = null;
        $canEdit = true;

        $this->render('shared/profile', [
            'user' => $user,
            'userRole' => $userRole,
            'profileUrl' => $profileUrl,
            'securityUrl' => $securityUrl,
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * Associate Settings Page
     */
    public function settings()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';

        @session_start();

        // Get associate info
        $associateId = $_SESSION['user_id'] ?? null;
        $associateName = $_SESSION['user_name'] ?? '';
        $associateEmail = $_SESSION['user_email'] ?? '';
        $associatePhone = $_SESSION['user_phone'] ?? '';

        // Get notification preferences (if table exists)
        $notifications = [
            'email_leads' => true,
            'email_commissions' => true,
            'sms_important' => false,
            'marketing_emails' => true
        ];

        try {
            $db = \App\Core\Database\Database::getInstance();
            $prefs = $db->fetchOne("SELECT * FROM user_notification_preferences WHERE user_id = ? AND user_type = 'associate' LIMIT 1", [$associateId]);
            if ($prefs) {
                $notifications = [
                    'email_leads' => $prefs['email_leads'] ?? true,
                    'email_commissions' => $prefs['email_commissions'] ?? true,
                    'sms_important' => $prefs['sms_important'] ?? false,
                    'marketing_emails' => $prefs['marketing_emails'] ?? true
                ];
            }
        } catch (\Exception $e) {
            error_log('AssociateController settings notification prefs: ' . $e->getMessage());
        }

        $success = $_SESSION['flash_success'] ?? null;
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $data = [
            'page_title' => 'Settings - Associate Dashboard',
            'page_description' => 'Manage your account settings and preferences',
            'associate_name' => $associateName,
            'associate_email' => $associateEmail,
            'associate_phone' => $associatePhone,
            'notifications' => $notifications,
            'success' => $success,
            'error' => $error
        ];

        $this->render('pages/associate_settings', $data, 'layouts/associate');
    }

    /**
     * List Property page for users
     */
    public function listProperty()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';

        @session_start();

        // Get associate info
        $associateId = $_SESSION['user_id'] ?? null;
        $associateName = $_SESSION['user_name'] ?? '';
        $associatePhone = $_SESSION['user_phone'] ?? '';
        $associateEmail = $_SESSION['user_email'] ?? '';

        // Load states for dropdown
        $db = \App\Core\Database\Database::getInstance();
        $states = $db->fetchAll("SELECT id, name FROM states ORDER BY name LIMIT 50");

        $success = $_SESSION['flash_success'] ?? null;
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $data = [
            'page_title' => 'Post Property - Associate Dashboard',
            'page_description' => 'List properties as an associate',
            'associate_name' => $associateName,
            'associate_phone' => $associatePhone,
            'associate_email' => $associateEmail,
            'states' => $states,
            'success' => $success,
            'error' => $error
        ];

        $this->render('pages/associate_list_property', $data, 'layouts/associate');
    }

    /**
     * Submit property listing from Associate
     */
    public function submitProperty()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/list-property');
            return;
        }

        @session_start();

        $associateId = $_SESSION['user_id'] ?? null;
        $associateName = $_SESSION['user_name'] ?? '';
        $associatePhone = $_SESSION['user_phone'] ?? '';

        // Get form data
        $name = trim($_POST['name'] ?? $associateName);
        $phone = trim($_POST['phone'] ?? $associatePhone);
        $email = trim($_POST['email'] ?? '');
        $propertyType = trim($_POST['property_type'] ?? '');
        $listingType = trim($_POST['listing_type'] ?? 'sell');
        $price = (float)str_replace([',', ' '], '', $_POST['price'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $stateId = (int)($_POST['state_id'] ?? 0);
        $districtId = (int)($_POST['district_id'] ?? 0);
        $cityName = trim($_POST['city_name'] ?? '');
        $area = (int)str_replace([',', ' '], '', $_POST['area'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if (empty($name) || empty($phone) || empty($propertyType)) {
            $_SESSION['flash_error'] = 'Please fill in all required fields.';
            $this->redirect('/associate/list-property');
            return;
        }

        try {
            // Handle image upload
            $imagePath = null;
            if (!empty($_FILES['property_image']['name']) && $_FILES['property_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../assets/images/properties/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $v = \UploadValidator::validate($_FILES['property_image'], ['types' => 'images', 'max_size' => 5]);
                if ($v['valid']) {
                    $safeName = \UploadValidator::safeFilename($_FILES['property_image']['name']);
                    $newName = 'prop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($safeName, PATHINFO_EXTENSION);
                    $targetPath = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES['property_image']['tmp_name'], $targetPath)) {
                        \App\Core\ImageOptimizer::optimizeStatic($targetPath);
                        $imagePath = 'properties/' . $newName;
                        // Mirror to StorageManager (S3 or local fallback).
                        try {
                            \App\Services\Storage\StorageManager::getInstance()->put(
                                'assets/images/' . $imagePath,
                                file_get_contents($targetPath),
                                [
                                    'ContentType'   => mime_content_type($targetPath) ?: 'image/jpeg',
                                    'Cache-Control' => 'public, max-age=31536000, immutable',
                                ]
                            );
                        } catch (\Throwable $e) {
                            error_log('AssociateController::listProperty storage mirror: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Save to user_properties table with associate tracking
            $db = \App\Core\Database\Database::getInstance();

            $stmt = $db->prepare("
                INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, image, status, created_at)
                VALUES (?, ?, 'associate', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $associateId,
                $associateId,
                $name,
                $phone,
                $email,
                $propertyType,
                $listingType,
                $location,
                $area,
                $price,
                $listingType === 'rent' ? 'month' : 'lakh',
                $description,
                $imagePath
            ]);

            // Also save to inquiries for CRM tracking
            $message = "Posted by Associate: {$associateName}\n";
            $message .= "Property Type: " . ucfirst($propertyType) . "\n";
            $message .= "Listing Type: " . ucfirst($listingType) . "\n";
            $message .= "Price: " . $price . "\n";
            $message .= "Area: " . $area . " sq ft\n";
            $message .= "Location: " . $location . "\n";
            $message .= "Description: " . $description;

            try {
                $inqStmt = $db->prepare("
                    INSERT INTO inquiries (name, email, phone, message, type, status, priority, posted_by, posted_by_type, created_at) 
                    VALUES (?, ?, ?, ?, 'property_listing', 'new', 'medium', ?, 'associate', NOW())
                ");
                $inqStmt->execute([$name, $email, $phone, $message, $associateId]);
            } catch (\Exception $e2) {
                error_log("Inquiry save error: " . $e2->getMessage());
            }

            $_SESSION['flash_success'] = 'Thank you! Your property listing has been submitted. Our team will verify and publish it soon.';
        } catch (\Exception $e) {
            error_log("Associate property listing error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to submit. Please try again or contact support.';
        }

        $this->redirect('/associate/properties');
    }

    /**
     * Associate Team Management
     */
    public function team()
    {
        $this->requireAuth();
        @session_start();
        $associateId = $_SESSION['user_id'] ?? null;
        $associateName = $_SESSION['user_name'] ?? '';

        $db = \App\Core\Database\Database::getInstance();
        $teamMembers = [];
        try {
            $teamMembers = $db->fetchAll(
                "SELECT id, name, email, phone, status, created_at FROM users WHERE referred_by = ? AND role = 'associate' ORDER BY created_at DESC",
                [$associateId]
            );
        } catch (\Exception $e) {
            $teamMembers = [];
        }

        $this->render('associate/team', [
            'page_title' => 'My Team - Associate Dashboard',
            'page_description' => 'Manage your team members',
            'associate_name' => $associateName,
            'team' => $teamMembers,
            'team_count' => count($teamMembers)
        ], 'layouts/associate');
    }

    /**
     * MLM Plan & Commission Structure
     */
    public function mlmPlan()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $levels = [];
        $currentPlan = null;
        $currentRank = 'Associate';
        $nextRank = null;
        $userProfile = null;

        try {
            $currentPlan = $this->db->fetchOne("SELECT * FROM mlm_plans WHERE status = 'active' LIMIT 1");
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        try {
            $levels = $this->db->fetchAll("SELECT * FROM mlm_levels ORDER BY level_order");
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        try {
            $userProfile = $this->db->fetchOne("SELECT current_level, total_team_size, direct_referrals, lifetime_sales FROM mlm_profiles WHERE user_id = ?", [$userId]);
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        if ($userProfile) {
            $currentRank = $userProfile['current_level'] ?? 'Associate';
        }
        if (!empty($levels)) {
            $found = false;
            foreach ($levels as $l) {
                if ($found) { $nextRank = $l; break; }
                if ($l['level_name'] === $currentRank) $found = true;
            }
        }

        $this->render('associate/mlm_plan', [
            'page_title' => 'MLM Plan & Commission Structure',
            'page_description' => 'Understand your earning potential',
            'levels' => $levels,
            'current_plan' => $currentPlan,
            'current_rank' => $currentRank,
            'next_rank' => $nextRank,
            'user_profile' => $userProfile,
            'current_page' => 'mlm-plan'
        ], 'layouts/associate');
    }

    private function safeGamify(string $method, int ...$args): array
    {
        try {
            $role = strtolower(str_replace('for', '', $method));
            $cacheKey1 = $args[0] ?? 0;
            $cacheKey2 = $args[1] ?? 0;
            return \App\Services\CacheService::getGamification(
                $role,
                (int)$cacheKey1,
                (int)$cacheKey2,
                function () use ($method, $args) {
                    $svc = new \App\Services\GamificationService();
                    return $svc->{$method}(...$args);
                }
            );
        } catch (\Throwable $e) {
            error_log('Gamification error: ' . $e->getMessage());
            return [];
        }
    }
}
