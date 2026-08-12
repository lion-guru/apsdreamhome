<?php

/** @var Router $router */

// Per-tenant rate limiting (plan-based), fallback to global if middleware unavailable
// Only apply to API routes (this file is included from web.php, so must not block web pages)
$apiUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (strpos($apiUri, '/api/') !== false || strpos($apiUri, '/apsdreamhome/api/') !== false) {
    if (class_exists('\App\Middleware\TenantRateLimitMiddleware')) {
        \App\Middleware\TenantRateLimitMiddleware::check();
    } elseif (class_exists('\App\Middleware\RateLimiter')) {
        \App\Middleware\RateLimiter::checkApi();
    }
}

// API Routes
$router->get('/api/user/resolve-sponsor', 'Api\UserController@resolveSponsor');

// Voice Assistant
$router->post('/api/voice-assistant/query', 'Api\VoiceAssistantController@query');
$router->get('/api/voice-assistant', 'Api\VoiceAssistantController@index');

$router->post('/api/v2/mobile/auth/login', 'Api\MobileApiController@login');
$router->post('/api/v2/mobile/auth/register', 'Api\MobileApiController@register');
$router->post('/api/v2/mobile/auth/logout', 'Api\MobileApiController@logout');
$router->post('/api/v2/mobile/auth/google-login', 'Api\MobileApiController@googleLogin');
$router->get('/api/v2/mobile/sync', 'Api\MobileApiController@syncProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads', 'Api\CRMController@createLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/batch-sync', 'Api\MobileApiController@batchSyncLeads')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties', 'Api\MobileApiController@syncProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/updates', 'Api\MobileApiController@getUpdates')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/sync', 'Api\MobileApiController@sync')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/summary', 'Api\MobileApiController@getMlmSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/payouts', 'Api\MobileApiController@getMlmPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/incentives', 'Api\MobileApiController@getMlmIncentives')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/upload-document', 'Api\MobileApiController@uploadDocument')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/documents', 'Api\MobileApiController@getDocuments')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/start', 'Api\MobileApiController@startSiteVisit')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/update', 'Api\MobileApiController@updateSiteVisitLocation')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/complete', 'Api\MobileApiController@completeSiteVisit')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/site-visit/status', 'Api\MobileApiController@getSiteVisitStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/site-visit/slots', 'Api\MobileApiController@getAvailableSlots');
$router->post('/api/v2/mobile/site-visit/book', 'Api\MobileApiController@bookSiteVisitApi')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/site-visit/my-visits', 'Api\MobileApiController@getMySiteVisits')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/cancel', 'Api\MobileApiController@cancelSiteVisitApi')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/reschedule', 'Api\MobileApiController@rescheduleSiteVisitApi')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/profile', 'Api\MobileApiController@getUserProfile')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/payouts/pending', 'Api\MobileApiController@getPendingPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/payouts/process', 'Api\MobileApiController@processPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/payouts/history', 'Api\MobileApiController@getPayoutHistory')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/genealogy', 'Api\MobileApiController@getGenealogy')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/business-breakdown', 'Api\MobileApiController@getBusinessBreakdown')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/my-team', 'Api\MobileApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/rank-progress', 'Api\MobileApiController@getRankProgress')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/mlm/request-payout', 'Api\MobileApiController@requestPayout')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/customer/bookings', 'Api\MobileApiController@getCustomerBookings')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/customer/emi-schedule', 'Api\MobileApiController@getEmiSchedule')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/customer/pay-emi', 'Api\MobileApiController@makeEmiPayment')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/properties/submit', 'Api\MobileApiController@submitProperty')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/my-submissions', 'Api\MobileApiController@getSubmissions')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 — User Favorites
// ============================================================
$router->get('/api/v2/mobile/user/favorites', 'Api\MobileApiController@getFavorites')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/documents', 'Api\MobileApiController@getCustomerDocuments')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 — User Notifications
// ============================================================
$router->get('/api/v2/mobile/user/notifications', 'Api\MobileApiController@getCustomerNotifications')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/notifications/read', 'Api\MobileApiController@markNotificationsRead')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 — FCM Token Registration
// ============================================================
$router->post('/api/v2/mobile/fcm/register', 'Api\MobileApiController@registerFcmToken')->middleware('App\Http\Middleware\ApiAuthMiddleware');

$router->post('/api/v2/mobile/user/favorites', 'Api\MobileApiController@addFavorite')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/user/favorites/{id}', 'Api\MobileApiController@removeFavorite')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/favorites/check', 'Api\MobileApiController@checkFavorite')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/favorites/stats', 'Api\MobileApiController@getFavoritesStats')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 — Colonies & Plots (Public Browsing)
// ============================================================
$router->get('/api/v2/mobile/colonies', 'Api\MobileApiController@getColonies');
$router->get('/api/v2/mobile/colonies/search', 'Api\MobileApiController@searchColonies');
$router->get('/api/v2/mobile/colonies/{id}', 'Api\MobileApiController@getColonyDetail');
$router->get('/api/v2/mobile/colonies/{id}/stats', 'Api\MobileApiController@getColonyStats');
$router->get('/api/v2/mobile/colonies/{id}/plots', 'Api\MobileApiController@getColonyPlots');
$router->get('/api/v2/mobile/colonies/{id}/health', 'Api\MobileApiController@getColonyHealth');
$router->get('/api/v2/mobile/colonies/health/all', 'Api\MobileApiController@getAllColoniesHealth');

$router->get('/api/v2/mobile/plots/{id}', 'Api\MobileApiController@getPlotDetail');
$router->post('/api/v2/mobile/plots/{id}/hold', 'Api\MobileApiController@holdPlot')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/plots/{id}/release', 'Api\MobileApiController@releasePlot')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 — Property Marketplace / Premium Listings
// ============================================================
$router->get('/api/v2/mobile/marketplace', 'Api\MobileApiController@getMarketplace');
$router->get('/api/v2/mobile/marketplace/premium', 'Api\MobileApiController@getPremiumProperties');

// Public property browsing (no auth required)
$router->get('/api/v2/mobile/properties/browse', 'Api\MobileApiController@properties');
$router->get('/api/v2/mobile/plots/all', 'Api\MobileApiController@getAllPlots');

// Property detail + search under /api/v2/mobile/ prefix
$router->get('/api/v2/mobile/properties/{id}', 'Api\MobileApiController@propertyDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/search', 'Api\MobileApiController@searchProperties');

$router->get('/api/health', 'Api\SystemController@health');
$router->get('/api/properties', 'Api\PropertyController@index');
$router->post('/api/contact', 'Api\ApiEnquiryController@store');
$router->post('/api/newsletter', 'Api\NewsletterController@subscribe');
$router->post('/api/property-inquiry', 'Api\ApiEnquiryController@propertyInquiry');

// Location API (Pan-India)
$router->get('/api/locations', 'Api\LocationApiController@index');
$router->get('/api/locations/state/{id}', 'Api\LocationApiController@byState');
$router->get('/api/locations/district/{id}', 'Api\LocationApiController@byDistrict');

// Notification API
$router->post('/api/notification', 'Api\NotificationController@create');


// Payment Gateway API Routes
$router->post('/api/payment/phonepe/initiate', 'Api\PaymentGatewayController@initiatePhonePe');
$router->get('/api/payment/phonepe/verify/{transactionId}', 'Api\PaymentGatewayController@verifyPhonePe');
$router->post('/api/payment/phonepe/webhook', 'Api\PaymentGatewayController@phonePeWebhook');
$router->post('/api/payment/gpay/initiate', 'Api\PaymentGatewayController@initiateGPay');
$router->post('/api/payment/upi/qrcode', 'Api\PaymentGatewayController@generateQRCode');
$router->post('/api/payment/upi/callback', 'Api\PaymentGatewayController@upiCallback');
$router->get('/api/payment/status/{orderId}', 'Api\PaymentGatewayController@getStatus');
$router->get('/api/payment/methods', 'Api\PaymentGatewayController@getPaymentMethods');

// AI Assistant API Routes
$router->post('/api/assistant/chat', 'App\\Http\\Controllers\\AIAssistantController@chat');
$router->post('/api/v2/mobile/ai/parse-lead', 'App\\Http\\Controllers\\AIAssistantController@parseLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Gemini AI API Routes
$router->post('/api/gemini/chat', 'Api\GeminiApiController@chat');
$router->post('/api/gemini/generate', 'Api\GeminiApiController@generateContent');
$router->post('/api/gemini/recommendations', 'Api\GeminiApiController@propertyRecommendations');
$router->post('/api/gemini/support', 'Api\GeminiApiController@customerSupport');
$router->post('/api/gemini/market-analysis', 'Api\GeminiApiController@marketAnalysis');
$router->post('/api/gemini/social-media', 'Api\GeminiApiController@socialMediaContent');
$router->get('/api/gemini/test', 'Api\GeminiApiController@testConnection');
$router->get('/api/gemini/status', 'Api\GeminiApiController@getStatus');

// Farmer Management Routes
require_once __DIR__ . '/farmers.php';

// Event Bus Management Routes
require_once __DIR__ . '/events.php';

// Core Functions Management Routes - Now integrated in web.php
$router->get('/api/ai/recommendations', 'App\\Http\\Controllers\\AIAssistantController@recommendations');
$router->get('/api/ai/analyze/{id}', 'App\\Http\\Controllers\\AIAssistantController@analyze');

// Monitoring API Routes
$router->get('/api/monitoring/health', 'App\\Http\\Controllers\\MonitoringController@healthCheck');

// ============================================================
// MOBILE API V2 (JWT Auth)
// ============================================================
$router->post('/api/mobile/auth/login', 'Api\MobileApiController@loginV2');
$router->post('/api/mobile/auth/refresh', 'Api\MobileApiController@refreshV2');
$router->get('/api/mobile/profile', 'Api\MobileApiController@profileV2');
$router->get('/api/mobile/properties', 'Api\MobileApiController@mobileProperties');
$router->get('/api/mobile/dashboard', 'Api\MobileApiController@dashboardV2');
$router->post('/api/mobile/notifications/register', 'Api\MobileApiController@registerPushTokenV2');

// ============================================================
// MOBILE API V2 EXTENDED — Properties, Bookings, Inquiries, Profile
// ============================================================
$router->get('/api/mobile/v2/properties', 'Api\MobileApiController@browseProperties');
$router->get('/api/mobile/v2/properties/search', 'Api\MobileApiController@searchProperties');
$router->get('/api/mobile/v2/properties/{id}', 'Api\MobileApiController@propertyDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/mobile/v2/bookings', 'Api\MobileApiController@listBookings');
$router->get('/api/mobile/v2/bookings/{id}', 'Api\MobileApiController@bookingDetail');
$router->post('/api/mobile/v2/bookings/{id}/pay', 'Api\MobileApiController@recordBookingPayment');
$router->post('/api/mobile/v2/inquiries', 'Api\MobileApiController@submitInquiryV2');
$router->get('/api/mobile/v2/inquiries', 'Api\MobileApiController@listInquiries');
$router->put('/api/mobile/v2/profile', 'Api\MobileApiController@updateProfileV2');
$router->get('/api/mobile/v2/dashboard', 'Api\MobileApiController@dashboardV3');

// Employee Dashboard API Routes (mobile v2)
$router->get('/api/mobile/v2/employee/dashboard', 'Api\MobileApiController@employeeDashboard');
$router->get('/api/mobile/v2/employee/tasks', 'Api\MobileApiController@employeeTasks');
$router->get('/api/mobile/v2/employee/attendance', 'Api\MobileApiController@employeeAttendance');

// AI Dashboard API Routes
$router->post('/api/ai-dashboard/training', 'App\\Http\\Controllers\\AIDashboardController@startTraining');
$router->post('/api/ai-dashboard/reset', 'App\\Http\\Controllers\\AIDashboardController@resetMemory');
$router->post('/api/ai-dashboard/export', 'App\\Http\\Controllers\\AIDashboardController@exportData');
$router->get('/api/ai-dashboard/training-log', 'App\\Http\\Controllers\\AIDashboardController@getTrainingLog');

// Analytics API Routes
$router->get('/api/analytics/metrics', 'Api\AnalyticsController@getRealTimeMetrics');
$router->post('/api/analytics/export', 'Api\AnalyticsController@exportData');
$router->get('/api/analytics/properties', 'Api\AnalyticsController@getPropertyAnalytics');
$router->get('/api/analytics/users', 'Api\AnalyticsController@getUserAnalytics');

// WhatsApp Templates API Routes
$router->post('/api/whatsapp-templates/create', 'App\\Http\\Controllers\\WhatsAppTemplateController@createTemplate');
$router->post('/api/whatsapp-templates/update/{id}', 'App\\Http\\Controllers\\WhatsAppTemplateController@updateTemplate');
$router->delete('/api/whatsapp-templates/delete/{id}', 'App\\Http\\Controllers\\WhatsAppTemplateController@deleteTemplate');
$router->post('/api/whatsapp-templates/send-test', 'App\\Http\\Controllers\\WhatsAppTemplateController@sendTestMessage');
$router->get('/api/whatsapp-templates/stats', 'App\\Http\\Controllers\\WhatsAppTemplateController@getUsageStats');
$router->get('/api/whatsapp-templates/preview/{id}', 'App\\Http\\Controllers\\WhatsAppTemplateController@previewTemplate');

// Referral API Routes
$router->get('/api/referral/dashboard', 'Api\ReferralController@dashboard');
$router->get('/api/referral/stats', 'Api\ReferralController@stats');
$router->get('/api/referral/list', 'Api\ReferralController@index');
$router->post('/api/referral/track', 'Api\\MobileApiController@trackReferral');

// Attendance API Routes (geo-fenced to office)
$router->post('/api/attendance/punch-in', 'Api\\MobileApiController@punchIn');
$router->post('/api/attendance/punch-out', 'Api\\MobileApiController@punchOut');
$router->get('/api/attendance/status', 'Api\\MobileApiController@attendanceStatus');

// MLM API Routes
$router->get('/api/mlm/analytics', 'App\\Http\\Controllers\\MLMController@getAnalytics');
$router->post('/api/mlm/commission', 'App\\Http\\Controllers\\MLMController@calculateCommission');
$router->get('/api/mlm/network-tree', 'App\\Http\\Controllers\\MLMController@getNetworkTree');
$router->get('/api/mlm/commission-history', 'App\\Http\\Controllers\\MLMController@getCommissionHistory');
$router->get('/api/mlm/my-rank', 'App\\Http\\Controllers\\MLMController@myRank');
$router->get('/api/mlm/member-details', 'App\\Http\\Controllers\\MLMController@getMemberDetails');

// AI Valuation API Routes
$router->post('/api/ai-valuation/calculator', 'App\\Http\\Controllers\\AIValuationController@calculateValuation');
$router->get('/api/ai-valuation/market-trends', 'App\\Http\\Controllers\\AIValuationController@getMarketTrends');
$router->post('/api/ai-valuation/investment-analysis', 'App\\Http\\Controllers\\AIValuationController@getInvestmentAnalysis');

// Legacy Mobile API Routes (v1 — Deprecated, use /api/v2/mobile/*)
// These routes are kept for backward compatibility. Added Sunset/Deprecation headers via middleware.
// v1 routes will be removed in a future release. Please migrate to /api/v2/mobile/* endpoints.
$router->get('/api/v1/mobile/properties', 'Api\MobileApiController@properties')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->get('/api/v1/mobile/properties/{id}', 'Api\MobileApiController@property')
    ->middleware('App\Http\Middleware\ApiAuthMiddleware')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->get('/api/v1/mobile/leads', 'Api\MobileApiController@leads')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->post('/api/v1/mobile/leads', 'Api\MobileApiController@submitLead')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->get('/api/v1/mobile/user/profile', 'Api\MobileApiController@userProfile')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->get('/api/v1/search/properties', 'Api\SearchController@searchProperties')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->post('/api/v1/finance/emi-calculate', 'Api\NewFeaturesApiController@calculateEmi')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');

// Advanced Search API Routes
$router->get('/api/search/properties', 'Api\SearchController@searchProperties');
$router->get('/api/search/suggestions', 'Api\SearchController@getSuggestions');
$router->get('/api/search/facets', 'Api\SearchController@getFacets');
$router->get('/api/search/recent', 'Api\SearchController@getRecentSearches');
$router->get('/api/search/popular', 'Api\SearchController@getPopularSearches');
$router->post('/api/search/clear-cache', 'Api\SearchController@clearCache');

// Voice Agent API Routes
$router->post('/api/voice-agent/start-call', 'App\Http\Controllers\Api\VoiceAgentController@startCall');
$router->post('/api/voice-agent/process-response', 'App\Http\Controllers\Api\VoiceAgentController@processResponse');
$router->get('/api/voice-agent/session/{id}', 'App\Http\Controllers\Api\VoiceAgentController@getSession');
$router->post('/api/voice-agent/end-call', 'App\Http\Controllers\Api\VoiceAgentController@endCall');
$router->get('/api/voice-agent/schedule', 'App\Http\Controllers\Api\VoiceAgentController@getSchedule');
$router->post('/api/voice-agent/schedule', 'App\Http\Controllers\Api\VoiceAgentController@scheduleCall');
$router->get('/api/voice-agent/extracted-leads', 'App\Http\Controllers\Api\VoiceAgentController@getExtractedLeads');
$router->post('/api/voice-agent/extracted-leads/convert/{id}', 'App\Http\Controllers\Api\VoiceAgentController@convertExtractedLead');
$router->get('/api/voice-agent/stats', 'App\Http\Controllers\Api\VoiceAgentController@getStats');
$router->get('/api/voice-agent/call-history', 'App\Http\Controllers\Api\VoiceAgentController@getCallHistory');

// AI Agent API Routes (Mobile App)
$router->post('/api/v2/mobile/ai-agent/chat', 'App\Http\Controllers\Api\AIAgentApiController@chat');
$router->post('/api/v2/mobile/ai-agent/process-lead', 'App\Http\Controllers\Api\AIAgentApiController@processLead');
$router->post('/api/v2/mobile/ai-agent/analyze-property', 'App\Http\Controllers\Api\AIAgentApiController@analyzeProperty');
$router->post('/api/v2/mobile/ai-agent/recommendations', 'App\Http\Controllers\Api\AIAgentApiController@recommendations');
$router->post('/api/v2/mobile/ai-agent/decide', 'App\Http\Controllers\Api\AIAgentApiController@decide');
$router->post('/api/v2/mobile/ai-agent/feedback', 'App\Http\Controllers\Api\AIAgentApiController@feedback');
$router->get('/api/v2/mobile/ai-agent/stats', 'App\Http\Controllers\Api\AIAgentApiController@stats');
$router->get('/api/v2/mobile/ai-agent/analytics', 'App\Http\Controllers\Api\AIAgentApiController@analytics');

// Auto-Dialer API (call scheduling, bulk messaging)
$router->post('/api/v2/mobile/auto-dialer/schedule', 'App\Http\Controllers\Api\AutoDialerController@schedule');
$router->post('/api/v2/mobile/auto-dialer/bulk-schedule', 'App\Http\Controllers\Api\AutoDialerController@bulkSchedule');
$router->get('/api/v2/mobile/auto-dialer/schedule', 'App\Http\Controllers\Api\AutoDialerController@getSchedule');
$router->post('/api/v2/mobile/auto-dialer/cancel/{id}', 'App\Http\Controllers\Api\AutoDialerController@cancel');
$router->post('/api/v2/mobile/auto-dialer/reschedule/{id}', 'App\Http\Controllers\Api\AutoDialerController@reschedule');
$router->get('/api/v2/mobile/auto-dialer/stats', 'App\Http\Controllers\Api\AutoDialerController@stats');
$router->get('/api/v2/mobile/auto-dialer/history', 'App\Http\Controllers\Api\AutoDialerController@history');
$router->post('/api/v2/mobile/auto-dialer/process', 'App\Http\Controllers\Api\AutoDialerController@processQueue');
$router->post('/api/v2/mobile/auto-dialer/send-sms', 'App\Http\Controllers\Api\AutoDialerController@sendSms');
$router->post('/api/v2/mobile/auto-dialer/send-whatsapp', 'App\Http\Controllers\Api\AutoDialerController@sendWhatsApp');
$router->post('/api/v2/mobile/auto-dialer/bulk-sms', 'App\Http\Controllers\Api\AutoDialerController@bulkSms');
$router->post('/api/v2/mobile/auto-dialer/bulk-whatsapp', 'App\Http\Controllers\Api\AutoDialerController@bulkWhatsApp');
$router->post('/api/v2/mobile/auto-dialer/ai-schedule', 'App\Http\Controllers\Api\AutoDialerController@aiSchedule');
$router->post('/api/v2/mobile/calls/log', 'App\Http\Controllers\Api\AutoDialerController@logCall');
$router->get('/api/v2/mobile/calls/stats', 'App\Http\Controllers\Api\AutoDialerController@callStats');
$router->post('/api/v2/mobile/voice-chat', 'App\Http\Controllers\Api\AutoDialerController@voiceChat');

// Gemini Chatbot API (separate from GeminiApiController)
$router->post('/api/gemini/chatbot/message', 'Api\GeminiChatbotController@message');
$router->get('/api/gemini/chatbot/history/{userId}', 'Api\GeminiChatbotController@history');
$router->post('/api/gemini/chatbot/suggestions', 'Api\GeminiChatbotController@suggestions');
$router->post('/api/gemini/chatbot/detect-intent', 'Api\GeminiChatbotController@detectIntent');
$router->get('/api/gemini/chatbot/health', 'Api\GeminiChatbotController@health');

// Social Sharing API
$router->post('/api/sharing/generate', 'Api\SharingController@generate');
$router->post('/api/sharing/track', 'Api\SharingController@trackClick');

// WhatsApp Click Tracking
$router->post('/api/track/whatsapp-click', function() {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $source = $input['source'] ?? 'unknown';
    $page = $input['page'] ?? '';
    $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
    try {
        $db = \App\Core\Database\Database::getInstance();
        $db->execute("INSERT INTO whatsapp_click_log (user_id, source_page, referral_page, clicked_at) VALUES (?, ?, ?, NOW())",
            [$userId, $source, $page]);
    } catch (\Exception $e) {
        error_log("WA track error: " . $e->getMessage());
    }
    echo json_encode(['success' => true]);
});

// Workflow API
$router->get('/api/workflow', 'Api\WorkflowController@index');
$router->get('/api/workflow/{id}', 'Api\WorkflowController@show');
$router->post('/api/workflow', 'Api\WorkflowController@store');
$router->put('/api/workflow/{id}', 'Api\WorkflowController@update');
$router->delete('/api/workflow/{id}', 'Api\WorkflowController@destroy');

// Async Task API
$router->post('/api/async/process', 'App\Http\Controllers\Async\AsyncController@processNextTask');
$router->get('/api/async/tasks', 'App\Http\Controllers\Async\AsyncController@getTasks');
$router->post('/api/async/create', 'App\Http\Controllers\Async\AsyncController@createTaskAjax');
$router->post('/api/async/cancel/{id}', 'App\Http\Controllers\Async\AsyncController@cancelTaskAjax');
$router->post('/api/async/retry/{id}', 'App\Http\Controllers\Async\AsyncController@retryTaskAjax');

// Work Distribution API
$router->get('/api/work-distribution/analytics', 'App\Http\Controllers\Employee\WorkDistributionController@getDistributionAnalytics');

// ============================================================
// NEW API ROUTES (Batch - June 2026)
// ============================================================

// --- Api\AuthController ---
$router->post('/api/auth/login', 'Api\AuthController@login');
$router->get('/api/auth/me', 'Api\AuthController@me');
$router->post('/api/auth/refresh', 'Api\AuthController@refresh');
$router->post('/api/auth/logout', 'Api\AuthController@logout');

// --- Api\MonitorController ---
$router->get('/api/monitor/status', 'Api\\MonitorController@status');
$router->get('/api/monitor/health', 'Api\\MonitorController@health');
$router->get('/api/monitor/performance', 'Api\\MonitorController@performance');
$router->get('/api/monitor/errors', 'Api\\MonitorController@errors');

// ============================================================
// Twilio Voice Webhooks (Cluster 2 - 2026-06-05)
// ============================================================
// Inbound from Twilio. CSRF is bypassed (HMAC-signed by Twilio).
$router->post('/api/twilio/voice', 'Api\TwilioVoiceWebhookController@voice');
$router->post('/api/twilio/voice/status', 'Api\TwilioVoiceWebhookController@status');
$router->post('/api/twilio/voice/recording', 'Api\TwilioVoiceWebhookController@recording');

// ============================================================
// Auto-generated API Documentation (Cluster 4 - 2026-06-05)
// ============================================================
// DocsController introspects the live Router and generates an OpenAPI 3.0 spec.
// CSRF is bypassed for these GET endpoints (read-only, no state mutation).
$router->get('/api/docs', 'App\Http\Controllers\Api\DocsController@index');
$router->get('/api/docs/spec', 'App\Http\Controllers\Api\DocsController@spec');
$router->get('/api/docs/spec/{version}', 'App\Http\Controllers\Api\DocsController@specVersion');
$router->get('/api/docs/list', 'App\Http\Controllers\Api\DocsController@list');
$router->post('/api/twilio/voice/gather', 'Api\TwilioVoiceWebhookController@gather');

// API v1 Routes
$router->get('/api/v1/search/properties', 'Api\SearchController@searchProperties');
$router->post('/api/v1/finance/emi-calculate', 'Api\NewFeaturesApiController@calculateEmi');

// ══════════════════════════════════════════════════════════════
// CRM API (Flutter Lead Management + Pipeline + Follow-ups)
// ══════════════════════════════════════════════════════════════
$crmPrefix = '/api/v2/mobile/crm';

// Public endpoints (no auth — for lead capture forms)
$router->post("$crmPrefix/capture", 'Api\CRMController@captureForm');

// Authenticated endpoints
$router->get("$crmPrefix/dashboard", 'Api\CRMController@dashboard');
$router->get("$crmPrefix/admin-overview", 'Api\CRMController@adminOverview');
$router->get("$crmPrefix/pipeline", 'Api\CRMController@pipeline');
$router->post("$crmPrefix/pipeline/move-stage", 'Api\CRMController@moveStage');
$router->get("$crmPrefix/leads", 'Api\CRMController@leads');
$router->post("$crmPrefix/leads", 'Api\CRMController@createLead');
$router->get("$crmPrefix/leads/{id}", 'Api\CRMController@leadDetail');
$router->put("$crmPrefix/leads/{id}", 'Api\CRMController@updateLead');
$router->delete("$crmPrefix/leads/{id}", 'Api\CRMController@deleteLead');
$router->post("$crmPrefix/leads/{id}/interact", 'Api\CRMController@addInteraction');
$router->get("$crmPrefix/leads/{id}/interactions", 'Api\CRMController@getInteractions');
$router->post("$crmPrefix/leads/{id}/assign", 'Api\CRMController@assignLead');
$router->get("$crmPrefix/tasks", 'Api\CRMController@myTasks');
$router->post("$crmPrefix/tasks", 'Api\CRMController@createTask');
$router->put("$crmPrefix/tasks/{id}/complete", 'Api\CRMController@completeTask');
$router->get("$crmPrefix/campaigns", 'Api\CRMController@campaigns');
$router->post("$crmPrefix/campaigns", 'Api\CRMController@createCampaign');
$router->get("$crmPrefix/forms", 'Api\CRMController@forms');
$router->get("$crmPrefix/admin-employees", 'Api\CRMController@adminEmployees');
$router->get("$crmPrefix/finance-overview", 'Api\CRMController@financeOverview');
$router->get("$crmPrefix/search", 'Api\CRMController@search');
$router->post("$crmPrefix/rescore-all", 'Api\CRMController@rescoreAll');
$router->post("$crmPrefix/rescore/{id}", 'Api\CRMController@rescoreLead');
$router->post("$crmPrefix/auto-assign", 'Api\CRMController@autoAssign');

// ─── CSV Import ──────────────────────────────────────────────────────
$router->post("$crmPrefix/import-csv", 'Api\CRMController@importCsv')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ─── Deal Pipeline ───────────────────────────────────────────────────
$router->get("$crmPrefix/deals", 'Api\CRMController@deals')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post("$crmPrefix/deals", 'Api\CRMController@createDeal')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$crmPrefix/deals/pipeline", 'Api\CRMController@dealPipeline')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$crmPrefix/deals/{id}", 'Api\CRMController@dealDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->put("$crmPrefix/deals/{id}", 'Api\CRMController@updateDeal')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post("$crmPrefix/deals/{id}/move-stage", 'Api\CRMController@moveDealStage')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete("$crmPrefix/deals/{id}", 'Api\CRMController@deleteDeal')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ─── Score Breakdown ─────────────────────────────────────────────────
$router->get("$crmPrefix/leads/{id}/score-breakdown", 'Api\CRMController@scoreBreakdown')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ─── Lead Timeline ───────────────────────────────────────────────────
$router->get("$crmPrefix/leads/{id}/timeline", 'Api\CRMController@leadTimeline')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ─── Commission Estimate ─────────────────────────────────────────────
$router->get("$crmPrefix/leads/{id}/commission-estimate", 'Api\CRMController@commissionEstimate')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ─── Follow-up Reminders ─────────────────────────────────────────────
$router->get("$crmPrefix/reminders", 'Api\CRMController@followUpReminders')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ─── Bulk Operations ─────────────────────────────────────────────────
$router->post("$crmPrefix/bulk-update", 'Api\CRMController@bulkUpdate')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ─── Analytics ───────────────────────────────────────────────────────
$router->get("$crmPrefix/analytics/sources", 'Api\CRMController@sourceAnalytics')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$crmPrefix/analytics/funnel", 'Api\CRMController@conversionFunnel')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$crmPrefix/analytics/agents", 'Api\CRMController@agentPerformance')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ══════════════════════════════════════════════════════════════
// FLUTTER ROUTE ALIASES — Map /api/v2/mobile/* to existing routes
// Flutter app sends ALL requests under /api/v2/mobile/ prefix
// but backend routes for employee, bookings, etc use different prefixes
// ══════════════════════════════════════════════════════════════

// Employee routes (existing at /api/mobile/v2/employee/*)
$router->get('/api/v2/mobile/employee/dashboard', 'Api\MobileApiController@employeeDashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/employee/tasks', 'Api\MobileApiController@employeeTasks')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/employee/attendance', 'Api\MobileApiController@employeeAttendance')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Bookings routes (existing at /api/mobile/v2/bookings)
$router->get('/api/v2/mobile/bookings', 'Api\MobileApiController@listBookings')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/bookings/{id}', 'Api\MobileApiController@bookingDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/bookings/{id}/pay', 'Api\MobileApiController@recordBookingPayment')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// MLM aliases (Flutter calls /mlm/commissions, /mlm/network, etc but routes exist at different names)
$router->get('/api/v2/mobile/mlm/commissions', 'Api\MobileApiController@getMlmPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/network', 'Api\MobileApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/network/tree', 'Api\MobileApiController@getGenealogy')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/dashboard', 'Api\MobileApiController@getMlmSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/rank', 'Api\MobileApiController@getRankProgress')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/direct-referrals', 'Api\MobileApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/referrals', 'Api\MobileApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Commission Simulation API (admin-only, for what-if analysis)
$router->post('/api/commission/simulate', 'Api\CommissionSimulationController@simulate');
$router->post('/api/commission/simulate-bulk', 'Api\CommissionSimulationController@simulateBulk');
$router->get('/api/commission/tds', 'Api\CommissionSimulationController@tdsCalc');
$router->get('/api/commission/summary/{id}', 'Api\CommissionSimulationController@summary');

// Referral routes — point to MobileApiController (Api\ReferralController doesn't exist)
$router->get('/api/v2/mobile/referral/stats', 'Api\MobileApiController@getMlmSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/referral/dashboard', 'Api\MobileApiController@getMlmSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/referral/list', 'Api\MobileApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/referral/track', 'Api\MobileApiController@trackReferral')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// KYC routes (existing at /api/kyc/*)
$router->get('/api/v2/mobile/kyc/status', 'Api\KYCController@getStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/kyc/verify-pan', 'Api\KYCController@verifyPan')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/kyc/verify-aadhaar', 'Api\KYCController@verifyAadhaar')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Attendance routes (existing at /api/attendance/*)
$router->get('/api/v2/mobile/attendance/status', 'Api\MobileApiController@attendanceStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/attendance/punch-in', 'Api\MobileApiController@punchIn')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/attendance/punch-out', 'Api\MobileApiController@punchOut')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Notification registration (existing at /api/mobile/notifications/*)
$router->post('/api/v2/mobile/notifications/register', 'Api\MobileApiController@registerPushTokenV2')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// CRM analytics aliases (Flutter calls /crm/analytics, /crm/team-performance)
$router->get('/api/v2/mobile/crm/analytics', 'Api\CRMController@adminOverview');

// ══════════════════════════════════════════════════════════════
// MOBILE API V2 — User Bank Accounts (dedicated)
// ══════════════════════════════════════════════════════════════
$router->get('/api/v2/mobile/user/bank-accounts', 'Api\MobileApiController@getUserBankAccounts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/bank-accounts', 'Api\MobileApiController@saveUserBankAccount')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/user/bank-accounts', 'Api\MobileApiController@deleteUserBankAccount')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ══════════════════════════════════════════════════════════════
// MOBILE API V2 — User Addresses (dedicated)
// ══════════════════════════════════════════════════════════════
$router->get('/api/v2/mobile/user/addresses', 'Api\MobileApiController@getUserAddresses')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/addresses', 'Api\MobileApiController@saveUserAddress')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/user/addresses', 'Api\MobileApiController@deleteUserAddress')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ══════════════════════════════════════════════════════════════
// MOBILE API V2 — Payment History (standalone)
// ══════════════════════════════════════════════════════════════
$router->get('/api/v2/mobile/user/payment-history', 'Api\MobileApiController@getPaymentHistory')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ══════════════════════════════════════════════════════════════
// MOBILE API V2 — Blog / News (public)
// ══════════════════════════════════════════════════════════════
$router->get('/api/v2/mobile/blog', 'Api\MobileApiController@getBlogPosts');
$router->get('/api/v2/mobile/blog/{slug}', 'Api\MobileApiController@getBlogPostDetail');

// ══════════════════════════════════════════════════════════════
// MOBILE API V2 — About Us (public)
// ══════════════════════════════════════════════════════════════
$router->get('/api/v2/mobile/about', 'Api\MobileApiController@getAboutInfo');

// ══════════════════════════════════════════════════════════════
// MOBILE API V2 — Careers / Jobs (public + auth for apply)
// ══════════════════════════════════════════════════════════════
$router->get('/api/v2/mobile/careers', 'Api\MobileApiController@getJobListings');
$router->post('/api/v2/mobile/careers/apply', 'Api\MobileApiController@submitJobApplication')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/careers/{id}', 'Api\MobileApiController@getJobDetail');

// ══════════════════════════════════════════════════════════════

// ============================================================
// MOBILE API V2 -- Missing Flutter Route Aliases
// Flutter calls PUT user/profile, POST user/profile/avatar,
// GET /dashboard -- these routes map to existing methods
// ============================================================
$router->put('/api/v2/mobile/user/profile', 'Api\MobileApiController@updateProfileV2')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/profile/avatar', 'Api\MobileApiController@uploadAvatar')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/dashboard', 'Api\MobileApiController@dashboardV3')->middleware('App\Http\Middleware\ApiAuthMiddleware');
// ADMIN MOBILE API — JSON endpoints for Flutter admin pages
// ══════════════════════════════════════════════════════════════
$adminMobilePrefix = '/api/v2/mobile/admin';
$router->get("$adminMobilePrefix/bookings", 'Api\AdminMobileController@bookings')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post("$adminMobilePrefix/bookings/{id}/status", 'Api\AdminMobileController@updateBookingStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$adminMobilePrefix/commissions", 'Api\AdminMobileController@commissions')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post("$adminMobilePrefix/commissions/{id}/action", 'Api\AdminMobileController@commissionAction')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$adminMobilePrefix/plots", 'Api\AdminMobileController@plots')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$adminMobilePrefix/users", 'Api\AdminMobileController@users')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$adminMobilePrefix/reports", 'Api\AdminMobileController@reports')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$adminMobilePrefix/telecaller-dashboard", 'Api\AdminMobileController@telecallerDashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/crm/team-performance', 'Api\CRMController@dashboard');

// ══════════════════════════════════════════════════════════════
// MOBILE API V2 — Flutter Route Alias Expansion Batch
// Maps /api/v2/mobile/* calls from Flutter to existing controllers
// ══════════════════════════════════════════════════════════════

// --- Auth (password mgmt, OTP, phone checks) ---
$router->post('/api/v2/mobile/auth/forgot-password', 'Api\MobileApiController@forgotPassword');
$router->post('/api/v2/mobile/auth/verify-otp', 'Api\MobileApiController@verifyOtp');
$router->post('/api/v2/mobile/auth/resend-otp', 'Api\MobileApiController@resendOtp');
$router->post('/api/v2/mobile/auth/reset-password', 'Api\MobileApiController@resetPassword');
$router->post('/api/v2/mobile/auth/change-password', 'Api\MobileApiController@changePassword')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/auth/refresh', 'Api\MobileApiController@refreshV2');
$router->get('/api/v2/mobile/auth/check-user', 'Api\MobileApiController@checkUser');
$router->get('/api/v2/mobile/auth/referrer', 'Api\MobileApiController@getReferrer');

// --- Leads (flat pattern) — alias to CRMController ---
$router->get('/api/v2/mobile/leads', 'Api\CRMController@leads')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/create', 'Api\CRMController@createLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/leads/{id}', 'Api\CRMController@leadDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->put('/api/v2/mobile/leads/{id}', 'Api\CRMController@updateLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/leads/search', 'Api\CRMController@search')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/leads/follow-ups', 'Api\CRMController@followUpReminders')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/status', 'Api\MobileApiController@changeLeadStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/follow-up', 'Api\MobileApiController@scheduleLeadFollowup')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/activities', 'Api\MobileApiController@addLeadActivity')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/convert', 'Api\MobileApiController@convertLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/lost', 'Api\MobileApiController@markLeadLost')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/leads/statistics', 'Api\MobileApiController@getLeadStatistics')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/call-logs', 'Api\MobileApiController@logLeadCall')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Bookings (full CRUD) ---
$router->post('/api/v2/mobile/bookings', 'Api\MobileApiController@createBookingRequest')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->put('/api/v2/mobile/bookings/{id}', 'Api\MobileApiController@updateBooking')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/bookings/{id}', 'Api\MobileApiController@cancelBooking')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Deals ---
$router->get('/api/v2/mobile/deals', 'Api\CRMController@deals')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Properties (favorite, similar, colony properties) ---
$router->post('/api/v2/mobile/properties/{id}/favorite', 'Api\MobileApiController@toggleFavorite')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/{id}/similar', 'Api\MobileApiController@getSimilarProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/colonies/{colonyId}/properties', 'Api\MobileApiController@getColonyProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Notifications (individual read/delete) ---
$router->post('/api/v2/mobile/user/notifications/{id}/read', 'Api\MobileApiController@markNotificationRead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/user/notifications/{id}', 'Api\MobileApiController@deleteNotification')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Referral (customer dashboard, share tracking) ---
$router->get('/api/v2/mobile/user/referral/dashboard', 'Api\MobileApiController@getReferralDashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/referral/share', 'Api\MobileApiController@trackReferralShare')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Support Tickets ---
$router->get('/api/v2/mobile/user/support/tickets', 'Api\MobileApiController@getSupportTickets')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/support/tickets', 'Api\MobileApiController@createSupportTicket')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/support/tickets/{id}', 'Api\MobileApiController@getSupportTicketDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Settings ---
$router->post('/api/v2/mobile/user/notification-preferences', 'Api\MobileApiController@updateNotificationPreferences')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/preferences', 'Api\MobileApiController@updateUserPreferences')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/account/delete', 'Api\MobileApiController@deleteAccount')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Admin Analytics ---
$router->get('/api/v2/mobile/admin/dashboard-stats', 'Api\AdminMobileController@dashboardStats')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/sales-trend', 'Api\AdminMobileController@salesTrend')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/top-associates', 'Api\AdminMobileController@topAssociates')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/colony-performance', 'Api\AdminMobileController@colonyPerformance')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/emi-collection', 'Api\AdminMobileController@emiCollection')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/lead-conversion', 'Api\AdminMobileController@leadConversion')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/daily-sales', 'Api\AdminMobileController@dailySales')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Authentication (firebase login) ---
$router->post('/api/v2/mobile/auth/firebase-login', 'Api\MobileApiController@firebaseLogin');

// --- Misc (employee tasks with wrong path) ---
$router->get('/api/v2/mobile/employee/api/tasks', 'Api\MobileApiController@employeeTasks')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- MLM aliases ---
$router->get('/api/v2/mobile/mlm/team-performance', 'Api\CRMController@dashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/mlm/process-sale', 'Api\MobileApiController@processMlmSale')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/mlm/upgrade-rank', 'Api\MobileApiController@upgradeMlmRank')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/form16', 'Api\MobileApiController@getForm16')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/tax-summary', 'Api\MobileApiController@getTaxSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Notification/lead assignment webhook ---
$router->post('/api/v2/mobile/notification', 'Api\MobileApiController@createNotification')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Company Loan API ---
$router->get('/api/v2/mobile/loans', 'Api\MobileApiController@getLoans')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/loans/{id}', 'Api\MobileApiController@getLoanDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/loans/{id}/installments', 'Api\MobileApiController@getLoanInstallments')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/loans/apply', 'Api\MobileApiController@applyLoan')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/loans/offers', 'Api\MobileApiController@getLoanOffers');
$router->post('/api/v2/mobile/loans/calculate-eligibility', 'Api\MobileApiController@calculateLoanEligibility');
$router->get('/api/v2/mobile/loans/early-settlement/{id}', 'Api\MobileApiController@getEarlySettlement')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Legal Documents API ---
$router->get('/api/v2/mobile/legal/documents', 'Api\LegalApiController@getDocuments')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/legal/documents/{id}', 'Api\LegalApiController@getDocumentDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/legal/documents/{id}/upload', 'Api\LegalApiController@uploadDocument')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/legal/categories', 'Api\LegalApiController@getCategories');
$router->get('/api/v2/mobile/legal/templates', 'Api\LegalApiController@getTemplates');
$router->get('/api/v2/mobile/legal/documents/{id}/preview', 'Api\LegalApiController@previewDocument')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- In-App Messaging API ---
$router->get('/api/v2/mobile/messages/conversations', 'Api\MobileApiController@getConversations')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/messages/{otherUserId}', 'Api\MobileApiController@getMessages')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/messages/send', 'Api\MobileApiController@sendMessage')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/messages/read/{otherUserId}', 'Api\MobileApiController@markMessagesRead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/messages/unread/count', 'Api\MobileApiController@getUnreadCount')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Stamp Duty Calculator API (mobile) ---
$router->post('/api/v2/mobile/stamp-duty/calculate', 'Api\StampDutyController@calculate');
$router->get('/api/v2/mobile/stamp-duty/rates', 'Api\StampDutyController@getRates');
$router->get('/api/v2/mobile/stamp-duty/states', 'Api\StampDutyController@getStates');
$router->get('/api/v2/mobile/stamp-duty/circle-rate', 'Api\StampDutyController@getCircleRate');
$router->get('/api/v2/mobile/stamp-duty/circle-rates', 'Api\StampDutyController@searchCircleRates');

// --- Property Tax Calculator API (mobile) ---
$router->post('/api/v2/mobile/property-tax/calculate', 'Api\PropertyTaxController@calculate');
$router->get('/api/v2/mobile/property-tax/rates', 'Api\PropertyTaxController@getRates');
$router->get('/api/v2/mobile/property-tax/search', 'Api\PropertyTaxController@search');
$router->get('/api/v2/mobile/property-tax/states', 'Api\PropertyTaxController@getStates');

// --- Landmarks & Neighborhood API (mobile) ---
$router->get('/api/v2/mobile/landmarks/nearby', 'Api\LandmarksApiController@nearby');
$router->get('/api/v2/mobile/landmarks/list', 'Api\LandmarksApiController@list');
$router->get('/api/v2/mobile/landmarks/types', 'Api\LandmarksApiController@types');
$router->get('/api/v2/mobile/landmarks/colony/{colonyId}', 'Api\LandmarksApiController@byColony');

// --- RERA Verification API (mobile) ---
$router->get('/api/v2/mobile/rera/verify/{reraNumber}', 'Api\MobileApiController@reraVerify');
$router->get('/api/v2/mobile/rera/search', 'Api\MobileApiController@reraSearch');
$router->get('/api/v2/mobile/rera/projects', 'Api\MobileApiController@reraProjects');

// --- Directory API (mobile) ---
$router->get('/api/v2/mobile/directory/categories', 'Api\MobileApiController@directoryCategories');
$router->get('/api/v2/mobile/directory/featured', 'Api\MobileApiController@directoryFeatured');
$router->get('/api/v2/mobile/directory/jobs', 'Api\MobileApiController@directoryJobs');

// --- Property Valuation API (mobile) ---
$router->post('/api/v2/mobile/property-valuation/calculate', 'Api\MobileApiController@propertyValuation');
$router->get('/api/v2/mobile/property-valuation/cities', 'Api\MobileApiController@valuationCities');

// --- Property Marketplace APIs (my listings, packages, messaging, boost) ---
$router->get('/api/v2/mobile/my-listings', 'Api\MobileApiController@getMyListings')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/listing-packages', 'Api\MobileApiController@getListingPackages');
$router->post('/api/v2/mobile/properties/inquiry', 'Api\MobileApiController@submitPropertyInquiry');
$router->post('/api/v2/mobile/properties/message', 'Api\MobileApiController@sendPropertyMessage')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/{id}/messages', 'Api\MobileApiController@getPropertyMessages')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/properties/boost', 'Api\MobileApiController@boostProperty')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/properties/messages/{id}/read', 'Api\MobileApiController@markMessageRead')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Listing Upgrade Payment ---
$router->post('/api/v2/mobile/listing/create-order', 'Api\ListingPaymentController@createOrder')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/listing/verify-payment', 'Api\ListingPaymentController@verifyPayment')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/listing/activate-free', 'Api\ListingPaymentController@activateFree')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- DigiLocker API (KYC / document fetch) ---
$router->get('/api/digilocker/auth-url', 'Api\DigiLockerController@getAuthUrl');
$router->get('/api/digilocker/callback', 'Api\DigiLockerController@callback');
$router->get('/api/digilocker/user-data', 'Api\DigiLockerController@getUserData');
$router->post('/api/digilocker/kyc/initiate', 'Api\DigiLockerController@initiateKyc');

// --- Document AI API (extraction jobs) ---
$router->post('/api/document-ai/extract', 'Api\DocumentAIController@createJob');
$router->post('/api/document-ai/process/{jobId}', 'Api\DocumentAIController@processJob');
$router->get('/api/document-ai/job/{jobId}', 'Api\DocumentAIController@getJob');
$router->get('/api/document-ai/jobs', 'Api\DocumentAIController@listJobs');
$router->post('/api/document-ai/review/{jobId}', 'Api\DocumentAIController@reviewJob');
$router->get('/api/document-ai/template/{documentType}', 'Api\DocumentAIController@getTemplate');
$router->get('/api/document-ai/engines', 'Api\DocumentAIController@getEngines');
$router->get('/api/document-ai/document-types', 'Api\DocumentAIController@getDocumentTypes');

// --- eSign API ---
$router->post('/api/esign/initiate', 'Api\ESignController@initiate');
$router->post('/api/esign/verify-otp', 'Api\ESignController@verifyOtp');
$router->get('/api/esign/status/{transactionId}', 'Api\ESignController@getStatus');
$router->get('/api/esign/document/{transactionId}', 'Api\ESignController@getDocument');
$router->get('/api/esign/booking/{bookingId}', 'Api\ESignController@getByBooking');
$router->post('/api/esign/callback', 'Api\ESignController@callback');

// ============================================================
// INFRASTRUCTURE & DEBUGGING API (Admin Tools)
// ============================================================
require_once __DIR__ . '/container.php';
require_once __DIR__ . '/performance-cache.php';
require_once __DIR__ . '/request-middleware.php';
