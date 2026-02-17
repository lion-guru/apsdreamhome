<?php
/**
 * PERFECT ADMIN - APS Dream Home
 * Consolidated Admin Panel with All Best Features
 * Combines: Dashboard, user management, property management, analytics, and security
 */

require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

$page_title = "Perfect Admin - APS Dream Home";

// Enhanced dashboard analytics
class PerfectAnalytics {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getDashboardStats() {
        try {
            $stats = [];
            
            // Property stats
            $stats['total_properties'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM properties WHERE is_deleted = 0")['total'];
            
            $stats['available_properties'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM properties WHERE status = 'available' AND is_deleted = 0")['total'];
            
            $stats['sold_properties'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM properties WHERE status = 'sold' AND is_deleted = 0")['total'];
            
            // User stats
            $stats['total_users'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM user")['total'];
            
            $stats['total_agents'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM user WHERE utype = '2'")['total'];
            
            $stats['total_customers'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM user WHERE utype = '3'")['total'];
            
            // Booking stats
            $stats['pending_bookings'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'")['total'];
            
            $stats['confirmed_bookings'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'")['total'];
            
            // Revenue stats
            $stats['monthly_revenue'] = $this->db->fetchOne("SELECT SUM(amount) as total FROM bookings WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['total'] ?? 0;
            
            // Recent activity
            $stats['today_users'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM user WHERE DATE(join_date) = CURDATE()")['total'];
            
            $stats['today_properties'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM properties WHERE DATE(created_at) = CURDATE()")['total'];
            
            $stats['today_bookings'] = $this->db->fetchOne("SELECT COUNT(*) as total FROM bookings WHERE DATE(created_at) = CURDATE()")['total'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error fetching dashboard stats: " . $e->getMessage());
            return $this->getDefaultStats();
        }
    }
    
    private function getDefaultStats() {
        return [
            'total_properties' => 0,
            'available_properties' => 0,
            'sold_properties' => 0,
            'total_users' => 0,
            'total_agents' => 0,
            'total_customers' => 0,
            'pending_bookings' => 0,
            'confirmed_bookings' => 0,
            'monthly_revenue' => 0,
            'today_users' => 0,
            'today_properties' => 0,
            'today_bookings' => 0
        ];
    }
    
    public function getRecentActivity($limit = 10) {
        try {
            return $this->db->fetchAll("
                SELECT 
                    'user' as type,
                    u.uname as name,
                    u.join_date as date,
                    'New user registered' as description
                FROM user u
                UNION ALL
                SELECT 
                    'property' as type,
                    p.title as name,
                    p.created_at as date,
                    'New property added' as description
                FROM properties p
                WHERE p.is_deleted = 0
                UNION ALL
                SELECT 
                    'booking' as type,
                    CONCAT('Booking #', b.id) as name,
                    b.created_at as date,
                    CONCAT('New booking: ', b.status) as description
                FROM bookings b
                ORDER BY date DESC
                LIMIT ?
            ", [$limit]);
        } catch (Exception $e) {
            error_log("Error fetching recent activity: " . $e->getMessage());
            return [];
        }
    }
    
    public function getChartData() {
        try {
            // Monthly property additions
            $propertyChartData = $this->db->fetchAll("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM properties
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ");
            
            // Monthly bookings
            $bookingChartData = $this->db->fetchAll("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM bookings
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ");
            
            return [
                'properties' => $propertyChartData,
                'bookings' => $bookingChartData
            ];
        } catch (Exception $e) {
            error_log("Error fetching chart data: " . $e->getMessage());
            return ['properties' => [], 'bookings' => []];
        }
    }
}

// Enhanced admin service
class PerfectAdminService {
    private $db;
    private $analytics;
    
    public function __construct($db) {
        $this->db = $db;
        $this->analytics = new PerfectAnalytics($db);
    }
    
    public function getDashboardData() {
        return [
            'stats' => $this->analytics->getDashboardStats(),
            'recent_activity' => $this->analytics->getRecentActivity(),
            'chart_data' => $this->analytics->getChartData()
        ];
    }
    
    public function getUserList($filters = [], $page = 1, $limit = 20) {
        try {
            $whereConditions = ["1=1"];
            $params = [];
            
            if (!empty($filters['search'])) {
                $whereConditions[] = "(u.uname LIKE ? OR u.uemail LIKE ? OR u.uphone LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            if (!empty($filters['role'])) {
                $whereConditions[] = "u.utype = ?";
                $params[] = $filters['role'];
            }
            
            if (!empty($filters['status'])) {
                $whereConditions[] = "u.is_updated = ?";
                $params[] = $filters['status'];
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM user u WHERE $whereClause";
            $totalCount = $this->db->fetchOne($countQuery, $params)['total'];
            
            // Calculate pagination
            $totalPages = ceil($totalCount / $limit);
            $offset = ($page - 1) * $limit;
            
            // Get users
            $query = "
                SELECT u.*, r.role_name, 
                       DATE_FORMAT(u.join_date, '%d %b %Y') as created_date,
                       DATE_FORMAT(u.join_date, '%d %b %Y %H:%i') as last_login_formatted
                FROM user u
                LEFT JOIN roles r ON u.utype = r.id
                WHERE $whereClause
                ORDER BY u.join_date DESC
                LIMIT ? OFFSET ?
            ";
            
            $queryParams = array_merge($params, [$limit, $offset]);
            $users = $this->db->fetchAll($query, $queryParams);
            
            return [
                'users' => $users,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'total_count' => $totalCount
            ];
        } catch (Exception $e) {
            error_log("Error fetching user list: " . $e->getMessage());
            return ['users' => [], 'total_pages' => 0, 'current_page' => 1, 'total_count' => 0];
        }
    }
    
    public function getPropertyList($filters = [], $page = 1, $limit = 20) {
        try {
            $whereConditions = ["p.is_deleted = 0"];
            $params = [];
            
            if (!empty($filters['search'])) {
                $whereConditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            if (!empty($filters['type'])) {
                $whereConditions[] = "p.type = ?";
                $params[] = $filters['type'];
            }
            
            if (!empty($filters['status'])) {
                $whereConditions[] = "p.status = ?";
                $params[] = $filters['status'];
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM properties p WHERE $whereClause";
            $totalCount = $this->db->fetchOne($countQuery, $params)['total'];
            
            // Calculate pagination
            $totalPages = ceil($totalCount / $limit);
            $offset = ($page - 1) * $limit;
            
            // Get properties
            $query = "
                SELECT p.*, pt.type_name, u.uname as first_name, '' as last_name,
                       COUNT(DISTINCT pi.id) as image_count,
                       AVG(r.rating) as avg_rating,
                       COUNT(DISTINCT r.id) as review_count
                FROM properties p
                LEFT JOIN property_types pt ON p.type = pt.id
                LEFT JOIN user u ON p.agent_id = u.uid
                LEFT JOIN property_images pi ON p.id = pi.property_id
                LEFT JOIN reviews r ON p.id = r.property_id
                WHERE $whereClause
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $queryParams = array_merge($params, [$limit, $offset]);
            $properties = $this->db->fetchAll($query, $queryParams);
            
            return [
                'properties' => $properties,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'total_count' => $totalCount
            ];
        } catch (Exception $e) {
            error_log("Error fetching property list: " . $e->getMessage());
            return ['properties' => [], 'total_pages' => 0, 'current_page' => 1, 'total_count' => 0];
        }
    }
}

// Initialize components
$adminService = new PerfectAdminService($db);

// Handle authentication
$action = $_GET['action'] ?? 'dashboard';
$error = '';
$success = '';

// Check if user is logged in
if (!isAuthenticated() && $action !== 'login' && $action !== 'authenticate') {
    header('Location: login.php');
    exit;
}

// Handle login - simplified as it should be handled by login.php
if ($action === 'authenticate') {
    header('Location: login.php');
    exit;
}

// Handle logout
if ($action === 'logout') {
    destroyAuthSession();
    header('Location: login.php');
    exit;
}

// Update last activity
updateLastActivity();

// Generate CSRF token
$csrfToken = getCsrfToken();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfect Admin - APS Dream Home</title>
    
    <!-- Enhanced CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #f56565;
            --info-color: #4299e1;
            --dark-color: #2d3748;
            --light-color: #f7fafc;
            --sidebar-width: 280px;
            --header-height: 70px;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        
        /* Enhanced Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar-header {
            padding: 0 20px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }
        
        .sidebar-logo {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .sidebar.collapsed .sidebar-logo span {
            display: none;
        }
        
        .nav-item {
            margin: 5px 0;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
            border-radius: 0 25px 25px 0;
            margin-right: 10px;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar.collapsed .nav-link span {
            display: none;
        }
        
        /* Enhanced Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed + .main-content {
            margin-left: 80px;
        }
        
        /* Enhanced Header */
        .header {
            background: white;
            height: var(--header-height);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--dark-color);
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background: var(--light-color);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            border-radius: 25px;
            background: var(--light-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-profile:hover {
            background: #e2e8f0;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        /* Enhanced Stats Cards */
        .stats-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 20px;
        }
        
        .stats-icon.properties { background: var(--primary-color); }
        .stats-icon.users { background: var(--success-color); }
        .stats-icon.bookings { background: var(--warning-color); }
        .stats-icon.revenue { background: var(--info-color); }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stats-label {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .stats-change {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .stats-change.positive { color: var(--success-color); }
        .stats-change.negative { color: var(--danger-color); }
        
        /* Enhanced Tables */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        .table {
            margin: 0;
        }
        
        .table thead th {
            border: none;
            background: var(--light-color);
            color: var(--dark-color);
            font-weight: 600;
            padding: 20px 15px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 20px 15px;
            border-color: var(--border-color);
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }
        
        /* Enhanced Buttons */
        .btn {
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-success { background: var(--success-color); }
        .btn-warning { background: var(--warning-color); }
        .btn-danger { background: var(--danger-color); }
        .btn-info { background: var(--info-color); }
        
        /* Enhanced Forms */
        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        /* Enhanced Cards */
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            border-radius: 20px 20px 0 0 !important;
            padding: 20px 30px;
            font-weight: 600;
        }
        
        /* Enhanced Login Page */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-number {
                font-size: 2rem;
            }
            
            .header {
                padding: 0 15px;
            }
            
            .table-container {
                padding: 15px;
            }
        }
        
        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .spinner {
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Custom Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
    </style>
</head>
<body>
    <?php if ($action === 'login'): ?>
        <!-- Enhanced Login Page -->
        <div class="login-container">
            <div class="login-card" data-aos="fade-up">
                <div class="login-header">
                    <div class="login-logo">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="mb-2">Welcome Back</h3>
                    <p class="text-muted">Sign in to access your admin panel</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['timeout'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-clock me-2"></i>Your session has expired. Please login again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="?action=authenticate" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" name="username" required 
                                   placeholder="Enter your username" autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" name="password" required 
                                   placeholder="Enter your password" id="passwordInput">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="remember_me" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                    
                    <div class="text-center">
                        <a href="#" class="text-decoration-none">Forgot password?</a>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
            // Password toggle functionality
            document.getElementById('togglePassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('passwordInput');
                const toggleIcon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });
            
            // Form submission with loading
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
                submitBtn.disabled = true;
            });
        </script>
        
    <?php else: ?>
        <!-- Enhanced Admin Dashboard -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="?action=dashboard" class="sidebar-logo">
                    <i class="fas fa-home"></i>
                    <span>APS Dream Home</span>
                </a>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $action === 'dashboard' ? 'active' : ''; ?>" href="?action=dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $action === 'users' ? 'active' : ''; ?>" href="?action=users">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $action === 'properties' ? 'active' : ''; ?>" href="?action=properties">
                        <i class="fas fa-building"></i>
                        <span>Properties</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $action === 'bookings' ? 'active' : ''; ?>" href="?action=bookings">
                        <i class="fas fa-calendar-check"></i>
                        <span>Bookings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $action === 'leads' ? 'active' : ''; ?>" href="?action=leads">
                        <i class="fas fa-address-book"></i>
                        <span>Leads</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $action === 'reports' ? 'active' : ''; ?>" href="?action=reports">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $action === 'settings' ? 'active' : ''; ?>" href="?action=settings">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $action === 'database' ? 'active' : ''; ?>" href="?action=database">
                        <i class="fas fa-database"></i>
                        <span>Database</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $action === 'logs' ? 'active' : ''; ?>" href="?action=logs">
                        <i class="fas fa-file-alt"></i>
                        <span>Logs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="?action=logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="main-content">
            <!-- Enhanced Header -->
            <header class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="mb-0">
                        <?php
                        $titles = [
                            'dashboard' => 'Dashboard',
                            'users' => 'User Management',
                            'properties' => 'Property Management',
                            'bookings' => 'Booking Management',
                            'leads' => 'Lead Management',
                            'reports' => 'Reports & Analytics',
                            'settings' => 'System Settings',
                            'database' => 'Database Management',
                            'logs' => 'System Logs'
                        ];
                        echo $titles[$action] ?? 'Admin Panel';
                        ?>
                    </h4>
                </div>
                
                <div class="header-right">
                    <div class="dropdown">
                        <div class="user-profile dropdown-toggle" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['admin_user']['first_name'], 0, 1) . substr($_SESSION['admin_user']['last_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-semibold"><?php echo h($_SESSION['admin_user']['first_name'] . ' ' . $_SESSION['admin_user']['last_name']); ?></div>
                                <small class="text-muted"><?php echo h($_SESSION['admin_user']['role_name']); ?></small>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <div class="container-fluid py-4">
                <?php
                // Get dashboard data for all pages
                $dashboardData = $adminService->getDashboardData();
                
                switch ($action):
                    case 'dashboard':
                        include 'perfect_admin_dashboard.php';
                        break;
                    case 'users':
                        include 'perfect_admin_users.php';
                        break;
                    case 'properties':
                        include 'perfect_admin_properties.php';
                        break;
                    case 'bookings':
                        include 'perfect_admin_bookings.php';
                        break;
                    case 'leads':
                        include 'perfect_admin_leads.php';
                        break;
                    case 'reports':
                        include 'perfect_admin_reports.php';
                        break;
                    case 'settings':
                        include 'perfect_admin_settings.php';
                        break;
                    case 'database':
                        include 'perfect_admin_database.php';
                        break;
                    case 'logs':
                        include 'perfect_admin_logs.php';
                        break;
                    default:
                        echo '<div class="alert alert-info">Page not found</div>';
                endswitch;
                ?>
            </div>
        </div>
        
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner"></div>
        </div>
        
        <!-- Enhanced Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            // Sidebar toggle functionality
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
            
            // Restore sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                document.getElementById('sidebar').classList.add('collapsed');
            }
            
            // Loading functionality
            function showLoading() {
                document.getElementById('loadingOverlay').style.display = 'flex';
            }
            
            function hideLoading() {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
            
            // Enhanced confirmation dialogs
            function confirmAction(message, callback) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#f56565',
                    confirmButtonText: 'Yes, proceed!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        callback();
                    }
                });
            }
            
            // Success notifications
            function showSuccess(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
            
            // Error notifications
            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: message,
                    confirmButtonColor: '#f56565'
                });
            }
            
            // Session timeout warning
            let sessionTimeout;
            function startSessionTimer() {
                clearTimeout(sessionTimeout);
                sessionTimeout = setTimeout(function() {
                    Swal.fire({
                        title: 'Session Expiring!',
                        text: 'Your session will expire in 5 minutes. Do you want to extend it?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Extend Session',
                        cancelButtonText: 'Logout'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Extend session
                            fetch('?action=extend_session')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        showSuccess('Session extended successfully!');
                                        startSessionTimer();
                                    }
                                });
                        } else {
                            window.location.href = '?action=logout';
                        }
                    });
                }, 1500000); // 25 minutes
            }
            
            // Start session timer on page load
            startSessionTimer();
            
            // Reset timer on user activity
            document.addEventListener('click', startSessionTimer);
            document.addEventListener('keypress', startSessionTimer);
            
            // Auto-save functionality
            function autoSave(formId, endpoint) {
                const form = document.getElementById(formId);
                if (form) {
                    setInterval(function() {
                        const formData = new FormData(form);
                        fetch(endpoint, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Auto-saved successfully');
                            }
                        })
                        .catch(error => {
                            console.error('Auto-save error:', error);
                        });
                    }, 30000); // Auto-save every 30 seconds
                }
            }
            
            // Export functionality
            function exportData(type, format) {
                showLoading();
                fetch(`?action=export&type=${type}&format=${format}`)
                    .then(response => response.blob())
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `${type}_export.${format}`;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                        hideLoading();
                        showSuccess('Data exported successfully!');
                    })
                    .catch(error => {
                        hideLoading();
                        showError('Export failed. Please try again.');
                    });
            }
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl + S for save
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    const saveButton = document.querySelector('button[type="submit"]');
                    if (saveButton) {
                        saveButton.click();
                    }
                }
                
                // Ctrl + N for new
                if (e.ctrlKey && e.key === 'n') {
                    e.preventDefault();
                    const newButton = document.querySelector('.btn-primary[data-bs-toggle="modal"]');
                    if (newButton) {
                        newButton.click();
                    }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>
