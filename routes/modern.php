<?php

/**
 * Modern Routes Configuration
 * Unified routing system with modern App pipeline
 */

use App\Core\App;

// Make $app available for route registration
/** @var App $app */

// Modern route definitions with improved structure
$app->router()->group(['prefix' => 'api'], function($router) {
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
    $router->group(['middleware' => 'auth'], function($router) {
        $router->get('/user/profile', 'Api\UserController@profile');
        $router->put('/user/profile', 'Api\UserController@updateProfile');
        $router->get('/user/bookmarks', 'Api\UserController@bookmarks');
    });
});

// Modern web routes with better organization
$app->router()->group(['middleware' => 'web'], function($router) {
    // Public routes
    $router->get('/', 'HomeController@index');
    $router->get('/about', 'HomeController@about');
    $router->get('/contact', 'HomeController@contact');
    $router->post('/contact', 'HomeController@processContact');
    
    // Property routes
    $router->get('/properties', 'PropertyController@index');
    $router->get('/properties/{id}', 'PropertyController@show');
    $router->get('/properties/city/{city}', 'PropertyController@byCity');
    $router->get('/properties/featured', 'PropertyController@featured');
    
    // Blog routes
    $router->get('/blog', 'BlogController@index');
    $router->get('/blog/{slug}', 'BlogController@show');
    
    // Authentication routes
    $router->get('/login', 'AuthController@showLoginForm');
    $router->post('/login', 'AuthController@login');
    $router->post('/logout', 'AuthController@logout');
    $router->get('/register', 'AuthController@showRegistrationForm');
    $router->post('/register', 'AuthController@register');
    
    // Protected routes
    $router->group(['middleware' => 'auth'], function($router) {
        $router->get('/dashboard', 'DashboardController@index');
        $router->get('/profile', 'UserController@profile');
        $router->put('/profile', 'UserController@updateProfile');
        $router->get('/bookmarks', 'UserController@bookmarks');
        $router->post('/bookmarks/{id}', 'UserController@addBookmark');
        $router->delete('/bookmarks/{id}', 'UserController@removeBookmark');
    });
    
    // Admin routes
    $router->group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function($router) {
        $router->get('/', 'Admin\DashboardController@index');
        $router->get('/users', 'Admin\UserController@index');
        $router->get('/properties', 'Admin\PropertyController@index');
        $router->get('/leads', 'Admin\LeadController@index');
        $router->get('/reports', 'Admin\ReportController@index');
    });
    
    // Associate routes
    $router->group(['prefix' => 'associate', 'middleware' => ['auth', 'associate']], function($router) {
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