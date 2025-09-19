<?php

// Load bootstrap file
require_once __DIR__ . '/../src/bootstrap.php';

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Remove base path from request URI
$base_path = '/march2025apssite';
$request_uri = str_replace($base_path, '', $request_uri);

// Remove query string from request URI
$request_uri = strtok($request_uri, '?');

// Define routes
$routes = [
    '/' => 'home',
    '/about' => 'about',
    '/contact' => 'contact',
    '/properties' => 'properties',
    '/login' => 'login',
    '/register' => 'register',
    '/admin' => 'admin/dashboard',
];

// Check if route exists
if (isset($routes[$request_uri])) {
    $page = $routes[$request_uri];
} else {
    // Check if it's an admin route
    if (strpos($request_uri, '/admin/') === 0) {
        $page = substr($request_uri, 1); // Remove leading slash
    } else {
        // 404 page
        header('HTTP/1.0 404 Not Found');
        $page = '404';
    }
}

// Load the page
$page_path = APP_ROOT . '/app/views/' . $page . '.php';

if (file_exists($page_path)) {
    require_once $page_path;
} else {
    // Fallback to 404 if page file doesn't exist
    require_once APP_ROOT . '/app/views/404.php';
}