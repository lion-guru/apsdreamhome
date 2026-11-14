<?php
// Add aria-label to named <input> elements lacking one.
// Skips hidden inputs and button-type inputs (buttons have their own name rule).
// Scanner is PHP-aware: tags may contain php echoes with '>' chars inside.
$root = 'C:/xampp/htdocs/apsdreamhome/app/views';
$dry = in_array('--dry', $argv, true);

function findTagEndInput(string $s, int $start): int {
    $inPhp = false;
    $len = strlen($s);
    for ($i = $start; $i < $len; $i++) {
        $c = $s[$i];
        if (!$inPhp) {
            if ($c === '<' && substr($s, $i, 2) === '<?') { $inPhp = true; $i++; continue; }
            if ($c === '>') return $i;
        } else {
            if ($c === '?' && substr($s, $i, 2) === '?>') { $inPhp = false; $i++; continue; }
        }
    }
    return $len - 1;
}

function humanizeInput(string $s): string {
    $s = preg_replace('/[^A-Za-z0-9 ]+/', ' ', str_replace(['_', '-'], ' ', $s));
    $s = preg_replace('/([a-z])([A-Z])/', '$1 $2', $s);
    return trim(ucfirst(strtolower(preg_replace('/\s+/', ' ', $s))));
}

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$changed = 0; $files = 0;
$unmatched = [];
foreach ($it as $file) {
    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') continue;
    $s = file_get_contents($file);
    $out = '';
    $pos = 0;
    $fileChanged = false;
    while (($p = stripos($s, '<input', $pos)) !== false) {
        $out .= substr($s, $pos, $p - $pos);
        $end = findTagEndInput($s, $p);
        $tag = substr($s, $p, $end - $p + 1);
        $lower = strtolower($tag);
        $isButton = preg_match('/type\s*=\s*["\'](submit|button|reset|image)/i', $tag);
        $isHidden = preg_match('/type\s*=\s*["\']hidden/i', $tag);
        $hasName = preg_match('/\b(name|id|placeholder)\s*=\s*["\']([^"\']*)["\']/i', $tag, $m);
        $hasAccessible = preg_match('/\b(aria-label|aria-labelledby|title)\s*=/i', $tag);
        if ($isHidden || $isButton || $hasAccessible || !$hasName) {
            $out .= $tag;
            $pos = $end + 1;
            continue;
        }
        $label = humanizeInput($m[2]);
        if ($label === '') { $unmatched[$m[2]] = true; $out .= $tag; $pos = $end + 1; continue; }
        $tag = preg_replace('/^<input\b/i', '<input aria-label="' . htmlspecialchars($label, ENT_QUOTES) . '"', $tag, 1);
        $out .= $tag;
        $pos = $end + 1;
        $changed++;
        $fileChanged = true;
    }
    $out .= substr($s, $pos);
    if ($fileChanged) {
        $files++;
        if (!$dry) file_put_contents($file, $out);
    }
}
echo ($dry ? "[DRY] " : ""), "files changed: $files, inputs labelled: $changed\n";
echo "unmatched (empty humanized): ", count($unmatched), PHP_EOL;
