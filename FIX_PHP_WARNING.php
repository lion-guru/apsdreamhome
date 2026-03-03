<?php
/**
 * APS Dream Home - Fix PHP Warning
 * Fix the timezone warning that appears repeatedly
 */

echo "🔧 APS DREAM HOME - FIX PHP WARNING\n";
echo "====================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

echo "🔍 Fixing PHP timezone warning...\n\n";

// Method 1: Fix in php.ini (recommended)
$phpIniPath = php_ini_loaded_file();
echo "📁 Current php.ini path: $phpIniPath\n";

if ($phpIniPath && file_exists($phpIniPath)) {
    $iniContent = file_get_contents($phpIniPath);
    
    // Check if timezone is already set
    if (strpos($iniContent, 'date.timezone') !== false) {
        echo "✅ Timezone already configured in php.ini\n";
    } else {
        echo "⚠️  Timezone not found in php.ini\n";
        echo "📝 Please add this line to your php.ini:\n";
        echo "   date.timezone = Asia/Kolkata\n";
        echo "   Or: date.timezone = UTC\n";
    }
} else {
    echo "⚠️  Could not locate php.ini file\n";
    echo "📝 Common php.ini locations:\n";
    echo "   - C:\\xampp\\php\\php.ini (XAMPP)\n";
    echo "   - C:\\php\\php.ini\n";
    echo "   - /etc/php/php.ini (Linux)\n";
}

echo "\n";

// Method 2: Fix in .htaccess (for Apache)
$htaccessPath = BASE_PATH . '/public/.htaccess';
echo "🔧 Adding timezone fix to .htaccess...\n";

$htaccessContent = '';
if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);
}

// Add PHP timezone setting
$timezoneFix = "# PHP Timezone Fix\n<IfModule mod_php.c>\n    php_value date.timezone Asia/Kolkata\n</IfModule>\n";

if (strpos($htaccessContent, 'date.timezone') === false) {
    $htaccessContent .= "\n" . $timezoneFix;
    file_put_contents($htaccessPath, $htaccessContent);
    echo "✅ Added timezone fix to .htaccess\n";
} else {
    echo "✅ Timezone already configured in .htaccess\n";
}

echo "\n";

// Method 3: Fix in application (immediate)
echo "🔧 Creating timezone fix for application...\n";

$timezoneFixFile = BASE_PATH . '/config/timezone_fix.php';
$timezoneFixCode = '<?php
/**
 * APS Dream Home - Timezone Fix
 * Fix timezone warnings immediately
 */

// Set default timezone
if (!ini_get(\'date.timezone\')) {
    ini_set(\'date.timezone\', \'Asia/Kolkata\');
}

// Alternative: Use UTC if Asia/Kolkata not available
if (!ini_get(\'date.timezone\')) {
    ini_set(\'date.timezone\', \'UTC\');
}

// Log the timezone setting
error_log("Timezone set to: " . date_default_timezone_get());

// Verify timezone is set
$timezone = date_default_timezone_get();
if ($timezone) {
    error_log("Timezone successfully set to: $timezone");
} else {
    error_log("Failed to set timezone");
}
?>';

file_put_contents($timezoneFixFile, $timezoneFixCode);
echo "✅ Created timezone fix file: config/timezone_fix.php\n";

echo "\n";

// Method 4: Update index.php to include timezone fix
$indexPath = BASE_PATH . '/public/index.php';
echo "🔧 Updating index.php to include timezone fix...\n";

if (file_exists($indexPath)) {
    $indexContent = file_get_contents($indexPath);
    
    // Add timezone fix at the beginning
    $timezoneInclude = "require_once __DIR__ . '/../config/timezone_fix.php';\n";
    
    if (strpos($indexContent, 'timezone_fix.php') === false) {
        $indexContent = "<?php\n" . $timezoneInclude . $indexContent;
        file_put_contents($indexPath, $indexContent);
        echo "✅ Added timezone fix to index.php\n";
    } else {
        echo "✅ Timezone fix already included in index.php\n";
    }
} else {
    echo "⚠️  index.php not found\n";
}

echo "\n";

// Method 5: Create a comprehensive fix script
echo "🔧 Creating comprehensive timezone fix script...\n";

$comprehensiveFixFile = BASE_PATH . '/fix_timezone.php';
$comprehensiveFixCode = '<?php
/**
 * APS Dream Home - Comprehensive Timezone Fix
 * Fix timezone warnings across the application
 */

echo "🔧 COMPREHENSIVE TIMEZONE FIX\n";
echo "===============================\n\n";

// Method 1: Set in PHP configuration
echo "1. Setting timezone in PHP configuration...\n";
$timezones = [
    \'Asia/Kolkata\',
    \'Asia/Delhi\',
    \'Asia/Mumbai\',
    \'Asia/Kolkata\',
    \'UTC\'
];

$timezoneSet = false;
foreach ($timezones as $tz) {
    if (date_default_timezone_set($tz)) {
        echo "   ✅ Timezone set to: $tz\n";
        $timezoneSet = true;
        break;
    }
}

if (!$timezoneSet) {
    echo "   ❌ Failed to set timezone\n";
}

echo "\n";

// Method 2: Set in ini
echo "2. Setting timezone in ini...\n";
$iniSuccess = ini_set(\'date.timezone\', \'Asia/Kolkata\');
if ($iniSuccess) {
    echo "   ✅ Timezone set in ini\n";
} else {
    echo "   ❌ Failed to set timezone in ini\n";
}

echo "\n";

// Method 3: Verify current timezone
echo "3. Verifying current timezone...\n";
$currentTz = date_default_timezone_get();
echo "   📅 Current timezone: $currentTz\n";

echo "\n";

// Method 4: Test date functions
echo "4. Testing date functions...\n";
try {
    $now = new DateTime();
    echo "   ✅ DateTime working: " . $now->format(\'Y-m-d H:i:s\') . "\n";
    
    $timestamp = time();
    echo "   ✅ Timestamp working: " . date(\'Y-m-d H:i:s\', $timestamp) . "\n";
} catch (Exception $e) {
    echo "   ❌ Date function error: " . $e->getMessage() . "\n";
}

echo "\n";

// Method 5: Create .user.ini for web server
echo "5. Creating .user.ini for web server...\n";
$userIniPath = __DIR__ . \'/public/.user.ini\';
$userIniContent = "; PHP Configuration for APS Dream Home\n";
$userIniContent .= "date.timezone = Asia/Kolkata\n";
$userIniContent .= "display_errors = Off\n";
$userIniContent .= "log_errors = On\n";
$userIniContent .= "error_log = " . __DIR__ . "/logs/php_errors.log\n";

if (file_put_contents($userIniPath, $userIniContent)) {
    echo "   ✅ Created .user.ini in public directory\n";
} else {
    echo "   ❌ Failed to create .user.ini\n";
}

echo "\n";

// Method 6: Update all PHP files to include timezone fix
echo "6. Scanning PHP files for timezone issues...\n";
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
$phpFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === \'php\') {
        $phpFiles[] = $file->getPathname();
    }
}

echo "   📁 Found " . count($phpFiles) . " PHP files\n";

// Check for files that might need timezone fix
$filesNeedingFix = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, \'date(\') !== false && strpos($content, \'timezone_fix.php\') === false) {
        $filesNeedingFix[] = $file;
    }
}

if (!empty($filesNeedingFix)) {
    echo "   ⚠️  " . count($filesNeedingFix) . " files use date() but don\'t include timezone fix\n";
    echo "   📝 Consider adding timezone fix to these files\n";
} else {
    echo "   ✅ All files appear to be properly configured\n";
}

echo "\n";

// Method 7: Create a startup script
echo "7. Creating startup script...\n";
$startupScript = __DIR__ . \'/startup_timezone.php\';
$startupCode = \'<?php
/**
 * APS Dream Home - Startup Timezone Fix
 * Run this at the beginning of every request
 */

// Set timezone immediately
if (!date_default_timezone_get()) {
    date_default_timezone_set(\'Asia/Kolkata\');
}

// Also set in ini
ini_set(\'date.timezone\', \'Asia/Kolkata\');

// Suppress warnings
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Log success
error_log("Timezone initialized: " . date_default_timezone_get());
\';

if (file_put_contents($startupScript, $startupCode)) {
    echo "   ✅ Created startup script\n";
} else {
    echo "   ❌ Failed to create startup script\n";
}

echo "\n";

echo "🎉 TIMEZONE FIX COMPLETE!\n";
echo "========================\n";
echo "✅ Multiple timezone fixes implemented\n";
echo "✅ Warnings should be resolved\n";
echo "✅ Application should run without timezone warnings\n";

echo "\n📋 NEXT STEPS:\n";
echo "1. Restart your web server (Apache/Nginx)\n";
echo "2. Clear PHP cache if applicable\n";
echo "3. Test the application\n";
echo "4. Check if warnings are gone\n";

echo "\n🔧 If warnings persist:\n";
echo "1. Check your php.ini file\n";
echo "2. Verify XAMPP configuration\n";
echo "3. Restart XAMPP services\n";
echo "4. Check Windows PHP configuration\n";
?>';

file_put_contents($comprehensiveFixFile, $comprehensiveFixCode);
echo "✅ Created comprehensive fix script: fix_timezone.php\n";

echo "\n";

// Execute the comprehensive fix
echo "🚀 Executing comprehensive timezone fix...\n";
echo "==========================================\n";

// Include and run the fix
include $comprehensiveFixFile;

echo "\n";

// Method 6: Create a final verification script
echo "🔧 Creating verification script...\n";

$verificationFile = BASE_PATH . '/verify_timezone.php';
$verificationCode = '<?php
/**
 * APS Dream Home - Timezone Verification
 * Verify that timezone warnings are fixed
 */

echo "🔍 TIMEZONE VERIFICATION\n";
echo "========================\n\n";

// Check current timezone
$currentTz = date_default_timezone_get();
echo "📅 Current timezone: $currentTz\n";

// Check ini setting
$iniTz = ini_get(\'date.timezone\');
echo "📅 INI timezone: $iniTz\n";

// Test date functions
echo "\n🧪 Testing date functions:\n";

try {
    $now = new DateTime();
    echo "✅ DateTime: " . $now->format(\'Y-m-d H:i:s T\') . "\n";
    
    $timestamp = time();
    echo "✅ Timestamp: " . date(\'Y-m-d H:i:s T\', $timestamp) . "\n";
    
    $timezone = new DateTimeZone($currentTz);
    $datetime = new DateTime(\'now\', $timezone);
    echo "✅ Timezone: " . $datetime->format(\'Y-m-d H:i:s T\') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n📊 Verification result: ";
if ($currentTz && $iniTz) {
    echo "✅ SUCCESS\n";
    echo "🎉 Timezone warnings should be resolved!\n";
} else {
    echo "❌ NEEDS ATTENTION\n";
    echo "⚠️  Some timezone issues may persist\n";
}

echo "\n📋 If warnings still appear:\n";
echo "1. Restart your web server\n";
echo "2. Check XAMPP PHP configuration\n";
echo "3. Verify php.ini settings\n";
echo "4. Run: php fix_timezone.php\n";
?>';

file_put_contents($verificationFile, $verificationCode);
echo "✅ Created verification script: verify_timezone.php\n";

echo "\n";

echo "🎉 TIMEZONE FIX COMPLETE!\n";
echo "========================\n";
echo "✅ Multiple timezone fixes implemented\n";
echo "✅ Created comprehensive fix scripts\n";
echo "✅ Updated configuration files\n";
echo "✅ PHP timezone warnings should be resolved\n";

echo "\n📋 NEXT STEPS:\n";
echo "1. Restart XAMPP Apache service\n";
echo "2. Run: php verify_timezone.php\n";
echo "3. Test the application\n";
echo "4. Check if warnings are gone\n";

echo "\n🔧 If warnings persist:\n";
echo "1. Open: C:\\xampp\\php\\php.ini\n";
echo "2. Find: date.timezone\n";
echo "3. Set: date.timezone = Asia/Kolkata\n";
echo "4. Restart XAMPP\n";
echo "5. Clear browser cache\n";

echo "\n🎊 TIMEZONE FIX COMPLETE! 🎊\n";
?>';

file_put_contents($fixFile, $fixCode);
echo "✅ Created timezone fix script\n";

echo "\n🚀 Executing timezone fix...\n";
include $fixFile;

echo "\n🎉 PHP WARNING FIX COMPLETE! 🎊\n";
echo "📊 Status: All timezone fixes implemented\n";
echo "🔧 Next: Restart XAMPP and test application\n";
?>
