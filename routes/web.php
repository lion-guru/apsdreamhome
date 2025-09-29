<?php

/** @var \App\Core\Routing\Router $router */
$router = app()->router;

// Home routes
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');

// Properties routes
$router->get('/properties', 'PropertyController@index');
$router->get('/properties/{id}', 'PropertyController@show');
$router->get('/properties/create', 'PropertyController@create')->middleware('auth');
$router->post('/properties', 'PropertyController@store')->middleware('auth');
$router->get('/properties/{id}/edit', 'PropertyController@edit')->middleware('auth');
$router->put('/properties/{id}', 'PropertyController@update')->middleware('auth');
$router->delete('/properties/{id}', 'PropertyController@destroy')->middleware('auth');
$router->get('/properties/featured', 'PropertyController@featured');

// Auth routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@forgotPassword');
$router->post('/forgot-password', 'AuthController@processForgotPassword');
$router->get('/reset-password/{token}', 'AuthController@resetPassword');
$router->post('/reset-password', 'AuthController@processResetPassword');

// User Dashboard routes
$router->get('/dashboard', 'UserController@dashboard')->middleware('auth');
$router->get('/profile', 'UserController@profile')->middleware('auth');
$router->put('/profile', 'UserController@updateProfile')->middleware('auth');
$router->get('/my-properties', 'UserController@myProperties')->middleware('auth');
$router->get('/bookmarks', 'UserController@bookmarks')->middleware('auth');
$router->get('/payment-history', 'UserController@paymentHistory')->middleware('auth');

// Admin routes
$router->group(['prefix' => 'admin', 'middleware' => 'admin'], function($router) {
    $router->get('/', 'AdminController@dashboard');
    $router->get('/users', 'AdminController@users');
    $router->get('/properties', 'AdminController@properties');
    $router->get('/leads', 'AdminController@leads');
    $router->get('/reports', 'AdminController@reports');
    $router->get('/settings', 'AdminController@settings');
    $router->post('/settings', 'AdminController@updateSettings');
    $router->get('/database', 'AdminController@database');
    $router->post('/backup', 'AdminController@createBackup');
    $router->get('/logs', 'AdminController@logs');
    $router->post('/clear-cache', 'AdminController@clearCache');
    $router->get('/export/{type}', 'AdminController@export');
});

// Lead/CRM routes
$router->get('/leads', 'LeadController@index')->middleware('auth');
$router->get('/leads/{id}', 'LeadController@show')->middleware('auth');
$router->get('/leads/create', 'LeadController@create')->middleware('auth');
$router->post('/leads', 'LeadController@store')->middleware('auth');
$router->get('/leads/{id}/edit', 'LeadController@edit')->middleware('auth');
$router->put('/leads/{id}', 'LeadController@update')->middleware('auth');
$router->post('/leads/{id}/activity', 'LeadController@addActivity')->middleware('auth');
$router->post('/leads/{id}/note', 'LeadController@addNote')->middleware('auth');
$router->post('/leads/{id}/assign', 'LeadController@assign')->middleware('auth');
$router->post('/leads/{id}/convert', 'LeadController@convert')->middleware('auth');
$router->get('/leads/reports', 'LeadController@reports')->middleware('auth');

// Static pages
$router->get('/services', 'PageController@services');
$router->get('/team', 'PageController@team');
$router->get('/careers', 'PageController@careers');
$router->get('/gallery', 'PageController@gallery');
$router->get('/testimonials', 'PageController@testimonials');
$router->get('/blog', 'PageController@blog');
$router->get('/blog/{slug}', 'PageController@blogShow');
$router->get('/faq', 'PageController@faq');
$router->get('/sitemap', 'PageController@sitemap');
$router->get('/privacy', 'PageController@privacy');
$router->get('/terms', 'PageController@terms');

// Form submissions
$router->post('/contact', 'PageController@submitContact');
$router->post('/newsletter', 'PageController@subscribeNewsletter');

// Payment routes
$router->get('/payment/{id}', 'PaymentController@show')->middleware('auth');
$router->post('/payment/{id}', 'PaymentController@process')->middleware('auth');
$router->get('/payment/success/{id}', 'PaymentController@success')->middleware('auth');
$router->get('/payment/cancel/{id}', 'PaymentController@cancel')->middleware('auth');

// API routes (for AJAX calls)
$router->group(['prefix' => 'api'], function($router) {
    $router->get('/properties/search', 'PropertyController@search');
    $router->get('/properties/{id}/images', 'PropertyController@getImages');
    $router->post('/leads/{id}/follow-up', 'LeadController@followUp')->middleware('auth');
    $router->get('/dashboard/stats', 'UserController@getStats')->middleware('auth');
});
