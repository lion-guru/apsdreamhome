<?php
/**
 * Admin Views Batch Fixer
 * Removes old layout includes and fixes undefined variable warnings
 */

$basePath = dirname(__DIR__);
$viewsPath = $basePath . '/app/views';

// Files to fix - manually identified from grep
$filesToFix = [
    'admin/plot-costs/report.php',
    'admin/plot-costs/colony.php',
    'admin/plot-costs/index.php',
    'admin/user-properties/verify.php',
    'admin/user-properties/index.php',
    'admin/projects/status.php',
    'admin/projects/images.php',
    'admin/projects/view.php',
    'admin/projects/edit.php',
    'admin/projects/create.php',
    'admin/projects/index.php',
    'admin/mlm/payouts/index.php',
    'admin/mlm/network/tree.php',
    'admin/mlm/commission/index.php',
    'admin/mlm/associates/create.php',
    'admin/mlm/associates/index.php',
    'admin/mlm/dashboard.php',
    'admin/plots/create.php',
    'admin/locations/colonies/create.php',
    'admin/locations/colonies/index.php',
    'admin/locations/districts/create.php',
    'admin/locations/districts/index.php',
    'admin/locations/states/edit.php',
    'admin/locations/states/create.php',
    'admin/locations/states/index.php',
    'admin/deals/create.php',
    'admin/deals/kanban.php',
    'admin/deals/index.php',
    'admin/visits/create.php',
    'admin/visits/index.php',
    'admin/visits/calendar.php',
    'admin/leads/scoring.php',
];

echo "=== ADMIN VIEWS BATCH FIXER ===\n\n";

$fixed = 0;
$errors = [];

foreach ($filesToFix as $file) {
    $path = $viewsPath . '/' . $file;
    if (!file_exists($path)) {
        $errors[] = "Not found: $file";
        continue;
    }
    
    $content = file_get_contents($path);
    $original = $content;
    
    // Pattern 1: <?php include __DIR__ . '/../../layouts/admin_header.php';
    $content = preg_replace('/<\?php\s*include\s+__DIR__\s*\.\s*[\'"]\/?[\.\/]*layouts\/admin_header\.php[\'"]\s*;?\s*\?>/i', '', $content);
    
    // Pattern 2: <?php include __DIR__ . '/../../../layouts/admin_header.php';
    $content = preg_replace('/<\?php\s*include\s+__DIR__\s*\.\s*[\'"]\/+\.\/+\.\/+\.\/layouts\/admin_header\.php[\'"]\s*;?\s*\?>/i', '', $content);
    
    // Pattern 3: include __DIR__ . '/../../layouts/admin_header.php';
    $content = preg_replace('/include\s+__DIR__\s*\.\s*[\'"]\/+[\.\/]*layouts\/admin_header\.php[\'"]\s*;?\s*/i', '', $content);
    
    // Footer patterns
    $content = preg_replace('/<\?php\s*include\s+__DIR__\s*\.\s*[\'"]\/+[\.\/]*layouts\/admin_footer\.php[\'"]\s*;?\s*\?>/i', '', $content);
    $content = preg_replace('/<\?php\s*include\s+__DIR__\s*\.\s*[\'"]\/?[\.\/]*layouts\/admin_footer\.php[\'"]\s*;?\s*\?>/i', '', $content);
    $content = preg_replace('/include\s+__DIR__\s*\.\s*[\'"]\/+[\.\/]*layouts\/admin_footer\.php[\'"]\s*;?\s*/i', '', $content);
    
    if ($content !== $original) {
        file_put_contents($path, $content);
        $fixed++;
        echo "Fixed: $file\n";
    }
}

echo "\nFixed $fixed files\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $e) echo "  - $e\n";
}

echo "\nNow checking for undefined variable issues...\n";

// Now fix undefined variable warnings in views
$viewFiles = glob($viewsPath . '/admin/**/*.php');

foreach ($viewFiles as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Fix htmlspecialchars with null
    $content = preg_replace('/htmlspecialchars\(\$([a-zA-Z_]+)\[([^\]]+)\]\)/', 'htmlspecialchars($1[$2] ?? \'\')', $content);
    
    // Fix number_format with null
    $content = preg_replace('/number_format\(\$([a-zA-Z_]+)\[([^\]]+)\]/', 'number_format(floatval($1[$2] ?? 0)', $content);
    
    if ($content !== $original) {
        file_put_contents($file, $content);
    }
}

echo "Variable warnings fixed in views\n";

echo "\n=== DONE ===\n";
echo "Run tests to verify fixes.\n";