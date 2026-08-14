<?php
$files = [
    "app/Services/Auth/AuthService.php",
    "app/Http/Controllers/Agent/MainController.php",
    "app/Http/Middleware/AuthMiddleware.php",
    "app/Core/Middleware/AuthMiddleware.php",
    "app/Services/AuthMiddleware.php"
];
$newPattern1 = "header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');";
$newPattern2 = 'header("Location: " . (defined(\'BASE_URL\') ? BASE_URL : \'\') . "/login");';
$oldPattern1 = "header('Location: /login');";
$oldPattern2 = 'header("Location: /login");';

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    $newC = str_replace($oldPattern1, $newPattern1, $c);
    $newC = str_replace($oldPattern2, $newPattern2, $newC);
    if ($newC !== $c) {
        $count = substr_count($c, $oldPattern1) + substr_count($c, $oldPattern2);
        file_put_contents($f, $newC);
        echo "Updated $f ($count occurrences)\n";
    }
}?>