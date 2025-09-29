<?php
/**
 * APS Dream Home - Complete Database Setup
 * Creates all required tables and sample data
 */

echo "=== APS DREAM HOME - DATABASE SETUP ===\n\n";

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhomefinal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n\n";

    // Create Users table
    echo "Creating users table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            username VARCHAR(100) UNIQUE,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20),
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'agent', 'customer') DEFAULT 'customer',
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            profile_image VARCHAR(255),
            bio TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Users table created\n";

    // Create Properties table
    echo "Creating properties table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(12,2) NOT NULL,
            location VARCHAR(255) NOT NULL,
            address TEXT,
            type ENUM('apartment', 'villa', 'house', 'commercial', 'plot') NOT NULL,
            bedrooms INT DEFAULT 0,
            bathrooms INT DEFAULT 0,
            area_sqft INT DEFAULT 0,
            status ENUM('available', 'sold', 'rented', 'pending') DEFAULT 'available',
            user_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ Properties table created\n";

    // Create Property Images table
    echo "Creating property_images table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS property_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            is_primary BOOLEAN DEFAULT FALSE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
        )
    ");
    echo "✅ Property images table created\n";

    // Create Leads table
    echo "Creating leads table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            source ENUM('website', 'phone', 'email', 'referral', 'walk-in') DEFAULT 'website',
            status ENUM('new', 'contacted', 'qualified', 'converted', 'closed') DEFAULT 'new',
            budget DECIMAL(12,2),
            message TEXT,
            assigned_to INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ Leads table created\n";

    // Create Contact Messages table
    echo "Creating contact_messages table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            subject VARCHAR(255),
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Contact messages table created\n";

    // Create Site Settings table
    echo "Creating site_settings table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_name VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            setting_group VARCHAR(50) DEFAULT 'general',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Site settings table created\n";

    // Create Property Reviews table
    echo "Creating property_reviews table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS property_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT CHECK (rating >= 1 AND rating <= 5),
            review TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✅ Property reviews table created\n";

    // Create Lead Activities table
    echo "Creating lead_activities table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lead_activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lead_id INT NOT NULL,
            user_id INT,
            activity_type ENUM('call', 'email', 'meeting', 'note', 'status_change') NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ Lead activities table created\n";

    echo "\n=== SAMPLE DATA CREATION ===\n\n";

    // Insert sample users
    echo "Creating sample users...\n";
    $users = [
        ['Abhay Pratap Singh', 'admin', 'admin@apsdreamhome.com', '9838123456', password_hash('admin123', PASSWORD_DEFAULT), 'admin'],
        ['Rajesh Kumar', 'rajesh.agent', 'rajesh@apsdreamhome.com', '9838234567', password_hash('agent123', PASSWORD_DEFAULT), 'agent'],
        ['Priya Sharma', 'priya.agent', 'priya@apsdreamhome.com', '9838345678', password_hash('agent123', PASSWORD_DEFAULT), 'agent'],
        ['Amit Singh', 'amit.customer', 'amit@example.com', '9838456789', password_hash('customer123', PASSWORD_DEFAULT), 'customer'],
        ['Sneha Patel', 'sneha.customer', 'sneha@example.com', '9838567890', password_hash('customer123', PASSWORD_DEFAULT), 'customer']
    ];

    $pdo->exec("TRUNCATE TABLE users");
    foreach ($users as $user) {
        $stmt = $pdo->prepare("INSERT INTO users (name, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($user);
    }
    echo "✅ Created " . count($users) . " sample users\n";

    // Insert sample properties
    echo "Creating sample properties...\n";
    $properties = [
        ['Luxury 3BHK Apartment - Gomti Nagar', 'Spacious 3BHK apartment in prime location with modern amenities, parking, and 24/7 security.', 8500000, 'Gomti Nagar, Lucknow', 'Plot 123, Sector A, Gomti Nagar, Lucknow', 'apartment', 3, 2, 1500, 'available', 2],
        ['Modern Villa - Indira Nagar', 'Beautiful 4BHK villa with garden, parking space, and excellent connectivity to city center.', 15000000, 'Indira Nagar, Lucknow', 'Villa 45, Block C, Indira Nagar, Lucknow', 'villa', 4, 3, 2500, 'available', 3],
        ['Commercial Office Space - Hazratganj', 'Prime commercial property in business district with modern facilities and excellent footfall.', 25000000, 'Hazratganj, Lucknow', 'Office Complex, Mall Road, Hazratganj, Lucknow', 'commercial', 0, 2, 2000, 'available', 2],
        ['2BHK Apartment - Alambagh', 'Affordable 2BHK apartment perfect for small families with all basic amenities.', 4500000, 'Alambagh, Lucknow', 'Apartment 67, Sector B, Alambagh, Lucknow', 'apartment', 2, 2, 1200, 'available', 3],
        ['Independent House - Rajajipuram', 'Spacious independent house with garden and parking in peaceful neighborhood.', 12000000, 'Rajajipuram, Lucknow', 'House 89, Street 4, Rajajipuram, Lucknow', 'house', 3, 2, 1800, 'sold', 2]
    ];

    $pdo->exec("TRUNCATE TABLE properties");
    foreach ($properties as $property) {
        $stmt = $pdo->prepare("INSERT INTO properties (title, description, price, location, address, type, bedrooms, bathrooms, area_sqft, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($property);
    }
    echo "✅ Created " . count($properties) . " sample properties\n";

    // Insert sample leads
    echo "Creating sample leads...\n";
    $leads = [
        ['Vikram Singh', 'vikram.singh@email.com', '9838678901', 'website', 'new', 10000000, 'Looking for 3BHK apartment in Lucknow', 2],
        ['Anita Gupta', 'anita.gupta@email.com', '9838789012', 'phone', 'contacted', 8000000, 'Interested in villa properties', 3],
        ['Ravi Kumar', 'ravi.kumar@email.com', '9838890123', 'email', 'qualified', 20000000, 'Need commercial property for business', 2],
        ['Meera Sharma', 'meera.sharma@email.com', '9838901234', 'referral', 'converted', 8500000, 'Found perfect apartment through referral', 3],
        ['Deepak Patel', 'deepak.patel@email.com', '9839012345', 'website', 'new', 6000000, 'Looking for budget-friendly options', null]
    ];

    $pdo->exec("TRUNCATE TABLE leads");
    foreach ($leads as $lead) {
        $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status, budget, message, assigned_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($lead);
    }
    echo "✅ Created " . count($leads) . " sample leads\n";

    // Insert sample site settings
    echo "Creating sample site settings...\n";
    $pdo->exec("TRUNCATE TABLE site_settings");
    $settings = [
        ['site_title', 'APS Dream Home'],
        ['site_description', 'Premium real estate platform for buying, selling, and renting properties in Gorakhpur and across India'],
        ['contact_email', 'info@apsdreamhome.com'],
        ['contact_phone', '9838123456'],
        ['contact_address', '123 Main Street, Gorakhpur, Uttar Pradesh'],
        ['default_language', 'en'],
        ['maintenance_mode', 'false'],
        ['items_per_page', '20'],
        ['session_timeout', '60']
    ];

    foreach ($settings as $setting) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_name, setting_value) VALUES (?, ?)");
        $stmt->execute($setting);
    }
    echo "✅ Created " . count($settings) . " site settings\n";

    // Insert sample contact messages
    echo "Creating sample contact messages...\n";
    $pdo->exec("TRUNCATE TABLE contact_messages");
    $messages = [
        ['Suresh Kumar', 'suresh@example.com', '9838123456', 'Property Inquiry', 'I am interested in your properties. Please contact me.', 'new'],
        ['Kavita Singh', 'kavita@example.com', '9838234567', 'Partnership Opportunity', 'We would like to explore partnership opportunities with APS Dream Home.', 'read']
    ];

    foreach ($messages as $message) {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($message);
    }
    echo "✅ Created " . count($messages) . " contact messages\n";

    echo "\n=== DATABASE SETUP COMPLETE ===\n\n";
    echo "✅ All tables created successfully\n";
    echo "✅ Sample data inserted\n\n";
    echo "✅ Login Credentials:\n";
    echo "   • Admin: admin@apsdreamhome.com / admin123\n";
    echo "   • Agent: rajesh@apsdreamhome.com / agent123\n";
    echo "   • Customer: amit@example.com / customer123\n\n";
    echo "✅ Ready for testing!\n";

} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
