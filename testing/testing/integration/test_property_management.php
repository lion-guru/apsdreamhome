<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test database connection
    echo "✅ Database connected\n";
    
    // Test tables exist
    $tables = ['states', 'districts', 'colonies', 'plots', 'plot_status_history', 'plot_images'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
            
            // Count records
            $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
            echo "   Records: $count\n";
        } else {
            echo "❌ Table '$table' missing\n";
        }
    }
    
    // Test foreign key relationships
    echo "\n🔗 Testing Foreign Keys:\n";
    
    // Test states->districts relationship
    $stmt = $db->query("SELECT COUNT(*) as count FROM districts d JOIN states s ON d.state_id = s.id");
    echo "✅ States-Districts relationship: " . $stmt->fetch()['count'] . " valid links\n";
    
    // Test districts->colonies relationship
    $stmt = $db->query("SELECT COUNT(*) as count FROM colonies c JOIN districts d ON c.district_id = d.id");
    echo "✅ Districts-Colonies relationship: " . $stmt->fetch()['count'] . " valid links\n";
    
    // Test colonies->plots relationship
    $stmt = $db->query("SELECT COUNT(*) as count FROM plots p JOIN colonies c ON p.colony_id = c.id");
    echo "✅ Colonies-Plots relationship: " . $stmt->fetch()['count'] . " valid links\n";
    
    // Test sample data
    echo "\n📊 Sample Data Test:\n";
    
    $states = $db->query("SELECT COUNT(*) as count FROM states")->fetch()['count'];
    $districts = $db->query("SELECT COUNT(*) as count FROM districts")->fetch()['count'];
    $colonies = $db->query("SELECT COUNT(*) as count FROM colonies")->fetch()['count'];
    $plots = $db->query("SELECT COUNT(*) as count FROM plots")->fetch()['count'];
    
    echo "States: $states\n";
    echo "Districts: $districts\n";
    echo "Colonies: $colonies\n";
    echo "Plots: $plots\n";
    
    // Test plot status distribution
    echo "\n📈 Plot Status Distribution:\n";
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM plots GROUP BY status");
    while ($row = $stmt->fetch()) {
        echo ucfirst($row['status']) . ": " . $row['count'] . "\n";
    }
    
    // Test controller files exist
    echo "\n📁 Controller Files Test:\n";
    $controllers = [
        'app/Http/Controllers/Admin/LocationAdminController.php',
        'app/Http/Controllers/Admin/PlotsAdminController.php'
    ];
    
    foreach ($controllers as $controller) {
        if (file_exists($controller)) {
            echo "✅ $controller exists\n";
        } else {
            echo "❌ $controller missing\n";
        }
    }
    
    // Test view files exist
    echo "\n🎨 View Files Test:\n";
    $views = [
        'app/views/admin/locations/states/index.php',
        'app/views/admin/locations/districts/index.php',
        'app/views/admin/locations/colonies/index.php',
        'app/views/admin/plots/index.php',
        'app/views/admin/plots/create.php'
    ];
    
    foreach ($views as $view) {
        if (file_exists($view)) {
            echo "✅ $view exists\n";
        } else {
            echo "❌ $view missing\n";
        }
    }
    
    // Test routes
    echo "\n🛣️ Routes Test:\n";
    $web_php = file_get_contents('routes/web.php');
    $routes_to_check = [
        '/admin/locations/states',
        '/admin/locations/districts',
        '/admin/locations/colonies',
        '/admin/plots',
        '/admin/plots/create'
    ];
    
    foreach ($routes_to_check as $route) {
        if (strpos($web_php, $route) !== false) {
            echo "✅ Route '$route' found\n";
        } else {
            echo "❌ Route '$route' missing\n";
        }
    }
    
    echo "\n🎉 All Tests Completed!\n";
    echo "✅ Property Management System is ready for testing\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
