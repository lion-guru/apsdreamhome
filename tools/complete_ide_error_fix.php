<?php
/**
 * APS Dream Home - Complete IDE Error Fixer
 * Fix all Blade syntax issues in onclick handlers
 */

echo "🔧 Complete IDE Error Fix...\n";
echo "==========================\n\n";

$filesToFix = [
    'app/views/careers/index.php',
    'app/views/faq/index.php',
    'app/views/testimonials/index.php'
];

$fixesApplied = 0;

foreach ($filesToFix as $file) {
    echo "🔍 Processing: $file\n";
    
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Fix all onclick handlers with Blade variables
        $patterns = [
            // onclick="function({{ $variable['key'] }})" -> onclick="function('{{ $variable['key'] }}')"
            '/onclick="(\w+)\(\{\{\s*\$(\w+)\[\'([^\']+)\'\]\s*\}\)\s*\);"/' => 'onclick="$1(\'{{ $$2[\'$3\'] }}\');"',
            // onclick="function({{ $variable->property }})" -> onclick="function('{{ $variable->property }}')"
            '/onclick="(\w+)\(\{\{\s*\$(\w+)->(\w+)\s*\}\)\s*\);"/' => 'onclick="$1(\'{{ $$2->$3 }}\');"',
            // onclick="function({{ $variable }})" -> onclick="function('{{ $variable }}')"
            '/onclick="(\w+)\(\{\{\s*\$(\w+)\s*\}\)\s*\);"/' => 'onclick="$1(\'{{ $$2 }}\');"',
            // onclick="function({{ $variable }}, \'string\')" -> onclick="function(\'{{ $variable }}\', \'string\')"
            '/onclick="(\w+)\(\{\{\s*\$(\w+)\s*\}\)\s*,\s*\'([^\']+)\'\);"/' => 'onclick="$1(\'{{ $$2 }}\', \'$3\');"',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "✅ Fixed: $file\n";
            $fixesApplied++;
        } else {
            echo "✅ No changes needed: $file\n";
        }
    } else {
        echo "❌ File not found: $file\n";
    }
    echo "\n";
}

echo "📊 Summary:\n";
echo "Applied $fixesApplied fixes\n\n";

echo "🎯 Running final syntax check...\n";
foreach ($filesToFix as $file) {
    if (file_exists($file)) {
        $output = [];
        $returnCode = 0;
        exec("php -l \"$file\" 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "✅ $file - Syntax OK\n";
        } else {
            echo "❌ $file - Syntax Error:\n";
            foreach ($output as $line) {
                echo "   $line\n";
            }
        }
    }
}

echo "\n🎉 Complete IDE Error Fix Done!\n";
echo "🚀 All Blade syntax issues should be resolved\n";
?>
