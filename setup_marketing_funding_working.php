<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected\n";
    
    // 1. Create Marketing Campaigns Table
    echo "📈 Creating marketing_campaigns table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS marketing_campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        colony_id INT NOT NULL,
        campaign_name VARCHAR(200) NOT NULL,
        campaign_type ENUM('funding', 'pre_launch', 'launch', 'special_offer', 'investment_plan') DEFAULT 'funding',
        description TEXT,
        target_amount DECIMAL(15,2) DEFAULT 0,
        raised_amount DECIMAL(15,2) DEFAULT 0,
        start_date DATE,
        end_date DATE,
        status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
        features TEXT,
        terms_conditions TEXT,
        contact_person VARCHAR(100),
        contact_phone VARCHAR(20),
        contact_email VARCHAR(100),
        is_featured TINYINT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (colony_id) REFERENCES colonies(id)
    )");
    
    // 2. Create Investment Plans Table
    echo "💰 Creating investment_plans table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS investment_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        colony_id INT NOT NULL,
        plan_name VARCHAR(200) NOT NULL,
        plan_type ENUM('emi', 'down_payment', 'installment', 'hybrid') DEFAULT 'emi',
        min_investment DECIMAL(12,2) DEFAULT 0,
        max_investment DECIMAL(12,2) DEFAULT 0,
        interest_rate DECIMAL(5,2) DEFAULT 0,
        tenure_months INT DEFAULT 0,
        processing_fee DECIMAL(5,2) DEFAULT 0,
        down_payment_percent DECIMAL(5,2) DEFAULT 0,
        emi_per_lakh DECIMAL(10,2) DEFAULT 0,
        features TEXT,
        eligibility TEXT,
        documents_required TEXT,
        status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
        valid_till DATE,
        is_popular TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (colony_id) REFERENCES colonies(id)
    )");
    
    // 3. Create Funding Investors Table
    echo "👥 Creating funding_investors table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS funding_investors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NOT NULL,
        investor_name VARCHAR(200) NOT NULL,
        investor_phone VARCHAR(20),
        investor_email VARCHAR(100),
        investment_amount DECIMAL(12,2) DEFAULT 0,
        investment_date DATE,
        payment_method VARCHAR(50),
        transaction_id VARCHAR(100),
        status ENUM('pending', 'confirmed', 'refunded') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns(id)
    )");
    
    // 4. Create Special Offers Table
    echo "🎁 Creating special_offers table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS special_offers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        colony_id INT NOT NULL,
        offer_title VARCHAR(200) NOT NULL,
        offer_type ENUM('discount', 'cashback', 'free_service', 'gift', 'limited_time') DEFAULT 'discount',
        description TEXT,
        discount_percent DECIMAL(5,2) DEFAULT 0,
        max_discount_amount DECIMAL(12,2) DEFAULT 0,
        min_investment DECIMAL(12,2) DEFAULT 0,
        valid_from DATE,
        valid_till DATE,
        terms_conditions TEXT,
        is_limited_time TINYINT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (colony_id) REFERENCES colonies(id)
    )");
    
    // 5. Get Raghunath Nagri ID
    echo "🔍 Getting Raghunath Nagri details...\n";
    $stmt = $db->prepare("SELECT id, name FROM colonies WHERE name = 'Raghunath Nagri'");
    $stmt->execute();
    $raghunathColony = $stmt->fetch();
    
    if (!$raghunathColony) {
        echo "❌ Raghunath Nagri colony not found\n";
        exit;
    }
    
    echo "✅ Raghunath Nagri found (ID: {$raghunathColony['id']})\n";
    
    // 6. Add Marketing Campaign using prepared statement
    echo "📈 Adding Marketing Campaign for Raghunath Nagri...\n";
    $campaignStmt = $db->prepare("INSERT INTO marketing_campaigns (colony_id, campaign_name, campaign_type, description, target_amount, raised_amount, start_date, end_date, status, features, terms_conditions, contact_person, contact_phone, contact_email, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $campaignStmt->execute([
        $raghunathColony['id'],
        'Raghunath Nagri Phase 1 Funding Campaign',
        'funding',
        'Exclusive funding opportunity for Raghunath Nagri Phase 1 development. Invest in premium residential plots in Gorakhpur with guaranteed returns and modern amenities. Limited time offer with special booking discounts.',
        50000000,
        12500000,
        '2024-04-01',
        '2024-06-30',
        'active',
        '"Early Bird Discount", "0% Processing Fee", "Flexible Payment Plans", "Free Legal Documentation", "Instant Plot Allocation", "Complimentary Site Visit"',
        '1. Booking amount: 10% of plot value\n2. Remaining amount within 90 days\n3. Special discount for first 50 investors\n4. Free transfer and registration\n5. Complimentary layout plan and brochure',
        'Marketing Manager',
        '+91-XXXXXXXXXX',
        'marketing@apsdreamhome.com',
        1,
        1
    ]);
    
    $campaignId = $db->lastInsertId();
    echo "✅ Marketing campaign created (ID: $campaignId)\n";
    
    // 7. Add Investment Plans using prepared statement
    echo "💰 Adding Investment Plans for Raghunath Nagri...\n";
    $plansStmt = $db->prepare("INSERT INTO investment_plans (colony_id, plan_name, plan_type, min_investment, max_investment, interest_rate, tenure_months, processing_fee, down_payment_percent, emi_per_lakh, features, eligibility, documents_required, status, valid_till, is_popular) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Plan 1: Zero Interest EMI
    $plansStmt->execute([
        $raghunathColony['id'],
        'Zero Interest EMI Plan',
        'emi',
        100000,
        5000000,
        0,
        24,
        0,
        30,
        4167,
        '"0% Interest", "24 Months Tenure", "30% Down Payment", "No Hidden Charges", "Quick Approval", "Flexible EMI Dates"',
        'Minimum monthly income: ₹25,000\nAge: 21-60 years\nCredit score: 650+\nValid ID and address proof',
        '"Aadhaar Card", "PAN Card", "Income Proof", "Bank Statements", "Address Proof", "Photographs"',
        'active',
        '2024-06-30',
        1
    ]);
    
    // Plan 2: Down Payment Plan
    $plansStmt->execute([
        $raghunathColony['id'],
        'Smart Down Payment Plan',
        'down_payment',
        200000,
        10000000,
        0,
        12,
        2,
        50,
        0,
        '"50% Down Payment", "12 Months Balance", "2% Processing Fee", "Instant Booking", "Priority Plot Selection", "Free Registration"',
        'Minimum monthly income: ₹35,000\nAge: 25-65 years\nValid property documents\nBank account with minimum balance',
        '"Property Documents", "Income Proof", "Bank Statements", "ID Proof", "Address Proof", "Photographs"',
        'active',
        '2024-06-30',
        0
    ]);
    
    // Plan 3: Hybrid Investment Plan
    $plansStmt->execute([
        $raghunathColony['id'],
        'Hybrid Investment Plan',
        'hybrid',
        500000,
        15000000,
        6,
        36,
        1,
        20,
        3033,
        '"6% Interest Rate", "36 Months Tenure", "20% Down Payment", "1% Processing Fee", "Flexible Terms", "Investment Returns"',
        'Minimum monthly income: ₹40,000\nAge: 25-60 years\nGood credit history\nValid investment documents',
        '"Investment Proof", "Income Tax Returns", "Bank Statements", "ID Proof", "Address Proof", "Photographs"',
        'active',
        '2024-06-30',
        0
    ]);
    
    echo "✅ 3 investment plans created\n";
    
    // 8. Add Special Offers using prepared statement
    echo "🎁 Adding Special Offers for Raghunath Nagri...\n";
    $offersStmt = $db->prepare("INSERT INTO special_offers (colony_id, offer_title, offer_type, description, discount_percent, max_discount_amount, min_investment, valid_from, valid_till, terms_conditions, is_limited_time, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Offer 1: Early Bird Discount
    $offersStmt->execute([
        $raghunathColony['id'],
        'Early Bird Special - First 50 Investors',
        'discount',
        'Exclusive 5% discount for first 50 investors in Raghunath Nagri Phase 1. Book your dream plot now and save big!',
        5,
        100000,
        200000,
        '2024-04-01',
        '2024-04-30',
        '1. Valid for first 50 investors only\n2. Minimum investment: ₹2 lakhs\n3. Cannot be combined with other offers\n4. Discount applicable on plot value only\n5. Booking amount must be paid within 7 days',
        1,
        1
    ]);
    
    // Offer 2: Zero Processing Fee
    $offersStmt->execute([
        $raghunathColony['id'],
        'Zero Processing Fee - Limited Period',
        'free_service',
        'Pay zero processing fee on all investment plans for Raghunath Nagri. Save up to ₹25,000 on processing charges!',
        0,
        25000,
        100000,
        '2024-04-01',
        '2024-05-31',
        '1. Valid till May 31, 2024\n2. Applicable on all investment plans\n3. Minimum investment: ₹1 lakh\n4. Processing fee waiver applicable immediately\n5. Standard terms and conditions apply',
        1,
        1
    ]);
    
    echo "✅ 2 special offers created\n";
    
    // 9. Add Sample Investors using prepared statement
    echo "👥 Adding Sample Investors...\n";
    $investorStmt = $db->prepare("INSERT INTO funding_investors (campaign_id, investor_name, investor_phone, investor_email, investment_amount, investment_date, payment_method, transaction_id, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $sampleInvestors = [
        ['Ramesh Kumar', '+91-9876543210', 'ramesh.k@email.com', 500000, '2024-04-05', 'Bank Transfer', 'TXN123456', 'confirmed', 'Early bird investor - Block A preference'],
        ['Sunita Sharma', '+91-9876543211', 'sunita.sharma@email.com', 750000, '2024-04-08', 'Cheque', 'TXN123457', 'confirmed', 'Zero Interest EMI plan selected'],
        ['Amit Singh', '+91-9876543212', 'amit.singh@email.com', 300000, '2024-04-10', 'Online Transfer', 'TXN123458', 'confirmed', 'Down payment plan - Corner plot interest'],
        ['Priya Patel', '+91-9876543213', 'priya.patel@email.com', 600000, '2024-04-12', 'Bank Transfer', 'TXN123459', 'pending', 'Hybrid investment plan - Awaiting verification'],
        ['Rajesh Verma', '+91-9876543214', 'rajesh.verma@email.com', 400000, '2024-04-15', 'Cash', 'TXN123460', 'confirmed', 'Smart down payment - Block B preference']
    ];
    
    foreach ($sampleInvestors as $investor) {
        $investorStmt->execute([
            $campaignId,
            $investor[0],
            $investor[1],
            $investor[2],
            $investor[3],
            $investor[4],
            $investor[5],
            $investor[6],
            $investor[7],
            $investor[8]
        ]);
    }
    
    echo "✅ " . count($sampleInvestors) . " sample investors added\n";
    
    // 10. Update Raghunath Nagri with Marketing Info using prepared statement
    echo "🏰 Updating Raghunath Nagri with Marketing Information...\n";
    $updateStmt = $db->prepare("UPDATE colonies SET description = ?, amenities = ?, total_plots = ?, available_plots = ?, starting_price = ? WHERE id = ?");
    
    $updateStmt->execute([
        'Premium residential colony in Gorakhpur named after Lord Raghunath. Modern amenities with traditional values and excellent location. Special funding campaign available with zero-interest EMI plans and early bird discounts.',
        '"Temple", "Park", "School", "Hospital", "Market", "24/7 Security", "Wide Roads", "Underground Drainage", "Street Lights", "Water Supply", "Community Hall", "Jogging Track", "Children Play Area", "Senior Citizen Corner"',
        85,
        25,
        2000000,
        $raghunathColony['id']
    ]);
    
    echo "✅ Raghunath Nagri updated with marketing information\n";
    
    // 11. Final Summary
    echo "\n📊 Marketing & Funding Setup Summary:\n";
    
    $campaignCount = $db->query("SELECT COUNT(*) as count FROM marketing_campaigns WHERE colony_id = {$raghunathColony['id']}")->fetch()['count'];
    $planCount = $db->query("SELECT COUNT(*) as count FROM investment_plans WHERE colony_id = {$raghunathColony['id']}")->fetch()['count'];
    $investorCount = $db->query("SELECT COUNT(*) as count FROM funding_investors WHERE campaign_id = $campaignId")->fetch()['count'];
    $offerCount = $db->query("SELECT COUNT(*) as count FROM special_offers WHERE colony_id = {$raghunathColony['id']}")->fetch()['count'];
    
    echo "🏘️ Colony: Raghunath Nagri (ID: {$raghunathColony['id']})\n";
    echo "📈 Marketing Campaigns: $campaignCount\n";
    echo "💰 Investment Plans: $planCount\n";
    echo "👥 Investors: $investorCount\n";
    echo "🎁 Special Offers: $offerCount\n";
    
    // Campaign progress
    $campaign = $db->query("SELECT target_amount, raised_amount FROM marketing_campaigns WHERE id = $campaignId")->fetch();
    $progress = ($campaign['raised_amount'] / $campaign['target_amount']) * 100;
    echo "💸 Funding Progress: " . round($progress, 1) . "% (₹" . number_format($campaign['raised_amount']) . " / ₹" . number_format($campaign['target_amount']) . ")\n";
    
    echo "\n🎉 Marketing & Funding Setup Completed Successfully!\n";
    echo "📈 Campaign: Raghunath Nagri Phase 1 Funding\n";
    echo "⏰ Limited Time: April 1 - June 30, 2024\n";
    echo "💰 Target: ₹5 Crore, Raised: ₹1.25 Crore (25%)\n";
    echo "🎯 3 Investment Plans Available\n";
    echo "🎁 2 Special Offers Active\n";
    echo "👥 5 Sample Investors Added\n";
    echo "🏪 Ready for Marketing & Sales\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
?>
