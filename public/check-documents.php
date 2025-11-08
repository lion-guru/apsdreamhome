<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple database connection
require_once __DIR__ . '/simple_db.php';

try {
    $pdo = getDbConnection();
    
    // Fetch documents with their full paths
    $stmt = $pdo->query("SELECT id, url, drive_file_id, 
                         CONCAT('public', url) as full_path,
                         CASE 
                             WHEN drive_file_id IS NOT NULL THEN 'Google Drive'
                             WHEN url LIKE 'http%' THEN 'External URL'
                             WHEN url IS NOT NULL AND url != '' THEN 'Local File'
                             ELSE 'No Source'
                         END as source_type
                         FROM documents");
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Path Checker</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .exists { color: green; font-weight: bold; }
        .missing { color: red; }
    </style>
</head>
<body>
    <h1>Document Path Checker</h1>
    
    <?php if (isset($error)): ?>
        <div style="color: red; padding: 10px; background: #ffebee; border-radius: 4px;">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Source Type</th>
                    <th>URL/Path</th>
                    <th>Full Path</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td><?php echo $doc['id']; ?></td>
                        <td><?php echo $doc['source_type']; ?></td>
                        <td>
                            <?php if (!empty($doc['drive_file_id'])): ?>
                                Google Drive ID: <?php echo htmlspecialchars($doc['drive_file_id']); ?>
                            <?php else: ?>
                                <?php echo htmlspecialchars($doc['url']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($doc['source_type'] === 'Local File'): ?>
                                <?php 
                                $fullPath = __DIR__ . '/' . ltrim($doc['url'], '/');
                                echo htmlspecialchars($fullPath);
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($doc['source_type'] === 'Local File'): ?>
                                <?php 
                                $fullPath = __DIR__ . '/' . ltrim($doc['url'], '/');
                                if (file_exists($fullPath)) {
                                    echo '<span class="exists">File Exists</span>';
                                } else {
                                    echo '<span class="missing">File Missing</span>';
                                }
                                ?>
                            <?php elseif ($doc['source_type'] === 'Google Drive'): ?>
                                <a href="https://drive.google.com/uc?export=download&id=<?php echo urlencode($doc['drive_file_id']); ?>" 
                                   target="_blank">Test Drive Link</a>
                            <?php elseif ($doc['source_type'] === 'External URL'): ?>
                                <a href="<?php echo htmlspecialchars($doc['url']); ?>" 
                                   target="_blank">Test Link</a>
                            <?php else: ?>
                                No source available
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <div style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
        <h3>About This Page</h3>
        <p>This page helps diagnose file path issues by showing:</p>
        <ul>
            <li>Document records from the database</li>
            <li>Source type (Local File, Google Drive, or External URL)</li>
            <li>Full server path for local files</li>
            <li>File existence status</li>
        </ul>
    </div>
</body>
</html>
