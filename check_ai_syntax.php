<?php
// Comprehensive AI system scan
// 1. Check all AI-related PHP files for syntax errors
$aiFiles = [
    'app/Services/AI/AIService.php',
    'app/Services/AI/AIGateway.php',
    'app/Services/AI/FreeAIEngines.php',
    'app/Services/AI/VoiceService.php',
    'app/Services/AI/VoiceProviderManager.php',
    'app/Services/AI/RAGService.php',
    'app/Services/AI/LeadScorer.php',
    'app/Services/AI/PropertyValuationEngine.php',
    'app/Services/AI/RecommendationEngine.php',
    'app/Services/AI/IntentDetector.php',
    'app/Services/AI/SelfLearningAI.php',
    'app/Services/AI/ExecutiveAIService.php',
    'app/Services/AI/DocumentAIService.php',
    'app/Services/AI/DocumentGeneratorService.php',
    'app/Services/AI/SmartSearchService.php',
    'app/Services/AI/MarketingContentGenerator.php',
    'app/Services/AI/OllamaClient.php',
    'app/Services/AI/PatternLearner.php',
    'app/Services/AI/ConversationEngine.php',
    'app/Services/AI/JobManager.php',
    'app/Services/AI/AIToolsManager.php',
    'app/Services/AI/ActionHandlers.php',
    'app/Services/AI/AIHealthMonitor.php',
    'app/Services/AI/CodeReviewAgent.php',
    'app/Services/AI/WorkflowAutomationAgent.php',
    'app/Services/AI/WorkflowEngine.php',
    'app/Services/AI/PricePredictor.php',
    'app/Services/AI/PropertyImageTaggingService.php',
    'app/Services/AI/AIImageTagger.php',
    'app/Services/AI/AIAdvancedAgent.php',
    'app/Services/AI/AIManager.php',
    'app/Services/AI/AIGeminiChatbotService.php',
    'app/Services/AI/ConversationalAIService.php',
    'app/Services/AI/RAGAgent.php',
    'app/Services/AI/RAGEmbeddingService.php',
    'app/Services/AI/RAGKnowledgeManager.php',
    'app/Services/AI/DocumentGeneratorAgent.php',
    'app/Services/AIChatbotService.php',
    'app/Http/Controllers/Api/AIChatbotController.php',
    'app/Http/Controllers/Api/AIAgentApiController.php',
    'app/Http/Controllers/Api/AIAnalyticsController.php',
    'app/Http/Controllers/Api/DocumentAIController.php',
    'app/Http/Controllers/Api/EmailClassificationController.php',
    'app/Http/Controllers/Api/ExecutiveAIController.php',
    'app/Http/Controllers/Api/GeminiApiController.php',
    'app/Http/Controllers/Api/GeminiChatbotController.php',
    'app/Http/Controllers/Api/SmartRoutingController.php',
    'app/Http/Controllers/Api/SmartSearchController.php',
    'app/Http/Controllers/Api/VoiceAssistantController.php',
    'app/Http/Controllers/Api/VoiceAgentController.php',
    'app/Http/Controllers/Api/VoiceUploadApiController.php',
    'app/Http/Controllers/Api/TwilioVoiceWebhookController.php',
    'app/Http/Controllers/Admin/AIManagementController.php',
    'app/Http/Controllers/Admin/AISystemController.php',
    'app/Http/Controllers/Admin/AISettingsController.php',
    'app/Http/Controllers/Admin/AdminAIController.php',
    'app/Http/Controllers/Admin/AiController.php',
    'app/Http/Controllers/Admin/AICallingController.php',
    'app/Http/Controllers/Admin/AIAggregatorController.php',
    'app/Http/Controllers/Admin/ExecutiveAIController.php',
    'app/Http/Controllers/Admin/RAGAdminController.php',
    'app/Http/Controllers/Admin/CRMVoiceController.php',
    'app/Http/Controllers/Admin/VoiceCallingController.php',
    'app/Http/Controllers/Admin/VoiceAgentAdminController.php',
    'app/Http/Controllers/Admin/VoiceAnalyticsController.php',
    'app/Http/Controllers/Admin/VoiceCallSchedulerController.php',
    'app/Http/Controllers/Admin/VoiceUploadController.php',
    'app/Http/Controllers/Admin/AgenticAIController.php',
    'app/Http/Controllers/Admin/AIDashboardController.php',
    'app/Http/Controllers/Front/AIBotController.php',
    'app/Http/Controllers/Front/AIController.php',
    'app/Http/Controllers/Front/AiAssistantController.php',
    'app/Http/Controllers/Front/CustomerMaintenanceController.php',
    'app/Http/Controllers/AI/AIContentController.php',
    'app/Http/Controllers/AI/AIWebController.php',
    'app/Http/Controllers/AI/ChatbotAPIController.php',
    'app/Http/Controllers/Associate/AIDashboardController.php',
    'app/Http/Controllers/SmartAIController.php',
    'app/Http/Controllers/AIDashboardController.php',
    'app/Http/Controllers/AIValuationController.php',
    'app/Http/Controllers/AIAssistantController.php',
    'app/Http/Controllers/AIController.php',
];

echo "=== AI PHP File Syntax Check ===\n";
$errors = 0;
foreach($aiFiles as $f) {
    $path = 'C:/xampp/htdocs/apsdreamhome/' . $f;
    if(!file_exists($path)) {
        echo "  ❌ MISSING: $f\n";
        $errors++;
        continue;
    }
    $output = shell_exec("php -l " . escapeshellarg($path) . " 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "  ✅ $f\n";
    } else {
        echo "  ❌ SYNTAX ERROR: $f\n";
        echo "     $output\n";
        $errors++;
    }
}
echo "\n=== Summary ===\n";
echo "Total files checked: " . count($aiFiles) . "\n";
echo "Files with errors: $errors\n";