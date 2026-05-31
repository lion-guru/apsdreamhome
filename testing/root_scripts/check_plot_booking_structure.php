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
    
    echo "<h1>Plot Booking & MLM Commission Structure</h1>";
    
    // Check plot_bookings table structure
    echo "<h2>Plot Bookings Table Structure:</h2>";
    
    $stmt = $pdo->query("DESCRIBE plot_bookings");
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
    
    // Check user_properties table structure
    echo "<h2>User Properties Table Structure:</h2>";
    
    $stmt = $pdo->query("DESCRIBE user_properties");
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
    
    // Check recent user properties (plot bookings)
    echo "<h2>Recent User Properties (Plot Bookings):</h2>";
    
    $stmt = $pdo->query("
        SELECT up.*, u.name as user_name, u.email as user_email
        FROM user_properties up
        LEFT JOIN users u ON up.user_id = u.id
        ORDER BY up.created_at DESC
        LIMIT 10
    ");
    
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($properties) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>User</th><th>Property Type</th><th>Address</th><th>Price</th><th>Status</th><th>Posted By</th><th>Date</th></tr>";
        
        foreach ($properties as $property) {
            echo "<tr>";
            echo "<td>" . $property['id'] . "</td>";
            echo "<td>" . htmlspecialchars($property['user_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($property['property_type'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($property['address'] ?? 'N/A') . "</td>";
            echo "<td>₹" . number_format($property['price'] ?? 0) . "</td>";
            echo "<td>" . $property['status'] . "</td>";
            echo "<td>" . ($property['posted_by'] ?? 'N/A') . "</td>";
            echo "<td>" . $property['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No user properties found.<br>";
    }
    
    // Check MLM commissions with proper joins
    echo "<h2>MLM Commissions:</h2>";
    
    $stmt = $pdo->query("
        SELECT mc.*, u.name as associate_name, u.email as associate_email
        FROM mlm_commissions mc
        LEFT JOIN users u ON mc.associate_id = u.id
        ORDER BY mc.created_at DESC
        LIMIT 10
    ");
    
    $commissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($commissions) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Commission ID</th><th>Associate</th><th>Commission Amount</th><th>Percentage</th><th>Level</th><th>Status</th><th>Date</th></tr>";
        
        foreach ($commissions as $commission) {
            echo "<tr>";
            echo "<td>" . $commission['id'] . "</td>";
            echo "<td>" . htmlspecialchars($commission['associate_name'] ?? 'N/A') . "</td>";
            echo "<td>₹" . number_format($commission['commission_amount'] ?? 0) . "</td>";
            echo "<td>" . ($commission['commission_percentage'] ?? 0) . "%</td>";
            echo "<td>" . ($commission['level'] ?? 'N/A') . "</td>";
            echo "<td>" . $commission['status'] . "</td>";
            echo "<td>" . $commission['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No MLM commissions found.<br>";
    }
    
    // Check commission plans
    echo "<h2>Commission Plans:</h2>";
    
    $stmt = $pdo->query("SELECT * FROM mlm_commission_plans ORDER BY level");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($plans) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Plan Name</th><th>Level</th><th>Percentage</th><th>Status</th></tr>";
        
        foreach ($plans as $plan) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($plan['plan_name']) . "</td>";
            echo "<td>" . $plan['level'] . "</td>";
            echo "<td>" . $plan['commission_percentage'] . "%</td>";
            echo "<td>" . $plan['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No commission plans found.<br>";
    }
    
    // Create a test plot booking to test commission calculation
    echo "<h2>Create Test Plot Booking:</h2>";
    
    // Get a test user (associate)
    $stmt = $pdo->query("SELECT id, name, email FROM users WHERE user_type = 'associate' LIMIT 1");
    $associate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($associate) {
        echo "Found associate: " . htmlspecialchars($associate['name']) . " (ID: " . $associate['id'] . ")<br>";
        
        // Create test plot booking
        $stmt = $pdo->prepare("
            INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, status, created_at)
            VALUES (?, ?, 'customer', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved', NOW())
        ");
        
        $result = $stmt->execute([
            $associate['id'],
            $associate['id'],
            $associate['name'],
            $associate['phone'] ?? '9876543210',
            $associate['email'],
            'Residential Plot',
            'sell',
            'Test Plot Address, Lucknow',
            1000,
            500000, // 5 lakh
            'lakh',
            'Test plot for commission calculation'
        ]);
        
        if ($result) {
            $propertyId = $pdo->lastInsertId();
            echo "✅ Test plot booking created successfully! Property ID: $propertyId<br>";
            
            // Calculate and create commission
            $commissionAmount = 500000 * 0.10; // 10% commission
            $stmt = $pdo->prepare("
                INSERT INTO mlm_commissions (associate_id, property_id, commission_amount, commission_percentage, level, status, created_at)
                VALUES (?, ?, ?, ?, 1, 'pending', NOW())
            ");
            
            $result = $stmt->execute([
                $associate['id'],
                $propertyId,
                $commissionAmount,
                10
            ]);
            
            if ($result) {
                $commissionId = $pdo->lastInsertId();
                echo "✅ MLM commission created successfully! Commission ID: $commissionId<br>";
                echo "Commission Amount: ₹" . number_format($commissionAmount) . "<br>";
            } else {
                echo "❌ Failed to create MLM commission<br>";
            }
        } else {
            echo "❌ Failed to create test plot booking<br>";
        }
    } else {
        echo "❌ No associate found for testing<br>";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
