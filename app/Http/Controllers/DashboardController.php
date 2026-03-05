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
     * Show user dashboard
     */
    public function index()
    {
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            $userId = $_SESSION['user_id'];

            // Get user information
            $user = $this->db->table('users')->where('id', $userId)->first();
            if (!$user) {
                throw new Exception('User not found');
            }

            // Get dashboard statistics based on user type
            $userType = $user['user_type'] ?? 'customer';
            $stats = $this->getUserStats($userId, $userType);

            // Get recent activities
            $recentActivities = $this->getRecentActivities($userId);

            // Get favorite properties
            $favoriteProperties = $this->getFavoriteProperties($userId);

            // Get recent inquiries
            $recentInquiries = $this->getRecentInquiries($userId);

            // Get recommended properties
            $recommendedProperties = $this->getRecommendedProperties($userId, $user['user_type'] ?? 'customer');

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
     * Get user statistics based on user type
     */
    private function getUserStats($userId, $userType)
    {
        $stats = [
            'favorites_count' => 0,
            'inquiries_count' => 0,
            'views_count' => 0,
            'saved_searches_count' => 0
        ];

        try {
            // Count favorite properties
            $favoritesQuery = $this->db->table('user_favorites')
                ->where('user_id', $userId);
            $favoritesResult = $favoritesQuery->get();
            $stats['favorites_count'] = is_object($favoritesResult) ? $favoritesResult->rowCount() : 0;

            // Count property inquiries
            $inquiriesQuery = $this->db->table('property_inquiries')
                ->where('user_id', $userId);
            $inquiriesResult = $inquiriesQuery->get();
            $stats['inquiries_count'] = is_object($inquiriesResult) ? $inquiriesResult->rowCount() : 0;

            // Count profile views (if implemented)
            $stats['views_count'] = rand(10, 100); // Placeholder

            // Count saved searches (if implemented)
            $searchesQuery = $this->db->table('saved_searches')
                ->where('user_id', $userId);
            $searchesResult = $searchesQuery->get();
            $stats['saved_searches_count'] = is_object($searchesResult) ? $searchesResult->rowCount() : 0;

        } catch (Exception $e) {
            // Return default stats if query fails
        }

        return $stats;
    }

    /**
     * Get recent user activities
     */
    private function getRecentActivities($userId)
    {
        try {
            return $this->db->table('user_activity_log')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get user's favorite properties
     */
    private function getFavoriteProperties($userId)
    {
        try {
            return $this->db->table('user_favorites as uf')
                ->join('properties as p', 'uf.property_id', '=', 'p.id')
                ->where('uf.user_id', $userId)
                ->where('p.status', 'active')
                ->select('p.*', 'uf.created_at as favorited_at')
                ->orderBy('uf.created_at', 'desc')
                ->limit(4)
                ->get();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get recent property inquiries
     */
    private function getRecentInquiries($userId)
    {
        try {
            return $this->db->table('property_inquiries as pi')
                ->join('properties as p', 'pi.property_id', '=', 'p.id')
                ->where('pi.user_id', $userId)
                ->select('pi.*', 'p.title as property_title', 'p.price', 'p.location')
                ->orderBy('pi.created_at', 'desc')
                ->limit(3)
                ->get();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get recommended properties based on user type and preferences
     */
    private function getRecommendedProperties($userId, $userType)
    {
        try {
            $query = $this->db->table('properties')
                ->where('status', 'active')
                ->where('is_featured', 1)
                ->orderBy('created_at', 'desc')
                ->limit(6);

            // Add user-specific filtering based on type
            if ($userType === 'buyer') {
                // Show residential properties for buyers
                $query->where('type', 'residential');
            } elseif ($userType === 'investor') {
                // Show commercial properties for investors
                $query->where('type', 'commercial');
            } elseif ($userType === 'agent') {
                // Show all types for agents
                // No additional filtering
            }

            return $query->get();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Show user profile page
     */
    public function profile()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->db->table('users')->where('id', $userId)->first();

        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/dashboard');
            return;
        }

        $this->render('dashboard/profile', [
            'page_title' => 'My Profile - APS Dream Home',
            'page_description' => 'Manage your account settings and preferences',
            'user' => $user
        ], 'layouts/base');
    }

    /**
     * Show user's favorite properties
     */
    public function favorites()
    {
        $userId = $_SESSION['user_id'];

        $favorites = $this->db->table('user_favorites as uf')
            ->join('properties as p', 'uf.property_id', '=', 'p.id')
            ->where('uf.user_id', $userId)
            ->where('p.status', 'active')
            ->select('p.*', 'uf.created_at as favorited_at')
            ->orderBy('uf.created_at', 'desc')
            ->get();

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

        $inquiries = $this->db->table('property_inquiries as pi')
            ->join('properties as p', 'pi.property_id', '=', 'p.id')
            ->where('pi.user_id', $userId)
            ->select('pi.*', 'p.title as property_title', 'p.price', 'p.location', 'p.images')
            ->orderBy('pi.created_at', 'desc')
            ->get();

        $this->render('dashboard/inquiries', [
            'page_title' => 'My Inquiries - APS Dream Home',
            'page_description' => 'Your property inquiry history',
            'inquiries' => $inquiries
        ], 'layouts/base');
    }

    /**
     * Remove property from favorites
     */
    public function removeFavorite()
    {
        try {
            $this->validateCsrfToken();

            $userId = $_SESSION['user_id'];
            $propertyId = Security::sanitize($_POST['property_id']) ?? null;

            if (!$propertyId) {
                throw new Exception('Property ID is required');
            }

            $deleted = $this->db->table('user_favorites')
                ->where('user_id', $userId)
                ->where('property_id', $propertyId)
                ->delete();

            if ($deleted) {
                // Log activity
                $this->logActivity('Removed property from favorites: ' . $propertyId);

                echo json_encode(['success' => true, 'message' => 'Property removed from favorites']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Property not found in favorites']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Add property to favorites
     */
    public function addFavorite()
    {
        try {
            $this->validateCsrfToken();

            $userId = $_SESSION['user_id'];
            $propertyId = Security::sanitize($_POST['property_id']) ?? null;

            if (!$propertyId) {
                throw new Exception('Property ID is required');
            }

            // Check if property exists
            $property = $this->db->table('properties')
                ->where('id', $propertyId)
                ->where('status', 'active')
                ->first();

            if (!$property) {
                throw new Exception('Property not found');
            }

            // Check if already in favorites
            $existing = $this->db->table('user_favorites')
                ->where('user_id', $userId)
                ->where('property_id', $propertyId)
                ->first();

            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Property already in favorites']);
                exit;
            }

            // Add to favorites
            $inserted = $this->db->table('user_favorites')->insert([
                'user_id' => $userId,
                'property_id' => $propertyId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($inserted) {
                // Log activity
                $this->logActivity('Added property to favorites: ' . $propertyId);

                echo json_encode(['success' => true, 'message' => 'Property added to favorites']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add property to favorites']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Submit property inquiry
     */
    public function submitInquiry()
    {
        try {
            $this->validateCsrfToken();

            $userId = $_SESSION['user_id'];
            $propertyId = Security::sanitize($_POST['property_id']) ?? null;
            $name = trim(Security::sanitize($_POST['name']) ?? '');
            $email = trim(Security::sanitize($_POST['email']) ?? '');
            $phone = trim(Security::sanitize($_POST['phone']) ?? '');
            $message = trim(Security::sanitize($_POST['message']) ?? '');

            // Validate required fields
            if (!$propertyId || !$name || !$email || !$phone || !$message) {
                throw new Exception('All fields are required');
            }

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address');
            }

            // Check if property exists
            $property = $this->db->table('properties')
                ->where('id', $propertyId)
                ->where('status', 'active')
                ->first();

            if (!$property) {
                throw new Exception('Property not found');
            }

            // Insert inquiry
            $inquiryId = $this->db->table('property_inquiries')->insert([
                'user_id' => $userId,
                'property_id' => $propertyId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'status' => 'new',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$inquiryId) {
                throw new Exception('Failed to submit inquiry. Please try again.');
            }

            // Send inquiry notification to admin
            try {
                require_once $_SERVER['DOCUMENT_ROOT'] . '/app/Services/EmailService.php';
                $emailService = new \App\Services\EmailService();

                $inquiryData = [
                    'property_title' => $property['title'],
                    'location' => $property['location'],
                    'price' => $property['price'],
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'message' => $message,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $adminEmailResult = $emailService->sendPropertyInquiryNotification($inquiryData);
                if (!$adminEmailResult['success']) {
                    error_log('Admin inquiry notification failed: ' . $adminEmailResult['message']);
                }
            } catch (Exception $e) {
                error_log('Admin email service error: ' . $e->getMessage());
            }

            // Log activity
            $this->logActivity('Submitted property inquiry for: ' . $property['title']);

            echo json_encode([
                'success' => true,
                'message' => 'Inquiry submitted successfully! We will get back to you soon.'
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Associate Dashboard
     */
    public function associate()
    {
        try {
            $data = [
                'page_title' => 'Associate Dashboard - APS Dream Home',
                'user' => [
                    'name' => 'Associate User',
                    'email' => 'associate@apsdreamhome.com',
                    'role' => 'Associate',
                    'join_date' => '2024-01-15',
                    'performance' => [
                        'total_sales' => 12,
                        'total_revenue' => 4500000,
                        'commission_earned' => 225000,
                        'properties_sold' => 8,
                        'clients_served' => 15
                    ]
                ],
                'recent_activities' => [
                    ['type' => 'sale', 'property' => 'APS Gardenia', 'amount' => 3500000, 'date' => '2024-03-01'],
                    ['type' => 'inquiry', 'property' => 'APS Heights', 'client' => 'John Doe', 'date' => '2024-03-02'],
                    ['type' => 'commission', 'amount' => 175000, 'date' => '2024-03-01']
                ],
                'notifications' => [
                    ['type' => 'info', 'message' => 'New property listing available', 'time' => '2 hours ago'],
                    ['type' => 'success', 'message' => 'Commission payment processed', 'time' => '1 day ago']
                ]
            ];

            $this->render('dashboard/associate', $data);

        } catch (Exception $e) {
            echo "Error loading associate dashboard: " . $e->getMessage();
        }
    }
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\User\DashboardController.php

function settings()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        $data = [
            'title' => 'Settings - APS Dream Home',
            'user' => $user,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error')
        ];

        $this->view('user/settings', $data);
    }
function notifications()
    {
        $userId = $_SESSION['user_id'];

        // Fetch notifications from model (assuming a Notification model exists or using User model)
        // For now, let's just use dummy data if the model isn't ready
        $notifications = [];

        $data = [
            'title' => 'Notifications - APS Dream Home',
            'notifications' => $notifications
        ];

        $this->view('user/notifications', $data);
    }
function updateProfile()
    {
        // Validate CSRF token
        if (!$this->validateCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/dashboard/profile');
            return;
        }
function changePassword()
    {
        // Validate CSRF token
        if (!$this->validateCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/dashboard/profile');
            return;
        }
function savedProperties()
    {
        $userId = $_SESSION['user_id'];

        // Get user's saved properties with details
        $savedProperties = $this->propertyModel->getUserSavedProperties($userId);

        $data = [
            'title' => 'Saved Properties - APS Dream Home',
            'savedProperties' => $savedProperties
        ];

        $this->view('user/saved-properties', $data);
    }
function saveProperty()
    {
        // Validate CSRF token
        if (!$this->verifyCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }
function unsaveProperty()
    {
        // Validate CSRF token
        if (!$this->verifyCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }
function enquiries()
    {
        $userId = $_SESSION['user_id'];

        // Get user's enquiries
        $enquiries = $this->enquiryModel->getUserEnquiries($userId);

        $data = [
            'title' => 'My Enquiries - APS Dream Home',
            'enquiries' => $enquiries
        ];

        $this->view('user/enquiries', $data);
    }
function submitEnquiry()
    {
        // Validate CSRF token
        if (!$this->verifyCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\Customer\DashboardController.php

function dashboard()
    {
        return $this->index();
    }
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 579 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//