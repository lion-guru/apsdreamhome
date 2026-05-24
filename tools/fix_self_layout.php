<?php
$baseDir = __DIR__ . '/../app/views/admin';
$fixed = 0;
$errors = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
foreach ($it as $f) {
    if ($f->getExtension() !== 'php') continue;
    $orig = file_get_contents($f->getPathname());
    $c = $orig;
    if (strpos($c, 'ob_start') === false) continue;
    if (strpos($c, 'layouts/admin.php') === false) continue;
    // Remove ob_start() line and opening PHP block
    $c = preg_replace('~^<\?php\s*\n\$page_title\s*=\s*\$page_title\s*\?\?.*?;\s*\n\$page_heading\s*=\s*\$page_heading\s*\?\?.*?;\s*\nob_start\s*\(\)\s*;\s*\n\?>\s*~s', '', $c);
    $c = preg_replace('~^<\?php\s*\nob_start\s*\(\)\s*;\s*\n\?>\s*~s', '', $c);
    // Remove trailing layout include
    $c = preg_replace('~\n\s*\?>\s*\n\s*<\?php\s*\n\s*\$content\s*=\s*ob_get_clean\s*\(\s*\)\s*;\s*\n\s*require_once\s+__DIR__\s*\.\s*["\x27]\.\.\/layouts\/admin\.php["\x27]\s*;\s*\n\s*\?>\s*$~s', '', $c);
    $c = preg_replace('~\n\s*<\?php\s*\n\s*\$content\s*=\s*ob_get_clean\s*\(\s*\)\s*;\s*\n\s*require_once\s+__DIR__\s*\.\s*["\x27]\.\.\/layouts\/admin\.php["\x27]\s*;\s*\n\s*\?>\s*$~s', '', $c);
    $c = preg_replace('~require_once\s+__DIR__\s*\.\s*["\x27].*layouts/admin\.php["\x27]\s*;~s', '', $c);
    // Clean up any leftover ob_get_clean patterns
    $c = preg_replace('~\$content\s*=\s*ob_get_clean\s*\(\s*\)\s*;~', '', $c);
    $c = preg_replace('~ob_start\s*\(\s*\)\s*;~', '', $c);
    if ($c !== $orig) {
        file_put_contents($f->getPathname(), $c);
        echo "FIXED: " . str_replace($baseDir . '/', '', $f->getPathname()) . "\n";
        $fixed++;
    }
}
echo "\nFixed: $fixed files\n";
echo "Errors: $errors files\n";
