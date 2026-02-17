<?php
/**
 * Comprehensive Application Diagnostic Test
 * Tests all major components for runtime errors
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

echo "=== APS Dream Home - Comprehensive Diagnostic Test ===\n\n";

// Test 1: Check PHP Environment
echo "1. PHP Environment Check:\n";
echo "✓ PHP Version: " . PHP_VERSION . "\n";
echo "✓ PHP Extensions: PDO=" . (extension_loaded('pdo') ? '✓' : '✗') . ", MySQLi=" . (extension_loaded('mysqli') ? '✓' : '✗') . "\n";

// Test 2: Check File Structure
echo "\n2. File Structure Check:\n";
$required_files = [
    'includes/config.php',
    'includes/db_connection.php',
    'includes/enhanced_universal_template.php',
    'app/core/Database.php',
    'app/core/Model.php',
    'app/core/App.php',
    'app/services/AuthService.php',
    'app/controllers/AdminController.php',
    'app/controllers/Controller.php',
    'routes/api.php',
    'routes/web.php'
];

foreach ($required_files as $file) {
    $status = file_exists($file) ? '✓' : '✗';
    echo "{$status} {$file}\n";
}

// Test 3: Database Connection Test
echo "\n3. Database Connection Test:\n";
try {
    require_once 'includes/db_connection.php';
    if (isset($pdo) && $pdo) {
        $stmt = $pdo->query('SELECT 1');
        $result = $stmt->fetch();
        echo "✓ Database connection successful\n";
        echo "✓ Test query executed successfully\n";
    } else {
        echo "✗ Database connection failed - PDO not initialized\n";
    }
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
}

// Test 4: Core Classes Test
echo "\n4. Core Classes Test:\n";
try {
    require_once 'app/core/Database.php';
    $db = \App\Core\App::database();
    if ($db) {
        echo "✓ Database singleton created successfully\n";
    } else {
        echo "✗ Database singleton creation failed\n";
    }
} catch (Exception $e) {
    echo "✗ Database singleton error: " . $e->getMessage() . "\n";
}

// Test 5: Model System Test
echo "\n5. Model System Test:\n";
try {
    require_once 'app/core/Model.php';
    require_once 'app/models/User.php';

    // Test if we can create a User instance
    try {
        // Test if User class can be instantiated (basic test)
        $userReflection = new ReflectionClass('App\Models\User');
        if ($userReflection->isSubclassOf('App\Core\Model')) {
            echo "✓ User model properly extends Model class\n";
        } else {
            echo "✗ User model does not extend Model class properly\n";
        }
    } catch (Exception $e) {
        echo "✗ User model error: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "✗ User model error: " . $e->getMessage() . "\n";
}

// Test 6: Service Layer Test
echo "\n6. Service Layer Test:\n";
try {
    require_once 'app/services/AuthService.php';
    $authService = new App\Services\AuthService();

    if (method_exists($authService, 'isLoggedIn')) {
        echo "✓ AuthService instantiated successfully\n";
        echo "✓ isLoggedIn method exists\n";
    } else {
        echo "✗ AuthService missing required methods\n";
    }
} catch (Exception $e) {
    echo "✗ AuthService error: " . $e->getMessage() . "\n";
}

// Test 7: Controller Test
echo "\n7. Controller Test:\n";
try {
    require_once 'app/controllers/Controller.php';

    // Test if Controller class can be instantiated (basic test)
    $controllerReflection = new ReflectionClass('App\Controllers\Controller');
    if ($controllerReflection->isAbstract()) {
        echo "✓ Controller class exists and is abstract (correct)\n";
    } else {
        echo "✗ Controller class should be abstract\n";
    }
} catch (Exception $e) {
    echo "✗ Controller class error: " . $e->getMessage() . "\n";
}

// Test 8: Session Test
echo "\n8. Session Test:\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "✓ Session started successfully\n";
} else {
    echo "✓ Session already active\n";
}

if (isset($_SESSION)) {
    echo "✓ Session variables accessible\n";
} else {
    echo "✗ Session variables not accessible\n";
}

// Test 9: Configuration Test
echo "\n9. Configuration Test:\n";
try {
    require_once 'includes/config.php';

    if (defined('DB_HOST') && defined('DB_NAME')) {
        echo "✓ Database configuration loaded\n";
        echo "✓ DB_HOST: " . DB_HOST . "\n";
        echo "✓ DB_NAME: " . DB_NAME . "\n";
    } else {
        echo "✗ Database configuration missing\n";
    }
} catch (Exception $e) {
    echo "✗ Configuration error: " . $e->getMessage() . "\n";
}

// Test 10: Routes Test
echo "\n10. Routes Test:\n";
try {
    $apiRoutes = require 'routes/api.php';
    if (is_array($apiRoutes)) {
        echo "✓ API routes loaded successfully\n";
        echo "✓ Routes structure: " . count($apiRoutes) . " sections\n";
    } else {
        echo "✗ API routes not loaded properly\n";
    }
} catch (Exception $e) {
    echo "✗ API routes error: " . $e->getMessage() . "\n";
}

try {
    $webRoutes = require 'routes/web.php';
    if (is_array($webRoutes)) {
        echo "✓ Web routes loaded successfully\n";
        echo "✓ Routes structure: " . count($webRoutes) . " sections\n";

        // Check if routes are properly organized
        $expectedSections = ['public', 'authenticated', 'associate', 'employee', 'customer', 'admin'];
        foreach ($expectedSections as $section) {
            if (isset($webRoutes[$section])) {
                $totalRoutes = 0;
                foreach ($webRoutes[$section] as $method => $routes) {
                    $totalRoutes += count($routes);
                }
                echo "✓ {$section} section: {$totalRoutes} routes\n";
            } else {
                echo "⚠ {$section} section missing\n";
            }
        }
    } else {
        echo "✗ Web routes not loaded properly\n";
    }
} catch (Exception $e) {
    echo "✗ Web routes error: " . $e->getMessage() . "\n";
}

// Test 11: Template System Test
echo "\n11. Template System Test:\n";
try {
    require_once 'includes/enhanced_universal_template.php';

    if (class_exists('EnhancedUniversalTemplate')) {
        echo "✓ EnhancedUniversalTemplate class exists\n";

        $template = new EnhancedUniversalTemplate();
        if ($template) {
            echo "✓ Template instance created successfully\n";
        } else {
            echo "✗ Template instance creation failed\n";
        }
    } else {
        echo "✗ EnhancedUniversalTemplate class not found\n";
    }
} catch (Exception $e) {
    echo "✗ Template system error: " . $e->getMessage() . "\n";
}

// Test 12: Required Tables Check
echo "\n12. Database Tables Check:\n";
try {
    if (isset($pdo) && $pdo) {
        $required_tables = ['users', 'properties', 'leads', 'lead_notes', 'lead_files'];
        $existing_tables = [];

        foreach ($required_tables as $table) {
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
                if ($stmt->rowCount() > 0) {
                    $existing_tables[] = $table;
                    echo "✓ Table '{$table}' exists\n";
                } else {
                    echo "✗ Table '{$table}' missing\n";
                }
            } catch (Exception $e) {
                echo "✗ Error checking table '{$table}': " . $e->getMessage() . "\n";
            }
        }

        if (count($existing_tables) === count($required_tables)) {
            echo "✓ All required tables exist\n";
        } else {
            echo "⚠ Some tables are missing (" . (count($required_tables) - count($existing_tables)) . " missing)\n";
        }
    } else {
        echo "✗ Cannot check tables - no database connection\n";
    }
} catch (Exception $e) {
    echo "✗ Database tables check error: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Test Complete ===\n";
echo "\nSUMMARY:\n";
echo "If you see any ✗ or ⚠ symbols above, those indicate areas that need attention.\n";
echo "If all tests show ✓, your application should be running properly.\n";
?>

