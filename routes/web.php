<?php
// Web Routes - APS Dream Home
// Clean, deduplicated route definitions

// Include router
require_once __DIR__ . '/router.php';

// Initialize router
$router = new Router();

// ============================================================
// PUBLIC FRONTEND PAGES
// ============================================================

// Home
$router->get('/', 'Front\\PageController@home');

// Redirect /public to /
$router->get('/public', function() {
    header('Location: /', true, 301);
    exit;
});
$router->get('/public/', function() {
    header('Location: /', true, 301);
    exit;
});

// Static Pages
$router->get('/about', 'Front\\PageController@about');
$router->get('/contact', 'Front\\PageController@contact');
$router->post('/contact', 'Front\\PageController@contact');
$router->get('/services', 'Front\\PageController@services');
$router->get('/team', 'Front\\PageController@team');
$router->get('/testimonials', 'Front\\PageController@testimonials');
$router->get('/faq', 'Front\\PageController@faq');
$router->get('/faqs', 'Front\\PageController@faqs');
$router->get('/sitemap', 'Front\\PageController@sitemap');
$router->get('/privacy', 'Front\\PageController@privacy');
$router->get('/news', 'Front\\PageController@news');
$router->get('/blog', 'Front\\PageController@blog');
$router->get('/blog/{slug}', 'Front\\PageController@blogPost');
$router->get('/gallery', 'Front\\PageController@gallery');
$router->get('/resell', 'Front\\PageController@resell');
$router->get('/careers', 'Front\PageController@careers');
$router->get('/coming-soon', 'Front\PageController@comingSoon');

// MLM & AI Dashboard Routes
$router->get('/mlm-dashboard', 'MLM\MLMDashboardController@dashboard');
$router->get('/ai-dashboard', 'AIDashboardController@index');
$router->get('/ai-assistant', 'AIDashboardController@assistant');

// Property Pages
$router->get('/properties', 'Front\\PageController@properties');
$router->get('/properties/{id}', 'Front\\PageController@propertyDetails');
$router->get('/featured-properties', 'Front\PageController@featuredProperties');

// Property Comparison Routes
$router->get('/compare', 'Property\CompareController@index');
$router->get('/compare/results', 'Property\CompareController@compare');
$router->post('/compare/save', 'Property\CompareController@save');
$router->get('/compare/load/{id}', 'Property\CompareController@load');
$router->post('/compare/delete/{id}', 'Property\CompareController@delete');

// Project Pages
$router->get('/projects', 'Front\\PageController@projects');
$router->get('/company/projects', 'Front\\PageController@projects');
$router->get('/projects/{slug}', 'Front\\PageController@projectDetails');
$router->get('/projects/{location}', 'Front\\PageController@projectsByLocation');
$router->get('/navigation', 'Front\\PageController@navigation');
$router->get('/downloads', 'Front\\PageController@downloads');
$router->get('/under-construction', 'Front\\PageController@underConstruction');
$router->get('/thank-you', 'Front\\PageController@thankYou');
$router->get('/customer-reviews', 'Front\\PageController@customerReviews');

// Buy/Sell/Rent/Invest
$router->get('/buy', 'Front\\PageController@buyProperty');
$router->get('/sell', 'Front\\PageController@sellProperty');
$router->get('/rent', 'Front\\PageController@rentProperty');
$router->get('/invest', 'Front\\PageController@investProperty');

// Property Listing (User)
$router->get('/list-property', 'Front\\PageController@listProperty');
$router->post('/list-property/submit', 'Front\\PageController@handlePropertyListing');

// Form Handlers
$router->post('/quick-inquiry', 'Front\\PageController@handleQuickInquiry');

// AI Bot
$router->post('/ai-chat', 'Front\\AIBotController@chat');
$router->get('/ai-chat', 'Front\\AIBotController@chat');
$router->post('/whatsapp-webhook', 'Front\\AIBotController@whatsappWebhook');

// Admin Services
$router->get('/admin/services', 'App\\Http\\Controllers\\Admin\\ServiceController@index');
$router->get('/admin/services/view/{id}', 'App\\Http\\Controllers\\Admin\\ServiceController@view');
$router->post('/admin/services/update-status', 'App\\Http\\Controllers\\Admin\\ServiceController@updateStatus');

// Admin User Properties
$router->get('/admin/user-properties', 'App\\Http\\Controllers\\Admin\\UserPropertyController@index');
$router->get('/admin/user-properties/verify/{id}', 'App\\Http\\Controllers\\Admin\\UserPropertyController@verify');
$router->post('/admin/user-properties/action', 'App\\Http\\Controllers\\Admin\\UserPropertyController@action');

// Missing frontend routes (from header/footer links)
$router->get('/financial-services', 'Front\\PageController@financialServices');
$router->get('/interior-design', 'Front\\PageController@interiorDesign');
$router->get('/legal/terms-conditions', 'Front\\PageController@legalTermsConditions');
$router->get('/legal/services', 'Front\\PageController@legalServices');
$router->get('/legal/documents', 'Front\\PageController@legalDocuments');
$router->get('/user/edit-profile', 'Front\\PageController@userEditProfile');
$router->get('/news/view/{id}', 'Front\\PageController@newsView');
$router->get('/property/{id}', 'Front\\PageController@propertyDetails');
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/profile', 'DashboardController@profile');
$router->post('/dashboard/profile', 'DashboardController@updateProfile');
$router->get('/dashboard/favorites', 'DashboardController@favorites');
$router->post('/dashboard/favorites/add', 'DashboardController@addFavorite');
$router->post('/dashboard/favorites/remove', 'DashboardController@removeFavorite');
$router->get('/dashboard/inquiries', 'DashboardController@inquiries');
$router->post('/dashboard/inquiries/submit', 'DashboardController@submitInquiry');

// AI Routes
$router->get('/ai-valuation', 'AIController@propertyValuation');
$router->post('/api/ai/valuation', 'AIController@apiValuation');

// Lead Scoring Routes
$router->get('/admin/leads/scoring', 'App\Http\Controllers\Admin\LeadScoringController@index');
$router->get('/admin/leads/scoring/recalculate', 'App\Http\Controllers\Admin\LeadScoringController@recalculateScores');
$router->get('/api/leads/{id}/score-details', 'App\Http\Controllers\Admin\LeadScoringController@getScoreDetails');
$router->get('/admin/leads/scoring/export', 'App\Http\Controllers\Admin\LeadScoringController@export');

// Site Visit Routes
$router->get('/admin/visits', 'App\Http\Controllers\Admin\VisitController@index');
$router->get('/admin/visits/calendar', 'App\Http\Controllers\Admin\VisitController@calendar');
$router->get('/admin/visits/create', 'App\Http\Controllers\Admin\VisitController@create');
$router->post('/admin/visits/store', 'App\Http\Controllers\Admin\VisitController@store');
$router->post('/admin/visits/{id}/status', 'App\Http\Controllers\Admin\VisitController@updateStatus');

// Lead Documents Routes
$router->get('/admin/leads/{id}/documents', 'App\Http\Controllers\Admin\LeadController@getDocuments');
$router->post('/admin/leads/{id}/documents/upload', 'App\Http\Controllers\Admin\LeadController@uploadDocument');
$router->post('/admin/leads/documents/{id}/delete', 'App\Http\Controllers\Admin\LeadController@deleteDocument');

// Deal Tracking Routes
$router->get('/admin/deals', 'App\Http\Controllers\Admin\DealController@index');
$router->get('/admin/deals/kanban', 'App\Http\Controllers\Admin\DealController@kanban');
$router->get('/admin/deals/create', 'App\Http\Controllers\Admin\DealController@createFromLead');
$router->post('/admin/deals/store', 'App\Http\Controllers\Admin\DealController@store');
$router->post('/admin/deals/{id}/stage', 'App\Http\Controllers\Admin\DealController@updateStage');

// Achievement Routes
$router->get('/dashboard/achievements', 'AchievementController@index');
$router->get('/api/achievements/points', 'AchievementController@getPoints');
$router->get('/api/achievements/badges', 'AchievementController@getBadges');

// ============================================================
// AUTHENTICATION
// ============================================================

// Customer Auth
$router->get('/register', 'Auth\\CustomerAuthController@register');
$router->post('/register', 'Auth\\CustomerAuthController@handleRegister');
$router->get('/login', 'Auth\\CustomerAuthController@login');
$router->post('/login', 'Auth\\CustomerAuthController@authenticate');
$router->get('/logout', 'Auth\\CustomerAuthController@logout');
$router->get('/customer/dashboard', 'CustomerDashboardController@dashboard');

// Agent Auth
$router->get('/agent/register', 'Auth\\AgentAuthController@register');
$router->post('/agent/register', 'Auth\\AgentAuthController@handleRegister');
$router->get('/agent/login', 'Auth\\AgentAuthController@login');
$router->post('/agent/login', 'Auth\\AgentAuthController@authenticate');
$router->get('/agent/logout', 'Auth\\AgentAuthController@logout');
$router->get('/agent/dashboard', 'Auth\\AgentAuthController@login');

// Associate Auth
$router->get('/associate/register', 'Auth\\AssociateAuthController@associateRegister');
$router->post('/associate/register', 'Auth\\AssociateAuthController@handleAssociateRegister');
$router->get('/associate/login', 'Auth\\AssociateAuthController@associateLogin');
$router->post('/associate/login', 'Auth\\AssociateAuthController@authenticateAssociate');
$router->get('/associate/logout', 'Auth\\AssociateAuthController@logout');
$router->get('/associate/dashboard', 'DashboardController@associate');

// Employee Auth
$router->get('/employee/login', 'Employee\\EmployeeController@login');
$router->post('/employee/login', 'Employee\\EmployeeController@authenticate');
$router->get('/employee/logout', 'Employee\\EmployeeController@logout');
$router->get('/employee/dashboard', 'Employee\\EmployeeController@dashboard');
$router->get('/employee/profile', 'Employee\\EmployeeController@profile');
$router->post('/employee/profile', 'Employee\\EmployeeController@updateProfile');
$router->post('/employee/checkin', 'Employee\\EmployeeController@checkIn');
$router->post('/employee/checkout', 'Employee\\EmployeeController@checkOut');
$router->get('/employee/api/tasks', 'Employee\\EmployeeController@getTasks');
$router->post('/employee/api/update-task', 'Employee\\EmployeeController@updateTask');
$router->get('/employee/api/performance', 'Employee\\EmployeeController@getPerformance');
$router->get('/employee/api/attendance-records', 'Employee\\EmployeeController@getAttendanceRecords');

// Employee Pages
$router->get('/employee/tasks', 'Employee\\EmployeeController@tasks');
$router->get('/employee/activities', 'Employee\\EmployeeController@activities');
$router->get('/employee/attendance', 'Employee\\EmployeeController@attendance');
$router->get('/employee/performance-page', 'Employee\\EmployeeController@performancePage');
$router->get('/employee/salary', 'Employee\\EmployeeController@salary');
$router->get('/employee/documents', 'Employee\\EmployeeController@documents');
$router->get('/employee/leaves', 'Employee\\EmployeeController@leaves');
$router->get('/employee/reporting', 'Employee\\EmployeeController@reporting');
$router->get('/employee/settings', 'Employee\\EmployeeController@dashboard');

// MLM/Team
$router->get('/team/genealogy', 'Admin\\NetworkController@genealogy');
$router->get('/api/mlm/tree', 'MLMController@getNetworkTree');

// ============================================================
// AI PROPERTY VALUATION
// ============================================================

$router->get('/ai/property-valuation', 'AI\\PropertyValuationController@index');
$router->post('/ai/property-valuation/generate', 'AI\\PropertyValuationController@generateValuation');
$router->get('/ai/property-valuation/history', 'AI\\PropertyValuationController@getValuationHistory');
$router->post('/ai/property-valuation/batch', 'AI\\PropertyValuationController@batchValuation');
$router->post('/api/ai/valuation', 'AI\\PropertyValuationController@apiValuation');

// ============================================================
// AI CHATBOT
// ============================================================
$router->get('/ai/chatbot', 'AI\\AIWebController@chatbot');
$router->post('/api/ai/chatbot', 'AI\\ChatbotAPIController@handleMessage');
$router->get('/ai/chatbot/history', 'AI\\ChatbotAPIController@getHistory');

// ============================================================
// ADMIN PANEL
// ============================================================

// Admin Auth
$router->get('/admin/login', 'App\\Http\\Controllers\\Auth\\AdminAuthController@adminLogin');
$router->post('/admin/login', 'App\\Http\\Controllers\\Auth\\AdminAuthController@authenticateAdmin');
$router->get('/admin/logout', 'App\\Http\\Controllers\\Auth\\AdminAuthController@logout');

// Admin Dashboard (single route - uses RoleBasedDashboardController)
$router->get('/admin', 'App\\Http\\Controllers\\RoleBasedDashboardController@index');
$router->get('/admin/dashboard', 'App\\Http\\Controllers\\RoleBasedDashboardController@index');
$router->get('/admin/enterprise_dashboard', 'App\\Http\\Controllers\\RoleBasedDashboardController@enterpriseDashboard');

// Admin root route fix
$router->get('/admin/', 'App\\Http\\Controllers\\RoleBasedDashboardController@index');

// Role-specific dashboards
$router->get('/admin/dashboard/superadmin', 'App\\Http\\Controllers\\RoleBasedDashboardController@superadmin');
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

// Admin AJAX Dashboard APIs
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

// Admin Properties
$router->get('/admin/properties', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@index');
$router->get('/admin/properties/create', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@create');
$router->post('/admin/properties', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@store');
$router->get('/admin/properties/{id}', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@show');
$router->get('/admin/properties/{id}/edit', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@edit');
$router->post('/admin/properties/{id}/update', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@update');
$router->post('/admin/properties/{id}/destroy', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@destroy');
$router->get('/admin/properties/check-availability', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@checkAvailability');

// Admin Users
$router->get('/admin/users', 'App\\Http\\Controllers\\Admin\\UserController@index');
$router->get('/admin/users/create', 'App\\Http\\Controllers\\Admin\\UserController@create');
$router->post('/admin/users', 'App\\Http\\Controllers\\Admin\\UserController@store');
$router->get('/admin/users/{id}', 'App\\Http\\Controllers\\Admin\\UserController@show');
$router->get('/admin/users/{id}/edit', 'App\\Http\\Controllers\\Admin\\UserController@edit');
$router->post('/admin/users/{id}/update', 'App\\Http\\Controllers\\Admin\\UserController@update');
$router->post('/admin/users/{id}/destroy', 'App\\Http\\Controllers\\Admin\\UserController@destroy');

// Admin Leads/CRM
$router->get('/admin/leads', 'App\\Http\\Controllers\\Admin\\LeadController@index');
$router->get('/admin/leads/create', 'App\\Http\\Controllers\\Admin\\LeadController@create');
$router->post('/admin/leads', 'App\\Http\\Controllers\\Admin\\LeadController@store');
$router->get('/admin/leads/{id}', 'App\\Http\\Controllers\\Admin\\LeadController@show');
$router->get('/admin/leads/{id}/edit', 'App\\Http\\Controllers\\Admin\\LeadController@edit');
$router->post('/admin/leads/{id}/update', 'App\\Http\\Controllers\\Admin\\LeadController@update');
$router->post('/admin/leads/{id}/destroy', 'App\\Http\\Controllers\\Admin\\LeadController@destroy');
$router->post('/admin/leads/{id}/note', 'App\\Http\\Controllers\\Admin\\LeadController@addNote');
$router->post('/admin/leads/{id}/status', 'App\\Http\\Controllers\\Admin\\LeadController@updateStatus');

// Admin Bookings
$router->get('/admin/bookings', 'App\\Http\\Controllers\\Admin\\BookingController@index');
$router->get('/admin/bookings/create', 'App\\Http\\Controllers\\Admin\\BookingController@create');
$router->post('/admin/bookings', 'App\\Http\\Controllers\\Admin\\BookingController@store');
$router->get('/admin/bookings/{id}', 'App\\Http\\Controllers\\Admin\\BookingController@show');
$router->get('/admin/bookings/{id}/edit', 'App\\Http\\Controllers\\Admin\\BookingController@edit');
$router->post('/admin/bookings/{id}/update', 'App\\Http\\Controllers\\Admin\\BookingController@update');
$router->post('/admin/bookings/{id}/destroy', 'App\\Http\\Controllers\\Admin\\BookingController@destroy');
$router->post('/admin/bookings/{id}/payment', 'App\\Http\\Controllers\\Admin\\BookingController@processPayment');

// Admin Sites
$router->get('/admin/sites', 'App\\Http\\Controllers\\Admin\\SiteController@index');
$router->get('/admin/sites/create', 'App\\Http\\Controllers\\Admin\\SiteController@create');
$router->post('/admin/sites', 'App\\Http\\Controllers\\Admin\\SiteController@store');
$router->get('/admin/sites/{id}', 'App\\Http\\Controllers\\Admin\\SiteController@show');
$router->get('/admin/sites/{id}/edit', 'App\\Http\\Controllers\\Admin\\SiteController@edit');
$router->post('/admin/sites/{id}/update', 'App\\Http\\Controllers\\Admin\\SiteController@update');
$router->post('/admin/sites/{id}/destroy', 'App\\Http\\Controllers\\Admin\\SiteController@destroy');

// Admin Inquiries
$router->get('/admin/inquiries', 'App\\Http\\Controllers\\Admin\\InquiryController@index');
$router->get('/admin/inquiries/view/{id}', 'App\\Http\\Controllers\\Admin\\InquiryController@show');
$router->post('/admin/inquiries/update-status', 'App\\Http\\Controllers\\Admin\\InquiryController@updateStatus');
$router->post('/admin/inquiries/delete/{id}', 'App\\Http\\Controllers\\Admin\\InquiryController@delete');

// Admin Plots
$router->get('/admin/plots', 'App\\Http\\Controllers\\Admin\\PlotManagementController@index');
$router->get('/admin/plots/create', 'App\\Http\\Controllers\\Admin\\PlotManagementController@create');
$router->post('/admin/plots', 'App\\Http\\Controllers\\Admin\\PlotManagementController@store');
$router->get('/admin/plots/{id}', 'App\\Http\\Controllers\\Admin\\PlotManagementController@show');
$router->get('/admin/plots/{id}/edit', 'App\\Http\\Controllers\\Admin\\PlotManagementController@edit');
$router->post('/admin/plots/{id}/update', 'App\\Http\\Controllers\\Admin\\PlotManagementController@update');
$router->post('/admin/plots/{id}/destroy', 'App\\Http\\Controllers\\Admin\\PlotManagementController@destroy');
$router->get('/admin/plots/check-availability', 'App\\Http\\Controllers\\Admin\\PlotManagementController@checkAvailability');
$router->post('/admin/plots/{id}/update-status', 'App\\Http\\Controllers\\Admin\\PlotManagementController@updateStatus');

// Admin Testimonials
$router->get('/admin/testimonials', 'App\Http\Controllers\Admin\TestimonialsAdminController@index');
$router->get('/admin/testimonials/show/{id}', 'App\Http\Controllers\Admin\TestimonialsAdminController@show');
$router->post('/admin/testimonials/{id}/status', 'App\Http\Controllers\Admin\TestimonialsAdminController@updateStatus');
$router->post('/admin/testimonials/{id}/delete', 'App\Http\Controllers\Admin\TestimonialsAdminController@delete');

// Admin Location Management
$router->get('/admin/locations/states', 'App\Http\Controllers\Admin\LocationAdminController@index');
$router->get('/admin/locations/states/create', 'App\Http\Controllers\Admin\LocationAdminController@createState');
$router->post('/admin/locations/states/create', 'App\Http\Controllers\Admin\LocationAdminController@createState');
$router->get('/admin/locations/states/edit/{id}', 'App\Http\Controllers\Admin\LocationAdminController@editState');
$router->post('/admin/locations/states/edit/{id}', 'App\Http\Controllers\Admin\LocationAdminController@editState');
$router->get('/admin/locations/states/delete/{id}', 'App\Http\Controllers\Admin\LocationAdminController@deleteState');

$router->get('/admin/locations/districts', 'App\Http\Controllers\Admin\LocationAdminController@districts');
$router->get('/admin/locations/districts/create', 'App\Http\Controllers\Admin\LocationAdminController@createDistrict');
$router->post('/admin/locations/districts/create', 'App\Http\Controllers\Admin\LocationAdminController@createDistrict');
$router->get('/admin/locations/districts/edit/{id}', 'App\Http\Controllers\Admin\LocationAdminController@editDistrict');
$router->post('/admin/locations/districts/edit/{id}', 'App\Http\Controllers\Admin\LocationAdminController@editDistrict');
$router->get('/admin/locations/districts/delete/{id}', 'App\Http\Controllers\Admin\LocationAdminController@deleteDistrict');

$router->get('/admin/locations/colonies', 'App\Http\Controllers\Admin\LocationAdminController@colonies');
$router->get('/admin/locations/colonies/create', 'App\Http\Controllers\Admin\LocationAdminController@createColony');
$router->post('/admin/locations/colonies/create', 'App\Http\Controllers\Admin\LocationAdminController@createColony');
$router->get('/admin/locations/colonies/edit/{id}', 'App\Http\Controllers\Admin\LocationAdminController@editColony');
$router->post('/admin/locations/colonies/edit/{id}', 'App\Http\Controllers\Admin\LocationAdminController@editColony');
$router->get('/admin/locations/colonies/delete/{id}', 'App\Http\Controllers\Admin\LocationAdminController@deleteColony');

// Location API endpoints
$router->get('/admin/locations/api/districts/{state_id}', 'App\Http\Controllers\Admin\LocationAdminController@getDistrictsByState');
$router->get('/admin/locations/api/colonies/{district_id}', 'App\Http\Controllers\Admin\LocationAdminController@getColoniesByDistrict');

// Admin News/Blog
$router->get('/admin/news', 'App\\Http\\Controllers\\Admin\\NewsController@index');
$router->get('/admin/news/create', 'App\\Http\\Controllers\\Admin\\NewsController@create');
$router->post('/admin/news', 'App\\Http\\Controllers\\Admin\\NewsController@store');
$router->get('/admin/news/{id}/edit', 'App\\Http\\Controllers\\Admin\\NewsController@edit');
$router->post('/admin/news/{id}/update', 'App\\Http\\Controllers\\Admin\\NewsController@update');
$router->post('/admin/news/{id}/delete', 'App\\Http\\Controllers\\Admin\\NewsController@delete');

// Admin Campaigns
$router->get('/admin/campaigns', 'App\\Http\\Controllers\\Admin\\CampaignController@index');
$router->get('/admin/campaigns/create', 'App\\Http\\Controllers\\Admin\\CampaignController@create');
$router->post('/admin/campaigns/store', 'App\\Http\\Controllers\\Admin\\CampaignController@store');
$router->get('/admin/campaigns/{id}/edit', 'App\\Http\\Controllers\\Admin\\CampaignController@edit');
$router->post('/admin/campaigns/{id}/update', 'App\\Http\\Controllers\\Admin\\CampaignController@update');
$router->get('/admin/campaigns/{id}/delete', 'App\\Http\\Controllers\\Admin\\CampaignController@delete');
$router->get('/admin/campaigns/{id}/analytics', 'App\\Http\\Controllers\\Admin\\CampaignController@analytics');
$router->get('/admin/campaigns/{id}/launch', 'App\\Http\\Controllers\\Admin\\CampaignController@launch');

// Admin Gallery CRUD
$router->get('/admin/gallery', 'App\\Http\\Controllers\\Admin\\GalleryController@index');
$router->get('/admin/gallery/create', 'App\\Http\\Controllers\\Admin\\GalleryController@create');
$router->post('/admin/gallery', 'App\\Http\\Controllers\\Admin\\GalleryController@store');
$router->get('/admin/gallery/{id}/edit', 'App\\Http\\Controllers\\Admin\\GalleryController@edit');
$router->post('/admin/gallery/{id}/update', 'App\\Http\\Controllers\\Admin\\GalleryController@update');
$router->get('/admin/gallery/{id}/destroy', 'App\\Http\\Controllers\\Admin\\GalleryController@destroy');

// Admin Settings & System
$router->get('/admin/settings', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->post('/admin/settings', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@update');
$router->get('/admin/legal-pages', 'App\\Http\\Controllers\\Admin\\LegalPagesController@index');
$router->post('/admin/legal-pages/update-terms', 'App\\Http\\Controllers\\Admin\\LegalPagesController@updateTerms');
$router->post('/admin/legal-pages/update-privacy', 'App\\Http\\Controllers\\Admin\\LegalPagesController@updatePrivacy');
$router->get('/admin/layout-manager', 'App\\Http\\Controllers\\Admin\\LayoutController@layoutManager');
$router->post('/admin/layout-manager', 'App\\Http\\Controllers\\Admin\\LayoutController@updateLayoutSettings');
$router->get('/admin/ai-settings', 'App\\Http\\Controllers\\Admin\\AISettingsController@index');
$router->post('/admin/ai-settings/update-key', 'App\\Http\\Controllers\\Admin\\AISettingsController@updateApiKey');
$router->post('/admin/ai-settings/test-connection', 'App\\Http\\Controllers\\Admin\\AISettingsController@testConnection');
$router->post('/admin/ai-settings/generate-content', 'App\\Http\\Controllers\\Admin\\AISettingsController@generateSampleContent');
$router->post('/admin/ai-settings/clear-logs', 'App\\Http\\Controllers\\Admin\\AISettingsController@clearLogs');
$router->get('/admin/ai-settings/export-usage-report', 'App\\Http\\Controllers\\Admin\\AISettingsController@exportUsageReport');
$router->post('/admin/ai-settings/chat', 'App\\Http\\Controllers\\Admin\\AISettingsController@chat');

// Admin Stats & AJAX
$router->get('/admin/stats', 'App\\Http\\Controllers\\Admin\\AdminController@getStats');
$router->get('/admin/activities', 'App\\Http\\Controllers\\Admin\\AdminController@getRecentActivities');

// Admin Profile
$router->get('/admin/profile', 'App\\Http\\Controllers\\Admin\\AdminProfileController@index');
$router->post('/admin/profile', 'App\\Http\\Controllers\\Admin\\AdminProfileController@update');
$router->get('/admin/profile/security', 'App\\Http\\Controllers\\Admin\\AdminProfileController@security');
$router->post('/admin/profile/change-password', 'App\\Http\\Controllers\\Admin\\AdminProfileController@changePassword');

// ============================================================
// AI & SENIOR DEVELOPER
// ============================================================

$router->get('/ai-chat', 'App\\Http\\Controllers\\AIController@chat');
$router->get('/ai-chat-enhanced', 'App\\Http\\Controllers\\AIController@chatEnhanced');
$router->get('/ai-chat/popup', 'App\\Http\\Controllers\\AIController@chatPopup');
$router->get('/property-ai-chat', 'App\\Http\\Controllers\\AIController@propertyChat');
$router->get('/property-ai-chat/{id}', 'App\\Http\\Controllers\\AIController@propertyChat');
$router->post('/api/ai-chat', 'App\\Http\\Controllers\\AIController@apiChat');
$router->post('/api/save-lead', 'App\\Http\\Controllers\\AIController@saveLead');
$router->get('/api/lead-stats', 'App\\Http\\Controllers\\AIController@leadStats');
$router->get('/admin/ai-config', 'App\\Http\\Controllers\\AIController@configuration');
$router->post('/admin/test-ai-api', 'App\\Http\\Controllers\\AIController@testAPI');

$router->get('/senior-developer', 'App\\Http\\Controllers\\AIController@seniorDeveloper');
$router->get('/senior-developer/status', 'App\\Http\\Controllers\\AIController@seniorDeveloperStatus');
$router->post('/senior-developer/execute', 'App\\Http\\Controllers\\AIController@seniorDeveloperExecute');
$router->get('/senior-developer/logs', 'App\\Http\\Controllers\\AIController@seniorDeveloperLogs');
$router->get('/senior-developer/monitor', 'App\\Http\\Controllers\\AIController@seniorDeveloperMonitor');
$router->get('/senior-developer/dashboard', 'App\\Http\\Controllers\\AIController@seniorDeveloperDashboard');
$router->get('/senior-developer/unified', 'App\\Http\\Controllers\\AIController@seniorDeveloperUnified');
$router->post('/senior-developer/save-code', 'App\\Http\\Controllers\\AIController@saveCode');
$router->post('/senior-developer/run-code', 'App\\Http\\Controllers\\AIController@runCode');

// ============================================================
// API ROUTES
// ============================================================

// Gemini AI API
$router->post('/api/gemini/chat', 'Api\\GeminiApiController@chat');
$router->post('/api/gemini/generate', 'Api\\GeminiApiController@generateContent');
$router->post('/api/gemini/recommendations', 'Api\\GeminiApiController@propertyRecommendations');
$router->post('/api/gemini/support', 'Api\\GeminiApiController@customerSupport');
$router->post('/api/gemini/market-analysis', 'Api\\GeminiApiController@marketAnalysis');
$router->post('/api/gemini/social-media', 'Api\\GeminiApiController@socialMediaContent');
$router->get('/api/gemini/test', 'Api\\GeminiApiController@testConnection');
$router->get('/api/gemini/status', 'Api\\GeminiApiController@getStatus');

// Notifications API
$router->get('/api/notifications', 'NotificationController@getNotifications');
$router->post('/api/notifications/mark-read', 'NotificationController@markAsRead');
$router->get('/api/notifications/unread-count', 'NotificationController@getUnreadCount');
$router->get('/api/popups', 'NotificationController@getPopups');
$router->post('/api/popups/dismiss', 'NotificationController@dismissPopup');
$router->post('/admin/notifications/create', 'NotificationController@createNotification');
$router->post('/admin/popups/create', 'NotificationController@createPopup');

// Monitoring
$router->get('/monitoring', 'MonitoringController@dashboard');

// Virtual Tour Routes
$router->get('/virtual-tour', 'Tech\VirtualTourController@index');
$router->get('/virtual-tour/{id}', 'Tech\VirtualTourController@show');

// Meeting Scheduler Routes  
$router->get('/schedule-meeting', 'Front\PageController@scheduleMeeting');
$router->post('/schedule-meeting', 'Front\PageController@handleScheduleMeeting');

// Include additional API routes
if (file_exists(__DIR__ . '/api.php')) {
    require_once __DIR__ . '/api.php';
}


// MLM Management Routes
$router->get('/admin/mlm', 'App\Http\Controllers\Admin\MLMController@index');
$router->get('/admin/mlm/associates', 'App\Http\Controllers\Admin\MLMController@associates');
$router->get('/admin/mlm/associates/create', 'App\Http\Controllers\Admin\MLMController@createAssociate');
$router->post('/admin/mlm/associates/create', 'App\Http\Controllers\Admin\MLMController@createAssociate');
$router->get('/admin/mlm/commission', 'App\Http\Controllers\Admin\MLMController@commission');
$router->get('/admin/mlm/network', 'App\Http\Controllers\Admin\MLMController@network');
$router->get('/admin/mlm/payouts', 'App\Http\Controllers\Admin\MLMController@payouts');

// Projects Management Routes
$router->get('/admin/projects', 'App\Http\Controllers\Admin\ProjectsAdminController@index');
$router->get('/admin/projects/create', 'App\Http\Controllers\Admin\ProjectsAdminController@create');
$router->post('/admin/projects/store', 'App\Http\Controllers\Admin\ProjectsAdminController@store');
$router->get('/admin/projects/edit/{id}', 'App\Http\Controllers\Admin\ProjectsAdminController@edit');
$router->post('/admin/projects/update/{id}', 'App\Http\Controllers\Admin\ProjectsAdminController@update');
$router->get('/admin/projects/view/{id}', 'App\Http\Controllers\Admin\ProjectsAdminController@view');
$router->get('/admin/projects/images/{id}', 'App\Http\Controllers\Admin\ProjectsAdminController@images');
$router->get('/admin/projects/delete/{id}', 'App\Http\Controllers\Admin\ProjectsAdminController@delete');
$router->post('/admin/projects/status/{id}', 'App\Http\Controllers\Admin\ProjectsAdminController@status');

// Commission Management Routes
$router->get('/admin/commission', 'App\Http\Controllers\Admin\CommissionAdminController@index');
$router->get('/admin/commission/rules', 'App\Http\Controllers\Admin\CommissionAdminController@rules');
$router->get('/admin/commission/create-rule', 'App\Http\Controllers\Admin\CommissionAdminController@createRule');
$router->post('/admin/commission/create-rule', 'App\Http\Controllers\Admin\CommissionAdminController@createRule');
$router->get('/admin/commission/edit-rule/{id}', 'App\Http\Controllers\Admin\CommissionAdminController@editRule');
$router->post('/admin/commission/edit-rule/{id}', 'App\Http\Controllers\Admin\CommissionAdminController@editRule');
$router->get('/admin/commission/calculations', 'App\Http\Controllers\Admin\CommissionAdminController@calculations');
$router->get('/admin/commission/payments', 'App\Http\Controllers\Admin\CommissionAdminController@payments');
$router->get('/admin/commission/reports', 'App\Http\Controllers\Admin\CommissionAdminController@reports');

// Authentication Routes (Note: /login, /register, /logout already defined earlier)
$router->get('/forgot-password', 'App\Http\Controllers\AuthController@forgotPassword');
$router->post('/forgot-password', 'App\Http\Controllers\AuthController@forgotPassword');
$router->get('/reset-password', 'App\Http\Controllers\AuthController@resetPassword');
$router->post('/reset-password', 'App\Http\Controllers\AuthController@resetPassword');
$router->get('/verify-email', 'App\Http\Controllers\AuthController@verifyEmail');
$router->post('/verify-email', 'App\Http\Controllers\AuthController@verifyEmail');

// Customer Routes
$router->get('/customer', 'App\Http\Controllers\CustomerController@index');
$router->get('/customer/dashboard', 'App\Http\Controllers\CustomerController@index');
$router->get('/customer/profile', 'App\Http\Controllers\CustomerController@profile');
$router->post('/customer/profile', 'App\Http\Controllers\CustomerController@profile');
$router->get('/customer/wishlist', 'App\Http\Controllers\CustomerController@wishlist');
$router->get('/customer/inquiries', 'App\Http\Controllers\CustomerController@inquiries');
$router->get('/customer/documents', 'App\Http\Controllers\CustomerController@documents');
$router->get('/customer/settings', 'App\Http\Controllers\CustomerController@settings');
$router->get('/customer/property-history', 'App\Http\Controllers\CustomerController@propertyHistory');
$router->get('/customer/payments', 'App\Http\Controllers\CustomerController@payments');
$router->get('/customer/notifications', 'App\Http\Controllers\CustomerController@notifications');


// Property Routes (Note: /properties handled by Front\PageController@properties)
$router->get('/properties/search', 'App\Http\Controllers\PropertyController@search');
$router->get('/colonies', 'App\Http\Controllers\PropertyController@colonies');
$router->get('/colonies/{id}', 'App\Http\Controllers\PropertyController@colony');
$router->get('/resell/{id}', 'App\Http\Controllers\PropertyController@resellDetail');
$router->get('/submit-property', 'App\Http\Controllers\PropertyController@submitProperty');

// Payment Routes
$router->get('/payment', 'App\Http\Controllers\PaymentController@index');
$router->get('/payment/initiate', 'App\Http\Controllers\PaymentController@initiate');
$router->post('/payment/initiate', 'App\Http\Controllers\PaymentController@initiate');
$router->post('/payment/process', 'App\Http\Controllers\PaymentController@process');
$router->get('/payment/success', 'App\Http\Controllers\PaymentController@success');
$router->get('/payment/failure', 'App\Http\Controllers\PaymentController@failure');
$router->post('/payment/webhook', 'App\Http\Controllers\PaymentController@webhook');
$router->get('/payment/history', 'App\Http\Controllers\PaymentController@history');
$router->get('/payment/plans', 'App\Http\Controllers\PaymentController@plans');
$router->get('/payment/emi-calculator', 'App\Http\Controllers\PaymentController@emiCalculator');
$router->post('/payment/emi-calculator', 'App\Http\Controllers\PaymentController@emiCalculator');
$router->get('/payment/refund', 'App\Http\Controllers\PaymentController@refund');
$router->post('/payment/refund', 'App\Http\Controllers\PaymentController@refund');
$router->get('/payment/settings', 'App\Http\Controllers\PaymentController@settings');
$router->post('/payment/settings', 'App\Http\Controllers\PaymentController@settings');

// Missing Routes
$router->get('/privacy-policy', function() { include __DIR__ . '/../app/views/pages/privacy-policy.php'; });
$router->get('/terms', function() { include __DIR__ . '/../app/views/pages/terms.php'; });
$router->get('/inquiry', function() { include __DIR__ . '/../app/views/pages/inquiry.php'; });
$router->post('/inquiry', function() { 
    header('Location: /inquiry?success=1'); 
    exit; 
});


// Standalone Pages
$router->get('/plots', function() { include __DIR__ . '/../app/views/pages/plots.php'; });

// Newsletter Subscribe
$router->post('/subscribe', 'Api\NewsletterController@subscribe');

// Service Interest
$router->post('/service-interest', 'Front\PageController@serviceInterest');

// User Authentication (Customer)
$router->get('/user/logout', 'Auth\CustomerAuthController@logout');
$router->get('/user/dashboard', 'Front\UserController@dashboard');
$router->get('/user/properties', 'Front\UserController@myProperties');
$router->get('/user/inquiries', 'Front\UserController@myInquiries');
$router->get('/user/profile', 'Front\UserController@profile');
$router->post('/user/profile', 'Front\UserController@profile');