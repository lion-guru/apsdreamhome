<?php
$uri = '/apsdreamhome/public/simple_router_test.php/simple-test';

// Remove base path
$basePath = '/apsdreamhome';
$publicPath = '/apsdreamhome/public';

if (strpos($uri, $publicPath) === 0) {
    $uri = substr($uri, strlen($publicPath));
} elseif (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Remove leading slash if present
$uri = ltrim($uri, '/');

// Remove .php extension if present
$uri = preg_replace('/\.php$/', '', $uri);

echo "Final URI: '$uri'";
?>
