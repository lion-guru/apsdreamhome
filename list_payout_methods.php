<?php
$f = 'app/services/PayoutBatchService.php';
$c = file_get_contents($f);
$methods = [];
if (preg_match_all('/public function (\w+)\(/', $c, $m)) {
    foreach ($m[1] as $method) {
        echo $method . "\n";
    }
}