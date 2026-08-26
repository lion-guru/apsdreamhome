<?php
/**
 * Scan for mid-file `use` statements inside class bodies (fatal trait-import pattern).
 * A `use X;` after the first method/property inside a class = PHP treats it as trait import.
 */
$dirs = ['app/Http/Controllers', 'app/Services'];
$found = [];

foreach ($dirs as $dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->getExtension() !== 'php') continue;
        $path = $file->getPathname();
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $inClass = false;
        $braceDepth = 0;
        $classLine = 0;
        $seenMethod = false;

        foreach ($lines as $i => $line) {
            $trim = ltrim($line);
            if (!$inClass && preg_match('/^(abstract |final )?class\s+\w+/', $trim)) {
                $inClass = true;
                $classLine = $i + 1;
                $seenMethod = false;
            }
            if (!$inClass) continue;

            $open = substr_count($trim, '{');
            $close = substr_count($trim, '}');
            if ($seenMethod && preg_match('/^use\s+[\w\\\\]+;/', $trim) && !preg_match('/use function|use const/', $trim)) {
                // use inside class after a method = trait import; flag only if trait likely doesn't exist
                $trait = preg_replace('/^use\s+([\w\\\\]+);.*/', '$1', $trim);
                $found[] = [$path, $i + 1, $trait, $classLine];
            }
            if (preg_match('/(public|protected|private)\s+(static\s+)?function\s/', $trim)) {
                $seenMethod = true;
            }
            $braceDepth += $open - $close;
            if ($seenMethod && $braceDepth <= 0 && $open === 0 && $i > $classLine) {
                // possible class end
                if ($braceDepth < 0) { $inClass = false; $seenMethod = false; }
            }
        }
    }
}

if (empty($found)) {
    echo "CLEAN: no mid-file use statements found\n";
} else {
    echo "FOUND " . count($found) . " mid-file use statement(s):\n";
    foreach ($found as [$path, $line, $trait, $cls]) {
        echo "  $path:$line  use $trait  (class at line $cls)\n";
    }
}
