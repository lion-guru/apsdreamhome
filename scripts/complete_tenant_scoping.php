<?php
// Script to add ServiceTenantTrait to multiple service files

$traitClass = <<<'PHP'
class ServiceTenantTrait
{
    protected static function tenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    protected static function tenantWhere(string &$sql, array &$params): void
    {
        $tid = static::tenantId();
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tid;
        }
    }

    protected static function tenantInsertData(array &$columns, array &$values): void
    {
        $tid = static::tenantId();
        if ($tid > 1) {
            $columns[] = 'tenant_id';
            $values[] = $tid;
        }
    }
}
PHP;

$filesToUpdate = [
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

foreach ($filesToUpdate as $file) {
    if (!file_exists($file)) {
        echo "File $file not found, skipping\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Check if it already has ServiceTenantTrait
    if (strpos($content, 'class ServiceTenantTrait') !== false) {
        echo "File $file already has ServiceTenantTrait, skipping\n";
        continue;
    }
    
    // Check if it already extends ServiceTenantTrait
    if (strpos($content, 'extends ServiceTenantTrait') !== false) {
        echo "File $file already extends ServiceTenantTrait, skipping\n";
        continue;
    }
    
    // Determine where to insert the trait class (after namespace)
    $lines = explode('\n', $content);
    $insertPos = 0;
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], 'namespace App\\Services') !== false) {
            $insertPos = $i + 1;
            break;
        }
    }
    
    // Insert trait class after namespace
    array_splice($lines, $insertPos, 0, [$traitClass]);
    $content = implode('\n', $lines);
    
    // Change 'class SalaryService' to 'class SalaryService extends ServiceTenantTrait'
    $content = preg_replace('/^class ([A-Za-z0-9_]+) \{$/', 'class $1 extends ServiceTenantTrait {', $content);
    
    // Write back to file
    file_put_contents($file, $content);
    
    echo "Updated $file\n";
}

echo "All files updated successfully!\n";
