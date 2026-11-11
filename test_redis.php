<?php
$redis = new Redis();
try {
    $redis->connect('127.0.0.1', 6379);
    $redis->set('test_key', 'Hello Redis!', 3600);
    $value = $redis->get('test_key');
    echo "Redis Test: " . $value;
    echo "\nRedis is working perfectly!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}