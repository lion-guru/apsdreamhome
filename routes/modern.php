<?php

/**
 * Modern Routes Configuration
 * Unified routing system with modern App pipeline
 */

use App\Core\App;

// Make $app available for route registration

/** @var App $app */

// Modern route definitions with improved structure
$app->router()->group(['prefix' => 'api'], function ($router) {
    // API routes with proper REST structure
    $router->get('/properties', 'Api\PropertyController@index');
    $router->get('/properties/{id}', 'Api\PropertyController@show');
    $router->post('/properties', 'Api\PropertyController@store');
    $router->put('/properties/{id}', 'Api\PropertyController@update');
    $router->delete('/properties/{id}', 'Api\PropertyController@destroy');

    // Authentication routes
    $router->post('/auth/login', 'Api\AuthController@login');
    $router->post('/auth/register', 'Api\AuthController@register');
    $router->post('/auth/logout', 'Api\AuthController@logout');

    // User routes (protected)
    $router->group(['middleware' => 'auth'], function ($router) {
        $router->get('/user/profile', 'Api\UserController@profile');
        $router->put('/user/profile', 'Api\UserController@updateProfile');
        $router->get('/user/bookmarks', 'Api\UserController@bookmarks');
    });
});

// Modern web routes with better organization
$app->router()->group(['middleware' => 'web'], function ($router) {
    // Public routes
    $router->get('/', 'Public\PageController@index');
    $router->get('/about', 'Public\PageController@about');
    $router->get('/contact', 'Public\PageController@contact');
    $router->post('/contact', 'Public\PageController@processContact');

    // Property routes
    $router->get('/properties', 'Property\PropertyController@index');
    $router->get('/properties/{id}', 'Property\PropertyController@show');
    $router->get('/properties/city/{city}', 'Property\PropertyController@byCity');
    $router->get('/properties/featured', 'Property\PropertyController@featured');

    // Blog routes
    $router->get('/blog', 'Blog\BlogController@index');
    $router->get('/blog/{slug}', 'Blog\BlogController@show');

    // Authentication routes
    $router->get('/login', 'Public\AuthController@showLoginForm');
    $router->post('/login', 'Public\AuthController@login');
    $router->post('/logout', 'Public\AuthController@logout');
    $router->get('/register', 'Public\AuthController@showRegistrationForm');
    $router->post('/register', 'Public\AuthController@register');

    // Protected routes
    $router->group(['middleware' => 'auth'], function ($router) {
        $router->get('/dashboard', 'User\DashboardController@index');
        $router->get('/profile', 'User\UserController@profile');
        $router->put('/profile', 'User\UserController@updateProfile');
        $router->get('/bookmarks', 'User\UserController@bookmarks');
        $router->post('/bookmarks/{id}', 'User\UserController@addBookmark');
        $router->delete('/bookmarks/{id}', 'User\UserController@removeBookmark');
    });

    // Admin Dashboards
    $router->group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function ($router) {
        $router->get('/', 'Admin\AdminController@index');

        // Lead Management
        $router->get('/leads', 'Admin\LeadController@index');
        $router->get('/leads/create', 'Admin\LeadController@create');
        $router->post('/leads/store', 'Admin\LeadController@store');
        $router->get('/leads/edit/{id}', 'Admin\LeadController@edit');
        $router->post('/leads/update/{id}', 'Admin\LeadController@update');
        $router->get('/leads/delete/{id}', 'Admin\LeadController@delete');

        // Property Management
        $router->get('/properties', 'Admin\PropertyController@index');
        $router->get('/properties/create', 'Admin\PropertyController@create');
        $router->post('/properties/store', 'Admin\PropertyController@store');
        $router->get('/properties/edit/{id}', 'Admin\PropertyController@edit');
        $router->post('/properties/update/{id}', 'Admin\PropertyController@update');
        $router->get('/properties/delete/{id}', 'Admin\PropertyController@delete');

        // User Management
        $router->get('/users', 'Admin\UserController@index');
        $router->get('/users/create', 'Admin\UserController@create');
        $router->post('/users/store', 'Admin\UserController@store');
        $router->get('/users/edit/{id}', 'Admin\UserController@edit');
        $router->post('/users/update/{id}', 'Admin\UserController@update');
        $router->post('/users/delete/{id}', 'Admin\UserController@destroy');

        // Project Management
        $router->get('/projects', 'Admin\ProjectController@index');
        $router->get('/projects/create', 'Admin\ProjectController@create');
        $router->post('/projects/store', 'Admin\ProjectController@store');
        $router->get('/projects/edit/{id}', 'Admin\ProjectController@edit');
        $router->post('/projects/update/{id}', 'Admin\ProjectController@update');
        $router->post('/projects/delete/{id}', 'Admin\ProjectController@delete');

        // Static Pages
        $router->get('/about', 'Admin\AdminController@about');
        $router->get('/contact', 'Admin\AdminController@contact');

        // CRM Management
        $router->get('/crm/dashboard', 'Admin\AdminController@crmDashboard');
        $router->get('/customers', 'Admin\CustomerController@index');
        $router->get('/customers/create', 'Admin\CustomerController@create');
        $router->post('/customers/store', 'Admin\CustomerController@store');
        $router->get('/customers/edit/{id}', 'Admin\CustomerController@edit');
        $router->post('/customers/update/{id}', 'Admin\CustomerController@update');
        $router->post('/customers/delete/{id}', 'Admin\CustomerController@destroy');

        $router->get('/bookings', 'Admin\BookingController@index');
        $router->get('/bookings/create', 'Admin\BookingController@create');
        $router->post('/bookings/store', 'Admin\BookingController@store');
        $router->get('/bookings/edit/{id}', 'Admin\BookingController@edit');
        $router->post('/bookings/update/{id}', 'Admin\BookingController@update');
        $router->post('/bookings/delete/{id}', 'Admin\BookingController@destroy');

        $router->get('/employees', 'Admin\EmployeeController@index');
        $router->get('/employees/create', 'Admin\EmployeeController@create');
        $router->post('/employees/store', 'Admin\EmployeeController@store');
        $router->get('/employees/edit/{id}', 'Admin\EmployeeController@edit');
        $router->post('/employees/update/{id}', 'Admin\EmployeeController@update');
        $router->post('/employees/delete/{id}', 'Admin\EmployeeController@destroy');

        $router->get('/reports', 'Analytics\ReportController@index');
        $router->get('/settings', 'Admin\AdminController@settings');

        // AI Hub Routes
        $router->get('/ai/hub', 'Admin\AdminController@aiHub');
        $router->get('/ai/agent', 'Admin\AdminController@aiAgentDashboard');
        $router->get('/ai/lead-scoring', 'Admin\AdminController@aiLeadScoring');

        // Superadmin Routes
        $router->get('/superadmin', 'Admin\AdminController@superadminDashboard');
        $router->get('/settings/whatsapp', 'Admin\AdminController@whatsappSettings');
        $router->get('/settings/site', 'Admin\AdminController@siteSettings');
        $router->get('/settings/api', 'Admin\AdminController@apiSettings');
        $router->get('/settings/backup', 'Admin\AdminController@backupSettings');
        $router->get('/settings/logs', 'Admin\AdminController@auditLogs');

        // Kisaan (Land) Management
        $router->get('/kisaan/list', 'Admin\AdminController@kisaanList');
        $router->get('/kisaan/add', 'Admin\AdminController@kisaanAdd');

        // MLM Management Routes (Clean structure)
        $router->get('/mlm/reports', 'Admin\AdminController@mlmReports');
        $router->get('/mlm/settings', 'Admin\AdminController@mlmSettings');
        $router->get('/mlm/payouts', 'Admin\AdminController@mlmPayouts');
        $router->get('/mlm/commissions', 'Admin\AdminController@mlmCommissions');

        // Associates Management
        $router->get('/associates', 'Admin\AdminController@associates');

        // EMI Routes
        $router->get('/emi', 'Admin\EMIController@index');
        $router->get('/emi/{id}', 'Admin\EMIController@show');
        $router->post('/emi', 'Admin\EMIController@store');
        $router->post('/emi/pay', 'Admin\EMIController@pay');

        // Visit Management Routes
        $router->get('/visits', 'Admin\VisitController@index');
        $router->get('/visits/create', 'Admin\VisitController@create');
        $router->post('/visits', 'Admin\VisitController@store');
        $router->post('/visits/{id}/status', 'Admin\VisitController@updateStatus');

        // Legacy MLM routes (preserved for compatibility)
        $router->get('/mlm-analytics', 'Admin\AnalyticsController@index');
        $router->get('/mlm-analytics/data', 'Admin\AnalyticsController@data');
        $router->get('/mlm-analytics/ledger', 'Admin\AnalyticsController@ledger');
        $router->get('/mlm-analytics/export', 'Admin\AnalyticsController@export');

        $router->get('/mlm-network', 'Admin\NetworkController@index');
        $router->get('/mlm-network/search', 'Admin\NetworkController@searchUsers');
        $router->get('/mlm-network/tree', 'Admin\NetworkController@networkTree');
        $router->get('/mlm-network/agreements', 'Admin\NetworkController@listAgreements');
        $router->post('/mlm-network/agreements/create', 'Admin\NetworkController@createAgreement');
        $router->post('/mlm-network/agreements/update', 'Admin\NetworkController@updateAgreement');
        $router->post('/mlm-network/agreements/delete', 'Admin\NetworkController@deleteAgreement');
        $router->post('/mlm-network/rebuild', 'Admin\NetworkController@rebuildNetwork');

        $router->get('/mlm-payouts', 'Admin\PayoutController@index');
        $router->get('/mlm-payouts/list', 'Admin\PayoutController@list');
        $router->get('/mlm-payouts/items', 'Admin\PayoutController@items');
        $router->get('/mlm-payouts/export', 'Admin\PayoutController@export');
        $router->post('/mlm-payouts/create', 'Admin\PayoutController@create');
        $router->post('/mlm-payouts/approve', 'Admin\PayoutController@approve');
        $router->post('/mlm-payouts/disburse', 'Admin\PayoutController@disburse');
        $router->post('/mlm-payouts/cancel', 'Admin\PayoutController@cancel');

        $router->get('/mlm-engagement', 'Admin\EngagementController@index');
        $router->get('/mlm-engagement/metrics', 'Admin\EngagementController@metrics');
        $router->get('/mlm-engagement/leaderboard', 'Admin\EngagementController@leaderboard');
        $router->get('/mlm-engagement/goals', 'Admin\EngagementController@goals');
        $router->get('/mlm-engagement/goal-details', 'Admin\EngagementController@goalDetails');
        $router->get('/mlm-engagement/notifications', 'Admin\EngagementController@notificationFeed');
        $router->post('/mlm-engagement/goals/create', 'Admin\EngagementController@createGoal');
        $router->post('/mlm-engagement/goals/update', 'Admin\EngagementController@updateGoal');
        $router->post('/mlm-engagement/goals/progress', 'Admin\EngagementController@recordGoalProgress');
        $router->post('/mlm-engagement/goals/status', 'Admin\EngagementController@updateGoalStatus');
        $router->post('/mlm-engagement/notifications/mark-read', 'Admin\EngagementController@markNotificationRead');
        $router->post('/mlm-engagement/notifications/mark-all-read', 'Admin\EngagementController@markAllNotificationsRead');
    });

    // Associate routes
    $router->group(['prefix' => 'associate', 'middleware' => ['auth', 'associate']], function ($router) {
        $router->get('/', 'Associate\DashboardController@index');
        $router->get('/leads', 'Associate\LeadController@index');
        $router->get('/commissions', 'Associate\CommissionController@index');
        $router->get('/referrals', 'Associate\ReferralController@index');
    });
});

// Legacy route compatibility layer
// This ensures that any routes defined in the legacy format still work
if (file_exists(__DIR__ . '/web.php')) {
    // Legacy routes will be loaded after modern routes
    // and will be handled by the fallback mechanism in the modern router
}
