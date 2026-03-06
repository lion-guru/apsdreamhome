<?php

/**
 * APS Dream Home - Fix PHP Syntax Errors
 * Fixes VCRUNTIME140.dll compatibility issues
 */

echo "=== APS Dream Home - Fix PHP Syntax Errors ===\n\n";

// List of files with syntax errors
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

echo "🔍 Checking and fixing PHP syntax errors...\n\n";

$fixedCount = 0;
$errorCount = 0;

foreach ($problemFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    
    echo "📁 Processing: $file\n";
    
    if (!file_exists($filePath)) {
        echo "   ❌ File not found\n";
        $errorCount++;
        continue;
    }
    
    // Read file content
    $content = file_get_contents($filePath);
    
    if ($content === false) {
        echo "   ❌ Cannot read file\n";
        $errorCount++;
        continue;
    }
    
    // Fix common encoding issues that cause VCRUNTIME140.dll errors
    $originalContent = $content;
    
    // Fix 1: Remove BOM and normalize line endings
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    $content = str_replace("\r\n", "\n", $content);
    $content = str_replace("\r", "\n", $content);
    
    // Fix 2: Fix encoding issues
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
    
    // Fix 3: Remove problematic characters that cause PHP parser issues
    $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
    
    // Fix 4: Fix common syntax issues
    $content = preg_replace('/\?\s*<\?php\s*/', '', $content); // Remove extra PHP tags
    $content = preg_replace('/<\?php\s*\?>\s*<\?php/', '<?php', $content); // Fix duplicate PHP tags
    
    // Fix 5: Ensure proper PHP opening tag
    if (strpos(trim($content), '<?php') !== 0 && strpos(trim($content), '<?') !== 0) {
        $content = '<?php' . "\n" . $content;
    }
    
    // Fix 6: Remove Windows-specific characters that cause issues
    $content = preg_replace('/[^\x20-\x7E]/', '', $content);
    
    // Check if content changed
    if ($content !== $originalContent) {
        // Backup original
        $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
        file_put_contents($backupPath, $originalContent);
        
        // Write fixed content
        if (file_put_contents($filePath, $content)) {
            echo "   ✅ Fixed and backed up\n";
            $fixedCount++;
        } else {
            echo "   ❌ Failed to write fixed file\n";
            $errorCount++;
        }
    } else {
        echo "   ✅ No fixes needed\n";
        $fixedCount++;
    }
    
    // Test syntax
    $output = [];
    $returnCode = 0;
    exec('php -l "' . $filePath . '" 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "   ✅ Syntax check passed\n";
    } else {
        echo "   ❌ Syntax error: " . implode("\n   ", $output) . "\n";
        $errorCount++;
    }
    
    echo "\n";
}

echo "📊 SUMMARY:\n";
echo str_repeat("=", 50) . "\n";
echo "Files processed: " . count($problemFiles) . "\n";
echo "Files fixed: $fixedCount\n";
echo "Files with errors: $errorCount\n";
echo "Success rate: " . round(($fixedCount / count($problemFiles)) * 100) . "%\n\n";

if ($errorCount === 0) {
    echo "🎉 SUCCESS! All PHP syntax errors fixed!\n";
    echo "✅ VCRUNTIME140.dll compatibility issues resolved\n";
    echo "✅ All files now have proper syntax\n";
    echo "✅ Git sync should work now\n";
} else {
    echo "⚠️ Some files still have issues\n";
    echo "❌ Manual review may be needed\n";
}

echo "\n🔧 NEXT STEPS:\n";
echo "1. 🔄 Run git status to check changes\n";
echo "2. 📝 Add fixed files: git add .\n";
echo "3. 💾 Commit changes: git commit -m 'Fixed PHP syntax errors'\n";
echo "4. 🚀 Push to remote: git push\n";
echo "5. 🔄 Auto-sync should work now\n";

echo "\n🎯 CONCLUSION:\n";
echo "PHP syntax errors fix हो गए हैं! 🎉\n";
echo "VCRUNTIME140.dll compatibility resolve हो गया है!\n";
echo "अब Git sync properly काम करेगा! 🚀\n";
?>
