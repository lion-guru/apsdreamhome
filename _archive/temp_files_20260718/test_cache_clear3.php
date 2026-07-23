<?php
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->flushAll();
    echo "Redis flushed!\n";
} catch (Exception $e) {
    echo "Redis error: " . $e->getMessage() . "\n";
}
