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

// Legal Pages
$router->get('/terms', 'Front\\PageController@terms');
$router->get('/privacy', 'Front\\PageController@privacy');

// Admin Legal Pages Management
$router->get('/admin/legal-pages', 'Admin\\LegalPagesController@index');
$router->post('/admin/legal-pages/update-terms', 'Admin\\LegalPagesController@updateTerms');
$router->post('/admin/legal-pages/update-privacy', 'Admin\\LegalPagesController@updatePrivacy');

// Admin Layout Manager
$router->get('/admin/layout-manager', 'Admin\\LayoutController@layoutManager');
$router->post('/admin/layout-manager', 'Admin\\LayoutController@updateLayoutSettings');

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

// Admin routes - Unified Role-Based Dashboard
$router->get('/admin', 'App\Http\Controllers\RoleBasedDashboardController@index');
$router->get('/admin/dashboard', 'App\Http\Controllers\RoleBasedDashboardController@index');
$router->get('/admin/enterprise_dashboard', 'App\Http\Controllers\RoleBasedDashboardController@enterpriseDashboard');

// Role-based dashboard routes - Unified system
$router->get('/admin/dashboard/{role}', 'App\Http\Controllers\RoleBasedDashboardController@getRoleDashboard');
$router->get('/admin/dashboard/agent', 'App\Http\Controllers\RoleBasedDashboardController@agent');
$router->get('/admin/dashboard/builder', 'App\Http\Controllers\RoleBasedDashboardController@builder');
$router->get('/admin/dashboard/ceo', 'App\Http\Controllers\RoleBasedDashboardController@ceo');
$router->get('/admin/dashboard/cfo', 'App\Http\Controllers\RoleBasedDashboardController@cfo');
$router->get('/admin/dashboard/cm', 'App\Http\Controllers\RoleBasedDashboardController@cm');
$router->get('/admin/dashboard/coo', 'App\Http\Controllers\RoleBasedDashboardController@coo');
$router->get('/admin/dashboard/cto', 'App\Http\Controllers\RoleBasedDashboardController@cto');
$router->get('/admin/dashboard/director', 'App\Http\Controllers\RoleBasedDashboardController@director');
$router->get('/admin/dashboard/finance', 'App\Http\Controllers\RoleBasedDashboardController@finance');
$router->get('/admin/dashboard/hr', 'App\Http\Controllers\RoleBasedDashboardController@hr');
$router->get('/admin/dashboard/it', 'App\Http\Controllers\RoleBasedDashboardController@it');
$router->get('/admin/dashboard/marketing', 'App\Http\Controllers\RoleBasedDashboardController@marketing');
$router->get('/admin/dashboard/operations', 'App\Http\Controllers\RoleBasedDashboardController@operations');
$router->get('/admin/dashboard/sales', 'App\Http\Controllers\RoleBasedDashboardController@sales');
$router->get('/admin/dashboard/superadmin', 'App\Http\Controllers\RoleBasedDashboardController@superadmin');

// Dashboard AJAX Routes - Unified system
$router->get('/api/dashboard/{role}/performance', 'App\Http\Controllers\RoleBasedDashboardController@getPerformanceData');
$router->get('/api/dashboard/{role}/analytics', 'App\Http\Controllers\RoleBasedDashboardController@getAnalytics');
$router->get('/api/dashboard/agent/performance', 'App\Http\Controllers\RoleBasedDashboardController@getPerformanceData');
$router->get('/api/dashboard/agent/network', 'App\Http\Controllers\RoleBasedDashboardController@getNetworkTree');
$router->get('/api/dashboard/ceo/analytics', 'App\Http\Controllers\RoleBasedDashboardController@getRevenueAnalytics');
$router->get('/api/dashboard/ceo/team', 'App\Http\Controllers\RoleBasedDashboardController@getTeamPerformance');
$router->get('/api/dashboard/cfo/financial', 'App\Http\Controllers\RoleBasedDashboardController@getFinancialAnalytics');
$router->get('/api/dashboard/cfo/expenses', 'App\Http\Controllers\RoleBasedDashboardController@getExpenseBreakdown');
$router->get('/api/dashboard/builder/analytics', 'App\Http\Controllers\RoleBasedDashboardController@getConstructionAnalytics');
$router->get('/api/dashboard/builder/materials', 'App\Http\Controllers\RoleBasedDashboardController@getMaterialStatus');
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

// AI Settings Routes
$router->get('/admin/ai-settings', 'Admin\\AISettingsController@index');
$router->post('/admin/ai-settings/update-key', 'Admin\\AISettingsController@updateApiKey');
$router->post('/admin/ai-settings/test-connection', 'Admin\\AISettingsController@testConnection');
$router->post('/admin/ai-settings/generate-content', 'Admin\\AISettingsController@generateSampleContent');
$router->post('/admin/ai-settings/clear-logs', 'Admin\\AISettingsController@clearLogs');
$router->get('/admin/ai-settings/export-usage-report', 'Admin\\AISettingsController@exportUsageReport');
$router->post('/admin/ai-settings/chat', 'Admin\\AISettingsController@chat');

// Gemini AI API Routes
$router->post('/api/gemini/chat', 'Api\\GeminiApiController@chat');
$router->post('/api/gemini/generate', 'Api\\GeminiApiController@generateContent');
$router->post('/api/gemini/recommendations', 'Api\\GeminiApiController@propertyRecommendations');
$router->post('/api/gemini/support', 'Api\\GeminiApiController@customerSupport');
$router->post('/api/gemini/market-analysis', 'Api\\GeminiApiController@marketAnalysis');
$router->post('/api/gemini/social-media', 'Api\\GeminiApiController@socialMediaContent');
$router->get('/api/gemini/test', 'Api\\GeminiApiController@testConnection');
$router->get('/api/gemini/status', 'Api\\GeminiApiController@getStatus');

// Employee System Routes
$router->get('/employee/login', 'Employee\\EmployeeController@login');
$router->post('/employee/login', 'Employee\\EmployeeController@authenticate');
$router->get('/employee/logout', 'Employee\\EmployeeController@logout');
$router->get('/employee/dashboard', 'Employee\\EmployeeController@dashboard');
$router->get('/employee/profile', 'Employee\\EmployeeController@profile');
$router->post('/employee/profile', 'Employee\\EmployeeController@updateProfile');
$router->post('/employee/checkin', 'Employee\\EmployeeController@checkIn');
$router->post('/employee/checkout', 'Employee\\EmployeeController@checkOut');
$router->post('/employee/api/update-task', 'Employee\\EmployeeController@updateTask');

// Admin Authentication
$router->get('/admin/login', 'App\Http\Controllers\Auth\AdminAuthController@adminLogin');
$router->post('/admin/login', 'App\Http\Controllers\Auth\AdminAuthController@authenticateAdmin');
$router->get('/admin/logout', 'App\Http\Controllers\Auth\AdminAuthController@logout');

// Admin Dashboard
$router->get('/admin/dashboard', 'App\Http\Controllers\Admin\AdminDashboardController@index');
$router->get('/admin/dashboard/cm', 'App\Http\Controllers\Admin\CMDashboardController@index');

// Properties
$router->get('/properties', 'Front\\PageController@properties');
$router->get('/properties/{id}', 'Front\\PageController@propertyDetails');
$router->get('/company/projects', 'Front\\PageController@projects');
$router->get('/projects/{slug}', 'Front\\PageController@projectDetails');

// Careers
$router->get('/careers', 'Front\\PageController@career');
$router->get('/careers/jobs', 'Front\\PageController@careerJobs');
$router->get('/careers/job/{id}', 'Front\\PageController@careerJobDetails');

// 404 Handler (must be last)
// $router->set404Handler('Front\\PageController@notFound');
$router->get('/careers', 'Front\PageController@career');
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

// Admin Dashboard routes (Modern MVC)
$router->get('/admin/dashboard', 'Admin\AdminDashboardController@dashboard');
$router->get('/admin/stats', 'Admin\AdminDashboardController@getStats');

// Career/HR routes (Modern MVC) - Using Front\PageController for public pages
$router->get('/careers/jobs', 'Front\PageController@careerJobs');
$router->get('/careers/job/{id}', 'Front\PageController@careerJobDetails');

// Land/Plotting routes (Modern MVC)
$router->get('/land/dashboard', 'Land\PlottingController@dashboard');
$router->get('/land/plots', 'Land\PlottingController@plots');
$router->get('/land/plot/{id}', 'Land\PlottingController@plotDetails');
$router->post('/land/plot/book', 'Land\PlottingController@bookPlot');
$router->get('/land/acquisitions', 'Land\PlottingController@acquisitions');
$router->post('/land/acquisition/add', 'Land\PlottingController@addAcquisition');

// Media Library routes (Modern MVC)
$router->get('/media/library', 'Media\MediaLibraryController@index');
$router->get('/media/upload', 'Media\MediaLibraryController@upload');
$router->post('/media/upload', 'Media\MediaLibraryController@handleUpload');
$router->get('/media/file/{id}', 'Media\MediaLibraryController@viewFile');
$router->post('/media/file/{id}/update', 'Media\MediaLibraryController@updateFile');
$router->delete('/media/file/{id}', 'Media\MediaLibraryController@deleteFile');
$router->get('/media/categories', 'Media\MediaLibraryController@categories');
$router->get('/media/stats', 'Media\MediaLibraryController@getStats');

// Admin routes (Modern MVC)
$router->get('/admin/stats', 'Admin\AdminController@getStats');
$router->get('/admin/activities', 'Admin\AdminController@getRecentActivities');
$router->get('/admin/analytics/properties', 'Admin\AdminController@propertyAnalytics');
$router->get('/admin/analytics/users', 'Admin\AdminController@getUserManagementData');
$router->get('/admin/analytics/leads', 'Admin\AdminController@getLeadManagementData');
$router->get('/admin/analytics/bookings', 'Admin\AdminController@getBookingManagementData');
$router->get('/admin/system/health', 'Admin\AdminController@getSystemHealthStatus');

// Media routes (Modern MVC)
$router->get('/media', 'Media\MediaController@index');
$router->get('/media/templates', 'Media\MediaController@getMediaForTemplates');
$router->get('/media/headers', 'Media\MediaController@getHeaderImages');
$router->get('/media/team', 'Media\MediaController@getTeamPhotos');
$router->get('/media/properties', 'Media\MediaController@propertyImages');
$router->get('/media/projects', 'Media\MediaController@getProjectImages');
$router->get('/media/documents', 'Media\MediaController@getDocuments');
$router->get('/media/carousel', 'Media\MediaController@getCarouselImages');
$router->post('/media/upload', 'Media\MediaController@upload');
$router->get('/media/url/{id}', 'Media\MediaController@getMediaUrl');

// Event routes (Modern MVC)
$router->get('/events/dashboard', 'Event\EventController@dashboard');
$router->post('/events/publish', 'Event\EventController@publish');
$router->get('/events/stats', 'Event\EventController@getStats');
$router->get('/events/recent', 'Event\EventController@getRecentEvents');
$router->post('/events/process-queue', 'Event\EventController@processQueue');
$router->post('/events/clear-logs', 'Event\EventController@clearOldLogs');
$router->post('/events/subscribe', 'Event\EventController@subscribe');
$router->get('/events/subscribers/{event}', 'Event\EventController@getSubscribers');
$router->post('/events/create-tables', 'Event\EventController@createTables');

// Performance routes (Modern MVC)
$router->get('/performance/dashboard', 'Performance\PerformanceController@dashboard');
$router->get('/performance/cache/{key}', 'Performance\PerformanceController@getCache');
$router->post('/performance/cache', 'Performance\PerformanceController@setCache');
$router->delete('/performance/cache/{key}', 'Performance\PerformanceController@deleteCache');
$router->delete('/performance/cache', 'Performance\PerformanceController@clearCache');
$router->get('/performance/stats', 'Performance\PerformanceController@getStats');
$router->post('/performance/optimize', 'Performance\PerformanceController@optimize');

// Marketing routes (Modern MVC)
$router->get('/marketing/dashboard', 'Marketing\MarketingAutomationController@dashboard');
$router->get('/marketing/leads', 'Marketing\MarketingAutomationController@leads');
$router->get('/marketing/lead/{id}', 'Marketing\MarketingAutomationController@leadDetails');
$router->get('/marketing/lead/capture', 'Marketing\MarketingAutomationController@captureLead');
$router->post('/marketing/lead/capture', 'Marketing\MarketingAutomationController@handleCaptureLead');
$router->post('/marketing/lead/{id}/status', 'Marketing\MarketingAutomationController@updateLeadStatus');
$router->post('/marketing/lead/{id}/score', 'Marketing\MarketingAutomationController@assignLeadScore');
$router->get('/marketing/campaigns', 'Marketing\MarketingAutomationController@campaigns');
$router->get('/marketing/campaign/create', 'Marketing\MarketingAutomationController@createCampaign');
$router->post('/marketing/campaign/create', 'Marketing\MarketingAutomationController@handleCreateCampaign');

// AJAX Marketing routes
$router->get('/api/marketing/leads', 'Marketing\MarketingAutomationController@getLeads');
$router->get('/api/marketing/lead', 'Marketing\MarketingAutomationController@getLead');
$router->get('/api/marketing/campaigns', 'Marketing\MarketingAutomationController@getCampaigns');
$router->get('/api/marketing/dashboard', 'Marketing\MarketingAutomationController@getDashboardData');
$router->get('/api/marketing/leads/stats', 'Marketing\MarketingAutomationController@getLeadStats');
$router->post('/api/marketing/lead/capture', 'Marketing\MarketingAutomationController@captureLeadAjax');
$router->post('/api/marketing/lead/status', 'Marketing\MarketingAutomationController@updateLeadStatusAjax');
$router->post('/api/marketing/lead/score', 'Marketing\MarketingAutomationController@assignLeadScoreAjax');
$router->post('/api/marketing/campaign/create', 'Marketing\MarketingAutomationController@createCampaignAjax');
$router->post('/api/marketing/automation/trigger', 'Marketing\MarketingAutomationController@triggerAutomation');

// Auth routes (Modern MVC)
$router->get('/auth/universal_login', 'Auth\AuthController@universalLogin');
$router->get('/auth/login', 'Auth\AuthController@login');
$router->post('/auth/login', 'Auth\AuthController@authenticate');
$router->get('/auth/logout', 'Auth\AuthController@logout');
$router->get('/auth/register', 'Auth\AuthController@register');
$router->post('/auth/register', 'Auth\AuthController@createAccount');
$router->get('/auth/forgot-password', 'Auth\AuthController@forgotPassword');
$router->post('/auth/forgot-password', 'Auth\AuthController@sendPasswordReset');
$router->get('/auth/reset-password', 'Auth\AuthController@resetPassword');
$router->post('/auth/reset-password', 'Auth\AuthController@updatePassword');
$router->get('/auth/profile', 'Auth\AuthController@profile');
$router->post('/auth/profile', 'Auth\AuthController@updateProfile');
$router->get('/auth/stats', 'Auth\AuthController@getStats');

// Communication routes (Modern MVC)
$router->get('/communication/media', 'Communication\MediaController@index');
$router->post('/communication/media/upload', 'Communication\MediaController@upload');
$router->get('/communication/media/{id}', 'Communication\MediaController@getMedia');
$router->put('/communication/media/{id}', 'Communication\MediaController@updateMedia');
$router->delete('/communication/media/{id}', 'Communication\MediaController@deleteMedia');
$router->get('/communication/media/search', 'Communication\MediaController@search');
$router->get('/communication/media/gallery/{id}', 'Communication\MediaController@getGallery');
$router->post('/communication/media/gallery', 'Communication\MediaController@createGallery');
$router->get('/communication/media/stats', 'Communication\MediaController@getStats');
$router->post('/communication/sms/send', 'Communication\SmsController@send');
$router->post('/communication/sms/bulk', 'Communication\SmsController@sendBulk');
$router->post('/communication/sms/schedule', 'Communication\SmsController@schedule');
$router->get('/communication/sms/status/{id}', 'Communication\SmsController@getStatus');
$router->get('/communication/sms/stats', 'Communication\SmsController@getStats');

// Utility routes (Modern MVC)
$router->get('/utility/alerts', 'Utility\AlertController@index');
$router->post('/utility/alerts', 'Utility\AlertController@createAlert');
$router->get('/utility/alerts/{id}', 'Utility\AlertController@getAlert');
$router->put('/utility/alerts/{id}', 'Utility\AlertController@updateAlert');
$router->delete('/utility/alerts/{id}', 'Utility\AlertController@deleteAlert');
$router->get('/utility/escalations', 'Utility\AlertController@getEscalations');
$router->post('/utility/escalations/process', 'Utility\AlertController@processEscalations');
$router->get('/utility/alerts/stats', 'Utility\AlertController@getStats');
$router->post('/utility/alerts/acknowledge/{id}', 'Utility\AlertController@acknowledgeAlert');
$router->post('/utility/alerts/dismiss/{id}', 'Utility\AlertController@dismissAlert');

// Async routes (Modern MVC)
$router->get('/async/tasks', 'Async\AsyncController@index');
$router->post('/async/tasks', 'Async\AsyncController@createTask');
$router->get('/async/tasks/{id}', 'Async\AsyncController@getTask');
$router->post('/async/tasks/process', 'Async\AsyncController@processTasks');
$router->post('/async/tasks/cancel/{id}', 'Async\AsyncController@cancelTask');
$router->post('/async/tasks/retry', 'Async\AsyncController@retryFailedTasks');
$router->get('/async/tasks/stats', 'Async\AsyncController@getTaskStats');
$router->delete('/async/tasks/cleanup', 'Async\AsyncController@cleanOldTasks');

// Security routes (Modern MVC)
$router->get('/security/configuration', 'Security\SecurityController@getConfiguration');
$router->post('/security/configuration', 'Security\SecurityController@setConfiguration');
$router->post('/security/configuration/apply', 'Security\SecurityController@applyConfiguration');
$router->get('/security/audit', 'Security\SecurityController@getAuditLog');
$router->get('/security/stats', 'Security\SecurityController@getStats');
$router->post('/security/harden', 'Security\SecurityController@applyHardening');
$router->get('/security/status', 'Security\SecurityController@getSecurityStatus');
$router->post('/security/block-ip', 'Security\SecurityController@blockIP');
$router->post('/security/unblock-ip', 'Security\SecurityController@unblockIP');
$router->post('/security/incident', 'Security\SecurityController@logIncident');
$router->get('/security/policies', 'Security\SecurityController@getPolicies');
$router->post('/security/policies', 'Security\SecurityController@createPolicy');
$router->post('/security/policies/enforce', 'Security\SecurityController@enforcePolicies');
$router->get('/security/compliance', 'Security\SecurityController@getComplianceReport');

// Career routes (Modern MVC)
$router->get('/careers/dashboard', 'Career\CareerController@dashboard');
$router->get('/careers/application/{id}', 'Career\CareerController@applicationDetails');
$router->post('/careers/apply', 'Career\CareerController@submitApplication');
$router->get('/careers/applications', 'Career\CareerController@getApplications');
$router->get('/careers/application/{id}/details', 'Career\CareerController@getApplication');
$router->post('/careers/application/{id}/status', 'Career\CareerController@updateStatus');
$router->post('/careers/application/{id}/interview', 'Career\CareerController@scheduleInterview');
$router->get('/careers/application/{id}/timeline', 'Career\CareerController@getTimeline');
$router->post('/careers/application/{id}/note', 'Career\CareerController@addNote');
$router->get('/careers/stats', 'Career\CareerController@getStats');
$router->get('/careers/export', 'Career\CareerController@exportApplications');

// Marketing routes (Modern MVC)
$router->get('/marketing/dashboard', 'Marketing\MarketingController@dashboard');
$router->post('/marketing/campaign', 'Marketing\MarketingController@createCampaign');
$router->post('/marketing/campaign/{id}/execute', 'Marketing\MarketingController@executeCampaign');
$router->post('/marketing/lead', 'Marketing\MarketingController@addLead');
$router->get('/marketing/lead/{id}', 'Marketing\MarketingController@getLead');
$router->post('/marketing/lead/{id}/status', 'Marketing\MarketingController@updateLeadStatus');
$router->post('/marketing/workflows/process', 'Marketing\MarketingController@processWorkflows');
$router->get('/marketing/analytics', 'Marketing\MarketingController@getAnalytics');
$router->get('/marketing/leads', 'Marketing\MarketingController@getLeads');
$router->get('/marketing/scoring', 'Marketing\MarketingController@getLeadScoring');
$router->get('/marketing/export', 'Marketing\MarketingController@exportLeads');
$router->get('/marketing/campaign/performance', 'Marketing\MarketingController@getCampaignPerformance');
$router->get('/marketing/settings', 'Marketing\MarketingController@settings');

// Farmer routes (Modern MVC)
$router->get('/farmers/dashboard', 'Business\FarmerController@dashboard');
$router->post('/farmers/register', 'Business\FarmerController@registerFarmer');
$router->get('/farmers/{id}', 'Business\FarmerController@getFarmer');
$router->get('/farmers', 'Business\FarmerController@getFarmers');
$router->post('/farmers/{id}/status', 'Business\FarmerController@updateFarmerStatus');
$router->post('/farmers/{id}/allocate-land', 'Business\FarmerController@allocateLand');
$router->post('/farmers/{id}/commission', 'Business\FarmerController@generateCommission');
$router->get('/farmers/stats', 'Business\FarmerController@getFarmerStats');
$router->get('/farmers/export', 'Business\FarmerController@exportFarmers');

// Land routes (Modern MVC)
$router->get('/land/dashboard', 'Land\LandController@dashboard');
$router->post('/land/project', 'Land\LandController@createProject');
$router->post('/land/project/{id}/subdivide', 'Land\LandController@subdivideLand');
$router->post('/land/project/{id}/plot', 'Land\LandController@createPlot');
$router->post('/land/plot/{id}/reserve', 'Land\LandController@reservePlot');
$router->post('/land/plot/{id}/sell', 'Land\LandController@sellPlot');
$router->get('/land/project/{id}', 'Land\LandController@getProject');
$router->get('/land/plot/{id}', 'Land\LandController@getPlot');
$router->get('/land/projects', 'Land\LandController@getProjects');
$router->get('/land/plots', 'Land\LandController@getPlots');
$router->get('/land/stats', 'Land\LandController@getStats');
$router->get('/land/project/{id}/details', 'Land\LandController@projectDetails');
$router->get('/land/plot/{id}/details', 'Land\LandController@plotDetails');
$router->get('/land/export/projects', 'Land\LandController@exportProjects');
$router->get('/land/export/plots', 'Land\LandController@exportPlots');
$router->get('/land/project/{id}/analytics', 'Land\LandController@getProjectAnalytics');
$router->get('/land/market-insights', 'Land\LandController@getMarketInsights');
$router->get('/land/settings', 'Land\LandController@settings');

// Localization routes (Modern MVC)
$router->post('/localization/set-locale', 'LocalizationController@setLocale');
$router->get('/localization/current', 'LocalizationController@getCurrentLocale');
$router->post('/localization/translate', 'LocalizationController@translate');
$router->post('/localization/translation', 'LocalizationController@addTranslation');
$router->get('/localization/translations', 'LocalizationController@getTranslations');
$router->delete('/localization/translation', 'LocalizationController@deleteTranslation');
$router->get('/localization/statistics', 'LocalizationController@getStatistics');
$router->post('/localization/import', 'LocalizationController@importTranslations');
$router->get('/localization/export', 'LocalizationController@exportTranslations');
$router->post('/localization/locale', 'LocalizationController@addLocale');
$router->post('/localization/clear-cache', 'LocalizationController@clearCache');
$router->get('/localization/management', 'LocalizationController@management');
$router->get('/localization/editor', 'LocalizationController@editor');

// Backup Integrity routes (Modern MVC)
$router->post('/backup/verify', 'Backup\BackupIntegrityController@verify');
$router->get('/backup/history', 'Backup\BackupIntegrityController@getHistory');
$router->get('/backup/statistics', 'Backup\BackupIntegrityController@getStatistics');
$router->post('/backup/schedule', 'Backup\BackupIntegrityController@schedule');
$router->get('/backup/scheduled', 'Backup\BackupIntegrityController@getScheduled');
$router->post('/backup/export', 'Backup\BackupIntegrityController@export');
$router->post('/backup/cleanup', 'Backup\BackupIntegrityController@cleanup');
$router->get('/backup/management', 'Backup\BackupIntegrityController@management');
$router->post('/backup/upload-verify', 'Backup\BackupIntegrityController@uploadAndVerify');
$router->get('/backup/details', 'Backup\BackupIntegrityController@getDetails');
$router->post('/backup/reverify', 'Backup\BackupIntegrityController@reverify');
$router->delete('/backup/verification', 'Backup\BackupIntegrityController@delete');
$router->get('/backup/dashboard', 'Backup\BackupIntegrityController@dashboard');

// Payroll routes (Modern MVC)
$router->post('/payroll/salary-structure', 'Payroll\SalaryController@createSalaryStructure');
$router->put('/payroll/salary-structure', 'Payroll\SalaryController@updateSalaryStructure');
$router->post('/payroll/process-salary', 'Payroll\SalaryController@processMonthlySalary');
$router->get('/payroll/salary-history', 'Payroll\SalaryController@getSalaryHistory');
$router->get('/payroll/statistics', 'Payroll\SalaryController@getPayrollStatistics');
$router->get('/payroll/settings', 'Payroll\SalaryController@getPayrollSettings');
$router->put('/payroll/settings', 'Payroll\SalaryController@updatePayrollSetting');
$router->post('/payroll/bulk-process', 'Payroll\SalaryController@bulkProcessSalaries');
$router->get('/payroll/salary-slip', 'Payroll\SalaryController@getSalarySlip');
$router->get('/payroll/export', 'Payroll\SalaryController@exportSalaryReport');
$router->get('/payroll/management', 'Payroll\SalaryController@management');
$router->get('/payroll/structure', 'Payroll\SalaryController@salaryStructure');
$router->get('/payroll/processing', 'Payroll\SalaryController@salaryProcessing');
$router->get('/payroll/reports', 'Payroll\SalaryController@salaryReports');

// Custom Features routes (Modern MVC)
$router->get('/features/dashboard', 'CustomFeatures\CustomFeaturesController@dashboard');
$router->post('/features/virtual-tour', 'CustomFeatures\CustomFeaturesController@createVirtualTour');
$router->get('/features/virtual-tour/{propertyId}', 'CustomFeatures\CustomFeaturesController@getVirtualTour');
$router->post('/features/compare', 'CustomFeatures\CustomFeaturesController@compareProperties');
$router->get('/features/neighborhood/{propertyId}', 'CustomFeatures\CustomFeaturesController@getNeighborhoodAnalytics');
$router->post('/features/investment', 'CustomFeatures\CustomFeaturesController@calculateInvestment');
$router->post('/features/smart-search', 'CustomFeatures\CustomFeaturesController@smartSearch');
$router->get('/features/stats', 'CustomFeatures\CustomFeaturesController@getStats');
$router->get('/features/virtual-tours', 'CustomFeatures\CustomFeaturesController@virtualTours');
$router->get('/features/comparison', 'CustomFeatures\CustomFeaturesController@propertyComparison');
$router->get('/features/investment-calculator', 'CustomFeatures\CustomFeaturesController@investmentCalculator');
$router->get('/features/smart-search-page', 'CustomFeatures\CustomFeaturesController@smartSearchPage');
$router->get('/features/neighborhood/{propertyId}/analytics', 'CustomFeatures\CustomFeaturesController@neighborhoodAnalytics');
$router->post('/features/save-comparison', 'CustomFeatures\CustomFeaturesController@saveComparison');
$router->get('/features/saved-comparisons', 'CustomFeatures\CustomFeaturesController@getSavedComparisons');
$router->get('/features/investment-history', 'CustomFeatures\CustomFeaturesController@getInvestmentHistory');
$router->post('/features/export-comparison', 'CustomFeatures\CustomFeaturesController@exportComparison');
$router->get('/features/suggestions/{propertyId}', 'CustomFeatures\CustomFeaturesController@getPropertySuggestions');

// Security routes (Modern MVC) - Additional
$router->post('/security/sanitize', 'Security\SecurityController@sanitize');
$router->post('/security/validate', 'Security\SecurityController@validate');
$router->post('/security/csrf-token', 'Security\SecurityController@generateCsrfToken');
$router->post('/security/csrf-validate', 'Security\SecurityController@validateCsrfToken');
$router->post('/security/rate-limit', 'Security\SecurityController@checkRateLimit');
$router->post('/security/password-strength', 'Security\SecurityController@validatePasswordStrength');
$router->post('/security/validate-file', 'Security\SecurityController@validateFileUpload');
$router->post('/security/detect-sql', 'Security\SecurityController@detectSqlInjection');
$router->post('/security/detect-xss', 'Security\SecurityController@detectXss');
$router->post('/security/log-event', 'Security\SecurityController@logSecurityEvent');
$router->get('/security/stats', 'Security\SecurityController@getSecurityStats');

// General routes
$router->get('/', 'HomeController@index');
$router->get('/mlm-dashboard', 'App\Http\Controllers\MLMController@dashboard');
$router->get('/monitoring', 'MonitoringController@dashboard');
$router->get('/ai-valuation', 'AIValuationController@index');

// Blog routes
$router->get('/blog/category/{category}', 'BlogController@category');

// Project routes
$router->get('/projects', 'ProjectController@index');
$router->get('/projects/{id}', 'ProjectController@detail');

// Resell routes
$router->get('/resell', 'ResellController@index');

// Customer routes
$router->get('/customer/dashboard', 'CustomerController@dashboard');
$router->get('/customers/dashboard', 'CustomerController@dashboard');

// MCP routes
$router->get('/mcp_dashboard', 'MCPController@dashboard');
$router->get('/mcp_configuration_gui', 'MCPController@configuration');
$router->get('/import_mcp_config', 'MCPController@import');

// Associate routes (Modern MVC)
$router->get('/associates', 'Associate\AssociateController@index');
$router->get('/associates/dashboard', 'Associate\AssociateController@dashboard');
$router->get('/associates/create', 'Associate\AssociateController@create');
$router->post('/associates/store', 'Associate\AssociateController@store');
$router->get('/associates/edit/{id}', 'Associate\AssociateController@edit');
$router->post('/associates/update/{id}', 'Associate\AssociateController@update');
$router->get('/associates/show/{id}', 'Associate\AssociateController@show');
$router->get('/associates/metrics/{id}', 'Associate\AssociateController@metrics');
$router->post('/associates/update-status/{id}', 'Associate\AssociateController@updateStatus');
$router->get('/associates/delete/{id}', 'Associate\AssociateController@delete');

// User routes (Modern MVC)
$router->get('/users', 'User\UserController@index');
$router->get('/users/dashboard', 'User\UserController@dashboard');
$router->get('/users/create', 'User\UserController@create');
$router->post('/users/store', 'User\UserController@store');
$router->get('/users/edit/{id}', 'User\UserController@edit');
$router->post('/users/update/{id}', 'User\UserController@update');
$router->get('/users/show/{id}', 'User\UserController@show');
$router->get('/users/profile/{id}', 'User\UserController@profile');
$router->post('/users/update-profile/{id}', 'User\UserController@updateProfile');
$router->get('/users/change-password/{id}', 'User\UserController@changePassword');
$router->post('/users/update-password/{id}', 'User\UserController@updatePassword');
$router->post('/users/update-status/{id}', 'User\UserController@updateStatus');
$router->get('/users/delete/{id}', 'User\UserController@delete');
$router->get('/users/by-role/{role}', 'User\UserController@byRole');

// Report routes (Modern MVC)
$router->get('/reports', 'Reports\ReportController@dashboard');
$router->get('/reports/generate', 'Reports\ReportController@generate');
$router->post('/reports/create', 'Reports\ReportController@create');
$router->get('/reports/scheduled', 'Reports\ReportController@scheduled');
$router->get('/reports/schedule', 'Reports\ReportController@schedule');
$router->post('/reports/store-schedule', 'Reports\ReportController@storeSchedule');
$router->get('/reports/sales', 'Reports\ReportController@sales');
$router->get('/reports/property', 'Reports\ReportController@property');
$router->get('/reports/associate', 'Reports\ReportController@associate');
$router->get('/reports/customer', 'Reports\ReportController@customer');
$router->get('/reports/financial', 'Reports\ReportController@financial');

// Utility routes (Modern MVC)
$router->get('/utils/file/upload', 'Utils\FileController@upload');
$router->post('/utils/file/upload', 'Utils\FileController@processUpload');
$router->get('/utils/files', 'Utils\FileController@index');
$router->get('/utils/files/{category}', 'Utils\FileController@byCategory');
$router->get('/utils/file/{id}', 'Utils\FileController@show');
$router->post('/utils/file/{id}/delete', 'Utils\FileController@delete');
$router->get('/utils/file/{id}/download', 'Utils\FileController@download');
$router->post('/utils/file/batch', 'Utils\FileController@batchOperation');

// Core Helper routes (Modern MVC)
$router->get('/core/helpers/csrf', 'Core\HelperController@getCSRFToken');
$router->post('/core/helpers/validate', 'Core\HelperController@validateInput');
$router->post('/core/helpers/email', 'Core\HelperController@sendEmail');
$router->get('/core/helpers/system-info', 'Core\HelperController@systemInfo');
$router->post('/core/helpers/backup', 'Core\HelperController@createBackup');
$router->post('/core/helpers/cleanup', 'Core\HelperController@cleanup');

// Employee Authentication and Dashboard Routes
$router->get('/employee/login', 'Employee\\EmployeeController@login');
$router->post('/employee/login', 'Employee\\EmployeeController@authenticate');
$router->get('/employee/logout', 'Employee\\EmployeeController@logout');
$router->get('/employee/dashboard', 'Employee\\EmployeeController@dashboard');
$router->get('/employee/profile', 'Employee\\EmployeeController@profile');

// Employee API Endpoints
$router->post('/employee/checkin', 'Employee\\EmployeeController@checkIn');
$router->post('/employee/checkout', 'Employee\\EmployeeController@checkOut');
$router->get('/employee/api/tasks', 'Employee\\EmployeeController@getTasks');
$router->post('/employee/api/update-task', 'Employee\\EmployeeController@updateTask');
$router->get('/employee/api/performance', 'Employee\\EmployeeController@getPerformance');
$router->get('/employee/api/attendance-records', 'Employee\\EmployeeController@getAttendanceRecords');

// Employee Page Routes (using existing views)
$router->get('/employee/tasks', 'Front\\PageController@employeeTasks');
$router->get('/employee/activities', 'Front\\PageController@employeeActivities');
$router->get('/employee/attendance', 'Front\\PageController@employeeAttendance');
$router->get('/employee/performance-page', 'Front\\PageController@employeePerformance');
$router->get('/employee/salary', 'Front\\PageController@employeeSalary');
$router->get('/employee/documents', 'Front\\PageController@employeeDocuments');
$router->get('/employee/leaves', 'Front\\PageController@employeeLeaves');
$router->get('/employee/reporting', 'Front\\PageController@employeeReporting');

// Campaign Management Routes
$router->get('/admin/campaigns', 'Admin\\CampaignController@index');
$router->get('/admin/campaigns/create', 'Admin\\CampaignController@create');
$router->post('/admin/campaigns/store', 'Admin\\CampaignController@store');
$router->get('/admin/campaigns/{id}/edit', 'Admin\\CampaignController@edit');
$router->post('/admin/campaigns/{id}/update', 'Admin\\CampaignController@update');
$router->get('/admin/campaigns/{id}/delete', 'Admin\\CampaignController@delete');
$router->get('/admin/campaigns/{id}/analytics', 'Admin\\CampaignController@analytics');
$router->get('/admin/campaigns/{id}/launch', 'Admin\\CampaignController@launch');

// Notification System Routes
$router->get('/api/notifications', 'NotificationController@getNotifications');
$router->post('/api/notifications/mark-read', 'NotificationController@markAsRead');
$router->get('/api/notifications/unread-count', 'NotificationController@getUnreadCount');
$router->get('/api/popups', 'NotificationController@getPopups');
$router->post('/api/popups/dismiss', 'NotificationController@dismissPopup');

// Admin Notification Management
$router->post('/admin/notifications/create', 'NotificationController@createNotification');
$router->post('/admin/popups/create', 'NotificationController@createPopup');

// Advanced Features Routes
// Social Login
$router->get('/auth/social/url', 'AdvancedFeaturesController@getSocialAuthUrl');
$router->get('/auth/google/callback', 'AdvancedFeaturesController@handleSocialCallback');
$router->get('/auth/facebook/callback', 'AdvancedFeaturesController@handleSocialCallback');
$router->get('/auth/linkedin/callback', 'AdvancedFeaturesController@handleSocialCallback');

// OTP Authentication
$router->post('/auth/otp/send', 'AdvancedFeaturesController@sendOTP');
$router->post('/auth/otp/verify', 'AdvancedFeaturesController@verifyOTP');

// Progressive Registration
$router->post('/auth/progressive/start', 'AdvancedFeaturesController@startProgressiveRegistration');
$router->get('/auth/progressive/current', 'AdvancedFeaturesController@getCurrentRegistrationStep');
$router->post('/auth/progressive/save', 'AdvancedFeaturesController@saveRegistrationStepData');
$router->post('/auth/progressive/next', 'AdvancedFeaturesController@moveToNextRegistrationStep');
$router->post('/auth/progressive/previous', 'AdvancedFeaturesController@moveToPreviousRegistrationStep');
$router->post('/auth/progressive/complete', 'AdvancedFeaturesController@completeProgressiveRegistration');

// AI Chatbot
$router->post('/api/chatbot/message', 'AdvancedFeaturesController@processChatbotMessage');
$router->get('/api/chatbot/history', 'AdvancedFeaturesController@getChatbotHistory');
$router->post('/api/chatbot/clear', 'AdvancedFeaturesController@clearChatbotConversation');

// Campaign Delivery
$router->post('/api/campaigns/deliver', 'AdvancedFeaturesController@deliverCampaign');
$router->get('/api/campaigns/stats', 'AdvancedFeaturesController@getCampaignStats');
$router->post('/api/campaigns/track', 'AdvancedFeaturesController@trackCampaignEngagement');
