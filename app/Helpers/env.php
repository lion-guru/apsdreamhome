<?php
// app/helpers/env.php

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        return __DIR__ . '/../storage/' . $path;
    }
}

if (!function_exists('database_path')) {
    function database_path($path = '') {
        return __DIR__ . '/../database/' . $path;
    }
}
