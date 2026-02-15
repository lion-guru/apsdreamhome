<?php
/**
 * APS Dream Homes Pvt Ltd - Deployment Verification Script
 * This script verifies that the website is properly deployed and working
 */

// Include configuration
require_once 'config_production.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Deployment Verification - APS Dream Homes Pvt Ltd</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; }
        .status-card { margin: 10px 0; padding: 15px; border-radius: 8px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>🚀 Deployment Verification - APS Dream Homes Pvt Ltd</h1>";

// Test database connection
try {
    $conn = getMysqliConnection();
    if ($conn) {
        echo "<div class='status-card success'><strong>✅ Database Connection:</strong> SUCCESS</div>";
    } else {
        echo "<div class='status-card error'><strong>❌ Database Connection:</strong> FAILED</div>";
    }
} catch (Exception $e) {
    echo "<div class='status-card error'><strong>❌ Database Connection:</strong> FAILED - " . $e->getMessage() . "</div>";
}

// Test company settings
$settings = getCompanySettings();
if (!empty($settings)) {
    echo "<div class='status-card success'><strong>✅ Company Settings:</strong> LOADED (" . count($settings) . " settings)</div>";
} else {
    echo "<div class='status-card warning'><strong>⚠️ Company Settings:</strong> NOT FOUND</div>";
}

// Test properties count
$properties_count = getPropertiesCount();
echo "<div class='status-card " . ($properties_count > 0 ? 'success' : 'warning') . "'><strong>🏠 Properties:</strong> " . $properties_count . " listings available</div>";

// Check required files
$required_files = [
    'index.php',
    'properties_template.php',
    'about_template.php',
    'contact_template.php',
    'admin_panel.php',
    'includes/db_connection.php',
    'includes/universal_template.php'
];

$missing_files = [];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

if (empty($missing_files)) {
    echo "<div class='status-card success'><strong>✅ Required Files:</strong> ALL FILES PRESENT</div>";
} else {
    echo "<div class='status-card error'><strong>❌ Missing Files:</strong> " . implode(', ', $missing_files) . "</div>";
}

// Check PHP version
$php_version = phpversion();
$required_version = '8.0.0';
if (version_compare($php_version, $required_version, '>=')) {
    echo "<div class='status-card success'><strong>✅ PHP Version:</strong> $php_version (Compatible)</div>";
} else {
    echo "<div class='status-card warning'><strong>⚠️ PHP Version:</strong> $php_version (Recommended: $required_version+)</div>";
}

// Check server environment
$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
$is_production = $server_name !== 'localhost' && $server_name !== '127.0.0.1';

echo "<div class='status-card " . ($is_production ? 'success' : 'info') . "'><strong>🌐 Environment:</strong> " . ($is_production ? 'PRODUCTION' : 'DEVELOPMENT') . " ($server_name)</div>";

// Check HTTPS
$is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
echo "<div class='status-card " . ($is_https ? 'success' : 'warning') . "'><strong>🔒 HTTPS:</strong> " . ($is_https ? 'ENABLED' : 'NOT ENABLED') . "</div>";

// Check writable directories
$writable_dirs = ['uploads'];
$unwritable_dirs = [];
foreach ($writable_dirs as $dir) {
    if (is_dir($dir) && !is_writable($dir)) {
        $unwritable_dirs[] = $dir;
    }
}

if (empty($unwritable_dirs)) {
    echo "<div class='status-card success'><strong>✅ File Permissions:</strong> OK</div>";
} else {
    echo "<div class='status-card warning'><strong>⚠️ File Permissions:</strong> Some directories not writable: " . implode(', ', $unwritable_dirs) . "</div>";
}

// Display company information
echo "<div class='mt-4'>";
echo "<h3>🏢 Company Information:</h3>";
echo "<table class='table table-bordered'>";
echo "<tr><td><strong>Company Name:</strong></td><td>" . (COMPANY_NAME) . "</td></tr>";
echo "<tr><td><strong>Phone:</strong></td><td>" . COMPANY_PHONE . "</td></tr>";
echo "<tr><td><strong>Email:</strong></td><td>" . COMPANY_EMAIL . "</td></tr>";
echo "<tr><td><strong>Address:</strong></td><td>" . COMPANY_ADDRESS . "</td></tr>";
echo "<tr><td><strong>Working Hours:</strong></td><td>" . WORKING_HOURS . "</td></tr>";
echo "<tr><td><strong>Properties:</strong></td><td>" . TOTAL_PROPERTIES . "</td></tr>";
echo "<tr><td><strong>Portfolio Value:</strong></td><td>" . PORTFOLIO_VALUE . "</td></tr>";
echo "<tr><td><strong>Experience:</strong></td><td>" . YEARS_EXPERIENCE . " years</td></tr>";
echo "</table>";
echo "</div>";

// Navigation links
echo "<div class='mt-4'>";
echo "<h3>🔗 Quick Navigation:</h3>";
echo "<div class='d-flex flex-wrap gap-2'>";
echo "<a href='index.php' class='btn btn-primary'>🏠 Homepage</a>";
echo "<a href='properties_template.php' class='btn btn-success'>🏢 Properties</a>";
echo "<a href='about_template.php' class='btn btn-info'>📖 About</a>";
echo "<a href='contact_template.php' class='btn btn-warning'>📞 Contact</a>";
echo "<a href='admin_panel.php' class='btn btn-secondary'>⚙️ Admin Panel</a>";
echo "</div>";
echo "</div>";

// Recommendations
echo "<div class='mt-4'>";
echo "<h3>📋 Recommendations:</h3>";
echo "<div class='alert alert-info'>";
if (!$is_production) {
    echo "<p><strong>🌐 Production Setup:</strong> Deploy to a web hosting provider for live access.</p>";
}
if (!$is_https) {
    echo "<p><strong>🔒 SSL Certificate:</strong> Enable HTTPS for security and better SEO.</p>";
}
if ($properties_count == 0) {
    echo "<p><strong>🏠 Properties:</strong> Add property listings through the admin panel.</p>";
}
if (empty($settings)) {
    echo "<p><strong>⚙️ Settings:</strong> Configure company information in the database.</p>";
}
echo "<p><strong>📊 Analytics:</strong> Set up Google Analytics to track visitors.</p>";
echo "<p><strong>🔍 SEO:</strong> Submit your website to Google Search Console.</p>";
echo "<p><strong>📱 Mobile:</strong> Test your website on different devices.</p>";
echo "</div>";
echo "</div>";

echo "<div class='mt-4 text-center'>";
echo "<h4>🎉 Deployment Status: ";
if ($conn && $properties_count > 0 && $is_production) {
    echo "<span style='color: green;'>READY FOR BUSINESS! 🚀</span>";
} else {
    echo "<span style='color: orange;'>SETUP REQUIRED ⚠️</span>";
}
echo "</h4>";
echo "</div>";

echo "</div></body></html>";
?>
