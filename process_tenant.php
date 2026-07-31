<?php

require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';

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

foreach ($files as $file) {
    $basename = basename($file);
    if (!file_exists($file)) {
        echo "SKIP (not found): $basename\n";
        continue;
    }
    $content = file_get_contents($file);
    if (strpos($content, 'ServiceTenantTrait') !== false || strpos($content, 'TenantContext::') !== false) {
        echo "SKIP (already has trait): $basename\n";
        continue;
    }
    $modified = applyTenantScoping($content, $file);
    if ($modified) {
        file_put_contents($file, $modified);
        echo "MODIFIED: $basename\n";
    } else {
        echo "NO CHANGE: $basename\n";
    }
}

function applyTenantScoping($content, $file) {
    $basename = basename($file);

    // Add use import after existing use statements
    $useImport = "\nuse \\App\\Traits\\ServiceTenantTrait;";
    $content = preg_replace(
        '/(use [^;]+;)(\s*\n\s*\*?\/?\s*\n\s*\/\*\*)?/',
        '$1' . $useImport . '${3:-}',
        $content, 1
    );

    // Fallback: simpler approach - add use after last use statement
    if (strpos($content, 'use \\App\\Traits\\ServiceTenantTrait;') === false) {
        // Try adding after namespace line
        $content = preg_replace(
            '/(^namespace\s+[^\n;]+;\s*\n)/m',
            '$1' . 'use \App\Traits\ServiceTenantTrait;' . '\n',
            $content, 1
        );
    }

    // Add trait use inside class body
    $classMatch = [];
    if (preg_match('/^\s*class\s+\w+\s*\{/m', $content, $classMatch, PREG_OFFSET_CAPTURE)) {
        $bracePos = strrpos($classMatch[0][0], '{');
        $insertPt = $classMatch[1] + $bracePos + 1;
        // Find the next newline after opening brace
        $nlPos = strpos($content, "\n", $insertPt);
        $content = substr($content, 0, $nlPos + 1) . "    use \\App\\Traits\\ServiceTenantTrait;\n" . substr($content, $nlPos + 1);
    }

    // Now apply SQL scoping based on file-specific patterns
    $original = $content;
    $content = applySqlScoping($content, $basename);

    return ($content !== $original) ? $content : false;
}

function applySqlScoping($content, $basename) {
    // Pattern helpers
    $isDbWrapper = (strpos($content, '$this->database') !== false || strpos($content, '$this->db') !== false);
    $isPdo = strpos($content, '$pdo->') !== false || strpos($content, '$stmt->') !== false;

    switch ($basename) {
        case 'MarketingAutomationService.php':
            return scopeMarketing($content);
        case 'MonitoringService.php':
            return scopeMonitoring($content);
        case 'PdfService.php':
            return scopePdf($content);
        case 'PropertyComparisonService.php':
            return scopePropertyComparison($content);
        case 'SEOManagementService.php':
            return scopeSeo($content);
        case 'ComplianceService.php':
            return scopeCompliance($content);
        case 'SecurityConfigurationService.php':
            return scopeSecurityConfig($content);
        case 'S3CorsHelper.php':
        case 'S3Storage.php':
        case 'StorageManager.php':
            // Storage services: mostly config/S3 network calls, minimal DB
            return scopeStorage($content);
        case 'ModernThemeService.php':
            return scopeTheme($content);
        case 'AlertManagerService.php':
            return scopeAlert($content);
        case 'AIVoicePipeline.php':
            return scopeAIVoice($content);
        case 'TwilioVoiceService.php':
            return scopeTwilio($content);
        default:
            return $content;
    }
}

function scopeMarketing($content) {
    // marketing_leads INSERT - in captureLead()
    $content = str_replace(
        '$sql = "INSERT INTO marketing_leads (name, email, phone, source, campaign)\n                        VALUES (?, ?, ?, ?, ?)";',
        '$insertData = $this->tenantInsertData();' . "\n" .
        '                $sql = "INSERT INTO marketing_leads (name, email, phone, source, campaign" . (count($insertData) ? ", tenant_id" : "") . ")\n                        VALUES (?, ?, ?, ?, ?" . (count($insertData) ? ", ?" : "") . ")";' . "\n" .
        '                $params = array_merge([$name, $email, $phone, $source, $campaign], array_values($insertData));',
        $content
    );
    $content = str_replace(
        '$this->database->query($sql, [$name, $email, $phone, $source, $campaign]);',
        '$this->database->query($sql, $params);',
        $content
    );

    // marketing_leads UPDATE - in captureLead()
    $content = str_replace(
        '$sql = "UPDATE marketing_leads SET \n                        source = ?, \n                        campaign = ?, \n                        updated_at = NOW() \n                        WHERE id = ?";',
        '$sql = "UPDATE marketing_leads SET \n                        source = ?, \n                        campaign = ?, \n                        updated_at = NOW() \n                        WHERE id = ?" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        '$this->database->query($sql, [$source, $campaign, $existingLead[\'id\']]);',
        '$params = [$source, $campaign, $existingLead[\'id\']]; if ($this->tenantId() > 1) $params[] = $this->tenantId(); $this->database->query($sql, $params);',
        $content
    );

    // marketing_leads SELECT WHERE email
    $content = str_replace(
        '$sql = "SELECT * FROM marketing_leads WHERE email = ?";',
        '$sql = "SELECT * FROM marketing_leads WHERE email = ?" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        'return $this->database->selectOne($sql, [$email]);',
        '$params = [$email]; if ($this->tenantId() > 1) $params[] = $this->tenantId(); return $this->database->selectOne($sql, $params);',
        $content
    );

    // marketing_leads SELECT WHERE id
    $content = str_replace(
        '$sql = "SELECT * FROM marketing_leads WHERE id = ?";',
        '$sql = "SELECT * FROM marketing_leads WHERE id = ?" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        '$lead = $this->database->selectOne($sql, [$leadId]);',
        '$params = [$leadId]; if ($this->tenantId() > 1) $params[] = $this->tenantId(); $lead = $this->database->selectOne($sql, $params);',
        $content
    );

    // marketing_leads SELECT WHERE status ORDER
    $content = str_replace(
        '$sql = "SELECT * FROM marketing_leads WHERE 1=1";',
        '$sql = "SELECT * FROM marketing_leads WHERE 1=1" . $this->tenantSql();',
        $content
    );
    // Add tenantId to params for the getLeads query
    $content = str_replace(
        '$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";\n            $params[] = $limit;\n            $params[] = $offset;',
        '$sql .= $this->tenantSql(); if ($this->tenantId() > 1) $params[] = $this->tenantId();' . "\n" .
        '            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";' . "\n" .
        '            $params[] = $limit;' . "\n" .
        '            $params[] = $offset;',
        $content
    );

    // marketing_leads UPDATE status
    $content = str_replace(
        '$sql = "UPDATE marketing_leads SET status = ?, updated_at = NOW() WHERE id = ?";',
        '$sql = "UPDATE marketing_leads SET status = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        '$this->database->query($sql, [$status, $leadId]);',
        '$params = [$status, $leadId]; if ($this->tenantId() > 1) $params[] = $this->tenantId(); $this->database->query($sql, $params);',
        $content
    );

    // marketing_leads UPDATE score
    $content = str_replace(
        '$sql = "UPDATE marketing_leads SET score = ? WHERE id = ?";',
        '$sql = "UPDATE marketing_leads SET score = ? WHERE id = ?" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        '$this->database->query($sql, [$score, $leadId]);',
        '$params = [$score, $leadId]; if ($this->tenantId() > 1) $params[] = $this->tenantId(); $this->database->query($sql, $params);',
        $content
    );

    // marketing_leads UPDATE last_contacted
    $content = str_replace(
        '$sql = "UPDATE marketing_leads SET last_contacted = NOW() WHERE id = ?";',
        '$sql = "UPDATE marketing_leads SET last_contacted = NOW() WHERE id = ?" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        '$this->database->query($sql, [$leadId]);',
        '$params = [$leadId]; if ($this->tenantId() > 1) $params[] = $this->tenantId(); $this->database->query($sql, $params);',
        $content
    );

    // marketing_leads SELECT COUNT (getTotalLeads)
    $content = str_replace(
        '$sql = "SELECT COUNT(*) as count FROM marketing_leads";',
        '$sql = "SELECT COUNT(*) as count FROM marketing_leads" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        '$result = $this->database->selectOne($sql);\n        return $result[\'count\'] ?? 0;',
        '$params = []; if ($this->tenantId() > 1) $params[] = $this->tenantId(); $result = $this->database->selectOne($sql, $params); return $result[\'count\'] ?? 0;',
        $content
    );

    // marketing_leads SELECT DATE (getNewLeadsToday)
    $content = str_replace(
        '$sql = "SELECT COUNT(*) as count FROM marketing_leads WHERE DATE(created_at) = CURDATE()";',
        '$sql = "SELECT COUNT(*) as count FROM marketing_leads WHERE DATE(created_at) = CURDATE()" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        '$result = $this->database->selectOne($sql);\n        return $result[\'count\'] ?? 0;',
        '$params = []; if ($this->tenantId() > 1) $params[] = $this->tenantId(); $result = $this->database->selectOne($sql, $params); return $result[\'count\'] ?? 0;',
        $content
    );

    // marketing_leads SELECT YEARWEEK (getNewLeadsThisWeek)
    $content = str_replace(
        '$sql = "SELECT COUNT(*) as count FROM marketing_leads WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())";',
        '$sql = "SELECT COUNT(*) as count FROM marketing_leads WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        '$result = $this->database->selectOne($sql);\n        return $result[\'count\'] ?? 0;',
        '$params = []; if ($this->tenantId() > 1) $params[] = $this->tenantId(); $result = $this->database->selectOne($sql, $params); return $result[\'count\'] ?? 0;',
        $content
    );

    // marketing_leads SELECT conversion rate
    $content = str_replace(
        '$sql = "SELECT\n                SUM(CASE WHEN status = \'converted\' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as rate\n                FROM marketing_leads";',
        '$sql = "SELECT\n                SUM(CASE WHEN status = \'converted\' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as rate\n                FROM marketing_leads" . $this->tenantSql();',
        $content
    );
    $content = str_replace(
        '$result = $this->database->selectOne($sql);\n        return round($result[\'rate\'] ?? 0, 2);',
        '$params = []; if ($this->tenantId() > 1) $params[] = $this->tenantId(); $result = $this->database->selectOne($sql, $params); return round($result[\'rate\'] ?? 0, 2);',
        $content
    );

    // marketing_campaigns INSERT
    $content = str_replace(
        '$sql = "INSERT INTO marketing_campaigns (name, type, subject, content, target_audience, schedule_at, status)\n                    VALUES (?, \'email\', ?, ?, ?, ?, \'draft\')";',
        '$insertData = $this->tenantInsertData();' . "\n" .
        '            $columns = array_merge(["name", "type", "subject", "content", "target_audience", "schedule_at", "status"], array_keys($insertData));' . "\n" .
        '            $sql = "INSERT INTO marketing_campaigns (" . implode(",", $columns) . ")\n                    VALUES (" . implode(",", array_fill(0, count($columns), "?")) . ")";',
        $content
    );

    return $content;
}

echo "Script loaded.\n";
