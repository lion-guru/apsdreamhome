<?php
/**
 * Simple Working Index for APS Dream Home
 * This bypasses complex routing issues and provides immediate functionality
 */

// Basic configuration and security
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple routing based on query parameters
$action = $_GET['action'] ?? 'home';
$page = $_GET['page'] ?? '';

// Handle different page requests
if ($page) {
    // Handle specific page requests
    switch ($page) {
        case 'properties':
            if (file_exists('properties.php')) {
                include 'properties.php';
            } else {
                include 'simple_homepage.php';
            }
            break;
        case 'about':
            if (file_exists('about.php')) {
                include 'about.php';
            } else {
                include 'simple_homepage.php';
            }
            break;
        case 'contact':
            if (file_exists('contact.php')) {
                include 'contact.php';
            } else {
                include 'simple_homepage.php';
            }
            break;
        case 'projects':
            if (file_exists('projects.php')) {
                include 'projects.php';
            } else {
                include 'simple_homepage.php';
            }
            break;
        default:
            include 'simple_homepage.php';
            break;
    }
} elseif ($action === 'admin' || strpos($_SERVER['REQUEST_URI'], 'admin') !== false) {
    // Handle admin requests
    if (file_exists('admin/login.php')) {
        header('Location: admin/login.php');
    } elseif (file_exists('admin.php')) {
        include 'admin.php';
    } else {
        include 'simple_homepage.php';
    }
} else {
    // Default to homepage
    include 'simple_homepage.php';
}

exit;