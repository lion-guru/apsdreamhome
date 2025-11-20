<?php
/**
 * WORKING INDEX FOR APS DREAM HOME
 * This version bypasses complex routing issues and provides immediate functionality
 */

// Basic configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define basic constants
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_url = $protocol . $host . '/';

// Simple routing based on the URL
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$request_path = parse_url($request_uri, PHP_URL_PATH);
$request_path = trim($request_path, '/');

// Remove project folder from path if present
$project_folders = ['apsdreamhome', 'apsdreamhomefinal'];
foreach ($project_folders as $folder) {
    if (strpos($request_path, $folder) === 0) {
        $request_path = substr($request_path, strlen($folder));
        $request_path = trim($request_path, '/');
        break;
    }
}

// Handle different routes
if (empty($request_path) || $request_path === 'home' || $request_path === 'index') {
    // Homepage
    if (file_exists('simple_homepage.php')) {
        include 'simple_homepage.php';
    } else {
        echo '<h1>APS Dream Home</h1><p>Welcome to APS Dream Home. Please create simple_homepage.php for the homepage.</p>';
    }
} elseif ($request_path === 'properties' || $request_path === 'properties.php') {
    // Properties page
    if (file_exists('properties.php')) {
        include 'properties.php';
    } else {
        echo '<h1>Properties</h1><p>Properties page coming soon!</p>';
    }
} elseif ($request_path === 'about' || $request_path === 'about.php') {
    // About page
    if (file_exists('about.php')) {
        include 'about.php';
    } else {
        echo '<h1>About Us</h1><p>About APS Dream Home coming soon!</p>';
    }
} elseif ($request_path === 'contact' || $request_path === 'contact.php') {
    // Contact page
    if (file_exists('contact.php')) {
        include 'contact.php';
    } else {
        echo '<h1>Contact Us</h1><p>Contact information coming soon!</p>';
    }
} elseif ($request_path === 'projects' || $request_path === 'projects.php') {
    // Projects page
    if (file_exists('projects.php')) {
        include 'projects.php';
    } else {
        echo '<h1>Our Projects</h1><p>Project information coming soon!</p>';
    }
} elseif (strpos($request_path, 'admin') === 0) {
    // Admin pages
    $admin_path = str_replace('admin/', '', $request_path);
    if (file_exists('admin/' . $admin_path)) {
        include 'admin/' . $admin_path;
    } elseif (file_exists('admin/index.php')) {
        include 'admin/index.php';
    } else {
        echo '<h1>Admin Panel</h1><p>Admin functionality coming soon!</p>';
    }
} elseif ($request_path === 'login' || $request_path === 'login.php') {
    // Login page
    if (file_exists('login.php')) {
        include 'login.php';
    } else {
        echo '<h1>Login</h1><p>Login page coming soon!</p>';
    }
} elseif ($request_path === 'register' || $request_path === 'registration.php') {
    // Registration page
    if (file_exists('registration.php')) {
        include 'registration.php';
    } elseif (file_exists('register.php')) {
        include 'register.php';
    } else {
        echo '<h1>Register</h1><p>Registration page coming soon!</p>';
    }
} elseif ($request_path === 'test' || $request_path === 'test.php') {
    // Test page
    if (file_exists('test.php')) {
        include 'test.php';
    } else {
        echo '<h1>System Test</h1><p>Test page coming soon!</p>';
    }
} else {
    // 404 - Page not found
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>404 - Page Not Found</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            h1 { color: #e53e3e; }
            a { color: #667eea; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <h1>404 - Page Not Found</h1>
        <p>The page you requested (' . htmlspecialchars($request_path) . ') could not be found.</p>
        <p><a href="' . $base_url . '">Return to Homepage</a></p>
        <hr>
        <p><small>APS Dream Home - Real Estate Solutions</small></p>
    </body>
    </html>';
}

exit;