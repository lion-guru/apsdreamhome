<?php
// scripts/fix-file-permissions.php
// Fix file permissions for production security

$basePath = __DIR__ . '/../';
$errors = [];
$successes = [];

echo "🔧 FIXING FILE PERMISSIONS FOR PRODUCTION SECURITY\n";
echo "================================================\n\n";

try {
    // Get all PHP files
    $phpFiles = glob($basePath . '**/*.php', GLOB_BRACE);
    $totalFiles = count($phpFiles);

    echo "📊 Processing {$totalFiles} PHP files...\n";

    $correctPerms = 0;
    $incorrectPerms = 0;

    foreach ($phpFiles as $file) {
        $currentPerms = fileperms($file) & 0777;

        // Check if already has correct permissions
        if ($currentPerms === 0644) {
            $correctPerms++;
            continue;
        }

        // Try to fix permissions
        if (chmod($file, 0644)) {
            $correctPerms++;
            $successes[] = "✅ Fixed: " . str_replace($basePath, '', $file);
        } else {
            $incorrectPerms++;
            $errors[] = "❌ Failed to fix: " . str_replace($basePath, '', $file);
        }
    }

    // Fix directory permissions
    echo "\n📁 Processing directories...\n";
    $directories = glob($basePath . '**/', GLOB_ONLYDIR | GLOB_BRACE);
    $totalDirs = count($directories);

    foreach ($directories as $dir) {
        // Skip if it's the base directory or logs directory
        if ($dir === $basePath || strpos($dir, 'storage/logs') !== false) {
            continue;
        }

        $currentPerms = fileperms($dir) & 0777;

        // Set directory permissions to 755
        if ($currentPerms !== 0755) {
            if (chmod($dir, 0755)) {
                $successes[] = "✅ Fixed directory: " . str_replace($basePath, '', $dir);
            } else {
                $errors[] = "❌ Failed to fix directory: " . str_replace($basePath, '', $dir);
            }
        }
    }

    // Create secure directories
    $secureDirs = [
        $basePath . 'storage/uploads',
        $basePath . 'storage/logs',
        $basePath . 'storage/backups'
    ];

    foreach ($secureDirs as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                $successes[] = "✅ Created secure directory: " . str_replace($basePath, '', $dir);
            } else {
                $errors[] = "❌ Failed to create directory: " . str_replace($basePath, '', $dir);
            }
        }
    }

    // Results
    echo "\n📊 PERMISSION FIX RESULTS\n";
    echo "========================\n";

    $percentage = ($totalFiles > 0) ? round(($correctPerms / $totalFiles) * 100, 1) : 100;

    echo "PHP Files: {$correctPerms}/{$totalFiles} ({$percentage}%)\n";
    echo "Directories: {$totalDirs} processed\n";
    echo "Secure Directories: Created/verified\n";

    if ($percentage >= 90) {
        echo "🎯 STATUS: FILE PERMISSIONS SECURED ✅\n";
    } else {
        echo "⚠️  STATUS: MOST PERMISSIONS FIXED, SOME MANUAL ATTENTION NEEDED ⚠️\n";
    }

    echo "\n✅ SUCCESSES:\n";
    foreach ($successes as $success) {
        echo "  {$success}\n";
    }

    if (!empty($errors)) {
        echo "\n❌ ERRORS:\n";
        foreach ($errors as $error) {
            echo "  {$error}\n";
        }
    }

    echo "\n📋 SUMMARY:\n";
    echo "  • Total PHP files processed: {$totalFiles}\n";
    echo "  • Files with correct permissions: {$correctPerms}\n";
    echo "  • Files with incorrect permissions: {$incorrectPerms}\n";
    echo "  • Directories processed: {$totalDirs}\n";
    echo "  • Security score: {$percentage}%\n";

    if ($percentage >= 95) {
        echo "\n🎉 File permissions are now production-ready!\n";
    } else {
        echo "\n⚠️  Some file permissions may need manual fixing.\n";
        echo "   Run this command as administrator if you encounter permission errors.\n";
    }

} catch (Exception $e) {
    echo "❌ Error fixing permissions: " . $e->getMessage() . "\n";
    echo "Try running this script with appropriate permissions.\n";
}

?>
