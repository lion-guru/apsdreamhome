<?php
/**
 * Fix NotificationService Placeholder Issue
 * Removes duplicate placeholder NotificationService class from BookingController
 */

$bookingControllerPath = 'c:/xampp/htdocs/apsdreamhome/app/Http/Controllers/Admin/BookingController.php';

try {
    $content = file_get_contents($bookingControllerPath);
    
    // Remove the placeholder NotificationService class
    $pattern = '/\/\/ Placeholder NotificationService\s+if \(!class_exists\(.*?\)\)\s*\{[^}+\}/s';
    
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, '', $content);
        file_put_contents($bookingControllerPath, $content);
        echo "✅ Removed placeholder NotificationService class from BookingController\n";
    } else {
        echo "ℹ️ Placeholder NotificationService class not found in expected format\n";
        // Try alternative approach
        $content = str_replace('// Placeholder NotificationService', '', $content);
        $content = preg_replace('/if \(!class_exists\(.*NotificationService.*?\)\s*\{[^}]+\}/s', '', $content);
        file_put_contents($bookingControllerPath, $content);
        echo "✅ Removed using alternative approach\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "🎉 NotificationService fix complete!\n";
