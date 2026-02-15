<?php
/**
 * Legacy News Detail Page Redirect
 * This file redirects to the new MVC news route.
 */
require_once __DIR__ . '/app/core/autoload.php';

// Get news ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Redirect to the new clean URL structure
    header("Location: " . BASE_URL . "news/" . $id);
    exit;
} else {
    // Redirect to the news listing if no ID provided
    header("Location: " . BASE_URL . "news");
    exit;
}
