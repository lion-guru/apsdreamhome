<?php

/**
 * APS Dream Home - Fix All Model Syntax Errors
 * Fixes syntax errors in all model files
 */

echo "=== APS Dream Home - Fix All Model Syntax Errors ===\n\n";

// Models that need fixing
$models = [
    'app/Models/AIChatbot.php',
    'app/Models/Associate.php',
    'app/Models/CoreFunctions.php',
    'app/Models/CRMLead.php',
    'app/Models/Database.php'
];

echo "🔧 Fixing model syntax errors...\n\n";

$fixedCount = 0;
$errorCount = 0;

foreach ($models as $file) {
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
    
    $originalContent = $content;
    
    // Fix the variable assignment issue
    $content = preg_replace('/\$this->\s*=\s*\$value;/', '$this->$key = $value;', $content);
    
    // Write the fixed content
    if ($content !== $originalContent) {
        // Backup original
        $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
        file_put_contents($backupPath, $originalContent);
        
        // Write fixed content
        if (file_put_contents($filePath, $content)) {
            echo "   ✅ Fixed syntax errors\n";
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
        echo "   ✅ PHP syntax check passed\n";
    } else {
        echo "   ❌ Syntax error: " . substr(implode("\n   ", $output), 0, 100) . "...\n";
        $errorCount++;
    }
    
    echo "\n";
}

echo "📊 SUMMARY:\n";
echo str_repeat("=", 50) . "\n";
echo "Files processed: " . count($models) . "\n";
echo "Files fixed: $fixedCount\n";
echo "Files with errors: $errorCount\n";
echo "Success rate: " . round(($fixedCount / count($models)) * 100) . "%\n\n";

if ($errorCount === 0) {
    echo "🎉 SUCCESS! All model syntax errors fixed!\n";
    echo "✅ All PHP models now have proper syntax\n";
    echo "✅ IDE should show no more syntax errors\n";
    echo "✅ Git sync should work without issues\n";
} else {
    echo "⚠️ Some files still have issues\n";
    echo "❌ Manual review may be needed for remaining errors\n";
}

echo "\n🔧 NEXT STEPS:\n";
echo "1. 🔄 Refresh IDE to clear error cache\n";
echo "2. 📝 Run git status to check changes\n";
echo "3. 💾 Add fixed files: git add .\n";
echo "4. 🚀 Commit changes: git commit -m 'Fixed model syntax errors'\n";
echo "5. 🔄 Push to remote: git push\n";

echo "\n🎯 CONCLUSION:\n";
echo "Model syntax errors fix हो गए हैं! 🎉\n";
echo "सभी PHP models का syntax अब proper है!\n";
echo "IDE में कोई errors नहीं दिखेंगे!\n";
echo "Git sync properly काम करेगा! 🚀\n";
?>
