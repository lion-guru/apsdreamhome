<?php
/**
 * Create site_content table for dynamic CMS content management
 * Run: php scripts/create_site_content_table.php
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$queries = [
    "CREATE TABLE IF NOT EXISTS `site_content` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `section` VARCHAR(50) NOT NULL COMMENT 'about, home, footer, contact, services',
        `content_key` VARCHAR(100) NOT NULL COMMENT 'e.g. leader_1_name, hero_title',
        `content_value` TEXT DEFAULT NULL,
        `content_type` ENUM('text','textarea','image','number','json') DEFAULT 'text',
        `content_group` VARCHAR(50) DEFAULT NULL COMMENT 'grouping: leader_1, leader_2, stats, hero etc',
        `sort_order` INT(11) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_section_key` (`section`, `content_key`),
        KEY `idx_section` (`section`),
        KEY `idx_group` (`content_group`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($queries as $q) {
    $pdo->exec($q);
}
echo "✓ site_content table created\n";

// Seed default content
$defaults = [
    // ── About Page: Leaders ──
    ['about', 'leader_1_name',  'Abhay Kumar Singh', 'text', 'leader_1', 1],
    ['about', 'leader_1_role',  'Founder & CEO', 'text', 'leader_1', 2],
    ['about', 'leader_1_exp',   '15+ years in Real Estate', 'text', 'leader_1', 3],
    ['about', 'leader_1_bio',   'Visionary leader with a passion for creating dream homes for families across Uttar Pradesh.', 'textarea', 'leader_1', 4],
    ['about', 'leader_1_photo', 'assets/images/property-placeholder.jpg', 'image', 'leader_1', 5],

    ['about', 'leader_2_name',  'Praveen Singh', 'text', 'leader_2', 6],
    ['about', 'leader_2_role',  'Director - Operations', 'text', 'leader_2', 7],
    ['about', 'leader_2_exp',   '12+ years in Project Management', 'text', 'leader_2', 8],
    ['about', 'leader_2_bio',   'Expert in land acquisition and project execution with a track record of timely deliveries.', 'textarea', 'leader_2', 9],
    ['about', 'leader_2_photo', 'assets/images/property-placeholder.jpg', 'image', 'leader_2', 10],

    ['about', 'leader_3_name',  'Vijay Verma', 'text', 'leader_3', 11],
    ['about', 'leader_3_role',  'Director - Finance', 'text', 'leader_3', 12],
    ['about', 'leader_3_exp',   '10+ years in Financial Planning', 'text', 'leader_3', 13],
    ['about', 'leader_3_bio',   'Financial strategist ensuring transparent and secure transactions for every customer.', 'textarea', 'leader_3', 14],
    ['about', 'leader_3_photo', 'assets/images/property-placeholder.jpg', 'image', 'leader_3', 15],

    // ── About Page: Stats ──
    ['about', 'stat_properties', '500+', 'text', 'stats', 16],
    ['about', 'stat_families',   '2000+', 'text', 'stats', 17],
    ['about', 'stat_projects',   '50+', 'text', 'stats', 18],
    ['about', 'stat_years',      '8+', 'text', 'stats', 19],
    ['about', 'reg_number',      'U70109UP2022PTC163047', 'text', 'registration', 20],

    // ── Homepage: Hero ──
    ['home', 'hero_title',    'Find Your Dream Home in Uttar Pradesh', 'text', 'hero', 1],
    ['home', 'hero_subtitle', 'Trusted real estate partner for 2000+ families. Premium plots and homes in Gorakhpur, Lucknow, Kushinagar & beyond.', 'textarea', 'hero', 2],
    ['home', 'hero_cta',      'Explore Properties', 'text', 'hero', 3],

    // ── Footer ──
    ['footer', 'company_name',    'APS Dream Home', 'text', 'company', 1],
    ['footer', 'address',         'Gorakhpur, Uttar Pradesh, India', 'text', 'company', 2],
    ['footer', 'phone',           '+91 92771 21112', 'text', 'company', 3],
    ['footer', 'email',           'info@apsdreamhome.com', 'text', 'company', 4],
    ['footer', 'tagline',         'Building Dreams, Delivering Trust', 'text', 'company', 5],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO site_content (section, content_key, content_value, content_type, content_group, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
$count = 0;
foreach ($defaults as $row) {
    $stmt->execute($row);
    if ($stmt->rowCount() > 0) $count++;
}
echo "✓ {$count} default content rows seeded\n";
echo "Done!\n";
