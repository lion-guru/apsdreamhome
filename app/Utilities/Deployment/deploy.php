<?php

/**
 * APS Dream Home - Production Deployment Script
 * One-click deployment for production servers
 */

// Production deployment configuration
$productionConfig = [
    'app_url' => 'https://yourdomain.com',
    'app_env' => 'production',
    'app_debug' => false,
    'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
    'db_database' => $_ENV['DB_DATABASE'] ?? 'apsdreamhome',
    'db_username' => $_ENV['DB_USERNAME'] ?? 'root',
    'db_password' => $_ENV['DB_PASSWORD'] ?? '',
    'mail_host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
    'mail_port' => $_ENV['MAIL_PORT'] ?? '587',
    'mail_username' => $_ENV['MAIL_USERNAME'] ?? '',
    'mail_password' => $_ENV['MAIL_PASSWORD'] ?? '',
    'mail_from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@yourdomain.com',
    'mail_from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'APS Dream Home'
];

function deployProduction($config) {
    $basePath = __DIR__;
    
    echo "🚀 Starting APS Dream Home Production Deployment...\n\n";
    
    // Step 1: Environment setup
    echo "📝 Setting up production environment...\n";
    setupProductionEnv($basePath, $config);
    
    // Step 2: Database setup
    echo "🗄️ Setting up production database...\n";
    setupProductionDatabase($config);
    
    // Step 3: Security hardening
    echo "🔒 Hardening security...\n";
    hardenSecurity($basePath);
    
    // Step 4: Performance optimization
    echo "⚡ Optimizing performance...\n";
    optimizePerformance($basePath);
    
    // Step 5: Final verification
    echo "✅ Verifying deployment...\n";
    verifyDeployment($basePath);
    
    echo "\n🎉 Production deployment completed successfully!\n";
    echo "🌐 Your application is now live at: {$config['app_url']}\n";
    echo "👤 Admin login: admin@apsdreamhome.com / admin123\n";
    echo "📋 Next steps:\n";
    echo "   1. Change default admin password\n";
    echo "   2. Update email settings\n";
    echo "   3. Configure domain SSL\n";
    echo "   4. Set up monitoring\n";
}

function setupProductionEnv($basePath, $config) {
    $envContent = "# APS Dream Home Production Environment\n";
    $envContent .= "APP_ENV=production\n";
    $envContent .= "APP_DEBUG=false\n";
    $envContent .= "APP_URL={$config['app_url']}\n\n";
    $envContent .= "# Database Configuration\n";
    $envContent .= "DB_HOST={$config['db_host']}\n";
    $envContent .= "DB_DATABASE={$config['db_database']}\n";
    $envContent .= "DB_USERNAME={$config['db_username']}\n";
    $envContent .= "DB_PASSWORD={$config['db_password']}\n\n";
    $envContent .= "# Mail Configuration\n";
    $envContent .= "MAIL_HOST={$config['mail_host']}\n";
    $envContent .= "MAIL_PORT={$config['mail_port']}\n";
    $envContent .= "MAIL_USERNAME={$config['mail_username']}\n";
    $envContent .= "MAIL_PASSWORD={$config['mail_password']}\n";
    $envContent .= "MAIL_FROM_ADDRESS={$config['mail_from_address']}\n";
    $envContent .= "MAIL_FROM_NAME={$config['mail_from_name']}\n";
    
    file_put_contents($basePath . '/.env', $envContent);
    echo "   ✅ Production .env file created\n";
}

function setupProductionDatabase($config) {
    // Include auto-setup functionality
    require_once $basePath . '/auto-setup.php';
    echo "   ✅ Production database configured\n";
}

function hardenSecurity($basePath) {
    // Secure .htaccess for uploads
    $htaccessContent = "
# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
    Header always set Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'\"
</IfModule>

# Hide .htaccess and other sensitive files
<FilesMatch \"^\.|\.bak$|\.log$|\.sql$\">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevent PHP execution in uploads
<Directory \"uploads\">
    php_flag engine off
    <FilesMatch \"\.(php|phtml|pht|php3|php4|php5|phpt)$\">
        Order allow,deny
        Deny from all
    </FilesMatch>
</Directory>

# Disable directory listing
Options -Indexes

# Error handling
ErrorDocument 404 /404.html
ErrorDocument 500 /500.html
";
    
    file_put_contents($basePath . '/public/.htaccess', $htaccessContent);
    
    // Secure uploads directory
    $uploadHtaccess = "
# No PHP execution in uploads
<FilesMatch \"\.(php|phtml|pht|php3|php4|php5|phpt)$\">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Allow only images and documents
<FilesMatch \"\.(jpg|jpeg|png|gif|webp|pdf|doc|docx)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>
";
    
    file_put_contents($basePath . '/public/uploads/.htaccess', $uploadHtaccess);
    
    // Set proper permissions
    $directories = ['storage/logs', 'storage/cache', 'storage/sessions', 'public/uploads'];
    foreach ($directories as $dir) {
        $fullPath = $basePath . '/' . $dir;
        if (is_dir($fullPath)) {
            chmod($fullPath, 0755);
            // Recursively set permissions for files
            exec("find $fullPath -type d -exec chmod 755 {} +");
            exec("find $fullPath -type f -exec chmod 644 {} +");
        }
    }
    
    echo "   ✅ Security hardening completed\n";
}

function optimizePerformance($basePath) {
    // Create production cache configuration
    $cacheConfig = "<?php\n";
    $cacheConfig .= "// Production cache configuration\n";
    $cacheConfig .= "return [\n";
    $cacheConfig .= "    'default' => 'file',\n";
    $cacheConfig .= "    'stores' => [\n";
    $cacheConfig .= "        'file' => [\n";
    $cacheConfig .= "            'driver' => 'file',\n";
    $cacheConfig .= "            'path' => '{$basePath}/storage/cache',\n";
    $cacheConfig .= "        ],\n";
    $cacheConfig .= "    ],\n";
    $cacheConfig .= "];\n";
    
    file_put_contents($basePath . '/config/cache.php', $cacheConfig);
    
    // Clear any existing cache
    $cacheDir = $basePath . '/storage/cache';
    if (is_dir($cacheDir)) {
        exec("rm -rf $cacheDir/*");
    }
    
    echo "   ✅ Performance optimization completed\n";
}

function verifyDeployment($basePath) {
    $checks = [
        'Database connection' => function() use ($basePath) {
            try {
                $pdo = new PDO("mysql:host=" . ($_ENV['DB_HOST'] ?? 'localhost'), 
                              $_ENV['DB_USERNAME'] ?? 'root', 
                              $_ENV['DB_PASSWORD'] ?? '');
                return true;
            } catch (Exception $e) {
                return false;
            }
        },
        'Essential directories' => function() use ($basePath) {
            $dirs = ['storage/logs', 'storage/cache', 'public/uploads'];
            foreach ($dirs as $dir) {
                if (!is_dir($basePath . '/' . $dir)) {
                    return false;
                }
            }
            return true;
        },
        'Configuration files' => function() use ($basePath) {
            return file_exists($basePath . '/.env') && 
                   file_exists($basePath . '/config/cache.php');
        }
    ];
    
    foreach ($checks as $check => $callback) {
        if ($callback()) {
            echo "   ✅ $check\n";
        } else {
            echo "   ❌ $check\n";
        }
    }
}

// Run deployment if accessed directly
if (php_sapi_name() === 'cli' || isset(Security::sanitize($_GET['deploy']))) {
    deployProduction($productionConfig);
} else {
    echo "<h1>APS Dream Home Production Deployment</h1>";
    echo "<p>This script prepares your application for production deployment.</p>";
    echo "<p><a href='?deploy=1'>Start Deployment</a></p>";
    echo "<p><strong>Warning:</strong> This will modify your production environment.</p>";
}
?>
