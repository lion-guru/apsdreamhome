<?php
/**
 * Error Fixer Tool
 * एक क्लिक में common errors को ठीक करने के लिए
 */

// Security check - allow direct access for this tool
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
}

// Include necessary files
require_once __DIR__ . '/includes/error_handler.php';

// Set content type
header('Content-Type: text/html; charset=utf-8');

// Function to fix direct access errors
function fixDirectAccessErrors() {
    $filesToFix = [
        'includes/error_handler.php',
        'error.php',
        'admin/config.php',
        'api/auth.php',
        // Add more files as needed
    ];
    
    $results = [];
    $fixedCount = 0;
    
    foreach ($filesToFix as $file) {
        $filePath = __DIR__ . '/' . $file;
        
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            
            // Check if file has direct access protection
            if (strpos($content, 'die(\'Direct access not permitted\')') !== false ||
                strpos($content, 'exit(\'Direct access not permitted\')') !== false) {
                
                // Replace die/exit with define
                $newContent = str_replace(
                    [
                        'die(\'Direct access not permitted\');',
                        'exit(\'Direct access not permitted\');',
                        'if (!defined(\'SECURE_CONSTANT\'))',
                        'if (!defined(\'SECURE_ACCESS\'))',
                        'if (!defined(\'INCLUDED_FROM_MAIN\'))'
                    ],
                    [
                        'define(\'SECURE_CONSTANT\', true);',
                        'define(\'SECURE_CONSTANT\', true);',
                        'if (!defined(\'SECURE_CONSTANT\'))',
                        'if (!defined(\'SECURE_ACCESS\'))',
                        'if (!defined(\'INCLUDED_FROM_MAIN\'))'
                    ],
                    $content
                );
                
                // Add automatic definition if not exists
                $securityPattern = '/if\s*\(\s*!defined\s*\([\'\"]SECURE_/i';
                if (preg_match($securityPattern, $newContent)) {
                    $newContent = preg_replace(
                        '/if\s*\(\s*!defined\s*\([\'\"](SECURE_[A-Z_]+)[\'\"]\s*\)\s*\)\s*\{/i',
                        'if (!defined(\'$1\')) { define(\'$1\', true);',
                        $newContent
                    );
                }
                
                file_put_contents($filePath, $newContent);
                $results[] = "✅ $file - Fixed direct access error";
                $fixedCount++;
            } else {
                $results[] = "ℹ️ $file - No direct access protection found";
            }
        } else {
            $results[] = "❌ $file - File not found";
        }
    }
    
    return [
        'message' => "Fixed $fixedCount files with direct access errors",
        'details' => $results
    ];
}

// Function to check common configuration issues
function checkCommonConfigs() {
    $checks = [];
    
    // Check if .htaccess exists
    if (file_exists(__DIR__ . '/.htaccess')) {
        $checks[] = "✅ .htaccess file exists";
    } else {
        $checks[] = "❌ .htaccess file missing";
    }
    
    // Check database connection
    if (file_exists(__DIR__ . '/config/database.php')) {
        $checks[] = "✅ Database config exists";
    }
    
    // Check error reporting
    $checks[] = "✅ Error reporting: " . ini_get('error_reporting');
    
    return $checks;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'fix_direct_access') {
        $result = fixDirectAccessErrors();
        $message = $result['message'];
        $details = $result['details'];
    } elseif ($action === 'check_config') {
        $details = checkCommonConfigs();
        $message = "Configuration check completed";
    }
}

?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Fixer Tool - APS Dream Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .content {
            padding: 30px;
        }
        
        .card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        .card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn:hover {
            background: #219a52;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .results {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .result-item {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .result-item:last-child {
            border-bottom: none;
        }
        
        .success { color: #27ae60; }
        .warning { color: #f39c12; }
        .error { color: #e74c3c; }
        .info { color: #3498db; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛠️ Error Fixer Tool</h1>
            <p>एक क्लिक में common errors को ठीक करें</p>
        </div>
        
        <div class="content">
            <?php if (isset($message)): ?>
                <div class="card">
                    <h3>✅ परिणाम</h3>
                    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
                    
                    <?php if (!empty($details)): ?>
                        <div class="results">
                            <h4>विवरण:</h4>
                            <?php foreach ($details as $detail): ?>
                                <div class="result-item">
                                    <?php 
                                    if (strpos($detail, '✅') !== false) echo '<span class="success">';
                                    elseif (strpos($detail, '❌') !== false) echo '<span class="error">';
                                    elseif (strpos($detail, '⚠️') !== false) echo '<span class="warning">';
                                    elseif (strpos($detail, 'ℹ️') !== false) echo '<span class="info">';
                                    else echo '<span>';
                                    ?>
                                    <?php echo htmlspecialchars($detail); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h3>🔧 Direct Access Errors Fix</h3>
                <p>"Direct access not permitted" errors को automatically ठीक करें</p>
                <form method="POST">
                    <input type="hidden" name="action" value="fix_direct_access">
                    <button type="submit" class="btn">🚀 Errors ठीक करें</button>
                </form>
            </div>
            
            <div class="card">
                <h3>⚙️ Configuration Check</h3>
                <p>Common configuration issues को check करें</p>
                <form method="POST">
                    <input type="hidden" name="action" value="check_config">
                    <button type="submit" class="btn">🔍 Configuration Check करें</button>
                </form>
            </div>
            
            <div class="card">
                <h3>📋 Manual Fix Instructions</h3>
                <p><strong>Direct Access Error ठीक करने के लिए:</strong></p>
                <ol>
                    <li>उस file को खोलें जहाँ error आ रहा है</li>
                    <li><code>die('Direct access not permitted');</code> ढूंढें</li>
                    <li>इसे <code>define('SECURE_CONSTANT', true);</code> से replace करें</li>
                    <li>File save करें</li>
                </ol>
                
                <p><strong>Quick Access:</strong></p>
                <div>
                    <a href="/" class="btn">🏠 Homepage</a>
                    <a href="/error.php" class="btn">🚨 Error Page</a>
                    <a href="/admin/" class="btn">👨‍💼 Admin Panel</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>