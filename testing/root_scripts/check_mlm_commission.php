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
    
    echo "<h1>MLM Commission System Check</h1>";
    
    // Check MLM tables
    echo "<h2>MLM Tables Status:</h2>";
    
    $mlmTables = [
        'mlm_associates',
        'mlm_commissions',
        'mlm_commission_plans',
        'mlm_network_tree',
        'mlm_payouts',
        'mlm_levels',
        'plot_bookings',
        'user_properties'
    ];
    
    foreach ($mlmTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo "- $table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "<br>";
        
        if ($exists) {
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "  Records: $count<br>";
        }
    }
    
    // Check recent plot bookings
    echo "<h2>Recent Plot Bookings:</h2>";
    
    $stmt = $pdo->query("
        SELECT pb.*, u.name as customer_name, u.email as customer_email, 
               p.plot_number, p.area_sqft
        FROM plot_bookings pb
        LEFT JOIN users u ON pb.customer_id = u.id
        LEFT JOIN plots p ON pb.plot_id = p.id
        ORDER BY pb.created_at DESC
        LIMIT 10
    ");
    
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($bookings) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Booking ID</th><th>User</th><th>Property</th><th>Amount</th><th>Status</th><th>Date</th></tr>";
        
        foreach ($bookings as $booking) {
            echo "<tr>";
            echo "<td>" . $booking['id'] . "</td>";
            echo "<td>" . htmlspecialchars($booking['customer_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($booking['plot_number'] ?? 'N/A') . "</td>";
            echo "<td>₹" . number_format($booking['booking_amount'] ?? 0) . "</td>";
            echo "<td>" . $booking['status'] . "</td>";
            echo "<td>" . $booking['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No plot bookings found.<br>";
    }
    
    // Check MLM commissions
    echo "<h2>MLM Commissions:</h2>";
    
    $stmt = $pdo->query("
        SELECT mc.*, u.name as associate_name, u.email as associate_email,
               pb.booking_amount as booking_amount, pb.status as booking_status
        FROM mlm_commissions mc
        LEFT JOIN users u ON mc.associate_id = u.id
        LEFT JOIN plot_bookings pb ON mc.booking_id = pb.id
        ORDER BY mc.created_at DESC
        LIMIT 10
    ");
    
    $commissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($commissions) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Commission ID</th><th>Associate</th><th>Booking Amount</th><th>Commission</th><th>Percentage</th><th>Level</th><th>Status</th><th>Date</th></tr>";
        
        foreach ($commissions as $commission) {
            echo "<tr>";
            echo "<td>" . $commission['id'] . "</td>";
            echo "<td>" . htmlspecialchars($commission['associate_name'] ?? 'N/A') . "</td>";
            echo "<td>₹" . number_format($commission['booking_amount'] ?? 0) . "</td>";
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
    
    // Check MLM network tree
    echo "<h2>MLM Network Tree:</h2>";
    
    $stmt = $pdo->query("
        SELECT mnt.*, u.name, u.email, u.customer_id
        FROM mlm_network_tree mnt
        LEFT JOIN users u ON mnt.associate_id = u.id
        WHERE mnt.level <= 3
        ORDER BY mnt.level, mnt.position
        LIMIT 20
    ");
    
    $network = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($network) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Associate</th><th>Customer ID</th><th>Sponsor</th><th>Level</th><th>Position</th></tr>";
        
        foreach ($network as $member) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($member['name'] ?? 'N/A') . "</td>";
            echo "<td>" . $member['customer_id'] . "</td>";
            echo "<td>" . $member['sponsor_id'] . "</td>";
            echo "<td>" . $member['level'] . "</td>";
            echo "<td>" . $member['position'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No MLM network tree found.<br>";
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
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
