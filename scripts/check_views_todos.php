<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app\views';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$found = 0;
foreach ($files as $file) {
    if ($file->isFile() && ($file->getExtension() === 'php' || $file->getExtension() === 'phtml')) {
        $content = file_get_contents($file->getPathname());
        if (preg_match('/\/\/\s*TODO:\s*Add proper error handling with try-catch blocks/i', $content)) {
            $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
            echo "Found in: $relativePath\n";
            $found++;
        }
    }
}
echo "Total found: $found\n";