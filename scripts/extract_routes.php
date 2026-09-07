<?php
$content = file_get_contents('C:/xampp/htdocs/apsdreamhome/routes/web.php');
preg_match_all('/Route::(get|post|put|patch|delete)\(["\'\`]([^"\'\`]+)/', $content, $m);
$routes = array_unique($m[2]);
sort($routes);
foreach ($routes as $route) {
    echo $route . "\n";
}