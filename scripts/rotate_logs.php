<?php
/**
 * Log Rotation Script
 * 
 * Rotates log files that exceed the size threshold, compresses old logs,
 * and cleans up rotated copies beyond the retention count.
 * 
 * Usage:
 *   php scripts/rotate_logs.php                    # Dry-run (preview only)
 *   php scripts/rotate_logs.php --apply            # Actually rotate
 *   php scripts/rotate_logs.php --apply --max-size 10485760  # Custom threshold (10MB)
 * 
 * Schedule via Windows Task Scheduler or cron:
 *   0 0 * * * php C:\xampp\htdocs\apsdreamhome\scripts\rotate_logs.php --apply
 */

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

// --- Configuration ---
$maxSizeBytes = 5 * 1024 * 1024; // 5MB default threshold
$retentionCount = 5;             // Keep last N rotated copies per log
$apply = false;

// Parse CLI args
$args = array_slice($argv, 1);
if (in_array('--apply', $args)) {
    $apply = true;
}
if (($idx = array_search('--max-size', $args)) !== false && isset($args[$idx + 1])) {
    $maxSizeBytes = (int) $args[$idx + 1];
}

// Directories to scan
$logDirs = [
    $root . '/storage/logs',
    $root . '/logs',
];

$rotated = 0;
$deleted = 0;
$skipped = 0;
$errors = 0;
$actionLabel = $apply ? 'ROTATE' : 'DRY-RUN';

echo "=== Log Rotation ($actionLabel) ===" . PHP_EOL;
echo "Threshold: " . number_format($maxSizeBytes / 1024 / 1024, 1) . " MB" . PHP_EOL;
echo "Retention: $retentionCount rotated copies per log" . PHP_EOL;
echo str_repeat('-', 60) . PHP_EOL;

foreach ($logDirs as $dir) {
    if (!is_dir($dir)) {
        echo "[SKIP] Directory not found: $dir" . PHP_EOL;
        continue;
    }

    echo PHP_EOL . "Scanning: $dir" . PHP_EOL;

    // Rotate oversized active logs
    $files = glob($dir . '/*.log');
    foreach ($files as $file) {
        $size = filesize($file);
        $name = basename($file);

        if ($size < $maxSizeBytes) {
            echo "  [OK]   $name (" . formatSize($size) . ") — within threshold" . PHP_EOL;
            $skipped++;
            continue;
        }

        $timestamp = date('Y-m-d_His');
        $rotatedFile = $file . '.' . $timestamp . '.gz';

        if ($apply) {
            // Compress current log into rotated copy
            $content = file_get_contents($file);
            if ($content === false) {
                echo "  [ERR]  $name — failed to read" . PHP_EOL;
                $errors++;
                continue;
            }

            $gzContent = gzencode($content);
            if ($gzContent === false) {
                echo "  [ERR]  $name — failed to compress" . PHP_EOL;
                $errors++;
                continue;
            }

            if (file_put_contents($rotatedFile, $gzContent) === false) {
                echo "  [ERR]  $name — failed to write rotated copy" . PHP_EOL;
                $errors++;
                continue;
            }

            // Truncate original (preserve inode for processes writing to it)
            file_put_contents($file, '');
            echo "  [ROT]  $name (" . formatSize($size) . ") → " . basename($rotatedFile) . PHP_EOL;
            $rotated++;
        } else {
            echo "  [PLAN] $name (" . formatSize($size) . ") → " . basename($rotatedFile) . PHP_EOL;
            $rotated++;
        }
    }

    // Clean up old rotated copies beyond retention
    $rotatedFiles = glob($dir . '/*.log.*.gz');
    usort($rotatedFiles, function ($a, $b) {
        return filemtime($b) - filemtime($a); // newest first
    });

    // Group by base name
    $groups = [];
    foreach ($rotatedFiles as $rFile) {
        // Extract base name: "php_error.log.2026-06-15_000000.gz" → "php_error.log"
        if (preg_match('/^(.+\.log)\.\d{4}-\d{2}-\d{2}_\d{6}\.gz$/', basename($rFile), $m)) {
            $groups[$m[1]][] = $rFile;
        }
    }

    foreach ($groups as $baseName => $groupFiles) {
        // Sort newest first, keep only $retentionCount
        $toDelete = array_slice($groupFiles, $retentionCount);
        foreach ($toDelete as $oldFile) {
            if ($apply) {
                unlink($oldFile);
                echo "  [DEL]  " . basename($oldFile) . " (exceeded retention)" . PHP_EOL;
            } else {
                echo "  [PLAN] " . basename($oldFile) . " (exceeded retention)" . PHP_EOL;
            }
            $deleted++;
        }
    }
}

echo PHP_EOL . str_repeat('-', 60) . PHP_EOL;
echo "Summary ($actionLabel):" . PHP_EOL;
echo "  Rotated:  $rotated files" . PHP_EOL;
echo "  Deleted:  $deleted old copies" . PHP_EOL;
echo "  Skipped:  $skipped (within threshold)" . PHP_EOL;
echo "  Errors:   $errors" . PHP_EOL;
echo "Done." . PHP_EOL;

function formatSize($bytes)
{
    if ($bytes >= 1024 * 1024) {
        return round($bytes / 1024 / 1024, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}
