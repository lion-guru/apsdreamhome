<?php

namespace App\Services\Security;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

class ComplianceService
{
    use ServiceTenantTrait;

    private $db;
    private $weights = [
        'data_encryption'   => 0.25,
        'access_control'    => 0.20,
        'kyc_compliance'    => 0.20,
        'payment_security'  => 0.20,
        'data_retention'    => 0.10,
        'consent_tracking'  => 0.05,
    ];

    private $areaLabels = [
        'data_encryption'   => 'Data Encryption',
        'access_control'    => 'Access Control',
        'kyc_compliance'    => 'KYC Compliance',
        'payment_security'  => 'Payment Security',
        'data_retention'    => 'Data Retention',
        'consent_tracking'  => 'Consent Tracking',
    ];

    private $areaIcons = [
        'data_encryption'   => 'fas fa-lock',
        'access_control'    => 'fas fa-user-shield',
        'kyc_compliance'    => 'fas fa-id-card',
        'payment_security'  => 'fas fa-credit-card',
        'data_retention'    => 'fas fa-database',
        'consent_tracking'  => 'fas fa-file-signature',
    ];

    public function __construct($pdo = null)
    {
        $this->db = $pdo ?? Database::getInstance();
    }

    public function calculateComplianceScore(): array
    {
        $areas = [];
        foreach (array_keys($this->weights) as $area) {
            $areas[$area] = $this->getComplianceArea($area);
        }

        $overall = $this->computeWeightedScore($areas);
        $lastChecked = date('Y-m-d H:i:s');

        $this->storeTrend($overall, $areas, $lastChecked);

        return [
            'overall'         => $overall,
            'areas'           => $areas,
            'last_checked'    => $lastChecked,
            'recommendations' => $this->getPrioritizedRecommendations($areas),
        ];
    }

    public function getComplianceArea(string $area): array
    {
        switch ($area) {
            case 'data_encryption':  return $this->checkDataEncryption();
            case 'access_control':   return $this->checkAccessControl();
            case 'data_retention':   return $this->checkDataRetention();
            case 'consent_tracking': return $this->checkConsentTracking();
            case 'kyc_compliance':   return $this->checkKycCompliance();
            case 'payment_security': return $this->checkPaymentSecurity();
            default:
                return ['score' => 0, 'status' => 'non_compliant', 'details' => 'Unknown area', 'findings' => [], 'recommendations' => []];
        }
    }

    public function getOverallScore(): int
    {
        $areas = [];
        foreach (array_keys($this->weights) as $area) {
            $areas[$area] = $this->getComplianceArea($area);
        }
        return $this->computeWeightedScore($areas);
    }

    public function getComplianceTrend(): array
    {
        try {
            $rows = $this->db->query(
                "SELECT * FROM compliance_scorecard_trend ORDER BY checked_at DESC LIMIT 30"
            )->fetchAll(\PDO::FETCH_ASSOC);
            return $rows ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getPrioritizedRecommendations(?array $areas = null): array
    {
        if ($areas === null) {
            $areas = [];
            foreach (array_keys($this->weights) as $area) {
                $areas[$area] = $this->getComplianceArea($area);
            }
        }

        $all = [];
        foreach ($areas as $key => $data) {
            $weight = $this->weights[$key] ?? 0;
            foreach ($data['recommendations'] as $rec) {
                $impact = (int)ceil((100 - $data['score']) * $weight);
                $all[] = [
                    'area'           => $this->areaLabels[$key] ?? $key,
                    'area_key'       => $key,
                    'recommendation' => $rec,
                    'impact'         => $impact,
                    'priority'       => $data['score'] < 40 ? 'critical' : ($data['score'] < 70 ? 'high' : 'medium'),
                ];
            }
        }

        usort($all, function ($a, $b) {
            if ($a['impact'] !== $b['impact']) {
                return $b['impact'] <=> $a['impact'];
            }
            $order = ['critical' => 0, 'high' => 1, 'medium' => 2];
            return ($order[$a['priority']] ?? 3) <=> ($order[$b['priority']] ?? 3);
        });

        return $all;
    }

    public function getAreaLabels(): array
    {
        return $this->areaLabels;
    }

    public function getAreaIcons(): array
    {
        return $this->areaIcons;
    }

    public function getWeights(): array
    {
        return $this->weights;
    }

    private function computeWeightedScore(array $areas): int
    {
        $total = 0;
        foreach ($this->weights as $area => $weight) {
            $score = $areas[$area]['score'] ?? 0;
            $total += $score * $weight;
        }
        return (int)round($total);
    }

    private function storeTrend(int $overall, array $areas, string $checkedAt): void
    {
        try {
            $this->db->query(
                "INSERT INTO compliance_scorecard_trend (overall_score, area_scores, checked_at) VALUES (?, ?, ?)",
                [$overall, json_encode(array_map(fn($d) => $d['score'], $areas)), $checkedAt]
            );
        } catch (\Exception $e) {
            error_log('ComplianceService::storeTrend error: ' . $e->getMessage());
        }
    }

    private function scoreToStatus(int $score): string
    {
        if ($score >= 80) return 'compliant';
        if ($score >= 50) return 'partial';
        return 'non_compliant';
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        try {
            $result = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
                [$table]
            );
            $cache[$table] = (int)$result > 0;
        } catch (\Exception $e) {
            $cache[$table] = false;
        }
        return $cache[$table];
    }

    private function getCreatedAtColumn(string $table): ?string
    {
        try {
            $cols = $this->db->fetchAll("DESCRIBE {$table}");
            $colNames = array_column($cols, 'Field');
            foreach (['created_at', 'createdAt', 'date_created', 'timestamp'] as $candidate) {
                if (in_array($candidate, $colNames)) {
                    return $candidate;
                }
            }
        } catch (\Exception $e) { error_log($e->getMessage()); }
        return null;
    }

    private function getEncryptedTablesWithColumns(): array
    {
        $encrypted = [];
        try {
            $rows = $this->db->fetchAll(
                "SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND (COLUMN_TYPE LIKE '%binary%' OR COLUMN_TYPE LIKE '%varbinary%')"
            );
            foreach ($rows as $row) {
                $encrypted[$row['TABLE_NAME']][] = $row['COLUMN_NAME'];
            }
        } catch (\Exception $e) { error_log($e->getMessage()); }
        return $encrypted;
    }

    private function checkDataEncryption(): array
    {
        $findings = [];
        $recommendations = [];
        $checksPassed = 0;
        $totalChecks = 5;

        $sensitiveTables = [
            'company_loans'         => ['loan_amount', 'interest_rate', 'penalty_rate'],
            'bank_accounts'         => ['account_number', 'ifsc_code', 'bank_name'],
            'kyc_verification_logs' => ['pan_number', 'aadhaar_number'],
            'booking_payments'      => ['amount', 'payment_method', 'transaction_id'],
            'loan_installments'     => ['amount', 'penalty_amount', 'paid_amount'],
        ];

        $encryptedTables = $this->getEncryptedTablesWithColumns();

        foreach ($sensitiveTables as $table => $expectedCols) {
            if (!$this->tableExists($table)) {
                $findings[] = "Table '{$table}' does not exist — sensitive data location unknown";
                $recommendations[] = "Create or verify {$table} table and ensure sensitive columns are encrypted";
                continue;
            }

            $actualCols = $encryptedTables[$table] ?? [];
            $missing = array_diff($expectedCols, $actualCols);

            if (empty($missing)) {
                $checksPassed++;
            } elseif (count($missing) === count($expectedCols)) {
                $findings[] = "Table '{$table}': 0/" . count($expectedCols) . " sensitive columns encrypted";
                $recommendations[] = "Encrypt sensitive columns in {$table}: " . implode(', ', $expectedCols);
            } else {
                $findings[] = "Table '{$table}': " . count($missing) . "/" . count($expectedCols) . " sensitive columns unencrypted (" . implode(', ', $missing) . ")";
                $recommendations[] = "Encrypt remaining columns in {$table}: " . implode(', ', $missing);
                $checksPassed += 0.5;
            }
        }

        $hasEncryptionKey = !empty(getenv('APP_KEY')) || !empty(getenv('ENCRYPTION_KEY'));
        if ($hasEncryptionKey) {
            $checksPassed++;
        } else {
            $findings[] = "No APP_KEY or ENCRYPTION_KEY environment variable detected";
            $recommendations[] = "Set APP_KEY in .env for AES-256 encryption at rest";
        }

        $score = (int)round(($checksPassed / $totalChecks) * 100);

        return [
            'score'           => $score,
            'status'          => $this->scoreToStatus($score),
            'details'         => "Checked {$totalChecks} encryption points across sensitive financial tables",
            'findings'        => $findings,
            'recommendations' => $recommendations,
        ];
    }

    private function checkAccessControl(): array
    {
        $findings = [];
        $recommendations = [];

        $totalRoutes = 0;
        $protectedRoutes = 0;

        try {
            $routesFile = (defined('APS_ROOT') ? APS_ROOT : dirname(__DIR__, 3)) . '/routes/web.php';
            if (file_exists($routesFile)) {
                $content = file_get_contents($routesFile);
                preg_match_all('/\$router->(get|post|put|delete)\s*\(\s*[\'\"]/', $content, $matches);
                $totalRoutes = count($matches[0] ?? []);
            }
        } catch (\Exception $e) {
            $findings[] = "Could not read routes file for analysis";
        }

        try {
            $protectedRoutes = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM admin_menu_items WHERE is_active = 1");
        } catch (\Exception $e) {
            $findings[] = "Could not query admin_menu_items for route analysis";
        }

        $rbacTablesExist = true;
        foreach (['admin_role_menu_permissions', 'admin_user_menu_permissions'] as $t) {
            if (!$this->tableExists($t)) {
                $rbacTablesExist = false;
                $findings[] = "RBAC table '{$t}' does not exist";
            }
        }

        $roleCount = 0;
        try {
            $roleCount = (int)$this->db->fetchColumn("SELECT COUNT(DISTINCT role) FROM admin_role_menu_permissions");
        } catch (\Exception $e) { error_log($e->getMessage()); }

        $adminCount = 0;
        try {
            $adminCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM admin WHERE is_active = 1");
        } catch (\Exception $e) { error_log($e->getMessage()); }

        $score = 50;
        if ($totalRoutes > 0) {
            $coverage = ($protectedRoutes / $totalRoutes) * 100;
            $score = min(100, (int)round($coverage));
        }
        if ($rbacTablesExist) {
            $score = min(100, $score + 10);
        }
        if ($roleCount > 0) {
            $score = min(100, $score + 5);
        }

        if (!$rbacTablesExist) {
            $recommendations[] = "Create RBAC permission tables (admin_role_menu_permissions, admin_user_menu_permissions)";
        }
        if ($roleCount === 0) {
            $recommendations[] = "Configure role-based menu permissions for admin panel access control";
        }
        if ($adminCount === 0) {
            $findings[] = "No active admin users found in database";
            $recommendations[] = "Ensure at least one admin user exists with proper role assignment";
        }

        $publicRoutes = max(0, $totalRoutes - $protectedRoutes);
        if ($totalRoutes > 0 && $publicRoutes > $totalRoutes * 0.5) {
            $findings[] = round($publicRoutes / $totalRoutes * 100) . "% of routes may lack auth middleware";
            $recommendations[] = "Review public routes and add auth middleware where admin access is required";
        }

        return [
            'score'           => $score,
            'status'          => $this->scoreToStatus($score),
            'details'         => "Analyzed {$totalRoutes} routes, {$protectedRoutes} admin-menu items, {$roleCount} RBAC roles, {$adminCount} active admins",
            'findings'        => $findings,
            'recommendations' => $recommendations,
        ];
    }

    private function checkDataRetention(): array
    {
        $findings = [];
        $recommendations = [];
        $checksPassed = 0;
        $totalChecks = 3;

        $archiveFound = false;
        foreach (['_archive', 'audit_log', 'system_backups'] as $t) {
            if ($this->tableExists($t) || is_dir((defined('APS_ROOT') ? APS_ROOT : dirname(__DIR__, 3)) . '/' . $t)) {
                $archiveFound = true;
                break;
            }
        }

        if ($archiveFound) {
            $checksPassed++;
        } else {
            $findings[] = "No archive/audit trail mechanism found";
            $recommendations[] = "Implement data archival strategy — move records older than 7 years to archive tables";
        }

        $sevenYearsAgo = date('Y-m-d', strtotime('-7 years'));
        $oldRecordCount = 0;
        foreach (['leads', 'booking_payments', 'commissions', 'kyc_verification_logs', 'audit_log'] as $table) {
            if (!$this->tableExists($table)) {
                continue;
            }
            try {
                $createdCol = $this->getCreatedAtColumn($table);
                if ($createdCol) {
                    $oldRecordCount += (int)$this->db->fetchColumn(
                        "SELECT COUNT(*) FROM {$table} WHERE {$createdCol} < ?",
                        [$sevenYearsAgo]
                    );
                }
            } catch (\Exception $e) { error_log($e->getMessage()); }
        }

        if ($oldRecordCount === 0) {
            $checksPassed++;
        } else {
            $findings[] = "{$oldRecordCount} records older than 7 years found without archival";
            $recommendations[] = "Archive or purge records older than 7 years to comply with data retention policies";
        }

        if ($this->tableExists('data_retention_policies')) {
            $checksPassed++;
        } else {
            $findings[] = "No data_retention_policies table found";
            $recommendations[] = "Create a data_retention_policies table to document retention rules per data category";
        }

        $score = (int)round(($checksPassed / $totalChecks) * 100);

        return [
            'score'           => $score,
            'status'          => $this->scoreToStatus($score),
            'details'         => "Checked archival, old records (>7yr), and retention policy documentation",
            'findings'        => $findings,
            'recommendations' => $recommendations,
        ];
    }

    private function checkConsentTracking(): array
    {
        $findings = [];
        $recommendations = [];
        $checksPassed = 0;
        $totalChecks = 3;

        $consentTableFound = false;
        foreach (['user_consents', 'consent_logs', 'privacy_consents'] as $t) {
            if ($this->tableExists($t)) {
                $consentTableFound = true;
                break;
            }
        }

        if ($consentTableFound) {
            $checksPassed++;
        } else {
            $findings[] = "No consent tracking table found";
            $recommendations[] = "Create a user_consents table to record user consent for data processing (GDPR/DPDP compliance)";
        }

        $deletionFound = false;
        foreach (['data_deletion_requests', 'account_deletions', 'gdpr_requests'] as $t) {
            if ($this->tableExists($t)) {
                $deletionFound = true;
                break;
            }
        }

        if ($deletionFound) {
            $checksPassed++;
        } else {
            $findings[] = "No data deletion request mechanism found";
            $recommendations[] = "Implement data_deletion_requests table and API for users to request data erasure";
        }

        $privacyPolicyExists = $this->tableExists('privacy_policies') || $this->tableExists('legal_documents');
        if ($privacyPolicyExists) {
            $checksPassed++;
        } else {
            $findings[] = "No privacy policy version tracking found";
            $recommendations[] = "Track privacy policy versions and record user acceptance timestamps";
        }

        $score = (int)round(($checksPassed / $totalChecks) * 100);

        return [
            'score'           => $score,
            'status'          => $this->scoreToStatus($score),
            'details'         => "Checked consent tracking, deletion requests, and privacy policy versioning",
            'findings'        => $findings,
            'recommendations' => $recommendations,
        ];
    }

    private function checkKycCompliance(): array
    {
        $findings = [];
        $recommendations = [];
        $checksPassed = 0;
        $totalChecks = 4;

        $totalUsers = 0;
        try {
            $totalUsers = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'customer'" . $this->tenantSql());
        } catch (\Exception $e) {
            $findings[] = "Could not query users table for KYC stats";
        }

        $kycVerified = 0;
        $kycPending = 0;
        $kycTotal = 0;

        foreach (['kyc_requests', 'kyc_verification_logs'] as $table) {
            if (!$this->tableExists($table)) {
                continue;
            }
            try {
                if ($table === 'kyc_requests') {
                    $kycTotal = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$table}" . $this->tenantSql());
                    $kycVerified = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$table} WHERE status = 'approved'" . $this->tenantSql());
                    $kycPending = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$table} WHERE status = 'pending'" . $this->tenantSql());
                } elseif ($table === 'kyc_verification_logs') {
                    $kycTotal = max($kycTotal, (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$table}" . $this->tenantSql()));
                    $kycVerified = max($kycVerified, (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$table} WHERE status = 'verified'" . $this->tenantSql()));
                }
            } catch (\Exception $e) { error_log($e->getMessage()); }
        }

        if ($kycTotal > 0) {
            $checksPassed++;
        } else {
            $findings[] = "No KYC verification records found in database";
            $recommendations[] = "Start collecting KYC documents (PAN, Aadhaar) from all customers during registration";
        }

        if ($kycVerified > 0 && $totalUsers > 0) {
            $coverage = ($kycVerified / $totalUsers) * 100;
            if ($coverage >= 80) {
                $checksPassed++;
            } elseif ($coverage >= 40) {
                $checksPassed += 0.5;
                $findings[] = "KYC verification coverage is " . round($coverage) . "% — needs improvement";
                $recommendations[] = "Increase KYC verification rate (currently " . round($coverage) . "%) — send reminders to unverified users";
            } else {
                $findings[] = "KYC verification coverage is critically low at " . round($coverage) . "%";
                $recommendations[] = "Urgently improve KYC completion rate — currently only " . round($coverage) . "% of customers verified";
            }
        } elseif ($totalUsers > 0) {
            $findings[] = "Zero verified KYC records for {$totalUsers} customers";
            $recommendations[] = "Implement KYC verification workflow for all customer accounts";
        }

        $panCheck = false;
        $aadhaarCheck = false;
        foreach (['kyc_requests', 'kyc_verification_logs'] as $table) {
            if (!$this->tableExists($table)) {
                continue;
            }
            try {
                $cols = $this->db->fetchAll("DESCRIBE {$table}");
                $colNames = array_column($cols, 'Field');
                if (in_array('pan_number', $colNames) || in_array('pan', $colNames)) {
                    $panCheck = true;
                }
                if (in_array('aadhaar_number', $colNames) || in_array('aadhaar', $colNames)) {
                    $aadhaarCheck = true;
                }
            } catch (\Exception $e) { error_log($e->getMessage()); }
        }

        if ($panCheck) {
            $checksPassed += 0.5;
        } else {
            $findings[] = "No PAN number field found in KYC tables";
            $recommendations[] = "Add PAN verification field to KYC workflow";
        }

        if ($aadhaarCheck) {
            $checksPassed += 0.5;
        } else {
            $findings[] = "No Aadhaar number field found in KYC tables";
            $recommendations[] = "Add Aadhaar verification field to KYC workflow";
        }

        $score = min(100, (int)round(($checksPassed / $totalChecks) * 100));

        return [
            'score'           => $score,
            'status'          => $this->scoreToStatus($score),
            'details'         => "Checked {$kycTotal} KYC records: {$kycVerified} verified, {$kycPending} pending, {$totalUsers} total customers",
            'findings'        => $findings,
            'recommendations' => $recommendations,
        ];
    }

    private function checkPaymentSecurity(): array
    {
        $findings = [];
        $recommendations = [];
        $checksPassed = 0;
        $totalChecks = 4;

        $noRawCardData = true;
        foreach (['booking_payments', 'loan_installments', 'payment_gateway_logs'] as $table) {
            if (!$this->tableExists($table)) {
                continue;
            }
            try {
                $cols = $this->db->fetchAll("DESCRIBE {$table}");
                $colNames = array_column($cols, 'Field');
                $cardFields = array_filter($colNames, function ($c) {
                    return in_array($c, ['card_number', 'card_no', 'cvv', 'expiry']);
                });
                if (!empty($cardFields)) {
                    $noRawCardData = false;
                    $findings[] = "Table '{$table}' has raw card fields: " . implode(', ', $cardFields);
                    $recommendations[] = "Remove raw card data from {$table} — use tokenized payment references only";
                }
            } catch (\Exception $e) { error_log($e->getMessage()); }
        }

        if ($noRawCardData) {
            $checksPassed++;
        }

        if ($this->tableExists('payment_gateway_logs')) {
            $checksPassed++;
        } else {
            $findings[] = "No payment_gateway_logs table for audit trail";
            $recommendations[] = "Create payment_gateway_logs to record all payment gateway interactions";
        }

        $webhookSecure = $this->tableExists('payment_webhook_signatures') || $this->tableExists('webhook_logs');
        if ($webhookSecure) {
            $checksPassed++;
        } else {
            $findings[] = "No webhook signature verification table found";
            $recommendations[] = "Implement webhook signature verification for payment gateway callbacks";
        }

        $tokenized = false;
        foreach (['booking_payments', 'loan_installments'] as $table) {
            if (!$this->tableExists($table)) {
                continue;
            }
            try {
                $cols = $this->db->fetchAll("DESCRIBE {$table}");
                $colNames = array_column($cols, 'Field');
                if (in_array('transaction_id', $colNames) || in_array('gateway_reference', $colNames) || in_array('razorpay_payment_id', $colNames)) {
                    $tokenized = true;
                    break;
                }
            } catch (\Exception $e) { error_log($e->getMessage()); }
        }

        if ($tokenized) {
            $checksPassed++;
        } else {
            $findings[] = "No tokenized payment reference fields found";
            $recommendations[] = "Use tokenized payment references (transaction_id, gateway_reference) instead of storing raw card data";
        }

        $score = min(100, (int)round(($checksPassed / $totalChecks) * 100));

        return [
            'score'           => $score,
            'status'          => $this->scoreToStatus($score),
            'details'         => "Checked PCI patterns: raw card storage, gateway logging, webhook verification, tokenization",
            'findings'        => $findings,
            'recommendations' => $recommendations,
        ];
    }
}
