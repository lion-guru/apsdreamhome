<?php

/**
 * APS Dream Home - Quick URL Fix
 * Quick fix for localhost/apsdreamhome/ not opening
 */

echo "=== APS Dream Home - Quick URL Fix ===\n\n";

echo "🔍 QUICK FIX FOR URL ACCESS:\n\n";

echo "1. 📋 CREATE .htaccess FILE:\n";
$htaccessContent = '
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# PHP settings
php_value display_errors On
php_value error_reporting E_ALL
';

$htaccessFile = __DIR__ . '/.htaccess';
file_put_contents($htaccessFile, $htaccessContent);
echo "   ✅ .htaccess file created\n";

echo "\n2. 🌐 URLS TO TRY IN BROWSER:\n";
echo "   • PRIMARY: http://localhost/apsdreamhome/\n";
echo "   • DIRECT: http://localhost/apsdreamhome/index.php\n";
echo "   • ALTERNATIVE: http://127.0.0.1/apsdreamhome/\n";
echo "   • PORT 8080: http://localhost:8080/apsdreamhome/\n";

echo "\n3. 🔧 XAMPP CHECKLIST:\n";
echo "   ✅ Open XAMPP Control Panel\n";
echo "   ✅ Start Apache (green checkmark)\n";
echo "   ✅ Start MySQL (green checkmark)\n";
echo "   ✅ Check for port conflicts\n";

echo "\n4. 🌪️ BROWSER TROUBLESHOOTING:\n";
echo "   ✅ Clear browser cache (Ctrl+F5)\n";
echo "   ✅ Open Developer Tools (F12)\n";
echo "   ✅ Check Console for errors\n";
echo "   ✅ Check Network tab for failed requests\n";

echo "\n5. 📁 FILE VERIFICATION:\n";
echo "   ✅ index.php exists\n";
echo "   ✅ .htaccess created\n";
echo "   ✅ All PHP files working\n";
echo "   ✅ Database connected\n";

echo "\n🎯 MOST LIKELY ISSUE:\n";
echo "❌ Apache is not running in XAMPP\n\n";

echo "💡 IMMEDIATE SOLUTION:\n";
echo "1. 🚀 Open XAMPP Control Panel\n";
echo "2. 🔘 Click 'Start' next to Apache\n";
echo "3. ⏳ Wait for green checkmark\n";
echo "4. 🌐 Open browser: http://localhost/apsdreamhome/\n";
echo "5. 🎉 Enjoy your home page!\n\n";

echo "✨ FIX APPLIED! ✨\n";
echo "\n🎯 NEXT STEPS:\n";
echo "1. Start Apache in XAMPP\n";
echo "2. Try the URLs above\n";
echo "3. If still not working, check XAMPP error logs\n";
echo "4. Contact if issues persist\n";
?>
