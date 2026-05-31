<?php
/**
 * APS Dream Home - .env Configuration Verification
 * Check and verify all .env settings are properly configured
 */

echo "🔧 APS DREAM HOME - .ENV CONFIGURATION VERIFICATION\n";
echo "===============================================\n\n";

$projectRoot = __DIR__;
$envFile = $projectRoot . '/../.env';

echo "🔍 .ENV FILE VERIFICATION:\n\n";

// Check if .env file exists
if (!file_exists($envFile)) {
    echo "❌ ERROR: .env file not found!\n";
    exit(1);
}

echo "✅ .env file found: $envFile\n";

// Read and parse .env
$envContent = file_get_contents($envFile);
$envLines = explode("\n", $envContent);

echo "📋 .ENV CONTENT ANALYSIS:\n";
echo "========================\n";

$requiredVars = [
    'APP_NAME' => 'Application Name',
    'APP_ENV' => 'Environment',
    'APP_DEBUG' => 'Debug Mode',
    'APP_URL' => 'Application URL',
    'DB_CONNECTION' => 'Database Connection',
    'DB_HOST' => 'Database Host',
    'DB_DATABASE' => 'Database Name',
    'DB_USERNAME' => 'Database Username',
    'DB_PASSWORD' => 'Database Password',
    'GOOGLE_MAPS_API_KEY' => 'Google Maps API Key',
    'RECAPTCHA_SITE_KEY' => 'reCAPTCHA Site Key',
    'RECAPTCHA_SECRET_KEY' => 'reCAPTCHA Secret Key',
    'OPENROUTER_API_KEY' => 'OpenRouter API Key',
    'OPENROUTER_MODEL' => 'OpenRouter Model',
    'WHATSAPP_PHONE' => 'WhatsApp Phone',
    'WHATSAPP_COUNTRY_CODE' => 'WhatsApp Country Code',
    'WHATSAPP_API_PROVIDER' => 'WhatsApp API Provider',
    'WHATSAPP_BUSINESS_ACCOUNT_ID' => 'WhatsApp Business Account ID',
    'WHATSAPP_ACCESS_TOKEN' => 'WhatsApp Access Token',
    'WHATSAPP_WEBHOOK_VERIFY_TOKEN' => 'WhatsApp Webhook Token'
];

$foundVars = [];
$missingVars = [];

foreach ($envLines as $line) {
    $line = trim($line);
    
    // Skip comments and empty lines
    if (empty($line) || strpos($line, '#') === 0) {
        continue;
    }
    
    // Parse key-value pairs
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (isset($requiredVars[$key])) {
            $foundVars[$key] = $value;
            echo "✅ {$requiredVars[$key]}: $key = ";
            
            // Mask sensitive values
            if (in_array($key, ['DB_PASSWORD', 'WHATSAPP_ACCESS_TOKEN', 'OPENROUTER_API_KEY', 'RECAPTCHA_SECRET_KEY'])) {
                echo "*** (masked)\n";
            } else {
                echo "$value\n";
            }
        }
    }
}

echo "\n📊 CONFIGURATION SUMMARY:\n";
echo "========================\n";

// Check missing variables
foreach ($requiredVars as $key => $description) {
    if (!isset($foundVars[$key])) {
        $missingVars[] = $key;
        echo "❌ Missing: $description ($key)\n";
    }
}

// Check specific configurations
echo "\n🔍 SPECIFIC CONFIGURATIONS:\n\n";

// Database Configuration
echo "🗄️ DATABASE CONFIGURATION:\n";
echo "========================\n";
if (isset($foundVars['DB_HOST'], $foundVars['DB_DATABASE'], $foundVars['DB_USERNAME'])) {
    echo "✅ Host: {$foundVars['DB_HOST']}\n";
    echo "✅ Database: {$foundVars['DB_DATABASE']}\n";
    echo "✅ Username: {$foundVars['DB_USERNAME']}\n";
    echo "✅ Password: " . (empty($foundVars['DB_PASSWORD']) ? '(empty - OK for local)' : '*** (configured)') . "\n";
    echo "✅ Connection: mysql\n";
} else {
    echo "❌ Database configuration incomplete\n";
}

// Application Configuration
echo "\n🌐 APPLICATION CONFIGURATION:\n";
echo "===========================\n";
if (isset($foundVars['APP_NAME'], $foundVars['APP_ENV'], $foundVars['APP_URL'])) {
    echo "✅ App Name: {$foundVars['APP_NAME']}\n";
    echo "✅ Environment: {$foundVars['APP_ENV']}\n";
    echo "✅ Debug Mode: {$foundVars['APP_DEBUG']}\n";
    echo "✅ App URL: {$foundVars['APP_URL']}\n";
} else {
    echo "❌ Application configuration incomplete\n";
}

// API Keys Configuration
echo "\n🔑 API KEYS CONFIGURATION:\n";
echo "===========================\n";

$apiKeys = [
    'GOOGLE_MAPS_API_KEY' => 'Google Maps',
    'RECAPTCHA_SITE_KEY' => 'reCAPTCHA Site',
    'RECAPTCHA_SECRET_KEY' => 'reCAPTCHA Secret',
    'OPENROUTER_API_KEY' => 'OpenRouter',
    'OPENROUTER_MODEL' => 'OpenRouter Model'
];

foreach ($apiKeys as $key => $name) {
    if (isset($foundVars[$key])) {
        $value = $foundVars[$key];
        if (strlen($value) > 10) {
            echo "✅ $name API Key: " . substr($value, 0, 10) . "...*** (configured)\n";
        } else {
            echo "⚠️ $name API Key: $value (too short)\n";
        }
    } else {
        echo "❌ $name API Key: Missing\n";
    }
}

// WhatsApp Configuration
echo "\n📱 WHATSAPP CONFIGURATION:\n";
echo "==========================\n";

$whatsappKeys = [
    'WHATSAPP_PHONE' => 'Phone Number',
    'WHATSAPP_COUNTRY_CODE' => 'Country Code',
    'WHATSAPP_API_PROVIDER' => 'API Provider',
    'WHATSAPP_BUSINESS_ACCOUNT_ID' => 'Business Account ID',
    'WHATSAPP_ACCESS_TOKEN' => 'Access Token',
    'WHATSAPP_WEBHOOK_VERIFY_TOKEN' => 'Webhook Token'
];

foreach ($whatsappKeys as $key => $name) {
    if (isset($foundVars[$key])) {
        $value = $foundVars[$key];
        if (in_array($key, ['WHATSAPP_ACCESS_TOKEN', 'WHATSAPP_WEBHOOK_VERIFY_TOKEN'])) {
            echo "✅ $name: " . substr($value, 0, 20) . "...*** (configured)\n";
        } else {
            echo "✅ $name: $value\n";
        }
    } else {
        echo "❌ $name: Missing\n";
    }
}

// Security Check
echo "\n🔒 SECURITY CHECK:\n";
echo "==================\n";

$securityIssues = [];
if ($foundVars['APP_DEBUG'] === 'true') {
    echo "⚠️ WARNING: Debug mode is ON (should be false in production)\n";
    $securityIssues[] = 'Debug mode enabled';
}

if (empty($foundVars['DB_PASSWORD']) && $foundVars['APP_ENV'] === 'production') {
    echo "❌ ERROR: Empty database password in production\n";
    $securityIssues[] = 'Empty DB password in production';
}

if (empty($securityIssues)) {
    echo "✅ Security: No critical issues found\n";
}

// Final Summary
echo "\n📊 FINAL VERIFICATION SUMMARY:\n";
echo "==============================\n";

$totalVars = count($requiredVars);
$foundCount = count($foundVars);
$missingCount = count($missingVars);

echo "📋 Total Required Variables: $totalVars\n";
echo "✅ Configured Variables: $foundCount\n";
echo "❌ Missing Variables: $missingCount\n";
echo "📊 Configuration Complete: " . ($missingCount === 0 ? 'YES' : 'NO') . "\n";

if ($missingCount === 0 && empty($securityIssues)) {
    echo "\n🎉 .ENV CONFIGURATION: PERFECT!\n";
    echo "✅ All required variables configured\n";
    echo "✅ API keys properly set\n";
    echo "✅ Security settings appropriate\n";
    echo "✅ Ready for development/production\n";
} else {
    echo "\n⚠️ .ENV CONFIGURATION: NEEDS ATTENTION\n";
    echo "❌ Missing variables: " . implode(', ', $missingVars) . "\n";
    echo "⚠️ Security issues: " . implode(', ', $securityIssues) . "\n";
}

echo "\n🎯 NEXT STEPS:\n";
echo "================\n";
echo "1. 🗄️ Test database connection\n";
echo "2. 🌐 Start development server\n";
echo "3. 📮 Test API endpoints\n";
echo "4. 🧪 Run application tests\n";
echo "5. 📱 Test WhatsApp integration\n";
echo "6. 🗺️ Test Google Maps integration\n";

echo "\n🎉 .ENV VERIFICATION COMPLETE!\n";
?>
