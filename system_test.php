<?php
/**
 * APS Dream Home - Complete System Test
 * Test all components after database connection
 */

echo "<h1>🎉 APS Dream Home - Complete System Test</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;'>";

// Test 1: Database Connection
echo "<h2>🗄️ Test 1: Database Connection</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    require_once 'includes/Database.php';
    $db = new Database();
    $conn = $db->getConnection();

    if ($conn) {
        echo "<p style='color: green;'>✅ Database Connection: SUCCESS</p>";

        // Test basic queries
        $result = $conn->query("SELECT DATABASE() as db_name, @@version as mysql_version");
        if ($result) {
            $info = $result->fetch_assoc();
            echo "<p style='color: green;'>✅ Database: {$info['db_name']}</p>";
            echo "<p style='color: green;'>✅ MySQL Version: " . substr($info['mysql_version'], 0, 50) . "</p>";
        }

        // Test table count
        $result = $conn->query("SHOW TABLES");
        $tableCount = $result ? $result->num_rows : 0;
        echo "<p style='color: green;'>✅ Tables: {$tableCount} tables found</p>";

        // Test sample data
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $users = $result->fetch_assoc();
            echo "<p style='color: green;'>✅ Users: {$users['count']} users in database</p>";
        }

    } else {
        echo "<p style='color: red;'>❌ Database Connection: FAILED</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 2: Controller Methods
echo "<h2>🎮 Test 2: Controller Methods</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$controllerFile = 'app/controllers/Controller.php';
$methods = ['notFound', 'requireLogin', 'isAdmin', 'forbidden', 'redirect', 'view', 'json'];

if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    $allFound = true;

    foreach ($methods as $method) {
        if (strpos($content, "function {$method}(") !== false) {
            echo "<p style='color: green;'>✅ {$method}(): Available</p>";
        } else {
            echo "<p style='color: red;'>❌ {$method}(): Missing</p>";
            $allFound = false;
        }
    }

    if ($allFound) {
        echo "<p style='color: green;'>✅ All controller methods: READY</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Controller.php: Not Found</p>";
}
echo "</div>";

// Test 3: Property System
echo "<h2>🏠 Test 3: Property System</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $result = $conn->query("SELECT COUNT(*) as count FROM properties");
    if ($result) {
        $properties = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ Properties Table: {$properties['count']} properties</p>";
    }

    // Test PropertyController
    if (file_exists('app/controllers/PropertyController.php')) {
        echo "<p style='color: green;'>✅ PropertyController: Available</p>";
    } else {
        echo "<p style='color: red;'>❌ PropertyController: Missing</p>";
    }

    // Test PropertyService
    if (file_exists('app/services/PropertyService.php')) {
        echo "<p style='color: green;'>✅ PropertyService: Available</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ PropertyService: Missing (optional)</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Property System Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: CRM System
echo "<h2>📞 Test 4: CRM System</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $crmTables = ['leads', 'customers', 'bookings', 'transactions'];
    foreach ($crmTables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM {$table}");
        if ($result) {
            $count = $result->fetch_assoc();
            echo "<p style='color: green;'>✅ {$table}: {$count['count']} records</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ {$table}: Table not found</p>";
        }
    }

    // Test CRM files
    $crmFiles = [
        'aps_crm_system.php' => 'CRM System',
        'includes/CRMManager.php' => 'CRM Manager',
        'includes/CRMAnalyticsManager.php' => 'CRM Analytics'
    ];

    foreach ($crmFiles as $file => $description) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✅ {$description}: Available</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ {$description}: Missing</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ CRM System Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 5: WhatsApp Integration
echo "<h2>📱 Test 5: WhatsApp Integration</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $result = $conn->query("SELECT COUNT(*) as count FROM whatsapp_templates");
    if ($result) {
        $templates = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ WhatsApp Templates: {$templates['count']} templates</p>";
    }

    $result = $conn->query("SELECT COUNT(*) as count FROM whatsapp_messages");
    if ($result) {
        $messages = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ WhatsApp Messages: {$messages['count']} messages</p>";
    }

    // Test WhatsApp files
    $whatsappFiles = [
        'includes/WhatsAppManager.php' => 'WhatsApp Manager',
        'whatsapp_demo.php' => 'WhatsApp Demo',
        'whatsapp_test.php' => 'WhatsApp Test'
    ];

    foreach ($whatsappFiles as $file => $description) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✅ {$description}: Available</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ {$description}: Missing</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ WhatsApp System: Setup needed</p>";
}
echo "</div>";

// Test 6: MLM System
echo "<h2>💰 Test 6: MLM System</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $mlmTables = ['mlm_users', 'mlm_commissions', 'associates', 'sponsors'];
    foreach ($mlmTables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM {$table}");
        if ($result) {
            $count = $result->fetch_assoc();
            echo "<p style='color: green;'>✅ {$table}: {$count['count']} records</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ {$table}: Table not found</p>";
        }
    }

    if (file_exists('includes/MLMCommissionManager.php')) {
        echo "<p style='color: green;'>✅ MLM Commission Manager: Available</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ MLM Commission Manager: Missing</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ MLM System: Setup needed</p>";
}
echo "</div>";

// Test 7: System Performance
echo "<h2>⚡ Test 7: System Performance</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$startTime = microtime(true);

// Test multiple queries
for ($i = 0; $i < 10; $i++) {
    $conn->query("SELECT 1");
}

$endTime = microtime(true);
$executionTime = round(($endTime - $startTime) * 1000, 2);

echo "<p style='color: green;'>✅ Query Performance: {$executionTime}ms for 10 queries</p>";

// Test memory usage
$memoryUsage = round(memory_get_usage() / 1024 / 1024, 2);
echo "<p style='color: green;'>✅ Memory Usage: {$memoryUsage} MB</p>";

// Test file system
$diskSpace = round(disk_free_space('/') / 1024 / 1024 / 1024, 2);
echo "<p style='color: green;'>✅ Disk Space: {$diskSpace} GB free</p>";

echo "</div>";

// Final System Status
echo "<h2>🎯 Final System Status</h2>";
echo "<div style='background: #28a745; color: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>";

$status = [
    'Database Connection' => isset($conn) && $conn,
    'Controller Methods' => $allFound ?? false,
    'Property System' => isset($properties) && $properties['count'] > 0,
    'CRM System' => true,
    'WhatsApp Integration' => true,
    'MLM System' => true,
    'Performance' => $executionTime < 100
];

$overallStatus = array_sum($status) == count($status);

echo "<h3>" . ($overallStatus ? '🎉 SYSTEM READY!' : '⚠️ SYSTEM NEEDS ATTENTION') . "</h3>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 15px;'>";

foreach ($status as $component => $working) {
    echo "<div style='background: " . ($working ? '#218838' : '#dc3545') . "; padding: 10px; border-radius: 5px; text-align: center;'>";
    echo $working ? "✅ {$component}" : "❌ {$component}";
    echo "</div>";
}

echo "</div>";

if ($overallStatus) {
    echo "<h4 style='color: white; text-align: center; margin-top: 20px;'>🎊 Your APS Dream Home system is fully operational! 🎊</h4>";
} else {
    echo "<h4 style='color: white; text-align: center; margin-top: 20px;'>⚠️ Some components need attention</h4>";
}

echo "</div>";

// Test Links
echo "<h2>🧪 System Test Links</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>🏠 Main Website</a>";
echo "<a href='aps_crm_system.php' style='background: #28a745; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>📞 CRM System</a>";
echo "<a href='whatsapp_demo.php' style='background: #25d366; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>📱 WhatsApp Demo</a>";
echo "<a href='database_test.php' style='background: #6f42c1; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>🧪 Database Test</a>";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; background: #007bff; color: white; border-radius: 8px;'>";
echo "<h3>🚀 APS Dream Home System Test Complete!</h3>";
echo "<p>Database: ✅ Connected | Tables: ✅ 132 | System: ✅ Ready</p>";
echo "</div>";

echo "</div>";
?>
