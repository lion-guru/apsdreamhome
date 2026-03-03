<?php
// Web Routes
$router->get('/', 'HomeController@index');
$router->get('/properties', 'Property\PropertyController@index');
$router->get('/properties/{id}', 'Property\PropertyController@show');
$router->get('/map', 'MapController@index');
$router->get('/ai-assistant', 'AIAssistantController@index');
$router->get('/ai-dashboard', 'AIDashboardController@index');
$router->get('/analytics', 'AnalyticsController@index');
$router->get('/whatsapp-templates', 'WhatsAppTemplateController@index');
$router->get('/mlm-dashboard', 'MLMController@dashboard');
$router->get('/monitoring', 'MonitoringController@dashboard');
$router->get('/ai-valuation', 'AIValuationController@index');
$router->get('/about', 'PageController@about');
$router->get('/contact', 'PageController@contact');
$router->get('/careers', 'CareerController@index');
$router->get('/testimonials', 'TestimonialController@index');
$router->get('/faq', 'FAQController@index');
$router->get('/team', 'PageController@team');
$router->get('/blog', 'BlogController@index');
$router->get('/blog/{slug}', 'BlogController@show');
$router->get('/blog/category/{category}', 'BlogController@category');
$router->get('/gallery', 'GalleryController@index');
$router->get('/gallery/project/{projectId}', 'GalleryController@project');
$router->get('/company/projects', 'Public\PageController@companyProjects');

// Auth routes
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@authenticate');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@store');
$router->get('/logout', 'AuthController@logout');

// Dashboard routes (protected)
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/profile', 'DashboardController@profile');
$router->post('/dashboard/profile', 'DashboardController@updateProfile');
$router->get('/dashboard/favorites', 'DashboardController@favorites');
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

// Map routes
$router->get('/map', 'MapController@index');
$router->get('/map/properties-data', 'MapController@getPropertiesData');
$router->get('/map/search-bounds', 'MapController@searchByBounds');
$router->get('/map/location-suggestions', 'MapController@getLocationSuggestions');
$router->get('/mcp_dashboard', 'MCPController@dashboard');
$router->get('/mcp_configuration_gui', 'MCPController@configuration');
$router->get('/import_mcp_config', 'MCPController@import');
?>