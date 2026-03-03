<?php
/**
 * APS Dream Home - Timezone Fix
 * Fix timezone warnings immediately
 */

// Set default timezone
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'Asia/Kolkata');
}

// Alternative: Use UTC if Asia/Kolkata not available
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
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
?>