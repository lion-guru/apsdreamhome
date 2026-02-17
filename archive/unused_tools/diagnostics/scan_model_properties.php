<?php
require_once __DIR__ . '/../../app/core/autoload.php';

$modelsDir = __DIR__ . '/../../app/Models';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modelsDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

echo "Scanning models for property type mismatches...\n";

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());

        // Check for $hidden without type array
        if (preg_match('/protected\s+\$hidden\s*=/', $content) && !preg_match('/protected\s+array\s+\$hidden\s*=/', $content)) {
            echo "Mismatch in \$hidden: " . $file->getPathname() . "\n";
        }

        // Check for $guarded without type array
        if (preg_match('/protected\s+\$guarded\s*=/', $content) && !preg_match('/protected\s+array\s+\$guarded\s*=/', $content)) {
            echo "Mismatch in \$guarded: " . $file->getPathname() . "\n";
        }

        // Check for $fillable without type array
        if (preg_match('/protected\s+\$fillable\s*=/', $content) && !preg_match('/protected\s+array\s+\$fillable\s*=/', $content)) {
            echo "Mismatch in \$fillable: " . $file->getPathname() . "\n";
        }
    }
}
echo "Scan complete.\n";
