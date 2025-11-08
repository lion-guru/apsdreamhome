<?php
// admin_setup.php
require_once 'includes/db_connection.php';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create admin user if not exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    
    if(!$stmt->fetch()) {
        $hashed_password = password_hash('admin@123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, full_name, role, status) 
                VALUES ('admin', 'admin@apsdreamhomes.com', ?, 'Administrator', 'admin', 'active')";
        $pdo->prepare($sql)->execute([$hashed_password]);
        echo "✅ Admin user created successfully\n";
        echo "Username: admin\n";
        echo "Password: admin@123\n\n";
        echo "⚠️ Please change this password after first login!\n";
    } else {
        echo "ℹ️ Admin user already exists\n";
    }
    
    // Create default settings if not exists
    $tables = $pdo->query("SHOW TABLES LIKE 'site_settings'")->fetchAll();
    if(empty($tables)) {
        $pdo->exec("CREATE TABLE site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $defaultSettings = [
            ['site_name', 'APS Dream Homes'],
            ['company_name', 'APS Dream Homes Private Limited'],
            ['contact_email', 'info@apsdreamhomes.com'],
            ['contact_phone', '+91 9876543210'],
            ['office_address', '123 Main Road, Gorakhpur, Uttar Pradesh, India'],
            ['currency', '₹'],
            ['date_format', 'd/m/Y'],
            ['timezone', 'Asia/Kolkata'],
            ['items_per_page', '10'],
            ['maintenance_mode', '0']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        foreach($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        echo "✅ Default settings created\n";
    } else {
        echo "ℹ️ Site settings already exist\n";
    }
    
} catch(PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>
