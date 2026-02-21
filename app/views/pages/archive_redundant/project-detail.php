<?php
/**
 * Project Detail Redirector
 * Redirects legacy project URLs to modern MVC routes
 */
require_once __DIR__ . '/init.php';

// Try to get ID or project slug
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$project_slug = isset($_GET['project']) ? $_GET['project'] : '';

if ($id > 0) {
    header("Location: " . BASE_URL . "project/" . $id);
    exit;
} elseif (!empty($project_slug)) {
    // If we only have slug, we might need a slug-based route or just redirect to projects
    header("Location: " . BASE_URL . "projects");
    exit;
}

// Fallback to projects list
header("Location: " . BASE_URL . "projects");
exit;
