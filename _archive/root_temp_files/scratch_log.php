<?php
$log = 'C:\\xampp\\apache\\logs\\error.log';
if (file_exists($log)) {
    echo tailCustom($log, 20);
}
function tailCustom($filepath, $lines = 10) {
    $f = fopen($filepath, "r");
    fseek($f, -1000, SEEK_END);
    $data = fread($f, 1000);
    $linesArr = explode("\n", $data);
    return implode("\n", array_slice($linesArr, -$lines));
}?>