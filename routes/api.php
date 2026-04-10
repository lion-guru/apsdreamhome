<?php

/** @var Router $router */
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


// AI Assistant API Routes
$router->post('/api/ai/chat', 'AIAssistantController@chat');
$router->post('/api/v2/mobile/ai/parse-lead', 'AIAssistantController@parseLead')->middleware('App\Http\Middleware\ApiAuthMiddleware');

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

// Core Functions Routes
require_once __DIR__ . '/core-functions.php';

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
$router->get('/api/ai/recommendations', 'AIAssistantController@recommendations');
$router->get('/api/ai/analyze/{id}', 'AIAssistantController@analyze');

// Monitoring API Routes
$router->get('/api/monitoring/health', 'MonitoringController@healthCheck');

// AI Dashboard API Routes
$router->post('/api/ai-dashboard/training', 'AIDashboardController@startTraining');
$router->post('/api/ai-dashboard/reset', 'AIDashboardController@resetMemory');
$router->post('/api/ai-dashboard/export', 'AIDashboardController@exportData');
$router->get('/api/ai-dashboard/training-log', 'AIDashboardController@getTrainingLog');

// Analytics API Routes
$router->get('/api/analytics/metrics', 'Api\AnalyticsController@getRealTimeMetrics');
$router->post('/api/analytics/export', 'Api\AnalyticsController@exportData');
$router->get('/api/analytics/properties', 'Api\AnalyticsController@getPropertyAnalytics');
$router->get('/api/analytics/users', 'Api\AnalyticsController@getUserAnalytics');

// WhatsApp Templates API Routes
$router->post('/api/whatsapp-templates/create', 'WhatsAppTemplateController@createTemplate');
$router->post('/api/whatsapp-templates/update/{id}', 'WhatsAppTemplateController@updateTemplate');
$router->delete('/api/whatsapp-templates/delete/{id}', 'WhatsAppTemplateController@deleteTemplate');
$router->post('/api/whatsapp-templates/send-test', 'WhatsAppTemplateController@sendTestMessage');
$router->get('/api/whatsapp-templates/stats', 'WhatsAppTemplateController@getUsageStats');
$router->get('/api/whatsapp-templates/preview/{id}', 'WhatsAppTemplateController@previewTemplate');

// MLM API Routes
$router->get('/api/mlm/analytics', 'MLMController@getAnalytics');
$router->post('/api/mlm/commission', 'MLMController@calculateCommission');
$router->get('/api/mlm/network-tree', 'MLMController@getNetworkTree');
$router->get('/api/mlm/commission-history', 'MLMController@getCommissionHistory');

// AI Valuation API Routes
$router->post('/api/ai-valuation/calculator', 'AIValuationController@calculateValuation');
$router->get('/api/ai-valuation/market-trends', 'AIValuationController@getMarketTrends');
$router->post('/api/ai-valuation/investment-analysis', 'AIValuationController@getInvestmentAnalysis');

// Legacy Mobile API Routes (Backward Compatibility)
$router->get('/api/v1/mobile/properties', 'Api\MobileApiController@properties');
$router->get('/api/v1/mobile/properties/{id}', 'Api\MobileApiController@property');
$router->get('/api/v1/mobile/leads', 'Api\MobileApiController@leads');
$router->post('/api/v1/mobile/leads', 'Api\MobileApiController@submitLead');
$router->get('/api/v1/mobile/user/profile', 'Api\MobileApiController@userProfile');
