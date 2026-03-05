<?php

namespace App\Http\Controllers\Admin;

require_once __DIR__ . '/../BaseController.php';

/**
 * AdminDashboardController - Complete Admin Management System
 */
class AdminDashboardController extends \App\Http\Controllers\BaseController
{
    private $pdo;
    
    public function __construct()
    {
        parent::__construct();
        $this->pdo = new \PDO(
            "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}",
            $_ENV['DB_USERNAME'] ?? 'root',
            $_ENV['DB_PASSWORD'] ?? '',
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    /**
     * Main Admin Dashboard
     */
    public function index()
    {
        // Check if user is logged in and has admin privileges
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        $this->render('admin/dashboard', [
            'page_title' => 'Admin Dashboard - APS Dream Home',
            'stats' => $stats,
            'recent_projects' => $this->getRecentProjects(),
            'recent_applications' => $this->getRecentApplications(),
            'pending_tasks' => $this->getPendingTasks()
        ]);
    }

    /**
     * Project Management
     */
    public function projects()
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        $projects = $this->getAllProjects();
        
        $this->render('admin/projects', [
            'page_title' => 'Project Management - APS Dream Home',
            'projects' => $projects
        ]);
    }

    /**
     * Add/Edit Project
     */
    public function projectForm($id = null)
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        $project = null;
        if ($id) {
            $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch();
        }

        $this->render('admin/project_form', [
            'page_title' => $id ? 'Edit Project' : 'Add New Project',
            'project' => $project,
            'states' => $this->getStates()
        ]);
    }

    /**
     * Save Project
     */
    public function saveProject()
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = Security::sanitize($_POST['id']) ?? null;
                $data = [
                    'name' => Security::sanitize($_POST['name']),
                    'state' => Security::sanitize($_POST['state']),
                    'location' => Security::sanitize($_POST['location']),
                    'type' => Security::sanitize($_POST['type']),
                    'status' => Security::sanitize($_POST['status']),
                    'size' => Security::sanitize($_POST['size']),
                    'price' => Security::sanitize($_POST['price']),
                    'description' => Security::sanitize($_POST['description']),
                    'amenities' => Security::sanitize($_POST['amenities']) ?? '',
                    'google_map_link' => Security::sanitize($_POST['google_map_link']) ?? '',
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Handle image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $image_paths = $this->handleImageUpload($_FILES['images']);
                    $data['images'] = json_encode($image_paths);
                }

                if ($id) {
                    // Update existing project
                    $sql = "UPDATE projects SET ";
                    $set_clauses = [];
                    $values = [];
                    foreach ($data as $key => $value) {
                        $set_clauses[] = "$key = ?";
                        $values[] = $value;
                    }
                    $values[] = $id;
                    $sql .= implode(', ', $set_clauses) . " WHERE id = ?";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute($values);
                } else {
                    // Insert new project
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $sql = "INSERT INTO projects (" . implode(', ', array_keys($data)) . ") VALUES (" . str_repeat('?,', count($data) - 1) . "?)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute(array_values($data));
                }

                $_SESSION['success'] = 'Project saved successfully!';
                header('Location: ' . BASE_URL . 'admin/projects');
                exit;

            } catch (Exception $e) {
                $_SESSION['error'] = 'Error saving project: ' . $e->getMessage();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }
    }

    /**
     * HR Management - Career Applications
     */
    public function careerApplications()
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        $applications = $this->getCareerApplications();
        
        $this->render('admin/career_applications', [
            'page_title' => 'Career Applications - APS Dream Home',
            'applications' => $applications
        ]);
    }

    /**
     * Update Application Status
     */
    public function updateApplicationStatus()
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = Security::sanitize($_POST['id']);
            $status = Security::sanitize($_POST['status']);
            
            $stmt = $this->pdo->prepare("UPDATE career_applications SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            $_SESSION['success'] = 'Application status updated!';
            header('Location: ' . BASE_URL . 'admin/career-applications');
            exit;
        }
    }

    /**
     * FAQ Management
     */
    public function faqs()
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        $faqs = $this->getFAQs();
        
        $this->render('admin/faqs', [
            'page_title' => 'FAQ Management - APS Dream Home',
            'faqs' => $faqs
        ]);
    }

    /**
     * Save FAQ
     */
    public function saveFAQ()
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = Security::sanitize($_POST['id']) ?? null;
            $question = Security::sanitize($_POST['question']);
            $answer = Security::sanitize($_POST['answer']);
            $category = Security::sanitize($_POST['category']);
            $status = Security::sanitize($_POST['status']) ?? 'active';

            if ($id) {
                $stmt = $this->pdo->prepare("UPDATE faqs SET question = ?, answer = ?, category = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$question, $answer, $category, $status, $id]);
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO faqs (question, answer, category, status, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$question, $answer, $category, $status]);
            }

            $_SESSION['success'] = 'FAQ saved successfully!';
            header('Location: ' . BASE_URL . 'admin/faqs');
            exit;
        }
    }

    /**
     * AI Chatbot Management
     */
    public function aiChatbot()
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        $this->render('admin/ai_chatbot', [
            'page_title' => 'AI Chatbot Settings - APS Dream Home',
            'chat_settings' => $this->getChatbotSettings()
        ]);
    }

    /**
     * Private helper methods
     */
    private function isAdmin(): bool
    {
        return parent::isAdmin() || in_array($_SESSION['user_role'] ?? '', ['cto', 'hr']);
    }

    private function getDashboardStats()
    {
        $stats = [];
        
        // Projects count
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM projects");
        $stats['total_projects'] = $stmt->fetch()['total'];
        
        // Career applications
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM career_applications WHERE status = 'pending'");
        $stats['pending_applications'] = $stmt->fetch()['total'];
        
        // Testimonials
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM testimonials WHERE status = 'pending'");
        $stats['pending_testimonials'] = $stmt->fetch()['total'];
        
        return $stats;
    }

    private function getAllProjects()
    {
        $stmt = $this->pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    private function getCareerApplications()
    {
        $stmt = $this->pdo->query("SELECT * FROM career_applications ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    private function getFAQs()
    {
        $stmt = $this->pdo->query("SELECT * FROM faqs ORDER BY category, question");
        return $stmt->fetchAll();
    }

    private function getStates()
    {
        return [
            'uttar_pradesh' => 'Uttar Pradesh',
            'bihar' => 'Bihar',
            'madhya_pradesh' => 'Madhya Pradesh'
        ];
    }

    private function handleImageUpload($files)
    {
        $upload_dir = 'uploads/projects/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $image_paths = [];
        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $filename = time() . '_' . basename($name);
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($files['tmp_name'][$key], $target_path)) {
                    $image_paths[] = $target_path;
                }
            }
        }
        
        return $image_paths;
    }

    private function getRecentProjects()
    {
        $stmt = $this->pdo->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5");
        return $stmt->fetchAll();
    }

    private function getRecentApplications()
    {
        $stmt = $this->pdo->query("SELECT * FROM career_applications ORDER BY created_at DESC LIMIT 5");
        return $stmt->fetchAll();
    }

    private function getPendingTasks()
    {
        $tasks = [];
        
        // Pending applications
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM career_applications WHERE status = 'pending'");
        $tasks['pending_applications'] = $stmt->fetch()['count'];
        
        // Pending testimonials
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM testimonials WHERE status = 'pending'");
        $tasks['pending_testimonials'] = $stmt->fetch()['count'];
        
        return $tasks;
    }

    private function getChatbotSettings()
    {
        // Return default settings or from database
        return [
            'enabled' => true,
            'welcome_message' => 'Hello! How can I help you today?',
            'auto_learn' => true,
            'response_delay' => 1.5
        ];
    }

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

    public function updateProfile()
    {
        // Validate CSRF token
        if (!$this->validateCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/dashboard/profile');
            return;
        }

        $userId = $_SESSION['user_id'];

        // Get and validate input
        $name = trim(Security::sanitize($_POST['name']) ?? '');
        $phone = trim(Security::sanitize($_POST['phone']) ?? '');
        $address = trim(Security::sanitize($_POST['address']) ?? '');
        $city = trim(Security::sanitize($_POST['city']) ?? '');
        $state = trim(Security::sanitize($_POST['state']) ?? '');
        $zipcode = trim(Security::sanitize($_POST['zipcode']) ?? '');

        if (!$name || strlen($name) < 2) {
            $this->setFlash('error', 'Please enter a valid name (at least 2 characters).');
            $this->redirect('/dashboard/profile');
            return;
        }

        $updateData = [
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zipcode' => $zipcode,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $result = $this->userModel->updateUser($userId, $updateData);

            if ($result) {
                // Update session name
                $_SESSION['user_name'] = $name;
                $this->setFlash('success', 'Profile updated successfully!');
            } else {
                $this->setFlash('error', 'Failed to update profile. Please try again.');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'An error occurred while updating your profile.');
        }

        $this->redirect('/dashboard/profile');
    }

    public function changePassword()
    {
        // Validate CSRF token
        if (!$this->validateCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/dashboard/profile');
            return;
        }

        $userId = $_SESSION['user_id'];
        $currentPassword = Security::sanitize($_POST['current_password']) ?? '';
        $newPassword = Security::sanitize($_POST['new_password']) ?? '';
        $confirmPassword = Security::sanitize($_POST['confirm_password']) ?? '';

        // Get current user to verify password
        $user = $this->userModel->getUserById($userId);

        if (!password_verify($currentPassword, $user['password'])) {
            $this->setFlash('error', 'Current password is incorrect.');
            $this->redirect('/dashboard/profile');
            return;
        }

        if (!$newPassword || strlen($newPassword) < 6) {
            $this->setFlash('error', 'New password must be at least 6 characters long.');
            $this->redirect('/dashboard/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'New passwords do not match.');
            $this->redirect('/dashboard/profile');
            return;
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            $result = $this->userModel->updatePassword($userId, $hashedPassword);

            if ($result) {
                $this->setFlash('success', 'Password changed successfully!');
            } else {
                $this->setFlash('error', 'Failed to change password. Please try again.');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'An error occurred while changing your password.');
        }

        $this->redirect('/dashboard/profile');
    }

    public function savedProperties()
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

    public function saveProperty()
    {
        // Validate CSRF token
        if (!$this->verifyCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $propertyId = intval(Security::sanitize($_POST['property_id']) ?? 0);

        if (!$propertyId) {
            echo json_encode(['success' => false, 'message' => 'Invalid property ID.']);
            return;
        }

        try {
            $result = $this->propertyModel->savePropertyForUser($userId, $propertyId);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Property saved successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Property already saved or not found.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred.']);
        }
    }

    public function unsaveProperty()
    {
        // Validate CSRF token
        if (!$this->verifyCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $propertyId = intval(Security::sanitize($_POST['property_id']) ?? 0);

        if (!$propertyId) {
            echo json_encode(['success' => false, 'message' => 'Invalid property ID.']);
            return;
        }

        try {
            $result = $this->propertyModel->unsavePropertyForUser($userId, $propertyId);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Property removed from saved list!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Property not found in saved list.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred.']);
        }
    }

    public function enquiries()
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

    public function submitEnquiry()
    {
        // Validate CSRF token
        if (!$this->verifyCsrfToken(Security::sanitize($_POST['csrf_token']) ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $userId = $_SESSION['user_id'];

        // Get and validate input
        $propertyId = intval(Security::sanitize($_POST['property_id']) ?? 0);
        $projectId = intval(Security::sanitize($_POST['project_id']) ?? 0);
        $message = trim(Security::sanitize($_POST['message']) ?? '');
        $enquiryType = Security::sanitize($_POST['enquiry_type']) ?? 'general';

        if (!$message || strlen($message) < 10) {
            echo json_encode(['success' => false, 'message' => 'Please enter a detailed message (at least 10 characters).']);
            return;
        }

        if (!$propertyId && !$projectId) {
            echo json_encode(['success' => false, 'message' => 'Please select a property or project.']);
            return;
        }

        $enquiryData = [
            'user_id' => $userId,
            'property_id' => $propertyId ?: null,
            'project_id' => $projectId ?: null,
            'message' => $message,
            'enquiry_type' => $enquiryType,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $enquiryId = $this->enquiryModel->createEnquiry($enquiryData);

            if ($enquiryId) {
                echo json_encode(['success' => true, 'message' => 'Enquiry submitted successfully! We will get back to you soon.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit enquiry. Please try again.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred while submitting your enquiry.']);
        }
    }
}

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 884 lines. Consider optimizations:
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