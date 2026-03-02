<?php
$__env = getenv('APP_ENV') ?: 'production';
if ($__env === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Get the file path from the query string
$filePath = $_GET['file'] ?? '';

// Security check: prevent directory traversal
if (empty($filePath)) {
    http_response_code(400);
    die('Error: No file specified');
}

// Remove any directory traversal attempts
$filePath = str_replace(['../', '..\\'], '', $filePath);

// Construct the full file path
$baseDir = realpath(__DIR__);
$fullPath = realpath($baseDir . '/' . ltrim($filePath, '/'));

// Security check: ensure the file is within the allowed directory
if ($fullPath === false || strpos($fullPath, $baseDir) !== 0) {
    http_response_code(403);
    die('Error: Access denied');
}

// Check if file exists
if (!file_exists($fullPath)) {
    http_response_code(404);
    echo "<h1>File Not Found</h1>";
    echo "<p>The requested file was not found on the server.</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($filePath) . "</p>";
    echo "<p><strong>Full Path:</strong> " . htmlspecialchars($fullPath) . "</p>";
    echo "<h3>Common Issues:</h3>";
    echo "<ul>";
    echo "<li>Check if the file exists at the specified path</li>";
    echo "<li>Verify file permissions</li>";
    echo "<li>Make sure the file hasn't been moved or deleted</li>";
    echo "</ul>";
    exit;
}

// Get file info
$fileName = basename($fullPath);
$fileSize = filesize($fullPath);
$mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

// Check if we should view inline or force download
$view = isset($_GET['view']) && $_GET['view'] == '1';

// Set headers
header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Pragma: public');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Set Content-Disposition based on view parameter
if ($view && $mimeType === 'application/pdf') {
    // For PDFs, show in browser
    header('Content-Disposition: inline; filename="' . $fileName . '"');
} else {
    // For other files, force download
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
}

// Clear output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Stream the file
if ($file = fopen($fullPath, 'rb')) {
    while (!feof($file) && connection_status() == 0) {
        echo fread($file, 8192);
        flush();
    }
    fclose($file);
}

exit;
