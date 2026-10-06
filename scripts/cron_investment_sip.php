<?php
/**
 * Cron job for SIP auto-debit processing
 * Runs daily to process due SIP installments
 * Usage: php scripts/cron_investment_sip.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\InvestmentService;
use App\Core\Middleware\TenantContext;

echo "========================================\n";
echo "  SIP AUTO-DEBIT CRON - " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
    
    // Get all active tenants (for multi-tenant support)
    $tenants = $pdo->query("SELECT id FROM tenants WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN) ?: [1];
    
    $globalStats = [
        'total_installments' => 0,
        'paid' => 0,
        'failed' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    foreach ($tenants as $tenantId) {
        TenantContext::setById($tenantId);
        
        $investmentService = new InvestmentService();
        $result = $investmentService->processDueSipInstallments();
        
        echo "Tenant {$tenantId}: Processed {$result['processed']} installments\n";
        echo "  Paid: {$result['paid']}, Failed: {$result['failed']}\n";
        
        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $err) {
                echo "  ERROR: {$err}\n";
                $globalStats['errors'][] = "Tenant {$tenantId}: {$err}";
            }
        }
        
        $globalStats['total_installments'] += $result['processed'];
        $globalStats['paid'] += $result['paid'];
        $globalStats['failed'] += $result['failed'];
        $globalStats['skipped'] += $result['skipped'];
    }

    echo "\n========================================\n";
    echo "  SUMMARY\n";
    echo "========================================\n";
    echo "Total Installments Processed: {$globalStats['total_installments']}\n";
    echo "Successfully Paid: {$globalStats['paid']}\n";
    echo "Failed: {$globalStats['failed']}\n";
    echo "Errors: " . count($globalStats['errors']) . "\n";
    
    if (!empty($globalStats['errors'])) {
        echo "\nError Details:\n";
        foreach ($globalStats['errors'] as $err) {
            echo "  - {$err}\n";
        }
    }
    
    echo "\nCompleted at " . date('Y-m-d H:i:s') . "\n";
    
} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    error_log("[cron_investment_sip] " . $e->getMessage());
    exit(1);
}