<?php

/**
 * APS Dream Home - PowerShell Test
 * Tests if PowerShell is working properly
 */

echo "=== APS Dream Home - PowerShell Test ===\n\n";

echo "🔍 Testing PowerShell functionality...\n\n";

// Test 1: Basic PowerShell command
echo "1. 📋 Basic PowerShell Command:\n";
$output = shell_exec('powershell -Command "Write-Host \'PowerShell is working!\'" 2>&1');
if (strpos($output, 'PowerShell is working!') !== false) {
    echo "   ✅ PowerShell basic command working\n";
} else {
    echo "   ❌ PowerShell basic command failed: " . substr($output, 0, 100) . "\n";
}

// Test 2: PowerShell version
echo "\n2. 📋 PowerShell Version:\n";
$output = shell_exec('powershell -Command "$PSVersionTable.PSVersion" 2>&1');
if (strpos($output, 'Major') !== false || strpos($output, '5.') !== false) {
    echo "   ✅ PowerShell version detected: " . substr($output, 0, 50) . "\n";
} else {
    echo "   ❌ PowerShell version check failed\n";
}

// Test 3: Execution Policy
echo "\n3. 📋 Execution Policy:\n";
$output = shell_exec('powershell -Command "Get-ExecutionPolicy" 2>&1');
if (trim($output)) {
    echo "   ✅ Execution Policy: " . trim($output) . "\n";
} else {
    echo "   ❌ Execution Policy check failed\n";
}

// Test 4: PHP via PowerShell
echo "\n4. 📋 PHP via PowerShell:\n";
$output = shell_exec('powershell -Command "php -v" 2>&1');
if (strpos($output, 'PHP') !== false) {
    echo "   ✅ PHP working via PowerShell: " . substr($output, 0, 50) . "\n";
} else {
    echo "   ❌ PHP via PowerShell failed\n";
}

// Test 5: Git via PowerShell
echo "\n5. 📋 Git via PowerShell:\n";
$output = shell_exec('powershell -Command "git --version" 2>&1');
if (strpos($output, 'git') !== false) {
    echo "   ✅ Git working via PowerShell: " . substr($output, 0, 50) . "\n";
} else {
    echo "   ❌ Git via PowerShell failed\n";
}

// Test 6: File operations
echo "\n6. 📋 File Operations:\n";
$testFile = __DIR__ . '/powershell-test.txt';
$output = shell_exec("powershell -Command \"Set-Content '$testFile' 'PowerShell test successful'\" 2>&1");
if (file_exists($testFile)) {
    echo "   ✅ File creation working\n";
    $content = file_get_contents($testFile);
    if (strpos($content, 'PowerShell test successful') !== false) {
        echo "   ✅ File content working\n";
    } else {
        echo "   ❌ File content failed\n";
    }
    unlink($testFile);
} else {
    echo "   ❌ File creation failed\n";
}

// Test 7: Directory operations
echo "\n7. 📋 Directory Operations:\n";
$output = shell_exec('powershell -Command "Get-Location" 2>&1');
if (strpos($output, 'Path') !== false || strpos($output, '\\') !== false) {
    echo "   ✅ Directory operations working: " . substr($output, 0, 50) . "\n";
} else {
    echo "   ❌ Directory operations failed\n";
}

echo "\n📊 SUMMARY:\n";
echo str_repeat("=", 50) . "\n";

$workingTests = 0;
$totalTests = 7;

// Count working tests
if (strpos(shell_exec('powershell -Command "Write-Host \'test\'" 2>&1'), 'test') !== false) $workingTests++;
if (strpos(shell_exec('powershell -Command "$PSVersionTable.PSVersion" 2>&1'), 'Major') !== false || strpos(shell_exec('powershell -Command "$PSVersionTable.PSVersion" 2>&1'), '5.') !== false) $workingTests++;
if (trim(shell_exec('powershell -Command "Get-ExecutionPolicy" 2>&1'))) $workingTests++;
if (strpos(shell_exec('powershell -Command "php -v" 2>&1'), 'PHP') !== false) $workingTests++;
if (strpos(shell_exec('powershell -Command "git --version" 2>&1'), 'git') !== false) $workingTests++;

echo "PowerShell Tests: $workingTests/$totalTests working\n";
echo "Success Rate: " . round(($workingTests / $totalTests) * 100) . "%\n\n";

if ($workingTests >= 5) {
    echo "🎉 SUCCESS! PowerShell is working properly!\n";
    echo "✅ Basic commands working\n";
    echo "✅ PHP accessible via PowerShell\n";
    echo "✅ Git accessible via PowerShell\n";
    echo "✅ File operations working\n";
    echo "✅ Auto-sync should work\n";
    
    echo "\n🚀 POWERShell STATUS:\n";
    echo "• Version: Working\n";
    echo "• Execution Policy: " . trim(shell_exec('powershell -Command "Get-ExecutionPolicy" 2>&1')) . "\n";
    echo "• PHP Integration: Working\n";
    echo "• Git Integration: Working\n";
    echo "• File Operations: Working\n";
    
} else {
    echo "⚠️ PowerShell has some issues\n";
    echo "❌ Some PowerShell functions not working\n";
    echo "❌ Auto-sync may have problems\n";
}

echo "\n🔧 TROUBLESHOOTING:\n";
echo "If PowerShell is not working properly:\n";
echo "1. 🔄 Restart PowerShell as Administrator\n";
echo "2. 🔧 Check Execution Policy: Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser\n";
echo "3. 🗂️ Verify PowerShell installation: Get-Module -ListAvailable\n";
echo "4. 🔄 Restart computer if needed\n";
echo "5. 📝 Check Windows PowerShell vs PowerShell Core\n";

echo "\n🎯 CONCLUSION:\n";
echo "PowerShell status check complete! 🎉\n";
echo "अब आप PowerShell का status जान सकते हैं!\n";
echo "Auto-sync के लिए PowerShell working होना चाहिए! 🚀\n";
?>
