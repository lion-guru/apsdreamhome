<?php
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== SITES TABLE STRUCTURE ===\n";
    $cols = $conn->query("DESCRIBE sites")->fetchAll();
    foreach($cols as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n=== SITES DATA (First 10) ===\n";
    $sites = $conn->query("SELECT id, site_name, location, status FROM sites ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    foreach($sites as $site) {
        echo "  [{$site['id']}] {$site['site_name']} - {$site['location']} ({$site['status']})\n";
    }
    
    echo "\n=== LOCATIONS GROUPED ===\n";
    $locs = $conn->query("SELECT location, COUNT(*) as cnt FROM sites GROUP BY location ORDER BY cnt DESC")->fetchAll();
    foreach($locs as $loc) {
        echo "  {$loc['location']}: {$loc['cnt']} projects\n";
    }
    
    echo "\n=== PROPERTIES TABLE STRUCTURE ===\n";
    $propCols = $conn->query("DESCRIBE properties")->fetchAll();
    foreach($propCols as $col) {
        if(in_array($col['Field'], ['id', 'title', 'location', 'price', 'status', 'featured', 'property_type'])) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    }
    
    echo "\n=== FEATURED PROPERTIES ===\n";
    $featured = $conn->query("SELECT id, title, location, price FROM properties WHERE status = 'active' ORDER BY id DESC LIMIT 6")->fetchAll();
    foreach($featured as $prop) {
        echo "  ★ {$prop['title']} - {$prop['location']} - Rs " . number_format($prop['price']) . "\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
