<?php
/**
 * Test Admin Dashboard Direct Access
 * 
 * Direct test of admin dashboard functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>Admin Dashboard Test</title>\n";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>\n";
echo "</head>\n";
echo "<body>\n";

try {
    // Test basic PHP functionality
    echo "<div class='container mt-4'>\n";
    echo "<h1>Admin Dashboard Test</h1>\n";
    
    // Test database connection
    echo "<div class='card mb-4'>\n";
    echo "<div class='card-header'>Database Connection Test</div>\n";
    echo "<div class='card-body'>\n";
    
    try {
        require_once __DIR__ . '/config/database.php';
        $db = new Database();
        echo "<div class='alert alert-success'>✅ Database connection successful!</div>\n";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>❌ Database connection failed: " . $e->getMessage() . "</div>\n";
    }
    
    echo "</div></div>\n";
    
    // Test admin files
    echo "<div class='card mb-4'>\n";
    echo "<div class='card-header'>Admin Files Test</div>\n";
    echo "<div class='card-body'>\n";
    
    $adminFiles = [
        'admin/dashboard.php',
        'admin/user_management.php', 
        'admin/property_management.php',
        'admin/unified_key_management.php'
    ];
    
    foreach ($adminFiles as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "<div class='alert alert-success'>✅ $file exists</div>\n";
        } else {
            echo "<div class='alert alert-warning'>⚠️ $file not found</div>\n";
        }
    }
    
    echo "</div></div>\n";
    
    // Test MVC components
    echo "<div class='card mb-4'>\n";
    echo "<div class='card-header'>MVC Components Test</div>\n";
    echo "<div class='card-body'>\n";
    
    $mvcFiles = [
        'app/Controllers/AdminController.php',
        'app/Models/User.php',
        'app/Models/Property.php',
        'app/Core/Security.php',
        'app/Core/Validator.php'
    ];
    
    foreach ($mvcFiles as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "<div class='alert alert-success'>✅ $file exists</div>\n";
        } else {
            echo "<div class='alert alert-warning'>⚠️ $file not found</div>\n";
        }
    }
    
    echo "</div></div>\n";
    
    // Test direct admin dashboard inclusion
    echo "<div class='card mb-4'>\n";
    echo "<div class='card-header'>Admin Dashboard Direct Test</div>\n";
    echo "<div class='card-body'>\n";
    
    try {
        // Mock the required classes and functions
        if (!class_exists('Database')) {
            class Database {
                public function __construct() {
                    // Mock database connection
                }
                public function prepare($query) {
                    return new class {
                        public function execute() { return true; }
                        public function fetchAll() { return []; }
                        public function fetch() { return null; }
                    };
                }
            }
        }
        
        if (!class_exists('Security')) {
            class Security {
                public function __construct() {}
                public function isLoggedIn() { return true; }
                public function hasRole($role) { return true; }
            }
        }
        
        // Include the dashboard
        ob_start();
        include __DIR__ . '/admin/dashboard.php';
        $dashboardOutput = ob_get_clean();
        
        echo "<div class='alert alert-success'>✅ Admin dashboard loaded successfully!</div>\n";
        echo "<div class='mt-3'>$dashboardOutput</div>\n";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>❌ Admin dashboard failed: " . $e->getMessage() . "</div>\n";
    }
    
    echo "</div></div>\n";
    
    // Test navigation links
    echo "<div class='card mb-4'>\n";
    echo "<div class='card-header'>Navigation Links Test</div>\n";
    echo "<div class='card-body'>\n";
    
    echo "<h5>Admin Navigation:</h5>\n";
    echo "<div class='list-group'>\n";
    echo "<a href='test_admin_direct.php?page=dashboard' class='list-group-item list-group-item-action'>📊 Dashboard</a>\n";
    echo "<a href='test_admin_direct.php?page=users' class='list-group-item list-group-item-action'>👥 User Management</a>\n";
    echo "<a href='test_admin_direct.php?page=properties' class='list-group-item list-group-item-action'>🏠 Property Management</a>\n";
    echo "<a href='test_admin_direct.php?page=keys' class='list-group-item list-group-item-action'>🔑 Key Management</a>\n";
    echo "</div>\n";
    
    echo "</div></div>\n";
    
    echo "</div>\n"; // Close container
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Fatal Error: " . $e->getMessage() . "</div>\n";
}

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>\n";
echo "</body>\n";
echo "</html>\n";
?>
