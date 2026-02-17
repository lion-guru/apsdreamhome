<?php
/**
 * Simple MVC System Test
 */

echo "🏠 APS Dream Home - SIMPLE MVC TEST\n";
echo "=================================\n\n";

// Test 1: Check MVC structure
echo "1. 🏗️ MVC STRUCTURE CHECK\n";
echo "========================\n";

$mvcFiles = [
    'app/core/App.php' => 'Application Core',
    'routes/web.php' => 'Web Routes',
    'bootstrap.php' => 'Bootstrap',
    '.htaccess' => 'Apache Config',
    '.env' => 'Environment Config'
];

foreach ($mvcFiles as $file => $desc) {
    $exists = file_exists($file) ? "✅" : "❌";
    echo "   $exists $desc\n";
}

// Test 2: Check controllers
echo "\n2. 🎮 CONTROLLERS CHECK\n";
echo "====================\n";

$controllers = [
    'app/Http/Controllers/Public/AuthController.php',
    'app/Http/Controllers/Admin/AdminDashboardController.php',
    'app/Http/Controllers/User/DashboardController.php'
];

foreach ($controllers as $controller) {
    $exists = file_exists($controller) ? "✅" : "❌";
    $name = basename(dirname($controller)) . '/' . basename($controller);
    echo "   $exists $name\n";
}

// Test 3: Check views
echo "\n3. 👁️ VIEWS CHECK\n";
echo "================\n";

$viewDirs = [
    'resources/views/' => 'Main Views',
    'resources/views/admin/' => 'Admin Views',
    'resources/views/auth/' => 'Auth Views'
];

foreach ($viewDirs as $dir => $desc) {
    $exists = is_dir($dir) ? "✅" : "❌";
    echo "   $exists $desc\n";
}

// Test 4: Check database
echo "\n4. 🗄️ DATABASE CHECK\n";
echo "==================\n";

try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($conn->connect_error) {
        echo "   ❌ Database: Connection failed\n";
    } else {
        echo "   ✅ Database: Connected\n";
        $result = $conn->query("SHOW TABLES");
        echo "   ✅ Tables: " . $result->num_rows . "\n";
        $conn->close();
    }
} catch (Exception $e) {
    echo "   ❌ Database: " . $e->getMessage() . "\n";
}

// Test 5: Check routing
echo "\n5. 🛣️ ROUTING CHECK\n";
echo "==================\n";

$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    echo "   ✅ Routes file exists\n";
    $content = file_get_contents($routesFile);
    
    $routes = [
        '/login' => strpos($content, "'/login'") !== false,
        '/admin' => strpos($content, "'/admin'") !== false,
        '/dashboard' => strpos($content, "'/dashboard'") !== false
    ];
    
    foreach ($routes as $route => $found) {
        $status = $found ? "✅" : "❌";
        echo "   $status Route $route\n";
    }
} else {
    echo "   ❌ Routes file missing\n";
}

// Test 6: Environment
echo "\n6. 🔧 ENVIRONMENT CHECK\n";
echo "====================\n";

if (file_exists('.env')) {
    echo "   ✅ .env file exists\n";
    $envContent = file_get_contents('.env');
    
    $vars = ['DB_HOST', 'DB_NAME', 'APP_NAME'];
    foreach ($vars as $var) {
        $found = strpos($envContent, $var) !== false;
        $status = $found ? "✅" : "❌";
        echo "   $status $var\n";
    }
} else {
    echo "   ❌ .env file missing\n";
}

echo "\n7. 🌐 ACCESS URLS\n";
echo "================\n";
echo "   📱 Main: http://localhost/apsdreamhome/\n";
echo "   🔐 Login: http://localhost/apsdreamhome/login\n";
echo "   🎛️  Admin: http://localhost/apsdreamhome/admin\n";
echo "   📊 Dashboard: http://localhost/apsdreamhome/dashboard\n";

echo "\n🎉 MVC TEST COMPLETED!\n";
echo "======================\n";

?>
