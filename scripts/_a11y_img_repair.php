<?php
// Repair pass: for every img tag in app/views, scan the full tag with a
// PHP-aware scanner. If my inserted front alt duplicates another alt later
// in the same tag, drop the front one. If the tag has no alt at all, insert
// a keyword-based alt or empty alt.
$root = 'C:/xampp/htdocs/apsdreamhome/app/views';
$keywords = [
    'screenshot' => 'Screenshot', 'logo' => 'Logo', 'banner' => 'Banner', 'hero' => 'Hero image',
    'qr' => 'QR code', 'receipt' => 'Receipt photo', 'map' => 'Map', 'gallery' => 'Gallery image',
    'profile' => 'Profile photo', 'avatar' => 'Avatar', 'property' => 'Property photo',
    'plot' => 'Plot photo', 'colony' => 'Colony image', 'building' => 'Building image',
    'villa' => 'Villa image', 'testimonial' => 'Testimonial photo', 'author' => 'Author photo',
    'video' => 'Video thumbnail', 'icon' => 'Icon', 'thumbnail' => 'Thumbnail',
    'flyer' => 'Flyer', 'poster' => 'Poster', 'event' => 'Event image',
];

function findTagEnd(string $s, int $start): int {
    $inPhp = false;
    $len = strlen($s);
    for ($i = $start; $i < $len; $i++) {
        $c = $s[$i];
        if (!$inPhp) {
            if ($c === '<' && substr($s, $i, 2) === '<?') { $inPhp = true; continue; }
            if ($c === '>') return $i;
        } else {
            if ($c === '?' && substr($s, $i, 2) === '?>') { $inPhp = false; $i++; continue; }
        }
    }
    return $len - 1;
}

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$fixed = 0; $files = 0;
foreach ($it as $file) {
    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') continue;
    $s = file_get_contents($file);
    $out = '';
    $pos = 0;
    $changed = false;
    while (($p = stripos($s, '<img', $pos)) !== false) {
        $out .= substr($s, $pos, $p - $pos);
        $end = findTagEnd($s, $p);
        $tag = substr($s, $p, $end - $p + 1);
        $lower = strtolower($tag);
        $altCount = preg_match_all('/\balt\s*=/i', $tag);
        $firstIsFront = preg_match('/^<img\s+alt="[^"]*"/', $tag);
        if ($altCount >= 2 && $firstIsFront) {
            $tag = preg_replace('/^<img\s+alt="[^"]*"/', '<img', $tag, 1);
            $changed = true; $fixed++;
        } elseif ($altCount === 0) {
            $alt = '';
            foreach ($keywords as $k => $label) {
                if (strpos($lower, $k) !== false) { $alt = $label; break; }
            }
            $tag = preg_replace('/^<img\b/i', '<img alt="' . $alt . '"', $tag, 1);
            $changed = true; $fixed++;
        }
        $out .= $tag;
        $pos = $end + 1;
    }
    $out .= substr($s, $pos);
    if ($changed) {
        $files++;
        file_put_contents($file, $out);
    }
}
echo "files fixed: $files, images adjusted: $fixed\n";
