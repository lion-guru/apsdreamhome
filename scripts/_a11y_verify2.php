<?php
// Verify: every '+' line equals its paired '-' line after stripping the
// inserted tokens (scope="col|row" on <th>, alt="..." on <img>).
$s = file_get_contents("C:/Users/abhay/AppData/Local/Temp/opencode/scope_diff.txt");
$sections = preg_split("/^diff --git /m", $s);
$sections = array_filter($sections, fn($x) => trim($x) !== "");
$bad = 0;
$checked = 0;
$nononeToOne = 0;
$mismatchFiles = [];
foreach ($sections as $sec) {
    if (preg_match('/^\+\+\+ b\/(.+)$/m', $sec, $fm)) { $file = $fm[1]; } else { $file = '(unknown)'; }
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
            continue;
        }
        foreach ($minus as $i => $m) {
            $p = $plus[$i];
            $stripped = preg_replace('/ scope="(col|row)"/', '', substr($p, 1));
            $stripped = preg_replace('/ alt="[^"]*"/', '', $stripped);
            $mn = substr($m, 1);
            $checked++;
            if ($stripped !== $mn) {
                $bad++;
                if (count($mismatchFiles) < 10) { $mismatchFiles[] = $file; }
                if ($bad <= 5) {
                    echo "MISMATCH in $file:\n  - ", substr($mn, 0, 140), "\n  + ", substr(substr($p, 1), 0, 140), "\n";
                }
            }
        }
    }
}
echo "verified line pairs: $checked\n";
echo "mismatches: $bad\n";
echo "non-1:1 hunks: $nononeToOne (expected: 1 = AGENTS.md.new deletion)\n";
if ($mismatchFiles) { echo "mismatch files:\n  ", implode("\n  ", array_unique($mismatchFiles)), "\n"; }
