<?php
/**
 * APS Dream Home - Real Database Update Script
 * Applies all missing updates to the live database
 */

echo "<h1>🗄️ Database Update Application</h1>";
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

// Apply critical security updates
echo "<h2>🔒 Applying Critical Security Updates</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$securityUpdates = "
-- Critical Security Tables
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

CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert password security for existing users
INSERT INTO `password_security` (`user_id`, `failed_attempts`)
SELECT `id`, 0 FROM `users` WHERE NOT EXISTS (
    SELECT 1 FROM `password_security` WHERE `user_id` = `users`.`id`
);
";

$securityStatements = array_filter(array_map('trim', explode(';', $securityUpdates)));
$securityApplied = 0;

foreach ($securityStatements as $statement) {
    if (!empty($statement) && !preg_match('/^--/', $statement)) {
        if ($conn->query($statement)) {
            $securityApplied++;
        }
    }
}

echo "<p style='color: green;'>✅ Applied $securityApplied security updates</p>";
echo "</div>";

// Apply modern enhancements
echo "<h2>🚀 Applying Modern Enhancements</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$modernUpdates = "
-- Modern Enhancement Tables
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

CREATE TABLE IF NOT EXISTS `social_media_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_name` varchar(50) NOT NULL,
  `platform_url` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_name` (`platform_name`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample data
INSERT INTO `property_amenities` (`property_id`, `amenity_name`, `amenity_type`, `amenity_icon`) VALUES
(1, 'Swimming Pool', 'luxury', '🏊'),
(1, 'Gymnasium', 'luxury', '💪'),
(1, '24/7 Security', 'security', '🔒'),
(1, 'Parking', 'basic', '🅿️');

INSERT INTO `social_media_links` (`platform_name`, `platform_url`, `display_order`) VALUES
('Facebook', 'https://www.facebook.com/apsdreamhomes', 1),
('Instagram', 'https://www.instagram.com/apsdreamhomes', 2),
('LinkedIn', 'https://www.linkedin.com/company/aps-dream-homes', 3),
('YouTube', 'https://www.youtube.com/channel/apsdreamhomes', 4);
";

$modernStatements = array_filter(array_map('trim', explode(';', $modernUpdates)));
$modernApplied = 0;

foreach ($modernStatements as $statement) {
    if (!empty($statement) && !preg_match('/^--/', $statement)) {
        if ($conn->query($statement)) {
            $modernApplied++;
        }
    }
}

echo "<p style='color: green;'>✅ Applied $modernApplied modern enhancements</p>";
echo "</div>";

// Final verification
echo "<h2>🎯 Final Verification</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$finalCheck = $conn->query("SELECT COUNT(*) as count FROM site_settings");
if ($finalCheck) {
    $settingsCount = $finalCheck->fetch_assoc()['count'];
    echo "<p><strong>✅ Site Settings:</strong> $settingsCount configurations</p>";
}

// Check tables that should exist now
$verifyTables = ['password_security', 'system_logs', 'property_amenities', 'social_media_links'];
foreach ($verifyTables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $exists = $result->num_rows > 0;
    $icon = $exists ? '✅' : '❌';
    echo "<p><strong>$icon $table:</strong> " . ($exists ? 'Created successfully' : 'Failed to create') . "</p>";
}

echo "<p style='color: green; font-size: 16px;'>🎉 Database updates completed!</p>";
echo "<p>Your database now has modern security features and enhancements.</p>";
echo "</div>";

$conn->close();

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "Updated on: " . date('Y-m-d H:i:s') . " | ";
echo "Database: $dbname | ";
echo "Updates Applied: Security + Modern Features";
echo "</p>";

echo "</div>";
?>
