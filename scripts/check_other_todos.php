<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$patterns = [
    '/\/\/\s*TODO:\s*Add proper error handling with try-catch blocks/i',
    '/\/\/\s*TODO:\s*This file is large.*Consider splitting/i',
    '/\/\/\s*TODO:\s*Add input validation for all user inputs/i',
];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
                echo "Found in: $relativePath\n";
            }
        }
    }
}