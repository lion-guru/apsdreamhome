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
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        pre { 
            background: #f5f5f5; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .section { margin-bottom: 30px; }
        h2 { color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Documents Table Information</h1>
    
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
</body>
</html>
