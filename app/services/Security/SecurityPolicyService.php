<?php

namespace App\Services\Security;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Security Policy Service
 * Handles comprehensive security policy management
 */
class SecurityPolicyService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $policies = [];

    // Policy modes
    public const MODE_STRICT = 'strict';
    public const MODE_BALANCED = 'balanced';
    public const MODE_PERMISSIVE = 'permissive';

    // Policy domains
    public const DOMAIN_AUTH = 'authentication';
    public const DOMAIN_DATA = 'data_protection';
    public const DOMAIN_ACCESS = 'access_control';
    public const DOMAIN_NETWORK = 'network_security';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'default_mode' => self::MODE_BALANCED,
            'auto_enforce' => true,
            'audit_policies' => true,
            'policy_cache_ttl' => 600
        ], $config);
        
        $this->initializePolicyTables();
        $this->loadDefaultPolicies();
    }

    /**
     * Create security policy
     */
    public function createPolicy(string $domain, string $name, array $rules, string $mode = self::MODE_BALANCED): array
    {
        try {
            $policyId = $this->createPolicyRecord($domain, $name, $rules, $mode);
            
            if ($this->config['auto_enforce']) {
                $this->enforcePolicy($policyId);
            }

            return [
                'success' => true,
                'message' => 'Policy created successfully',
                'policy_id' => $policyId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create policy: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enforce security policies
     */
    public function enforcePolicies(): array
    {
        try {
            $policies = $this->getActivePolicies();
            $enforced = 0;
            $violations = 0;

            foreach ($policies as $policy) {
                $result = $this->enforcePolicy($policy['id']);
                
                if ($result['success']) {
                    $enforced++;
                } else {
                    $violations++;
                }
            }

            return [
                'success' => true,
                'message' => "Enforced {$enforced} policies",
                'enforced' => $enforced,
                'violations' => $violations
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Policy enforcement failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get policy compliance report
     */
    public function getComplianceReport(): array
    {
        try {
            $policies = $this->getActivePolicies();
            $report = [
                'total_policies' => count($policies),
                'compliant' => 0,
                'non_compliant' => 0,
                'policies' => []
            ];

            foreach ($policies as $policy) {
                $compliance = $this->checkPolicyCompliance($policy);
                
                $report['policies'][] = [
                    'id' => $policy['id'],
                    'domain' => $policy['domain'],
                    'name' => $policy['name'],
                    'compliant' => $compliance,
                    'violations' => $compliance ? [] : $this->getPolicyViolations($policy['id'])
                ];

                if ($compliance) {
                    $report['compliant']++;
                } else {
                    $report['non_compliant']++;
                }
            }

            $report['compliance_percentage'] = $report['total_policies'] > 0 
                ? ($report['compliant'] / $report['total_policies']) * 100 
                : 0;

            return $report;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate compliance report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function initializePolicyTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS security_policies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                domain VARCHAR(50) NOT NULL,
                name VARCHAR(255) NOT NULL,
                rules JSON NOT NULL,
                mode ENUM('strict', 'balanced', 'permissive') DEFAULT 'balanced',
                enabled BOOLEAN DEFAULT TRUE,
                created_by VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_domain (domain),
                INDEX idx_enabled (enabled)
            )",
            
            "CREATE TABLE IF NOT EXISTS policy_violations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                policy_id INT NOT NULL,
                violation_type VARCHAR(100),
                description TEXT,
                severity INT DEFAULT 2,
                resolved BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (policy_id) REFERENCES security_policies(id) ON DELETE CASCADE
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function loadDefaultPolicies(): void
    {
        $this->policies = [
            self::DOMAIN_AUTH => [
                'max_login_attempts' => 5,
                'password_min_length' => 12,
                'session_timeout' => 1800,
                'two_factor_required' => false
            ],
            self::DOMAIN_DATA => [
                'encryption_at_rest' => true,
                'encryption_in_transit' => true,
                'data_retention_days' => 365,
                'backup_frequency' => 'daily'
            ],
            self::DOMAIN_ACCESS => [
                'role_based_access' => true,
                'least_privilege' => true,
                'audit_access' => true,
                'session_concurrent_limit' => 3
            ],
            self::DOMAIN_NETWORK => [
                'https_required' => true,
                'security_headers' => true,
                'rate_limiting' => true,
                'ip_whitelist' => false
            ]
        ];
    }

    private function createPolicyRecord(string $domain, string $name, array $rules, string $mode): string
    {
        $sql = "INSERT INTO security_policies 
                (domain, name, rules, mode, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            $domain,
            $name,
            json_encode($rules),
            $mode,
            $this->getCurrentUserId()
        ]);
        
        return $this->db->lastInsertId();
    }

    private function enforcePolicy(int $policyId): array
    {
        try {
            $policy = $this->getPolicy($policyId);
            if (!$policy) {
                return ['success' => false, 'message' => 'Policy not found'];
            }

            $rules = json_decode($policy['rules'], true) ?? [];
            $violations = [];

            foreach ($rules as $rule => $expected) {
                $actual = $this->checkRule($rule);
                if ($actual !== $expected) {
                    $violations[] = $rule;
                    $this->logPolicyViolation($policyId, $rule, $expected, $actual);
                }
            }

            return [
                'success' => empty($violations),
                'violations' => $violations
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function getActivePolicies(): array
    {
        $sql = "SELECT * FROM security_policies WHERE enabled = 1";
        return $this->db->fetchAll($sql);
    }

    private function getPolicy(int $id): ?array
    {
        $sql = "SELECT * FROM security_policies WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    private function checkPolicyCompliance(array $policy): bool
    {
        $rules = json_decode($policy['rules'], true) ?? [];
        
        foreach ($rules as $rule => $expected) {
            $actual = $this->checkRule($rule);
            if ($actual !== $expected) {
                return false;
            }
        }
        
        return true;
    }

    private function getPolicyViolations(int $policyId): array
    {
        $sql = "SELECT * FROM policy_violations WHERE policy_id = ? AND resolved = FALSE";
        return $this->db->fetchAll($sql, [$policyId]);
    }

    private function checkRule(string $rule): bool
    {
        switch ($rule) {
            case 'https_required':
                return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            case 'security_headers':
                return true; // Would check actual headers
            case 'rate_limiting':
                return true; // Would check rate limiting status
            case 'two_factor_required':
                return true; // Would check 2FA status
            default:
                return true;
        }
    }

    private function logPolicyViolation(int $policyId, string $rule, $expected, $actual): void
    {
        $sql = "INSERT INTO policy_violations 
                (policy_id, violation_type, description, severity, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            $policyId,
            $rule,
            "Expected: {$expected}, Actual: {$actual}",
            2
        ]);
    }

    private function getCurrentUserId(): string
    {
        return $_SESSION['user_id'] ?? 'system';
    }
}
