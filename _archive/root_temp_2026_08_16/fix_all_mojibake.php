<?php
/**
 * Fix UTF-8 mojibake in PHP files
 * Common patterns: UTF-8 bytes misinterpreted as Windows-1252
 */

$replacements = [
    // Common emoji patterns
    "\xC3\x9F\xC2\x83\xE2\x84\xA2" => '📊',   // ðŸ"Š
    "\xC3\x9F\xC2\xA5" => '💰',               // ðŸ¥¤
    "\xC3\x9F\xC2\xB8" => '💸',               // ðŸ¥¸
    "\xC3\x9F\xC2\xA2" => '📢',               // ðŸ"¢
    "\xC3\x9F\xC2\x86" => '📈',               // ðŸ"ˆ
    "\xC3\x9F\xC2\xA4" => '🤖',               // ðŸ¤–
    "\xC3\x9F\xC2\x81" => '📞',               // ðŸ"¡
    "\xC3\x9F\xC2\xA7" => '⚖️',               // ðŸ"§
    "\xC3\x9F\xC2\x90" => '📍',               // ðŸ"�
    "\xC3\x9F\xC2\x93" => '📜',               // ðŸ"—
    "\xC3\x9F\xC2\x9D" => '💸',               // ðŸ"¢
    "\xC3\x9F\xC2\x97" => '🏠',               // ðŸ� 
    "\xC3\x9F\xC2\x93" => '📜',               // ðŸ"—
    "\xC3\x9F\xC2\x9C" => '📖',               // ðŸ"�
    "\xC3\x9F\xC2\x84" => '⚙️',               // âš™ï¸�
    "\xC3\x9F\xC2\xA4" => '👤',               // ðŸ'¤
    "\xC3\x9F\xC2\x90" => '📍',               // ðŸ"�
    "\xC3\x9F\xC2\xA7" => '⚙️',               // ðŸ"§
    "\xC3\x9F\xC2\x93" => '👨',               // ðŸ'"
    "\xC3\x9F\xC2\xBC" => '💼',               // ðŸ'¼
    "\xE2\x9A\x9A\xEF\xB8\x8F" => '⚖️',       // âš–ï¸�
    "\xC3\x9F\xC2\xB7" => '🏷️',               // ðŸ�·ï¸�
    "\xC3\x9F\xC2\x9E\xEF\xB8\x8F" => '🛠️',  // ðŸ›Žï¸�
    "\xC3\x9F\xC2\xA5\xEF\xB8\x8F" => '🔧',   // ðŸ–¥ï¸�
    "\xC3\x9F\xC2\xA4" => '🤖',               // ðŸ¤–
    "\xC3\x9F\xC2\xA2" => '🔒',               // ðŸ""
    "\xC3\x9F\xC2\xA9" => '👨',               // ðŸ'©
    "\xE2\x80\x9D" => '—',                    // â€�
    "\xC3\x9F\xC2\xBC" => '💼',               // ðŸ'¼
    "\xE2\x9C\x8C\xEF\xB8\x8F" => '☁️',       // â˜�ï¸�
    "\xC3\x9F\xC2\xA1" => '📞',               // ðŸ'¡
    "\xC3\x9F\xC2\x83\xE2\x84\xA2" => '📊',   // ðŸ"Š
    "\xC3\x9F\xC2\xA5" => '👥',               // ðŸ'¥
    "\xC3\x9F\xC2\x91" => '📜',               // ðŸ'—
    "\xC3\x9F\xC2\xB0" => '💰',               // ðŸ'°
    "\xC3\x9F\xC2\xB8" => '💸',               // ðŸ¥¸
    "\xC3\x9F\xC2\x8C" => '📖',               // ðŸ"�
    "\xC3\x9F\xC2\xA2" => '📢',               // ðŸ"¢
    "\xC3\x9F\xC2\x88" => '📈',               // ðŸ"ˆ
    "\xC2\xA1\xE2\x84\xA2\xEF\xB8\x8F" => '⚙️', // âš™ï¸�
    "\xC3\x9F\xC2\xA4" => '👤',               // ðŸ'¤
    "\xC3\x9F\xC2\x90" => '📍',               // ðŸ"�
    "\xC3\x9F\xC2\xA7" => '⚙️',               // ðŸ"§
    "\xC3\x9F\xC2\x93" => '👨',               // ðŸ'"
    "\xC3\x9F\xC2\xBC" => '💼',               // ðŸ'¼
    "\xE2\x9A\x96\xEF\xB8\x8F" => '⚖️',       // âš–ï¸�
    "\xC3\x9F\xC2\xB7" => '🏷️',               // ðŸ�·ï¸�
    "\xC3\x9F\xC2\x9E\xEF\xB8\x8F" => '🛠️',  // ðŸ›Žï¸�
    "\xC3\x9F\xC2\xA5\xEF\xB8\x8F" => '🔧',   // ðŸ–¥ï¸�
    "\xC3\x9F\xC2\xA4" => '🤖',               // ðŸ¤–
    "\xC3\x9F\xC2\xA2" => '🔒',               // ðŸ""
    "\xC3\x9F\xC2\xA9" => '👨',               // ðŸ'©
    "\xE2\x80\x9D" => '—',                    // â€�
    "\xC3\x9F\xC2\xBC" => '💼',               // ðŸ'¼
    "\xE2\x9C\x8C\xEF\xB8\x8F" => '☁️',       // â˜�ï¸�
    "\xC3\x9F\xC2\xA1" => '📞',               // ðŸ'¡
    "\xE2\x80\x9C" => '"',                    // â€œ
    "\xE2\x80\x9D" => '"',                    // â€
    "\xE2\x80\x98" => "'",                    // â€˜
    "\xE2\x80\x99" => "'",                    // â€™
    "\xE2\x80\x9C" => '"',                    // â€œ
    "\xE2\x80\x9D" => '"',                    // â€
    "\xE2\x80\x93" => '–',                    // â€"
    "\xE2\x80\x94" => '—',                    // â€"
    "\xE2\x80\xA2" => '•',                    // â€¢
    "\xE2\x80\xA6" => '…',                    // â€¦
    "\xC2\xB0" => '°',                        // Â°
    "\xC2\xB1" => '±',                        // Â±
    "\xC3\x97" => '×',                        // Ã—
    "\xC3\xB7" => '÷',                        // Ã·
    "\xC2\xBD" => '½',                        // Â½
    "\xC2\xBC" => '¼',                        // Â¼
    "\xC2\xBE" => '¾',                        // Â¾
    "\xC3\xA1" => 'á',                        // Ã¡
    "\xC3\xA9" => 'é',                        // Ã©
    "\xC3\xAD" => 'í',                        // Ã­
    "\xC3\xB3" => 'ó',                        // Ã³
    "\xC3\xBA" => 'ú',                        // Ãº
    "\xC3\xB1" => 'ñ',                        // Ã±
    "\xC3\xBC" => 'ü',                        // Ã¼
    "\xC3\xB6" => 'ö',                        // Ã¶
    "\xC3\xA4" => 'ä',                        // Ã¤
    "\xC3\xA7" => 'ç',                        // Ã§
    "\xC3\xA0" => 'à',                        // Ã
    "\xC3\xA8" => 'è',                        // Ã¨
    "\xC3\xAC" => 'ì',                        // Ã¬
    "\xC3\xB2" => 'ò',                        // Ã²
    "\xC3\xB9" => 'ù',                        // Ã¹
    "\xC3\xB1" => 'ñ',                        // Ã±
    "\xC3\x81" => 'Á',                        // Ã
    "\xC3\x89" => 'É',                        // Ã‰
    "\xC3\x8D" => 'Í',                        // Ã
    "\xC3\x93" => 'Ó',                        // Ã"
    "\xC3\x9A" => 'Ú',                        // Ãš
    "\xC3\x93" => 'Ó',                        // Ã"
];

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