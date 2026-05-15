<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    
    echo "=== ADDING COMPANY INFO TO DATABASE ===\n\n";
    
    // Check if site_settings table exists
    $tables = $db->query("SHOW TABLES LIKE 'site_settings'")->fetchAll();
    
    if (empty($tables)) {
        echo "Creating site_settings table...\n";
        $db->query("CREATE TABLE site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_group VARCHAR(50) DEFAULT 'general',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    }
    
    // Company info data
    $settings = [
        // Contact Info
        ['company_name', 'APS Dream Homes Pvt Ltd', 'contact'],
        ['company_phone', '+91 92771 21112', 'contact'],
        ['company_phone2', '+91 70074 44842', 'contact'],
        ['company_whatsapp', '+91 92771 21112', 'contact'],
        ['company_email', 'info@apsdreamhome.com', 'contact'],
        ['company_email2', 'admin@apsdreamhome.com', 'contact'],
        ['company_address', '1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008', 'contact'],
        
        // Social Media
        ['facebook_url', 'https://www.facebook.com/apsdreamhomes/', 'social'],
        ['instagram_url', 'https://www.instagram.com/apsdreamhomes/', 'social'],
        ['justdial_url', 'https://www.justdial.com/Gorakhpur/Aps-Dream-Homes-Pvt-Ltd-Near-Ganpati-Lawn-Kunraghat/9999PX551-X551-220919133119-G7Q6_BZDET', 'social'],
        ['falconebiz_url', 'https://www.falconebiz.com/company/APS-DREAM-HOMES-PRIVATE-LIMITED-U70109UP2022PTC163047', 'social'],
        
        // Google Maps
        ['map_embed_suryoday', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.991144111075!2d83.30122467380973!3d26.840233976690463!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399149002e8a386b%3A0x907b565a09c02435!2sSuryoday%20Colony%20developed%20by%20APS%20Dream%20Homes!5e0!3m2!1sen!2sin!4v1775289074035!5m2!1sen!2sin', 'maps'],
        ['map_coords_suryoday', '26.840233976690463,83.30122467380973', 'maps'],
    ];
    
    foreach ($settings as $setting) {
        list($key, $value, $group) = $setting;
        
        // Check if setting exists
        $exists = $db->query("SELECT COUNT(*) as count FROM site_settings WHERE setting_key = ?", [$key])->fetch();
        
        if ($exists['count'] == 0) {
            $db->query("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)", [$key, $value, $group]);
            echo "✅ Added: $key\n";
        } else {
            $db->query("UPDATE site_settings SET setting_value = ?, setting_group = ? WHERE setting_key = ?", [$value, $group, $key]);
            echo "🔄 Updated: $key\n";
        }
    }
    
    echo "\n=== COMPANY INFO DATABASE SETUP COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
