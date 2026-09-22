<?php
$targets = [
    'app/Http/Controllers/Admin/AdminController.php' => [257, 305],
    'app/Services/Backoffice/DailyOperationsService.php' => [349, 399, 415, 419, 588],
    'app/Http/Controllers/Api/MobilePropertyApiController.php' => [271],
    'app/Http/Controllers/Front/HomePageController.php' => [131],
];
foreach ($targets as $f => $nums) {
    $l = file($f);
    foreach ($nums as $n) echo "$f:$n: " . trim(substr($l[$n-1], 0, 160)) . "\n";
}
