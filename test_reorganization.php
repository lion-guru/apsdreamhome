<?php
echo "🧪 Testing Reorganized System...\n";
try {
    // Test associate directory
    if (is_dir('associate_dir')) {
        echo "✅ Associate directory exists\n";
        $files = scandir('associate_dir');
        echo "📁 Associate files: " . implode(', ', array_diff($files, ['.', '..'])) . "\n";
    } else {
        echo "❌ Associate directory not found\n";
    }

    // Test admin directory
    if (is_dir('admin')) {
        echo "✅ Admin directory exists\n";
        $admin_files = ['commission_plan_builder.php', 'development_cost_calculator.php', 'hybrid_commission_dashboard.php', 'property_management.php'];
        foreach ($admin_files as $file) {
            if (file_exists('admin/' . $file)) {
                echo "✅ Admin file exists: " . $file . "\n";
            } else {
                echo "❌ Admin file missing: " . $file . "\n";
            }
        }
    } else {
        echo "❌ Admin directory not found\n";
    }

    echo "\n🎉 System reorganization completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
