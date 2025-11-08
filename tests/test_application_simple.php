<?php
echo "Testing APS Dream Home Application (Simple Test)\n";
echo "==============================================\n\n";

// Test 1: Check if config file exists and loads
echo "Test 1: Configuration Loading\n";
if (file_exists('config.php')) {
    echo "✓ config.php found\n";
    require_once 'config.php';
    echo "✓ config.php loaded successfully\n";
} else {
    echo "✗ config.php not found\n";
}

// Test 2: Check database connection
echo "\nTest 2: Database Connection\n";
try {
    $db = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($db->connect_error) {
        echo "✗ Database connection failed: " . $db->connect_error . "\n";
    } else {
        echo "✓ Database connection successful\n";
        $db->close();
    }
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
}

// Test 3: Check if required files exist
echo "\nTest 3: Core Files\n";
$files = [
    'app/core/App.php' => 'App class',
    'app/core/Controller.php' => 'Controller class',
    'app/controllers/HomeController.php' => 'HomeController',
    'app/models/User.php' => 'User model',
    'app/models/Property.php' => 'Property model',
    'app/views/home/index.php' => 'Home view'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description exists\n";
    } else {
        echo "✗ $description not found\n";
    }
}

// Test 4: Check if required tables exist
echo "\nTest 4: Database Tables\n";
try {
    $db = new mysqli('localhost', 'root', '', 'apsdreamhome');
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

// Test 5: Check if .env file exists
echo "\nTest 5: Environment Configuration\n";
if (file_exists('.env')) {
    echo "✓ .env file found\n";
    $env_content = file_get_contents('.env');
    if (strpos($env_content, 'DB_HOST') !== false) {
        echo "✓ Database configuration found in .env\n";
    } else {
        echo "✗ Database configuration missing in .env\n";
    }

    if (strpos($env_content, 'MAIL_') !== false) {
        echo "✓ Email configuration found in .env\n";
    } else {
        echo "✗ Email configuration missing in .env\n";
    }
} else {
    echo "✗ .env file not found\n";
}

echo "\nTest Summary:\n";
echo "============\n";
echo "The application structure is in place.\n";
echo "Next steps:\n";
echo "1. Start a web server (XAMPP Apache)\n";
echo "2. Access the application through browser\n";
echo "3. Test login, registration, property listing\n";
echo "4. Verify email verification system\n";
echo "5. Test payment integration\n";
?>
