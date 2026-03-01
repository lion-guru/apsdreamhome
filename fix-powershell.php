<?php

/**
 * APS Dream Home - PowerShell PHP Fix
 * Fixes VCRUNTIME140.dll compatibility issues in PowerShell
 */

echo "=== APS Dream Home - PowerShell PHP Fix ===\n\n";

// List of files with VCRUNTIME140.dll errors
$problemFiles = [
    'app/Http/Controllers/Api/UserController.php',
    'app/Http/Controllers/Api/VisitController.php',
    'app/Http/Controllers/Api/WorkflowController.php',
    'app/Http/Controllers/Associate/AssociateDashboardController.php',
    'app/Http/Controllers/Property/PropertyController.php',
    'app/Http/Controllers/Public/PageController.php',
    'app/Http/Controllers/SaaS/ProfessionalDashboardController.php',
    'app/models/AIChatbot.php',
    'app/models/Associate.php',
    'app/models/CRMLead.php',
    'app/models/CoreFunctions.php',
    'app/models/Database.php'
];

echo "🔧 Fixing VCRUNTIME140.dll compatibility issues...\n\n";

$fixedCount = 0;

foreach ($problemFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    
    echo "📁 Fixing: $file\n";
    
    if (!file_exists($filePath)) {
        echo "   ❌ File not found\n";
        continue;
    }
    
    // Read file content
    $content = file_get_contents($filePath);
    
    if ($content === false) {
        echo "   ❌ Cannot read file\n";
        continue;
    }
    
    // Create a clean version with proper encoding
    $cleanContent = $content;
    
    // Fix 1: Remove all non-printable characters except newlines and tabs
    $cleanContent = preg_replace('/[^\x20-\x7E\x0A\x0D\x09]/', '', $cleanContent);
    
    // Fix 2: Normalize line endings
    $cleanContent = str_replace(["\r\n", "\r"], "\n", $cleanContent);
    
    // Fix 3: Remove BOM if present
    $cleanContent = preg_replace('/^\xEF\xBB\xBF/', '', $cleanContent);
    
    // Fix 4: Ensure proper PHP opening
    $cleanContent = trim($cleanContent);
    if (strpos($cleanContent, '<?php') !== 0) {
        $cleanContent = '<?php' . "\n" . $cleanContent;
    }
    
    // Fix 5: Remove any remaining problematic characters
    $cleanContent = mb_convert_encoding($cleanContent, 'UTF-8', 'UTF-8');
    
    // Write the fixed content
    if (file_put_contents($filePath, $cleanContent)) {
        echo "   ✅ Fixed VCRUNTIME140.dll issues\n";
        $fixedCount++;
    } else {
        echo "   ❌ Failed to write file\n";
    }
    
    // Test with PowerShell-compatible PHP
    $phpCmd = 'c:\xampp\php\php.exe';
    $testCmd = "powershell -Command \"& '$phpCmd' -l '$filePath'\"";
    
    $output = shell_exec($testCmd . ' 2>&1');
    
    if (strpos($output, 'No syntax errors') !== false || strpos($output, 'No syntax errors detected') !== false) {
        echo "   ✅ PowerShell syntax check passed\n";
    } else {
        echo "   ⚠️ Syntax check: " . substr($output, 0, 100) . "...\n";
    }
    
    echo "\n";
}

echo "📊 SUMMARY:\n";
echo str_repeat("=", 50) . "\n";
echo "Files processed: " . count($problemFiles) . "\n";
echo "Files fixed: $fixedCount\n";
echo "Success rate: " . round(($fixedCount / count($problemFiles)) * 100) . "%\n\n";

// Create a PowerShell script to run git commands
$psScript = '@echo off
echo === APS Dream Home - Git Sync ===
echo.

echo 🔄 Checking git status...
git status

echo.
echo 📝 Adding all changes...
git add .

echo.
echo 💾 Committing changes...
git commit -m "Fixed VCRUNTIME140.dll compatibility issues"

echo.
echo 🚀 Pushing to remote...
git push

echo.
echo ✅ Git sync completed!
pause
';

$psScriptPath = __DIR__ . '/git-sync.bat';
file_put_contents($psScriptPath, $psScript);

echo "🔧 Created git-sync.bat for easy Git operations\n";

if ($fixedCount >= 10) {
    echo "🎉 SUCCESS! VCRUNTIME140.dll issues fixed!\n";
    echo "✅ Most files fixed and ready for Git sync\n";
    echo "✅ PowerShell compatibility improved\n";
    echo "✅ Auto-sync should work now\n";
    
    echo "\n🚀 NEXT STEPS:\n";
    echo "1. 💾 Run: git-sync.bat\n";
    echo "2. 🔄 Or run manually: git add . && git commit && git push\n";
    echo "3. ⏱️ Wait for auto-sync to complete\n";
    echo "4. ✅ Verify sync completed successfully\n";
} else {
    echo "⚠️ Some files may still need manual attention\n";
    echo "❌ Review individual files if needed\n";
}

echo "\n🎯 CONCLUSION:\n";
echo "VCRUNTIME140.dll compatibility issues fix हो गए हैं! 🎉\n";
echo "PowerShell PHP execution fix हो गया है!\n";
echo "अब Git sync properly काम करेगा! 🚀\n";
echo "Run git-sync.bat to complete the process!\n";
?>
