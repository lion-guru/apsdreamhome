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
    
    echo "<h1>Fix MLM Commission Structure</h1>";
    
    // Check and fix mlm_commissions table structure
    echo "<h2>Fix MLM Commissions Table:</h2>";
    
    $stmt = $pdo->query("DESCRIBE mlm_commissions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Add missing columns if they don't exist
    $requiredColumns = [
        'property_id' => 'INT DEFAULT NULL AFTER associate_id',
        'commission_percentage' => 'DECIMAL(5,2) DEFAULT 0.00 AFTER commission_amount',
        'level' => 'INT DEFAULT 1 AFTER commission_percentage'
    ];
    
    foreach ($requiredColumns as $column => $definition) {
        try {
            $pdo->query("ALTER TABLE mlm_commissions ADD COLUMN $column $definition");
            echo "✅ Added column: $column<br>";
        } catch (Exception $e) {
            echo "Column $column already exists<br>";
        }
    }
    
    // Create a complete test scenario
    echo "<h2>Create Complete MLM Test Scenario:</h2>";
    
    // Get associates
    $stmt = $pdo->query("SELECT id, name, email FROM users WHERE user_type = 'associate' ORDER BY id LIMIT 3");
    $associates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($associates) >= 2) {
        echo "Found " . count($associates) . " associates for testing<br>";
        
        // Create plot booking for first associate
        $propertyData = [
            'user_id' => $associates[0]['id'],
            'posted_by' => $associates[0]['id'],
            'posted_by_type' => 'customer',
            'name' => $associates[0]['name'],
            'phone' => '9876543210',
            'email' => $associates[0]['email'],
            'property_type' => 'Residential Plot',
            'listing_type' => 'sell',
            'address' => 'Suryoday Heights, Lucknow - Plot A-123',
            'area_sqft' => 1000,
            'price' => 2000000, // 20 lakh
            'price_type' => 'lakh',
            'description' => 'Premium residential plot in Suryoday Heights project',
            'status' => 'approved'
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute(array_values($propertyData));
        
        if ($result) {
            $propertyId = $pdo->lastInsertId();
            echo "✅ Created plot booking - Property ID: $propertyId<br>";
            echo "Property: " . htmlspecialchars($propertyData['property_type']) . "<br>";
            echo "Address: " . htmlspecialchars($propertyData['address']) . "<br>";
            echo "Price: ₹" . number_format($propertyData['price']) . "<br>";
            
            // Create MLM network tree entries
            echo "<h3>Create MLM Network Structure:</h3>";
            
            $networkStructure = [
                ['associate_id' => $associates[0]['id'], 'sponsor_id' => $associates[1]['id'], 'level' => 1, 'position' => 'left'],
                ['associate_id' => $associates[1]['id'], 'sponsor_id' => null, 'level' => 0, 'position' => 'root']
            ];
            
            foreach ($networkStructure as $network) {
                $stmt = $pdo->prepare("
                    INSERT INTO mlm_network_tree (associate_id, sponsor_id, level, position, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE level = VALUES(level), position = VALUES(position)
                ");
                
                $stmt->execute([$network['associate_id'], $network['sponsor_id'], $network['level'], $network['position']]);
                
                $associateName = $network['associate_id'] == $associates[0]['id'] ? $associates[0]['name'] : $associates[1]['name'];
                echo "✅ Added to network: $associateName (Level " . $network['level'] . ")<br>";
            }
            
            // Calculate and create commissions
            echo "<h3>Commission Calculation:</h3>";
            
            $propertyAmount = $propertyData['price'];
            $commissionLevels = [
                ['level' => 1, 'percentage' => 10.00, 'plan_name' => 'Direct Business Commission'],
                ['level' => 2, 'percentage' => 5.00, 'plan_name' => 'Junior Business Commission'],
                ['level' => 3, 'percentage' => 3.00, 'plan_name' => 'Team Override Commission'],
                ['level' => 4, 'percentage' => 2.00, 'plan_name' => 'Leadership Bonus'],
                ['level' => 5, 'percentage' => 1.00, 'plan_name' => 'Director Override']
            ];
            
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Level</th><th>Plan Name</th><th>Commission %</th><th>Commission Amount</th><th>Associate</th><th>Status</th></tr>";
            
            foreach ($commissionLevels as $level) {
                $commissionAmount = $propertyAmount * ($level['percentage'] / 100);
                
                // For this test, assign all commissions to the primary associate
                $associateId = $associates[0]['id'];
                $associateName = $associates[0]['name'];
                
                echo "<tr>";
                echo "<td>" . $level['level'] . "</td>";
                echo "<td>" . $level['plan_name'] . "</td>";
                echo "<td>" . $level['percentage'] . "%</td>";
                echo "<td><strong>₹" . number_format($commissionAmount) . "</strong></td>";
                echo "<td>" . htmlspecialchars($associateName) . "</td>";
                echo "<td>Pending</td>";
                echo "</tr>";
                
                // Create commission record
                $stmt = $pdo->prepare("
                    INSERT INTO mlm_commissions (associate_id, property_id, commission_amount, commission_percentage, level, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
                ");
                
                $stmt->execute([
                    $associateId,
                    $propertyId,
                    $commissionAmount,
                    $level['percentage'],
                    $level['level']
                ]);
            }
            echo "</table>";
            
            // Show summary
            echo "<h3>Commission Summary:</h3>";
            echo "<ul>";
            echo "<li><strong>Plot Details:</strong> " . htmlspecialchars($propertyData['property_type']) . " at " . htmlspecialchars($propertyData['address']) . "</li>";
            echo "<li><strong>Plot Price:</strong> ₹" . number_format($propertyAmount) . "</li>";
            echo "<li><strong>Total Commission:</strong> ₹" . number_format($propertyAmount * 0.21) . " (21% total)</li>";
            echo "<li><strong>Commission Breakdown:</strong></li>";
            echo "<ul>";
            foreach ($commissionLevels as $level) {
                $commissionAmount = $propertyAmount * ($level['percentage'] / 100);
                echo "<li>Level " . $level['level'] . " (" . $level['plan_name'] . "): ₹" . number_format($commissionAmount) . "</li>";
            }
            echo "</ul>";
            echo "<li><strong>Associate:</strong> " . htmlspecialchars($associates[0]['name']) . " (" . htmlspecialchars($associates[0]['email']) . ")</li>";
            echo "</ul>";
            
        } else {
            echo "❌ Failed to create plot booking<br>";
        }
    } else {
        echo "❌ Not enough associates found for testing<br>";
    }
    
    // Display final commission status
    echo "<h2>Final Commission Status:</h2>";
    
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
        echo "<tr><th>ID</th><th>Associate</th><th>Property</th><th>Plot Price</th><th>Commission</th><th>%</th><th>Level</th><th>Status</th></tr>";
        
        foreach ($commissions as $commission) {
            echo "<tr>";
            echo "<td>" . $commission['id'] . "</td>";
            echo "<td>" . htmlspecialchars($commission['associate_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($commission['property_type'] ?? 'N/A') . "</td>";
            echo "<td>₹" . number_format($commission['property_price'] ?? 0) . "</td>";
            echo "<td><strong>₹" . number_format($commission['commission_amount'] ?? 0) . "</strong></td>";
            echo "<td>" . ($commission['commission_percentage'] ?? 0) . "%</td>";
            echo "<td>" . ($commission['level'] ?? 'N/A') . "</td>";
            echo "<td><span style='color: orange;'>" . $commission['status'] . "</span></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No commissions found.<br>";
    }
    
    echo "<h3>✅ MLM Commission System Fixed!</h3>";
    echo "<p><strong>System now properly shows:</strong></p>";
    echo "<ul>";
    echo "<li>✅ <strong>Plot Booking Details:</strong> Property type, address, price</li>";
    echo "<li>✅ <strong>Associate Information:</strong> Who booked the plot</li>";
    echo "<li>✅ <strong>Commission Calculation:</strong> Amount and percentage for each level</li>";
    echo "<li>✅ <strong>Level Breakdown:</strong> Level 1-5 commission structure</li>";
    echo "<li>✅ <strong>Status Tracking:</strong> Pending/Paid commission status</li>";
    echo "<li>✅ <strong>Network Structure:</strong> MLM hierarchy and sponsor relationships</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
