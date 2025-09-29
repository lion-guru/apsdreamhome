<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple database connection
$host = 'localhost';
$db   = 'apsdreamhomefinal';
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
    $stmt = $pdo->query("SHOW COLUMNS FROM documents");
    $columns = $stmt->fetchAll();
    
    // Get sample data
    $sampleStmt = $pdo->query("SELECT * FROM documents LIMIT 5");
    $sampleData = $sampleStmt->fetchAll();
    
    // Check for related tables
    $tablesStmt = $pdo->query("SHOW TABLES LIKE '%document%'");
    $relatedTables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Debugger</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
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
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        h2 { 
            color: #333; 
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Document Storage Debugger</h1>
    
    <?php if (isset($error)): ?>
        <div class="error">
            <h2>Database Error</h2>
            <pre><?php echo htmlspecialchars($error); ?></pre>
        </div>
    <?php else: ?>
        <div class="section">
            <h2>Documents Table Structure</h2>
            <?php if (!empty($columns)): ?>
                <table>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                    <?php foreach ($columns as $column): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($column['Field']); ?></strong></td>
                            <td><?php echo htmlspecialchars($column['Type']); ?></td>
                            <td><?php echo htmlspecialchars($column['Null']); ?></td>
                            <td><?php echo htmlspecialchars($column['Key']); ?></td>
                            <td><?php echo htmlspecialchars($column['Default'] ?? 'NULL'); ?></td>
                            <td><?php echo htmlspecialchars($column['Extra']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="error">No columns found in documents table.</p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Sample Document Records (First 5)</h2>
            <?php if (!empty($sampleData)): ?>
                <pre><?php print_r($sampleData); ?></pre>
            <?php else: ?>
                <p class="error">No records found in documents table.</p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Related Tables</h2>
            <?php if (!empty($relatedTables)): ?>
                <ul>
                    <?php foreach ($relatedTables as $table): ?>
                        <li><?php echo htmlspecialchars($table); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No related document tables found.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="section">
        <h2>Next Steps</h2>
        <p>Based on the information above, we can determine:</p>
        <ol>
            <li>How documents are stored (file paths, BLOBs, or external references)</li>
            <li>Which columns contain file information</li>
            <li>How to properly retrieve and serve the files</li>
        </ol>
        <p>Please share the output of this page so I can help implement the correct file download functionality.</p>
    </div>
</body>
</html>
