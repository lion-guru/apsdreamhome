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
        ]);
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
}
