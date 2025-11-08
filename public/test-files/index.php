<?php
// Simple test page for file downloads
$files = [
    'sample.txt' => 'Text File',
    'test-download.pdf' => 'PDF Document'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Download Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        .file-list { margin: 20px 0; }
        .file-item { 
            display: flex; 
            justify-content: space-between; 
            padding: 10px; 
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        .file-actions { display: flex; gap: 10px; }
        .btn {
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }
        .btn-download { background-color: #e1f5fe; }
        .btn-view { background-color: #e8f5e9; }
        .file-info { flex-grow: 1; }
        .file-name { font-weight: bold; }
        .file-type { color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>File Download Test</h1>
    <p>This page tests the file download functionality with different file types.</p>
    
    <div class="file-list">
        <h2>Available Test Files</h2>
        <?php foreach ($files as $filename => $description): 
            $filepath = __DIR__ . '/' . $filename;
            if (!file_exists($filepath)) continue;
            
            $filesize = filesize($filepath);
            $filetype = mime_content_type($filepath);
        ?>
        <div class="file-item">
            <div class="file-info">
                <div class="file-name"><?php echo htmlspecialchars($description); ?></div>
                <div class="file-type">
                    <?php echo htmlspecialchars($filename); ?> 
                    (<?php echo number_format($filesize / 1024, 2); ?> KB, <?php echo $filetype; ?>)
                </div>
            </div>
            <div class="file-actions">
                <a href="download.php?file=<?php echo urlencode($filename); ?>" class="btn btn-download">Download</a>
                <a href="download.php?file=<?php echo urlencode($filename); ?>&action=view" class="btn btn-view">View</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
        <h3>Test Results</h3>
        <p>For each file, test both the "Download" and "View" buttons:</p>
        <ul>
            <li><strong>Download:</strong> Should prompt to save the file</li>
            <li><strong>View:</strong> Should display in the browser (if supported)</li>
        </ul>
    </div>
</body>
</html>
