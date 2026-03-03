<?php
/**
 * APS Dream Home - Deployment Verification Script
 * Comprehensive system verification and testing
 */

echo "🧪 APS DREAM HOME - DEPLOYMENT VERIFICATION\n";
echo "==========================================\n\n";

$tests = [
    "PHP Environment" => function() {
        return version_compare(PHP_VERSION, "8.0", ">=");
    },
    "Required Extensions" => function() {
        $required = ["mysqli", "gd", "curl", "json", "mbstring"];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                return false;
            }
        }
        return true;
    },
    "Database Connection" => function() {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=apsdreamhome", "root", "");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    },
    "File Permissions" => function() {
        $dirs = ["uploads", "logs", "cache"];
        foreach ($dirs as $dir) {
            if (!is_writable(__DIR__ . "/../$dir")) {
                return false;
            }
        }
        return true;
    },
    "Configuration Files" => function() {
        $files = ["config/paths.php", "config/database.php"];
        foreach ($files as $file) {
            if (!file_exists(__DIR__ . "/../$file")) {
                return false;
            }
        }
        return true;
    }
];

$passed = 0;
$total = count($tests);

foreach ($tests as $testName => $testFunction) {
    $result = $testFunction();
    $status = $result ? "✅ PASS" : "❌ FAIL";
    echo "$status $testName\n";
    if ($result) $passed++;
}

$percentage = round(($passed / $total) * 100, 1);
echo "\n📊 VERIFICATION RESULTS: $passed/$total tests passed ($percentage%)\n";

if ($percentage >= 95) {
    echo "🎉 DEPLOYMENT VERIFICATION: SUCCESS!\n";
} else {
    echo "⚠️  DEPLOYMENT VERIFICATION: NEEDS ATTENTION\n";
}
?>