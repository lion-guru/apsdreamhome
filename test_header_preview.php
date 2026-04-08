<?php
require_once __DIR__ . '/vendor/autoload.php';

use Microsoft\Playwright\Playwright;
use Microsoft\Playwright\Browser\BrowserType;
use Microsoft\Playwright\Page\Page;

try {
    echo "Starting Playwright...\n";
    
    $playwright = Playwright::create();
    $browser = $playwright->launch([
        'headless' => false,
        'args' => ['--window-size=1920,1080'],
    ]);
    
    $context = $browser->newContext([
        'viewport' => ['width' => 1920, 'height' => 1080],
    ]);
    
    $page = $context->newPage();
    
    echo "Navigating to http://localhost/apsdreamhome/\n";
    $page->navigate('http://localhost/apsdreamhome/');
    $page->waitForLoadState('networkidle');
    
    echo "Taking screenshot...\n";
    $page->screenshot([
        'path' => __DIR__ . '/screenshot_header.png',
        'fullPage' => false,
    ]);
    
    // Check for specific elements
    echo "\nChecking header elements:\n";
    
    $elements = [
        'Logo' => '.navbar-brand',
        'Home Link' => '.nav-link:has-text("Home")',
        'Properties Link' => '.nav-link:has-text("Properties")',
        'Projects Dropdown' => '.nav-link:has-text("Projects")',
        'Register Dropdown' => '#registerDropdown',
        'Login Dropdown' => '#loginDropdown',
        'Phone Button' => 'a[href^="tel:"]',
        'Admin Button' => 'a[href*="admin/login"]',
        'Phone Number Text' => 'text="+91 92771 21112"',
        'Admin Text' => 'text="Admin"',
    ];
    
    foreach ($elements as $name => $selector) {
        try {
            $count = $page->locator($selector)->count();
            $visible = $count > 0 ? $page->locator($selector)->first()->isVisible() : false;
            echo "  - $name: found=$count, visible=" . ($visible ? 'YES' : 'NO') . "\n";
        } catch (Exception $e) {
            echo "  - $name: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    // Get navbar HTML
    echo "\nNavbar HTML:\n";
    $navbarHtml = $page->locator('.navbar-nav')->innerHTML();
    file_put_contents(__DIR__ . '/navbar_html.txt', $navbarHtml);
    echo "  Saved to navbar_html.txt\n";
    
    // Get viewport info
    echo "\nViewport: " . $page->viewportSize()['width'] . "x" . $page->viewportSize()['height'] . "\n";
    
    // Check if elements are within viewport
    echo "\nElement positions:\n";
    try {
        $adminBtn = $page->locator('a[href*="admin/login"]')->first();
        $box = $adminBtn->boundingBox();
        if ($box) {
            echo "  Admin button: x={$box['x']}, y={$box['y']}, width={$box['width']}, height={$box['height']}\n";
        }
    } catch (Exception $e) {
        echo "  Admin button position: ERROR\n";
    }
    
    $page->screenshot(['path' => __DIR__ . '/screenshot_header.png']);
    echo "\nScreenshot saved to screenshot_header.png\n";
    
    $browser->close();
    $playwright->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
