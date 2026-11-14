<?php
// Add aria-label to <select> and <textarea> elements lacking one, using the
// name attribute humanized as the accessible label. Idempotent.
$root = 'C:/xampp/htdocs/apsdreamhome/app/views';
$dry = in_array('--dry', $argv, true);

function humanize(string $name): string {
    $n = preg_replace('/\[[^\]]*\]/', '', $name);       // strip array suffixes
    $n = str_replace(['_', '-'], ' ', trim($n));
    $n = preg_replace('/([a-z])([A-Z])/', '$1 $2', $n);  // camelCase -> spaced
    return trim(ucfirst(strtolower($n)));
}

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$changed = [];
foreach ($it as $file) {
    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') continue;
    $src = file_get_contents($file);
    $out = preg_replace_callback(
        '/<(select|textarea)\b([^>]*)>/s',
        function ($m) {
            $open = $m[2];
            if (preg_match('/\b(aria-label|aria-labelledby)\s*=/i', $open)) return $m[0];
            // wrapped in an implicit <label>? can't detect cheaply; adding aria-label is additive and safe
            if (preg_match('/\bname\s*=\s*"([^"]+)"/i', $open, $n)) {
                $label = humanize($n[1]);
            } elseif (preg_match('/\bid\s*=\s*"([^"]+)"/i', $open, $n)) {
                $label = humanize($n[1]);
            } else {
                return $m[0];
            }
            return '<' . $m[1] . ' aria-label="' . htmlspecialchars($label, ENT_QUOTES) . '"' . $open . '>';
        },
        $src
    );
    if ($out !== $src) {
        $changed[] = $file->getPathname();
        if (!$dry) file_put_contents($file, $out);
    }
}
echo ($dry ? "[DRY] " : ""), "changed files: ", count($changed), PHP_EOL;
foreach ($changed as $c) echo "  M ", str_replace('C:\\xampp\\htdocs\\apsdreamhome/', '', str_replace('\\', '/', $c)), PHP_EOL;
