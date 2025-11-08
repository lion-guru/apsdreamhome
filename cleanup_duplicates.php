<?php
$duplicate_files = [
    'index-backup.php',
    'index-enhanced.php',
    'index-improved.php',
    'index-new.php',
    'index-old-backup.php',
    'index-old.php',
    'index-pdo-fixed.php',
    'index-super-enhanced.php',
    'index-test.php',
    'index.backup.php',
    'index2.php',
    'index_backup.php',
    'index_broken.php',
    'index_clean.php',
    'index_complex.php',
    'index_diagnostic.php',
    'index_fixed.php',
    'index_new_backup.php',
    'index_new_fixed.php',
    'index_old_backup.php',
    'index_original_backup.php',
    'index_simple.php',
    'index_syntax_fixed.php',
    'index_template.php',
    'index_template_backup.php',
    'index_template_new.php',
    'index_template_old.php',
    'index_template_original.php',
    'index_universal.php'
];

foreach ($duplicate_files as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            echo "✅ Deleted: $file\n";
        } else {
            echo "❌ Failed to delete: $file\n";
        }
    } else {
        echo "⚠️  File not found: $file\n";
    }
}

echo "\n✅ Cleanup completed!\n";
?>
