<?php
// Batch fix htmlspecialchars() null deprecation warnings
// Run from project root: php fix_htmlspecialchars.php

$viewDir = __DIR__ . '/app/views';
$filesFixed = 0;
$totalReplacements = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewDir));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    
    $content = file_get_contents($file->getPathname());
    if ($content === false) continue;
    
    $original = $content;
    $fileReplacements = 0;
    
    // Pattern 1: htmlspecialchars($var['key']) or $var['k1']['k2'] -> add ?? ''
    $content = preg_replace_callback(
        '/htmlspecialchars\((\$[a-zA-Z_]\w*(?:\[[^\]]+\])+)\)(?!\s*\?\?)/',
        function($m) use (&$fileReplacements) {
            $fileReplacements++;
            return "htmlspecialchars({$m[1]} ?? '')";
        },
        $content
    );
    
    // Pattern 2: htmlspecialchars($variable) bare var -> add ?? ''
    $content = preg_replace_callback(
        '/htmlspecialchars\((\$(?!_)[a-zA-Z_]\w*)\)(?!\s*\?\?)/',
        function($m) use (&$fileReplacements) {
            $fileReplacements++;
            return "htmlspecialchars({$m[1]} ?? '')";
        },
        $content
    );
    
    // Pattern 3: htmlspecialchars(ucfirst($var)) -> add ?? ''
    $content = preg_replace_callback(
        '/htmlspecialchars\(ucfirst\((\$[a-zA-Z_]\w*)\)\)(?!\s*\?\?)/',
        function($m) use (&$fileReplacements) {
            $fileReplacements++;
            return "htmlspecialchars(ucfirst({$m[1]} ?? ''))";
        },
        $content
    );
    
    // Pattern 4: htmlspecialchars(mb_substr($var['key'], -> add ?? ''
    $content = preg_replace_callback(
        '/htmlspecialchars\(mb_substr\((\$[a-zA-Z_]\w*(?:\[[^\]]+\])+)\s*,/',
        function($m) use (&$fileReplacements) {
            $fileReplacements++;
            return "htmlspecialchars(mb_substr({$m[1]} ?? '',";
        },
        $content
    );
    
    // Pattern 5: htmlspecialchars(substr($var, -> add ?? ''
    $content = preg_replace_callback(
        '/htmlspecialchars\(substr\((\$[a-zA-Z_]\w*)\s*,/',
        function($m) use (&$fileReplacements) {
            $fileReplacements++;
            return "htmlspecialchars(substr({$m[1]} ?? '',";
        },
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $filesFixed++;
        $totalReplacements += $fileReplacements;
    }
}

echo "DONE: Fixed $filesFixed files, $totalReplacements total replacements\n";