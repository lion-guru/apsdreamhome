<?php
/**
 * Find all code references to entity document tables
 * Need to update these AFTER migration
 */
$refs = [
    'business_documents' => [],
    'customer_documents' => [],
    'employee_documents' => [],
    'farmer_documents' => [],
    'user_documents' => [],
    'property_documents' => [],
];

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $content = file_get_contents($f->getPathname());
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        foreach ($refs as $table => &$matches) {
            if (preg_match("/\b(FROM|JOIN|INTO|UPDATE)\s+`?$table`?/i", $line)) {
                $matches[] = basename($f->getPathname()) . ":" . ($i + 1);
            }
        }
    }
}

foreach ($refs as $table => $files) {
    if (empty($files)) continue;
    echo "$table (" . count($files) . " refs):\n";
    foreach ($files as $f) echo "  $f\n";
    echo "\n";
}?>