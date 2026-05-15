<?php
// Fast syntax check using token_get_all (no subprocess spawning)
$dirs = ['app/Http/Controllers', 'app/Http/Middleware', 'app/Models', 'app/Core', 'app/Services'];
$errors = [];
$checked = 0;

function checkSyntax($file) {
    $code = file_get_contents($file);
    if ($code === false) return "Cannot read file";
    // Suppress errors, use token_get_all
    $oldLevel = error_reporting(0);
    try {
        token_get_all($code, TOKEN_PARSE);
        error_reporting($oldLevel);
        return null;
    } catch (ParseError $e) {
        error_reporting($oldLevel);
        return "Line " . $e->getLine() . ": " . $e->getMessage();
    }
}

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->getExtension() === 'php') {
            $checked++;
            $err = checkSyntax($file->getPathname());
            if ($err !== null) {
                $rel = ltrim(str_replace(getcwd(), '', $file->getPathname()), '/\\');
                $errors[] = "$rel\n  => $err";
            }
        }
    }
}

echo "Checked: $checked files\n";
if (empty($errors)) {
    echo "ALL CLEAN - No syntax errors!\n";
} else {
    echo "ERRORS (" . count($errors) . "):\n\n" . implode("\n\n", $errors) . "\n";
}
