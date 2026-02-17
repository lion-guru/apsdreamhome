<?php
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Check if system_settings table exists
$table_check = $db->fetchOne("SHOW TABLES LIKE 'system_settings'");

if (!$table_check) {
    echo "Creating system_settings table...\n";
    
    $sql = "CREATE TABLE system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        display_name VARCHAR(255) NOT NULL,
        description TEXT,
        setting_group VARCHAR(50) DEFAULT 'general',
        is_sensitive BOOLEAN DEFAULT FALSE,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($db->execute($sql)) {
        echo "Table created successfully.\n";
        
        // Insert some default settings
        echo "Inserting default settings...\n";
        $default_settings = [
            ['google_maps_api_key', '', 'Google Maps API Key', 'Used for showing property locations on maps', 'api', true],
            ['smtp_host', 'smtp.gmail.com', 'SMTP Host', 'Mail server for outgoing notifications', 'email', false],
            ['smtp_port', '587', 'SMTP Port', 'Port for the mail server', 'email', false],
            ['smtp_user', '', 'SMTP Username', 'Username for email authentication', 'email', true],
            ['smtp_pass', '', 'SMTP Password', 'Password for email authentication', 'email', true],
            ['razorpay_key_id', '', 'Razorpay Key ID', 'Key ID for Razorpay payment gateway', 'payment', true],
            ['razorpay_key_secret', '', 'Razorpay Key Secret', 'Secret key for Razorpay payment gateway', 'payment', true],
            ['company_name', 'APS Dream Home', 'Company Name', 'The official name of the real estate firm', 'general', false],
            ['support_email', 'support@apsdreamhome.com', 'Support Email', 'Email address for customer support', 'general', false]
        ];
        
        $insert_sql = "INSERT INTO system_settings (setting_key, setting_value, display_name, description, setting_group, is_sensitive) VALUES (?, ?, ?, ?, ?, ?)";
        
        foreach ($default_settings as $s) {
            $db->execute($insert_sql, $s);
        }
        echo "Default settings inserted.\n";
    } else {
        echo "Error creating table.\n";
    }
} else {
    echo "system_settings table already exists.\n";
}
?>
