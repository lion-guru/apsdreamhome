<?php
/**
 * IdentityService — Unified identity resolution for APS Dream Home.
 *
 * Phase 4/7: Identity Unification (Session 396)
 * Provides a single entry point to query user + profile data across legacy tables.
 *
 * Usage: $identity = new IdentityService();
 *        $profile = $identity->getAssociateProfile($userId);
 *        $profile = $identity->getEmployeeProfile($userId);
 *        $profile = $identity->getAgentProfile($userId);
 *        $profile = $identity->getCustomerProfile($userId);
 *        $profile = $identity->getUnifiedProfile($userId);
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

class IdentityService
{
    private $db;
    private $tenantId;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->tenantId = TenantContext::getId();
    }

    /**
     * Get associate profile for a user_id.
     * Returns null if not found.
     */
    public function getAssociateProfile(int $userId, bool $withUser = false): ?array
    {
        $tid = $this->tenantId;
        $tenantWhere = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;

        if ($withUser) {
            $sql = "SELECT u.id AS user_id, u.name, u.email, u.phone, u.role, u.status,
                           ap.level, ap.referral_code, ap.sponsor_id, ap.agent_track, ap.agent_type,
                           ap.brokerage_model, ap.brokerage_rate, ap.telecaller_salary, ap.telecaller_incentive_rate,
                           ap.total_sales, ap.commission_earned, ap.team_size, ap.joining_date,
                           ap.can_offline_book, ap.can_collect_cash, ap.daily_cash_limit, ap.cash_policy_agreed
                    FROM users u LEFT JOIN associate_profiles ap ON ap.user_id = u.id
                    WHERE u.id = ? AND u.role = 'associate'{$tenantWhere} LIMIT 1";
        } else {
            $sql = "SELECT ap.* FROM associate_profiles ap
                    JOIN users u ON u.id = ap.user_id
                    WHERE ap.user_id = ? AND u.role = 'associate'{$tenantWhere} LIMIT 1";
        }

        try {
            $row = $this->db->fetch($sql, $params);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('IdentityService::getAssociateProfile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get employee profile for a user_id.
     */
    public function getEmployeeProfile(int $userId, bool $withUser = false): ?array
    {
        $tid = $this->tenantId;
        $tenantWhere = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;

        if ($withUser) {
            $sql = "SELECT u.id AS user_id, u.name, u.email, u.phone, u.role, u.status,
                           ep.employee_code, ep.role AS employee_role, ep.department, ep.designation,
                           ep.joining_date, ep.salary, ep.incentive_model, ep.commission_rate, ep.commission_type,
                           ep.offboarded_at, ep.offboard_reason, ep.address, ep.emergency_contact,
                           ep.date_of_birth, ep.pan_number, ep.aadhaar_number, ep.bank_account, ep.bank_ifsc
                    FROM users u LEFT JOIN employee_profiles ep ON ep.user_id = u.id
                    WHERE u.id = ? AND u.role = 'employee'{$tenantWhere} LIMIT 1";
        } else {
            $sql = "SELECT ep.* FROM employee_profiles ep
                    JOIN users u ON u.id = ep.user_id
                    WHERE ep.user_id = ? AND u.role = 'employee'{$tenantWhere} LIMIT 1";
        }

        try {
            $row = $this->db->fetch($sql, $params);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('IdentityService::getEmployeeProfile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get agent profile for a user_id.
     */
    public function getAgentProfile(int $userId, bool $withUser = false): ?array
    {
        $tid = $this->tenantId;
        $tenantWhere = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;

        if ($withUser) {
            $sql = "SELECT u.id AS user_id, u.name, u.email, u.phone, u.role, u.status,
                           a.license_number, a.experience_years, a.bank_name, a.account_number, a.ifsc_code
                    FROM users u LEFT JOIN agent_profiles a ON a.user_id = u.id
                    WHERE u.id = ? AND u.role = 'agent'{$tenantWhere} LIMIT 1";
        } else {
            $sql = "SELECT a.* FROM agent_profiles a
                    JOIN users u ON u.id = a.user_id
                    WHERE a.user_id = ? AND u.role = 'agent'{$tenantWhere} LIMIT 1";
        }

        try {
            $row = $this->db->fetch($sql, $params);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('IdentityService::getAgentProfile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get customer profile for a user_id.
     */
    public function getCustomerProfile(int $userId, bool $withUser = false): ?array
    {
        $tid = $this->tenantId;
        $tenantWhere = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;

        if ($withUser) {
            $sql = "SELECT u.id AS user_id, u.name, u.email, u.phone, u.role, u.status,
                           cp.preferred_location, cp.budget_min, cp.budget_max, cp.property_type,
                           cp.occupation, cp.annual_income, cp.kyc_status, cp.kyc_documents,
                           cp.preferred_contact_method, cp.marketing_consent
                    FROM users u LEFT JOIN customer_profiles cp ON cp.user_id = u.id
                    WHERE u.id = ? AND u.role = 'customer'{$tenantWhere} LIMIT 1";
        } else {
            $sql = "SELECT cp.* FROM customer_profiles cp
                    JOIN users u ON u.id = cp.user_id
                    WHERE cp.user_id = ? AND u.role = 'customer'{$tenantWhere} LIMIT 1";
        }

        try {
            $row = $this->db->fetch($sql, $params);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('IdentityService::getCustomerProfile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get unified profile for any user_id (regardless of role).
     * Returns user + matching profile data based on role.
     */
    public function getUnifiedProfile(int $userId, bool $withUser = true): ?array
    {
        $tid = $this->tenantId;
        $tenantWhere = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;

        try {
            $user = $this->db->fetch(
                "SELECT u.* FROM users u WHERE u.id = ?{$tenantWhere} LIMIT 1",
                $params
            );
            if (!$user) return null;

            $role = $user['role'] ?? 'customer';
            switch ($role) {
                case 'associate':
                    return $this->getAssociateProfile($userId, $withUser);
                case 'employee':
                    return $this->getEmployeeProfile($userId, $withUser);
                case 'agent':
                    return $this->getAgentProfile($userId, $withUser);
                default:
                    return $this->getCustomerProfile($userId, $withUser);
            }
        } catch (\Throwable $e) {
            error_log('IdentityService::getUnifiedProfile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get active associates count (for dashboard/KPI display).
     */
    public function getActiveAssociatesCount(): int
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT COUNT(*) FROM users u JOIN associate_profiles ap ON ap.user_id = u.id
                    WHERE u.role = 'associate' AND u.status = 'active'";
            if ($tid > 1) {
                $sql .= " AND u.tenant_id = ?";
                return (int)$this->db->fetchColumn($sql, [$tid]);
            }
            return (int)$this->db->fetchColumn($sql);
        } catch (\Throwable $e) {
            error_log('IdentityService::getActiveAssociatesCount: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get active employees count.
     */
    public function getActiveEmployeesCount(): int
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT COUNT(*) FROM users u JOIN employee_profiles ep ON ep.user_id = u.id
                    WHERE u.role = 'employee' AND u.status = 'active'";
            if ($tid > 1) {
                $sql .= " AND u.tenant_id = ?";
                return (int)$this->db->fetchColumn($sql, [$tid]);
            }
            return (int)$this->db->fetchColumn($sql);
        } catch (\Throwable $e) {
            error_log('IdentityService::getActiveEmployeesCount: ' . $e->getMessage());
            return 0;
        }
    }

    // ========================================================================
    // REVERSE LOOKUPS (legacy-id → user_id)
    // The legacy tables (associates/agents/employees) are deprecated but their
    // PKs are still referenced by plot_bookings.associate_id, call_logs.agent_id,
    // employee_* tables etc. These helpers resolve those legacy PKs to user_id
    // so new code can work purely in user_id space.
    // ========================================================================

    /**
     * Resolve legacy associates.id → user_id.
     */
    public function resolveAssociateUserId(int $associateId): int
    {
        if ($associateId <= 0) return 0;
        try {
            $row = $this->db->fetch(
                "SELECT user_id FROM associates WHERE id = ? AND user_id IS NOT NULL LIMIT 1",
                [$associateId]
            );
            return $row ? (int)$row['user_id'] : 0;
        } catch (\Throwable $e) {
            error_log('IdentityService::resolveAssociateUserId: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Resolve legacy agents.id → user_id.
     */
    public function resolveAgentUserId(int $agentId): int
    {
        if ($agentId <= 0) return 0;
        try {
            $row = $this->db->fetch(
                "SELECT user_id FROM agents WHERE id = ? AND user_id IS NOT NULL LIMIT 1",
                [$agentId]
            );
            return $row ? (int)$row['user_id'] : 0;
        } catch (\Throwable $e) {
            error_log('IdentityService::resolveAgentUserId: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Resolve legacy employees.id → user_id.
     */
    public function resolveEmployeeUserId(int $employeeId): int
    {
        if ($employeeId <= 0) return 0;
        try {
            $row = $this->db->fetch(
                "SELECT user_id FROM employees WHERE id = ? AND user_id IS NOT NULL LIMIT 1",
                [$employeeId]
            );
            return $row ? (int)$row['user_id'] : 0;
        } catch (\Throwable $e) {
            error_log('IdentityService::resolveEmployeeUserId: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Resolve user_id → legacy associate id (forward lookup).
     */
    public function resolveAssociateId(int $userId): int
    {
        if ($userId <= 0) return 0;
        try {
            $row = $this->db->fetch(
                "SELECT id FROM associates WHERE user_id = ? LIMIT 1",
                [$userId]
            );
            return $row ? (int)$row['id'] : 0;
        } catch (\Throwable $e) {
            error_log('IdentityService::resolveAssociateId: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Resolve user_id → legacy employee id (forward lookup).
     */
    public function resolveEmployeeId(int $userId): int
    {
        if ($userId <= 0) return 0;
        try {
            $row = $this->db->fetch(
                "SELECT id FROM employees WHERE user_id = ? LIMIT 1",
                [$userId]
            );
            return $row ? (int)$row['id'] : 0;
        } catch (\Throwable $e) {
            error_log('IdentityService::resolveEmployeeId: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get associate status by user_id ('active', 'inactive', 'suspended', 'pending').
     * Falls back to legacy associates table (deprecated but canonical for status).
     */
    public function getAssociateStatus(int $userId): string
    {
        if ($userId <= 0) return '';
        try {
            $row = $this->db->fetch(
                "SELECT ap.status FROM associate_profiles ap WHERE ap.user_id = ? LIMIT 1",
                [$userId]
            );
            if ($row && !empty($row['status'])) return (string)$row['status'];

            // Fallback to legacy table (deprecated but still canonical for status)
            $row = $this->db->fetch(
                "SELECT status FROM associates WHERE user_id = ? LIMIT 1",
                [$userId]
            );
            return $row ? (string)($row['status'] ?? '') : '';
        } catch (\Throwable $e) {
            error_log('IdentityService::getAssociateStatus: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get employee status by user_id ('active', 'inactive', 'on_leave', 'terminated').
     * Uses legacy employees table (deprecated but canonical for status).
     */
    public function getEmployeeStatus(int $userId): string
    {
        if ($userId <= 0) return '';
        try {
            // employee_profiles has no status column — use legacy table (deprecated but canonical)
            $row = $this->db->fetch(
                "SELECT status FROM employees WHERE user_id = ? LIMIT 1",
                [$userId]
            );
            return $row ? (string)($row['status'] ?? '') : '';
        } catch (\Throwable $e) {
            error_log('IdentityService::getEmployeeStatus: ' . $e->getMessage());
            return '';
        }
    }
}
