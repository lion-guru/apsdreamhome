<?php
$web = file_get_contents('routes/web.php');
$controllers = [];
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Http/Controllers'));
foreach ($dir as $file) {
    if ($file->isDir()) continue;
    if ($file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    $name = $file->getBasename('.php');
    if ($name === 'BaseController' || $name === 'Controller') continue;
    
    // Check if controller name (or a common variation) is in web.php
    if (strpos($web, $name) === false) {
        $controllers[] = $path;
    }
}

echo "Total un-routed controllers: " . count($controllers) . "\n";
foreach ($controllers as $c) {
    echo "  $c\n";
}
