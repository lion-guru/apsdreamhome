<?php
/**
 * Seed ad slot data for testing
 */
$base = 'http://localhost/apsdreamhome';
define('BASE_URL', $base);
require_once __DIR__ . '/../config/database.php';

try {
    $db = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS ad_slots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slot_key VARCHAR(50) NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        content TEXT,
        image_url VARCHAR(500),
        link_url VARCHAR(500),
        html_code TEXT,
        slot_type ENUM('banner','sidebar','inline','popup','footer') DEFAULT 'banner',
        status ENUM('active','inactive') DEFAULT 'active',
        sort_order INT DEFAULT 0,
        views INT DEFAULT 0,
        clicks INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_slot_key (slot_key),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $slots = [
        [
            'slot_key' => 'header_banner',
            'title' => 'Header Banner - Property Deal',
            'content' => '',
            'image_url' => 'https://placehold.co/728x90/2c3e50/ffffff?text=Premium+Plots+Available+-+Call+Now+92771+21112',
            'link_url' => BASE_URL . '/properties',
            'html_code' => '',
            'slot_type' => 'banner',
            'status' => 'active',
            'sort_order' => 1,
        ],
        [
            'slot_key' => 'footer_banner',
            'title' => 'Footer Banner - Home Loans',
            'content' => '',
            'image_url' => 'https://placehold.co/728x90/27ae60/ffffff?text=Home+Loan+at+6.5%25+-+Apply+Now',
            'link_url' => BASE_URL . '/services',
            'html_code' => '',
            'slot_type' => 'footer',
            'status' => 'active',
            'sort_order' => 1,
        ],
        [
            'slot_key' => 'sidebar_tools',
            'title' => 'Sidebar - Free Tools',
            'content' => '',
            'image_url' => 'https://placehold.co/300x250/3498db/ffffff?text=Free+Property+Tools',
            'link_url' => BASE_URL . '/properties',
            'html_code' => '',
            'slot_type' => 'sidebar',
            'status' => 'active',
            'sort_order' => 1,
        ],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO ad_slots (slot_key, title, content, image_url, link_url, html_code, slot_type, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($slots as $slot) {
        $stmt->execute([$slot['slot_key'], $slot['title'], $slot['content'], $slot['image_url'], $slot['link_url'], $slot['html_code'], $slot['slot_type'], $slot['status'], $slot['sort_order']]);
    }
    echo "Ad slots seeded successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
