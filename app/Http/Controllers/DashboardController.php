<?php

namespace App\Http\Controllers;

use Exception;

class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin(); // Require user to be logged in
    }

    /**
     * Show user dashboard - redirects based on user type
     */
    public function index()
    {
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            $userType = $_SESSION['user_type'] ?? 'customer';

            // Redirect based on user type
            switch ($userType) {
                case 'customer':
                    header('Location: ' . BASE_URL . '/customer/dashboard');
                    exit;
                case 'agent':
                    header('Location: ' . BASE_URL . '/agent/dashboard');
                    exit;
                case 'associate':
                    header('Location: ' . BASE_URL . '/associate/dashboard');
                    exit;
                case 'admin':
                    header('Location: ' . BASE_URL . '/admin/dashboard');
                    exit;
                default:
                    $_SESSION['errors'] = ["Invalid user type"];
                    header('Location: ' . BASE_URL . '/logout');
                    exit;
            }

            $this->render('dashboard/index', [
                'page_title' => 'Dashboard - APS Dream Home',
                'page_description' => 'Your personalized real estate dashboard',
                'user' => $user,
                'stats' => $stats,
                'recent_activities' => $recentActivities,
                'favorite_properties' => $favoriteProperties,
                'recent_inquiries' => $recentInquiries,
                'recommended_properties' => $recommendedProperties
            ], 'layouts/base');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load dashboard: ' . $e->getMessage());
            $this->redirect('/');
        }
    }

    /**
     * Customer Dashboard
     */
    public function customer()
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        try {
            // Get live stats
            $stats = [
                'favorites_count' => $this->db->fetchColumn("SELECT COUNT(*) FROM property_favorites WHERE user_id = ?", [$userId]),
                'inquiries_count' => $this->db->fetchColumn("SELECT COUNT(*) FROM property_inquiries WHERE user_id = ?", [$userId]),
                'views_count' => 12, // Mock for now
                'saved_searches_count' => 3 // Mock for now
            ];

            // Get favorite properties
            $favorite_properties = $this->db->fetchAll("
                SELECT p.*, f.created_at as favorited_at
                FROM properties p
                JOIN property_favorites f ON p.id = f.property_id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC
                LIMIT 5
            ", [$userId]);

            // Get recent activities (Inquiries + Favorites)
            $activities = [];

            // Fetch inquiries
            $inquiries = $this->db->fetchAll("
                SELECT 'inquiry' as type, p.title as property, i.created_at as date
                FROM property_inquiries i
                JOIN properties p ON i.property_id = p.id
                WHERE i.user_id = ?
                ORDER BY i.created_at DESC
                LIMIT 5
            ", [$userId]);

            // Fetch favorites
            $favorites = $this->db->fetchAll("
                SELECT 'favorite' as type, p.title as property, f.created_at as date
                FROM property_favorites f
                JOIN properties p ON f.property_id = p.id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC
                LIMIT 5
            ", [$userId]);

            $recent_activities = array_merge($inquiries, $favorites);
            usort($recent_activities, function ($a, $b) {
                return strtotime($b['date'] ?? 'now') - strtotime($a['date'] ?? 'now');
            });
            $recent_activities = array_slice($recent_activities, 0, 5);

            // Get recommended properties - select only needed columns for performance
            $recommended_properties = $this->db->fetchAll("
                SELECT id, title, property_type, location, price, status, created_at
                FROM properties
                WHERE status = 'available'
                ORDER BY created_at DESC
                LIMIT 4
            ");

            $data = [
                'page_title' => 'Customer Dashboard - APS Dream Home',
                'user' => [
                    'name' => $_SESSION['user_name'] ?? 'Guest',
                    'customer_id' => 'CUST-' . str_pad($userId, 5, '0', STR_PAD_LEFT),
                    'join_date' => $_SESSION['join_date'] ?? date('Y-m-d')
                ],
                'stats' => $stats,
                'favorite_properties' => $favorite_properties,
                'recent_activities' => $recent_activities,
                'recommended_properties' => $recommended_properties
            ];

            $this->render('dashboard/customer', $data);
        } catch (Exception $e) {
            error_log("Error loading customer dashboard: " . $e->getMessage());
            $this->render('dashboard/customer', [
                'page_title' => 'Customer Dashboard',
                'error' => "Could not load dashboard data."
            ]);
        }
    }

    /**
     * Show user profile page
     */
    public function profile()
    {
        $userId = $_SESSION['user_id'];

        $user = [
            'name' => $_SESSION['user_name'] ?? 'User',
            'email' => $_SESSION['user_email'] ?? 'user@example.com',
            'phone' => '9876543210',
            'address' => 'Gorakhpur, Uttar Pradesh',
            'join_date' => '2024-01-15'
        ];

        $this->render('dashboard/profile', [
            'page_title' => 'My Profile - APS Dream Home',
            'page_description' => 'Manage your account settings and preferences',
            'user' => $user
        ], 'layouts/base');
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('/dashboard/profile');
                return;
            }

            $userId = $_SESSION['user_id'];
            $name = $this->sanitizeInput($_POST['name'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phone = $this->sanitizeInput($_POST['phone'] ?? '');
            $address = $this->sanitizeInput($_POST['address'] ?? '');

            // Validation
            if (empty($name) || empty($email) || empty($phone)) {
                $_SESSION['errors'] = ["Name, email and phone are required"];
                $this->redirect('/dashboard/profile');
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['errors'] = ["Valid email is required"];
                $this->redirect('/dashboard/profile');
                return;
            }

            // Update user in database
            try {
                $stmt = $this->db->prepare("
                    UPDATE users SET name = ?, email = ?, phone = ?, address = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $phone, $address, $userId]);

                // Update session
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;

                $_SESSION['success'] = "Profile updated successfully!";
            } catch (Exception $e) {
                $_SESSION['errors'] = ["Failed to update profile: " . $e->getMessage()];
            }

            $this->redirect('/dashboard/profile');
        } catch (Exception $e) {
            $_SESSION['errors'] = ["An error occurred: " . $e->getMessage()];
            $this->redirect('/dashboard/profile');
        }
    }

    /**
     * Show user's favorite properties
     */
    public function favorites()
    {
        $userId = $_SESSION['user_id'];

        $favorites = [
            ['id' => 1, 'title' => 'Suyoday Colony', 'location' => 'Gorakhpur', 'price' => '₹7.5 Lakhs', 'favorited_at' => '2024-03-01'],
            ['id' => 2, 'title' => 'Raghunat Nagri', 'location' => 'Gorakhpur', 'price' => '₹8.5 Lakhs', 'favorited_at' => '2024-02-28']
        ];

        $this->render('dashboard/favorites', [
            'page_title' => 'My Favorites - APS Dream Home',
            'page_description' => 'Your saved property listings',
            'favorites' => $favorites
        ], 'layouts/base');
    }

    /**
     * Show user's property inquiries
     */
    public function inquiries()
    {
        $userId = $_SESSION['user_id'];

        $inquiries = [
            ['property_title' => 'Braj Radha Nagri', 'price' => '₹6.5 Lakhs', 'location' => 'Gorakhpur', 'status' => 'Pending', 'created_at' => '2024-03-01'],
            ['property_title' => 'Budh Bihar Colony', 'price' => '₹5.5 Lakhs', 'location' => 'Kushinagar', 'status' => 'Responded', 'created_at' => '2024-02-28']
        ];

        $this->render('dashboard/inquiries', [
            'page_title' => 'My Inquiries - APS Dream Home',
            'page_description' => 'Your property inquiry history',
            'inquiries' => $inquiries
        ], 'layouts/base');
    }

    /**
     * Add property to favorites
     */
    public function addFavorite()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }

            $userId = $_SESSION['user_id'];
            $propertyId = $this->sanitizeInput($_POST['property_id'] ?? '');

            if (empty($propertyId)) {
                echo json_encode(['success' => false, 'message' => 'Property ID is required']);
                exit;
            }

            // Add to favorites (simplified for demo)
            echo json_encode(['success' => true, 'message' => 'Property added to favorites']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Remove property from favorites
     */
    public function removeFavorite()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }

            $userId = $_SESSION['user_id'];
            $propertyId = $this->sanitizeInput($_POST['property_id'] ?? '');

            if (empty($propertyId)) {
                echo json_encode(['success' => false, 'message' => 'Property ID is required']);
                exit;
            }

            // Remove from favorites (simplified for demo)
            echo json_encode(['success' => true, 'message' => 'Property removed from favorites']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Submit property inquiry
     */
    public function submitInquiry()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }

            $userId = $_SESSION['user_id'];
            $propertyId = $this->sanitizeInput($_POST['property_id'] ?? '');
            $name = $this->sanitizeInput($_POST['name'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phone = $this->sanitizeInput($_POST['phone'] ?? '');
            $message = $this->sanitizeInput($_POST['message'] ?? '');

            // Validation
            if (empty($propertyId) || empty($name) || empty($email) || empty($phone) || empty($message)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Valid email is required']);
                exit;
            }

            // Submit inquiry (simplified for demo)
            echo json_encode(['success' => true, 'message' => 'Inquiry submitted successfully!']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Associate Dashboard
     */
    public function associate()
    {
        try {
            $userId = $_SESSION['user_id'];

            // Get performance and commission data
            $perfCalculator = new \App\Services\PerformanceRankCalculator();
            $commCalculator = new \App\Services\DifferentialCommissionCalculator();

            $perfData = $perfCalculator->calculateRank($userId);

            // Get recent activities (sales and commissions)
            $stmt = $this->db->prepare("
                SELECT 'commission' as type, amount, created_at as date, type as subtype 
                FROM commissions 
                WHERE user_id = ? 
                ORDER BY created_at DESC LIMIT 5
            ");
            $stmt->execute([$userId]);
            $recentActivities = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get user profile info
            $stmt = $this->db->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Associate Dashboard - APS Dream Home',
                'user' => [
                    'name' => $userData['name'] ?? 'Associate User',
                    'email' => $userData['email'] ?? '',
                    'role' => $perfData['rank'] ?? 'Associate',
                    'join_date' => $userData['created_at'] ?? date('Y-m-d'),
                    'performance' => [
                        'total_sales' => $perfData['team_size'] ?? 0,
                        'total_revenue' => $perfData['business_volume'] ?? 0,
                        'commission_earned' => $this->getTotalCommission($userId),
                        'properties_sold' => $this->getPersonalSalesCount($userId),
                        'clients_served' => $this->getClientCount($userId)
                    ]
                ],
                'recent_activities' => $recentActivities,
                'notifications' => [
                    ['type' => 'info', 'message' => 'Your current rank is ' . ($perfData['rank'] ?? 'Associate'), 'time' => 'Just now'],
                    ['type' => 'success', 'message' => 'Next rank target: ' . ($perfData['next_rank_info']['next_rank'] ?? 'None'), 'time' => 'Available']
                ],
                'rank_info' => $perfData
            ];

            $this->render('dashboard/associate', $data);
        } catch (Exception $e) {
            error_log("Error loading associate dashboard: " . $e->getMessage());
            echo "Error loading associate dashboard. Please check logs.";
        }
    }

    private function getTotalCommission($userId)
    {
        $stmt = $this->db->prepare("SELECT SUM(amount) FROM commissions WHERE user_id = ? AND status = 'paid'");
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn() ?: 0;
    }

    private function getPersonalSalesCount($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM property_sales WHERE agent_id = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn() ?: 0;
    }

    private function getClientCount($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT buyer_id) FROM property_sales WHERE agent_id = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn() ?: 0;
    }
}
