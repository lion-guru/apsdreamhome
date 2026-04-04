<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    
    echo "=== SETUP PROPERTY IMAGES SYSTEM ===\n\n";
    
    // 1. Create property_images table
    echo "1. Creating property_images table...\n";
    $db->query("CREATE TABLE IF NOT EXISTS property_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        image_path VARCHAR(500) NOT NULL,
        image_name VARCHAR(255),
        is_primary TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        caption TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_property_id (property_id),
        INDEX idx_is_primary (is_primary)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "   ✅ property_images table ready\n";
    
    // 2. Create directory structure
    echo "\n2. Creating upload directories...\n";
    $dirs = [
        'assets/images/properties',
        'assets/uploads/properties',
        'assets/uploads/temp'
    ];
    
    foreach ($dirs as $dir) {
        $fullPath = "c:/xampp/htdocs/apsdreamhome/$dir";
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
            echo "   ✅ Created: $dir\n";
        } else {
            echo "   ✅ Exists: $dir\n";
        }
    }
    
    // 3. Add sample property images (placeholder structure)
    echo "\n3. Property Images Setup Instructions:\n";
    echo "   ====================================\n";
    echo "   Manual Step Required:\n";
    echo "   ---------------------\n";
    echo "   1. Download images from Facebook: https://www.facebook.com/apsdreamhomes/\n";
    echo "   2. Download images from Instagram: https://www.instagram.com/apsdreamhomes/\n";
    echo "   3. Save to: assets/images/properties/\n";
    echo "   4. Name format: property-{id}-{number}.jpg\n";
    echo "   5. Use Admin Panel to upload or run import script\n\n";
    
    // 4. Check existing properties
    echo "4. Existing Properties:\n";
    $properties = $db->query("SELECT id, title, location FROM properties LIMIT 5")->fetchAll();
    if (empty($properties)) {
        echo "   ⚠️ No properties found. Add properties first.\n";
    } else {
        foreach ($properties as $prop) {
            echo "   📍 ID {$prop['id']}: {$prop['title']} ({$prop['location']})\n";
        }
    }
    
    // 5. Image Import Template
    echo "\n5. Image Import Template (for admin use):\n";
    echo "   SQL Template to add images after manual download:\n";
    
    $template = "INSERT INTO property_images (property_id, image_path, is_primary, caption) VALUES\n";
    $samples = [];
    foreach ($properties as $i => $prop) {
        $samples[] = "({$prop['id']}, 'assets/images/properties/property-{$prop['id']}-1.jpg', 1, '{$prop['title']} - Main View')";
    }
    if (!empty($samples)) {
        $template .= implode(",\n", $samples) . ";";
        echo $template . "\n";
    }
    
    // 6. Admin URL
    echo "\n6. Admin Image Management:\n";
    echo "   URL: /admin/properties\n";
    echo "   Features: Upload multiple images, Set primary image, Reorder images\n";
    
    echo "\n✅ PROPERTY IMAGES SYSTEM READY\n";
    echo "\nNext Steps:\n";
    echo "1. Download images from Facebook/Instagram manually\n";
    echo "2. Upload via Admin Panel /admin/properties\n";
    echo "3. Or place in assets/images/properties/ and run import\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
