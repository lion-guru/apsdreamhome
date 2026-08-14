<?php
/**
 * Marketplace Schema â€” Premium Packages + User Properties enhancements
 * RUN ONCE then DELETE this file
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

$pdo->exec("CREATE TABLE IF NOT EXISTS premium_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    duration_days INT NOT NULL DEFAULT 30,
    features JSON,
    badge_label VARCHAR(50) DEFAULT 'Featured',
    badge_color VARCHAR(20) DEFAULT '#ff6b35',
    priority_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS user_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    package_id INT NOT NULL,
    property_id INT NOT NULL,
    start_date DATETIME NOT NULL,
    expiry_date DATETIME NOT NULL,
    payment_status ENUM('pending','completed','failed') DEFAULT 'pending',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES premium_packages(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_property (property_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Alter user_properties table
$pdo->exec("ALTER TABLE user_properties 
    ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) DEFAULT 0 AFTER status,
    ADD COLUMN IF NOT EXISTS is_urgent TINYINT(1) DEFAULT 0 AFTER is_featured,
    ADD COLUMN IF NOT EXISTS expires_at DATETIME NULL AFTER sold_at,
    ADD COLUMN IF NOT EXISTS listing_type_new VARCHAR(20) AS (
        CASE WHEN listing_type IN ('sell','rent','lease') THEN listing_type ELSE 'sell' END
    ) PERSISTENT");

// Add 'lease' to listing_type enum if not present
try {
    $pdo->exec("ALTER TABLE user_properties MODIFY COLUMN listing_type 
        ENUM('sell','rent','lease') DEFAULT 'sell'");
} catch (Exception $e) {
    echo "Note: listing_type alter skipped: " . $e->getMessage() . "\n";
}

// Seed default packages
$packages = [
    ['Basic Listing', 'basic', 'Free listing with standard visibility', 0, 365, json_encode(['Standard badge', 'Email support', '1 photo']), 'Free', '#6b7280', 0],
    ['Featured', 'featured', 'Highlighted listing with featured badge', 999, 30, json_encode(['Featured badge (orange)', 'Priority in search', 'Listed on homepage', '5 photos', 'WhatsApp share']), 'Featured', '#ff6b35', 1],
    ['Urgent Sale', 'urgent', 'Urgent tag + top of search results', 1999, 15, json_encode(['URGENT badge (red)', 'Top of search results', 'Featured badge', 'SMS alert to buyers', '10 photos', 'Social media promotion']), 'URGENT', '#ef4444', 2],
    ['Premium Plus', 'premium_plus', 'Maximum visibility â€” 99acres Premium equivalent', 4999, 60, json_encode(['PREMIUM badge (gold)', '#1 position in search', 'Homepage featured slider', 'Push notification to buyers', 'Unlimited photos', 'Virtual tour link', 'Dedicated relationship manager', 'WhatsApp broadcast']), 'PREMIUM', '#f59e0b', 3],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO premium_packages (name, slug, description, price, duration_days, features, badge_label, badge_color, priority_order) VALUES (?,?,?,?,?,?,?,?,?)");
foreach ($packages as $p) {
    $stmt->execute($p);
}

echo "Marketplace schema installed successfully.\n";
echo "Packages created: " . $pdo->query("SELECT COUNT(*) FROM premium_packages")->fetchColumn() . "\n";?>