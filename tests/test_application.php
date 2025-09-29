<?php
echo "Testing APS Dream Home Application\n";
echo "===================================\n\n";

// Test 1: Check if config file exists and loads
echo "Test 1: Configuration Loading\n";
if (file_exists('config.php')) {
    echo "✓ config.php found\n";
} else {
    echo "✗ config.php not found\n";
}

// Test 2: Check if autoload exists
echo "\nTest 2: Autoloader\n";
if (file_exists('app/core/autoload.php')) {
    echo "✓ Autoloader found\n";
    require_once 'app/core/autoload.php';
    echo "✓ Autoloader loaded successfully\n";
} else {
    echo "✗ Autoloader not found\n";
}

// Test 3: Check if core classes exist
echo "\nTest 3: Core Classes\n";
if (class_exists('App\Core\App')) {
    echo "✓ App class exists\n";
} else {
    echo "✗ App class not found\n";
}

if (class_exists('App\Core\Controller')) {
    echo "✓ Controller class exists\n";
} else {
    echo "✗ Controller class not found\n";
}

if (class_exists('App\Core\Database')) {
    echo "✓ Database class exists\n";
} else {
    echo "✗ Database class not found\n";
}

// Test 4: Check if models exist
echo "\nTest 4: Models\n";
if (class_exists('App\Models\User')) {
    echo "✓ User model exists\n";
} else {
    echo "✗ User model not found\n";
}

if (class_exists('App\Models\Property')) {
    echo "✓ Property model exists\n";
} else {
    echo "✗ Property model not found\n";
}

// Test 5: Check if controllers exist
echo "\nTest 5: Controllers\n";
if (class_exists('App\Controllers\HomeController')) {
    echo "✓ HomeController exists\n";
} else {
    echo "✗ HomeController not found\n";
}

if (class_exists('App\Controllers\AuthController')) {
    echo "✓ AuthController exists\n";
} else {
    echo "✗ AuthController not found\n";
}

// Test 6: Check database connection
echo "\nTest 6: Database Connection\n";
try {
    $db = new mysqli('localhost', 'root', '', 'apsdreamhomefinal');
    if ($db->connect_error) {
        echo "✗ Database connection failed: " . $db->connect_error . "\n";
    } else {
        echo "✓ Database connection successful\n";
        $db->close();
    }
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
}

// Test 7: Check if required tables exist
echo "\nTest 7: Database Tables\n";
try {
    $db = new mysqli('localhost', 'root', '', 'apsdreamhomefinal');
    if (!$db->connect_error) {
        $tables = ['users', 'properties', 'email_verifications', 'payments'];
        foreach ($tables as $table) {
            $result = $db->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "✓ Table '$table' exists\n";
            } else {
                echo "✗ Table '$table' not found\n";
            }
        }
        $db->close();
    }
} catch (Exception $e) {
    echo "✗ Could not check database tables: " . $e->getMessage() . "\n";
}

echo "\nTest Summary:\n";
echo "============\n";
echo "Basic application structure appears to be in place.\n";
echo "Next steps: Run the application through web server.\n";
?>
