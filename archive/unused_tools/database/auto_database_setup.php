<?php
/**
 * One-Click Database Setup
 * Creates database and tables automatically
 */

echo "<h1>🚀 One-Click Database Setup</h1>";

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=localhost", "root", "");

    echo "<div style='color: green; font-size: 18px;'>✅ Connected to MySQL server</div>";

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "<div style='color: green; font-size: 16px;'>✅ Database 'apsdreamhome' created/verified</div>";

    // Select the database
    $pdo->exec("USE apsdreamhome");
    echo "<div style='color: green; font-size: 16px;'>✅ Using database 'apsdreamhome'</div>";

    // Create company_settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS company_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        company_name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(255) NOT NULL,
        address TEXT,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Insert default company data
    $stmt = $pdo->prepare("INSERT IGNORE INTO company_settings (id, company_name, phone, email, address, description) VALUES (1, 'APS Dream Homes Pvt Ltd', '+91-9554000001', 'info@apsdreamhomes.com', '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008', 'Leading real estate developer in Gorakhpur with 8+ years of excellence')");
    $stmt->execute();

    echo "<div style='color: green; font-size: 16px;'>✅ Company settings table created with data</div>";

    // Create properties table
    $pdo->exec("CREATE TABLE IF NOT EXISTS properties (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(15,2) NOT NULL,
        area VARCHAR(50) NOT NULL,
        location VARCHAR(255) NOT NULL,
        status ENUM('available', 'sold', 'rented') DEFAULT 'available',
        featured BOOLEAN DEFAULT FALSE,
        images JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    echo "<div style='color: green; font-size: 16px;'>✅ Properties table created</div>";

    // Create property_types table
    $pdo->exec("CREATE TABLE IF NOT EXISTS property_types (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert default property types
    $pdo->exec("INSERT IGNORE INTO property_types (name, description) VALUES
        ('Residential Plot', 'Premium residential plots for building your dream home'),
        ('Apartment', 'Modern apartments with world-class amenities'),
        ('Villa', 'Luxurious villas with private gardens'),
        ('Commercial Shop', 'Commercial spaces for business'),
        ('Office Space', 'Professional office spaces')
    ");

    echo "<div style='color: green; font-size: 16px;'>✅ Property types table created with data</div>";

    echo "<div style='color: green; font-size: 20px; padding: 20px; background: #d4edda; border-radius: 5px; margin: 20px 0;'>";
    echo "🎉 DATABASE SETUP COMPLETE!<br>";
    echo "✅ All required tables created<br>";
    echo "✅ Sample data inserted<br>";
    echo "✅ Ready for APS Dream Homes system!";
    echo "</div>";

    echo "<div style='margin-top: 20px;'>";
    echo "<a href='db_test.php' style='color: green; text-decoration: none; font-size: 18px;'>🧪 Test Database Connection</a> | ";
    echo "<a href='about_template.php' style='color: green; text-decoration: none; font-size: 18px;'>📄 Test About Page</a> | ";
    echo "<a href='contact_template.php' style='color: green; text-decoration: none; font-size: 18px;'>📞 Test Contact Page</a> | ";
    echo "<a href='properties_template.php' style='color: green; text-decoration: none; font-size: 18px;'>🏠 Test Properties Page</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='color: red; font-size: 18px; padding: 20px; background: #f8d7da; border-radius: 5px;'>";
    echo "❌ Database Setup Failed: " . $e->getMessage();
    echo "</div>";

    echo "<div style='margin-top: 20px;'>";
    echo "<strong>Troubleshooting Steps:</strong><br>";
    echo "1. Make sure XAMPP is running<br>";
    echo "2. Start MySQL service in XAMPP<br>";
    echo "3. Try again or use phpMyAdmin manually<br>";
    echo "4. Check MySQL error logs if needed";
    echo "</div>";
}
?>
