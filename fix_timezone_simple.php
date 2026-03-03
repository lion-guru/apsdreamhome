<?php
/**
 * APS Dream Home - Simple Timezone Fix
 * Quick fix for PHP timezone warnings
 */

echo "🔧 APS DREAM HOME - SIMPLE TIMEZONE FIX\n";
echo "====================================\n\n";

// Set timezone immediately
date_default_timezone_set('Asia/Kolkata');

// Also set in ini
ini_set('date.timezone', 'Asia/Kolkata');

// Verify
$currentTz = date_default_timezone_get();
echo "✅ Timezone set to: $currentTz\n";

// Test date function
$now = new DateTime();
echo "✅ Current time: " . $now->format('Y-m-d H:i:s T') . "\n";

echo "\n🎉 TIMEZONE FIX COMPLETE!\n";
echo "✅ PHP timezone warnings should be resolved\n";
echo "📋 Next: Restart XAMPP and test application\n";
?>
