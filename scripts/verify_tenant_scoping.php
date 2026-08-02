<?php

// Check which files have ServiceTenantTrait class
$basePath = __DIR__;

$filesToCheck = [
    'app/Services/Esign/ESignManager.php',
    'app/Services/Utility/FileService.php',
    'app/Services/Pdf/AgreementPDFService.php',
    'app/Services/SalaryService.php',
    'app/Services/Property/VirtualTourService.php',
    'app/Services/Property/PropertyImageService.php',
    'app/Services/Notifications/NotificationService.php',
    'app/Services/Collaboration/CollaborationService.php',
    'app/Services/Workflow/WorkflowService.php',
    'app/Services/CRM/CRMService.php',
    'app/Services/HR/HRService.php',
    'app/Services/Finance/FinanceService.php',
    'app/Services/Accounting/AccountingService.php',
    'app/Services/Marketing/MarketingService.php',
    'app/Services/Sales/SalesService.php',
    'app/Services/Inventory/InventoryService.php',
    'app/Services/Procurement/ProcurementService.php',
    'app/Services/Maintenance/MaintenanceService.php',
    'app/Services/Helpdesk/HelpdeskService.php',
    'app/Services/Assets/AssetService.php',
    'app/Services/Library/LibraryService.php',
    'app/Services/Events/EventsService.php',
    'app/Services/Communication/CommunicationService.php',
    'app/Services/Document/DocumentService.php',
    'app/Services/Digital/DigitalService.php',
    'app/Services/Reporting/ReportingService.php',
    'app/Services/Audit/AuditService.php',
    'app/Services/Security/SecurityService.php',
    'app/Services/Compliance/ComplianceService.php',
    'app/Services/Identity/IdentityService.php',
    'app/Services/Integration/IntegrationService.php',
    'app/Services/Automation/AutomationService.php',
    'app/Services/Subscription/SubscriptionService.php',
    'app/Services/Payment/PaymentService.php',
    'app/Services/Messaging/MessagingService.php',
    'app/Services/Engagement/EngagementService.php',
    'app/Services/Support/SupportService.php',
    'app/Services/Analytics/AnalyticsService.php',
    'app/Services/Customer/CustomerService.php',
    'app/Services/Onboarding/OnboardingService.php',
    'app/Services/Feedback/FeedbackService.php',
    'app/Services/Revenue/RevenueService.php',
];

foreach ($filesToCheck as $file) {
    $fullPath = $basePath . '/' . $file;
    
    if (!file_exists($fullPath)) {
        echo "File $file not found, skipping\n";
        continue;
    }
    
    $content = file_get_contents($fullPath);
    
    // Check if it already extends ServiceTenantTrait
    if (strpos($content, 'extends ServiceTenantTrait') !== false) {
        echo "✓ $file extends ServiceTenantTrait\n";
    } else {
        echo "✗ $file does NOT extend ServiceTenantTrait\n";
    }
}
