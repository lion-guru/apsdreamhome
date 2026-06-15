<?php
$r = @new Redis();
$ok = @$r->connect('127.0.0.1', 6379, 2);
echo $ok ? 'Redis CONNECTED' : 'Redis NOT available';
echo PHP_EOL;

if ($ok) {
    $keys = @$r->keys('*admin_sidebar*');
    echo 'admin_sidebar keys: ' . count($keys) . PHP_EOL;
    foreach ($keys as $k) {
        $v = @$r->get($k);
        $d = @unserialize($v);
        echo '  ' . $k . ': ' . (is_array($d) ? count($d) . ' items' : 'non-array, val_len=' . strlen($v ?? '')) . PHP_EOL;
    }

    // Also check ALL keys
    $allKeys = @$r->keys('*');
    echo PHP_EOL . 'Total Redis keys: ' . count($allKeys) . PHP_EOL;
    foreach ($allKeys as $k) {
        echo "  {$k}" . PHP_EOL;
    }
} else {
    echo 'Cannot connect to Redis on 127.0.0.1:6379' . PHP_EOL;
}
