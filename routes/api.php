<?php
// API Routes
$router->get('/api/health', 'Api\SystemController@health');
$router->get('/api/properties', 'Api\PropertyController@index');
$router->post('/api/contact', 'Api\ApiEnquiryController@store');
$router->post('/api/newsletter', 'Api\NewsletterController@subscribe');
$router->post('/api/property-inquiry', 'Api\ApiEnquiryController@propertyInquiry');

// AI Assistant API Routes
$router->post('/api/ai/chat', 'AIAssistantController@chat');
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
$router->post('/api/ai-valuation/calculate', 'AIValuationController@calculateValuation');
$router->get('/api/ai-valuation/market-trends', 'AIValuationController@getMarketTrends');
$router->post('/api/ai-valuation/investment-analysis', 'AIValuationController@getInvestmentAnalysis');
?>