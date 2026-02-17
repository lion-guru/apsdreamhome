<?php
/**
 * APS Dream Home - SIMPLE DATABASE FIXER
 * Quick database connection fix and test
 */

echo "🏠 APS Dream Home - SIMPLE DATABASE FIXER\n";
echo "=======================================\n\n";

$projectRoot = 'c:\\xampp\\htdocs\\apsdreamhome';
$envFile = $projectRoot . '/.env';

// 1. Check .env file
echo "1. 🔍 CHECKING .env FILE\n";
echo "======================\n";

if (!file_exists($envFile)) {
    echo "   ❌ .env file not found\n";
    echo "   📝 Creating .env file...\n";
    
    $defaultEnv = "DB_HOST=localhost
DB_NAME=apsdreamhome
DB_USER=root
DB_PASS=
APP_NAME=APS Dream Home
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/apsdreamhome";
    
    file_put_contents($envFile, $defaultEnv);
    echo "   ✅ .env file created\n";
} else {
    echo "   ✅ .env file found\n";
}

// 2. Parse .env
$envContent = file_get_contents($envFile);
$envVars = [];

foreach (explode("\n", $envContent) as $line) {
    if (strpos($line, '=') !== false && !empty(trim($line)) && substr($line, 0, 1) !== '#') {
        list($key, $value) = explode('=', $line, 2);
        $envVars[trim($key)] = trim($value);
    }
}

echo "   📋 Database settings:\n";
echo "      Host: " . ($envVars['DB_HOST'] ?? 'not set') . "\n";
echo "      Name: " . ($envVars['DB_NAME'] ?? 'not set') . "\n";
echo "      User: " . ($envVars['DB_USER'] ?? 'not set') . "\n";
echo "      Pass: " . (isset($envVars['DB_PASS']) ? '***' : 'not set') . "\n";

// 3. Test connection
echo "\n2. 🗄️ TESTING DATABASE CONNECTION\n";
echo "===============================\n";

$success = false;
$error = '';

try {
    $conn = new mysqli(
        $envVars['DB_HOST'] ?? 'localhost',
        $envVars['DB_USER'] ?? 'root',
        $envVars['DB_PASS'] ?? '',
        $envVars['DB_NAME'] ?? 'apsdreamhome'
    );
    
    if ($conn->connect_error) {
        $error = $conn->connect_error;
        echo "   ❌ Connection failed: $error\n";
    } else {
        echo "   ✅ Connection successful!\n";
        
        $result = $conn->query("SELECT VERSION() as version");
        $row = $result->fetch_assoc();
        echo "   ✅ MySQL Version: " . $row['version'] . "\n";
        
        $result = $conn->query("SHOW TABLES");
        $tableCount = $result->num_rows;
        echo "   ✅ Found $tableCount tables\n";
        
        $success = true;
        $conn->close();
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "   ❌ Error: $error\n";
}

// 4. Status
echo "\n3. 🏆 STATUS\n";
echo "===========\n";

if ($success) {
    echo "   🟢 DATABASE: WORKING\n";
    echo "   🟢 PROJECT: READY\n";
    echo "\n   🎯 Your APS Dream Home is ready to use!\n";
} else {
    echo "   🔴 DATABASE: FAILED\n";
    echo "   🔴 PROJECT: NEEDS SETUP\n";
    echo "\n   🔧 To fix:\n";
    echo "   1. Open phpMyAdmin\n";
    echo "   2. Create database 'apsdreamhome'\n";
    echo "   3. Update .env with correct credentials\n";
    echo "   4. Run this script again\n";
}

echo "\n🎉 DATABASE FIXER COMPLETED!\n";
echo "===========================\n";
echo "Status: " . ($success ? '✅ FIXED' : '❌ NEEDS MANUAL SETUP') . "\n";

?>
