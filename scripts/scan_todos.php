<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$todos = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match_all('/\/\/\s*(TODO|FIXME|HACK|XXX|BUG):\s*(.+)/i', $content, $matches)) {
            foreach ($matches[0] as $i => $match) {
                $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
                $todos[] = $relativePath . ': ' . $match;
            }
        }
    }
}

echo "TODO/FIXME found: " . count($todos) . "\n\n";
foreach ($todos as $t) {
    echo $t . "\n";
}