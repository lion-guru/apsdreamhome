<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 User Authentication & Customer Management System\n";
    
    // 1. Create customers table
    echo "👥 Creating Customers Table...\n";
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("DROP TABLE IF EXISTS customers");
    
    $createCustomersTable = "CREATE TABLE customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_code VARCHAR(50) UNIQUE NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        phone VARCHAR(20) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        
        -- Personal Information
        date_of_birth DATE,
        gender ENUM('male', 'female', 'other'),
        marital_status ENUM('single', 'married', 'divorced', 'widowed'),
        occupation VARCHAR(100),
        annual_income DECIMAL(12,2),
        
        -- Address Information
        permanent_address TEXT,
        current_address TEXT,
        city VARCHAR(100),
        state VARCHAR(100),
        pincode VARCHAR(10),
        country VARCHAR(100) DEFAULT 'India',
        
        -- Identity Information
        aadhar_number VARCHAR(12),
        pan_number VARCHAR(10),
        passport_number VARCHAR(20),
        
        -- Preferences
        preferred_property_type VARCHAR(50),
        preferred_location TEXT,
        budget_range_min DECIMAL(12,2),
        budget_range_max DECIMAL(12,2),
        preferred_area_min DECIMAL(10,2),
        preferred_area_max DECIMAL(10,2),
        
        -- Account Information
        account_type ENUM('individual', 'company', 'partnership') DEFAULT 'individual',
        company_name VARCHAR(200),
        gst_number VARCHAR(15),
        
        -- Verification
        email_verified TINYINT DEFAULT 0,
        phone_verified TINYINT DEFAULT 0,
        aadhar_verified TINYINT DEFAULT 0,
        kyc_completed TINYINT DEFAULT 0,
        verification_documents TEXT,
        
        -- Profile
        profile_image VARCHAR(500),
        bio TEXT,
        
        -- Status
        status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'pending',
        is_newsletter_subscribed TINYINT DEFAULT 1,
        is_promotional_subscribed TINYINT DEFAULT 1,
        
        -- Login Information
        last_login TIMESTAMP NULL,
        login_count INT DEFAULT 0,
        failed_login_attempts INT DEFAULT 0,
        account_locked_until TIMESTAMP NULL,
        
        -- Management
        created_by INT,
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- Indexes
        INDEX idx_email (email),
        INDEX idx_phone (phone),
        INDEX idx_customer_code (customer_code),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createCustomersTable);
    echo "✅ Customers table created\n";
    
    // 2. Create customer_preferences table
    echo "⚙️ Creating Customer Preferences Table...\n";
    
    $db->exec("DROP TABLE IF EXISTS customer_preferences");
    
    $createPreferencesTable = "CREATE TABLE customer_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        preference_key VARCHAR(100) NOT NULL,
        preference_value TEXT,
        preference_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_customer (customer_id),
        INDEX idx_key (preference_key)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createPreferencesTable);
    echo "✅ Customer preferences table created\n";
    
    // 3. Create customer_wishlist table
    echo "❤️ Creating Customer Wishlist Table...\n";
    
    $db->exec("DROP TABLE IF EXISTS customer_wishlist");
    
    $createWishlistTable = "CREATE TABLE customer_wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        property_type ENUM('plot', 'project', 'resell_property') NOT NULL,
        property_id INT NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        
        UNIQUE KEY unique_wishlist (customer_id, property_type, property_id),
        INDEX idx_customer (customer_id),
        INDEX idx_property (property_type, property_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createWishlistTable);
    echo "✅ Customer wishlist table created\n";
    
    // 4. Create customer_inquiries table
    echo "📞 Creating Customer Inquiries Table...\n";
    
    $db->exec("DROP TABLE IF EXISTS customer_inquiries");
    
    $createInquiriesTable = "CREATE TABLE customer_inquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT,
        inquiry_type ENUM('property', 'project', 'resell', 'general', 'complaint', 'suggestion') DEFAULT 'property',
        property_type ENUM('plot', 'project', 'resell_property'),
        property_id INT,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        
        -- Contact Information
        contact_name VARCHAR(200),
        contact_email VARCHAR(150),
        contact_phone VARCHAR(20),
        
        -- Status
        status ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        
        -- Response
        admin_response TEXT,
        responded_by INT,
        responded_at TIMESTAMP NULL,
        
        -- Management
        assigned_to INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_customer (customer_id),
        INDEX idx_status (status),
        INDEX idx_type (inquiry_type),
        INDEX idx_created_at (created_at)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createInquiriesTable);
    echo "✅ Customer inquiries table created\n";
    
    // 5. Create customer_documents table
    echo "📄 Creating Customer Documents Table...\n";
    
    $db->exec("DROP TABLE IF EXISTS customer_documents");
    
    $createDocumentsTable = "CREATE TABLE customer_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        document_type ENUM('aadhar', 'pan', 'passport', 'income_proof', 'address_proof', 'photo', 'signature', 'other') NOT NULL,
        document_name VARCHAR(200) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size DECIMAL(10,2),
        file_type VARCHAR(50),
        is_verified TINYINT DEFAULT 0,
        verification_remarks TEXT,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        verified_by INT,
        verified_at TIMESTAMP NULL,
        
        INDEX idx_customer (customer_id),
        INDEX idx_type (document_type),
        INDEX idx_verified (is_verified)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createDocumentsTable);
    echo "✅ Customer documents table created\n";
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // 6. Insert Sample Customer Data
    echo "👤 Inserting Sample Customer Data...\n";
    
    $sampleCustomers = [
        [
            'CUS001',
            'Rahul',
            'Sharma',
            'rahul.sharma@example.com',
            '+91-9876543210',
            password_hash('password123', PASSWORD_DEFAULT),
            '1990-05-15',
            'male',
            'single',
            'Software Engineer',
            1200000.00,
            '123 Main Street, Gorakhpur',
            '456 Park Avenue, Gorakhpur',
            'Gorakhpur',
            'Uttar Pradesh',
            '273001',
            'India',
            '123456789012',
            'ABCDE1234F',
            '',
            'residential',
            'Suryoday Colony, Gorakhpur',
            2000000.00,
            5000000.00,
            800.00,
            2000.00,
            'individual',
            '',
            '',
            1, // email_verified
            1, // phone_verified
            1, // aadhar_verified
            1, // kyc_completed
            json_encode(['aadhar', 'pan', 'photo']),
            '/uploads/customers/rahul_profile.jpg',
            'Looking for residential property in Gorakhpur with modern amenities.',
            'active',
            1, // newsletter
            1, // promotional
            date('Y-m-d H:i:s'),
            5, // login_count
            0, // failed_attempts
            NULL, // account_locked_until
            1, // created_by
            NULL // updated_by
        ],
        [
            'CUS002',
            'Priya',
            'Singh',
            'priya.singh@example.com',
            '+91-9876543220',
            password_hash('password123', PASSWORD_DEFAULT),
            '1988-08-22',
            'female',
            'married',
            'Teacher',
            600000.00,
            '789 School Road, Deoria',
            '456 College Road, Deoria',
            'Deoria',
            'Uttar Pradesh',
            '274001',
            'India',
            '987654321098',
            'FGHIJ5678K',
            '',
            'residential',
            'Braj Radha Nagri, Deoria',
            1500000.00,
            3000000.00,
            1000.00,
            1500.00,
            'individual',
            '',
            '',
            1, // email_verified
            1, // phone_verified
            0, // aadhar_verified
            0, // kyc_completed
            json_encode(['aadhar', 'pan']),
            '/uploads/customers/priya_profile.jpg',
            'Interested in affordable residential property with good connectivity.',
            'active',
            1, // newsletter
            0, // promotional
            date('Y-m-d H:i:s', strtotime('-2 days')),
            3, // login_count
            0, // failed_attempts
            NULL, // account_locked_until
            1, // created_by
            NULL // updated_by
        ]
    ];
    
    foreach ($sampleCustomers as $customer) {
        $stmt = $db->prepare("INSERT INTO customers (
            customer_code, first_name, last_name, email, phone, password,
            date_of_birth, gender, marital_status, occupation, annual_income,
            permanent_address, current_address, city, state, pincode, country,
            aadhar_number, pan_number, passport_number,
            preferred_property_type, preferred_location, budget_range_min, budget_range_max,
            preferred_area_min, preferred_area_max,
            account_type, company_name, gst_number,
            email_verified, phone_verified, aadhar_verified, kyc_completed, verification_documents,
            profile_image, bio, status, is_newsletter_subscribed, is_promotional_subscribed,
            last_login, login_count, failed_login_attempts, account_locked_until,
            created_by, updated_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute($customer);
    }
    
    echo "✅ " . count($sampleCustomers) . " sample customers inserted\n";
    
    // 7. Insert Sample Preferences
    echo "⚙️ Inserting Sample Preferences...\n";
    
    $samplePreferences = [
        [1, 'notification_email', 'enabled', 'boolean'],
        [1, 'notification_sms', 'enabled', 'boolean'],
        [1, 'language', 'english', 'string'],
        [1, 'currency', 'INR', 'string'],
        [1, 'search_radius', '50', 'number'],
        [2, 'notification_email', 'enabled', 'boolean'],
        [2, 'notification_sms', 'disabled', 'boolean'],
        [2, 'language', 'hindi', 'string'],
        [2, 'currency', 'INR', 'string'],
        [2, 'search_radius', '25', 'number']
    ];
    
    foreach ($samplePreferences as $preference) {
        $stmt = $db->prepare("INSERT INTO customer_preferences (customer_id, preference_key, preference_value, preference_type) VALUES (?, ?, ?, ?)");
        $stmt->execute($preference);
    }
    
    echo "✅ " . count($samplePreferences) . " sample preferences inserted\n";
    
    // 8. Insert Sample Wishlist Items
    echo "❤️ Inserting Sample Wishlist Items...\n";
    
    $sampleWishlist = [
        [1, 'plot', 1, 'Interested in this plot for investment'],
        [1, 'project', 1, 'Good project for future investment'],
        [1, 'resell_property', 1, 'Good deal in resell market'],
        [2, 'plot', 2, 'Perfect location for my family'],
        [2, 'resell_property', 1, 'Affordable option']
    ];
    
    foreach ($sampleWishlist as $item) {
        $stmt = $db->prepare("INSERT INTO customer_wishlist (customer_id, property_type, property_id, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute($item);
    }
    
    echo "✅ " . count($sampleWishlist) . " sample wishlist items inserted\n";
    
    echo "\n🎉 User Authentication & Customer Management System Complete!\n";
    echo "✅ Customers Table: Created with 30+ fields\n";
    echo "✅ Customer Preferences: Personalized settings\n";
    echo "✅ Customer Wishlist: Property wishlist management\n";
    echo "✅ Customer Inquiries: Support and inquiry system\n";
    echo "✅ Customer Documents: KYC and document management\n";
    echo "✅ Sample Data: 2 customers with preferences\n";
    echo "✅ Features: Complete customer lifecycle management\n";
    echo "📈 Ready for Customer Authentication!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
?>
