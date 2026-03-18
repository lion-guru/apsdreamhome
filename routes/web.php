<?php
// Web Routes - APS Dream Home

// Include router
require_once __DIR__ . '/router.php';

// Initialize router
$router = new Router();

// Main routes - Public Pages
$router->get('/', 'Front\\PageController@home');
$router->get('/about', 'Front\\PageController@about');
$router->get('/contact', 'Front\\PageController@contact');
$router->post('/contact', 'Front\\PageController@submitContact');

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
$router->get('/team/genealogy', 'MLMController@genealogy');
$router->get('/api/mlm/tree', 'MLMController@getNetworkTree');

// Property routes
$router->get('/properties', 'Property\PropertyController@index');
$router->get('/properties/{id}', 'Property\PropertyController@show');

// Project routes
$router->get('/projects', 'Admin\ProjectController@index');
$router->get('/projects/{id}', 'Admin\ProjectController@detail');
$router->get('/projects/suyoday-colony', 'Front\PageController@suyodayColony');
$router->get('/projects/raghunat-nagri', 'Front\PageController@raghunatNagri');
$router->get('/projects/braj-radha-nagri', 'Front\PageController@brajRadhaNagri');
$router->get('/projects/budh-bihar-colony', 'Front\PageController@budhBiharColony');
$router->get('/projects/awadhpuri', 'Front\PageController@awadhpuri');

// Admin routes
$router->get('/admin', 'App\Http\Controllers\Admin\AdminController@dashboard');
$router->get('/admin/dashboard', 'App\Http\Controllers\Admin\AdminController@dashboard');
$router->get('/admin/enterprise_dashboard', 'App\Http\Controllers\Admin\AdminController@enterpriseDashboard');
$router->get('/admin/login', 'App\Http\Controllers\Auth\AdminAuthController@adminLogin');
$router->post('/admin/login', 'App\Http\Controllers\Auth\AdminAuthController@authenticateAdmin');
$router->get('/admin/logout', 'App\Http\Controllers\Auth\AdminAuthController@logout');
$router->get('/admin/properties', 'App\Http\Controllers\Admin\AdminController@properties');
$router->get('/admin/users', 'App\Http\Controllers\Admin\AdminController@users');

// Admin Booking CRUD Routes
$router->get('/admin/bookings', 'App\Http\Controllers\Admin\BookingController@index');
$router->get('/admin/bookings/create', 'App\Http\Controllers\Admin\BookingController@create');
$router->post('/admin/bookings', 'App\Http\Controllers\Admin\BookingController@store');
$router->get('/admin/bookings/{id}', 'App\Http\Controllers\Admin\BookingController@show');
$router->get('/admin/bookings/{id}/edit', 'App\Http\Controllers\Admin\BookingController@edit');
$router->post('/admin/bookings/{id}/update', 'App\Http\Controllers\Admin\BookingController@update');
$router->post('/admin/bookings/{id}/destroy', 'App\Http\Controllers\Admin\BookingController@destroy');
$router->post('/admin/bookings/{id}/payment', 'App\Http\Controllers\Admin\BookingController@processPayment');

// Additional routes
$router->get('/career', 'Front\PageController@careers');
$router->get('/careers/apply', 'Front\PageController@careerApply');
$router->post('/careers/apply', 'Front\PageController@submitCareerApplication');
$router->get('/company/projects', 'Front\PageController@projects');
$router->get('/blog', 'Front\PageController@blog');
$router->get('/blog/{slug}', 'Front\PageController@blogPost');
$router->get('/faq', 'Front\PageController@faq');
$router->get('/team', 'Front\PageController@team');
$router->get('/testimonials', 'Front\PageController@testimonials');
$router->get('/gallery', 'GalleryController@index');
$router->get('/gallery/project/{projectId}', 'GalleryController@project');
$router->get('/resell', 'ResellController@index');

// Map routes
$router->get('/map', 'MapController@index');
$router->get('/map/properties-data', 'MapController@getPropertiesData');
$router->get('/map/search-bounds', 'MapController@searchByBounds');
$router->get('/map/location-suggestions', 'MapController@getLocationSuggestions');

// API Routes
require_once __DIR__ . '/api.php';
