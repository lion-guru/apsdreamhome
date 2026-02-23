<?php
require_once __DIR__ . '/public/index.php';
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "\n";
echo "ASSET_URL: " . (defined('ASSET_URL') ? ASSET_URL : 'NOT DEFINED') . "\n";
?>