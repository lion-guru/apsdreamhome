<?php
/**
 * FIX ALL ADMIN VIEWS - Make sidebar consistent
 * This script will fix all admin views to use proper layout
 */

$basePath = __DIR__ . '/..';
$viewsPath = $basePath . '/app/views';

// Find all admin views that use old layout includes
$viewsToFix = [];
$dirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
foreach ($dirIterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        // Check if this is an admin view with old includes
        if (strpos($file->getPathname(), '/admin/') !== false && 
            (strpos($content, "layouts/admin_header.php") !== false || 
             strpos($content, "layouts/admin_footer.php") !== false)) {
            $viewsToFix[] = $file->getPathname();
        }
    }
}

echo "Found " . count($viewsToFix) . " admin views with old layout includes\n\n";

// Each view needs different fix based on its structure
// Let's fix the most critical ones first

$criticalViews = [
    'admin/properties/index.php',
    'admin/leads/index.php',
    'admin/plots/index.php',
    'admin/sites/index.php',
    'admin/bookings/index.php',
    'admin/commission/index.php',
    'admin/gallery/index.php',
    'admin/users/index.php',
    'admin/users/create.php',
];

echo "Creating FIXED versions of key admin views...\n\n";

// For each critical view, we need to check what the controller passes
// and make the view compatible

// Fix 1: Check if view expects $content variable (from controller render)
// Many views are standalone pages that don't use the controller's render method

// Let's check what the controllers return
$controllers = [
    'PropertyManagementController',
    'LeadController', 
    'BookingController',
    'UserController',
    'SiteController',
    'GalleryController'
];

echo "1. Controllers return data to views via \$data array\n";
echo "2. Views should access data directly, not expect \$content\n\n";

// Check a sample controller to understand the flow
$sampleController = $basePath . '/app/Http/Controllers/Admin/PropertyManagementController.php';
if (file_exists($sampleController)) {
    $content = file_get_contents($sampleController);
    if (strpos($content, 'render(') !== false) {
        echo "✅ PropertyManagementController uses render() method\n";
    }
}

echo "Strategy:\n";
echo "- Fix views to properly extract \$data from controller\n";
echo "- Remove old manual header/footer includes\n";
echo "- Use consistent sidebar from admin_header.php\n\n";

echo "Starting fixes...\n";

// For views that use controller->render(), the data is automatically extracted
// The issue is views that have their own full HTML with old includes

// Let's identify which views use controller render vs standalone HTML
$viewsUsingRender = [];
$viewsWithFullHTML = [];

foreach ($viewsToFix as $viewPath) {
    $content = file_get_contents($viewPath);
    $relativePath = str_replace($viewsPath . '/', '', $viewPath);
    
    // Check if it has <html> tag (standalone) or just content (from controller)
    if (strpos($content, '<html') !== false || strpos($content, '<!DOCTYPE') !== false) {
        $viewsWithFullHTML[] = $relativePath;
    } else {
        $viewsUsingRender[] = $relativePath;
    }
}

echo "Views using controller render(): " . count($viewsUsingRender) . "\n";
echo "Views with standalone HTML: " . count($viewsWithFullHTML) . "\n\n";

echo "Fixing standalone HTML views...\n";

// For standalone views, we need to either:
// 1. Convert them to use controller render (best solution)
// 2. Or remove the old includes and fix the structure

$fixed = 0;
foreach ($viewsWithFullHTML as $view) {
    $path = $viewsPath . '/' . $view;
    
    // Skip if already fixed or if it's a layout file
    if (strpos($view, '/layouts/') !== false) continue;
    
    // Remove the old includes
    $content = file_get_contents($path);
    $original = $content;
    
    // Remove header include
    $content = preg_replace('/<\?php\s*include[^>]*admin_header[^>]*\/?>/i', '', $content);
    $content = preg_replace('/<\?php\s*include\s+__DIR__\s*\.\s*[\'"][^"]*admin_header[\'"]\s*;?\s*?>/i', '', $content);
    
    // Remove footer include  
    $content = preg_replace('/<\?php\s*include[^>]*admin_footer[^>]*\/?>/i', '', $content);
    $content = preg_replace('/<\?php\s*include\s+__DIR__\s*\.\s*[\'"][^"]*admin_footer[\'"]\s*;?\s*?>/i', '', $content);
    
    // If content changed, save
    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "   Fixed: $view\n";
        $fixed++;
    }
}

echo "\nFixed $fixed views\n";

echo "\n=== SUMMARY ===\n";
echo "Total views with old includes: " . count($viewsToFix) . "\n";
echo "Views with standalone HTML (fixed): " . count($viewsWithFullHTML) . "\n";
echo "Views using controller render: " . count($viewsUsingRender) . "\n\n";

echo "Note: The actual sidebar consistency depends on:\n";
echo "1. All views using controller->render() method\n";
echo "2. Controllers passing proper \$data to views\n";
echo "3. Views using \$page_title from \$data\n\n";

echo "For proper fix, controllers need to use render() and views need to be content-only.\n";