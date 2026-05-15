<?php
$dir = new RecursiveDirectoryIterator('app');
$iter = new RecursiveIteratorIterator($dir);
$errors = [];
foreach ($iter as $f) {
    if ($f->getExtension() === 'php') {
        $path = $f->getPathname();
        $output = [];
        $code = 0;
        exec('C:\\xampp\\php\\php.exe -l ' . escapeshellarg($path) . ' 2>&1', $output, $code);
        $result = implode("\n", $output);
        if (strpos($result, 'No syntax errors') === false) {
            $errors[] = $result;
        }
    }
}
echo count($errors) . " files with errors:\n";
foreach ($errors as $e) {
    echo $e . "\n";
}
