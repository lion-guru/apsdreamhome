<?php
/**
 * Check if doc table references are protected by try/catch
 */
$refs = [
    'business_documents', 'customer_documents', 'employee_documents',
    'farmer_documents', 'user_documents', 'property_documents',
];

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
$allFiles = [];
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

foreach ($refs as $table) {
    $protected = 0; $unprotected = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$table`?/i";
    foreach ($allFiles as $path => $content) {
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (!preg_match($pattern, $line)) continue;
            $inTry = false;
            for ($j = max(0, $i - 15); $j < $i; $j++) {
                if (preg_match('/try\s*\{/', $lines[$j])) { $inTry = true; break; }
            }
            if ($inTry) $protected++; else $unprotected++;
        }
    }
    echo "$table: $protected protected, $unprotected UNPROTECTED\n";
}?>