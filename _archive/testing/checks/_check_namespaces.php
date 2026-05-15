<?php
// Check service files missing proper namespace declarations
$dirs = ['app/Services', 'app/Models'];
$missingNS = [];
$wrongNS = [];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->getExtension() !== 'php') continue;
        
        $content = file_get_contents($file->getPathname());
        $rel = ltrim(str_replace(getcwd(), '', $file->getPathname()), '/\\');
        
        // Expected namespace based on path
        $relPath = ltrim(str_replace(['app/', 'app\\'], '', $rel), '/\\');
        $dirPart = dirname($relPath);
        $dirPart = str_replace(['/', '\\'], '\\', $dirPart);
        
        // Build expected namespace
        if ($dirPart === '.') {
            $expectedNS = 'App\\' . rtrim(str_replace(['/', '\\'], '\\', $relPath === '.' ? '' : dirname($relPath)), '\\');
        } else {
            $expectedNS = 'App\\' . $dirPart;
        }
        $expectedNS = rtrim($expectedNS, '\\');
        
        // Check if file has namespace
        if (!preg_match('/^namespace\s+([A-Za-z\\\\]+)\s*;/m', $content, $nsMatch)) {
            $missingNS[] = $rel;
        } elseif ($nsMatch[1] !== $expectedNS) {
            $wrongNS[] = ['file' => $rel, 'actual' => $nsMatch[1], 'expected' => $expectedNS];
        }
    }
}

echo "=== MISSING NAMESPACE (" . count($missingNS) . " files) ===\n";
foreach ($missingNS as $f) {
    echo "  $f\n";
}

echo "\n=== WRONG NAMESPACE (" . count($wrongNS) . " files) ===\n";
foreach (array_slice($wrongNS, 0, 20) as $w) {
    echo "  File: " . $w['file'] . "\n  Expected: " . $w['expected'] . "\n  Actual: " . $w['actual'] . "\n\n";
}
if (count($wrongNS) > 20) echo "  ... and " . (count($wrongNS)-20) . " more\n";
