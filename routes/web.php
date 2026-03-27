<?php
/**
 * Web Routes - APS Dream Home (CLEAN VERSION)
 * All routes organized and deduplicated
 * NOTE: $router is created in public/index.php
 */

error_log("ROUTER SETUP: Starting route registration");

// ============================================
// PUBLIC FRONTEND PAGES
// ============================================
$router->get('/', 'Front\\PageController@home');
$router->get('/about', 'Front\\PageController@about');
$router->get('/contact', 'Front\\PageController@contact');
$router->get('/privacy', 'Front\\PageController@privacy');
$router->get('/services', 'Front\\PageController@services');
$router->get('/faqs', 'Front\\PageController@faqs');
$router->get('/team', 'Front\\PageController@team');
$router->get('/testimonials', 'Front\\PageController@testimonials');
$router->get('/support', 'Front\\PageController@support');
$router->get('/downloads', 'Front\\PageController@downloads');
$router->get('/sitemap', 'Front\\PageController@sitemap');

// Legal Pages
$router->get('/legal/terms-conditions', 'Front\\PageController@legalTermsConditions');
$router->get('/legal/services', 'Front\\PageController@legalServices');
$router->get('/legal/documents', 'Front\\PageController@legalDocuments');

// ============================================
// USER AUTHENTICATION
// ============================================
// Customer Auth
$router->get('/register', 'Auth\\CustomerAuthController@register');
$router->post('/register', 'Auth\\CustomerAuthController@handleRegister');
$router->get('/login', 'Auth\\CustomerAuthController@login');
$router->post('/login', 'Auth\\CustomerAuthController@authenticate');

// Agent Auth
$router->get('/agent/register', 'Auth\\AgentAuthController@register');
$router->post('/agent/register', 'Auth\\AgentAuthController@handleRegister');
$router->get('/agent/login', 'AgentController@login');
$router->post('/agent/login', 'AgentController@authenticate');

// Associate Auth
$router->get('/associate/register', 'Auth\\AssociateAuthController@associateRegister');
$router->post('/associate/register', 'Auth\\AssociateAuthController@handleAssociateRegister');
$router->get('/associate/login', 'Auth\\AssociateAuthController@associateLogin');
$router->post('/associate/login', 'Auth\\AssociateAuthController@authenticateAssociate');

// ============================================
// DASHBOARDS
// ============================================
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/customer', 'DashboardController@customer');
$router->get('/dashboard/profile', 'DashboardController@profile');
$router->post('/dashboard/profile', 'DashboardController@updateProfile');
$router->get('/dashboard/favorites', 'DashboardController@favorites');
$router->post('/dashboard/favorites/add', 'DashboardController@addFavorite');
$router->post('/dashboard/favorites/remove', 'DashboardController@removeFavorite');
$router->get('/dashboard/inquiries', 'DashboardController@inquiries');
$router->post('/dashboard/inquiries/submit', 'DashboardController@submitInquiry');

// Associate Dashboard
$router->get('/associate/dashboard', 'DashboardController@associate');
$router->get('/team/genealogy', 'Admin\\NetworkController@genealogy');

// Customer Dashboards
$router->get('/customer/dashboard', 'CustomerController@dashboard');
$router->get('/customers/dashboard', 'CustomerController@dashboard');

// ============================================
// PROPERTIES & PROJECTS
// ============================================
$router->get('/properties', 'Property\\PropertyController@index');
$router->get('/properties/{id}', 'Property\\PropertyController@show');
$router->get('/properties/submit', 'Property\\PropertyController@submit');
$router->get('/properties/list', 'Property\\PropertyController@list');
$router->get('/properties/edit', 'Property\\PropertyController@edit');
$router->get('/properties/book-plot', 'Property\\PropertyController@bookPlot');
$router->get('/properties/book', 'Property\\PropertyController@book');

// Projects
$router->get('/projects', 'Admin\\ProjectController@index');
$router->get('/projects/{id}', 'Admin\\ProjectController@detail');
$router->get('/projects/suyoday-colony', 'Front\\PageController@suyodayColony');
$router->get('/projects/raghunat-nagri', 'Front\\PageController@raghunatNagri');
$router->get('/projects/braj-radha-nagri', 'Front\\PageController@brajRadhaNagri');
$router->get('/projects/budh-bihar-colony', 'Front\\PageController@budhBiharColony');
$router->get('/projects/awadhpuri', 'Front\\PageController@awadhpuri');

// ============================================
// ADMIN AUTHENTICATION (PRIMARY)
// ============================================
$router->get('/admin/login', 'App\\Http\\Controllers\\Auth\\AdminAuthController@adminLogin');
$router->post('/admin/login', 'App\\Http\\Controllers\\Auth\\AdminAuthController@authenticateAdmin');
$router->get('/admin/logout', 'App\\Http\\Controllers\\Auth\\AdminAuthController@logout');
$router->get('/admin/profile', 'App\\Http\\Controllers\\Admin\\AdminProfileController@index');
$router->post('/admin/profile', 'App\\Http\\Controllers\\Admin\\AdminProfileController@update');
$router->get('/admin/profile/security', 'App\\Http\\Controllers\\Admin\\AdminProfileController@security');
$router->post('/admin/profile/change-password', 'App\\Http\\Controllers\\Admin\\AdminProfileController@changePassword');

// ============================================
// ADMIN DASHBOARD (UNIFIED)
// ============================================
$router->get('/admin', 'App\\Http\\Controllers\\RoleBasedDashboardController@index');
$router->get('/admin/dashboard', 'App\\Http\\Controllers\\RoleBasedDashboardController@index');
$router->get('/admin/enterprise_dashboard', 'App\\Http\\Controllers\\RoleBasedDashboardController@enterpriseDashboard');
$router->get('/admin/dashboard/{role}', 'App\\Http\\Controllers\\RoleBasedDashboardController@getRoleDashboard');

// Role-specific dashboards
$router->get('/admin/dashboard/agent', 'App\\Http\\Controllers\\RoleBasedDashboardController@agent');
$router->get('/admin/dashboard/builder', 'App\\Http\\Controllers\\RoleBasedDashboardController@builder');
$router->get('/admin/dashboard/ceo', 'App\\Http\\Controllers\\RoleBasedDashboardController@ceo');
$router->get('/admin/dashboard/cfo', 'App\\Http\\Controllers\\RoleBasedDashboardController@cfo');
$router->get('/admin/dashboard/cm', 'App\\Http\\Controllers\\RoleBasedDashboardController@cm');
$router->get('/admin/dashboard/coo', 'App\\Http\\Controllers\\RoleBasedDashboardController@coo');
$router->get('/admin/dashboard/cto', 'App\\Http\\Controllers\\RoleBasedDashboardController@cto');
$router->get('/admin/dashboard/director', 'App\\Http\\Controllers\\RoleBasedDashboardController@director');
$router->get('/admin/dashboard/finance', 'App\\Http\\Controllers\\RoleBasedDashboardController@finance');
$router->get('/admin/dashboard/hr', 'App\\Http\\Controllers\\RoleBasedDashboardController@hr');
$router->get('/admin/dashboard/it', 'App\\Http\\Controllers\\RoleBasedDashboardController@it');
$router->get('/admin/dashboard/marketing', 'App\\Http\\Controllers\\RoleBasedDashboardController@marketing');
$router->get('/admin/dashboard/operations', 'App\\Http\\Controllers\\RoleBasedDashboardController@operations');
$router->get('/admin/dashboard/sales', 'App\\Http\\Controllers\\RoleBasedDashboardController@sales');
$router->get('/admin/dashboard/superadmin', 'App\\Http\\Controllers\\RoleBasedDashboardController@superadmin');

// ============================================
// ADMIN MODULES (CRUD Routes)
// ============================================
// Properties
$router->get('/admin/properties', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@index');
$router->get('/admin/properties/create', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@create');
$router->post('/admin/properties', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@store');
$router->get('/admin/properties/{id}', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@show');
$router->get('/admin/properties/{id}/edit', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@edit');
$router->post('/admin/properties/{id}/update', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@update');
$router->post('/admin/properties/{id}/destroy', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@destroy');

// Users
$router->get('/admin/users', 'App\\Http\\Controllers\\Admin\\AdminController@users');

// Bookings
$router->get('/admin/bookings', 'App\\Http\\Controllers\\Admin\\BookingController@index');
$router->get('/admin/bookings/create', 'App\\Http\\Controllers\\Admin\\BookingController@create');
$router->post('/admin/bookings', 'App\\Http\\Controllers\\Admin\\BookingController@store');
$router->get('/admin/bookings/{id}', 'App\\Http\\Controllers\\Admin\\BookingController@show');
$router->get('/admin/bookings/{id}/edit', 'App\\Http\\Controllers\\Admin\\BookingController@edit');
$router->post('/admin/bookings/{id}/update', 'App\\Http\\Controllers\\Admin\\BookingController@update');
$router->post('/admin/bookings/{id}/destroy', 'App\\Http\\Controllers\\Admin\\BookingController@destroy');
$router->post('/admin/bookings/{id}/payment', 'App\\Http\\Controllers\\Admin\\BookingController@processPayment');

// Sites
$router->get('/admin/sites', 'App\\Http\\Controllers\\Admin\\SiteController@index');
$router->get('/admin/sites/create', 'App\\Http\\Controllers\\Admin\\SiteController@create');
$router->post('/admin/sites', 'App\\Http\\Controllers\\Admin\\SiteController@store');
$router->get('/admin/sites/{id}', 'App\\Http\\Controllers\\Admin\\SiteController@show');
$router->get('/admin/sites/{id}/edit', 'App\\Http\\Controllers\\Admin\\SiteController@edit');
$router->post('/admin/sites/{id}/update', 'App\\Http\\Controllers\\Admin\\SiteController@update');
$router->post('/admin/sites/{id}/destroy', 'App\\Http\\Controllers\\Admin\\SiteController@destroy');

// Plots
$router->get('/admin/plots', 'App\\Http\\Controllers\\Admin\\PlotManagementController@index');
$router->get('/admin/plots/create', 'App\\Http\\Controllers\\Admin\\PlotManagementController@create');
$router->post('/admin/plots', 'App\\Http\\Controllers\\Admin\\PlotManagementController@store');
$router->get('/admin/plots/{id}', 'App\\Http\\Controllers\\Admin\\PlotManagementController@show');
$router->get('/admin/plots/{id}/edit', 'App\\Http\\Controllers\\Admin\\PlotManagementController@edit');
$router->post('/admin/plots/{id}/update', 'App\\Http\\Controllers\\Admin\\PlotManagementController@update');
$router->post('/admin/plots/{id}/destroy', 'App\\Http\\Controllers\\Admin\\PlotManagementController@destroy');

// Campaign Management
$router->get('/admin/campaigns', 'Admin\\CampaignController@index');
$router->get('/admin/campaigns/create', 'Admin\\CampaignController@create');
$router->post('/admin/campaigns/store', 'Admin\\CampaignController@store');
$router->get('/admin/campaigns/{id}/edit', 'Admin\\CampaignController@edit');
$router->post('/admin/campaigns/{id}/update', 'Admin\\CampaignController@update');
$router->get('/admin/campaigns/{id}/delete', 'Admin\\CampaignController@delete');
$router->get('/admin/campaigns/{id}/analytics', 'Admin\\CampaignController@analytics');
$router->get('/admin/campaigns/{id}/launch', 'Admin\\CampaignController@launch');

// ============================================
// ADMIN ADMINISTRATIVE
// ============================================
$router->get('/admin/legal-pages', 'Admin\\LegalPagesController@index');
$router->post('/admin/legal-pages/update-terms', 'Admin\\LegalPagesController@updateTerms');
$router->post('/admin/legal-pages/update-privacy', 'Admin\\LegalPagesController@updatePrivacy');
$router->get('/admin/layout-manager', 'Admin\\LayoutController@layoutManager');
$router->post('/admin/layout-manager', 'Admin\\LayoutController@updateLayoutSettings');
$router->get('/admin/ai-config', 'App\\Http\\Controllers\\AIController@configuration');
$router->post('/admin/test-ai-api', 'App\\Http\\Controllers\\AIController@testAPI');

// ============================================
// EMPLOYEE SYSTEM
// ============================================
$router->get('/employee/login', 'Employee\\EmployeeController@login');
$router->post('/employee/login', 'Employee\\EmployeeController@authenticate');
$router->get('/employee/logout', 'Employee\\EmployeeController@logout');
$router->get('/employee/dashboard', 'Employee\\EmployeeController@dashboard');
$router->get('/employee/profile', 'Employee\\EmployeeController@profile');
$router->post('/employee/profile', 'Employee\\EmployeeController@updateProfile');
$router->post('/employee/checkin', 'Employee\\EmployeeController@checkIn');
$router->post('/employee/checkout', 'Employee\\EmployeeController@checkOut');
$router->post('/employee/api/update-task', 'Employee\\EmployeeController@updateTask');

// ============================================
// USER MANAGEMENT
// ============================================
$router->get('/users', 'User\\UserController@index');
$router->get('/users/dashboard', 'User\\UserController@dashboard');
$router->get('/users/create', 'User\\UserController@create');
$router->post('/users/store', 'User\\UserController@store');
$router->get('/users/edit/{id}', 'User\\UserController@edit');
$router->post('/users/update/{id}', 'User\\UserController@update');
$router->get('/users/show/{id}', 'User\\UserController@show');
$router->get('/users/profile/{id}', 'User\\UserController@profile');
$router->post('/users/update-profile/{id}', 'User\\UserController@updateProfile');
$router->get('/users/change-password/{id}', 'User\\UserController@changePassword');
$router->post('/users/update-password/{id}', 'User\\UserController@updatePassword');
$router->post('/users/update-status/{id}', 'User\\UserController@updateStatus');
$router->get('/users/delete/{id}', 'User\\UserController@delete');
$router->get('/users/by-role/{role}', 'User\\UserController@byRole');

// ============================================
// ASSOCIATES
// ============================================
$router->get('/associates', 'Associate\\AssociateController@index');
$router->get('/associates/dashboard', 'Associate\\AssociateController@dashboard');
$router->get('/associates/create', 'Associate\\AssociateController@create');
$router->post('/associates/store', 'Associate\\AssociateController@store');
$router->get('/associates/edit/{id}', 'Associate\\AssociateController@edit');
$router->post('/associates/update/{id}', 'Associate\\AssociateController@update');
$router->get('/associates/show/{id}', 'Associate\\AssociateController@show');
$router->get('/associates/metrics/{id}', 'Associate\\AssociateController@metrics');
$router->post('/associates/update-status/{id}', 'Associate\\AssociateController@updateStatus');
$router->get('/associates/delete/{id}', 'Associate\\AssociateController@delete');

// ============================================
// LAND & PLOTTING
// ============================================
$router->get('/land/dashboard', 'Land\\PlottingController@dashboard');
$router->get('/land/plots', 'Land\\PlottingController@plots');
$router->get('/land/plot/{id}', 'Land\\PlottingController@plotDetails');
$router->post('/land/plot/book', 'Land\\PlottingController@bookPlot');
$router->get('/land/acquisitions', 'Land\\PlottingController@acquisitions');
$router->post('/land/acquisition/add', 'Land\\PlottingController@addAcquisition');

// ============================================
// MEDIA LIBRARY
// ============================================
$router->get('/media/library', 'Media\\MediaLibraryController@index');
$router->get('/media/upload', 'Media\\MediaLibraryController@upload');
$router->post('/media/upload', 'Media\\MediaLibraryController@handleUpload');
$router->get('/media/file/{id}', 'Media\\MediaLibraryController@viewFile');
$router->post('/media/file/{id}/update', 'Media\\MediaLibraryController@updateFile');
$router->delete('/media/file/{id}', 'Media\\MediaLibraryController@deleteFile');
$router->get('/media/categories', 'Media\\MediaLibraryController@categories');
$router->get('/media/stats', 'Media\\MediaLibraryController@getStats');

// ============================================
// API ROUTES (Included from api.php)
// ============================================
require_once __DIR__ . '/api.php';

// ============================================
// ADDITIONAL FRONTEND PAGES
// ============================================
$router->get('/resell', 'ResellController@index');
$router->get('/news', 'Front\\PageController@news');
$router->get('/whatsapp-chat', 'Front\\PageController@whatsappChat');
$router->get('/virtual-tour', 'Front\\PageController@virtualTour');
$router->get('/mlm-dashboard', 'App\\Http\\Controllers\\MLMController@dashboard');
$router->get('/financial-services', 'Front\\PageController@financialServices');
$router->get('/featured-properties', 'Front\\PageController@featuredProperties');
$router->get('/customer-reviews', 'Front\\PageController@customerReviews');
$router->get('/interior-design', 'Front\\PageController@interiorDesign');
$router->get('/under-construction', 'Front\\PageController@underConstruction');
$router->get('/thank-you', 'Front\\PageController@thankYou');
$router->get('/plots-availability', 'Front\\PageController@plotsAvailability');
$router->get('/plot', 'Front\\PageController@plot');
$router->get('/user-ai-suggestions', 'Front\\PageController@userAiSuggestions');
$router->get('/user/saved-searches', 'Front\\PageController@userSavedSearches');
$router->get('/user/notifications', 'Front\\PageController@userNotifications');
$router->get('/user/investments', 'Front\\PageController@userInvestments');
$router->get('/user/edit-profile', 'Front\\PageController@userEditProfile');

// System Pages
$router->get('/system/log-security-event', 'Front\\PageController@systemLogSecurityEvent');
$router->get('/system/launch-system', 'Front\\PageController@systemLaunchSystem');
$router->get('/system/kyc-upload', 'Front\\PageController@systemKycUpload');

// Careers
$router->get('/careers', 'Front\\PageController@careers');
$router->get('/careers/apply', 'Front\\PageController@careerApply');
$router->post('/careers/apply', 'Front\\PageController@submitCareerApplication');

// Blog
$router->get('/blog', 'Front\\PageController@blog');
$router->get('/blog/category/{category}', 'BlogController@category');

// Gallery
$router->get('/gallery', 'GalleryController@index');
$router->get('/gallery/project/{projectId}', 'GalleryController@project');

// Map
$router->get('/map', 'MapController@index');
$router->get('/map/properties-data', 'MapController@getPropertiesData');
$router->get('/map/search-bounds', 'MapController@searchByBounds');
$router->get('/map/location-suggestions', 'MapController@getLocationSuggestions');

// ============================================
// AI ROUTES
// ============================================
$router->get('/ai-chat', 'App\\Http\\Controllers\\AIController@chat');
$router->get('/ai-chat-enhanced', 'App\\Http\\Controllers\\AIController@chatEnhanced');
$router->get('/ai-chat/popup', 'App\\Http\\Controllers\\AIController@chatPopup');
$router->get('/property-ai-chat', 'App\\Http\\Controllers\\AIController@propertyChat');
$router->get('/property-ai-chat/{id}', 'App\\Http\\Controllers\\AIController@propertyChat');
$router->get('/ai-valuation', 'AIValuationController@index');

// Senior Developer Routes
$router->get('/senior-developer', 'App\\Http\\Controllers\\AIController@seniorDeveloper');
$router->get('/senior-developer/status', 'App\\Http\\Controllers\\AIController@seniorDeveloperStatus');
$router->post('/senior-developer/execute', 'App\\Http\\Controllers\\AIController@seniorDeveloperExecute');
$router->get('/senior-developer/logs', 'App\\Http\\Controllers\\AIController@seniorDeveloperLogs');
$router->get('/senior-developer/monitor', 'App\\Http\\Controllers\\AIController@seniorDeveloperMonitor');
$router->get('/senior-developer/dashboard', 'App\\Http\\Controllers\\AIController@seniorDeveloperDashboard');
$router->get('/senior-developer/unified', 'App\\Http\\Controllers\\AIController@seniorDeveloperUnified');
$router->post('/senior-developer/save-code', 'App\\Http\\Controllers\\AIController@saveCode');
$router->post('/senior-developer/run-code', 'App\\Http\\Controllers\\AIController@runCode');

// ============================================
// MCP ROUTES
// ============================================
$router->get('/mcp_dashboard', 'MCPController@dashboard');
$router->get('/mcp_configuration_gui', 'MCPController@configuration');
$router->get('/import_mcp_config', 'MCPController@import');

// ============================================
// MONITORING
// ============================================
$router->get('/monitoring', 'MonitoringController@dashboard');

// ============================================
// API AJAX ROUTES (Dashboard)
// ============================================
$router->get('/api/dashboard/{role}/performance', 'App\\Http\\Controllers\\RoleBasedDashboardController@getPerformanceData');
$router->get('/api/dashboard/{role}/analytics', 'App\\Http\\Controllers\\RoleBasedDashboardController@getAnalytics');
$router->get('/api/dashboard/agent/performance', 'App\\Http\\Controllers\\RoleBasedDashboardController@getPerformanceData');
$router->get('/api/dashboard/agent/network', 'App\\Http\\Controllers\\RoleBasedDashboardController@getNetworkTree');
$router->get('/api/dashboard/ceo/analytics', 'App\\Http\\Controllers\\RoleBasedDashboardController@getRevenueAnalytics');
$router->get('/api/dashboard/ceo/team', 'App\\Http\\Controllers\\RoleBasedDashboardController@getTeamPerformance');
$router->get('/api/dashboard/cfo/financial', 'App\\Http\\Controllers\\RoleBasedDashboardController@getFinancialAnalytics');
$router->get('/api/dashboard/cfo/expenses', 'App\\Http\\Controllers\\RoleBasedDashboardController@getExpenseBreakdown');
$router->get('/api/dashboard/builder/analytics', 'App\\Http\\Controllers\\RoleBasedDashboardController@getConstructionAnalytics');
$router->get('/api/dashboard/builder/materials', 'App\\Http\\Controllers\\RoleBasedDashboardController@getMaterialStatus');
$router->get('/api/mlm/tree', 'MLMController@getNetworkTree');
$router->get('/api/properties/featured', 'Front\\PageController@getFeaturedProperties');
$router->get('/api/lead-stats', 'App\\Http\\Controllers\\AIController@leadStats');

// ============================================
// NOTIFICATIONS
// ============================================
$router->get('/api/notifications', 'NotificationController@getNotifications');
$router->post('/api/notifications/mark-read', 'NotificationController@markAsRead');
$router->get('/api/notifications/unread-count', 'NotificationController@getUnreadCount');
$router->get('/api/popups', 'NotificationController@getPopups');
$router->post('/api/popups/dismiss', 'NotificationController@dismissPopup');
$router->post('/admin/notifications/create', 'NotificationController@createNotification');
$router->post('/admin/popups/create', 'NotificationController@createPopup');

// ============================================
// AI SETTINGS (Admin)
// ============================================
$router->get('/admin/ai-settings', 'Admin\\AISettingsController@index');
$router->post('/admin/ai-settings/update-key', 'Admin\\AISettingsController@updateApiKey');
$router->post('/admin/ai-settings/test-connection', 'Admin\\AISettingsController@testConnection');
$router->post('/admin/ai-settings/generate-content', 'Admin\\AISettingsController@generateSampleContent');
$router->post('/admin/ai-settings/clear-logs', 'Admin\\AISettingsController@clearLogs');
$router->get('/admin/ai-settings/export-usage-report', 'Admin\\AISettingsController@exportUsageReport');
$router->post('/admin/ai-settings/chat', 'Admin\\AISettingsController@chat');

error_log("ROUTER SETUP: Route registration complete");
