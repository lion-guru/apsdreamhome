<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
echo 'Redis connected: ' . $redis->ping() . PHP_EOL;
$redis->set('test_key_web', 'test_value_web');
echo 'Test key: ' . $redis->get('test_key_web') . PHP_EOL;
echo "Redis extension loaded: " . (extension_loaded('redis') ? 'YES' : 'NO') . PHP_EOL;