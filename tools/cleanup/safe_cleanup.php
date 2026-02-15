<?php
// Safe cleanup of duplicate files
echo "=== SAFE CLEANUP OF DUPLICATE FILES ===\n\n";

// Files to keep (essential files)
$essential_files = [
    'index.php',           // Original homepage
    'index_improved.php',  // Improved homepage (being used)
    'login.php',           // Main login page
    'register_simple.php', // Main register page
    'about.php',           // About page
    'contact.php',         // Contact page
    'properties.php',      // Properties page
    'projects.php',        // Projects page
    'app.php',             // Main router
    'admin/index.php',     // Admin login
    'admin/enhanced_dashboard.php', // Admin dashboard
    'includes/components/header.php', // Header template
    'includes/components/footer.php', // Footer template
    'config/unified_config.php', // Configuration
];

// Files to delete (duplicates, test files, debug files)
$files_to_delete = [
    // Test files
    'test_login_existing.php',
    'test_improved_homepage.php',
    'ui_improvement_final.php',
    'css_lint_check.php',
    'remaining_issues_check.php',
    'final_readiness_check.php',
    'final_website_test.php',
    'analysis_existing_auth.php',
    'auth_structure_analysis.php',
    'duplicate_files_analysis.php',
    
    // Debug files (keep only debug_login_deep.php)
    'debug_app_properties.php',
    'debug_clean.php',
    'debug_contact.php',
    'debug_contact_individual.php',
    'debug_direct.php',
    'debug_direct2.php',
    'debug_exact.php',
    'debug_fresh.php',
    'debug_fresh_contact.php',
    'debug_index.php',
    'debug_individual.php',
    'debug_properties.php',
    'debug_properties_app.php',
    'debug_properties_final.php',
    'debug_routing.php',
    
    // Temporary files
    'temp_app_test.php',
    'temp_login_test.php',
    'temp_homepage_test.php',
    'temp_homepage_improved.php',
    'temp_ui_final.php',
    'temp_final_test.php',
    'temp_route_check.php',
    
    // Backup files
    'index_backup_simple_router.php',
    'properties_backup_original.php',
    
    // Duplicates
    'index_enhanced.php', // Using index_improved.php instead
    'testimonials.php', // Not essential
];

$total_space_freed = 0;
$deleted_count = 0;
$error_count = 0;

echo "🧹 CLEANING UP FILES...\n\n";

foreach ($files_to_delete as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        
        if (unlink($file)) {
            $total_space_freed += $size;
            $deleted_count++;
            echo "✅ DELETED: $file (" . number_format($size) . " bytes)\n";
        } else {
            $error_count++;
            echo "❌ ERROR: Could not delete $file\n";
        }
    } else {
        echo "ℹ️  SKIP: $file (not found)\n";
    }
}

echo "\n=== CLEANUP SUMMARY ===\n";
echo "✅ Files deleted: $deleted_count\n";
echo "❌ Errors: $error_count\n";
echo "💾 Space freed: " . number_format($total_space_freed) . " bytes (" . round($total_space_freed / 1024, 2) . " KB)\n";

echo "\n🎉 CLEANUP COMPLETE! 🎉\n";
echo "📁 Essential files preserved\n";
echo "🗑️  Duplicates and test files removed\n";
echo "💾 Space saved: " . round($total_space_freed / 1024, 2) . " KB\n";
?>

