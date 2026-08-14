<?php
/**
 * Count code references to each voice AI table
 */
$tables = [
    'ai_calling_agents', 'ai_call_scripts', 'ai_call_sessions',
    'ai_calling_schedule', 'ai_call_extracted_leads', 'ai_call_logs',
];

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[] = $f->getPathname();
    }
}

foreach ($tables as $t) {
    $count = 0;
    $files = [];
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path) {
        $content = file_get_contents($path);
        $matches = preg_match_all($pattern, $content);
        if ($matches) {
            $count += $matches;
            $files[] = basename($path) . " ($matches)";
        }
    }
    echo "$t: $count refs\n";
    foreach ($files as $f) echo "  - $f\n";
    echo "\n";
}?>