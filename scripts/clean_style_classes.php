#!/usr/bin/env php
<?php
/**
 * Script: clean_style_classes.php (v2 — SAFE)
 * Removes dead style-XXXXX classes from view files.
 * Uses only exact token replacement — NEVER eats newlines.
 *
 * Usage: php scripts/clean_style_classes.php [--dry-run]
 */

$isDryRun = in_array('--dry-run', $argv);

$viewsDir = __DIR__ . '/../app/views';
$filesScanned = 0;
$filesModified = 0;
$totalRemoved = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;

    $content = file_get_contents($file->getPathname());
    if ($content === false) continue;

    $filesScanned++;

    // Step 1: Count occurrences of style-XXXXX tokens
    preg_match_all('/\bstyle-\d+\b/', $content, $matches);
    $count = count($matches[0]);

    if ($count === 0) continue;

    // Step 2: Remove the style-XXXXX token (just the token, nothing else)
    $newContent = preg_replace('/\bstyle-\d+\b/', '', $content);

    // Step 3: Clean up resulting double spaces within class attributes only
    // Only collapse spaces/tabs (NOT newlines!)
    $newContent = preg_replace('/class="[ ]{2,}/', 'class="', $newContent);
    $newContent = preg_replace('/class=\'[ ]{2,}/', 'class=\'', $newContent);
    // Clean trailing space before closing quote: class="foo " → class="foo"
    $newContent = preg_replace('/class="([^"]*?) +"/', 'class="$1"', $newContent);
    $newContent = preg_replace('/class=\'([^\']*?) +\'/', 'class=\'$1\'', $newContent);
    // Remove empty class attributes
    $newContent = preg_replace('/ class=""\s*/', ' ', $newContent);
    $newContent = preg_replace('/ class=\'\'\s*/', ' ', $newContent);

    if ($newContent !== $content) {
        $filesModified++;
        $totalRemoved += $count;
        $relPath = str_replace($viewsDir . DIRECTORY_SEPARATOR, '', $file->getPathname());

        if ($isDryRun) {
            echo "[DRY-RUN] $relPath: $count classes\n";
        } else {
            file_put_contents($file->getPathname(), $newContent);
            echo "[FIXED] $relPath: $count classes removed\n";
        }
    }
}

echo "\n=== Results ===\n";
echo "Files scanned: $filesScanned\n";
echo "Files modified: $filesModified\n";
echo "Total style-XXXXX removed: $totalRemoved\n";
echo "Mode: " . ($isDryRun ? 'DRY-RUN (no changes)' : 'LIVE') . "\n";
