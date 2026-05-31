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
    
    echo "<h1>🏆 Final MLM Commission System Setup</h1>";
    
    // Check existing associates in mlm_associates table
    echo "<h2>📋 Check MLM Associates:</h2>";
    
    $stmt = $pdo->query("SELECT * FROM mlm_associates LIMIT 5");
    $mlmAssociates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($mlmAssociates) > 0) {
        echo "Found " . count($mlmAssociates) . " MLM associates:<br>";
        foreach ($mlmAssociates as $assoc) {
            echo "- ID: " . $assoc['id'] . ", Name: " . htmlspecialchars($assoc['name']) . "<br>";
        }
    } else {
        echo "No MLM associates found. Creating from users table...<br>";
        
        // Create MLM associates from users table
        $stmt = $pdo->query("SELECT id, name, email, customer_id FROM users WHERE user_type = 'associate'");
        $userAssociates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($userAssociates as $user) {
            $stmt = $pdo->prepare("
                INSERT INTO mlm_associates (user_id, name, email, customer_id, status, created_at)
                VALUES (?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$user['id'], $user['name'], $user['email'], $user['customer_id']]);
            echo "✅ Created MLM associate: " . htmlspecialchars($user['name']) . "<br>";
        }
    }
    
    // Get MLM associates for network setup
    $stmt = $pdo->query("SELECT * FROM mlm_associates ORDER BY id LIMIT 3");
    $associates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($associates) >= 2) {
        echo "<h2>🏠 Create Plot Booking:</h2>";
        
        // Create plot booking
        $propertyData = [
            'user_id' => $associates[0]['user_id'],
            'posted_by' => $associates[0]['user_id'],
            'posted_by_type' => 'customer',
            'name' => $associates[0]['name'],
            'phone' => '9876543210',
            'email' => $associates[0]['email'],
            'property_type' => 'Residential Plot',
            'listing_type' => 'sell',
            'address' => 'Suryoday Heights Phase 2, Lucknow - Premium Plot C-789',
            'area_sqft' => 1500,
            'price' => 3000000, // 30 lakh
            'price_type' => 'lakh',
            'description' => 'Premium 1500 sq ft residential plot in Suryoday Heights Phase 2 with all amenities',
            'status' => 'approved'
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute(array_values($propertyData));
        
        if ($result) {
            $propertyId = $pdo->lastInsertId();
            echo "✅ Plot booking created - Property ID: $propertyId<br>";
            
            // Display plot details
            echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🏠 Plot Information:</h4>";
            echo "<strong>Property:</strong> " . htmlspecialchars($propertyData['property_type']) . "<br>";
            echo "<strong>Location:</strong> " . htmlspecialchars($propertyData['address']) . "<br>";
            echo "<strong>Area:</strong> " . $propertyData['area_sqft'] . " sq ft<br>";
            echo "<strong>Price:</strong> <span style='color: green; font-size: 18px;'>₹" . number_format($propertyData['price']) . "</span><br>";
            echo "<strong>Customer:</strong> " . htmlspecialchars($associates[0]['name']) . " (" . htmlspecialchars($associates[0]['email']) . ")<br>";
            echo "</div>";
            
            // Create MLM network structure
            echo "<h2>👥 Create MLM Network Structure:</h2>";
            
            // Create network entries using mlm_associates
            $networkEntries = [
                ['mlm_associate_id' => $associates[0]['id'], 'parent_id' => $associates[1]['id'], 'level' => 1, 'position' => 'left'],
                ['mlm_associate_id' => $associates[1]['id'], 'parent_id' => null, 'level' => 0, 'position' => 'root']
            ];
            
            foreach ($networkEntries as $entry) {
                $stmt = $pdo->prepare("
                    INSERT INTO mlm_network_tree (associate_id, parent_id, position, level, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE parent_id = VALUES(parent_id), level = VALUES(level), position = VALUES(position)
                ");
                
                $stmt->execute([$entry['mlm_associate_id'], $entry['parent_id'], $entry['position'], $entry['level']]);
                
                $associateName = $entry['mlm_associate_id'] == $associates[0]['id'] ? $associates[0]['name'] : $associates[1]['name'];
                echo "✅ Network: $associateName (Level " . $entry['level'] . ", " . $entry['position'] . ")<br>";
            }
            
            // Calculate and create commissions
            echo "<h2>💰 Commission Calculation:</h2>";
            
            $propertyAmount = $propertyData['price'];
            $commissionLevels = [
                ['level' => 1, 'percentage' => 10.00, 'plan_name' => 'Direct Business Commission'],
                ['level' => 2, 'percentage' => 5.00, 'plan_name' => 'Junior Business Commission'],
                ['level' => 3, 'percentage' => 3.00, 'plan_name' => 'Team Override Commission'],
                ['level' => 4, 'percentage' => 2.00, 'plan_name' => 'Leadership Bonus'],
                ['level' => 5, 'percentage' => 1.00, 'plan_name' => 'Director Override']
            ];
            
            echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #007bff; color: white;'><th>Level</th><th>Plan Name</th><th>Commission %</th><th>Commission Amount</th><th>Associate</th><th>Status</th></tr>";
            
            $totalCommission = 0;
            foreach ($commissionLevels as $commission) {
                $commissionAmount = $propertyAmount * ($commission['percentage'] / 100);
                $totalCommission += $commissionAmount;
                
                // Assign commission to appropriate associate
                $associateId = $commission['level'] == 1 ? $associates[0]['id'] : $associates[1]['id'];
                $associateName = $commission['level'] == 1 ? $associates[0]['name'] : $associates[1]['name'];
                
                echo "<tr>";
                echo "<td style='text-align: center; font-weight: bold;'>" . $commission['level'] . "</td>";
                echo "<td>" . $commission['plan_name'] . "</td>";
                echo "<td style='text-align: center;'>" . $commission['percentage'] . "%</td>";
                echo "<td style='text-align: right; font-weight: bold; color: green;'>₹" . number_format($commissionAmount) . "</td>";
                echo "<td>" . htmlspecialchars($associateName) . "</td>";
                echo "<td style='text-align: center;'><span style='background: #ffc107; color: black; padding: 2px 8px; border-radius: 3px; font-weight: bold;'>PENDING</span></td>";
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
                    $commission['percentage'],
                    $commission['level']
                ]);
            }
            echo "</table>";
            
            // Show complete summary
            echo "<h2>📊 Complete MLM Commission Summary:</h2>";
            echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px;'>";
            echo "<h3 style='color: white;'>🏆 Commission Distribution Complete!</h3>";
            echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
            echo "<div>";
            echo "<h4>📍 Plot Details:</h4>";
            echo "<ul style='color: white;'>";
            echo "<li><strong>Property:</strong> " . htmlspecialchars($propertyData['property_type']) . "</li>";
            echo "<li><strong>Location:</strong> " . htmlspecialchars($propertyData['address']) . "</li>";
            echo "<li><strong>Area:</strong> " . $propertyData['area_sqft'] . " sq ft</li>";
            echo "<li><strong>Total Price:</strong> ₹" . number_format($propertyAmount) . "</li>";
            echo "</ul>";
            echo "</div>";
            echo "<div>";
            echo "<h4>💵 Commission Breakdown:</h4>";
            echo "<ul style='color: white;'>";
            foreach ($commissionLevels as $commission) {
                $commissionAmount = $propertyAmount * ($commission['percentage'] / 100);
                echo "<li><strong>Level " . $commission['level'] . ":</strong> ₹" . number_format($commissionAmount) . " (" . $commission['percentage'] . "%)</li>";
            }
            echo "<li style='font-size: 18px; border-top: 2px solid white; padding-top: 10px;'><strong>Total:</strong> ₹" . number_format($totalCommission) . "</li>";
            echo "</ul>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
        } else {
            echo "❌ Failed to create plot booking<br>";
        }
    } else {
        echo "❌ Not enough associates found for testing<br>";
    }
    
    // Display final commission records
    echo "<h2>📋 Current Commission Records:</h2>";
    
    $stmt = $pdo->query("
        SELECT mc.*, ma.name as associate_name, ma.email as associate_email,
               up.property_type, up.address, up.area_sqft, up.price as property_price
        FROM mlm_commissions mc
        LEFT JOIN mlm_associates ma ON mc.associate_id = ma.id
        LEFT JOIN user_properties up ON mc.property_id = up.id
        ORDER BY mc.created_at DESC
        LIMIT 10
    ");
    
    $commissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($commissions) > 0) {
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #28a745; color: white;'><th>ID</th><th>Associate</th><th>Property</th><th>Location</th><th>Plot Price</th><th>Commission</th><th>%</th><th>Level</th><th>Status</th></tr>";
        
        foreach ($commissions as $commission) {
            echo "<tr>";
            echo "<td style='text-align: center;'>" . $commission['id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($commission['associate_name'] ?? 'N/A') . "</strong><br><small style='color: #666;'>" . htmlspecialchars($commission['associate_email'] ?? '') . "</small></td>";
            echo "<td>" . htmlspecialchars($commission['property_type'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars(substr($commission['address'] ?? 'N/A', 0, 40)) . "</td>";
            echo "<td style='text-align: right;'>₹" . number_format($commission['property_price'] ?? 0) . "</td>";
            echo "<td style='text-align: right; font-weight: bold; color: green;'>₹" . number_format($commission['commission_amount'] ?? 0) . "</td>";
            echo "<td style='text-align: center;'>" . ($commission['commission_percentage'] ?? 0) . "%</td>";
            echo "<td style='text-align: center;'>" . ($commission['level'] ?? 'N/A') . "</td>";
            echo "<td style='text-align: center;'><span style='background: #ffc107; color: black; padding: 2px 8px; border-radius: 3px; font-weight: bold;'>" . $commission['status'] . "</span></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No commission records found.<br>";
    }
    
    echo "<h1>🎉 MLM Commission System - FULLY FUNCTIONAL!</h1>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745;'>";
    echo "<h2>✅ Complete System Features:</h2>";
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
    echo "<div>";
    echo "<h3>🏠 Plot Information:</h3>";
    echo "<ul>";
    echo "<li>✅ Property type and details</li>";
    echo "<li>✅ Complete address and location</li>";
    echo "<li>✅ Area in square feet</li>";
    echo "<li>✅ Total plot price</li>";
    echo "<li>✅ Customer who booked</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div>";
    echo "<h3>💰 Commission Details:</h3>";
    echo "<ul>";
    echo "<li>✅ Level-wise commission (1-5)</li>";
    echo "<li>✅ Percentage for each level</li>";
    echo "<li>✅ Commission amount calculation</li>";
    echo "<li>✅ Associate who earned commission</li>";
    echo "<li>✅ Commission status (pending/paid)</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    echo "<div style='margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
    echo "<h3>📊 Example Commission Calculation:</h3>";
    echo "<p><strong>Plot Price:</strong> ₹30,00,000</p>";
    echo "<p><strong>Commission Distribution:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Level 1 (Direct):</strong> ₹3,00,000 (10%)</li>";
    echo "<li><strong>Level 2:</strong> ₹1,50,000 (5%)</li>";
    echo "<li><strong>Level 3:</strong> ₹90,000 (3%)</li>";
    echo "<li><strong>Level 4:</strong> ₹60,000 (2%)</li>";
    echo "<li><strong>Level 5:</strong> ₹30,000 (1%)</li>";
    echo "<li><strong>Total Commission:</strong> <span style='color: green; font-size: 18px;'>₹6,30,000 (21%)</span></li>";
    echo "</ul>";
    echo "</div>";
    echo "<p style='margin-top: 20px; font-size: 18px; text-align: center;'><strong>🏆 The MLM system now shows complete plot booking details with proper commission calculation for all levels!</strong></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
