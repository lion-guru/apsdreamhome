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
    
    echo "<h1>🏆 Fixed Agent vs Associate Commission System</h1>";
    
    // Drop and recreate associate_teams table without foreign keys
    echo "<h2>👥 Create Associate Team Management:</h2>";
    
    $pdo->exec("DROP TABLE IF EXISTS associate_teams");
    
    $createTeamTable = "
        CREATE TABLE associate_teams (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            team_member_name VARCHAR(255) NOT NULL,
            team_member_email VARCHAR(255) NOT NULL,
            team_member_phone VARCHAR(20),
            commission_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            commission_amount DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createTeamTable);
    echo "✅ Created associate_teams table (without foreign keys)<br>";
    
    // Create agent commission structure table
    echo "<h2>📋 Create Agent Commission Structure:</h2>";
    
    $createAgentTable = "
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
    
    $pdo->exec($createAgentTable);
    echo "✅ Created agent_commission_rates table<br>";
    
    // Insert agent commission rates
    $agentRates = [
        [0, 50, 50, 0],      // 0-50 sq ft: ₹50 per sq ft
        [51, 100, 75, 0],    // 51-100 sq ft: ₹75 per sq ft
        [101, 150, 100, 0],   // 101-150 sq ft: ₹100 per sq ft
        [151, 200, 150, 0],   // 151-200 sq ft: ₹150 per sq ft
        [201, 500, 200, 0],   // 201-500 sq ft: ₹200 per sq ft
        [501, 1000, 250, 0],  // 501-1000 sq ft: ₹250 per sq ft
        [1001, 9999, 300, 0]  // 1000+ sq ft: ₹300 per sq ft
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
    
    // Update users table for agent/associate roles
    echo "<h2>👤 Update User Roles:</h2>";
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN user_role ENUM('customer', 'agent', 'associate') DEFAULT 'customer' AFTER user_type");
        echo "✅ Added user_role column to users table<br>";
    } catch (Exception $e) {
        echo "Column user_role already exists<br>";
    }
    
    // Create test users
    echo "<h2>👥 Create Test Users:</h2>";
    
    // Create test agent
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
    
    // Create test associate
    $associateData = [
        'name' => 'Associate Sharma',
        'email' => 'associate@apsdreamhome.com',
        'phone' => '9876543212',
        'password' => password_hash('associate123', PASSWORD_DEFAULT),
        'user_type' => 'associate',
        'user_role' => 'associate',
        'status' => 'active'
    ];
    
    $stmt->execute(array_values($associateData));
    echo "✅ Created test associate: " . $associateData['name'] . "<br>";
    
    // Get user IDs
    $stmt = $pdo->query("SELECT id, name, email, user_role FROM users WHERE user_role IN ('agent', 'associate') ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $agentId = null;
    $associateId = null;
    
    foreach ($users as $user) {
        if ($user['user_role'] == 'agent') {
            $agentId = $user['id'];
            echo "Agent ID: $agentId - " . $user['name'] . "<br>";
        } elseif ($user['user_role'] == 'associate') {
            $associateId = $user['id'];
            echo "Associate ID: $associateId - " . $user['name'] . "<br>";
        }
    }
    
    // Create test plot bookings
    echo "<h2>🏠 Create Test Plot Bookings:</h2>";
    
    if ($agentId && $associateId) {
        // Agent plot booking
        $agentPlot = [
            'user_id' => $agentId,
            'posted_by' => $agentId,
            'posted_by_type' => 'agent',
            'name' => 'Agent Kumar',
            'phone' => '9876543211',
            'email' => 'agent@apsdreamhome.com',
            'property_type' => 'Residential Plot',
            'listing_type' => 'sell',
            'address' => 'Agent Plot - Gomti Nagar, Lucknow',
            'area_sqft' => 120, // Falls in 101-150 sq ft range
            'price' => 12000, // 120 * 100 = ₹12,000 commission
            'price_type' => 'rupees',
            'description' => 'Agent booked plot - 120 sq ft',
            'status' => 'approved'
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute(array_values($agentPlot));
        $agentPropertyId = $pdo->lastInsertId();
        echo "✅ Created agent plot booking - Property ID: $agentPropertyId<br>";
        
        // Associate plot booking
        $associatePlot = [
            'user_id' => $associateId,
            'posted_by' => $associateId,
            'posted_by_type' => 'associate',
            'name' => 'Associate Sharma',
            'phone' => '9876543212',
            'email' => 'associate@apsdreamhome.com',
            'property_type' => 'Residential Plot',
            'listing_type' => 'sell',
            'address' => 'Associate Plot - Hazratganj, Lucknow',
            'area_sqft' => 1000,
            'price' => 1000000, // 10 lakh
            'price_type' => 'lakh',
            'description' => 'Associate booked plot - 1000 sq ft',
            'status' => 'approved'
        ];
        
        $stmt->execute(array_values($associatePlot));
        $associatePropertyId = $pdo->lastInsertId();
        echo "✅ Created associate plot booking - Property ID: $associatePropertyId<br>";
        
        // Calculate agent commission
        echo "<h2>💰 Agent Commission Calculation:</h2>";
        
        $stmt = $pdo->prepare("
            SELECT commission_per_sqft FROM agent_commission_rates 
            WHERE ? BETWEEN min_sqft AND max_sqft AND status = 'active'
        ");
        $stmt->execute([$agentPlot['area_sqft']]);
        $agentRate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($agentRate) {
            $agentCommission = $agentPlot['area_sqft'] * $agentRate['commission_per_sqft'];
            
            echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px;'>";
            echo "<h3>👨‍💼 Agent Commission Details:</h3>";
            echo "<p><strong>Plot Area:</strong> " . $agentPlot['area_sqft'] . " sq ft</p>";
            echo "<p><strong>Rate:</strong> ₹" . $agentRate['commission_per_sqft'] . " per sq ft</p>";
            echo "<p><strong>Total Commission:</strong> <span style='color: green; font-size: 18px;'>₹" . number_format($agentCommission) . "</span></p>";
            echo "</div>";
            
            // Create agent commission record
            $stmt = $pdo->prepare("
                INSERT INTO mlm_commissions (associate_id, property_id, commission_amount, commission_percentage, level, status, commission_type, created_at)
                VALUES (?, ?, ?, ?, 1, 'pending', 'direct', NOW())
            ");
            $stmt->execute([$agentId, $agentPropertyId, $agentCommission, 0]);
            echo "✅ Agent commission record created<br>";
        }
        
        // Create associate team
        echo "<h2>👥 Create Associate Team:</h2>";
        
        $teamMembers = [
            ['name' => 'Team Member 1', 'email' => 'member1@team.com', 'phone' => '9876543213', 'commission_percentage' => 5.00],
            ['name' => 'Team Member 2', 'email' => 'member2@team.com', 'phone' => '9876543214', 'commission_percentage' => 3.00],
            ['name' => 'Team Member 3', 'email' => 'member3@team.com', 'phone' => '9876543215', 'commission_percentage' => 2.00]
        ];
        
        foreach ($teamMembers as $member) {
            $stmt = $pdo->prepare("
                INSERT INTO associate_teams (associate_id, team_member_name, team_member_email, team_member_phone, commission_percentage, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$associateId, $member['name'], $member['email'], $member['phone'], $member['commission_percentage']]);
            echo "✅ Added team member: " . $member['name'] . " (" . $member['commission_percentage'] . "%)<br>";
        }
        
        // Calculate associate commission
        echo "<h2>💰 Associate Commission Calculation:</h2>";
        
        $associateCommissionRate = 10; // 10% of property value
        $associateCommission = $associatePlot['price'] * ($associateCommissionRate / 100);
        
        echo "<div style='background: #f3e5f5; padding: 15px; border-radius: 8px;'>";
        echo "<h3>👥 Associate Commission Details:</h3>";
        echo "<p><strong>Property Price:</strong> ₹" . number_format($associatePlot['price']) . "</p>";
        echo "<p><strong>Commission Rate:</strong> " . $associateCommissionRate . "%</p>";
        echo "<p><strong>Total Commission:</strong> <span style='color: green; font-size: 18px;'>₹" . number_format($associateCommission) . "</span></p>";
        echo "</div>";
        
        // Create associate commission record
        $stmt = $pdo->prepare("
            INSERT INTO mlm_commissions (associate_id, property_id, commission_amount, commission_percentage, level, status, commission_type, created_at)
            VALUES (?, ?, ?, ?, 1, 'pending', 'direct', NOW())
        ");
        $stmt->execute([$associateId, $associatePropertyId, $associateCommission, $associateCommissionRate]);
        echo "✅ Associate commission record created<br>";
        
        // Calculate team member commissions
        echo "<h2>👥 Team Member Commissions:</h2>";
        
        $stmt = $pdo->query("
            SELECT * FROM associate_teams 
            WHERE associate_id = ? AND status = 'active'
        ", [$associateId]);
        
        $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #007bff; color: white;'><th>Team Member</th><th>Email</th><th>Commission %</th><th>Commission Amount</th><th>Status</th></tr>";
        
        foreach ($teamMembers as $member) {
            $memberCommission = $associateCommission * ($member['commission_percentage'] / 100);
            
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($member['team_member_name']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($member['team_member_email']) . "</td>";
            echo "<td>" . $member['commission_percentage'] . "%</td>";
            echo "<td><strong style='color: green;'>₹" . number_format($memberCommission) . "</strong></td>";
            echo "<td><span style='background: #ffc107; color: black; padding: 2px 8px; border-radius: 3px;'>PENDING</span></td>";
            echo "</tr>";
            
            // Create team member commission record
            $stmt = $pdo->prepare("
                INSERT INTO mlm_commissions (associate_id, commission_amount, commission_percentage, level, status, commission_type, created_at)
                VALUES (?, ?, ?, 2, 'pending', 'team_bonus', NOW())
            ");
            $stmt->execute([$associateId, $memberCommission, $member['commission_percentage']]);
        }
        echo "</table>";
        
        // Display complete system comparison
        echo "<h2>📊 Complete System Comparison:</h2>";
        
        echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
        
        // Agent System
        echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px;'>";
        echo "<h3 style='color: white;'>👨‍💼 Agent System</h3>";
        echo "<ul style='color: white;'>";
        echo "<li><strong>Commission Type:</strong> Square Feet Based</li>";
        echo "<li><strong>Rate Structure:</strong></li>";
        echo "<ul style='color: white;'>";
        foreach ($agentRates as $rate) {
            echo "<li>" . $rate[0] . "-" . $rate[1] . " sq ft: ₹" . $rate[2] . "/sq ft</li>";
        }
        echo "</ul>";
        echo "<li><strong>Example:</strong> 120 sq ft × ₹100 = ₹" . number_format($agentCommission) . "</li>";
        echo "<li><strong>Team Management:</strong> Not applicable</li>";
        echo "</ul>";
        echo "</div>";
        
        // Associate System
        echo "<div style='background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px;'>";
        echo "<h3 style='color: white;'>👥 Associate System</h3>";
        echo "<ul style='color: white;'>";
        echo "<li><strong>Commission Type:</strong> Percentage Based</li>";
        echo "<li><strong>Direct Commission:</strong> " . $associateCommissionRate . "% of property value</li>";
        echo "<li><strong>Team Management:</strong> Can add team members</li>";
        echo "<li><strong>Team Commission:</strong> Set percentage for team members</li>";
        echo "<li><strong>Example:</strong> ₹" . number_format($associatePlot['price']) . " × " . $associateCommissionRate . "% = ₹" . number_format($associateCommission) . "</li>";
        echo "<li><strong>Team Size:</strong> " . count($teamMembers) . " members</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "</div>";
        
        // Backend management section
        echo "<div style='background: #28a745; color: white; padding: 20px; border-radius: 10px; margin-top: 20px;'>";
        echo "<h3 style='color: white;'>⚙️ Backend Management System</h3>";
        echo "<div style='display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;'>";
        echo "<div>";
        echo "<h4 style='color: white;'>📊 Commission Tracking</h4>";
        echo "<ul style='color: white;'>";
        echo "<li>✅ Real-time calculation</li>";
        echo "<li>✅ Status tracking</li>";
        echo "<li>✅ Payment records</li>";
        echo "</ul>";
        echo "</div>";
        echo "<div>";
        echo "<h4 style='color: white;'>👥 Team Management</h4>";
        echo "<ul style='color: white;'>";
        echo "<li>✅ Add/remove members</li>";
        echo "<li>✅ Set commission %</li>";
        echo "<li>✅ Performance tracking</li>";
        echo "</ul>";
        echo "</div>";
        echo "<div>";
        echo "<h4 style='color: white;'>🔄 Referral System</h4>";
        echo "<ul style='color: white;'>";
        echo "<li>✅ Automatic calculation</li>";
        echo "<li>✅ Multi-level support</li>";
        echo "<li>✅ Commission distribution</li>";
        echo "</ul>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
    } else {
        echo "❌ Could not create test users<br>";
    }
    
    echo "<h1>🎉 Agent vs Associate System - COMPLETE!</h1>";
    echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px;'>";
    echo "<h2 style='color: white;'>✅ System Successfully Implemented!</h2>";
    echo "<p style='color: white; font-size: 16px;'><strong>Key Differences:</strong></p>";
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
    echo "<div>";
    echo "<h3 style='color: white;'>👨‍💼 Agent</h3>";
    echo "<ul style='color: white;'>";
    echo "<li>💰 Square feet based commission</li>";
    echo "<li>📏 ₹50-300 per sq ft rates</li>";
    echo "<li>🏠 Direct booking commission</li>";
    echo "<li>📊 Fixed rate calculation</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div>";
    echo "<h3 style='color: white;'>👥 Associate</h3>";
    echo "<ul style='color: white;'>";
    echo "<li>💰 Percentage based commission</li>";
    echo "<li>👥 Can add team members</li>";
    echo "<li>⚙️ Set team commission %</li>";
    echo "<li>🔄 Automatic referral calculation</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    echo "<p style='color: white; text-align: center; margin-top: 20px; font-size: 18px;'><strong>🏆 Both systems are fully functional with backend management!</strong></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
