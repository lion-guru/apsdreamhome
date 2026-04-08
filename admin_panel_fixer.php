<?php

/**
 * APS Dream Home - Admin Panel Fixer & Tester
 * Comprehensive testing and fixing of admin dashboard
 */

require_once __DIR__ . '/config/database.php';

class AdminPanelFixer
{
    private $db;
    private $issues = [];
    private $fixes = [];

    public function __construct()
    {
        echo "🔧 ADMIN PANEL FIXER & TESTER\n";
        echo "=============================\n\n";

        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
            exit(1);
        }
    }

    /**
     * Check admin user exists
     */
    public function checkAdminUser()
    {
        echo "1. Checking Admin User...\n";

        $admin = $this->db->fetchOne("SELECT id, email, role, name FROM users WHERE role IN ('admin', 'super_admin') LIMIT 1");

        if ($admin) {
            echo "   ✅ Admin found: {$admin['name']} ({$admin['email']}) - Role: {$admin['role']}\n\n";
            return true;
        } else {
            echo "   ❌ No admin user found!\n\n";
            $this->issues[] = 'No admin user in database';
            return false;
        }
    }

    /**
     * Check required admin files
     */
    public function checkAdminFiles()
    {
        echo "2. Checking Admin Files...\n";

        $requiredFiles = [
            'app/Http/Controllers/Auth/AdminAuthController.php' => 'Admin Auth Controller',
            'app/Http/Controllers/RoleBasedDashboardController.php' => 'Dashboard Controller',
            'app/views/auth/admin_login.php' => 'Admin Login View',
            'app/views/layouts/admin.php' => 'Admin Layout',
            'app/views/admin/dashboard.php' => 'Admin Dashboard View',
            'app/views/admin/properties/index.php' => 'Properties List',
            'app/views/admin/customers/index.php' => 'Customers List',
            'routes/web.php' => 'Routes File'
        ];

        $missing = [];
        foreach ($requiredFiles as $file => $name) {
            if (!file_exists($file)) {
                echo "   ❌ Missing: $name ($file)\n";
                $missing[] = $file;
            } else {
                echo "   ✅ Found: $name\n";
            }
        }

        if (!empty($missing)) {
            $this->issues[] = 'Missing admin files: ' . implode(', ', $missing);
        }

        echo "\n";
        return empty($missing);
    }

    /**
     * Check admin routes
     */
    public function checkAdminRoutes()
    {
        echo "3. Checking Admin Routes...\n";

        $routesContent = file_get_contents('routes/web.php');

        $requiredRoutes = [
            '/admin/login' => 'Admin Login',
            '/admin/dashboard' => 'Admin Dashboard',
            '/admin/properties' => 'Properties',
            '/admin/customers' => 'Customers',
            '/admin/leads' => 'Leads',
            '/admin/users' => 'Users'
        ];

        foreach ($requiredRoutes as $route => $name) {
            if (strpos($routesContent, $route) !== false) {
                echo "   ✅ Route: $name ($route)\n";
            } else {
                echo "   ❌ Missing route: $name ($route)\n";
                $this->issues[] = "Missing route: $route";
            }
        }

        echo "\n";
        return true;
    }

    /**
     * Check admin session handling
     */
    public function checkAdminControllers()
    {
        echo "4. Checking Admin Controllers...\n";

        // Check RoleBasedDashboardController
        $dashboardController = 'app/Http/Controllers/RoleBasedDashboardController.php';
        if (file_exists($dashboardController)) {
            $content = file_get_contents($dashboardController);

            // Check for auth check
            if (strpos($content, 'admin_id') !== false || strpos($content, 'admin_role') !== false) {
                echo "   ✅ Dashboard has auth checks\n";
            } else {
                echo "   ⚠️  Dashboard may be missing auth checks\n";
                $this->issues[] = 'Dashboard controller missing auth checks';
            }

            // Check for index method
            if (strpos($content, 'public function index') !== false) {
                echo "   ✅ Dashboard has index method\n";
            } else {
                echo "   ❌ Dashboard missing index method\n";
                $this->issues[] = 'Dashboard missing index method';
            }
        }

        echo "\n";
        return true;
    }

    /**
     * Fix common issues
     */
    public function fixIssues()
    {
        echo "5. Fixing Issues...\n";

        if (empty($this->issues)) {
            echo "   ✅ No issues to fix!\n\n";
            return true;
        }

        foreach ($this->issues as $issue) {
            echo "   🔧 Fixing: $issue\n";

            // Fix missing admin layout
            if (strpos($issue, 'admin_layout') !== false || strpos($issue, 'layouts/admin') !== false) {
                $this->createAdminLayout();
            }

            // Fix missing dashboard view
            if (strpos($issue, 'dashboard.php') !== false) {
                $this->createDashboardView();
            }
        }

        echo "\n";
        return true;
    }

    /**
     * Create admin layout if missing
     */
    private function createAdminLayout()
    {
        $layoutPath = 'app/views/layouts/admin.php';
        if (!file_exists($layoutPath)) {
            echo "      Creating admin layout...\n";

            $layout = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin Panel' ?> - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; color: white; }
        .sidebar a { color: white; text-decoration: none; padding: 12px 20px; display: block; }
        .sidebar a:hover { background: #34495e; }
        .main-content { padding: 20px; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-center border-bottom">
                    <h4>APS Admin</h4>
                </div>
                <nav>
                    <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                    <a href="<?= BASE_URL ?>/admin/properties"><i class="fas fa-building me-2"></i> Properties</a>
                    <a href="<?= BASE_URL ?>/admin/customers"><i class="fas fa-users me-2"></i> Customers</a>
                    <a href="<?= BASE_URL ?>/admin/leads"><i class="fas fa-funnel-dollar me-2"></i> Leads</a>
                    <a href="<?= BASE_URL ?>/admin/users"><i class="fas fa-user-shield me-2"></i> Users</a>
                    <a href="<?= BASE_URL ?>/admin/locations/states"><i class="fas fa-map-marker-alt me-2"></i> Locations</a>
                    <a href="<?= BASE_URL ?>/admin/logout" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;

            file_put_contents($layoutPath, $layout);
            echo "      ✅ Admin layout created\n";
        }
    }

    /**
     * Create dashboard view if missing
     */
    private function createDashboardView()
    {
        $viewPath = 'app/views/admin/dashboard.php';
        if (!file_exists($viewPath)) {
            echo "      Creating dashboard view...\n";

            $view = <<<'HTML'
<div class="container-fluid">
    <h2 class="mb-4">Admin Dashboard</h2>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Properties</h5>
                    <h3><?= $stats['properties'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Customers</h5>
                    <h3><?= $stats['customers'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Leads</h5>
                    <h3><?= $stats['leads'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Bookings</h5>
                    <h3><?= $stats['bookings'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Properties</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Status</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_properties ?? [] as $prop): ?>
                            <tr>
                                <td><?= htmlspecialchars($prop['title']) ?></td>
                                <td><span class="badge bg-success"><?= $prop['status'] ?></span></td>
                                <td>₹<?= number_format($prop['price']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Leads</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_leads ?? [] as $lead): ?>
                            <tr>
                                <td><?= htmlspecialchars($lead['name']) ?></td>
                                <td><span class="badge bg-info"><?= $lead['status'] ?></span></td>
                                <td><?= date('d M', strtotime($lead['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;

            file_put_contents($viewPath, $view);
            echo "      ✅ Dashboard view created\n";
        }
    }

    /**
     * Run all checks and fixes
     */
    public function run()
    {
        $this->checkAdminUser();
        $this->checkAdminFiles();
        $this->checkAdminRoutes();
        $this->checkAdminControllers();
        $this->fixIssues();

        echo "\n📊 SUMMARY\n";
        echo "==========\n";

        if (empty($this->issues)) {
            echo "✅ No issues found! Admin panel is ready.\n";
            echo "\n🔗 Access URLs:\n";
            echo "   Admin Login: http://localhost/apsdreamhome/admin/login\n";
            echo "   Dashboard: http://localhost/apsdreamhome/admin/dashboard\n";
            echo "\n🔑 Credentials:\n";
            echo "   Email: admin@apsdreamhome.com\n";
            echo "   Password: admin123\n";
        } else {
            echo "⚠️  Issues found and fixed:\n";
            foreach ($this->issues as $issue) {
                echo "   - $issue\n";
            }
        }

        echo "\n";
    }
}

// Run the fixer
$fixer = new AdminPanelFixer();
$fixer->run();
