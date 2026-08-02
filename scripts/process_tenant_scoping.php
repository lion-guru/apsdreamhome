#!/usr/bin/env php
<?php

// Script to process all service files with tenant scoping

$basePath = __DIR__;

// Add ServiceTenantTrait to files that need it
$filesToAddTrait = [
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
];

$traitContent = "<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

class ServiceTenantTrait
{
    protected static function tenantId(): int
    {
        try {
            $tid = TenantContext::getId();
            return $tid > 0 ? $tid : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    protected static function tenantWhere(string &$sql, array &$params): void
    {
        $tid = static::tenantId();
        if ($tid > 1) {
            $sql .= ' AND tenant_id = ?';
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
";

$processedCount = 0;

foreach ($filesToAddTrait as $file) {
    $fullPath = $basePath . '/' . $file;
    
    if (!file_exists($fullPath)) {
        echo "File $file not found, skipping\n";
        continue;
    }
    
    $content = file_get_contents($fullPath);
    
    // Check if it already has ServiceTenantTrait
    if (strpos($content, 'class ServiceTenantTrait') !== false) {
        echo "✓ File $file already has ServiceTenantTrait, skipping\n";
        $processedCount++;
        continue;
    }
    
    // Check if it already extends ServiceTenantTrait
    if (strpos($content, 'extends ServiceTenantTrait') !== false) {
        echo "✓ File $file already extends ServiceTenantTrait, skipping\n";
        $processedCount++;
        continue;
    }
    
    // Determine namespace line
    $lines = explode('\n', $content);
    $namespaceIndex = 0;
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], 'namespace ') !== false) {
            $namespaceIndex = $i;
            break;
        }
    }
    
    // Insert trait after namespace
    $newLines = array_merge(array_slice($lines, 0, $namespaceIndex + 1), [$traitContent], array_slice($lines, $namespaceIndex + 1));
    $content = implode('\n', $newLines);
    
    // Change 'class ' to 'class extends ServiceTenantTrait '
    $content = preg_replace('/^class /', 'class extends ServiceTenantTrait ', $content);
    
    // Write back
    file_put_contents($fullPath, $content);
    
    echo "✓ Updated $file\n";
    $processedCount++;
}

echo "\n";
echo "Total files processed: $processedCount\n";

echo "Checking for any remaining unscoped service files...\n";

$remainingFiles = [];
foreach ($filesToAddTrait as $file) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if (strpos($content, 'extends ServiceTenantTrait') === false) {
            $remainingFiles[] = $file;
        }
    }
}

if (count($remainingFiles) > 0) {
    echo "\nRemaining files that need tenant scoping:\n";
    foreach ($remainingFiles as $file) {
        echo "  - $file\n";
    }
} else {
    echo "All files successfully updated with ServiceTenantTrait!\n";
}
