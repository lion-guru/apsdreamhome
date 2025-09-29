<?php
// Bootstrap the application
require_once __DIR__ . '/../../app/core/autoload.php';

use App\Core\Http\Response;

// Get the requested file
$file = $_GET['file'] ?? '';
$action = $_GET['action'] ?? 'download';
$baseDir = __DIR__;

// Security check: prevent directory traversal
if (strpos($file, '..') !== false || strpos($file, '/') !== false) {
    Response::make('Invalid file path', 400)->send();
    exit;
}

$filePath = $baseDir . '/' . $file;

// Check if file exists
if (!file_exists($filePath)) {
    Response::make('File not found', 404)->send();
    exit;
}

// Check if it's a file (not a directory)
if (!is_file($filePath)) {
    Response::make('Not a file', 400)->send();
    exit;
}

// Set appropriate headers based on action
if ($action === 'view') {
    // Try to display the file inline
    Response::file($filePath, basename($filePath));
} else {
    // Force download
    Response::download($filePath, basename($filePath));
}

// Send the response
Response::getInstance()->send();
