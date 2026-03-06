<?php

/**
 * APS Dream Home - Emergency Fix
 * Emergency fix for when localhost/apsdreamhome/ is not working
 */

echo "=== APS Dream Home - Emergency Fix ===\n\n";

echo "🚨 EMERGENCY TROUBLESHOOTING:\n\n";

echo "1. 🔍 CHECK XAMPP STATUS:\n";
echo "   • Open XAMPP Control Panel\n";
echo "   • Look at Apache status\n";
echo "   • If RED or STOPPED, click START\n";
echo "   • Wait for GREEN checkmark\n\n";

echo "2. 🔍 CHECK PORTS:\n";
echo "   • Apache should be on PORT 80\n";
echo "   • If port 80 busy, try PORT 8080\n";
echo "   • Check for Skype/other apps using port 80\n\n";

echo "3. 🔍 TRY THESE URLS EXACTLY:\n";
echo "   • http://localhost/apsdreamhome/\n";
echo "   • http://localhost/apsdreamhome/index.php\n";
echo "   • http://127.0.0.1/apsdreamhome/\n";
echo "   • http://localhost:8080/apsdreamhome/\n";
echo "   • http://127.0.0.1:8080/apsdreamhome/\n\n";

echo "4. 🔍 BROWSER TROUBLESHOOTING:\n";
echo "   • Clear ALL browser data (Ctrl+Shift+Del)\n";
echo "   • Try different browser (Chrome, Firefox, Edge)\n";
echo "   • Open Private/Incognito window\n";
echo "   • Disable ALL browser extensions\n\n";

echo "5. 🔍 XAMPP CONFIGURATION:\n";
echo "   • Click 'Config' next to Apache\n";
echo "   • Select 'Apache (httpd.conf)'\n";
echo "   • Find 'DocumentRoot' line\n";
echo "   • Should be: DocumentRoot \"C:/xampp/htdocs/apsdreamhome\"\n";
echo "   • Find '<Directory' line after it\n";
echo "   • Should be: <Directory \"C:/xampp/htdocs/apsdreamhome\">\n\n";

echo "6. 🔍 CREATE SIMPLE TEST FILE:\n";

// Create a simple test file
$testContent = '<?php
echo "PHP is working!";
echo "<br>";
echo "Time: " . date("Y-m-d H:i:s");
echo "<br>";
echo "Document Root: " . $_SERVER["DOCUMENT_ROOT"] ?? "Not set";
echo "<br>";
echo "Request URI: " . $_SERVER["REQUEST_URI"] ?? "Not set";
?>';

file_put_contents(__DIR__ . '/test.php', $testContent);
echo "   ✅ Created test.php file\n";
echo "   • Try: http://localhost/apsdreamhome/test.php\n\n";

echo "7. 🔍 CHECK ERROR LOGS:\n";
echo "   • XAMPP Control Panel → Apache → Logs\n";
echo "   • Look for 'error.log'\n";
echo "   • Open and check for recent errors\n\n";

echo "8. 🔍 RESTART EVERYTHING:\n";
echo "   • Stop Apache in XAMPP\n";
echo "   • Stop MySQL in XAMPP\n";
echo "   • Close XAMPP Control Panel\n";
echo "   • Reopen XAMPP Control Panel\n";
echo "   • Start MySQL\n";
echo "   • Start Apache\n";
echo "   • Wait for green checkmarks\n\n";

echo "🎯 MOST COMMON ISSUES:\n";
echo "1. ❌ Apache not started\n";
echo "2. ❌ Port 80 conflict (Skype, IIS, etc.)\n";
echo "3. ❌ Wrong DocumentRoot in Apache config\n";
echo "4. ❌ Browser cache issues\n";
echo "5. ❌ Firewall blocking Apache\n\n";

echo "💡 QUICK TEST SEQUENCE:\n";
echo "1. 🚀 Start XAMPP Apache\n";
echo "2. 🌐 Try: http://localhost/apsdreamhome/test.php\n";
echo "3. ✅ If test.php works, try main URL\n";
echo "4. ❌ If test.php doesn\'t work, Apache issue\n";
echo "5. 🔧 Check Apache config and ports\n\n";

echo "🔧 IF NOTHING WORKS:\n";
echo "1. 📱 Check if other localhost sites work\n";
echo "2. 🔍 Try different XAMPP port (8080, 8888)\n";
echo "3. 🔄 Reinstall XAMPP (last resort)\n";
echo "4. 💻 Try different web server (WAMP, MAMP)\n\n";

echo "✨ EMERGENCY FIX COMPLETE! ✨\n";
echo "\n🎯 IMMEDIATE ACTION:\n";
echo "1. Start Apache in XAMPP\n";
echo "2. Try: http://localhost/apsdreamhome/test.php\n";
echo "3. If test works, try main URL\n";
echo "4. Check browser console (F12) for errors\n";
?>
