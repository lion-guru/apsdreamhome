<?php
// Database connection
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DATABASE ANALYSIS ===\n\n";
    
    // Get all tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\n=== HOMEPAGE RELEVANT DATA ===\n\n";
    
    // Properties count
    $propCount = $conn->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    echo "Properties: $propCount\n";
    
    // Sites/Projects count
    $siteCount = $conn->query("SELECT COUNT(*) FROM sites")->fetchColumn();
    echo "Sites/Projects: $siteCount\n";
    
    // Users count
    $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Users: $userCount\n";
    
    // Recent properties
    echo "\nRECENT PROPERTIES:\n";
    $recentProps = $conn->query("SELECT id, title, location, price FROM properties ORDER BY id DESC LIMIT 5")->fetchAll();
    foreach ($recentProps as $prop) {
        echo "  - {$prop['title']} ({$prop['location']}) - Rs {$prop['price']}\n";
    }
    
    // Featured properties
    echo "\nFEATURED PROPERTIES:\n";
    $featured = $conn->query("SELECT id, title, location, price, featured FROM properties WHERE featured = 1 LIMIT 5")->fetchAll();
    if (count($featured) > 0) {
        foreach ($featured as $prop) {
            echo "  ★ {$prop['title']} ({$prop['location']}) - Rs {$prop['price']}\n";
        }
    } else {
        echo "  No featured properties found\n";
    }
    
    // Sites data
    echo "\nSITES/PROJECTS:\n";
    $sites = $conn->query("SELECT id, name, location, status FROM sites ORDER BY id DESC LIMIT 10")->fetchAll();
    foreach ($sites as $site) {
        echo "  - {$site['name']} ({$site['location']}) - {$site['status']}\n";
    }
    
    // Testimonials
    echo "\nTESTIMONIALS:\n";
    $tests = $conn->query("SELECT name, message FROM testimonials WHERE status = 'active' LIMIT 3")->fetchAll();
    foreach ($tests as $test) {
        echo "  - {$test['name']}: \"" . substr($test['message'], 0, 50) . "...\"\n";
    }
    
    // Stats from database
    echo "\nDATABASE STATS:\n";
    $stats = [
        'properties' => $propCount,
        'sites' => $siteCount,
        'users' => $userCount,
        'bookings' => $conn->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
        'payments' => $conn->query("SELECT COUNT(*) FROM payments")->fetchColumn(),
    ];
    foreach ($stats as $key => $val) {
        echo "  $key: $val\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
