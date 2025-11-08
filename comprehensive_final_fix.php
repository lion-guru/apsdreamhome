<?php
/**
 * Final Comprehensive SQL Fixer
 * Handles all remaining syntax errors
 */

function comprehensiveSQLFix($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Pattern 1: Fix broken prepared statements with nested calls
    $content = preg_replace_callback(
        '/\$stmt = \$conn->prepare\(\$conn->query\("([^"]+)`\?"\);[^"]*"\);/s',
        function($matches) {
            return '$stmt = $conn->prepare("' . $matches[1] . ' `$table`");';
        },
        $content
    );
    
    // Pattern 2: Fix completely broken nested statements
    $content = preg_replace_callback(
        '/\$stmt = \$conn->prepare\(\$stmt = \$conn->prepare\(\$conn->query\("([^"]+)"\);[^}]*\);/s',
        function($matches) {
            return '$stmt = $conn->prepare("' . $matches[1] . '");';
        },
        $content
    );
    
    // Pattern 3: Fix broken INSERT statements with orphaned parts
    $content = preg_replace_callback(
        '/\$stmt = \$conn->prepare\(\$conn->query\("(INSERT INTO [^(]+ \([^)]+\) VALUES \([^)]+\))[^"]*"\);/s',
        function($matches) {
            return '$stmt = $conn->prepare("' . $matches[1] . '");';
        },
        $content
    );
    
    // Pattern 4: Fix broken SELECT COUNT statements
    $content = preg_replace_callback(
        '/\$stmt = \$conn->prepare\(\$conn->query\("(SELECT COUNT\(\*\) FROM [^"]+)"\);/s',
        function($matches) {
            return '$stmt = $conn->prepare("' . $matches[1] . '");';
        },
        $content
    );
    
    // Pattern 5: Fix broken variable assignments
    $content = str_replace('$stmt = $conn->prepare($conn->query("', '$stmt = $conn->prepare("', $content);
    
    // Pattern 6: Remove orphaned SQL fragments
    $content = preg_replace('/\$result = \$stmt->get_result\(\);[^)]*\)"\);/', '', $content);
    $content = preg_replace('/\$stmt->bind_param\("[^"]*",\s*\$[a-zA-Z_]*,\s*\);/', '', $content);
    $content = preg_replace('/\$stmt->bind_param\("[^"]*",\s*\$[a-zA-Z_]*`\);/', '', $content);
    
    // Pattern 7: Fix incomplete NOW() statements
    $content = str_replace('NOW())");', 'NOW());', $content);
    
    // Pattern 10: Remove completely broken nested statements
    if (strpos($content, '$stmt = $conn->prepare($stmt = $conn->prepare(') !== false) {
        $content = str_replace('$stmt = $conn->prepare($stmt = $conn->prepare(', '$result = $conn->query(', $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        return true;
    }
    return false;
}

function checkFileSyntax($filePath) {
    $command = sprintf('php -l %s 2>&1', escapeshellarg($filePath));
    $output = shell_exec($command);
    return strpos($output, 'No syntax errors') !== false;
}

    // Define the list of files to fix
    $filesToFix = [
        'admin/add_admin.php',
        'admin/add_category.php',
        'admin/add_product.php',
        'admin/add_user.php',
        'admin/admin_dashboard.php',
        'admin/edit_admin.php',
        'admin/edit_category.php',
        'admin/edit_product.php',
        'admin/edit_user.php',
        'admin/manage_admins.php',
        'admin/manage_categories.php',
        'admin/manage_products.php',
        'admin/manage_users.php',
        'admin/view_orders.php',
        'authentication/login.php',
        'authentication/register.php',
        'cart.php',
        'checkout.php',
        'contact.php',
        'includes/db_connection.php',
        'index.php',
        'product_detail.php',
        'products.php',
        'profile.php',
        'search.php',
        'update_profile.php',
        'wishlist.php'
    ];

    echo "Processing files...\n";

echo "=== Final Comprehensive SQL Fixer ===\n\n";

$fixedCount = 0;
$stillBroken = [];

foreach ($filesToFix as $file) {
    if (file_exists($file)) {
        echo basename($file) . ":\n";
        
        // Check current syntax
        if (checkFileSyntax($file)) {
            echo "  ✓ Already has valid syntax\n";
            continue;
        }
        
        // Try to fix
        if (comprehensiveSQLFix($file)) {
            echo "  ✓ Applied comprehensive fixes\n";
            $fixedCount++;
        } else {
            echo "  - No automated fixes applied\n";
        }
        
        // Check again
        if (checkFileSyntax($file)) {
            echo "  ✓ Syntax now valid\n";
        } else {
            echo "  ✗ Still has syntax errors\n";
            $stillBroken[] = $file;
        }
        
        echo "\n";
    }
}

echo "=== Final Results ===\n";
echo "Files processed: " . count($filesToFix) . "\n";
echo "Files fixed: $fixedCount\n";
echo "Files still with errors: " . count($stillBroken) . "\n";

if (count($stillBroken) > 0) {
    echo "\nFiles requiring manual intervention:\n";
    foreach ($stillBroken as $file) {
        echo "- $file\n";
    }
} else {
    echo "\n🎉 All syntax errors have been resolved!\n";
}

    $filesWithErrors = [];

    // Get all PHP files in the project
    $allPhpFiles = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($allPhpFiles as $file) {
        if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getPathname(), 'vendor') === false) {
            $filePath = $file->getPathname();
            if (!checkFileSyntax($filePath)) {
                $filesWithErrors[] = $filePath;
            }
        }
    }

    if (!empty($filesWithErrors)) {
        echo "\n=== Files Still With Syntax Errors ===\n";
        foreach ($filesWithErrors as $errorFile) {
            echo "- " . $errorFile . "\n";
        }
        echo "\nManual intervention required for these files.\n";
    } else {
        echo "\nAll PHP files in the project have valid syntax.\n";
    }

    echo "\n";

?>