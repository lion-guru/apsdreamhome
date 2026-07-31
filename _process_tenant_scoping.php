<?php

$files = [
    'C:/xampp/htdocs/apsdreamhome/app/Services/Marketing/MarketingAutomationService.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Monitoring/MonitoringService.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Pdf/PdfService.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Property/PropertyComparisonService.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/SEO/SEOManagementService.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Security/ComplianceService.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Security/SecurityConfigurationService.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Storage/S3CorsHelper.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Storage/S3Storage.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Storage/StorageManager.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/UI/ModernThemeService.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Utility/AlertManagerService.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Voice/AIVoicePipeline.php',
    'C:/xampp/htdocs/apsdreamhome/app/Services/Voice/TwilioVoiceService.php',
];

$crossTenantTables = [
    'mlm_settings','settings','company_settings','states','cities','countries',
    'pincode','property_types','property_categories','lead_sources','lead_statuses',
    'document_types','document_categories','tenant_subscriptions','tenant_usage',
    'tenant_users','roles','permissions','user_roles','admin_menu_items',
    'admin_role_menu_permissions','admin_user_menu_permissions','ai_knowledge_base',
];

$modified = [];
$skipped = [];
$errors = [];

foreach ($files as $file) {
    if (!file_exists($file)) {
        $errors[] = "NOT FOUND: $file";
        continue;
    }
    $content = file_get_contents($file);
    $original = $content;
    $basename = basename($file);

    // Check if already has ServiceTenantTrait
    if (strpos($content, 'ServiceTenantTrait') !== false || strpos($content, 'TenantContext::') !== false) {
        $skipped[] = "$basename: Already has ServiceTenantTrait or TenantContext";
        continue;
    }

    // Check if file uses Database wrapper or raw PDO
    $usesDatabaseWrapper = strpos($content, '$this->database') !== false || strpos($content, '$this->db') !== false;

    // Determine the use statement position (after last use statement before class declaration)
    $usePattern = '/^use [^\n;]++;\s*$/m';
    preg_match_all($usePattern, $content, $matches, PREG_OFFSET_CAPTURE);
    $lastUseEnd = 0;
    if (!empty($matches[0])) {
        $lastUse = end($matches[0]);
        $lastUseEnd = $lastUse[1] + strlen($lastUse[0]);
    }

    // Find class declaration
    $classPattern = '/^\s*class\s+\w+/m';
    preg_match($classPattern, $content, $classMatch, PREG_OFFSET_CAPTURE);

    // Add use statement after last existing use
    $useStatement = "\nuse \\App\\Traits\\ServiceTenantTrait;";
    if ($lastUseEnd > 0) {
        $content = substr_replace($content, $useStatement, $lastUseEnd, 0);
        $offsetShift = strlen($useStatement);
    } else {
        // No existing use statements, add after namespace
        $nsPattern = '/^namespace\s+[^\n;]++;\s*$/m';
        preg_match($nsPattern, $content, $nsMatch, PREG_OFFSET_CAPTURE);
        if ($nsMatch) {
            $insertPos = $nsMatch[1] + strlen($nsMatch[0]);
            $content = substr_replace($content, "\n" . $useStatement, $insertPos, 0);
            $offsetShift = strlen($useStatement) + 1;
        } else {
            $errors[] = "$basename: Could not find namespace or use statements";
            continue;
        }
    }

    // Add trait use inside class body (after opening brace)
    $classPos = strrpos($content, 'class ') + 6;
    $bracePos = strpos($content, '{', $classPos);
    $afterBrace = strpos($content, "\n", $bracePos) + 1;
    $traitUse = "    use \\App\\Traits\\ServiceTenantTrait;\n";
    $content = substr_replace($content, $traitUse, $afterBrace, 0);

    // Now apply tenant scoping to SQL operations
    // This is complex - we need to handle different SQL patterns

    // Pattern 1: INSERT INTO tenant_tables - add tenant_id column
    // We need to identify tenant-scoped tables in INSERT statements
    // For simplicity, we'll scan for INSERT/UPDATE/DELETE/SELECT on known business tables
    // and apply tenantSql()/tenantInsertData() patterns

    // The approach: wrap all SQL strings in tenant-aware patterns
    // For Database wrapper (query/select/selectOne/execute with positional params):
    //   - Append tenantSql() to SQL
    //   - Add tenantId to params array if tenant > 1
    // For raw PDO (prepare/execute):
    //   - Use tenantNamedSql() or tenantBind()

    // This is getting very complex for a generic script. Let me use file_put_contents
    // and then manually verify each file. For now, let me just mark files as modified
    // and use a simpler approach: wrap the file content after all other edits.

    // Actually, given the complexity, let me just do this file and check syntax,
    // then continue with individual edits for precision.
    file_put_contents($file, $content);
    $modified[] = $basename;
}

echo "=== MODIFIED ===\n";
foreach ($modified as $f) echo "  MODIFIED: $f\n";
echo "\n=== SKIPPED ===\n";
foreach ($skipped as $f) echo "  SKIPPED: $f\n";
echo "\n=== ERRORS ===\n";
foreach ($errors as $e) echo "  ERROR: $e\n";
echo "\nDone.\n";
