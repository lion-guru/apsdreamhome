<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple database connection
require_once __DIR__ . '/simple_db.php';

try {
    $pdo = getDbConnection();
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE documents");
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get sample data
    $sampleStmt = $pdo->query("SELECT * FROM documents LIMIT 5");
    $sampleData = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents Table Structure</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .section { margin-bottom: 30px; }
        h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h1>Documents Table Structure</h1>
    
    <?php if (isset($error)): ?>
        <div style="color: red; padding: 10px; background: #ffebee; border-radius: 4px;">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <div class="section">
            <h2>Table Structure</h2>
            <pre><?php print_r($structure); ?></pre>
        </div>
        
        <div class="section">
            <h2>Sample Data (First 5 Records)</h2>
            <pre><?php print_r($sampleData); ?></pre>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
        <h3>What This Shows</h3>
        <p>This page helps us understand how documents are stored in your database by showing:</p>
        <ul>
            <li>The structure of the documents table (column names and types)</li>
            <li>Sample document records to see actual data</li>
        </ul>
    </div>
</body>
</html>
