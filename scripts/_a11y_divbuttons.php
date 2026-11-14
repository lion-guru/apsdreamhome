<?php
// Insert role="button" tabindex="0" into <div ... onclick=...> elements,
// skipping backdrops/overlays/modal-close layers (they must stay out of the tab order).
$root = 'C:/xampp/htdocs/apsdreamhome/app/views';
$dry = in_array('--dry', $argv, true);

function findTagEnd2(string $s, int $start): int {
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

$skipTokens = [
    'aps-cp-modal-overlay', 'modal-overlay', 'modalOverlay', 'quickview-backdrop',
    'mobile-drawer-overlay', 'mobileOverlay', 'createModal', 'overlay', 'backdrop',
];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$changed = 0; $files = 0; $skipped = 0;
$skippedLog = [];
foreach ($it as $file) {
    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') continue;
    $s = file_get_contents($file);
    $out = '';
    $pos = 0;
    $fileChanged = false;
    while (($p = stripos($s, '<div', $pos)) !== false) {
        $out .= substr($s, $pos, $p - $pos);
        $end = findTagEnd2($s, $p);
        $tag = substr($s, $p, $end - $p + 1);
        $lower = strtolower($tag);
        if (!preg_match('/\bonclick\s*=/i', $tag)) { $out .= $tag; $pos = $end + 1; continue; }
        if (preg_match('/\brole\s*=|\btabindex\s*=/i', $tag)) { $out .= $tag; $pos = $end + 1; continue; }
        // backdrop / overlay exclusion
        $isBackdrop = false;
        foreach ($skipTokens as $tok) {
            if (stripos($lower, $tok) !== false) { $isBackdrop = true; break; }
        }
        if (!$isBackdrop && strpos($lower, 'position:fixed') !== false && strpos($lower, 'inset:0') !== false) {
            $isBackdrop = true;
        }
        if ($isBackdrop) {
            $skipped++;
            if (count($skippedLog) < 20) $skippedLog[] = str_replace('app/views/', '', $file->getPathname()) . ' :: ' . substr(preg_replace('/\s+/', ' ', $tag), 0, 100);
            $out .= $tag; $pos = $end + 1; continue;
        }
        $tag = preg_replace('/^<div\b/i', '<div role="button" tabindex="0"', $tag, 1);
        $out .= $tag;
        $pos = $end + 1;
        $changed++;
        $fileChanged = true;
    }
    $out .= substr($s, $pos);
    if ($changed && $fileChanged) {
        $files++;
        if (!$dry) file_put_contents($file, $out);
    }
}
echo ($dry ? "[DRY] " : ""), "files changed: $files, divs labelled: $changed, backdrops skipped: $skipped\n";
foreach ($skippedLog as $l) echo "  skipped: $l\n";
