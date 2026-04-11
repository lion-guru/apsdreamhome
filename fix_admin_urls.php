<?php
/**
 * Admin URL Fixer Script - Fixes hardcoded URLs in admin views
 */

$adminViewsPath = __DIR__ . '/app/views/admin';
$fixedCount = 0;
$fixedFiles = [];

function fixUrlsInFile($filePath) {
    $content = file_get_contents($filePath);
    $original = $content;
    
    // Fix 1: href="/admin/..." -> href="<?= BASE_URL ?>/admin/..."
    $content = preg_replace('/href="(\/admin\/[^"]+)"/', 'href="<?= BASE_URL ?>$1"', $content);
    
    // Fix 2: action="/admin/..." -> action="<?= BASE_URL ?>/admin/..."
    $content = preg_replace('/action="(\/admin\/[^"]+)"/', 'action="<?= BASE_URL ?>$1"', $content);
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        return true;
    }
    return false;
}

// Process all admin view files
foreach (glob($adminViewsPath . '/**/*.php') as $filePath) {
    if (fixUrlsInFile($filePath)) {
        $fixedFiles[] = basename(dirname($filePath)) . '/' . basename($filePath);
        $fixedCount++;
    }
}

echo "=== ADMIN URL FIX COMPLETE ===\n";
echo "Fixed files: $fixedCount\n";
if ($fixedCount > 0) {
    echo "\nFixed:\n";
    foreach ($fixedFiles as $f) echo "  - $f\n";
}
