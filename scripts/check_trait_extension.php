<?php
$files = [
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
    'app/Services/Notifications/NotificationService.php',
    'app/Services/Engagement/EngagementService.php',
    'app/Services/Support/SupportService.php',
    'app/Services/Analytics/AnalyticsService.php',
    'app/Services/Customer/CustomerService.php',
    'app/Services/Onboarding/OnboardingService.php',
    'app/Services/Feedback/FeedbackService.php',
    'app/Services/Revenue/RevenueService.php',
    'app/Services/Success/SuccessService.php',
    'app/Services/Onboarding/OnboardingService.php',
    'app/Services/Customer/CustomerService.php',
    'app/Services/Feedback/FeedbackService.php',
    'app/Services/Success/SuccessService.php',
    'app/Services/Revenue/RevenueService.php',
];

$traitFile = 'app/Services/ServiceTenantTrait.php';
$trait = '';

if (file_exists($traitFile)) {
    $trait = file_get_contents($traitFile);
    echo "ServiceTenantTrait.php exists!\n";
} else {
    echo "ServiceTenantTrait.php NOT FOUND!\n";
}

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File $file not found\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    if (strpos($content, 'extends ServiceTenantTrait') !== false) {
        echo "✓ $file extends ServiceTenantTrait\n";
    } else {
        echo "✗ $file does NOT extend ServiceTenantTrait\n";
        // Show class line
        $lines = explode('\n', $content);
        foreach ($lines as $line) {
            if (preg_match('/^class /', $line)) {
                echo "   Class: $line\n";
                break;
            }
        }
    }
}
