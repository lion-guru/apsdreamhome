<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple database connection
require_once __DIR__ . '/simple_db.php';

// Get database connection
try {
    $pdo = getMysqliConnection();
    
    // Fetch documents from database
    $stmt = $pdo->prepare("SELECT d.*, u.name as username, p.title as property_title 
                         FROM documents d 
                         LEFT JOIN users u ON d.user_id = u.id 
                         LEFT JOIN properties p ON d.property_id = p.id 
                         ORDER BY d.uploaded_on DESC 
                         LIMIT 10");
    $stmt->execute();
    $documents = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Download Test</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        .document-list { margin: 20px 0; }
        .document-item { 
            display: flex; 
            justify-content: space-between; 
            padding: 15px; 
            border-bottom: 1px solid #eee;
            align-items: center;
            background: #f9f9f9;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .document-info { flex-grow: 1; }
        .document-actions { display: flex; gap: 10px; }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-download { background-color: #4CAF50; }
        .btn-download:hover { background-color: #45a049; }
        .btn-view { background-color: #2196F3; }
        .btn-view:hover { background-color: #0b7dda; }
        .document-name { font-weight: bold; font-size: 16px; margin-bottom: 5px; }
        .document-meta { color: #666; font-size: 0.9em; }
        .error { color: #f44336; padding: 10px; background: #ffebee; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Document Download Test</h1>
    
    <?php if (isset($error)): ?>
        <div class="error">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <div class="document-list">
            <h2>Available Documents</h2>
            
            <?php if (empty($documents)): ?>
                <p>No documents found in the database.</p>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-name">
                                <?php echo htmlspecialchars($doc['type'] ?? 'Document'); ?>
                            </div>
                            <div class="document-meta">
                                <?php 
                                echo 'Uploaded by: ' . htmlspecialchars($doc['username'] ?? 'Unknown'); 
                                if (!empty($doc['property_title'])) {
                                    echo ' | Property: ' . htmlspecialchars($doc['property_title']);
                                }
                                echo ' | ' . date('M d, Y', strtotime($doc['uploaded_on']));
                                ?>
                            </div>
                        </div>
                        <div class="document-actions">
                            <?php if (!empty($doc['drive_file_id'])): ?>
                                <a href="https://drive.google.com/uc?export=download&id=<?php echo urlencode($doc['drive_file_id']); ?>" 
                                   class="btn btn-download" target="_blank">
                                    <i class="fas fa-google-drive"></i> Download from Drive
                                </a>
                            <?php elseif (!empty($doc['url'])): ?>
                                <a href="download.php?file=<?php echo urlencode($doc['url']); ?>" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <a href="download.php?file=<?php echo urlencode($doc['url']); ?>&view=1" target="_blank" class="btn btn-success">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            <?php else: ?>
                                <span style="color: #f44336; padding: 8px 15px; background: #ffebee; border-radius: 4px;">
                                    <i class="fas fa-exclamation-triangle"></i> No file source available
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px; padding: 20px; background: #f5f5f5; border-radius: 5px;">
        <h3>About This Test</h3>
        <p>This page demonstrates the file download functionality integrated with your database:</p>
        <ul>
            <li>Fetches documents from the <code>documents</code> table</li>
            <li>Supports both locally stored files and Google Drive files</li>
            <li>Handles file downloads with proper MIME types and headers</li>
            <li>Shows document metadata (uploader, property, date)</li>
        </ul>
    </div>
</body>
</html>
