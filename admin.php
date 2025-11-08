<?php
/**
 * APS Dream Home - Admin Panel
 * Main entry point for admin functionality
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants only if not already defined
if (!defined('ROOT')) {
    define('ROOT', __DIR__ . '/');
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/apsdreamhome/');
}

// Load autoloader first
require_once __DIR__ . '/app/core/autoload.php';

// Load core dependencies
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/services/AuthService.php';
require_once __DIR__ . '/app/controllers/Controller.php';
require_once __DIR__ . '/app/services/AdminService.php';
require_once __DIR__ . '/app/controllers/AdminController.php';

// Create service instances
$authService = new App\Services\AuthService();
$adminController = new App\Controllers\AdminController();

// Simple routing
$action = $_GET['action'] ?? 'dashboard';
$method = $_SERVER['REQUEST_METHOD'];
// Handle authentication
if ($action === 'authenticate') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Invalid security token. Please try again.';
        header('Location: admin.php');
        exit();
    }

    $adminController->authenticate();
    exit();
}

// Check if user is logged in
if (!$authService->isLoggedIn()) {
    // Generate CSRF token if not exists
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    // Show login form
    showLoginForm();
    exit();
}

// Check if user is admin
if (!$authService->isAdmin()) {
    showAccessDenied();
    exit();
}

// Route to appropriate method based on action
switch ($action) {
    case 'dashboard':
        $adminController->dashboard();
        break;
    case 'users':
        $adminController->users();
        break;
    case 'properties':
        $adminController->properties();
        break;
    case 'bookings':
        $adminController->bookings();
        break;
    case 'leads':
        $adminController->leads();
        break;
    case 'reports':
        $adminController->reports();
        break;
    case 'settings':
        if ($method === 'POST') {
            $adminController->updateSettings();
        } else {
            $adminController->settings();
        }
        break;
    case 'database':
        if (isset($_GET['backup']) && $_GET['backup'] === 'create') {
            $adminController->createBackup();
        } else {
            $adminController->database();
        }
        break;
    case 'logs':
        $adminController->logs();
        break;
    case 'clear-cache':
        $adminController->clearCache();
        break;
    case 'export':
        $type = $_GET['type'] ?? 'users';
        $adminController->export($type);
        break;
    case 'logout':
        $authService->logout();
        header('Location: admin.php');
    default:
        showDashboard();
}

/**
 * Get dashboard statistics from database
 */
function getDashboardStats() {
    try {
        // Use the proper database connection from Database class
        $db = \App\Models\Database::getInstance();
        $pdo = $db->getConnection();

        $stats = [];

        // Total users
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
        $stats['users'] = $stmt->fetch()['count'];

        // Total properties
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM properties');
        $stats['properties'] = $stmt->fetch()['count'];

        // Total leads
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM leads');
        $stats['leads'] = $stmt->fetch()['count'];

        // Total contact messages
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM contact_messages');
        $stats['messages'] = $stmt->fetch()['count'];

        return $stats;
    } catch (Exception $e) {
        return ['users' => 0, 'properties' => 0, 'leads' => 0, 'messages' => 0];
    }
}

/**
 * Show login form
 */
function showLoginForm() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - APS Dream Home</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-card {
                max-width: 400px;
                width: 100%;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
            .login-header {
                text-align: center;
                margin-bottom: 2rem;
            }
            .login-header h2 {
                color: #333;
                font-weight: 600;
            }
            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            }
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                padding: 12px;
                font-weight: 600;
            }
            .btn-primary:hover {
                background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card login-card">
                        <div class="card-body p-5">
                            <div class="login-header">
                                <h2><i class="fas fa-user-shield text-primary me-2"></i>Admin Login</h2>
                                <p class="text-muted">Sign in to access the admin panel</p>
                            </div>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="admin.php?action=authenticate">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email"
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="Enter your email" required>
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password"
                                           placeholder="Enter your password" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>

                            <div class="mt-4 text-center">
                                <small class="text-muted">
                                    <strong>Demo Credentials:</strong><br>
                                    Use the credentials provided by your administrator
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    </body>
    </html>
    <?php
}

/**
 * Show dashboard
 */
function showDashboard() {
    $stats = getDashboardStats();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - APS Dream Home</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            .sidebar { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
            .sidebar .nav-link { color: rgba(255, 255, 255, 0.8); border-radius: 0; }
            .sidebar .nav-link:hover,
            .sidebar .nav-link.active { color: white; background-color: rgba(255, 255, 255, 0.1); }
            .main-content { min-height: 100vh; background-color: #f8f9fa; }
            .stat-card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        </style>
    </head>
    <body>
        <div class="d-flex">
            <!-- Sidebar -->
            <nav class="sidebar p-3" style="width: 250px;">
                <div class="text-center mb-4">
                    <h5><i class="fas fa-crown me-2"></i>Admin Panel</h5>
                </div>

                <ul class="nav nav-pills flex-column">
                    <li class="nav-item mb-1">
                        <a href="admin.php?action=dashboard" class="nav-link active">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="admin.php?action=users" class="nav-link">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="admin.php?action=properties" class="nav-link">
                            <i class="fas fa-building me-2"></i>Properties
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <div class="flex-grow-1">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">
                            Welcome, <?= htmlspecialchars($_SESSION['auser'] ?? 'Admin') ?>
                        </span>
                        <span class="text-muted">
                            <?= date('M d, Y H:i') ?>
                        </span>
                    </div>
                </nav>

                <!-- Dashboard Content -->
                <main class="p-4">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                            <p class="text-muted">Welcome to your admin dashboard</p>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <h3><?= number_format($stats['users']) ?></h3>
                                    <p>Total Users</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-building fa-2x mb-2"></i>
                                    <h3><?= number_format($stats['properties']) ?></h3>
                                    <p>Total Properties</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <h3><?= number_format($stats['leads']) ?></h3>
                                    <p>Total Leads</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-envelope fa-2x mb-2"></i>
                                    <h3><?= number_format($stats['messages']) ?></h3>
                                    <p>Contact Messages</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">No recent activity to display.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
function showAccessDenied() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - APS Dream Home</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-card {
                max-width: 500px;
                text-align: center;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
            .error-icon {
                font-size: 4rem;
                color: #dc3545;
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card error-card">
                        <div class="card-body p-5">
                            <div class="error-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h2 class="card-title text-danger">Access Denied</h2>
                            <p class="card-text text-muted mb-4">
                                You don't have permission to access the admin panel. Please contact your administrator if you believe this is an error.
                            </p>
                            <a href="admin.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    </body>
    </html>
    <?php
}
