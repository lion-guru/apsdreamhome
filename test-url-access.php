<?php

/**
 * APS Dream Home - URL Access Test
 * Test why localhost/apsdreamhome/ is not opening in browser
 */

echo "=== APS Dream Home - URL Access Test ===\n\n";

echo "🔍 TESTING URL ACCESS ISSUES...\n\n";

// Test 1: Check if Apache is actually running
echo "1. 🌐 APACHE STATUS TEST:\n";
$apacheTest = @file_get_contents('http://localhost/');
if ($apacheTest !== false) {
    echo "   ✅ Apache is responding on http://localhost/\n";
    echo "   📄 Response length: " . strlen($apacheTest) . " characters\n";
} else {
    echo "   ❌ Apache is NOT responding on http://localhost/\n";
    echo "   🔧 SOLUTION: Start Apache in XAMPP Control Panel\n";
}

// Test 2: Check if project directory is accessible
echo "\n2. 📁 PROJECT DIRECTORY TEST:\n";
$projectTest = @file_get_contents('http://localhost/apsdreamhome/');
if ($projectTest !== false) {
    echo "   ✅ Project is accessible on http://localhost/apsdreamhome/\n";
    echo "   📄 Response length: " . strlen($projectTest) . " characters\n";
    
    // Check if response contains our content
    if (strpos($projectTest, 'APS Dream Home') !== false) {
        echo "   ✅ Response contains 'APS Dream Home'\n";
    } else {
        echo "   ⚠️ Response missing 'APS Dream Home'\n";
        echo "   📄 First 200 chars: " . substr($projectTest, 0, 200) . "...\n";
    }
} else {
    echo "   ❌ Project is NOT accessible on http://localhost/apsdreamhome/\n";
    
    // Try alternative URLs
    $altUrls = [
        'http://localhost/apsdreamhome/index.php',
        'http://127.0.0.1/apsdreamhome/',
        'http://127.0.0.1/apsdreamhome/index.php'
    ];
    
    foreach ($altUrls as $url) {
        $test = @file_get_contents($url);
        if ($test !== false) {
            echo "   ✅ Alternative URL works: $url\n";
            echo "   🔧 USE THIS URL INSTEAD!\n";
            break;
        }
    }
}

// Test 3: Check if .htaccess exists
echo "\n3. 📋 .htaccess FILE CHECK:\n";
$htaccess = __DIR__ . '/.htaccess';
if (file_exists($htaccess)) {
    echo "   ✅ .htaccess file exists\n";
    echo "   📄 Content: " . file_get_contents($htaccess) . "\n";
} else {
    echo "   ❌ .htaccess file missing\n";
    echo "   🔧 CREATING .htaccess...\n";
    
    $htaccessContent = "
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection \"1; mode=block\"

# PHP settings
php_value display_errors On
php_value error_reporting E_ALL
";
    
    file_put_contents($htaccess, $htaccessContent);
    echo "   ✅ .htaccess file created\n";
}

// Test 4: Check directory permissions
echo "\n4. 🔐 PERMISSIONS CHECK:\n";
$indexFile = __DIR__ . '/index.php';
if (is_readable($indexFile)) {
    echo "   ✅ index.php is readable\n";
} else {
    echo "   ❌ index.php is NOT readable\n";
}

// Test 5: Check Apache configuration
echo "\n5. ⚙️  APACHE CONFIG CHECK:\n";
echo "   📋 Expected Apache DocumentRoot: C:/xampp/htdocs/apsdreamhome\n";
echo "   📋 Current working directory: " . __DIR__ . "\n";

echo "\n🎯 DIAGNOSIS RESULTS:\n";
echo str_repeat("=", 50) . "\n";

echo "🔍 MOST LIKELY ISSUES:\n";
echo "1. ❌ Apache not running in XAMPP\n";
echo "2. ❌ Wrong URL (try alternatives)\n";
echo "3. ❌ Port conflict (try port 8080)\n";
echo "4. ❌ Missing .htaccess file\n";
echo "5. ❌ Apache configuration issues\n";

echo "\n💡 IMMEDIATE SOLUTIONS:\n";
echo "1. 🚀 START XAMPP APACHE:\n";
echo "   • Open XAMPP Control Panel\n";
echo "   • Click 'Start' button next to Apache\n";
echo "   • Wait for green checkmark\n";
echo "   • Try URL again\n\n";

echo "2. 🔧 TRY ALTERNATIVE URLS:\n";
echo "   • http://localhost/apsdreamhome/index.php\n";
echo "   • http://127.0.0.1/apsdreamhome/\n";
echo "   • http://localhost:8080/apsdreamhome/\n\n";

echo "3. 🌐 BROWSER TROUBLESHOOTING:\n";
echo "   • Clear browser cache (Ctrl+F5)\n";
echo "   • Try different browser\n";
echo "   • Check browser console (F12)\n";
echo "   • Disable browser extensions\n\n";

echo "4. 📋 XAMPP CONFIGURATION:\n";
echo "   • Ensure Apache DocumentRoot: C:/xampp/htdocs/apsdreamhome\n";
echo "   • Ensure AllowOverride is set to All\n";
echo "   • Check for port conflicts (80, 443, 8080)\n";

echo "\n✨ TEST COMPLETE! ✨\n";
echo "\n🎯 NEXT STEPS:\n";
echo "1. Start Apache in XAMPP Control Panel\n";
echo "2. Open browser and try: http://localhost/apsdreamhome/\n";
echo "3. If not working, try: http://localhost/apsdreamhome/index.php\n";
echo "4. Check browser console for errors (F12)\n";
?>
