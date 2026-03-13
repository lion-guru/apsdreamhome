<?php
// Web Routes - APS Dream Home

// Include router
require_once __DIR__ . '/router.php';

// Initialize router
$router = new Router();

// Main routes - Public Pages
$router->get('/', 'Public\\PageController@home');
$router->get('/about', 'Public\\PageController@about');
$router->get('/contact', 'Public\\PageController@contact');
$router->post('/contact', 'Public\\PageController@submitContact');

// Customer Authentication
$router->get('/register', 'Auth\CustomerAuthController@register');
$router->post('/register', 'Auth\CustomerAuthController@handleRegister');
$router->get('/login', 'Auth\CustomerAuthController@login');
$router->post('/login', 'Auth\CustomerAuthController@authenticate');

// Agent Authentication
$router->get('/agent/register', 'Auth\AgentAuthController@register');
$router->post('/agent/register', 'Auth\AgentAuthController@handleRegister');
$router->get('/agent/login', 'AgentController@login');
$router->post('/agent/login', 'AgentController@authenticate');

// Associate Authentication
$router->get('/associate/register', 'Auth\AssociateAuthController@associateRegister');
$router->post('/associate/register', 'Auth\AssociateAuthController@handleAssociateRegister');
$router->get('/associate/login', 'Auth\AssociateAuthController@associateLogin');
$router->post('/associate/login', 'Auth\AssociateAuthController@authenticateAssociate');

// Dashboard routes (protected)
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/customer', 'DashboardController@customer');
$router->get('/dashboard/profile', 'DashboardController@profile');
$router->post('/dashboard/profile', 'DashboardController@updateProfile');
$router->get('/dashboard/favorites', 'DashboardController@favorites');
$router->post('/dashboard/favorites/add', 'DashboardController@addFavorite');
$router->post('/dashboard/favorites/remove', 'DashboardController@removeFavorite');
$router->get('/dashboard/inquiries', 'DashboardController@inquiries');
$router->post('/dashboard/inquiries/submit', 'DashboardController@submitInquiry');
$router->get('/associate/dashboard', 'DashboardController@associate');

// Property routes
$router->get('/properties', 'Property\PropertyController@index');
$router->get('/properties/{id}', 'Property\PropertyController@show');

// Project routes
$router->get('/projects', 'ProjectController@index');
$router->get('/projects/{id}', 'ProjectController@detail');
$router->get('/projects/suyoday-colony', 'Public\PageController@suyodayColony');
$router->get('/projects/raghunat-nagri', 'Public\PageController@raghunatNagri');
$router->get('/projects/braj-radha-nagri', 'Public\PageController@brajRadhaNagri');
$router->get('/projects/budh-bihar-colony', 'Public\PageController@budhBiharColony');
$router->get('/projects/awadhpuri', 'Public\PageController@awadhpuri');

// Admin routes
$router->get('/admin', 'App\Http\Controllers\AdminController@dashboard');
$router->get('/admin/dashboard', 'App\Http\Controllers\AdminController@dashboard');
$router->get('/admin/login', 'App\Http\Controllers\Auth\AdminAuthController@adminLogin');
$router->post('/admin/login', 'App\Http\Controllers\Auth\AdminAuthController@authenticateAdmin');
$router->get('/logout', 'App\Http\Controllers\Auth\AdminAuthController@logout');

// Additional routes
$router->get('/career', 'Public\PageController@careers');
$router->get('/careers/apply', 'Public\PageController@careerApply');
$router->post('/careers/apply', 'Public\PageController@submitCareerApplication');
$router->get('/company/projects', 'Public\PageController@projects');
$router->get('/blog', 'Public\PageController@blog');
$router->get('/blog/{slug}', 'Public\PageController@blogPost');
$router->get('/faq', 'Public\PageController@faq');
$router->get('/team', 'Public\PageController@team');
$router->get('/testimonials', 'Public\PageController@testimonials');
$router->get('/gallery', 'GalleryController@index');
$router->get('/gallery/project/{projectId}', 'GalleryController@project');
$router->get('/resell', 'ResellController@index');
$router->get('/map', 'MapController@index');
$router->get('/map/properties-data', 'MapController@getPropertiesData');
$router->get('/map/search-bounds', 'MapController@searchByBounds');
$router->get('/map/location-suggestions', 'MapController@getLocationSuggestions');
