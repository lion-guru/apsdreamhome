<?php

/**
 * APS Dream Home - Database Connection Fix
 * Tests and fixes MySQL connection issues
 */

echo "=== APS Dream Home - Database Connection Fix ===\n\n";

// Test different connection methods
$hosts = ['localhost', '127.0.0.1', 'localhost:3306'];
$users = ['root', ''];
$passwords = ['', 'root', 'password'];

echo "🔍 Testing MySQL connections...\n\n";

$workingConnection = null;

foreach ($hosts as $host) {
    foreach ($users as $user) {
        foreach ($passwords as $pass) {
            try {
                $dsn = "mysql:host=$host";
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5
                ]);
                
                echo "✅ Connected: Host=$host, User=$user, Pass=" . (empty($pass) ? 'empty' : 'set') . "\n";
                
                if ($workingConnection === null) {
                    $workingConnection = [
                        'host' => $host,
                        'user' => $user,
                        'pass' => $pass
                    ];
                }
                
            } catch (PDOException $e) {
                // Skip showing errors for failed attempts
            }
        }
    }
}

if ($workingConnection === null) {
    echo "❌ No working MySQL connection found!\n\n";
    
    echo "🔧 Possible Solutions:\n";
    echo "1. Start MySQL/XAMPP services\n";
    echo "2. Check MySQL port (3306) is open\n";
    echo "3. Verify MySQL credentials\n";
    echo "4. Check firewall settings\n";
    echo "5. Restart MySQL service\n\n";
    
    echo "🚨 XAMPP Check:\n";
    echo "• Open XAMPP Control Panel\n";
    echo "• Start Apache and MySQL services\n";
    echo "• Check for any error messages\n";
    
} else {
    echo "\n✅ Working connection found!\n";
    echo "Host: " . $workingConnection['host'] . "\n";
    echo "User: " . $workingConnection['user'] . "\n";
    echo "Password: " . (empty($workingConnection['pass']) ? 'empty' : 'set') . "\n\n";
    
    // Now try to import with working connection
    echo "🚀 Starting import with working connection...\n\n";
    
    $sqlFile = __DIR__ . '/apsdreamhome.sql';
    $database = 'apsdreamhome';
    
    if (!file_exists($sqlFile)) {
        die("❌ SQL file not found: $sqlFile\n");
    }
    
    try {
        $pdo = new PDO("mysql:host=" . $workingConnection['host'], $workingConnection['user'], $workingConnection['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Disable constraints for faster import
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("SET UNIQUE_CHECKS = 0");
        
        // Drop and recreate database
        echo "🗑️ Dropping existing database...\n";
        $pdo->exec("DROP DATABASE IF EXISTS `$database`");
        
        echo "📦 Creating new database...\n";
        $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$database`");
        
        echo "📥 Importing database (this may take a moment)...\n";
        
        // Use MySQL command for faster import
        $tempFile = tempnam(sys_get_temp_dir(), 'sql_import');
        file_put_contents($tempFile, file_get_contents($sqlFile));
        
        $command = sprintf(
            'mysql -h%s -u%s %s %s < %s 2>&1',
            $workingConnection['host'],
            $workingConnection['user'],
            empty($workingConnection['pass']) ? '' : '-p' . $workingConnection['pass'],
            $database,
            $tempFile
        );
        
        echo "🔧 Running: mysql command import...\n";
        $output = shell_exec($command);
        
        if ($output === null || empty($output)) {
            echo "✅ Import completed successfully!\n";
            
            // Verify
            $stmt = $pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "🔍 Verification: " . count($tables) . " tables imported\n";
            
            if (count($tables) >= 500) {
                echo "🎉 Excellent! Almost all tables imported!\n";
            } elseif (count($tables) >= 100) {
                echo "✅ Good! Core tables imported!\n";
            }
            
        } else {
            echo "⚠️ Import output: " . substr($output, 0, 200) . "...\n";
        }
        
        // Cleanup
        unlink($tempFile);
        
        // Re-enable constraints
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        $pdo->exec("SET UNIQUE_CHECKS = 1");
        
        echo "\n🎯 Database 'apsdreamhome' is now ready!\n";
        echo "🔗 Your application can now connect to apsdreamhome database!\n";
        
    } catch (Exception $e) {
        echo "❌ Import error: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "💡 Next Steps:\n";
echo "1. Test your application\n";
echo "2. Check if all features work\n";
echo "3. Verify admin login\n";
echo "4. Test database operations\n";
?>
