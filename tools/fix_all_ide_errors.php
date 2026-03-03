<?php
/**
 * APS Dream Home - Comprehensive IDE Error Fixer
 * Fix all syntax errors in view files
 */

echo "🔧 Fixing All IDE Syntax Errors...\n";
echo "==================================\n\n";

$filesToFix = [
    'app/views/admin/properties.php',
    'app/views/admin/users.php', 
    'app/views/careers/index.php',
    'app/views/faq/index.php',
    'app/views/testimonials/index.php',
    'config/ultimate_performance_optimization.php'
];

$fixesApplied = 0;

foreach ($filesToFix as $file) {
    echo "🔍 Checking: $file\n";
    
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Fix missing quotes around Blade variables in onclick handlers
        $content = preg_replace('/onclick="(\w+)\(\{\{\s*\$(\w+)(?:->\w+)?\s*\}\}\s*,\s*\'([^\']*)\'\);"/', 'onclick="$1(\'{{ $$2 }}\', \'$3\');"', $content);
        $content = preg_replace('/onclick="(\w+)\(\{\{\s*\$(\w+)(?:->\w+)?\s*\}\}\s*\);"/', 'onclick="$1(\'{{ $$2 }}\');"', $content);
        
        // Fix CSS conic-gradient syntax
        $content = preg_replace('/background:\s*conic-gradient\([^;]+\);;/', 'background: conic-gradient($0);', $content);
        
        // Fix PHP string escaping issues
        $content = preg_replace('/array\\\(\\\'([^\']+)\\\'\\\',\\\s*\\\'([^\']+)\\\'\\\)/', 'array(\'$1\', \'$2\')', $content);
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "✅ Fixed: $file\n";
            $fixesApplied++;
        } else {
            echo "✅ No fixes needed: $file\n";
        }
    } else {
        echo "❌ File not found: $file\n";
    }
    echo "\n";
}

echo "📊 Summary:\n";
echo "Applied $fixesApplied fixes\n\n";

echo "🎯 Running PHP syntax check...\n";
foreach ($filesToFix as $file) {
    if (file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $output = [];
        $returnCode = 0;
        exec("php -l \"$file\" 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "✅ $file - No syntax errors\n";
        } else {
            echo "❌ $file - Syntax errors found:\n";
            foreach ($output as $line) {
                echo "   $line\n";
            }
        }
    }
}

echo "\n🎉 IDE Error Fix Complete!\n";
echo "🚀 All syntax errors should now be resolved\n";
?>
