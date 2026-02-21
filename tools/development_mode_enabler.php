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
// header('Content-Type: text/html; charset=utf-8'); // Removed as this is a CLI tool primarily

// Function to enable development mode
function enableDevelopmentMode() {
    $htaccessPath = __DIR__ . '/../.htaccess'; // Adjusted path to root
    
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
        file_put_contents(__DIR__ . '/../.htaccess.backup', $content);
        
        // Apply changes
        file_put_contents($htaccessPath, $newContent);
        
        return "✅ Development mode enabled - Security restrictions temporarily lifted";
    }
    
    return "❌ .htaccess file not found at $htaccessPath";
}

// Function to restore normal mode
function restoreNormalMode() {
    $backupPath = __DIR__ . '/../.htaccess.backup';
    $htaccessPath = __DIR__ . '/../.htaccess';
    
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
    $rootPath = __DIR__ . '/../';
    $filesToFix = [
        'error.php',
        'app/config/error_handler.php',
        'admin/config.php',
        'api/auth.php',
        'index.php',
        'router.php'
    ];
    
    $results = [];
    
    foreach ($filesToFix as $file) {
        $filePath = $rootPath . $file;
        
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

// CLI usage
if (php_sapi_name() === 'cli') {
    echo "Development Mode Tool\n";
    echo "1. Enable Development Mode\n";
    echo "2. Restore Normal Mode\n";
    echo "3. Fix File Security Checks\n";
    echo "Select option: ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $option = trim($line);
    
    switch ($option) {
        case '1':
            echo enableDevelopmentMode() . "\n";
            break;
        case '2':
            echo restoreNormalMode() . "\n";
            break;
        case '3':
            print_r(fixFileSecurityChecks());
            break;
        default:
            echo "Invalid option\n";
    }
}
