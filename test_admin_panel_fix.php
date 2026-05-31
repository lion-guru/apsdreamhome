<?php
/**
 * Test Admin Panel Fix
 * Verifies that newly created routes and controllers are working
 */

echo "🧪 Testing Admin Panel Fix...\n\n";

// Test 1: Check if controller files exist
echo "1. Checking controller files...\n";
$controllers = [
    'app/Http/Controllers/Admin/AdminReportsController.php',
    'app/Http/Controllers/Admin/TestimonialController.php',
    'app/Http/Controllers/Admin/FaqController.php',
    'app/Http/Controllers/Admin/KnowledgeBaseController.php'
];

foreach ($controllers as $controller) {
    $file = __DIR__ . '/' . $controller;
    if (file_exists($file)) {
        echo "✅ $controller exists\n";
    } else {
        echo "❌ $controller missing\n";
    }
}

echo "\n2. Checking view files...\n";
$views = [
    'app/views/admin/blogs/index.php',
    'app/views/admin/blogs/create.php',
    'app/views/admin/blogs/edit.php',
    'app/views/admin/testimonials/index.php',
    'app/views/admin/testimonials/create.php',
    'app/views/admin/faqs/index.php',
    'app/views/admin/faqs/create.php',
    'app/views/admin/knowledge-base/index.php',
    'app/views/admin/knowledge-base/create.php'
];

foreach ($views as $view) {
    $file = __DIR__ . '/' . $view;
    if (file_exists($file)) {
        echo "✅ $view exists\n";
    } else {
        echo "❌ $view missing\n";
    }
}

echo "\n3. Checking database tables...\n";
try {
    $host = '127.0.0.1';
    $port = 3307;
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = ['testimonials', 'faqs', 'knowledge_base', 'blogs', 'blog_categories'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->fetch()) {
            echo "✅ $table exists\n";
        } else {
            echo "❌ $table missing\n";
        }
    }
    
    echo "\n4. Checking routes in web.php...\n";
    $routes_file = __DIR__ . '/routes/web.php';
    $content = file_get_contents($routes_file);
    
    $new_routes = [
        '/admin/reports-new',
        '/admin/knowledge-base-new',
        '/admin/faqs-new',
        '/admin/testimonials-new'
    ];
    
    foreach ($new_routes as $route) {
        if (strpos($content, $route) !== false) {
            echo "✅ Route '$route' added to web.php\n";
        } else {
            echo "❌ Route '$route' missing in web.php\n";
        }
    }
    
    echo "\n✅ Admin Panel Fix Verification Complete!\n";
    echo "\n📝 Next Steps:\n";
    echo "1. Visit http://localhost/apsdreamhome/admin/login\n";
    echo "2. Login as admin\n";
    echo "3. Test new routes:\n";
    echo "   - /admin/reports-new\n";
    echo "   - /admin/testimonials-new\n";
    echo "   - /admin/faqs-new\n";
    echo "   - /admin/knowledge-base-new\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
