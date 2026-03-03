<?php
// Web Routes
$router->get('/', 'HomeController@index');
$router->get('/properties', 'PropertyController@index');
$router->get('/properties/{id}', 'PropertyController@show');
$router->get('/about', 'PageController@about');
$router->get('/contact', 'PageController@contact');
$router->get('/careers', 'CareerController@index');
$router->get('/testimonials', 'TestimonialController@index');
$router->get('/faq', 'FAQController@index');
$router->get('/team', 'PageController@team');

// Auth routes
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@authenticate');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@store');
$router->get('/logout', 'AuthController@logout');

// Admin routes
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/properties', 'Admin\PropertyController@index');
$router->get('/admin/users', 'Admin\UserController@index');
$router->get('/admin/dashboard', 'AdminController@dashboard');

// MCP routes
$router->get('/mcp_dashboard', 'MCPController@dashboard');
$router->get('/mcp_configuration_gui', 'MCPController@configuration');
$router->get('/import_mcp_config', 'MCPController@import');
?>