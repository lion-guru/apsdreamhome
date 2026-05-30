<?php
echo "opcache_enabled: " . (function_exists('opcache_get_status') ? (opcache_get_status() ? 'yes' : 'no') : 'N/A') . "\n";
echo "apc_enabled: " . (extension_loaded('apc') ? 'yes' : 'no') . "\n";
echo "PHP version: " . phpversion() . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
