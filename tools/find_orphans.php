<?php
/**
 * Find orphaned view files — exist on disk but never referenced
 * by any controller render(), view(), or require/include statement.
 */

$viewsDir = __DIR__ . '/../app/views';
$controllersDir = __DIR__ . '/../app/Http/Controllers';
$routesFile = __DIR__ . '/../routes/web.php';
$routesAdminFile = __DIR__ . '/../routes/admin_routes.php';

// --- Step 1: Collect all view files on disk ---
$allFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $relPath = str_replace([$viewsDir . '\\', $viewsDir . '/'], '', $file->getPathname());
        $relPath = str_replace('\\', '/', $relPath);
        $allFiles[$relPath] = filesize($file->getPathname());
    }
}
echo "Total view files on disk: " . count($allFiles) . "\n\n";

// --- Step 2: Collect all referenced view paths ---
$referenced = [];

// 2a. From controllers: render('path')
$pattern1 = "/render\s*\(\s*'([^']+)'\s*[\),]/";
$pattern2 = '/render\s*\(\s*"([^"]+)"\s*[\),]/';
$pattern3 = "/view\s*\(\s*'([^']+)'\s*[\),]/";
$pattern4 = '/view\s*\(\s*"([^"]+)"\s*[\),]/';

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersDir));
foreach ($iter as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        foreach ([$pattern1, $pattern2, $pattern3, $pattern4] as $pat) {
            if (preg_match_all($pat, $content, $m)) {
                foreach ($m[1] as $path) {
                    if (strpos($path, '$') !== false) continue; // skip dynamic
                    $referenced[$path] = true;
                }
            }
        }
    }
}

// 2b. From routes: require __DIR__ . '/../app/views/...'
foreach ([$routesFile, $routesAdminFile] as $rf) {
    if (!file_exists($rf)) continue;
    $content = file_get_contents($rf);
    preg_match_all("/require(_once)?\s*__DIR__\s*\.\s*'\/\.\.\/app\/views\/([^']+)'/", $content, $m);
    foreach ($m[2] as $path) {
        $referenced[$path] = true;
    }
}

// 2c. From controllers: __DIR__ . '/../../../views/...' includes
$iterC = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersDir));
foreach ($iterC as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    preg_match_all("/__DIR__\s*\.\s*'([^']+)'/", $content, $m);
    foreach ($m[1] as $relPath) {
        // Resolve relative to the controller file
        $fullPath = realpath(dirname($file->getPathname()) . '/' . $relPath);
        if ($fullPath && strpos($fullPath, realpath($viewsDir)) === 0) {
            $rel = str_replace(realpath($viewsDir) . '\\', '', $fullPath);
            $rel = str_replace('\\', '/', $rel);
            $rel = ltrim($rel, '/');
            if (isset($allFiles[$rel])) {
                $referenced[$rel] = true;
            }
        }
    }
}

// 2d. VIEW_PATH / APP_PATH references in controllers
$iterC2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersDir));
foreach ($iterC2 as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    // VIEW_PATH . '/some/path.php' or VIEW_PATH . '/some/path'
    preg_match_all("/(VIEW_PATH|APP_PATH)\s*\.\s*'([^']+)'/", $content, $m);
    foreach ($m[2] as $path) {
        $path = ltrim($path, '/');
        // Strip leading 'views/' or '/views/' if present
        if (strpos($path, 'views/') === 0) $path = substr($path, 6);
        if (substr($path, -4) !== '.php') $path .= '.php';
        $referenced[$path] = true;
    }
    // Also __DIR__ . '/../views/...' (one level up from Controllers dir)
    preg_match_all("/__DIR__\s*\.\s*'\/\.\.\/views\/([^']+)'/", $content, $m2);
    foreach ($m2[1] as $path) {
        $path = ltrim($path, '/');
        $referenced[$path] = true;
    }
}

// 2e. From views: include/require statements referencing other views
// Look for patterns like include __DIR__ . '/../layouts/header.php' which means layouts/header.php
// Also APP_PATH . '/views/...
$iter2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$viewDirLen = strlen($viewsDir);
foreach ($iter2 as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    
    // Pattern: __DIR__ . '/some/path.php' relative to the current view file
    preg_match_all("/__DIR__\s*\.\s*'([^']+)'/", $content, $m);
    foreach ($m[1] as $relPath) {
        $resolved = resolvePath(dirname($file->getPathname()), $relPath, $viewsDir);
        if ($resolved) {
            $referenced[$resolved] = true;
        }
    }
    
    // Pattern: APP_PATH . '/views/some/path.php'
    preg_match_all("/(APP_PATH|BASE_PATH)\s*\.\s*'\/views\/([^']+)'/", $content, $m2);
    foreach ($m2[2] as $path) {
        $referenced[$path] = true;
    }
    
    // Pattern: APP_PATH . '/views/...path...'
    // This catches paths without leading /views/ prefix - but let's see
    
    // Check for direct relative path includes like include '../layouts/header.php';
    preg_match_all("/(require|include)(_once)?\s*\(?\s*'((\.\.\/)[^']+\.php)'\s*\)?\s*;/", $content, $m3);
    foreach ($m3[3] as $relPath) {
        $resolved = resolvePath(dirname($file->getPathname()), $relPath, $viewsDir);
        if ($resolved) {
            $referenced[$resolved] = true;
        }
    }
    
    // Same with double quotes
    preg_match_all("/(require|include)(_once)?\s*\(?\s*\"((\.\.\/)[^\"]+\.php)\"\s*\)?\s*;/", $content, $m4);
    foreach ($m4[3] as $relPath) {
        $resolved = resolvePath(dirname($file->getPathname()), $relPath, $viewsDir);
        if ($resolved) {
            $referenced[$resolved] = true;
        }
    }
}

// --- Step 3: Map referenced render paths to actual file paths ---
// render('admin/dashboard') → admin/dashboard.php
// require 'admin/colonies/index.php' → admin/colonies/index.php (already has .php)
$referencedFiles = [];
foreach ($referenced as $path => $_) {
    $path = ltrim($path, '/');
    // Check if the path already ends in .php (as in route requires)
    if (substr($path, -4) === '.php') {
        if (isset($allFiles[$path])) {
            $referencedFiles[$path] = true;
            continue;
        }
    }
    // Could be path.php or path/index.php
    $candidates = [$path . '.php', $path . '/index.php'];
    foreach ($candidates as $c) {
        if (isset($allFiles[$c])) {
            $referencedFiles[$c] = true;
        }
    }
}

// --- Step 4: Also add all files that are included by other view files
// These would show up in the __DIR__ resolution above, but also check direct references
// like 'layouts/header.php' in include statements
$iter3 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
foreach ($iter3 as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    // include 'layouts/header.php' (relative to views dir or current file)
    preg_match_all("/(require|include)(_once)?\s+['\"]([^'\"]+\.php)['\"].*;/", $content, $m);
    foreach ($m[3] as $path) {
        $path = str_replace('\\', '/', $path);
        if (isset($allFiles[$path])) {
            $referencedFiles[$path] = true;
        }
    }
    // include 'path' without .php? (e.g. include 'sidebar' which would mean sidebar.php)
    preg_match_all("/(require|include)(_once)?\s+['\"]([^'\"]+)['\"]\s*;/", $content, $m2);
    foreach ($m2[3] as $path) {
        $path = str_replace('\\', '/', $path);
        if (isset($allFiles[$path])) {
            $referencedFiles[$path] = true;
        }
        if (isset($allFiles[$path . '.php'])) {
            $referencedFiles[$path . '.php'] = true;
        }
    }
}

// Also add files referenced via __DIR__ (from the earlier resolution)
// Redo __DIR__ resolution more carefully
$iter4 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
foreach ($iter4 as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    // require_once __DIR__ . '/../layouts/header.php' -> this resolves to a file
    preg_match_all("/__DIR__\s*\.\s*['\"]([^'\"]+)['\"]/", $content, $m);
    foreach ($m[1] as $relPath) {
        $fullPath = realpath(dirname($file->getPathname()) . '/' . $relPath);
        if ($fullPath && strpos($fullPath, realpath($viewsDir)) === 0) {
            $rel = str_replace(realpath($viewsDir) . '\\', '', $fullPath);
            $rel = str_replace('\\', '/', $rel);
            $rel = ltrim($rel, '/');
            if (isset($allFiles[$rel])) {
                $referencedFiles[$rel] = true;
            }
        }
    }
}

echo "Total referenced view paths: " . count($referencedFiles) . "\n";

// --- Step 5: Find orphans ---
$orphans = array_diff_key($allFiles, $referencedFiles);
echo "Orphaned view files: " . count($orphans) . "\n\n";

// Sort by path
ksort($orphans);

echo "=== ORPHANED VIEW FILES ===\n";
foreach ($orphans as $path => $size) {
    printf("%-80s %s bytes\n", $path, number_format($size));
}

echo "\n=== SUMMARY ===\n";
echo "Total on disk: " . count($allFiles) . "\n";
echo "Total referenced: " . count($referencedFiles) . "\n";
echo "Total orphaned: " . count($orphans) . "\n";

function resolvePath($baseDir, $relativePath, $viewsDir) {
    $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    $baseDir = rtrim($baseDir, '/\\');
    
    // Resolve the path
    $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
    $stack = explode(DIRECTORY_SEPARATOR, $baseDir);
    
    foreach ($parts as $part) {
        if ($part === '..') {
            array_pop($stack);
        } elseif ($part === '.' || $part === '') {
            continue;
        } else {
            $stack[] = $part;
        }
    }
    
    $fullPath = implode(DIRECTORY_SEPARATOR, $stack);
    $viewsDirReal = realpath($viewsDir);
    if (!$viewsDirReal) return null;
    
    $fullPathReal = realpath($fullPath);
    if (!$fullPathReal) return null;
    
    if (strpos($fullPathReal, $viewsDirReal) !== 0) return null;
    
    $rel = substr($fullPathReal, strlen($viewsDirReal) + 1);
    return str_replace('\\', '/', $rel);
}
