<?php
$c = file_get_contents(__DIR__.'/../routes/web.php');
preg_match_all("/\\\$router->(?:get|post|put|patch|delete)\s*\(\s*['\"]([^'\"]+)['\"]/", $c, $m);
$routes = array_unique($m[1]);
$static = array_values(array_filter($routes, fn($r)=>strpos($r,'{')===false && strpos($r,':')===false));
sort($static);
echo "Total routes: " . count($routes) . PHP_EOL;
echo "Static routes: " . count($static) . PHP_EOL;
foreach(array_slice($static,0,10) as $r) echo $r . PHP_EOL;
file_put_contents(__DIR__.'/../testing/visual_tests/web_static_routes.json', json_encode($static, JSON_PRETTY_PRINT));
echo "dumped: " . count($static) . PHP_EOL;
