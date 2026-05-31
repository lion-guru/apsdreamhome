<?php
// Backward compatibility shim
// Routes old App\Core\Logger references to App\Services\SystemLogger
if (!class_exists('App\\Core\\Logger', false)) {
    class_alias('App\\Services\\SystemLogger', 'App\\Core\\Logger');
}
