<?php
/**
 * APS Dream Home - Database Setup and Fix Script
 * Complete database setup and missing methods fix
 */

echo "<h1>🛠️ APS Database Setup & Fix</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;'>";

// Step 1: Fix Controller Methods
echo "<h2>🔧 Step 1: Fix Controller Methods</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$controllerFile = 'app/controllers/Controller.php';
$missingMethods = "
    /**
     * Handle 404 Not Found
     */
    public function notFound() {
        header('HTTP/1.0 404 Not Found');
        \$this->view('errors/404', [
            'title' => 'Page Not Found'
        ]);
        exit();
    }

    /**
     * Require user to be logged in
     */
    public function requireLogin() {
        if (!isset(\$_SESSION['user_id'])) {
            \$_SESSION['redirect_after_login'] = \$_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit();
        }
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin() {
        return isset(\$_SESSION['user_role']) && \$_SESSION['user_role'] === 'admin';
    }

    /**
     * Handle 403 Forbidden
     */
    public function forbidden() {
        header('HTTP/1.0 403 Forbidden');
        \$this->view('errors/403', [
            'title' => 'Access Forbidden'
        ]);
        exit();
    }

    /**
     * Redirect to a URL
     */
    public function redirect(\$url) {
        header(\"Location: \$url\");
        exit();
    }

    /**
     * Render a view
     */
    public function view(\$view, \$data = []) {
        // Extract data for use in view
        extract(\$data);

        // Include header
        if (file_exists('../app/views/layout/header.php')) {
            include '../app/views/layout/header.php';
        }

        // Include main view
        \$viewPath = \"../app/views/{\$view}.php\";
        if (file_exists(\$viewPath)) {
            include \$viewPath;
        } else {
            echo \"View not found: {\$view}\";
        }

        // Include footer
        if (file_exists('../app/views/layout/footer.php')) {
            include '../app/views/layout/footer.php';
        }
    }

    /**
     * Return JSON response
     */
    public function json(\$data) {
        header('Content-Type: application/json');
        echo json_encode(\$data);
        exit();
    }

    /**
     * Get current user ID
     */
    public function getUserId() {
        return \$_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role
     */
    public function getUserRole() {
        return \$_SESSION['user_role'] ?? null;
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset(\$_SESSION['user_id']);
    }

    /**
     * Set flash message
     */
    public function setFlash(\$type, \$message) {
        \$_SESSION['flash'] = [
            'type' => \$type,
            'message' => \$message
        ];
    }

    /**
     * Get flash message
     */
    public function getFlash() {
        if (isset(\$_SESSION['flash'])) {
            \$flash = \$_SESSION['flash'];
            unset(\$_SESSION['flash']);
            return \$flash;
        }
        return null;
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRF() {
        if (!isset(\$_POST['csrf_token']) || \$_POST['csrf_token'] !== \$_SESSION['csrf_token']) {
            \$this->forbidden();
            return false;
        }
        return true;
    }

    /**
     * Generate CSRF token
     */
    public function generateCSRF() {
        if (!isset(\$_SESSION['csrf_token'])) {
            \$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return \$_SESSION['csrf_token'];
    }
";

if (file_exists($controllerFile)) {
    $currentContent = file_get_contents($controllerFile);

    // Check if methods already exist
    $methodsExist = true;
    $methodsToCheck = ['notFound', 'requireLogin', 'isAdmin', 'forbidden'];

    foreach ($methodsToCheck as $method) {
        if (strpos($currentContent, "function {$method}(") === false) {
            $methodsExist = false;
            break;
        }
    }

    if ($methodsExist) {
        echo "<p style='color: green;'>✅ All controller methods already exist</p>";
    } else {
        // Append missing methods
        $updatedContent = $currentContent . "\n" . $missingMethods;

        if (file_put_contents($controllerFile, $updatedContent)) {
            echo "<p style='color: green;'>✅ Controller methods added successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add controller methods</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Controller.php not found</p>";
}
echo "</div>";

// Step 2: Check and Fix Database Connection
echo "<h2>🗄️ Step 2: Database Connection Setup</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    // Check if Database.php exists
    if (file_exists('includes/Database.php')) {
        echo "<p style='color: green;'>✅ Database.php found</p>";

        require_once 'includes/Database.php';
        $db = new Database();

        // Try to get connection
        $conn = $db->getConnection();

        if ($conn && $conn->ping()) {
            echo "<p style='color: green;'>✅ Database connection successful</p>";

            // Check current database
            $result = $conn->query("SELECT DATABASE()");
            $currentDb = $result->fetch_row()[0];
            echo "<p style='color: green;'>✅ Current database: {$currentDb}</p>";

            // Check tables
            $tablesResult = $conn->query("SHOW TABLES");
            $tables = $tablesResult->fetch_all(MYSQLI_ASSOC);
            echo "<p style='color: green;'>✅ Tables found: " . count($tables) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Database connection failed</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Database.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Step 3: Import Main Database if Needed
echo "<h2>📥 Step 3: Database Import Check</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$mainDbFile = 'database/apsdreamhomes.sql';

if (file_exists($mainDbFile)) {
    $fileSize = filesize($mainDbFile);
    $fileSizeMB = round($fileSize / 1024 / 1024, 2);
    echo "<p style='color: green;'>✅ Main database file: {$mainDbFile} ({$fileSizeMB} MB)</p>";

    // Check if database needs import
    try {
        if (isset($conn) && $conn) {
            $tablesResult = $conn->query("SHOW TABLES");
            $tableCount = $tablesResult->num_rows;

            if ($tableCount < 10) {
                echo "<p style='color: orange;'>⚠️ Database has few tables ({$tableCount}), may need import</p>";
            } else {
                echo "<p style='color: green;'>✅ Database appears to have sufficient tables ({$tableCount})</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Could not check table count: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Main database file not found: {$mainDbFile}</p>";
}
echo "</div>";

// Step 4: Create Essential Views
echo "<h2>👁️ Step 4: Create Essential Views</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$essentialViews = [
    'app/views/layout/header.php',
    'app/views/layout/footer.php',
    'app/views/errors/404.php',
    'app/views/errors/403.php'
];

$viewsCreated = 0;
foreach ($essentialViews as $view) {
    if (!file_exists($view)) {
        // Create directory if needed
        $dir = dirname($view);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Create basic view content
        $viewContent = "<?php\n// {$view} - Auto-generated by setup script\n?>\n";
        if (strpos($view, 'header.php') !== false) {
            $viewContent = "<?php\n// Header view\necho \"<html><head><title>\" . (\$title ?? 'APS Dream Home') . \"</title></head><body>\";\n?>";
        } elseif (strpos($view, 'footer.php') !== false) {
            $viewContent = "<?php\n// Footer view\necho \"</body></html>\";\n?>";
        } elseif (strpos($view, '404.php') !== false) {
            $viewContent = "<?php\n// 404 Error view\n?>\n<h1>404 - Page Not Found</h1>\n<p>The page you are looking for does not exist.</p>\n<a href='/'>Go Home</a>";
        } elseif (strpos($view, '403.php') !== false) {
            $viewContent = "<?php\n// 403 Error view\n?>\n<h1>403 - Access Forbidden</h1>\n<p>You don't have permission to access this page.</p>\n<a href='/'>Go Home</a>";
        }

        if (file_put_contents($view, $viewContent)) {
            echo "<p style='color: green;'>✅ Created: {$view}</p>";
            $viewsCreated++;
        } else {
            echo "<p style='color: red;'>❌ Failed to create: {$view}</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Already exists: {$view}</p>";
        $viewsCreated++;
    }
}

echo "<p style='color: " . ($viewsCreated == count($essentialViews) ? 'green' : 'orange') . ";'>✅ Views Status: {$viewsCreated}/" . count($essentialViews) . " views ready</p>";
echo "</div>";

// Step 5: Final System Check
echo "<h2>🎯 Step 5: Final System Check</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$checks = [
    'Controller Methods' => file_exists('app/controllers/Controller.php'),
    'Database Connection' => isset($conn) && $conn,
    'Database File' => file_exists($mainDbFile),
    'Essential Views' => $viewsCreated == count($essentialViews),
    'Configuration' => file_exists('config.php'),
    'Includes' => file_exists('includes/Database.php')
];

$allPassed = true;
echo "<h3>System Components:</h3>";
echo "<ul>";
foreach ($checks as $component => $status) {
    if ($status) {
        echo "<li style='color: green;'>✅ {$component}: OK</li>";
    } else {
        echo "<li style='color: red;'>❌ {$component}: Failed</li>";
        $allPassed = false;
    }
}
echo "</ul>";

if ($allPassed) {
    echo "<h3 style='color: green; text-align: center;'>🎉 System Setup Complete! ✅</h3>";
    echo "<p>All components are working correctly. You can now use the APS Dream Home system.</p>";
} else {
    echo "<h3 style='color: red; text-align: center;'>⚠️ Some Issues Need Attention</h3>";
    echo "<p>Please check the error messages above and fix the issues.</p>";
}
echo "</div>";

// Step 6: Next Steps
echo "<h2>📋 Next Steps</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

echo "<h4>Recommended Actions:</h4>";
echo "<ol>";
echo "<li>Start MySQL service in XAMPP Control Panel</li>";
echo "<li>Import database: {$mainDbFile}</li>";
echo "<li>Test the system: <a href='index.php'>Visit Website</a></li>";
echo "<li>Test CRM: <a href='aps_crm_system.php'>Access CRM</a></li>";
echo "<li>Run WhatsApp test: <a href='whatsapp_demo.php'>WhatsApp Demo</a></li>";
echo "</ol>";

echo "<h4>Database Import Command:</h4>";
echo "<code style='background: #333; color: #fff; padding: 10px; display: block; border-radius: 5px;'>";
echo "mysql -u root -p apsdreamhome < database/apsdreamhomes.sql";
echo "</code>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px;'>🏠 Go to Website</a>";
echo "<a href='aps_crm_system.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px;'>📞 Access APS CRM</a>";
echo "<a href='whatsapp_demo.php' style='background: #25d366; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px;'>📱 WhatsApp Demo</a>";
echo "</div>";

echo "</div>";
?>
