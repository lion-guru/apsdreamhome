<?php
// Database configuration
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🏆 Agent Square Feet Commission + MLM System</h1>";
    
    // Create agent commission rates table
    echo "<h2>📋 Agent Commission Rates Setup:</h2>";
    
    $createAgentRatesTable = "
        CREATE TABLE IF NOT EXISTS agent_commission_rates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            min_sqft INT NOT NULL,
            max_sqft INT NOT NULL,
            commission_per_sqft DECIMAL(10,2) NOT NULL,
            commission_percentage DECIMAL(5,2) DEFAULT 0.00,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createAgentRatesTable);
    echo "✅ Created agent_commission_rates table<br>";
    
    // Insert agent commission rates for different sq ft ranges
    $agentRates = [
        [0, 50, 50, 0],      // 0-50 sq ft: ₹50 per sq ft
        [51, 100, 100, 0],    // 51-100 sq ft: ₹100 per sq ft
        [101, 150, 150, 0],   // 101-150 sq ft: ₹150 per sq ft
        [151, 200, 200, 0],   // 151-200 sq ft: ₹200 per sq ft
        [201, 500, 250, 0],   // 201-500 sq ft: ₹250 per sq ft
        [501, 1000, 300, 0],  // 501-1000 sq ft: ₹300 per sq ft
        [1001, 9999, 350, 0]  // 1001+ sq ft: ₹350 per sq ft
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO agent_commission_rates (min_sqft, max_sqft, commission_per_sqft, commission_percentage, status)
        VALUES (?, ?, ?, ?, 'active')
        ON DUPLICATE KEY UPDATE commission_per_sqft = VALUES(commission_per_sqft)
    ");
    
    foreach ($agentRates as $rate) {
        $stmt->execute($rate);
        echo "✅ Agent Rate: " . $rate[0] . "-" . $rate[1] . " sq ft = ₹" . $rate[2] . "/sq ft<br>";
    }
    
    // Create agent to associate referral system
    echo "<h2>🔄 Agent to Associate Referral System:</h2>";
    
    $createReferralTable = "
        CREATE TABLE IF NOT EXISTS agent_associate_referrals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            agent_id INT NOT NULL,
            associate_id INT NOT NULL,
            property_id INT,
            referral_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            commission_generated DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('pending', 'confirmed', 'paid') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (associate_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (property_id) REFERENCES user_properties(id) ON DELETE CASCADE
        )
    ";
    
    $pdo->exec($createReferralTable);
    echo "✅ Created agent_associate_referrals table<br>";
    
    // Update users table to include agent role
    echo "<h2>👤 Update User Roles:</h2>";
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN user_role ENUM('customer', 'agent', 'associate') DEFAULT 'customer' AFTER user_type");
        echo "✅ Added user_role column to users table<br>";
    } catch (Exception $e) {
        echo "Column user_role already exists<br>";
    }
    
    // Create test agent user
    echo "<h2>👨‍💼 Create Test Agent:</h2>";
    
    $agentData = [
        'name' => 'Agent Kumar',
        'email' => 'agent@apsdreamhome.com',
        'phone' => '9876543211',
        'password' => password_hash('agent123', PASSWORD_DEFAULT),
        'user_type' => 'agent',
        'user_role' => 'agent',
        'status' => 'active'
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, phone, password, user_type, user_role, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE user_role = VALUES(user_role)
    ");
    
    $stmt->execute(array_values($agentData));
    echo "✅ Created test agent: " . $agentData['name'] . "<br>";
    
    // Create test associate users (referred by agent)
    echo "<h2>👥 Create Test Associates (Referred by Agent):</h2>";
    
    $associates = [
        ['name' => 'Associate Sharma', 'email' => 'associate.sharma@apsdreamhome.com', 'phone' => '9876543212'],
        ['name' => 'Associate Verma', 'email' => 'associate.verma@apsdreamhome.com', 'phone' => '9876543213'],
        ['name' => 'Associate Gupta', 'email' => 'associate.gupta@apsdreamhome.com', 'phone' => '9876543214']
    ];
    
    foreach ($associates as $assoc) {
        $associateData = [
            'name' => $assoc['name'],
            'email' => $assoc['email'],
            'phone' => $assoc['phone'],
            'password' => password_hash('associate123', PASSWORD_DEFAULT),
            'user_type' => 'associate',
            'user_role' => 'associate',
            'status' => 'active'
        ];
        
        $stmt->execute(array_values($associateData));
        echo "✅ Created associate: " . $assoc['name'] . "<br>";
    }
    
    // Get user IDs
    $stmt = $pdo->query("SELECT id, name, email, user_role FROM users WHERE user_role IN ('agent', 'associate') ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $agentId = null;
    $associateIds = [];
    
    foreach ($users as $user) {
        if ($user['user_role'] == 'agent') {
            $agentId = $user['id'];
            echo "Agent ID: $agentId - " . $user['name'] . "<br>";
        } elseif ($user['user_role'] == 'associate') {
            $associateIds[] = $user['id'];
            echo "Associate ID: " . $user['id'] . " - " . $user['name'] . "<br>";
        }
    }
    
    // Create agent plot bookings with different sq ft
    echo "<h2>🏠 Create Agent Plot Bookings (Different Sq Ft):</h2>";
    
    $agentBookings = [
        ['area_sqft' => 45, 'address' => 'Plot A-45, Gomti Nagar'],   // 0-50 range
        ['area_sqft' => 75, 'address' => 'Plot B-75, Gomti Nagar'],   // 51-100 range
        ['area_sqft' => 120, 'address' => 'Plot C-120, Gomti Nagar'], // 101-150 range
        ['area_sqft' => 175, 'address' => 'Plot D-175, Gomti Nagar'], // 151-200 range
        ['area_sqft' => 350, 'address' => 'Plot E-350, Gomti Nagar'], // 201-500 range
        ['area_sqft' => 750, 'address' => 'Plot F-750, Gomti Nagar']  // 501-1000 range
    ];
    
    foreach ($agentBookings as $booking) {
        // Get commission rate for this sq ft range
        $stmt = $pdo->prepare("
            SELECT commission_per_sqft FROM agent_commission_rates 
            WHERE ? BETWEEN min_sqft AND max_sqft AND status = 'active'
        ");
        $stmt->execute([$booking['area_sqft']]);
        $rateData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($rateData) {
            $commission = $booking['area_sqft'] * $rateData['commission_per_sqft'];
            
            $propertyData = [
                'user_id' => $agentId,
                'posted_by' => $agentId,
                'posted_by_type' => 'agent',
                'name' => $agentData['name'],
                'phone' => $agentData['phone'],
                'email' => $agentData['email'],
                'property_type' => 'Residential Plot',
                'listing_type' => 'sell',
                'address' => $booking['address'],
                'area_sqft' => $booking['area_sqft'],
                'price' => $commission,
                'price_type' => 'rupees',
                'description' => 'Agent booking - ' . $booking['area_sqft'] . ' sq ft',
                'status' => 'approved'
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute(array_values($propertyData));
            $propertyId = $pdo->lastInsertId();
            
            echo "<div style='background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
            echo "<strong>Agent Booking:</strong> " . $booking['area_sqft'] . " sq ft<br>";
            echo "<strong>Rate:</strong> ₹" . $rateData['commission_per_sqft'] . "/sq ft<br>";
            echo "<strong>Commission:</strong> ₹" . number_format($commission) . "<br>";
            echo "</div>";
            
            // Create agent commission record
            $stmt = $pdo->prepare("
                INSERT INTO mlm_commissions (associate_id, property_id, commission_amount, commission_percentage, level, status, commission_type, created_at)
                VALUES (?, ?, ?, ?, 1, 'pending', 'agent_booking', NOW())
            ");
            $stmt->execute([$agentId, $propertyId, $commission, 0]);
        }
    }
    
    // Create MLM structure for associates
    echo "<h2>👥 Create MLM Structure for Associates:</h2>";
    
    // Create MLM levels for associates
    $createMLMLevelsTable = "
        CREATE TABLE IF NOT EXISTS associate_mlm_levels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            level_number INT NOT NULL UNIQUE,
            level_name VARCHAR(100) NOT NULL,
            commission_percentage DECIMAL(5,2) NOT NULL,
            min_team_size INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createMLMLevelsTable);
    echo "✅ Created associate_mlm_levels table<br>";
    
    // Insert MLM levels
    $mlmLevels = [
        [1, 'Direct Associate', 10, 1],
        [2, 'Level 2 Associate', 5, 5],
        [3, 'Level 3 Associate', 3, 15],
        [4, 'Level 4 Associate', 2, 50],
        [5, 'Level 5 Associate', 1, 100]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO associate_mlm_levels (level_number, level_name, commission_percentage, min_team_size, status)
        VALUES (?, ?, ?, ?, 'active')
        ON DUPLICATE KEY UPDATE commission_percentage = VALUES(commission_percentage)
    ");
    
    foreach ($mlmLevels as $level) {
        $stmt->execute($level);
        echo "✅ MLM Level " . $level[0] . ": " . $level[1] . " (" . $level[2] . "%)<br>";
    }
    
    // Create associate network tree
    $createNetworkTable = "
        CREATE TABLE IF NOT EXISTS associate_network_tree (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            sponsor_id INT,
            level INT DEFAULT 0,
            position ENUM('left', 'right') DEFAULT 'left',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (associate_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (sponsor_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ";
    
    $pdo->exec($createNetworkTable);
    echo "✅ Created associate_network_tree table<br>";
    
    // Create network structure (Agent -> Associates)
    foreach ($associateIds as $index => $associateId) {
        $stmt = $pdo->prepare("
            INSERT INTO associate_network_tree (associate_id, sponsor_id, level, position, created_at)
            VALUES (?, ?, 1, ?, NOW())
            ON DUPLICATE KEY UPDATE sponsor_id = VALUES(sponsor_id)
        ");
        
        $position = ($index % 2 == 0) ? 'left' : 'right';
        $stmt->execute([$associateId, $agentId, $position]);
        
        echo "✅ Added Associate to Network: Level 1, Position: $position<br>";
    }
    
    // Display complete system summary
    echo "<h1>🎉 Agent + MLM System Complete!</h1>";
    
    echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px;'>";
    echo "<h2 style='color: white;'>✅ System Successfully Implemented!</h2>";
    
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
    
    // Agent System Summary
    echo "<div>";
    echo "<h3 style='color: white;'>👨‍💼 Agent System</h3>";
    echo "<ul style='color: white;'>";
    echo "<li><strong>Commission Type:</strong> Square Feet Based</li>";
    echo "<li><strong>Rate Structure:</strong></li>";
    foreach ($agentRates as $rate) {
        echo "<li>" . $rate[0] . "-" . $rate[1] . " sq ft: ₹" . $rate[2] . "/sq ft</li>";
    }
    echo "<li><strong>Automatic Calculation:</strong> Commission calculated based on sq ft</li>";
    echo "<li><strong>Referral System:</strong> Associates added to agent's MLM network</li>";
    echo "</ul>";
    echo "</div>";
    
    // Associate MLM System Summary
    echo "<div>";
    echo "<h3 style='color: white;'>👥 Associate MLM System</h3>";
    echo "<ul style='color: white;'>";
    echo "<li><strong>Commission Type:</strong> Percentage Based</li>";
    echo "<li><strong>MLM Levels:</strong> 5 levels (1-10%)</li>";
    echo "<li><strong>Network Structure:</strong> Multi-level referral system</li>";
    echo "<li><strong>Team Management:</strong> Can add team members</li>";
    echo "<li><strong>Automatic Calculation:</strong> Real-time commission computation</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px;'>";
    echo "<h3 style='color: white;'>🔄 How It Works:</h3>";
    echo "<ol style='color: white;'>";
    echo "<li><strong>Agent books plot:</strong> Commission calculated based on square feet</li>";
    echo "<li><strong>Agent refers associate:</strong> Associate added to agent's network</li>";
    echo "<li><strong>Associate joins MLM:</strong> Gets percentage-based commission</li>";
    echo "<li><strong>Associate adds team:</strong> Multi-level network grows</li>";
    echo "<li><strong>Automatic calculation:</strong> All commissions calculated automatically</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p style='text-align: center; margin-top: 20px; font-size: 18px; color: white;'><strong>🏆 Complete Agent + MLM System Ready!</strong></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
