<?php
/**
 * APS Dream Home - Final Project Cleanup Script
 * This script organizes all remaining scattered files
 */

echo "🧹 APS Dream Home - Final Cleanup Script\n";
echo "=======================================\n\n";

// Create organized directories
$directories = [
    'src/controllers',
    'src/models',
    'src/services',
    'src/views',
    'src/helpers',
    'src/utils',
    'database/backups',
    'database/migrations',
    'database/tools',
    'tests/unit',
    'tests/integration',
    'tests/functional',
    'assets/css',
    'assets/js',
    'assets/images',
    'assets/fonts',
    'logs/errors',
    'logs/access',
    'config/environments',
    'config/backups',
    'scripts/database',
    'scripts/deployment',
    'scripts/maintenance',
    'docs/api',
    'docs/user-guides',
    'docs/developer',
    'docs/troubleshooting'
];

// Create directories
echo "📁 Creating comprehensive directory structure...\n";
foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "✓ Created: $dir\n";
    } else {
        echo "✓ Directory exists: $dir\n";
    }
}

// Files to move to src/views/
$viewFiles = [
    'about.php',
    'contact.php',
    'property.php',
    'properties.php',
    'property-detail.php',
    'property-details.php',
    'property-details.old.php',
    'property-details.new.php',
    'property-listings.php',
    'post-property.php',
    'submitproperty.php',
    'submitpropertyupdate.php',
    'submitpropertydelete.php',
    'gallery.php',
    'legal.php',
    'career.php',
    'news.php',
    'news-detail.php',
    'plot.php',
    'plots-availability.php',
    'thank-you.php',
    'budhacity.php',
    'gorakhpur-raghunath-nagri.php',
    'gorakhpur-suryoday-colony.php',
    'lucknow-project.php',
    'lucknow-ram-nagri.php',
    'lucknow-ram-nagri.phpR',
    'varanasi-ganga-nagri.php',
    'stateproperty.php',
    'project.php',
    'project-detail.php',
    'view_project.php',
    'bank.php',
    'calc.php',
    'captcha.php',
    'ifsc_api.php',
    'mail.php',
    'smail.php',
    'send mail.php',
    'send_otp.php',
    'verify_otp.php',
    'header.php',
    'PrivacyPolicy.php',
    'SMTP.php'
];

// Move view files
echo "\n📂 Moving view files to src/views/...\n";
foreach ($viewFiles as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    $destPath = __DIR__ . '/src/views/' . $file;

    if (file_exists($sourcePath)) {
        if (rename($sourcePath, $destPath)) {
            echo "✓ Moved: $file → src/views/$file\n";
        } else {
            echo "✗ Failed to move: $file\n";
        }
    } else {
        echo "⚠ Source not found: $file\n";
    }
}

// Files to move to src/controllers/
$controllerFiles = [
    'admin_debug.php',
    'admin_diagnostic.php',
    'admin_test.php',
    'ai_assistant_test.php',
    'ai_chatbot_api.php',
    'ai_suggestions.php',
    'google_login.php',
    'process_lead.php',
    'process_login.php',
    'process_registration.php',
    'process_schedule.php',
    'process_visit.php',
    'property_chatbot.php',
    'property_description_generator.php',
    'property_search.php',
    'protected_page.php',
    'session_check.php',
    'session_test.php',
    'user.php',
    'users.php',
    'newsletter_signup_handler.php',
    'sample_api_test.php',
    'routing_debug.php',
    'login_authentication_test.php',
    'login_system_diagnostic.php',
    'test_env.php',
    'test_include.php',
    'users_columns_test.php'
];

// Move controller files
echo "\n🎮 Moving controller files to src/controllers/...\n";
foreach ($controllerFiles as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    $destPath = __DIR__ . '/src/controllers/' . $file;

    if (file_exists($sourcePath)) {
        if (rename($sourcePath, $destPath)) {
            echo "✓ Moved: $file → src/controllers/$file\n";
        } else {
            echo "✗ Failed to move: $file\n";
        }
    } else {
        echo "⚠ Source not found: $file\n";
    }
}

// Files to move to database/tools/
$databaseToolFiles = [
    'database_analyzer.php',
    'db_schema_audit.php',
    'db_schema_version_control.php',
    'db_backup_migration_manager.php',
    'db_check.php',
    'db_checklist.php',
    'db_connect_test.php',
    'db_schema_dump.php',
    'simple_schema_compare.php',
    'compare_full_schema.php',
    'improved_migration.php',
    'final_migration.php',
    'migrate.php',
    'migrate_databases.php',
    'migrate_databases.bat',
    'migrate_without_fk.php',
    'import_database.php',
    'export_database_structure.php',
    'export_import.php',
    'export_mlm_schema.php',
    'check_database.php',
    'check_databases.php',
    'check_database_structure.php',
    'check_mlm_setup.php',
    'check_mlm_state.php',
    'check_mlm_tables.php',
    'check_sponsor.php',
    'check_sponsor_ajax.php',
    'check_tables_simple.php',
    'setup_mlm_commissions.php',
    'setup_mlm_test_users.php',
    'create_sample_sponsor.php',
    'create_test_users.php',
    'create_test_associates.php',
    'fix_commission_levels.php',
    'fix_db_permissions.php',
    'fix_mlm_test_data.php',
    'update_db_connections.php',
    'update_schema_safely.php',
    'organize_project.php'
];

// Move database tool files
echo "\n🗃️ Moving database tool files to database/tools/...\n";
foreach ($databaseToolFiles as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    $destPath = __DIR__ . '/database/tools/' . $file;

    if (file_exists($sourcePath)) {
        if (rename($sourcePath, $destPath)) {
            echo "✓ Moved: $file → database/tools/$file\n";
        } else {
            echo "✗ Failed to move: $file\n";
        }
    } else {
        echo "⚠ Source not found: $file\n";
    }
}

// Files to move to tests/functional/
$testFiles = [
    'test_application.php',
    'test_application_simple.php',
    'test_connection.php',
    'test_db.php',
    'test_db_connection.php',
    'test_admin.php',
    'test_commission_calculation.php',
    'test_commission_simple.php',
    'test_css.php',
    'test_dashboard_loads.php',
    'test_google_auth.php',
    'test_mlm_calculation.php',
    'test_mlm_commissions.php',
    'test_properties.php',
    'test_visit_schedule.php',
    'testingdash.php',
    'debug.php',
    'test.php',
    'tmp_hash.php'
];

// Move test files
echo "\n🧪 Moving test files to tests/functional/...\n";
foreach ($testFiles as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    $destPath = __DIR__ . '/tests/functional/' . $file;

    if (file_exists($sourcePath)) {
        if (rename($sourcePath, $destPath)) {
            echo "✓ Moved: $file → tests/functional/$file\n";
        } else {
            echo "✗ Failed to move: $file\n";
        }
    } else {
        echo "⚠ Source not found: $file\n";
    }
}

// Files to move to scripts/
$scriptFiles = [
    'advanced_apache_fix.ps1',
    'apache_config_check.ps1',
    'apache_php_verify.ps1',
    'apache_startup_diagnostic.ps1',
    'auto_config_repair.php',
    'auto_routing_fix.ps1',
    'automated_maintenance.ps1',
    'automation_cron.php',
    'check_admin_dashboard.php',
    'check_and_cleanup.php',
    'check_assets.php',
    'check_broken_links.php',
    'cleanup_assets.php',
    'cleanup_db_connection.ps1',
    'cleanup_large_files.ps1',
    'cleanup_old_databases.php',
    'code_quality_analyzer.php',
    'comprehensive_apache_diagnostic.ps1',
    'comprehensive_apache_fix.ps1',
    'comprehensive_duplicate_cleanup.php',
    'comprehensive_duplicate_cleanup.ps1',
    'comprehensive_duplicate_finder.ps1',
    'comprehensive_system_diagnostic.php',
    'copy_vendor_files.php',
    'delete_project.php',
    'execute_migration.ps1',
    'find_duplicates.bat',
    'find_duplicates.py',
    'find_duplicates.sh',
    'find_js_css_duplicates.ps1',
    'find_js_css_duplicates.sh',
    'find_php_duplicates.ps1',
    'find_unused_files.php',
    'find_unused_files_no_admin.php',
    'fix_admin_htaccess.ps1',
    'fix_apache.ps1',
    'fix_apache_config.ps1',
    'fix_htaccess.ps1',
    'fix_mysql_aria.bat',
    'fix_php_apache.ps1',
    'manage_assets.php',
    'optimize_dependencies.ps1',
    'optimize_project_structure.ps1',
    'remove_duplicates.ps1',
    'remove_js_css_duplicates.ps1',
    'restart_apache.ps1',
    'run_all_updates.php',
    'run_duplicate_cleanup.bat',
    'run_duplicate_cleanup.php',
    'setup.php',
    'setup_assets.php',
    'system_health_check.php',
    'system_repair.php',
    'update_bank_details.php',
    'update_menu.php',
    'update_pages.php',
    'update_password.php',
    'update_passwords.php',
    'update_paths.php'
];

// Move script files
echo "\n📜 Moving script files to scripts/...\n";
foreach ($scriptFiles as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    $destPath = __DIR__ . '/scripts/' . $file;

    if (file_exists($sourcePath)) {
        if (rename($sourcePath, $destPath)) {
            echo "✓ Moved: $file → scripts/$file\n";
        } else {
            echo "✗ Failed to move: $file\n";
        }
    } else {
        echo "⚠ Source not found: $file\n";
    }
}

// Dashboard files to move to src/views/dashboards/
$dashboardFiles = [
    'agent_dashboard.php',
    'associate_dashboard.php',
    'associate_self_service.php',
    'associate_notifications.php',
    'builder_dashboard.php',
    'customer_dashboard.php',
    'dash.php',
    'dashasso.php',
    'employee_dashboard.php',
    'investor_dashboard.php',
    'tenant_dashboard.php',
    'user_dashboard.php',
    'user_ai_suggestions.php',
    'edit-profile.php',
    'profile.php',
    'saved-searches.php'
];

// Move dashboard files
echo "\n📊 Moving dashboard files to src/views/dashboards/...\n";
foreach ($dashboardFiles as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    $destPath = __DIR__ . '/src/views/dashboards/' . $file;

    if (file_exists($sourcePath)) {
        if (rename($sourcePath, $destPath)) {
            echo "✓ Moved: $file → src/views/dashboards/$file\n";
        } else {
            echo "✗ Failed to move: $file\n";
        }
    } else {
        echo "⚠ Source not found: $file\n";
    }
}

// Files to move to docs/
$docFiles = [
    'google_auth_setup_guide.md',
    'google_oauth_setup_guide_hi.txt',
    'project_structure.md'
];

// Move documentation files
echo "\n📖 Moving documentation files to docs/...\n";
foreach ($docFiles as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    $destPath = __DIR__ . '/docs/' . $file;

    if (file_exists($sourcePath)) {
        if (rename($sourcePath, $destPath)) {
            echo "✓ Moved: $file → docs/$file\n";
        } else {
            echo "✗ Failed to move: $file\n";
        }
    } else {
        echo "⚠ Source not found: $file\n";
    }
}

// Files to move to assets/
$assetFiles = [
    'favicon.ico',
    'jquery.orgchart.css'
];

// Move asset files
echo "\n🎨 Moving asset files to assets/...\n";
foreach ($assetFiles as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    $destPath = __DIR__ . '/assets/' . $file;

    if (file_exists($sourcePath)) {
        if (rename($sourcePath, $destPath)) {
            echo "✓ Moved: $file → assets/$file\n";
        } else {
            echo "✗ Failed to move: $file\n";
        }
    } else {
        echo "⚠ Source not found: $file\n";
    }
}

// Create a summary report
echo "\n📊 FINAL CLEANUP SUMMARY\n";
echo "========================\n";
echo "✓ Organized view files into src/views/\n";
echo "✓ Organized controller files into src/controllers/\n";
echo "✓ Organized database tools into database/tools/\n";
echo "✓ Organized test files into tests/functional/\n";
echo "✓ Organized script files into scripts/\n";
echo "✓ Organized dashboard files into src/views/dashboards/\n";
echo "✓ Organized documentation files into docs/\n";
echo "✓ Organized asset files into assets/\n\n";

echo "📁 FINAL PROJECT STRUCTURE:\n";
echo "==========================\n";
echo "apsdreamhome/\n";
echo "├── 📁 app/                    # Application core (MVC)\n";
echo "├── 📁 public/                 # Web root\n";
echo "├── 📁 src/                    # Source code (organized)\n";
echo "│   ├── 📁 controllers/        # All controllers\n";
echo "│   ├── 📁 models/            # Models\n";
echo "│   ├── 📁 services/          # Services\n";
echo "│   ├── 📁 views/             # Views organized\n";
echo "│   │   ├── 📁 dashboards/    # Dashboard views\n";
echo "│   │   └── 📁 ...            # Other views\n";
echo "│   └── 📁 helpers/           # Helper classes\n";
echo "├── 📁 database/              # All database files\n";
echo "│   ├── 📁 backups/           # Database backups\n";
echo "│   ├── 📁 migrations/       # Database migrations\n";
echo "│   └── 📁 tools/             # Database tools\n";
echo "├── 📁 tests/                 # Test files\n";
echo "│   ├── 📁 unit/             # Unit tests\n";
echo "│   ├── 📁 integration/      # Integration tests\n";
echo "│   └── 📁 functional/        # Functional tests\n";
echo "├── 📁 scripts/               # Utility scripts\n";
echo "├── 📁 assets/                # Static assets\n";
echo "├── 📁 config/                # Configuration files\n";
echo "├── 📁 docs/                  # Documentation\n";
echo "├── 📁 logs/                  # Log files\n";
echo "├── 📁 uploads/               # File uploads\n";
echo "└── 📁 vendor/                # Dependencies\n\n";

echo "🎯 REMAINING ROOT FILES:\n";
echo "=======================\n";
$remainingFiles = array_diff(scandir(__DIR__), ['.', '..', '.git', '.vscode', '.trae', 'node_modules', 'vendor', 'app', 'public', 'src', 'database', 'tests', 'assets', 'config', 'docs', 'logs', 'uploads', 'scripts']);

foreach ($remainingFiles as $file) {
    if (is_file($file) && !in_array($file, ['.htaccess', 'config.php', 'composer.json', 'composer.lock', 'composer.phar', 'index.php', '404.php', 'robots.txt', 'package.json', 'package-lock.json', 'vite.config.js', '.babelrc', '.gitignore', '.gitignore1', '.ftpquota', '.windsurfrules'])) {
        echo "📄 $file (should be moved to appropriate directory)\n";
    }
}

echo "\n✅ COMPREHENSIVE PROJECT CLEANUP COMPLETE!\n";
echo "🎉 Your APS Dream Home project is now perfectly organized!\n";
?>
