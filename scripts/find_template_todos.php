<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$templateTodos = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        // Check for the specific template TODO pattern
        if (preg_match('/\/\/\s*TODO:\s*Add proper error handling with try-catch blocks/i', $content)) {
            $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
            $templateTodos[] = $relativePath;
        }
    }
}

echo "Files with template TODO: " . count($templateTodos) . "\n\n";
foreach ($templateTodos as $t) {
    echo $t . "\n";
}