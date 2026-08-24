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

$router->post('/api/v2/mobile/auth/login', 'Api\MobileAuthApiController@login');
$router->post('/api/v2/mobile/auth/register', 'Api\MobileAuthApiController@register');
$router->post('/api/v2/mobile/auth/logout', 'Api\MobileAuthApiController@logout');
$router->post('/api/v2/mobile/auth/google-login', 'Api\MobileAuthApiController@googleLogin');
$router->get('/api/v2/mobile/sync', 'Api\MobileSyncApiController@syncProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads', 'Api\CRMController@createLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/batch-sync', 'Api\MobileSyncApiController@batchSyncLeads')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties', 'Api\MobilePropertyApiController@syncProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/updates', 'Api\MobileSyncApiController@getUpdates')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/sync', 'Api\MobileSyncApiController@sync')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/summary', 'Api\MobileMLMApiController@getMlmSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/payouts', 'Api\MobileMLMApiController@getMlmPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/incentives', 'Api\MobileMLMApiController@getMlmIncentives')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/upload-document', 'Api\MobileUserApiController@uploadDocument')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/documents', 'Api\MobileUserApiController@getDocuments')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/start', 'Api\MobileBookingApiController@startSiteVisit')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/update', 'Api\MobileBookingApiController@updateSiteVisitLocation')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/complete', 'Api\MobileBookingApiController@completeSiteVisit')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/site-visit/status', 'Api\MobileBookingApiController@getSiteVisitStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/site-visit/slots', 'Api\MobileBookingApiController@getAvailableSlots');
$router->post('/api/v2/mobile/site-visit/book', 'Api\MobileBookingApiController@bookSiteVisitApi')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/site-visit/my-visits', 'Api\MobileBookingApiController@getMySiteVisits')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/cancel', 'Api\MobileBookingApiController@cancelSiteVisitApi')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/reschedule', 'Api\MobileBookingApiController@rescheduleSiteVisitApi')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/profile', 'Api\MobileUserApiController@getUserProfile')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/payouts/pending', 'Api\MobileUserApiController@getPendingPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/payouts/process', 'Api\MobileUserApiController@processPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/payouts/history', 'Api\MobileUserApiController@getPayoutHistory')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/genealogy', 'Api\MobileMLMApiController@getGenealogy')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/business-breakdown', 'Api\MobileMLMApiController@getBusinessBreakdown')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/my-team', 'Api\MobileMLMApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/rank-progress', 'Api\MobileMLMApiController@getRankProgress')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/mlm/request-payout', 'Api\MobileMLMApiController@requestPayout')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/customer/bookings', 'Api\MobileUserApiController@getCustomerBookings')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/customer/emi-schedule', 'Api\MobileUserApiController@getEmiSchedule')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/customer/pay-emi', 'Api\MobileUserApiController@makeEmiPayment')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/properties/submit', 'Api\MobileAdminApiController@submitProperty')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/my-submissions', 'Api\MobileAdminApiController@getSubmissions')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 - MLM Engagement, Incentives, Packages
// ============================================================
$router->get('/api/v2/mobile/mlm/incentives/summary', 'Api\MobileMLMApiController@getIncentiveSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/incentives/targets', 'Api\MobileMLMApiController@getMonthlyTargets')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/packages', 'Api\MobileMLMApiController@listPackages')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/mlm/packages/purchase', 'Api\MobileMLMApiController@purchasePackage')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/goals', 'Api\MobileMLMApiController@getGoals')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/leaderboard/{metricType}', 'Api\MobileMLMApiController@getLeaderboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/form-data/{type}', 'Api\MobileMLMApiController@getFormData')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 â€” User Favorites
// ============================================================
$router->get('/api/v2/mobile/user/favorites', 'Api\MobilePropertyApiController@getFavorites')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/documents', 'Api\MobileUserApiController@getCustomerDocuments')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 â€” User Notifications
// ============================================================
$router->get('/api/v2/mobile/user/notifications', 'Api\MobileUserApiController@getCustomerNotifications')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/notifications/read', 'Api\MobileUserApiController@markNotificationsRead')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 â€” FCM Token Registration
// ============================================================
$router->post('/api/v2/mobile/fcm/register', 'Api\MobileAuthApiController@registerFcmToken')->middleware('App\Http\Middleware\ApiAuthMiddleware');

$router->post('/api/v2/mobile/user/favorites', 'Api\MobilePropertyApiController@addFavorite')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/user/favorites/{id}', 'Api\MobilePropertyApiController@removeFavorite')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/favorites/check', 'Api\MobilePropertyApiController@checkFavorite')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/favorites/stats', 'Api\MobilePropertyApiController@getFavoritesStats')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 â€” Colonies & Plots (Public Browsing)
// ============================================================
$router->get('/api/v2/mobile/colonies', 'Api\MobilePropertyApiController@getColonies');
$router->get('/api/v2/mobile/colonies/search', 'Api\MobilePropertyApiController@searchColonies');
$router->get('/api/v2/mobile/colonies/{id}', 'Api\MobilePropertyApiController@getColonyDetail');
$router->get('/api/v2/mobile/colonies/{id}/stats', 'Api\MobilePropertyApiController@getColonyStats');
$router->get('/api/v2/mobile/colonies/{id}/plots', 'Api\MobilePropertyApiController@getColonyPlots');
$router->get('/api/v2/mobile/colonies/{id}/health', 'Api\MobilePropertyApiController@getColonyHealth');
$router->get('/api/v2/mobile/colonies/health/all', 'Api\MobilePropertyApiController@getAllColoniesHealth');

$router->get('/api/v2/mobile/plots/{id}', 'Api\MobilePropertyApiController@getPlotDetail');
$router->post('/api/v2/mobile/plots/{id}/hold', 'Api\MobilePropertyApiController@holdPlot')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/plots/{id}/release', 'Api\MobilePropertyApiController@releasePlot')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// MOBILE API V2 â€” Property Marketplace / Premium Listings
// ============================================================
$router->get('/api/v2/mobile/marketplace', 'Api\MobilePropertyApiController@getMarketplace');
$router->get('/api/v2/mobile/marketplace/premium', 'Api\MobilePropertyApiController@getPremiumProperties');

// Public property browsing (no auth required)
$router->get('/api/v2/mobile/properties/browse', 'Api\MobilePropertyApiController@properties');
$router->get('/api/v2/mobile/plots/all', 'Api\MobilePropertyApiController@getAllPlots');

// Property detail + search under /api/v2/mobile/ prefix
$router->get('/api/v2/mobile/properties/featured', 'Api\MobilePropertyApiController@getFeaturedProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/{id}', 'Api\MobilePropertyApiController@propertyDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/search', 'Api\MobilePropertyApiController@searchProperties');

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
$router->post('/api/mobile/auth/login', 'Api\MobileAuthApiController@loginV2');
$router->post('/api/mobile/auth/refresh', 'Api\MobileAuthApiController@refreshV2');
$router->get('/api/mobile/profile', 'Api\MobileUserApiController@profileV2');
$router->get('/api/mobile/properties', 'Api\MobilePropertyApiController@properties');
$router->get('/api/mobile/dashboard', 'Api\MobileUserApiController@dashboardV2');
$router->post('/api/mobile/notifications/register', 'Api\MobileUserApiController@registerPushTokenV2');

// ============================================================
// MOBILE API V2 EXTENDED â€” Properties, Bookings, Inquiries, Profile
// ============================================================
$router->get('/api/mobile/v2/properties', 'Api\MobilePropertyApiController@browseProperties');
$router->get('/api/mobile/v2/properties/search', 'Api\MobilePropertyApiController@searchProperties');
$router->get('/api/mobile/v2/properties/{id}', 'Api\MobilePropertyApiController@propertyDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/mobile/v2/bookings', 'Api\MobileBookingApiController@listBookings');
$router->get('/api/mobile/v2/bookings/{id}', 'Api\MobileBookingApiController@bookingDetail');
$router->post('/api/mobile/v2/bookings/{id}/pay', 'Api\MobileBookingApiController@recordBookingPayment');
$router->post('/api/mobile/v2/inquiries', 'Api\MobileUserApiController@submitInquiryV2');
$router->get('/api/mobile/v2/inquiries', 'Api\MobileUserApiController@listInquiries');
$router->put('/api/mobile/v2/profile', 'Api\MobileUserApiController@updateProfileV2');
$router->get('/api/mobile/v2/dashboard', 'Api\MobileUserApiController@dashboardV3');

// Employee Dashboard API Routes (mobile v2)
$router->get('/api/mobile/v2/employee/dashboard', 'Api\MobileAdminApiController@employeeDashboard');
$router->get('/api/mobile/v2/employee/tasks', 'Api\MobileAdminApiController@employeeTasks');
$router->get('/api/mobile/v2/employee/attendance', 'Api\MobileAdminApiController@employeeAttendance');

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
$router->post('/api/referral/track', 'Api\MobileMLMApiController@trackReferral');

// Attendance API Routes (geo-fenced to office)
$router->post('/api/attendance/punch-in', 'Api\MobileMLMApiController@punchIn');
$router->post('/api/attendance/punch-out', 'Api\MobileMLMApiController@punchOut');
$router->get('/api/attendance/status', 'Api\MobileMLMApiController@attendanceStatus');

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

// Legacy Mobile API Routes (v1 â€” Deprecated, use /api/v2/mobile/*)
// These routes are kept for backward compatibility. Added Sunset/Deprecation headers via middleware.
// v1 routes will be removed in a future release. Please migrate to /api/v2/mobile/* endpoints.
$router->get('/api/v1/mobile/properties', 'Api\MobilePropertyApiController@properties')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->get('/api/v1/mobile/properties/{id}', 'Api\MobilePropertyApiController@property')
    ->middleware('App\Http\Middleware\ApiAuthMiddleware')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->get('/api/v1/mobile/leads', 'Api\CRMController@leads')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->post('/api/v1/mobile/leads', 'Api\CRMController@createLead')
    ->middleware('App\Http\Middleware\DeprecationHeaderMiddleware');
$router->get('/api/v1/mobile/user/profile', 'Api\MobileUserApiController@userProfile')
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

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
// CRM API (Flutter Lead Management + Pipeline + Follow-ups)
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
$crmPrefix = '/api/v2/mobile/crm';

// Public endpoints (no auth â€” for lead capture forms)
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

// â”€â”€â”€ CSV Import â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$router->post("$crmPrefix/import-csv", 'Api\CRMController@importCsv')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â”€â”€â”€ Deal Pipeline â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$router->get("$crmPrefix/deals", 'Api\CRMController@deals')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post("$crmPrefix/deals", 'Api\CRMController@createDeal')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$crmPrefix/deals/pipeline", 'Api\CRMController@dealPipeline')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$crmPrefix/deals/{id}", 'Api\CRMController@dealDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->put("$crmPrefix/deals/{id}", 'Api\CRMController@updateDeal')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post("$crmPrefix/deals/{id}/move-stage", 'Api\CRMController@moveDealStage')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete("$crmPrefix/deals/{id}", 'Api\CRMController@deleteDeal')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â”€â”€â”€ Score Breakdown â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$router->get("$crmPrefix/leads/{id}/score-breakdown", 'Api\CRMController@scoreBreakdown')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â”€â”€â”€ Lead Timeline â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$router->get("$crmPrefix/leads/{id}/timeline", 'Api\CRMController@leadTimeline')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â”€â”€â”€ Commission Estimate â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$router->get("$crmPrefix/leads/{id}/commission-estimate", 'Api\CRMController@commissionEstimate')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â”€â”€â”€ Follow-up Reminders â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$router->get("$crmPrefix/reminders", 'Api\CRMController@followUpReminders')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â”€â”€â”€ Bulk Operations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$router->post("$crmPrefix/bulk-update", 'Api\CRMController@bulkUpdate')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â”€â”€â”€ Analytics â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$router->get("$crmPrefix/analytics/sources", 'Api\CRMController@sourceAnalytics')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$crmPrefix/analytics/funnel", 'Api\CRMController@conversionFunnel')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get("$crmPrefix/analytics/agents", 'Api\CRMController@agentPerformance')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
// FLUTTER ROUTE ALIASES â€” Map /api/v2/mobile/* to existing routes
// Flutter app sends ALL requests under /api/v2/mobile/ prefix
// but backend routes for employee, bookings, etc use different prefixes
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�

// Employee routes (existing at /api/mobile/v2/employee/*)
$router->get('/api/v2/mobile/employee/dashboard', 'Api\MobileAdminApiController@employeeDashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/employee/tasks', 'Api\MobileAdminApiController@employeeTasks')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/employee/attendance', 'Api\MobileAdminApiController@employeeAttendance')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Bookings routes (existing at /api/mobile/v2/bookings)
$router->get('/api/v2/mobile/bookings', 'Api\MobileBookingApiController@listBookings')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/bookings/{id}', 'Api\MobileBookingApiController@bookingDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/bookings/{id}/pay', 'Api\MobileBookingApiController@recordBookingPayment')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// MLM aliases (Flutter calls /mlm/commissions, /mlm/network, etc but routes exist at different names)
$router->get('/api/v2/mobile/mlm/commissions', 'Api\MobileMLMApiController@getMlmPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/network', 'Api\MobileMLMApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/network/tree', 'Api\MobileMLMApiController@getGenealogy')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/dashboard', 'Api\MobileMLMApiController@getMlmSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/rank', 'Api\MobileMLMApiController@getRankProgress')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/direct-referrals', 'Api\MobileMLMApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/referrals', 'Api\MobileMLMApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Commission Simulation API (admin-only, for what-if analysis)
$router->post('/api/commission/simulate', 'Api\CommissionSimulationController@simulate');
$router->post('/api/commission/simulate-bulk', 'Api\CommissionSimulationController@simulateBulk');
$router->get('/api/commission/tds', 'Api\CommissionSimulationController@tdsCalc');
$router->get('/api/commission/summary/{id}', 'Api\CommissionSimulationController@summary');

// Referral routes â€” point to MobileApiController (Api\ReferralController doesn't exist)
$router->get('/api/v2/mobile/referral/stats', 'Api\MobileMLMApiController@getMlmSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/referral/dashboard', 'Api\MobileMLMApiController@getMlmSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/referral/list', 'Api\MobileMLMApiController@getMyTeam')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/referral/track', 'Api\MobileMLMApiController@trackReferral')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// KYC routes (existing at /api/kyc/*)
$router->get('/api/v2/mobile/kyc/status', 'Api\KYCController@getStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/kyc/verify-pan', 'Api\KYCController@verifyPAN')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/kyc/verify-aadhaar', 'Api\KYCController@verifyAadhaar')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Attendance routes (existing at /api/attendance/*)
$router->get('/api/v2/mobile/attendance/status', 'Api\MobileMLMApiController@attendanceStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/attendance/punch-in', 'Api\MobileMLMApiController@punchIn')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/attendance/punch-out', 'Api\MobileMLMApiController@punchOut')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Notification registration (existing at /api/mobile/notifications/*)
$router->post('/api/v2/mobile/notifications/register', 'Api\MobileUserApiController@registerPushTokenV2')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// CRM analytics aliases (Flutter calls /crm/analytics, /crm/team-performance)
$router->get('/api/v2/mobile/crm/analytics', 'Api\CRMController@adminOverview');

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
// MOBILE API V2 â€” User Bank Accounts (dedicated)
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
$router->get('/api/v2/mobile/user/bank-accounts', 'Api\MobileUserApiController@getUserBankAccounts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/bank-accounts', 'Api\MobileUserApiController@saveUserBankAccount')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/user/bank-accounts', 'Api\MobileUserApiController@deleteUserBankAccount')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
// MOBILE API V2 â€” User Addresses (dedicated)
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
$router->get('/api/v2/mobile/user/addresses', 'Api\MobileUserApiController@getUserAddresses')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/addresses', 'Api\MobileUserApiController@saveUserAddress')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/user/addresses', 'Api\MobileUserApiController@deleteUserAddress')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
// MOBILE API V2 â€” Payment History (standalone)
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
$router->get('/api/v2/mobile/user/payment-history', 'Api\MobileUserApiController@getPaymentHistory')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
// MOBILE API V2 â€” Blog / News (public)
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
$router->get('/api/v2/mobile/blog', 'Api\MobileUserApiController@getBlogPosts');
$router->get('/api/v2/mobile/blog/{slug}', 'Api\MobileUserApiController@getBlogPostDetail');

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
// MOBILE API V2 â€” About Us (public)
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
$router->get('/api/v2/mobile/about', 'Api\MobileUserApiController@getAboutInfo');

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
// MOBILE API V2 â€” Careers / Jobs (public + auth for apply)
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
$router->get('/api/v2/mobile/careers', 'Api\MobileUserApiController@getJobListings');
$router->post('/api/v2/mobile/careers/apply', 'Api\MobileUserApiController@submitJobApplication')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/careers/{id}', 'Api\MobileUserApiController@getJobDetail');

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�

// ============================================================
// MOBILE API V2 -- Missing Flutter Route Aliases
// Flutter calls PUT user/profile, POST user/profile/avatar,
// GET /dashboard -- these routes map to existing methods
// ============================================================
$router->put('/api/v2/mobile/user/profile', 'Api\MobileUserApiController@updateProfileV2')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/profile/avatar', 'Api\MobileUserApiController@uploadAvatar')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/dashboard', 'Api\MobileUserApiController@dashboardV3')->middleware('App\Http\Middleware\ApiAuthMiddleware');
// ADMIN MOBILE API â€” JSON endpoints for Flutter admin pages
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
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

// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
// MOBILE API V2 â€” Flutter Route Alias Expansion Batch
// Maps /api/v2/mobile/* calls from Flutter to existing controllers
// â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�

// --- Auth (password mgmt, OTP, phone checks) ---
$router->post('/api/v2/mobile/auth/forgot-password', 'Api\MobileAuthApiController@forgotPassword');
$router->post('/api/v2/mobile/auth/verify-otp', 'Api\MobileAuthApiController@verifyOtp');
$router->post('/api/v2/mobile/auth/resend-otp', 'Api\MobileAuthApiController@resendOtp');
$router->post('/api/v2/mobile/auth/reset-password', 'Api\MobileAuthApiController@resetPassword');
$router->post('/api/v2/mobile/auth/change-password', 'Api\MobileAuthApiController@changePassword')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/auth/refresh', 'Api\MobileAuthApiController@refresh');
$router->get('/api/v2/mobile/auth/check-user', 'Api\MobileAuthApiController@checkUser');
$router->get('/api/v2/mobile/auth/referrer', 'Api\MobileAuthApiController@getReferrer');

// --- Leads (flat pattern) â€” alias to CRMController ---
$router->get('/api/v2/mobile/leads', 'Api\CRMController@leads')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/create', 'Api\CRMController@createLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/leads/{id}', 'Api\CRMController@leadDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->put('/api/v2/mobile/leads/{id}', 'Api\CRMController@updateLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/leads/search', 'Api\CRMController@search')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/leads/follow-ups', 'Api\CRMController@followUpReminders')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/status', 'Api\MobileAdminApiController@changeLeadStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/follow-up', 'Api\MobileAdminApiController@scheduleLeadFollowup')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/activities', 'Api\MobileAdminApiController@addLeadActivity')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/convert', 'Api\MobileAdminApiController@convertLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/lost', 'Api\MobileAdminApiController@markLeadLost')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/leads/statistics', 'Api\MobileAdminApiController@getLeadStatistics')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads/{id}/call-logs', 'Api\MobileAdminApiController@logLeadCall')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Bookings (full CRUD) ---
$router->post('/api/v2/mobile/bookings', 'Api\MobileBookingApiController@createBookingRequest')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->put('/api/v2/mobile/bookings/{id}', 'Api\MobileBookingApiController@updateBooking')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/bookings/{id}', 'Api\MobileBookingApiController@cancelBooking')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Deals ---
$router->get('/api/v2/mobile/deals', 'Api\CRMController@deals')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Properties (favorite, similar, colony properties) ---
$router->post('/api/v2/mobile/properties/{id}/favorite', 'Api\MobilePropertyApiController@toggleFavorite')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/{id}/similar', 'Api\MobilePropertyApiController@getSimilarProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/colonies/{colonyId}/properties', 'Api\MobilePropertyApiController@getColonyProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Notifications (individual read/delete) ---
$router->post('/api/v2/mobile/user/notifications/{id}/read', 'Api\MobileUserApiController@markNotificationRead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/v2/mobile/user/notifications/{id}', 'Api\MobileUserApiController@deleteNotification')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Referral (customer dashboard, share tracking) ---
$router->get('/api/v2/mobile/user/referral/dashboard', 'Api\MobileUserApiController@getReferralDashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/referral/share', 'Api\MobileUserApiController@trackReferralShare')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Support Tickets ---
$router->get('/api/v2/mobile/user/support/tickets', 'Api\MobileUserApiController@getSupportTickets')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/support/tickets', 'Api\MobileUserApiController@createSupportTicket')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/support/tickets/{id}', 'Api\MobileUserApiController@getSupportTicketDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Settings ---
$router->post('/api/v2/mobile/user/notification-preferences', 'Api\MobileUserApiController@updateNotificationPreferences')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/preferences', 'Api\MobileUserApiController@updateUserPreferences')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/user/account/delete', 'Api\MobileUserApiController@deleteAccount')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Admin Analytics ---
$router->get('/api/v2/mobile/admin/dashboard-stats', 'Api\AdminMobileController@dashboardStats')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/sales-trend', 'Api\AdminMobileController@salesTrend')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/top-associates', 'Api\AdminMobileController@topAssociates')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/colony-performance', 'Api\AdminMobileController@colonyPerformance')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/emi-collection', 'Api\AdminMobileController@emiCollection')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/lead-conversion', 'Api\AdminMobileController@leadConversion')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/admin/daily-sales', 'Api\AdminMobileController@dailySales')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Authentication (firebase login) ---
$router->post('/api/v2/mobile/auth/firebase-login', 'Api\MobileAuthApiController@firebaseLogin');

// --- Misc (employee tasks with wrong path) ---
$router->get('/api/v2/mobile/employee/api/tasks', 'Api\MobileAdminApiController@employeeTasks')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- MLM aliases ---
$router->get('/api/v2/mobile/mlm/team-performance', 'Api\CRMController@dashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/mlm/process-sale', 'Api\MobileMLMApiController@processMlmSale')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/mlm/upgrade-rank', 'Api\MobileMLMApiController@upgradeMlmRank')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/form16', 'Api\MobileMLMApiController@getForm16')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/tax-summary', 'Api\MobileMLMApiController@getTaxSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Notification/lead assignment webhook ---
$router->post('/api/v2/mobile/notification', 'Api\MobileUserApiController@createNotification')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Company Loan API ---
$router->get('/api/v2/mobile/loans', 'Api\MobileUserApiController@getLoans')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/loans/{id}', 'Api\MobileUserApiController@getLoanDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/loans/{id}/installments', 'Api\MobileUserApiController@getLoanInstallments')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/loans/apply', 'Api\MobileUserApiController@applyLoan')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/loans/offers', 'Api\MobileUserApiController@getLoanOffers');
$router->post('/api/v2/mobile/loans/calculate-eligibility', 'Api\MobileUserApiController@calculateLoanEligibility');
$router->get('/api/v2/mobile/loans/early-settlement/{id}', 'Api\MobileUserApiController@getEarlySettlement')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Legal Documents API ---
$router->get('/api/v2/mobile/legal/documents', 'Api\LegalApiController@getDocuments')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/legal/documents/{id}', 'Api\LegalApiController@getDocumentDetail')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/legal/documents/{id}/upload', 'Api\LegalApiController@uploadDocument')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/legal/categories', 'Api\LegalApiController@getCategories');
$router->get('/api/v2/mobile/legal/templates', 'Api\LegalApiController@getTemplates');
$router->get('/api/v2/mobile/legal/documents/{id}/preview', 'Api\LegalApiController@previewDocument')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- In-App Messaging API ---
$router->get('/api/v2/mobile/messages/conversations', 'Api\MobileUserApiController@getConversations')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/messages/{otherUserId}', 'Api\MobileUserApiController@getMessages')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/messages/send', 'Api\MobileUserApiController@sendMessage')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/messages/read/{otherUserId}', 'Api\MobileUserApiController@markMessagesRead')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/messages/unread/count', 'Api\MobileUserApiController@getUnreadCount')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// --- Stamp Duty Calculator API (mobile) ---
$router->post('/api/v2/mobile/stamp-duty/calculate', 'Api\StampDutyController@calculate');
$router->get('/api/v2/mobile/stamp-duty/rates', 'Api\StampDutyController@getRates');
$router->get('/api/v2/mobile/stamp-duty/states', 'Api\StampDutyController@getStates');
$router->get('/api/v2/mobile/stamp-duty/circle-rate', 'Api\StampDutyController@getCircleRate');
$router->get('/api/v2/mobile/stamp-duty/circle-rates', 'Api\StampDutyController@searchCircleRates');

// --- AI Property Search ---
$router->post('/api/properties/ai-search', 'Api\PropertyMatchmakerController@aiSearch');

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
$router->get('/api/v2/mobile/rera/verify/{reraNumber}', 'Api\MobileUserApiController@reraVerify');
$router->get('/api/v2/mobile/rera/search', 'Api\MobileUserApiController@reraSearch');
$router->get('/api/v2/mobile/rera/projects', 'Api\MobileUserApiController@reraProjects');

// --- Directory API (mobile) ---
$router->get('/api/v2/mobile/directory/categories', 'Api\MobileUserApiController@directoryCategories');
$router->get('/api/v2/mobile/directory/featured', 'Api\MobileUserApiController@directoryFeatured');
$router->get('/api/v2/mobile/directory/jobs', 'Api\MobileUserApiController@directoryJobs');

// --- Property Valuation API (mobile) ---
$router->post('/api/v2/mobile/property-valuation/calculate', 'Api\MobilePropertyApiController@propertyValuation');
$router->get('/api/v2/mobile/property-valuation/cities', 'Api\MobilePropertyApiController@valuationCities');

// --- Property Marketplace APIs (my listings, packages, messaging, boost) ---
$router->get('/api/v2/mobile/my-listings', 'Api\MobilePropertyApiController@getMyListings')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/listing-packages', 'Api\MobilePropertyApiController@getListingPackages');
$router->post('/api/v2/mobile/properties/inquiry', 'Api\MobilePropertyApiController@submitPropertyInquiry');
$router->post('/api/v2/mobile/properties/message', 'Api\MobilePropertyApiController@sendPropertyMessage')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/{id}/messages', 'Api\MobilePropertyApiController@getPropertyMessages')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/properties/boost', 'Api\MobilePropertyApiController@boostProperty')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/properties/messages/{id}/read', 'Api\MobileUserApiController@markMessageRead')->middleware('App\Http\Middleware\ApiAuthMiddleware');

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

// --- Document E-Sign API (New - Property Transaction Documents) ---
$router->post('/api/v2/mobile/document-esign/store', 'Api\DocumentEsignApiController@store')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/document-esign/sign/{id}', 'Api\DocumentEsignApiController@sign')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/document-esign/{id}', 'Api\DocumentEsignApiController@getDocument')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/document-esign', 'Api\DocumentEsignApiController@getDocuments')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// ============================================================
// INFRASTRUCTURE & DEBUGGING API (Admin Tools)
// ============================================================
require_once __DIR__ . '/container.php';
require_once __DIR__ . '/performance-cache.php';
require_once __DIR__ . '/request-middleware.php';
