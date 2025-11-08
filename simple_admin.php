<?php
/**
 * APS Dream Home - Simple Admin Panel
 * Working version without complex MVC dependencies
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants
define('ROOT', __DIR__ . '/');
define('BASE_URL', '/apsdreamhome/');

// Load basic dependencies
require_once __DIR__ . '/includes/config.php';

// Simple AuthService
class SimpleAuthService {
    public function isLoggedIn(): bool {
        return isset($_SESSION['auser']);
    }

    public function isAdmin(): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public function authenticate(string $email, string $password): bool {
        $demo_users = [
            'admin@apsdreamhome.com' => ['password' => 'admin123', 'role' => 'admin'],
            'rajesh@apsdreamhome.com' => ['password' => 'agent123', 'role' => 'agent']
        ];

        if (isset($demo_users[$email]) && $demo_users[$email]['password'] === $password) {
            $_SESSION['auser'] = $demo_users[$email]['role'] === 'admin' ? 'Administrator' : ucfirst($demo_users[$email]['role']);
            $_SESSION['user_id'] = array_search($email, array_keys($demo_users)) + 1;
            $_SESSION['role'] = $demo_users[$email]['role'];
            $_SESSION['email'] = $email;
            return true;
        }

        return false;
    }

    public function logout(): void {
        session_unset();
        session_destroy();
    }
}

// Create auth service
$auth = new SimpleAuthService();

// Simple database connection
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO(
                "mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $db;
}

// Get action
$action = $_GET['action'] ?? 'dashboard';

// Handle authentication
if ($action === 'authenticate') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($auth->authenticate($email, $password)) {
            $_SESSION['success'] = 'Login successful!';
            header('Location: admin.php');
            exit();
        } else {
            $_SESSION['error'] = 'Invalid email or password';
            header('Location: admin.php');
            exit();
        }
    }
}

// Check authentication
if (!$auth->isLoggedIn()) {
    showLoginForm();
    exit();
}

if (!$auth->isAdmin()) {
    showAccessDenied();
    exit();
}

// Route to appropriate action
switch ($action) {
    case 'dashboard':
        showDashboard();
        break;
    case 'users':
        showUsers();
        break;
    case 'properties':
        showProperties();
        break;
    case 'logout':
        $auth->logout();
        header('Location: admin.php');
        exit();
        break;
    default:
        showDashboard();
        break;
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
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
            .login-card { max-width: 400px; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card login-card">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h2><i class="fas fa-user-shield text-primary me-2"></i>Admin Login</h2>
                            </div>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="admin.php?action=authenticate">
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control form-control-lg" name="email"
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? 'admin@apsdreamhome.com'); ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control form-control-lg" name="password"
                                           value="<?php echo htmlspecialchars($_POST['password'] ?? 'admin123'); ?>" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Show access denied
 */
function showAccessDenied() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - APS Dream Home</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-danger text-center">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
                        <p>You don't have permission to access the admin panel.</p>
                        <a href="admin.php" class="btn btn-primary">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Show dashboard
 */
function showDashboard() {
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

                <div class="mt-auto">
                    <a href="admin.php?action=logout" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
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
                                    <h3>0</h3>
                                    <p>Total Users</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-building fa-2x mb-2"></i>
                                    <h3>0</h3>
                                    <p>Total Properties</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <h3>0</h3>
                                    <p>Total Leads</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                    <h3>0</h3>
                                    <p>Total Bookings</p>
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

/**
 * Show users page
 */
function showUsers() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Users - Admin Panel</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            .sidebar { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
            .sidebar .nav-link { color: rgba(255, 255, 255, 0.8); border-radius: 0; }
            .sidebar .nav-link:hover,
            .sidebar .nav-link.active { color: white; background-color: rgba(255, 255, 255, 0.1); }
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
                        <a href="admin.php?action=dashboard" class="nav-link">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="admin.php?action=users" class="nav-link active">
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
                <div class="container-fluid p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-users me-2"></i>User Management</h2>
                        <a href="admin.php?action=dashboard" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted">User management functionality will be implemented here.</p>
                            <p><strong>Current User:</strong> <?= htmlspecialchars($_SESSION['auser'] ?? 'Unknown') ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email'] ?? 'N/A') ?></p>
                            <p><strong>Role:</strong> <?= htmlspecialchars($_SESSION['role'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}

/**
 * Show properties page
 */
function showProperties() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Properties - Admin Panel</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            .sidebar { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
            .sidebar .nav-link { color: rgba(255, 255, 255, 0.8); border-radius: 0; }
            .sidebar .nav-link:hover,
            .sidebar .nav-link.active { color: white; background-color: rgba(255, 255, 255, 0.1); }
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
                        <a href="admin.php?action=dashboard" class="nav-link">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="admin.php?action=users" class="nav-link">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="admin.php?action=properties" class="nav-link active">
                            <i class="fas fa-building me-2"></i>Properties
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <div class="flex-grow-1">
                <div class="container-fluid p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-building me-2"></i>Property Management</h2>
                        <a href="admin.php?action=dashboard" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted">Property management functionality will be implemented here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
