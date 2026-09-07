<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app\Http\Controllers';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (stripos($content, 'review') !== false || stripos($content, 'tenantSignup') !== false) {
            echo $file->getPathname() . "\n";
        }
    }
}