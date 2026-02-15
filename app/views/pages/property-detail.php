<?php
/**
 * Legacy Property Detail Page Redirect
 * This file redirects to the new MVC property route.
 */
require_once __DIR__ . '/app/core/autoload.php';

// Get property ID from URL
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($pid > 0) {
    // Redirect to the new clean URL structure
    header("Location: " . BASE_URL . "property/" . $pid);
    exit;
} else {
    // Redirect to the properties listing if no ID provided
    header("Location: " . BASE_URL . "properties");
    exit;
}
