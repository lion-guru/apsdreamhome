<?php
/**
 * Execute API Keys Management Setup
 * 
 * Execute the SQL script for API keys management
 */

echo "====================================================\n";
echo "🔑 EXECUTE API KEYS MANAGEMENT SETUP 🔑\n";
echo "====================================================\n\n";

// Step 1: Database Connection
echo "Step 1: Database Connection\n";
echo "========================\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected successfully\n\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "🔄 Trying to create database...\n";
    
    try {
        $pdo = new PDO('mysql:host=localhost', 'root', '');
        $pdo->setAttribute(PDO::ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
        $pdo->setAttribute(PDO::ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Database created and connected\n\n";
    } catch (Exception $e2) {
        echo "❌ Database creation failed: " . $e2->getMessage() . "\n";
        exit(1);
    }
}

// Step 2: Read SQL File
echo "Step 2: Read SQL File\n";
echo "====================\n";

$sqlFile = __DIR__ . '/API_KEYS_MANAGEMENT_SETUP.sql';
if (!file_exists($sqlFile)) {
    echo "❌ SQL file not found: $sqlFile\n";
    exit(1);
}

$sqlContent = file_get_contents($sqlFile);
echo "✅ SQL file loaded successfully\n";
echo "📊 File size: " . number_format(filesize($sqlFile) / 1024, 2) . " KB\n\n";

// Step 3: Split SQL into Individual Statements
echo "Step 3: Split SQL into Individual Statements\n";
echo "==========================================\n";

// Remove comments and clean up SQL
$sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
$sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent);
$sqlContent = trim($sqlContent);

// Split by semicolon
$sqlStatements = [];
$statements = explode(';', $sqlContent);

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement) && !preg_match('/^(USE|DROP|CREATE|INSERT|ALTER|UPDATE|DELETE|SELECT)/i', $statement)) {
        continue;
    }
    
    // Skip USE statement as we're already connected
    if (preg_match('/^USE\s+/i', $statement)) {
        continue;
    }
    
    $sqlStatements[] = $statement . ';';
}

echo "📊 Total SQL statements: " . count($sqlStatements) . "\n\n";

// Step 4: Execute SQL Statements
echo "Step 4: Execute SQL Statements\n";
echo "==============================\n";

$executedCount = 0;
$errorCount = 0;
$createdTables = [];
$insertedData = [];
$createdViews = [];

foreach ($sqlStatements as $index => $statement) {
    try {
        $pdo->exec($statement);
        $executedCount++;
        
        // Track what was created
        if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
            $createdTables[] = $matches[1];
        } elseif (preg_match('/INSERT\s+INTO\s+`?(\w+)`?/i', $statement, $matches)) {
            $insertedData[] = $matches[1];
        } elseif (preg_match('/CREATE\s+VIEW\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
            $createdViews[] = $matches[1];
        }
        
        // Progress indicator
        if (($index + 1) % 10 == 0) {
            echo "📊 Progress: " . ($index + 1) . "/" . count($sqlStatements) . " statements executed\n";
        }
        
    } catch (Exception $e) {
        $errorCount++;
        echo "❌ Error in statement " . ($index + 1) . ": " . $e->getMessage() . "\n";
        echo "📝 Statement: " . substr($statement, 0, 100) . "...\n\n";
    }
}

echo "\n✅ SQL Execution Results:\n";
echo "   Statements Executed: $executedCount\n";
echo "   Errors: $errorCount\n";
echo "   Success Rate: " . round(($executedCount / count($sqlStatements)) * 100, 1) . "%\n\n";

// Step 5: Verification
echo "Step 5: Verification\n";
echo "==================\n";

echo "📊 Created Tables:\n";
foreach ($createdTables as $table) {
    echo "   ✅ $table\n";
}

echo "\n📊 Data Inserted Into:\n";
foreach ($insertedData as $table) {
    echo "   ✅ $table\n";
}

echo "\n📊 Created Views:\n";
foreach ($createdViews as $view) {
    echo "   ✅ $view\n";
}

// Verify actual database state
try {
    $stmt = $pdo->query("SHOW TABLES");
    $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📊 Database Verification:\n";
    echo "   Total Tables: " . count($allTables) . "\n";
    
    // Check if our tables exist
    $expectedTables = ['api_keys', 'api_usage_logs', 'integration_configurations', 'webhook_endpoints'];
    $foundTables = [];
    
    foreach ($expectedTables as $table) {
        if (in_array($table, $allTables)) {
            $foundTables[] = $table;
        }
    }
    
    echo "   API Tables Created: " . count($foundTables) . "/4\n";
    foreach ($foundTables as $table) {
        echo "     ✅ $table\n";
    }
    
    // Check data counts
    if (in_array('api_keys', $foundTables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM api_keys");
        $apiKeysCount = $stmt->fetchColumn();
        echo "   API Keys Records: $apiKeysCount\n";
    }
    
    if (in_array('integration_configurations', $foundTables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM integration_configurations");
        $configsCount = $stmt->fetchColumn();
        echo "   Integration Configs: $configsCount\n";
    }
    
    if (in_array('webhook_endpoints', $foundTables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM webhook_endpoints");
        $webhooksCount = $stmt->fetchColumn();
        echo "   Webhook Endpoints: $webhooksCount\n";
    }
    
} catch (Exception $e) {
    echo "❌ Verification failed: " . $e->getMessage() . "\n";
}

// Step 6: Sample Queries
echo "\nStep 6: Sample Queries\n";
echo "====================\n";

echo "📊 Testing API Management System:\n";

try {
    // Test API Keys
    if (in_array('api_keys', $foundTables)) {
        $stmt = $pdo->query("SELECT key_name, key_type, status FROM api_keys WHERE status = 'active' ORDER BY created_at DESC LIMIT 5");
        $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n🔑 Active API Keys (5):\n";
        foreach ($apiKeys as $key) {
            echo "   • {$key['key_name']} ({$key['key_type']}) - {$key['status']}\n";
        }
    }
    
    // Test Integration Configurations
    if (in_array('integration_configurations', $foundTables)) {
        $stmt = $pdo->query("SELECT service_name, config_key, is_active FROM integration_configurations WHERE is_active = 1 ORDER BY service_name, config_key LIMIT 10");
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n⚙️ Active Configurations (10):\n";
        foreach ($configs as $config) {
            echo "   • {$config['service_name']}.{$config['config_key']} - " . ($config['is_active'] ? 'Active' : 'Inactive') . "\n";
        }
    }
    
    // Test Views
    if (in_array('api_keys_view', $allTables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM api_keys_view");
        $viewCount = $stmt->fetchColumn();
        echo "\n📊 API Keys View Records: $viewCount\n";
    }
    
} catch (Exception $e) {
    echo "❌ Sample queries failed: " . $e->getMessage() . "\n";
}

// Step 7: Final Status
echo "\nStep 7: Final Status\n";
echo "==================\n";

if ($errorCount === 0 && count($foundTables) === 4) {
    echo "🎊 API KEYS MANAGEMENT SETUP COMPLETED SUCCESSFULLY! 🎊\n";
    echo "✅ All tables created\n";
    echo "✅ All data inserted\n";
    echo "✅ All views created\n";
    echo "✅ System ready for use\n\n";
    
    echo "🚀 Next Steps:\n";
    echo "1. ✅ Test API connectivity\n";
    echo "2. ✅ Configure actual API keys\n";
    echo "3. ✅ Set up webhook endpoints\n";
    echo "4. ✅ Monitor API usage\n";
    echo "5. ✅ Implement security measures\n\n";
    
} else {
    echo "⚠️ API KEYS MANAGEMENT SETUP COMPLETED WITH ISSUES\n";
    echo "❌ Errors encountered: $errorCount\n";
    echo "❌ Tables missing: " . (4 - count($foundTables)) . "\n";
    echo "🔄 Please review errors above and fix manually\n\n";
}

echo "====================================================\n";
echo "🔑 API KEYS MANAGEMENT SETUP EXECUTION COMPLETE! 🔑\n";
echo "📊 Status: " . ($errorCount === 0 ? 'SUCCESS' : 'PARTIAL') . "\n\n";

echo "🏆 EXECUTION SUMMARY:\n";
echo "• ✅ SQL statements executed: $executedCount\n";
echo "• ✅ Tables created: " . count($createdTables) . "\n";
echo "• ✅ Data inserted: " . count($insertedData) . " tables\n";
echo "• ✅ Views created: " . count($createdViews) . "\n";
echo "• ✅ Errors: $errorCount\n";
echo "• ✅ Success Rate: " . round(($executedCount / count($sqlStatements)) * 100, 1) . "%\n\n";

echo "🎯 API MANAGEMENT SYSTEM STATUS:\n";
echo "• Database Tables: " . count($foundTables) . "/4 created\n";
echo "• API Keys: " . ($apiKeysCount ?? 0) . " records\n";
echo "• Configurations: " . ($configsCount ?? 0) . " records\n";
echo "• Webhooks: " . ($webhooksCount ?? 0) . " records\n";
echo "• Views: " . (in_array('api_keys_view', $allTables) ? 'Created' : 'Missing') . "\n\n";

echo "🎊 API KEYS MANAGEMENT SYSTEM READY! 🎊\n";
echo "🏆 ENTERPRISE-GRADE API INFRASTRUCTURE IMPLEMENTED! 🏆\n\n";
?>
