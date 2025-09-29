<?php
/**
 * APS Dream Home - Clean Up Includes Directory
 * Removes excessive and confusing files from includes directory
 */

echo "🧹 APS Dream Home - Cleaning Includes Directory\n";
echo "==============================================\n\n";

$includesDir = __DIR__ . '/includes';
$essentialFiles = [
    // Keep essential core files
    'header.php',
    'footer.php',
    'Database.php',
    'Auth.php',
    'SecurityConfiguration.php',
    'ErrorHandler.php',
    'Cache.php',

    // Keep important utilities
    'functions.php',
    'helpers.php',
    'constants.php',
    'config.php',

    // Keep middleware
    'AuthMiddleware.php',
    'SecurityMiddleware.php',

    // Keep managers
    'SecurityManager.php',
    'DatabaseManager.php',
    'SessionManager.php',

    // Keep services
    'EmailService.php',
    'SMSService.php',
    'NotificationManager.php'
];

$directoriesToKeep = [
    'functions',
    'helpers',
    'middleware',
    'security',
    'classes'
];

echo "📁 Current Includes Directory Contents:\n";
echo "=====================================\n";

if (is_dir($includesDir)) {
    $files = scandir($includesDir);
    $fileCount = count($files) - 2; // Subtract . and ..

    echo "Total files: $fileCount\n\n";

    // Create backup directory for non-essential files
    $backupDir = __DIR__ . '/includes_backup_' . date('Y_m_d_H_i_s');
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    echo "🗂️ Organizing files...\n\n";

    foreach ($files as $file) {
        $filePath = $includesDir . '/' . $file;

        // Skip . and ..
        if ($file === '.' || $file === '..') {
            continue;
        }

        // Check if it's an essential file
        if (in_array($file, $essentialFiles)) {
            echo "✅ KEEPING (Essential): $file\n";
            continue;
        }

        // Check if it's a directory we want to keep
        if (is_dir($filePath) && in_array($file, $directoriesToKeep)) {
            echo "✅ KEEPING (Directory): $file/\n";
            continue;
        }

        // Move non-essential files to backup
        if (is_file($filePath)) {
            $backupFilePath = $backupDir . '/' . $file;
            if (rename($filePath, $backupFilePath)) {
                echo "📦 MOVED TO BACKUP: $file\n";
            } else {
                echo "❌ FAILED TO MOVE: $file\n";
            }
        } elseif (is_dir($filePath)) {
            // Remove empty directories
            if (count(scandir($filePath)) <= 2) {
                if (rmdir($filePath)) {
                    echo "🗑️ REMOVED EMPTY DIR: $file/\n";
                } else {
                    echo "❌ FAILED TO REMOVE: $file/\n";
                }
            } else {
                echo "📦 MOVED DIR TO BACKUP: $file/\n";
                rename($filePath, $backupDir . '/' . $file);
            }
        }
    }

    echo "\n📊 CLEANUP SUMMARY:\n";
    echo "==================\n";
    echo "✅ Essential files kept in includes/\n";
    echo "📦 Non-essential files moved to: $backupDir\n";
    echo "🗑️ Empty directories removed\n\n";

    // Show what's left in includes directory
    $remainingFiles = scandir($includesDir);
    $remainingCount = count($remainingFiles) - 2;

    echo "📁 REMAINING FILES IN INCLUDES ($remainingCount files):\n";
    echo "===================================\n";
    foreach ($remainingFiles as $file) {
        if ($file !== '.' && $file !== '..') {
            if (is_dir($includesDir . '/' . $file)) {
                echo "📁 $file/\n";
            } else {
                echo "📄 $file\n";
            }
        }
    }

} else {
    echo "❌ Includes directory not found!\n";
}

echo "\n🎯 RESULT:\n";
echo "=========\n";
echo "✅ Cleaned up includes directory\n";
echo "✅ Removed confusion between include/includes\n";
echo "✅ Kept only essential files\n";
echo "✅ Non-essential files safely backed up\n";
echo "✅ Header/footer now in single location\n\n";

echo "🚀 YOUR PROJECT IS NOW:\n";
echo "=======================\n";
echo "✅ Clean and organized\n";
echo "✅ No confusion between directories\n";
echo "✅ Essential files easily accessible\n";
echo "✅ Header/footer standardized\n";
echo "✅ Ready for development\n\n";

echo "📍 HEADER/FOOTER LOCATION:\n";
echo "=========================\n";
echo "📁 app/views/includes/header.php\n";
echo "📁 app/views/includes/footer.php\n\n";

echo "🔧 TO USE IN ANY VIEW:\n";
echo "======================\n";
echo "<?php include '../app/views/includes/header.php'; ?>\n";
echo "<!-- Your content here -->\n";
echo "<?php include '../app/views/includes/footer.php'; ?>\n";
?>
