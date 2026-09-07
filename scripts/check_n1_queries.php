<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$n1Patterns = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
        
        // Pattern 1: Query inside foreach/for loop
        if (preg_match_all('/(foreach|for)\s*\([^)]+\)\s*\{[^}]*(query|execute|fetchAll|fetch|prepare)\s*\(/is', $content, $matches)) {
            $n1Patterns[] = "$relativePath: Query inside loop (possible N+1)";
        }
        
        // Pattern 2: ->find() or ->get() inside loop
        if (preg_match_all('/(foreach|for)\s*\([^)]+\)\s*\{[^}]*(->find|->get|->first)\s*\(/is', $content, $matches)) {
            $n1Patterns[] = "$relativePath: Model find/get inside loop (possible N+1)";
        }
        
        // Pattern 3: Multiple queries in loop - look for $stmt->execute() inside loops
        if (preg_match_all('/foreach\s*\([^)]+\$\w+\s+as\s+\$\w+\)\s*\{[^}]*\$\w+->execute\s*\(/is', $content)) {
            $n1Patterns[] = "$relativePath: Prepared statement execute inside foreach";
        }
    }
}

echo "N+1 query patterns found: " . count($n1Patterns) . "\n\n";
foreach ($n1Patterns as $p) {
    echo $p . "\n";
}