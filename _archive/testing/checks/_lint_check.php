<?php
$dirs = ['app/Http/Controllers', 'app/Http/Middleware', 'app/Models', 'app/Core', 'app/Services'];
$errors = [];
$checked = 0;
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->getExtension() === 'php') {
            $checked++;
            $out = shell_exec('php -l ' . escapeshellarg($file->getPathname()) . ' 2>&1');
            if (strpos($out, 'No syntax errors') === false) {
                $rel = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $errors[] = $rel . "\n  => " . trim($out);
            }
        }
    }
}
echo "Checked: $checked files\n";
if (empty($errors)) {
    echo "ALL CLEAN - No syntax errors found!\n";
} else {
    echo "ERRORS FOUND (" . count($errors) . "):\n\n";
    echo implode("\n\n", $errors) . "\n";
}
