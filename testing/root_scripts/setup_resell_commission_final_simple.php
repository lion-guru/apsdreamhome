<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 Phase 7: Resell Properties + Commission System\n";
    
    // 1. Create resell_properties table
    echo "🏠 Creating Resell Properties Table...\n";
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("DROP TABLE IF EXISTS resell_properties");
    
    $createResellTable = "CREATE TABLE resell_properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT,
        original_plot_id INT,
        seller_id INT,
        seller_name VARCHAR(200),
        seller_email VARCHAR(150),
        seller_phone VARCHAR(20),
        seller_address TEXT,
        
        -- Property Details
        property_title VARCHAR(300) NOT NULL,
        description TEXT,
        property_type VARCHAR(50) DEFAULT 'residential',
        area_sqft DECIMAL(10,2),
        area_sqm DECIMAL(10,2),
        bedrooms INT,
        bathrooms INT,
        parking_spaces INT,
        age_years INT,
        property_condition VARCHAR(20) DEFAULT 'good',
        
        -- Location
        address TEXT,
        colony_name VARCHAR(200),
        district_name VARCHAR(100),
        state_name VARCHAR(100),
        latitude DECIMAL(10,8),
        longitude DECIMAL(11,8),
        
        -- Pricing
        original_price DECIMAL(12,2),
        expected_price DECIMAL(12,2),
        negotiable TINYINT DEFAULT 1,
        price_negotiable_range VARCHAR(100),
        
        -- Features
        amenities TEXT,
        features TEXT,
        furnishing VARCHAR(20) DEFAULT 'unfurnished',
        
        -- Legal & Documentation
        ownership_type VARCHAR(20) DEFAULT 'freehold',
        possession_status VARCHAR(30) DEFAULT 'immediate',
        documents_available TEXT,
        
        -- Media
        images TEXT,
        videos TEXT,
        virtual_tour_url VARCHAR(500),
        floor_plan_url VARCHAR(500),
        
        -- Listing Details
        listing_type VARCHAR(20) DEFAULT 'sale',
        listing_status VARCHAR(20) DEFAULT 'active',
        featured TINYINT DEFAULT 0,
        premium_listing TINYINT DEFAULT 0,
        listing_date DATE,
        expiry_date DATE,
        
        -- Commission & Payment
        commission_type VARCHAR(20) DEFAULT 'percentage',
        commission_rate DECIMAL(5,2) DEFAULT 2.00,
        commission_amount DECIMAL(12,2),
        commission_paid TINYINT DEFAULT 0,
        commission_status VARCHAR(20) DEFAULT 'pending',
        
        -- Buyer Information
        buyer_id INT,
        buyer_name VARCHAR(200),
        buyer_contact VARCHAR(100),
        sale_date DATE,
        sale_price DECIMAL(12,2),
        
        -- Management
        created_by INT,
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createResellTable);
    echo "✅ Resell Properties table created\n";
    
    // 2. Create commission_calculations table
    echo "💰 Creating Commission Calculations Table...\n";
    
    $db->exec("DROP TABLE IF EXISTS commission_calculations");
    
    $createCommissionTable = "CREATE TABLE commission_calculations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        resell_property_id INT NOT NULL,
        agent_id INT,
        agent_name VARCHAR(200),
        agent_email VARCHAR(150),
        agent_phone VARCHAR(20),
        
        -- Commission Details
        commission_type VARCHAR(20) DEFAULT 'percentage',
        commission_rate DECIMAL(5,2) DEFAULT 2.00,
        commission_amount DECIMAL(12,2),
        commission_percentage DECIMAL(5,2),
        base_amount DECIMAL(12,2),
        
        -- Calculation Breakdown
        sale_price DECIMAL(12,2),
        original_commission DECIMAL(12,2),
        bonus_amount DECIMAL(12,2),
        deduction_amount DECIMAL(12,2),
        final_commission DECIMAL(12,2),
        
        -- Payment Status
        payment_status VARCHAR(20) DEFAULT 'pending',
        paid_amount DECIMAL(12,2) DEFAULT 0.00,
        payment_date DATE,
        payment_method VARCHAR(50),
        transaction_id VARCHAR(100),
        
        -- Time Tracking
        calculation_date DATE,
        approval_date DATE,
        payment_due_date DATE,
        
        -- Notes & History
        notes TEXT,
        calculation_breakdown TEXT,
        approval_notes TEXT,
        
        -- Management
        created_by INT,
        approved_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createCommissionTable);
    echo "✅ Commission Calculations table created\n";
    
    // 3. Create commission_rules table
    echo "📋 Creating Commission Rules Table...\n";
    
    $db->exec("DROP TABLE IF EXISTS commission_rules");
    
    $createRulesTable = "CREATE TABLE commission_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rule_name VARCHAR(200) NOT NULL,
        rule_type VARCHAR(20) DEFAULT 'percentage',
        
        -- Rule Conditions
        min_price DECIMAL(12,2),
        max_price DECIMAL(12,2),
        property_type VARCHAR(100),
        agent_level VARCHAR(50),
        
        -- Commission Structure
        commission_rate DECIMAL(5,2) DEFAULT 2.00,
        fixed_amount DECIMAL(12,2),
        tier_rates TEXT,
        bonus_conditions TEXT,
        
        -- Rule Settings
        is_active TINYINT DEFAULT 1,
        priority INT DEFAULT 0,
        effective_date DATE,
        expiry_date DATE,
        
        -- Additional Settings
        tax_deduction DECIMAL(5,2) DEFAULT 0.00,
        other_deductions TEXT,
        payment_terms TEXT,
        notes TEXT,
        
        -- Management
        created_by INT,
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createRulesTable);
    echo "✅ Commission Rules table created\n";
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // 4. Insert Sample Data using direct SQL
    echo "📊 Inserting Sample Data...\n";
    
    // Insert sample resell properties
    $db->exec("INSERT INTO resell_properties (
        property_id, original_plot_id, seller_id, seller_name, seller_email, seller_phone, seller_address,
        property_title, description, property_type, area_sqft, area_sqm, bedrooms, bathrooms, parking_spaces, age_years, property_condition,
        address, colony_name, district_name, state_name, latitude, longitude,
        original_price, expected_price, negotiable, price_negotiable_range,
        amenities, features, furnishing, ownership_type, possession_status, documents_available,
        images, videos, virtual_tour_url, floor_plan_url,
        listing_type, listing_status, featured, premium_listing, listing_date, expiry_date,
        commission_type, commission_rate, commission_amount, commission_paid, commission_status,
        buyer_id, buyer_name, buyer_contact, sale_date, sale_price,
        created_by, updated_by
    ) VALUES (
        1, 1, 1, 'Rahul Sharma', 'rahul@example.com', '+91-9876543210', '123 Main Street, Gorakhpur',
        'Premium Residential Plot in Suryoday Colony', 'Beautiful 1000 sqft residential plot in prime location of Suryoday Colony. Well-connected with all amenities nearby.',
        'residential', 1000.00, 92.90, 0, 0, 2, 5, 'good',
        '123 Main Street, Suryoday Colony, Gorakhpur', 'Suryoday Colony', 'Gorakhpur', 'Uttar Pradesh', 26.7485, 83.3792,
        2500000.00, 2800000.00, 1, '5-10% negotiation possible',
        '24/7 Security, Park, Water Supply, Power Backup', 'Corner Plot, East Facing, Wide Road Frontage', 'unfurnished',
        'freehold', 'immediate', 'title_deed, possession_certificate, tax_receipts',
        'resell_1_1.jpg, resell_1_2.jpg, resell_1_3.jpg', 'resell_1_tour.mp4', '',
        'sale', 'active', 1, 0, '2024-04-01', '2024-07-01',
        'percentage', 2.00, 56000.00, 0, 'pending',
        NULL, NULL, NULL, NULL, NULL, 1, NULL
    )");
    
    echo "✅ 1 resell property inserted\n";
    
    // Insert sample commission rules
    $db->exec("INSERT INTO commission_rules (
        rule_name, rule_type, min_price, max_price, property_type, agent_level,
        commission_rate, fixed_amount, tier_rates, bonus_conditions,
        is_active, priority, effective_date, expiry_date,
        tax_deduction, other_deductions, payment_terms, notes,
        created_by, updated_by
    ) VALUES (
        'Standard Residential Commission', 'percentage', 0, 10000000, 'residential', 'all',
        2.00, 0.00, '', 'bonus_for_quick_sale: 0.5%',
        1, 1, '2024-01-01', NULL,
        18.00, 'gst: 18%', 'Payment within 7 days of sale completion', 'Standard commission rate for all residential properties',
        1, NULL
    )");
    
    echo "✅ 1 commission rule inserted\n";
    
    echo "\n🎉 Phase 7: Resell Properties + Commission System Complete!\n";
    echo "✅ Resell Properties Table: Created with 40+ fields\n";
    echo "✅ Commission Calculations Table: Complete tracking system\n";
    echo "✅ Commission Rules Table: Flexible rule engine\n";
    echo "✅ Sample Data: 1 resell property, 1 commission rule\n";
    echo "✅ Features: Multiple commission types, tiered rates, bonuses\n";
    echo "✅ Status Tracking: Full lifecycle management\n";
    echo "📈 Ready for Resell Property Management!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
?>
