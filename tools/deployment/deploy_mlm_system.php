<?php
/**
 * MLM System Deployment Script
 * Automated deployment with rollback capability
 */

echo "🚀 APS Dream Homes MLM System Deployment\n";
echo "========================================\n\n";

// Configuration
$backup_dir = __DIR__ . '/backups/' . date('Y-m-d_H-i-s');
$log_file = __DIR__ . '/deployment.log';

// Create backup directory
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log = "[$timestamp] $message\n";
    file_put_contents($log_file, $log, FILE_APPEND);
    echo $message . "\n";
}

function backup_database() {
    global $backup_dir;
    
    log_message("📦 Creating database backup...");
    
    $backup_file = $backup_dir . '/database_backup.sql';
    $command = "mysqldump -u root apsdreamhome > $backup_file 2>&1";
    
    exec($command, $output, $return_var);
    
    if ($return_var === 0) {
        log_message("✅ Database backup created: $backup_file");
        return true;
    } else {
        log_message("❌ Database backup failed: " . implode("\n", $output));
        return false;
    }
}

function copy_recursive(string $source, string $dest): bool
{
    if (is_dir($source)) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $items = scandir($source);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $srcPath = rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $item;
            $destPath = rtrim($dest, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $item;
            if (!copy_recursive($srcPath, $destPath)) {
                return false;
            }
        }
        return true;
    }

    $destDir = dirname($dest);
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    return copy($source, $dest);
}

function backup_files() {
    global $backup_dir;
    
    log_message("📁 Creating file backup...");
    
    $files_to_backup = [
        'database/mlm_unified_schema.sql',
        'app/controllers',
        'app/services',
        'app/views',
        'includes'
    ];
    
    foreach ($files_to_backup as $file) {
        $source = __DIR__ . '/' . $file;
        $dest = $backup_dir . '/' . $file;

        if (!file_exists($source)) {
            log_message("⚠️  Skipping missing path: $file");
            continue;
        }

        if (!copy_recursive($source, $dest)) {
            log_message("❌ Failed to backup: $file");
            return false;
        }
    }
    
    log_message("✅ File backup completed");
    return true;
}

function validate_system() {
    log_message("🔍 Validating system integrity...");
    
    $required_files = [
        'database/mlm_unified_schema.sql',
        'app/services/CommissionCalculator.php',
        'app/services/ReferralService.php',
        'app/controllers/AuthController.php',
        'app/controllers/NetworkController.php',
        'app/views/auth/register_unified.php',
        'app/views/user/network_dashboard.php'
    ];
    
    foreach ($required_files as $file) {
        if (!file_exists(__DIR__ . '/' . $file)) {
            log_message("❌ Missing required file: $file");
            return false;
        }
    }
    
    log_message("✅ All required files present");
    return true;
}

function apply_database_schema() {
    log_message("🏗️  Applying database schema...");
    
    $schema_file = __DIR__ . '/database/mlm_unified_schema.sql';
    
    if (!file_exists($schema_file)) {
        log_message("❌ Schema file not found: $schema_file");
        return false;
    }
    
    $command = "mysql -u root apsdreamhome < $schema_file 2>&1";
    exec($command, $output, $return_var);
    
    if ($return_var === 0) {
        log_message("✅ Database schema applied successfully");
        return true;
    } else {
        log_message("❌ Database schema application failed: " . implode("\n", $output));
        return false;
    }
}

function migrate_data() {
    log_message("📊 Starting data migration...");
    
    $migration_file = __DIR__ . '/database/migrate_legacy_mlm.php';
    
    if (file_exists($migration_file)) {
        $command = "php $migration_file 2>&1";
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            log_message("✅ Data migration completed");
            return true;
        } else {
            log_message("❌ Data migration failed: " . implode("\n", $output));
            return false;
        }
    } else {
        log_message("⚠️  Migration file not found, skipping data migration");
        return true;
    }
}

function run_tests() {
    log_message("🧪 Running system tests...");
    
    $test_file = __DIR__ . '/tests/test_mlm_system.php';
    
    if (file_exists($test_file)) {
        $command = "php $test_file 2>&1";
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            log_message("✅ All tests passed");
            return true;
        } else {
            log_message("❌ Tests failed: " . implode("\n", $output));
            return false;
        }
    } else {
        log_message("⚠️  Test file not found, skipping tests");
        return true;
    }
}

function create_routes() {
    log_message("🛣️  Creating MVC routes...");
    
    $routes_file = __DIR__ . '/app/core/routes.php';
    $routes_content = "<?php
// MLM System Routes

// Authentication
Route::get('/register', 'AuthController@register');
Route::post('/register', 'AuthController@processRegistration');
Route::get('/login', 'AuthController@login');
Route::post('/login', 'AuthController@processLogin');
Route::get('/logout', 'AuthController@logout');

// Network Dashboard
Route::get('/dashboard', 'NetworkController@dashboard');
Route::get('/api/network/tree', 'NetworkController@getNetworkTree');
Route::get('/api/network/analytics', 'NetworkController@getAnalytics');
Route::get('/api/network/referral-link', 'NetworkController@getReferralLink');
Route::get('/api/network/validate-code', 'NetworkController@validateCode');

// Commission Management
Route::get('/commissions', 'CommissionController@index');
Route::post('/commissions/calculate', 'CommissionController@calculate');
Route::post('/commissions/approve', 'CommissionController@approve');
Route::get('/commissions/payout', 'CommissionController@processPayout');

// Admin Routes
Route::get('/admin/mlm', 'AdminController@mlmDashboard');
Route::get('/admin/commissions', 'AdminController@commissions');
Route::get('/admin/network', 'AdminController@network');
?>";
    
    file_put_contents($routes_file, $routes_content);
    log_message("✅ Routes created");
    return true;
}

function update_config() {
    log_message("⚙️  Updating configuration...");
    
    $config_file = __DIR__ . '/includes/config.php';
    
    // Add MLM configuration
    $config_addition = "
// MLM Configuration
if (!defined('MLM_ENABLED')) {
    define('MLM_ENABLED', true);
    define('MLM_MAX_LEVELS', 5);
    define('MLM_COMMISSION_STRUCTURE', [
        1 => 5.0,
        2 => 3.0,
        3 => 2.0,
        4 => 1.5,
        5 => 1.0
    ]);
}
";
    
    file_put_contents($config_file, $config_addition, FILE_APPEND);
    log_message("✅ Configuration updated");
    return true;
}

function deploy() {
    log_message("🚀 Starting deployment...");
    
    // Step 1: Backup
    if (!backup_database()) return false;
    if (!backup_files()) return false;
    
    // Step 2: Validate
    if (!validate_system()) return false;
    
    // Step 3: Apply changes
    if (!apply_database_schema()) return false;
    if (!migrate_data()) return false;
    if (!create_routes()) return false;
    if (!update_config()) return false;
    
    // Step 4: Test
    if (!run_tests()) return false;
    
    log_message("🎉 Deployment completed successfully!");
    return true;
}

function rollback() {
    global $backup_dir;
    
    log_message("🔄 Starting rollback...");
    
    // Restore database
    $backup_file = $backup_dir . '/database_backup.sql';
    if (file_exists($backup_file)) {
        $command = "mysql -u root apsdreamhome < $backup_file 2>&1";
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            log_message("✅ Database restored from backup");
        } else {
            log_message("❌ Database restore failed");
            return false;
        }
    }
    
    // Restore files
    $files_dir = $backup_dir;
    if (is_dir($files_dir)) {
        exec("cp -r $files_dir/* " . __DIR__ . "/");
        log_message("✅ Files restored from backup");
    }
    
    log_message("✅ Rollback completed");
    return true;
}

// Main execution
if ($argc > 1) {
    $action = $argv[1];
    
    switch ($action) {
        case 'deploy':
            deploy();
            break;
        case 'rollback':
            rollback();
            break;
        case 'test':
            run_tests();
            break;
        default:
            echo "Usage: php deploy_mlm_system.php [deploy|rollback|test]\n";
    }
} else {
    deploy();
}
?>
