<?php
/**
 * Fix Header/Footer Includes
 * Replaces references to renamed header/footer files with the correct path
 */

$directory = __DIR__ . '/../app/views';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

$replacements = [
    // Includes/Partials/Layout -> Layouts
    "include __DIR__ . '/../includes/header.php'" => "include __DIR__ . '/../layouts/header.php'",
    "include __DIR__ . '/../includes/footer.php'" => "include __DIR__ . '/../layouts/footer.php'",
    "include __DIR__ . '/../partials/header.php'" => "include __DIR__ . '/../layouts/header.php'",
    "include __DIR__ . '/../partials/footer.php'" => "include __DIR__ . '/../layouts/footer.php'",
    "include __DIR__ . '/../layout/header.php'" => "include __DIR__ . '/../layouts/header.php'",
    "include __DIR__ . '/../layout/footer.php'" => "include __DIR__ . '/../layouts/footer.php'",
    
    "require __DIR__ . '/../includes/header.php'" => "require __DIR__ . '/../layouts/header.php'",
    "require __DIR__ . '/../includes/footer.php'" => "require __DIR__ . '/../layouts/footer.php'",
    "require_once __DIR__ . '/../includes/header.php'" => "require_once __DIR__ . '/../layouts/header.php'",
    "require_once __DIR__ . '/../includes/footer.php'" => "require_once __DIR__ . '/../layouts/footer.php'",
    
    // Relative paths
    "include '../includes/header.php'" => "include '../layouts/header.php'",
    "include '../includes/footer.php'" => "include '../layouts/footer.php'",
    
    // Legacy Unified Header
    "include __DIR__ . '/../layouts/header_unified.php'" => "include __DIR__ . '/../layouts/header.php'",
    "require_once __DIR__ . '/../layouts/header_unified.php'" => "require_once __DIR__ . '/../layouts/header.php'",
];

echo "Scanning $directory for header/footer include fixes...\n";

$count = 0;
foreach ($files as $file) {
    if ($file->isDir()) continue;
    if ($file->getExtension() !== 'php') continue;
    if (strpos($file->getPathname(), '.bak') !== false) continue;

    $content = file_get_contents($file->getPathname());
    $originalContent = $content;
    $modified = false;

    foreach ($replacements as $search => $replace) {
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $replace, $content);
            $modified = true;
        }
    }

    if ($modified) {
        file_put_contents($file->getPathname(), $content);
        echo "Fixed: " . $file->getPathname() . "\n";
        $count++;
    }
}

echo "Fixed $count files.\n";
?>
