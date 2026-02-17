<?php
$directory = __DIR__ . '/../app';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$count = 0;
$errors = 0;

echo "Starting syntax check...\n";

foreach ($regex as $file) {
    $count++;
    $filePath = $file[0];
    $output = [];
    $returnVar = 0;
    exec("php -l \"$filePath\"", $output, $returnVar);
    
    if ($returnVar !== 0) {
        echo "Syntax error in: $filePath\n";
        foreach ($output as $line) {
            echo "  $line\n";
        }
        $errors++;
    }
}

echo "\nChecked $count files.\n";
if ($errors === 0) {
    echo "No syntax errors found.\n";
} else {
    echo "Found $errors files with syntax errors.\n";
}
