<?php
// Web Routes - APS Dream Home

// Include router
require_once __DIR__ . '/router.php';

// Initialize router
$router = new Router();

// Debug: Log routing setup
error_log("ROUTER SETUP: Starting route registration");

// Main routes - Public Pages
$router->get('/', 'Front\\PageController@home');
$router->get('/about', 'Front\\PageController@about');
$router->get('/contact', 'Front\\PageController@contact');
$router->post('/contact', 'Front\\PageController@submitContact');

// Customer Authentication
$router->get('/register', 'Auth\\CustomerAuthController@register');
$router->post('/register', 'Auth\\CustomerAuthController@handleRegister');
$router->get('/login', 'Auth\\CustomerAuthController@login');
$router->post('/login', 'Auth\\CustomerAuthController@authenticate');

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

// Dashboard Routes - Role-based
$router->get('/admin/dashboard/agent', 'App\Http\Controllers\Admin\AgentDashboardController@index');
$router->get('/admin/dashboard/builder', 'App\Http\Controllers\Admin\BuilderDashboardController@index');
$router->get('/admin/dashboard/ceo', 'App\Http\Controllers\Admin\CEODashboardController@index');
$router->get('/admin/dashboard/cfo', 'App\Http\Controllers\Admin\CFODashboardController@index');
$router->get('/admin/dashboard/cm', 'App\Http\Controllers\Admin\CMDashboardController@index');
$router->get('/admin/dashboard/coo', 'App\Http\Controllers\Admin\COODashboardController@index');
$router->get('/admin/dashboard/cto', 'App\Http\Controllers\Admin\CTODashboardController@index');
$router->get('/admin/dashboard/director', 'App\Http\Controllers\Admin\DirectorDashboardController@index');
$router->get('/admin/dashboard/finance', 'App\Http\Controllers\Admin\FinanceDashboardController@index');
$router->get('/admin/dashboard/hr', 'App\Http\Controllers\Admin\HRDashboardController@index');
$router->get('/admin/dashboard/it', 'App\Http\Controllers\Admin\ITDashboardController@index');
$router->get('/admin/dashboard/marketing', 'App\Http\Controllers\Admin\MarketingDashboardController@index');
$router->get('/admin/dashboard/operations', 'App\Http\Controllers\Admin\OperationsDashboardController@index');
$router->get('/admin/dashboard/sales', 'App\Http\Controllers\Admin\SalesDashboardController@index');
$router->get('/admin/dashboard/superadmin', 'App\Http\Controllers\Admin\SuperAdminDashboardController@index');

// Dashboard AJAX Routes
$router->get('/api/dashboard/agent/performance', 'App\Http\Controllers\Admin\AgentDashboardController@getPerformanceData');
$router->get('/api/dashboard/agent/network', 'App\Http\Controllers\Admin\AgentDashboardController@getNetworkTree');
$router->get('/api/dashboard/ceo/analytics', 'App\Http\Controllers\Admin\CEODashboardController@getRevenueAnalytics');
$router->get('/api/dashboard/ceo/team', 'App\Http\Controllers\Admin\CEODashboardController@getTeamPerformance');
$router->get('/api/dashboard/cfo/financial', 'App\Http\Controllers\Admin\CFODashboardController@getFinancialAnalytics');
$router->get('/api/dashboard/cfo/expenses', 'App\Http\Controllers\Admin\CFODashboardController@getExpenseBreakdown');
$router->get('/api/dashboard/builder/analytics', 'App\Http\Controllers\Admin\BuilderDashboardController@getConstructionAnalytics');
$router->get('/api/dashboard/builder/materials', 'App\Http\Controllers\Admin\BuilderDashboardController@getMaterialStatus');
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

// Admin Site Management Routes
$router->get('/admin/sites', 'App\Http\Controllers\Admin\SiteController@index');
$router->get('/admin/sites/create', 'App\Http\Controllers\Admin\SiteController@create');
$router->post('/admin/sites', 'App\Http\Controllers\Admin\SiteController@store');
$router->get('/admin/sites/{id}', 'App\Http\Controllers\Admin\SiteController@show');
$router->get('/admin/sites/{id}/edit', 'App\Http\Controllers\Admin\SiteController@edit');
$router->post('/admin/sites/{id}/update', 'App\Http\Controllers\Admin\SiteController@update');
$router->post('/admin/sites/{id}/destroy', 'App\Http\Controllers\Admin\SiteController@destroy');

// Admin Plot Management Routes
$router->get('/admin/plots', 'App\Http\Controllers\Admin\PlotManagementController@index');
$router->get('/admin/plots/create', 'App\Http\Controllers\Admin\PlotManagementController@create');
$router->post('/admin/plots', 'App\Http\Controllers\Admin\PlotManagementController@store');
$router->get('/admin/plots/{id}', 'App\Http\Controllers\Admin\PlotManagementController@show');
$router->get('/admin/plots/{id}/edit', 'App\Http\Controllers\Admin\PlotManagementController@edit');
$router->post('/admin/plots/{id}/update', 'App\Http\Controllers\Admin\PlotManagementController@update');
$router->post('/admin/plots/{id}/destroy', 'App\Http\Controllers\Admin\PlotManagementController@destroy');
$router->get('/admin/plots/check-availability', 'App\Http\Controllers\Admin\PlotManagementController@checkAvailability');
$router->post('/admin/plots/{id}/update-status', 'App\Http\Controllers\Admin\PlotManagementController@updateStatus');

// Admin Property Management Routes
$router->get('/admin/properties', 'App\Http\Controllers\Admin\PropertyManagementController@index');
$router->get('/admin/properties/create', 'App\Http\Controllers\Admin\PropertyManagementController@create');
$router->post('/admin/properties', 'App\Http\Controllers\Admin\PropertyManagementController@store');
$router->get('/admin/properties/{id}', 'App\Http\Controllers\Admin\PropertyManagementController@show');
$router->get('/admin/properties/{id}/edit', 'App\Http\Controllers\Admin\PropertyManagementController@edit');
$router->post('/admin/properties/{id}/update', 'App\Http\Controllers\Admin\PropertyManagementController@update');
$router->post('/admin/properties/{id}/destroy', 'App\Http\Controllers\Admin\PropertyManagementController@destroy');
$router->get('/admin/properties/check-availability', 'App\Http\Controllers\Admin\PropertyManagementController@checkAvailability');

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
