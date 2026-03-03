<?php
/**
 * APS Dream Home - Final IDE Error Verification
 * Check and fix any remaining syntax issues
 */

echo "🔍 Final IDE Error Verification...\n";
echo "=================================\n\n";

$filesToCheck = [
    'app/views/careers/index.php',
    'app/views/faq/index.php', 
    'app/views/testimonials/index.php',
    'config/ultimate_performance_optimization.php'
];

$allGood = true;

foreach ($filesToCheck as $file) {
    echo "🔍 Checking: $file\n";
    
    if (file_exists($file)) {
        // Check PHP syntax
        $output = [];
        $returnCode = 0;
        exec("php -l \"$file\" 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "✅ PHP Syntax: OK\n";
        } else {
            echo "❌ PHP Syntax Error:\n";
            foreach ($output as $line) {
                echo "   $line\n";
            }
            $allGood = false;
        }
        
        // Check for common Blade syntax issues
        $content = file_get_contents($file);
        
        // Check for onclick handlers without proper quotes
        if (preg_match('/onclick="[^"]*{{[^}]*}}[^"]*"/', $content)) {
            echo "⚠️  Found Blade in onclick - needs manual review\n";
            $allGood = false;
        }
        
        // Check for CSS with Blade variables
        if (preg_match('/style="[^"]*{{[^}]*}}[^"]*"/', $content)) {
            echo "⚠️  Found Blade in CSS style - needs manual review\n";
            $allGood = false;
        }
        
        // Check for JavaScript with Blade variables
        if (preg_match('/<script[^>]*>.*?{{[^}]*}}.*?<\/script>/s', $content)) {
            echo "⚠️  Found Blade in JavaScript - needs manual review\n";
            $allGood = false;
        }
        
        if ($allGood) {
            echo "✅ File is clean\n";
        }
        
    } else {
        echo "❌ File not found: $file\n";
        $allGood = false;
    }
    echo "\n";
}

if ($allGood) {
    echo "🎉 All files are clean and ready!\n";
    echo "🚀 IDE errors might be false positives\n";
    echo "✅ Project is ready for development\n";
} else {
    echo "⚠️  Some issues found that need manual attention\n";
    echo "🔧 Please review the warnings above\n";
}

echo "\n📊 Summary:\n";
echo "Status: " . ($allGood ? "✅ READY" : "⚠️  NEEDS ATTENTION") . "\n";
echo "Files checked: " . count($filesToCheck) . "\n";
?>
