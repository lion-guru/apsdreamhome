<?php
/**
 * AuthSessionTrait — Unified session establishment for all authentication flows
 * 
 * Used by: AuthController, AssociateAuthController, AgentAuthController, 
 *          OtpAuthController, EmployeeController, MobileAuthApiController
 */

namespace App\Traits;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

trait AuthSessionTrait
{
    /**
     * Establish user session after successful authentication
     * 
     * @param array $user User record from database
     * @param string $identity Email or phone used for login (for notifications)
     * @param string $loginType 'password' | 'otp' | 'social' | 'air'
     */
    protected function establishSession(array $user, string $identity = '', string $loginType = 'password'): void
    {
        session_regenerate_id(true);
        $_SESSION['last_regenerate'] = time();

        // Core session data
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['customer_id'] = $user['customer_id'] ?? $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'] ?? '';
        $_SESSION['role'] = $user['role'] ?? 'customer';
        $_SESSION['referral_code'] = $user['referral_code'] ?? '';
        $_SESSION['logged_in'] = true;

        $db = Database::getInstance();
        [$tSql, $tParams] = $this->getTenantSql();

        // Role-specific session IDs
        $role = $_SESSION['role'];
        
        // Associate/Agent: fetch associate_id from associates table
        if (in_array($role, ['agent', 'associate'], true)) {
            try {
                $params = array_merge([(int)$user['id']], $tParams);
                $ass = $db->fetchOne("SELECT id FROM associates WHERE user_id = ?" . $tSql . " LIMIT 1", $params);
                if ($ass) {
                    $_SESSION['associate_id'] = (int)$ass['id'];
                    if ($role === 'agent') {
                        $_SESSION['agent_id'] = (int)$ass['id'];
                    }
                }
            } catch (\Throwable $e) { 
                error_log("AuthSessionTrait associate lookup error: " . $e->getMessage()); 
            }
        } 
        // Employee/Telecaller: fetch employee_id from employees table
        elseif ($role === 'employee' || $role === 'telecaller') {
            try {
                $params = array_merge([(int)$user['id']], $tParams);
                $emp = $db->fetchOne("SELECT id FROM employees WHERE user_id = ?" . $tSql . " LIMIT 1", $params);
                $_SESSION['employee_id'] = (int)($emp['id'] ?? $user['id']);
                $_SESSION['employee_role'] = $role;
            } catch (\Throwable $e) { 
                error_log("AuthSessionTrait employee lookup error: " . $e->getMessage()); 
                $_SESSION['employee_id'] = (int)$user['id'];
                $_SESSION['employee_role'] = $role;
            }
        }
        // Farmer: fetch farmer_id from farmers table
        elseif ($role === 'farmer') {
            try {
                $params = array_merge([(int)$user['id']], $tParams);
                $farmer = $db->fetchOne("SELECT id FROM farmers WHERE user_id = ?" . $tSql . " LIMIT 1", $params);
                if ($farmer) {
                    $_SESSION['farmer_id'] = (int)$farmer['id'];
                }
            } catch (\Throwable $e) { 
                error_log("AuthSessionTrait farmer lookup error: " . $e->getMessage()); 
            }
        }

        // Admin-level roles get admin_id
        $adminRoles = [
            'admin', 'super_admin', 'manager', 'employee', 'telecaller',
            'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro',
            'sales_director', 'marketing_director', 'construction_director',
            'finance_director', 'hr_director', 'operations_director',
            'legal_head', 'finance_head', 'hr_head', 'operations_head',
            'department_manager', 'project_manager', 'sales_manager',
            'hr_manager', 'marketing_manager', 'finance_manager',
            'property_manager', 'it_manager', 'operations_manager',
            'legal_advisor', 'chartered_accountant', 'senior_developer',
            'team_lead', 'telecalling_lead', 'sales_team_lead', 'support_lead',
            'senior_accountant', 'developer', 'content_writer', 'graphic_designer',
            'data_entry_operator', 'backoffice_staff', 'telecalling_executive',
            'support_executive', 'senior_associate', 'associate_team_lead',
            'senior_agent', 'franchise_owner', 'premium_customer',
            'verified_customer', 'guest_customer',
        ];

        if (in_array($role, $adminRoles, true)) {
            $_SESSION['admin_id']       = (int)$user['id'];
            $_SESSION['admin_user_id']  = (int)$user['id'];
            $_SESSION['admin_email']    = $user['email'] ?? '';
            $_SESSION['admin_role']     = $role;
            $_SESSION['admin_name']     = $user['name'] ?? 'Admin';
            $_SESSION['admin_username'] = $user['name'] ?? 'admin';
        }

        // Audit log
        try {
            require_once __DIR__ . '/../Services/AuditService.php';
            $audit = new \App\Services\AuditService($db);
            $audit->log('login', (int)$user['id'], $role, 'user', (int)$user['id'], 'User logged in', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'login_type' => $loginType,
            ]);
        } catch (\Throwable $e) { 
            error_log("AuthSessionTrait audit error: " . $e->getMessage()); 
        }

        // Login notifications
        try {
            require_once __DIR__ . '/../Services/Communication/LoginNotificationService.php';
            $notifier = new \App\Services\Communication\LoginNotificationService();
            $isMobile = !empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad)/i', $_SERVER['HTTP_USER_AGENT']);
            $channel = $loginType === 'otp' ? 'otp' : 'email';
            $notifier->sendLoginAlerts(
                (int)$user['id'], $role,
                $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '',
                $isMobile, $channel
            );
        } catch (\Throwable $e) { 
            error_log("AuthSessionTrait login notification failed: " . $e->getMessage()); 
        }
    }

    /**
     * Get tenant SQL helper
     */
    private function getTenantSql(): array
    {
        $tid = 1;
        try { $tid = TenantContext::getId(); } catch (\Throwable $e) { error_log($e->getMessage()); }
        if ($tid > 1) return [" AND tenant_id = ?", [$tid]];
        return ["", []];
    }

    /**
     * Redirect to role-specific dashboard
     */
    protected function redirectToDashboard(string $role): void
    {
        $map = [
            'admin'                  => '/admin/dashboard',
            'super_admin'            => '/admin/dashboard',
            'manager'                => '/admin/dashboard',
            'employee'               => '/employee/dashboard',
            'telecaller'             => '/employee/dashboard',
            'associate'              => '/associate/dashboard',
            'agent'                  => '/agent/dashboard',
            'customer'               => '/user/dashboard',
            'ceo'                    => '/admin/dashboard/ceo',
            'cfo'                    => '/admin/dashboard/cfo',
            'cto'                    => '/admin/dashboard/cto',
            'coo'                    => '/admin/dashboard/coo',
            'cmo'                    => '/admin/dashboard/cmo',
            'chro'                   => '/admin/dashboard/chro',
            'sales_director'         => '/admin/dashboard/sales',
            'marketing_director'     => '/admin/dashboard/marketing',
            'construction_director'  => '/admin/dashboard/operations',
            'finance_director'       => '/admin/dashboard/finance',
            'hr_director'            => '/admin/dashboard/hr',
            'department_manager'     => '/admin/dashboard/sales',
            'project_manager'        => '/admin/dashboard/operations',
            'sales_manager'          => '/admin/dashboard/sales',
            'hr_manager'             => '/admin/dashboard/hr',
            'marketing_manager'      => '/admin/dashboard/marketing',
            'finance_manager'        => '/admin/dashboard/finance',
            'property_manager'       => '/admin/dashboard/operations',
            'it_manager'             => '/admin/dashboard/it',
            'operations_manager'     => '/admin/dashboard/operations',
            'team_lead'              => '/admin/dashboard',
            'telecalling_lead'       => '/admin/dashboard',
            'sales_team_lead'        => '/admin/dashboard/sales',
            'support_lead'           => '/admin/dashboard',
            'senior_accountant'      => '/admin/dashboard/finance',
            'senior_developer'       => '/admin/dashboard/it',
            'legal_advisor'          => '/admin/dashboard/operations',
            'chartered_accountant'   => '/admin/dashboard/finance',
            'accountant'             => '/admin/dashboard/finance',
            'developer'              => '/admin/dashboard/it',
            'content_writer'         => '/admin/dashboard/marketing',
            'graphic_designer'       => '/admin/dashboard/marketing',
            'data_entry_operator'    => '/admin/dashboard',
            'backoffice_staff'       => '/admin/dashboard',
            'telecalling_executive'  => '/employee/dashboard',
            'support_executive'      => '/employee/dashboard',
            'senior_associate'       => '/associate/dashboard',
            'associate_team_lead'    => '/associate/dashboard',
            'senior_agent'           => '/agent/dashboard',
            'franchise_owner'        => '/admin/dashboard/sales',
            'premium_customer'       => '/user/dashboard',
            'verified_customer'      => '/user/dashboard',
            'guest_customer'         => '/user/dashboard',
            'farmer'                 => '/farmer/dashboard',
        ];
        $redirect = $map[$role] ?? '/admin/dashboard';

        $roleLabels = [
            'admin'       => 'Admin Panel',
            'super_admin' => 'Super Admin Panel',
            'manager'     => 'Manager Dashboard',
            'employee'    => 'Employee Dashboard',
            'telecaller'  => 'Telecaller Dashboard',
            'associate'   => 'Associate Dashboard',
            'agent'       => 'Agent Dashboard',
            'customer'    => 'User Dashboard',
            'ceo'         => 'CEO Dashboard',
            'cfo'         => 'CFO Dashboard',
            'cto'         => 'CTO Dashboard',
            'coo'         => 'COO Dashboard',
            'cmo'         => 'CMO Dashboard',
            'chro'        => 'CHRO Dashboard',
            'sales_director'        => 'Sales Director Dashboard',
            'marketing_director'    => 'Marketing Director Dashboard',
            'construction_director' => 'Construction Director Dashboard',
            'finance_director'      => 'Finance Director Dashboard',
            'hr_director'           => 'HR Director Dashboard',
            'department_manager'    => 'Department Manager Dashboard',
            'project_manager'       => 'Project Manager Dashboard',
            'sales_manager'         => 'Sales Manager Dashboard',
            'hr_manager'            => 'HR Manager Dashboard',
            'marketing_manager'     => 'Marketing Manager Dashboard',
            'finance_manager'       => 'Finance Manager Dashboard',
            'property_manager'      => 'Property Manager Dashboard',
            'it_manager'            => 'IT Manager Dashboard',
            'operations_manager'    => 'Operations Manager Dashboard',
            'team_lead'             => 'Team Dashboard',
            'telecalling_lead'      => 'Telecalling Dashboard',
            'sales_team_lead'       => 'Sales Team Dashboard',
            'support_lead'          => 'Support Dashboard',
            'senior_accountant'     => 'Senior Accountant Dashboard',
            'senior_developer'      => 'Senior Developer Dashboard',
            'legal_advisor'         => 'Legal Advisor Dashboard',
            'chartered_accountant'  => 'Chartered Accountant Dashboard',
            'accountant'            => 'Accountant Dashboard',
            'developer'             => 'Developer Dashboard',
            'content_writer'        => 'Content Writer Dashboard',
            'graphic_designer'      => 'Graphic Designer Dashboard',
            'data_entry_operator'   => 'Data Entry Dashboard',
            'backoffice_staff'      => 'Backoffice Dashboard',
            'telecalling_executive' => 'Telecalling Executive Dashboard',
            'support_executive'     => 'Support Executive Dashboard',
            'senior_associate'      => 'Senior Associate Dashboard',
            'associate_team_lead'   => 'Associate Team Lead Dashboard',
            'senior_agent'          => 'Senior Agent Dashboard',
            'franchise_owner'       => 'Franchise Owner Dashboard',
            'premium_customer'      => 'Premium Customer Dashboard',
            'verified_customer'     => 'Verified Customer Dashboard',
            'guest_customer'        => 'Customer Dashboard',
            'farmer'                => 'Farmer Dashboard',
        ];

        $label = $roleLabels[$role] ?? 'Dashboard';
        $_SESSION['login_success'] = "Welcome! Redirecting to {$label}...";
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}