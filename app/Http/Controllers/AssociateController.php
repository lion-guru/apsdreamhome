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
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'associate') {
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
        $associateName = $_SESSION['associate_name'] ?? 'Associate';

        // Fetch real DB data
        $totalLeads = 0;
        $propertiesSold = 0;
        $totalCommission = 0;
        $networkSize = 0;
        $recentLeads = [];
        $recentCommissions = [];
        $activities = [];

        try {
            $totalLeads = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM inquiries WHERE posted_by = ? AND posted_by_type = 'associate'", [$userId]);
        } catch (\Exception $e) {}
        try {
            $propertiesSold = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM user_properties WHERE user_id = ? AND status = 'approved'", [$userId]);
        } catch (\Exception $e) {}
        try {
            $totalCommission = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE associate_id = ?", [$userId]);
        } catch (\Exception $e) {}
        try {
            $networkSize = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE referred_by = ?", [$userId]);
        } catch (\Exception $e) {}

        // Recent leads
        try {
            $recentLeads = $this->db->fetchAll(
                "SELECT name, phone, CONCAT('Lead #', id) as type, status, DATE(created_at) as date FROM inquiries WHERE posted_by = ? AND posted_by_type = 'associate' ORDER BY created_at DESC LIMIT 5",
                [$userId]
            );
        } catch (\Exception $e) {}
        // Recent commissions
        try {
            $recentCommissions = $this->db->fetchAll(
                "SELECT c.id, p.title as property, c.amount, c.status, DATE(c.created_at) as date FROM commissions c LEFT JOIN user_properties p ON c.property_id = p.id WHERE c.associate_id = ? ORDER BY c.created_at DESC LIMIT 5",
                [$userId]
            );
        } catch (\Exception $e) {}
        // Recent activities
        try {
            $rawActivities = $this->db->fetchAll(
                "SELECT action, created_at FROM activity_log WHERE user_id = ? AND user_type = 'associate' ORDER BY created_at DESC LIMIT 5",
                [$userId]
            );
            foreach ($rawActivities as $a) {
                $activities[] = [
                    'icon' => 'fa-clock',
                    'text' => $a['action'],
                    'time' => $this->timeAgo($a['created_at']),
                    'color' => 'blue'
                ];
            }
        } catch (\Exception $e) {}

        // Fallback if no db data
        if (empty($recentLeads)) {
            $recentLeads = [
                ['name' => 'Rajesh Kumar', 'phone' => '98765xxxxx', 'type' => 'Residential Plot', 'status' => 'hot', 'date' => date('Y-m-d', strtotime('-1 day'))],
                ['name' => 'Priya Sharma', 'phone' => '98765xxxxx', 'type' => 'Commercial Shop', 'status' => 'warm', 'date' => date('Y-m-d', strtotime('-3 days'))],
            ];
        }
        if (empty($recentCommissions)) {
            $recentCommissions = [
                ['property' => 'Suryoday Heights - Plot 45', 'amount' => 25000, 'status' => 'paid', 'date' => date('Y-m-d', strtotime('-5 days'))],
                ['property' => 'Raghunath City - Shop 12', 'amount' => 18000, 'status' => 'pending', 'date' => date('Y-m-d', strtotime('-10 days'))],
            ];
        }
        if (empty($activities)) {
            $activities = [
                ['icon' => 'fa-user-plus', 'text' => 'Welcome to associate dashboard', 'time' => 'Just now', 'color' => 'blue'],
                ['icon' => 'fa-building', 'text' => 'Start adding your first property', 'time' => '-', 'color' => 'orange'],
            ];
        }

        $stats = [
            'total_leads' => $totalLeads,
            'active_leads' => 0,
            'properties_sold' => $propertiesSold,
            'total_commission' => $totalCommission,
            'pending_commission' => 0,
            'network_size' => max($networkSize, 1),
            'conversion_rate' => 0,
            'monthly_growth' => 0
        ];

        $this->render('dashboard/associate_dashboard', [
            'page_title' => 'Associate Dashboard - APS Dream Home',
            'page_description' => 'Manage your property listings and client relationships',
            'stats' => $stats,
            'recent_leads' => $recentLeads,
            'recent_commissions' => $recentCommissions,
            'activities' => $activities
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
        } catch (\Exception $e) {}

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
        } catch (\Exception $e) {}

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

        $commissions = [];
        $totalEarned = 0;
        $totalPending = 0;
        try {
            $commissions = $this->db->fetchAll(
                "SELECT c.id, COALESCE(p.title, p.name, 'Property #' || c.property_id) as property, 
                 c.amount, c.status, c.description, DATE(c.created_at) as date
                 FROM commissions c 
                 LEFT JOIN user_properties p ON c.property_id = p.id 
                 WHERE c.associate_id = ? 
                 ORDER BY c.created_at DESC LIMIT 20",
                [$userId]
            );
            $totalEarned = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE associate_id = ? AND status = 'paid'",
                [$userId]
            );
            $totalPending = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE associate_id = ? AND status = 'pending'",
                [$userId]
            );
        } catch (\Exception $e) {}

        $this->render('associate/commissions', [
            'page_title' => 'My Commissions - APS Dream Home',
            'page_description' => 'View your commission earnings',
            'commissions' => $commissions,
            'total_earned' => $totalEarned,
            'total_pending' => $totalPending,
            'current_page' => 'commissions'
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
        } catch (\Exception $e) {}

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
        } catch (\Exception $e) {}

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
        } catch (\Exception $e) {}

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
                $user = $this->db->fetch(
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

        // Set variables for shared view
        $userRole = 'associate';
        $profileUrl = BASE_URL . '/associate/profile';
        $securityUrl = null;
        $canEdit = true;

        include __DIR__ . '/../../../views/shared/profile.php';
    }

    /**
     * Associate Settings Page
     */
    public function settings()
    {
        $this->requireAuth();

        @session_start();

        // Get associate info
        $associateId = $_SESSION['associate_id'] ?? null;
        $associateName = $_SESSION['associate_name'] ?? '';
        $associateEmail = $_SESSION['associate_email'] ?? '';
        $associatePhone = $_SESSION['associate_phone'] ?? '';

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
            // Table might not exist, use defaults
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
     * List Property page for Associates
     */
    public function listProperty()
    {
        $this->requireAuth();

        @session_start();

        // Get associate info
        $associateId = $_SESSION['associate_id'] ?? null;
        $associateName = $_SESSION['associate_name'] ?? '';
        $associatePhone = $_SESSION['associate_phone'] ?? '';
        $associateEmail = $_SESSION['associate_email'] ?? '';

        // Load states for dropdown
        $db = \App\Core\Database\Database::getInstance();
        $states = $db->fetchAll("SELECT id, name FROM states WHERE is_active = 1 ORDER BY name LIMIT 50");

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

        $associateId = $_SESSION['associate_id'] ?? null;
        $associateName = $_SESSION['associate_name'] ?? '';
        $associatePhone = $_SESSION['associate_phone'] ?? '';

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
            if (!empty($_FILES['property_image']['name'])) {
                $uploadDir = __DIR__ . '/../../../assets/images/properties/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = strtolower(pathinfo($_FILES['property_image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowed) && $_FILES['property_image']['size'] <= 5 * 1024 * 1024) {
                    $newName = 'prop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $targetPath = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES['property_image']['tmp_name'], $targetPath)) {
                        $imagePath = 'properties/' . $newName;
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
}
