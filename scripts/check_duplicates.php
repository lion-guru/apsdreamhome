<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$methodSignatures = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
        
        // Find method signatures
        if (preg_match_all('/\b(public|protected|private)\s+(static\s+)?function\s+(\w+)\s*\(/m', $content, $matches)) {
            foreach ($matches[3] as $method) {
                $key = strtolower($method);
                if (!isset($methodSignatures[$key])) {
                    $methodSignatures[$key] = [];
                }
                $methodSignatures[$key][] = $relativePath;
            }
        }
    }
}

// Find methods with same name in many files (potential duplication)
echo "Methods appearing in 10+ files:\n";
foreach ($methodSignatures as $method => $files) {
    if (count($files) >= 10) {
        echo "$method: " . count($files) . " files\n";
        foreach ($files as $f) {
            echo "  - $f\n";
        }
        echo "\n";
    }
}

// Also check for exact duplicate code blocks (10+ lines)
echo "\n--- Checking for duplicate code blocks (20+ identical lines) ---\n";
$allContent = [];
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $lines = explode("\n", $content);
        $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
        
        // Create 20-line windows
        for ($i = 0; $i <= count($lines) - 20; $i++) {
            $block = implode("\n", array_slice($lines, $i, 20));
            $block = trim($block);
            if (strlen($block) > 200) { // Only substantial blocks
                $hash = md5($block);
                if (!isset($allContent[$hash])) {
                    $allContent[$hash] = [];
                }
                $allContent[$hash][] = "$relativePath: line " . ($i + 1);
            }
        }
    }
}

$dupes = 0;
foreach ($allContent as $hash => $locs) {
    if (count($locs) >= 3) { // Appears in 3+ files
        $dupes++;
        echo "Duplicate block (hash: $hash) in " . count($locs) . " locations:\n";
        foreach ($locs as $loc) {
            echo "  - $loc\n";
        }
        echo "\n";
        if ($dupes >= 20) break;
    }
}

echo "Total duplicate blocks found: $dupes\n";