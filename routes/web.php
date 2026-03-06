<?php
// Web Routes - APS Dream Home

// Include router
require_once __DIR__ . '/router.php';

// Initialize router
$router = new Router();

// Property routes
$router->get('/properties', 'Property\PropertyController@index');
$router->get('/properties/{id}', 'Property\PropertyController@show');

// General routes
$router->get('/', 'HomeController@index');
$router->get('/mlm-dashboard', 'MLMController@dashboard');
$router->get('/monitoring', 'MonitoringController@dashboard');
$router->get('/ai-valuation', 'AIValuationController@index');

// General pages
$router->get('/about', 'Public\PageController@about');
$router->get('/contact', 'Public\PageController@contact');
$router->post('/contact', 'Public\PageController@submitContact');
$router->get('/careers', 'Public\PageController@careers');
$router->get('/career', 'Public\PageController@careers');
$router->get('/careers/apply', 'Public\PageController@careerApply');
$router->post('/careers/apply', 'Public\PageController@submitCareerApplication');
$router->get('/company/projects', 'Public\PageController@projects');
$router->get('/blog', 'Public\PageController@blog');
$router->get('/blog/{slug}', 'Public\PageController@blogPost');
$router->get('/faq', 'Public\PageController@faq');
$router->get('/team', 'Public\PageController@team');
$router->get('/testimonials', 'Public\PageController@testimonials');

// Blog routes
$router->get('/blog/category/{category}', 'BlogController@category');

// Gallery routes
$router->get('/gallery', 'GalleryController@index');
$router->get('/gallery/project/{projectId}', 'GalleryController@project');

// Project routes
$router->get('/projects', 'ProjectController@index');
$router->get('/projects/{id}', 'ProjectController@detail');

// Resell routes
$router->get('/resell', 'ResellController@index');

// Authentication routes
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@authenticate');
$router->get('/employee/login', 'AuthController@employeeLogin');
$router->post('/employee/login', 'AuthController@authenticateEmployee');
$router->get('/associate/login', 'AuthController@associateLogin');
$router->post('/associate/login', 'AuthController@authenticateAssociate');
$router->get('/admin/login', 'AuthController@adminLogin');
$router->post('/admin/login', 'AuthController@authenticateAdmin');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@handleRegister');
$router->get('/associate/register', 'AuthController@associateRegister');
$router->post('/associate/register', 'AuthController@handleAssociateRegister');
$router->get('/logout', 'AuthController@logout');

// Agent authentication
$router->get('/agent/login', 'AgentController@login');
$router->post('/agent/login', 'AgentController@authenticate');

// Dashboard routes (protected)
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/profile', 'DashboardController@profile');
$router->post('/dashboard/profile', 'DashboardController@updateProfile');
$router->get('/dashboard/favorites', 'DashboardController@favorites');
$router->get('/associate/dashboard', 'DashboardController@associate');
$router->get('/admin/dashboard', 'AdminController@dashboard');

// Agent routes
$router->get('/agents/dashboard', 'AgentController@dashboard');
$router->post('/dashboard/favorites/add', 'DashboardController@addFavorite');
$router->post('/dashboard/favorites/remove', 'DashboardController@removeFavorite');
$router->get('/dashboard/inquiries', 'DashboardController@inquiries');
$router->post('/dashboard/inquiries/submit', 'DashboardController@submitInquiry');

// Admin routes
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/properties', 'Admin\PropertyController@index');
$router->get('/admin/properties/create', 'Admin\PropertyController@create');
$router->post('/admin/properties/store', 'Admin\PropertyController@store');
$router->get('/admin/properties/edit/{id}', 'Admin\PropertyController@edit');
$router->post('/admin/properties/update/{id}', 'Admin\PropertyController@update');
$router->get('/admin/properties/delete/{id}', 'Admin\PropertyController@destroy');
$router->post('/admin/properties/toggle-featured/{id}', 'Admin\PropertyController@toggleFeatured');
$router->get('/admin/users', 'Admin\UserController@index');
$router->get('/admin/dashboard', 'AdminController@dashboard');

// Customer routes
$router->get('/customer/dashboard', 'CustomerController@dashboard');
$router->get('/customers/dashboard', 'CustomerController@dashboard');

// Map routes
$router->get('/map', 'MapController@index');
$router->get('/map/properties-data', 'MapController@getPropertiesData');
$router->get('/map/search-bounds', 'MapController@searchByBounds');
$router->get('/map/location-suggestions', 'MapController@getLocationSuggestions');

// MCP routes
$router->get('/mcp_dashboard', 'MCPController@dashboard');
$router->get('/mcp_configuration_gui', 'MCPController@configuration');
$router->get('/import_mcp_config', 'MCPController@import');
?>
