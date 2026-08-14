<?php
$file = $argv[1] ?? null;

if (!$file || !file_exists($file)) {
    echo "Usage: php fix_characters.php <file_path>\n";
    exit(1);
}

$content = file_get_contents($file);

$replacements = [
    'Ã¢â€šÂ¹' => 'â‚¹',
    'Ã¢â‚¬â„¢' => "'",
    'Ã¢â‚¬Å“' => '"',
    'Ã¢â‚¬Â�' => '"',
    'Ã¢â‚¬â€�' => 'â€”',
    'Ã¢â‚¬Â¢' => 'â€¢',
    'Ã¢â‚¬â€œ' => 'â€“',
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents($file, $content);
echo "Fixed characters in $file\n";?>