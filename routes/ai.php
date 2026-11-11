<?php
/**
 * AI-related routes (extracted from web.php for maintainability)
 * S354 - Route file splitting
 * Loaded from public/index.php alongside web.php, land.php, gap_builds.php
 */
// AI System Dashboard + 5 Agents
$router->get('/admin/ai-system', 'App\\Http\\Controllers\\Admin\\AISystemController@index');
$router->post('/admin/ai-system/run', 'App\\Http\\Controllers\\Admin\\AISystemController@runAgent');
$router->get('/admin/ai-system/qualifier', 'App\\Http\\Controllers\\Admin\\AISystemController@qualifier');
$router->get('/admin/ai-system/market-report', 'App\\Http\\Controllers\\Admin\\AISystemController@marketReport');
$router->get('/admin/ai-system/settings', 'App\\Http\\Controllers\\Admin\\AISystemController@settings');
$router->post('/admin/ai-system/settings', 'App\\Http\\Controllers\\Admin\\AISystemController@settings');
$router->get('/admin/ai-system/health', 'App\\Http\\Controllers\\Admin\\AISystemController@engineHealth');

// Executive AI Assistant
$router->get('/admin/ai/executive-assistant', 'App\\Http\\Controllers\\Admin\\ExecutiveAIController@index');
$router->post('/admin/ai/executive-assistant/chat', 'App\\Http\\Controllers\\Admin\\ExecutiveAIController@chat');
$router->post('/admin/ai/executive-assistant/execute', 'App\\Http\\Controllers\\Admin\\ExecutiveAIController@executeAction');

// AI Content Generation
$router->post('/ai/content/description', 'App\\Http\\Controllers\\AI\\AIContentController@generateDescription');
$router->post('/ai/content/blog-draft', 'App\\Http\\Controllers\\AI\\AIContentController@generateBlogDraft');
$router->post('/ai/content/image-tags', 'App\\Http\\Controllers\\AI\\AIContentController@generateImageTags');

// AI Training
$router->get('/admin/ai-training', 'App\\Http\\Controllers\\Admin\\AdminAIController@training');

// AI Calling System
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
$router->get('/admin/ai-calling/history', 'App\\Http\\Controllers\\Admin\\AICallingController@history');
$router->get('/admin/ai-calling/campaign', 'App\\Http\\Controllers\\Admin\\AICallingController@campaign');
$router->get('/admin/ai-calling/analytics', 'App\\Http\\Controllers\\Admin\\AICallingController@analytics');
$router->get('/admin/ai-calling/auto-dialer', 'App\\Http\\Controllers\\Admin\\AICallingController@autoDialer');
$router->get('/admin/ai-calling/call-analytics', 'App\\Http\\Controllers\\Admin\\AICallingController@callAnalytics');
$router->post('/admin/ai-calling/auto-dialer/process', 'App\\Http\\Controllers\\Admin\\AICallingController@autoDialerProcess');
$router->post('/admin/ai-calling/auto-dialer/ai-schedule', 'App\\Http\\Controllers\\Admin\\AICallingController@autoDialerAiSchedule');
$router->get('/admin/ai-calling/call-logs', 'App\\Http\\Controllers\\Admin\\AICallingController@callLogs');
$router->get('/admin/ai-calling/call-detail', 'App\\Http\\Controllers\\Admin\\AICallingController@callDetail');

// Voice Calling
$router->get('/admin/voice-calling', 'App\\Http\\Controllers\\Admin\\VoiceCallingController@index');
$router->post('/admin/voice-calling/test-provider', 'App\\Http\\Controllers\\Admin\\VoiceCallingController@testProvider');
$router->get('/admin/voice-calling/provider-config', 'App\\Http\\Controllers\\Admin\\VoiceCallingController@providerConfig');

// IVR Flows
$router->get('/admin/ivr-flows', 'App\\Http\\Controllers\\Admin\\IVRFlowController@index');
$router->get('/admin/ivr-flows/create', 'App\\Http\\Controllers\\Admin\\IVRFlowController@create');
$router->post('/admin/ivr-flows/store', 'App\\Http\\Controllers\\Admin\\IVRFlowController@store');
$router->get('/admin/ivr-flows/{id}/edit', 'App\\Http\\Controllers\\Admin\\IVRFlowController@edit');
$router->post('/admin/ivr-flows/{id}/update', 'App\\Http\\Controllers\\Admin\\IVRFlowController@update');
$router->post('/admin/ivr-flows/{id}/delete', 'App\\Http\\Controllers\\Admin\\IVRFlowController@delete');
$router->get('/admin/ivr-flows/{id}/steps', 'App\\Http\\Controllers\\Admin\\IVRFlowController@steps');

// Voice Analytics
$router->get('/admin/voice-analytics', 'App\\Http\\Controllers\\Admin\\VoiceAnalyticsController@index');

// Voice Agents
$router->get('/admin/voice-agents', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@dashboard');
$router->get('/admin/voice-agents/history', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@history');
$router->get('/admin/voice-agents/schedule', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@schedule');
$router->get('/admin/voice-agents/scripts', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@scripts');
$router->get('/admin/voice-agents/extracted-leads', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@extractedLeads');
$router->get('/admin/voice-agents/settings', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@settings');
$router->get('/admin/voice-agents/oln', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@olnDashboard');
$router->get('/admin/voice-agents/live', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@live');
$router->post('/admin/voice-agents/transfer-call', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@transferCall');
$router->post('/admin/voice-agents/hangup-call', 'App\\Http\\Controllers\\Admin\\VoiceAgentAdminController@hangupCall');

// RAG Knowledge Base
$router->get('/admin/rag', 'App\\Http\\Controllers\\Admin\\RAGAdminController@index');
$router->post('/admin/rag/set-model', 'App\\Http\\Controllers\\Admin\\RAGAdminController@setModel');
$router->post('/admin/rag/reembed', 'App\\Http\\Controllers\\Admin\\RAGAdminController@reembed');

// AI Training
$router->get('/admin/ai-training', 'App\\Http\\Controllers\\Admin\\AdminAIController@training');

// AI Dashboard
$router->get('/ai-dashboard', 'App\\Http\\Controllers\\AIDashboardController@index');