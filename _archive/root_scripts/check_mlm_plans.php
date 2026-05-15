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
    
    echo "<h1>MLM Commission Plans & System Check</h1>";
    
    // Check mlm_commission_plans table structure
    echo "<h2>MLM Commission Plans Structure:</h2>";
    
    $stmt = $pdo->query("DESCRIBE mlm_commission_plans");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    
    // Check existing commission plans
    echo "<h2>Existing Commission Plans:</h2>";
    
    $stmt = $pdo->query("SELECT * FROM mlm_commission_plans ORDER BY id");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($plans) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Plan Name</th><th>Commission %</th><th>Status</th><th>Created</th></tr>";
        
        foreach ($plans as $plan) {
            echo "<tr>";
            echo "<td>" . $plan['id'] . "</td>";
            echo "<td>" . htmlspecialchars($plan['plan_name']) . "</td>";
            echo "<td>" . $plan['commission_percentage'] . "%</td>";
            echo "<td>" . $plan['status'] . "</td>";
            echo "<td>" . $plan['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No commission plans found.<br>";
    }
    
    // Check MLM levels table
    echo "<h2>MLM Levels Structure:</h2>";
    
    $stmt = $pdo->query("DESCRIBE mlm_levels");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    
    // Check MLM levels data
    echo "<h2>MLM Levels Data:</h2>";
    
    $stmt = $pdo->query("SELECT * FROM mlm_levels ORDER BY level_number");
    $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($levels) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Level</th><th>Name</th><th>Commission %</th><th>Min Members</th><th>Status</th></tr>";
        
        foreach ($levels as $level) {
            echo "<tr>";
            echo "<td>" . $level['level_number'] . "</td>";
            echo "<td>" . htmlspecialchars($level['level_name']) . "</td>";
            echo "<td>" . $level['commission_percentage'] . "%</td>";
            echo "<td>" . $level['min_members_required'] . "</td>";
            echo "<td>" . $level['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No MLM levels found.<br>";
    }
    
    // Create proper commission plans if missing
    echo "<h2>Setup MLM Commission Structure:</h2>";
    
    // Get existing levels
    $stmt = $pdo->query("SELECT * FROM mlm_levels ORDER BY level_number");
    $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($levels) > 0) {
        echo "Found " . count($levels) . " MLM levels.<br>";
        
        // Create commission plans based on levels
        foreach ($levels as $level) {
            $stmt = $pdo->prepare("
                INSERT INTO mlm_commission_plans (plan_name, commission_percentage, status, created_at)
                VALUES (?, ?, 'active', NOW())
                ON DUPLICATE KEY UPDATE commission_percentage = VALUES(commission_percentage)
            ");
            
            $planName = "Level " . $level['level_number'] . " - " . $level['level_name'];
            $result = $stmt->execute([$planName, $level['commission_percentage']]);
            
            if ($result) {
                echo "✅ Created/Updated commission plan: $planName (" . $level['commission_percentage'] . "%)<br>";
            }
        }
    } else {
        echo "❌ No MLM levels found. Creating default structure...<br>";
        
        // Create default MLM levels
        $defaultLevels = [
            [1, 'Direct Associate', 10, 1],
            [2, 'Level 2', 5, 5],
            [3, 'Level 3', 3, 15],
            [4, 'Level 4', 2, 50],
            [5, 'Level 5', 1, 100]
        ];
        
        foreach ($defaultLevels as $levelData) {
            $stmt = $pdo->prepare("
                INSERT INTO mlm_levels (level_number, level_name, commission_percentage, min_members_required, status, created_at)
                VALUES (?, ?, ?, ?, 'active', NOW())
            ");
            
            $result = $stmt->execute($levelData);
            
            if ($result) {
                echo "✅ Created MLM Level: " . $levelData[1] . " (" . $levelData[2] . "%)<br>";
                
                // Create corresponding commission plan
                $planName = "Level " . $levelData[0] . " - " . $levelData[1];
                $stmt = $pdo->prepare("
                    INSERT INTO mlm_commission_plans (plan_name, commission_percentage, status, created_at)
                    VALUES (?, ?, 'active', NOW())
                ");
                
                $stmt->execute([$planName, $levelData[2]]);
            }
        }
    }
    
    // Test commission calculation with a plot booking
    echo "<h2>Test Commission Calculation:</h2>";
    
    // Get an approved property
    $stmt = $pdo->query("
        SELECT up.*, u.name as user_name, u.email as user_email
        FROM user_properties up
        LEFT JOIN users u ON up.user_id = u.id
        WHERE up.status = 'approved'
        LIMIT 1
    ");
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($property) {
        echo "Found approved property: " . htmlspecialchars($property['property_type']) . " - ₹" . number_format($property['price']) . "<br>";
        
        // Get associate network for this property user
        $stmt = $pdo->prepare("
            SELECT mnt.*, u.name as associate_name, u.email as associate_email
            FROM mlm_network_tree mnt
            LEFT JOIN users u ON mnt.associate_id = u.id
            WHERE mnt.associate_id = ? OR mnt.sponsor_id = ?
            ORDER BY mnt.level
            LIMIT 5
        ");
        $stmt->execute([$property['user_id'], $property['user_id']]);
        $network = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($network) > 0) {
            echo "Found " . count($network) . " associates in network.<br>";
            
            // Calculate commissions for each level
            $propertyAmount = $property['price'];
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Associate</th><th>Level</th><th>Commission %</th><th>Commission Amount</th><th>Status</th></tr>";
            
            foreach ($network as $member) {
                // Get commission percentage for this level
                $stmt = $pdo->prepare("
                    SELECT commission_percentage FROM mlm_levels WHERE level_number = ? AND status = 'active'
                ");
                $stmt->execute([$member['level']]);
                $levelData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($levelData) {
                    $commissionPercentage = $levelData['commission_percentage'];
                    $commissionAmount = $propertyAmount * ($commissionPercentage / 100);
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($member['associate_name']) . "</td>";
                    echo "<td>" . $member['level'] . "</td>";
                    echo "<td>" . $commissionPercentage . "%</td>";
                    echo "<td>₹" . number_format($commissionAmount) . "</td>";
                    echo "<td>Pending</td>";
                    echo "</tr>";
                    
                    // Create commission record
                    $stmt = $pdo->prepare("
                        INSERT INTO mlm_commissions (associate_id, property_id, commission_amount, commission_percentage, level, status, created_at)
                        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
                        ON DUPLICATE KEY UPDATE commission_amount = VALUES(commission_amount)
                    ");
                    
                    $stmt->execute([
                        $member['associate_id'],
                        $property['id'],
                        $commissionAmount,
                        $commissionPercentage,
                        $member['level']
                    ]);
                }
            }
            echo "</table>";
        } else {
            echo "❌ No associates found in network for this property.<br>";
        }
    } else {
        echo "❌ No approved properties found for testing.<br>";
    }
    
    echo "<h3>✅ MLM Commission System Check Complete!</h3>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
