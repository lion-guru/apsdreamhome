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
    
    // 4. Insert Sample Data using direct SQL (minimal)
    echo "📊 Inserting Sample Data...\n";
    
    // Insert sample resell property (minimal columns)
    $db->exec("INSERT INTO resell_properties (
        property_id, original_plot_id, seller_id, seller_name, seller_email, seller_phone,
        property_title, description, property_type, area_sqft,
        original_price, expected_price, negotiable,
        listing_type, listing_status, featured, premium_listing, listing_date, expiry_date,
        commission_type, commission_rate, commission_amount, commission_paid, commission_status,
        created_by
    ) VALUES (
        1, 1, 1, 'Rahul Sharma', 'rahul@example.com', '+91-9876543210',
        'Premium Residential Plot in Suryoday Colony', 'Beautiful 1000 sqft residential plot in prime location of Suryoday Colony.',
        'residential', 1000.00,
        2500000.00, 2800000.00, 1,
        'sale', 'active', 1, 0, '2024-04-01', '2024-07-01',
        'percentage', 2.00, 56000.00, 0, 'pending',
        1
    )");
    
    echo "✅ 1 resell property inserted\n";
    
    // Insert sample commission rule (minimal columns)
    $db->exec("INSERT INTO commission_rules (
        rule_name, rule_type, min_price, max_price, property_type, agent_level,
        commission_rate, fixed_amount, is_active, priority, effective_date,
        created_by
    ) VALUES (
        'Standard Residential Commission', 'percentage', 0, 10000000, 'residential', 'all',
        2.00, 0.00, 1, 1, '2024-01-01',
        1
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
