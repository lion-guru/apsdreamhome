<?php
$directory = __DIR__ . '/../../';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

echo "Starting syntax check...\n";
$count = 0;
$errors = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        // Skip vendor and node_modules
        if (strpos($file->getPathname(), 'vendor') !== false || strpos($file->getPathname(), 'node_modules') !== false) {
            continue;
        }

        $output = [];
        $returnVar = 0;
        exec("php -l \"" . $file->getPathname() . "\"", $output, $returnVar);

        if ($returnVar !== 0) {
            echo "Syntax error in: " . $file->getPathname() . "\n";
            echo implode("\n", $output) . "\n";
            $errors++;
        }
        $count++;
        if ($count % 100 === 0) {
            echo "Checked $count files...\n";
        }
    }
}

echo "Syntax check complete. Checked $count files. Found $errors errors.\n";
