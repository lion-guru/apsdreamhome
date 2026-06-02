<?php
$_SERVER['REQUEST_URI'] = '/apsdreamhome/admin/social-analytics';
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/routes/router.php';
$router = new Router();
require_once __DIR__ . '/routes/web.php';
$router->dispatch();
