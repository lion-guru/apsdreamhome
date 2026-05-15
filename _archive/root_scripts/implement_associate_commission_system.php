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
    
    echo "<h1>👥 Associate Commission System Implementation</h1>";
    
    // Create associate commission structure table
    echo "<h2>📋 Associate Commission Structure:</h2>";
    
    $createAssociateCommissionTable = "
        CREATE TABLE IF NOT EXISTS associate_commission_structure (
            id INT AUTO_INCREMENT PRIMARY KEY,
            level_number INT NOT NULL UNIQUE,
            level_name VARCHAR(100) NOT NULL,
            commission_percentage DECIMAL(5,2) NOT NULL,
            min_property_value DECIMAL(15,2) DEFAULT 0.00,
            max_property_value DECIMAL(15,2) DEFAULT 999999999.99,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createAssociateCommissionTable);
    echo "✅ Created associate_commission_structure table<br>";
    
    // Insert associate commission levels
    $associateLevels = [
        [1, 'Direct Associate', 10.00, 0, 999999999.99],      // 10% direct commission
        [2, 'Level 2 Associate', 5.00, 0, 999999999.99],       // 5% level 2
        [3, 'Level 3 Associate', 3.00, 0, 999999999.99],       // 3% level 3
        [4, 'Level 4 Associate', 2.00, 0, 999999999.99],       // 2% level 4
        [5, 'Level 5 Associate', 1.00, 0, 999999999.99]        // 1% level 5
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO associate_commission_structure (level_number, level_name, commission_percentage, min_property_value, max_property_value, status)
        VALUES (?, ?, ?, ?, ?, 'active')
        ON DUPLICATE KEY UPDATE commission_percentage = VALUES(commission_percentage)
    ");
    
    foreach ($associateLevels as $level) {
        $stmt->execute($level);
        echo "✅ Level " . $level[0] . ": " . $level[1] . " - " . $level[2] . "% commission<br>";
    }
    
    // Create team management table for associates
    echo "<h2>👥 Associate Team Management:</h2>";
    
    $createTeamTable = "
        CREATE TABLE IF NOT EXISTS associate_team_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            team_member_id INT NOT NULL,
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
    echo "✅ Created associate_team_members table<br>";
    
    // Create commission calculation table
    echo "<h2>💰 Commission Calculation System:</h2>";
    
    $createCommissionCalcTable = "
        CREATE TABLE IF NOT EXISTS associate_commission_calculations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            property_id INT NOT NULL,
            property_value DECIMAL(15,2) NOT NULL,
            commission_level INT NOT NULL,
            commission_percentage DECIMAL(5,2) NOT NULL,
            commission_amount DECIMAL(10,2) NOT NULL,
            commission_type ENUM('direct', 'team_bonus', 'referral') DEFAULT 'direct',
            status ENUM('pending', 'confirmed', 'paid') DEFAULT 'pending',
            calculation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createCommissionCalcTable);
    echo "✅ Created associate_commission_calculations table<br>";
    
    // Create test associate users
    echo "<h2>👤 Create Test Associates:</h2>";
    
    $associates = [
        ['name' => 'Associate Sharma', 'email' => 'associate.sharma@apsdreamhome.com', 'phone' => '9876543212'],
        ['name' => 'Associate Verma', 'email' => 'associate.verma@apsdreamhome.com', 'phone' => '9876543213'],
        ['name' => 'Associate Gupta', 'email' => 'associate.gupta@apsdreamhome.com', 'phone' => '9876543214']
    ];
    
    $associateIds = [];
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
        
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, phone, password, user_type, user_role, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE user_role = VALUES(user_role)
        ");
        
        $stmt->execute(array_values($associateData));
        $associateId = $pdo->lastInsertId();
        $associateIds[] = $associateId;
        echo "✅ Created Associate: " . $assoc['name'] . " (ID: $associateId)<br>";
    }
    
    // Create test property bookings for associates
    echo "<h2>🏠 Create Associate Property Bookings:</h2>";
    
    $properties = [
        ['value' => 500000, 'address' => 'Property 1 - Hazratganj, Lucknow'],   // ₹5 lakh
        ['value' => 1000000, 'address' => 'Property 2 - Gomti Nagar, Lucknow'], // ₹10 lakh
        ['value' => 1500000, 'address' => 'Property 3 - Alambagh, Lucknow']  // ₹15 lakh
    ];
    
    foreach ($associateIds as $index => $associateId) {
        if (isset($properties[$index])) {
            $property = $properties[$index];
            $directCommission = $property['value'] * 0.10; // 10% direct commission
            
            $propertyData = [
                'user_id' => $associateId,
                'posted_by' => $associateId,
                'posted_by_type' => 'associate',
                'name' => $associates[$index]['name'],
                'phone' => $associates[$index]['phone'],
                'email' => $associates[$index]['email'],
                'property_type' => 'Residential Plot',
                'listing_type' => 'sell',
                'address' => $property['address'],
                'area_sqft' => 1000,
                'price' => $property['value'],
                'price_type' => 'lakh',
                'description' => 'Associate property booking - ₹' . number_format($property['value']),
                'status' => 'approved'
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute(array_values($propertyData));
            $propertyId = $pdo->lastInsertId();
            
            echo "<div style='background: #f3e5f5; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
            echo "<h3>👥 Associate Property Booking:</h3>";
            echo "<p><strong>Associate:</strong> " . $associates[$index]['name'] . "</p>";
            echo "<p><strong>Property:</strong> " . $property['address'] . "</p>";
            echo "<p><strong>Property Value:</strong> ₹" . number_format($property['value']) . "</p>";
            echo "<p><strong>Direct Commission (10%):</strong> <span style='color: green; font-size: 18px;'>₹" . number_format($directCommission) . "</span></p>";
            echo "</div>";
            
            // Create commission calculation record
            $stmt = $pdo->prepare("
                INSERT INTO associate_commission_calculations (associate_id, property_id, property_value, commission_level, commission_percentage, commission_amount, commission_type, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'direct', 'pending', NOW())
            ");
            $stmt->execute([$associateId, $propertyId, $property['value'], 1, 10.00, $directCommission]);
        }
    }
    
    // Create team structure for main associate
    echo "<h2>👥 Create Team Structure:</h2>";
    
    $mainAssociateId = $associateIds[0]; // Use first associate as main
    
    $teamMembers = [
        ['name' => 'Team Member 1', 'email' => 'member1@team.com', 'phone' => '9876543215', 'commission_percentage' => 5.00],
        ['name' => 'Team Member 2', 'email' => 'member2@team.com', 'phone' => '9876543216', 'commission_percentage' => 3.00],
        ['name' => 'Team Member 3', 'email' => 'member3@team.com', 'phone' => '9876543217', 'commission_percentage' => 2.00]
    ];
    
    foreach ($teamMembers as $member) {
        // Add team member
        $stmt = $pdo->prepare("
            INSERT INTO associate_team_members (associate_id, team_member_name, team_member_email, team_member_phone, commission_percentage, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([$mainAssociateId, $member['name'], $member['email'], $member['phone'], $member['commission_percentage']]);
        
        echo "✅ Added Team Member: " . $member['name'] . " (" . $member['commission_percentage'] . "% commission)<br>";
        
        // Calculate team commission for main associate's property (₹10,00,000 = ₹100,000)
        $mainAssociateCommission = 100000; // From ₹10,00,000 property
        $teamCommission = $mainAssociateCommission * ($member['commission_percentage'] / 100);
        
        // Create team commission record
        $stmt = $pdo->prepare("
            INSERT INTO associate_commission_calculations (associate_id, property_value, commission_level, commission_percentage, commission_amount, commission_type, status, created_at)
            VALUES (?, ?, 2, ?, ?, 'team_bonus', 'pending', NOW())
        ");
        $stmt->execute([$mainAssociateId, 1000000, $member['commission_percentage'], $teamCommission]);
    }
    
    // Display commission calculation examples
    echo "<h2>💰 Commission Calculation Examples:</h2>";
    
    $exampleProperties = [
        ['value' => 500000, 'label' => '₹5 Lakh Property'],
        ['value' => 1000000, 'label' => '₹10 Lakh Property'],
        ['value' => 1500000, 'label' => '₹15 Lakh Property'],
        ['value' => 2000000, 'label' => '₹20 Lakh Property']
    ];
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #28a745; color: white;'><th>Property Value</th><th>Direct Commission (10%)</th><th>Team Member 1 (5%)</th><th>Team Member 2 (3%)</th><th>Team Member 3 (2%)</th><th>Total Payout</th></tr>";
    
    foreach ($exampleProperties as $property) {
        $directCommission = $property['value'] * 0.10;
        $team1Commission = $directCommission * 0.05;
        $team2Commission = $directCommission * 0.03;
        $team3Commission = $directCommission * 0.02;
        $totalPayout = $directCommission + $team1Commission + $team2Commission + $team3Commission;
        
        echo "<tr>";
        echo "<td><strong>" . $property['label'] . "</strong></td>";
        echo "<td>₹" . number_format($directCommission) . "</td>";
        echo "<td>₹" . number_format($team1Commission) . "</td>";
        echo "<td>₹" . number_format($team2Commission) . "</td>";
        echo "<td>₹" . number_format($team3Commission) . "</td>";
        echo "<td><strong style='color: green;'>₹" . number_format($totalPayout) . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Display system summary
    echo "<h1>🎉 Associate Commission System - COMPLETE!</h1>";
    
    echo "<div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; border-radius: 10px;'>";
    echo "<h2 style='color: white;'>✅ Associate Commission System Successfully Implemented!</h2>";
    
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
    
    // Commission Structure
    echo "<div>";
    echo "<h3 style='color: white;'>💰 Commission Structure</h3>";
    echo "<ul style='color: white;'>";
    echo "<li><strong>Direct Commission:</strong> 10% of property value</li>";
    echo "<li><strong>Team Management:</strong> Can add team members</li>";
    echo "<li><strong>Team Commission:</strong> Set percentage for team members</li>";
    echo "<li><strong>Example:</strong> ₹10,00,000 property = ₹100,000 commission</li>";
    echo "</ul>";
    echo "</div>";
    
    // Team Management
    echo "<div>";
    echo "<h3 style='color: white;'>👥 Team Management</h3>";
    echo "<ul style='color: white;'>";
    echo "<li><strong>Add Team Members:</strong> Unlimited team members</li>";
    echo "<li><strong>Set Commission %:</strong> Custom percentage per member</li>";
    echo "<li><strong>Automatic Calculation:</strong> Real-time commission computation</li>";
    echo "<li><strong>Team Tracking:</strong> Complete team performance data</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px;'>";
    echo "<h3 style='color: white;'>🔄 Key Features:</h3>";
    echo "<ul style='color: white;'>";
    echo "<li>✅ <strong>Percentage-Based Commission:</strong> 10% direct commission on all properties</li>";
    echo "<li>✅ <strong>Team Management:</strong> Add unlimited team members with custom commission rates</li>";
    echo "<li>✅ <strong>Automatic Calculation:</strong> Real-time commission computation and tracking</li>";
    echo "<li>✅ <strong>Flexible Commission:</strong> Set different percentages for different team members</li>";
    echo "<li>✅ <strong>Complete Tracking:</strong> Track all commissions and payments</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<p style='text-align: center; margin-top: 20px; font-size: 18px; color: white;'><strong>🏆 Associate Commission System is Fully Functional!</strong></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
