<?php
// Backward compatibility shim
// Routes old App\Core\Database references to App\Core\Database\Database
if (!class_exists('App\\Core\\Database', false)) {
    class_alias('App\\Core\\Database\\Database', 'App\\Core\\Database');
}
