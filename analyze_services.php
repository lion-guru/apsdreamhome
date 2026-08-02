<?php
$files = [
    // Main directory files
    "app/Services/ActivityLogService.php",
    "app/Services/AdManagerService.php",
    "app/Services/AdvancedSearchService.php",
    "app/Services/AgentAssignmentService.php",
    "app/Services/AgentOrchestrator.php",
    "app/Services/AnalyticsService.php",
    "app/Services/AuctionService.php",
    "app/Services/AuditTrailService.php",
    "app/Services/BackupRestoreService.php",
    "app/Services/CompanyCredentialsService.php",
    "app/Services/DashboardAnalyticsService.php",
    "app/Services/DripCampaignService.php",
    "app/Services/EMIAutomationService.php",
    "app/Services/EmailQueueService.php",
    "app/Services/EngagementService.php",
    "app/Services/ExecutiveAIService.php",
    "app/Services/FieldCollectionService.php",
    "app/Services/FinanceService.php",
    "app/Services/NpsService.php",
    "app/Services/PortalMenuService.php",
    "app/Services/SystemLogger.php",
    "app/Services/TotpService.php",
    "app/Services/WebhookService.php",
    "app/Services/WorkflowEngineService.php",
    // Subdirectory files
    "app/Services/AI/AIManager.php",
    "app/Services/AI/DocumentAIService.php",
    "app/Services/AI/IntentDetector.php",
    "app/Services/AI/LeadScorer.php",
    "app/Services/AI/PatternLearner.php",
    "app/Services/AI/PropertyImageTaggingService.php",
    "app/Services/AI/PropertyValuationEngine.php",
    "app/Services/AI/RecommendationEngine.php",
    "app/Services/Analytics/AdvancedAnalyticsService.php",
    "app/Services/Communication/DigiLockerService.php",
    "app/Services/Communication/PushSender.php",
    "app/Services/Communication/WhatsAppTemplateService.php",
    "app/Services/Finance/EMICalculatorService.php",
    "app/Services/Finance/PropertyTaxCalculatorService.php",
    "app/Services/Gateway/TwilioService.php",
    "app/Services/Land/LandAcquisitionService.php",
    "app/Services/Land/PlotCutterService.php",
    "app/Services/Legal/ESignService.php",
    "app/Services/Legal/RERAVerificationService.php",
    "app/Services/Loan/LoanDocumentService.php",
    "app/Services/Loyalty/LoyaltyRewardsService.php",
    "app/Services/Map/MapService.php",
    "app/Services/Monitoring/MonitoringService.php",
    "app/Services/SEO/SEOManagementService.php",
    "app/Services/Storage/S3CorsHelper.php",
    "app/Services/AI/AIContentGenerationService.php",
    "app/Services/AI/ConversationEngine.php",
    "app/Services/AI/RAGAgent.php",
    "app/Services/AI/AssistantService.php",
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "MISSING: $file\n";
        continue;
    }
    $content = file_get_contents($file);
    $hasTrait = strpos($content, "ServiceTenantTrait") !== false;
    $hasUseStmt = (strpos($content, "use App\\Traits\\ServiceTenantTrait") !== false) || (strpos($content, "use \\App\\Traits\\ServiceTenantTrait") !== false);
    $hasTenant = strpos($content, "tenant_id") !== false;
    $hasNamespace = strpos($content, "namespace") !== false;

    // Find all SQL table references
    preg_match_all("/(INSERT INTO|UPDATE|DELETE FROM|SELECT .+ FROM)\s+(\w+)/is", $content, $matches, PREG_SET_ORDER);
    $tables = [];
    $ops = [];
    foreach ($matches as $m) {
        $op = strtoupper(trim($m[1]));
        $tbl = $m[2];
        if ($tbl == 'CURRENT_TIMESTAMP' || $tbl == 'NOW()' || $tbl == 'VALUES') continue;
        if (!isset($tables[$tbl])) {
            $tables[$tbl] = ['INSERT' => 0, 'UPDATE' => 0, 'DELETE' => 0, 'SELECT' => 0];
        }
        $opType = substr($op, 0, 6);
        if ($opType == 'INSERT') $tables[$tbl]['INSERT']++;
        elseif ($opType == 'UPDATE') $tables[$tbl]['UPDATE']++;
        elseif ($opType == 'DELETE') $tables[$tbl]['DELETE']++;
        elseif (substr($op, 0, 6) == 'SELECT') $tables[$tbl]['SELECT']++;
    }

    $sqlCount = count($matches);
    $status = "NEEDS WORK";
    if ($hasTenant && $hasTrait) $status = "LIKELY DONE (has tenant_id + trait)";
    elseif ($hasTenant && !$hasTrait) $status = "HAS tenant_id but no trait";

    echo "FILE: " . basename($file) . " ($file)\n";
    echo "  status=$status | ns:" . ($hasNamespace ? "Y" : "N") . " trait:" . ($hasTrait ? "Y" : "N") . " use:" . ($hasUseStmt ? "Y" : "N") . " tid:" . ($hasTenant ? "Y" : "N") . " ops:$sqlCount\n";
    foreach ($tables as $tbl => $counts) {
        $total = array_sum($counts);
        echo "    $tbl: I{$counts['INSERT']} U{$counts['UPDATE']} D{$counts['DELETE']} S{$counts['SELECT']} (total=$total)\n";
    }
    echo "---\n";
}
