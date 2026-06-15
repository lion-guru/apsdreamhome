<?php
$file = 'C:\xampp\htdocs\apsdreamhome\app\Services\AdminMenuService.php';
$tokens = token_get_all(file_get_contents($file));

$braceDepth = 0;
$inClass = false;
$classStart = 0;
$classEnd = 0;

foreach ($tokens as $i => $token) {
    if (is_array($token)) {
        if ($token[0] === T_CLASS) {
            echo "Class keyword at line {$token[2]}\n";
            $inClass = true;
        }
    } else {
        if ($token === '{') {
            $braceDepth++;
        }
        if ($token === '}') {
            $braceDepth--;
            if ($braceDepth === 0 && $inClass) {
                // Find line number for this closing brace
                $line = 0;
                $chars = 0;
                $content = file_get_contents($file);
                for ($j = 0; $j <= $i; $j++) {
                    if (is_array($tokens[$j])) {
                        $chars += strlen($tokens[$j][1]);
                    } else {
                        $chars += strlen($tokens[$j]);
                    }
                }
                $line = substr_count(substr($content, 0, $chars), "\n") + 1;
                echo "Class closes at line $line\n";
                echo "Remaining tokens after class close:\n";
                $showNext = 0;
                for ($k = $i+1; $k < min($i+20, count($tokens)); $k++) {
                    $t = $tokens[$k];
                    $val = is_array($t) ? token_name($t[0]) . "('" . trim($t[1]) . "')" : "'$t'";
                    echo "  [$k] $val\n";
                }
                break;
            }
        }
    }
}
