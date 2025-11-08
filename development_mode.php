<?php
/**
 * Development Mode Enabler
 * Development के दौरान security restrictions को temporarily disable करने के लिए
 */

// Allow direct access for this tool
if (!defined('DEV_MODE_ENABLER')) {
    define('DEV_MODE_ENABLER', true);
}

// Set content type
header('Content-Type: text/html; charset=utf-8');

// Function to enable development mode
function enableDevelopmentMode() {
    $htaccessPath = __DIR__ . '/.htaccess';
    
    if (file_exists($htaccessPath)) {
        $content = file_get_contents($htaccessPath);
        
        // Temporary modifications for development
        $newContent = str_replace(
            [
                'php_flag display_errors Off',
                'Order allow,deny\n    Deny from all',
                'RewriteRule ^(.*)$ router.php?url=$1 [QSA,L]'
            ],
            [
                'php_flag display_errors On',
                'Order allow,deny\n    Allow from all',
                '# Development mode - Direct file access allowed\n    # RewriteRule ^(.*)$ router.php?url=$1 [QSA,L]'
            ],
            $content
        );
        
        // Create backup
        file_put_contents(__DIR__ . '/.htaccess.backup', $content);
        
        // Apply changes
        file_put_contents($htaccessPath, $newContent);
        
        return "✅ Development mode enabled - Security restrictions temporarily lifted";
    }
    
    return "❌ .htaccess file not found";
}

// Function to restore normal mode
function restoreNormalMode() {
    $backupPath = __DIR__ . '/.htaccess.backup';
    $htaccessPath = __DIR__ . '/.htaccess';
    
    if (file_exists($backupPath)) {
        $backupContent = file_get_contents($backupPath);
        file_put_contents($htaccessPath, $backupContent);
        unlink($backupPath);
        
        return "✅ Normal mode restored - Security re-enabled";
    }
    
    return "ℹ️ No backup found - Normal mode already active";
}

// Function to fix file security checks
function fixFileSecurityChecks() {
    $filesToFix = [
        'error.php',
        'includes/error_handler.php',
        'admin/config.php',
        'api/auth.php',
        'index.php',
        'router.php'
    ];
    
    $results = [];
    
    foreach ($filesToFix as $file) {
        $filePath = __DIR__ . '/' . $file;
        
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            
            // Replace die/exit with continue for development
            $newContent = preg_replace(
                '/die\([\'\"]Direct access not permitted[\'\"]\);/i',
                '// Development mode: Direct access allowed\n    if (!defined(\'DEV_MODE\')) define(\'DEV_MODE\', true);',
                $content
            );
            
            file_put_contents($filePath, $newContent);
            $results[] = "✅ $file - Security check modified for development";
        } else {
            $results[] = "ℹ️ $file - File not found (skipped)";
        }
    }
    
    return $results;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'enable_dev_mode') {
        $result = enableDevelopmentMode();
        $details = fixFileSecurityChecks();
        array_unshift($details, $result);
        $message = "Development Mode Activated";
    } elseif ($action === 'restore_normal') {
        $result = restoreNormalMode();
        $details = [$result];
        $message = "Normal Mode Restored";
    } elseif ($action === 'fix_security') {
        $details = fixFileSecurityChecks();
        $message = "Security Checks Modified";
    }
}

?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Development Mode - APS Dream Home</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; padding: 20px;
        }
        .container { 
            max-width: 900px; margin: 0 auto; background: white;
            border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header { 
            background: #2c3e50; color: white; padding: 30px; text-align: center;
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .content { padding: 30px; }
        .card { 
            background: #f8f9fa; border-radius: 10px; padding: 20px;
            margin-bottom: 20px; border-left: 4px solid #3498db;
        }
        .card h3 { color: #2c3e50; margin-bottom: 15px; }
        .btn { 
            background: #27ae60; color: white; border: none; padding: 12px 25px;
            border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold;
            transition: all 0.3s ease; display: inline-block; text-decoration: none;
            margin: 5px;
        }
        .btn:hover { 
            background: #219a52; transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-warning { background: #f39c12; }
        .btn-danger { background: #e74c3c; }
        .results { 
            background: #2c3e50; color: white; padding: 20px; border-radius: 8px;
            margin-top: 20px;
        }
        .result-item { 
            padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .success { color: #27ae60; }
        .warning { color: #f39c12; }
        .error { color: #e74c3c; }
        .info { color: #3498db; }
        .warning-box { 
            background: #fff3cd; border: 1px solid #ffeaa7; color: #856404;
            padding: 15px; border-radius: 5px; margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Development Mode</h1>
            <p>Development के दौरान security restrictions को temporarily disable करें</p>
        </div>
        
        <div class="content">
            <div class="warning-box">
                <strong>⚠️ चेतावनी:</strong> यह mode केवल development के लिए है। Production में इसे कभी भी enable न करें!
            </div>
            
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
                <h3>🔧 Development Mode Enable करें</h3>
                <p>सभी security restrictions को temporarily disable करें</p>
                <form method="POST">
                    <input type="hidden" name="action" value="enable_dev_mode">
                    <button type="submit" class="btn">🚀 Development Mode चालू करें</button>
                </form>
            </div>
            
            <div class="card">
                <h3>⚡ Quick Security Fix</h3>
                <p>सिर्फ file security checks को modify करें</p>
                <form method="POST">
                    <input type="hidden" name="action" value="fix_security">
                    <button type="submit" class="btn">🔒 Security Checks Fix करें</button>
                </form>
            </div>
            
            <div class="card">
                <h3>🔙 Normal Mode Restore करें</h3>
                <p>Security restrictions को वापस enable करें</p>
                <form method="POST">
                    <input type="hidden" name="action" value="restore_normal">
                    <button type="submit" class="btn btn-warning">🛡️ Normal Mode वापस करें</button>
                </form>
            </div>
            
            <div class="card">
                <h3>📋 Direct Access Links</h3>
                <p>Development mode में इन pages को directly access करें:</p>
                <div>
                    <a href="/apsdreamhome/error.php" class="btn" target="_blank">🚨 Error Page</a>
                    <a href="/apsdreamhome/index.php" class="btn" target="_blank">🏠 Homepage</a>
                    <a href="/apsdreamhome/admin/" class="btn" target="_blank">👨‍💼 Admin</a>
                    <a href="/apsdreamhome/fix_errors.php" class="btn" target="_blank">🛠️ Fix Errors</a>
                </div>
                
                <p style="margin-top: 15px;"><strong>Note:</strong> Development mode में सीधे URLs use करें:</p>
                <code>http://localhost/apsdreamhome/error.php</code>
            </div>
        </div>
    </div>
</body>
</html>