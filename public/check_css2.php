<?php
$files = glob(__DIR__ . '/assets/css/*.css');
$files = array_merge($files, glob(__DIR__ . '/assets/css/consolidated/*.css'));
foreach ($files as $f) {
    $content = file_get_contents($f);
    if (preg_match_all('/(html|body)[^{]*{[^}]*height:\s*100(%|vh)/si', $content, $matches)) {
        echo basename($f) . ":\n";
        foreach ($matches[0] as $match) {
            echo trim($match) . "\n\n";
        }
    }
}
