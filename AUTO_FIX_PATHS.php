<?php
/**
 * APS Dream Home - Auto Fix Paths
 * Automatically fix all path and routing issues in the project
 */

echo "🔧 APS DREAM HOME - AUTO FIX PATHS\n";
echo "==================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Fix results
$fixResults = [];
$totalFixes = 0;
$successfulFixes = 0;

echo "🔧 AUTO-FIXING ALL PATH & ROUTING ISSUES...\n\n";

// 1. Fix BASE_URL configuration
echo "Step 1: Fixing BASE_URL configuration\n";
$baseUrlFix = [
    'fix_base_url' => function() {
        $pathsFile = BASE_PATH . '/config/paths.php';
        
        if (!file_exists($pathsFile)) {
            return ['status' => 'ERROR', 'message' => 'paths.php not found'];
        }
        
        $content = file_get_contents($pathsFile);
        
        // Determine correct BASE_URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = '/apsdreamhome';
        $correctBaseUrl = $protocol . '://' . $host . $path;
        
        // Replace BASE_URL definition
        $newContent = preg_replace(
            "/define\(['\"]BASE_URL['\"],\s*['\"]([^'\"]+)['\"]\)/",
            "define('BASE_URL', '$correctBaseUrl')",
            $content
        );
        
        if ($newContent !== $content) {
            file_put_contents($pathsFile, $newContent);
            return ['status' => 'SUCCESS', 'base_url' => $correctBaseUrl, 'message' => "BASE_URL updated to: $correctBaseUrl"];
        }
        
        return ['status' => 'NO_CHANGE', 'message' => 'BASE_URL already correct'];
    }
];

foreach ($baseUrlFix as $fixName => $fixFunction) {
    echo "   🔧 Applying $fixName...\n";
    $result = $fixFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'NO_CHANGE' ? '➡️' : '❌');
    echo "      $status {$result['message']}\n";
    
    $fixResults['base_url'][$fixName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $successfulFixes++;
    }
    $totalFixes++;
}

echo "\n";

// 2. Fix .htaccess configuration
echo "Step 2: Fixing .htaccess configuration\n";
$htaccessFix = [
    'fix_public_htaccess' => function() {
        $htaccessPath = BASE_PATH . '/public/.htaccess';
        
        // Create proper .htaccess content
        $htaccessContent = '# APS Dream Home - Apache Configuration
# Enable URL rewriting
RewriteEngine On

# Set the base directory
RewriteBase /apsdreamhome/

# Handle requests to public directory
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Route to index.php
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# PHP configuration
<IfModule mod_php.c>
    php_flag display_errors Off
    php_flag log_errors On
    php_value error_log ' . BASE_PATH . '/logs/php_errors.log
    php_value max_execution_time 300
    php_value memory_limit 256M
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value date.timezone Asia/Kolkata
</IfModule>

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/ico "access plus 1 year"
    ExpiresByType image/svg "access plus 1 year"
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Block access to sensitive files
<Files ~ "^\.">
    Order allow,deny
    Deny from all
</Files>

<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|inc|bak|backup|old|tmp|cache)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Directory protection
<IfModule mod_autoindex.c>
    Options -Indexes
</IfModule>

# Default character set
AddDefaultCharset UTF-8

# Error pages
ErrorDocument 404 /apsdreamhome/public/error/404.php
ErrorDocument 500 /apsdreamhome/public/error/500.php
';
        
        file_put_contents($htaccessPath, $htaccessContent);
        return ['status' => 'SUCCESS', 'message' => 'public/.htaccess updated'];
    },
    'create_root_htaccess' => function() {
        $htaccessPath = BASE_PATH . '/.htaccess';
        
        $htaccessContent = '# APS Dream Home - Root .htaccess
# Redirect all requests to public directory
RewriteEngine On
RewriteBase /apsdreamhome/

# Redirect to public directory
RewriteRule ^(.*)$ public/$1 [L]
';
        
        file_put_contents($htaccessPath, $htaccessContent);
        return ['status' => 'SUCCESS', 'message' => 'Root .htaccess created'];
    }
];

foreach ($htaccessFix as $fixName => $fixFunction) {
    echo "   🔧 Applying $fixName...\n";
    $result = $fixFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : '❌';
    echo "      $status {$result['message']}\n";
    
    $fixResults['htaccess'][$fixName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $successfulFixes++;
    }
    $totalFixes++;
}

echo "\n";

// 3. Fix hardcoded paths in PHP files
echo "Step 3: Fixing hardcoded paths in PHP files\n";
$pathFix = [
    'fix_hardcoded_paths' => function() {
        $fixedFiles = 0;
        $totalFixes = 0;
        
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                $relativePath = str_replace(BASE_PATH . '/', '', $filePath);
                
                // Skip backup and legacy files
                if (strpos($relativePath, '_backup') !== false || strpos($relativePath, 'legacy') !== false) {
                    continue;
                }
                
                $content = file_get_contents($filePath);
                $originalContent = $content;
                
                // Fix hardcoded apsdreamhome paths
                $content = preg_replace(
                    '/([\'"])(\/?apsdreamhome\/)([a-zA-Z0-9\/\._-]+)([\'"])/',
                    '$1' . BASE_URL . '/$3$4',
                    $content
                );
                
                // Fix relative paths that should use BASE_URL
                $content = preg_replace(
                    '/(href|action|src)\s*=\s*([\'"])(?!http|https|\/\/|#)([a-zA-Z0-9\/\._-]+)([\'"])/',
                    '$1=$2' . BASE_URL . '/$3$4',
                    $content
                );
                
                // Fix PHP echo statements with base_url
                $content = preg_replace(
                    '/<\?php\s+echo\s+\$base_url\s*\?\s*>/',
                    BASE_URL,
                    $content
                );
                
                if ($content !== $originalContent) {
                    file_put_contents($filePath, $content);
                    $fixedFiles++;
                    $totalFixes++;
                }
            }
        }
        
        return [
            'status' => 'SUCCESS',
            'files_fixed' => $fixedFiles,
            'total_fixes' => $totalFixes,
            'message' => "Fixed paths in $fixedFiles files"
        ];
    }
];

foreach ($pathFix as $fixName => $fixFunction) {
    echo "   🔧 Applying $fixName...\n";
    $result = $fixFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : '❌';
    echo "      $status {$result['message']}\n";
    
    $fixResults['path_fix'][$fixName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $successfulFixes++;
    }
    $totalFixes++;
}

echo "\n";

// 4. Fix navigation and menu links
echo "Step 4: Fixing navigation and menu links\n";
$navigationFix = [
    'fix_header_navigation' => function() {
        $headerFile = BASE_PATH . '/app/views/layouts/header.php';
        
        if (!file_exists($headerFile)) {
            return ['status' => 'ERROR', 'message' => 'header.php not found'];
        }
        
        $content = file_get_contents($headerFile);
        $originalContent = $content;
        
        // Fix navigation links
        $patterns = [
            '/href\s*=\s*([\'"])(?!http|https|\/\/|#)([a-zA-Z0-9\/\._-]+)([\'"])/' => 'href=$1' . BASE_URL . '/$2$3',
            '/<a\s+href\s*=\s*([\'"])(?!http|https|\/\/|#)([a-zA-Z0-9\/\._-]+)([\'"])/' => '<a href="$1' . BASE_URL . '/$2$3',
            '/action\s*=\s*([\'"])(?!http|https|\/\/|#)([a-zA-Z0-9\/\._-]+)([\'"])/' => 'action=$1' . BASE_URL . '/$2$3'
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($headerFile, $content);
            return ['status' => 'SUCCESS', 'message' => 'Header navigation fixed'];
        }
        
        return ['status' => 'NO_CHANGE', 'message' => 'Header navigation already correct'];
    },
    'fix_footer_links' => function() {
        $footerFile = BASE_PATH . '/app/views/layouts/footer.php';
        
        if (!file_exists($footerFile)) {
            return ['status' => 'ERROR', 'message' => 'footer.php not found'];
        }
        
        $content = file_get_contents($footerFile);
        $originalContent = $content;
        
        // Fix footer links
        $content = preg_replace(
            '/href\s*=\s*([\'"])(?!http|https|\/\/|#)([a-zA-Z0-9\/\._-]+)([\'"])/',
            'href=$1' . BASE_URL . '/$2$3',
            $content
        );
        
        if ($content !== $originalContent) {
            file_put_contents($footerFile, $content);
            return ['status' => 'SUCCESS', 'message' => 'Footer links fixed'];
        }
        
        return ['status' => 'NO_CHANGE', 'message' => 'Footer links already correct'];
    }
];

foreach ($navigationFix as $fixName => $fixFunction) {
    echo "   🔧 Applying $fixName...\n";
    $result = $fixFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'NO_CHANGE' ? '➡️' : '❌');
    echo "      $status {$result['message']}\n";
    
    $fixResults['navigation'][$fixName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $successfulFixes++;
    }
    $totalFixes++;
}

echo "\n";

// 5. Fix form actions and method calls
echo "Step 5: Fixing form actions and method calls\n";
$formFix = [
    'fix_form_actions' => function() {
        $fixedForms = 0;
        
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH . '/app/views'));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                $content = file_get_contents($filePath);
                $originalContent = $content;
                
                // Fix form actions
                $content = preg_replace(
                    '/<form\s+[^>]*action\s*=\s*([\'"])(?!http|https|\/\/|#)([a-zA-Z0-9\/\._-]+)([\'"])/',
                    '<form action="$1' . BASE_URL . '/$2$3',
                    $content
                );
                
                // Fix method calls in forms
                $content = preg_replace(
                    '/method\s*=\s*([\'"])(post|get)([\'"])/',
                    'method=$1$2$3',
                    $content
                );
                
                if ($content !== $originalContent) {
                    file_put_contents($filePath, $content);
                    $fixedForms++;
                }
            }
        }
        
        return [
            'status' => 'SUCCESS',
            'forms_fixed' => $fixedForms,
            'message' => "Fixed $fixedForms form actions"
        ];
    }
];

foreach ($formFix as $fixName => $fixFunction) {
    echo "   🔧 Applying $fixName...\n";
    $result = $fixFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : '❌';
    echo "      $status {$result['message']}\n";
    
    $fixResults['form_fix'][$fixName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $successfulFixes++;
    }
    $totalFixes++;
}

echo "\n";

// 6. Create routing helper functions
echo "Step 6: Creating routing helper functions\n";
$routingHelper = [
    'create_url_helper' => function() {
        $helperFile = BASE_PATH . '/app/Helpers/UrlHelper.php';
        $helperDir = dirname($helperFile);
        
        if (!is_dir($helperDir)) {
            mkdir($helperDir, 0755, true);
        }
        
        $helperContent = '<?php
/**
 * APS Dream Home - URL Helper Functions
 * Centralized URL generation and path management
 */

if (!function_exists(\'base_url\')) {
    /**
     * Get base URL
     * @param string $path Optional path to append
     * @return string Complete URL
     */
    function base_url($path = \'\') {
        $baseUrl = BASE_URL;
        return $baseUrl . \'/\' . ltrim($path, \'/\');
    }
}

if (!function_exists(\'asset_url\')) {
    /**
     * Get asset URL
     * @param string $asset Asset path
     * @return string Complete asset URL
     */
    function asset_url($asset) {
        return base_url(\'public/assets/\' . ltrim($asset, \'/\'));
    }
}

if (!function_exists(\'route_url\')) {
    /**
     * Get route URL
     * @param string $route Route name
     * @param array $params Route parameters
     * @return string Complete route URL
     */
    function route_url($route, $params = []) {
        $baseUrl = base_url();
        
        // Basic routing logic
        switch ($route) {
            case \'home\':
                return $baseUrl;
            case \'properties\':
                return $baseUrl . \'/properties\';
            case \'about\':
                return $baseUrl . \'/about\';
            case \'contact\':
                return $baseUrl . \'/contact\';
            case \'login\':
                return $baseUrl . \'/login\';
            case \'register\':
                return $baseUrl . \'/register\';
            case \'dashboard\':
                return $baseUrl . \'/dashboard\';
            default:
                return $baseUrl . \'/\' . $route;
        }
    }
}

if (!function_exists(\'current_url\')) {
    /**
     * Get current URL
     * @return string Current URL
     */
    function current_url() {
        $protocol = isset($_SERVER[\'HTTPS\']) && $_SERVER[\'HTTPS\'] === \'on\' ? \'https\' : \'http\';
        $host = $_SERVER[\'HTTP_HOST\'];
        $uri = $_SERVER[\'REQUEST_URI\'];
        return $protocol . \'://\' . $host . $uri;
    }
}

if (!function_exists(\'is_current_page\')) {
    /**
     * Check if current page matches given path
     * @param string $path Path to check
     * @return bool True if current page
     */
    function is_current_page($path) {
        $currentUri = $_SERVER[\'REQUEST_URI\'];
        $targetPath = \'/\' . ltrim($path, \'/\');
        return strpos($currentUri, $targetPath) !== false;
    }
}

if (!function_exists(\'is_active_path\')) {
    /**
     * Check if current path is active (for navigation)
     * @param string $path Path to check
     * @return string Active class if current page
     */
    function is_active_path($path) {
        return is_current_page($path) ? \'active\' : \'\';
    }
}
?>';
        
        file_put_contents($helperFile, $helperContent);
        return ['status' => 'SUCCESS', 'message' => 'URL helper functions created'];
    },
    'update_composer_autoload' => function() {
        $composerFile = BASE_PATH . '/composer.json';
        
        if (!file_exists($composerFile)) {
            return ['status' => 'ERROR', 'message' => 'composer.json not found'];
        }
        
        $content = file_get_contents($composerFile);
        $data = json_decode($content, true);
        
        // Add helpers to autoload
        if (!isset($data['autoload']['files'])) {
            $data['autoload']['files'] = [];
        }
        
        $helperPath = 'app/Helpers/UrlHelper.php';
        if (!in_array($helperPath, $data['autoload']['files'])) {
            $data['autoload']['files'][] = $helperPath;
            
            file_put_contents($composerFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return ['status' => 'SUCCESS', 'message' => 'Composer autoload updated'];
        }
        
        return ['status' => 'NO_CHANGE', 'message' => 'Composer autoload already includes helpers'];
    }
];

foreach ($routingHelper as $fixName => $fixFunction) {
    echo "   🔧 Applying $fixName...\n";
    $result = $fixFunction();
    $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'NO_CHANGE' ? '➡️' : '❌');
    echo "      $status {$result['message']}\n";
    
    $fixResults['routing_helper'][$fixName] = $result;
    if ($result['status'] === 'SUCCESS') {
        $successfulFixes++;
    }
    $totalFixes++;
}

echo "\n";

// 7. Generate fix report
echo "Step 7: Generating fix report\n";
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_fixes_attempted' => $totalFixes,
    'successful_fixes' => $successfulFixes,
    'success_rate' => round(($successfulFixes / $totalFixes) * 100, 1),
    'fix_results' => $fixResults,
    'base_url' => BASE_URL,
    'summary' => [
        'base_url_fixed' => isset($fixResults['base_url']['fix_base_url']['status']) && $fixResults['base_url']['fix_base_url']['status'] === 'SUCCESS',
        'htaccess_fixed' => isset($fixResults['htaccess']['fix_public_htaccess']['status']) && $fixResults['htaccess']['fix_public_htaccess']['status'] === 'SUCCESS',
        'paths_fixed' => isset($fixResults['path_fix']['fix_hardcoded_paths']['status']) && $fixResults['path_fix']['fix_hardcoded_paths']['status'] === 'SUCCESS',
        'navigation_fixed' => isset($fixResults['navigation']['fix_header_navigation']['status']) && $fixResults['navigation']['fix_header_navigation']['status'] === 'SUCCESS',
        'forms_fixed' => isset($fixResults['form_fix']['fix_form_actions']['status']) && $fixResults['form_fix']['fix_form_actions']['status'] === 'SUCCESS',
        'helpers_created' => isset($fixResults['routing_helper']['create_url_helper']['status']) && $fixResults['routing_helper']['create_url_helper']['status'] === 'SUCCESS'
    ]
];

// Save report
$reportFile = BASE_PATH . '/logs/auto_fix_paths_report.json';
file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
echo "   ✅ Report saved to: $reportFile\n";

echo "\n";

// 8. Display summary
echo "====================================================\n";
echo "🔧 AUTO FIX PATHS SUMMARY\n";
echo "====================================================\n";

echo "📊 TOTAL FIXES ATTEMPTED: $totalFixes\n";
echo "✅ SUCCESSFUL FIXES: $successfulFixes\n";
echo "📊 SUCCESS RATE: " . round(($successfulFixes / $totalFixes) * 100, 1) . "%\n\n";

echo "📋 FIX DETAILS:\n";
foreach ($fixResults as $category => $fixes) {
    echo "🔧 $category:\n";
    foreach ($fixes as $fixName => $result) {
        $status = $result['status'] === 'SUCCESS' ? '✅' : ($result['status'] === 'NO_CHANGE' ? '➡️' : '❌');
        echo "   $status $fixName: {$result['message']}\n";
    }
    echo "\n";
}

echo "🎯 CURRENT BASE_URL: " . BASE_URL . "\n\n";

if ($successfulFixes >= $totalFixes * 0.8) {
    echo "🎉 AUTO-FIX RESULT: EXCELLENT!\n";
    echo "✅ Most issues have been fixed automatically\n";
} elseif ($successfulFixes >= $totalFixes * 0.6) {
    echo "✅ AUTO-FIX RESULT: GOOD!\n";
    echo "⚠️  Most issues fixed, some manual attention may be needed\n";
} else {
    echo "❌ AUTO-FIX RESULT: NEEDS ATTENTION!\n";
    echo "🚨 Some fixes failed, manual intervention required\n";
}

echo "\n🎯 NEXT STEPS:\n";
echo "1. Test all navigation links\n";
echo "2. Verify form submissions work\n";
echo "3. Check image and asset loading\n";
echo "4. Test routing functionality\n";
echo "5. Run: php VERIFY_PATHS_FIX.php\n";
echo "6. Restart web server if needed\n";

echo "\n🔧 MANUAL FIXES NEEDED:\n";
echo "1. Check any remaining broken links\n";
echo "2. Test navigation in browser\n";
echo "3. Verify all pages load correctly\n";
echo "4. Check form functionality\n";
echo "5. Test user authentication flows\n";

echo "\n🎊 AUTO PATH FIXING COMPLETE! 🎊\n";
echo "📊 Status: " . ($successfulFixes >= $totalFixes * 0.8 ? 'SUCCESS' : 'NEEDS_ATTENTION') . "\n";
?>
