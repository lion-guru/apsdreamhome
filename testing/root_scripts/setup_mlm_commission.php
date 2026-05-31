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
    
    echo "<h1>Setup MLM Commission System</h1>";
    
    // Add commission_percentage column to mlm_commission_plans if not exists
    echo "<h2>Update Commission Plans Table:</h2>";
    
    try {
        $pdo->query("ALTER TABLE mlm_commission_plans ADD COLUMN commission_percentage DECIMAL(5,2) DEFAULT 0.00 AFTER plan_code");
        echo "✅ Added commission_percentage column to mlm_commission_plans<br>";
    } catch (Exception $e) {
        echo "Column commission_percentage already exists<br>";
    }
    
    // Update existing commission plans with proper percentages
    $commissionPlans = [
        ['Direct Business Commission', 10.00],
        ['Junior Business Commission', 5.00],
        ['Team Override Commission', 3.00],
        ['Leadership Bonus', 2.00],
        ['Director Override', 1.00]
    ];
    
    foreach ($commissionPlans as $plan) {
        $stmt = $pdo->prepare("
            UPDATE mlm_commission_plans 
            SET commission_percentage = ? 
            WHERE plan_name = ?
        ");
        $stmt->execute([$plan[1], $plan[0]]);
        echo "✅ Updated: " . $plan[0] . " - " . $plan[1] . "%<br>";
    }
    
    // Check and update mlm_levels table structure
    echo "<h2>Update MLM Levels Table:</h2>";
    
    try {
        $pdo->query("ALTER TABLE mlm_levels ADD COLUMN level_number INT DEFAULT 1 AFTER id");
        echo "✅ Added level_number column to mlm_levels<br>";
    } catch (Exception $e) {
        echo "Column level_number already exists<br>";
    }
    
    // Update level numbers based on level_order
    $stmt = $pdo->query("SELECT id, level_name, level_order FROM mlm_levels ORDER BY level_order");
    $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($levels as $level) {
        $stmt = $pdo->prepare("
            UPDATE mlm_levels 
            SET level_number = ? 
            WHERE id = ?
        ");
        $stmt->execute([$level['level_order'], $level['id']]);
        echo "✅ Updated level: " . $level['level_name'] . " -> Level " . $level['level_order'] . "<br>";
    }
    
    // Create a test plot booking with commission calculation
    echo "<h2>Create Test Plot Booking & Commission:</h2>";
    
    // Get an associate user
    $stmt = $pdo->query("SELECT id, name, email FROM users WHERE user_type = 'associate' LIMIT 1");
    $associate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($associate) {
        echo "Found associate: " . htmlspecialchars($associate['name']) . "<br>";
        
        // Create test plot booking
        $stmt = $pdo->prepare("
            INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, status, created_at)
            VALUES (?, ?, 'customer', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved', NOW())
        ");
        
        $result = $stmt->execute([
            $associate['id'],
            $associate['id'],
            $associate['name'],
            '9876543210',
            $associate['email'],
            'Residential Plot',
            'sell',
            'Test Plot - Suryoday Heights, Lucknow',
            1000,
            1000000, // 10 lakh
            'lakh',
            'Test plot for MLM commission calculation'
        ]);
        
        if ($result) {
            $propertyId = $pdo->lastInsertId();
            echo "✅ Created test plot booking - Property ID: $propertyId<br>";
            
            // Calculate commissions for upline
            $propertyAmount = 1000000;
            echo "<h3>Commission Calculation Breakdown:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Level</th><th>Plan Name</th><th>Commission %</th><th>Commission Amount</th><th>Associate</th></tr>";
            
            $commissionLevels = [
                [1, 'Direct Business Commission', 10.00, $associate['name']],
                [2, 'Junior Business Commission', 5.00, 'Level 2 Associate'],
                [3, 'Team Override Commission', 3.00, 'Level 3 Associate'],
                [4, 'Leadership Bonus', 2.00, 'Level 4 Associate'],
                [5, 'Director Override', 1.00, 'Level 5 Associate']
            ];
            
            foreach ($commissionLevels as $level) {
                $commissionAmount = $propertyAmount * ($level[2] / 100);
                
                echo "<tr>";
                echo "<td>" . $level[0] . "</td>";
                echo "<td>" . $level[1] . "</td>";
                echo "<td>" . $level[2] . "%</td>";
                echo "<td>₹" . number_format($commissionAmount) . "</td>";
                echo "<td>" . htmlspecialchars($level[3]) . "</td>";
                echo "</tr>";
                
                // Create commission record for the primary associate (Level 1)
                if ($level[0] == 1) {
                    $stmt = $pdo->prepare("
                        INSERT INTO mlm_commissions (associate_id, property_id, commission_amount, commission_percentage, level, status, created_at)
                        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
                    ");
                    
                    $stmt->execute([
                        $associate['id'],
                        $propertyId,
                        $commissionAmount,
                        $level[2],
                        $level[0]
                    ]);
                    
                    echo "✅ Created commission record for Level 1<br>";
                }
            }
            echo "</table>";
            
            echo "<h3>Total Commission Distribution:</h3>";
            echo "<ul>";
            echo "<li>Direct Associate (Level 1): ₹100,000 (10%)</li>";
            echo "<li>Level 2 Associate: ₹50,000 (5%)</li>";
            echo "<li>Level 3 Associate: ₹30,000 (3%)</li>";
            echo "<li>Level 4 Associate: ₹20,000 (2%)</li>";
            echo "<li>Level 5 Associate: ₹10,000 (1%)</li>";
            echo "<li><strong>Total Commission: ₹210,000 (21%)</strong></li>";
            echo "</ul>";
            
        } else {
            echo "❌ Failed to create test plot booking<br>";
        }
    } else {
        echo "❌ No associate found for testing<br>";
    }
    
    // Display current commission status
    echo "<h2>Current Commission Status:</h2>";
    
    $stmt = $pdo->query("
        SELECT mc.*, u.name as associate_name, u.email as associate_email,
               up.property_type, up.address, up.price as property_price
        FROM mlm_commissions mc
        LEFT JOIN users u ON mc.associate_id = u.id
        LEFT JOIN user_properties up ON mc.property_id = up.id
        ORDER BY mc.created_at DESC
        LIMIT 10
    ");
    
    $commissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($commissions) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Commission ID</th><th>Associate</th><th>Property</th><th>Property Price</th><th>Commission</th><th>%</th><th>Level</th><th>Status</th></tr>";
        
        foreach ($commissions as $commission) {
            echo "<tr>";
            echo "<td>" . $commission['id'] . "</td>";
            echo "<td>" . htmlspecialchars($commission['associate_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($commission['property_type'] ?? 'N/A') . "</td>";
            echo "<td>₹" . number_format($commission['property_price'] ?? 0) . "</td>";
            echo "<td><strong>₹" . number_format($commission['commission_amount'] ?? 0) . "</strong></td>";
            echo "<td>" . ($commission['commission_percentage'] ?? 0) . "%</td>";
            echo "<td>" . ($commission['level'] ?? 'N/A') . "</td>";
            echo "<td>" . $commission['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No commissions found.<br>";
    }
    
    echo "<h3>✅ MLM Commission System Setup Complete!</h3>";
    echo "<p>The system now shows:</p>";
    echo "<ul>";
    echo "<li>✅ Plot booking details (property type, address, price)</li>";
    echo "<li>✅ Commission breakdown by level (1-5)</li>";
    echo "<li>✅ Commission percentage for each level</li>";
    echo "<li>✅ Commission amount calculation</li>";
    echo "<li>✅ Associate details who earned commission</li>";
    echo "<li>✅ Status of commission (pending/paid)</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
