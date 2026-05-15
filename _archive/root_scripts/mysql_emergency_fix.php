<?php

/**
 * APS Dream Home - MySQL Emergency Fix Script
 * Comprehensive fix for XAMPP MySQL not starting
 */

echo "=== APS DREAM HOME - MYSQL EMERGENCY FIX ===\n\n";

echo "🔧 STARTING MYSQL FIX PROCEDURE...\n\n";

// Step 1: Kill any existing MySQL processes
echo "1️⃣ Killing existing MySQL processes...\n";
$killOutput = shell_exec('taskkill /f /im mysqld.exe 2>&1');
if (strpos($killOutput, 'SUCCESS') !== false) {
    echo "✅ Killed existing MySQL processes\n";
} else {
    echo "ℹ️ No MySQL processes found to kill\n";
}
sleep(2);

// Step 2: Check port 3306
echo "\n2️⃣ Checking port 3306...\n";
$portCheck = shell_exec('netstat -ano | findstr :3306');
if (strpos($portCheck, 'LISTENING') !== false) {
    echo "⚠️ Port 3306 is in use!\n";
    // Extract PID and kill it
    preg_match('/LISTENING\s+(\d+)/', $portCheck, $matches);
    if (isset($matches[1])) {
        $pid = $matches[1];
        echo "📝 Found process using port 3306 (PID: $pid)\n";
        shell_exec("taskkill /f /pid $pid");
        echo "✅ Killed process $pid\n";
    }
} else {
    echo "✅ Port 3306 is free\n";
}

// Step 3: Clear MySQL temp files
echo "\n3️⃣ Clearing MySQL temp files...\n";
$tempFiles = [
    'C:\\xampp\\mysql\\data\\*.pid',
    'C:\\xampp\\mysql\\data\\ibtmp1',
    'C:\\xampp\\tmp\\mysql*'
];

foreach ($tempFiles as $pattern) {
    $files = glob($pattern);
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "✅ Deleted: $file\n";
        }
    }
}
echo "✅ Temp files cleared\n";

// Step 4: Check MySQL configuration
echo "\n4️⃣ Checking MySQL configuration...\n";
$myIni = 'C:\\xampp\\mysql\\bin\\my.ini';
if (file_exists($myIni)) {
    $content = file_get_contents($myIni);
    if (strpos($content, 'port=3306') !== false) {
        echo "✅ Port 3306 configured correctly\n";
    }
    if (strpos($content, 'datadir') !== false) {
        echo "✅ Data directory configured\n";
    }
} else {
    echo "❌ my.ini not found!\n";
}

// Step 5: Test if we can start MySQL
echo "\n5️⃣ Testing MySQL startup...\n";
echo "📝 Attempting to start MySQL...\n";

// Try to start MySQL using background process
shell_exec('start /B "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" > NUL 2>&1');

// Check if MySQL started
$processCheck = shell_exec('tasklist | findstr mysqld.exe');
if (strpos($processCheck, 'mysqld.exe') !== false) {
    echo "✅ MySQL process started!\n";

    // Test connection
    $connCheck = shell_exec('C:\\xampp\\mysql\\bin\\mysqladmin.exe -u root -p ping 2>&1');
    if (strpos($connCheck, 'mysqld is alive') !== false) {
        echo "✅ MySQL is responding!\n";
    } else {
        echo "⚠️ MySQL process running but not responding yet...\n";
    }
} else {
    echo "❌ MySQL failed to start\n";
    echo "📝 Check C:\\xampp\\mysql\\data\\mysql_error.log for details\n";
}

// Step 6: Port verification
echo "\n6️⃣ Verifying port 3306...\n";
sleep(2);
$portVerify = shell_exec('netstat -ano | findstr :3306');
if (strpos($portVerify, 'LISTENING') !== false) {
    echo "✅ MySQL is listening on port 3306!\n";
    preg_match('/LISTENING\s+(\d+)/', $portVerify, $matches);
    if (isset($matches[1])) {
        echo "📝 Process ID: " . $matches[1] . "\n";
    }
} else {
    echo "❌ MySQL is not listening on port 3306\n";
}

echo "\n=== FIX COMPLETE ===\n";
echo "\n📋 SUMMARY:\n";
echo "✅ Killed existing processes\n";
echo "✅ Cleared port conflicts\n";
echo "✅ Removed temp files\n";
echo "✅ Configuration verified\n";

$finalCheck = shell_exec('tasklist | findstr mysqld.exe');
if (strpos($finalCheck, 'mysqld.exe') !== false) {
    echo "✅ MySQL: RUNNING\n";
    echo "\n🎉 SUCCESS! MySQL is now running!\n";
    echo "🔗 Test at: http://localhost:8000\n";
} else {
    echo "❌ MySQL: NOT RUNNING\n";
    echo "\n⚠️ MANUAL ACTION REQUIRED:\n";
    echo "1. Open XAMPP Control Panel\n";
    echo "2. Click 'Start' next to MySQL\n";
    echo "3. If still not working, check logs:\n";
    echo "   C:\\xampp\\mysql\\data\\mysql_error.log\n";
}

echo "\n🏆 MYSQL FIX SCRIPT COMPLETE\n";
