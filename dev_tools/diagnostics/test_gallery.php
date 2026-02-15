<?php
require_once __DIR__ . '/../../app/core/App.php';
$db = \App\Core\App::database();
$res = $db->fetchAll('SELECT * FROM gallery LIMIT 5');
print_r($res);

