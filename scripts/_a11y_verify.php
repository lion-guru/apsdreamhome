<?php
// Verify: every '+' line in the big diff equals its paired '-' line after
// stripping the single inserted ` aria-label="..."` token.
$s = file_get_contents("C:/Users/abhay/AppData/Local/Temp/opencode/big_diff.txt");
$sections = preg_split("/^diff --git /m", $s);
$sections = array_filter($sections, fn($x) => trim($x) !== "");
$bad = 0;
$checked = 0;
$nononeToOne = 0;
$nononeToOneFiles = [];
$mismatchFiles = [];
foreach ($sections as $sec) {
    // find filename from the +++ line
    $file = '(unknown)';
    if (preg_match('/^\+\+\+ b\/(.+)$/m', $sec, $fm)) { $file = $fm[1]; }
    $lines = explode("\n", $sec);
    $curHunk = [];
    $hunks = [];
    foreach ($lines as $ln) {
        if (preg_match("/^@@/", $ln)) {
            if ($curHunk) { $hunks[] = $curHunk; }
            $curHunk = [];
            continue;
        }
        $curHunk[] = $ln;
    }
    if ($curHunk) { $hunks[] = $curHunk; }
    foreach ($hunks as $h) {
        $minus = array_values(array_filter($h, fn($l) => str_starts_with($l, "-") && !str_starts_with($l, "---")));
        $plus  = array_values(array_filter($h, fn($l) => str_starts_with($l, "+") && !str_starts_with($l, "+++")));
        if (count($minus) !== count($plus)) {
            $nononeToOne++;
            if (count($nononeToOneFiles) < 10) { $nononeToOneFiles[] = "$file (-" . count($minus) . " +" . count($plus) . ")"; }
            continue;
        }
        foreach ($minus as $i => $m) {
            $p = $plus[$i];
            $pn = preg_replace('/ aria-label="[^"]*"/', '', substr($p, 1));
            $mn = substr($m, 1);
            $checked++;
            if ($pn !== $mn) {
                $bad++;
                if (count($mismatchFiles) < 10) { $mismatchFiles[] = $file; }
                if ($bad <= 5) {
                    echo "MISMATCH in $file:\n  - ", substr($mn, 0, 140), "\n  + ", substr(substr($p, 1), 0, 140), "\n";
                }
            }
        }
    }
}
echo "verified line pairs (1:1 hunks): $checked\n";
echo "mismatches: $bad\n";
echo "non-1:1 hunks: $nononeToOne\n";
if ($mismatchFiles) { echo "mismatch files:\n  ", implode("\n  ", array_unique($mismatchFiles)), "\n"; }
if ($nononeToOneFiles) { echo "non-1:1 files:\n  ", implode("\n  ", $nononeToOneFiles), "\n"; }
