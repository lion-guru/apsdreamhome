<?php
/**
 * Script to fix admin layout consistency
 * Converts admin pages to use unified layout system
 */

$adminDir = 'app/views/admin';
$filesToConvert = [
    'customers/index.php',
    'leads/index.php', 
    'properties/index.php',
    'projects/index.php',
    'users/index.php',
    'settings/index.php',
    'reports/index.php',
];

echo "=== FIXING ADMIN LAYOUT CONSISTENCY ===\n\n";

foreach ($filesToConvert as $file) {
    $filePath = $adminDir . '/' . $file;
    
    if (!file_exists($filePath)) {
        echo "Skipping $file - file not found\n";
        continue;
    }
    
    echo "Processing $file...\n";
    
    $content = file_get_contents($filePath);
    
    // Check if file already uses layout
    if (strpos($content, '$layout') !== false || strpos($content, 'unified.php') !== false) {
        echo "  Already using layout system - skipping\n";
        continue;
    }
    
    // Check if file has standalone HTML
    if (strpos($content, '<!DOCTYPE html>') !== false || strpos($content, '<html') !== false) {
        echo "  Has standalone HTML - needs conversion\n";
        
        // Extract the main content (remove HTML structure)
        $content = preg_replace('/<!DOCTYPE html>.*?<body>/s', '', $content);
        $content = preg_replace('/<\/body>.*?<\/html>/s', '', $content);
        $content = preg_replace('/<head>.*?<\/head>/s', '', $content);
        
        // Add layout directive
        $layoutDirective = '<?php $layout = "admin/layouts/unified"; $active_page = "' . basename($file, '.php') . '"; ?>' . "\n";
        $content = $layoutDirective . $content;
        
        // Remove old includes
        $content = preg_replace('/<\?php include.*?layouts\/header\.php.*?\?>/', '', $content);
        $content = preg_replace('/<\?php include.*?layouts\/footer\.php.*?\?>/', '', $content);
        
        file_put_contents($filePath, $content);
        echo "  ✅ Converted to use unified layout\n";
    } else {
        echo "  No standalone HTML - adding layout directive\n";
        
        // Add layout directive at the top
        $layoutDirective = '<?php $layout = "admin/layouts/unified"; $active_page = "' . basename($file, '.php') . '"; ?>' . "\n";
        $content = $layoutDirective . $content;
        
        // Remove old includes
        $content = preg_replace('/<\?php include.*?layouts\/header\.php.*?\?>/', '', $content);
        $content = preg_replace('/<\?php include.*?layouts\/footer\.php.*?\?>/', '', $content);
        
        file_put_contents($filePath, $content);
        echo "  ✅ Added layout directive\n";
    }
}

echo "\n=== CONVERSION COMPLETE ===\n";
echo "Review the converted files and test the changes.\n";
?>