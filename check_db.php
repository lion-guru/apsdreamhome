<?php
try {
    require_once 'config.php';
    require_once 'db_connection.php';
    
    // Check if database connection is working
    if (isset($pdo) && $pdo) {
        echo 'Database connection: OK' . PHP_EOL;
        
        // Check total properties count
        $stmt = $pdo->query('SELECT COUNT(*) as total FROM properties');
        $total_props = $stmt->fetch(PDO::FETCH_ASSOC);
        echo 'Total properties: ' . $total_props['total'] . PHP_EOL;
        
        // Check featured properties count
        $stmt = $pdo->query('SELECT COUNT(*) as featured FROM properties WHERE featured = 1 AND status = "available"');
        $featured_props = $stmt->fetch(PDO::FETCH_ASSOC);
        echo 'Featured properties: ' . $featured_props['featured'] . PHP_EOL;
        
        // Check if any properties exist at all
        $stmt = $pdo->query('SELECT COUNT(*) as any FROM properties WHERE status = "available"');
        $any_props = $stmt->fetch(PDO::FETCH_ASSOC);
        echo 'Available properties: ' . $any_props['any'] . PHP_EOL;
        
    } else {
        echo 'Database connection: FAILED' . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>