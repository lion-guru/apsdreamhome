<?php
/**
 * Fix UTF-8 mojibake in PHP files
 * Common patterns: UTF-8 bytes misinterpreted as Windows-1252
 */

$replacements = [
    // Euro sign
    'â‚¬' => '€',
    
    // Smart quotes
    'â€œ' => '"',
    'â€' => '"',
    'â€˜' => '\'',
    'â€™' => '\'',
    
    // Dashes
    'â€" ' => '—',
    'â€"' => '–',
    
    // Ellipsis
    'â€¦' => '…',
    
    // Bullet
    'â€¢' => '•',
    
    // Copyright/registered
    'â€¢' => '©',
    'â€œ' => '®',
    
    // Trademark
    'â"¢' => '™',
    
    // Degree
    'Â°' => '°',
    
    // Plus/minus
    'Â±' => '±',
    
    // Multiplication
    'Ã—' => '×',
    
    // Division
    'Ã·' => '÷',
    
    // Fractions
    'Â½' => '½',
    'Â¼' => '¼',
    'Â¾' => '¾',
    
    // Accented characters (common)
    'Ã¡' => 'á',
    'Ã©' => 'é',
    'Ã­' => 'í',
    'Ã³' => 'ó',
    'Ãº' => 'ú',
    'Ã±' => 'ñ',
    'Ã¼' => 'ü',
    'Ã¶' => 'ö',
    'Ã¤' => 'ä',
    'Ã§' => 'ç',
    'Ã' => 'à',
    'Ã¨' => 'è',
    'Ã¬' => 'ì',
    'Ã²' => 'ò',
    'Ã¹' => 'ù',
    'Ã±' => 'ñ',
    
    // Uppercase
    'Ã' => 'Á',
    'Ã‰' => 'É',
    'Ã' => 'Í',
    'Ã"'=> 'Ó',
    'Ãš' => 'Ú',
    'Ã"'=> 'Ó',
    
    // Fix the specific "â€" pattern (em dash often appears as â€")
    'â€' => '—',
    
    // The "â€" without space (en dash)
    'â€"' => '–',
];

// Get all PHP files in views and app directories
$directories = [
    'app/views',
    'app/Http/Controllers',
    'app/Services',
    'app/Models',
];

$files = [];
foreach ($directories as $dir) {
    $fullPath = "C:\\xampp\\htdocs\\apsdreamhome\\$dir";
    if (is_dir($fullPath)) {
        $files = array_merge($files, glob($fullPath . '\\*.php'));
        $files = array_merge($files, glob($fullPath . '\\**\\*.php'));
    }
}

echo "Found " . count($files) . " PHP files to check\n";

$fixed = 0;
$totalReplacements = 0;

foreach ($files as $file) {
    // Skip vendor, _archive, .history, node_modules
    if (strpos($file, 'vendor') !== false || 
        strpos($file, '_archive') !== false || 
        strpos($file, '.history') !== false ||
        strpos($file, 'node_modules') !== false) {
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $fixed++;
        $totalReplacements += substr_count($originalContent, $search);
        echo "Fixed: $file\n";
    }
}

echo "\nFixed $fixed files with $totalReplacements total replacements\n";