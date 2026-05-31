<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 Checking Current Colony Data...\n";
    
    // Check current colonies data
    $stmt = $db->query("SELECT id, name, description, starting_price, total_plots, available_plots FROM colonies WHERE name LIKE '%Raghunath%' OR name LIKE '%Suryoday%' OR name LIKE '%Braj%' OR name LIKE '%Budh%' ORDER BY name");
    $colonies = $stmt->fetchAll();
    
    echo "\n📊 Current Colony Data:\n";
    foreach ($colonies as $colony) {
        echo "ID: {$colony['id']}\n";
        echo "Name: {$colony['name']}\n";
        echo "Starting Price: ₹{$colony['starting_price']}\n";
        echo "Total Plots: {$colony['total_plots']}\n";
        echo "Available: {$colony['available_plots']}\n";
        echo "Description: " . substr($colony['description'], 0, 100) . "...\n";
        echo "----------------------------------------\n";
    }
    
    // Check plots table structure
    echo "\n🏘️ Checking plots table structure...\n";
    $stmt = $db->query("DESCRIBE plots");
    $plotColumns = $stmt->fetchAll();
    
    foreach ($plotColumns as $col) {
        echo $col['Field'] . ' - ' . $col['Type'] . "\n";
    }
    
    // Check if plots data exists
    $stmt = $db->query("SELECT COUNT(*) as count FROM plots");
    $plotCount = $stmt->fetch()['count'];
    echo "\nTotal Plots in Database: $plotCount\n";
    
    if ($plotCount > 0) {
        $stmt = $db->query("SELECT p.plot_number, p.area_sqft, p.price_per_sqft, p.total_price, p.status, c.name as colony_name FROM plots p JOIN colonies c ON p.colony_id = c.id WHERE c.name LIKE '%Raghunath%' OR c.name LIKE '%Suryoday%' LIMIT 5");
        $plots = $stmt->fetchAll();
        
        echo "\nSample Plots:\n";
        foreach ($plots as $plot) {
            echo "Colony: {$plot['colony_name']}\n";
            echo "Plot {$plot['plot_number']}: {$plot['area_sqft']} sqft @ ₹{$plot['price_per_sqft']}/sqft = ₹{$plot['total_price']} ({$plot['status']})\n";
            echo "---\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
?>
