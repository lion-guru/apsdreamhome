<?php

/** @var Router $router */

if (class_exists('\App\Middleware\RateLimiter')) {
    \App\Middleware\RateLimiter::checkApi();
}

// API Routes
$router->post('/api/v2/mobile/auth/login', 'Api\MobileApiController@login');
$router->post('/api/v2/mobile/auth/logout', 'Api\MobileApiController@logout');
$router->get('/api/v2/mobile/sync', 'Api\MobileApiController@syncProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/leads', 'Api\MobileApiController@batchSyncLeads')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties', 'Api\MobileApiController@syncProperties')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/summary', 'Api\MobileApiController@getMlmSummary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/payouts', 'Api\MobileApiController@getMlmPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/incentives', 'Api\MobileApiController@getMlmIncentives')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/upload-document', 'Api\MobileApiController@uploadDocument')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/documents', 'Api\MobileApiController@getDocuments')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/start', 'Api\MobileApiController@startSiteVisit')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/site-visit/update', 'Api\MobileApiController@updateSiteVisitLocation')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/site-visit/status', 'Api\MobileApiController@getSiteVisitStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/user/profile', 'Api\MobileApiController@getUserProfile')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/payouts/pending', 'Api\MobileApiController@getPendingPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/payouts/process', 'Api\MobileApiController@processPayouts')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/payouts/history', 'Api\MobileApiController@getPayoutHistory')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/genealogy', 'Api\MobileApiController@getGenealogy')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/mlm/business-breakdown', 'Api\MobileApiController@getBusinessBreakdown')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/mlm/request-payout', 'Api\MobileApiController@requestPayout')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/customer/bookings', 'Api\MobileApiController@getCustomerBookings')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/customer/emi-schedule', 'Api\MobileApiController@getEmiSchedule')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/customer/pay-emi', 'Api\MobileApiController@makeEmiPayment')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/v2/mobile/properties/submit', 'Api\MobileApiController@submitProperty')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/v2/mobile/properties/my-submissions', 'Api\MobileApiController@getSubmissions')->middleware('App\Http\Middleware\ApiAuthMiddleware');

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

// Dependency Injection Container Routes
require_once __DIR__ . '/container.php';

// Request Middleware Routes
require_once __DIR__ . '/request-middleware.php';

// Farmer Management Routes
require_once __DIR__ . '/farmers.php';

// Security Management Routes
require_once __DIR__ . '/security.php';

// Performance Cache Management Routes
require_once __DIR__ . '/performance-cache.php';

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

// MLM API Routes
$router->get('/api/mlm/analytics', 'App\\Http\\Controllers\\MLMController@getAnalytics');
$router->post('/api/mlm/commission', 'App\\Http\\Controllers\\MLMController@calculateCommission');
$router->get('/api/mlm/network-tree', 'App\\Http\\Controllers\\MLMController@getNetworkTree');
$router->get('/api/mlm/commission-history', 'App\\Http\\Controllers\\MLMController@getCommissionHistory');

// AI Valuation API Routes
$router->post('/api/ai-valuation/calculator', 'App\\Http\\Controllers\\AIValuationController@calculateValuation');
$router->get('/api/ai-valuation/market-trends', 'App\\Http\\Controllers\\AIValuationController@getMarketTrends');
$router->post('/api/ai-valuation/investment-analysis', 'App\\Http\\Controllers\\AIValuationController@getInvestmentAnalysis');

// Legacy Mobile API Routes (Backward Compatibility)
$router->get('/api/v1/mobile/properties', 'Api\MobileApiController@properties');
$router->get('/api/v1/mobile/properties/{id}', 'Api\MobileApiController@property');
$router->get('/api/v1/mobile/leads', 'Api\MobileApiController@leads');
$router->post('/api/v1/mobile/leads', 'Api\MobileApiController@submitLead');
$router->get('/api/v1/mobile/user/profile', 'Api\MobileApiController@userProfile');

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

// Gemini Chatbot API (separate from GeminiApiController)
$router->post('/api/gemini/chatbot/message', 'Api\GeminiChatbotController@message');
$router->get('/api/gemini/chatbot/history/{userId}', 'Api\GeminiChatbotController@history');
$router->post('/api/gemini/chatbot/suggestions', 'Api\GeminiChatbotController@suggestions');
$router->post('/api/gemini/chatbot/detect-intent', 'Api\GeminiChatbotController@detectIntent');
$router->get('/api/gemini/chatbot/health', 'Api\GeminiChatbotController@health');

// Social Sharing API
$router->post('/api/sharing/generate', 'Api\SharingController@generate');
$router->post('/api/sharing/track', 'Api\SharingController@trackClick');

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
$router->get('/api/docs/list', 'App\Http\Controllers\Api\DocsController@list');
$router->post('/api/twilio/voice/gather', 'Api\TwilioVoiceWebhookController@gather');
