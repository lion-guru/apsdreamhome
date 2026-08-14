<?php
/**
 * Convert PHP file to UTF-8 encoding
 */

$file = $argv[1] ?? null;

if (!$file || !file_exists($file)) {
    echo "Usage: php convert_utf8.php <file_path>\n";
    exit(1);
}

// Read file content
$content = file_get_contents($file);

// Detect and convert encoding if needed
$encoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1, Windows-1252');

if ($encoding !== 'UTF-8') {
    echo "Converting $file from $encoding to UTF-8\n";
    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
    
    // Add BOM only if file had it
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = "\xEF\xBB\xBF" . mb_substr($content, 3);
    }
    
    file_put_contents($file, $content);
    echo "âœ… $file converted to UTF-8\n";
} else {
    echo "â„¹ï¸� $file already UTF-8\n";
}?>