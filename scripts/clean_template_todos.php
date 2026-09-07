<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$cleaned = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        // Remove the specific template TODO pattern
        $newContent = preg_replace('/\/\/\s*TODO:\s*Add proper error handling with try-catch blocks\s*\n?/i', '', $content);
        
        if ($newContent !== $content) {
            file_put_contents($file->getPathname(), $newContent);
            $cleaned++;
            $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
            echo "Cleaned: $relativePath\n";
        }
    }
}

echo "\nTotal cleaned: $cleaned\n";