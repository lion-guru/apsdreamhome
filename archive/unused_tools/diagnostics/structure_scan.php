<?php

/**
 * Project Structure Deep Scan Tool
 * Analyzes directory structure, identifies duplicates, and checks for MVC compliance.
 */

define('ROOT_PATH', dirname(__DIR__, 2));

function scanDirectory($dir, $depth = 0, $maxDepth = 3)
{
    if ($depth > $maxDepth) return [];

    $items = [];
    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $path = $dir . '/' . $file;
        $isDir = is_dir($path);

        $items[] = [
            'name' => $file,
            'path' => $path,
            'type' => $isDir ? 'dir' : 'file',
            'depth' => $depth,
            'children' => $isDir ? scanDirectory($path, $depth + 1, $maxDepth) : []
        ];
    }

    return $items;
}

function analyzeStructure($structure)
{
    $report = [];
    $issues = [];
    $recommendations = [];

    // Check for root admin folder (Anti-pattern in MVC)
    if (file_exists(ROOT_PATH . '/admin')) {
        $issues[] = "CRITICAL: 'admin' folder found in root. This violates MVC structure. Admin logic should be in app/Http/Controllers/Admin and app/views/admin.";
        $recommendations[] = "Delete root 'admin' folder and migrate logic to Controllers/Views.";
    }

    // Check for app/models vs app/Models (PSR-4)
    $app_contents = scandir(ROOT_PATH . '/app');
    if (!in_array('Models', $app_contents)) {
        if (in_array('models', $app_contents)) {
            $issues[] = "WARNING: 'app/models' is lowercase. PSR-4 recommends 'app/Models' for namespace App\\Models.";
            $recommendations[] = "Rename 'app/models' to 'app/Models'.";
        }
    } else {
        $report[] = "Models: app/Models found (PSR-4 compliant).";
    }

    // Check for app/middleware vs app/Middleware
    if (!in_array('Middleware', $app_contents)) {
        if (in_array('middleware', $app_contents)) {
            $issues[] = "WARNING: 'app/middleware' is lowercase. PSR-4 recommends 'app/Middleware'.";
            $recommendations[] = "Rename 'app/middleware' to 'app/Middleware'.";
        }
    } else {
        $report[] = "Middleware: app/Middleware found (PSR-4 compliant).";
    }

    // Check for app/services vs app/Services
    if (!in_array('Services', $app_contents)) {
        if (in_array('services', $app_contents)) {
            $issues[] = "WARNING: 'app/services' is lowercase. PSR-4 recommends 'app/Services'.";
            $recommendations[] = "Rename 'app/services' to 'app/Services'.";
        }
    } else {
        $report[] = "Services: app/Services found (PSR-4 compliant).";
    }

    // Check for app/core vs app/Core
    if (!in_array('Core', $app_contents)) {
        if (in_array('core', $app_contents)) {
            $issues[] = "WARNING: 'app/core' is lowercase. PSR-4 recommends 'app/Core'.";
            $recommendations[] = "Rename 'app/core' to 'app/Core'.";
        }
    } else {
        $report[] = "Core: app/Core found (PSR-4 compliant).";
    }

    // Check for public/index.php
    if (!file_exists(ROOT_PATH . '/public/index.php')) {
        $issues[] = "CRITICAL: 'public/index.php' missing. This is the entry point for MVC apps.";
    } else {
        $report[] = "MVC Entry Point: public/index.php found.";
    }

    // Check for Controllers
    if (!is_dir(ROOT_PATH . '/app/Http/Controllers')) {
        $issues[] = "CRITICAL: 'app/Http/Controllers' missing. MVC Controller logic not found.";
    } else {
        $report[] = "Controllers: app/Http/Controllers found.";
    }

    // Check for Legacy code
    if (is_dir(ROOT_PATH . '/app/services/Legacy') || is_dir(ROOT_PATH . '/app/core/Legacy')) {
        $issues[] = "INFO: Legacy code detected in app/services/Legacy or app/core/Legacy.";
        $recommendations[] = "Plan refactoring of legacy code to modern Services/Controllers.";
    }

    return ['report' => $report, 'issues' => $issues, 'recommendations' => $recommendations];
}

echo "Starting Deep Scan...\n";
$structure = scanDirectory(ROOT_PATH);
$analysis = analyzeStructure($structure);

echo "\n--- Analysis Report ---\n";
foreach ($analysis['report'] as $item) {
    echo "[OK] $item\n";
}

echo "\n--- Issues Found ---\n";
foreach ($analysis['issues'] as $item) {
    echo "[!] $item\n";
}

echo "\n--- Recommendations ---\n";
foreach ($analysis['recommendations'] as $item) {
    echo "[*] $item\n";
}

echo "\nScan Complete.\n";
