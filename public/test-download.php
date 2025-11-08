<?php

// Bootstrap the application
require_once __DIR__ . '/../app/core/autoload.php';

use App\Core\Http\Response;

// Test file download
$filePath = __DIR__ . '/test-download.pdf';

// Check if the file exists
if (!file_exists($filePath)) {
    die('Test file not found. Please make sure test-download.pdf exists in the public directory.');
}

// Check if this is a download or view request
if (isset($_GET['action']) && $_GET['action'] === 'view') {
    // Display the file inline
    Response::file($filePath, 'test-document.pdf');
} else {
    // Force download
    Response::download($filePath, 'test-document.pdf');
}
