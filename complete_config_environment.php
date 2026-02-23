<?php
/**
 * APS Dream Home - Configuration and Environment Setup Completion
 * Check and complete missing configuration files and environment settings
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'config_environment_completion',
    'missing_configs' => [],
    'env_issues' => [],
    'fixes_applied' => [],
    'recommendations' => []
];

echo "⚙️ CONFIGURATION & ENVIRONMENT COMPLETION\n";
echo "=========================================\n\n";

// Function to check if .env file exists and has required variables
function checkEnvFile($envPath, &$results) {
    if (!file_exists($envPath)) {
        $results['env_issues'][] = [
            'type' => 'missing_env_file',
            'file' => '.env',
            'description' => '.env file is missing'
        ];
        return false;
    }

    $envContent = file_get_contents($envPath);
    if ($envContent === false) {
        $results['env_issues'][] = [
            'type' => 'unreadable_env_file',
            'file' => '.env',
            'description' => '.env file exists but cannot be read'
        ];
        return false;
    }

    // Required environment variables
    $requiredVars = [
        'APP_NAME',
        'APP_ENV',
        'APP_KEY',
        'APP_DEBUG',
        'APP_URL',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
        'CACHE_DRIVER',
        'SESSION_DRIVER',
        'MAIL_MAILER',
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_PASSWORD'
    ];

    $missingVars = [];
    foreach ($requiredVars as $var) {
        if (!preg_match('/^' . preg_quote($var) . '\s*=/m', $envContent)) {
            $missingVars[] = $var;
        }
    }

    if (!empty($missingVars)) {
        $results['env_issues'][] = [
            'type' => 'missing_env_variables',
            'file' => '.env',
            'description' => 'Missing required environment variables: ' . implode(', ', $missingVars),
            'missing_variables' => $missingVars
        ];
    }

    // Check for hardcoded secrets (passwords, keys, etc.)
    $sensitivePatterns = [
        '/PASSWORD\s*=\s*[\'"]?[^\'"\n]+[\'"]?/i',
        '/SECRET\s*=\s*[\'"]?[^\'"\n]+[\'"]?/i',
        '/KEY\s*=\s*[\'"]?[^\'"\n]+[\'"]?/i'
    ];

    foreach ($sensitivePatterns as $pattern) {
        if (preg_match_all($pattern, $envContent, $matches)) {
            $results['env_issues'][] = [
                'type' => 'potential_hardcoded_secrets',
                'file' => '.env',
                'description' => 'Potential hardcoded secrets found in .env file',
                'matches' => $matches[0]
            ];
        }
    }

    return empty($results['env_issues']);
}

// Function to check config files
function checkConfigFiles($configDir, &$results) {
    $requiredConfigs = [
        'app.php',
        'database.php',
        'mail.php',
        'cache.php',
        'session.php',
        'filesystems.php'
    ];

    foreach ($requiredConfigs as $configFile) {
        $configPath = $configDir . '/' . $configFile;
        if (!file_exists($configPath)) {
            $results['missing_configs'][] = [
                'file' => 'config/' . $configFile,
                'description' => 'Required configuration file is missing'
            ];
        } else {
            // Basic syntax check
            $content = file_get_contents($configPath);
            if ($content !== false) {
                // Check for basic PHP syntax (opening and closing tags)
                if (!preg_match('/^<\?php/', $content) || !preg_match('/\?>$/', $content)) {
                    $results['missing_configs'][] = [
                        'file' => 'config/' . $configFile,
                        'description' => 'Configuration file has invalid PHP syntax'
                    ];
                }
            }
        }
    }
}

// Function to create missing .env file
function createEnvFile($envPath, &$results) {
    $envTemplate = "# APS Dream Home Environment Configuration
APP_NAME=\"APS Dream Home\"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apsdreamhome
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=\"hello@example.com\"
MAIL_FROM_NAME=\"\${APP_NAME}\"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\"
VITE_PUSHER_HOST=\"\${PUSHER_HOST}\"
VITE_PUSHER_PORT=\"\${PUSHER_PORT}\"
VITE_PUSHER_SCHEME=\"\${PUSHER_SCHEME}\"
VITE_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\"
";

    if (file_put_contents($envPath, $envTemplate)) {
        $results['fixes_applied'][] = [
            'type' => 'created_env_file',
            'file' => '.env',
            'description' => 'Created basic .env file with default settings'
        ];
        return true;
    }
    return false;
}

// Function to generate APP_KEY
function generateAppKey(&$results) {
    $key = 'base64:' . base64_encode(random_bytes(32));
    $envPath = dirname(__FILE__) . '/.env';

    if (file_exists($envPath)) {
        $content = file_get_contents($envPath);
        $content = preg_replace('/APP_KEY\s*=\s*/', "APP_KEY={$key}", $content);

        if (file_put_contents($envPath, $content)) {
            $results['fixes_applied'][] = [
                'type' => 'generated_app_key',
                'description' => 'Generated and set APP_KEY in .env file'
            ];
            return true;
        }
    }
    return false;
}

// Check .env file
echo "🔍 Checking Environment Configuration\n";
echo "=====================================\n";

$envPath = $projectRoot . '/.env';
checkEnvFile($envPath, $results);

if (!empty($results['env_issues'])) {
    echo "❌ Environment issues found:\n";
    foreach ($results['env_issues'] as $issue) {
        echo "   • {$issue['description']}\n";
    }
    echo "\n";
}

// Check config files
echo "🔍 Checking Configuration Files\n";
echo "===============================\n";

$configDir = $projectRoot . '/config';
checkConfigFiles($configDir, $results);

if (!empty($results['missing_configs'])) {
    echo "❌ Missing or invalid config files:\n";
    foreach ($results['missing_configs'] as $config) {
        echo "   • {$config['file']}: {$config['description']}\n";
    }
    echo "\n";
}

// Apply fixes
echo "🔧 Applying Configuration Fixes\n";
echo "===============================\n";

$fixesApplied = 0;

// Create .env if missing
if (!file_exists($envPath)) {
    echo "📝 Creating .env file...\n";
    if (createEnvFile($envPath, $results)) {
        echo "✅ .env file created\n";
        $fixesApplied++;
    } else {
        echo "❌ Failed to create .env file\n";
    }
}

// Generate APP_KEY if missing
$envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
if ($envContent && !preg_match('/APP_KEY\s*=\s*[^\'"\s]/', $envContent)) {
    echo "🔑 Generating APP_KEY...\n";
    if (generateAppKey($results)) {
        echo "✅ APP_KEY generated and set\n";
        $fixesApplied++;
    } else {
        echo "❌ Failed to generate APP_KEY\n";
    }
}

// Generate summary
echo "\n📊 Configuration Completion Summary\n";
echo "===================================\n";
echo "🔍 Environment issues found: " . count($results['env_issues']) . "\n";
echo "🔍 Config issues found: " . count($results['missing_configs']) . "\n";
echo "🔧 Fixes applied: {$fixesApplied}\n";

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Review and customize the .env file for your environment\n";
echo "• Set secure database credentials in production\n";
echo "• Configure mail settings for email functionality\n";
echo "• Set up proper caching and session drivers for production\n";
echo "• Generate a new APP_KEY for production deployment\n";
echo "• Use environment-specific .env files (.env.local, .env.production)\n";
echo "• 🔄 Next: Final project health assessment\n";

$results['summary'] = [
    'env_issues_found' => count($results['env_issues']),
    'config_issues_found' => count($results['missing_configs']),
    'fixes_applied' => $fixesApplied
];

// Save results
$resultsFile = $projectRoot . '/config_environment_completion.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Configuration and environment setup completion finished!\n";

?>
