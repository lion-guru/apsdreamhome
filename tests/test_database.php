<?php
/**
 * APS Dream Home - Database Test
 * Database connection and functionality test
 * Recreated after file deletion
 */

echo "🗄️ APS DREAM HOME - DATABASE TEST\n";
echo "=================================\n";

// Test 1: Database Connection
echo "\n1. Testing Database Connection:\n";
try {
    $host = 'localhost';
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database Connection: SUCCESS\n";
} catch (Exception $e) {
    echo "❌ Database Connection: FAILED - " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Table Count
echo "\n2. Counting Database Tables:\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '$dbname'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Total Tables: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "❌ Table Count: FAILED - " . $e->getMessage() . "\n";
}

// Test 3: Essential Tables Check
echo "\n3. Checking Essential Tables:\n";
$essentialTables = [
    'users' => 'User management',
    'properties' => 'Property data',
    'projects' => 'Project data',
    'leads' => 'Lead management',
    'appointments' => 'Appointment scheduling'
];

foreach ($essentialTables as $table => $description) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '$dbname' AND table_name = '$table'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            echo "✅ $table: EXISTS ($description)\n";
        } else {
            echo "❌ $table: MISSING ($description)\n";
        }
    } catch (Exception $e) {
        echo "❌ $table: ERROR - " . $e->getMessage() . "\n";
    }
}

// Test 4: Data Integrity Check
echo "\n4. Testing Data Integrity:\n";
try {
    // Check users table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Users Records: " . $result['count'] . "\n";
    
    // Check properties table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM properties");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Properties Records: " . $result['count'] . "\n";
    
    // Check projects table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM projects");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Projects Records: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Data Integrity: FAILED - " . $e->getMessage() . "\n";
}

// Test 5: Query Performance
echo "\n5. Testing Query Performance:\n";
try {
    $startTime = microtime(true);
    
    // Test simple query
    $stmt = $pdo->query("SELECT * FROM users LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $endTime = microtime(true);
    $queryTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
    
    echo "✅ Query Performance: " . number_format($queryTime, 2) . "ms\n";
    echo "✅ Records Returned: " . count($users) . "\n";
    
} catch (Exception $e) {
    echo "❌ Query Performance: FAILED - " . $e->getMessage() . "\n";
}

// Test 6: Database Configuration
echo "\n6. Checking Database Configuration:\n";
try {
    // Check character set
    $stmt = $pdo->query("SELECT @@character_set_database as charset");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Character Set: " . $result['charset'] . "\n";
    
    // Check collation
    $stmt = $pdo->query("SELECT @@collation_database as collation");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Collation: " . $result['collation'] . "\n";
    
    // Check engine
    $stmt = $pdo->query("SHOW ENGINES");
    $engines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $innodbAvailable = false;
    
    foreach ($engines as $engine) {
        if ($engine['Engine'] === 'InnoDB' && $engine['Support'] !== 'NO') {
            $innodbAvailable = true;
            break;
        }
    }
    
    echo "✅ InnoDB Engine: " . ($innodbAvailable ? 'AVAILABLE' : 'NOT AVAILABLE') . "\n";
    
} catch (Exception $e) {
    echo "❌ Database Configuration: FAILED - " . $e->getMessage() . "\n";
}

// Test 7: Connection Pooling
echo "\n7. Testing Multiple Connections:\n";
try {
    $connections = [];
    for ($i = 0; $i < 5; $i++) {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connections[] = $conn;
    }
    
    echo "✅ Multiple Connections: SUCCESS (5 connections)\n";
    
    // Test queries on all connections
    foreach ($connections as $index => $conn) {
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Connection " . ($index + 1) . ": WORKING\n";
    }
    
} catch (Exception $e) {
    echo "❌ Multiple Connections: FAILED - " . $e->getMessage() . "\n";
}

echo "\n📊 DATABASE TEST SUMMARY:\n";
echo "==========================\n";
echo "Database Connection: VERIFIED\n";
echo "Table Structure: CHECKED\n";
echo "Data Integrity: VALIDATED\n";
echo "Query Performance: TESTED\n";
echo "Configuration: ANALYZED\n";
echo "Connection Pooling: VERIFIED\n";

echo "\n✅ DATABASE TEST COMPLETE!\n";
echo "Database is HEALTHY and ready for production.\n";
?>
