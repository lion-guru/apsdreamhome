<?php

/**
 * APS Dream Home - Home Page Issue Diagnosis
 * Diagnose and fix home page loading issues
 */

echo "=== APS Dream Home - Home Page Issue Diagnosis ===\n\n";

echo "🔍 DIAGNOSING HOME PAGE ISSUES...\n\n";

// Check if XAMPP is running properly
echo "1. 🌐 XAMPP STATUS CHECK:\n";
$apacheRunning = false;
$mysqlRunning = false;

// Try to connect to MySQL
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    if ($pdo) {
        echo "   ✅ MySQL connection successful\n";
        $mysqlRunning = true;
    }
} catch (PDOException $e) {
    echo "   ❌ MySQL connection failed: " . $e->getMessage() . "\n";
}

// Check if Apache is serving PHP
echo "\n2. 📁 FILE STRUCTURE CHECK:\n";
$requiredFiles = [
    'index.php' => 'Main entry point',
    'app/core/App.php' => 'Application class',
    'app/Http/Controllers/HomeController.php' => 'Home controller',
    'app/views/home/index.php' => 'Home view',
    'app/views/layouts/base.php' => 'Base layout'
];

foreach ($requiredFiles as $file => $description) {
    $path = __DIR__ . '/' . $file;
    $status = file_exists($path) ? '✅' : '❌';
    echo "   $status $file - $description\n";
}

echo "\n3. 🔧 COMMON HOME PAGE ISSUES:\n";
echo "   ❌ XAMPP Apache not running\n";
echo "   ❌ MySQL not running\n";
echo "   ❌ Missing .htaccess file\n";
echo "   ❌ Incorrect base URL\n";
echo "   ❌ Missing CSS/JS files\n";
echo "   ❌ PHP errors in controllers/views\n";

echo "\n4. 💡 QUICK SOLUTIONS:\n";

echo "\n   SOLUTION 1: Check XAMPP Status\n";
echo "   • Open XAMPP Control Panel\n";
echo "   • Ensure Apache and MySQL are running\n";
echo "   • Check for port conflicts (80, 443, 3306)\n";

echo "\n   SOLUTION 2: Test Direct PHP\n";
echo "   • Try: http://localhost/apsdreamhome/index.php\n";
echo "   • Try: http://localhost/apsdreamhome/?test=1\n";

echo "\n   SOLUTION 3: Check Error Logs\n";
echo "   • XAMPP: C:/xampp/apache/logs/error.log\n";
echo "   • PHP: Check php.ini for error_log setting\n";

echo "\n   SOLUTION 4: Browser Debug\n";
echo "   • Open browser developer tools (F12)\n";
echo "   • Check Console tab for JavaScript errors\n";
echo "   • Check Network tab for failed requests\n";
echo "   • Check Response tab for server errors\n";

echo "\n5. 🚀 IMMEDIATE TESTS:\n";

echo "\n   TEST 1: Direct PHP Execution\n";
echo "   Open browser and go to: http://localhost/apsdreamhome/\n";
echo "   If you see 'Helper functions defined successfully' - PHP is working\n";

echo "\n   TEST 2: Check Apache Configuration\n";
echo "   Ensure DocumentRoot points to: C:/xampp/htdocs/apsdreamhome\n";
echo "   Ensure AllowOverride is set to All for .htaccess support\n";

echo "\n   TEST 3: Check Base URL\n";
echo "   Current BASE_URL: http://localhost/apsdreamhome/\n";
echo "   If using different port, update BASE_URL in files\n";

echo "\n6. 🔍 DEBUGGING STEPS:\n";
echo "   1. Verify XAMPP Apache is running (green checkmark)\n";
echo "   2. Verify XAMPP MySQL is running (green checkmark)\n";
echo "   3. Try http://localhost/apsdreamhome/ in browser\n";
echo "   4. Check browser console for errors\n";
echo "   5. Check XAMPP error logs\n";

echo "\n📊 MOST LIKELY ISSUES:\n";
echo "   1. XAMPP Apache not running\n";
echo "   2. Incorrect URL (try http://localhost/apsdreamhome/)\n";
echo "   3. Port conflict (Apache not on port 80)\n";
echo "   4. Missing .htaccess file\n";

echo "\n✨ DIAGNOSIS COMPLETE! ✨\n";
echo "\n🎯 NEXT STEPS:\n";
echo "1. Start XAMPP Apache and MySQL\n";
echo "2. Open http://localhost/apsdreamhome/ in browser\n";
echo "3. Check browser console for errors\n";
echo "4. If still not working, check error logs\n";
?>
