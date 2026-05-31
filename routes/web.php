<?php
// Web Routes - APS Dream Home
// Clean, deduplicated route definitions

// IMPORTANT: Router is already initialized in public/index.php
// Do NOT create new Router instance here - use the existing $router

// ============================================================
// CONTROLLER INCLUDES (Fix for route loading issues)
// ============================================================

// MLM Tree Controller
if (file_exists(__DIR__ . '/../app/Http/Controllers/MLMTreeController.php')) {
    require_once __DIR__ . '/../app/Http/Controllers/MLMTreeController.php';
}

// SMS Controller
if (file_exists(__DIR__ . '/../app/Http/Controllers/SMSController.php')) {
    require_once __DIR__ . '/../app/Http/Controllers/SMSController.php';
}

// God Mode Controller
if (file_exists(__DIR__ . '/../app/Http/Controllers/Admin/GodModeController.php')) {
    require_once __DIR__ . '/../app/Http/Controllers/Admin/GodModeController.php';
}

// ============================================================
// PUBLIC FRONTEND PAGES
// ============================================================

// Agent System Routes
$router->get('/auto_orchestrator', function () {
    $file = __DIR__ . '/../auto_orchestrator.php';
    if (file_exists($file)) { include $file; }
});
$router->get('/orchestrator', function () {
    $file = __DIR__ . '/../auto_orchestrator.php';
    if (file_exists($file)) { include $file; }
});
$router->get('/agent_dashboard', function () {
    $file = __DIR__ . '/../agent_dashboard.php';
    if (file_exists($file)) { include $file; }
});
$router->get('/agents', function () {
    $file = __DIR__ . '/../agent_dashboard.php';
    if (file_exists($file)) { include $file; }
});
$router->get('/project_health_check', function () {
    $file = __DIR__ . '/../project_health_check.php';
    if (file_exists($file)) { include $file; }
});
$router->get('/project_health', function () {
    $file = __DIR__ . '/../project_health_check.php';
    if (file_exists($file)) { include $file; }
});
$router->get('/health', function () {
    $file = __DIR__ . '/../project_health_check.php';
    if (file_exists($file)) { include $file; }
});

// Home
$router->get('/', 'Front\\PageController@home');

// Redirect /public to /
$router->get('/public', function () {
    header('Location: /', true, 301);
    exit;
});
$router->get('/public/', function () {
    header('Location: /', true, 301);
    exit;
});

// Static Pages
$router->get('/about', 'Front\\PageController@about');
$router->get('/contact', 'Front\\PageController@contact');
$router->post('/contact', 'Front\\PageController@contact');
$router->get('/services', 'Front\\PageController@services');
$router->get('/team', 'Front\\PageController@team');
$router->get('/our-team', function () {
    header('Location: ' . BASE_URL . '/team', true, 301);
    exit;
});
$router->get('/testimonials', 'Front\\PageController@testimonials');
$router->get('/faq', 'Front\\PageController@faq');
$router->get('/faqs', 'Front\\PageController@faqs');
$router->get('/home', 'Front\\PageController@home');
$router->get('/sitemap.xml', function () {
    $controller = new \App\Http\Controllers\Api\SitemapController();
    $controller->generate();
});
$router->get('/robots.txt', function () {
    $file = __DIR__ . '/../robots.txt';
    if (file_exists($file)) {
        header('Content-Type: text/plain');
        readfile($file);
        exit;
    }
});
$router->get('/sitemap', 'Front\\PageController@sitemap');
$router->get('/privacy', 'Front\\PageController@privacy');
$router->get('/news', 'Front\\PageController@news');
$router->get('/blog', 'Front\\PageController@blog');
$router->get('/blog/{slug}', 'Front\\PageController@blogPost');
$router->get('/gallery', 'Front\\PageController@gallery');
$router->get('/resell', 'Front\\PageController@resell');
$router->get('/careers', 'Front\PageController@careers');
$router->get('/coming-soon', 'Front\\PageController@comingSoon');
$router->get('/become-associate', function () {
    @session_start();
    $referral_code = 'APS2025COMP';
    $referrer_name = 'APS Dream Home';
    try {
        $db = \App\Core\Database\Database::getInstance();
        $row = $db->fetchOne("SELECT referral_code, name FROM users WHERE referral_code = ? LIMIT 1", ['APS2025COMP']);
        if ($row) {
            $referral_code = $row['referral_code'];
            $referrer_name = $row['name'];
        } else {
            $admin = $db->fetchOne("SELECT referral_code, name FROM users WHERE (user_type = 'admin' OR role = 'super_admin') AND referral_code IS NOT NULL AND referral_code != '' LIMIT 1");
            if ($admin) {
                $referral_code = $admin['referral_code'];
                $referrer_name = $admin['name'];
            }
        }
    } catch (\Exception $e) {}
    $base = BASE_URL;
    $isLoggedIn = !empty($_SESSION['user_id']);
    $loggedInReferralCode = $isLoggedIn ? ($_SESSION['referral_code'] ?? '') : '';
    $userName = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
    include __DIR__ . '/../app/views/pages/become_associate.php';
});
$router->get('/become_associate', function () {
    header('Location: ' . BASE_URL . '/become-associate', true, 301);
    exit;
});

// Support
$router->get('/support', 'Front\\SupportController@index');
$router->post('/support', 'Front\\SupportController@store');
$router->get('/whatsapp-chat', 'Front\\PageController@whatsappChat');

// Google OAuth
$router->get('/auth/google', 'Auth\\GoogleAuthController@googleRedirect');
$router->get('/auth/google/redirect', 'Auth\\GoogleAuthController@googleRedirect');
$router->get('/auth/google/callback', 'Auth\\GoogleAuthController@callback');
$router->get('/auth/google/role-selection', 'Auth\\GoogleAuthController@roleSelection');
$router->post('/auth/google/complete-registration', 'Auth\\GoogleAuthController@completeRegistration');

// Facebook Auth
$router->get('/auth/facebook', 'Auth\FacebookAuthController@redirectToProvider');
$router->get('/auth/facebook/callback', 'Auth\FacebookAuthController@callback');

// LinkedIn Auth
$router->get('/auth/linkedin', function() {
    $_SESSION['error'] = 'LinkedIn login coming soon. Use Google or Facebook.';
    header('Location: ' . BASE_URL . '/login');
    exit;
});

// Quick Auth (for casual visitors, booking, etc.)
$router->post('/auth/quick-register', 'Auth\\QuickAuthController@quickRegister');
$router->post('/auth/request-referral-code', 'Auth\\QuickAuthController@requestReferralCode');
$router->post('/auth/auto-generate-user', 'Auth\\QuickAuthController@autoGenerateUser');

// Visitor Tracking & Lead Capture
$router->post('/track/page-view', 'VisitorTrackingController@trackPageView');
$router->post('/track/incomplete-registration', 'VisitorTrackingController@trackIncompleteRegistration');
$router->post('/track/interest', 'VisitorTrackingController@trackInterest');
$router->get('/admin/visitor-stats', 'VisitorTrackingController@getVisitorStats');

// Lead Follow-up System
$router->post('/admin/send-follow-ups', 'Admin\\LeadFollowUpController@sendFollowUps');
$router->get('/admin/follow-up-stats', 'Admin\\LeadFollowUpController@getFollowUpStats');
$router->get('/user-ai-suggestions', 'Front\\PageController@userAiSuggestions');
$router->get('/user/investments', 'Front\\PageController@userInvestments');
$router->get('/builder-registration', 'Front\\PageController@builderRegistration');
$router->post('/builder-registration', 'Front\\PageController@builderRegistration');

// Free Tools
$router->get('/stamp-duty-calculator', 'Front\\PageController@stampDutyCalculator');
$router->get('/plot-size-converter', 'Front\\PageController@plotSizeConverter');
$router->get('/home-loan-eligibility', 'Front\\PageController@homeLoanEligibility');
$router->get('/documents', 'Front\\PageController@documentGallery');
$router->get('/documents/download/{id}', 'Front\\PageController@downloadDocument');
$router->get('/property-valuation', 'Front\\PageController@propertyValuation');
$router->get('/tools-hub', 'Front\\PageController@toolsHub');
$router->get('/rent-vs-buy', 'Front\\PageController@rentVsBuy');
$router->get('/sip-vs-realestate', 'Front\\PageController@sipVsRealestate');
$router->get('/capital-gains-calculator', 'Front\\PageController@capitalGains');
$router->get('/gst-calculator', 'Front\\PageController@gstCalculator');
$router->get('/construction-cost-estimator', 'Front\\PageController@constructionCostEstimator');
$router->get('/rental-yield-calculator', 'Front\\PageController@rentalYieldCalculator');
$router->get('/property-tax-calculator', 'Front\\PageController@propertyTaxCalculator');
$router->get('/rera-lookup', 'Front\\PageController@reraLookup');

// MLM & AI Dashboard Routes
$router->get('/mlm-dashboard', 'MLM\MLMDashboardController@dashboard');
$router->get('/ai-dashboard', 'AIDashboardController@index');
// Career Pages
$router->get('/careers/apply', 'Front\\PageController@careerApply');
$router->post('/careers/apply', 'Front\\PageController@submitCareerApplication');
$router->get('/career_apply', function () {
    header('Location: ' . BASE_URL . '/careers/apply', true, 301);
    exit;
});
$router->get('/careers/jobs', 'Front\\PageController@careerJobs');
$router->get('/careers/job/{id}', 'Front\\PageController@careerJobDetails');

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
$router->get('/projects/budha-city', 'Front\\PageController@budhaCity');

// Dynamic Colony Pages (single-template, DB-driven)
$router->get('/colony/{slug}', 'Front\\PageController@colonyDetail');
$router->get('/plots', 'Front\\PlotController@index');
$router->get('/plot/{id}', 'Front\\PlotController@show');
$router->get('/plot/{id}/book', 'Front\\PlotController@bookPlot');
$router->post('/plot/book', 'Front\\PlotController@storeBooking');
$router->get('/booking/{id}/confirmation', 'Front\\PlotController@bookingConfirmation');
$router->get('/booking/{id}/pay', 'Front\\PlotController@payBooking');
$router->post('/booking/{id}/pay', 'Front\\PlotController@processPayment');
$router->get('/booking/{id}/receipt', 'Front\\PlotController@receipt');
$router->get('/colony/{slug}/plots', 'Front\\PlotController@colonyPlots');
$router->get('/api/plots/by-colony/{colonyId}', 'Front\\PlotController@apiByColony');
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
$router->get('/properties/submit', 'Front\\PageController@propertySubmit');

// Form Handlers
$router->post('/quick-inquiry', 'Front\\PageController@handleQuickInquiry');

// AI Bot
$router->post('/whatsapp-webhook', 'Front\\AIBotController@whatsappWebhook');

// Admin Services
$router->get('/admin/services', 'App\\Http\\Controllers\\Admin\\ServiceController@index');
$router->get('/admin/services/view/{id}', 'App\\Http\\Controllers\\Admin\\ServiceController@detail');
$router->post('/admin/services/update-status', 'App\\Http\\Controllers\\Admin\\ServiceController@updateStatus');

// Admin User Properties
$router->get('/admin/user-properties', 'App\\Http\\Controllers\\Admin\\UserPropertyController@index');
$router->get('/admin/user-properties/verify/{id}', 'App\\Http\\Controllers\\Admin\\UserPropertyController@verify');
$router->post('/admin/user-properties/action', 'App\\Http\\Controllers\\Admin\\UserPropertyController@action');

// Admin API Keys Management
$router->get('/admin/api-keys', 'App\\Http\\Controllers\\Admin\\ApiKeyController@index');
$router->get('/admin/api-keys/guide', 'App\\Http\\Controllers\\Admin\\ApiKeyController@guide');
$router->get('/admin/api-keys/create', 'App\\Http\\Controllers\\Admin\\ApiKeyController@create');
$router->post('/admin/api-keys/store', 'App\\Http\\Controllers\\Admin\\ApiKeyController@store');
$router->get('/admin/api-keys/edit/{id}', 'App\\Http\\Controllers\\Admin\\ApiKeyController@edit');
$router->post('/admin/api-keys/update/{id}', 'App\\Http\\Controllers\\Admin\\ApiKeyController@update');
$router->get('/admin/api-keys/delete/{id}', 'App\\Http\\Controllers\\Admin\\ApiKeyController@delete');
$router->get('/admin/api-keys/toggle/{id}', 'App\\Http\\Controllers\\Admin\\ApiKeyController@toggle');
$router->get('/admin/api-keys/test/{id}', 'App\Http\Controllers\Admin\ApiKeyController@test');

// Admin AI Chatbot Training
$router->get('/admin/ai-training', 'App\\Http\\Controllers\\Admin\\AdminAIController@training');

// Admin WhatsApp Integration
$router->get('/admin/whatsapp-integration', function () {
    require_once __DIR__ . '/../app/views/admin/whatsapp_integration.php';
});

// Admin WhatsApp Web (QR scan - new)
$router->get('/admin/whatsapp-web', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    require_once __DIR__ . '/../app/views/admin/whatsapp-web/index.php';
});
$router->get('/admin/whatsapp-web/manage', function () {
    header('Location: http://localhost:3001');
    exit;
});

// Missing frontend routes (from header/footer links)
$router->get('/financial-services', 'Front\\PageController@financialServices');
$router->get('/interior-design', 'Front\\PageController@interiorDesign');
$router->get('/construction-services', 'Front\\PageController@constructionServices');
$router->post('/construction-services/inquiry', 'Front\\PageController@constructionInquiry');
$router->get('/legal/terms-conditions', 'Front\\PageController@legalTermsConditions');
$router->get('/legal/services', 'Front\\PageController@legalServices');
$router->get('/legal/documents', 'Front\\PageController@legalDocuments');
$router->get('/user/edit-profile', 'Front\\PageController@userEditProfile');
$router->get('/user/logout', 'Auth\\CustomerAuthController@logout');
$router->get('/user/dashboard', 'Front\\UserController@dashboard');
$router->get('/user/properties', 'Front\\UserController@myProperties');
$router->get('/user/bookings', 'Front\\UserController@userBookings');
$router->get('/user/inquiries', 'Front\\UserController@myInquiries');
$router->get('/user/profile', 'Front\\UserController@profile');
$router->post('/user/profile', 'Front\\UserController@updateProfile');
$router->get('/user/bank-details', 'Front\\UserController@bankDetails');
$router->post('/user/bank-details/save', 'Front\\UserController@saveBankDetails');
$router->get('/user/network', function () {
    include __DIR__ . '/../app/views/pages/user_network.php';
});
$router->get('/news/view/{id}', 'Front\\PageController@newsView');
$router->get('/property/{id}', 'Front\\PageController@propertyDetails');
$router->post('/property/review', 'Front\\PageController@reviewSubmit');
$router->get('/listing/{id}', 'Front\\PageController@userPropertyDetail');
$router->post('/property/inquire', 'Front\\PageController@propertyInquiry');
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

// Lead Scoring Routes (API)
$router->get('/api/leads/{id}/score-details', 'App\Http\Controllers\Admin\LeadScoringController@getScoreDetails');

// Site Visit Routes
$router->get('/admin/visits', 'App\Http\Controllers\Admin\VisitController@index');
$router->get('/admin/visits/calendar', 'App\Http\Controllers\Admin\VisitController@calendar');
$router->get('/admin/visits/create', 'App\Http\Controllers\Admin\VisitController@create');
$router->post('/admin/visits/store', 'App\Http\Controllers\Admin\VisitController@store');
$router->get('/admin/visits/{id}', 'App\Http\Controllers\Admin\VisitController@show');
$router->get('/admin/visits/{id}/edit', 'App\Http\Controllers\Admin\VisitController@edit');
$router->post('/admin/visits/{id}/update', 'App\Http\Controllers\Admin\VisitController@update');
$router->post('/admin/visits/{id}/destroy', 'App\Http\Controllers\Admin\VisitController@destroy');
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

// Agent Auth
$router->get('/agent/register', 'Auth\\AgentAuthController@register');
$router->post('/agent/register', 'Auth\\AgentAuthController@handleRegister');
$router->get('/agent/login', 'Auth\\AgentAuthController@login');
$router->post('/agent/login', 'Auth\\AgentAuthController@authenticate');
$router->get('/agent/logout', 'Auth\\AgentAuthController@logout');
$router->get('/agent/dashboard', 'Agent\\AgentDashboardController@index');
$router->get('/agent/leads', 'Agent\\AgentDashboardController@leads');
$router->get('/agent/properties', 'Agent\\AgentDashboardController@properties');
$router->get('/agent/commissions', 'Agent\\AgentDashboardController@commissions');
$router->get('/agent/profile', 'Agent\\AgentDashboardController@profile');

// Associate Auth
$router->get('/associate/register', 'Auth\\AssociateAuthController@associateRegister');
$router->post('/associate/register', 'Auth\\AssociateAuthController@handleAssociateRegister');
$router->get('/associate/login', 'Auth\\AssociateAuthController@associateLogin');
$router->post('/associate/login', 'Auth\\AssociateAuthController@authenticateAssociate');
$router->get('/associate/logout', 'Auth\\AssociateAuthController@logout');
$router->get('/associate/dashboard', 'AssociateController@dashboard');
$router->get('/associate/add-property', 'AssociateController@addProperty');
$router->get('/associate/leads', 'AssociateController@leads');
$router->get('/associate/commissions', 'AssociateController@commissions');
$router->get('/associate/properties', 'AssociateController@properties');
$router->get('/associate/sold', 'AssociateController@sold');
$router->get('/associate/pending', 'AssociateController@pending');
$router->get('/associate/profile', 'AssociateController@profile');
$router->get('/associate/genealogy', 'MLMTreeController@genealogy');
$router->get('/associate/wallet', 'WalletController@associateWallet');
$router->get('/associate/bank-details', 'WalletController@bankAccounts');
$router->get('/associate/settings', 'AssociateController@settings');
$router->get('/associate/mlm-plan', 'AssociateController@mlmPlan');
$router->get('/associate/list-property', 'AssociateController@listProperty');
$router->post('/associate/list-property/submit', 'AssociateController@submitProperty');

// Associate Leads sub-routes
$router->get('/associate/leads/add', 'AssociateController@leads');
$router->get('/associate/leads/all', 'AssociateController@leads');
$router->get('/associate/commissions/history', 'AssociateController@commissions');
$router->get('/associate/wallet/withdraw', 'WalletController@withdrawal');
$router->get('/associate/network/tree', 'MLMTreeController@tree');
$router->get('/associate/team', 'AssociateController@team');
$router->get('/associate/team/add', 'AssociateController@team');
$router->get('/associate/team/performance', 'AssociateController@team');

// Associate Exports
$router->get('/associate/export/my-earnings', 'Associate\ExportController@myEarnings');
$router->get('/associate/export/active-team', 'Associate\ExportController@activeTeam');
$router->get('/associate/export/my-payouts', 'Associate\ExportController@myPayouts');
$router->get('/associate/export/downline', 'Associate\ExportController@downline');
$router->get('/associate/export/new-directs', 'Associate\ExportController@newDirects');
$router->get('/associate/export/plot-sales', 'Associate\ExportController@plotSales');
$router->get('/associate/export/registry', 'Associate\ExportController@registry');

// Farmer Management
$router->get('/farmers', 'User\\FarmerController@index');
$router->get('/farmers/list', 'User\\FarmerController@list');
$router->get('/farmers/create', 'User\\FarmerController@create');
$router->post('/farmers', 'User\\FarmerController@store');
$router->get('/farmers/search', 'User\\FarmerController@search');
$router->get('/farmers/{id}', 'User\\FarmerController@show');
$router->get('/farmers/{id}/edit', 'User\\FarmerController@edit');
$router->post('/farmers/{id}/update', 'User\\FarmerController@update');
$router->post('/farmers/{id}/delete', 'User\\FarmerController@delete');

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
$router->get('/employee/user-properties', 'Employee\\EmployeeController@userProperties');
$router->post('/employee/user-properties/action', 'Employee\\EmployeeController@updatePropertyStatus');

// Employee Role-specific Dashboards
$router->get('/employee/ca-dashboard', 'Employee\\CAController@dashboard');
$router->post('/employee/ca/invoice/process', 'Employee\\CAController@processInvoice');
$router->post('/employee/ca/tax-compliance', 'Employee\\CAController@updateTaxCompliance');
$router->post('/employee/ca/budget', 'Employee\\CAController@updateBudget');
$router->post('/employee/ca/financial-report', 'Employee\\CAController@generateFinancialReport');

$router->get('/employee/hr-dashboard', 'Employee\\HRManagerController@dashboard');
$router->post('/employee/hr/payroll', 'Employee\\HRManagerController@processPayroll');
$router->post('/employee/hr/review', 'Employee\\HRManagerController@scheduleReview');
$router->post('/employee/hr/application', 'Employee\\HRManagerController@processApplication');

$router->get('/employee/land-dashboard', 'Employee\\LandManagerController@dashboard');
$router->post('/employee/land/site-visit', 'Employee\\LandManagerController@scheduleSiteVisit');
$router->post('/employee/land/acquisition', 'Employee\\LandManagerController@updateAcquisition');
$router->post('/employee/land/complete-visit', 'Employee\\LandManagerController@completeSiteVisit');
$router->post('/employee/land/documentation', 'Employee\\LandManagerController@updatePropertyDocumentation');
$router->post('/employee/land/report', 'Employee\\LandManagerController@generateLandReport');

$router->get('/employee/legal-dashboard', 'Employee\\LegalAdvisorController@dashboard');
$router->post('/employee/legal/review-document', 'Employee\\LegalAdvisorController@reviewDocument');
$router->post('/employee/legal/compliance', 'Employee\\LegalAdvisorController@updateComplianceTask');
$router->post('/employee/legal/dispute', 'Employee\\LegalAdvisorController@handleDispute');
$router->post('/employee/legal/template', 'Employee\\LegalAdvisorController@createDocumentTemplate');
$router->post('/employee/legal/report', 'Employee\\LegalAdvisorController@generateLegalReport');

$router->get('/employee/telecalling-dashboard', 'Employee\\TelecallingController@dashboard');
$router->post('/employee/telecalling/log-call', 'Employee\\TelecallingController@logCall');
$router->get('/employee/telecalling/lead/{id}', 'Employee\\TelecallingController@getLeadDetails');
$router->get('/employee/telecalling/script/{id}', 'Employee\\TelecallingController@getRecommendedScript');
$router->get('/employee/telecalling/followups', 'Employee\\TelecallingController@getTodayFollowUps');
$router->post('/employee/telecalling/complete-followup', 'Employee\\TelecallingController@completeFollowUp');

$router->get('/employee/dashboard-overview', 'Employee\\EmployeeDashboardController@dashboard');
$router->post('/employee/dashboard/update-task-status', 'Employee\\EmployeeDashboardController@updateTaskStatus');

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
$router->get('/admin/dashboard/agent', 'App\\Http\\Controllers\\RoleBasedDashboardController@agent');
$router->get('/admin/dashboard/builder', 'App\\Http\\Controllers\\RoleBasedDashboardController@builder');
$router->get('/admin/dashboard/ceo', 'App\\Http\\Controllers\\RoleBasedDashboardController@ceo');
$router->get('/admin/dashboard/cfo', 'App\\Http\\Controllers\\RoleBasedDashboardController@cfo');
$router->get('/admin/dashboard/cto', 'App\\Http\\Controllers\\RoleBasedDashboardController@cto');
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

// AI Aggregator Trigger Route
$router->post('/admin/ai-aggregator/fetch', 'App\\Http\\Controllers\\Admin\\AIAggregatorController@triggerFetch');

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
$router->get('/admin/leads/status', 'App\\Http\\Controllers\\Admin\\LeadController@status');
$router->get('/admin/leads/followups', 'App\\Http\\Controllers\\Admin\\LeadController@followups');
$router->get('/admin/leads/import', 'App\\Http\\Controllers\\Admin\\LeadController@import');
$router->get('/admin/leads/analysis', 'App\\Http\\Controllers\\Admin\\LeadController@analysis');
$router->get('/admin/leads/{id}', 'App\\Http\\Controllers\\Admin\\LeadController@show');
$router->get('/admin/leads/{id}/edit', 'App\\Http\\Controllers\\Admin\\LeadController@edit');
$router->post('/admin/leads/{id}/update', 'App\\Http\\Controllers\\Admin\\LeadController@update');
$router->post('/admin/leads/{id}/destroy', 'App\\Http\\Controllers\\Admin\\LeadController@destroy');
$router->post('/admin/leads/{id}/note', 'App\\Http\\Controllers\\Admin\\LeadController@addNote');
$router->post('/admin/leads/{id}/status', 'App\\Http\\Controllers\\Admin\\LeadController@updateStatus');

// Lead Scoring Dashboard
$router->get('/admin/leads/scoring', 'App\\Http\\Controllers\\Admin\\LeadScoringController@index');
$router->get('/admin/leads/scoring/show/{id}', 'App\\Http\\Controllers\\Admin\\LeadScoringController@show');
$router->post('/admin/leads/scoring/process-all', 'App\\Http\\Controllers\\Admin\\LeadScoringController@processAll');
$router->post('/admin/leads/scoring/auto-assign', 'App\\Http\\Controllers\\Admin\\LeadScoringController@autoAssign');
$router->post('/admin/leads/scoring/rescore/{id}', 'App\\Http\\Controllers\\Admin\\LeadScoringController@rescore');
$router->get('/admin/leads/scoring/export', 'App\\Http\\Controllers\\Admin\\LeadScoringController@export');

// Plot Development Cost Calculator
$router->get('/admin/plot-costs', 'App\\Http\\Controllers\\Admin\\PlotCostController@index');
$router->get('/admin/plot-costs/colony/{id}', 'App\\Http\\Controllers\\Admin\\PlotCostController@colony');
$router->post('/admin/plot-costs/add-cost', 'App\\Http\\Controllers\\Admin\\PlotCostController@addCost');
$router->post('/admin/plot-costs/calculate', 'App\\Http\\Controllers\\Admin\\PlotCostController@calculateAll');
$router->get('/admin/plot-costs/report/{id}', 'App\\Http\\Controllers\\Admin\\PlotCostController@report');

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
$router->get('/admin/inventory', 'App\\Http\\Controllers\\Admin\\SiteController@inventory');
$router->get('/admin/sites/inventory', 'App\\Http\\Controllers\\Admin\\SiteController@inventory');

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
$router->post('/admin/plots/bulk-price-update', 'App\\Http\\Controllers\\Admin\\PlotManagementController@bulkPriceUpdate');
$router->get('/admin/plots/api/price-history/{plotId}', 'App\\Http\\Controllers\\Admin\\PlotManagementController@apiPriceHistory');

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

// Admin Blog Management
$router->get('/admin/blog', 'App\\Http\\Controllers\\Admin\\BlogController@index');
$router->get('/admin/blog/create', 'App\\Http\\Controllers\\Admin\\BlogController@create');

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

// Admin Menu Permissions Management (RBAC)
$router->get('/admin/menu-permissions', 'App\\Http\\Controllers\\Admin\\AdminMenuPermissionController@index');
$router->post('/admin/menu-permissions/update-role', 'App\\Http\\Controllers\\Admin\\AdminMenuPermissionController@updateRolePermissions');
$router->post('/admin/menu-permissions/update-user', 'App\\Http\\Controllers\\Admin\\AdminMenuPermissionController@updateUserPermissions');
$router->post('/admin/menu-permissions/revoke-user', 'App\\Http\\Controllers\\Admin\\AdminMenuPermissionController@revokeUserPermission');
$router->get('/admin/menu-permissions/get-users', 'App\\Http\\Controllers\\Admin\\AdminMenuPermissionController@getUsers');
$router->get('/admin/menu-permissions/get-user-permissions', 'App\\Http\\Controllers\\Admin\\AdminMenuPermissionController@getUserPermissions');

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

// Smart AI Chatbot (RBAC-enabled, Human-like)
$router->post('/api/ai/chat', 'SmartAIController@chat');
$router->get('/api/ai/history', 'SmartAIController@history');
$router->get('/ai-assistant', 'SmartAIController@assistantPage');

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

// God Mode - Admin Super Powers
$router->get('/admin/godmode', 'App\\Http\\Controllers\\Admin\\GodModeController@dashboard');
$router->post('/admin/godmode/impersonate/{id}', 'App\\Http\\Controllers\\Admin\\GodModeController@impersonate');
$router->post('/admin/godmode/stop-impersonation', 'App\\Http\\Controllers\\Admin\\GodModeController@stopImpersonation');
$router->post('/admin/godmode/switch-role', 'App\\Http\\Controllers\\Admin\\GodModeController@switchRole');
$router->post('/admin/godmode/restore-role', 'App\\Http\\Controllers\\Admin\\GodModeController@restoreRole');
$router->get('/admin/godmode/users', 'App\\Http\\Controllers\\Admin\\GodModeController@getUsersList');
$router->post('/admin/godmode/execute-command', 'App\\Http\\Controllers\\Admin\\GodModeController@executeCommand');
$router->get('/admin/godmode/system-health', 'App\\Http\\Controllers\\Admin\\GodModeController@systemHealth');

// MLM Management Routes
$router->get('/admin/mlm', 'App\Http\Controllers\Admin\MLMController@index');
$router->get('/admin/mlm/associates', 'App\Http\Controllers\Admin\MLMController@associates');
$router->get('/admin/mlm/associates/create', 'App\Http\Controllers\Admin\MLMController@createAssociate');
$router->post('/admin/mlm/associates/create', 'App\Http\Controllers\Admin\MLMController@createAssociate');
$router->get('/admin/mlm/commission', 'App\Http\Controllers\Admin\MLMController@commission');
$router->get('/admin/mlm/network', 'App\Http\Controllers\Admin\MLMController@network');
$router->get('/admin/mlm/payouts', 'App\Http\Controllers\Admin\MLMController@payouts');

// MLM Settings & Rank Evaluation
$router->get('/admin/mlm-settings/levels', 'App\Http\Controllers\Admin\MLMSettingsController@levels');
$router->get('/admin/mlm-settings/levels/edit/{id}', 'App\Http\Controllers\Admin\MLMSettingsController@editLevel');
$router->post('/admin/mlm-settings/levels/update/{id}', 'App\Http\Controllers\Admin\MLMSettingsController@updateLevel');
$router->get('/admin/mlm-settings/rules', 'App\Http\Controllers\Admin\MLMSettingsController@rules');
$router->post('/admin/mlm-settings/rules/update/{id}', 'App\Http\Controllers\Admin\MLMSettingsController@updateRule');
$router->get('/admin/mlm-settings/evaluate', 'App\Http\Controllers\Admin\MLMSettingsController@evaluateRanks');
$router->get('/admin/mlm-settings/associate-progress', 'App\Http\Controllers\Admin\MLMSettingsController@associateProgress');

// Projects Management Routes
$router->get('/admin/projects', 'App\Http\Controllers\Admin\ProjectsAdminController@index');
$router->get('/admin/projects/create', 'App\Http\Controllers\Admin\ProjectsAdminController@create');
$router->post('/admin/projects/store', 'App\Http\Controllers\Admin\ProjectsAdminController@store');
$router->get('/admin/projects/edit/{id}', 'App\Http\Controllers\Admin\ProjectsAdminController@edit');
$router->post('/admin/projects/update/{id}', 'App\Http\Controllers\Admin\ProjectsAdminController@update');
$router->get('/admin/projects/view/{id}', 'App\Http\Controllers\Admin\ProjectsAdminController@detail');
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
$router->post('/forgot-password', 'App\Http\Controllers\Auth\AuthenticationController@forgotPassword');
$router->get('/reset-password', 'App\Http\Controllers\Auth\AuthenticationController@showResetPassword');
$router->post('/reset-password', 'App\Http\Controllers\Auth\AuthenticationController@resetPassword');
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
$router->get('/colonies', 'Front\PageController@colonies');
$router->get('/ai-chatbot', 'Front\PageController@aiChatbotPage');

$router->get('/terms', 'App\\Http\\Controllers\\Front\\PageController@legalTermsPage');
$router->get('/inquiry', function () {
    include __DIR__ . '/../app/views/pages/inquiry.php';
});
$router->post('/inquiry', function () {
    header('Location: /inquiry?success=1');
    exit;
});


// Admin Analytics
$router->get('/admin/analytics', 'App\\Http\\Controllers\\Admin\\AnalyticsController@index');

// Newsletter Subscribe
$router->post('/subscribe', 'Api\NewsletterController@subscribe');

// ============================================================
// SMART LOCATION & BANK APIs
// ============================================================

// Location APIs
$router->get('/api/locations/countries', 'Api\LocationController@countries');
$router->get('/api/locations/states', 'Api\LocationController@states');
$router->get('/api/locations/districts', 'Api\LocationController@districts');
$router->get('/api/locations/cities', 'Api\LocationController@cities');
$router->get('/api/locations/search', 'Api\LocationController@search');
$router->get('/api/locations/pincode/{pincode}', 'Api\LocationController@byPincode');
$router->get('/api/locations/pincodes', 'Api\LocationController@pincodes');

// Bank APIs
$router->get('/api/banks/search', 'Api\BankController@search');
$router->get('/api/banks/ifsc/{ifsc}', 'Api\BankController@byIfsc');
$router->get('/api/banks/branches', 'Api\BankController@searchBranches');
$router->get('/api/banks/validate-account', 'Api\BankController@validateAccount');
$router->get('/api/banks/{id}/branches', 'Api\BankController@branches');

// Service Interest
$router->post('/service-interest', 'Front\PageController@serviceInterest');

// ============================================================
// WALLET SYSTEM
// ============================================================

// Wallet Dashboard
$router->get('/wallet', 'WalletController@index');
$router->get('/wallet/dashboard', 'WalletController@index');

// Wallet Transactions
$router->get('/wallet/transactions', 'WalletController@transactions');

// Wallet Transfer to EMI
$router->get('/wallet/transfer-emi', 'WalletController@transferToEmi');
$router->post('/wallet/transfer-emi/process', 'WalletController@processEmiTransfer');

// Wallet Withdrawal
$router->get('/wallet/withdrawal', 'WalletController@withdrawal');
$router->post('/wallet/withdrawal/process', 'WalletController@processWithdrawal');

// Bank Account Management
$router->get('/wallet/bank-accounts', 'WalletController@bankAccounts');
$router->post('/wallet/bank-accounts/add', 'WalletController@addBankAccount');

// Referral Network
$router->get('/wallet/referral-network', 'WalletController@referralNetwork');

// Wallet Analytics
$router->get('/wallet/analytics', 'WalletController@analytics');

// ============================================================
// ML & AI API ROUTES
// ============================================================

// ML Dashboard API
$router->get('/api/ml/dashboard', 'MLController@getMLDashboard');

// ML Predictions
$router->get('/api/ml/predict-price', 'MLController@predictPrice');
$router->get('/api/ml/recommendations', 'MLController@getRecommendations');

// ML User Behavior
$router->get('/api/ml/analyze-behavior', 'MLController@analyzeUserBehavior');

// ============================================================
// FRAUD DETECTION API ROUTES
// ============================================================

// Fraud Detection
$router->get('/api/fraud/detect', 'MLController@detectFraud');
$router->post('/api/fraud/detect', 'MLController@detectFraud');

// Fraud Dashboard
$router->get('/api/fraud/dashboard', 'MLController@fraudDashboard');

// Admin Network MLM
$router->get('/admin/network/tree', 'App\\Http\\Controllers\\MLMTreeController@tree');
$router->get('/admin/network/genealogy', 'App\\Http\\Controllers\\Admin\\NetworkController@genealogy');
$router->get('/admin/network/ranks', 'App\\Http\\Controllers\\Admin\\NetworkController@ranks');

// Admin Payouts
$router->get('/admin/payouts', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@payouts');

// Admin Payments
$router->get('/admin/payments', 'App\\Http\\Controllers\\Admin\\PaymentController@index');

// Admin EMI
$router->get('/admin/emi', 'App\\Http\\Controllers\\Admin\\EMIController@index');
$router->get('/admin/emi/create', 'App\\Http\\Controllers\\Admin\\EMIController@create');
$router->post('/admin/emi/store', 'App\\Http\\Controllers\\Admin\\EMIController@store');
$router->get('/admin/emi/{id}', 'App\\Http\\Controllers\\Admin\\EMIController@show');
$router->post('/admin/emi/payment/{id}', 'App\\Http\\Controllers\\Admin\\EMIController@processPayment');
$router->get('/admin/emi/stats', 'App\\Http\\Controllers\\Admin\\EMIController@getStats');

// Admin Accounting
$router->get('/admin/accounting', 'App\\Http\\Controllers\\Admin\\AccountingController@index');

// Admin Tasks
$router->get('/admin/tasks', 'App\\Http\\Controllers\\Admin\\TaskController@index');
$router->get('/admin/tasks/create', 'App\\Http\\Controllers\\Admin\\TaskController@create');
$router->post('/admin/tasks/store', 'App\\Http\\Controllers\\Admin\\TaskController@store');
$router->get('/admin/tasks/show/{id}', 'App\\Http\\Controllers\\Admin\\TaskController@show');
$router->get('/admin/tasks/edit/{id}', 'App\\Http\\Controllers\\Admin\\TaskController@edit');
$router->post('/admin/tasks/update/{id}', 'App\\Http\\Controllers\\Admin\\TaskController@update');
$router->post('/admin/tasks/destroy/{id}', 'App\\Http\\Controllers\\Admin\\TaskController@destroy');
$router->get('/admin/tasks/stats', 'App\\Http\\Controllers\\Admin\\TaskController@getStats');

// Admin Support Tickets
$router->get('/admin/support_tickets', 'App\\Http\\Controllers\\Admin\\SupportTicketController@index');

// Admin Media
$router->get('/admin/media', 'App\\Http\\Controllers\\Admin\\MediaController@index');

// Admin Careers
$router->get('/admin/careers', 'App\\Http\\Controllers\\Admin\\CareerController@index');
$router->get('/admin/careers/create', 'App\\Http\\Controllers\\Admin\\CareerController@create');
$router->post('/admin/careers/store', 'App\\Http\\Controllers\\Admin\\CareerController@store');
$router->get('/admin/careers/{id}', 'App\\Http\\Controllers\\Admin\\CareerController@show');
$router->get('/admin/careers/{id}/edit', 'App\\Http\\Controllers\\Admin\\CareerController@edit');
$router->post('/admin/careers/{id}/update', 'App\\Http\\Controllers\\Admin\\CareerController@update');
$router->post('/admin/careers/{id}/destroy', 'App\\Http\\Controllers\\Admin\\CareerController@destroy');
$router->get('/admin/careers/stats', 'App\\Http\\Controllers\\Admin\\CareerController@getStats');

// Admin AI
$router->get('/admin/ai', 'App\\Http\\Controllers\\Admin\\AiController@hub');
$router->get('/admin/ai/analytics', 'App\\Http\\Controllers\\Admin\\AiController@analytics');

// Admin Resell Properties
$router->get('/admin/resell-properties', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@index');
$router->get('/admin/resell-properties/create', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@create');
$router->get('/admin/resell-properties/edit/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@edit');
$router->get('/admin/resell-properties/view/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@details');
$router->get('/admin/resell-properties/images/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@images');
$router->get('/admin/resell-properties/status/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@status');
$router->get('/admin/resell-properties/commission/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@commission');

// Include additional admin routes
require_once __DIR__ . '/admin_routes.php';

// ============================================================
// MISSING ADMIN ROUTES (FIXED)
// ============================================================

// Colonies Management (unique update/destroy patterns)
$router->post('/admin/colonies/{id}/update', 'App\\Http\\Controllers\\Admin\\ColonyController@update');
$router->post('/admin/colonies/{id}/destroy', 'App\\Http\\Controllers\\Admin\\ColonyController@destroy');

// Employees Management
$router->get('/admin/employees', 'App\\Http\\Controllers\\Admin\\HRMController@employeeList');

// Commissions Management
$router->get('/admin/commissions', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@commissionsList');

// Accounts/Financial Management
$router->get('/admin/accounts', 'App\\Http\\Controllers\\Admin\\FinanceController@adminAccounts');

// Developer Tools
$router->get('/admin/dev-tools', 'App\\Http\\Controllers\\Admin\\AdminController@devTools');

// Missing sidebar menu items - Route stubs for DB-driven menu
$router->get('/admin/finance', 'App\\Http\\Controllers\\Admin\\FinanceController@index');
$router->get('/admin/finance/invoices', 'App\\Http\\Controllers\\Admin\\FinanceController@invoices');
$router->get('/admin/finance/create-invoice', 'App\\Http\\Controllers\\Admin\\FinanceController@createInvoice');
$router->get('/admin/finance/expenses', 'App\\Http\\Controllers\\Admin\\FinanceController@expenses');
$router->get('/admin/finance/create-expense', 'App\\Http\\Controllers\\Admin\\FinanceController@createExpense');
$router->get('/admin/finance/payments', 'App\\Http\\Controllers\\Admin\\FinanceController@payments');
$router->get('/admin/finance/calculator', 'App\\Http\\Controllers\\Admin\\FinanceController@calculator');
$router->get('/admin/finance/reports', 'App\\Http\\Controllers\\Admin\\FinanceController@reports');
$router->get('/admin/invoices', 'App\\Http\\Controllers\\Admin\\FinanceController@invoices');
$router->get('/admin/invoices/view/{id}', 'App\\Http\\Controllers\\Admin\\FinanceController@viewInvoice');
$router->get('/admin/invoices/download/{id}', 'App\\Http\\Controllers\\Admin\\FinanceController@downloadInvoice');
$router->post('/admin/invoices/delete/{id}', function($id) {
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE invoices SET status='cancelled' WHERE id=?");
        $stmt->execute([$id]);
    } catch (\Exception $e) {}
    header('Location: ' . BASE_URL . '/admin/invoices');
    exit;
});
$router->get('/admin/roles', 'App\\Http\\Controllers\\RoleBasedDashboardController@roles');
$router->get('/admin/associates', function () {
    header('Location: ' . BASE_URL . '/admin/mlm/associates');
    exit;
});
$router->get('/admin/associates/create', function () {
    header('Location: ' . BASE_URL . '/admin/mlm/associates/create');
    exit;
});
$router->get('/admin/hrm/employees', 'App\\Http\\Controllers\\Admin\\HRMController@employees');

// ============================================================
// NEWLY ROUTED ADMIN CONTROLLERS
// ============================================================

// Engagement Tracking
$router->get('/admin/engagement', 'App\\Http\\Controllers\\Admin\\EngagementController@index');
$router->get('/admin/engagement/goals', 'App\\Http\\Controllers\\Admin\\EngagementController@goals');
$router->get('/admin/engagement/goals/create', 'App\\Http\\Controllers\\Admin\\EngagementController@createGoal');
$router->post('/admin/engagement/goals/store', 'App\\Http\\Controllers\\Admin\\EngagementController@storeGoal');
$router->post('/admin/engagement/goals/{id}/progress', 'App\\Http\\Controllers\\Admin\\EngagementController@updateGoalProgress');
$router->get('/admin/engagement/stats', 'App\\Http\\Controllers\\Admin\\EngagementController@getStats');

// Jobs Management
$router->get('/admin/jobs/manage', 'App\\Http\\Controllers\\Admin\\JobsAdminController@index');
$router->get('/admin/jobs/manage/create', 'App\\Http\\Controllers\\Admin\\JobsAdminController@create');
$router->post('/admin/jobs/manage/store', 'App\\Http\\Controllers\\Admin\\JobsAdminController@store');
$router->get('/admin/jobs/manage/{id}/edit', 'App\\Http\\Controllers\\Admin\\JobsAdminController@edit');
$router->post('/admin/jobs/manage/{id}/update', 'App\\Http\\Controllers\\Admin\\JobsAdminController@update');
$router->get('/admin/jobs/manage/applications/{jobId?}', 'App\\Http\\Controllers\\Admin\\JobsAdminController@applications');
$router->get('/admin/jobs/manage/applications/view/{id}', 'App\\Http\\Controllers\\Admin\\JobsAdminController@viewApplication');
$router->post('/admin/jobs/manage/applications/{id}/status', 'App\\Http\\Controllers\\Admin\\JobsAdminController@updateApplicationStatus');

// Plot Admin (alternative plot management)
$router->get('/admin/plots/categories', 'App\\Http\\Controllers\\Admin\\PlotManagementController@categories');
$router->get('/admin/plots/manage', 'App\\Http\\Controllers\\Admin\\PlotsAdminController@index');
$router->get('/admin/plots/manage/create', 'App\\Http\\Controllers\\Admin\\PlotsAdminController@create');
$router->get('/admin/plots/manage/{id}/edit', 'App\\Http\\Controllers\\Admin\\PlotsAdminController@edit');
$router->get('/admin/plots/manage/{id}', 'App\\Http\\Controllers\\Admin\\PlotsAdminController@show');
$router->post('/admin/plots/manage/{id}/status', 'App\\Http\\Controllers\\Admin\\PlotsAdminController@updateStatus');
$router->post('/admin/plots/manage/bulk-status', 'App\\Http\\Controllers\\Admin\\PlotsAdminController@bulkStatusUpdate');
$router->get('/admin/plots/manage/export', 'App\\Http\\Controllers\\Admin\\PlotsAdminController@export');

// Property Images
$router->get('/admin/properties/{id}/images', 'App\\Http\\Controllers\\Admin\\PropertyImageController@manage');
$router->post('/admin/properties/images/upload', 'App\\Http\\Controllers\\Admin\\PropertyImageController@upload');
$router->post('/admin/properties/images/ajax-upload', 'App\\Http\\Controllers\\Admin\\PropertyImageController@ajaxUpload');
$router->post('/admin/properties/images/primary', 'App\\Http\\Controllers\\Admin\\PropertyImageController@setPrimary');
$router->post('/admin/properties/images/caption', 'App\\Http\\Controllers\\Admin\\PropertyImageController@updateCaption');
$router->post('/admin/properties/images/delete', 'App\\Http\\Controllers\\Admin\\PropertyImageController@delete');
$router->post('/admin/properties/images/reorder', 'App\\Http\\Controllers\\Admin\\PropertyImageController@reorder');

// Email Settings
$router->get('/admin/settings/email-config', 'App\\Http\\Controllers\\Admin\\EmailSettingsController@index');
$router->post('/admin/settings/email-config/save', 'App\\Http\\Controllers\\Admin\\EmailSettingsController@save');
$router->post('/admin/settings/email-config/test', 'App\\Http\\Controllers\\Admin\\EmailSettingsController@test');

// SMTP Settings (dedicated page)
$router->get('/admin/settings/smtp', 'App\\Http\\Controllers\\Admin\\EmailSettingsController@smtpSettings');
$router->post('/admin/settings/smtp-save', 'App\\Http\\Controllers\\Admin\\EmailSettingsController@saveSmtp');

// SMS
$router->get('/admin/sms/send', 'App\\Http\\Controllers\\Communication\\SmsController@send');
$router->post('/admin/sms/send', 'App\\Http\\Controllers\\Communication\\SmsController@send');
$router->post('/admin/sms/send-bulk', 'App\\Http\\Controllers\\Communication\\SmsController@sendBulk');
$router->post('/admin/sms/schedule', 'App\\Http\\Controllers\\Communication\\SmsController@schedule');
$router->get('/admin/sms/status/{id}', 'App\\Http\\Controllers\\Communication\\SmsController@getStatus');
$router->get('/admin/sms/stats', 'App\\Http\\Controllers\\Communication\\SmsController@getStats');

// Payment Gateway
$router->get('/admin/payments/gateway', 'App\\Http\\Controllers\\Payment\\AdvancedPaymentController@gatewaySelection');
$router->post('/admin/payments/gateway/process', 'App\\Http\\Controllers\\Payment\\AdvancedPaymentController@processPayment');
$router->get('/admin/payments/gateway/success', 'App\\Http\\Controllers\\Payment\\AdvancedPaymentController@paymentSuccess');
$router->get('/admin/payments/gateway/failed', 'App\\Http\\Controllers\\Payment\\AdvancedPaymentController@paymentFailed');
$router->any('/admin/payments/emi-calculator', 'App\\Http\\Controllers\\Payment\\AdvancedPaymentController@emiCalculator');
$router->get('/admin/payments/history', 'App\\Http\\Controllers\\Payment\\AdvancedPaymentController@paymentHistory');
$router->get('/admin/payments/receipt/{order_id}', 'App\\Http\\Controllers\\Payment\\AdvancedPaymentController@downloadReceipt');
$router->get('/admin/payments/analytics', 'App\\Http\\Controllers\\Payment\\AdvancedPaymentController@adminPaymentAnalytics');

// ============================================================
// ADMIN WORKFLOW CONTROLLER ROUTES
// ============================================================

$router->get('/admin/workflows', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@dashboard');
$router->get('/admin/workflows/list', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@workflows');
$router->get('/admin/workflows/create', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@createWorkflow');
$router->post('/admin/workflows/create', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@createWorkflow');
$router->get('/admin/workflows/{id}/steps', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@workflowSteps');
$router->post('/admin/workflows/{id}/steps', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@workflowSteps');
$router->get('/admin/workflows/pending', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@pendingApprovals');
$router->post('/admin/workflows/action/{id}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@processWorkflowAction');
$router->get('/admin/reports', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@reports');
$router->get('/admin/reports/sales', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@salesReport');
$router->get('/admin/reports/leads', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@leadsReport');
$router->get('/admin/reports/commission', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@commissionReport');
$router->post('/admin/reports/save', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@saveReport');
$router->get('/admin/audit', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@auditTrail');
$router->get('/admin/audit/entity/{type}/{id}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@entityHistory');
$router->get('/admin/audit/user/{id}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@userActivity');
$router->get('/admin/import-export', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@importExport');
$router->get('/admin/import-export/import', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@importData');
$router->post('/admin/import-export/import', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@importData');
$router->get('/admin/import-export/export', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@exportData');
$router->get('/admin/import-export/template/{type}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@downloadTemplate');
$router->get('/admin/khatabook-sales', 'App\\Http\\Controllers\\Admin\\KhatabookSalesController@index');
$router->get('/admin/khatabook-sales/view/{id}', 'App\\Http\\Controllers\\Admin\\KhatabookSalesController@show');
$router->get('/admin/khatabook-sales/export', 'App\\Http\\Controllers\\Admin\\KhatabookSalesController@export');
$router->post('/admin/khatabook-sales/delete/{id}', 'App\\Http\\Controllers\\Admin\\KhatabookSalesController@delete');
$router->get('/admin/backups', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@backups');
$router->post('/admin/backups/create', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@createBackup');
$router->get('/admin/backups/download/{filename}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@downloadBackup');
$router->get('/admin/emails', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@emailQueue');
$router->post('/admin/emails/process', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@processEmailQueue');
$router->post('/admin/emails/retry', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@retryFailedEmails');
$router->get('/admin/api-docs', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@apiDocs');
$router->get('/admin/api-docs/export/{format}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@exportApiSpec');

// Admin Pages Management (CMS)
$router->get('/admin/pages', 'App\\Http\\Controllers\\Admin\\PagesController@index');
$router->get('/admin/pages/create', 'App\\Http\\Controllers\\Admin\\PagesController@create');
$router->post('/admin/pages/store', 'App\\Http\\Controllers\\Admin\\PagesController@store');
$router->get('/admin/pages/edit/{id}', 'App\\Http\\Controllers\\Admin\\PagesController@edit');
$router->post('/admin/pages/update/{id}', 'App\\Http\\Controllers\\Admin\\PagesController@update');

// Admin Colony Management
$router->get('/admin/colonies', 'App\\Http\\Controllers\\Admin\\ColonyController@index');
$router->get('/admin/colonies/create', 'App\\Http\\Controllers\\Admin\\ColonyController@create');
$router->post('/admin/colonies/store', 'App\\Http\\Controllers\\Admin\\ColonyController@store');
$router->get('/admin/colonies/{id}', 'App\\Http\\Controllers\\Admin\\ColonyController@show');
$router->get('/admin/colonies/{id}/edit', 'App\\Http\\Controllers\\Admin\\ColonyController@edit');
$router->post('/admin/colonies/update/{id}', 'App\\Http\\Controllers\\Admin\\ColonyController@update');
$router->post('/admin/colonies/destroy/{id}', 'App\\Http\\Controllers\\Admin\\ColonyController@destroy');
$router->get('/admin/colonies/{id}/plots', 'App\\Http\\Controllers\\Admin\\ColonyController@plots');
$router->get('/admin/colonies/{id}/financials', 'App\\Http\\Controllers\\Admin\\ColonyController@financials');

// Admin ERP Dashboard (Cross-Module Reports)
$router->get('/admin/erp/inventory', 'App\\Http\\Controllers\\Admin\\ErpDashboardController@inventory');
$router->get('/admin/erp/plot-profit', 'App\\Http\\Controllers\\Admin\\ErpDashboardController@plotProfit');
$router->get('/admin/erp/land-mapping', 'App\\Http\\Controllers\\Admin\\ErpDashboardController@landMapping');

// Admin Team Management
$router->get('/admin/team', 'App\\Http\\Controllers\\Admin\\TeamController@index');
$router->get('/admin/team/create', 'App\\Http\\Controllers\\Admin\\TeamController@create');
$router->post('/admin/team/store', 'App\\Http\\Controllers\\Admin\\TeamController@store');
$router->get('/admin/team/edit/{id}', 'App\\Http\\Controllers\\Admin\\TeamController@edit');
$router->post('/admin/team/update/{id}', 'App\\Http\\Controllers\\Admin\\TeamController@update');
$router->post('/admin/team/destroy/{id}', 'App\\Http\\Controllers\\Admin\\TeamController@destroy');

// Admin Expenses
$router->get('/admin/expenses', 'App\\Http\\Controllers\\Admin\\ExpensesController@index');
$router->get('/admin/expenses/create', 'App\\Http\\Controllers\\Admin\\ExpensesController@create');
$router->get('/admin/expense', 'App\\Http\\Controllers\\Admin\\ExpensesController@index');

// Admin FAQ
$router->get('/admin/faqs', function() {
    require __DIR__ . '/../app/views/admin/faqs.php';
});

// Admin Settings Company
$router->get('/admin/settings/company', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');

// Admin Cache Clear
$router->get('/admin/cache', function() {
    require __DIR__ . '/../app/views/admin/cache.php';
});

// Admin Service Enquiries (alias for services)
$router->get('/admin/services/enquiry', 'App\\Http\\Controllers\\Admin\\ExpensesController@index');

// Admin Activity Log
$router->get('/admin/activity-log', 'App\\Http\\Controllers\\Admin\\ActivityLogController@index');

// Admin Settings Sub-pages
$router->get('/admin/settings/payment', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->get('/admin/settings/email', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->get('/admin/settings/sms', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');

// ============================================================
// CUSTOM FEATURES CONTROLLER ROUTES
// ============================================================

$router->get('/features', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@dashboard');
$router->get('/features/virtual-tours', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@virtualTours');
$router->post('/features/virtual-tours/create', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@createVirtualTour');
$router->get('/features/virtual-tours/{propertyId}', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@getVirtualTour');
$router->get('/features/comparison', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@propertyComparison');
$router->post('/features/comparison/compare', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@compareProperties');
$router->post('/features/comparison/save', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@saveComparison');
$router->get('/features/comparison/saved', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@getSavedComparisons');
$router->post('/features/comparison/export', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@exportComparison');
$router->get('/features/investment-calculator', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@investmentCalculator');
$router->post('/features/investment-calculator/calculate', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@calculateInvestment');
$router->get('/features/investment-calculator/history', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@getInvestmentHistory');
$router->get('/features/smart-search', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@smartSearchPage');
$router->post('/features/smart-search/search', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@smartSearch');
$router->get('/features/neighborhood/{propertyId}', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@neighborhoodAnalytics');
$router->get('/features/stats', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@getStats');
$router->get('/features/properties/{propertyId}/suggestions', 'App\\Http\\Controllers\\CustomFeatures\\CustomFeaturesController@getPropertySuggestions');

// ============================================================
// LOGGING CONTROLLER ROUTES
// ============================================================

$router->get('/logging', 'App\\Http\\Controllers\\LoggingController@showDashboard');
$router->get('/logging/logs', 'App\\Http\\Controllers\\LoggingController@showLogs');
$router->get('/logging/security-alerts', 'App\\Http\\Controllers\\LoggingController@showSecurityAlerts');
$router->post('/logging/export', 'App\\Http\\Controllers\\LoggingController@exportLogs');
$router->post('/logging/clean', 'App\\Http\\Controllers\\LoggingController@cleanLogs');
$router->get('/logging/stats', 'App\\Http\\Controllers\\LoggingController@getLogStats');
$router->get('/logging/search', 'App\\Http\\Controllers\\LoggingController@searchLogs');
$router->get('/logging/details/{id}', 'App\\Http\\Controllers\\LoggingController@viewLogDetails');
$router->post('/logging/dismiss-alert', 'App\\Http\\Controllers\\LoggingController@dismissAlert');
$router->get('/logging/stream', 'App\\Http\\Controllers\\LoggingController@getLogStream');

// ============================================================
// MARKETING CONTROLLER ROUTES
// ============================================================

$router->get('/marketing', 'App\\Http\\Controllers\\Marketing\\MarketingController@dashboard');
$router->post('/marketing/campaigns/create', 'App\\Http\\Controllers\\Marketing\\MarketingController@createCampaign');
$router->post('/marketing/campaigns/execute/{id}', 'App\\Http\\Controllers\\Marketing\\MarketingController@executeCampaign');
$router->post('/marketing/leads/add', 'App\\Http\\Controllers\\Marketing\\MarketingController@addLead');
$router->get('/marketing/leads/{id}', 'App\\Http\\Controllers\\Marketing\\MarketingController@getLead');
$router->post('/marketing/leads/{id}/status', 'App\\Http\\Controllers\\Marketing\\MarketingController@updateLeadStatus');
$router->post('/marketing/workflows/process', 'App\\Http\\Controllers\\Marketing\\MarketingController@processWorkflows');
$router->get('/marketing/analytics', 'App\\Http\\Controllers\\Marketing\\MarketingController@getAnalytics');
$router->get('/marketing/leads', 'App\\Http\\Controllers\\Marketing\\MarketingController@getLeads');
$router->get('/marketing/lead-scoring', 'App\\Http\\Controllers\\Marketing\\MarketingController@getLeadScoring');
$router->post('/marketing/leads/export', 'App\\Http\\Controllers\\Marketing\\MarketingController@exportLeads');
$router->get('/marketing/campaigns/performance', 'App\\Http\\Controllers\\Marketing\\MarketingController@getCampaignPerformance');
$router->get('/marketing/settings', 'App\\Http\\Controllers\\Marketing\\MarketingController@settings');
$router->post('/marketing/settings', 'App\\Http\\Controllers\\Marketing\\MarketingController@settings');

// ============================================================
// ROLE-BASED ADMIN DASHBOARDS
// ============================================================

$router->get('/admin/ceo-dashboard', 'App\\Http\\Controllers\\Admin\\CEODashboardController@index');
$router->get('/admin/ceo-dashboard/revenue', 'App\\Http\\Controllers\\Admin\\CEODashboardController@getRevenueAnalytics');
$router->get('/admin/ceo-dashboard/team', 'App\\Http\\Controllers\\Admin\\CEODashboardController@getTeamPerformance');

$router->get('/admin/cfo-dashboard', 'App\\Http\\Controllers\\Admin\\CFODashboardController@index');
$router->get('/admin/cfo-dashboard/financial', 'App\\Http\\Controllers\\Admin\\CFODashboardController@getFinancialAnalytics');
$router->get('/admin/cfo-dashboard/expenses', 'App\\Http\\Controllers\\Admin\\CFODashboardController@getExpenseBreakdown');

$router->get('/admin/builder-dashboard', 'App\\Http\\Controllers\\Admin\\BuilderDashboardController@index');
$router->get('/admin/builder-dashboard/construction', 'App\\Http\\Controllers\\Admin\\BuilderDashboardController@getConstructionAnalytics');
$router->get('/admin/builder-dashboard/materials', 'App\\Http\\Controllers\\Admin\\BuilderDashboardController@getMaterialStatus');

// ============================================================
// ADMIN LAND MANAGEMENT
// ============================================================

$router->get('/admin/land', 'App\\Http\\Controllers\\Admin\\LandController@index');
$router->get('/admin/land/create', 'App\\Http\\Controllers\\Admin\\LandController@create');
$router->post('/admin/land/store', 'App\\Http\\Controllers\\Admin\\LandController@store');
$router->get('/admin/land/acquisitions', 'App\\Http\\Controllers\\Admin\\LandController@acquisitions');
$router->get('/admin/land/records', 'App\\Http\\Controllers\\Admin\\LandController@records');
$router->get('/admin/land/{id}', 'App\\Http\\Controllers\\Admin\\LandController@show');
$router->get('/admin/land/{id}/edit', 'App\\Http\\Controllers\\Admin\\LandController@edit');
$router->post('/admin/land/{id}/update', 'App\\Http\\Controllers\\Admin\\LandController@update');
$router->post('/admin/land/{id}/destroy', 'App\\Http\\Controllers\\Admin\\LandController@destroy');
$router->get('/admin/land/stats', 'App\\Http\\Controllers\\Admin\\LandController@getStats');

// ============================================================
// ADMIN LOYALTY PROGRAM
// ============================================================

$router->get('/admin/loyalty', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@index');
$router->get('/admin/loyalty/members', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@members');
$router->get('/admin/loyalty/members/{id}', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@memberDetails');
$router->post('/admin/loyalty/points/add', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@addPoints');
$router->get('/admin/loyalty/rewards', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@rewards');
$router->get('/admin/loyalty/rewards/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@editReward');
$router->get('/admin/loyalty/redemptions', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@redemptions');
$router->post('/admin/loyalty/redemptions/status', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@updateRedemptionStatus');
$router->get('/admin/loyalty/rules', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@rules');
$router->get('/admin/loyalty/tier-benefits', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@tierBenefits');

// ============================================================
// ADMIN SCHEDULER
// ============================================================

$router->get('/admin/scheduler', 'App\\Http\\Controllers\\Admin\\AdminSchedulerController@index');
$router->get('/admin/scheduler/tasks/{id}', 'App\\Http\\Controllers\\Admin\\AdminSchedulerController@taskDetails');
$router->get('/admin/scheduler/tasks/create', 'App\\Http\\Controllers\\Admin\\AdminSchedulerController@create');
$router->get('/admin/scheduler/tasks/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdminSchedulerController@edit');
$router->post('/admin/scheduler/tasks/delete/{id}', 'App\\Http\\Controllers\\Admin\\AdminSchedulerController@delete');
$router->post('/admin/scheduler/tasks/run/{id}', 'App\\Http\\Controllers\\Admin\\AdminSchedulerController@runTask');
$router->get('/admin/scheduler/logs', 'App\\Http\\Controllers\\Admin\\AdminSchedulerController@logs');
$router->get('/admin/scheduler/health', 'App\\Http\\Controllers\\Admin\\AdminSchedulerController@health');
$router->post('/admin/scheduler/cleanup', 'App\\Http\\Controllers\\Admin\\AdminSchedulerController@cleanup');

// ============================================================
// ADMIN FILE MANAGER
// ============================================================

$router->get('/admin/files', 'App\\Http\\Controllers\\Admin\\AdminFileController@index');
$router->post('/admin/files/upload', 'App\\Http\\Controllers\\Admin\\AdminFileController@upload');
$router->get('/admin/files/{uuid}', 'App\\Http\\Controllers\\Admin\\AdminFileController@fileDetails');
$router->get('/admin/files/download/{uuid}', 'App\\Http\\Controllers\\Admin\\AdminFileController@download');
$router->post('/admin/files/delete/{uuid}', 'App\\Http\\Controllers\\Admin\\AdminFileController@delete');
$router->post('/admin/files/version/{uuid}', 'App\\Http\\Controllers\\Admin\\AdminFileController@uploadVersion');
$router->get('/admin/files/browse', 'App\\Http\\Controllers\\Admin\\AdminFileController@browse');
$router->get('/admin/files/storage', 'App\\Http\\Controllers\\Admin\\AdminFileController@storage');

// ============================================================
// ADMIN COMMISSION MANAGEMENT
// ============================================================

$router->get('/admin/commission-manage', 'App\\Http\\Controllers\\Admin\\CommissionController@index');
$router->get('/admin/commission-manage/calculate', 'App\\Http\\Controllers\\Admin\\CommissionController@calculate');
$router->post('/admin/commission-manage/calculate', 'App\\Http\\Controllers\\Admin\\CommissionController@processCalculation');
$router->get('/admin/commission-manage/approve/{id}', 'App\\Http\\Controllers\\Admin\\CommissionController@approve');
$router->post('/admin/commission-manage/approve', 'App\\Http\\Controllers\\Admin\\CommissionController@processApproval');
$router->get('/admin/commission-manage/payout', 'App\\Http\\Controllers\\Admin\\CommissionController@payout');
$router->post('/admin/commission-manage/payout', 'App\\Http\\Controllers\\Admin\\CommissionController@processPayout');
$router->get('/admin/commission-manage/reports', 'App\\Http\\Controllers\\Admin\\CommissionController@reports');

// ============================================================
// ADMIN REPORTS
// ============================================================

$router->get('/admin/mlm-growth-reports', 'App\\Http\\Controllers\\Admin\\Reports\\MLMGrowthReportController@index');
$router->get('/admin/mlm-growth-reports/export', 'App\\Http\\Controllers\\Admin\\Reports\\MLMGrowthReportController@exportPdf');
$router->get('/admin/mlm-growth-reports/chart-data', 'App\\Http\\Controllers\\Admin\\Reports\\MLMGrowthReportController@apiChartData');

$router->get('/admin/roi-calculator', 'App\\Http\\Controllers\\Admin\\Reports\\ROICalculatorController@index');
$router->post('/admin/roi-calculator/calculate', 'App\\Http\\Controllers\\Admin\\Reports\\ROICalculatorController@apiCalculate');
$router->get('/admin/roi-calculator/compare', 'App\\Http\\Controllers\\Admin\\Reports\\ROICalculatorController@compare');

// ============================================================
// ADMIN AJAX ENDPOINTS (11 orphaned view files)
// ============================================================

$router->get('/admin/ajax/advanced-search', 'App\\Http\\Controllers\\Admin\\AjaxController@advancedSearch');
$router->get('/admin/ajax/consolidated-dashboard', 'App\\Http\\Controllers\\Admin\\AjaxController@consolidatedDashboard');
$router->get('/admin/ajax/export-dashboard-data', 'App\\Http\\Controllers\\Admin\\AjaxController@exportDashboardData');
$router->post('/admin/ajax/generate-followup', 'App\\Http\\Controllers\\Admin\\AjaxController@generateFollowup');
$router->get('/admin/ajax/get-chart-data', 'App\\Http\\Controllers\\Admin\\AjaxController@getChartData');
$router->get('/admin/ajax/get-component', 'App\\Http\\Controllers\\Admin\\AjaxController@getComponent');
$router->get('/admin/ajax/get-lead-timeline', 'App\\Http\\Controllers\\Admin\\AjaxController@getLeadTimeline');
$router->get('/admin/ajax/get-recent-activity', 'App\\Http\\Controllers\\Admin\\AjaxController@getRecentActivity');
$router->get('/admin/ajax/get-system-status', 'App\\Http\\Controllers\\Admin\\AjaxController@getSystemStatus');
$router->get('/admin/ajax/global-search', 'App\\Http\\Controllers\\Admin\\AjaxController@globalSearch');
$router->post('/admin/ajax/save-content', 'App\\Http\\Controllers\\Admin\\AjaxController@saveContent');

// ============================================================
// OLD ADMIN ROLE-BASED DASHBOARDS — all redirect to unified /admin/dashboard
// which renders role-appropriate stats via admin.php canonical layout
// ============================================================

$router->get('/admin/dashboard/accounting', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });
$router->get('/admin/dashboard/cm', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });
$router->get('/admin/dashboard/coo', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });
$router->get('/admin/dashboard/director', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });
$router->get('/admin/dashboard/finance', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });
$router->get('/admin/dashboard/hr', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });
$router->get('/admin/dashboard/it', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });
$router->get('/admin/dashboard/marketing', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });
$router->get('/admin/dashboard/operations', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });
$router->get('/admin/dashboard/superadmin', function () { header('Location: ' . BASE_URL . '/admin/dashboard'); exit; });

// ============================================================
// ORPHANED PUBLIC PAGE ROUTES (added 2026-05-15)
// ============================================================

// Content pages via PageController
$router->get('/bank', 'Front\\PageController@bank');
$router->get('/suyoday-colony', 'Front\\PageController@suyodayColonyPage');

// Legal section
$router->get('/legal', 'Front\\PageController@legal');
$router->get('/legal/privacy', 'Front\\PageController@legalPrivacy');
$router->get('/legal/terms', 'Front\\PageController@legalTermsPage');

// Standalone full-HTML pages
$router->get('/analytics', function () {
    include __DIR__ . '/../app/views/pages/analytics.php';
});
$router->get('/calc', function () {
    // Note: calc.php references __DIR__ . '/init.php' (may need fixing)
    include __DIR__ . '/../app/views/pages/calc.php';
});

// ============================================================
// LOCATION PROJECT PAGES (added 2026-05-15)
// ============================================================
// NOTE: gorakhpur-raghunath-nagri, gorakhpur-suryoday-colony, and
// varanasi-ganga-nagri are partial views (no <html>/<head>/<body>).
// They render as content fragments. For full layout, route through
// a controller using $this->render('locations/xxx') instead.

$router->get('/locations/gorakhpur-bohisawagar', function () {
    $projectName = 'Bohi Sawagar - Gorakhpur';
    include __DIR__ . '/../app/views/locations/gorakhpur-bohisawagar.php';
});
$router->get('/locations/gorakhpur-raghunath-nagri', function () {
    // Partial view: requires ASSETS_URL
    if (!defined('ASSETS_URL')) define('ASSETS_URL', BASE_URL . '/assets/');
    include __DIR__ . '/../app/views/locations/gorakhpur-raghunath-nagri.php';
});
$router->get('/locations/gorakhpur-suryoday-colony', function () {
    // Partial view: requires ASSETS_URL
    if (!defined('ASSETS_URL')) define('ASSETS_URL', BASE_URL . '/assets/');
    include __DIR__ . '/../app/views/locations/gorakhpur-suryoday-colony.php';
});
$router->get('/locations/kushinagar-budha-city', function () {
    // Uses its own layout via ob_start() + require layout
    // Replaces missing init.php — helpers.php is already loaded from bootstrap
    include __DIR__ . '/../app/views/locations/kushinagar-budha-city.php';
});
$router->get('/locations/lucknow-ram-nagri', function () {
    // Uses its own layout via ob_start() + require layout
    // Replaces missing init.php — helpers.php is already loaded from bootstrap
    include __DIR__ . '/../app/views/locations/lucknow-ram-nagri.php';
});
$router->get('/locations/varanasi-ganga-nagri', function () {
    // Partial view: requires ASSETS_URL
    if (!defined('ASSETS_URL')) define('ASSETS_URL', BASE_URL . '/assets/');
    include __DIR__ . '/../app/views/locations/varanasi-ganga-nagri.php';
});

// ====== Project Management ======
$router->get('/admin/projects/manage', 'App\Http\Controllers\Admin\ProjectController@index');
$router->get('/admin/projects/manage/create', 'App\Http\Controllers\Admin\ProjectController@create');
$router->post('/admin/projects/manage/store', 'App\Http\Controllers\Admin\ProjectController@store');
$router->get('/admin/projects/manage/show/{id}', 'App\Http\Controllers\Admin\ProjectController@show');
$router->get('/admin/projects/manage/edit/{id}', 'App\Http\Controllers\Admin\ProjectController@edit');
$router->post('/admin/projects/manage/update/{id}', 'App\Http\Controllers\Admin\ProjectController@update');
$router->post('/admin/projects/manage/destroy/{id}', 'App\Http\Controllers\Admin\ProjectController@destroy');
$router->get('/admin/projects/manage/analytics', 'App\Http\Controllers\Admin\ProjectController@analytics');

// ====== Sales Management ======
$router->get('/admin/sales', 'App\Http\Controllers\Admin\SalesController@index');
$router->get('/admin/sales/create', 'App\Http\Controllers\Admin\SalesController@create');
$router->post('/admin/sales/store', 'App\Http\Controllers\Admin\SalesController@store');
$router->get('/admin/sales/show/{id}', 'App\Http\Controllers\Admin\SalesController@show');
$router->get('/admin/sales/edit/{id}', 'App\Http\Controllers\Admin\SalesController@edit');
$router->post('/admin/sales/update/{id}', 'App\Http\Controllers\Admin\SalesController@update');
$router->post('/admin/sales/destroy/{id}', 'App\Http\Controllers\Admin\SalesController@destroy');
$router->get('/admin/sales/analytics', 'App\Http\Controllers\Admin\SalesController@analytics');

// ====== Payout Management ======
$router->get('/admin/payouts/list', 'App\Http\Controllers\Admin\PayoutController@index');
$router->get('/admin/payouts/list/all', 'App\Http\Controllers\Admin\PayoutController@list');
$router->get('/admin/payouts/show/{id}', 'App\Http\Controllers\Admin\PayoutController@show');
$router->get('/admin/payouts/analytics', 'App\Http\Controllers\Admin\PayoutController@analytics');

// ====== Newsletter Admin ======
$router->get('/admin/newsletter', 'App\Http\Controllers\Admin\NewsletterAdminController@index');

// ====== Accounting ======
$router->get('/admin/accounting/income', 'App\Http\Controllers\Admin\AccountingController@income');
$router->get('/admin/accounting/expenses', 'App\Http\Controllers\Admin\AccountingController@expenses');
$router->post('/admin/accounting/store-income', 'App\Http\Controllers\Admin\AccountingController@storeIncome');
$router->post('/admin/accounting/store-expense', 'App\Http\Controllers\Admin\AccountingController@storeExpense');

// ============================================================
// MISSING ADMIN ROUTES (From Menu Analysis)
// ============================================================

// AI Settings
$router->get('/admin/ai_settings', 'App\\Http\\Controllers\\Admin\\AISettingsController@index');

// Marketing
$router->get('/admin/email-templates', 'App\\Http\\Controllers\\Admin\\CampaignController@emailTemplates');
$router->get('/admin/email-templates/editor', 'App\\Http\\Controllers\\Admin\\CampaignController@templateEditor');
$router->post('/admin/email-templates/save', 'App\\Http\\Controllers\\Admin\\CampaignController@saveTemplate');
$router->get('/admin/email-logs', 'App\\Http\\Controllers\\Admin\\CampaignController@logs');
$router->get('/admin/sms-campaigns', 'App\\Http\\Controllers\\Admin\\CampaignController@smsCampaigns');
$router->get('/admin/whatsapp-broadcast', 'App\\Http\\Controllers\\Admin\\CampaignController@whatsappBroadcast');
$router->post('/admin/whatsapp-broadcast', 'App\\Http\\Controllers\\Admin\\CampaignController@whatsappBroadcast');
$router->get('/admin/referrals', 'App\\Http\\Controllers\\Admin\\ReferralController@index');
$router->get('/admin/social-media', 'App\\Http\\Controllers\\Admin\\SocialMediaController@index');

// News Categories
$router->get('/admin/news/categories', 'App\\Http\\Controllers\\Admin\\NewsController@categories');

// Operations
$router->get('/admin/support-tickets', 'App\\Http\\Controllers\\Admin\\SupportTicketController@index');
$router->get('/admin/meetings', 'App\\Http\\Controllers\\Admin\\MeetingController@index');
$router->get('/admin/documents', 'App\\Http\\Controllers\\Admin\\DocumentController@index');
$router->get('/admin/documents/upload', 'App\\Http\\Controllers\\Admin\\DocumentController@upload');
$router->post('/admin/documents/store', 'App\\Http\\Controllers\\Admin\\DocumentController@store');
$router->get('/admin/documents/show/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@show');
$router->post('/admin/documents/delete/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@delete');
$router->get('/admin/documents/download/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@download');
$router->get('/admin/documents/categories', 'App\\Http\\Controllers\\Admin\\DocumentController@categories');
$router->post('/admin/documents/categories/store', 'App\\Http\\Controllers\\Admin\\DocumentController@storeCategory');
$router->post('/admin/documents/categories/update/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@updateCategory');
$router->post('/admin/documents/categories/delete/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@deleteCategory');
$router->get('/admin/documents/types', 'App\\Http\\Controllers\\Admin\\DocumentController@types');
$router->post('/admin/documents/types/store', 'App\\Http\\Controllers\\Admin\\DocumentController@storeType');
$router->post('/admin/documents/types/update/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@updateType');
$router->post('/admin/documents/types/delete/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@deleteType');
$router->get('/admin/documents/templates', 'App\\Http\\Controllers\\Admin\\DocumentController@templates');
$router->post('/admin/documents/templates/store', 'App\\Http\\Controllers\\Admin\\DocumentController@storeTemplate');
$router->get('/admin/documents/templates/edit/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@editTemplate');
$router->post('/admin/documents/templates/update/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@updateTemplate');
$router->post('/admin/documents/templates/delete/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@deleteTemplate');
$router->get('/admin/documents/reviews', 'App\\Http\\Controllers\\Admin\\DocumentController@reviews');
$router->post('/admin/documents/reviews/store', 'App\\Http\\Controllers\\Admin\\DocumentController@storeReview');
$router->get('/admin/documents/classification', 'App\\Http\\Controllers\\Admin\\DocumentController@classification');
$router->get('/admin/documents/business', 'App\\Http\\Controllers\\Admin\\DocumentController@businessDocuments');
$router->get('/admin/documents/customer', 'App\\Http\\Controllers\\Admin\\DocumentController@customerDocuments');
$router->get('/admin/documents/user', 'App\\Http\\Controllers\\Admin\\DocumentController@userDocuments');
$router->get('/admin/documents/property', 'App\\Http\\Controllers\\Admin\\DocumentController@propertyDocuments');
$router->get('/admin/documents/generated', 'App\\Http\\Controllers\\Admin\\DocumentController@generatedDocuments');
$router->get('/admin/documents/ocr', 'App\\Http\\Controllers\\Admin\\DocumentController@ocrDocuments');
$router->get('/admin/documents/search', 'App\\Http\\Controllers\\Admin\\DocumentController@search');

// AI Chatbot
$router->get('/admin/chatbot', 'App\\Http\\Controllers\\Admin\\AIChatbotController@index');
$router->get('/admin/ai-chatbot', 'App\\Http\\Controllers\\Admin\\AIChatbotController@index');
$router->get('/admin/chatbot/settings', 'App\\Http\\Controllers\\Admin\\AIChatbotController@settings');
$router->post('/admin/chatbot/settings', 'App\\Http\\Controllers\\Admin\\AIChatbotController@saveSettings');
$router->get('/admin/chatbot/analytics', 'App\\Http\\Controllers\\Admin\\AIChatbotController@analytics');
$router->get('/admin/chatbot/train', 'App\\Http\\Controllers\\Admin\\AIChatbotController@train');
$router->post('/admin/chatbot/train/store', 'App\\Http\\Controllers\\Admin\\AIChatbotController@storeTraining');
$router->get('/admin/chatbot/train/toggle/{id}', 'App\\Http\\Controllers\\Admin\\AIChatbotController@toggleTraining');
$router->get('/admin/chatbot/train/delete/{id}', 'App\\Http\\Controllers\\Admin\\AIChatbotController@deleteTraining');
$router->get('/admin/ai-analytics', 'App\\Http\\Controllers\\Admin\\AIAnalyticsController@index');

// AI Calling
$router->get('/admin/ai-calling/dashboard', 'App\\Http\\Controllers\\Admin\\AICallingController@dashboard');
$router->get('/admin/ai-calling/schedule', 'App\\Http\\Controllers\\Admin\\AICallingController@schedule');
$router->get('/admin/ai-calling/sessions', 'App\\Http\\Controllers\\Admin\\AICallingController@sessions');
$router->get('/admin/ai-calling/extracted-leads', 'App\\Http\\Controllers\\Admin\\AICallingController@extractedLeads');
$router->get('/admin/ai-calling/training', 'App\\Http\\Controllers\\Admin\\AICallingController@training');

// Telecalling
$router->get('/admin/telecalling/dashboard', 'App\\Http\\Controllers\\Employee\\TelecallingController@dashboard');
$router->get('/admin/telecalling/assign', 'App\\Http\\Controllers\\Employee\\TelecallingController@assign');
$router->get('/admin/telecalling/commissions', 'App\\Http\\Controllers\\Employee\\TelecallingController@commissions');
$router->get('/admin/telecalling/approvals', 'App\\Http\\Controllers\\Employee\\TelecallingController@approvals');

// CRM Customers
$router->get('/admin/customers', 'App\\Http\\Controllers\\Admin\\CustomerController@index');
$router->get('/admin/crm', 'App\\Http\\Controllers\\Admin\\CRMController@index');
$router->get('/admin/crm/customers', 'App\\Http\\Controllers\\Admin\\CRMController@customers');
$router->get('/admin/crm/customers/create', 'App\\Http\\Controllers\\Admin\\CRMController@createCustomer');
$router->get('/admin/crm/groups', 'App\\Http\\Controllers\\Admin\\CRMController@groups');
$router->get('/admin/crm/followups', 'App\\Http\\Controllers\\Admin\\CRMController@followups');

// Ad Manager
$router->get('/admin/ads', 'App\\Http\\Controllers\\Admin\\AdManagerController@index');
$router->get('/admin/ads/create', 'App\\Http\\Controllers\\Admin\\AdManagerController@create');
$router->post('/admin/ads/create', 'App\\Http\\Controllers\\Admin\\AdManagerController@create');
$router->get('/admin/ads/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdManagerController@edit');
$router->post('/admin/ads/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdManagerController@edit');
$router->get('/admin/ads/delete/{id}', 'App\\Http\\Controllers\\Admin\\AdManagerController@delete');

// Ad click tracking
$router->get('/ad-click/{id}', function($id) {
    try {
        $svc = new \App\Services\AdManagerService();
        $svc->incrementClicks((int)$id);
    } catch (\Exception $e) {}
    $ref = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $ref);
    exit;
});

// Ad settings
$router->get('/admin/ads/settings', 'App\\Http\\Controllers\\Admin\\AdManagerController@settings');
$router->post('/admin/ads/save-settings', 'App\\Http\\Controllers\\Admin\\AdManagerController@saveSettings');

// Jobs & Applicants
$router->get('/admin/jobs', 'App\\Http\\Controllers\\Admin\\CareerController@index');
$router->get('/admin/applicants', 'App\\Http\\Controllers\\Admin\\CareerController@applicants');

// Testimonials
$router->get('/admin/testimonials/manage', 'App\\Http\\Controllers\\Admin\\TestimonialController@manage');

// Financial
$router->get('/admin/emi-calculator', 'App\\Http\\Controllers\\Admin\\EMIController@calculator');
$router->get('/admin/loans', 'App\\Http\\Controllers\\Admin\\LoanController@index');

// Users
$router->get('/admin/agents', 'App\\Http\\Controllers\\Admin\\AgentController@index');
$router->get('/admin/builders', 'App\\Http\\Controllers\\Admin\\BuilderController@index');

// Reports
$router->get('/admin/reports/financial', 'App\\Http\\Controllers\\Admin\\Reports\\FinancialReportController@index');

// HRM
$router->get('/admin/hrm', 'App\\Http\\Controllers\\Admin\\HRMController@index');
$router->get('/admin/hrm/employees/create', 'App\\Http\\Controllers\\Admin\\HRMController@createEmployee');
$router->get('/admin/hrm/attendance', 'App\\Http\\Controllers\\Admin\\HRMController@attendance');
$router->get('/admin/hrm/leave', 'App\\Http\\Controllers\\Admin\\HRMController@leave');

// Backup
$router->get('/admin/backup', 'App\\Http\\Controllers\\Admin\\BackupController@index');

// Services
$router->get('/admin/services/home-loan', 'App\\Http\\Controllers\\Admin\\ServiceController@homeLoan');
$router->get('/admin/services/legal', 'App\\Http\\Controllers\\Admin\\ServiceController@legal');
$router->get('/admin/services/interior', 'App\\Http\\Controllers\\Admin\\ServiceController@interior');
$router->get('/admin/services/tax', 'App\\Http\\Controllers\\Admin\\ServiceController@propertyTax');

// HRM Remaining
$router->get('/admin/hrm/payroll', 'App\\Http\\Controllers\\Admin\\HRMController@payroll');
$router->get('/admin/hrm/salary-slips', 'App\\Http\\Controllers\\Admin\\HRMController@salarySlips');
$router->get('/admin/hrm/performance', 'App\\Http\\Controllers\\Admin\\HRMController@performance');
$router->get('/admin/hrm/recruitment', 'App\\Http\\Controllers\\Admin\\HRMController@recruitment');
$router->get('/admin/hrm/jobs', 'App\\Http\\Controllers\\Admin\\HRMController@jobs');
$router->get('/admin/hrm/applicants', 'App\\Http\\Controllers\\Admin\\HRMController@applicants');
$router->get('/admin/hrm/documents', 'App\\Http\\Controllers\\Admin\\HRMController@documents');
$router->get('/admin/hrm/departments', 'App\\Http\\Controllers\\Admin\\HRMController@departments');
$router->get('/admin/hrm/designations', 'App\\Http\\Controllers\\Admin\\HRMController@designations');
$router->get('/admin/hrm/settings', 'App\\Http\\Controllers\\Admin\\HRMController@settings');

// CRM Remaining
$router->get('/admin/crm/feedback', 'App\\Http\\Controllers\\Admin\\CRMController@feedback');
$router->get('/admin/crm/support', 'App\\Http\\Controllers\\Admin\\CRMController@support');

// Customer Portal v2
$router->get('/user/book-site-visit', 'Front\\UserController@bookSiteVisit');
$router->post('/user/book-site-visit', 'Front\\UserController@bookSiteVisit');
$router->get('/user/notifications', function() {
    $c = new \App\Http\Controllers\NotificationController();
    $c->index();
});
$router->get('/user/payments', function() {
    header('Location: ' . BASE_URL . '/payment/history');
    exit;
});

// Lead source analytics
$router->get('/admin/leads/sources', 'Admin\\LeadController@sources');

// Advanced Reports
$router->get('/admin/reports/funnel', 'Admin\AdvancedReportController@funnel');
$router->get('/admin/reports/agent-performance', 'Admin\AdvancedReportController@agentPerformance');
$router->get('/admin/reports/conversion', 'Admin\AdvancedReportController@conversion');

// MLM Real Estate Enterprise
$router->get('/admin/mlm-realestate', 'Admin\MLMRealEstateController@dashboard');
$router->get('/admin/mlm-realestate/packages', 'Admin\MLMRealEstateController@packages');
$router->post('/admin/mlm-realestate/packages/save', 'Admin\MLMRealEstateController@savePackage');
$router->get('/admin/mlm-realestate/networkers', 'Admin\MLMRealEstateController@networkers');
$router->post('/admin/mlm-realestate/networkers/register', 'Admin\MLMRealEstateController@registerNetworker');
$router->get('/admin/mlm-realestate/free-consultants', 'Admin\MLMRealEstateController@freeConsultants');
$router->post('/admin/mlm-realestate/free-consultants/register', 'Admin\MLMRealEstateController@registerConsultant');
$router->get('/admin/mlm-realestate/rera', 'Admin\MLMRealEstateController@reraRequests');
$router->post('/admin/mlm-realestate/rera/approve', 'Admin\MLMRealEstateController@approveRERA');
$router->get('/admin/mlm-realestate/plots', 'Admin\MLMRealEstateController@plotsInventory');
$router->get('/admin/mlm-realestate/bookings', 'Admin\MLMRealEstateController@bookings');
$router->get('/admin/mlm-realestate/bookings/{id}', 'Admin\MLMRealEstateController@bookingDetail');
$router->get('/admin/mlm-realestate/bookings/{id}/approve', 'Admin\MLMRealEstateController@approveBooking');
$router->post('/admin/mlm-realestate/bookings/{id}/reject', 'Admin\MLMRealEstateController@rejectBooking');
$router->post('/admin/mlm-realestate/bookings/payment', 'Admin\MLMRealEstateController@recordPayment');
$router->post('/admin/mlm-realestate/bookings/commission', 'Admin\MLMRealEstateController@processCommission');
$router->get('/admin/mlm-realestate/bookings/create', 'Admin\MLMRealEstateController@createBooking');
$router->post('/admin/mlm-realestate/bookings/store', 'Admin\MLMRealEstateController@storeBooking');
$router->get('/admin/mlm-realestate/salary', 'Admin\MLMRealEstateController@salaryTracker');
$router->get('/admin/mlm-realestate/salary/evaluate', 'Admin\MLMRealEstateController@evaluateSalary');
$router->get('/admin/mlm-realestate/cron', 'Admin\MLMRealEstateController@runCron');

// Language switcher
$router->get('/language/set/{lang}', function($lang) {
    $allowed = ['en', 'hi'];
    if (in_array($lang, $allowed)) {
        $_SESSION['user_language'] = $lang;
        setcookie('user_language', $lang, time() + 86400 * 30, '/');
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
    header('Location: ' . $referer);
    exit;
});

// ============================================================
// PWA (Progressive Web App)
// ============================================================
$router->get('/pwa/service-worker', 'Tech\\PWAController@serviceWorker');
$router->get('/pwa/manifest', 'Tech\\PWAController@manifest');
$router->get('/pwa/offline', 'Tech\\PWAController@offline');

// ═══════════════════════════════════════════════════
// CUSTOMER LEAD EXTRAS MANAGEMENT (New System)
// ═══════════════════════════════════════════════════
$router->get('/admin/customer-lead/behavior', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@behavior');
$router->get('/admin/customer-lead/behavior/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@showBehavior');
$router->get('/admin/customer-lead/journeys', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@journeys');
$router->get('/admin/customer-lead/journeys/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@showJourney');
$router->get('/admin/customer-lead/lead-scores', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@leadScores');
$router->post('/admin/customer-lead/lead-scores/update/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@updateLeadScore');
$router->get('/admin/customer-lead/events', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@events');
$router->get('/admin/customer-lead/events/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@showEvent');
$router->get('/admin/customer-lead/custom-fields', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@customFields');
$router->post('/admin/customer-lead/custom-fields/store', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@storeCustomField');
$router->post('/admin/customer-lead/custom-fields/update/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@updateCustomField');
$router->post('/admin/customer-lead/custom-fields/delete/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@deleteCustomField');
$router->get('/admin/customer-lead/approvals', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@approvals');
$router->get('/admin/customer-lead/approvals/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@showApproval');
$router->post('/admin/customer-lead/approvals/update-status/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@updateApprovalStatus');
$router->get('/admin/customer-lead/file-extractions', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@fileExtractions');

// ═══════════════════════════════════════════════════
// HR MANAGEMENT SYSTEM (New System)
// ═══════════════════════════════════════════════════
$router->get('/admin/hr', 'App\\Http\\Controllers\\Admin\\HRController@index');
$router->get('/admin/hr/employees', 'App\\Http\\Controllers\\Admin\\HRController@employees');
$router->get('/admin/hr/employees/create', 'App\\Http\\Controllers\\Admin\\HRController@createEmployee');
$router->post('/admin/hr/employees/store', 'App\\Http\\Controllers\\Admin\\HRController@storeEmployee');
$router->get('/admin/hr/employees/edit/{id}', 'App\\Http\\Controllers\\Admin\\HRController@editEmployee');
$router->post('/admin/hr/employees/update/{id}', 'App\\Http\\Controllers\\Admin\\HRController@updateEmployee');
$router->get('/admin/hr/employees/delete/{id}', 'App\\Http\\Controllers\\Admin\\HRController@deleteEmployee');
$router->get('/admin/hr/employees/view/{id}', 'App\\Http\\Controllers\\Admin\\HRController@viewEmployee');
$router->get('/admin/hr/attendance', 'App\\Http\\Controllers\\Admin\\HRController@attendance');
$router->post('/admin/hr/attendance/mark', 'App\\Http\\Controllers\\Admin\\HRController@markAttendance');
$router->get('/admin/hr/attendance/report', 'App\\Http\\Controllers\\Admin\\HRController@attendanceReport');
$router->get('/admin/hr/leaves', 'App\\Http\\Controllers\\Admin\\HRController@leaves');
$router->post('/admin/hr/leaves/store', 'App\\Http\\Controllers\\Admin\\HRController@storeLeave');
$router->get('/admin/hr/leaves/approve/{id}', 'App\\Http\\Controllers\\Admin\\HRController@approveLeave');
$router->get('/admin/hr/leaves/reject/{id}', 'App\\Http\\Controllers\\Admin\\HRController@rejectLeave');
$router->get('/admin/hr/leave-types', 'App\\Http\\Controllers\\Admin\\HRController@leaveTypes');
$router->post('/admin/hr/leave-types/store', 'App\\Http\\Controllers\\Admin\\HRController@storeLeaveType');
$router->get('/admin/hr/leave-balances', 'App\\Http\\Controllers\\Admin\\HRController@leaveBalances');
$router->get('/admin/hr/shifts', 'App\\Http\\Controllers\\Admin\\HRController@shifts');
$router->post('/admin/hr/shifts/store', 'App\\Http\\Controllers\\Admin\\HRController@storeShift');
$router->any('/admin/hr/shifts/assign', 'App\\Http\\Controllers\\Admin\\HRController@assignShift');
$router->get('/admin/hr/shifts/schedule', 'App\\Http\\Controllers\\Admin\\HRController@shiftSchedule');
$router->get('/admin/hr/kpis', 'App\\Http\\Controllers\\Admin\\HRController@kpis');
$router->post('/admin/hr/kpis/store', 'App\\Http\\Controllers\\Admin\\HRController@storeKpi');
$router->get('/admin/hr/performance', 'App\\Http\\Controllers\\Admin\\HRController@performance');
$router->post('/admin/hr/performance/store', 'App\\Http\\Controllers\\Admin\\HRController@storeReview');
$router->get('/admin/hr/bonuses', 'App\\Http\\Controllers\\Admin\\HRController@bonuses');
$router->post('/admin/hr/bonuses/store', 'App\\Http\\Controllers\\Admin\\HRController@storeBonus');
$router->get('/admin/hr/salary-structure', 'App\\Http\\Controllers\\Admin\\HRController@salaryStructure');
$router->post('/admin/hr/salary-structure/store', 'App\\Http\\Controllers\\Admin\\HRController@storeSalaryStructure');
$router->get('/admin/hr/salary-structure/edit/{id}', 'App\\Http\\Controllers\\Admin\\HRController@editSalaryStructure');
$router->post('/admin/hr/salary-structure/update/{id}', 'App\\Http\\Controllers\\Admin\\HRController@updateSalaryStructure');
$router->get('/admin/hr/documents', 'App\\Http\\Controllers\\Admin\\HRController@employeeDocuments');
$router->post('/admin/hr/documents/upload', 'App\\Http\\Controllers\\Admin\\HRController@uploadEmployeeDocument');
$router->get('/admin/hr/activities', 'App\\Http\\Controllers\\Admin\\HRController@activities');
$router->get('/admin/hr/report', 'App\\Http\\Controllers\\Admin\\HRController@employeeReport');
$router->get('/admin/hr/settings', 'App\\Http\\Controllers\\Admin\\HRController@settings');

// ═══════════════════════════════════════════════════
// WORK SCHEDULE & SHIFT MANAGEMENT (New System)
// ═══════════════════════════════════════════════════
$router->get('/admin/schedule', 'App\\Http\\Controllers\\Admin\\ScheduleController@index');
$router->get('/admin/schedule/shift-types', 'App\\Http\\Controllers\\Admin\\ScheduleController@shiftTypes');
$router->post('/admin/schedule/shift-types/store', 'App\\Http\\Controllers\\Admin\\ScheduleController@storeShiftType');
$router->post('/admin/schedule/shift-types/update/{id}', 'App\\Http\\Controllers\\Admin\\ScheduleController@updateShiftType');
$router->post('/admin/schedule/shift-types/delete/{id}', 'App\\Http\\Controllers\\Admin\\ScheduleController@deleteShiftType');
$router->get('/admin/schedule/employee-shifts', 'App\\Http\\Controllers\\Admin\\ScheduleController@employeeShifts');
$router->post('/admin/schedule/assign-shift', 'App\\Http\\Controllers\\Admin\\ScheduleController@assignShift');
$router->post('/admin/schedule/assignments/update/{id}', 'App\\Http\\Controllers\\Admin\\ScheduleController@updateAssignment');
$router->post('/admin/schedule/assignments/remove/{id}', 'App\\Http\\Controllers\\Admin\\ScheduleController@removeAssignment');
$router->get('/admin/schedule/shift-schedule', 'App\\Http\\Controllers\\Admin\\ScheduleController@shiftSchedule');
$router->post('/admin/schedule/create', 'App\\Http\\Controllers\\Admin\\ScheduleController@createSchedule');
$router->post('/admin/schedule/bulk', 'App\\Http\\Controllers\\Admin\\ScheduleController@bulkSchedule');
$router->get('/admin/schedule/work-schedules', 'App\\Http\\Controllers\\Admin\\ScheduleController@workSchedules');
$router->post('/admin/schedule/work-schedules/store', 'App\\Http\\Controllers\\Admin\\ScheduleController@storeWorkSchedule');
$router->get('/admin/schedule/weekly', 'App\\Http\\Controllers\\Admin\\ScheduleController@weeklyView');
$router->get('/admin/schedule/rotation', 'App\\Http\\Controllers\\Admin\\ScheduleController@rotation');

// ═══════════════════════════════════════════════════
// SALARY & PAYMENT MANAGEMENT SYSTEM (New System)
// ═══════════════════════════════════════════════════
$router->get('/admin/salary', 'App\\Http\\Controllers\\Admin\\SalaryController@index');
$router->get('/admin/salary/stats', 'App\\Http\\Controllers\\Admin\\SalaryController@stats');
$router->get('/admin/salary/structures', 'App\\Http\\Controllers\\Admin\\SalaryController@structures');
$router->get('/admin/salary/structures/edit/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@editStructure');
$router->post('/admin/salary/structures/store', 'App\\Http\\Controllers\\Admin\\SalaryController@storeStructure');
$router->post('/admin/salary/structures/update/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@updateStructure');
$router->get('/admin/salary/payments', 'App\\Http\\Controllers\\Admin\\SalaryController@payments');
$router->get('/admin/salary/payments/create', 'App\\Http\\Controllers\\Admin\\SalaryController@createPayment');
$router->post('/admin/salary/payments/store', 'App\\Http\\Controllers\\Admin\\SalaryController@storePayment');
$router->get('/admin/salary/payments/view/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@viewPayment');
$router->post('/admin/salary/payments/bulk', 'App\\Http\\Controllers\\Admin\\SalaryController@processBulk');
$router->get('/admin/salary/payouts', 'App\\Http\\Controllers\\Admin\\SalaryController@payouts');
$router->post('/admin/salary/payouts/create', 'App\\Http\\Controllers\\Admin\\SalaryController@createPayout');
$router->post('/admin/salary/payouts/process/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@processPayout');
$router->get('/admin/salary/history', 'App\\Http\\Controllers\\Admin\\SalaryController@history');
$router->get('/admin/salary/history/employee/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@historyByEmployee');
$router->get('/admin/salary/contracts', 'App\\Http\\Controllers\\Admin\\SalaryController@contracts');
$router->post('/admin/salary/contracts/store', 'App\\Http\\Controllers\\Admin\\SalaryController@storeContract');
$router->get('/admin/salary/contracts/view/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@viewContract');
$router->post('/admin/salary/contracts/terminate/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@terminateContract');
$router->get('/admin/salary/plans', 'App\\Http\\Controllers\\Admin\\SalaryController@plans');
$router->post('/admin/salary/plans/store', 'App\\Http\\Controllers\\Admin\\SalaryController@storePlan');
$router->post('/admin/salary/plans/update/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@updatePlan');
$router->get('/admin/salary/records', 'App\\Http\\Controllers\\Admin\\SalaryController@records');
$router->get('/admin/salary/records/{year}/{month}', 'App\\Http\\Controllers\\Admin\\SalaryController@recordByMonth');
$router->get('/admin/salary/tracker', 'App\\Http\\Controllers\\Admin\\SalaryController@tracker');
$router->post('/admin/salary/tracker/update/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@updateTracker');
$router->get('/admin/salary/report', 'App\\Http\\Controllers\\Admin\\SalaryController@report');
$router->get('/admin/salary/export-csv', 'App\\Http\\Controllers\\Admin\\SalaryController@exportCSV');
$router->get('/admin/salary/payroll-integration', 'App\\Http\\Controllers\\Admin\\SalaryController@payrollIntegration');

// ═══════════════════════════════════════════════════
// AI MANAGEMENT (Integrations, Chatbot Logs, Memory)
// ═══════════════════════════════════════════════════
$router->get('/admin/ai-management/integrations', 'App\\Http\\Controllers\\Admin\\AIManagementController@integrations');
$router->post('/admin/ai-management/toggle-integration/{id}', 'App\\Http\\Controllers\\Admin\\AIManagementController@toggleIntegration');
$router->get('/admin/ai-management/chatbot-logs', 'App\\Http\\Controllers\\Admin\\AIManagementController@chatbotLogs');
$router->get('/admin/ai-management/context-memory', 'App\\Http\\Controllers\\Admin\\AIManagementController@contextMemory');
$router->get('/admin/ai-management/generated-content', 'App\\Http\\Controllers\\Admin\\AIManagementController@generatedContent');
$router->post('/admin/ai-management/toggle-content/{id}', 'App\\Http\\Controllers\\Admin\\AIManagementController@toggleContent');
$router->get('/admin/ai-management/lead-scores', 'App\\Http\\Controllers\\Admin\\AIManagementController@leadScores');
$router->get('/admin/ai-management/suggestions', 'App\\Http\\Controllers\\Admin\\AIManagementController@suggestions');
$router->get('/admin/ai-management/usage-analytics', 'App\\Http\\Controllers\\Admin\\AIManagementController@usageAnalytics');

// ═══════════════════════════════════════════════════
// COMPANY SETTINGS
// ═══════════════════════════════════════════════════
$router->get('/admin/company/settings', 'App\\Http\\Controllers\\Admin\\CompanyController@settings');
$router->post('/admin/company/settings', 'App\\Http\\Controllers\\Admin\\CompanyController@updateSettings');
$router->get('/admin/company/employees', 'App\\Http\\Controllers\\Admin\\CompanyController@employees');
$router->post('/admin/company/employees', 'App\\Http\\Controllers\\Admin\\CompanyController@addEmployee');

// ═══════════════════════════════════════════════════
// GST INVOICE MANAGEMENT
// ═══════════════════════════════════════════════════
$router->get('/admin/gst', 'App\\Http\\Controllers\\Admin\\GstController@index');
$router->get('/admin/gst/create', 'App\\Http\\Controllers\\Admin\\GstController@create');
$router->post('/admin/gst/store', 'App\\Http\\Controllers\\Admin\\GstController@store');
$router->get('/admin/gst/{id}', 'App\\Http\\Controllers\\Admin\\GstController@show');

// ═══════════════════════════════════════════════════
// KYC DOCUMENT VERIFICATION
// ═══════════════════════════════════════════════════
$router->get('/admin/kyc', 'App\\Http\\Controllers\\Admin\\KycController@index');
$router->get('/admin/kyc/pending', 'App\\Http\\Controllers\\Admin\\KycController@pending');
$router->get('/admin/kyc/{id}', 'App\\Http\\Controllers\\Admin\\KycController@show');
$router->post('/admin/kyc/{id}/verify', 'App\\Http\\Controllers\\Admin\\KycController@verify');

// ═══════════════════════════════════════════════════
// PAYROLL MANAGEMENT
// ═══════════════════════════════════════════════════
$router->get('/admin/payroll', 'App\\Http\\Controllers\\Admin\\PayrollController@index');
$router->get('/admin/payroll/create', 'App\\Http\\Controllers\\Admin\\PayrollController@create');
$router->post('/admin/payroll/store', 'App\\Http\\Controllers\\Admin\\PayrollController@store');
$router->get('/admin/payroll/{id}/edit', 'App\\Http\\Controllers\\Admin\\PayrollController@edit');
$router->post('/admin/payroll/{id}/update', 'App\\Http\\Controllers\\Admin\\PayrollController@update');
$router->get('/admin/payroll/advances', 'App\\Http\\Controllers\\Admin\\PayrollController@advances');
$router->post('/admin/payroll/advances/add', 'App\\Http\\Controllers\\Admin\\PayrollController@addAdvance');

// ═══════════════════════════════════════════════════
// TRAINING COURSES
// ═══════════════════════════════════════════════════
$router->get('/admin/training', 'App\\Http\\Controllers\\Admin\\TrainingController@courses');
$router->get('/admin/training/create', 'App\\Http\\Controllers\\Admin\\TrainingController@createCourse');
$router->post('/admin/training/store', 'App\\Http\\Controllers\\Admin\\TrainingController@storeCourse');
$router->get('/admin/training/{id}/edit', 'App\\Http\\Controllers\\Admin\\TrainingController@editCourse');
$router->post('/admin/training/{id}/update', 'App\\Http\\Controllers\\Admin\\TrainingController@updateCourse');
$router->get('/admin/training/modules', 'App\\Http\\Controllers\\Admin\\TrainingController@modules');
$router->post('/admin/training/modules/store', 'App\\Http\\Controllers\\Admin\\TrainingController@storeModule');
$router->get('/admin/training/enrollments', 'App\\Http\\Controllers\\Admin\\TrainingController@enrollments');
$router->get('/admin/training/enrollments/{id}', 'App\\Http\\Controllers\\Admin\\TrainingController@showEnrollment');
$router->get('/admin/training/certificates', 'App\\Http\\Controllers\\Admin\\TrainingController@certificates');

// ═══════════════════════════════════════════════════
// VOICE AGENTS
// ═══════════════════════════════════════════════════
$router->get('/admin/voice-agents', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@dashboard');
$router->get('/admin/voice-agents/dashboard', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@dashboard');
$router->get('/admin/voice-agents/history', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@history');
$router->get('/admin/voice-agents/schedule', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@schedule');
$router->post('/admin/voice-agents/bulk-schedule', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@bulkSchedule');
$router->post('/admin/voice-agents/auto-assign', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@autoAssign');
$router->get('/admin/voice-agents/scripts', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@scripts');
$router->get('/admin/voice-agents/extracted-leads', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@extractedLeads');
$router->get('/admin/voice-agents/settings', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@settings');
$router->get('/admin/voice-agents/oln-dashboard', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@olnDashboard');
$router->post('/admin/voice-agents/cancel-schedule/{id}', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@cancelSchedule');
$router->post('/admin/voice-agents/reschedule/{id}', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@rescheduleCall');
$router->post('/admin/voice-agents/ajax/convert-lead', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@ajaxConvertLead');
$router->get('/admin/voice-agents/ajax/lead-timeline/{id}', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@ajaxLeadTimeline');

// ═══════════════════════════════════════════════════
// FARMERS MANAGEMENT (Admin)
// ═══════════════════════════════════════════════════
$router->get('/admin/farmers/manage', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@index');
$router->get('/admin/farmers/manage/{id}', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@show');
$router->get('/admin/farmers/agreements', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@agreements');
$router->get('/admin/farmers/agreements/{id}', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@showAgreement');
$router->post('/admin/farmers/agreements/store', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@storeAgreement');
$router->post('/admin/farmers/agreements/{id}/status', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@updateAgreementStatus');
$router->get('/admin/farmers/loans', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@loans');
$router->get('/admin/farmers/loans/{id}', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@showLoan');
$router->post('/admin/farmers/loans/store', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@storeLoan');
$router->post('/admin/farmers/loans/{id}/status', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@updateLoanStatus');
$router->get('/admin/farmers/gata', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@gata');
$router->post('/admin/farmers/gata/store', 'App\\Http\\Controllers\\Admin\\FarmerAdminController@storeGata');

// ═══════════════════════════════════════════════════
// PROJECT PROGRESS TRACKING
// ═══════════════════════════════════════════════════
$router->get('/admin/projects/progress', 'App\\Http\\Controllers\\Admin\\ProjectProgressController@index');
$router->get('/admin/projects/progress/{id}', 'App\\Http\\Controllers\\Admin\\ProjectProgressController@show');
$router->post('/admin/projects/progress/{id}/update', 'App\\Http\\Controllers\\Admin\\ProjectProgressController@updateProgress');
$router->get('/admin/projects/progress/{id}/budget', 'App\\Http\\Controllers\\Admin\\ProjectProgressController@budget');

// ═══════════════════════════════════════════════════
// PROPERTY FEATURES (Ratings, Reviews, Favorites, Maintenance)
// ═══════════════════════════════════════════════════
$router->get('/admin/property-features/ratings', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@ratings');
$router->post('/admin/property-features/reviews/{id}/status', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@updateReviewStatus');
$router->get('/admin/property-features/favorites', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@favorites');
$router->get('/admin/property-features/maintenance', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@maintenance');
$router->get('/admin/property-features/maintenance/{id}', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@showMaintenance');
$router->post('/admin/property-features/maintenance/{id}/status', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@updateMaintenanceStatus');
$router->post('/admin/property-features/maintenance/assign', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@assignMaintenance');
$router->get('/admin/property-features/market-data', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@marketData');
$router->post('/admin/property-features/market-data/store', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@storeMarketData');
$router->get('/admin/property-features/analytics', 'App\\Http\\Controllers\\Admin\\PropertyFeaturesController@analytics');

// ═══════════════════════════════════════════════════
// BANKING & TRANSACTIONS
// ═══════════════════════════════════════════════════
$router->get('/admin/banking', 'App\\Http\\Controllers\\Admin\\BankingController@index');
$router->get('/admin/banking/{id}', 'App\\Http\\Controllers\\Admin\\BankingController@show');
$router->get('/admin/banking/reconciliation', 'App\\Http\\Controllers\\Admin\\BankingController@reconciliation');
$router->post('/admin/banking/reconcile', 'App\\Http\\Controllers\\Admin\\BankingController@reconcile');
$router->get('/admin/banking/financial-years', 'App\\Http\\Controllers\\Admin\\BankingController@financialYears');

// ═══════════════════════════════════════════════════
// MLM REWARDS & RANK CRITERIA
// ═══════════════════════════════════════════════════
$router->get('/admin/mlm-rewards', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@rankCriteria');
$router->post('/admin/mlm-rewards/rank-criteria/store', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@storeRankCriteria');
$router->get('/admin/mlm-rewards/rewards', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@rewards');
$router->get('/admin/mlm-rewards/withdrawals', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@withdrawals');
$router->post('/admin/mlm-rewards/withdrawals/{id}/status', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@updateWithdrawalStatus');
$router->get('/admin/mlm-rewards/upgrades', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@upgrades');

// ═══════════════════════════════════════════════════
// LEGAL SERVICES (Admin)
// ═══════════════════════════════════════════════════
$router->get('/admin/legal/services', 'App\\Http\\Controllers\\Admin\\LegalController@services');
$router->get('/admin/legal/services/create', 'App\\Http\\Controllers\\Admin\\LegalController@createService');
$router->post('/admin/legal/services/store', 'App\\Http\\Controllers\\Admin\\LegalController@storeService');
$router->get('/admin/legal/disputes', 'App\\Http\\Controllers\\Admin\\LegalController@disputes');
$router->get('/admin/legal/disputes/{id}', 'App\\Http\\Controllers\\Admin\\LegalController@showDispute');
$router->post('/admin/legal/disputes/{id}/update', 'App\\Http\\Controllers\\Admin\\LegalController@updateDispute');
$router->get('/admin/legal/deadlines', 'App\\Http\\Controllers\\Admin\\LegalController@deadlines');
$router->post('/admin/legal/deadlines/store', 'App\\Http\\Controllers\\Admin\\LegalController@storeDeadline');

// ═══════════════════════════════════════════════════
// TELECALLER MANAGEMENT (Admin)
// ═══════════════════════════════════════════════════
$router->get('/admin/telecaller', 'App\\Http\\Controllers\\Admin\\TelecallerController@index');
$router->get('/admin/telecaller/{id}', 'App\\Http\\Controllers\\Admin\\TelecallerController@showTask');
$router->post('/admin/telecaller/store', 'App\\Http\\Controllers\\Admin\\TelecallerController@store');
$router->get('/admin/telecaller/performance', 'App\\Http\\Controllers\\Admin\\TelecallerController@performance');
$router->get('/admin/telecaller/performance/{id}', 'App\\Http\\Controllers\\Admin\\TelecallerController@showPerformance');
$router->post('/admin/telecaller/performance/update', 'App\\Http\\Controllers\\Admin\\TelecallerController@updatePerformance');

// ═══════════════════════════════════════════════════
// MARKETPLACE, COMPLIANCE, DEVELOPER, ANALYTICS, PERFORMANCE, SECURITY
// ═══════════════════════════════════════════════════
$router->get('/admin/marketplace', 'App\\Http\\Controllers\\Admin\\AdminMarketplaceController@index');
$router->get('/admin/compliance', 'App\\Http\\Controllers\\Admin\\AdminComplianceController@index');
$router->get('/admin/developer', 'App\\Http\\Controllers\\Admin\\AdminDeveloperController@index');
$router->get('/admin/analytics/advanced', 'App\\Http\\Controllers\\Admin\\AnalyticsController@advanced');
$router->get('/admin/performance', 'App\\Http\\Controllers\\Admin\\AdminPerformanceController@index');
$router->get('/admin/security', 'App\\Http\\Controllers\\Admin\\AdminSecurityController@index');

// ============================================================
// REDIRECT ROUTES (backward compatibility with different URL styles)
// ============================================================

// /admin/workflow (no s) -> /admin/workflows
$router->get('/admin/workflow', function() {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/workflows');
    exit;
});
$router->get('/admin/workflow/approvals', function() {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/workflows/pending');
    exit;
});
$router->get('/admin/workflow/reports', function() {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/workflows');
    exit;
});

// /admin/schedule/weekly-view -> /admin/schedule/weekly
$router->get('/admin/schedule/weekly-view', function() {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/schedule/weekly');
    exit;
});

// /admin/customer-leads -> /admin/customer-lead dashboard
$router->get('/admin/customer-leads', function() {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/customer-lead/behavior');
    exit;
});

// /admin/farmers -> /farmers (public)
$router->get('/admin/farmers', function() {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/farmers');
    exit;
});

// Training courses redirect (sidebar link alias)
$router->get('/admin/training/courses', function() {
    $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    header('Location: ' . $base . '/admin/training');
    exit;
});

// ============================================================
// NOTIFICATION ROUTES
// ============================================================
$router->get('/admin/notifications', 'App\\Http\\Controllers\\Admin\\AdminNotificationController@index');
$router->get('/admin/notifications/panel', 'App\\Http\\Controllers\\Admin\\AdminNotificationController@panel');
$router->post('/admin/notifications/mark-read/{id}', 'App\\Http\\Controllers\\Admin\\AdminNotificationController@markRead');
$router->post('/admin/notifications/mark-all-read', 'App\\Http\\Controllers\\Admin\\AdminNotificationController@markAllRead');

// ============================================================
// TECH: BLOCKCHAIN PROPERTY VERIFICATION
// ============================================================
$router->get('/blockchain/dashboard', 'Tech\\BlockchainController@verificationDashboard');
$router->any('/blockchain/verify/{id}', 'Tech\\BlockchainController@verifyProperty');
$router->get('/blockchain/certificate/{id}', 'Tech\\BlockchainController@viewCertificate');
$router->get('/blockchain/transactions/{id}', 'Tech\\BlockchainController@transactionHistory');
$router->get('/blockchain/generate-certificate/{id}', 'Tech\\BlockchainController@generateCertificate');
$router->get('/blockchain/explorer/{id}', 'Tech\\BlockchainController@blockchainExplorer');
$router->get('/blockchain/smart-contract', 'Tech\\BlockchainController@smartContract');
$router->get('/blockchain/nft-certificates', 'Tech\\BlockchainController@nftCertificates');
$router->any('/blockchain/transfer/{id}', 'Tech\\BlockchainController@transferOwnership');
$router->get('/blockchain/provenance/{id}', 'Tech\\BlockchainController@propertyProvenance');
$router->get('/blockchain/document-verification/{id}', 'Tech\\BlockchainController@documentVerification');
$router->any('/blockchain/signature/{id}', 'Tech\\BlockchainController@digitalSignature');
$router->get('/admin/blockchain', 'Tech\\BlockchainController@adminBlockchain');
$router->get('/admin/blockchain/process/{id}', 'Tech\\BlockchainController@processVerification');
$router->get('/admin/blockchain/analytics', 'Tech\\BlockchainController@blockchainAnalytics');
$router->get('/admin/blockchain/fraud-detection', 'Tech\\BlockchainController@fraudDetection');
$router->get('/api/blockchain/verification-status/{id}', 'Tech\\BlockchainController@apiVerificationStatus');
$router->post('/api/blockchain/verify-document', 'Tech\\BlockchainController@apiVerifyDocument');
$router->post('/api/blockchain/submit-documents', 'Tech\\BlockchainController@apiSubmitDocuments');

// ============================================================
// TECH: IoT SMART HOME
// ============================================================
$router->get('/iot/smart-home/{id}', 'Tech\\IoTController@smartHomeDashboard');
$router->any('/iot/devices', 'Tech\\IoTController@manageDevices');
$router->any('/iot/device-control/{id}', 'Tech\\IoTController@deviceControl');
$router->any('/iot/automation/{id}', 'Tech\\IoTController@automationRules');
$router->get('/iot/energy/{id}', 'Tech\\IoTController@energyMonitoring');
$router->get('/iot/security/{id}', 'Tech\\IoTController@securityMonitoring');
$router->get('/iot/device-catalog', 'Tech\\IoTController@deviceCatalog');
$router->get('/iot/demo', 'Tech\\IoTController@demo');
$router->get('/iot/market-insights', 'Tech\\IoTController@marketInsights');
$router->get('/iot/service-packages', 'Tech\\IoTController@servicePackages');
$router->get('/iot/installation-guide', 'Tech\\IoTController@installationGuide');
$router->get('/iot/troubleshooting', 'Tech\\IoTController@troubleshooting');
$router->any('/iot/roi-calculator', 'Tech\\IoTController@roiCalculator');
$router->get('/admin/iot/analytics', 'Tech\\IoTController@iotAnalytics');
$router->get('/api/iot/device-status/{id}', 'Tech\\IoTController@apiDeviceStatus');
$router->post('/api/iot/control-device', 'Tech\\IoTController@apiControlDevice');
$router->get('/api/iot/energy/{id}', 'Tech\\IoTController@apiEnergyData');
$router->post('/api/iot/compatibility', 'Tech\\IoTController@compatibilityCheck');
$router->any('/api/iot/integration', 'Tech\\IoTController@apiDeviceIntegration');

// ============================================================
// TECH: METAVERSE INTEGRATION
// ============================================================
$router->any('/metaverse/virtual-development', 'Tech\\MetaverseController@virtualDevelopment');
$router->get('/metaverse/collaborative-spaces', 'Tech\\MetaverseController@collaborativeSpace');
$router->get('/metaverse/collaborative-space/{id}', 'Tech\\MetaverseController@collaborativeSpace');
$router->any('/metaverse/create-space', 'Tech\\MetaverseController@createCollaborativeSpace');
$router->get('/metaverse/virtual-marketplace', 'Tech\\MetaverseController@virtualMarketplace');
$router->get('/metaverse/vr-showroom/{id}', 'Tech\\MetaverseController@vrShowroom');
$router->get('/metaverse/virtual-events', 'Tech\\MetaverseController@virtualEvents');
$router->get('/metaverse/nft-ownership/{id}', 'Tech\\MetaverseController@nftOwnership');
$router->get('/metaverse/vr-tours', 'Tech\\MetaverseController@vrTours');
$router->any('/metaverse/virtual-property/{id}', 'Tech\\MetaverseController@customizeVirtualProperty');
$router->get('/metaverse/social-hub', 'Tech\\MetaverseController@socialHub');
$router->get('/metaverse/virtual-economy', 'Tech\\MetaverseController@virtualEconomy');
$router->get('/metaverse/academy', 'Tech\\MetaverseController@metaverseAcademy');
$router->get('/metaverse/investment-portfolio', 'Tech\\MetaverseController@investmentPortfolio');
$router->get('/admin/metaverse/analytics', 'Tech\\MetaverseController@metaverseAnalytics');
$router->get('/api/metaverse/vr-data/{id}', 'Tech\\MetaverseController@apiVRData');
$router->post('/api/metaverse/create-property', 'Tech\\MetaverseController@apiCreateVirtualProperty');
$router->post('/api/metaverse/join-space', 'Tech\\MetaverseController@apiJoinSpace');

// ============================================================
// TECH: EDGE COMPUTING & 5G
// ============================================================
$router->get('/edge/5g-integration', 'Tech\\EdgeComputingController@fiveGIntegration');
$router->any('/edge/ai', 'Tech\\EdgeComputingController@edgeAI');
$router->get('/edge/real-time-processing', 'Tech\\EdgeComputingController@realTimeProcessing');
$router->get('/edge/mobile-edge', 'Tech\\EdgeComputingController@mobileEdge');
$router->get('/edge/content-delivery', 'Tech\\EdgeComputingController@contentDelivery');
$router->get('/edge/cost-analysis', 'Tech\\EdgeComputingController@costAnalysis');
$router->get('/edge/security-features', 'Tech\\EdgeComputingController@securityFeatures');
$router->get('/edge/performance-benchmarks', 'Tech\\EdgeComputingController@performanceBenchmarks');
$router->get('/edge/integration-guide', 'Tech\\EdgeComputingController@integrationGuide');
$router->get('/edge/use-cases', 'Tech\\EdgeComputingController@useCases');
$router->any('/edge/roi-calculator', 'Tech\\EdgeComputingController@roiCalculator');
$router->get('/edge/roadmap', 'Tech\\EdgeComputingController@roadmap');
$router->get('/edge/partnerships', 'Tech\\EdgeComputingController@partnerships');
$router->get('/edge/education', 'Tech\\EdgeComputingController@education');
$router->get('/edge/industry-impact', 'Tech\\EdgeComputingController@industryImpact');
$router->get('/edge/sustainability', 'Tech\\EdgeComputingController@sustainability');
$router->get('/edge/research', 'Tech\\EdgeComputingController@research');
$router->get('/edge/case-studies', 'Tech\\EdgeComputingController@caseStudies');
$router->get('/admin/edge/dashboard', 'Tech\\EdgeComputingController@edgeDashboard');
$router->get('/admin/edge/distributed-network', 'Tech\\EdgeComputingController@distributedNetwork');
$router->get('/api/edge/status', 'Tech\\EdgeComputingController@apiEdgeStatus');
$router->post('/api/edge/process', 'Tech\\EdgeComputingController@apiProcessAtEdge');

// ============================================================
// TECH: SUSTAINABLE TECHNOLOGY
// ============================================================
$router->get('/sustainability/carbon-footprint', 'Tech\\SustainableTechController@carbonFootprint');
$router->get('/sustainability/energy-efficiency', 'Tech\\SustainableTechController@energyEfficiency');
$router->get('/sustainability/green-technology', 'Tech\\SustainableTechController@greenTechnology');
$router->get('/sustainability/sustainable-properties', 'Tech\\SustainableTechController@sustainableProperties');
$router->get('/sustainability/environmental-impact', 'Tech\\SustainableTechController@environmentalImpact');
$router->get('/sustainability/green-finance', 'Tech\\SustainableTechController@greenFinance');
$router->get('/sustainability/education', 'Tech\\SustainableTechController@sustainabilityEducation');
$router->get('/sustainability/partnerships', 'Tech\\SustainableTechController@sustainabilityPartnerships');
$router->get('/sustainability/innovation-lab', 'Tech\\SustainableTechController@innovationLab');
$router->get('/sustainability/awards', 'Tech\\SustainableTechController@awards');
$router->any('/sustainability/calculator', 'Tech\\SustainableTechController@sustainabilityCalculator');
$router->get('/sustainability/roadmap', 'Tech\\SustainableTechController@sustainabilityRoadmap');
$router->get('/sustainability/case-studies', 'Tech\\SustainableTechController@caseStudies');
$router->get('/sustainability/community-engagement', 'Tech\\SustainableTechController@communityEngagement');
$router->get('/sustainability/governance', 'Tech\\SustainableTechController@governance');
$router->get('/sustainability/investment-opportunities', 'Tech\\SustainableTechController@investmentOpportunities');
$router->get('/sustainability/trends', 'Tech\\SustainableTechController@trends');
$router->get('/sustainability/resources', 'Tech\\SustainableTechController@resources');
$router->get('/sustainability/challenges', 'Tech\\SustainableTechController@challenges');
$router->get('/sustainability/success-stories', 'Tech\\SustainableTechController@successStories');
$router->get('/sustainability/future-vision', 'Tech\\SustainableTechController@futureVision');
$router->get('/admin/sustainability/dashboard', 'Tech\\SustainableTechController@sustainabilityDashboard');
$router->get('/admin/sustainability/reporting', 'Tech\\SustainableTechController@sustainabilityReporting');
$router->get('/api/sustainability/data', 'Tech\\SustainableTechController@apiSustainabilityData');
$router->get('/api/sustainability/endpoints', 'Tech\\SustainableTechController@apiSustainabilityEndpoints');

// ============================================================
// TECH: PWA ADDITIONAL ROUTES
// ============================================================
$router->post('/api/pwa/subscribe', 'Tech\\PWAController@subscribeNotifications');
$router->post('/api/pwa/send-notification', 'Tech\\PWAController@sendPushNotification');
$router->get('/pwa/install-prompt', 'Tech\\PWAController@installPrompt');
$router->get('/api/pwa/stats', 'Tech\\PWAController@getPWAStats');
$router->post('/api/pwa/log-install-prompt', 'Tech\\PWAController@logInstallPrompt');
$router->post('/api/pwa/log-installation', 'Tech\\PWAController@logInstallation');

// ============================================================
// NOTIFICATION MANAGEMENT (old NotificationController, now using admin.php layout)
// ============================================================
$router->get('/admin/notification-management', 'NotificationController@index');
$router->get('/admin/notification-management/templates', 'NotificationController@templates');
$router->get('/admin/notification-management/templates/create', 'NotificationController@createTemplate');
$router->get('/admin/notification-management/templates/edit/{id}', 'NotificationController@editTemplate');
$router->get('/admin/notification-management/logs/email', 'NotificationController@emailLogs');
$router->get('/admin/notification-management/logs/sms', 'NotificationController@smsLogs');
$router->get('/admin/notification-management/settings', 'NotificationController@settings');
$router->get('/admin/notification-management/send-test', 'NotificationController@sendTest');
$router->get('/admin/notification-management/preview/{id}', 'NotificationController@preview');

// Redirect old /user/notifications to admin notification management
$router->get('/user/notifications', function () {
    header('Location: ' . BASE_URL . '/admin/notification-management');
    exit;
});

// ============================================================
// MISSING SIDEBAR MENU ROUTES (19 items from admin_menu_items table)
// ============================================================

// Associate section
$router->get('/admin/associate-extensions', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Associate Extensions';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Associate Extensions</h1><p class="text-muted">Manage associate profile extensions and custom fields.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});

// Marketing section
$router->get('/admin/marketing/strategies', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Marketing Strategies';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Marketing Strategies</h1><p class="text-muted">This section is under development.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/marketing/marketplace', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Marketing Marketplace';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Marketing Marketplace</h1><p class="text-muted">This section is under development.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});

// Commission section (MLM)
$router->get('/admin/commission/agent-rates', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Agent Commission Rates';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Agent Commission Rates</h1><p class="text-muted">Configure commission rates for agents.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/commission/associate/structure', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Associate Commission Structure';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Associate Commission Structure</h1><p class="text-muted">This section is under development.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/commission/associate/calculations', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Associate Commission Calculations';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Associate Commission Calculations</h1><p class="text-muted">This section is under development.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/commission/bonuses', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Commission Bonuses';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Commission Bonuses</h1><p class="text-muted">Manage bonus rules and payouts.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/commission/mlm/levels', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'MLM Commission Levels';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">MLM Commission Levels</h1><p class="text-muted">This section is under development.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/commission/mlm/records', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'MLM Commission Records';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">MLM Commission Records</h1><p class="text-muted">This section is under development.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/commission/mlm/analytics', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'MLM Commission Analytics';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">MLM Commission Analytics</h1><p class="text-muted">This section is under development.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/commission/revenue/daily', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Daily Revenue';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Daily Revenue</h1><p class="text-muted">View daily revenue breakdown.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/commission/telecaller/rules', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Telecaller Commission Rules';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Telecaller Commission Rules</h1><p class="text-muted">This section is under development.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/commission/telecaller/commissions', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'Telecaller Commissions';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">Telecaller Commissions</h1><p class="text-muted">This section is under development.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});

// MLM section
$router->get('/admin/mlm/rank-criteria', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'MLM Rank Criteria';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">MLM Rank Criteria</h1><p class="text-muted">Define rank advancement criteria for associates.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/mlm/upgrades', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'MLM Upgrades';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">MLM Upgrades</h1><p class="text-muted">View and manage associate rank upgrades.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/mlm/withdrawals', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'MLM Withdrawals';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">MLM Withdrawals</h1><p class="text-muted">Manage withdrawal requests from associates.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/mlm/rewards', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'MLM Rewards';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">MLM Rewards</h1><p class="text-muted">Manage rewards and recognition for associates.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});

// Settings section (API)
$router->get('/admin/api/integrations', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'API Integrations';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">API Integrations</h1><p class="text-muted">Manage third-party API integrations and webhooks.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
$router->get('/admin/api/developers', function () {
    \App\Core\Middleware\AuthMiddleware::requireAdmin();
    $page_title = 'API Developers';
    $content = '<div class="container-fluid"><h1 class="h3 mb-4">API Developers</h1><p class="text-muted">Developer portal with API documentation and keys.</p></div>';
    include APP_PATH . '/views/layouts/admin.php';
});
