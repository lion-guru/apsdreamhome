<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bootstrap the application
require_once __DIR__ . '/../../../app/core/autoload.php';

use App\Core\Http\Response;
use App\Core\Database\Database;

// Set JSON content type
header('Content-Type: application/json');

// Get document ID from URL
$documentId = $_GET['id'] ?? null;

if (!$documentId) {
    echo json_encode([
        'success' => false,
        'message' => 'Document ID is required',
        'error_code' => 'MISSING_ID'
    ]);
    exit;
}

try {
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // Prepare and execute query
    $stmt = $db->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->bind_param('i', $documentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Document not found', 404);
    }
    
    $document = $result->fetch_assoc();
    $filePath = null;
    
    // Determine file path based on storage method
    if (!empty($document['drive_file_id'])) {
        // Handle Google Drive files
        $filePath = 'https://drive.google.com/uc?export=download&id=' . $document['drive_file_id'];
        
        // For Google Drive files, we'll redirect to the download URL
        header('Location: ' . $filePath);
        exit;
    } elseif (!empty($document['url'])) {
        // Handle locally stored files
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/../' . ltrim($document['url'], '/');
        
        // Check if file exists
        if (!file_exists($filePath)) {
            throw new Exception('File not found on server', 404);
        }
        
        // Get file info
        $fileName = basename($filePath);
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);
        
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);
        header('Pragma: public');
        
        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Read the file and output it
        readfile($filePath);
        exit;
    } else {
        throw new Exception('No valid file source found', 400);
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'DOWNLOAD_ERROR',
        'document_id' => $documentId
    ]);
    exit;
}
