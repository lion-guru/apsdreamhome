<?php
// 1) <img> without alt: keyword-based alt or alt="" (decorative).
// 2) <th> without scope: scope="col" inside thead/tfoot/no-section, scope="row" inside tbody.
$root = 'C:/xampp/htdocs/apsdreamhome/app/views';
$dry = in_array('--dry', $argv, true);

$keywords = [
    'screenshot' => 'Screenshot',
    'logo' => 'Logo',
    'banner' => 'Banner',
    'hero' => 'Hero image',
    'qr' => 'QR code',
    'receipt' => 'Receipt photo',
    'map' => 'Map',
    'gallery' => 'Gallery image',
    'profile' => 'Profile photo',
    'avatar' => 'Avatar',
    'property' => 'Property photo',
    'plot' => 'Plot photo',
    'colony' => 'Colony image',
    'building' => 'Building image',
    'villa' => 'Villa image',
    'testimonial' => 'Testimonial photo',
    'author' => 'Author photo',
    'video' => 'Video thumbnail',
    'icon' => 'Icon',
    'thumbnail' => 'Thumbnail',
    'flyer' => 'Flyer',
    'poster' => 'Poster',
    'event' => 'Event image',
];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$imgChanged = 0; $thChanged = 0; $fileCount = 0;

foreach ($it as $file) {
    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') continue;
    $src = file_get_contents($file);
    $out = $src;

    // ---- images ----
    $out = preg_replace_callback('/<img\b([^>]*)>/s', function ($m) use ($keywords, &$imgChanged) {
        $inner = $m[1];
        if (preg_match('/\balt\s*=/i', $inner)) return $m[0];
        $alt = null;
        $lower = strtolower($inner);
        foreach ($keywords as $k => $label) {
            if (strpos($lower, $k) !== false) { $alt = $label; break; }
        }
        if ($alt === null) { $alt = ''; } // decorative/unknown -> empty alt (valid)
        $imgChanged++;
        // keep PHP inside src intact; insert alt right after "<img"
        return '<img alt="' . $alt . '"' . $inner . '>';
    }, $out);

    // ---- th scope (state machine over tags) ----
    $tokens = [];
    if (preg_match_all('/<(table|\/table|thead|\/thead|tbody|\/tbody|tfoot|\/tfoot|th)\b[^>]*>|<\/th>/i', $out, $mm, PREG_OFFSET_CAPTURE)) {
        $tokens = $mm[0];
    }
    $inThead = false; $inTbody = false; $inTfoot = false; $depth = 0; $thCount = 0;
    $newOut = '';
    $cursor = 0;
    foreach ($tokens as $tok) {
        [$tagText, $pos] = $tok;
        $newOut .= substr($out, $cursor, $pos - $cursor);
        $cursor = $pos + strlen($tagText);
        $lower = strtolower($tagText);
        if (preg_match('/^<table\b/i', $lower)) { $depth++; $inThead = false; $inTbody = false; $inTfoot = false; }
        elseif (preg_match('/^<\/table/i', $lower)) { $depth--; $inThead = false; $inTbody = false; $inTfoot = false; }
        elseif (preg_match('/^<thead\b/i', $lower)) { $inThead = true; $inTbody = false; $inTfoot = false; }
        elseif (preg_match('/^<\/thead/i', $lower)) { $inThead = false; }
        elseif (preg_match('/^<tbody\b/i', $lower)) { $inThead = false; $inTbody = true; $inTfoot = false; }
        elseif (preg_match('/^<\/tbody/i', $lower)) { $inTbody = false; }
        elseif (preg_match('/^<tfoot\b/i', $lower)) { $inTfoot = true; $inThead = false; $inTbody = false; }
        elseif (preg_match('/^<\/tfoot/i', $lower)) { $inTfoot = false; }
        elseif (preg_match('/^<th\b/i', $lower) && !preg_match('/\bscope\s*=/i', $tagText)) {
            $scope = $inTbody ? 'row' : 'col';
            $tagText = preg_replace('/^<th\b/i', '<th scope="' . $scope . '"', $tagText);
            $thCount++;
        }
        $newOut .= $tagText;
    }
    $newOut .= substr($out, $cursor);
    $out = $newOut;
    $thChanged += $thCount;

    if ($out !== $src) {
        $fileCount++;
        if (!$dry) file_put_contents($file, $out);
    }
}
echo ($dry ? "[DRY] " : ""), "files changed: $fileCount, images labelled: $imgChanged, th scoped: $thChanged\n";
