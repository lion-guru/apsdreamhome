<?php
/**
 * Simple test to verify legacy route processing fix
 */

// Test the web.php structure directly
echo "Testing web.php structure...\n\n";

$legacyRoutesFile = __DIR__ . '/../routes/web.php';
if (file_exists($legacyRoutesFile)) {
    $webRoutes = [];
    require $legacyRoutesFile;
    
    echo "Web routes structure:\n";
    echo "- Root keys: " . implode(', ', array_keys($webRoutes)) . "\n";
    echo "- Public routes: " . (isset($webRoutes['public']) ? 'YES' : 'NO') . "\n";
    
    if (isset($webRoutes['public']['GET'])) {
        echo "- Public GET routes: " . count($webRoutes['public']['GET']) . "\n";
        echo "- Error test routes found:\n";
        
        $errorRoutes = [];
        foreach ($webRoutes['public']['GET'] as $route => $handler) {
            if (strpos($route, 'test/error') !== false) {
                $errorRoutes[] = "  $route => $handler";
            }
        }
        
        if (empty($errorRoutes)) {
            echo "  (none found)\n";
        } else {
            echo implode("\n", $errorRoutes) . "\n";
        }
    }
    
    // Test direct access to error test route
    echo "\nTesting direct route access:\n";
    if (isset($webRoutes['public']['GET']['/test/error/404'])) {
        echo "✓ Route /test/error/404 found: " . $webRoutes['public']['GET']['/test/error/404'] . "\n";
    } else {
        echo "✗ Route /test/error/404 not found\n";
    }
    
    if (isset($webRoutes['public']['GET']['/test/error/500'])) {
        echo "✓ Route /test/error/500 found: " . $webRoutes['public']['GET']['/test/error/500'] . "\n";
    } else {
        echo "✗ Route /test/error/500 not found\n";
    }
} else {
    echo "✗ web.php file not found at: $legacyRoutesFile\n";
}

echo "\nTest completed.\n";