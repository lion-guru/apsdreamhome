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
        property_type ENUM('residential', 'commercial', 'industrial', 'land', 'mixed') DEFAULT 'residential',
        area_sqft DECIMAL(10,2),
        area_sqm DECIMAL(10,2),
        bedrooms INT,
        bathrooms INT,
        parking_spaces INT,
        age_years INT,
        condition ENUM('excellent', 'good', 'fair', 'needs_repair') DEFAULT 'good',
        
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
        amenities JSON,
        features JSON,
        furnishing ENUM('unfurnished', 'semi_furnished', 'fully_furnished') DEFAULT 'unfurnished',
        
        -- Legal & Documentation
        ownership_type ENUM('freehold', 'leasehold', 'cooperative') DEFAULT 'freehold',
        possession_status ENUM('immediate', 'within_1_month', 'within_3_months', 'within_6_months', 'more_than_6_months') DEFAULT 'immediate',
        documents_available JSON,
        
        -- Media
        images JSON,
        videos JSON,
        virtual_tour_url VARCHAR(500),
        floor_plan_url VARCHAR(500),
        
        -- Listing Details
        listing_type ENUM('sale', 'rent', 'lease') DEFAULT 'sale',
        listing_status ENUM('active', 'pending', 'sold', 'withdrawn', 'expired') DEFAULT 'active',
        featured TINYINT DEFAULT 0,
        premium_listing TINYINT DEFAULT 0,
        listing_date DATE,
        expiry_date DATE,
        
        -- Commission & Payment
        commission_type ENUM('percentage', 'fixed', 'hybrid') DEFAULT 'percentage',
        commission_rate DECIMAL(5,2) DEFAULT 2.00,
        commission_amount DECIMAL(12,2),
        commission_paid TINYINT DEFAULT 0,
        commission_status ENUM('pending', 'partial', 'paid', 'waived') DEFAULT 'pending',
        
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- Indexes
        INDEX idx_seller (seller_id),
        INDEX idx_property (property_id),
        INDEX idx_status (listing_status),
        INDEX idx_featured (featured),
        INDEX idx_premium (premium_listing),
        INDEX idx_listing_date (listing_date),
        INDEX idx_price (expected_price),
        INDEX idx_location (colony_name, district_name)
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
        commission_type ENUM('percentage', 'fixed', 'hybrid') DEFAULT 'percentage',
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
        payment_status ENUM('pending', 'partial', 'paid', 'overdue', 'waived') DEFAULT 'pending',
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
        calculation_breakdown JSON,
        approval_notes TEXT,
        
        -- Management
        created_by INT,
        approved_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- Indexes
        INDEX idx_resell_property (resell_property_id),
        INDEX idx_agent (agent_id),
        INDEX idx_status (payment_status),
        INDEX idx_calculation_date (calculation_date),
        INDEX idx_payment_due (payment_due_date)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createCommissionTable);
    echo "✅ Commission Calculations table created\n";
    
    // 3. Create commission_rules table
    echo "📋 Creating Commission Rules Table...\n";
    
    $db->exec("DROP TABLE IF EXISTS commission_rules");
    
    $createRulesTable = "CREATE TABLE commission_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rule_name VARCHAR(200) NOT NULL,
        rule_type ENUM('percentage', 'fixed', 'tiered', 'hybrid') DEFAULT 'percentage',
        
        -- Rule Conditions
        min_price DECIMAL(12,2),
        max_price DECIMAL(12,2),
        property_type VARCHAR(100),
        location_filter JSON,
        agent_level VARCHAR(50),
        
        -- Commission Structure
        commission_rate DECIMAL(5,2) DEFAULT 2.00,
        fixed_amount DECIMAL(12,2),
        tier_rates JSON,
        bonus_conditions JSON,
        
        -- Rule Settings
        is_active TINYINT DEFAULT 1,
        priority INT DEFAULT 0,
        effective_date DATE,
        expiry_date DATE,
        
        -- Additional Settings
        tax_deduction DECIMAL(5,2) DEFAULT 0.00,
        other_deductions JSON,
        payment_terms TEXT,
        notes TEXT,
        
        -- Management
        created_by INT,
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- Indexes
        INDEX idx_active (is_active),
        INDEX idx_effective_date (effective_date),
        INDEX idx_priority (priority),
        INDEX idx_rule_type (rule_type)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createRulesTable);
    echo "✅ Commission Rules table created\n";
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // 4. Insert Sample Data
    echo "📊 Inserting Sample Data...\n";
    
    // Sample resell properties
    $resellProperties = [
        [
            1, // property_id
            1, // original_plot_id
            1, // seller_id
            'Rahul Sharma',
            'rahul@example.com',
            '+91-9876543210',
            '123 Main Street, Gorakhpur',
            'Premium Residential Plot in Suryoday Colony',
            'Beautiful 1000 sqft residential plot in prime location of Suryoday Colony. Well-connected with all amenities nearby.',
            'residential',
            1000.00,
            92.90,
            0,
            0,
            2,
            5,
            'good',
            '123 Main Street, Suryoday Colony, Gorakhpur',
            'Suryoday Colony',
            'Gorakhpur',
            'Uttar Pradesh',
            26.7485,
            83.3792,
            2500000.00, // original price
            2800000.00, // expected price
            1, // negotiable
            '5-10% negotiation possible',
            json_encode(['24/7 Security', 'Park', 'Water Supply', 'Power Backup']),
            json_encode(['Corner Plot', 'East Facing', 'Wide Road Frontage']),
            'unfurnished',
            'freehold',
            'immediate',
            json_encode(['title_deed', 'possession_certificate', 'tax_receipts']),
            json_encode(['resell_1_1.jpg', 'resell_1_2.jpg', 'resell_1_3.jpg']),
            json_encode(['resell_1_tour.mp4']),
            '',
            '',
            'sale',
            'active',
            1, // featured
            0, // premium
            '2024-04-01',
            '2024-07-01',
            'percentage',
            2.00,
            56000.00, // commission amount
            0, // commission paid
            'pending',
            NULL, // buyer_id
            NULL, // buyer_name
            NULL, // buyer_contact
            NULL, // sale_date
            NULL, // sale_price
            1, // created_by
            NULL // updated_by
        ],
        [
            2, // property_id
            2, // original_plot_id
            2, // seller_id
            'Priya Singh',
            'priya@example.com',
            '+91-9876543220',
            '456 Park Road, Deoria',
            'Commercial Space in Braj Radha Nagri',
            'Prime commercial space near Budhya Mata Mandir in Braj Radha Nagri. Perfect for office or retail business.',
            'commercial',
            1500.00,
            139.35,
            0,
            2,
            4,
            3,
            'excellent',
            '456 Park Road, Braj Radha Nagri, Deoria',
            'Braj Radha Nagri',
            'Deoria',
            'Uttar Pradesh',
            26.6404,
            83.5906,
            4500000.00, // original price
            5200000.00, // expected price
            1, // negotiable
            '3-7% negotiation possible',
            json_encode(['24/7 Security', 'Parking', 'Power Backup', 'Water Supply']),
            json_encode(['Main Road Frontage', 'Corner Plot', 'High Visibility']),
            'unfurnished',
            'freehold',
            'immediate',
            json_encode(['title_deed', 'possession_certificate', 'commercial_license']),
            json_encode(['resell_2_1.jpg', 'resell_2_2.jpg']),
            json_encode([]),
            '',
            '',
            'sale',
            'active',
            0, // featured
            1, // premium
            '2024-04-05',
            '2024-08-05',
            'fixed',
            0.00,
            100000.00, // fixed commission
            0, // commission paid
            'pending',
            NULL, // buyer_id
            NULL, // buyer_name
            NULL, // buyer_contact
            NULL, // sale_date
            NULL, // sale_price
            1, // created_by
            NULL // updated_by
        ]
    ];
    
    foreach ($resellProperties as $property) {
        $stmt = $db->prepare("INSERT INTO resell_properties (
            property_id, original_plot_id, seller_id, seller_name, seller_email, seller_phone, seller_address,
            property_title, description, property_type, area_sqft, area_sqm, bedrooms, bathrooms, parking_spaces, age_years, condition,
            address, colony_name, district_name, state_name, latitude, longitude,
            original_price, expected_price, negotiable, price_negotiable_range,
            amenities, features, furnishing, ownership_type, possession_status, documents_available,
            images, videos, virtual_tour_url, floor_plan_url,
            listing_type, listing_status, featured, premium_listing, listing_date, expiry_date,
            commission_type, commission_rate, commission_amount, commission_paid, commission_status,
            buyer_id, buyer_name, buyer_contact, sale_date, sale_price,
            created_by, updated_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute($property);
    }
    
    echo "✅ " . count($resellProperties) . " resell properties inserted\n";
    
    // Sample commission rules
    $commissionRules = [
        [
            'Standard Residential Commission',
            'percentage',
            0, // min price
            10000000, // max price (1 crore)
            'residential',
            json_encode(['all_locations']),
            'all',
            2.00, // 2% commission
            0.00,
            json_encode([]),
            json_encode(['bonus_for_quick_sale: 0.5%']),
            1, // active
            1, // priority
            '2024-01-01',
            NULL,
            18.00, // tax deduction
            json_encode(['gst: 18%']),
            'Payment within 7 days of sale completion',
            'Standard commission rate for all residential properties',
            1, // created_by
            NULL // updated_by
        ],
        [
            'Premium Property Commission',
            'tiered',
            10000001, // min price
            50000000, // max price (5 crore)
            'residential,commercial',
            json_encode(['prime_locations']),
            'senior_agent',
            0.00, // commission rate
            0.00,
            json_encode([
                ['min_price' => 10000001, 'max_price' => 20000000, 'rate' => 1.5],
                ['min_price' => 20000001, 'max_price' => 35000000, 'rate' => 1.25],
                ['min_price' => 35000001, 'max_price' => 50000000, 'rate' => 1.0]
            ]),
            json_encode(['luxury_bonus: 0.25%']),
            1, // active
            2, // priority
            '2024-01-01',
            NULL,
            18.00,
            json_encode(['gst: 18%', 'tds: 1%']),
            'Tiered commission for premium properties',
            1, // created_by
            NULL // updated_by
        ],
        [
            'Fixed Commercial Commission',
            'fixed',
            0, // min price
            0, // max price
            'commercial',
            json_encode(['all_locations']),
            'all',
            0.00, // commission rate
            50000.00, // fixed amount ₹50,000
            json_encode([]),
            json_encode(['quick_sale_bonus: 10000']),
            1, // active
            3, // priority
            '2024-01-01',
            NULL,
            18.00,
            json_encode(['gst: 18%']),
            'Fixed commission for all commercial properties',
            1, // created_by
            NULL // updated_by
        ]
    ];
    
    foreach ($commissionRules as $rule) {
        $stmt = $db->prepare("INSERT INTO commission_rules (
            rule_name, rule_type, min_price, max_price, property_type, location_filter, agent_level,
            commission_rate, fixed_amount, tier_rates, bonus_conditions,
            is_active, priority, effective_date, expiry_date,
            tax_deduction, other_deductions, payment_terms, notes,
            created_by, updated_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute($rule);
    }
    
    echo "✅ " . count($commissionRules) . " commission rules inserted\n";
    
    echo "\n🎉 Phase 7: Resell Properties + Commission System Complete!\n";
    echo "✅ Resell Properties Table: Created with 40+ fields\n";
    echo "✅ Commission Calculations Table: Complete tracking system\n";
    echo "✅ Commission Rules Table: Flexible rule engine\n";
    echo "✅ Sample Data: 2 resell properties, 3 commission rules\n";
    echo "✅ Features: Multiple commission types, tiered rates, bonuses\n";
    echo "✅ Status Tracking: Full lifecycle management\n";
    echo "📈 Ready for Resell Property Management!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
?>
