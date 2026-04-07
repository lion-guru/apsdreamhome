<?php
/**
 * Router Fix for PHP Built-in Server
 * Handles all requests properly
 */

// Handle static files first
$request_uri = $_SERVER['REQUEST_URI'];
$static_extensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf'];

$path_parts = pathinfo($request_uri);
$extension = strtolower($path_parts['extension'] ?? '');

// Serve static files directly
if (in_array($extension, $static_extensions)) {
    $file_path = __DIR__ . '/public' . $request_uri;
    if (file_exists($file_path)) {
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf'
        ];
        
        $mime = $mime_types[$extension] ?? 'application/octet-stream';
        header("Content-Type: $mime");
        readfile($file_path);
        exit;
    }
}

// Route everything else to public/index.php
if ($request_uri === '/' || $request_uri === '/public/' || strpos($request_uri, '/public') === 0) {
    require_once __DIR__ . '/public/index.php';
} else {
    // Try to find file in public directory
    $public_file = __DIR__ . '/public' . $request_uri;
    if (file_exists($public_file) && !is_dir($public_file)) {
        require_once $public_file;
    } else {
        // Route to main application
        require_once __DIR__ . '/public/index.php';
    }
}
?>
