<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$db   = 'apsdreamhome';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE documents");
    $structure = $stmt->fetchAll();
    
    // Get sample data
    $sampleStmt = $pdo->query("SELECT * FROM documents LIMIT 5");
    $sampleData = $sampleStmt->fetchAll();
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents Table Info</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            line-height: 1.6;
        }
        pre { 
            background: #f5f5f5; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .section { 
            margin-bottom: 30px; 
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1, h2 { 
            color: #2c3e50;
            margin-top: 0;
        }
        h1 { 
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        code {
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
        .success { 
            color: #27ae60;
            padding: 10px;
            background: #e8f8f0;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error { 
            color: #e74c3c;
            padding: 10px;
            background: #fde8e8;
            border-radius: 4px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Documents Table Information</h1>
    
    <?php if (isset($error)): ?>
        <div class="error">
            <h2>Database Error</h2>
            <pre><?php echo htmlspecialchars($error); ?></pre>
            <p>Please check your database configuration in <code>show-documents.php</code></p>
        </div>
    <?php else: ?>
        <div class="success">
            <strong>✓ Connected to database successfully</strong>
            <div>Database: <?php echo htmlspecialchars($db); ?></div>
            <div>Server: <?php echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></div>
        </div>
        
        <div class="section">
            <h2>Table Structure</h2>
            <?php if (!empty($structure)): ?>
                <table>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                    <?php foreach ($structure as $column): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($column['Field']); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($column['Type']); ?></code></td>
                            <td><?php echo htmlspecialchars($column['Null']); ?></td>
                            <td><?php echo htmlspecialchars($column['Key']); ?></td>
                            <td><?php echo htmlspecialchars($column['Default'] ?? 'NULL'); ?></td>
                            <td><?php echo htmlspecialchars($column['Extra']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="error">No columns found in the documents table.</p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Sample Data (First 5 Records)</h2>
            <?php if (!empty($sampleData)): ?>
                <pre><?php print_r($sampleData); ?></pre>
                
                <h3>Document Links</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>File Path/URL</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($sampleData as $doc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($doc['type'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($doc['url'])): ?>
                                    <code><?php echo htmlspecialchars($doc['url']); ?></code>
                                <?php elseif (!empty($doc['file_path'])): ?>
                                    <code><?php echo htmlspecialchars($doc['file_path']); ?></code>
                                <?php else: ?>
                                    <em>No path/URL found</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($doc['url']) || !empty($doc['file_path'])): ?>
                                    <?php 
                                    $filePath = !empty($doc['url']) ? $doc['url'] : $doc['file_path'];
                                    $fullPath = __DIR__ . '/' . ltrim($filePath, '/');
                                    ?>
                                    <?php if (file_exists($fullPath)): ?>
                                        <a href="<?php echo htmlspecialchars($filePath); ?>" class="btn" target="_blank">View</a>
                                        <a href="download.php?file=<?php echo urlencode($filePath); ?>" class="btn">Download</a>
                                    <?php else: ?>
                                        <span style="color: #e74c3c;">File not found</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="error">No records found in the documents table.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="section">
        <h2>Next Steps</h2>
        <p>Based on the information above, we can determine how to implement the file download functionality.</p>
        
        <h3>Common Storage Patterns:</h3>
        <ol>
            <li><strong>Local File Paths</strong>: If you see columns like <code>file_path</code> or <code>url</code> with relative/absolute paths</li>
            <li><strong>BLOB Storage</strong>: If you see BLOB/LONGBLOB columns containing file data</li>
            <li><strong>External Storage</strong>: If you see references to Google Drive, S3, or other cloud storage</li>
        </ol>
        
        <h3>What to Do Next:</h3>
        <ol>
            <li>Check if the file paths in the database are correct</li>
            <li>Verify file permissions on the server</li>
            <li>Let me know what you find, and I'll help implement the download functionality</li>
        </ol>
    </div>
</body>
</html>
