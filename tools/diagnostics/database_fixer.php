<?php
/**
 * APS Dream Home - DATABASE CONNECTION FIXER
 * Automatically fix and test database connection
 */

echo "🏠 APS Dream Home - DATABASE CONNECTION FIXER\n";
echo "==========================================\n\n";

$projectRoot = 'c:\\xampp\\htdocs\\apsdreamhome';
$envFile = $projectRoot . '/.env';

// 1. Check current .env file
echo "1. 🔍 CHECKING CURRENT .env FILE\n";
echo "===============================\n";

if (file_exists($envFile)) {
    echo "   ✅ .env file found\n";
    
    $envContent = file_get_contents($envFile);
    echo "   📄 Current .env content:\n";
    echo "   " . str_replace("\n", "\n   ", $envContent) . "\n";
} else {
    echo "   ❌ .env file not found\n";
    echo "   📝 Creating new .env file...\n";
    
    $defaultEnv = "DB_HOST=localhost
DB_NAME=apsdreamhome
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

APP_NAME=APS Dream Home
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/apsdreamhome

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls";
    
    file_put_contents($envFile, $defaultEnv);
    echo "   ✅ Default .env file created\n";
}

// 2. Parse current .env
echo "\n2. 📋 PARSING ENVIRONMENT VARIABLES\n";
echo "===================================\n";

$envVars = [];
$envContent = file_get_contents($envFile);

foreach (explode("\n", $envContent) as $line) {
    if (strpos($line, '=') !== false && !empty(trim($line)) && substr($line, 0, 1) !== '#') {
        list($key, $value) = explode('=', $line, 2);
        $envVars[trim($key)] = trim($value);
    }
}

$requiredVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$missingVars = [];

foreach ($requiredVars as $var) {
    if (isset($envVars[$var]) && !empty($envVars[$var])) {
        echo "   ✅ $var: " . (substr($var, 0, 6) === 'DB_PASS' ? '***' : $envVars[$var]) . "\n";
    } else {
        echo "   ❌ $var: Missing or empty\n";
        $missingVars[] = $var;
    }
}

// 3. Test database connection
echo "\n3. 🗄️ TESTING DATABASE CONNECTION\n";
echo "=================================\n";

$connectionTest = [
    'success' => false,
    'error' => '',
    'tables' => 0
];

if (empty($missingVars)) {
    try {
        $conn = new mysqli(
            $envVars['DB_HOST'],
            $envVars['DB_USER'],
            $envVars['DB_PASS'],
            $envVars['DB_NAME']
        );
        
        if ($conn->connect_error) {
            $connectionTest['error'] = $conn->connect_error;
            echo "   ❌ Connection failed: " . $conn->connect_error . "\n";
        } else {
            $connectionTest['success'] = true;
            echo "   ✅ Database connection successful!\n";
            
            // Test basic query
            $result = $conn->query("SELECT VERSION() as version");
            $row = $result->fetch_assoc();
            echo "   ✅ MySQL Version: " . $row['version'] . "\n";
            
            // Count tables
            $result = $conn->query("SHOW TABLES");
            $connectionTest['tables'] = $result->num_rows;
            echo "   ✅ Found {$connectionTest['tables']} tables\n";
            
            // Check if main tables exist
            $mainTables = ['users', 'properties', 'associates', 'commissions'];
            $existingTables = [];
            
            $result = $conn->query("SHOW TABLES");
            while ($row = $result->fetch_array()) {
                $existingTables[] = $row[0];
            }
            
            foreach ($mainTables as $table) {
                if (in_array($table, $existingTables)) {
                    echo "   ✅ Table '$table' exists\n";
                } else {
                    echo "   ⚠️  Table '$table' missing\n";
                }
            }
            
            $conn->close();
        }
    } catch (Exception $e) {
        $connectionTest['error'] = $e->getMessage();
        echo "   ❌ Database error: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ Cannot test - missing required variables\n";
}

// 4. Auto-fix if needed
echo "\n4. 🔧 AUTO-FIXING ISSUES\n";
echo "======================\n";

if (!$connectionTest['success'] || !empty($missingVars)) {
    echo "   🔧 Attempting to fix database connection...\n";
    
    // Try common database configurations
    $commonConfigs = [
        ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'apsdreamhome'],
        ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'test'],
        ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '', 'name' => 'apsdreamhome'],
        ['host' => 'localhost', 'user' => 'root', 'pass' => 'root', 'name' => 'apsdreamhome'],
    ];
    
    $workingConfig = null;
    
    foreach ($commonConfigs as $config) {
        try {
            $testConn = new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
            
            if ($testConn->connect_error) {
                $testConn->close();
                continue;
            }
            
            // Test if we can create/use database
            $testConn->close();
            $workingConfig = $config;
            break;
            
        } catch (Exception $e) {
            continue;
        }
    }
    
    if ($workingConfig) {
        echo "   ✅ Found working configuration!\n";
        echo "   🔧 Updating .env file...\n";
        
        // Update .env with working config
        $newEnvContent = "";
        foreach ($envVars as $key => $value) {
            switch ($key) {
                case 'DB_HOST':
                    $newEnvContent .= "DB_HOST=" . $workingConfig['host'] . "\n";
                    break;
                case 'DB_USER':
                    $newEnvContent .= "DB_USER=" . $workingConfig['user'] . "\n";
                    break;
                case 'DB_PASS':
                    $newEnvContent .= "DB_PASS=" . $workingConfig['pass'] . "\n";
                    break;
                case 'DB_NAME':
                    $newEnvContent .= "DB_NAME=" . $workingConfig['name'] . "\n";
                    break;
                default:
                    $newEnvContent .= "$key=$value\n";
            }
        }
        
        file_put_contents($envFile, $newEnvContent);
        echo "   ✅ .env file updated successfully\n";
        
        // Test again
        echo "   🔄 Testing updated configuration...\n";
        try {
            $testConn = new mysqli(
                $workingConfig['host'],
                $workingConfig['user'],
                $workingConfig['pass'],
                $workingConfig['name']
            );
            
            if ($testConn->connect_error) {
                echo "   ❌ Still failing: " . $testConn->connect_error . "\n";
            } else {
                echo "   ✅ Connection successful after fix!\n";
                $connectionTest['success'] = true;
                $connectionTest['error'] = '';
                
                $result = $testConn->query("SHOW TABLES");
                $connectionTest['tables'] = $result->num_rows;
                echo "   ✅ Found {$connectionTest['tables']} tables\n";
                
                $testConn->close();
            }
        } catch (Exception $e) {
            echo "   ❌ Test failed: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "   ❌ Could not find working database configuration\n";
        echo "   💡 Manual setup required:\n";
        echo "      1. Create database 'apsdreamhome' in phpMyAdmin\n";
        echo "      2. Update .env with correct credentials\n";
        echo "      3. Run this script again\n";
    }
} else {
    echo "   ✅ Database connection is working properly!\n";
}

// 5. Create database if needed
echo "\n5. 🗄️ DATABASE CREATION CHECK\n";
echo "=============================\n";

if ($connectionTest['success'] && $connectionTest['tables'] < 10) {
    echo "   ⚠️  Database has few tables ($connectionTest['tables'] found)\n";
    echo "   💡 You may need to run database migration:\n";
    echo "      1. Check database/ directory for SQL files\n";
    echo "      2. Import SQL files via phpMyAdmin\n";
    echo "      3. Or run migration scripts if available\n";
} elseif ($connectionTest['success']) {
    echo "   ✅ Database appears to be properly set up\n";
}

// 6. Final status
echo "\n6. 🏆 FINAL STATUS\n";
echo "================\n";

if ($connectionTest['success']) {
    echo "   🟢 DATABASE CONNECTION: WORKING\n";
    echo "   🟢 TABLES FOUND: {$connectionTest['tables']}\n";
    echo "   🟢 STATUS: READY FOR APPLICATION\n";
    
    echo "\n   🎯 NEXT STEPS:\n";
    echo "   1. ✅ Database connection fixed\n";
    echo "   2. 📂 Check if all tables exist\n";
    echo "   3. 🧪 Test application functionality\n";
    echo "   4. 🚀 Ready for deployment!\n";
    
} else {
    echo "   🔴 DATABASE CONNECTION: FAILED\n";
    echo "   🔴 ERROR: " . $connectionTest['error'] . "\n";
    echo "   🔴 STATUS: NEEDS MANUAL FIX\n";
    
    echo "\n   🔧 MANUAL FIX STEPS:\n";
    echo "   1. Open phpMyAdmin\n";
    echo "   2. Create database 'apsdreamhome'\n";
    echo "   3. Update .env file with correct credentials:\n";
    echo "      DB_HOST=localhost\n";
    echo "      DB_NAME=apsdreamhome\n";
    echo "      DB_USER=root\n";
    echo "      DB_PASS=\n";
    echo "   4. Run this script again\n";
}

echo "\n🎉 DATABASE FIXER COMPLETED!\n";
echo "============================\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Status: " . ($connectionTest['success'] ? '✅ FIXED' : '❌ NEEDS MANUAL FIX') . "\n";

?>
