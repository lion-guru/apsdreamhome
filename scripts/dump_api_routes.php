<?php
$c = file_get_contents(__DIR__.'/../routes/api.php');
preg_match_all("/\\\$router->(?:get|post|put|patch|delete)\s*\(\s*['\"]([^'\"]+)['\"]/", $c, $m);
$routes = array_unique($m[1]);
sort($routes);
$public = array_values(array_filter($routes, fn($r)=>str_starts_with($r,'/api/') && strpos($r,'{')===false));
echo "API total: ".count($routes)." public static: ".count($public)."\n";
foreach(array_slice($public,0,15) as $r) echo $r."\n";
file_put_contents(__DIR__.'/../testing/api_routes.json', json_encode($public, JSON_PRETTY_PRINT));
