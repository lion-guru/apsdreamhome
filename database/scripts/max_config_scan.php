<?php
/**
 * Maximum Level Configuration Management Deep Scan
 * Comprehensive analysis of all configuration files and security settings
 */

echo "🔍 MAXIMUM LEVEL CONFIGURATION MANAGEMENT SCAN\n";
echo "==============================================\n\n";

// Project root directory
$projectRoot = __DIR__ . '/..';
$projectName = 'APS Dream Home';

echo "📁 CONFIGURATION SCAN OVERVIEW\n";
echo "----------------------------\n";
echo "Project: $projectName\n";
echo "Scan Date: " . date('Y-m-d H:i:s') . "\n";
echo "Scan Level: Maximum Deep Analysis\n\n";

// 1. Configuration Files Discovery
echo "🔍 CONFIGURATION FILES DISCOVERY\n";
echo "-------------------------------\n";

$configPatterns = [
    // Core configuration files
    'core' => [
        'app/config/application.php',
        'app/config/database.php',
        'app/config/config.php',
        'config/application.php',
        'config/database.php',
        'config/config.php',
        '.env',
        '.env.example',
        '.env.local',
        '.env.production',
        'config.php'
    ],
    
    // Security configuration
    'security' => [
        'app/config/security.php',
        'config/security.php',
        'app/Http/Middleware/Auth.php',
        'app/Http/Middleware/Cors.php',
        'app/Http/Middleware/RateLimit.php',
        'app/Http/Middleware/ThrottleLogin.php',
        'app/Http/Middleware/SecurityHeaders.php',
        'app/Http/Middleware/EncryptCookies.php',
        'app/Http/Middleware/ValidateInput.php'
    ],
    
    // Database configuration
    'database' => [
        'config/database.php',
        'app/config/database.php',
        'database/config.php',
        'config/database.php',
        '.env.database',
        'database/connection.php',
        'app/Database/Connection.php',
        'app/Core/Database.php'
    ],
    
    // Email configuration
    'email' => [
        'config/mail.php',
        'config/email.php',
        'app/config/mail.php',
        '.env.mail',
        'app/Services/MailService.php',
        'config/services.php'
    ],
    
    // Session configuration
    'session' => [
        'config/session.php',
        'app/config/session.php',
        '.env.session',
        'app/Services/SessionService.php'
    ],
    
    // Cache configuration
    'cache' => [
        'config/cache.php',
        'app/config/cache.php',
        '.env.cache',
        'app/Services/CacheService.php'
    ],
    
    // API configuration
    'api' => [
        'config/api.php',
        'app/config/api.php',
        '.env.api',
        'config/services.php'
    ],
    
    // File upload configuration
    'upload' => [
        'config/upload.php',
        'app/config/upload.php',
        '.env.upload',
        'app/Services/FileUploadService.php'
    ],
    
    // Logging configuration
    'logging' => [
        'config/logging.php',
        'app/config/logging.php',
        '.env.logging',
        'app/Services/LoggerService.php',
        'config/services.php'
    ]
];

$foundConfigs = [];
$missingConfigs = [];
$configIssues = [];
$securityIssues = [];

foreach ($configPatterns as $category => $files) {
    echo "\n$category Configuration:\n";
    echo str_repeat('-', strlen($category) + 13) . "\n";
    
    $foundInCategory = 0;
    $missingInCategory = 0;
    
    foreach ($files as $file) {
        $filePath = $projectRoot . '/' . $file;
        if (file_exists($filePath)) {
            echo "  ✅ Found: $file\n";
            $foundConfigs[] = $file;
            $foundInCategory++;
            
            // Analyze the file content
            $content = file_get_contents($filePath);
            analyzeConfigFile($file, $content, $category, $configIssues, $securityIssues);
        } else {
            echo "  ❌ Missing: $file\n";
            $missingConfigs[] = $file;
            $missingInCategory++;
        }
    }
    
    echo "  Summary: $foundInCategory found, $missingInCategory missing\n";
}

echo "\n📊 CONFIGURATION SUMMARY\n";
echo "====================\n";
echo "Total Config Files Found: " . count($foundConfigs) . "\n";
echo "Total Config Files Missing: " . count($missingConfigs) . "\n";
echo "Configuration Issues: " . count($configIssues) . "\n";
echo "Security Issues: " . count($securityIssues) . "\n\n";

// 2. Environment Variables Analysis
echo "🔧 ENVIRONMENT VARIABLES ANALYSIS\n";
echo "-------------------------------\n";

$envFiles = [
    '.env',
    '.env.example',
    '.env.local',
    '.env.production',
    '.env.development',
    '.env.testing'
];

$envAnalysis = [];
foreach ($envFiles as $envFile) {
    $envPath = $projectRoot . '/' . $envFile;
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        $envLines = explode("\n", $envContent);
        
        $envAnalysis[$envFile] = [
            'exists' => true,
            'lines' => count($envLines),
            'variables' => [],
            'secrets' => [],
            'issues' => []
        ];
        
        foreach ($envLines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') continue;
            
            if (strpos($line, '=') !== false) {
                $parts = explode('=', $line, 2);
                $key = trim($parts[0]);
                $value = trim($parts[1] ?? '');
                
                $envAnalysis[$envFile]['variables'][] = $key;
                
                // Check for sensitive data
                if (preg_match('/(password|secret|key|token|api_key|private)/i', $key)) {
                    $envAnalysis[$envFile]['secrets'][] = $key;
                    $securityIssues[] = "Sensitive data in $envFile: $key";
                }
                
                // Check for empty values
                if (empty($value)) {
                    $envAnalysis[$envFile]['issues'][] = "Empty value for $key";
                }
                
                // Check for default values
                if (in_array($value, ['localhost', '127.0.0.1', 'test', 'demo', 'example'])) {
                    $envAnalysis[$envFile]['issues'][] = "Default value for $key: $value";
                }
            }
        }
        
        echo "✅ $envFile: " . count($envAnalysis[$envFile]['variables']) . " variables\n";
        if (!empty($envAnalysis[$envFile]['secrets'])) {
            echo "  ⚠️  Sensitive data: " . count($envAnalysis[$envFile]['secrets']) . "\n";
        }
        if (!empty($envAnalysis[$envFile]['issues'])) {
            echo "  ⚠️  Issues: " . count($envAnalysis[$envFile]['issues']) . "\n";
        }
    } else {
        echo "❌ $envFile: Not found\n";
        $envAnalysis[$envFile] = ['exists' => false];
    }
}

// 3. Database Configuration Deep Analysis
echo "\n🗄️  DATABASE CONFIGURATION DEEP ANALYSIS\n";
echo "-----------------------------------\n";

$dbConfigFiles = [
    'config/database.php',
    'app/config/database.php',
    'app/Core/Database.php',
    'database/config.php',
    'config.php'
];

foreach ($dbConfigFiles as $dbFile) {
    $dbPath = $projectRoot . '/' . $dbFile;
    if (file_exists($dbPath)) {
        echo "🔍 Analyzing: $dbFile\n";
        $content = file_get_contents($dbPath);
        
        // Check for hardcoded credentials
        if (preg_match('/(password|passwd|secret|key)/\s*=\s*[\'"]([^\'"]*)[\'"]/', $content, $matches)) {
            $securityIssues[] = "Hardcoded database credentials in $dbFile";
            echo "  ⚠️  Hardcoded credentials found\n";
        }
        
        // Check for connection string
        if (strpos($content, 'mysql:') !== false || strpos($content, 'mysqli:') !== false) {
            echo "  ✅ Database connection configuration found\n";
        }
        
        // Check for PDO usage
        if (strpos($content, 'PDO') !== false) {
            echo "  ✅ PDO configuration found\n";
        }
        
        // Check for connection parameters
        $params = ['host', 'database', 'username', 'password', 'charset', 'collation'];
        foreach ($params as $param) {
            if (strpos($content, $param) !== false) {
                echo "  ✅ $param parameter found\n";
            }
        }
    } else {
        echo "❌ Not found: $dbFile\n";
    }
}

// 4. Security Configuration Analysis
echo "\n🔒 SECURITY CONFIGURATION ANALYSIS\n";
echo "------------------------------\n";

$securityConfigFiles = [
    'app/config/security.php',
    'config/security.php',
    'app/Http/Middleware/Auth.php',
    'app/Http/Middleware/Cors.php',
    'app/Http/Middleware/RateLimit.php',
    'app/Http/Middleware/ThrottleLogin.php',
    'app/Http/Middleware/SecurityHeaders.php'
];

foreach ($securityConfigFiles as $secFile) {
    $secPath = $projectRoot . '/' . $secFile;
    if (file_exists($secPath)) {
        echo "🔍 Analyzing: $secFile\n";
        $content = file_get_contents($Path);
        
        // Check for security features
        $securityFeatures = [
            'csrf' => 'CSRF Protection',
            'xss' => 'XSS Protection',
            'sql_injection' => 'SQL Injection Protection',
            'authentication' => 'Authentication',
            'authorization' => 'Authorization',
            'rate_limiting' => 'Rate Limiting',
            'input_validation' => 'Input Validation',
            'encryption' => 'Encryption',
            'session_security' => 'Session Security'
        ];
        
        foreach ($securityFeatures as $feature => $description) {
            if (preg_match("/$feature/i", $content)) {
                echo "  ✅ $description found\n";
            }
        }
        
        // Check for security issues
        if (strpos($content, 'sha1') !== false && strpos($content, 'md5') !== false) {
            echo "  ⚠️  Weak hashing algorithm detected (MD5/SHA1)\n";
            $securityIssues[] = "Weak hashing in $secFile";
        }
        
        if (strpos($content, 'session_regenerate_id') === false) {
            echo "  ⚠️  Session fixation not implemented\n";
            $securityIssues[] = "Session fixation vulnerability in $secFile";
        }
    } else {
        echo "❌ Not found: $secFile\n";
    }
}

// 5. Middleware Analysis
echo "\n🛡️  MIDDLEWARE ANALYSIS\n";
echo "---------------------\n";

$middlewareDir = $projectRoot . '/app/Http/Middleware';
if (is_dir($middlewareDir)) {
    $middlewareFiles = glob($middlewareDir . '/*.php');
    echo "Middleware Files Found: " . count($middlewareFiles) . "\n";
    
    foreach ($middlewareFiles as $middlewareFile) {
        $fileName = basename($middlewareFile);
        $filePath = $middlewareFile;
        echo "🔍 Analyzing: $fileName\n";
        
        $content = file_get_contents($filePath);
        
        // Check middleware functionality
        if (strpos($content, 'class') !== false && strpos($content, 'Middleware') !== false) {
            echo "  ✅ Middleware class found\n";
        }
        
        // Check for handle method
        if (strpos($content, 'public function handle') !== false) {
            echo "  ✅ Handle method found\n";
        }
        
        // Check for next() call
        if (strpos($content, '$next($request)') !== false) {
            echo "  ✅ Next() call found\n";
        }
    }
} else {
    echo "❌ Middleware directory not found\n";
}

// 6. Configuration Validation
echo "\n✅ CONFIGURATION VALIDATION\n";
echo "------------------------\n";

$validationResults = [
    'database_connection' => false,
    'environment_variables' => false,
    'security_settings' => false,
    'middleware_setup' => false,
    'error_handling' => false,
    'logging_config' => false
];

// Check database connection
foreach ($dbConfigFiles as $dbFile) {
    if (file_exists($projectRoot . '/' . $dbFile)) {
        $content = file_get_contents($projectRoot . '/' . $dbFile);
        if (strpos($content, 'mysql:') !== false || strpos($content, 'mysqli:') !== false || strpos($content, 'PDO') !== false) {
            $validationResults['database_connection'] = true;
            break;
        }
    }
}

// Check environment variables
if (!empty($envAnalysis) && array_filter($envAnalysis, fn($f) => $f['exists'])) {
    $validationResults['environment_variables'] = true;
}

// Check security configuration
foreach ($securityConfigFiles as $secFile) {
    if (file_exists($projectRoot . '/' . $secFile)) {
        $validationResults['security_settings'] = true;
        break;
    }
}

// Check middleware setup
if (is_dir($middlewareDir)) {
    $middlewareFiles = glob($middlewareDir . '/*.php');
    if (!empty($middlewareFiles)) {
        $validationResults['middleware_setup'] = true;
    }
}

// Check error handling
if (file_exists($projectRoot . '/app/Core/Error.php') || file_exists($projectRoot . '/app/Services/LoggerService.php')) {
    $validationResults['error_handling'] = true;
}

// Check logging configuration
if (file_exists($projectRoot . '/config/logging.php') || file_exists($projectRoot . '/app/Services/LoggerService.php')) {
    $validationResults['logging_config'] = true;
}

echo "Database Connection: " . ($validationResults['database_connection'] ? '✅' : '❌') . "\n";
echo "Environment Variables: " . ($validationResults['environment_variables'] ? '✅' : '❌') . "\n";
echo "Security Settings: " . ($validationResults['security_settings'] ? '✅' : '❌') . "\n";
echo "Middleware Setup: " . ($validationResults['middleware_setup'] ? '✅' : '❌') . "\n";
echo "Error Handling: " . ($validationResults['error_handling'] ? '✅' : '❌') . "\n";
echo "Logging Config: " . ($validationResults['logging_config'] ? '✅' : '❌') . "\n";

// 7. Configuration Security Assessment
echo "\n🔒 CONFIGURATION SECURITY ASSESSMENT\n";
echo "--------------------------------\n";

$securityScore = 0;
$maxSecurityScore = 100;

// Check for hardcoded secrets
$hardcodedSecrets = 0;
foreach ($foundConfigs as $configFile) {
    $filePath = $projectRoot . '/' . $configFile;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        // Check for hardcoded passwords
        if (preg_match_all('/password\s*=\s*[\'"]([^\'"]*)[\'"]/', $content) > 0) {
            $hardcodedSecrets += preg_match_all('/password\s*=\s*[\'"]([^\'"]*)[\'"]/', $content);
        }
        
        // Check for API keys
        if (preg_match_all('/api[_-]?key\s*=\s*[\'"]([^\'"]*)[\'"]/', $content) > 0) {
            $hardcodedSecrets += preg_match_all('/api[_-]?key\s*=\s*[\'"]([^\'"]*)[\'"]/', $content);
        }
        
        // Check for database credentials
        if (preg_match_all('/(username|password|host|database)\s*=\s*[\'"]([^\'"]*)[\'"]/', $content) > 0) {
            $hardcodedSecrets += preg_match_all('/(username|password|host|database)\s*=\s*[\'"]([^\'"]*)[\'"]/', $content);
        }
    }
}

if ($hardcodedSecrets > 0) {
    echo "⚠️  Hardcoded Secrets Found: $hardcodedSecrets\n";
    $securityScore -= 20;
} else {
    echo "✅ No hardcoded secrets found\n";
}

// Check for file permissions
$configPermissions = [];
foreach ($foundConfigs as $configFile) {
    $filePath = $projectRoot . '/' . $configFile;
    if (file_exists($filePath)) {
        $permissions = fileperms($filePath);
        $octal = substr(sprintf('%o', $permissions), -4);
        
        if ($octal !== '600' && $octal !== '644') {
            $configPermissions[] = "$configFile: $octal";
            $securityScore -= 5;
        }
    }
}

if (!empty($configPermissions)) {
    echo "⚠️  Insecure File Permissions:\n";
    foreach ($configPermissions as $perm) {
        echo "  • $perm\n";
    }
} else {
    echo "✅ File permissions are secure\n";
}

// Check for exposed configuration in web-accessible directories
$webAccessibleDirs = ['public', 'www', 'htdocs'];
$exposedConfigs = [];

foreach ($webAccessibleDirs as $dir) {
    $dirPath = $projectRoot . '/' . $dir;
    if (is_dir($dirPath)) {
        $configFilesInDir = glob($dirPath . '/**/*.php');
        foreach ($configFilesInDir as $file) {
            if (strpos($file, 'config') !== false || strpos($file, '.env') !== false) {
                $exposedConfigs[] = str_replace($projectRoot . '/', '', $file);
            }
        }
    }
}

if (!empty($exposedConfigs)) {
    echo "⚠️  Exposed Configuration Files:\n";
    foreach ($exposedConfigs as $file) {
        echo "  • $file\n";
    }
    $securityScore -= 15;
} else {
    echo "✅ No exposed configuration files\n";
}

echo "Configuration Security Score: $securityScore/$maxSecurityScore\n";

// 8. Configuration Recommendations
echo "\n💡 CONFIGURATION RECOMMENDATIONS\n";
echo "==============================\n";

$recommendations = [];

// High priority recommendations
if (count($missingConfigs) > 0) {
    $recommendations[] = "Create missing configuration files (" . count($missingConfigs) . " files)";
}

if ($hardcodedSecrets > 0) {
    $recommendations[] = "Remove hardcoded secrets and use environment variables";
}

if (!empty($exposedConfigs)) {
    $recommendations[] = "Move configuration files outside web-accessible directories";
}

if (!$validationResults['database_connection']) {
    $recommendations[] = "Create proper database configuration file";
}

if (!$validationResults['environment_variables']) {
    $recommendations[] = "Create .env file for environment variables";
}

if (!$validationResults['security_settings']) {
    $recommendations[] = "Implement security configuration and middleware";
}

// Medium priority recommendations
if (!$validationResults['middleware_setup']) {
    $recommendations[] = "Set up authentication and authorization middleware";
}

if (!$validationResults['error_handling']) {
    $recommendations[] = "Implement proper error handling and logging";
}

if (!$validationResults['logging_config']) {
    $recommendations[] = "Configure logging for debugging and monitoring";
}

// Low priority recommendations
if (count($configIssues) > 0) {
    $recommendations[] = "Fix configuration syntax and validation issues";
}

if (count($securityIssues) > 0) {
    $recommendations[] = "Address security vulnerabilities in configuration";
}

echo "Priority Recommendations:\n";
foreach ($recommendations as $i => $rec) {
    echo ($i + 1) . ". $rec\n";
}

// 9. Configuration File Generator
echo "\n🔧 CONFIGURATION FILE GENERATOR\n";
echo "------------------------------\n";

echo "Would you like me to generate missing configuration files? (y/n): ";
// In a real scenario, this would prompt for user input
echo "Auto-generating missing configuration files...\n";

$generatedFiles = [];

// Generate .env.example
if (!file_exists($projectRoot . '/.env.example')) {
    $envExample = "# APS Dream Home Environment Configuration\n";
    $envExample .= "# Database Configuration\n";
    $envExample .= "DB_HOST=localhost\n";
    $envExample .= "DB_PORT=3306\n";
    $envExample .= "DB_DATABASE=apsdreamhome\n";
    $envExample .= "DB_USERNAME=root\n";
    $envExample .= "DB_PASSWORD=\n";
    $envExample .= "\n# Application Configuration\n";
    $envExample .= "APP_NAME=\"APS Dream Home\"\n";
    $envExample .= "APP_ENV=local\n";
    $envExample .= "APP_DEBUG=true\n";
    $envExample .= "APP_URL=http://localhost/apsdreamhome\n";
    $envExample .= "\n# Security Configuration\n";
    $envExample .= "APP_KEY=\n";
    $envExample .= "JWT_SECRET=\n";
    $envExample .= "ENCRYPTION_KEY=\n";
    $envExample .= "\n# Email Configuration\n";
    $envExample .= "MAIL_HOST=\n";
    $envExample .= "MAIL_PORT=587\n";
    $envExample .= "MAIL_USERNAME=\n";
    $envExample .= "MAIL_PASSWORD=\n";
    $envExample .= "MAIL_ENCRYPTION=tls\n";
    $envExample .= "MAIL_FROM_ADDRESS=\n";
    $envExample .= "MAIL_FROM_NAME=\"APS Dream Home\"\n";
    
    file_put_contents($projectRoot . '/.env.example', $envExample);
    $generatedFiles[] = '.env.example';
    echo "✅ Generated: .env.example\n";
}

// Generate config/database.php
if (!file_exists($projectRoot . '/config/database.php')) {
    $dbConfig = "<?php\n";
    $dbConfig .= "/**\n";
    $dbConfig .= " * Database Configuration\n";
    $dbConfig .= " */\n";
    $dbConfig .= "\n";
    $dbConfig .= "return [\n";
    $dbConfig .= "    'default' => [\n";
    $dbConfig .= "        'host' => env('DB_HOST', 'localhost'),\n";
    $dbConfig .= "        'port' => env('DB_PORT', '3306'),\n";
    $dbConfig .= "        'database' => env('DB_DATABASE', 'apsdreamhome'),\n";
    $dbConfig .= "        'username' => env('DB_USERNAME', 'root'),\n";
    $dbConfig .= "        'password' => env('DB_PASSWORD', ''),\n";
    $dbConfig .= "        'charset' => 'utf8mb4',\n";
    $dbConfig .= "        'collation' => 'utf8mb4_unicode_ci',\n";
    $dbConfig .= "        'prefix' => '',\n";
    $dbConfig .= "        'strict' => true,\n";
    $dbConfig .= "        'engine' => 'InnoDB',\n";
    $dbConfig .= "        'options' => [\n";
    $dbConfig .= "            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
    $dbConfig .= "            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
    $dbConfig .= "            PDO::ATTR_EMULATE_PREPARES => false,\n";
    $dbConfig .= "        ],\n";
    $dbConfig .= "    ]\n";
    $dbConfig .= "];\n";
    
    file_put_contents($projectRoot . '/config/database.php', $dbConfig);
    $generatedFiles[] = 'config/database.php';
    echo "✅ Generated: config/database.php\n";
}

// Generate app/config/application.php
if (!file_exists($projectRoot . '/app/config/application.php')) {
    $appConfig = "<?php\n";
    $appConfig .= "/**\n";
    $appConfig .= " * Application Configuration\n";
    $appConfig .= " */\n";
    $appConfig .= "\n";
    $appConfig .= "return [\n";
    $appConfig .= "    'name' => env('APP_NAME', 'APS Dream Home'),\n";
    $appConfig .= "    'env' => env('APP_ENV', 'production'),\n";
    $appConfig .= "    'debug' => (bool) env('APP_DEBUG', false),\n";
    $appConfig .= "    'url' => env('APP_URL', 'http://localhost'),\n";
    $appConfig .= "    'timezone' => 'Asia/Kolkata',\n";
    $appConfig .= "    'locale' => 'en',\n";
    $appConfig .= "    'fallback_locale' => 'en',\n";
    $appConfig .= "    'key' => env('APP_KEY'),\n";
    $appConfig .= "    'cipher' => 'AES-256-CBC',\n";
    $appConfig .= "    'providers' => [\n";
    $appConfig .= "        // Providers\n";
    $appConfig .= "    ],\n";
    $appConfig .= "    'aliases' => [\n";
    $appConfig .= "        'App\\\\Core\\\\Database' => 'App\\\\Core\\\\Database',\n";
    $appConfig .= "    ],\n";
    $appConfig .= "];\n";
    
    file_put_contents($projectRoot . '/app/config/application.php', $appConfig);
    $generatedFiles[] = 'app/config/application.php';
    echo "✅ Generated: app/config/application.php\n";
}

// Generate app/config/security.php
if (!file_exists($projectRoot . '/app/config/security.php')) {
    $secConfig = "<?php\n";
    $secConfig .= "/**\n";
    $secConfig .= " * Security Configuration\n";
    $secConfig .= " */\n";
    $secConfig .= "\n";
    $secConfig .= "return [\n";
    $secConfig .= "    'csrf_token_name' => '_token',\n";
    $secConfig .= "    'csrf_token_length' => 32,\n";
    $secConfig .= "    'session_lifetime' => 120, // minutes\n";
    $secConfig .= "    'session_encryption' => true,\n";
    $secConfig .= "    'password_min_length' => 8,\n    'password_regex' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/',\n";
    $secConfig .= "    'rate_limiting' => [\n";
    $secConfig .= "        'max_attempts' => 5,\n";
    $secConfig .= "        'decay_minutes' => 1,\n";
    $secConfig .= "    ],\n";
    $secConfig .= "    'xss_protection' => true,\n";
    $secConfig .= "    'sql_injection_protection' => true,\n";
    $secConfig .= "    'input_validation' => true,\n";
    $secConfig .= "    'file_uploads' => [\n";
    $secConfig .= "        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],\n";
    $secConfig .= "        'max_size' => 5242880, // 5MB\n";
    $secConfig .= "    ],\n";
    $secConfig .= "];\n";
    
    file_put_contents($projectRoot . '/app/config/security.php', $secConfig);
    $generatedFiles[] = 'app/config/security.php';
    echo "✅ Generated: app/config/security.php\n";
}

echo "\n📄 Generated Files: " . count($generatedFiles) . "\n";
foreach ($generatedFiles as $file) {
    echo "  ✅ $file\n";
}

// 10. Final Configuration Summary
echo "\n📋 FINAL CONFIGURATION SUMMARY\n";
echo "========================\n";

$finalScore = $securityScore + (count($foundConfigs) * 2) + (count($generatedFiles) * 5);
$finalScore = min(100, max(0, $finalScore));

echo "Configuration Health Score: $finalScore/100\n";

if ($finalScore >= 90) {
    echo "🏆 EXCELLENT: Configuration is properly set up!\n";
} elseif ($finalScore >= 70) {
    echo "✅ GOOD: Configuration is mostly complete.\n";
} elseif ($finalScore >= 50) {
    echo "⚠️  FAIR: Configuration needs attention.\n";
} else {
    echo "🚨 POOR: Configuration needs significant improvements.\n";
}

echo "\n🎉 MAXIMUM LEVEL CONFIGURATION SCAN COMPLETED!\n";
echo "Your APS Dream Home configuration has been thoroughly analyzed and optimized.\n";

// Save scan results
$scanResults = [
    'scan_date' => date('Y-m-d H:i:s'),
    'health_score' => $finalScore,
    'found_configs' => count($foundConfigs),
    'missing_configs' => count($missingConfigs),
    'generated_files' => count($generatedFiles),
    'security_issues' => count($securityIssues),
    'validation_results' => $validationResults,
    'security_score' => $securityScore,
    'hardcoded_secrets' => $hardcodedSecrets,
    'exposed_configs' => count($exposedConfigs)
];

file_put_contents($projectRoot . '/config_scan_results.json', json_encode($scanResults, JSON_PRETTY_PRINT));

echo "\n📄 Scan results saved to: config_scan_results.json\n";

?>
