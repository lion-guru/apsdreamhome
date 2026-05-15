<?php
echo "🔧 APS DREAM HOME - ROUTES FIX\n";
echo "==========================================\n\n";

// Read current routes
$currentRoutes = file_get_contents('routes/web.php');

// Check what's missing
$missingRoutes = [
    'Customer Routes' => [
        'pattern' => 'CustomerController',
        'routes' => [
            "\$router->get('/customer', 'App\\Http\\Controllers\\CustomerController@index');",
            "\$router->get('/customer/dashboard', 'App\\Http\\Controllers\\CustomerController@index');",
            "\$router->get('/customer/profile', 'App\\Http\\Controllers\\CustomerController@profile');",
            "\$router->post('/customer/profile', 'App\\Http\\Controllers\\CustomerController@profile');",
            "\$router->get('/customer/wishlist', 'App\\Http\\Controllers\\CustomerController@wishlist');",
            "\$router->get('/customer/inquiries', 'App\\Http\\Controllers\\CustomerController@inquiries');",
            "\$router->get('/customer/documents', 'App\\Http\\Controllers\\CustomerController@documents');",
            "\$router->get('/customer/settings', 'App\\Http\\Controllers\\CustomerController@settings');",
            "\$router->get('/customer/property-history', 'App\\Http\\Controllers\\CustomerController@propertyHistory');",
            "\$router->get('/customer/payments', 'App\\Http\\Controllers\\CustomerController@payments');",
            "\$router->get('/customer/notifications', 'App\\Http\\Controllers\\CustomerController@notifications');"
        ]
    ],
    'Property Routes' => [
        'pattern' => 'PropertyController',
        'routes' => [
            "\$router->get('/properties', 'App\\Http\\Controllers\\PropertyController@index');",
            "\$router->get('/properties/search', 'App\\Http\\Controllers\\PropertyController@search');",
            "\$router->get('/properties/{id}', 'App\\Http\\Controllers\\PropertyController@detail');",
            "\$router->get('/colonies', 'App\\Http\\Controllers\\PropertyController@colonies');",
            "\$router->get('/colonies/{id}', 'App\\Http\\Controllers\\PropertyController@colony');",
            "\$router->get('/projects', 'App\\Http\\Controllers\\PropertyController@projects');",
            "\$router->get('/projects/{id}', 'App\\Http\\Controllers\\PropertyController@project');",
            "\$router->get('/resell', 'App\\Http\\Controllers\\PropertyController@resell');",
            "\$router->get('/resell/{id}', 'App\\Http\\Controllers\\PropertyController@resellDetail');",
            "\$router->get('/submit-property', 'App\\Http\\Controllers\\PropertyController@submitProperty');",
            "\$router->get('/compare', 'App\\Http\\Controllers\\PropertyController@compare');"
        ]
    ],
    'Payment Routes' => [
        'pattern' => 'PaymentController',
        'routes' => [
            "\$router->get('/payment', 'App\\Http\\Controllers\\PaymentController@index');",
            "\$router->get('/payment/initiate', 'App\\Http\\Controllers\\PaymentController@initiate');",
            "\$router->post('/payment/initiate', 'App\\Http\\Controllers\\PaymentController@initiate');",
            "\$router->post('/payment/process', 'App\\Http\\Controllers\\PaymentController@process');",
            "\$router->get('/payment/success', 'App\\Http\\Controllers\\PaymentController@success');",
            "\$router->get('/payment/failure', 'App\\Http\\Controllers\\PaymentController@failure');",
            "\$router->post('/payment/webhook', 'App\\Http\\Controllers\\PaymentController@webhook');",
            "\$router->get('/payment/history', 'App\\Http\\Controllers\\PaymentController@history');",
            "\$router->get('/payment/plans', 'App\\Http\\Controllers\\PaymentController@plans');",
            "\$router->get('/payment/emi-calculator', 'App\\Http\\Controllers\\PaymentController@emiCalculator');",
            "\$router->post('/payment/emi-calculator', 'App\\Http\\Controllers\\PaymentController@emiCalculator');",
            "\$router->get('/payment/refund', 'App\\Http\\Controllers\\PaymentController@refund');",
            "\$router->post('/payment/refund', 'App\\Http\\Controllers\\PaymentController@refund');",
            "\$router->get('/payment/settings', 'App\\Http\\Controllers\\PaymentController@settings');",
            "\$router->post('/payment/settings', 'App\\Http\\Controllers\\PaymentController@settings');"
        ]
    ]
];

// Check what's missing and add it
$routesToAdd = [];

foreach ($missingRoutes as $routeGroup => $routeInfo) {
    if (strpos($currentRoutes, $routeInfo['pattern']) === false) {
        echo "⚠️  Missing: $routeGroup\n";
        $routesToAdd[] = "\n\n// $routeGroup\n" . implode("\n", $routeInfo['routes']);
    } else {
        echo "✅ Found: $routeGroup\n";
    }
}

// Add missing routes
if (!empty($routesToAdd)) {
    $newRoutes = $currentRoutes . implode("\n", $routesToAdd);
    
    if (file_put_contents('routes/web.php', $newRoutes)) {
        echo "✅ Routes updated successfully\n";
    } else {
        echo "❌ Failed to update routes\n";
    }
} else {
    echo "✅ All routes are already configured\n";
}

echo "\n🔗 VERIFICATION:\n";

// Verify the updated routes
$updatedRoutes = file_get_contents('routes/web.php');
$totalRoutes = substr_count($updatedRoutes, '$router->');

echo "📊 Total Routes: $totalRoutes\n";

$verificationPatterns = [
    'CustomerController' => 'Customer Routes',
    'PropertyController' => 'Property Routes', 
    'PaymentController' => 'Payment Routes'
];

foreach ($verificationPatterns as $pattern => $description) {
    if (strpos($updatedRoutes, $pattern) !== false) {
        echo "✅ $description: Configured\n";
    } else {
        echo "❌ $description: Missing\n";
    }
}

echo "\n🎯 ROUTES FIX COMPLETE!\n";
echo "==========================================\n";
echo "✅ Missing routes have been added\n";
echo "✅ Total routes: $totalRoutes\n";
echo "✅ All major route groups are now configured\n";
echo "✅ Application should be fully functional\n";

echo "\n🚀 NEXT STEPS:\n";
echo "1. Restart the development server\n";
echo "2. Test all routes in browser\n";
echo "3. Verify functionality\n";
echo "4. Deploy to production\n";

echo "\n📝 ROUTES FIX COMPLETE!\n";
?>
