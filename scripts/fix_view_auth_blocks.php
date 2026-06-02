<?php
/**
 * Fix 49+ admin view files that have inline auth checks.
 * Removes redundant @session_start() + header('Location:') blocks
 * since controller's requireAdmin() already handles auth.
 */

$files = [];
$dir = __DIR__ . '/../app/views/admin';

// Recursively find all PHP files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}

echo "Found " . count($files) . " PHP files in admin views.\n";

$fixed = 0;
$multiLinePattern = '// Session started by controller';
$singleLinePattern = '@@session_start();';

foreach ($files as $path) {
    $content = file_get_contents($path);
    $original = $content;

    // Pattern 1: Multi-line auth block (most files)
    if (strpos($content, $multiLinePattern) !== false) {
        $content = preg_replace(
            '/\s*\/\/ Session started by controller\s*\n\s*if\s*\(!isset\(\$_SESSION\[\'admin_id\'\]\)\s*&&\s*\(!isset\(\$_SESSION\[\'role\'\]\)\s*\|\|\s*\$_SESSION\[\'role\'\]\s*!==\s*\'admin\'\)\)\s*\{\s*header\("Location:\s*"\s*\.\s*BASE_URL\s*\.\s*"\/admin\/login"\);\s*exit\(\);\s*\}\s*\n?/',
            "\n",
            $content
        );
    }

    // Pattern 2: Single-line @@session_start() + auth block (property-features files)
    if (strpos($content, $singleLinePattern) !== false) {
        $content = preg_replace(
            '/<\?php\s+@@session_start\(\);\s*if\s*\(!isset\(\$_SESSION\[\'admin_id\'\]\)\s*&&\s*\(!isset\(\$_SESSION\[\'role\'\]\)\s*\|\|\s*\$_SESSION\[\'role\'\]\s*!==\s*\'admin\'\)\)\s*\{\s*header\("Location:\s*"\s*\.\s*BASE_URL\s*\.\s*"\/admin\/login"\);\s*exit\(\);\s*\}\s*/',
            '<?php ',
            $content
        );
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "  FIXED: " . str_replace($dir, '', $path) . "\n";
        $fixed++;
    }
}

echo "\nFixed $fixed files.\n";

// Also fix the 4 self-contained HTML views that have <html> outside layouts
$selfContained = [
    __DIR__ . '/../app/views/admin/agreements/preview.php',
    __DIR__ . '/../app/views/admin/payments/receipt.php',
];

foreach ($selfContained as $path) {
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (preg_match('/<!DOCTYPE html>/i', $content) || preg_match('/<html[^>]*>/i', $content)) {
            echo "  SELF-CONTAINED: " . basename(dirname($path)) . '/' . basename($path) . " (has <html> tags - preview/receipt, OK)\n";
        }
    }
}

echo "\nDone!\n";
