<?php
// scripts/final-security-validation.php
// Final security validation - quick and reliable

$basePath = __DIR__ . '/../';

// Include environment helper
require_once $basePath . 'app/helpers/env.php';

$errors = [];
$successes = [];
$warnings = [];

echo "🔒 APS Dream Home - FINAL SECURITY VALIDATION\n";
echo "============================================\n\n";

$securityFiles = [
    'app/helpers/security.php',
    'app/helpers/env.php',
    'app/Services/FileUploadService.php',
    'admin/includes/session_manager.php',
    'scripts/security-monitor.php',
    'scripts/security-audit.php',
    'scripts/security-test-suite.php'
];

foreach ($securityFiles as $file) {
    if (file_exists($basePath . $file)) {
        $successes[] = "✅ $file exists";
    } else {
        $errors[] = "❌ $file missing";
    }
}

// 2. Check .env configuration
echo "\n⚙️  Checking Environment Configuration...\n";

$envFile = $basePath . '.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (strpos($envContent, 'APP_DEBUG=false') !== false) {
        $successes[] = "✅ Debug mode disabled in production";
    } else {
        $errors[] = "❌ Debug mode not disabled";
    }

    if (strpos($envContent, 'APP_HTTPS=true') !== false) {
        $successes[] = "✅ HTTPS configuration ready";
    } else {
        $warnings[] = "⚠️  HTTPS configuration needed";
    }
} else {
    $errors[] = "❌ .env file missing";
}

// 3. Check database connection
echo "\n🗄️  Checking Database Security...\n";

try {
    $dbConfig = require $basePath . 'config/database.php';
    if (isset($dbConfig['options'][PDO::ATTR_EMULATE_PREPARES]) &&
        $dbConfig['options'][PDO::ATTR_EMULATE_PREPARES] === false) {
        $successes[] = "✅ PDO security enabled";
    } else {
        $errors[] = "❌ PDO security not properly configured";
    }
} catch (Exception $e) {
    $errors[] = "❌ Database configuration error: " . $e->getMessage();
}

// 4. Check file permissions
echo "\n📁 Checking File Permissions...\n";

$phpFiles = glob($basePath . '**/*.php', GLOB_BRACE);
$correctPerms = 0;

foreach ($phpFiles as $file) {
    $perms = fileperms($file) & 0777;
    if ($perms === 0644) {
        $correctPerms++;
    }
}

$permPercentage = round(($correctPerms / count($phpFiles)) * 100, 1);
if ($permPercentage >= 90) {
    $successes[] = "✅ File permissions: {$permPercentage}% correct (644)";
} else {
    $errors[] = "❌ File permissions: Only {$permPercentage}% correct (need 644)";
}

// 5. Check for remaining SQL injection vulnerabilities
echo "\n🔍 Checking for SQL Injection Vulnerabilities...\n";

$rawQueries = 0;
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (preg_match('/\$conn->query\(["\']/', $content)) {
        $rawQueries++;
    }
}

if ($rawQueries === 0) {
    $successes[] = "✅ No raw SQL queries found";
} else {
    $errors[] = "❌ {$rawQueries} potential SQL injection vulnerabilities found";
}

// 6. Check .htaccess security
echo "\n🌐 Checking Web Server Security...\n";

$htaccess = $basePath . '.htaccess';
if (file_exists($htaccess)) {
    $content = file_get_contents($htaccess);
    $securityHeaders = 0;
    $headers = ['X-Content-Type-Options', 'X-Frame-Options', 'Content-Security-Policy'];

    foreach ($headers as $header) {
        if (strpos($content, $header) !== false) {
            $securityHeaders++;
        }
    }

    if ($securityHeaders >= 2) {
        $successes[] = "✅ Security headers configured ({$securityHeaders}/3)";
    } else {
        $errors[] = "❌ Security headers insufficient ({$securityHeaders}/3)";
    }
} else {
    $errors[] = "❌ .htaccess file missing";
}

// 7. Check security scripts functionality
echo "\n🛡️  Checking Security Scripts...\n";

$testScript = $basePath . 'scripts/security-test-suite.php';
if (file_exists($testScript)) {
    $successes[] = "✅ Security test suite available";
} else {
    $errors[] = "❌ Security test suite missing";
}

// Generate final report
echo "\n📊 FINAL SECURITY VALIDATION REPORT\n";
echo "==================================\n\n";

$score = count($successes);
$total = count($successes) + count($errors) + count($warnings ?? []);

if ($total > 0) {
    $percentage = round(($score / $total) * 100, 1);
} else {
    $percentage = 100;
}

echo "🎯 SECURITY SCORE: {$percentage}%\n";

if ($percentage >= 95) {
    echo "📈 STATUS: PRODUCTION READY ✅\n";
} elseif ($percentage >= 90) {
    echo "📈 STATUS: NEARLY READY ⚠️\n";
} else {
    echo "📈 STATUS: NEEDS IMPROVEMENT ❌\n";
}

echo "\n✅ SUCCESSES ({$score}):\n";
foreach ($successes as $success) {
    echo "  {$success}\n";
}

if (!empty($warnings)) {
    echo "\n⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "  {$warning}\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "  {$error}\n";
    }
}

// Create final validation summary
$validationSummary = [
    'validation_date' => date('Y-m-d H:i:s'),
    'security_score' => $percentage,
    'status' => $percentage >= 95 ? 'PRODUCTION READY' : 'NEEDS ATTENTION',
    'successes' => $successes,
    'warnings' => $warnings ?? [],
    'errors' => $errors,
    'total_checks' => $total
];

file_put_contents($basePath . 'storage/logs/final-security-validation.json', json_encode($validationSummary, JSON_PRETTY_PRINT));

echo "\n📋 VALIDATION COMPLETE\n";
echo "📄 Report saved to: storage/logs/final-security-validation.json\n";

if ($percentage >= 95) {
    echo "\n🎉 Your APS Dream Home application is READY FOR PRODUCTION DEPLOYMENT!\n";
} else {
    echo "\n⚠️  Please address the errors above before production deployment.\n";
}

echo "\n🚀 NEXT STEPS:\n";
echo "  1. Enable HTTPS on your production server\n";
echo "  2. Run: php scripts/deploy-security.php\n";
echo "  3. Start monitoring: php scripts/security-monitor.php\n";
echo "  4. Final check: php scripts/security-test-suite.php\n";

?>
