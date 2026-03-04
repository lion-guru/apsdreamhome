<?php

/**
 * Web Routes for APS Dream Home
 * 
 * Define all application routes here
 */

// Home page routes
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');

// Page routes
$router->get('/contact', 'PageController@contact');
$router->get('/about', 'PageController@about');
$router->get('/properties', 'PageController@properties');

// Admin routes
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/dashboard', 'AdminController@dashboard');

// API routes
$router->get('/api/properties', 'ApiController@properties');
$router->post('/api/contact', 'ApiController@contact');

// Fallback route for 404
$router->get('/404', 'ErrorController@notFound');

// Catch-all route for SPA-style routing
$router->get('/{path}', 'HomeController@index')->where('path', '.*');
?>
