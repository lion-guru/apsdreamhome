<?php
/**
 * Admin Menu Fixes Verification Script
 * Tests BlogController and routes that were fixed
 */

echo "=== Admin Menu Fixes Verification Script ===\n\n";

// Include required files
require_once __DIR__ . '/app/Core/ConfigService.php';
require_once __DIR__ . '/app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

$results = [];
$allPassed = true;

// ============================================================
// Test 1: Check BlogController exists and is readable
// ============================================================
echo "Test 1: Checking BlogController.php...\n";
$blogControllerPath = __DIR__ . '/app/Http/Controllers/Admin/BlogController.php';
if (file_exists($blogControllerPath)) {
    $content = file_get_contents($blogControllerPath);
    
    // Check if it uses correct views (admin/blogs instead of admin/blog)
    $usesBlogsViews = (strpos($content, 'admin/blogs/index') !== false);
    $usesBlogViews = (strpos($content, 'admin/blog/index') !== false);
    
    if ($usesBlogsViews && !$usesBlogViews) {
        echo "✅ BlogController uses correct views (admin/blogs)\n";
        $results['blog_controller'] = 'PASS';
    } else {
        echo "❌ BlogController view paths incorrect\n";
        echo "   Uses admin/blogs: " . ($usesBlogsViews ? 'Yes' : 'No') . "\n";
        echo "   Uses admin/blog: " . ($usesBlogViews ? 'Yes (should be No)' : 'No') . "\n";
        $results['blog_controller'] = 'FAIL';
        $allPassed = false;
    }
    
    // Check if redirects are correct (/admin/blogs)
    $correctRedirects = (strpos($content, '/admin/blogs') !== false);
    $oldRedirects = (strpos($content, '/admin/blog') !== false);
    
    if ($correctRedirects) {
        echo "✅ BlogController uses correct redirects (/admin/blogs)\n";
        $results['blog_redirects'] = 'PASS';
    } else {
        echo "❌ BlogController redirects incorrect\n";
        $results['blog_redirects'] = 'FAIL';
        $allPassed = false;
    }
    
    // Check variable names
    $correctVariable = (strpos($content, '$blogs') !== false);
    $oldVariable = (strpos($content, '$posts') !== false || strpos($content, '$post') !== false);
    
    if ($correctVariable && !$oldVariable) {
        echo "✅ BlogController uses correct variable names ($blogs)\n";
        $results['blog_variables'] = 'PASS';
    } else {
        echo "⚠️ BlogController variable names might need review\n";
        $results['blog_variables'] = 'WARNING';
    }
} else {
    echo "❌ BlogController.php not found\n";
    $results['blog_controller'] = 'FAIL';
    $allPassed = false;
}

echo "\n";

// ============================================================
// Test 2: Check Blog Routes in web.php
// ============================================================
echo "Test 2: Checking Blog Routes in routes/web.php...\n";
$routesPath = __DIR__ . '/routes/web.php';
if (file_exists($routesPath)) {
    $routesContent = file_get_contents($routesPath);
    
    // Check for blog routes
    $blogRoutes = [
        '/admin/blogs',
        '/admin/blogs/create',
        '/admin/blogs/store',
        '/admin/blogs/{id}/edit',
        '/admin/blogs/{id}/update',
        '/admin/blogs/{id}/destroy'
    ];
    
    $missingBlogRoutes = [];
    foreach ($blogRoutes as $route) {
        if (strpos($routesContent, $route) === false) {
            $missingBlogRoutes[] = $route;
        }
    }
    
    if (empty($missingBlogRoutes)) {
        echo "✅ All Blog routes found in web.php\n";
        $results['blog_routes'] = 'PASS';
    } else {
        echo "❌ Missing Blog routes:\n";
        foreach ($missingBlogRoutes as $route) {
            echo "   - $route\n";
        }
        $results['blog_routes'] = 'FAIL';
        $allPassed = false;
    }
} else {
    echo "❌ routes/web.php not found\n";
    $results['blog_routes'] = 'FAIL';
    $allPassed = false;
}

echo "\n";

// ============================================================
// Test 3: Check FAQ Routes in web.php
// ============================================================
echo "Test 3: Checking FAQ Routes in routes/web.php...\n";
if (file_exists($routesPath)) {
    $routesContent = file_get_contents($routesPath);
    
    // Check for FAQ routes
    $faqRoutes = [
        '/admin/faqs-new',
        '/admin/faqs-new/create',
        '/admin/faqs-new/store',
        '/admin/faqs-new/{id}',
        '/admin/faqs-new/{id}/edit',
        '/admin/faqs-new/{id}/update',
        '/admin/faqs-new/{id}/delete'
    ];
    
    $missingFaqRoutes = [];
    foreach ($faqRoutes as $route) {
        if (strpos($routesContent, $route) === false) {
            $missingFaqRoutes[] = $route;
        }
    }
    
    if (empty($missingFaqRoutes)) {
        echo "✅ All FAQ routes found in web.php\n";
        $results['faq_routes'] = 'PASS';
    } else {
        echo "❌ Missing FAQ routes:\n";
        foreach ($missingFaqRoutes as $route) {
            echo "   - $route\n";
        }
        $results['faq_routes'] = 'FAIL';
        $allPassed = false;
    }
} else {
    echo "❌ routes/web.php not found\n";
    $results['faq_routes'] = 'FAIL';
    $allPassed = false;
}

echo "\n";

// ============================================================
// Test 4: Check Blog Views Exist
// ============================================================
echo "Test 4: Checking Blog Views...\n";
$blogViews = [
    'admin/blogs/index.php',
    'admin/blogs/create.php',
    'admin/blogs/edit.php'
];

$missingBlogViews = [];
foreach ($blogViews as $view) {
    $viewPath = __DIR__ . '/app/views/' . $view;
    if (!file_exists($viewPath)) {
        $missingBlogViews[] = $view;
    }
}

if (empty($missingBlogViews)) {
    echo "✅ All Blog views exist\n";
    $results['blog_views'] = 'PASS';
} else {
    echo "❌ Missing Blog views:\n";
    foreach ($missingBlogViews as $view) {
        echo "   - $view\n";
    }
    $results['blog_views'] = 'FAIL';
    $allPassed = false;
}

echo "\n";

// ============================================================
// Test 5: Check FAQ Views Exist
// ============================================================
echo "Test 5: Checking FAQ Views...\n";
$faqViews = [
    'admin/faqs/index.php',
    'admin/faqs/create.php',
    'admin/faqs/edit.php',
    'admin/faqs/show.php'
];

$missingFaqViews = [];
foreach ($faqViews as $view) {
    $viewPath = __DIR__ . '/app/views/' . $view;
    if (!file_exists($viewPath)) {
        $missingFaqViews[] = $view;
    }
}

if (empty($missingFaqViews)) {
    echo "✅ All FAQ views exist\n";
    $results['faq_views'] = 'PASS';
} else {
    echo "❌ Missing FAQ views:\n";
    foreach ($missingFaqViews as $view) {
        echo "   - $view\n";
    }
    $results['faq_views'] = 'FAIL';
    $allPassed = false;
}

echo "\n";

// ============================================================
// Test 6: Check blog_posts Table Exists
// ============================================================
echo "Test 6: Checking blog_posts table...\n";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'blog_posts'");
    if ($stmt->fetch()) {
        echo "✅ blog_posts table exists\n";
        
        // Check for required columns
        $stmt = $db->query("DESCRIBE blog_posts");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = ['id', 'title', 'slug', 'content', 'status', 'created_at'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (empty($missingColumns)) {
            echo "✅ Required columns exist\n";
            $results['blog_table'] = 'PASS';
        } else {
            echo "⚠️ Missing columns: " . implode(', ', $missingColumns) . "\n";
            $results['blog_table'] = 'WARNING';
        }
    } else {
        echo "❌ blog_posts table does not exist\n";
        $results['blog_table'] = 'FAIL';
        $allPassed = false;
    }
} catch (Exception $e) {
    echo "❌ Error checking blog_posts table: " . $e->getMessage() . "\n";
    $results['blog_table'] = 'FAIL';
    $allPassed = false;
}

echo "\n";

// ============================================================
// Test 7: Check faqs Table Exists
// ============================================================
echo "Test 7: Checking faqs table...\n";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'faqs'");
    if ($stmt->fetch()) {
        echo "✅ faqs table exists\n";
        
        // Check for required columns
        $stmt = $db->query("DESCRIBE faqs");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = ['id', 'question', 'answer', 'category', 'status', 'created_at'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (empty($missingColumns)) {
            echo "✅ Required columns exist\n";
            $results['faq_table'] = 'PASS';
        } else {
            echo "⚠️ Missing columns: " . implode(', ', $missingColumns) . "\n";
            $results['faq_table'] = 'WARNING';
        }
    } else {
        echo "❌ faqs table does not exist\n";
        echo "   Run: php scripts/create_admin_tables_simple.php\n";
        $results['faq_table'] = 'FAIL';
        $allPassed = false;
    }
} catch (Exception $e) {
    echo "❌ Error checking faqs table: " . $e->getMessage() . "\n";
    $results['faq_table'] = 'FAIL';
    $allPassed = false;
}

echo "\n";

// ============================================================
// Test 8: Check Admin Menu Items in Database
// ============================================================
echo "Test 8: Checking admin_menu_items table...\n";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'admin_menu_items'");
    if ($stmt->fetch()) {
        echo "✅ admin_menu_items table exists\n";
        
        // Check for Blogs menu item
        $stmt = $db->prepare("SELECT * FROM admin_menu_items WHERE url = '/admin/blogs'");
        $stmt->execute();
        $blogsMenu = $stmt->fetch();
        
        if ($blogsMenu) {
            echo "✅ Blogs menu item exists in database (ID: {$blogsMenu['id']})\n";
            $results['blogs_menu'] = 'PASS';
        } else {
            echo "⚠️ Blogs menu item not in database\n";
            echo "   Run: php scripts/add_admin_menu_items.php\n";
            $results['blogs_menu'] = 'WARNING';
        }
        
        // Check for FAQs menu item
        $stmt = $db->prepare("SELECT * FROM admin_menu_items WHERE url = '/admin/faqs-new'");
        $stmt->execute();
        $faqsMenu = $stmt->fetch();
        
        if ($faqsMenu) {
            echo "✅ FAQs menu item exists in database (ID: {$faqsMenu['id']})\n";
            $results['faqs_menu'] = 'PASS';
        } else {
            echo "⚠️ FAQs menu item not in database\n";
            echo "   Run: php scripts/add_admin_menu_items.php\n";
            $results['faqs_menu'] = 'WARNING';
        }
    } else {
        echo "❌ admin_menu_items table does not exist\n";
        echo "   Run: php testing/root_scripts/setup_rbac_menu.php\n";
        $results['menu_table'] = 'FAIL';
        $allPassed = false;
    }
} catch (Exception $e) {
    echo "❌ Error checking admin_menu_items table: " . $e->getMessage() . "\n";
    $results['menu_table'] = 'FAIL';
    $allPassed = false;
}

echo "\n";

// ============================================================
// Summary
// ============================================================
echo "=== VERIFICATION SUMMARY ===\n";
echo "\nTest Results:\n";
foreach ($results as $test => $status) {
    $symbol = $status === 'PASS' ? '✅' : ($status === 'WARNING' ? '⚠️' : '❌');
    echo "$symbol $test: $status\n";
}

echo "\n";
if ($allPassed) {
    echo "🎉 ALL TESTS PASSED! Admin menu fixes are complete.\n";
    echo "\nNext Steps:\n";
    echo "1. Login to admin panel: http://localhost/apsdreamhome/admin/login\n";
    echo "2. Navigate to Content → Blogs\n";
    echo "3. Navigate to Content → FAQs\n";
    echo "4. Test the features in your browser\n";
} else {
    echo "❌ Some tests failed. Please review the errors above.\n";
    echo "\nTo fix missing tables, run:\n";
    echo "  php scripts/create_admin_tables_simple.php\n";
    echo "  php scripts/add_admin_menu_items.php\n";
}

echo "\nDone.\n";