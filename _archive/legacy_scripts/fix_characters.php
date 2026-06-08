<?php
$file = $argv[1] ?? null;

if (!$file || !file_exists($file)) {
    echo "Usage: php fix_characters.php <file_path>\n";
    exit(1);
}

$content = file_get_contents($file);

$replacements = [
    'â‚¹' => '₹',
    'â€™' => "'",
    'â€œ' => '"',
    'â€' => '"',
    'â€”' => '—',
    'â€¢' => '•',
    'â€“' => '–',
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents($file, $content);
echo "Fixed characters in $file\n";
