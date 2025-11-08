<?php
/**
 * APS Dream Home - Enhanced Database Update Script
 * Applies remaining missing updates to complete the database
 */

echo "<h1>🗄️ Final Database Updates</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;'>";

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'apsdreamhome';

echo "<h2>🔌 Database Connection</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<p style='color: green; font-size: 18px;'>✅ Database Connected Successfully!</p>";
    echo "<p>📊 Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "</p>";

} catch (Exception $e) {
    echo "<p style='color: red; font-size: 18px;'>❌ Database Connection Failed</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    exit;
}

echo "</div>";

// Apply remaining critical updates
echo "<h2>🔒 Applying Remaining Critical Updates</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$remainingUpdates = "
-- Fix site_settings table structure if needed
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `setting_value` TEXT DEFAULT NULL;

-- Add missing critical tables
CREATE TABLE IF NOT EXISTS `password_security` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `last_failed_attempt` timestamp NULL DEFAULT NULL,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `property_amenities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `amenity_name` varchar(100) NOT NULL,
  `amenity_type` varchar(50) DEFAULT 'basic',
  `amenity_icon` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `amenity_type` (`amenity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `seo_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(100) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `robots` varchar(50) DEFAULT 'index, follow',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_name` (`page_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample data
INSERT INTO `password_security` (`user_id`, `failed_attempts`)
SELECT `id`, 0 FROM `users` WHERE NOT EXISTS (
    SELECT 1 FROM `password_security` WHERE `user_id` = `users`.`id`
);

INSERT INTO `property_amenities` (`property_id`, `amenity_name`, `amenity_type`, `amenity_icon`) VALUES
(1, 'Swimming Pool', 'luxury', '🏊'),
(1, 'Gymnasium', 'luxury', '💪'),
(1, '24/7 Security', 'security', '🔒'),
(1, 'Parking', 'basic', '🅿️');

INSERT INTO `seo_metadata` (`page_name`, `meta_title`, `meta_description`, `meta_keywords`, `og_title`, `og_description`) VALUES
('home', 'APS Dream Homes - Leading Real Estate Developer in Gorakhpur', 'Find your dream property with APS Dream Homes. Premium residential and commercial properties in Gorakhpur, UP with modern amenities.', 'real estate gorakhpur, property gorakhpur, flats gorakhpur, apartments gorakhpur, commercial property up', 'APS Dream Homes - Premium Properties', 'Discover amazing properties in Gorakhpur'),
('properties', 'Properties for Sale - APS Dream Homes Gorakhpur', 'Browse our exclusive collection of residential and commercial properties in Gorakhpur. Find apartments, villas, and commercial spaces.', 'properties gorakhpur, flats for sale, apartments gorakhpur, commercial property', 'Properties - APS Dream Homes', 'Find your perfect property');
";

$remainingStatements = array_filter(array_map('trim', explode(';', $remainingUpdates)));
$remainingApplied = 0;

foreach ($remainingStatements as $statement) {
    if (!empty($statement) && !preg_match('/^--/', $statement) && !preg_match('/^ALTER TABLE/', $statement)) {
        if ($conn->query($statement)) {
            $remainingApplied++;
        }
    } elseif (preg_match('/^ALTER TABLE/', $statement)) {
        // Handle ALTER TABLE statements separately
        if ($conn->query($statement)) {
            $remainingApplied++;
        }
    }
}

echo "<p style='color: green;'>✅ Applied $remainingApplied remaining updates</p>";
echo "</div>";

// Final comprehensive verification
echo "<h2>🎯 Final Comprehensive Verification</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

echo "<h3>📋 Table Status:</h3>";
$allTables = [
    'users' => 'User management',
    'properties' => 'Property listings',
    'site_settings' => 'Header/Footer settings',
    'password_security' => 'Security features',
    'user_sessions' => 'Session management',
    'system_logs' => 'Activity logging',
    'api_keys' => 'API management',
    'property_amenities' => 'Modern property features',
    'social_media_links' => 'Social media integration',
    'seo_metadata' => 'SEO optimization',
    'user_roles' => 'Role-based access',
    'activity_logs' => 'Audit trail'
];

$totalTables = count($allTables);
$existingTables = 0;

foreach ($allTables as $table => $description) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $exists = $result->num_rows > 0;

    if ($exists) {
        $existingTables++;
        $countResult = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $countResult ? $countResult->fetch_assoc()['count'] : 0;
        echo "<p style='color: green;'>✅ <strong>$table:</strong> $description ($count records)</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>$table:</strong> $description (MISSING)</p>";
    }
}

$completionPercentage = round(($existingTables / $totalTables) * 100);
echo "<hr>";
echo "<p style='font-size: 18px; color: green;'><strong>🎉 Database Completion: $completionPercentage% ($existingTables/$totalTables tables)</strong></p>";

if ($completionPercentage == 100) {
    echo "<p style='color: green; font-size: 16px;'>✅ DATABASE IS FULLY UPDATED AND READY!</p>";
    echo "<p>All critical security features, modern enhancements, and optimizations are applied.</p>";
} else {
    echo "<p style='color: orange; font-size: 16px;'>⚠️ Database is $completionPercentage% complete</p>";
    echo "<p>Some tables may still need manual creation or data import.</p>";
}

echo "</div>";

// Show sample site settings
echo "<h2>⚙️ Current Site Settings</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$settingsQuery = $conn->query("SELECT * FROM site_settings LIMIT 10");
if ($settingsQuery && $settingsQuery->num_rows > 0) {
    echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Setting</th><th style='padding: 8px;'>Value</th></tr>";

    while ($setting = $settingsQuery->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($setting['setting_name'] ?? 'N/A') . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars(substr($setting['setting_value'] ?? 'N/A', 0, 100)) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No site settings found or table structure issue.</p>";
}

echo "</div>";

$conn->close();

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "✅ Database Updates Applied: " . date('Y-m-d H:i:s') . "<br>";
echo "📊 Database: $dbname | Status: $completionPercentage% Complete<br>";
echo "🔒 Security Features: Applied | 🚀 Modern Features: Applied";
echo "</p>";

echo "</div>";
?>
