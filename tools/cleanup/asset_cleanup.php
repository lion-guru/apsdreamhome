<?php
/**
 * Asset Cleanup Script for APS Dream Home
 * Removes duplicate JavaScript/CSS files and optimizes frontend assets
 */

echo "=== APS Dream Home Asset Cleanup ===\n\n";

// Define duplicate file mappings
$duplicates = [
    'jquery' => [
        'primary' => 'assets/js/jquery.min.js',
        'duplicates' => [
            'jquery_archive/jquery.min.js',
            'jquery_archive/jquery-3.2.1.min.js'
        ]
    ],
    'bootstrap' => [
        'primary' => 'assets/js/bootstrap.min.js',
        'duplicates' => []
    ],
    'font-awesome' => [
        'primary' => 'assets/css/font-awesome.min.css',
        'duplicates' => []
    ]
];

// Remove duplicate files
$removedFiles = [];
$totalSpaceSaved = 0;

foreach ($duplicates as $library => $config) {
    if (!empty($config['duplicates'])) {
        foreach ($config['duplicates'] as $duplicate) {
            $duplicatePath = __DIR__ . "/../$duplicate";
            
            if (file_exists($duplicatePath)) {
                $fileSize = filesize($duplicatePath);
                if (unlink($duplicatePath)) {
                    $removedFiles[] = $duplicate;
                    $totalSpaceSaved += $fileSize;
                    echo "✓ Removed duplicate: $duplicate (" . formatBytes($fileSize) . ")\n";
                } else {
                    echo "✗ Failed to remove: $duplicate\n";
                }
            } else {
                echo "- File not found: $duplicate\n";
            }
        }
    }
}

// Create modern build configuration
$viteConfig = "<?php
/**
 * Vite Configuration for APS Dream Home
 */

return [
    'build' => [
        'outDir' => 'dist',
        'assetsDir' => 'assets',
        'rollupOptions' => [
            'input' => [
                'main' => 'src/js/app.js',
                'style' => 'src/css/style.css'
            ],
            'output' => [
                'manualChunks' => [
                    'vendor' => ['jquery', 'bootstrap'],
                    'utils' => ['font-awesome']
                ]
            ]
        ]
    ],
    'server' => [
        'port' => 3000,
        'proxy' => [
            '/api' => 'http://localhost:8080'
        ]
    ]
];
";

file_put_contents(__DIR__ . '/../vite.config.php', $viteConfig);
echo "✓ Created modern build configuration\n";

// Generate cleanup report
$report = "
# Asset Cleanup Report

## Removed Files
" . count($removedFiles) . " duplicate files removed

## Space Saved
" . formatBytes($totalSpaceSaved) . "

## Files Removed
" . implode("\n", array_map(fn($f) => "- $f", $removedFiles)) . "

## Next Steps
1. Update HTML templates to use primary asset files
2. Implement Vite build system
3. Test all pages for broken asset references
4. Configure CDN for production
";

file_put_contents(__DIR__ . '/../asset-cleanup-report.md', $report);
echo "✓ Generated cleanup report\n";

echo "\n=== Cleanup Summary ===\n";
echo "Files removed: " . count($removedFiles) . "\n";
echo "Space saved: " . formatBytes($totalSpaceSaved) . "\n";
echo "Report saved: asset-cleanup-report.md\n";
echo "\nNext: Update templates to use primary asset paths\n";

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>
