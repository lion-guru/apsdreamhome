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
        if (!$this->isLoggedIn()) {
            $this->setFlash('error', 'Please login to access this page');
            $this->redirect('/login');
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

        // Sample data matching view expectations
        $dashboardData = [
            'role' => 'associate',
            'title' => 'Associate Dashboard',
            'widgets' => [
                'total_properties' => [
                    'title' => 'Total Properties',
                    'count' => 15,
                    'icon' => 'building',
                    'link' => '/associate/properties'
                ],
                'sold_properties' => [
                    'title' => 'Sold Properties',
                    'count' => 8,
                    'icon' => 'check-circle',
                    'link' => '/associate/sold'
                ],
                'pending_deals' => [
                    'title' => 'Pending Deals',
                    'count' => 3,
                    'icon' => 'clock',
                    'link' => '/associate/pending'
                ],
                'commissions' => [
                    'title' => 'Commission Earned',
                    'count' => '₹1.25L',
                    'icon' => 'rupee-sign',
                    'link' => '/associate/commissions'
                ]
            ],
            'recent_activities' => [
                ['action' => 'Property listed: Luxury Apartment in Gomti Nagar', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
                ['action' => 'Deal closed: Modern Villa in Hazratganj', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
                ['action' => 'New lead added: Ramesh Kumar', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))]
            ],
            'analytics' => [
                'sales_performance' => ['data' => 'Available'],
                'lead_conversion' => ['data' => 'Available'],
                'commission_trend' => ['data' => 'Available']
            ],
            'quick_actions' => [
                'add_property' => '/associate/add-property',
                'view_leads' => '/associate/leads',
                'my_commissions' => '/associate/commissions',
                'profile' => '/associate/profile'
            ]
        ];

        $this->render('dashboard/associate_dashboard', [
            'page_title' => 'Associate Dashboard - APS Dream Home',
            'page_description' => 'Manage your property listings and client relationships',
            'dashboardData' => $dashboardData
        ], 'layouts/associate');
    }

    /**
     * Add property form
     */
    public function addProperty()
    {
        $this->requireAuth();

        $this->render('associate/add_property', [
            'page_title' => 'Add Property - APS Dream Home',
            'page_description' => 'Add a new property listing'
        ]);
    }

    /**
     * View leads
     */
    public function leads()
    {
        $this->requireAuth();

        $this->render('associate/leads', [
            'page_title' => 'My Leads - APS Dream Home',
            'page_description' => 'Manage your client leads'
        ]);
    }

    /**
     * View commissions
     */
    public function commissions()
    {
        $this->requireAuth();

        $this->render('associate/commissions', [
            'page_title' => 'My Commissions - APS Dream Home',
            'page_description' => 'View your commission earnings'
        ]);
    }

    /**
     * View properties
     */
    public function properties()
    {
        $this->requireAuth();

        $this->render('associate/properties', [
            'page_title' => 'My Properties - APS Dream Home',
            'page_description' => 'Manage your property listings'
        ]);
    }

    /**
     * View sold properties
     */
    public function sold()
    {
        $this->requireAuth();

        $this->render('associate/sold', [
            'page_title' => 'Sold Properties - APS Dream Home',
            'page_description' => 'View your sold properties'
        ]);
    }

    /**
     * View pending deals
     */
    public function pending()
    {
        $this->requireAuth();

        $this->render('associate/pending', [
            'page_title' => 'Pending Deals - APS Dream Home',
            'page_description' => 'Manage your pending deals'
        ]);
    }

    /**
     * View profile
     */
    public function profile()
    {
        $this->requireAuth();

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
        $securityUrl = null; // Associates don't have security page yet
        $canEdit = true;

        include __DIR__ . '/../../../views/shared/profile.php';
    }

    /**
     * Associate Settings Page
     */
    public function settings()
    {
        $this->requireAuth();

        if (session_status() === PHP_SESSION_NONE) session_start();

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

        if (session_status() === PHP_SESSION_NONE) session_start();

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

        if (session_status() === PHP_SESSION_NONE) session_start();

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
