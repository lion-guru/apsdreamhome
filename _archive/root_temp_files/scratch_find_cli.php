<?php
function findFiles($dir, $pattern) {
    $results = [];
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iter as $file) {
        if (!$file->isDir() && preg_match($pattern, $file->getFilename())) {
            $results[] = $file->getPathname();
        }
    }
    return $results;
}

$files = findFiles('C:\\xampp\\htdocs\\apsdreamhome', '/(unified|login|register)/i');
file_put_contents('C:\\xampp\\htdocs\\apsdreamhome\\scratch_find_out.txt', implode("\n", $files));
