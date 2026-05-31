<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    
    echo "=== FIXING SITE_SETTINGS TABLE STRUCTURE ===\n\n";
    
    // Check if table exists
    $tables = $db->query("SHOW TABLES LIKE 'site_settings'")->fetchAll();
    
    if (empty($tables)) {
        echo "Creating site_settings table from scratch...\n";
        $db->query("CREATE TABLE site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            category VARCHAR(50) DEFAULT 'general',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "✅ Table created successfully\n";
    } else {
        echo "Table exists, checking columns...\n";
        $columns = $db->query("SHOW COLUMNS FROM site_settings")->fetchAll();
        $colNames = array_column($columns, 'Field');
        
        // Check if we need to migrate from old structure (setting_name) to new (setting_key)
        if (in_array('setting_name', $colNames) && !in_array('setting_key', $colNames)) {
            echo "🔄 Migrating old structure (setting_name -> setting_key)...\n";
            $db->query("ALTER TABLE site_settings CHANGE setting_name setting_key VARCHAR(100) NOT NULL");
            echo "✅ Column renamed\n";
        }
        
        // Add missing columns
        if (!in_array('category', $colNames)) {
            $db->query("ALTER TABLE site_settings ADD COLUMN category VARCHAR(50) DEFAULT 'general' AFTER setting_value");
            echo "✅ Added category column\n";
        }
        
        if (!in_array('updated_at', $colNames)) {
            $db->query("ALTER TABLE site_settings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            echo "✅ Added updated_at column\n";
        }
        
        echo "\n✅ Table structure is correct now\n";
    }
    
    // Insert company info
    echo "\n=== ADDING COMPANY INFO TO SITE_SETTINGS ===\n";
    
    $settings = [
        // General
        ['site_name', 'APS Dream Homes Pvt Ltd', 'general'],
        ['site_description', 'Premium Real Estate in Gorakhpur, Lucknow & UP', 'general'],
        ['site_keywords', 'real estate, property, dream home, gorakhpur, lucknow, plots, apartments', 'seo'],
        
        // Contact
        ['contact_phone', '+91 92771 21112', 'general'],
        ['contact_phone2', '+91 70074 44842', 'general'],
        ['contact_email', 'info@apsdreamhome.com', 'email'],
        ['contact_email2', 'admin@apsdreamhome.com', 'email'],
        ['contact_address', '1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008', 'general'],
        ['contact_whatsapp', '+91 92771 21112', 'general'],
        
        // Social Media
        ['social_facebook', 'https://www.facebook.com/apsdreamhomes/', 'social'],
        ['social_instagram', 'https://www.instagram.com/apsdreamhomes/', 'social'],
        ['social_justdial', 'https://www.justdial.com/Gorakhpur/Aps-Dream-Homes-Pvt-Ltd-Near-Ganpati-Lawn-Kunraghat/9999PX551-X551-220919133119-G7Q6_BZDET', 'social'],
        ['social_falconebiz', 'https://www.falconebiz.com/company/APS-DREAM-HOMES-PRIVATE-LIMITED-U70109UP2022PTC163047', 'social'],
        
        // Appearance
        ['logo_url', '/assets/images/logo/apslogonew.jpg', 'appearance'],
        ['favicon_url', '/assets/images/apple-touch-icon.png', 'appearance'],
        ['primary_color', '#6366f1', 'appearance'],
        ['secondary_color', '#8b5cf6', 'appearance'],
        
        // Google Maps
        ['map_embed_suryoday', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.991144111075!2d83.30122467380973!3d26.840233976690463!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399149002e8a386b%3A0x907b565a09c02435!2sSuryoday%20Colony%20developed%20by%20APS%20Dream%20Homes!5e0!3m2!1sen!2sin!4v1775289074035!5m2!1sen!2sin', 'general'],
        ['map_coords_suryoday', '26.840233976690463,83.30122467380973', 'general'],
        
        // Maintenance
        ['enable_maintenance', '0', 'general'],
        ['maintenance_message', 'Site is under maintenance. We will be back soon!', 'general']
    ];
    
    foreach ($settings as $setting) {
        list($key, $value, $category) = $setting;
        
        // Check if exists
        $exists = $db->query("SELECT COUNT(*) as cnt FROM site_settings WHERE setting_key = ?", [$key])->fetch();
        
        if ($exists['cnt'] == 0) {
            $db->query("INSERT INTO site_settings (setting_key, setting_value, category) VALUES (?, ?, ?)", [$key, $value, $category]);
            echo "✅ Added: $key\n";
        } else {
            $db->query("UPDATE site_settings SET setting_value = ?, category = ? WHERE setting_key = ?", [$value, $category, $key]);
            echo "🔄 Updated: $key\n";
        }
    }
    
    echo "\n=== SETUP COMPLETE ===\n";
    echo "Admin can now manage settings at: /admin/site_settings\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
