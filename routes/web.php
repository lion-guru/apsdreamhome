<?php
use App\Core\Database\Database;

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
$router->get('/contact', 'Front\\ContactController@contact');
$router->post('/contact', 'Front\\ContactController@contact');
$router->get('/team', 'Front\\PageController@team');
$router->get('/opportunity', 'Front\\CareerController@opportunity');
$router->get('/our-team', function () {
    header('Location: ' . BASE_URL . '/team', true, 301);
    exit;
});
// /testimonials is also defined at line 430 (Front\TestimonialsController@index) - the LATER route wins
$router->get('/faq', 'Front\\PageController@faq');
$router->get('/faqs', 'Front\\PageController@faqs');
$router->get('/home', 'Front\\PageController@home');
$router->get('/sitemap.xml', 'Api\\SitemapController@generate');
$router->get('/robots.txt', function () {
    $file = __DIR__ . '/../robots.txt';
    if (file_exists($file)) {
        header('Content-Type: text/plain');
        readfile($file);
        exit;
    }
});
$router->get('/sitemap', 'Front\\PageController@sitemap');
$router->get('/mobile-app', 'Front\\PageController@createMobileApp');
$router->get('/privacy', 'Front\\LegalController@privacy');
$router->get('/news', 'Front\\PageController@news');
$router->get('/blog', 'App\\Http\\Controllers\\Front\\BlogController@index');
$router->get('/blog/{slug}', 'App\\Http\\Controllers\\Front\\BlogController@show');
$router->get('/gallery', 'Front\\PageController@gallery');
$router->get('/resell', 'Front\\ServiceController@resell');
// /resell is also defined at line 3183 (Front\ResellPropertyController@index) - the LATER route wins
// /careers is also defined at line 3065 (Career\CareerController@index) - the LATER route wins
$router->get('/coming-soon', 'Front\\PageController@comingSoon');
$router->get('/become-associate', 'Front\\AssociateController@becomeAssociate');
$router->get('/become_associate', function () {
    header('Location: ' . BASE_URL . '/become-associate', true, 301);
    exit;
});

// Support
$router->get('/support', 'Front\\SupportController@index');
$router->post('/support', 'Front\\SupportController@store');
$router->get('/whatsapp-chat', 'Front\\AIController@whatsappChat');

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
$router->get('/auth/linkedin', function () {
    $_SESSION['error'] = 'LinkedIn login coming soon. Use Google or Facebook.';
    header('Location: ' . BASE_URL . '/login');
    exit;
});

// Quick Auth (for casual visitors, booking, etc.)
$router->post('/auth/quick-register', 'Auth\\QuickAuthController@quickRegister');
$router->post('/auth/request-referral-code', 'Auth\\QuickAuthController@requestReferralCode');
$router->post('/auth/auto-generate-user', 'Auth\\QuickAuthController@autoGenerateUser');

// Visitor Tracking & Lead Capture
$router->post('/track/page-view', 'App\\Http\\Controllers\\VisitorTrackingController@trackPageView');
$router->post('/track/incomplete-registration', 'App\\Http\\Controllers\\VisitorTrackingController@trackIncompleteRegistration');
$router->post('/track/interest', 'App\\Http\\Controllers\\VisitorTrackingController@trackInterest');
$router->get('/admin/visitor-stats', 'App\\Http\\Controllers\\VisitorTrackingController@getVisitorStats');

// Lead Follow-up System
$router->post('/admin/send-follow-ups', 'Admin\\LeadFollowUpController@sendFollowUps');
$router->get('/admin/follow-up-stats', 'Admin\\LeadFollowUpController@getFollowUpStats');
$router->get('/user-ai-suggestions', 'Front\\AIController@userAiSuggestions');
$router->get('/user/investments', 'Front\\UserDashboardController@userInvestments');
$router->get('/builder-registration', 'Front\\PageController@builderRegistration');
$router->post('/builder-registration', 'Front\\PageController@builderRegistration');
$router->get('/plots-availability', 'Front\\PropertyController@plotsAvailability');
$router->get('/plots/layout', 'Front\\ProjectController@plotMap');
$router->get('/map', 'App\\Http\\Controllers\\MapController@index');
$router->get('/gallery/{id}', 'App\\Http\\Controllers\\GalleryController@project');

// Free Tools
$router->get('/stamp-duty-calculator', 'Front\\ToolController@stampDutyCalculator');
$router->get('/plot-size-converter', 'Front\\PropertyController@plotSizeConverter');
$router->get('/home-loan-eligibility', 'Front\\ToolController@homeLoanEligibility');
$router->get('/documents', 'Front\\PageController@documentGallery');
$router->get('/documents/download/{id}', 'Front\\PageController@downloadDocument');
$router->get('/property-valuation', 'Front\\ToolController@propertyValuation');
$router->get('/tools-hub', 'Front\\ToolController@toolsHub');
$router->get('/partner-tools', 'Front\\ToolController@partnerTools');
$router->get('/rent-vs-buy', 'Front\\ToolController@rentVsBuy');
$router->get('/sip-vs-realestate', 'Front\\ToolController@sipVsRealestate');
$router->get('/capital-gains-calculator', 'Front\\ToolController@capitalGains');
$router->get('/gst-calculator', 'Front\\ToolController@gstCalculator');
$router->get('/construction-cost-estimator', 'Front\\ToolController@constructionCostEstimator');
$router->get('/rental-yield-calculator', 'Front\\ToolController@rentalYieldCalculator');
$router->get('/property-tax-calculator', 'Front\\ToolController@propertyTaxCalculator');
$router->get('/rera-lookup', 'Front\\ToolController@reraLookup');

// Free Tools (new)
$router->get('/tools/plot-converter', 'Front\\PropertyController@plotConverter');
$router->get('/tools/valuation-calculator', 'Front\\ToolController@valuationCalculator');

// Free Tools API
$router->get('/api/tools/convert-area', function () {
    $value = floatval($_GET['value'] ?? 0);
    $from  = $_GET['from'] ?? 'sqft';
    $to    = $_GET['to'] ?? 'sqm';
    $rates = [
        'sqft' => 1, 'sqm' => 0.092903, 'acre' => 43560, 'hectare' => 107639,
        'bigha' => 27000, 'bigha_bi' => 27220, 'gaj' => 9, 'katha' => 1361,
        'marla' => 272.25, 'guntha' => 1089, 'ground' => 2400,
    ];
    $labels = [
        'sqft' => 'Square Feet', 'sqm' => 'Square Meter', 'acre' => 'Acre',
        'hectare' => 'Hectare', 'bigha' => 'Bigha (UP)', 'bigha_bi' => 'Bigha (Bihar)',
        'gaj' => 'Gaj', 'katha' => 'Katha', 'marla' => 'Marla',
        'guntha' => 'Guntha', 'ground' => 'Ground',
    ];
    if (!isset($rates[$from]) || !isset($rates[$to])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid unit']);
        exit;
    }
    $sqftValue = $value * $rates[$from];
    $result    = $sqftValue / $rates[$to];
    $formula   = "$value {$labels[$from]} × (" . round(1 / $rates[$from], 6) . " sqft/{$labels[$from]}) ÷ (" . round(1 / $rates[$to], 6) . " sqft/{$labels[$to]})";
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'value'   => $value,
        'from'    => $from,
        'from_label' => $labels[$from],
        'to'      => $to,
        'to_label' => $labels[$to],
        'result'  => round($result, 4),
        'formula' => $formula,
    ]);
    exit;
});

$router->post('/api/tools/estimate-value', function () {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $location   = intval($input['location'] ?? 0);
    $type       = $input['type'] ?? 'plot';
    $area       = floatval($input['area'] ?? 0);
    $bedrooms   = intval($input['bedrooms'] ?? 0);
    $age        = intval($input['age'] ?? 0);
    $condition  = $input['condition'] ?? 'new';
    if ($area <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Area must be greater than 0']);
        exit;
    }
    $baseRates = [
        3000 => 2800, 5 => 3200, 2 => 4500, 7 => 3800, 10 => 3500,
        4 => 4200, 14 => 4000, 12 => 3600, 91 => 5500, 90 => 6000,
    ];
    $rate = $baseRates[$location] ?? 2500;
    $typeMultipliers = [
        'plot' => 1.0, 'house' => 1.25, 'flat' => 1.15,
        'shop' => 1.5, 'farmhouse' => 1.3,
    ];
    $typeMul = $typeMultipliers[$type] ?? 1.0;
    if ($bedrooms > 0) {
        $typeMul += ($bedrooms - 2) * 0.03;
    }
    $ageFactor = 1.0;
    if ($age <= 5) $ageFactor = 1.0;
    elseif ($age <= 10) $ageFactor = 0.95;
    elseif ($age <= 20) $ageFactor = 0.85;
    else $ageFactor = 0.75;
    $condFactors = ['new' => 1.0, 'old' => 0.85, 'renovated' => 0.95];
    $condFactor = $condFactors[$condition] ?? 1.0;
    $pricePerSqft = $rate * $typeMul * $ageFactor * $condFactor;
    $estimated    = $pricePerSqft * $area;
    $minPrice     = $estimated * 0.85;
    $maxPrice     = $estimated * 1.15;
    $confidence   = 60;
    if ($location > 0) $confidence += 10;
    if ($type !== 'plot') $confidence += 5;
    if ($age < 5) $confidence += 5;
    $confidence = min($confidence, 95);
    header('Content-Type: application/json');
    echo json_encode([
        'success'        => true,
        'min_price'      => round($minPrice),
        'max_price'      => round($maxPrice),
        'price_per_sqft' => round($pricePerSqft),
        'estimated'      => round($estimated),
        'confidence'     => $confidence,
    ]);
    exit;
});

// MLM & AI Dashboard Routes
$router->get('/mlm-dashboard', 'MLM\MLMDashboardController@dashboard');
$router->get('/ai-dashboard', 'App\\Http\\Controllers\\AIDashboardController@index');
// Career Pages
$router->get('/careers/apply', 'Front\\CareerController@careerApply');
$router->post('/careers/apply', 'Front\\CareerController@submitCareerApplication');
$router->get('/career_apply', function () {
    header('Location: ' . BASE_URL . '/careers/apply', true, 301);
    exit;
});
$router->get('/careers/jobs', 'Front\\CareerController@careerJobs');
$router->get('/careers/job/{id}', 'Front\\CareerController@careerJobDetails');

// Property Pages
$router->get('/properties', 'Front\\PropertyController@properties');
$router->get('/featured-properties', 'Front\PropertyController@getFeaturedProperties');
$router->get('/properties/{id}', 'Front\\PropertyController@propertyDetails');

// Plot Comparison Routes
$router->get('/compare', 'Front\CompareController@index');
$router->post('/compare/add', 'Front\CompareController@add');
$router->post('/compare/remove', 'Front\CompareController@remove');
$router->post('/compare/clear', 'Front\CompareController@clear');
$router->get('/compare/count', 'Front\CompareController@count');

// Project Pages
$router->get('/projects', 'Front\\ProjectController@projects');
$router->get('/company/projects', 'Front\\ProjectController@projects');
$router->get('/projects/budha-city', 'Front\\ProjectController@budhaCity');
$router->get('/projects/{slug}', 'Front\\ProjectController@projectDetails');
$router->get('/projects/{location}', 'Front\\ProjectController@projectsByLocation');

// Dynamic Colony Pages (single-template, DB-driven)
$router->get('/colony/{slug}', 'Front\\ProjectController@colonyDetail');
$router->get('/plots', 'Front\\PlotController@index');

// ── Customer Booking Portal ──────────────────────────────────────
$router->get('/plots/browse', 'Front\\BookingController@browse');
$router->get('/plots/{id}/detail', 'Front\\BookingController@detail');
$router->get('/plots/{id}/book', 'Front\\BookingController@bookForm');
$router->post('/plots/{id}/book', 'Front\\BookingController@submitBooking');
$router->get('/booking/confirmation/{id}', 'Front\\BookingController@confirmation');

// ── E-Sign (Leegality) ───────────────────────────────────────────
$router->get('/user/bookings/{id}/esign',          'Front\\BookingController@esign');
$router->post('/user/bookings/{id}/esign/initiate', 'Front\\BookingController@initiateEsign');
$router->post('/webhook/esign',                    'Front\\BookingController@esignWebhook');

// ── Plot Lock (30-min reservation) ────────────────────────────
$router->post('/plots/{id}/lock',   'Front\\BookingController@lockPlot');
$router->post('/plots/{id}/unlock', 'Front\\BookingController@unlockPlot');

// ── KYC Verification (pre-booking) ────────────────────────────
$router->post('/plots/{id}/verify-kyc', 'Front\\BookingController@verifyKyc');

// ── NACH Mandate Registration ─────────────────────────────────
$router->get('/user/bookings/{id}/nach',              'Front\\BookingController@nachMandate');
$router->post('/user/bookings/{id}/nach/register',    'Front\\BookingController@registerNachMandate');

$router->get('/plot/{id}', 'Front\\PlotController@show');
$router->get('/plot/{id}/book', 'Front\\PlotController@bookPlot');
$router->post('/plot/book', 'Front\\PlotController@storeBooking');
$router->get('/booking/{id}/confirmation', 'Front\\PlotController@bookingConfirmation');
$router->get('/booking/{id}/pay', 'Front\\PlotController@payBooking');
$router->post('/booking/{id}/pay', 'Front\\PlotController@processPayment');
$router->get('/booking/{id}/receipt', 'Front\\PlotController@receipt');

$router->get('/colony/{slug}/plots', 'Front\\PlotController@colonyPlots');
$router->get('/colony/{slug}/map', 'App\\Http\\Controllers\\MapController@colonyPlotMap');
$router->get('/api/colony/{id}/map/geojson', 'App\\Http\\Controllers\\MapController@colonyGeoJson');
$router->get('/colony/raghunath-nagri/block-c-dashboard', 'Front\\ColonyDashboardController@raghunathBlockC');
$router->post('/api/colony/raghunath-nagri/sync-booking', 'Front\\ColonyDashboardController@syncBookingFromFirebase');
$router->get('/api/colony/raghunath-nagri/bookings', 'Front\\ColonyDashboardController@getBlockCBookings');
$router->get('/api/plots/by-colony/{colonyId}', 'Front\\PlotController@apiByColony');

// ── Digital Booking Flow (Customer-facing) ──────────────────────────
$router->get('/booking/digital/{bookingNumber}', 'Front\\DigitalBookingController@show');
$router->get('/booking/digital/{bookingNumber}/document/{docId}', 'Front\\DigitalBookingController@viewDocument');
$router->post('/booking/digital/{bookingNumber}/document/{docId}/sign', 'Front\\DigitalBookingController@signDocument');
$router->post('/booking/digital/{bookingNumber}/video-consent', 'Front\\DigitalBookingController@recordVideoConsent');
$router->get('/booking/digital/{bookingNumber}/emi-preview', 'Front\\DigitalBookingController@emiPreview');
$router->post('/booking/digital/{bookingNumber}/emi-confirm', 'Front\\DigitalBookingController@emiConfirm');
$router->get('/booking/digital/{bookingNumber}/download/{docId}', 'Front\\DigitalBookingController@downloadDocument');
$router->get('/booking/digital/{bookingNumber}/documents', 'Front\\DigitalBookingController@getDocuments');
$router->get('/booking/digital/{bookingNumber}/success', 'Front\\DigitalBookingController@success');
$router->post('/booking/digital/{bookingNumber}/submit', 'Front\\DigitalBookingController@submit');
$router->get('/navigation', 'Front\\PageController@navigation');
$router->get('/downloads', 'Front\\PageController@downloads');
$router->get('/under-construction', 'Front\\PageController@underConstruction');
$router->get('/thank-you', 'Front\\PageController@thankYou');
$router->get('/customer-reviews', 'Front\\PageController@customerReviews');

// Buy/Sell/Rent/Invest
$router->get('/buy', 'Front\\PropertyController@buyProperty');
$router->get('/sell', 'Front\\PropertyController@sellProperty');
$router->get('/rent', 'Front\\PropertyController@rentProperty');
$router->get('/invest', 'Front\\PropertyController@investProperty');

// Property Listing (User)
$router->get('/list-property', 'Front\\PropertyController@listProperty');
$router->post('/list-property/submit', 'Front\\PropertyController@handlePropertyListing');
$router->get('/properties/submit', 'Front\\PropertyController@listProperty');

// Multi-step Property Listing Wizard (8 steps + draft + publish + image upload)
if (file_exists(__DIR__ . '/../app/Http/Controllers/Front/PropertyListingWizardController.php')) {
    $router->get('/list-property/step1', 'Front\\PropertyListingWizardController@step1');
    $router->post('/list-property/step1', 'Front\\PropertyListingWizardController@saveStep1');
    $router->get('/list-property/step2', 'Front\\PropertyListingWizardController@step2');
    $router->post('/list-property/step2', 'Front\\PropertyListingWizardController@saveStep2');
    $router->get('/list-property/step3', 'Front\\PropertyListingWizardController@step3');
    $router->post('/list-property/step3', 'Front\\PropertyListingWizardController@saveStep3');
    $router->get('/list-property/step4', 'Front\\PropertyListingWizardController@step4');
    $router->post('/list-property/step4', 'Front\\PropertyListingWizardController@saveStep4');
    $router->get('/list-property/step5', 'Front\\PropertyListingWizardController@step5');
    $router->post('/list-property/step5', 'Front\\PropertyListingWizardController@saveStep5');
    $router->get('/list-property/step6', 'Front\\PropertyListingWizardController@step6');
    $router->post('/list-property/step6', 'Front\\PropertyListingWizardController@saveStep6');
    $router->get('/list-property/step7', 'Front\\PropertyListingWizardController@step7');
    $router->post('/list-property/step7', 'Front\\PropertyListingWizardController@saveStep7');
    $router->get('/list-property/step8', 'Front\\PropertyListingWizardController@step8');
    $router->post('/list-property/step8', 'Front\\PropertyListingWizardController@saveStep8');
    $router->post('/list-property/publish', 'Front\\PropertyListingWizardController@publish');
    $router->post('/list-property/save-draft', 'Front\\PropertyListingWizardController@saveDraft');
    $router->post('/list-property/upload-image', 'Front\\PropertyListingWizardController@uploadImage');
}

// Form Handlers
$router->post('/quick-inquiry', 'Front\\ContactController@handleQuickInquiry');

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

// Admin API Keys Management - additional routes (index/create/revoke/activate/delete defined at line ~4380)
$router->get('/admin/api-keys/guide', 'App\\Http\\Controllers\\Admin\\ApiKeyController@guide');

// Bulk Property Import (CSV)
if (file_exists(__DIR__ . '/../app/Services/Bulk/PropertyImportService.php')) {
    $router->get('/admin/bulk/property-import', 'App\\Http\\Controllers\\Admin\\BulkOperationsController@propertyImport');
    $router->post('/admin/bulk/property-import/upload', 'App\\Http\\Controllers\\Admin\\BulkOperationsController@propertyImportUpload');
    $router->post('/admin/bulk/property-import/execute', 'App\\Http\\Controllers\\Admin\\BulkOperationsController@propertyImportExecute');
    $router->get('/admin/bulk/property-import/template', 'App\\Http\\Controllers\\Admin\\BulkOperationsController@propertyImportTemplate');
    $router->get('/admin/bulk/property-import/sample', 'App\\Http\\Controllers\\Admin\\BulkOperationsController@propertyImportSample');
}
$router->get('/admin/api-keys/edit/{id}', 'App\\Http\\Controllers\\Admin\\ApiKeyController@edit');
$router->post('/admin/api-keys/update/{id}', 'App\\Http\\Controllers\\Admin\\ApiKeyController@update');
$router->get('/admin/api-keys/toggle/{id}', 'App\\Http\\Controllers\\Admin\\ApiKeyController@toggle');
$router->get('/admin/api-keys/test/{id}', 'App\Http\Controllers\Admin\ApiKeyController@test');

// API Key Management AJAX endpoints (for api_key_management.php view)
$router->get('/admin/api-key-mgmt/stats', function () {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success' => false]); exit; }
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $total = $db->query("SELECT COUNT(*) FROM api_keys")->fetchColumn();
        $active = $db->query("SELECT COUNT(*) FROM api_keys WHERE status = 'active'")->fetchColumn();
        $mcp = 0; $userKeys = 0;
        try { $mcp = $db->query("SELECT COUNT(*) FROM mcp_api_keys")->fetchColumn(); } catch (\Exception $e) {}
        try { $userKeys = $db->query("SELECT COUNT(*) FROM user_api_keys")->fetchColumn(); } catch (\Exception $e) {}
        echo json_encode(['success' => true, 'stats' => ['total_keys' => (int)$total, 'active_keys' => (int)$active, 'mcp_keys' => ['total' => (int)$mcp], 'user_keys' => ['total' => (int)$userKeys]]]);
    } catch (\Exception $e) { echo json_encode(['success' => false]); }
    exit;
});
$router->get('/admin/api-key-mgmt/mcp-keys', function () {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success' => false, 'keys' => []]); exit; }
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM mcp_api_keys ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'keys' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
    } catch (\Exception $e) { echo json_encode(['success' => true, 'keys' => []]); }
    exit;
});
$router->get('/admin/api-key-mgmt/user-keys', function () {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success' => false, 'keys' => []]); exit; }
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM api_keys ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'keys' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
    } catch (\Exception $e) { echo json_encode(['success' => true, 'keys' => []]); }
    exit;
});
$router->get('/admin/api-key-mgmt/integration', function () {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success' => false]); exit; }
    echo json_encode(['success' => true, 'integration' => [
        'mcp_servers' => [['name' => 'MySQL', 'status' => 'active', 'description' => 'Database access'], ['name' => 'Filesystem', 'status' => 'active', 'description' => 'File operations'], ['name' => 'Sequential Thinking', 'status' => 'active', 'description' => 'Step-by-step reasoning']],
        'api_system' => [['name' => 'REST API', 'status' => 'active', 'description' => 'Core API endpoints'], ['name' => 'Mobile API', 'status' => 'active', 'description' => 'Flutter app endpoints'], ['name' => 'Webhook System', 'status' => 'active', 'description' => 'Event-driven notifications']]
    ]]);
    exit;
});
$router->post('/admin/api-key-mgmt/add-mcp-key', function () {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success' => false]); exit; }
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $name = $_POST['service_name'] ?? '';
        $keyName = $_POST['key_name'] ?? '';
        $keyType = $_POST['key_type'] ?? 'api_key';
        $description = $_POST['description'] ?? '';
        $keyValue = 'mcp_' . bin2hex(random_bytes(24));
        $stmt = $db->prepare("INSERT INTO mcp_api_keys (service_name, key_name, key_type, key_value, description, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$name, $keyName, $keyType, $keyValue, $description]);
        echo json_encode(['success' => true]);
    } catch (\Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    exit;
});
$router->post('/admin/api-key-mgmt/create-user-key', function () {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success' => false]); exit; }
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $name = $_POST['name'] ?? '';
        $userId = $_POST['user_id'] ?? 0;
        $permissions = $_POST['permissions'] ?? '[]';
        $rateLimit = $_POST['rate_limit'] ?? 1000;
        $apiKey = 'ak_' . bin2hex(random_bytes(32));
        $stmt = $db->prepare("INSERT INTO api_keys (api_key, name, user_id, permissions, rate_limit, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
        $stmt->execute([$apiKey, $name, $userId, $permissions, $rateLimit]);
        echo json_encode(['success' => true, 'api_key' => $apiKey]);
    } catch (\Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    exit;
});

// Admin AI Chatbot Training
$router->get('/admin/ai-training', 'App\\Http\\Controllers\\Admin\\AdminAIController@training');

// Admin Chat Analytics & History
$router->get('/admin/chat-analytics', 'App\\Http\\Controllers\\SmartAIController@analytics');
$router->get('/admin/chat-history', 'App\\Http\\Controllers\\SmartAIController@chatHistory');

// Admin WhatsApp Integration
$router->get('/admin/whatsapp-integration', 'App\\Http\\Controllers\\Admin\\AdminController@whatsappIntegration');
$router->get('/admin/whatsapp-web', 'App\\Http\\Controllers\\Admin\\AdminController@whatsappWeb');
$router->get('/admin/whatsapp-web/manage', 'App\\Http\\Controllers\\Admin\\AdminController@whatsappWebManage');

// Missing frontend routes (from header/footer links)
$router->get('/financial-services', 'Front\\FinancialController@financialServices');
$router->post('/financial-services/contact', 'Front\\FinancialController@financialContact');
$router->get('/interior-design', 'Front\\ServiceController@interiorDesign');
$router->get('/construction-services', 'Front\\ServiceController@constructionServices');
$router->post('/construction-services/inquiry', 'Front\\ServiceController@constructionInquiry');

// Services Directory (Real Estate Business Directory)
$router->get('/services', 'Front\\DirectoryController@index');
$router->get('/services/submit', 'Front\\DirectoryController@submitListing');
$router->post('/services/submit', 'Front\\DirectoryController@submitListing');
$router->get('/services/categories', 'Front\\DirectoryController@apiCategories');
$router->get('/services/listing/{id}', 'Front\\DirectoryController@detail');
$router->post('/services/add-review', 'Front\\DirectoryController@addReview');
$router->get('/services/jobs', 'Front\\DirectoryController@jobs');
$router->get('/services/jobs/post', 'Front\\DirectoryController@postJob');
$router->post('/services/jobs/post', 'Front\\DirectoryController@postJob');
$router->get('/services/materials', 'Front\\DirectoryController@materials');
$router->get('/services/{slug}', 'Front\\DirectoryController@category');

$router->get('/legal/terms-conditions', 'Front\\LegalController@terms');
$router->get('/legal/services', 'Front\\LegalController@services');
$router->get('/legal/documents', 'Front\\LegalController@documents');
$router->get('/user/edit-profile', 'Front\\UserDashboardController@userEditProfile');
$router->get('/user/logout', 'Auth\\CustomerAuthController@logout');
$router->get('/user/dashboard', 'Front\\UserController@dashboard');
$router->get('/user/properties', 'Front\\UserController@myProperties');
$router->get('/user/boost-property/{id}', 'Front\\UserPackageController@boost');
$router->post('/user/boost-property/purchase', 'Front\\UserPackageController@purchase');
$router->get('/user/bookings', 'Front\\UserController@userBookings');
$router->get('/user/bookings/new', 'Front\\UserController@newBooking');
$router->post('/user/bookings/create', 'Front\\UserController@createBooking');
$router->get('/user/bookings/{id}', 'Front\\UserController@bookingDetail');
$router->get('/user/bookings/{id}/confirmation', 'Front\\UserController@bookingConfirmation');
$router->get('/user/bookings/{id}/pay-token', 'Front\\UserController@payToken');
$router->post('/user/bookings/{id}/pay-token', 'Front\\UserController@processTokenPayment');
$router->get('/user/bookings/{id}/payment-success', 'Front\\UserController@paymentSuccess');
$router->get('/user/agreements', 'Front\\UserController@agreements');
$router->get('/user/agreements/{id}', 'Front\\UserController@agreementDetail');
$router->post('/user/agreements/{id}/sign', 'Front\\UserController@signAgreement');
$router->get('/user/agreements/{id}/preview', 'Front\\UserController@agreementPreview');
$router->get('/user/installments/{id}/demand-letter', 'Front\\UserController@demandLetter');
$router->get('/user/installments/{id}/pay', 'Front\\UserController@payInstallment');
$router->post('/user/installments/{id}/pay', 'Front\\UserController@processInstallmentPayment');
$router->get('/user/installments/{id}/success', 'Front\\UserController@installmentSuccess');
$router->get('/user/inquiries', 'Front\\UserController@myInquiries');
$router->get('/user/tickets', 'Front\\UserController@myTickets');
$router->post('/user/tickets/create', 'Front\\UserController@createTicket');

// Customer Support Tickets (v2)
$router->get('/user/support', 'Front\\UserController@supportTickets');
$router->get('/user/support/create', 'Front\\UserController@createSupportTicket');
$router->post('/user/support/store', 'Front\\UserController@storeSupportTicket');
$router->get('/user/support/{id}', 'Front\\UserController@ticketDetail');
$router->post('/user/support/{id}/reply', 'Front\\UserController@ticketReply');

$router->get('/user/profile', 'Front\\UserController@profile');
$router->post('/user/profile', 'Front\\UserController@updateProfile');
$router->get('/user/bank-details', 'Front\\UserController@bankDetails');
$router->post('/user/bank-details/save', 'Front\\UserController@saveBankDetails');
$router->get('/user/network', 'Front\\UserController@network');
$router->get('/user/notification-settings', 'Front\\UserController@notificationSettings');
$router->get('/user/address', 'Front\\UserController@address');
$router->get('/user/insurance', 'Front\\UserController@insurance');
$router->get('/user/investment-plans', 'Front\\UserController@investmentPlans');
$router->get('/user/kyc', 'Front\\KycController@index');
$router->post('/user/kyc/submit', 'Front\\KycController@submit');
$router->get('/user/kyc/status', 'Front\\KycController@status');

// Portal: Insurance / Investment / Address (Phase 8 do-next-all)
$router->post('/user/insurance/enrol', 'Front\\PortalController@insuranceEnrol');
$router->post('/user/investment-plans/invest', 'Front\\PortalController@invest');
$router->post('/user/address/store', 'Front\\PortalController@addressCreate');
$router->post('/user/address/update', 'Front\\PortalController@addressUpdate');
$router->post('/user/address/delete', 'Front\\PortalController@addressDelete');
$router->post('/user/address/primary', 'Front\\PortalController@addressSetPrimary');
$router->get('/api/address/pincode', 'Front\\PortalController@pincodeLookup');
$router->post('/user/notification-settings', 'Front\\UserController@updateNotificationSettings');
$router->get('/user/notification-preferences', 'Front\\NotificationPreferenceController@index');
$router->post('/user/notification-preferences', 'Front\\NotificationPreferenceController@update');
$router->get('/api/user/notification-preferences', 'Front\\NotificationPreferenceController@getPreferences');
$router->get('/user/favorites', 'Front\\UserController@favorites');

// Saved Searches (Phase 56 - Advanced Search Feature)
$router->get('/user/saved-searches', 'Front\\SavedSearchController@index');
$router->post('/user/saved-searches', 'Front\\SavedSearchController@store');
$router->post('/user/saved-searches/store', 'Front\\SavedSearchController@store');
$router->put('/user/saved-searches/{id}', 'Front\\SavedSearchController@update');
$router->post('/user/saved-searches/{id}/update', 'Front\\SavedSearchController@update');
$router->delete('/user/saved-searches/{id}', 'Front\\SavedSearchController@destroy');
$router->post('/user/saved-searches/{id}/delete', 'Front\\SavedSearchController@destroy');
$router->get('/user/saved-searches/{id}/delete', 'Front\\SavedSearchController@destroy');
$router->get('/user/saved-searches/{id}/execute', 'Front\\SavedSearchController@execute');
$router->post('/user/saved-searches/{id}/alerts', 'Front\\SavedSearchController@toggleAlerts');
$router->get('/user/saved-searches/manage-alerts', 'Front\\SavedSearchController@manageAlerts');
$router->post('/user/saved-searches/manage-alerts', 'Front\\SavedSearchController@manageAlerts');
$router->get('/api/saved-searches/autocomplete', 'Front\\SavedSearchController@autocomplete');
$router->get('/user/saved-searches/cron-alerts', 'Front\\SavedSearchController@cronAlerts');
$router->post('/user/saved-searches/cron-alerts', 'Front\\SavedSearchController@cronAlerts');
$router->get('/news/view/{id}', 'Front\\PageController@newsView');
$router->post('/property/review', 'Front\\PageController@reviewSubmit');
$router->get('/property/{id}', 'Front\\PropertyController@propertyDetails');
$router->get('/listing/{id}', 'Front\\PageController@userPropertyDetail');
$router->get('/marketplace', 'Front\\MarketplaceController@index');
$router->get('/marketplace/{id}', 'Front\\MarketplaceController@detail');

$router->get('/admin/saved-searches', 'App\\Http\\Controllers\\Admin\\SavedSearchController@index');
$router->post('/admin/saved-searches/store', 'App\\Http\\Controllers\\Admin\\SavedSearchController@store');
$router->post('/admin/saved-searches/update/{id}', 'App\\Http\\Controllers\\Admin\\SavedSearchController@update');
$router->get('/admin/saved-searches/delete/{id}', 'App\\Http\\Controllers\\Admin\\SavedSearchController@delete');
$router->post('/admin/saved-searches/favorite/{id}', 'App\\Http\\Controllers\\Admin\\SavedSearchController@favorite');
$router->get('/admin/saved-searches/apply/{id}', 'App\\Http\\Controllers\\Admin\\SavedSearchController@apply');

$router->get('/admin/lead-kanban', 'App\\Http\\Controllers\\Admin\\LeadKanbanController@index');
$router->post('/admin/lead-kanban/update-stage', 'App\\Http\\Controllers\\Admin\\LeadKanbanController@updateStage');
$router->get('/admin/lead-kanban/lead-quickview', 'App\\Http\\Controllers\\Admin\\LeadKanbanController@leadQuickView');
$router->get('/admin/lead-kanban/pipeline-stats', 'App\\Http\\Controllers\\Admin\\LeadKanbanController@pipelineStats');

$router->get('/admin/sales-dashboard', 'App\\Http\\Controllers\\Admin\\SalesManagerDashboardController@index');

$router->get('/admin/property-alerts', 'App\\Http\\Controllers\\Admin\\PropertyAlertController@index');
$router->get('/admin/property-alerts/delete', 'App\\Http\\Controllers\\Admin\\PropertyAlertController@delete');
$router->post('/admin/property-alerts/toggle', 'App\\Http\\Controllers\\Admin\\PropertyAlertController@toggle');
$router->get('/admin/property-alerts/test-match', 'App\\Http\\Controllers\\Admin\\PropertyAlertController@testMatch');

$router->get('/admin/marketing-campaigns', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@index');
$router->get('/admin/marketing-campaigns/create', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@create');
$router->post('/admin/marketing-campaigns/store', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@store');
$router->get('/admin/marketing-campaigns/show/{id}', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@show');
$router->get('/admin/marketing-campaigns/send/{id}', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@send');
$router->get('/admin/marketing-campaigns/delete', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@delete');
$router->get('/admin/marketing-campaigns/templates', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@templates');
$router->get('/admin/marketing-campaigns/{id}/edit', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@edit');
$router->post('/admin/marketing-campaigns/{id}/update', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@update');
$router->post('/admin/marketing-campaigns/{id}/pause', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@pause');
$router->post('/admin/marketing-campaigns/{id}/resume', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@resume');
$router->post('/admin/marketing-campaigns/{id}/cancel', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@cancel');
$router->post('/admin/marketing-campaigns/{id}/clone', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@clone');
$router->post('/admin/marketing-campaigns/{id}/test-send', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@testSend');
$router->post('/admin/marketing-campaigns/{id}/schedule', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@schedule');
$router->get('/admin/marketing-campaigns/{id}/stats', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@stats');
$router->get('/admin/marketing-campaigns/{id}/export', 'App\\Http\\Controllers\\Admin\\MarketingCampaignController@exportRecipients');

$router->get('/admin/drip-campaigns', 'App\\Http\\Controllers\\Admin\\DripCampaignController@index');
$router->get('/admin/drip-campaigns/create', 'App\\Http\\Controllers\\Admin\\DripCampaignController@create');
$router->post('/admin/drip-campaigns/store', 'App\\Http\\Controllers\\Admin\\DripCampaignController@store');
$router->get('/admin/drip-campaigns/show/{id}', 'App\\Http\\Controllers\\Admin\\DripCampaignController@show');
$router->get('/admin/drip-campaigns/process', 'App\\Http\\Controllers\\Admin\\DripCampaignController@process');
$router->get('/admin/drip-campaigns/toggle', 'App\\Http\\Controllers\\Admin\\DripCampaignController@toggle');
$router->get('/admin/drip-campaigns/delete', 'App\\Http\\Controllers\\Admin\\DripCampaignController@delete');

$router->get('/admin/live-chat', 'App\\Http\\Controllers\\Admin\\LiveChatController@index');
$router->get('/admin/live-chat/open/{id}', 'App\\Http\\Controllers\\Admin\\LiveChatController@open');
$router->post('/admin/live-chat/send', 'App\\Http\\Controllers\\Admin\\LiveChatController@send');
$router->get('/admin/live-chat/poll', 'App\\Http\\Controllers\\Admin\\LiveChatController@poll');
$router->get('/admin/live-chat/assign', 'App\\Http\\Controllers\\Admin\\LiveChatController@assign');
$router->get('/admin/live-chat/close', 'App\\Http\\Controllers\\Admin\\LiveChatController@close');
$router->any('/admin/live-chat/settings', 'App\\Http\\Controllers\\Admin\\LiveChatController@settings');
$router->get('/admin/live-chat/quick-replies', 'App\\Http\\Controllers\\Admin\\LiveChatController@quickReplies');
$router->get('/api/live-chat', 'App\\Http\\Controllers\\Admin\\LiveChatController@api');

$router->get('/admin/nps', 'App\\Http\\Controllers\\Admin\\NpsController@index');
$router->get('/admin/nps/create', 'App\\Http\\Controllers\\Admin\\NpsController@create');
$router->post('/admin/nps/store', 'App\\Http\\Controllers\\Admin\\NpsController@store');
$router->get('/admin/nps/show/{id}', 'App\\Http\\Controllers\\Admin\\NpsController@show');
$router->get('/admin/nps/edit/{id}', 'App\\Http\\Controllers\\Admin\\NpsController@edit');
$router->post('/admin/nps/update', 'App\\Http\\Controllers\\Admin\\NpsController@update');
$router->get('/admin/nps/delete', 'App\\Http\\Controllers\\Admin\\NpsController@delete');
$router->get('/admin/nps/send', 'App\\Http\\Controllers\\Admin\\NpsController@send');
$router->get('/admin/nps/process-triggers', 'App\\Http\\Controllers\\Admin\\NpsController@processTriggers');

$router->get('/admin/auctions', 'App\\Http\\Controllers\\Admin\\AuctionController@index');
$router->get('/admin/auctions/create', 'App\\Http\\Controllers\\Admin\\AuctionController@create');
$router->post('/admin/auctions/store', 'App\\Http\\Controllers\\Admin\\AuctionController@store');
$router->get('/admin/auctions/show/{id}', 'App\\Http\\Controllers\\Admin\\AuctionController@show');
$router->get('/admin/auctions/start/{id}', 'App\\Http\\Controllers\\Admin\\AuctionController@start');
$router->get('/admin/auctions/end/{id}', 'App\\Http\\Controllers\\Admin\\AuctionController@end');
$router->get('/admin/auctions/cancel', 'App\\Http\\Controllers\\Admin\\AuctionController@cancel');
$router->get('/admin/auctions/delete/{id}', 'App\\Http\\Controllers\\Admin\\AuctionController@delete');
$router->get('/admin/auctions/process-ending', 'App\\Http\\Controllers\\Admin\\AuctionController@processEnding');

$router->get('/auctions', 'App\\Http\\Controllers\\Front\\AuctionController@index');
$router->get('/auctions/{id}', 'App\\Http\\Controllers\\Front\\AuctionController@show');
$router->post('/auctions/bid', 'App\\Http\\Controllers\\Front\\AuctionController@bid');
$router->post('/auctions/watch', 'App\\Http\\Controllers\\Front\\AuctionController@watch');
$router->post('/auctions/unwatch', 'App\\Http\\Controllers\\Front\\AuctionController@unwatch');
$router->post('/auctions/deposit', 'App\\Http\\Controllers\\Front\\AuctionController@deposit');

$router->post('/api/chat/start', 'App\\Http\\Controllers\\Front\\LiveChatWidgetController@start');
$router->post('/api/chat/send', 'App\\Http\\Controllers\\Front\\LiveChatWidgetController@send');
$router->get('/api/chat/poll', 'App\\Http\\Controllers\\Front\\LiveChatWidgetController@poll');
$router->get('/api/chat/widget', 'App\\Http\\Controllers\\Front\\LiveChatWidgetController@widget');
$router->get('/api/chat/history', 'App\\Http\\Controllers\\Front\\LiveChatWidgetController@history');

$router->get('/admin/reviews', 'App\\Http\\Controllers\\Admin\\ReviewController@index');
$router->get('/admin/reviews/approve', 'App\\Http\\Controllers\\Admin\\ReviewController@approve');
$router->get('/admin/reviews/reject', 'App\\Http\\Controllers\\Admin\\ReviewController@reject');
$router->post('/admin/reviews/respond', 'App\\Http\\Controllers\\Admin\\ReviewController@respond');
$router->get('/admin/reviews/delete', 'App\\Http\\Controllers\\Admin\\ReviewController@delete');
$router->get('/admin/reviews/feature-testimonial', 'App\\Http\\Controllers\\Admin\\ReviewController@featureTestimonial');
$router->get('/admin/reviews/approve-testimonial', 'App\\Http\\Controllers\\Admin\\ReviewController@approveTestimonial');
$router->get('/admin/reviews/reject-testimonial', 'App\\Http\\Controllers\\Admin\\ReviewController@rejectTestimonial');
$router->get('/admin/reviews/delete-testimonial', 'App\\Http\\Controllers\\Admin\\ReviewController@deleteTestimonial');

// /admin/visits is also defined at line 463 (Admin\VisitController@index) - the LATER route wins
$router->get('/admin/visits/confirm', 'App\\Http\\Controllers\\Admin\\VisitController@confirm');
$router->get('/admin/visits/complete', 'App\\Http\\Controllers\\Admin\\VisitController@complete');
$router->get('/admin/visits/cancel', 'App\\Http\\Controllers\\Admin\\VisitController@cancel');
$router->get('/admin/visits/noshow', 'App\\Http\\Controllers\\Admin\\VisitController@noshow');

$router->get('/visit/book', 'App\\Http\\Controllers\\Front\\VisitController@book');
$router->post('/visit/store', 'App\\Http\\Controllers\\Front\\VisitController@store');
$router->get('/visit/confirm', 'App\\Http\\Controllers\\Front\\VisitController@confirm');
$router->get('/visit/my-visits', 'App\\Http\\Controllers\\Front\\VisitController@myVisits');
$router->post('/visit/cancel', 'App\\Http\\Controllers\\Front\\VisitController@cancel');

$router->get('/testimonials', 'App\\Http\\Controllers\\Front\\TestimonialsController@index');
$router->get('/testimonials/submit', 'App\\Http\\Controllers\\Front\\TestimonialsController@showSubmit');
$router->post('/testimonials/submit', 'App\\Http\\Controllers\\Front\\TestimonialsController@submit');

$router->get('/property-alerts/subscribe', 'App\\Http\\Controllers\\Front\\PropertyAlertsController@index');
$router->post('/property-alerts/subscribe', 'App\\Http\\Controllers\\Front\\PropertyAlertsController@store');
$router->get('/property-alerts/unsubscribe', 'App\\Http\\Controllers\\Front\\PropertyAlertsController@unsubscribe');

$router->get('/property-comparison', 'App\\Http\\Controllers\\Front\\PropertyComparisonController@index');
$router->post('/property-comparison/add', 'App\\Http\\Controllers\\Front\\PropertyComparisonController@add');
$router->get('/property-comparison/add', 'App\\Http\\Controllers\\Front\\PropertyComparisonController@add');
$router->post('/property-comparison/remove', 'App\\Http\\Controllers\\Front\\PropertyComparisonController@remove');
$router->post('/property-comparison/clear', 'App\\Http\\Controllers\\Front\\PropertyComparisonController@clear');
$router->get('/property-comparison/share', 'App\\Http\\Controllers\\Front\\PropertyComparisonController@share');

$router->get('/user/referral', 'App\\Http\\Controllers\\Front\\ReferralController@index');
$router->get('/api/referral/share', 'App\\Http\\Controllers\\Front\\ReferralController@share');
$router->post('/property/inquire', 'Front\\PropertyController@propertyInquiry');
$router->get('/dashboard', 'App\\Http\\Controllers\\DashboardController@index');
$router->get('/dashboard/profile', 'App\\Http\\Controllers\\DashboardController@profile');
$router->post('/dashboard/profile', 'App\\Http\\Controllers\\DashboardController@updateProfile');
$router->get('/dashboard/favorites', 'App\\Http\\Controllers\\DashboardController@favorites');
$router->post('/dashboard/favorites/add', 'App\\Http\\Controllers\\DashboardController@addFavorite');
$router->post('/dashboard/favorites/remove', 'App\\Http\\Controllers\\DashboardController@removeFavorite');
$router->get('/dashboard/inquiries', 'App\\Http\\Controllers\\DashboardController@inquiries');
$router->post('/dashboard/inquiries/submit', 'App\\Http\\Controllers\\DashboardController@submitInquiry');

// AI Routes
$router->get('/ai-valuation', 'App\\Http\\Controllers\\AIController@propertyValuation');

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
$router->get('/admin/deals/create', 'App\Http\Controllers\Admin\DealController@create');
$router->post('/admin/deals/store', 'App\Http\Controllers\Admin\DealController@store');
$router->post('/admin/deals/{id}/stage', 'App\Http\Controllers\Admin\DealController@updateStage');

// Achievement Routes
$router->get('/dashboard/achievements', 'App\\Http\\Controllers\\AchievementController@index');
$router->get('/api/achievements/points', 'App\\Http\\Controllers\\AchievementController@getPoints');
$router->get('/api/achievements/badges', 'App\\Http\\Controllers\\AchievementController@getBadges');

// ============================================================
// AUTHENTICATION
// ============================================================

// Unified Registration (Customer / Agent / Associate — main entry point)
$router->get('/register', 'Auth\\RegisterController@showRegister');
$router->post('/register', 'Auth\\RegisterController@handleRegister');

// Unified Registration alias (backward compatibility)
$router->get('/register/unified', 'Auth\\RegisterController@showRegister');
$router->post('/register/unified', 'Auth\\RegisterController@handleRegister');

// Direct Customer-only registration — redirect to unified register with role=customer
$router->get('/register/customer', function() { header('Location: ' . BASE_URL . '/register?role=customer'); exit; });
$router->post('/register/customer', function() { $_POST['role'] = 'customer'; header('Location: ' . BASE_URL . '/register'); exit; });

// Smart Registration (Phone-First One-Click)
$router->get('/register/smart', 'Auth\\OtpAuthController@showPhoneInput');
$router->post('/register/smart/send-otp', 'Auth\\OtpAuthController@sendOtp');
$router->get('/register/smart/otp', 'Auth\\OtpAuthController@showOtpVerification');
$router->post('/register/smart/verify-otp', 'Auth\\OtpAuthController@verifyOtp');
$router->get('/register/smart/profile-complete', 'Auth\\OtpAuthController@showProfileCompletion');
$router->post('/register/smart/save-profile', 'Auth\\OtpAuthController@saveProfileProgress');
$router->post('/register/smart/skip-profile', 'Auth\\OtpAuthController@skipProfileCompletion');
$router->post('/register/smart/resend-otp', 'Auth\\OtpAuthController@resendOtp');
$router->post('/api/smart-register/track', 'Auth\\OtpAuthController@trackBehavior');

// Multi-step Registration Wizard (4 steps + OTP + skip)
if (file_exists(__DIR__ . '/../app/Http/Controllers/Auth/RegistrationWizardController.php')) {
    $router->get('/register/step1', 'Auth\\RegistrationWizardController@step1');
    $router->post('/register/step1', 'Auth\\RegistrationWizardController@saveStep1');
    $router->get('/register/step2', 'Auth\\RegistrationWizardController@step2');
    $router->post('/register/step2', 'Auth\\RegistrationWizardController@saveStep2');
    $router->get('/register/step3', 'Auth\\RegistrationWizardController@step3');
    $router->post('/register/step3', 'Auth\\RegistrationWizardController@saveStep3');
    $router->get('/register/step4', 'Auth\\RegistrationWizardController@step4');
    $router->post('/register/step4', 'Auth\\RegistrationWizardController@saveStep4');
    $router->post('/register/complete', 'Auth\\RegistrationWizardController@complete');
    $router->post('/register/resend-otp', 'Auth\\RegistrationWizardController@resendOtp');
    $router->post('/register/verify-otp', 'Auth\\RegistrationWizardController@verifyOtp');
    $router->post('/register/skip', 'Auth\\RegistrationWizardController@skip');
}

// Unified Authentication (AuthController — consolidates LoginController + AuthenticationController + AuthController + CustomerAuthController)
$router->get('/login', 'Auth\\AuthController@showLogin');
$router->post('/login', 'Auth\\AuthController@authenticate');
$router->get('/logout', 'Auth\\AuthController@logout');

// Unified Auth aliases (backward compatibility)
$router->get('/auth/login', 'Auth\\AuthController@showLogin');
$router->post('/auth/login', 'Auth\\AuthController@authenticate');
$router->get('/auth/register', 'Auth\\RegisterController@showRegister');
$router->post('/auth/register', 'Auth\\RegisterController@handleRegister');
$router->get('/auth/logout', 'Auth\\AuthController@logout');

// CoreAuth — Unified Auth (replaces all role-specific auth over time)
$router->post('/auth/smart/role', 'Auth\\OtpAuthController@saveRoleSelection');

// Air Login — OTP-based login without password
$router->get('/auth/air-login', 'Auth\\OtpAuthController@showAirLogin');
$router->post('/auth/air-login', 'Auth\\OtpAuthController@requestAirLoginOtp');
$router->get('/auth/air-login/verify', 'Auth\\OtpAuthController@showAirLoginVerify');
$router->post('/auth/air-login/verify', 'Auth\\OtpAuthController@verifyAirLoginOtp');

// Profile Photo — Unified upload/delete for all roles
$router->post('/profile/photo/upload', 'ProfilePhotoController@upload');
$router->post('/profile/photo/delete', 'ProfilePhotoController@delete');

// Agent Auth
$router->get('/agent/register', 'Auth\\AgentAuthController@register');
$router->post('/agent/register', 'Auth\\AgentAuthController@handleRegister');
$router->get('/agent/login', 'Auth\\AgentAuthController@login');
$router->post('/agent/login', 'Auth\\AgentAuthController@authenticate');
$router->get('/agent/logout', 'Auth\\AgentAuthController@logout');
$router->get('/agent/dashboard', 'Agent\\AgentDashboardController@index');
$router->get('/agent/leads', 'Agent\\AgentDashboardController@leads');
$router->get('/agent/leads/{id}', 'Agent\\AgentDashboardController@leadDetail');
$router->post('/agent/leads/{id}/status', 'Agent\\AgentDashboardController@updateLeadStatus');
$router->post('/agent/leads/{id}/note', 'Agent\\AgentDashboardController@addLeadNote');
$router->get('/agent/properties', 'Agent\\AgentDashboardController@properties');
$router->get('/agent/commissions', 'Agent\\AgentDashboardController@commissions');
$router->get('/agent/profile', 'Agent\\AgentDashboardController@profile');
$router->post('/agent/profile', 'Agent\\AgentDashboardController@updateProfile');
$router->get('/agent/wallet', 'Agent\\AgentDashboardController@wallet');
$router->get('/agent/deals', 'Agent\\AgentDashboardController@deals');

// Agent Cash Collections
$router->get('/agent/collections', 'App\\Http\\Controllers\\FieldCollectionController@index');
$router->get('/agent/collections/create', 'App\\Http\\Controllers\\FieldCollectionController@create');
$router->post('/agent/collections/store', 'App\\Http\\Controllers\\FieldCollectionController@store');
$router->get('/agent/collections/{id}', 'App\\Http\\Controllers\\FieldCollectionController@show');
// Associate Auth
$router->get('/register/associate', function() { header('Location: ' . BASE_URL . '/associate/register'); exit; });
$router->get('/associate/register', 'Auth\\AssociateAuthController@associateRegister');
$router->post('/associate/register', 'Auth\\AssociateAuthController@handleAssociateRegister');
$router->get('/associate/login', 'Auth\\AssociateAuthController@associateLogin');
$router->post('/associate/login', 'Auth\\AssociateAuthController@authenticateAssociate');
$router->get('/associate/logout', 'Auth\\AssociateAuthController@logout');
$router->get('/associate/dashboard', 'App\\Http\\Controllers\\AssociateController@dashboard');
$router->get('/associate/add-property', 'App\\Http\\Controllers\\AssociateController@addProperty');
$router->post('/associate/add-property', 'App\\Http\\Controllers\\AssociateController@storeAddProperty');
$router->get('/associate/leads', 'App\\Http\\Controllers\\AssociateController@leads');
$router->get('/associate/crm', 'App\\Http\\Controllers\\AssociateController@crmDashboard');
$router->get('/associate/commissions', 'App\\Http\\Controllers\\AssociateController@commissions');
$router->get('/associate/properties', 'App\\Http\\Controllers\\AssociateController@properties');
$router->get('/associate/properties/edit/{id}', 'App\\Http\\Controllers\\AssociateController@editProperty');
$router->post('/associate/properties/update/{id}', 'App\\Http\\Controllers\\AssociateController@updateProperty');
$router->post('/associate/properties/delete/{id}', 'App\\Http\\Controllers\\AssociateController@deleteProperty');
$router->get('/associate/sold', 'App\\Http\\Controllers\\AssociateController@sold');
$router->get('/associate/pending', 'App\\Http\\Controllers\\AssociateController@pending');
$router->get('/associate/profile', 'App\\Http\\Controllers\\AssociateController@profile');
$router->post('/associate/profile', 'App\\Http\\Controllers\\AssociateController@profile');
$router->get('/associate/genealogy', 'App\\Http\\Controllers\\MLMTreeController@genealogy');
$router->get('/associate/wallet', 'App\\Http\\Controllers\\WalletController@associateWallet');
$router->get('/associate/bank-details', 'App\\Http\\Controllers\\WalletController@bankAccounts');
$router->get('/associate/settings', 'App\\Http\\Controllers\\AssociateController@settings');
$router->get('/associate/mlm-plan', 'App\\Http\\Controllers\\AssociateController@mlmPlan');
$router->get('/associate/documents', 'App\\Http\\Controllers\\AssociateController@documents');
$router->post('/associate/documents/upload', 'App\\Http\\Controllers\\AssociateController@uploadDocument');
$router->get('/associate/browse', 'App\\Http\\Controllers\\AssociateController@browse');
$router->get('/associate/list-property', 'App\\Http\\Controllers\\AssociateController@listProperty');
$router->post('/associate/list-property/submit', 'App\\Http\\Controllers\\AssociateController@submitProperty');
$router->post('/property/interest', 'App\\Http\\Controllers\\Front\\PropertyController@propertyInterest');

// Associate Leads CRM routes (specific routes FIRST)
$router->get('/associate/leads/add', 'App\\Http\\Controllers\\AssociateController@addLead');
$router->post('/associate/leads/store', 'App\\Http\\Controllers\\AssociateController@storeLead');
$router->get('/associate/leads/all', 'App\\Http\\Controllers\\AssociateController@leads');
$router->post('/associate/leads/{id}/status', 'App\\Http\\Controllers\\AssociateController@updateLeadStatus');
$router->post('/associate/leads/{id}/note', 'App\\Http\\Controllers\\AssociateController@addLeadNote');
$router->get('/associate/leads/{id}', 'App\\Http\\Controllers\\AssociateController@leadDetail');
$router->get('/associate/leads/import', 'App\\Http\\Controllers\\AssociateController@importLeads');
$router->post('/associate/leads/import', 'App\\Http\\Controllers\\AssociateController@importLeads');
$router->get('/associate/leads/bulk-whatsapp', 'App\\Http\\Controllers\\AssociateController@bulkWhatsApp');
$router->post('/associate/leads/bulk-whatsapp', 'App\\Http\\Controllers\\AssociateController@bulkWhatsApp');
$router->post('/associate/leads/{id}/assign', 'App\\Http\\Controllers\\AssociateController@assignLead');
$router->post('/associate/leads/{id}/recalculate-score', 'App\\Http\\Controllers\\AssociateController@recalculateScore');
$router->post('/associate/leads/recalculate-all-scores', 'App\\Http\\Controllers\\AssociateController@recalculateAllScores');
$router->get('/associate/leads/export', 'App\\Http\\Controllers\\AssociateController@exportLeads');
$router->get('/associate/commissions/history', 'App\\Http\\Controllers\\AssociateController@commissions');
$router->get('/associate/wallet/withdraw', 'App\\Http\\Controllers\\WalletController@withdrawal');
$router->get('/associate/network/tree', 'App\\Http\\Controllers\\MLMTreeController@tree');
$router->get('/associate/team', 'App\\Http\\Controllers\\AssociateController@team');


// Associate Cash Collections
$router->get('/associate/collections', 'App\\Http\\Controllers\\FieldCollectionController@index');
$router->get('/associate/collections/create', 'App\\Http\\Controllers\\FieldCollectionController@create');
$router->post('/associate/collections/store', 'App\\Http\\Controllers\\FieldCollectionController@store');
$router->get('/associate/collections/{id}', 'App\\Http\\Controllers\\FieldCollectionController@show');
// Associate Exports
$router->get('/associate/export/my-earnings', 'Associate\ExportController@myEarnings');
$router->get('/associate/export/active-team', 'Associate\ExportController@activeTeam');
$router->get('/associate/export/my-payouts', 'Associate\ExportController@myPayouts');
$router->get('/associate/export/downline', 'Associate\ExportController@downline');

// Associate Colony Map
$router->get('/associate/colonies/{id}/map', 'App\\Http\\Controllers\\AssociateController@colonyMap');
$router->get('/associate/export/new-directs', 'Associate\ExportController@newDirects');
$router->get('/associate/export/plot-sales', 'Associate\ExportController@plotSales');
$router->get('/associate/export/registry', 'Associate\ExportController@registry');

// Associate Follow-ups & Schedule
$router->get('/associate/followups', 'App\\Http\\Controllers\\AssociateController@followups');
$router->post('/associate/followups/update/{id}', 'App\\Http\\Controllers\\AssociateController@updateFollowup');
$router->get('/associate/schedule', 'App\\Http\\Controllers\\AssociateController@schedule');
$router->get('/associate/schedule/calendar-data', 'App\\Http\\Controllers\\AssociateController@calendarData');

// Associate Site Visits
$router->get('/associate/site-visits', 'App\\Http\\Controllers\\AssociateController@siteVisits');
$router->get('/associate/site-visits/schedule', 'App\\Http\\Controllers\\AssociateController@scheduleSiteVisit');
$router->post('/associate/site-visits/schedule', 'App\\Http\\Controllers\\AssociateController@scheduleSiteVisit');
$router->post('/associate/site-visits/{id}/complete', 'App\\Http\\Controllers\\AssociateController@completeSiteVisit');
$router->post('/associate/site-visits/{id}/cancel', 'App\\Http\\Controllers\\AssociateController@cancelSiteVisit');
$router->post('/associate/site-visits/{id}/reschedule', 'App\\Http\\Controllers\\AssociateController@rescheduleSiteVisit');

$router->get('/associate/referral', 'App\\Http\\Controllers\\AssociateController@referral');
$router->get('/associate/compare', 'App\\Http\\Controllers\\AssociateController@compareProperties');

// Associate Business Intelligence
$router->get('/associate/my-bookings', 'App\\Http\\Controllers\\AssociateController@myBookings');
$router->get('/associate/my-customers', 'App\\Http\\Controllers\\AssociateController@myCustomers');
$router->get('/associate/customer/{id}', 'App\\Http\\Controllers\\AssociateController@customerDetail');
$router->get('/associate/emi-tracker', 'App\\Http\\Controllers\\AssociateController@emiTracker');
$router->get('/associate/payment-history', 'App\\Http\\Controllers\\AssociateController@paymentHistory');
$router->get('/associate/booking/{id}/receipt', 'App\\Http\\Controllers\\AssociateController@bookingReceipt');
$router->get('/associate/rank-eligibility', 'App\\Http\\Controllers\\AssociateController@rankEligibility');
$router->get('/associate/book-plot', 'App\\Http\\Controllers\\AssociateController@bookPlot');
$router->post('/associate/book-plot/submit', 'App\\Http\\Controllers\\AssociateController@bookPlot');
$router->get('/associate/tools', 'App\\Http\\Controllers\\AssociateController@tools');
$router->post('/associate/tools/emi', 'App\\Http\\Controllers\\AssociateController@emiCalculator');
$router->post('/associate/tools/stamp-duty', 'App\\Http\\Controllers\\AssociateController@stampDutyCalculator');
$router->post('/associate/tools/plot-converter', 'App\\Http\\Controllers\\AssociateController@plotConverter');
$router->post('/associate/tools/commission', 'App\\Http\\Controllers\\AssociateController@commissionCalculator');

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

// Farmer Self-Service Portal
$router->get('/farmer/login', 'Auth\\FarmerAuthController@loginForm');
$router->post('/farmer/login', 'Auth\\FarmerAuthController@login');
$router->get('/farmer/logout', 'Auth\\FarmerAuthController@logout');
$router->get('/farmer/dashboard', 'Front\\FarmerDashboardController@dashboard');
$router->get('/farmer/land-holdings', 'Front\\FarmerDashboardController@landHoldings');
$router->get('/farmer/payments', 'Front\\FarmerDashboardController@payments');
$router->get('/farmer/agreements', 'Front\\FarmerDashboardController@agreements');
$router->get('/farmer/agreements/download/{id}', 'Front\\FarmerDashboardController@agreementDownload');
$router->get('/farmer/profile', 'Front\\FarmerDashboardController@profile');
$router->post('/farmer/profile', 'Front\\FarmerDashboardController@updateProfile');

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
$router->get('/employee/notifications', 'Employee\\EmployeeController@notifications');
$router->post('/employee/notifications/read-all', 'Employee\\EmployeeController@markAllNotificationsRead');
$router->post('/employee/notifications/{id}/read', 'Employee\\EmployeeController@markNotificationRead');

// Employee Pages
$router->get('/employee/leads', 'Employee\\EmployeeController@leads');
$router->get('/employee/leads/{id}', 'Employee\\EmployeeController@leadDetail');
$router->post('/employee/leads/{id}/status', 'Employee\\EmployeeController@updateLeadStatus');
$router->post('/employee/leads/{id}/note', 'Employee\\EmployeeController@addLeadNote');
$router->get('/employee/tasks', 'Employee\\EmployeeController@tasks');
$router->get('/employee/activities', 'Employee\\EmployeeController@activities');
$router->get('/employee/attendance', 'Employee\\EmployeeController@attendance');
$router->get('/employee/performance-page', 'Employee\\EmployeeController@performancePage');
$router->get('/employee/performance', 'Employee\\EmployeeController@performancePage');
$router->get('/employee/salary', 'Employee\\EmployeeController@salary');
$router->get('/employee/payroll', 'Employee\\EmployeeController@salary');
$router->get('/employee/documents', 'Employee\\EmployeeController@documents');
$router->post('/employee/documents/upload', 'Employee\\EmployeeController@uploadDocument');
$router->get('/employee/leaves', 'Employee\\EmployeeController@leaves');
$router->post('/employee/leaves/apply', 'Employee\\EmployeeController@leaveApply');
$router->get('/employee/leaves/{id}', 'Employee\\EmployeeController@leaveDetail');
$router->post('/employee/leaves/{id}/cancel', 'Employee\\EmployeeController@leaveCancel');
$router->get('/employee/reporting', 'Employee\\EmployeeController@reporting');
$router->get('/employee/settings', 'Employee\\EmployeeController@dashboard');
$router->get('/employee/user-properties', 'Employee\\EmployeeController@userProperties');
$router->post('/employee/user-properties/action', 'Employee\\EmployeeController@updatePropertyStatus');

// Employee Password Change
$router->get('/employee/change-password', 'Employee\\EmployeeController@changePasswordView');
$router->post('/employee/change-password', 'Employee\\EmployeeController@changePassword');

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
$router->get('/employee/marketing-dashboard', 'Employee\\EmployeeDashboardController@marketingDashboard');
$router->get('/employee/finance-dashboard', 'Employee\\EmployeeDashboardController@financeDashboard');
$router->get('/employee/it-dashboard', 'Employee\\EmployeeDashboardController@itDashboard');
$router->get('/employee/ops-dashboard', 'Employee\\EmployeeDashboardController@opsDashboard');
$router->get('/employee/sales-dashboard', 'Employee\\EmployeeDashboardController@salesDashboard');
$router->post('/employee/dashboard/update-task-status', 'Employee\\EmployeeDashboardController@updateTaskStatus');

// Employee department pages (16 missing sidebar routes — single dynamic route)
$router->get('/employee/{slug}', 'Employee\\EmployeeController@departmentPage');

// MLM/Team
$router->get('/team/genealogy', 'Admin\\NetworkController@genealogy');
$router->get('/api/mlm/tree', 'App\\Http\\Controllers\\MLMController@getNetworkTree');
$router->get('/api/mlm/tree-data', 'App\\Http\\Controllers\\MLMTreeController@getTreeData');
$router->get('/api/mlm/search', 'App\\Http\\Controllers\\MLMTreeController@search');

// ============================================================
// AI PROPERTY VALUATION
// ============================================================

$router->get('/ai/property-valuation', 'AI\\PropertyValuationController@index');
$router->post('/ai/property-valuation/generate', 'AI\\PropertyValuationController@generateValuation');
$router->get('/ai/property-valuation/history', 'AI\\PropertyValuationController@getValuationHistory');
$router->post('/ai/property-valuation/batch', 'AI\\PropertyValuationController@batchValuation');
$router->post('/api/ai/valuation', 'AI\\PropertyValuationController@apiValuation');

// Property Valuation Reports
$router->get('/admin/property-valuations', 'AI\\PropertyValuationController@reports');
$router->get('/admin/property-valuations/view/{id}', 'AI\\PropertyValuationController@viewReport');
$router->get('/admin/property-valuations/generate', 'AI\\PropertyValuationController@showGenerateForm');
$router->post('/admin/property-valuations/generate', 'AI\\PropertyValuationController@generateAndStore');
$router->get('/admin/property-valuations/property/{id}', 'AI\\PropertyValuationController@valuationByProperty');

// ============================================================
// AI CHATBOT
// ============================================================
$router->get('/ai/chatbot', 'AI\\AIWebController@chatbot');
$router->get('/ai/assistant', 'Front\\AiAssistantController@index');
$router->get('/ai/description-generator', 'AI\\AIWebController@descriptionGenerator');
$router->get('/ai/suggestions', 'AI\\AIWebController@suggestions');
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
$router->get('/admin', function() {
    header('Location: ' . BASE_URL . '/admin/erp');
    exit;
});
$router->get('/admin/dashboard', 'App\\Http\\Controllers\\RoleBasedDashboardController@index');
$router->get('/admin/erp', 'App\\Http\\Controllers\\Admin\\AdminController@erpOverview');
$router->get('/admin/enterprise_dashboard', 'App\\Http\\Controllers\\RoleBasedDashboardController@enterpriseDashboard');

// Real-Time Analytics Dashboard
$router->get('/admin/analytics/realtime', 'App\\Http\\Controllers\\Admin\\RealtimeAnalyticsController@dashboard');
$router->get('/admin/analytics/realtime/metrics', 'App\\Http\\Controllers\\Admin\\RealtimeAnalyticsController@apiMetrics');
$router->get('/admin/analytics/realtime/chart-data', 'App\\Http\\Controllers\\Admin\\RealtimeAnalyticsController@apiChartData');

// Admin root route fix
$router->get('/admin/', function() {
    header('Location: ' . BASE_URL . '/admin/erp');
    exit;
});

// Role-specific dashboards
$router->get('/admin/dashboard/agent', 'App\\Http\\Controllers\\RoleBasedDashboardController@agent');
$router->get('/admin/dashboard/builder', 'App\\Http\\Controllers\\RoleBasedDashboardController@builder');
$router->get('/admin/dashboard/ceo', 'App\\Http\\Controllers\\RoleBasedDashboardController@ceo');
$router->get('/admin/dashboard/cfo', 'App\\Http\\Controllers\\RoleBasedDashboardController@cfo');
$router->get('/admin/dashboard/cto', 'App\\Http\\Controllers\\RoleBasedDashboardController@cto');
$router->get('/admin/dashboard/coo', 'App\\Http\\Controllers\\RoleBasedDashboardController@coo');
$router->get('/admin/dashboard/cmo', 'App\\Http\\Controllers\\RoleBasedDashboardController@cm');
$router->get('/admin/dashboard/chro', 'App\\Http\\Controllers\\RoleBasedDashboardController@hr');
$router->get('/admin/dashboard/sales', 'App\\Http\\Controllers\\RoleBasedDashboardController@sales');
$router->get('/admin/dashboard/marketing', 'App\\Http\\Controllers\\RoleBasedDashboardController@marketing');
$router->get('/admin/dashboard/finance', 'App\\Http\\Controllers\\RoleBasedDashboardController@finance');
$router->get('/admin/dashboard/hr', 'App\\Http\\Controllers\\RoleBasedDashboardController@hr');
$router->get('/admin/dashboard/it', 'App\\Http\\Controllers\\RoleBasedDashboardController@it');
$router->get('/admin/dashboard/operations', 'App\\Http\\Controllers\\RoleBasedDashboardController@operations');

// Admin AJAX Dashboard APIs
$router->get('/admin/api/dashboard-stats', function () {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id']) && !isset($_SESSION['role'])) {
        echo json_encode(['success' => false]);
        exit;
    }
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $properties = $db->query("SELECT COUNT(*) FROM user_properties")->fetchColumn();
        $revenue = $db->query("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE status = 'approved'")->fetchColumn();
        echo json_encode(['success' => true, 'users' => (int)$users, 'properties' => (int)$properties, 'revenue' => (float)$revenue]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false]);
    }
    exit;
});
$router->get('/api/dashboard/updates', function () {
    header('Content-Type: application/json');
    header('X-Requested-With: XMLHttpRequest');
    if (!isset($_SESSION['admin_id']) && !isset($_SESSION['role'])) {
        echo json_encode(['success' => false]);
        exit;
    }
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $newLeads = $db->query("SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
        $newBookings = $db->query("SELECT COUNT(*) FROM plot_bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
        $pendingPayments = $db->query("SELECT COUNT(*) FROM booking_payment_schedules WHERE status = 'pending' AND due_date <= CURDATE()")->fetchColumn();
        $activeUsers = $db->query("SELECT COUNT(*) FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 1 HOUR)")->fetchColumn();
        echo json_encode([
            'success' => true,
            'new_leads' => (int)$newLeads,
            'new_bookings' => (int)$newBookings,
            'pending_payments' => (int)$pendingPayments,
            'active_users' => (int)$activeUsers,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false]);
    }
    exit;
});
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
$router->get('/admin/properties/check-availability', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@checkAvailability');
$router->get('/admin/properties/{id}', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@show');
$router->get('/admin/properties/{id}/edit', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@edit');
$router->post('/admin/properties/{id}/update', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@update');
$router->post('/admin/properties/{id}/destroy', 'App\\Http\\Controllers\\Admin\\PropertyManagementController@destroy');

// AI Aggregator Trigger Route
$router->post('/admin/ai-aggregator/fetch', 'App\\Http\\Controllers\\Admin\\AIAggregatorController@triggerFetch');

// Admin Users
$router->get('/admin/users', 'App\\Http\\Controllers\\Admin\\UserController@index');
$router->get('/admin/users/create', 'App\\Http\\Controllers\\Admin\\UserController@create');
$router->post('/admin/users', 'App\\Http\\Controllers\\Admin\\UserController@store');
$router->get('/admin/users/pending', 'App\\Http\\Controllers\\Admin\\UserController@pending');
$router->post('/admin/users/{id}/approve', 'App\\Http\\Controllers\\Admin\\UserController@approve');
$router->post('/admin/users/{id}/reject', 'App\\Http\\Controllers\\Admin\\UserController@reject');
$router->post('/admin/users/bulk-approve', 'App\\Http\\Controllers\\Admin\\UserController@bulkApprove');
$router->get('/admin/users/{id}', 'App\\Http\\Controllers\\Admin\\UserController@show');
$router->get('/admin/users/{id}/edit', 'App\\Http\\Controllers\\Admin\\UserController@edit');
$router->post('/admin/users/{id}/update', 'App\\Http\\Controllers\\Admin\\UserController@update');
$router->post('/admin/users/{id}/destroy', 'App\\Http\\Controllers\\Admin\\UserController@destroy');

// Admin Users — Enhanced Management (Wallet, Commissions, Team, Sponsor)
$router->get('/admin/users/{id}/wallet', 'App\\Http\\Controllers\\Admin\\UserController@viewWallet');
$router->post('/admin/users/{id}/wallet/credit', 'App\\Http\\Controllers\\Admin\\UserController@creditWallet');
$router->post('/admin/users/{id}/wallet/debit', 'App\\Http\\Controllers\\Admin\\UserController@debitWallet');
$router->post('/admin/users/{id}/change-sponsor', 'App\\Http\\Controllers\\Admin\\UserController@changeSponsor');
$router->post('/admin/users/{id}/change-referral', 'App\\Http\\Controllers\\Admin\\UserController@changeReferralCode');
$router->get('/admin/users/{id}/team', 'App\\Http\\Controllers\\Admin\\UserController@viewTeam');
$router->get('/admin/users/{id}/commissions', 'App\\Http\\Controllers\\Admin\\UserController@viewCommissions');
$router->get('/admin/users/{id}/activity-log', 'App\\Http\\Controllers\\Admin\\UserController@viewActivityLog');
$router->post('/admin/users/{id}/soft-delete', 'App\\Http\\Controllers\\Admin\\UserController@softDelete');
$router->post('/admin/users/bulk-operation', 'App\\Http\\Controllers\\Admin\\UserController@bulkOperation');

// Admin Leads/CRM
$router->get('/admin/leads', 'App\\Http\\Controllers\\Admin\\LeadController@index');
$router->get('/admin/leads/create', 'App\\Http\\Controllers\\Admin\\LeadController@create');
$router->post('/admin/leads', 'App\\Http\\Controllers\\Admin\\LeadController@store');
$router->get('/admin/leads/status', 'App\\Http\\Controllers\\Admin\\LeadController@status');
$router->get('/admin/leads/followups', 'App\\Http\\Controllers\\Admin\\LeadController@followups');
$router->get('/admin/leads/analysis', 'App\\Http\\Controllers\\Admin\\LeadController@analysis');
$router->get('/admin/leads/assign', 'App\\Http\\Controllers\\Admin\\LeadController@assignPage');
$router->post('/admin/leads/assign/process', 'App\\Http\\Controllers\\Admin\\LeadController@processAssignment');
$router->get('/admin/leads/{id}', 'App\\Http\\Controllers\\Admin\\LeadController@show');
$router->get('/admin/leads/{id}/edit', 'App\\Http\\Controllers\\Admin\\LeadController@edit');
$router->post('/admin/leads/{id}/update', 'App\\Http\\Controllers\\Admin\\LeadController@update');
$router->post('/admin/leads/{id}/destroy', 'App\\Http\\Controllers\\Admin\\LeadController@destroy');
$router->post('/admin/leads/{id}/note', 'App\\Http\\Controllers\\Admin\\LeadController@addNote');
$router->post('/admin/leads/{id}/status', 'App\\Http\\Controllers\\Admin\\LeadController@updateStatus');
$router->post('/admin/leads/{id}/assign', 'App\\Http\\Controllers\\Admin\\LeadController@assign');

// Lead AJAX (inline interactions & tasks)
$router->post('/admin/leads/{id}/log-interaction', 'App\\Http\\Controllers\\Admin\\LeadController@logInteraction');
$router->post('/admin/leads/{id}/create-task', 'App\\Http\\Controllers\\Admin\\LeadController@createTask');
$router->post('/admin/leads/{id}/complete-task', 'App\\Http\\Controllers\\Admin\\LeadController@completeTask');

// Lead Bulk Action (AJAX)
$router->post('/admin/leads/bulk-action', 'App\\Http\\Controllers\\Admin\\LeadController@bulkAction');

// Lead Trash & Recovery
$router->get('/admin/leads/trash', 'App\\Http\\Controllers\\Admin\\LeadController@trash');
$router->get('/admin/leads/trash/list', 'App\\Http\\Controllers\\Admin\\LeadController@trash');
$router->post('/admin/leads/{id}/restore', 'App\\Http\\Controllers\\Admin\\LeadController@restore');
$router->post('/admin/leads/{id}/permanent-delete', 'App\\Http\\Controllers\\Admin\\LeadController@permanentDelete');

// Lead Export
$router->get('/admin/leads/export/csv', 'App\\Http\\Controllers\\Admin\\LeadController@export');

$router->get('/admin/leads/commission-heatmap', 'App\\Http\\Controllers\\Admin\\LeadController@commissionHeatmap');
$router->get('/admin/leads/property-comparison', 'App\\Http\\Controllers\\Admin\\LeadController@propertyComparison');
$router->get('/admin/leads/telecaller-performance', 'App\\Http\\Controllers\\Admin\\LeadController@telecallerPerformance');

// CRM Settings
$router->get('/admin/crm/settings', 'App\\Http\\Controllers\\Admin\\CRMSettingsController@index');
$router->get('/admin/crm-settings', 'App\\Http\\Controllers\\Admin\\CRMSettingsController@index');
$router->post('/admin/crm/settings/save', 'App\\Http\\Controllers\\Admin\\CRMSettingsController@save');
$router->post('/admin/crm-settings/save', 'App\\Http\\Controllers\\Admin\\CRMSettingsController@save');

// Employee lead create
$router->get('/employee/leads/add', 'Employee\\EmployeeController@addLead');
$router->post('/employee/leads/store', 'Employee\\EmployeeController@storeLead');
$router->post('/employee/leads/{id}/delete', 'Employee\\EmployeeController@deleteLead');

// Agent lead create (form-based)
$router->get('/agent/leads/add', 'Agent\\AgentDashboardController@addLeadForm');
$router->post('/agent/leads/store', 'Agent\\AgentDashboardController@storeLeadForm');
$router->post('/agent/leads/{id}/delete', 'Agent\\AgentDashboardController@deleteLead');

// Associate lead soft delete
$router->post('/associate/leads/{id}/delete', 'App\\Http\\Controllers\\AssociateController@deleteLead');

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

// Admin Site Visits
$router->get('/admin/site-visits', 'App\\Http\\Controllers\\Admin\\SiteVisitController@index');
$router->post('/admin/site-visits/{id}/status', 'App\\Http\\Controllers\\Admin\\SiteVisitController@updateStatus');

// Admin Agreements
$router->get('/admin/agreements', 'App\\Http\\Controllers\\Admin\\AgreementController@index');
$router->get('/admin/agreements/create', 'App\\Http\\Controllers\\Admin\\AgreementController@create');
$router->post('/admin/agreements/store', 'App\\Http\\Controllers\\Admin\\AgreementController@store');
$router->get('/admin/agreements/{id}', 'App\\Http\\Controllers\\Admin\\AgreementController@show');
$router->post('/admin/agreements/update/{id}', 'App\\Http\\Controllers\\Admin\\AgreementController@update');
$router->get('/admin/agreements/generate/{id}/{type}', 'App\\Http\\Controllers\\Admin\\AgreementController@generate');
$router->get('/admin/agreements/download/{id}', 'App\\Http\\Controllers\\Admin\\AgreementController@download');
$router->get('/admin/agreements/preview/{id}/{type}', 'App\\Http\\Controllers\\Admin\\AgreementController@preview');
$router->post('/admin/agreements/send/{id}', 'App\\Http\\Controllers\\Admin\\AgreementController@sendToCustomer');

// Admin Sites
$router->get('/admin/sites', 'App\\Http\\Controllers\\Admin\\SiteController@index');
$router->get('/admin/sites/create', 'App\\Http\\Controllers\\Admin\\SiteController@create');
$router->post('/admin/sites', 'App\\Http\\Controllers\\Admin\\SiteController@store');
$router->get('/admin/sites/inventory', 'App\\Http\\Controllers\\Admin\\SiteController@inventory');
$router->get('/admin/sites/{id}', 'App\\Http\\Controllers\\Admin\\SiteController@show');
$router->get('/admin/sites/{id}/edit', 'App\\Http\\Controllers\\Admin\\SiteController@edit');
$router->post('/admin/sites/{id}/update', 'App\\Http\\Controllers\\Admin\\SiteController@update');
$router->post('/admin/sites/{id}/destroy', 'App\\Http\\Controllers\\Admin\\SiteController@destroy');
$router->get('/admin/inventory', 'App\\Http\\Controllers\\Admin\\SiteController@inventory');

// Admin Inquiries
$router->get('/admin/inquiries', 'App\\Http\\Controllers\\Admin\\InquiryController@index');
$router->get('/admin/inquiries/view/{id}', 'App\\Http\\Controllers\\Admin\\InquiryController@show');
$router->post('/admin/inquiries/update-status', 'App\\Http\\Controllers\\Admin\\InquiryController@updateStatus');
$router->post('/admin/inquiries/delete/{id}', 'App\\Http\\Controllers\\Admin\\InquiryController@delete');
// Alias "enquiries" spelling to the same InquiryController (enquiry management is fully live here)
$router->get('/admin/enquiries', 'App\\Http\\Controllers\\Admin\\InquiryController@index');
$router->get('/admin/enquiries/view/{id}', 'App\\Http\\Controllers\\Admin\\InquiryController@show');
$router->post('/admin/enquiries/update-status', 'App\\Http\\Controllers\\Admin\\InquiryController@updateStatus');
$router->post('/admin/enquiries/delete/{id}', 'App\\Http\\Controllers\\Admin\\InquiryController@delete');

// Admin Plots
$router->get('/admin/plots', 'App\\Http\\Controllers\\Admin\\PlotManagementController@index');
$router->get('/admin/plots/create', 'App\\Http\\Controllers\\Admin\\PlotManagementController@create');
$router->post('/admin/plots', 'App\\Http\\Controllers\\Admin\\PlotManagementController@store');
$router->get('/admin/plots/check-availability', 'App\\Http\\Controllers\\Admin\\PlotManagementController@checkAvailability');
$router->post('/admin/plots/bulk-price-update', 'App\\Http\\Controllers\\Admin\\PlotManagementController@bulkPriceUpdate');
$router->get('/admin/plots/layout', 'Front\\ProjectController@plotMap');
$router->get('/admin/plots/availability', 'App\\Http\\Controllers\\Admin\\PlotManagementController@availability');
$router->get('/admin/plots/availability-data', 'App\\Http\\Controllers\\Admin\\PlotManagementController@availabilityData');
$router->get('/admin/plots/map', 'App\\Http\\Controllers\\Admin\\PlotManagementController@map');
$router->get('/admin/plots/{id}', 'App\\Http\\Controllers\\Admin\\PlotManagementController@show');
$router->get('/admin/plots/{id}/edit', 'App\\Http\\Controllers\\Admin\\PlotManagementController@edit');
$router->post('/admin/plots/{id}/update', 'App\\Http\\Controllers\\Admin\\PlotManagementController@update');
$router->post('/admin/plots/{id}/destroy', 'App\\Http\\Controllers\\Admin\\PlotManagementController@destroy');
$router->post('/admin/plots/{id}/update-status', 'App\\Http\\Controllers\\Admin\\PlotManagementController@updateStatus');
$router->get('/admin/plots/{id}/book', 'App\\Http\\Controllers\\Admin\\PlotManagementController@book');
$router->post('/admin/plots/{id}/book', 'App\\Http\\Controllers\\Admin\\PlotManagementController@storeBooking');
$router->get('/admin/plots/{id}/transfer', 'App\\Http\\Controllers\\Admin\\PlotManagementController@transfer');
$router->post('/admin/plots/{id}/transfer', 'App\\Http\\Controllers\\Admin\\PlotManagementController@transferPlot');
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
$router->post('/admin/blog/store', 'App\\Http\\Controllers\\Admin\\BlogController@store');
$router->get('/admin/blog/{id}/edit', 'App\\Http\\Controllers\\Admin\\BlogController@edit');
$router->post('/admin/blog/{id}/update', 'App\\Http\\Controllers\\Admin\\BlogController@update');
$router->post('/admin/blog/{id}/destroy', 'App\\Http\\Controllers\\Admin\\BlogController@destroy');

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

// Admin About CMS
$router->get('/admin/about-cms', 'App\\Http\\Controllers\\Admin\\AboutCmsController@index');
$router->post('/admin/about-cms/update', 'App\\Http\\Controllers\\Admin\\AboutCmsController@update');
$router->post('/admin/about-cms/upload-photo', 'App\\Http\\Controllers\\Admin\\AboutCmsController@uploadPhoto');

// Admin Settings & System
$router->get('/admin/settings', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->post('/admin/settings', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@update');
$router->get('/admin/settings/contact', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->post('/admin/settings/contact', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@update');
$router->get('/admin/settings/social', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->post('/admin/settings/social', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@update');
$router->get('/admin/settings/seo', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->post('/admin/settings/seo', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@update');
$router->get('/admin/settings/maintenance', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->post('/admin/settings/maintenance', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@update');
$router->post('/admin/settings/maintenance/toggle', 'App\\Http\\Controllers\\Admin\\MaintenanceController@toggle');
$router->post('/admin/settings/maintenance/ips/add', 'App\\Http\\Controllers\\Admin\\MaintenanceController@addIp');
$router->post('/admin/settings/maintenance/ips/remove', 'App\\Http\\Controllers\\Admin\\MaintenanceController@removeIp');
$router->get('/admin/settings/maintenance/status', 'App\\Http\\Controllers\\Admin\\MaintenanceController@status');
$router->get('/admin/settings/sms', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->post('/admin/settings/sms', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@update');
$router->get('/admin/settings/export', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@export');
$router->get('/admin/settings/stats', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@getStats');
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

// senior-developer routes removed — 7 dead stubs + saveCode/runCode archived

// ============================================================
// API ROUTES
// ============================================================

// Gemini AI API (canonical routes — also in api.php; web.php removed to avoid duplicates)

// Smart AI Chatbot (RBAC-enabled, Human-like)
$router->post('/api/ai/chat', 'App\\Http\\Controllers\\SmartAIController@chat');
$router->get('/api/ai/history', 'App\\Http\\Controllers\\SmartAIController@history');
$router->post('/api/ai/feedback', 'App\\Http\\Controllers\\SmartAIController@feedback');
$router->get('/api/ai/stats', 'App\\Http\\Controllers\\SmartAIController@stats');
$router->get('/api/ai/rag-stats', 'App\\Http\\Controllers\\SmartAIController@ragStats');
$router->post('/api/ai/generate-document', 'App\\Http\\Controllers\\SmartAIController@generateDocument');
$router->post('/api/ai/workflow-event', 'App\\Http\\Controllers\\SmartAIController@workflowEvent');
$router->get('/ai-assistant', 'App\\Http\\Controllers\\SmartAIController@assistantPage');

// Notifications API
$router->get('/api/notifications', 'App\\Http\\Controllers\\NotificationController@getNotifications');
$router->post('/api/notifications/mark-read', 'App\\Http\\Controllers\\NotificationController@markAsRead');
$router->get('/api/notifications/unread-count', 'App\\Http\\Controllers\\NotificationController@getUnreadCount');
$router->get('/api/user/notifications/unread-count', 'Front\\UserController@apiUnreadCount');
$router->get('/api/popups', 'App\\Http\\Controllers\\NotificationController@getPopups');
$router->post('/api/popups/dismiss', 'App\\Http\\Controllers\\NotificationController@dismissPopup');
$router->post('/admin/notifications/create', 'App\\Http\\Controllers\\NotificationController@createNotification');
$router->post('/admin/popups/create', 'App\\Http\\Controllers\\NotificationController@createPopup');

// Monitoring
$router->get('/monitoring', 'App\\Http\\Controllers\\MonitoringController@dashboard');
$router->get('/admin/monitoring', 'App\\Http\\Controllers\\MonitoringController@adminMonitoring');

// Virtual Tour Routes
$router->get('/virtual-tour', 'Tech\VirtualTourController@index');
$router->get('/virtual-tour/{id}', 'Tech\VirtualTourController@show');

// Meeting Scheduler Routes  
$router->get('/schedule-meeting', 'Front\ContactController@scheduleMeeting');
$router->post('/schedule-meeting', 'Front\ContactController@handleScheduleMeeting');

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
$router->get('/admin/mlm', 'App\Http\Controllers\Admin\MLMCommissionController@index');
$router->get('/admin/mlm/users', 'App\Http\Controllers\Admin\MLMController@users');
$router->get('/admin/mlm/users/create', 'App\Http\Controllers\Admin\MLMController@createAssociate');
$router->post('/admin/mlm/users/create', 'App\Http\Controllers\Admin\MLMController@createAssociate');
$router->get('/admin/mlm/associates/create', 'App\Http\Controllers\Admin\MLMController@createAssociate');
$router->post('/admin/mlm/associates/create', 'App\Http\Controllers\Admin\MLMController@createAssociate');
$router->get('/admin/mlm/commission', 'App\Http\Controllers\Admin\MLMController@commission');
$router->get('/admin/mlm/network', 'App\Http\Controllers\Admin\MLMController@network');
$router->get('/admin/mlm/tree', 'App\Http\Controllers\Admin\MLMController@tree');
$router->get('/admin/mlm/genealogy', 'App\Http\Controllers\Admin\MLMController@genealogy');
$router->get('/admin/mlm/ranks', 'App\Http\Controllers\Admin\MLMController@ranks');

// MLM Settings & Rank Evaluation
$router->get('/admin/mlm-settings/levels', 'App\Http\Controllers\Admin\MLMSettingsController@levels');
$router->get('/admin/mlm-settings/levels/edit/{id}', 'App\Http\Controllers\Admin\MLMSettingsController@editLevel');
$router->post('/admin/mlm-settings/levels/update/{id}', 'App\Http\Controllers\Admin\MLMSettingsController@updateLevel');
$router->get('/admin/mlm-settings/rules', 'App\Http\Controllers\Admin\MLMSettingsController@rules');
$router->post('/admin/mlm-settings/rules/update/{id}', 'App\Http\Controllers\Admin\MLMSettingsController@updateRule');
$router->get('/admin/mlm-settings/evaluate', 'App\Http\Controllers\Admin\MLMSettingsController@evaluateRanks');
$router->get('/admin/mlm-settings/associate-progress', 'App\Http\Controllers\Admin\MLMSettingsController@associateProgress');

// MLM Plan Editor (CRUD for rank benefits + mlm_settings)
$router->get('/admin/mlm/plan-editor', 'App\Http\Controllers\Admin\MLMCommissionController@planEditor');
$router->post('/admin/mlm/plan-editor/update', 'App\Http\Controllers\Admin\MLMCommissionController@planEditorUpdate');


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

// Password Reset (consolidated into AuthController)
$router->get('/forgot-password', 'Auth\\AuthController@showForgotPassword');
$router->post('/forgot-password', 'Auth\\AuthController@forgotPassword');
$router->get('/reset-password', 'Auth\\AuthController@showResetPassword');
$router->post('/reset-password', 'Auth\\AuthController@resetPassword');
$router->get('/change-password', 'Auth\\AuthController@showChangePassword');
$router->post('/change-password', 'Auth\\AuthController@changePassword');
$router->get('/verify-email', 'Auth\\AuthController@verifyEmail');
$router->post('/verify-email', 'Auth\\AuthController@verifyEmailPost');

// Legacy /customer/* routes removed — customers use /user/dashboard via Front\UserController
// AppCoreService handles any lingering /customer/dashboard URLs with a redirect

// Property Routes (Note: /properties handled by Front\PropertyController@properties)
$router->get('/properties/search', 'App\Http\Controllers\PropertyController@search');
$router->get('/colonies', 'Front\ProjectController@colonies');
$router->get('/ai-chatbot', 'Front\AIController@aiChatbotPage');

$router->get('/terms', 'App\\Http\\Controllers\\Front\\LegalController@terms');
$router->get('/inquiry', 'Front\\ServiceController@inquiry');
$router->post('/inquiry', 'Front\\ServiceController@inquiry');


// Admin Analytics
$router->get('/admin/analytics', 'App\\Http\\Controllers\\Admin\\AnalyticsController@index');
$router->get('/admin/analytics/associate-performance', 'App\\Http\\Controllers\\Admin\\AnalyticsController@associatePerformance');
$router->get('/admin/analytics/sales', 'App\\Http\\Controllers\\Admin\\AnalyticsController@sales');
$router->get('/admin/analytics/property', 'App\\Http\\Controllers\\Admin\\AnalyticsController@property');
$router->get('/admin/analytics/financial', 'App\\Http\\Controllers\\Admin\\AnalyticsController@financial');
$router->get('/admin/analytics/export', 'App\\Http\\Controllers\\Admin\\AnalyticsController@export');

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
$router->post('/service-interest', 'Front\ContactController@serviceInterest');

// ============================================================
// WALLET SYSTEM
// ============================================================

// Wallet Dashboard
$router->get('/wallet', 'App\\Http\\Controllers\\WalletController@index');
$router->get('/wallet/dashboard', 'App\\Http\\Controllers\\WalletController@index');

// Wallet Transactions
$router->get('/wallet/transactions', 'App\\Http\\Controllers\\WalletController@transactions');

// Wallet Transfer to EMI
$router->get('/wallet/transfer-emi', 'App\\Http\\Controllers\\WalletController@transferToEmi');
$router->post('/wallet/transfer-emi/process', 'App\\Http\\Controllers\\WalletController@processEmiTransfer');

// Wallet Withdrawal
$router->get('/wallet/withdrawal', 'App\\Http\\Controllers\\WalletController@withdrawal');
$router->post('/wallet/withdrawal/process', 'App\\Http\\Controllers\\WalletController@processWithdrawal');

// Bank Account Management
$router->get('/wallet/bank-accounts', 'App\\Http\\Controllers\\WalletController@bankAccounts');
$router->post('/wallet/bank-accounts/add', 'App\\Http\\Controllers\\WalletController@addBankAccount');
$router->post('/wallet/bank-accounts/set-primary', 'App\\Http\\Controllers\\WalletController@setPrimaryBank');
$router->post('/wallet/bank-accounts/delete', 'App\\Http\\Controllers\\WalletController@deleteBankAccount');
$router->post('/wallet/bank-accounts/update', 'App\\Http\\Controllers\\WalletController@updateBankAccount');

// Referral Network
$router->get('/wallet/referral-network', 'App\\Http\\Controllers\\WalletController@referralNetwork');

// Wallet Analytics
$router->get('/wallet/analytics', 'App\\Http\\Controllers\\WalletController@analytics');

// Wallet Store
$router->get('/wallet/store', 'App\\Http\\Controllers\\WalletController@store');
$router->post('/wallet/process-purchase', 'App\\Http\\Controllers\\WalletController@processPurchase');

// ============================================================
// ML & AI API ROUTES
// ============================================================

// ML Dashboard API
$router->get('/api/ml/dashboard', 'App\\Http\\Controllers\\MLController@getMLDashboard');

// ML Predictions
$router->get('/api/ml/predict-price', 'App\\Http\\Controllers\\MLController@predictPrice');
$router->get('/api/ml/recommendations', 'App\\Http\\Controllers\\MLController@getRecommendations');

// ML User Behavior
$router->get('/api/ml/analyze-behavior', 'App\\Http\\Controllers\\MLController@analyzeUserBehavior');

// ============================================================
// FRAUD DETECTION API ROUTES
// ============================================================

// Fraud Detection
$router->get('/api/fraud/detect', 'App\\Http\\Controllers\\MLController@detectFraud');
$router->post('/api/fraud/detect', 'App\\Http\\Controllers\\MLController@detectFraud');

// Fraud Dashboard
$router->get('/api/fraud/dashboard', 'App\\Http\\Controllers\\MLController@fraudDashboard');

// Admin Network MLM
$router->get('/admin/network/tree', 'App\\Http\\Controllers\\MLMTreeController@tree');
$router->get('/admin/network/genealogy', 'App\\Http\\Controllers\\Admin\\NetworkController@genealogy');
$router->get('/admin/network/ranks', 'App\\Http\\Controllers\\Admin\\NetworkController@ranks');

// Admin Payouts
$router->get('/admin/payouts', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@payouts');

// Admin Payments
$router->get('/admin/payments', 'App\\Http\\Controllers\\Admin\\PaymentController@index');
$router->get('/admin/payments/show/{id}', 'App\\Http\\Controllers\\Admin\\PaymentController@show');
$router->post('/admin/payments/process/{id}', 'App\\Http\\Controllers\\Admin\\PaymentController@processPayment');
$router->post('/admin/payments/refund/{id}', 'App\\Http\\Controllers\\Admin\\PaymentController@refundPayment');
$router->get('/admin/payments/dashboard-stats', 'App\\Http\\Controllers\\Admin\\PaymentController@dashboardStats');
$router->get('/admin/payments/export', 'App\\Http\\Controllers\\Admin\\PaymentController@export');

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
$router->get('/admin/media/create', 'App\\Http\\Controllers\\Admin\\MediaController@create');
$router->post('/admin/media/store', 'App\\Http\\Controllers\\Admin\\MediaController@store');
$router->get('/admin/media/show/{id}', 'App\\Http\\Controllers\\Admin\\MediaController@show');
$router->get('/admin/media/edit/{id}', 'App\\Http\\Controllers\\Admin\\MediaController@edit');
$router->post('/admin/media/update/{id}', 'App\\Http\\Controllers\\Admin\\MediaController@update');
$router->post('/admin/media/destroy/{id}', 'App\\Http\\Controllers\\Admin\\MediaController@destroy');
$router->get('/admin/media/stats', 'App\\Http\\Controllers\\Admin\\MediaController@getStats');

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
$router->post('/admin/resell-properties/create', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@store');
$router->get('/admin/resell-properties/edit/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@edit');
$router->get('/admin/resell-properties/view/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@details');
$router->get('/admin/resell-properties/images/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@images');
$router->post('/admin/resell-properties/images/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@images');
$router->post('/admin/resell-properties/images/{id}/delete', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@deleteImage');
$router->get('/admin/resell-properties/status/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@status');
$router->get('/admin/resell-properties/commission/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@commission');
$router->post('/admin/resell-properties/update/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@update');
$router->post('/admin/resell-properties/status/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@updateStatus');
$router->post('/admin/resell-properties/delete/{id}', 'App\\Http\\Controllers\\Admin\\ResellPropertiesAdminController@deleteProperty');

// Note: admin_routes.php was removed in Phase 3+4 cleanup (2026-06-05).
// All admin routes are now defined in this file. Legacy admin_routes.php
// only had a single redundant /admin/sales route that pointed to the same
// controller as the one defined above at line 1723, so removing it has
// no functional impact.

// ============================================================
// MISSING ADMIN ROUTES (FIXED)
// ============================================================

// Colonies Management (unique update/destroy patterns)
$router->post('/admin/colonies/{id}/update', 'App\\Http\\Controllers\\Admin\\ColonyController@update');
$router->post('/admin/colonies/{id}/destroy', 'App\\Http\\Controllers\\Admin\\ColonyController@destroy');

// Employee Management
$router->get('/admin/employees', 'App\\Http\\Controllers\\Admin\\HRMController@employeeList');

// Commissions Management
$router->get('/admin/commissions', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@commissionsList');

// Accounts/Financial Management (merged into MoneyWorkflowController)
$router->get('/admin/accounts', 'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@adminAccounts');

// Developer Tools
$router->get('/admin/dev-tools', 'App\\Http\\Controllers\\Admin\\AdminController@devTools');

// Invoices (merged into MoneyWorkflowController)
$router->get('/admin/invoices', 'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@invoices');
$router->get('/admin/invoices/view/{id}', 'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@viewInvoice');
$router->get('/admin/invoices/download/{id}', 'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@downloadInvoice');
$router->post('/admin/invoices/delete/{id}', 'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@deleteInvoice');
$router->get('/admin/roles', 'App\\Http\\Controllers\\RoleBasedDashboardController@roles');
$router->get('/admin/hrm/users', 'App\\Http\\Controllers\\Admin\\HRMController@users');

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
$router->post('/admin/jobs/manage/{id}/delete', 'App\\Http\\Controllers\\Admin\\JobsAdminController@delete');

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
$router->get('/admin/sms/logs', function () {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id'])) { echo json_encode([]); exit; }
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $sql = "SELECT * FROM sms_queue WHERE 1=1";
        $params = [];
        if ($type) { $sql .= " AND type = ?"; $params[] = $type; }
        if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
        $sql .= " ORDER BY created_at DESC LIMIT 100";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
    } catch (\Exception $e) { echo json_encode([]); }
    exit;
});

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
$router->post('/admin/backups/delete', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@deleteBackup');
$router->post('/admin/backups/restore', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@restoreBackup');
$router->get('/admin/backups/download/{filename}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@downloadBackup');
$router->get('/admin/emails', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@emailQueue');
$router->post('/admin/emails/process', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@processEmailQueue');
$router->post('/admin/emails/cancel', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@cancelEmail');
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
$router->get('/admin/faqs', 'App\\Http\\Controllers\\Admin\\FaqController@index');
$router->get('/admin/faqs/create', 'App\\Http\\Controllers\\Admin\\FaqController@create');
$router->post('/admin/faqs/store', 'App\\Http\\Controllers\\Admin\\FaqController@store');
$router->get('/admin/faqs/{id}', 'App\\Http\\Controllers\\Admin\\FaqController@show');
$router->get('/admin/faqs/{id}/edit', 'App\\Http\\Controllers\\Admin\\FaqController@edit');
$router->post('/admin/faqs/{id}/update', 'App\\Http\\Controllers\\Admin\\FaqController@update');
$router->get('/admin/faqs/{id}/delete', 'App\\Http\\Controllers\\Admin\\FaqController@delete');

// Admin Settings Company
$router->get('/admin/settings/company', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');

// Admin Cache Management
$router->get('/admin/cache',                'App\\Http\\Controllers\\Admin\\CacheAdminController@index');
$router->get('/admin/cache/stats',          'App\\Http\\Controllers\\Admin\\CacheAdminController@stats');
$router->post('/admin/cache/flush',         'App\\Http\\Controllers\\Admin\\CacheAdminController@flush');
$router->post('/admin/cache/redis/flush',   'App\\Http\\Controllers\\Admin\\CacheAdminController@flushRedis');
$router->post('/admin/cache/test',          'App\\Http\\Controllers\\Admin\\CacheAdminController@test');
$router->post('/admin/cache/hotpath/flush', 'App\\Http\\Controllers\\Admin\\CacheAdminController@flushHotpath');
$router->get('/admin/cache/hotpath/stats',  'App\\Http\\Controllers\\Admin\\CacheAdminController@hotpathStats');

// Admin Security Test Suite
$router->get('/admin/security-test',        'App\\Http\\Controllers\\Admin\\SecurityTestController@index');
$router->post('/admin/security-test/run',   'App\\Http\\Controllers\\Admin\\SecurityTestController@runTests');
$router->get('/admin/security-test/report', 'App\\Http\\Controllers\\Admin\\SecurityTestController@report');

// Admin Gateway Manager (Twilio + future gateways)
$router->get('/admin/gateways',                       'App\\Http\\Controllers\\Admin\\GatewayTestController@index');
$router->post('/admin/gateways/test-twilio',          'App\\Http\\Controllers\\Admin\\GatewayTestController@testTwilio');
$router->post('/admin/gateways/test-whatsapp',        'App\\Http\\Controllers\\Admin\\GatewayTestController@testWhatsApp');
$router->get('/admin/gateways/logs/{gateway}',         'App\\Http\\Controllers\\Admin\\GatewayTestController@logs');

// Admin A/B Testing Framework
$router->get('/admin/experiments',                  'App\\Http\\Controllers\\Admin\\ExperimentController@index');
$router->get('/admin/experiments/create',           'App\\Http\\Controllers\\Admin\\ExperimentController@create');
$router->post('/admin/experiments/store',           'App\\Http\\Controllers\\Admin\\ExperimentController@store');
$router->get('/admin/experiments/{id}',             'App\\Http\\Controllers\\Admin\\ExperimentController@show');
$router->get('/admin/experiments/{id}/results',     'App\\Http\\Controllers\\Admin\\ExperimentController@results');
$router->post('/admin/experiments/{id}/set-winner', 'App\\Http\\Controllers\\Admin\\ExperimentController@setWinner');
$router->post('/admin/experiments/{id}/end',        'App\\Http\\Controllers\\Admin\\ExperimentController@end');
$router->get('/admin/experiments/{id}/export',      'App\\Http\\Controllers\\Admin\\ExperimentController@exportCsv');
$router->post('/admin/experiments/{id}/delete',     'App\\Http\\Controllers\\Admin\\ExperimentController@delete');
$router->post('/admin/experiments/seed-defaults',   'App\\Http\\Controllers\\Admin\\ExperimentController@seedDefaults');
$router->get('/api/ab/variant/{name}',              'App\\Http\\Controllers\\Admin\\ExperimentController@getVariant');
$router->post('/api/ab/track',                      'App\\Http\\Controllers\\Admin\\ExperimentController@track');

// Admin Service Enquiries (alias for services)
$router->get('/admin/services/enquiry', 'App\\Http\\Controllers\\Admin\\InquiryController@index');

// Admin Activity Log
$router->get('/admin/activity-log', 'App\\Http\\Controllers\\Admin\\ActivityLogController@index');

// Admin Vendor Management
$router->get('/admin/vendors', 'App\\Http\\Controllers\\Admin\\VendorController@index');
$router->get('/admin/vendors/create', 'App\\Http\\Controllers\\Admin\\VendorController@create');
$router->post('/admin/vendors/store', 'App\\Http\\Controllers\\Admin\\VendorController@store');
$router->get('/admin/vendors/show/{id}', 'App\\Http\\Controllers\\Admin\\VendorController@show');
$router->get('/admin/vendors/edit/{id}', 'App\\Http\\Controllers\\Admin\\VendorController@edit');
$router->post('/admin/vendors/update/{id}', 'App\\Http\\Controllers\\Admin\\VendorController@update');
$router->post('/admin/vendors/delete/{id}', 'App\\Http\\Controllers\\Admin\\VendorController@delete');
$router->get('/admin/vendors/contracts/{id}', 'App\\Http\\Controllers\\Admin\\VendorController@contracts');

// Admin Settings Sub-pages
$router->get('/admin/settings/payment', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');
$router->get('/admin/settings/email', 'App\\Http\\Controllers\\Admin\\SiteSettingsController@index');

// ============================================================
// ADMIN COMMUNICATION (Email & SMS Queue)
// ============================================================

$router->get('/admin/communication/queue', 'App\\Http\\Controllers\\Admin\\CommunicationController@queue');
$router->post('/admin/communication/process-queue', 'App\\Http\\Controllers\\Admin\\CommunicationController@processQueue');
$router->get('/admin/communication/test-email', 'App\\Http\\Controllers\\Admin\\CommunicationController@testEmail');
$router->post('/admin/communication/test-email', 'App\\Http\\Controllers\\Admin\\CommunicationController@sendTestEmail');
$router->get('/admin/communication/test-sms', 'App\\Http\\Controllers\\Admin\\CommunicationController@testSms');
$router->post('/admin/communication/test-sms', 'App\\Http\\Controllers\\Admin\\CommunicationController@sendTestSms');

// ============================================================
// ============================================================
// CUSTOM FEATURES (Real CustomFeaturesController + CustomFeaturesService)
// Uses real CustomFeaturesService for Neighborhood Analytics and Investment Calculator
// ============================================================
$router->get('/admin/custom-features', 'App\\Http\\Controllers\\CustomFeaturesController@dashboard');
$router->get('/admin/custom-features/stats', 'App\\Http\\Controllers\\CustomFeaturesController@statistics');
$router->get('/admin/custom-features/neighborhood', 'App\\Http\\Controllers\\CustomFeaturesController@neighborhoodPage');
$router->get('/admin/custom-features/neighborhood/{propertyId}', 'App\\Http\\Controllers\\CustomFeaturesController@neighborhoodAnalytics');
$router->get('/admin/custom-features/investment-calculator', 'App\\Http\\Controllers\\CustomFeaturesController@investmentPage');
$router->post('/admin/custom-features/investment-calculate', 'App\\Http\\Controllers\\CustomFeaturesController@calculateInvestment');

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

$router->get('/admin/agent-dashboard', 'App\\Http\\Controllers\\Admin\\AgentDashboardController@index');
$router->get('/admin/agent-dashboard/performance', 'App\\Http\\Controllers\\Admin\\AgentDashboardController@getPerformanceData');
$router->get('/admin/agent-dashboard/network', 'App\\Http\\Controllers\\Admin\\AgentDashboardController@getNetworkTree');

// ============================================================
// MODULE 1: LAND ACQUISITION + PLOT INVENTORY
// ============================================================

$router->get('/admin/land-inventory/leads', 'App\\Http\\Controllers\\Admin\\LandInventoryController@leads');
$router->get('/admin/land-inventory/leads/new', 'App\\Http\\Controllers\\Admin\\LandInventoryController@leadForm');
$router->post('/admin/land-inventory/leads/store', 'App\\Http\\Controllers\\Admin\\LandInventoryController@leadStore');
$router->get('/admin/land-inventory/leads/{id}', 'App\\Http\\Controllers\\Admin\\LandInventoryController@leadDetail');
$router->get('/admin/land-inventory/leads/{id}/edit', 'App\\Http\\Controllers\\Admin\\LandInventoryController@leadForm');
$router->post('/admin/land-inventory/leads/{id}/update', 'App\\Http\\Controllers\\Admin\\LandInventoryController@leadUpdate');
$router->post('/admin/land-inventory/leads/{id}/advance', 'App\\Http\\Controllers\\Admin\\LandInventoryController@leadAdvance');

$router->get('/admin/land-inventory/leads/{id}/visits', 'App\\Http\\Controllers\\Admin\\LandInventoryController@visits');
$router->post('/admin/land-inventory/leads/{id}/visits/store', 'App\\Http\\Controllers\\Admin\\LandInventoryController@visitStore');

$router->get('/admin/land-inventory/leads/{id}/documents', 'App\\Http\\Controllers\\Admin\\LandInventoryController@documents');
$router->post('/admin/land-inventory/leads/{id}/documents/upload', 'App\\Http\\Controllers\\Admin\\LandInventoryController@documentUpload');

$router->get('/admin/land-inventory/leads/{id}/opinions', 'App\\Http\\Controllers\\Admin\\LandInventoryController@opinions');
$router->post('/admin/land-inventory/leads/{id}/opinions/store', 'App\\Http\\Controllers\\Admin\\LandInventoryController@opinionStore');

$router->post('/admin/land-inventory/leads/{id}/register', 'App\\Http\\Controllers\\Admin\\LandInventoryController@registerSubmit');

$router->get('/admin/land-inventory/acquisitions', 'App\\Http\\Controllers\\Admin\\LandInventoryController@acquisitions');
$router->get('/admin/land-inventory/acquisitions/{id}', 'App\\Http\\Controllers\\Admin\\LandInventoryController@acquisitionDetail');
$router->get('/admin/land-inventory/acquisitions/{id}/register', 'App\\Http\\Controllers\\Admin\\LandInventoryController@registerForm');

$router->get('/admin/land-inventory/acquisitions/{id}/payments', 'App\\Http\\Controllers\\Admin\\LandInventoryController@payments');
$router->get('/admin/land-inventory/acquisitions/{id}/payments/new', 'App\\Http\\Controllers\\Admin\\LandInventoryController@paymentForm');
$router->post('/admin/land-inventory/acquisitions/{id}/payments/store', 'App\\Http\\Controllers\\Admin\\LandInventoryController@paymentStore');
$router->get('/admin/land-inventory/acquisitions/{id}/payments/edit/{pid}', 'App\\Http\\Controllers\\Admin\\LandInventoryController@paymentForm');
$router->post('/admin/land-inventory/acquisitions/{id}/payments/update/{pid}', 'App\\Http\\Controllers\\Admin\\LandInventoryController@paymentUpdate');

$router->get('/admin/land-inventory/colonies/{colonyId}/costs', 'App\\Http\\Controllers\\Admin\\LandInventoryController@developmentCosts');
$router->get('/admin/land-inventory/colonies/{colonyId}/costs/new', 'App\\Http\\Controllers\\Admin\\LandInventoryController@developmentCostForm');
$router->post('/admin/land-inventory/colonies/{colonyId}/costs/store', 'App\\Http\\Controllers\\Admin\\LandInventoryController@developmentCostStore');

$router->get('/admin/land-inventory/colonies/{colonyId}/layouts', 'App\\Http\\Controllers\\Admin\\LandInventoryController@layouts');
$router->get('/admin/land-inventory/colonies/{colonyId}/layouts/create', 'App\\Http\\Controllers\\Admin\\LandInventoryController@layoutForm');
$router->post('/admin/land-inventory/colonies/{colonyId}/layouts/store', 'App\\Http\\Controllers\\Admin\\LandInventoryController@layoutStore');

$router->get('/admin/land-inventory/brokers', 'App\\Http\\Controllers\\Admin\\LandInventoryController@brokers');
$router->post('/admin/land-inventory/brokers/store', 'App\\Http\\Controllers\\Admin\\LandInventoryController@brokerStore');

// ============================================================
// COLONY DEVELOPMENT PIPELINE
// ============================================================
$router->get('/admin/colony-pipeline',                                        'App\\Http\\Controllers\\Admin\\ColonyPipelineController@dashboard');
$router->get('/admin/colony-pipeline/{id}',                                   'App\\Http\\Controllers\\Admin\\ColonyPipelineController@colonyDetail');
$router->get('/admin/colony-pipeline/{id}/layout',                            'App\\Http\\Controllers\\Admin\\ColonyPipelineController@layoutForm');
$router->post('/admin/colony-pipeline/{id}/layout/generate',                  'App\\Http\\Controllers\\Admin\\ColonyPipelineController@generatePlots');
$router->post('/admin/colony-pipeline/{id}/layout/preview',                   'App\\Http\\Controllers\\Admin\\ColonyPipelineController@previewPlots');
$router->post('/admin/colony-pipeline/{id}/layout/delete',                    'App\\Http\\Controllers\\Admin\\ColonyPipelineController@deletePlots');
$router->post('/admin/colony-pipeline/{id}/layout/save',                      'App\\Http\\Controllers\\Admin\\ColonyPipelineController@saveLayout');
$router->get('/admin/colony-pipeline/{id}/pricing',                           'App\\Http\\Controllers\\Admin\\ColonyPipelineController@pricingDashboard');
$router->post('/admin/colony-pipeline/{id}/pricing/calculate',                'App\\Http\\Controllers\\Admin\\ColonyPipelineController@calculatePricing');
$router->post('/admin/colony-pipeline/{id}/pricing/apply',                    'App\\Http\\Controllers\\Admin\\ColonyPipelineController@applyPricing');
$router->get('/admin/colony-pipeline/{id}/costs',                             'App\\Http\\Controllers\\Admin\\ColonyPipelineController@developmentCosts');
$router->post('/admin/colony-pipeline/{id}/costs/store',                      'App\\Http\\Controllers\\Admin\\ColonyPipelineController@storeCost');
$router->get('/admin/colony-pipeline/{id}/plots',                             'App\\Http\\Controllers\\Admin\\ColonyPipelineController@plotList');
$router->get('/admin/colony-pipeline/{id}/plots/stats',                       'App\\Http\\Controllers\\Admin\\ColonyPipelineController@plotStats');
$router->get('/admin/colony-pipeline/{id}/map',                              'App\\Http\\Controllers\\Admin\\ColonyPipelineController@plotMap');
$router->get('/admin/colony-pipeline/{id}/map/geojson',                      'App\\Http\\Controllers\\Admin\\ColonyPipelineController@plotMapGeoJson');

// ============================================================
// LEGAL COLONY DEVELOPMENT PIPELINE (7-Phase)
// ============================================================
$router->get('/admin/legal-colony-pipeline',                                  'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@index');
$router->get('/admin/legal-colony-pipeline/detail/{id}',                      'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@detail');
$router->get('/admin/legal-colony-pipeline/start-acquisition',                'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@startAcquisition');
$router->post('/admin/legal-colony-pipeline/store-acquisition',               'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@storeAcquisition');
$router->post('/admin/legal-colony-pipeline/update-acquisition-status',       'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@updateAcquisitionStatus');
$router->get('/admin/legal-colony-pipeline/master-plan/{id}',                 'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@createMasterPlan');
$router->post('/admin/legal-colony-pipeline/store-master-plan',               'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@storeMasterPlan');
$router->get('/admin/legal-colony-pipeline/plot-cutting/{id}',                'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@plotCutting');
$router->post('/admin/legal-colony-pipeline/generate-plots',                  'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@generatePlots');
$router->get('/admin/legal-colony-pipeline/rera/{id}',                        'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@reraRegistration');
$router->post('/admin/legal-colony-pipeline/store-rera',                      'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@storeRera');
$router->get('/admin/legal-colony-pipeline/development/{id}',                 'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@recordCost');
$router->post('/admin/legal-colony-pipeline/store-cost',                      'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@storeCost');
$router->get('/admin/legal-colony-pipeline/pricing/{id}',                     'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@applyPricing');
$router->post('/admin/legal-colony-pipeline/calculate-pricing',               'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@calculatePricing');
$router->get('/admin/legal-colony-pipeline/readiness/{id}',                   'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@readiness');
$router->post('/admin/legal-colony-pipeline/compliance-check',                'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@complianceCheck');

// ── Pipeline Workflow Automation ──────────────────────────────
$router->post('/admin/legal-colony-pipeline/advance-stage',                 'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@advanceStage');
$router->post('/admin/legal-colony-pipeline/stage-readiness',               'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@stageReadiness');
$router->post('/admin/legal-colony-pipeline/stage-history',                 'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@stageHistory');
$router->post('/admin/legal-colony-pipeline/auto-advance',                  'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@autoAdvance');

// ── Colony Analytics ──────────────────────────────────────────
$router->get('/admin/legal-colony-pipeline/analytics/{id}',                 'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@analytics');
$router->get('/admin/legal-colony-pipeline/analytics-all',                  'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@analyticsComparison');

// ── RERA Milestone Tracker ────────────────────────────────────
$router->get('/admin/legal-colony-pipeline/milestones/{id}',                'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@milestones');
$router->post('/admin/legal-colony-pipeline/update-milestone',              'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@updateMilestone');

// ── Colony Health Dashboard ───────────────────────────────────
$router->get('/admin/legal-colony-pipeline/health',                         'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@healthOverview');
$router->get('/admin/legal-colony-pipeline/health/api',                     'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@healthApi');
$router->get('/admin/legal-colony-pipeline/health/alerts',                  'App\\Http\\Controllers\\Admin\\LegalColonyPipelineController@healthAlerts');

// ============================================================
// COLONY FEASIBILITY & PRICING ENGINE
// ============================================================
$router->get('/admin/colony-feasibility',                                     'App\\Http\\Controllers\\Admin\\ColonyFeasibilityController@index');
$router->get('/admin/colony-feasibility/{id}',                                'App\\Http\\Controllers\\Admin\\ColonyFeasibilityController@calculator');
$router->post('/admin/colony-feasibility/{id}/calculate',                     'App\\Http\\Controllers\\Admin\\ColonyFeasibilityController@calculate');
$router->get('/admin/colony-feasibility/{id}/history',                        'App\\Http\\Controllers\\Admin\\ColonyFeasibilityController@history');
$router->get('/admin/colony-feasibility/{id}/preview',                        'App\\Http\\Controllers\\Admin\\ColonyFeasibilityController@preview');

// ============================================================
// MODULE 2: CUSTOMER SALES + ALLOTMENT + REGISTRY
// ============================================================
$router->get('/admin/sales',                                       'App\\Http\\Controllers\\Admin\\BookingLifecycleController@index');
$router->get('/admin/sales/dashboard',                            'App\\Http\\Controllers\\Admin\\BookingLifecycleController@index');
$router->get('/admin/sales/bookings',                             'App\\Http\\Controllers\\Admin\\BookingLifecycleController@bookings');
$router->get('/admin/sales/bookings/new',                         'App\\Http\\Controllers\\Admin\\BookingLifecycleController@createBookingForm');
$router->post('/admin/sales/bookings/store',                      'App\\Http\\Controllers\\Admin\\BookingLifecycleController@createBookingStore');
$router->get('/admin/sales/bookings/{id}',                        'App\\Http\\Controllers\\Admin\\BookingLifecycleController@bookingDetail');
$router->get('/admin/sales/bookings/{id}/edit',                   'App\\Http\\Controllers\\Admin\\BookingLifecycleController@editBooking');
$router->post('/admin/sales/bookings/{id}/update',                'App\\Http\\Controllers\\Admin\\BookingLifecycleController@updateBooking');
$router->get('/admin/sales/bookings/{id}/schedule',               'App\\Http\\Controllers\\Admin\\BookingLifecycleController@paymentSchedule');
$router->post('/admin/sales/bookings/{id}/schedule/regenerate',   'App\\Http\\Controllers\\Admin\\BookingLifecycleController@regenerateSchedule');
$router->post('/admin/sales/bookings/{id}/cancel',                'App\\Http\\Controllers\\Admin\\BookingLifecycleController@cancelBookingStore');
$router->get('/admin/sales/bookings/{id}/cancel',                 'App\\Http\\Controllers\\Admin\\BookingLifecycleController@cancelBookingForm');
$router->post('/admin/sales/bookings/{id}/transfer',              'App\\Http\\Controllers\\Admin\\BookingLifecycleController@transferBookingStore');
$router->get('/admin/sales/bookings/{id}/transfer',               'App\\Http\\Controllers\\Admin\\BookingLifecycleController@transferBookingForm');
$router->get('/admin/sales/installments/{installmentId}/pay',      'App\\Http\\Controllers\\Admin\\BookingLifecycleController@recordPaymentForm');
$router->post('/admin/sales/installments/{installmentId}/pay',     'App\\Http\\Controllers\\Admin\\BookingLifecycleController@recordPaymentStore');
$router->get('/admin/sales/installments/{installmentId}/demand-letter', 'App\\Http\\Controllers\\Admin\\BookingLifecycleController@demandLetter');
$router->get('/admin/sales/approvals',                             'App\\Http\\Controllers\\Admin\\BookingLifecycleController@approvalList');
$router->post('/admin/sales/approvals/{id}/approve',              'App\\Http\\Controllers\\Admin\\BookingLifecycleController@approveBooking');
$router->post('/admin/sales/approvals/{id}/reject',               'App\\Http\\Controllers\\Admin\\BookingLifecycleController@rejectBooking');
$router->get('/admin/sales/commissions',                          'App\\Http\\Controllers\\Admin\\BookingLifecycleController@commissions');
$router->get('/admin/sales/refunds',                              'App\\Http\\Controllers\\Admin\\BookingLifecycleController@refunds');
$router->get('/admin/sales/rera',                                 'App\\Http\\Controllers\\Admin\\BookingLifecycleController@reraCompliance');
$router->post('/admin/sales/rera/store',                          'App\\Http\\Controllers\\Admin\\BookingLifecycleController@reraComplianceStore');

$router->get('/admin/sales/bookings/{id}/registry-check',       'App\\Http\\Controllers\\Admin\\BookingLifecycleController@registryCheck');
$router->post('/admin/sales/bookings/{id}/generate-noc',        'App\\Http\\Controllers\\Admin\\BookingLifecycleController@generateNoc');

// ============================================================
// MODULE 3: MONEY WORKFLOW + ACCOUNTING
// URL prefix: /admin/finance/*
// All actions delegate to App\Http\Controllers\Admin\MoneyWorkflowController.
// ============================================================

$router->get('/admin/finance',                                                    'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@dashboard');
$router->get('/admin/finance/dashboard',                                          'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@dashboard');

// Bank accounts
$router->get('/admin/finance/bank-accounts',                                      'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@bankAccounts');
$router->get('/admin/finance/bank-account-form',                                  'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@bankAccountForm');
$router->get('/admin/finance/bank-account-form/{id}',                             'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@bankAccountForm');
$router->post('/admin/finance/bank-account-store',                                'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@bankAccountStore');

// Daily cash book
$router->get('/admin/finance/cash-book',                                          'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@cashBook');
$router->get('/admin/finance/transaction-form',                                   'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@transactionForm');
$router->post('/admin/finance/transaction-store',                                 'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@transactionStore');

// Petty cash
$router->get('/admin/finance/petty-cash',                                         'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@pettyCash');
$router->post('/admin/finance/petty-topup',                                       'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@pettyTopup');
$router->post('/admin/finance/petty-expense',                                     'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@pettyExpense');

// Cash Flow Forecast
$router->get('/admin/finance/cash-flow',                                        'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@cashFlow');

// Cheques
$router->get('/admin/finance/cheques',                                            'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@cheques');
$router->get('/admin/finance/cheque-issue',                                       'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@chequeIssue');
$router->post('/admin/finance/cheque-store',                                      'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@chequeStore');
$router->post('/admin/finance/cheque-status',                                     'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@chequeStatus');
$router->get('/admin/finance/cheques/{id}/print',                                 'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@chequePrint');

// Bank reconciliation
$router->get('/admin/finance/reconciliation',                                     'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@reconciliation');
$router->get('/admin/finance/reconciliation-match',                               'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@reconciliationMatch');
$router->get('/admin/finance/reconciliation-match/{id}',                          'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@reconciliationMatch');
$router->post('/admin/finance/reconciliation-create',                             'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@reconciliationCreate');
$router->post('/admin/finance/reconciliation-item-match',                         'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@reconciliationItemMatch');
$router->post('/admin/finance/reconciliation-complete',                           'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@reconciliationComplete');

// TDS
$router->get('/admin/finance/tds',                                                'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@tds');
$router->get('/admin/finance/tds-record',                                         'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@tdsRecord');
$router->post('/admin/finance/tds-store',                                         'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@tdsStore');
$router->get('/admin/finance/tds-certificates',                                   'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@tdsCertificates');
$router->post('/admin/finance/tds-certificate-store',                             'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@tdsCertificateStore');

// GST
$router->get('/admin/finance/gst',                                                'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@gst');
$router->get('/admin/finance/gst-summary',                                        'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@gstRecord');
$router->post('/admin/finance/gst-store',                                         'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@gstStore');

// Expenses
$router->get('/admin/finance/expenses',                                           'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@expenses');
$router->get('/admin/finance/expense-form',                                       'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@expenseForm');
$router->get('/admin/finance/expense-form/{id}',                                  'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@expenseForm');
$router->post('/admin/finance/expense-store',                                     'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@expenseStore');
$router->post('/admin/finance/expense-approve',                                   'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@expenseApprove');
$router->post('/admin/finance/expense-reject',                                    'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@expenseReject');

// Vendors
$router->get('/admin/finance/vendors',                                            'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@vendors');
$router->get('/admin/finance/vendor-payment',                                     'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@vendorPayment');
$router->post('/admin/finance/vendor-payment-store',                              'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@vendorPaymentStore');

// Exchange Rate API (auto-fetch for multi-currency)
$router->get('/admin/finance/exchange-rate',                                       'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@getExchangeRate');
$router->get('/admin/finance/all-rates',                                           'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@getAllRates');

// Cash flow forecast
$router->get('/admin/finance/forecast',                                           'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@forecast');

// Demand letter templates
$router->get('/admin/finance/templates',                                          'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@templates');
$router->get('/admin/finance/template-form',                                      'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@templateForm');
$router->get('/admin/finance/template-form/{id}',                                 'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@templateForm');
$router->post('/admin/finance/template-store',                                    'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@templateStore');
$router->post('/admin/finance/template-delete',                                   'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@templateDelete');

// Voucher audit log
$router->get('/admin/finance/voucher-log',                                        'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@voucherLog');

// EMI Penalty Engine
$router->get('/admin/finance/penalties',                                          'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@penaltySummary');
$router->post('/admin/finance/penalties/apply',                                   'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@applyPenalties');

// EMI Auto-Payment (Razorpay mandates)
$router->get('/admin/finance/emi-auto-pay',                                        'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@emiAutoPayDashboard');
$router->post('/admin/finance/emi-auto-pay/run',                                   'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@runAutoPaymentCron');

// On-Field Cash Collection & Reconciliation
$router->get('/admin/finance/collections',                                        'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@collections');
$router->get('/admin/finance/collection-form',                                    'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@collectionForm');
$router->post('/admin/finance/collections/store',                                 'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@collectionStore');
$router->post('/admin/finance/collections/verify',                                'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@collectionVerify');
$router->post('/admin/finance/collections/reject',                                'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@collectionReject');
$router->get('/admin/finance/reconciliation-collections',                         'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@reconciliationCollections');
$router->post('/admin/finance/reconciliation-collections/start',                  'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@reconciliationCollectionsStart');
$router->post('/admin/finance/reconciliation-collections/close',                  'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@reconciliationCollectionsClose');

// PDF Downloads (Agreement, Demand Letter, Allotment, Refund Voucher)
$router->get('/admin/finance/agreement/{id}',             'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@downloadAgreement');
$router->get('/admin/finance/demand-letter/{id}',         'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@downloadDemandLetter');
$router->get('/admin/finance/allotment/{id}',             'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@downloadAllotmentLetter');
$router->get('/admin/finance/refund-voucher/{id}',        'App\\Http\\Controllers\\Admin\\MoneyWorkflowController@downloadRefundVoucher');

// ============================================================
// ADMIN E-FILING (TDS/GST)
// ============================================================

$router->get('/admin/efiling',                                                        'App\\Http\\Controllers\\Admin\\EFilingController@index');
$router->get('/admin/efiling/tds',                                                    'App\\Http\\Controllers\\Admin\\EFilingController@tdsRegister');
$router->post('/admin/efiling/tds/generate',                                          'App\\Http\\Controllers\\Admin\\EFilingController@generateTdsReturn');
$router->get('/admin/efiling/tds/challans',                                           'App\\Http\\Controllers\\Admin\\EFilingController@tdsChallans');
$router->get('/admin/efiling/tds/challans/create',                                    'App\\Http\\Controllers\\Admin\\EFilingController@createChallan');
$router->post('/admin/efiling/tds/challans/create',                                   'App\\Http\\Controllers\\Admin\\EFilingController@storeChallan');
$router->get('/admin/efiling/tds/challans/{id}',                                      'App\\Http\\Controllers\\Admin\\EFilingController@challanDetail');
$router->get('/admin/efiling/tds/certificates',                                       'App\\Http\\Controllers\\Admin\\EFilingController@tdsCertificates');
$router->get('/admin/efiling/gst',                                                    'App\\Http\\Controllers\\Admin\\EFilingController@gstFiling');
$router->post('/admin/efiling/gst/gstr1',                                             'App\\Http\\Controllers\\Admin\\EFilingController@generateGstr1');
$router->post('/admin/efiling/gst/gstr3b',                                            'App\\Http\\Controllers\\Admin\\EFilingController@generateGstr3b');
$router->get('/admin/efiling/calendar',                                               'App\\Http\\Controllers\\Admin\\EFilingController@calendar');
$router->get('/admin/efiling/submissions',                                            'App\\Http\\Controllers\\Admin\\EFilingController@submissions');
$router->get('/admin/efiling/submissions/{id}',                                       'App\\Http\\Controllers\\Admin\\EFilingController@showSubmission');
$router->post('/admin/efiling/submissions/{id}/update-status',                        'App\\Http\\Controllers\\Admin\\EFilingController@updateSubmissionStatus');

$router->get('/admin/efiling/tds/certificate/{id}/download',                          'App\\Http\\Controllers\\Admin\\EFilingController@downloadForm16A');
$router->get('/admin/efiling/gst/export/gstr1',                                       'App\\Http\\Controllers\\Admin\\EFilingController@exportGstr1');
$router->get('/admin/efiling/gst/export/gstr3b',                                      'App\\Http\\Controllers\\Admin\\EFilingController@exportGstr3b');

// E-Filing Portal Integration (GSTN + TIN)
$router->get('/admin/efiling/gstn-portal',                                           'App\\Http\\Controllers\\Admin\\EFilingController@gstnPortal');
$router->post('/admin/efiling/gstn/submit',                                          'App\\Http\\Controllers\\Admin\\EFilingController@submitGstn');
$router->get('/admin/efiling/gstn/status/{gstin}',                                   'App\\Http\\Controllers\\Admin\\EFilingController@gstnStatus');
$router->get('/admin/efiling/tin-portal',                                            'App\\Http\\Controllers\\Admin\\EFilingController@tinPortal');
$router->post('/admin/efiling/tin/submit',                                           'App\\Http\\Controllers\\Admin\\EFilingController@submitTin');
$router->get('/admin/efiling/tin/status/{token}',                                    'App\\Http\\Controllers\\Admin\\EFilingController@tinStatus');

// ============================================================
// ADMIN LAND MANAGEMENT
// ============================================================

$router->get('/admin/land', 'App\\Http\\Controllers\\Admin\\LandController@index');
$router->get('/admin/land/create', 'App\\Http\\Controllers\\Admin\\LandController@create');
$router->post('/admin/land/store', 'App\\Http\\Controllers\\Admin\\LandController@store');
$router->get('/admin/land/acquisitions', 'App\\Http\\Controllers\\Admin\\LandController@acquisitions');
$router->get('/admin/land/records', 'App\\Http\\Controllers\\Admin\\LandController@records');
$router->get('/admin/land/stats', 'App\\Http\\Controllers\\Admin\\LandController@getStats');
$router->get('/admin/land/{id}', 'App\\Http\\Controllers\\Admin\\LandController@show');
$router->get('/admin/land/{id}/edit', 'App\\Http\\Controllers\\Admin\\LandController@edit');
$router->post('/admin/land/{id}/update', 'App\\Http\\Controllers\\Admin\\LandController@update');
$router->post('/admin/land/{id}/destroy', 'App\\Http\\Controllers\\Admin\\LandController@destroy');

// ============================================================
// ADMIN LOYALTY PROGRAM
// ============================================================

$router->get('/admin/loyalty', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@index');
$router->get('/admin/loyalty/members', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@members');
$router->get('/admin/loyalty/members/{id}', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@memberDetails');
$router->post('/admin/loyalty/points/add', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@addPoints');
$router->get('/admin/loyalty/rewards', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@rewards');
$router->get('/admin/loyalty/rewards/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@editReward');
$router->get('/admin/loyalty/rewards/create', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@editReward');
$router->post('/admin/loyalty/rewards/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@editReward');
$router->post('/admin/loyalty/rewards/create', 'App\\Http\\Controllers\\Admin\\AdminLoyaltyController@editReward');
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
// REMOVED: Duplicate dashboard redirect routes (were overriding working
// controller routes at lines 1114-1128). All dashboard routing now goes
// through RoleBasedDashboardController with real DB data.
// ============================================================

// ============================================================
// ORPHANED PUBLIC PAGE ROUTES (added 2026-05-15)
// ============================================================

// Content pages via PageController
$router->get('/bank', 'Front\\FinancialController@bank');
$router->get('/suyoday-colony', 'Front\\ProjectController@suyodayColonyPage');

// Legal section
$router->get('/legal', 'Front\\LegalController@index');
$router->get('/legal/privacy', 'Front\\LegalController@privacy');
$router->get('/legal/terms', 'Front\\LegalController@terms');
$router->get('/legal/disclaimer', 'Front\\LegalController@disclaimer');
$router->get('/legal/cancellation-policy', 'Front\\LegalController@cancellationPolicy');
$router->get('/legal/refund-policy', 'Front\\LegalController@refundPolicy');
$router->get('/legal/insurance', 'Front\\LegalController@insurance');
$router->get('/legal/nach-mandate', 'Front\\LegalController@nachMandate');
$router->get('/legal/agreements', 'Front\\LegalController@agreements');
$router->get('/legal/title-protection', 'Front\\LegalController@titleProtection');
$router->get('/legal/property-verification', 'Front\\LegalController@propertyVerification');

// Standalone full-HTML pages
$router->get('/analytics', 'Front\\PageController@analytics');
$router->get('/calc', 'Front\\ToolController@calc');

// WebSocket Real-time Notification Test Page
$router->get('/websocket-test', function () {
    $file = __DIR__ . '/../app/views/pages/websocket_test.php';
    if (file_exists($file)) {
        include $file;
    } else {
        http_response_code(404);
        echo 'WebSocket test page not found';
    }
});

// ============================================================
// LOCATION PROJECT PAGES (added 2026-05-15)
// ============================================================
// NOTE: gorakhpur-raghunath-nagri, gorakhpur-suryoday-colony, and
// varanasi-ganga-nagri are partial views (no <html>/<head>/<body>).
// They render as content fragments. For full layout, route through
// a controller using $this->render('locations/xxx') instead.

$router->get('/locations/{slug}', 'Front\\ProjectController@location');

// ====== Project Management ======
$router->get('/admin/projects/manage', 'App\Http\Controllers\Admin\ProjectController@index');
$router->get('/admin/projects/manage/create', 'App\Http\Controllers\Admin\ProjectController@create');
$router->post('/admin/projects/manage/store', 'App\Http\Controllers\Admin\ProjectController@store');
$router->get('/admin/projects/manage/show/{id}', 'App\Http\Controllers\Admin\ProjectController@show');
$router->get('/admin/projects/manage/edit/{id}', 'App\Http\Controllers\Admin\ProjectController@edit');
$router->post('/admin/projects/manage/update/{id}', 'App\Http\Controllers\Admin\ProjectController@update');
$router->post('/admin/projects/manage/destroy/{id}', 'App\Http\Controllers\Admin\ProjectController@destroy');
$router->get('/admin/projects/manage/analytics', 'App\Http\Controllers\Admin\ProjectController@analytics');

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
$router->get('/admin/email-templates', 'App\\Http\\Controllers\\Admin\\EmailTemplateController@index');
$router->get('/admin/email-templates/preview/{code}', 'App\\Http\\Controllers\\Admin\\EmailTemplateController@preview');
$router->get('/admin/email-templates/test/{code}', 'App\\Http\\Controllers\\Admin\\EmailTemplateController@test');
$router->get('/admin/email-templates/editor', 'App\\Http\\Controllers\\Admin\\CampaignController@templateEditor');
$router->post('/admin/email-templates/save', 'App\\Http\\Controllers\\Admin\\CampaignController@saveTemplate');
$router->get('/admin/email-logs', 'App\\Http\\Controllers\\Admin\\CampaignController@logs');
$router->get('/admin/sms-campaigns', 'App\\Http\\Controllers\\Admin\\CampaignController@smsCampaigns');
$router->get('/admin/whatsapp-broadcast', 'App\\Http\\Controllers\\Admin\\CampaignController@whatsappBroadcast');
$router->post('/admin/whatsapp-broadcast', 'App\\Http\\Controllers\\Admin\\CampaignController@whatsappBroadcast');
$router->get('/admin/referrals', 'App\\Http\\Controllers\\Admin\\ReferralController@index');
$router->get('/admin/referrals/create', 'App\\Http\\Controllers\\Admin\\ReferralController@create');
$router->post('/admin/referrals/store', 'App\\Http\\Controllers\\Admin\\ReferralController@store');
$router->get('/admin/referrals/leaderboard', 'App\\Http\\Controllers\\Admin\\ReferralController@leaderboard');
$router->get('/admin/referrals/share-analytics', 'App\\Http\\Controllers\\Admin\\ReferralController@shareAnalytics');
$router->get('/admin/referrals/tiers', 'App\\Http\\Controllers\\Admin\\ReferralController@tiers');
$router->get('/admin/referrals/{id}', 'App\\Http\\Controllers\\Admin\\ReferralController@show');
$router->post('/admin/referrals/{id}/approve', 'App\\Http\\Controllers\\Admin\\ReferralController@approve');
$router->post('/admin/referrals/{id}/reject', 'App\\Http\\Controllers\\Admin\\ReferralController@reject');

// News Categories
$router->get('/admin/news/categories', 'App\\Http\\Controllers\\Admin\\NewsController@categories');

// Operations
$router->get('/admin/support-tickets', 'App\\Http\\Controllers\\Admin\\SupportTicketController@index');
$router->get('/admin/support-tickets/create', 'App\\Http\\Controllers\\Admin\\SupportTicketController@create');
$router->post('/admin/support-tickets/store', 'App\\Http\\Controllers\\Admin\\SupportTicketController@store');
$router->get('/admin/support-tickets/{id}', 'App\\Http\\Controllers\\Admin\\SupportTicketController@show');
$router->get('/admin/support-tickets/{id}/edit', 'App\\Http\\Controllers\\Admin\\SupportTicketController@edit');
$router->post('/admin/support-tickets/{id}/update', 'App\\Http\\Controllers\\Admin\\SupportTicketController@update');
$router->post('/admin/support-tickets/{id}/reply', 'App\\Http\\Controllers\\Admin\\SupportTicketController@reply');
$router->post('/admin/support-tickets/{id}/assign', 'App\\Http\\Controllers\\Admin\\SupportTicketController@assign');
$router->post('/admin/support-tickets/{id}/status', 'App\\Http\\Controllers\\Admin\\SupportTicketController@updateStatus');
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
$router->get('/admin/ai-calling', 'App\\Http\\Controllers\\Admin\\AICallingController@index');
$router->get('/admin/ai-calling/dashboard', 'App\\Http\\Controllers\\Admin\\AICallingController@dashboard');
$router->get('/admin/ai-calling/schedule', 'App\\Http\\Controllers\\Admin\\AICallingController@schedule');
$router->get('/admin/ai-calling/sessions', 'App\\Http\\Controllers\\Admin\\AICallingController@sessions');
$router->get('/admin/ai-calling/extracted-leads', 'App\\Http\\Controllers\\Admin\\AICallingController@extractedLeads');
$router->get('/admin/ai-calling/training', 'App\\Http\\Controllers\\Admin\\AICallingController@training');
$router->post('/admin/ai-calling/training/save-voice-model', 'App\\Http\\Controllers\\Admin\\AICallingController@saveVoiceModel');
$router->post('/admin/ai-calling/training/save-script', 'App\\Http\\Controllers\\Admin\\AICallingController@saveScript');
$router->post('/admin/ai-calling/training/save-intent', 'App\\Http\\Controllers\\Admin\\AICallingController@saveIntent');
$router->get('/admin/ai-calling/health', 'App\\Http\\Controllers\\Admin\\AICallingController@healthCheck');
$router->get('/admin/ai-calling/auto-dialer', 'App\\Http\\Controllers\\Admin\\AICallingController@autoDialer');
$router->get('/admin/ai-calling/call-analytics', 'App\\Http\\Controllers\\Admin\\AICallingController@callAnalytics');
$router->post('/admin/ai-calling/auto-dialer/process', 'App\\Http\\Controllers\\Admin\\AICallingController@autoDialerProcess');
$router->post('/admin/ai-calling/auto-dialer/ai-schedule', 'App\\Http\\Controllers\\Admin\\AICallingController@autoDialerAiSchedule');
$router->get('/admin/ai-calling/call-logs', 'App\\Http\\Controllers\\Admin\\AICallingController@callLogs');
$router->get('/admin/ai-calling/call-detail', 'App\\Http\\Controllers\\Admin\\AICallingController@callDetail');

// Telecalling
$router->get('/admin/telecalling/dashboard', 'App\\Http\\Controllers\\Employee\\TelecallingController@dashboard');
$router->get('/admin/telecalling/assign', 'App\\Http\\Controllers\\Employee\\TelecallingController@assign');
$router->get('/admin/telecalling/commissions', 'App\\Http\\Controllers\\Employee\\TelecallingController@commissions');
$router->get('/admin/telecalling/approvals', 'App\\Http\\Controllers\\Employee\\TelecallingController@approvals');

// CRM
$router->get('/admin/crm', 'App\\Http\\Controllers\\Admin\\CRMController@index');
$router->get('/admin/crm/analytics', 'App\\Http\\Controllers\\Admin\\CRMController@analytics');
$router->get('/admin/crm/leads/{id}/timeline', 'App\\Http\\Controllers\\Admin\\CRMController@leadTimeline');
$router->get('/admin/crm/users', 'App\\Http\\Controllers\\Admin\\CRMController@users');
$router->get('/admin/crm/users/create', 'App\\Http\\Controllers\\Admin\\CRMController@createCustomer');
$router->post('/admin/crm/users/store', 'App\\Http\\Controllers\\Admin\\CRMController@storeCustomer');
$router->get('/admin/crm/groups', 'App\\Http\\Controllers\\Admin\\CRMController@groups');
$router->get('/admin/crm/followups', 'App\\Http\\Controllers\\Admin\\CRMController@followups');

// CRM - Email/SMS Templates
$router->get('/admin/crm/templates', 'App\\Http\\Controllers\\Admin\\CRMTemplateController@index');
$router->get('/admin/crm/templates/create', 'App\\Http\\Controllers\\Admin\\CRMTemplateController@create');
$router->post('/admin/crm/templates/store', 'App\\Http\\Controllers\\Admin\\CRMTemplateController@store');
$router->get('/admin/crm/templates/{id}/edit', 'App\\Http\\Controllers\\Admin\\CRMTemplateController@edit');
$router->post('/admin/crm/templates/{id}/update', 'App\\Http\\Controllers\\Admin\\CRMTemplateController@update');
$router->post('/admin/crm/templates/{id}/delete', 'App\\Http\\Controllers\\Admin\\CRMTemplateController@delete');

// CRM - Bulk Email/SMS
$router->get('/admin/crm/bulk-send', 'App\\Http\\Controllers\\Admin\\CRMBulkController@index');
$router->post('/admin/crm/bulk-send/preview', 'App\\Http\\Controllers\\Admin\\CRMBulkController@preview');
$router->post('/admin/crm/bulk-send/send', 'App\\Http\\Controllers\\Admin\\CRMBulkController@send');

// CRM - Lead Segmentation
$router->get('/admin/crm/segments', 'App\\Http\\Controllers\\Admin\\CRMSegmentController@index');
$router->post('/admin/crm/segments/store', 'App\\Http\\Controllers\\Admin\\CRMSegmentController@store');
$router->post('/admin/crm/segments/{id}/delete', 'App\\Http\\Controllers\\Admin\\CRMSegmentController@delete');
$router->get('/admin/crm/segments/{id}/leads', 'App\\Http\\Controllers\\Admin\\CRMSegmentController@leads');

// CRM - Lead Forms
$router->get('/admin/crm/forms', 'App\\Http\\Controllers\\Admin\\CRMFormController@index');
$router->get('/admin/crm/forms/create', 'App\\Http\\Controllers\\Admin\\CRMFormController@create');
$router->post('/admin/crm/forms/store', 'App\\Http\\Controllers\\Admin\\CRMFormController@store');
$router->get('/admin/crm/forms/{id}/edit', 'App\\Http\\Controllers\\Admin\\CRMFormController@edit');
$router->post('/admin/crm/forms/{id}/update', 'App\\Http\\Controllers\\Admin\\CRMFormController@update');
$router->post('/admin/crm/forms/{id}/delete', 'App\\Http\\Controllers\\Admin\\CRMFormController@delete');
$router->get('/admin/crm/forms/{id}/preview', 'App\\Http\\Controllers\\Admin\\CRMFormController@preview');
$router->get('/admin/crm/forms/{id}/embed', 'App\\Http\\Controllers\\Admin\\CRMFormController@embedCode');

// CRM - Lead Import
$router->get('/admin/leads/import', 'App\\Http\\Controllers\\Admin\\LeadImportController@importForm');
$router->post('/admin/leads/import/preview', 'App\\Http\\Controllers\\Admin\\LeadImportController@previewImport');
$router->post('/admin/leads/import/commit', 'App\\Http\\Controllers\\Admin\\LeadImportController@commitImport');

// CRM - Bulk Outreach
$router->get('/admin/crm/outreach', 'App\\Http\\Controllers\\Admin\\BulkOutreachController@index');
$router->post('/admin/crm/outreach/create', 'App\\Http\\Controllers\\Admin\\BulkOutreachController@createCampaign');
$router->post('/admin/crm/outreach/{id}/send', 'App\\Http\\Controllers\\Admin\\BulkOutreachController@sendCampaign');
$router->get('/admin/crm/outreach/{id}/stats', 'App\\Http\\Controllers\\Admin\\BulkOutreachController@campaignStats');

// CRM - Agentic AI
$router->get('/admin/crm/agentic', 'App\\Http\\Controllers\\Admin\\AgenticCRMController@index');
$router->post('/admin/crm/agentic/auto-followup', 'App\\Http\\Controllers\\Admin\\AgenticCRMController@runAutoFollowup');
$router->post('/admin/crm/agentic/score-recalc', 'App\\Http\\Controllers\\Admin\\AgenticCRMController@runScoreRecalculation');
$router->post('/admin/crm/agentic/auto-assign', 'App\\Http\\Controllers\\Admin\\AgenticCRMController@runAutoAssignment');
$router->post('/admin/crm/agentic/insights', 'App\\Http\\Controllers\\Admin\\AgenticCRMController@generateInsights');
$router->post('/admin/crm/agentic/run-all', 'App\\Http\\Controllers\\Admin\\AgenticCRMController@runAll');

// AI System — Unified Dashboard + 5 Agents
$router->get('/admin/ai-system', 'App\\Http\\Controllers\\Admin\\AISystemController@index');
$router->post('/admin/ai-system/run', 'App\\Http\\Controllers\\Admin\\AISystemController@runAgent');
$router->get('/admin/ai-system/qualifier', 'App\\Http\\Controllers\\Admin\\AISystemController@qualifier');
$router->get('/admin/ai-system/market-report', 'App\\Http\\Controllers\\Admin\\AISystemController@marketReport');
$router->get('/admin/ai-system/settings', 'App\\Http\\Controllers\\Admin\\AISystemController@settings');
$router->post('/admin/ai-system/settings', 'App\\Http\\Controllers\\Admin\\AISystemController@settings');
$router->get('/admin/ai-system/health', 'App\\Http\\Controllers\\Admin\\AISystemController@engineHealth');
// Executive AI Assistant (unified role-aware assistant)
$router->get('/admin/ai/executive-assistant', 'App\\Http\\Controllers\\Admin\\ExecutiveAIController@index');
$router->post('/admin/ai/executive-assistant/chat', 'App\\Http\\Controllers\\Admin\\ExecutiveAIController@chat');
// AI Content Generation (description + blog draft) — AJAX
$router->post('/ai/content/description', 'App\\Http\\Controllers\\AI\\AIContentController@generateDescription');
$router->post('/ai/content/blog-draft', 'App\\Http\\Controllers\\AI\\AIContentController@generateBlogDraft');
$router->post('/ai/content/image-tags', 'App\\Http\\Controllers\\AI\\AIContentController@generateImageTags');
// CRM — Role-Based Dashboard + Dedup
$router->get('/admin/crm/role-dashboard', 'App\\Http\\Controllers\\Admin\\CRMAdminController@roleDashboard');
$router->get('/admin/crm/dedup', 'App\\Http\\Controllers\\Admin\\CRMAdminController@dedup');
$router->post('/admin/crm/dedup/merge', 'App\\Http\\Controllers\\Admin\\CRMAdminController@merge');
$router->post('/admin/crm/dedup/bulk-merge', 'App\\Http\\Controllers\\Admin\\CRMAdminController@bulkMerge');

// ============================================================
// CRM - Lead Routing Rules (Phase 3)
// ============================================================
$router->get('/admin/crm/routing', 'App\\Http\\Controllers\\Admin\\LeadRoutingController@index');
$router->get('/admin/crm/routing/create', 'App\\Http\\Controllers\\Admin\\LeadRoutingController@create');
$router->post('/admin/crm/routing/store', 'App\\Http\\Controllers\\Admin\\LeadRoutingController@store');
$router->get('/admin/crm/routing/{id}/edit', 'App\\Http\\Controllers\\Admin\\LeadRoutingController@edit');
$router->post('/admin/crm/routing/{id}/update', 'App\\Http\\Controllers\\Admin\\LeadRoutingController@update');
$router->post('/admin/crm/routing/{id}/delete', 'App\\Http\\Controllers\\Admin\\LeadRoutingController@delete');
$router->post('/admin/crm/routing/{id}/toggle', 'App\\Http\\Controllers\\Admin\\LeadRoutingController@toggle');

// ============================================================
// CRM - Assignment Approvals (Phase 6)
// ============================================================
$router->get('/admin/crm/assignments', 'App\\Http\\Controllers\\Admin\\AssignmentApprovalController@index');
$router->post('/admin/crm/assignments/{id}/approve', 'App\\Http\\Controllers\\Admin\\AssignmentApprovalController@approve');
$router->post('/admin/crm/assignments/{id}/reject', 'App\\Http\\Controllers\\Admin\\AssignmentApprovalController@reject');
$router->post('/admin/crm/assignments/bulk', 'App\\Http\\Controllers\\Admin\\AssignmentApprovalController@bulkAction');

// ============================================================
// Smart Registration — Admin Dashboard
// ============================================================
$router->get('/admin/smart-registration', 'Auth\\OtpAuthController@adminDashboard');
$router->get('/admin/smart-registration/detail', 'Auth\\OtpAuthController@adminSessionDetail');

// ============================================================
// CRM - Custom Fields
// ============================================================
$router->get('/admin/crm/custom-fields', 'App\\Http\\Controllers\\Admin\\CRMCustomFieldController@index');
$router->get('/admin/crm/custom-fields/create', 'App\\Http\\Controllers\\Admin\\CRMCustomFieldController@create');
$router->post('/admin/crm/custom-fields/store', 'App\\Http\\Controllers\\Admin\\CRMCustomFieldController@store');
$router->get('/admin/crm/custom-fields/{id}/edit', 'App\\Http\\Controllers\\Admin\\CRMCustomFieldController@edit');
$router->post('/admin/crm/custom-fields/{id}/update', 'App\\Http\\Controllers\\Admin\\CRMCustomFieldController@update');
$router->post('/admin/crm/custom-fields/{id}/delete', 'App\\Http\\Controllers\\Admin\\CRMCustomFieldController@delete');

// ============================================================
// CRM - SLA Tracking
// ============================================================
$router->get('/admin/crm/sla', 'App\\Http\\Controllers\\Admin\\SLAController@dashboard');
$router->get('/admin/crm/sla/rules', 'App\\Http\\Controllers\\Admin\\SLAController@rules');
$router->post('/admin/crm/sla/rules/store', 'App\\Http\\Controllers\\Admin\\SLAController@storeRule');
$router->get('/admin/crm/sla/breach-log', 'App\\Http\\Controllers\\Admin\\SLAController@breachLog');

// ============================================================
// CRM - Email Tracking
// ============================================================
$router->get('/admin/crm/email-tracking/stats', 'App\\Http\\Controllers\\Admin\\EmailTrackingController@stats');
$router->get('/api/email/track/open/{id}', 'App\\Http\\Controllers\\Admin\\EmailTrackingController@trackOpen');
$router->get('/api/email/track/click/{id}', 'App\\Http\\Controllers\\Admin\\EmailTrackingController@trackClick');

// ============================================================
// CRM - Meetings
// ============================================================
$router->get('/admin/meetings', 'App\\Http\\Controllers\\Admin\\MeetingController@index');
$router->get('/admin/meetings/schedule', 'App\\Http\\Controllers\\Admin\\MeetingController@schedule');
$router->post('/admin/meetings/store', 'App\\Http\\Controllers\\Admin\\MeetingController@store');
$router->get('/admin/meetings/{id}', 'App\\Http\\Controllers\\Admin\\MeetingController@show');
$router->post('/admin/meetings/{id}/update', 'App\\Http\\Controllers\\Admin\\MeetingController@update');
$router->post('/admin/meetings/{id}/cancel', 'App\\Http\\Controllers\\Admin\\MeetingController@cancel');
$router->post('/admin/meetings/{id}/complete', 'App\\Http\\Controllers\\Admin\\MeetingController@complete');
$router->get('/admin/meetings/calendar', 'App\\Http\\Controllers\\Admin\\MeetingController@calendar');

// ============================================================
// CRM - Voice CRM
// ============================================================
$router->get('/admin/crm/voice', 'App\\Http\\Controllers\\Admin\\CRMVoiceController@index');
$router->get('/admin/crm/voice/call/{id}', 'App\\Http\\Controllers\\Admin\\CRMVoiceController@callLead');
$router->post('/admin/crm/voice/note', 'App\\Http\\Controllers\\Admin\\CRMVoiceController@dictateNote');
$router->post('/admin/crm/voice/command', 'App\\Http\\Controllers\\Admin\\CRMVoiceController@voiceCommand');

// ============================================================
// CRM - Drip Campaigns (CRM path)
// ============================================================
$router->get('/admin/crm/drip', 'App\\Http\\Controllers\\Admin\\DripCampaignController@index');
$router->get('/admin/crm/drip/create', 'App\\Http\\Controllers\\Admin\\DripCampaignController@create');
$router->post('/admin/crm/drip/store', 'App\\Http\\Controllers\\Admin\\DripCampaignController@store');
$router->get('/admin/crm/drip/{id}', 'App\\Http\\Controllers\\Admin\\DripCampaignController@show');
$router->get('/admin/crm/drip/{id}/edit', 'App\\Http\\Controllers\\Admin\\DripCampaignController@create');
$router->post('/admin/crm/drip/{id}/update', 'App\\Http\\Controllers\\Admin\\DripCampaignController@store');
$router->get('/admin/crm/drip/{id}/delete', 'App\\Http\\Controllers\\Admin\\DripCampaignController@delete');

// CRM - Share Analytics
$router->get('/admin/crm/shares', 'App\\Http\\Controllers\\Front\\ShareController@shareStats');
$router->post('/share/track', 'App\\Http\\Controllers\\Front\\ShareController@trackShare');

// Buyer System
$router->get('/buyer/dashboard', 'App\\Http\\Controllers\\BuyerController@dashboard');
$router->post('/buyer/interest/submit', 'App\\Http\\Controllers\\BuyerController@submitInterest');

// Property Commission
$router->post('/api/property-commission/record', 'App\\Http\\Controllers\\PropertyCommissionController@recordSale');
$router->get('/api/property-commission/summary', 'App\\Http\\Controllers\\PropertyCommissionController@summary');

// Ad Manager
$router->get('/admin/ads', 'App\\Http\\Controllers\\Admin\\AdManagerController@index');
$router->get('/admin/ads/create', 'App\\Http\\Controllers\\Admin\\AdManagerController@create');
$router->post('/admin/ads/create', 'App\\Http\\Controllers\\Admin\\AdManagerController@create');
$router->get('/admin/ads/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdManagerController@edit');
$router->post('/admin/ads/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdManagerController@edit');
$router->get('/admin/ads/delete/{id}', 'App\\Http\\Controllers\\Admin\\AdManagerController@delete');

// Ad click tracking
$router->get('/ad-click/{id}', 'App\\Http\\Controllers\\Admin\\AdManagerController@trackClick');

// Ad settings
$router->get('/admin/ads/settings', 'App\\Http\\Controllers\\Admin\\AdManagerController@settings');
$router->post('/admin/ads/save-settings', 'App\\Http\\Controllers\\Admin\\AdManagerController@saveSettings');

// Directory Management
$router->get('/admin/directory', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@index');
$router->get('/admin/directory/categories', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@categories');
$router->post('/admin/directory/categories', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@categories');
$router->get('/admin/directory/delete-category/{id}', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@deleteCategory');
$router->get('/admin/directory/listings', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@listings');
$router->get('/admin/directory/listing-form/{id}', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@listingForm');
$router->post('/admin/directory/listing-form/{id}', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@listingForm');
$router->get('/admin/directory/listing-form', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@listingForm');
$router->post('/admin/directory/listing-form', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@listingForm');
$router->get('/admin/directory/delete-listing/{id}', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@deleteListing');
$router->get('/admin/directory/reviews', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@reviews');
$router->get('/admin/directory/approve-review/{id}', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@approveReview');
$router->get('/admin/directory/reject-review/{id}', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@rejectReview');
$router->get('/admin/directory/jobs', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@jobs');
$router->get('/admin/directory/delete-job/{id}', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@deleteJob');
$router->get('/admin/directory/materials', 'App\Http\Controllers\Admin\AdminDirectoryController@materials');
$router->get('/admin/directory/delete-material/{id}', 'App\\Http\\Controllers\\Admin\\AdminDirectoryController@deleteMaterial');

// Jobs & Applicants
$router->get('/admin/jobs', 'App\\Http\\Controllers\\Admin\\CareerController@index');
$router->get('/admin/applicants', 'App\\Http\\Controllers\\Admin\\CareerController@applicants');

// Testimonials
$router->get('/admin/testimonials/manage', 'App\\Http\\Controllers\\Admin\\TestimonialController@manage');

// Financial
$router->get('/admin/emi-calculator', 'App\\Http\\Controllers\\Admin\\EMIController@calculator');
$router->get('/admin/loans', 'App\\Http\\Controllers\\Admin\\LoanController@index');

// Company Loan System (In-House Loan Management)
$router->get('/admin/company-loans', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@index');
$router->get('/admin/company-loans/create', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@createForm');
$router->post('/admin/company-loans/create', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@createStore');
$router->get('/admin/company-loans/{id}', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@detail');
$router->post('/admin/company-loans/{id}/disburse', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@disburse');
$router->post('/admin/company-loans/{id}/default', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@markDefault');
$router->post('/admin/company-loans/{id}/foreclose', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@foreclose');
$router->post('/admin/company-loans/{id}/payment', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@recordPayment');
$router->post('/admin/company-loans/{id}/guarantor', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@addGuarantor');
$router->post('/admin/company-loans/{id}/document/{type}', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@generateDocument');
$router->get('/admin/company-loans/document/{docId}', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@viewDocument');
$router->post('/admin/company-loans/document/{docId}/sign', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@signDocument');
$router->post('/admin/company-loans/document/{docId}/finalize', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@finalizeDocument');
$router->get('/admin/company-loans/offers', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@offers');
$router->post('/admin/company-loans/offers/create', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@offerCreate');
$router->post('/admin/company-loans/offers/{id}/update', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@offerUpdate');
$router->get('/admin/company-loans/early-incentives', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@earlyIncentives');
$router->post('/admin/company-loans/early-incentives/create', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@earlyIncentiveCreate');
$router->get('/admin/company-loans/calculator', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@calculator');
$router->get('/admin/company-loans/check-eligibility', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@checkEligibility');
$router->post('/admin/company-loans/run-penalties', 'App\\Http\\Controllers\\Admin\\CompanyLoanController@runPenalties');

$router->get('/admin/builders', 'App\\Http\\Controllers\\Admin\\BuilderController@index');

// Reports
$router->get('/admin/reports/financial', 'App\\Http\\Controllers\\Admin\\Reports\\FinancialReportController@index');

// HRM
$router->get('/admin/hrm', 'App\\Http\\Controllers\\Admin\\HRMController@index');
$router->get('/admin/hrm/users/create', 'App\\Http\\Controllers\\Admin\\HRMController@createEmployee');
$router->get('/admin/hrm/attendance', 'App\\Http\\Controllers\\Admin\\HRMController@attendance');
$router->get('/admin/hrm/leave', 'App\\Http\\Controllers\\Admin\\HRMController@leave');

// Backup
$router->get('/admin/backup', 'App\\Http\\Controllers\\Admin\\BackupController@index');
$router->post('/admin/backup/create', 'App\\Http\\Controllers\\Admin\\BackupController@create');
$router->post('/admin/backup/restore/{id}', 'App\\Http\\Controllers\\Admin\\BackupController@restore');
$router->post('/admin/backup/upload', 'App\\Http\\Controllers\\Admin\\BackupController@upload');
$router->get('/admin/backup/health', 'App\\Http\\Controllers\\Admin\\BackupController@health');
$router->get('/admin/backup/download/{id}', 'App\\Http\\Controllers\\Admin\\BackupController@download');
$router->post('/admin/backup/to-s3', 'App\\Http\\Controllers\\Admin\\BackupController@toS3');
$router->get('/admin/backup/from-s3', 'App\\Http\\Controllers\\Admin\\BackupController@fromS3');
$router->get('/admin/backup/s3-download', 'App\\Http\\Controllers\\Admin\\BackupController@s3Download');

// Storage Gateway (admin UI)
$router->get('/admin/storage', 'App\\Http\\Controllers\\Admin\\StorageGatewayController@index');
$router->post('/admin/storage/test', 'App\\Http\\Controllers\\Admin\\StorageGatewayController@test');
$router->get('/admin/storage/list', 'App\\Http\\Controllers\\Admin\\StorageGatewayController@listBucket');
$router->post('/admin/storage/switch', 'App\\Http\\Controllers\\Admin\\StorageGatewayController@switchDriver');

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
$router->get('/user/notifications', 'Front\\UserController@notifications');
$router->post('/user/notifications/read-all', 'Front\\UserController@markAllNotificationsRead');
$router->post('/user/notifications/{id}/read', 'Front\\UserController@markNotificationRead');
$router->get('/user/messages', function () {
    header('Location: ' . BASE_URL . '/user/notifications');
    exit;
});

// Admin Messaging Panel
$router->get('/admin/messages',                          'App\\Http\\Controllers\\Admin\\MessagesController@inbox');
$router->get('/admin/messages/compose',                  'App\\Http\\Controllers\\Admin\\MessagesController@compose');
$router->post('/admin/messages/send',                    'App\\Http\\Controllers\\Admin\\MessagesController@sendMessage');
$router->get('/admin/messages/conversation/{id}',        'App\\Http\\Controllers\\Admin\\MessagesController@conversation');
$router->get('/admin/messages/ajax-search',              'App\\Http\\Controllers\\Admin\\MessagesController@ajaxSearchUsers');
$router->get('/user/payments', function () {
    header('Location: ' . BASE_URL . '/user/payment-history');
    exit;
});

// Customer Portal - EMI & Payments
$router->get('/user/emi-tracker', 'Front\\UserController@emiTracker');
$router->get('/user/payment-history', 'Front\\UserController@paymentHistory');
$router->get('/user/site-visits', 'Front\\UserController@mySiteVisits');
$router->post('/user/site-visits/book', 'Front\\UserController@bookSiteVisitAction');

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
$router->post('/admin/mlm-realestate/salary/process', 'Admin\MLMRealEstateController@processMonthlyPayouts');
$router->get('/admin/mlm-realestate/salary/diagnostics', 'Admin\MLMRealEstateController@salaryDiagnostics');
$router->get('/admin/mlm-realestate/rera/status', 'Admin\MLMRealEstateController@reraStatus');
$router->get('/admin/mlm-realestate/rera/diagnostics', 'Admin\MLMRealEstateController@reraDiagnostics');

// Language switcher
$router->get('/language/set/{lang}', 'Front\\PageController@setLanguage');
$router->get('/language/selector', 'App\\Http\\Controllers\\Utility\\LanguageController@languageSelector');

// Admin Translations (Utility\LanguageController)
$router->get('/admin/translations', 'App\\Http\\Controllers\\Utility\\LanguageController@adminTranslations');
$router->post('/admin/translations', 'App\\Http\\Controllers\\Utility\\LanguageController@adminTranslations');

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
$router->get('/admin/hr/users', 'App\\Http\\Controllers\\Admin\\HRController@users');
$router->get('/admin/hr/users/create', 'App\\Http\\Controllers\\Admin\\HRController@createEmployee');
$router->post('/admin/hr/users/store', 'App\\Http\\Controllers\\Admin\\HRController@storeEmployee');
$router->get('/admin/hr/users/edit/{id}', 'App\\Http\\Controllers\\Admin\\HRController@editEmployee');
$router->post('/admin/hr/users/update/{id}', 'App\\Http\\Controllers\\Admin\\HRController@updateEmployee');
$router->get('/admin/hr/users/delete/{id}', 'App\\Http\\Controllers\\Admin\\HRController@deleteEmployee');
$router->get('/admin/hr/users/view/{id}', 'App\\Http\\Controllers\\Admin\\HRController@viewEmployee');
$router->get('/admin/hr/attendance', 'App\\Http\\Controllers\\Admin\\HRController@attendance');
$router->post('/admin/hr/attendance/mark', 'App\\Http\\Controllers\\Admin\\HRController@markAttendance');
$router->get('/admin/hr/attendance/report', 'App\\Http\\Controllers\\Admin\\HRController@attendanceReport');
$router->get('/admin/hr/leave', 'App\\Http\\Controllers\\Admin\\HRController@leaves');
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
// PAYROLL BATCH GENERATION (Indian PF/ESI/TDS)
// ═══════════════════════════════════════════════════
$router->get('/admin/salary/batch/preview', 'App\\Http\\Controllers\\Admin\\SalaryController@batchPreview');
$router->post('/admin/salary/batch/generate', 'App\\Http\\Controllers\\Admin\\SalaryController@batchGenerate');
$router->post('/admin/salary/batch/process', 'App\\Http\\Controllers\\Admin\\SalaryController@batchProcess');
$router->get('/admin/salary/batch/history', 'App\\Http\\Controllers\\Admin\\SalaryController@batchHistory');

// ═══════════════════════════════════════════════════
// ASSOCIATE SALARY DASHBOARD
// ═══════════════════════════════════════════════════
$router->get('/admin/salary/associate-dashboard', 'App\\Http\\Controllers\\Admin\\SalaryController@associateDashboard');
$router->post('/admin/salary/update-associate-salary', 'App\\Http\\Controllers\\Admin\\SalaryController@updateAssociateSalary');
$router->post('/admin/salary/process-associate-salary', 'App\\Http\\Controllers\\Admin\\SalaryController@processAssociateSalary');

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
// SELF-LEARNING AI (Phase 23) - No External API
// ═══════════════════════════════════════════════════
$router->get('/admin/ai-dashboard', 'Front\\AIBotController@stats');          // JSON stats endpoint
$router->get('/admin/ai/dashboard', function() {
    $root = dirname(__DIR__);
    require_once $root . '/vendor/autoload.php';
    $config = require $root . '/config/database.php';
    try {
        $pdo = new \PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $ai = new \App\Services\AI\AIManager($pdo);
        $stats = $ai->getStats();
        $recentMessages = $pdo->query("SELECT * FROM ai_chat_messages ORDER BY created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
        $topIntents = $pdo->query("SELECT detected_intent, COUNT(*) as cnt FROM ai_chat_messages WHERE detected_intent IS NOT NULL GROUP BY detected_intent ORDER BY cnt DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        $topScores = $pdo->query("SELECT ls.*, l.name, l.phone FROM ai_lead_scores ls LEFT JOIN leads l ON l.id = ls.lead_id ORDER BY ls.score DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
        $priceModels = $pdo->query("SELECT * FROM ai_price_models ORDER BY trained_at DESC LIMIT 5")->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        $stats = [];
        $recentMessages = [];
        $topIntents = [];
        $topScores = [];
        $priceModels = [];
        error_log("ai/dashboard route: " . $e->getMessage());
    }
    $page_title = 'AI Dashboard - APS Dream Home';
    $page_heading = 'Self-Learning AI';
    ob_start();
    include $root . '/app/views/admin/ai-dashboard.php';
    $content = ob_get_clean();
    $GLOBALS['_admin_page_title'] = $page_title;
    $GLOBALS['_admin_page_heading'] = $page_heading;
    $GLOBALS['_admin_content'] = $content;
    $GLOBALS['_admin_extra_js'] = '';
    $GLOBALS['_admin_extra_css'] = '';
    require $root . '/app/views/admin/layouts/admin.php';
    exit;
});
$router->post('/api/ai/score-lead/{id}', 'Front\\AIBotController@scoreLead');
$router->post('/api/ai/predict-price', 'Front\\AIBotController@predictPrice');
$router->get('/api/ai/recommend', 'Front\\AIBotController@recommend');
$router->post('/api/ai/retrain', 'Front\\AIBotController@retrain');
$router->any('/api/ai/legacy-chat', 'Front\\AIBotController@chat');

// ═══════════════════════════════════════════════════
// COMPANY SETTINGS
// ═══════════════════════════════════════════════════
$router->get('/admin/company/settings', 'App\\Http\\Controllers\\Admin\\CompanyController@settings');
$router->post('/admin/company/settings', 'App\\Http\\Controllers\\Admin\\CompanyController@updateSettings');
$router->get('/admin/company/users', 'App\\Http\\Controllers\\Admin\\CompanyController@users');
$router->post('/admin/company/users', 'App\\Http\\Controllers\\Admin\\CompanyController@addEmployee');

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
$router->get('/admin/kyc/logs', 'App\\Http\\Controllers\\Admin\\KycController@logs');
$router->get('/admin/kyc/{id}', 'App\\Http\\Controllers\\Admin\\KycController@show');
$router->post('/admin/kyc/{id}/approve', 'App\\Http\\Controllers\\Admin\\KycController@approve');
$router->post('/admin/kyc/{id}/reject', 'App\\Http\\Controllers\\Admin\\KycController@reject');
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
$router->get('/admin/training/certificates/download/{id}', 'App\\Http\\Controllers\\Admin\\TrainingController@downloadCertificate');

// ═══════════════════════════════════════════════════
// FINANCIAL REPORTS
// ═══════════════════════════════════════════════════
$router->get('/admin/financial-reports', 'App\\Http\\Controllers\\Admin\\Reports\\FinancialReportController@index');

// ═══════════════════════════════════════════════════
// VOICE users
// ═══════════════════════════════════════════════════
$router->get('/admin/voice-users', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@dashboard');
$router->get('/admin/voice-users/dashboard', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@dashboard');
$router->get('/admin/voice-users/history', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@history');
$router->get('/admin/voice-users/schedule', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@schedule');
$router->post('/admin/voice-users/bulk-schedule', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@bulkSchedule');
$router->post('/admin/voice-users/auto-assign', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@autoAssign');
$router->get('/admin/voice-users/scripts', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@scripts');
$router->get('/admin/voice-users/extracted-leads', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@extractedLeads');
$router->get('/admin/voice-users/settings', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@settings');
$router->get('/admin/voice-users/oln-dashboard', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@olnDashboard');
$router->post('/admin/voice-users/cancel-schedule/{id}', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@cancelSchedule');
$router->post('/admin/voice-users/reschedule/{id}', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@rescheduleCall');
$router->post('/admin/voice-users/ajax/convert-lead', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@ajaxConvertLead');
$router->get('/admin/voice-users/ajax/lead-timeline/{id}', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@ajaxLeadTimeline');

// Voice Agents aliases (same as voice-users)
$router->get('/admin/voice-agents', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@dashboard');
$router->get('/admin/voice-agents/history', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@history');
$router->get('/admin/voice-agents/schedule', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@schedule');
$router->get('/admin/voice-agents/scripts', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@scripts');
$router->get('/admin/voice-agents/extracted-leads', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@extractedLeads');
$router->get('/admin/voice-agents/settings', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@settings');
$router->get('/admin/voice-agents/oln', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@olnDashboard');
// Live voice call monitor (Cluster 2 - 2026-06-05)
$router->get('/admin/voice-agents/live', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@live');
$router->post('/admin/voice-agents/transfer-call', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@transferCall');
$router->post('/admin/voice-agents/hangup-call', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@hangupCall');

// ═══════════════════════════════════════════════════
// SIM CALLING — Asterisk + GSM Gateway (Option C)
// ═══════════════════════════════════════════════════
$router->get('/admin/sim-calling',                      'App\\Http\\Controllers\\Admin\\SIMCallingController@dashboard');
$router->get('/admin/sim-calling/settings',             'App\\Http\\Controllers\\Admin\\SIMCallingController@settings');
$router->post('/admin/sim-calling/settings',            'App\\Http\\Controllers\\Admin\\SIMCallingController@settings');
$router->get('/admin/sim-calling/generate-dialplan',    'App\\Http\\Controllers\\Admin\\SIMCallingController@generateDialplan');
$router->post('/admin/sim-calling/api/make-call',       'App\\Http\\Controllers\\Admin\\SIMCallingController@makeCall');
$router->get('/admin/sim-calling/api/status',           'App\\Http\\Controllers\\Admin\\SIMCallingController@status');
$router->post('/admin/sim-calling/api/hangup',          'App\\Http\\Controllers\\Admin\\SIMCallingController@hangup');

// ═══════════════════════════════════════════════════
// AGENTIC AI — Auto-Reply Agent System
// ═══════════════════════════════════════════════════
$router->get('/admin/agentic-ai',                     'App\\Http\\Controllers\\Admin\\AgenticAIController@index');
$router->get('/admin/agentic-ai/auto-reply',          'App\\Http\\Controllers\\Admin\\AgenticAIController@autoReply');
$router->post('/admin/agentic-ai/auto-reply',         'App\\Http\\Controllers\\Admin\\AgenticAIController@autoReply');
$router->get('/admin/agentic-ai/conversations',       'App\\Http\\Controllers\\Admin\\AgenticAIController@conversations');
$router->get('/admin/agentic-ai/conversation/{id}',   'App\\Http\\Controllers\\Admin\\AgenticAIController@conversation');
$router->get('/admin/agentic-ai/agent/{type}',        'App\\Http\\Controllers\\Admin\\AgenticAIController@agent');
$router->get('/admin/agentic-ai/logs',                'App\\Http\\Controllers\\Admin\\AgenticAIController@logs');
$router->post('/admin/agentic-ai/run-all',             'App\\Http\\Controllers\\Admin\\AgenticAIController@runAll');
$router->post('/admin/agentic-ai/api/send',           'App\\Http\\Controllers\\Admin\\AgenticAIController@sendMessage');
$router->post('/admin/agentic-ai/api/claim',          'App\\Http\\Controllers\\Admin\\AgenticAIController@claimConversation');
$router->post('/admin/agentic-ai/api/resolve',        'App\\Http\\Controllers\\Admin\\AgenticAIController@resolveConversation');
$router->get('/admin/agentic-ai/api/messages',        'App\\Http\\Controllers\\Admin\\AgenticAIController@getMessages');

// PDF Service (Cluster 2 - 2026-06-05)
$router->get('/pdf/download/{type}/{id}',                          'App\\Http\\Controllers\\Front\\PdfController@download');
$router->get('/admin/pdfs',                                        'App\\Http\\Controllers\\Front\\PdfController@adminIndex');
$router->post('/admin/pdfs/generate',                              'App\\Http\\Controllers\\Front\\PdfController@adminGenerate');
$router->get('/admin/pdfs/view/{type}/{id}',                       'App\\Http\\Controllers\\Front\\PdfController@adminView');

// ═══════════════════════════════════════════════════
// VOICE CALL SCHEDULER (Admin)
// ═══════════════════════════════════════════════════
$router->get('/admin/voice-scheduler', 'App\\Http\\Controllers\\Admin\\VoiceCallSchedulerController@index');
$router->get('/admin/voice-scheduler/schedule', 'App\\Http\\Controllers\\Admin\\VoiceCallSchedulerController@schedule');
$router->post('/admin/voice-scheduler/store', 'App\\Http\\Controllers\\Admin\\VoiceCallSchedulerController@store');
$router->get('/admin/voice-scheduler/calls', 'App\\Http\\Controllers\\Admin\\VoiceCallSchedulerController@calls');
$router->get('/admin/voice-scheduler/calls/{id}', 'App\\Http\\Controllers\\Admin\\VoiceCallSchedulerController@callDetail');
$router->post('/admin/voice-scheduler/process', 'App\\Http\\Controllers\\Admin\\VoiceCallSchedulerController@processQueue');
$router->get('/admin/voice-scheduler/analytics', 'App\\Http\\Controllers\\Admin\\VoiceCallSchedulerController@analytics');
$router->post('/admin/voice-scheduler/reschedule', 'App\\Http\\Controllers\\Admin\\VoiceCallSchedulerController@rescheduleCall');
$router->post('/admin/voice-scheduler/cancel', 'App\\Http\\Controllers\\Admin\\VoiceCallSchedulerController@cancelSchedule');

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

// NOC / REGISTRY ELIGIBILITY CHECKS (Admin)
$router->get('/admin/legal/noc-index', 'App\\Http\\Controllers\\Admin\\NocController@index');
$router->get('/admin/legal/noc-eligibility', 'App\\Http\\Controllers\\Admin\\NocController@eligibility');
$router->post('/admin/legal/noc-check', 'App\\Http\\Controllers\\Admin\\NocController@check');
$router->get('/admin/legal/registry-show/{id}', 'App\\Http\\Controllers\\Admin\\NocController@showRegistry');
$router->get('/admin/legal/noc-show/{id}', 'App\\Http\\Controllers\\Admin\\NocController@showNoc');

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
$router->post('/admin/marketplace/toggle-featured', 'App\\Http\\Controllers\\Admin\\AdminMarketplaceController@toggleFeatured');
$router->post('/admin/marketplace/toggle-urgent', 'App\\Http\\Controllers\\Admin\\AdminMarketplaceController@toggleUrgent');
$router->get('/admin/premium-packages', 'App\\Http\\Controllers\\Admin\\AdminPackageController@index');
$router->get('/admin/premium-packages/create', 'App\\Http\\Controllers\\Admin\\AdminPackageController@create');
$router->post('/admin/premium-packages/create', 'App\\Http\\Controllers\\Admin\\AdminPackageController@create');
$router->get('/admin/premium-packages/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdminPackageController@edit');
$router->post('/admin/premium-packages/edit/{id}', 'App\\Http\\Controllers\\Admin\\AdminPackageController@edit');
$router->post('/admin/premium-packages/delete/{id}', 'App\\Http\\Controllers\\Admin\\AdminPackageController@delete');
$router->get('/admin/compliance', 'App\\Http\\Controllers\\Admin\\AdminComplianceController@index');
$router->get('/admin/compliance-scorecard', 'App\\Http\\Controllers\\Admin\\ComplianceController@index');
$router->get('/admin/compliance-scorecard/area/{area}', 'App\\Http\\Controllers\\Admin\\ComplianceController@area');
$router->get('/admin/compliance-scorecard/recommendations', 'App\\Http\\Controllers\\Admin\\ComplianceController@recommendations');
$router->get('/admin/developer', 'App\\Http\\Controllers\\Admin\\AdminDeveloperController@index');
$router->get('/admin/analytics/advanced', 'App\\Http\\Controllers\\Admin\\AnalyticsController@advanced');
$router->get('/admin/advanced-analytics', 'App\\Http\\Controllers\\Analytics\\AdvancedAnalyticsController@dashboard');
$router->get('/admin/advanced-analytics/property', 'App\\Http\\Controllers\\Analytics\\AdvancedAnalyticsController@propertyAnalytics');
$router->get('/admin/advanced-analytics/users', 'App\\Http\\Controllers\\Analytics\\AdvancedAnalyticsController@userAnalytics');
$router->get('/admin/advanced-analytics/financial', 'App\\Http\\Controllers\\Analytics\\AdvancedAnalyticsController@financialAnalytics');
$router->get('/admin/advanced-analytics/mlm', 'App\\Http\\Controllers\\Analytics\\AdvancedAnalyticsController@mlmAnalytics');
$router->get('/admin/advanced-analytics/realtime', 'App\\Http\\Controllers\\Analytics\\AdvancedAnalyticsController@apiGetRealtimeData');
$router->get('/admin/performance', 'App\\Http\\Controllers\\Admin\\AdminPerformanceController@index');
$router->get('/admin/security', 'App\\Http\\Controllers\\Admin\\AdminSecurityController@index');

// ============================================================
// REDIRECT ROUTES (backward compatibility with different URL styles)
// ============================================================

// /admin/workflow (no s) -> /admin/workflows
$router->get('/admin/workflow', function () {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/workflows');
    exit;
});
$router->get('/admin/workflow/approvals', function () {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/workflows/pending');
    exit;
});
$router->get('/admin/workflow/reports', function () {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/workflows');
    exit;
});

// /admin/schedule/weekly-view -> /admin/schedule/weekly
$router->get('/admin/schedule/weekly-view', function () {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/schedule/weekly');
    exit;
});

// /admin/customer-leads -> /admin/customer-lead dashboard
$router->get('/admin/customer-leads', function () {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/customer-lead/behavior');
    exit;
});

// /admin/farmers -> /farmers (public)
$router->get('/admin/farmers', function () {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/farmers');
    exit;
});

// Training courses redirect (sidebar link alias)
$router->get('/admin/training/courses', function () {
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
$router->get('/admin/notifications/booking-log', 'App\\Http\\Controllers\\Admin\\AdminNotificationController@bookingLog');

// ============================================================
// NOTIFICATION DASHBOARD — Delivery stats, channel health, templates
// ============================================================
$router->get('/admin/notification-dashboard', 'App\\Http\\Controllers\\Admin\\NotificationDashboardController@index');
$router->get('/admin/notification-dashboard/sms-templates', 'App\\Http\\Controllers\\Admin\\NotificationDashboardController@smsTemplates');
$router->get('/admin/notification-dashboard/whatsapp-templates', 'App\\Http\\Controllers\\Admin\\NotificationDashboardController@whatsappTemplates');
$router->post('/admin/notification-dashboard/send-test', 'App\\Http\\Controllers\\Admin\\NotificationDashboardController@sendTest');

// ============================================================
// TECH: PWA ADDITIONAL ROUTES
// ============================================================
// ============================================================
// NOTIFICATION MANAGEMENT (old NotificationController, now using admin.php layout)
// ============================================================
$router->get('/admin/notification-management', 'App\\Http\\Controllers\\NotificationController@index');
$router->get('/admin/notification-management/templates', 'App\\Http\\Controllers\\NotificationController@templates');
$router->get('/admin/notification-management/templates/create', 'App\\Http\\Controllers\\NotificationController@createTemplate');
$router->get('/admin/notification-management/templates/edit/{id}', 'App\\Http\\Controllers\\NotificationController@editTemplate');
$router->get('/admin/notification-management/logs/email', 'App\\Http\\Controllers\\NotificationController@emailLogs');
$router->get('/admin/notification-management/logs/sms', 'App\\Http\\Controllers\\NotificationController@smsLogs');
$router->get('/admin/notification-management/settings', 'App\\Http\\Controllers\\NotificationController@settings');
$router->get('/admin/notification-management/send-test', 'App\\Http\\Controllers\\NotificationController@sendTest');
$router->get('/admin/notification-management/preview/{id}', 'App\\Http\\Controllers\\NotificationController@preview');

// ============================================================
// MISSING SIDEBAR MENU ROUTES (19 items from admin_menu_items table)
// ============================================================

// Associate section
$router->get('/admin/associate-extensions', 'App\\Http\\Controllers\\Admin\\AssociateExtensionController@index');
$router->get('/admin/associate-extensions/show/{id}', 'App\\Http\\Controllers\\Admin\\AssociateExtensionController@show');
$router->post('/admin/associate-extensions/update-points/{id}', 'App\\Http\\Controllers\\Admin\\AssociateExtensionController@updatePoints');

// Marketing section (redirect stubs to MarketingController)
$router->get('/admin/marketing/strategies', 'App\\Http\\Controllers\\Admin\\MarketingController@strategies');
$router->get('/admin/marketing/marketplace', 'App\\Http\\Controllers\\Admin\\MarketingController@marketplace');

// Admin Marketing Controller (Admin\MarketingController)
$router->get('/admin/marketing/manage/strategies', 'App\\Http\\Controllers\\Admin\\MarketingController@strategies');
$router->get('/admin/marketing/manage/strategies/create', 'App\\Http\\Controllers\\Admin\\MarketingController@createStrategy');
$router->post('/admin/marketing/manage/strategies/store', 'App\\Http\\Controllers\\Admin\\MarketingController@storeStrategy');
$router->get('/admin/marketing/manage/strategies/edit/{id}', 'App\\Http\\Controllers\\Admin\\MarketingController@editStrategy');
$router->post('/admin/marketing/manage/strategies/update/{id}', 'App\\Http\\Controllers\\Admin\\MarketingController@updateStrategy');
$router->post('/admin/marketing/manage/strategies/toggle/{id}', 'App\\Http\\Controllers\\Admin\\MarketingController@toggleStrategy');
$router->get('/admin/marketing/manage/marketplace', 'App\\Http\\Controllers\\Admin\\MarketingController@marketplace');
$router->post('/admin/marketing/manage/marketplace/store', 'App\\Http\\Controllers\\Admin\\MarketingController@storeMarketplace');

// Commission section (redirect stubs to CommissionAdminController)
$router->get('/admin/commission/agent-rates', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@agentRates');
$router->get('/admin/commission/associate/structure', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@associateStructure');
$router->get('/admin/commission/associate/calculations', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@associateCalculations');
$router->get('/admin/commission/bonuses', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@bonuses');
$router->get('/admin/commission/mlm/levels', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@mlmLevels');
$router->get('/admin/commission/mlm/records', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@mlmRecords');
$router->get('/admin/commission/mlm/analytics', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@mlmAnalytics');
$router->get('/admin/commission/mlm/ledger/legacy', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@mlmLedgerLegacy');
$router->get('/admin/commission/calculations/all', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@commissionCalculations');
$router->get('/admin/commission/revenue/daily', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@revenueDaily');
$router->get('/admin/commission/telecaller/rules', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@telecallerRules');
$router->get('/admin/commission/telecaller/commissions', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@telecallerCommissions');

// Commission POST routes (form submissions)
$router->post('/admin/commission/agent-rates/store', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@agentRateStore');
$router->post('/admin/commission/agent-rates/delete/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@agentRateDelete');
$router->post('/admin/commission/associate/structure/store', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@associateStructureStore');
$router->post('/admin/commission/associate/structure/delete/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@associateStructureDelete');
$router->post('/admin/commission/associate/calc-status/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@associateCalcStatus');
$router->post('/admin/commission/bonuses/store', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@bonusStore');
$router->post('/admin/commission/bonuses/delete/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@bonusDelete');
$router->post('/admin/commission/mlm/levels/store', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@mlmLevelStore');
$router->post('/admin/commission/mlm/levels/delete/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@mlmLevelDelete');
$router->post('/admin/commission/mlm/records/status/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@mlmRecordStatus');
$router->post('/admin/commission/revenue/daily/store', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@revenueDailyStore');
$router->post('/admin/commission/revenue/daily/delete/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@revenueDailyDelete');
$router->post('/admin/commission/telecaller/rules/store', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@telecallerRuleStore');
$router->post('/admin/commission/telecaller/rules/toggle/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@telecallerRuleToggle');
$router->post('/admin/commission/telecaller/rules/delete/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@telecallerRuleDelete');
$router->post('/admin/commission/telecaller/commissions/approve/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@telecallerCommissionApprove');
$router->post('/admin/commission/telecaller/commissions/pay/{id}', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@telecallerCommissionPay');
// Commission calculate/payout/action (form actions without methods — route to CommissionController)
$router->post('/admin/commission/calculate', 'App\\Http\\Controllers\\Admin\\CommissionController@processCalculation');
$router->post('/admin/commission/payout', 'App\\Http\\Controllers\\Admin\\CommissionController@processPayout');
$router->post('/admin/commission/action', 'App\\Http\\Controllers\\Admin\\CommissionController@processApproval');
$router->post('/admin/commissions/processPayout', 'App\\Http\\Controllers\\Admin\\CommissionController@processPayout');

// Commission Reconciliation (daily audit + TDS config)
$router->get('/admin/commission/reconciliation', 'App\\Http\\Controllers\\Admin\\CommissionAdminController@reconciliation');

// Commission Plans (CRUD + versioning + simulator)
$router->get('/admin/commission-plans',                        'App\\Http\\Controllers\\Admin\\CommissionPlanController@index');
$router->get('/admin/commission-plans/create',                 'App\\Http\\Controllers\\Admin\\CommissionPlanController@create');
$router->post('/admin/commission-plans/store',                 'App\\Http\\Controllers\\Admin\\CommissionPlanController@store');
$router->get('/admin/commission-plans/edit/{id}',              'App\\Http\\Controllers\\Admin\\CommissionPlanController@edit');
$router->post('/admin/commission-plans/update/{id}',           'App\\Http\\Controllers\\Admin\\CommissionPlanController@update');
$router->post('/admin/commission-plans/clone/{id}',            'App\\Http\\Controllers\\Admin\\CommissionPlanController@cloneVersion');
$router->post('/admin/commission-plans/activate/{id}',         'App\\Http\\Controllers\\Admin\\CommissionPlanController@activate');
$router->post('/admin/commission-plans/deactivate/{id}',       'App\\Http\\Controllers\\Admin\\CommissionPlanController@deactivate');
$router->post('/admin/commission-plans/delete/{id}',           'App\\Http\\Controllers\\Admin\\CommissionPlanController@delete');
$router->get('/admin/commission-plans/history',                'App\\Http\\Controllers\\Admin\\CommissionPlanController@history');
$router->get('/admin/commission-plans/compare',                'App\\Http\\Controllers\\Admin\\CommissionPlanController@compare');
$router->get('/admin/commission-plans/simulator',             'App\\Http\\Controllers\\Admin\\CommissionPlanController@simulator');
$router->post('/admin/commission-plans/simulator',            'App\\Http\\Controllers\\Admin\\CommissionPlanController@simulator');
$router->get('/admin/commission-plans/calculator',             'App\\Http\\Controllers\\Admin\\CommissionPlanController@calculator');
$router->get('/admin/commission-plans/ajax-levels',            'App\\Http\\Controllers\\Admin\\CommissionPlanController@getLevels');
$router->get('/admin/commission-plans/ajax-simulate',          'App\\Http\\Controllers\\Admin\\CommissionPlanController@ajaxSimulate');

// Commission Recalculations (retroactive recalc with approval workflow)
$router->get('/admin/commission/recalculations',                          'App\\Http\\Controllers\\Admin\\RecalculationController@index');
$router->get('/admin/commission/recalculations/{id}',                     'App\\Http\\Controllers\\Admin\\RecalculationController@detail');
$router->post('/admin/commission/recalculations/request',                 'App\\Http\\Controllers\\Admin\\RecalculationController@request');
$router->post('/admin/commission/recalculations/approve',                 'App\\Http\\Controllers\\Admin\\RecalculationController@approve');
$router->post('/admin/commission/recalculations/reject',                  'App\\Http\\Controllers\\Admin\\RecalculationController@reject');
$router->post('/admin/commission/recalculations/bulk-request',            'App\\Http\\Controllers\\Admin\\RecalculationController@bulkRequest');

// Payout Batch System
$router->get('/admin/payout-batches',                                        'App\\Http\\Controllers\\Admin\\PayoutBatchController@index');
$router->get('/admin/payout-batches/create',                                 'App\\Http\\Controllers\\Admin\\PayoutBatchController@create');
$router->post('/admin/payout-batches/store',                                 'App\\Http\\Controllers\\Admin\\PayoutBatchController@store');
$router->get('/admin/payout-batches/{id}',                                   'App\\Http\\Controllers\\Admin\\PayoutBatchController@detail');
$router->post('/admin/payout-batches/populate/{id}',                         'App\\Http\\Controllers\\Admin\\PayoutBatchController@populate');
$router->post('/admin/payout-batches/submit/{id}',                           'App\\Http\\Controllers\\Admin\\PayoutBatchController@submit');
$router->post('/admin/payout-batches/approve/{id}',                          'App\\Http\\Controllers\\Admin\\PayoutBatchController@approve');
$router->post('/admin/payout-batches/reject/{id}',                           'App\\Http\\Controllers\\Admin\\PayoutBatchController@reject');
$router->post('/admin/payout-batches/process/{id}',                          'App\\Http\\Controllers\\Admin\\PayoutBatchController@process');
$router->post('/admin/payout-batches/complete-entry',                        'App\\Http\\Controllers\\Admin\\PayoutBatchController@completeEntry');
$router->post('/admin/payout-batches/export/{id}',                           'App\\Http\\Controllers\\Admin\\PayoutBatchController@export');

// MLM section (redirect stubs to MlmRewardsController)
$router->get('/admin/mlm/rank-criteria', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@rankCriteria');
$router->get('/admin/mlm/upgrades', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@upgrades');
$router->get('/admin/mlm/withdrawals', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@withdrawals');
$router->get('/admin/mlm/rewards', 'App\\Http\\Controllers\\Admin\\MlmRewardsController@rewards');

// Settings section (API)
$router->get('/admin/api/integrations', 'App\\Http\\Controllers\\Admin\\AdminController@apiIntegrations');

// Legacy "under development" stub URLs -> redirect to real feature modules
$router->get('/admin/marketing-strategies', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/marketing-marketplace', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/agent-commission-rates', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/associate-commission-structure', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/associate-commission-calculations', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/commission-bonuses', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/mlm-commission-levels', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/mlm-commission-records', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/mlm-commission-analytics', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/daily-revenue', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/telecaller-commission-rules', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/telecaller-commissions', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/mlm-rank-criteria', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/mlm-upgrades', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/mlm-withdrawals', 'App\\Http\\Controllers\\Admin\\AdminController@stubRedirect');
$router->get('/admin/api/developers', 'App\\Http\\Controllers\\Admin\\ApiIntegrationController@developers');
$router->get('/admin/api/developers/create', 'App\\Http\\Controllers\\Admin\\ApiIntegrationController@developersCreate');
$router->post('/admin/api/developers/store', 'App\\Http\\Controllers\\Admin\\ApiIntegrationController@developersStore');


// ============================================================
// MISSING ADMIN ROUTES - NEWLY CREATED CONTROLLERS
// ============================================================

// Admin Reports (AdminReportsController)
$router->get('/admin/reports-new', 'App\\Http\\Controllers\\Admin\\AdminReportsController@index');
$router->get('/admin/reports-new/daily', 'App\\Http\\Controllers\\Admin\\AdminReportsController@dailyReport');
$router->get('/admin/reports-new/weekly', 'App\\Http\\Controllers\\Admin\\AdminReportsController@weeklyReport');
$router->get('/admin/reports-new/monthly', 'App\\Http\\Controllers\\Admin\\AdminReportsController@monthlyReport');
$router->get('/admin/reports-new/sales', 'App\\Http\\Controllers\\Admin\\AdminReportsController@salesReport');
$router->get('/admin/reports-new/leads', 'App\\Http\\Controllers\\Admin\\AdminReportsController@leadReport');
$router->get('/admin/reports-new/export', 'App\\Http\\Controllers\\Admin\\AdminReportsController@export');

// Admin Knowledge Base
$router->get('/admin/knowledge-base-new', 'App\\Http\\Controllers\\Admin\\KnowledgeBaseController@index');
$router->get('/admin/knowledge-base-new/create', 'App\\Http\\Controllers\\Admin\\KnowledgeBaseController@create');
$router->post('/admin/knowledge-base-new/store', 'App\\Http\\Controllers\\Admin\\KnowledgeBaseController@store');
$router->get('/admin/knowledge-base-new/{id}', 'App\\Http\\Controllers\\Admin\\KnowledgeBaseController@show');
$router->get('/admin/knowledge-base-new/{id}/edit', 'App\\Http\\Controllers\\Admin\\KnowledgeBaseController@edit');
$router->post('/admin/knowledge-base-new/{id}/update', 'App\\Http\\Controllers\\Admin\\KnowledgeBaseController@update');
$router->post('/admin/knowledge-base-new/{id}/delete', 'App\\Http\\Controllers\\Admin\\KnowledgeBaseController@delete');



// Admin Testimonials (Additional routes)
$router->get('/admin/testimonials-new', 'App\\Http\\Controllers\\Admin\\TestimonialController@index');
$router->get('/admin/testimonials-new/create', 'App\\Http\\Controllers\\Admin\\TestimonialController@create');
$router->post('/admin/testimonials-new/store', 'App\\Http\\Controllers\\Admin\\TestimonialController@store');
$router->get('/admin/testimonials-new/{id}', 'App\\Http\\Controllers\\Admin\\TestimonialController@show');
$router->get('/admin/testimonials-new/{id}/edit', 'App\\Http\\Controllers\\Admin\\TestimonialController@edit');
$router->post('/admin/testimonials-new/{id}/update', 'App\\Http\\Controllers\\Admin\\TestimonialController@update');
$router->post('/admin/testimonials-new/{id}/delete', 'App\\Http\\Controllers\\Admin\\TestimonialController@delete');

// ============================================================
// DEAL PIPELINE (Kanban Sales Pipeline)
// ============================================================
$router->get('/admin/deal-pipeline', 'App\\Http\\Controllers\\Admin\\DealPipelineController@index');
$router->get('/admin/deal-pipeline/create', 'App\\Http\\Controllers\\Admin\\DealPipelineController@create');
$router->post('/admin/deal-pipeline/store', 'App\\Http\\Controllers\\Admin\\DealPipelineController@store');
$router->get('/admin/deal-pipeline/{id}', 'App\\Http\\Controllers\\Admin\\DealPipelineController@show');
$router->post('/admin/deal-pipeline/{id}/move-stage', 'App\\Http\\Controllers\\Admin\\DealPipelineController@moveStage');
$router->post('/admin/deal-pipeline/{id}/update-probability', 'App\\Http\\Controllers\\Admin\\DealPipelineController@updateProbability');
$router->get('/admin/deal-pipeline/{id}/mark-won', 'App\\Http\\Controllers\\Admin\\DealPipelineController@markWon');
$router->get('/admin/deal-pipeline/{id}/mark-lost', 'App\\Http\\Controllers\\Admin\\DealPipelineController@markLost');
$router->get('/admin/deal-pipeline/{id}/timeline', 'App\\Http\\Controllers\\Admin\\DealPipelineController@timeline');

// ============================================================
// PROPERTY ALLOCATIONS
// ============================================================
$router->get('/admin/property-allocations', 'App\\Http\\Controllers\\Admin\\PropertyAllocationController@index');
$router->get('/admin/property-allocations/create', 'App\\Http\\Controllers\\Admin\\PropertyAllocationController@create');
$router->post('/admin/property-allocations/store', 'App\\Http\\Controllers\\Admin\\PropertyAllocationController@store');
$router->get('/admin/property-allocations/{id}', 'App\\Http\\Controllers\\Admin\\PropertyAllocationController@show');
$router->get('/admin/property-allocations/{id}/confirm', 'App\\Http\\Controllers\\Admin\\PropertyAllocationController@confirm');
$router->get('/admin/property-allocations/{id}/cancel', 'App\\Http\\Controllers\\Admin\\PropertyAllocationController@cancel');
$router->get('/admin/property-allocations/calendar', 'App\\Http\\Controllers\\Admin\\PropertyAllocationController@calendar');

// ============================================================
// UNIFIED REGISTRATION (MLM + Customer) - DUPLICATE REMOVED
// Routes already defined at line 724-725 using UnifiedRegisterController
// ============================================================

// ============================================================
// ADVANCED FEATURES (Social Login, OTP, etc.)
// ============================================================
$router->get('/api/advanced/social-auth-url', 'App\\Http\\Controllers\\AdvancedFeaturesController@getSocialAuthUrl');
$router->get('/api/advanced/social-callback', 'App\\Http\\Controllers\\AdvancedFeaturesController@handleSocialCallback');
$router->post('/api/advanced/send-otp', 'App\\Http\\Controllers\\AdvancedFeaturesController@sendOTP');
$router->post('/api/advanced/verify-otp', 'App\\Http\\Controllers\\AdvancedFeaturesController@verifyOTP');
$router->post('/api/advanced/progressive-register', 'App\\Http\\Controllers\\AdvancedFeaturesController@progressiveRegister');
$router->post('/api/advanced/webhook/campaign', 'App\\Http\\Controllers\\AdvancedFeaturesController@campaignWebhook');
$router->post('/api/advanced/track-campaign', 'App\\Http\\Controllers\\AdvancedFeaturesController@trackCampaignEngagement');

// ============================================================
// REPORTS ENGINE (Reports\ReportController)
// ============================================================
$router->get('/admin/reports-engine', 'App\\Http\\Controllers\\Reports\\ReportController@dashboard');
$router->get('/admin/reports-engine/generate', 'App\\Http\\Controllers\\Reports\\ReportController@generate');
$router->post('/admin/reports-engine/create', 'App\\Http\\Controllers\\Reports\\ReportController@create');
$router->get('/admin/reports-engine/scheduled', 'App\\Http\\Controllers\\Reports\\ReportController@scheduled');
$router->get('/admin/reports-engine/schedule', 'App\\Http\\Controllers\\Reports\\ReportController@schedule');
$router->post('/admin/reports-engine/store-schedule', 'App\\Http\\Controllers\\Reports\\ReportController@storeSchedule');
$router->get('/admin/reports-engine/sales', 'App\\Http\\Controllers\\Reports\\ReportController@sales');
$router->get('/admin/reports-engine/property', 'App\\Http\\Controllers\\Reports\\ReportController@property');
$router->get('/admin/reports-engine/associate', 'App\\Http\\Controllers\\Reports\\ReportController@associate');
$router->get('/admin/reports-engine/customer', 'App\\Http\\Controllers\\Reports\\ReportController@customer');
$router->get('/admin/reports-engine/financial', 'App\\Http\\Controllers\\Reports\\ReportController@financial');

// ============================================================
// CM DASHBOARD (Admin\CMDashboardController)
// ============================================================
$router->get('/admin/cm-dashboard', 'Admin\\CMDashboardController@index');
$router->get('/admin/cm-dashboard/team-analytics', 'Admin\\CMDashboardController@getTeamAnalytics');
$router->get('/admin/cm-dashboard/performance-metrics', 'Admin\\CMDashboardController@getPerformanceMetrics');

// ============================================================
// TEAM MANAGEMENT (TeamManagementController)
// ============================================================
// NOTE: /team (GET) is also registered at line ~79 as Front\PageController@team (public page)
// The TeamManagementController handles /team/* sub-routes below only
$router->post('/team/add', 'App\\Http\\Controllers\\TeamManagementController@addTeamMember');
$router->get('/team/member/{id}', 'App\\Http\\Controllers\\TeamManagementController@getTeamMember');
$router->post('/team/member/{id}', 'App\\Http\\Controllers\\TeamManagementController@updateTeamMember');
$router->delete('/team/member/{id}', 'App\\Http\\Controllers\\TeamManagementController@deleteTeamMember');
$router->post('/team/message', 'App\\Http\\Controllers\\TeamManagementController@sendTeamMessage');
$router->get('/team/messages', 'App\\Http\\Controllers\\TeamManagementController@getTeamMessages');

// ============================================================
// CRON JOBS (System\CronController)
// ============================================================
$router->get('/system/cron/daily', 'App\\Http\\Controllers\\System\\CronController@daily');
$router->get('/system/cron/hourly', 'App\\Http\\Controllers\\System\\CronController@hourly');
$router->get('/system/cron/weekly', 'App\\Http\\Controllers\\System\\CronController@weekly');
$router->get('/cron/emi-auto-payment', function () {
    // Key auth handled inside the script
    require dirname(__DIR__) . '/scripts/emi_auto_payment_cron.php';
});

// ============================================================
// LOCALIZATION API (LocalizationController)
// ============================================================
$router->get('/api/localization/current', 'App\\Http\\Controllers\\LocalizationController@getCurrentLocale');
$router->get('/api/localization/supported', 'App\\Http\\Controllers\\LocalizationController@getSupportedLocales');
$router->get('/api/localization/translations', 'App\\Http\\Controllers\\LocalizationController@getTranslations');
$router->post('/api/localization/set-locale', 'App\\Http\\Controllers\\LocalizationController@setLocale');
$router->post('/api/localization/add-translation', 'App\\Http\\Controllers\\LocalizationController@addTranslation');
$router->post('/api/localization/update-translation', 'App\\Http\\Controllers\\LocalizationController@updateTranslation');
$router->post('/api/localization/delete-translation', 'App\\Http\\Controllers\\LocalizationController@deleteTranslation');
$router->get('/api/localization/export', 'App\\Http\\Controllers\\LocalizationController@exportTranslations');
$router->post('/api/localization/import', 'App\\Http\\Controllers\\LocalizationController@importTranslations');
$router->post('/api/localization/add-locale', 'App\\Http\\Controllers\\LocalizationController@addLocale');
$router->get('/admin/localization', 'App\\Http\\Controllers\\LocalizationController@management');
$router->get('/admin/localization/editor', 'App\\Http\\Controllers\\LocalizationController@editor');

// ============================================================
// MEDIA LIBRARY (Media\MediaLibraryController)
// ============================================================
$router->get('/admin/media-library', 'App\\Http\\Controllers\\Media\\MediaLibraryController@index');
$router->get('/admin/media-library/upload', 'App\\Http\\Controllers\\Media\\MediaLibraryController@upload');
$router->post('/admin/media-library/upload', 'App\\Http\\Controllers\\Media\\MediaLibraryController@handleUpload');
$router->get('/admin/media-library/details/{id}', 'App\\Http\\Controllers\\Media\\MediaLibraryController@details');
$router->post('/admin/media-library/update/{id}', 'App\\Http\\Controllers\\Media\\MediaLibraryController@update');
$router->post('/admin/media-library/delete/{id}', 'App\\Http\\Controllers\\Media\\MediaLibraryController@delete');
$router->get('/admin/media-library/files', 'App\\Http\\Controllers\\Media\\MediaLibraryController@getMediaFiles');
$router->get('/admin/media-library/file/{id}', 'App\\Http\\Controllers\\Media\\MediaLibraryController@getMediaFile');
$router->get('/admin/media-library/categories', 'App\\Http\\Controllers\\Media\\MediaLibraryController@getCategories');
$router->get('/admin/media-library/stats', 'App\\Http\\Controllers\\Media\\MediaLibraryController@getMediaStats');
$router->post('/admin/media-library/upload-file', 'App\\Http\\Controllers\\Media\\MediaLibraryController@uploadFile');
$router->post('/admin/media-library/update-file/{id}', 'App\\Http\\Controllers\\Media\\MediaLibraryController@updateFile');
$router->post('/admin/media-library/delete-file/{id}', 'App\\Http\\Controllers\\Media\\MediaLibraryController@deleteFile');
$router->get('/admin/media-library/download/{id}', 'App\\Http\\Controllers\\Media\\MediaLibraryController@download');
$router->get('/admin/media-library/preview/{id}', 'App\\Http\\Controllers\\Media\\MediaLibraryController@preview');
$router->post('/admin/media-library/thumbnail/{id}', 'App\\Http\\Controllers\\Media\\MediaLibraryController@createThumbnail');

// ============================================================
// MARKETING AUTOMATION (Marketing\MarketingAutomationController)
// ============================================================
$router->get('/admin/marketing-automation', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@dashboard');
$router->get('/admin/marketing-automation/leads', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@leads');
$router->get('/admin/marketing-automation/leads/{id}', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@leadDetails');
$router->get('/admin/marketing-automation/capture-lead', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@captureLead');
$router->post('/admin/marketing-automation/capture-lead', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@handleCaptureLead');
$router->post('/admin/marketing-automation/leads/{id}/status', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@updateLeadStatus');
$router->post('/admin/marketing-automation/leads/{id}/score', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@assignLeadScore');
$router->get('/admin/marketing-automation/campaigns', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@campaigns');
$router->get('/admin/marketing-automation/campaigns/create', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@createCampaign');
$router->post('/admin/marketing-automation/campaigns/create', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@handleCreateCampaign');
$router->get('/admin/marketing-automation/api/leads', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@getLeads');
$router->get('/admin/marketing-automation/api/lead/{id}', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@getLead');
$router->get('/admin/marketing-automation/api/campaigns', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@getCampaigns');
$router->get('/admin/marketing-automation/api/dashboard', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@getDashboardData');
$router->get('/admin/marketing-automation/api/lead-stats', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@getLeadStats');
$router->post('/admin/marketing-automation/api/capture-lead', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@captureLeadAjax');
$router->post('/admin/marketing-automation/api/leads/{id}/status', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@updateLeadStatusAjax');
$router->post('/admin/marketing-automation/api/leads/{id}/score', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@assignLeadScoreAjax');
$router->post('/admin/marketing-automation/api/campaigns/create', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@createCampaignAjax');
$router->post('/admin/marketing-automation/api/automation/trigger', 'App\\Http\\Controllers\\Marketing\\MarketingAutomationController@triggerAutomation');

// ============================================================
// PROPERTY WORKFLOW (Property\PropertyController)
// ============================================================
$router->get('/properties-workflow', 'App\\Http\\Controllers\\Property\\PropertyController@index');
$router->any('/properties-workflow/sell', 'App\\Http\\Controllers\\Property\\PropertyController@sell');
$router->get('/properties-workflow/{id}', 'App\\Http\\Controllers\\Property\\PropertyController@show');
$router->any('/properties-workflow/{id}/buy', 'App\\Http\\Controllers\\Property\\PropertyController@buy');
$router->any('/properties-workflow/{id}/schedule-visit', 'App\\Http\\Controllers\\Property\\PropertyController@scheduleVisit');

// ============================================================
// PAYMENT GATEWAY (Payment\PaymentGatewayController)
// ============================================================
$router->any('/payment/process', 'App\\Http\\Controllers\\Payment\\PaymentGatewayController@processPayment');
$router->get('/payment/success', 'App\\Http\\Controllers\\Payment\\PaymentGatewayController@paymentSuccess');
$router->get('/payment/failed', 'App\\Http\\Controllers\\Payment\\PaymentGatewayController@paymentFailed');
$router->get('/payment/history', 'App\\Http\\Controllers\\Payment\\PaymentGatewayController@paymentHistory');

// ============================================================
// MEDIA CENTER (Communication\MediaController)
// ============================================================
$router->get('/admin/media-center', 'App\\Http\\Controllers\\Communication\\MediaController@index');
$router->get('/admin/media-center/upload', 'App\\Http\\Controllers\\Communication\\MediaController@upload');
$router->get('/admin/media-center/media/{id}', 'App\\Http\\Controllers\\Communication\\MediaController@getMedia');
$router->post('/admin/media-center/media/{id}', 'App\\Http\\Controllers\\Communication\\MediaController@updateMedia');
$router->delete('/admin/media-center/media/{id}', 'App\\Http\\Controllers\\Communication\\MediaController@deleteMedia');
$router->get('/admin/media-center/search', 'App\\Http\\Controllers\\Communication\\MediaController@search');
$router->get('/admin/media-center/gallery/{id}', 'App\\Http\\Controllers\\Communication\\MediaController@getGallery');
$router->post('/admin/media-center/gallery', 'App\\Http\\Controllers\\Communication\\MediaController@createGallery');
$router->get('/admin/media-center/stats', 'App\\Http\\Controllers\\Communication\\MediaController@getStats');

// ============================================================
// INVOICES (Admin\InvoiceController)
// ============================================================
$router->get('/admin/invoices/manage', 'App\\Http\\Controllers\\Admin\\InvoiceController@index');
$router->get('/admin/invoices/manage/create', 'App\\Http\\Controllers\\Admin\\InvoiceController@create');
$router->post('/admin/invoices/manage/store', 'App\\Http\\Controllers\\Admin\\InvoiceController@store');
$router->get('/admin/invoices/manage/{id}', 'App\\Http\\Controllers\\Admin\\InvoiceController@show');
$router->get('/admin/invoices/manage/{id}/edit', 'App\\Http\\Controllers\\Admin\\InvoiceController@edit');
$router->post('/admin/invoices/manage/{id}/update', 'App\\Http\\Controllers\\Admin\\InvoiceController@update');
$router->post('/admin/invoices/manage/{id}/delete', 'App\\Http\\Controllers\\Admin\\InvoiceController@delete');
$router->post('/admin/invoices/manage/{id}/mark-paid', 'App\\Http\\Controllers\\Admin\\InvoiceController@markAsPaid');
$router->post('/admin/invoices/manage/{id}/send', 'App\\Http\\Controllers\\Admin\\InvoiceController@sendInvoice');
$router->get('/admin/invoices/manage/{id}/pdf', 'App\\Http\\Controllers\\Admin\\InvoiceController@downloadPdf');
$router->get('/admin/invoices/booking/{bookingId}', 'App\\Http\\Controllers\\Admin\\InvoiceController@createFromBooking');

// ============================================================
// API LEADS (Api\ApiLeadController)
// ============================================================
$router->get('/api/leads', 'App\\Http\\Controllers\\Api\\ApiLeadController@index');
$router->post('/api/leads', 'App\\Http\\Controllers\\Api\\ApiLeadController@store');
$router->get('/api/leads/{id}', 'App\\Http\\Controllers\\Api\\ApiLeadController@show');
$router->put('/api/leads/{id}', 'App\\Http\\Controllers\\Api\\ApiLeadController@update');
$router->delete('/api/leads/{id}', 'App\\Http\\Controllers\\Api\\ApiLeadController@destroy');
$router->post('/api/leads/{id}/note', 'App\\Http\\Controllers\\Api\\ApiLeadController@addNote');
$router->post('/api/leads/{id}/upload', 'App\\Http\\Controllers\\Api\\ApiLeadController@uploadFile');
$router->put('/api/leads/{id}/status', 'App\\Http\\Controllers\\Api\\ApiLeadController@updateStatus');
$router->put('/api/leads/{id}/assign', 'App\\Http\\Controllers\\Api\\ApiLeadController@assign');
$router->post('/api/leads/bulk-assign', 'App\\Http\\Controllers\\Api\\ApiLeadController@bulkAssign');
$router->put('/api/leads/tasks/{id}', function ($id) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success' => false]); exit; }
    $input = json_decode(file_get_contents('php://input'), true);
    $status = $input['status'] ?? 'pending';
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE crm_tasks SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
});
$router->get('/admin/api/lead-file-extraction/{id}', function ($id) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_id'])) { echo json_encode(['success' => false]); exit; }
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM lead_file_extractions WHERE id = ?");
        $stmt->execute([$id]);
        $extraction = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$extraction) { echo json_encode(['success' => false, 'message' => 'Not found']); exit; }
        echo json_encode(['success' => true, 'extraction' => $extraction]);
    } catch (\Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    exit;
});
$router->get('/api/leads/stats', 'App\\Http\\Controllers\\Api\\ApiLeadController@getStats');
$router->get('/api/leads/data/lookup', 'App\\Http\\Controllers\\Api\\ApiLeadController@getLookupData');

// ============================================================
// API KYC (Api\KYCController)
// ============================================================
$router->post('/api/kyc/verify-pan', 'App\\Http\\Controllers\\Api\\KYCController@verifyPAN');
$router->post('/api/kyc/verify-aadhaar', 'App\\Http\\Controllers\\Api\\KYCController@verifyAadhaar');
$router->post('/api/kyc/aadhaar/send-otp', 'App\\Http\\Controllers\\Api\\KYCController@sendAadhaarOtp');
$router->post('/api/kyc/aadhaar/verify-otp', 'App\\Http\\Controllers\\Api\\KYCController@verifyAadhaarOtp');
$router->get('/api/kyc/status', 'App\\Http\\Controllers\\Api\\KYCController@getStatus');

// ============================================================
// API SEO (Api\SeoController)
// ============================================================
$router->get('/api/seo/metadata', 'App\\Http\\Controllers\\Api\\SeoController@getMetadata');
$router->post('/api/seo/update', 'App\\Http\\Controllers\\Api\\SeoController@update');

// ============================================================
// API COMMUNICATION (Api\CommunicationController)
// ============================================================
$router->get('/api/communication/whatsapp-webhook', 'App\\Http\\Controllers\\Api\\CommunicationController@whatsappWebhook');
$router->post('/api/communication/whatsapp-webhook', 'App\\Http\\Controllers\\Api\\CommunicationController@whatsappWebhook');
$router->get('/api/communication/telegram-webhook', 'App\\Http\\Controllers\\Api\\CommunicationController@telegramWebhook');
$router->post('/api/communication/telegram-webhook', 'App\\Http\\Controllers\\Api\\CommunicationController@telegramWebhook');
$router->post('/api/communication/sms-webhook', 'App\\Http\\Controllers\\Api\\CommunicationController@smsWebhook');
$router->post('/api/communication/send-email', 'App\\Http\\Controllers\\Api\\CommunicationController@sendEmail');
$router->post('/api/communication/send-whatsapp', 'App\\Http\\Controllers\\Api\\CommunicationController@sendWhatsApp');
$router->post('/api/communication/send-telegram', 'App\\Http\\Controllers\\Api\\CommunicationController@sendTelegram');
$router->post('/api/communication/send-sms', 'App\\Http\\Controllers\\Api\\CommunicationController@sendSMS');
$router->get('/api/communication/status', 'App\\Http\\Controllers\\Api\\CommunicationController@channelStatus');

// ============================================================
// API REVIEWS (Api\ReviewController)
// ============================================================
$router->get('/api/reviews', 'App\\Http\\Controllers\\Api\\ReviewController@index');
$router->post('/api/reviews', 'App\\Http\\Controllers\\Api\\ReviewController@store');

// ============================================================
// API FOLLOWUPS (Api\FollowupController)
// ============================================================
$router->post('/api/followups/run', 'App\\Http\\Controllers\\Api\\FollowupController@run');

// ============================================================
// API SMS (SMSController)
// ============================================================
$router->post('/api/sms/send-otp', 'App\\Http\\Controllers\\SMSController@sendOTP');
$router->post('/api/sms/verify-otp', 'App\\Http\\Controllers\\SMSController@verifyOTP');

// ============================================================
// MISSING SIDEBAR ROUTES
// ============================================================
$router->get('/admin/customers', 'App\\Http\\Controllers\\Admin\\CRMController@index');
$router->get('/admin/hrm/employees', function () {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome') . '/admin/hr/users');
    exit;
});
$router->get('/admin/mlm/associates', 'App\\Http\\Controllers\\Admin\\MLMController@users');
$router->get('/admin/agents', 'App\\Http\\Controllers\\Admin\\AgentController@index');

// ============================================================
// SALARIED AGENTS — HR Salary Structure Management
// ============================================================
$router->get('/admin/agents/salaried',             'App\\Http\\Controllers\\Admin\\SalariedAgentController@index');
$router->get('/admin/agents/salaried/create',       'App\\Http\\Controllers\\Admin\\SalariedAgentController@create');
$router->post('/admin/agents/salaried/store',       'App\\Http\\Controllers\\Admin\\SalariedAgentController@store');
$router->get('/admin/agents/salaried/{id}',         'App\\Http\\Controllers\\Admin\\SalariedAgentController@show');
$router->get('/admin/async', 'App\\Http\\Controllers\\Async\\AsyncController@dashboard');
$router->get('/admin/async/tasks', 'App\\Http\\Controllers\\Async\\AsyncController@tasks');
$router->get('/admin/async/create', 'App\\Http\\Controllers\\Async\\AsyncController@createTask');
$router->post('/admin/async/create', 'App\\Http\\Controllers\\Async\\AsyncController@handleCreateTask');
$router->get('/api/work-distribution/analytics', 'App\\Http\\Controllers\\Employee\\WorkDistributionController@getDistributionAnalytics');

// ============================================================
// COLONY LAND COSTING (Admin\ColonyLandCostingController)
// ============================================================
$router->get('/admin/colony-costing',                    'App\\Http\\Controllers\\Admin\\ColonyLandCostingController@index');
$router->get('/admin/colony-costing/create/{id}',        'App\\Http\\Controllers\\Admin\\ColonyLandCostingController@create');
$router->post('/admin/colony-costing/store',             'App\\Http\\Controllers\\Admin\\ColonyLandCostingController@store');
$router->get('/admin/colony-costing/{id}',               'App\\Http\\Controllers\\Admin\\ColonyLandCostingController@show');
$router->post('/admin/colony-costing/calculate',         'App\\Http\\Controllers\\Admin\\ColonyLandCostingController@calculate');
$router->post('/admin/colony-costing/approve/{id}',      'App\\Http\\Controllers\\Admin\\ColonyLandCostingController@approve');

// ============================================================
// POSSESSION HANDOVER (Admin\PossessionController)
// ============================================================
$router->get('/admin/possession', 'App\\Http\\Controllers\\Admin\\PossessionController@index');
$router->get('/admin/possession/dashboard', 'App\\Http\\Controllers\\Admin\\PossessionController@dashboard');
$router->get('/admin/possession/show/{id}', 'App\\Http\\Controllers\\Admin\\PossessionController@show');
$router->get('/admin/possession/checklist/{id}', 'App\\Http\\Controllers\\Admin\\PossessionController@checklist');
$router->post('/admin/possession/checklist/{id}/add', 'App\\Http\\Controllers\\Admin\\PossessionController@addChecklistItem');
$router->post('/admin/possession/checklist/{id}/complete', 'App\\Http\\Controllers\\Admin\\PossessionController@completeChecklistItem');
$router->post('/admin/possession/{id}/schedule', 'App\\Http\\Controllers\\Admin\\PossessionController@scheduleHandover');
$router->post('/admin/possession/{id}/handover', 'App\\Http\\Controllers\\Admin\\PossessionController@markHandedOver');
$router->get('/admin/possession/letter/{id}', 'App\\Http\\Controllers\\Admin\\PossessionController@generateLetter');
$router->get('/admin/possession/defects/{id}', 'App\\Http\\Controllers\\Admin\\PossessionController@defectReports');
$router->post('/admin/possession/defects/{id}/report', 'App\\Http\\Controllers\\Admin\\PossessionController@reportDefect');
$router->post('/admin/possession/defects/resolve/{defectId}', 'App\\Http\\Controllers\\Admin\\PossessionController@resolveDefect');

// ============================================================
// REGISTRY WORKFLOW (Admin\RegistryController)
// ============================================================
$router->get('/admin/registry', 'App\\Http\\Controllers\\Admin\\RegistryController@index');
$router->get('/admin/registry/history/{bookingId}', 'App\\Http\\Controllers\\Admin\\RegistryController@history');
$router->get('/admin/registry/show/{id}', 'App\\Http\\Controllers\\Admin\\RegistryController@show');
$router->get('/admin/registry/certificate/{id}', 'App\\Http\\Controllers\\Admin\\RegistryController@generateCertificate');
$router->post('/admin/registry/{id}/documents', 'App\\Http\\Controllers\\Admin\\RegistryController@updateDocuments');
$router->post('/admin/registry/{id}/stamp-duty', 'App\\Http\\Controllers\\Admin\\RegistryController@updateStampDuty');
$router->post('/admin/registry/{id}/appointment', 'App\\Http\\Controllers\\Admin\\RegistryController@scheduleAppointment');
$router->post('/admin/registry/{id}/register', 'App\\Http\\Controllers\\Admin\\RegistryController@markRegistered');
$router->post('/admin/registry/{id}/mutation', 'App\\Http\\Controllers\\Admin\\RegistryController@updateMutation');

// ============================================================
// WHATSAPP CONFIG (Admin\WhatsAppConfigController)
// ============================================================
$router->get('/admin/whatsapp/settings', 'App\\Http\\Controllers\\Admin\\WhatsAppConfigController@settings');
$router->post('/admin/whatsapp/settings', 'App\\Http\\Controllers\\Admin\\WhatsAppConfigController@saveSettings');
$router->post('/admin/whatsapp/test', 'App\\Http\\Controllers\\Admin\\WhatsAppConfigController@testMessage');
$router->get('/admin/whatsapp/templates', 'App\\Http\\Controllers\\Admin\\WhatsAppConfigController@templates');
$router->post('/admin/whatsapp/templates/sync', 'App\\Http\\Controllers\\Admin\\WhatsAppConfigController@syncTemplates');

// ============================================================
// UNROUTED CONTROLLER INTEGRATION (Batch - June 2026)
// ============================================================

// --- Analytics\ReportController ---
$router->get('/admin/analytics/reports', 'App\\Http\\Controllers\\Reports\\ReportController@dashboard');
$router->get('/admin/analytics/reports/sales', 'App\\Http\\Controllers\\Reports\\ReportController@sales');
$router->get('/admin/analytics/reports/properties', 'App\\Http\\Controllers\\Reports\\ReportController@property');
$router->get('/admin/analytics/reports/user-activity', 'App\\Http\\Controllers\\Reports\\ReportController@userActivity');

// --- Business\AssociateController ---
$router->get('/admin/business/associates', 'Business\\AssociateController@index');
$router->get('/admin/business/associates/show/{id}', 'Business\\AssociateController@show');
$router->get('/admin/business/associates/create', 'Business\\AssociateController@create');
$router->post('/admin/business/associates/store', 'Business\\AssociateController@store');
$router->get('/admin/business/associates/edit/{id}', 'Business\\AssociateController@edit');
$router->post('/admin/business/associates/update/{id}', 'Business\\AssociateController@update');
$router->post('/admin/business/associates/delete/{id}', 'Business\\AssociateController@destroy');
$router->get('/admin/business/associates/performance', 'Business\\AssociateController@performanceReport');
$router->post('/admin/business/associates/update-commission', 'Business\\AssociateController@updateCommissionRate');
$router->get('/admin/business/associates/top-performers', 'Business\\AssociateController@getTopPerformers');
$router->post('/admin/business/associates/export', 'Business\\AssociateController@exportAssociates');
$router->get('/admin/business/associates/search', 'Business\\AssociateController@searchAssociates');
$router->post('/admin/business/associates/activate/{id}', 'Business\\AssociateController@activate');
$router->post('/admin/business/associates/deactivate/{id}', 'Business\\AssociateController@deactivate');

// --- PerformanceController (root namespace) ---
$router->get('/admin/system-perf', 'App\\Http\\Controllers\\PerformanceController@dashboard');
$router->get('/admin/system-perf/metrics', 'App\\Http\\Controllers\\PerformanceController@getMetrics');
$router->get('/admin/system-perf/system', 'App\\Http\\Controllers\\PerformanceController@getSystemPerformance');
$router->get('/admin/system-perf/database', 'App\\Http\\Controllers\\PerformanceController@getDatabasePerformance');
$router->get('/admin/system-perf/cache', 'App\\Http\\Controllers\\PerformanceController@getCachePerformance');
$router->post('/admin/system-perf/optimize', 'App\\Http\\Controllers\\PerformanceController@optimize');
$router->post('/admin/system-perf/clear-cache', 'App\\Http\\Controllers\\PerformanceController@clearCache');
$router->get('/admin/system-perf/report', 'App\\Http\\Controllers\\PerformanceController@generateReport');
$router->get('/admin/system-perf/alerts', 'App\\Http\\Controllers\\PerformanceController@getAlerts');
$router->get('/admin/system-perf/monitor', 'App\\Http\\Controllers\\PerformanceController@monitor');
$router->get('/admin/system-perf/trends', 'App\\Http\\Controllers\\PerformanceController@getTrends');
$router->post('/admin/system-perf/threshold', 'App\\Http\\Controllers\\PerformanceController@setThreshold');
$router->get('/admin/system-perf/settings', 'App\\Http\\Controllers\\PerformanceController@getSettings');
$router->post('/admin/system-perf/settings/update', 'App\\Http\\Controllers\\PerformanceController@updateSettings');

// --- SaaS\ProfessionalToolsController ---
$router->get('/saas/tools/inventory', 'SaaS\\ProfessionalToolsController@inventory');
$router->get('/saas/tools/workflow', 'SaaS\\ProfessionalToolsController@workflow');
$router->get('/saas/tools/expenses', 'SaaS\\ProfessionalToolsController@expenses');
$router->get('/saas/tools/labor', 'SaaS\\ProfessionalToolsController@labor');
$router->get('/saas/tools/whatsapp', 'SaaS\\ProfessionalToolsController@whatsapp');
$router->get('/saas/tools/referrals', 'SaaS\\ProfessionalToolsController@referrals');
$router->get('/saas/tools/documents', 'SaaS\\ProfessionalToolsController@documents');

// --- User\UserController ---
$router->get('/user-management', 'User\\UserController@dashboard');
$router->get('/user-management/users', 'User\\UserController@index');
$router->get('/user-management/users/create', 'User\\UserController@create');
$router->post('/user-management/users/store', 'User\\UserController@store');
$router->get('/user-management/users/edit/{id}', 'User\\UserController@edit');
$router->post('/user-management/users/update/{id}', 'User\\UserController@update');
$router->get('/user-management/users/show/{id}', 'User\\UserController@show');
$router->post('/user-management/users/delete/{id}', 'User\\UserController@delete');
$router->get('/user-management/users/profile/{id}', 'User\\UserController@profile');
$router->post('/user-management/users/profile/{id}', 'User\\UserController@updateProfile');
$router->get('/user-management/users/change-password/{id}', 'User\\UserController@changePassword');
$router->post('/user-management/users/update-password/{id}', 'User\\UserController@updatePassword');
$router->post('/user-management/users/update-status/{id}', 'User\\UserController@updateStatus');
$router->get('/user-management/users/role/{role}', 'User\\UserController@byRole');

// ============================================================
// NOTIFICATION MANAGEMENT (Controllers\NotificationController)
// ============================================================
$router->get('/admin/notifications/manage', 'App\\Http\\Controllers\\NotificationController@index');
$router->get('/admin/notifications/manage/templates', 'App\\Http\\Controllers\\NotificationController@templates');
$router->get('/admin/notifications/manage/templates/create', 'App\\Http\\Controllers\\NotificationController@createTemplate');
$router->get('/admin/notifications/manage/templates/edit/{id}', 'App\\Http\\Controllers\\NotificationController@editTemplate');
$router->get('/admin/notifications/manage/email-logs', 'App\\Http\\Controllers\\NotificationController@emailLogs');
$router->get('/admin/notifications/manage/sms-logs', 'App\\Http\\Controllers\\NotificationController@smsLogs');
$router->get('/admin/notifications/manage/settings', 'App\\Http\\Controllers\\NotificationController@settings');
$router->get('/admin/notifications/manage/send-test', 'App\\Http\\Controllers\\NotificationController@sendTest');
$router->get('/admin/notifications/manage/preview', 'App\\Http\\Controllers\\NotificationController@preview');
$router->post('/admin/notifications/manage/api/get-notifications', 'App\\Http\\Controllers\\NotificationController@getNotifications');
$router->post('/admin/notifications/manage/api/mark-read', 'App\\Http\\Controllers\\NotificationController@markAsRead');
$router->post('/admin/notifications/manage/api/unread-count', 'App\\Http\\Controllers\\NotificationController@getUnreadCount');
$router->post('/admin/notifications/manage/api/get-popups', 'App\\Http\\Controllers\\NotificationController@getPopups');
$router->post('/admin/notifications/manage/api/dismiss-popup', 'App\\Http\\Controllers\\NotificationController@dismissPopup');
$router->post('/admin/notifications/manage/api/create', 'App\\Http\\Controllers\\NotificationController@createNotification');
$router->post('/admin/notifications/manage/api/create-popup', 'App\\Http\\Controllers\\NotificationController@createPopup');

// ============================================================
// EVENT MANAGEMENT (Controllers\EventController -- Event Bus)
// ============================================================
$router->get('/admin/events/dashboard', 'App\\Http\\Controllers\\EventController@dashboard');
$router->post('/admin/events/publish', 'App\\Http\\Controllers\\EventController@publish');
$router->post('/admin/events/subscribe', 'App\\Http\\Controllers\\EventController@subscribe');
$router->post('/admin/events/unsubscribe', 'App\\Http\\Controllers\\EventController@unsubscribe');
$router->get('/admin/events/subscriptions', 'App\\Http\\Controllers\\EventController@getSubscriptions');
$router->post('/admin/events/clear-subscriptions', 'App\\Http\\Controllers\\EventController@clearSubscriptions');
$router->get('/admin/events/statistics', 'App\\Http\\Controllers\\EventController@statistics');
$router->get('/admin/events/recent', 'App\\Http\\Controllers\\EventController@recentEvents');
$router->post('/admin/events/bulk-publish', 'App\\Http\\Controllers\\EventController@bulkPublish');

// ============================================================
// EVENTS CRUD (Admin\EventController)
// ============================================================
$router->get('/admin/events/list', 'App\\Http\\Controllers\\Admin\\EventController@index');
$router->get('/admin/events/list/create', 'App\\Http\\Controllers\\Admin\\EventController@create');
$router->post('/admin/events/list/store', 'App\\Http\\Controllers\\Admin\\EventController@store');
$router->get('/admin/events/list/{id}', 'App\\Http\\Controllers\\Admin\\EventController@show');
$router->get('/admin/events/list/{id}/edit', 'App\\Http\\Controllers\\Admin\\EventController@edit');
$router->post('/admin/events/list/{id}/update', 'App\\Http\\Controllers\\Admin\\EventController@update');
$router->post('/admin/events/list/{id}/destroy', 'App\\Http\\Controllers\\Admin\\EventController@destroy');

// ============================================================
// PUBLIC FRONTEND PAGES (New Front Controllers)
// ============================================================
$router->get('/faq-list', 'App\\Http\\Controllers\\Front\\FAQController@index');
$router->get('/faq-list/{id}', 'App\\Http\\Controllers\\Front\\FAQController@show');
$router->get('/event-calendar', 'App\\Http\\Controllers\\Front\\EventController@index');
$router->get('/event-calendar/{id}', 'App\\Http\\Controllers\\Front\\EventController@show');
$router->get('/photo-gallery', 'App\\Http\\Controllers\\Front\\GalleryController@index');
$router->get('/photo-gallery/{id}', 'App\\Http\\Controllers\\Front\\GalleryController@show');

// --- AI Assistant: /ai-assistant already routed to SmartAIController@assistantPage ---

// ============================================================
// ADMIN DIAGNOSTIC & ALERTS
// ============================================================
$router->get('/admin/diagnostic', 'Utility\\SystemDiagnosticController@index');
$router->get('/admin/alerts', 'Utility\\AlertController@index');
$router->post('/admin/alerts/create', 'Utility\\AlertController@createAlert');
$router->get('/admin/alerts/{id}', 'Utility\\AlertController@getAlert');
$router->post('/admin/alerts/{id}/update', 'Utility\\AlertController@updateAlert');
$router->post('/admin/alerts/{id}/delete', 'Utility\\AlertController@deleteAlert');
$router->post('/admin/alerts/{id}/acknowledge', 'Utility\\AlertController@acknowledgeAlert');
$router->post('/admin/alerts/{id}/dismiss', 'Utility\\AlertController@dismissAlert');
$router->get('/admin/alerts/escalations', 'Utility\\AlertController@getEscalations');
$router->post('/admin/alerts/escalations/process', 'Utility\\AlertController@processEscalations');
$router->get('/admin/alerts/stats', 'Utility\\AlertController@getStats');

// ============================================================
// SOCIAL MEDIA INTEGRATION
// ============================================================
$router->get('/admin/social-media', 'Admin\SocialMediaController@index');
$router->get('/admin/social-media/add', 'Admin\SocialMediaController@create');
$router->post('/admin/social-media/store', 'Admin\SocialMediaController@store');
$router->get('/admin/social-media/edit/{id}', 'Admin\SocialMediaController@edit');
$router->post('/admin/social-media/update/{id}', 'Admin\SocialMediaController@update');
$router->post('/admin/social-media/delete/{id}', 'Admin\SocialMediaController@delete');
$router->post('/admin/social-media/sync/{id}', 'Admin\SocialMediaController@syncLeads');

$router->get('/admin/social-media/leads', 'Admin\SocialMediaController@leads');
$router->get('/admin/social-media/leads/{accountId}', 'Admin\SocialMediaController@leads');
$router->post('/admin/social-media/leads/update-status', 'Admin\SocialMediaController@updateLeadStatus');

$router->get('/admin/social-media/campaigns', 'Admin\SocialMediaController@campaignsAll');
$router->get('/admin/social-media/campaigns/{accountId}', 'Admin\SocialMediaController@campaigns');
$router->post('/admin/social-media/campaigns/{accountId}/create', 'Admin\SocialMediaController@createCampaign');

$router->get('/admin/social-media/insights', 'Admin\SocialMediaController@insightsAll');
$router->get('/admin/social-media/insights/{accountId}', 'Admin\SocialMediaController@insights');

$router->get('/admin/social-media/settings', 'Admin\SocialMediaController@settings');
$router->post('/admin/social-media/settings/save', 'Admin\SocialMediaController@saveSettings');

// ============================================================
// SUSTAINABLE TECH / GREEN REAL ESTATE
// ============================================================
$router->get('/admin/sustainable', 'Admin\SustainableTechController@index');
$router->get('/admin/sustainable/certifications', 'Admin\SustainableTechController@certifications');
$router->get('/admin/sustainable/certification/form', 'Admin\SustainableTechController@certificationForm');
$router->get('/admin/sustainable/certification/form/{id}', 'Admin\SustainableTechController@certificationForm');
$router->post('/admin/sustainable/certification/save', 'Admin\SustainableTechController@certificationSave');
$router->post('/admin/sustainable/certification/delete/{id}', 'Admin\SustainableTechController@certificationDelete');
$router->get('/admin/sustainable/features', 'Admin\SustainableTechController@features');
$router->get('/admin/sustainable/feature/form', 'Admin\SustainableTechController@featureForm');
$router->get('/admin/sustainable/feature/form/{id}', 'Admin\SustainableTechController@featureForm');
$router->post('/admin/sustainable/feature/save', 'Admin\SustainableTechController@featureSave');
$router->post('/admin/sustainable/feature/delete/{id}', 'Admin\SustainableTechController@featureDelete');
$router->get('/admin/sustainable/audits', 'Admin\SustainableTechController@audits');
$router->get('/admin/sustainable/audit/form', 'Admin\SustainableTechController@auditForm');
$router->get('/admin/sustainable/audit/form/{id}', 'Admin\SustainableTechController@auditForm');
$router->post('/admin/sustainable/audit/save', 'Admin\SustainableTechController@auditSave');
$router->post('/admin/sustainable/audit/delete/{id}', 'Admin\SustainableTechController@auditDelete');
$router->get('/admin/sustainable/carbon', 'Admin\SustainableTechController@carbon');
$router->post('/admin/sustainable/carbon/save', 'Admin\SustainableTechController@carbonSave');
$router->post('/admin/sustainable/carbon/delete/{id}', 'Admin\SustainableTechController@carbonDelete');

// ============================================================
// IoT SMART PROPERTY
// ============================================================
$router->get('/admin/iot', 'Admin\IoTController@index');
$router->get('/admin/iot/catalog', 'Admin\IoTController@catalog');
$router->get('/admin/iot/catalog/form', 'Admin\IoTController@catalogForm');
$router->get('/admin/iot/catalog/form/{id}', 'Admin\IoTController@catalogForm');
$router->post('/admin/iot/catalog/save', 'Admin\IoTController@catalogSave');
$router->post('/admin/iot/catalog/delete/{id}', 'Admin\IoTController@catalogDelete');
$router->get('/admin/iot/devices', 'Admin\IoTController@devices');
$router->get('/admin/iot/device/form', 'Admin\IoTController@deviceForm');
$router->get('/admin/iot/device/form/{id}', 'Admin\IoTController@deviceForm');
$router->post('/admin/iot/device/save', 'Admin\IoTController@deviceSave');
$router->post('/admin/iot/device/delete/{id}', 'Admin\IoTController@deviceDelete');
$router->get('/admin/iot/device/{id}', 'Admin\IoTController@deviceDetail');
$router->post('/admin/iot/device/reading', 'Admin\IoTController@recordReading');
$router->get('/admin/iot/automations', 'Admin\IoTController@automations');
$router->get('/admin/iot/automation/form', 'Admin\IoTController@automationForm');
$router->get('/admin/iot/automation/form/{id}', 'Admin\IoTController@automationForm');
$router->post('/admin/iot/automation/save', 'Admin\IoTController@automationSave');
$router->post('/admin/iot/automation/delete/{id}', 'Admin\IoTController@automationDelete');

// ============================================================
// NEWLY ROUTED CONTROLLERS (from unrouted scan)
// ============================================================



// Property Workflow (Property\PropertyWorkflowController)
$router->get('/property-workflow', 'Property\\PropertyWorkflowController@index');
$router->get('/property-workflow/show/{id}', 'Property\\PropertyWorkflowController@show');
$router->get('/property-workflow/buy/{id}', 'Property\\PropertyWorkflowController@buy');
$router->get('/property-workflow/sell', 'Property\\PropertyWorkflowController@sell');
$router->get('/property-workflow/schedule-visit/{id}', 'Property\\PropertyWorkflowController@scheduleVisit');

// Admin Report Center (Reports\ReportController)
$router->get('/admin/report-center', 'App\\Http\\Controllers\\Reports\\ReportController@dashboard');

// Careers Management (Career\CareerController)
$router->get('/careers', 'Career\\CareerController@index');
$router->post('/careers/submit-application', 'Career\\CareerController@submitApplication');
// /careers/apply (GET + POST) is registered earlier at lines 192-193 -> Front\PageController
// (PageController wins because the router uses first-registered handler)
$router->get('/careers/thank-you', 'Career\\CareerController@thankYou');
$router->get('/admin/careers/manage', 'Career\\CareerController@applications');
$router->get('/admin/careers/manage/jobs', 'Career\\CareerController@getAvailablePositions');
$router->get('/admin/careers/manage/applications', 'Career\\CareerController@applications');
$router->get('/admin/careers/manage/applications/{id}', 'Career\\CareerController@applicationDetails');
$router->get('/admin/careers/manage/applications/{id}/resume', 'Career\\CareerController@downloadResume');
$router->post('/admin/careers/manage/applications/{id}/status', 'Career\\CareerController@updateStatus');
$router->post('/admin/careers/manage/applications/{id}/delete', 'Career\\CareerController@deleteApplication');
$router->get('/admin/careers/manage/stats', 'Career\\CareerController@getApplicationStats');
$router->post('/admin/careers/manage/export', 'Career\\CareerController@exportApplications');


// ============================================================
// PHASE 24-33 NEW FEATURES (admin pages)
// ============================================================
$router->get('/admin/features/registrations', 'Admin\\NewFeaturesController@progressiveRegistrations');
$router->get('/admin/features/payroll', 'Admin\\NewFeaturesController@payroll');
$router->get('/admin/features/resell', 'Admin\\NewFeaturesController@resellProperties');
$router->get('/admin/features/commissions', 'Admin\\NewFeaturesController@commissions');
$router->get('/admin/features/notifications', 'Admin\\NewFeaturesController@notifications');
$router->get('/admin/features/security', 'Admin\\NewFeaturesController@security');
$router->get('/admin/features/finance', 'Admin\\NewFeaturesController@finance');
$router->get('/admin/features/analytics', 'Admin\\NewFeaturesController@analyticsDashboard');
$router->get('/admin/features/realtime-analytics', 'Admin\\NewFeaturesController@realtimeAnalytics');
$router->get('/admin/audit-log', 'Admin\\AuditLogController@index');
$router->get('/admin/audit-log/{id}', 'Admin\\AuditLogController@detail');
$router->get('/admin/audit-log/user/{userId}', 'Admin\\AuditLogController@userTimeline');
$router->get('/admin/audit-log/entity', 'Admin\\AuditLogController@entityTimeline');
$router->get('/admin/audit-log/stats', 'Admin\\AuditLogController@stats');
$router->get('/api/v2/audit/log', 'Admin\\AuditLogController@api');
$router->get('/api/v2/notifications/poll', 'Api\\NotificationStreamController@poll');
$router->post('/api/v2/notifications/read', 'Api\\NotificationStreamController@markRead');
$router->get('/api/v2/notifications/stream', 'Api\\NotificationStreamController@stream');

$router->get('/admin/webhooks', 'Admin\\WebhookController@index');
$router->post('/admin/webhooks/create', 'Admin\\WebhookController@create');
$router->post('/admin/webhooks/toggle/{id}', 'Admin\\WebhookController@toggle');
$router->post('/admin/webhooks/delete/{id}', 'Admin\\WebhookController@delete');
$router->post('/admin/webhooks/process', 'Admin\\WebhookController@process');

$router->get('/admin/bulk-operations', 'Admin\\BulkOperationsController@index');
$router->get('/admin/bulk-operations/template/{table}', 'Admin\\BulkOperationsController@template');
$router->get('/admin/bulk-operations/export/{table}', 'Admin\\BulkOperationsController@export');
$router->post('/admin/bulk-operations/import', 'Admin\\BulkOperationsController@import');

$router->get('/user/two-factor', 'User\\TwoFactorController@setup');
$router->post('/user/two-factor/enable', 'User\\TwoFactorController@enable');
$router->post('/user/two-factor/disable', 'User\\TwoFactorController@disable');
$router->get('/user/two-factor/verify', 'User\\TwoFactorController@verify');
$router->post('/user/two-factor/verify', 'User\\TwoFactorController@verify');
$router->get('/user/two-factor/backup-codes', 'User\\TwoFactorController@backupCodes');
$router->get('/user/two-factor/recovery', 'User\\TwoFactorController@recovery');
$router->post('/user/two-factor/recovery/verify', 'User\\TwoFactorController@verifyBackupCode');
$router->get('/user/two-factor/disabled', 'User\\TwoFactorController@disabled');

// Admin-side alias: `/admin/2fa` should be findable from bookmarks and
// admin menu links even though the actual handler lives under /user/two-factor.
// (Users with admin role share the same users table, so this just forwards.)
$router->get('/admin/2fa', function () {
    $base = defined('BASE_URL') ? BASE_URL : '';
    header('Location: ' . $base . '/user/two-factor', true, 302);
    exit;
});

$router->get('/admin/production-checklist', 'Admin\\ProductionChecklistController@index');
$router->post('/admin/production-checklist/mark/{key}', 'Admin\\ProductionChecklistController@mark');

$router->get('/admin/api-keys', 'Admin\\ApiKeyController@index');
$router->post('/admin/api-keys/create', 'Admin\\ApiKeyController@create');
$router->post('/admin/api-keys/revoke/{id}', 'Admin\\ApiKeyController@revoke');
$router->post('/admin/api-keys/activate/{id}', 'Admin\\ApiKeyController@activate');
$router->post('/admin/api-keys/delete/{id}', 'Admin\\ApiKeyController@delete');

$router->get('/admin/system-health', 'Admin\\SystemHealthController@index');
$router->get('/api/v2/system/health', 'Admin\\SystemHealthController@api');
$router->get('/admin/features/agent-tasks', 'Admin\\NewFeaturesController@agentTasks');
$router->get('/admin/features/ocr', 'Admin\\NewFeaturesController@ocrCenter');
$router->get('/admin/features/maintenance', 'Admin\\NewFeaturesController@propertyMaintenance');


// ============================================================
// PHASE 24-33 NEW FEATURES (APIs)
// ============================================================
$router->post('/api/v2/registration/start', 'Api\\NewFeaturesApiController@regStart');
$router->post('/api/v2/registration/progress/{token}', 'Api\\NewFeaturesApiController@regProgress');
$router->post('/api/v2/registration/complete/{token}', 'Api\\NewFeaturesApiController@regComplete');

$router->post('/api/v2/payroll/generate', 'Api\\NewFeaturesApiController@payrollGenerate');

$router->get('/api/v2/resell', 'Api\\NewFeaturesApiController@resellList');
$router->get('/api/v2/resell/public', 'Api\\NewFeaturesApiController@resellListPublic');
$router->post('/api/v2/resell/create', 'Api\\NewFeaturesApiController@resellCreate');
$router->post('/api/v2/resell/value/{id}', 'Api\\NewFeaturesApiController@resellValuate');

$router->post('/api/v2/commission/calculate', 'Api\\NewFeaturesApiController@commissionCalculate');
$router->post('/api/v2/commission/record', 'Api\\NewFeaturesApiController@commissionRecord');
$router->get('/api/v2/commission/mlm-ranks', 'Api\\NewFeaturesApiController@mlmRanks');

$router->post('/api/v2/notification/send', 'Api\\NewFeaturesApiController@sendNotification');
$router->post('/api/v2/notification/render', 'Api\\NewFeaturesApiController@renderTemplate');

$router->post('/api/v2/security/2fa/generate', 'Api\\NewFeaturesApiController@generate2fa');
$router->post('/api/v2/security/2fa/verify', 'Api\\NewFeaturesApiController@verify2fa');
$router->post('/api/v2/security/password-reset', 'Api\\NewFeaturesApiController@passwordReset');
$router->post('/api/v2/security/password-reset/confirm', 'Api\\NewFeaturesApiController@passwordResetConfirm');
$router->post('/api/v2/security/ip/block', 'Api\\NewFeaturesApiController@blockIp');
$router->post('/api/v2/security/ip/unblock', 'Api\\NewFeaturesApiController@unblockIp');

$router->post('/api/v2/finance/gst', 'Api\\NewFeaturesApiController@calculateGst');
$router->post('/api/v2/finance/tax', 'Api\\NewFeaturesApiController@calculateTax');
$router->post('/api/v2/finance/budget/create', 'Api\\NewFeaturesApiController@createBudget');

$router->post('/api/v2/analytics/kpi/record', 'Api\\NewFeaturesApiController@recordKpi');
$router->post('/api/v2/analytics/forecast', 'Api\\NewFeaturesApiController@generateForecast');
$router->get('/api/v2/analytics/dashboard', 'Api\\NewFeaturesApiController@analyticsDashboard');
$router->get('/api/v2/analytics/insights', 'Api\\NewFeaturesApiController@analyticsInsights');

$router->post('/api/v2/agent/task/create', 'Api\\NewFeaturesApiController@createTask');
$router->post('/api/v2/agent/task/execute/{id}', 'Api\\NewFeaturesApiController@executeTask');
$router->post('/api/v2/agent/tasks/process', 'Api\\NewFeaturesApiController@processPendingTasks');
$router->post('/api/v2/agent/workflow/trigger', 'Api\\NewFeaturesApiController@triggerWorkflow');

$router->post('/api/v2/ocr/classify', 'Api\\NewFeaturesApiController@classifyDocument');
$router->post('/api/v2/ocr/report', 'Api\\NewFeaturesApiController@executeReport');

$router->post('/api/v2/property/maintenance', 'Api\\NewFeaturesApiController@scheduleMaintenance');


// ============================================================
// PUBLIC RESELL PROPERTIES (Phase 26) - Use /resell-properties to avoid conflict with PageController@resell
// ============================================================
$router->get('/resell-properties', 'Front\\ResellPropertyController@index');
$router->get('/resell-properties/submit', 'Front\\ResellPropertyController@submit');
$router->post('/resell-properties/submit', 'Front\\ResellPropertyController@submit');
$router->get('/resell-properties/{id}', 'Front\\ResellPropertyController@show');


// ============================================================
// WEB PUSH NOTIFICATIONS (VAPID — RFC 8291/8292)
// Public/sw.js is the service worker (registered from header.php).
// Endpoints hit by the browser's PushManager.
// ============================================================
$router->post('/push/subscribe',   'App\\Http\\Controllers\\Front\\PushNotificationController@subscribe');
$router->post('/push/unsubscribe', 'App\\Http\\Controllers\\Front\\PushNotificationController@unsubscribe');
$router->post('/push/test',        'App\\Http\\Controllers\\Front\\PushNotificationController@test');
$router->get('/push/vapid-key',    'App\\Http\\Controllers\\Front\\PushNotificationController@vapidPublicKey');

// ADMIN PUSH NOTIFICATION MANAGEMENT
$router->get('/admin/push-notifications',          'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@index');
$router->get('/admin/push-notifications/send',     'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@sendForm');
$router->post('/admin/push-notifications/send',    'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@send');
$router->get('/admin/push-notifications/log',      'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@log');
$router->get('/admin/push-notifications/stats',    'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@stats');

// PUSH NOTIFICATION TEMPLATES
$router->get('/admin/push-notifications/templates',              'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@templates');
$router->get('/admin/push-notifications/templates/new',          'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@templateForm');
$router->get('/admin/push-notifications/templates/{id}/edit',    'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@templateForm');
$router->post('/admin/push-notifications/templates/store',       'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@templateStore');
$router->post('/admin/push-notifications/templates/{id}/delete', 'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@templateDelete');

// PUSH NOTIFICATION CAMPAIGNS
$router->get('/admin/push-notifications/campaigns',              'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@campaigns');
$router->get('/admin/push-notifications/campaigns/new',          'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@campaignForm');
$router->get('/admin/push-notifications/campaigns/{id}/edit',    'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@campaignForm');
$router->get('/admin/push-notifications/campaigns/{id}',         'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@campaignDetail');
$router->post('/admin/push-notifications/campaigns/store',       'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@campaignStore');
$router->post('/admin/push-notifications/campaigns/{id}/launch', 'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@campaignLaunch');
$router->post('/admin/push-notifications/campaigns/{id}/pause',  'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@campaignPause');

// PUSH QUEUE
$router->get('/admin/push-notifications/queue',                  'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@queueStatus');
$router->post('/admin/push-notifications/queue/process',         'App\\Http\\Controllers\\Admin\\PushNotificationAdminController@processQueue');

// ============================================================
// OCR DOCUMENT PIPELINE
// ============================================================
$router->get('/admin/ocr',                                    'App\\Http\\Controllers\\Admin\\DocumentOCRController@index');
$router->get('/admin/ocr/upload',                             'App\\Http\\Controllers\\Admin\\DocumentOCRController@upload');
$router->post('/admin/ocr/store',                             'App\\Http\\Controllers\\Admin\\DocumentOCRController@store');
$router->get('/admin/ocr/detail/{id}',                        'App\\Http\\Controllers\\Admin\\DocumentOCRController@detail');
$router->post('/admin/ocr/process/{id}',                      'App\\Http\\Controllers\\Admin\\DocumentOCRController@process');
$router->post('/admin/ocr/approve/{id}',                      'App\\Http\\Controllers\\Admin\\DocumentOCRController@approve');
$router->post('/admin/ocr/reject/{id}',                       'App\\Http\\Controllers\\Admin\\DocumentOCRController@reject');
$router->post('/admin/ocr/delete/{id}',                       'App\\Http\\Controllers\\Admin\\DocumentOCRController@delete');
$router->get('/admin/ocr/templates',                          'App\\Http\\Controllers\\Admin\\DocumentOCRController@templates');
$router->get('/admin/ocr/templates/create',                   'App\\Http\\Controllers\\Admin\\DocumentOCRController@templateForm');
$router->get('/admin/ocr/templates/edit/{id}',                'App\\Http\\Controllers\\Admin\\DocumentOCRController@templateForm');
$router->post('/admin/ocr/templates/store',                   'App\\Http\\Controllers\\Admin\\DocumentOCRController@templateStore');
$router->post('/admin/ocr/templates/delete/{id}',             'App\\Http\\Controllers\\Admin\\DocumentOCRController@templateDelete');

// ============================================================
// CHECKOUT + RAZORPAY FLOW
// GET  /checkout/{bookingId}           — payment page
// POST /checkout/process/{bookingId}   — create Razorpay order (AJAX)
// POST /checkout/verify                — verify signature after checkout
// GET  /checkout/success/{paymentId}   — receipt
// GET  /checkout/failed                — failure page
// POST /webhook/razorpay               — Razorpay server-side callback (HMAC)
// ============================================================
$router->get('/checkout/{bookingId}',              'App\\Http\\Controllers\\Front\\CheckoutController@checkout');
$router->post('/checkout/process/{bookingId}',     'App\\Http\\Controllers\\Front\\CheckoutController@processPayment');
$router->post('/checkout/verify',                  'App\\Http\\Controllers\\Front\\CheckoutController@verifyPayment');
$router->get('/checkout/success/{paymentId}',      'App\\Http\\Controllers\\Front\\CheckoutController@paymentSuccess');
$router->get('/checkout/failed',                   'App\\Http\\Controllers\\Front\\CheckoutController@paymentFailed');
$router->post('/webhook/razorpay',                 'App\\Http\\Controllers\\Front\\CheckoutController@webhook');

// ============================================================
// CSP report-uri endpoint
// ============================================================
// POST /csp-report          — browser reports Content-Security-Policy violations
// GET  /admin/csp-violations — admin dashboard for CSP reports
// ============================================================
$router->post('/csp-report',                          'App\\Http\\Controllers\\CspReportController@report');
$router->get('/admin/csp-violations',                'App\\Http\\Controllers\\CspReportController@list');

// ============================================================
// MODULE 4: MLM COMMISSION ENGINE + RANK SYSTEM
// All routes under /admin/mlm/* — require admin auth.
// ============================================================
$router->get('/admin/mlm/dashboard',                              'App\\Http\\Controllers\\Admin\\MLMCommissionController@index');
$router->get('/admin/mlm/commissions',                            'App\\Http\\Controllers\\Admin\\MLMCommissionController@commissions');
$router->get('/admin/mlm/commissions/{id}',                       'App\\Http\\Controllers\\Admin\\MLMCommissionController@commissionDetail');
$router->get('/admin/mlm/payouts',                                'App\\Http\\Controllers\\Admin\\MLMCommissionController@payouts');
$router->get('/admin/mlm/payouts/batches',                        'App\\Http\\Controllers\\Admin\\MLMCommissionController@payoutBatches');
$router->get('/admin/mlm/payouts/batches/create',                 'App\\Http\\Controllers\\Admin\\MLMCommissionController@payoutBatchCreate');
$router->post('/admin/mlm/payouts/batches/create',                'App\\Http\\Controllers\\Admin\\MLMCommissionController@payoutBatchStore');
$router->get('/admin/mlm/payouts/batches/{id}',                   'App\\Http\\Controllers\\Admin\\MLMCommissionController@payoutBatchView');
$router->post('/admin/mlm/payouts/batches/{id}/approve',          'App\\Http\\Controllers\\Admin\\MLMCommissionController@payoutBatchApprove');
$router->get('/admin/mlm/payouts/{id}/mark-paid',                 'App\\Http\\Controllers\\Admin\\MLMCommissionController@payoutPaidForm');
$router->post('/admin/mlm/payouts/{id}/mark-paid',                'App\\Http\\Controllers\\Admin\\MLMCommissionController@payoutMarkPaid');
$router->get('/admin/mlm/associate-ranks',                        'App\\Http\\Controllers\\Admin\\MLMCommissionController@associateRanks');
$router->post('/admin/mlm/associate-ranks/promote-all',          'App\\Http\\Controllers\\Admin\\MLMCommissionController@promoteAll');
$router->get('/admin/mlm/associate-ranks/{id}',                   'App\\Http\\Controllers\\Admin\\MLMCommissionController@associateRankView');
$router->post('/admin/mlm/associate-ranks/{id}/promote',          'App\\Http\\Controllers\\Admin\\MLMCommissionController@rankPromoteManual');
$router->get('/admin/mlm/rank-benefits',                          'App\\Http\\Controllers\\Admin\\MLMCommissionController@rankBenefits');
$router->get('/admin/mlm/clawbacks',                              'App\\Http\\Controllers\\Admin\\MLMCommissionController@clawbacks');
$router->get('/admin/mlm/clawbacks/{id}',                         'App\\Http\\Controllers\\Admin\\MLMCommissionController@clawbackView');
$router->post('/admin/mlm/clawbacks/{id}/recover',                'App\\Http\\Controllers\\Admin\\MLMCommissionController@clawbackRecover');
$router->post('/admin/mlm/clawbacks/process',                     'App\\Http\\Controllers\\Admin\\MLMCommissionController@processClawbacksNow');
$router->get('/admin/mlm/cron-log',                               'App\\Http\\Controllers\\Admin\\MLMCommissionController@cronLog');
$router->get('/admin/mlm/payout-simulator',                       'App\\Http\\Controllers\\Admin\\MLMCommissionController@payoutSimulator');
$router->post('/admin/mlm/payout-simulator/simulate',             'App\\Http\\Controllers\\Admin\\MLMCommissionController@payoutSimulateApi');
$router->get('/admin/mlm/royalty-pool',                           'App\\Http\\Controllers\\Admin\\MLMCommissionController@royaltyPool');
$router->get('/admin/mlm/api/rank-distribution',                  'App\\Http\\Controllers\\Admin\\MLMCommissionController@apiRankDistribution');

// ============================================================
// PUBLIC MLM PLAN INFO PAGE
// ============================================================
$router->get('/how-it-works', 'Front\\LegalController@howItWorks');

// ============================================================
// MODULE 5: BACKOFFICE + DAILY OPERATIONS
// URL prefix: /admin/backoffice/*
// All actions delegate to App\Http\Controllers\Admin\BackofficeController.
// ============================================================
$router->get('/admin/backoffice',                                    'App\\Http\\Controllers\\Admin\\BackofficeController@index');

// Attendance
$router->get('/admin/backoffice/attendance',                         'App\\Http\\Controllers\\Admin\\BackofficeController@attendance');
$router->post('/admin/backoffice/attendance/record',                 'App\\Http\\Controllers\\Admin\\BackofficeController@attendanceRecord');
$router->get('/admin/backoffice/attendance/monthly',                 'App\\Http\\Controllers\\Admin\\BackofficeController@attendanceMonthly');
$router->get('/admin/backoffice/attendance/monthly/export',          'App\\Http\\Controllers\\Admin\\BackofficeController@attendanceMonthlyReport');

// Leaves
$router->get('/admin/backoffice/leaves',                             'App\\Http\\Controllers\\Admin\\BackofficeController@leaves');
$router->post('/admin/backoffice/leaves/{id}/approve',               'App\\Http\\Controllers\\Admin\\BackofficeController@leaveApprove');
$router->post('/admin/backoffice/leaves/{id}/reject',                'App\\Http\\Controllers\\Admin\\BackofficeController@leaveReject');
$router->get('/admin/backoffice/leaves/history',                     'App\\Http\\Controllers\\Admin\\BackofficeController@leaveHistory');

// Payslips
$router->get('/admin/backoffice/payslips',                           'App\\Http\\Controllers\\Admin\\BackofficeController@payslips');
$router->post('/admin/backoffice/payslips/generate',                 'App\\Http\\Controllers\\Admin\\BackofficeController@payslipGenerate');
$router->get('/admin/backoffice/payslips/{id}',                      'App\\Http\\Controllers\\Admin\\BackofficeController@payslipView');
$router->post('/admin/backoffice/payslips/{id}/pay',                  'App\\Http\\Controllers\\Admin\\BackofficeController@payslipPay');

// Leads
$router->get('/admin/backoffice/leads',                              'App\\Http\\Controllers\\Admin\\BackofficeController@leads');
$router->get('/admin/backoffice/leads/create',                       'App\\Http\\Controllers\\Admin\\BackofficeController@leadCreate');
$router->post('/admin/backoffice/leads/store',                       'App\\Http\\Controllers\\Admin\\BackofficeController@leadStore');
$router->get('/admin/backoffice/leads/{id}',                         'App\\Http\\Controllers\\Admin\\BackofficeController@leadDetail');
$router->get('/admin/backoffice/leads/{id}/edit',                    'App\\Http\\Controllers\\Admin\\BackofficeController@leadEdit');
$router->post('/admin/backoffice/leads/{id}/update',                 'App\\Http\\Controllers\\Admin\\BackofficeController@leadUpdate');
$router->post('/admin/backoffice/leads/{id}/activity',               'App\\Http\\Controllers\\Admin\\BackofficeController@leadAddActivity');
$router->post('/admin/backoffice/leads/{id}/advance',                'App\\Http\\Controllers\\Admin\\BackofficeController@leadAdvanceStage');

// Operations
$router->get('/admin/backoffice/operations',                         'App\\Http\\Controllers\\Admin\\BackofficeController@operations');
$router->get('/admin/backoffice/operations/create',                  'App\\Http\\Controllers\\Admin\\BackofficeController@operationsCreate');
$router->post('/admin/backoffice/operations/store',                  'App\\Http\\Controllers\\Admin\\BackofficeController@operationsStore');

// Reports
$router->get('/admin/backoffice/reports',                            'App\\Http\\Controllers\\Admin\\BackofficeController@reports');
$router->get('/admin/backoffice/reports/{id}/run',                   'App\\Http\\Controllers\\Admin\\BackofficeController@reportsRun');
$router->post('/admin/backoffice/reports/{id}/run',                  'App\\Http\\Controllers\\Admin\\BackofficeController@reportsRun');
$router->get('/admin/backoffice/reports/{id}/history',               'App\\Http\\Controllers\\Admin\\BackofficeController@reportsHistory');

// API
$router->get('/admin/backoffice/api/lead-summary',                   'App\\Http\\Controllers\\Admin\\BackofficeController@apiLeadSummary');

// ============================================================
// DEPARTMENT & DESIGNATION MANAGEMENT
// URL prefix: /admin/departments/*, /admin/designations/*
// ============================================================
$router->get('/admin/departments',                        'App\\Http\\Controllers\\Admin\\DepartmentController@index');
$router->get('/admin/departments/create',                  'App\\Http\\Controllers\\Admin\\DepartmentController@create');
$router->post('/admin/departments/store',                  'App\\Http\\Controllers\\Admin\\DepartmentController@store');
$router->get('/admin/departments/{id}/edit',               'App\\Http\\Controllers\\Admin\\DepartmentController@edit');
$router->post('/admin/departments/{id}/update',            'App\\Http\\Controllers\\Admin\\DepartmentController@update');
$router->post('/admin/departments/{id}/delete',            'App\\Http\\Controllers\\Admin\\DepartmentController@delete');

$router->get('/admin/designations',                       'App\\Http\\Controllers\\Admin\\DesignationController@index');
$router->get('/admin/designations/create',                 'App\\Http\\Controllers\\Admin\\DesignationController@create');
$router->post('/admin/designations/store',                 'App\\Http\\Controllers\\Admin\\DesignationController@store');
$router->get('/admin/designations/{id}/edit',              'App\\Http\\Controllers\\Admin\\DesignationController@edit');
$router->post('/admin/designations/{id}/update',           'App\\Http\\Controllers\\Admin\\DesignationController@update');
$router->post('/admin/designations/{id}/delete',           'App\\Http\\Controllers\\Admin\\DesignationController@delete');

// ============================================================
// COMPANY CREDENTIALS
// ============================================================
$router->get('/admin/company-credentials',                           'Admin\\CompanyCredentialsController@index');
$router->get('/admin/company-credentials/create',                    'Admin\\CompanyCredentialsController@create');
$router->post('/admin/company-credentials/store',                    'Admin\\CompanyCredentialsController@store');
$router->get('/admin/company-credentials/expiring',                  'Admin\\CompanyCredentialsController@expiring');
$router->get('/admin/company-credentials/{id}',                      'Admin\\CompanyCredentialsController@show');
$router->get('/admin/company-credentials/{id}/edit',                 'Admin\\CompanyCredentialsController@edit');
$router->post('/admin/company-credentials/{id}/update',              'Admin\\CompanyCredentialsController@update');
$router->post('/admin/company-credentials/{id}/delete',              'Admin\\CompanyCredentialsController@delete');

// ============================================================
// ON-FIELD CASH COLLECTION & RECONCILIATION
// URL prefix: /admin/cash-collections/*
// ============================================================
$router->get('/admin/cash-collections',                                    'Admin\\CashCollectionController@index');
$router->get('/admin/cash-collections/create',                             'Admin\\CashCollectionController@create');
$router->post('/admin/cash-collections/store',                             'Admin\\CashCollectionController@store');
$router->get('/admin/cash-collections/{id}',                               'Admin\\CashCollectionController@show');
$router->post('/admin/cash-collections/verify',                            'Admin\\CashCollectionController@verify');
$router->post('/admin/cash-collections/reject',                            'Admin\\CashCollectionController@reject');
$router->post('/admin/cash-collections/bulk-verify',                       'Admin\\CashCollectionController@bulkVerify');
$router->get('/admin/cash-collections/reconciliations',                    'Admin\\CashCollectionController@reconciliations');
$router->get('/admin/cash-collections/reconciliations/create',             'Admin\\CashCollectionController@reconciliationForm');
$router->post('/admin/cash-collections/reconciliations/create',            'Admin\\CashCollectionController@reconciliationCreate');
$router->post('/admin/cash-collections/reconciliations/close',             'Admin\\CashCollectionController@reconciliationClose');

// ============================================================
// FINANCE ROUTE ALIASES — point to CashCollectionController (full-featured)
// Merging two parallel systems: /admin/cash-collections/* and /admin/finance/collections/*
// URL prefix: /admin/finance/cash-collections/*
// ============================================================
$router->get('/admin/finance/cash-collections',                                    'Admin\\CashCollectionController@index');
$router->get('/admin/finance/cash-collections/create',                             'Admin\\CashCollectionController@create');
$router->post('/admin/finance/cash-collections/store',                             'Admin\\CashCollectionController@store');
$router->get('/admin/finance/cash-collections/show/{id}',                          'Admin\\CashCollectionController@show');
$router->get('/admin/finance/cash-collections/reconciliation',                     'Admin\\CashCollectionController@reconciliations');
$router->post('/admin/finance/cash-collections/reconcile/{id}',                    'Admin\\CashCollectionController@reconcile');

// ============================================================
// NOC & REGISTRY PIPE
// URL prefix: /admin/noc-registry/*
// ============================================================
$router->get('/admin/noc-registry',                                       'Admin\\NocRegistryController@dashboard');
$router->get('/admin/noc-registry/eligibility',                           'Admin\\NocRegistryController@eligibilityCheck');
// NOC
$router->get('/admin/noc-registry/nocs',                                  'Admin\\NocRegistryController@nocList');
$router->get('/admin/noc-registry/nocs/create',                           'Admin\\NocRegistryController@nocCreate');
$router->post('/admin/noc-registry/nocs/store',                           'Admin\\NocRegistryController@nocStore');
$router->get('/admin/noc-registry/nocs/{id}',                             'Admin\\NocRegistryController@nocDetail');
$router->post('/admin/noc-registry/nocs/approve',                         'Admin\\NocRegistryController@nocApprove');
$router->post('/admin/noc-registry/nocs/reject',                          'Admin\\NocRegistryController@nocReject');
$router->post('/admin/noc-registry/nocs/reprocess',                       'Admin\\NocRegistryController@nocReprocess');
// Registry
$router->get('/admin/noc-registry/registries',                            'Admin\\NocRegistryController@registryList');
$router->get('/admin/noc-registry/registries/create',                     'Admin\\NocRegistryController@registryCreate');
$router->post('/admin/noc-registry/registries/store',                     'Admin\\NocRegistryController@registryStore');
$router->get('/admin/noc-registry/registries/{id}',                       'Admin\\NocRegistryController@registryDetail');
$router->post('/admin/noc-registry/registries/update-status',             'Admin\\NocRegistryController@registryUpdateStatus');

// ── Site Content Manager ──
$router->get('/admin/site-content',                                       'Admin\\SiteContentController@index');
$router->get('/admin/site-content/edit/{section}',                        'Admin\\SiteContentController@edit');
$router->post('/admin/site-content/update/{section}',                     'Admin\\SiteContentController@update');
$router->post('/admin/site-content/create',                               'Admin\\SiteContentController@create');
$router->post('/admin/site-content/delete',                               'Admin\\SiteContentController@delete');

// ── Site Settings (tabbed admin panel) ──
$router->get('/admin/site-settings',                                       'Admin\\SiteSettingsController@index');
$router->post('/admin/site-settings/update',                               'Admin\\SiteSettingsController@update');

// ============================================================
// BANK STATEMENT IMPORT & AUTO-RECONCILIATION
// URL prefix: /admin/bank-import/*
// ============================================================
$router->get('/admin/bank-import',                                    'Admin\\BankImportController@index');
$router->get('/admin/bank-import/upload',                             'Admin\\BankImportController@upload');
$router->post('/admin/bank-import/process',                           'Admin\\BankImportController@process');
$router->get('/admin/bank-import/{id}',                               'Admin\\BankImportController@show');
$router->post('/admin/bank-import/{id}/match',                        'Admin\\BankImportController@match');
$router->post('/admin/bank-import/manual-match',                      'Admin\\BankImportController@manualMatch');
$router->post('/admin/bank-import/unmatch/{txnId}',                   'Admin\\BankImportController@unmatch');
$router->post('/admin/bank-import/{id}/delete',                       'Admin\\BankImportController@delete');
$router->get('/admin/bank-import/{id}/export',                        'Admin\\BankImportController@export');
$router->get('/admin/bank-import/search-internal',                    'Admin\\BankImportController@searchInternal');

// ============================================================
// PUSH NOTIFICATIONS (API)
// ============================================================
$router->post('/api/push/subscribe',    'Api\\PushNotificationController@subscribe');
$router->post('/api/push/unsubscribe',  'Api\\PushNotificationController@unsubscribe');
$router->get('/api/push/vapid-key',     'Api\\PushNotificationController@vapidPublicKey');

// ============================================================
// API DOCUMENTATION (Admin UI) — canonical route at line ~1621
// ============================================================

// ============================================================
// SERVICE CONFIGURATION (Centralized admin settings)
// ============================================================
$router->get('/admin/service-configs',                  'Admin\\ServiceConfigController@index');
$router->post('/admin/service-configs/update',          'Admin\\ServiceConfigController@update');
$router->post('/admin/service-configs/test/{service}',  'Admin\\ServiceConfigController@testConnection');
$router->post('/admin/service-configs/reset/{service}', 'Admin\\ServiceConfigController@resetService');

// ============================================================
// URL ALIASES (fix 404s from sidebar mismatches)
// ============================================================
$router->get('/admin/backoffice/dashboard',  function() { header('Location: ' . BASE_URL . '/admin/backoffice'); exit; });
$router->get('/admin/legal/noc',             function() { header('Location: ' . BASE_URL . '/admin/legal/noc-index'); exit; });
$router->get('/admin/ai/training',           function() { header('Location: ' . BASE_URL . '/admin/ai-training'); exit; });
$router->get('/admin/realtime-analytics',    function() { header('Location: ' . BASE_URL . '/admin/analytics/realtime'); exit; });

// Legal Documentation Management System
$router->get('/admin/legal',                                         'App\\Http\\Controllers\\Admin\\LegalDocumentController@index');
$router->get('/admin/legal/dashboard',                              'App\\Http\\Controllers\\Admin\\LegalDocumentController@index');
// Categories
$router->get('/admin/legal/categories',                             'App\\Http\\Controllers\\Admin\\LegalDocumentController@categories');
$router->post('/admin/legal/categories/create',                     'App\\Http\\Controllers\\Admin\\LegalDocumentController@categoryCreate');
$router->post('/admin/legal/categories/{id}/update',                'App\\Http\\Controllers\\Admin\\LegalDocumentController@categoryUpdate');
$router->post('/admin/legal/categories/{id}/delete',                'App\\Http\\Controllers\\Admin\\LegalDocumentController@categoryDelete');
// Templates
$router->get('/admin/legal/templates',                              'App\\Http\\Controllers\\Admin\\LegalDocumentController@templates');
$router->post('/admin/legal/templates/create',                      'App\\Http\\Controllers\\Admin\\LegalDocumentController@templateCreate');
$router->get('/admin/legal/templates/{id}/edit',                    'App\\Http\\Controllers\\Admin\\LegalDocumentController@templateEdit');
$router->post('/admin/legal/templates/{id}/update',                 'App\\Http\\Controllers\\Admin\\LegalDocumentController@templateUpdate');
$router->post('/admin/legal/templates/{id}/delete',                 'App\\Http\\Controllers\\Admin\\LegalDocumentController@templateDelete');
$router->post('/admin/legal/templates/{id}/restore/{version}',      'App\\Http\\Controllers\\Admin\\LegalDocumentController@templateRestore');
// Clause Library
$router->get('/admin/legal/clauses',                                'App\\Http\\Controllers\\Admin\\LegalDocumentController@clauses');
$router->post('/admin/legal/clauses/create',                        'App\\Http\\Controllers\\Admin\\LegalDocumentController@clauseCreate');
$router->post('/admin/legal/clauses/{id}/update',                   'App\\Http\\Controllers\\Admin\\LegalDocumentController@clauseUpdate');
$router->post('/admin/legal/clauses/{id}/delete',                   'App\\Http\\Controllers\\Admin\\LegalDocumentController@clauseDelete');
// Documents
$router->get('/admin/legal/documents',                              'App\\Http\\Controllers\\Admin\\LegalDocumentController@documents');
$router->get('/admin/legal/documents/create',                       'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentCreate');
$router->post('/admin/legal/documents/create',                      'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentCreate');
$router->get('/admin/legal/documents/{id}',                         'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentDetail');
$router->post('/admin/legal/documents/{id}/update',                 'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentUpdate');
$router->post('/admin/legal/documents/{id}/status/{status}',        'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentUpdateStatus');
$router->post('/admin/legal/documents/{id}/delete',                 'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentDelete');
$router->post('/admin/legal/documents/{id}/mark-online',            'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentMarkOnline');
$router->post('/admin/legal/documents/{id}/mark-physical',          'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentMarkPhysical');
$router->post('/admin/legal/documents/{id}/kyc-verify',             'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentKycVerify');
$router->get('/admin/legal/documents/{id}/preview',                 'App\\Http\\Controllers\\Admin\\LegalDocumentController@documentPreview');
// Uploads
$router->post('/admin/legal/uploads/{id}/verify',                   'App\\Http\\Controllers\\Admin\\LegalDocumentController@uploadVerify');
$router->post('/admin/legal/uploads/{id}/delete',                   'App\\Http\\Controllers\\Admin\\LegalDocumentController@uploadDelete');
// AI Composer
$router->get('/admin/legal/ai-composer',                            'App\\Http\\Controllers\\Admin\\LegalDocumentController@aiComposer');
$router->post('/admin/legal/ai-generate',                           'App\\Http\\Controllers\\Admin\\LegalDocumentController@aiGenerate');
// AI Prompts
$router->get('/admin/legal/ai-prompts',                             'App\\Http\\Controllers\\Admin\\LegalDocumentController@aiPrompts');
$router->post('/admin/legal/ai-prompts/create',                     'App\\Http\\Controllers\\Admin\\LegalDocumentController@aiPromptCreate');
$router->post('/admin/legal/ai-prompts/{id}/update',                'App\\Http\\Controllers\\Admin\\LegalDocumentController@aiPromptUpdate');
$router->post('/admin/legal/ai-prompts/{id}/delete',                'App\\Http\\Controllers\\Admin\\LegalDocumentController@aiPromptDelete');

// ============================================================
// VOICE BOT (Option A — Browser-based, 100% free)
// ============================================================
$router->get('/voice-bot',  'Front\\VoiceBotController@index');
$router->get('/admin/voice-bot', 'Front\\VoiceBotController@adminDashboard');

// API Routes
$router->post('/api/voice-bot/chat', 'Front\\VoiceBotController@chat');

// ============================================================
// WHATSAPP WEBHOOK
// ============================================================
$router->get('/api/whatsapp/webhook', function() {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    if ($mode === 'subscribe' && $token === (getenv('WHATSAPP_VERIFY_TOKEN') ?: 'aps_dream_homes_verify')) {
        http_response_code(200);
        echo $challenge;
    } else {
        http_response_code(403);
        echo 'Forbidden';
    }
});

$router->post('/api/whatsapp/webhook', function() {
    $payload = json_decode(file_get_contents('php://input'), true);
    require_once __DIR__ . '/../app/Services/Auc/WhatsAppService.php';
    $wa = new \App\Services\Auc\WhatsAppService();
    $wa->handleIncomingMessage($payload);
    http_response_code(200);
    echo 'ok';
});

// ============================================================
// ADMIN TOOLS (ToolsAdminController — Document AI, eSign, Stamp Duty, Landmarks, WhatsApp Templates)
$router->get('/admin/tools/document-extraction',          'App\\Http\\Controllers\\Admin\\ToolsAdminController@documentExtraction');
$router->get('/admin/tools/esign',                        'App\\Http\\Controllers\\Admin\\ToolsAdminController@esignDashboard');
$router->get('/admin/tools/stamp-duty',                   'App\\Http\\Controllers\\Admin\\ToolsAdminController@stampDutyConfig');
$router->post('/admin/tools/stamp-duty/save',             'App\\Http\\Controllers\\Admin\\ToolsAdminController@stampDutyConfigSave');
$router->get('/admin/tools/landmarks',                    'App\\Http\\Controllers\\Admin\\ToolsAdminController@landmarks');
$router->post('/admin/tools/landmarks/save',              'App\\Http\\Controllers\\Admin\\ToolsAdminController@landmarksSave');
$router->post('/admin/tools/landmarks/{id}/delete',       'App\\Http\\Controllers\\Admin\\ToolsAdminController@landmarksDelete');
$router->get('/admin/tools/whatsapp-templates',           'App\\Http\\Controllers\\Admin\\ToolsAdminController@whatsappTemplates');
$router->post('/admin/tools/whatsapp-templates/save',     'App\\Http\\Controllers\\Admin\\ToolsAdminController@whatsappTemplatesSave');
$router->post('/admin/tools/whatsapp-templates/{id}/delete', 'App\\Http\\Controllers\\Admin\\ToolsAdminController@whatsappTemplatesDelete');

// COMMUNICATION AUTOMATION ADMIN
$router->get('/admin/communication/automation',           'App\\Http\\Controllers\\Admin\\CommunicationAdminController@automation');
$router->get('/admin/communication/whatsapp-setup',       'App\\Http\\Controllers\\Admin\\CommunicationAdminController@whatsappSetup');
$router->get('/admin/communication/telegram-setup',       'App\\Http\\Controllers\\Admin\\CommunicationAdminController@telegramSetup');
$router->get('/admin/communication/sms-setup',            'App\\Http\\Controllers\\Admin\\CommunicationAdminController@smsSetup');
$router->get('/admin/communication/email-templates',      'App\\Http\\Controllers\\Admin\\CommunicationAdminController@emailTemplates');
$router->get('/admin/communication/logs',                 'App\\Http\\Controllers\\Admin\\CommunicationAdminController@logs');
$router->post('/admin/communication/test-send',           'App\\Http\\Controllers\\Admin\\CommunicationAdminController@testSend');

// PUBLIC API — Stamp Duty Calculator
// ============================================================
$router->post('/api/stamp-duty/calculate', 'Api\\StampDutyController@calculate');
$router->get('/api/stamp-duty/rates', 'Api\\StampDutyController@getRates');
$router->get('/api/stamp-duty/states', 'Api\\StampDutyController@getStates');
$router->get('/api/stamp-duty/circle-rate', 'Api\\StampDutyController@getCircleRate');
$router->get('/api/stamp-duty/circle-rates', 'Api\\StampDutyController@searchCircleRates');

// ============================================================
// PUBLIC API — Property Tax Calculator
// ============================================================
$router->post('/api/property-tax/calculate', 'Api\\PropertyTaxController@calculate');
$router->get('/api/property-tax/rates', 'Api\\PropertyTaxController@getRates');
$router->get('/api/property-tax/search', 'Api\\PropertyTaxController@search');
$router->get('/api/property-tax/states', 'Api\\PropertyTaxController@getStates');

// ============================================================
// PUBLIC API — Landmarks & Neighborhood
// ============================================================
$router->get('/api/landmarks/nearby', 'Api\\LandmarksApiController@nearby');
$router->get('/api/landmarks/list', 'Api\\LandmarksApiController@list');
$router->get('/api/landmarks/types', 'Api\\LandmarksApiController@types');
$router->get('/api/landmarks/colony/{colonyId}', 'Api\\LandmarksApiController@byColony');

// ============================================================
// ADMIN COMMUNICATION AUTOMATION
// ============================================================
$router->get('/admin/communication/automation',       'App\\Http\\Controllers\\Admin\\CommunicationAdminController@automation');
$router->get('/admin/communication/whatsapp-setup',   'App\\Http\\Controllers\\Admin\\CommunicationAdminController@whatsappSetup');
$router->post('/admin/communication/whatsapp-setup',  'App\\Http\\Controllers\\Admin\\CommunicationAdminController@whatsappSetupSave');
$router->get('/admin/communication/telegram-setup',   'App\\Http\\Controllers\\Admin\\CommunicationAdminController@telegramSetup');
$router->post('/admin/communication/telegram-setup',  'App\\Http\\Controllers\\Admin\\CommunicationAdminController@telegramSetupSave');
$router->get('/admin/communication/sms-setup',        'App\\Http\\Controllers\\Admin\\CommunicationAdminController@smsSetup');
$router->post('/admin/communication/sms-setup',       'App\\Http\\Controllers\\Admin\\CommunicationAdminController@smsSetupSave');
$router->get('/admin/communication/email-templates',  'App\\Http\\Controllers\\Admin\\CommunicationAdminController@emailTemplates');
$router->post('/admin/communication/email-templates', 'App\\Http\\Controllers\\Admin\\CommunicationAdminController@emailTemplatesSave');
$router->post('/admin/communication/email-templates/{id}/delete', 'App\\Http\\Controllers\\Admin\\CommunicationAdminController@emailTemplatesDelete');
$router->get('/admin/communication/logs',             'App\\Http\\Controllers\\Admin\\CommunicationAdminController@logs');
$router->post('/admin/communication/test-send',       'App\\Http\\Controllers\\Admin\\CommunicationAdminController@testSend');

// ============================================================
// SAAS TENANT MANAGEMENT (Super Admin)
// ============================================================
$router->get('/admin/tenants',                          'App\\Http\\Controllers\\Admin\\TenantController@index');
$router->get('/admin/tenants/dashboard',                'App\\Http\\Controllers\\Admin\\TenantController@dashboard');
$router->get('/admin/tenants/create',                   'App\\Http\\Controllers\\Admin\\TenantController@create');
$router->post('/admin/tenants/store',                   'App\\Http\\Controllers\\Admin\\TenantController@store');
$router->get('/admin/tenants/{id}',                     'App\\Http\\Controllers\\Admin\\TenantController@show');
$router->get('/admin/tenants/{id}/edit',                'App\\Http\\Controllers\\Admin\\TenantController@edit');
$router->post('/admin/tenants/{id}/update',             'App\\Http\\Controllers\\Admin\\TenantController@update');
$router->post('/admin/tenants/{id}/delete',             'App\\Http\\Controllers\\Admin\\TenantController@delete');
$router->post('/admin/tenants/{id}/suspend',            'App\\Http\\Controllers\\Admin\\TenantController@suspend');
$router->post('/admin/tenants/{id}/restore',            'App\\Http\\Controllers\\Admin\\TenantController@restore');
$router->post('/admin/tenants/{id}/switch',             'App\\Http\\Controllers\\Admin\\TenantController@switchTenant');
$router->post('/admin/tenants/stop-switch',             'App\\Http\\Controllers\\Admin\\TenantController@stopSwitch');
$router->get('/admin/tenants/onboard',                 'App\\Http\\Controllers\\Admin\\TenantController@onboard');
$router->post('/admin/tenants/onboard/save',            'App\\Http\\Controllers\\Admin\\TenantController@onboardSave');
$router->post('/admin/tenants/onboard/launch',          'App\\Http\\Controllers\\Admin\\TenantController@onboardLaunch');

// ============================================================
// SAAS BILLING & SUBSCRIPTIONS (Super Admin)
// ============================================================
$router->get('/admin/billing',                              'App\\Http\\Controllers\\Admin\\BillingController@dashboard');
$router->get('/admin/billing/plans',                        'App\\Http\\Controllers\\Admin\\BillingController@plans');
$router->get('/admin/billing/subscribe/{id}',               'App\\Http\\Controllers\\Admin\\BillingController@subscribe');
$router->post('/admin/billing/subscribe/{id}',              'App\\Http\\Controllers\\Admin\\BillingController@processSubscription');
$router->post('/admin/billing/cancel/{id}',                 'App\\Http\\Controllers\\Admin\\BillingController@cancelSubscription');
$router->post('/admin/billing/change-plan/{id}',            'App\\Http\\Controllers\\Admin\\BillingController@changePlan');
$router->get('/admin/billing/invoices/{id}',                'App\\Http\\Controllers\\Admin\\BillingController@invoices');
$router->post('/admin/billing/webhook',                     'App\\Http\\Controllers\\Admin\\BillingController@webhook');

// ── Public SaaS Pricing & Tenant Signup ──────────────────────
$router->get('/pricing',                                    'App\\Http\\Controllers\\Front\\PageController@pricing');
$router->get('/saas-home',                                  'App\\Http\\Controllers\\Front\\PageController@saasHome');
$router->get('/tenant-signup',                              'App\\Http\\Controllers\\Front\\PageController@tenantSignup');
$router->post('/tenant-signup',                             'App\\Http\\Controllers\\Front\\PageController@tenantSignup');

// ============================================================
// CROSS-DEPARTMENT REQUEST WORKFLOW
// ============================================================
$router->get('/admin/department-requests',                    'App\\Http\\Controllers\\Admin\\DepartmentRequestController@dashboard');
$router->get('/admin/department-requests/list',               'App\\Http\\Controllers\\Admin\\DepartmentRequestController@index');
$router->get('/admin/department-requests/submit',             'App\\Http\\Controllers\\Admin\\DepartmentRequestController@submit');
$router->post('/admin/department-requests/submit',            'App\\Http\\Controllers\\Admin\\DepartmentRequestController@submit');
$router->get('/admin/department-requests/{id}',               'App\\Http\\Controllers\\Admin\\DepartmentRequestController@show');
$router->post('/admin/department-requests/{id}/status',       'App\\Http\\Controllers\\Admin\\DepartmentRequestController@updateStatus');

$router->get('/admin/tools/emi-calculator', function() {
    return App\Core\View::make('tools/emi_calculator')->render();
});
$router->post('/admin/department-requests/{id}/assign',       'App\\Http\\Controllers\\Admin\\DepartmentRequestController@assign');
$router->post('/admin/department-requests/{id}/comment',      'App\\Http\\Controllers\\Admin\\DepartmentRequestController@addComment');
$router->get('/admin/department-requests/my-requests',        'App\\Http\\Controllers\\Admin\\DepartmentRequestController@myRequests');

// ============================================================
// FINANCIAL REPORTS
// ============================================================
$router->get('/admin/reports/financial', 'App\\Http\\Controllers\\Admin\\FinancialReportController@index');
$router->get('/admin/reports/financial/profit-loss', 'App\\Http\\Controllers\\Admin\\FinancialReportController@profitLoss');
$router->get('/admin/reports/financial/balance-sheet', 'App\\Http\\Controllers\\Admin\\FinancialReportController@balanceSheet');
$router->get('/admin/reports/financial/cash-flow', 'App\\Http\\Controllers\\Admin\\FinancialReportController@cashFlow');
$router->get('/admin/reports/financial/export', 'App\\Http\\Controllers\\Admin\\FinancialReportController@export');

// ============================================================
// BULK OPERATIONS
// ============================================================
$router->post('/admin/leads/bulk-action', 'App\\Http\\Controllers\\Admin\\LeadController@bulkAction');

// ============================================================
// AGENT COMMISSION DASHBOARD
// ============================================================
$router->get('/admin/agent-commission', 'App\\Http\\Controllers\\Admin\\AgentCommissionController@index');
$router->get('/admin/agent-commission/agent/{id}', 'App\\Http\\Controllers\\Admin\\AgentCommissionController@agentDetail');
$router->post('/admin/agent-commission/assign', 'App\\Http\\Controllers\\Admin\\AgentCommissionController@assignAgent');

// ============================================================
// AGENT AGREEMENT SIGNING
// ============================================================
$router->get('/admin/agent-agreements', 'App\\Http\\Controllers\\Admin\\AgentAgreementController@index');
$router->get('/admin/agent-agreements/create', 'App\\Http\\Controllers\\Admin\\AgentAgreementController@create');
$router->post('/admin/agent-agreements/store', 'App\\Http\\Controllers\\Admin\\AgentAgreementController@store');
$router->get('/admin/agent-agreements/detail/{id}', 'App\\Http\\Controllers\\Admin\\AgentAgreementController@detail');
$router->post('/admin/agent-agreements/send/{id}', 'App\\Http\\Controllers\\Admin\\AgentAgreementController@send');
$router->post('/admin/agent-agreements/sign/{id}', 'App\\Http\\Controllers\\Admin\\AgentAgreementController@sign');
$router->post('/admin/agent-agreements/cancel/{id}', 'App\\Http\\Controllers\\Admin\\AgentAgreementController@cancel');
