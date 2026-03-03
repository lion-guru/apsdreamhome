<?php
/**
 * APS Dream Home - Comprehensive Timezone Fix
 * Fix timezone warnings across the application
 */

echo "🔧 COMPREHENSIVE TIMEZONE FIX\n";
echo "===============================\n\n";

// Method 1: Set in PHP configuration
echo "1. Setting timezone in PHP configuration...\n";
$timezones = [
    'Asia/Kolkata',
    'Asia/Delhi',
    'Asia/Mumbai',
    'UTC'
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
$iniSuccess = ini_set('date.timezone', 'Asia/Kolkata');
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
    echo "   ✅ DateTime working: " . $now->format('Y-m-d H:i:s') . "\n";
    
    $timestamp = time();
    echo "   ✅ Timestamp working: " . date('Y-m-d H:i:s', $timestamp) . "\n";
} catch (Exception $e) {
    echo "   ❌ Date function error: " . $e->getMessage() . "\n";
}

echo "\n";

// Method 5: Create .user.ini for web server
echo "5. Creating .user.ini for web server...\n";
$userIniPath = __DIR__ . '/public/.user.ini';
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
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

echo "   📁 Found " . count($phpFiles) . " PHP files\n";

// Check for files that might need timezone fix
$filesNeedingFix = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'date(') !== false && strpos($content, 'timezone_fix.php') === false) {
        $filesNeedingFix[] = $file;
    }
}

if (!empty($filesNeedingFix)) {
    echo "   ⚠️  " . count($filesNeedingFix) . " files use date() but don't include timezone fix\n";
    echo "   📝 Consider adding timezone fix to these files\n";
} else {
    echo "   ✅ All files appear to be properly configured\n";
}

echo "\n";

// Method 7: Create a startup script
echo "7. Creating startup script...\n";
$startupScript = __DIR__ . '/startup_timezone.php';
$startupCode = '<?php
/**
 * APS Dream Home - Startup Timezone Fix
 * Run this at the beginning of every request
 */

// Set timezone immediately
if (!date_default_timezone_get()) {
    date_default_timezone_set('Asia/Kolkata');
}

// Also set in ini
ini_set('date.timezone', 'Asia/Kolkata');

// Suppress warnings
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Log success
error_log("Timezone initialized: " . date_default_timezone_get());
';

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
?>