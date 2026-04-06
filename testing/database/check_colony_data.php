<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 Checking Current Colony Data...\n";
    
    // Check current colonies data
    $stmt = $db->query("SELECT id, name, description, price_per_sqft, total_plots, available_plots FROM colonies WHERE name LIKE '%Raghunath%' OR name LIKE '%Suryoday%' OR name LIKE '%Braj%' OR name LIKE '%Budh%' ORDER BY name");
    $colonies = $stmt->fetchAll();
    
    echo "\n📊 Current Colony Data:\n";
    foreach ($colonies as $colony) {
        echo "ID: {$colony['id']}\n";
        echo "Name: {$colony['name']}\n";
        echo "Price: ₹{$colony['price_per_sqft']}/sqft\n";
        echo "Total Plots: {$colony['total_plots']}\n";
        echo "Available: {$colony['available_plots']}\n";
        echo "Description: " . substr($colony['description'], 0, 100) . "...\n";
        echo "----------------------------------------\n";
    }
    
    // Check if we have the correct rate list data
    echo "\n🏷️ Checking Rate List Data...\n";
    $stmt = $db->query("SELECT * FROM rate_lists ORDER BY created_at DESC LIMIT 10");
    $rateLists = $stmt->fetchAll();
    
    if (count($rateLists) > 0) {
        echo "Found " . count($rateLists) . " rate lists:\n";
        foreach ($rateLists as $rate) {
            echo "- {$rate['title']}: {$rate['description']}\n";
        }
    } else {
        echo "❌ No rate lists found\n";
    }
    
    // Check plots data for Raghunath Nagri
    echo "\n🏘️ Checking Raghunath Nagri Plots...\n";
    $stmt = $db->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available FROM plots WHERE colony_id IN (SELECT id FROM colonies WHERE name LIKE '%Raghunath%')");
    $plotStats = $stmt->fetch();
    
    echo "Raghunath Nagri Plots:\n";
    echo "Total: {$plotStats['total']}\n";
    echo "Available: {$plotStats['available']}\n";
    
    // Check specific plot details
    $stmt = $db->query("SELECT plot_number, area_sqft, price_per_sqft, total_price, status FROM plots WHERE colony_id IN (SELECT id FROM colonies WHERE name LIKE '%Raghunath%') LIMIT 5");
    $plots = $stmt->fetchAll();
    
    if (count($plots) > 0) {
        echo "\nSample Plots:\n";
        foreach ($plots as $plot) {
            echo "Plot {$plot['plot_number']}: {$plot['area_sqft']} sqft @ ₹{$plot['price_per_sqft']}/sqft = ₹{$plot['total_price']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
?>
