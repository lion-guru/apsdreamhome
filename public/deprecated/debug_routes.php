<?php
echo "DEBUG: Routes file loading test<br>";
require_once '../routes/web.php';
echo "DEBUG: Routes loaded<br>";
$router = new Router();
echo "DEBUG: Router created<br>";
$router->dispatch();
echo "DEBUG: Router dispatched<br>";
?>
