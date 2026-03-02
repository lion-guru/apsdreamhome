<?php
/**
 * Session Configuration
 */

return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
    'encrypt' => env('SESSION_ENCRYPT', false),
    'files' => env('SESSION_FILES_PATH', '/tmp'),
    'connection' => env('SESSION_CONNECTION', null),
    'table' => env('SESSION_TABLE', 'sessions'),
    'store' => env('SESSION_STORE', null),
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE_NAME', 'aps_session'),
    'path' => env('SESSION_PATH', '/'),
    'domain' => env('SESSION_DOMAIN', null),
    'secure' => env('SESSION_SECURE_COOKIE', false),
    'http_only' => env('SESSION_HTTP_ONLY', true),
    'same_site' => env('SESSION_SAME_SITE', 'lax'),
    'partitioned_cookies' => env('SESSION_PARTITIONED_COOKIE', false),
];
