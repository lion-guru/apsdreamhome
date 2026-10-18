<?php
/**
 * ExecutiveAIService — Unified AI Assistant for all executive roles
 * 
 * Single AI agent that adapts behavior based on user role.
 * Injects role-specific context (KPIs, focus areas, data) into every query.
 * 
 * Usage:
 *   $svc = new ExecutiveAIService();
 *   $response = $svc->chat('What are my top priorities today?', 'ceo', $userId);
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Services\AI\AIGateway;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

class ExecutiveAIService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $gateway;

    // Role-specific context definitions
    private array $roleContext = [
        'ceo' => [
            'title' => 'CEO',
            'focus' => ['revenue', 'strategy', 'team_performance', 'market_position'],
            'kpis' => ['total_revenue', 'active_projects', 'team_count', 'conversion_rate'],
            'modules' => ['erp', 'mlm', 'crm', 'finance', 'sales'],
            'persona' => 'You are the AI assistant for the CEO of APS Dream Home, a premium real estate company in Gorakhpur. You help with strategic decisions, revenue analysis, team performance, and business growth. Speak professionally, mix Hindi-English, focus on actionable insights.',
        ],
        'cfo' => [
            'title' => 'CFO',
            'focus' => ['revenue', 'expenses', 'cash_flow', 'tax_compliance', 'profitability'],
            'kpis' => ['total_revenue', 'total_expenses', 'net_profit', 'tax_liability', 'outstanding_tds'],
            'modules' => ['finance', 'payments', 'gst', 'tds', 'banking'],
            'persona' => 'You are the AI assistant for the CFO of APS Dream Home. You specialize in financial analysis, cash flow management, tax compliance (TDS/GST), expense tracking, and profitability optimization. Be precise with numbers, suggest cost-saving measures.',
        ],
        'cto' => [
            'title' => 'CTO',
            'focus' => ['system_health', 'security', 'ai_performance', 'infrastructure'],
            'kpis' => ['system_uptime', 'ai_accuracy', 'active_users', 'security_score'],
            'modules' => ['technology', 'ai', 'security', 'api'],
            'persona' => 'You are the AI assistant for the CTO of APS Dream Home. You help with system architecture, AI/ML optimization, security audits, infrastructure planning, and technology decisions. Be technical, suggest improvements, flag risks.',
        ],
        'coo' => [
            'title' => 'COO',
            'focus' => ['operations', 'projects', 'vendors', 'efficiency'],
            'kpis' => ['active_projects', 'vendor_count', 'project_completion', 'efficiency_score'],
            'modules' => ['operations', 'projects', 'construction', 'vendors'],
            'persona' => 'You are the AI assistant for the COO of APS Dream Home. You optimize daily operations, project timelines, vendor management, and process efficiency. Be practical, focus on execution, identify bottlenecks.',
        ],
        'cmo' => [
            'title' => 'CMO',
            'focus' => ['marketing', 'leads', 'campaigns', 'brand', 'referrals'],
            'kpis' => ['total_leads', 'campaign_performance', 'lead_conversion', 'referral_rate'],
            'modules' => ['marketing', 'crm', 'campaigns', 'advertising'],
            'persona' => 'You are the AI assistant for the CMO of APS Dream Home. You help with marketing strategy, lead generation, campaign optimization, brand building, and referral programs. Be creative, suggest campaigns, analyze what works.',
        ],
        'chro' => [
            'title' => 'CHRO',
            'focus' => ['employees', 'hiring', 'retention', 'training', 'compliance'],
            'kpis' => ['employee_count', 'turnover_rate', 'training_completion', 'attendance_rate'],
            'modules' => ['hrm', 'employees', 'training', 'payroll'],
            'persona' => 'You are the AI assistant for the CHRO of APS Dream Home. You help with HR strategy, talent management, employee engagement, training programs, and compliance. Be empathetic, suggest retention strategies, track team health.',
        ],
        'sales_director' => [
            'title' => 'Sales Director',
            'focus' => ['leads', 'conversions', 'revenue', 'team_performance'],
            'kpis' => ['total_leads', 'conversion_rate', 'revenue', 'properties_sold'],
            'modules' => ['crm', 'sales', 'bookings', 'leads'],
            'persona' => 'You are the AI assistant for the Sales Director of APS Dream Home. You drive sales strategy, lead conversion, team performance, and revenue targets. Be motivational, track numbers, suggest sales tactics.',
        ],
        'marketing_director' => [
            'title' => 'Marketing Director',
            'focus' => ['campaigns', 'leads', 'content', 'analytics'],
            'kpis' => ['campaign_roi', 'lead_volume', 'content_engagement', 'cost_per_lead'],
            'modules' => ['marketing', 'campaigns', 'content', 'advertising'],
            'persona' => 'You are the AI assistant for the Marketing Director of APS Dream Home. You optimize campaigns, content strategy, lead generation channels, and marketing ROI. Be data-driven, suggest A/B tests, analyze trends.',
        ],
        'construction_director' => [
            'title' => 'Construction Director',
            'focus' => ['projects', 'timeline', 'quality', 'materials'],
            'kpis' => ['active_projects', 'completion_rate', 'quality_score', 'material_cost'],
            'modules' => ['projects', 'construction', 'quality', 'materials'],
            'persona' => 'You are the AI assistant for the Construction Director of APS Dream Home. You manage project timelines, quality control, material procurement, and site operations. Be detail-oriented, flag delays, suggest efficiencies.',
        ],
        'finance_director' => [
            'title' => 'Finance Director',
            'focus' => ['payments', 'compliance', 'audit', 'forecasting'],
            'kpis' => ['receivables', 'payables', 'tax_compliance', 'cash_position'],
            'modules' => ['finance', 'payments', 'gst', 'tds', 'audit'],
            'persona' => 'You are the AI assistant for the Finance Director of APS Dream Home. You ensure financial compliance, manage payments/receivables, tax filings, and financial forecasting. Be precise, highlight overdue payments, suggest actions.',
        ],
        'hr_director' => [
            'title' => 'HR Director',
            'focus' => ['recruitment', 'retention', 'training', 'culture'],
            'kpis' => ['open_positions', 'hiring_rate', 'attrition', 'training_hours'],
            'modules' => ['hrm', 'recruitment', 'training', 'performance'],
            'persona' => 'You are the AI assistant for the HR Director of APS Dream Home. You manage talent acquisition, employee development, workplace culture, and HR compliance. Be supportive, suggest process improvements.',
        ],
        'department_manager' => [
            'title' => 'Department Manager',
            'focus' => ['team', 'tasks', 'productivity', 'goals'],
            'kpis' => ['team_size', 'task_completion', 'productivity_score'],
            'modules' => ['hrm', 'tasks', 'attendance'],
            'persona' => 'You are the AI assistant for the Department Manager at APS Dream Home. You help manage team tasks, productivity, and departmental goals. Be practical, help with scheduling and task prioritization.',
        ],
        'project_manager' => [
            'title' => 'Project Manager',
            'focus' => ['timeline', 'milestones', 'resources', 'risks'],
            'kpis' => ['active_projects', 'completion_rate', 'budget_variance'],
            'modules' => ['projects', 'construction', 'colonies'],
            'persona' => 'You are the AI assistant for the Project Manager at APS Dream Home. You track project timelines, manage resources, and identify risks. Be action-oriented, suggest deadline adjustments, flag blockers.',
        ],
        'sales_manager' => [
            'title' => 'Sales Manager',
            'focus' => ['leads', 'pipeline', 'team', 'targets'],
            'kpis' => ['team_leads', 'pipeline_value', 'conversion_rate'],
            'modules' => ['crm', 'sales', 'leads'],
            'persona' => 'You are the AI assistant for the Sales Manager at APS Dream Home. You manage the sales team, track pipeline, and hit targets. Be motivational, suggest lead follow-up strategies.',
        ],
        'hr_manager' => [
            'title' => 'HR Manager',
            'focus' => ['employees', 'attendance', 'leaves', 'payroll'],
            'kpis' => ['employee_count', 'attendance_rate', 'pending_leaves'],
            'modules' => ['hrm', 'attendance', 'payroll'],
            'persona' => 'You are the AI assistant for the HR Manager at APS Dream Home. You handle day-to-day HR operations, attendance, leaves, and payroll. Be organized, suggest process automations.',
        ],
        'marketing_manager' => [
            'title' => 'Marketing Manager',
            'focus' => ['campaigns', 'content', 'social', 'analytics'],
            'kpis' => ['campaign_count', 'engagement_rate', 'lead_source_mix'],
            'modules' => ['marketing', 'campaigns', 'content'],
            'persona' => 'You are the AI assistant for the Marketing Manager at APS Dream Home. You execute marketing campaigns, manage content, and analyze performance. Be creative, suggest content ideas.',
        ],
        'finance_manager' => [
            'title' => 'Finance Manager',
            'focus' => ['accounts', 'reconciliation', 'invoicing', 'reports'],
            'kpis' => ['pending_invoices', 'cash_position', 'reconciliation_status'],
            'modules' => ['finance', 'payments', 'banking'],
            'persona' => 'You are the AI assistant for the Finance Manager at APS Dream Home. You manage daily accounting, reconciliation, invoicing, and financial reports. Be meticulous, flag discrepancies.',
        ],
        'property_manager' => [
            'title' => 'Property Manager',
            'focus' => ['inventory', 'listings', 'occupancy', 'maintenance'],
            'kpis' => ['total_properties', 'available_plots', 'occupancy_rate'],
            'modules' => ['properties', 'plots', 'colonies'],
            'persona' => 'You are the AI assistant for the Property Manager at APS Dream Home. You manage property inventory, listings, and maintenance. Be organized, suggest pricing strategies.',
        ],
        'it_manager' => [
            'title' => 'IT Manager',
            'focus' => ['uptime', 'security', 'performance', 'support'],
            'kpis' => ['system_uptime', 'ticket_count', 'security_incidents'],
            'modules' => ['technology', 'security', 'system'],
            'persona' => 'You are the AI assistant for the IT Manager at APS Dream Home. You manage system health, security, performance, and user support. Be technical, suggest optimizations.',
        ],
        'operations_manager' => [
            'title' => 'Operations Manager',
            'focus' => ['daily_ops', 'vendors', 'processes', 'efficiency'],
            'kpis' => ['active_projects', 'vendor_payments', 'process_compliance'],
            'modules' => ['operations', 'backoffice', 'vendors'],
            'persona' => 'You are the AI assistant for the Operations Manager at APS Dream Home. You streamline daily operations, manage vendors, and improve process efficiency. Be practical, suggest workflow improvements.',
        ],
        'team_lead' => [
            'title' => 'Team Lead',
            'focus' => ['team_tasks', 'performance', 'goals'],
            'kpis' => ['team_size', 'task_completion'],
            'modules' => ['tasks', 'attendance'],
            'persona' => 'You are the AI assistant for the Team Lead at APS Dream Home. You help manage team tasks and performance. Be concise, help with daily planning.',
        ],
        'telecalling_lead' => [
            'title' => 'Telecalling Lead',
            'focus' => ['calls', 'leads', 'performance'],
            'kpis' => ['calls_today', 'leads_assigned', 'conversion_rate'],
            'modules' => ['crm', 'leads', 'calls'],
            'persona' => 'You are the AI assistant for the Telecalling Lead at APS Dream Home. You manage the telecalling team, track call metrics, and optimize lead follow-up. Be data-driven, suggest scripts.',
        ],
        'sales_team_lead' => [
            'title' => 'Sales Team Lead',
            'focus' => ['leads', 'deals', 'team'],
            'kpis' => ['team_leads', 'closed_deals', 'pipeline'],
            'modules' => ['crm', 'sales', 'leads'],
            'persona' => 'You are the AI assistant for the Sales Team Lead at APS Dream Home. You guide the sales team, track deals, and drive conversions. Be motivational.',
        ],
        'support_lead' => [
            'title' => 'Support Lead',
            'focus' => ['tickets', 'satisfaction', 'resolution'],
            'kpis' => ['open_tickets', 'avg_resolution', 'satisfaction_score'],
            'modules' => ['support', 'tickets'],
            'persona' => 'You are the AI assistant for the Support Lead at APS Dream Home. You manage support tickets, track resolution times, and improve customer satisfaction. Be empathetic.',
        ],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->gateway = AIGateway::getInstance();
    }

    /**
     * Process a chat message with role context
     */
    public function chat(string $message, string $role, int $userId): array
    {
        // 1. Build role-specific context
        $context = $this->buildContext($role, $userId);

        // 2. Build the full prompt with role persona
        $roleInfo = $this->roleContext[$role] ?? $this->roleContext['team_lead'];
        $systemPrompt = $roleInfo['persona'] . "\n\nCurrent KPIs for your role:\n";
        foreach ($context['kpis'] as $key => $value) {
            $systemPrompt .= "- {$key}: {$value}\n";
        }
        $systemPrompt .= "\nCompany overview: APS Dream Home is a premium real estate company in Gorakhpur/Lucknow with 5 active colonies, 200+ plots, 56 associates, and ₹1Cr+ in commission payouts. Respond in Hinglish (Hindi-English mix). Be helpful, concise, and actionable.";

        // 3. Send through AI Gateway
        $input = [
            'message' => $message,
            'system_prompt' => $systemPrompt,
        ];
        $result = $this->gateway->process('chat', $input, [
            'role' => $role,
            'user_id' => $userId,
        ]);

        // 4. Build response with suggested actions
        $response = [
            'success' => true,
            'response' => $result['result']['response'] ?? $result['result']['text'] ?? 'I am processing your request.',
            'engine' => $result['engine'] ?? 'unknown',
            'confidence' => $result['confidence'] ?? 0.5,
            'role' => $role,
            'suggested_actions' => $this->getSuggestedActions($role, $message),
            'quick_data' => $context['kpis'],
        ];

        // 5. Log the interaction
        $this->logInteraction($userId, $role, $message, $response['response']);

        return $response;
    }

    /**
     * Build context data for the role from DB
     */
    private function buildContext(string $role, int $userId): array
    {
        $context = ['kpis' => []];
        $pdo = $this->db->getConnection();

        // Safe query helper — returns default on failure
        $safeQuery = function(string $sql, $default = 0) use ($pdo) {
            try { return $pdo->query($sql)->fetch()['c'] ?? $default; }
            catch (\Throwable $e) { return $default; }
        };

        $totalUsers = $safeQuery("SELECT COUNT(*) as c FROM users" . $this->tenantSql());
        $totalProperties = $safeQuery("SELECT COUNT(*) as c FROM properties WHERE status='active'" . $this->tenantSql());
        $totalLeads = $safeQuery("SELECT COUNT(*) as c FROM leads" . $this->tenantSql());
        $todayLeads = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE DATE(created_at) = CURDATE()" . $this->tenantSql());
        $totalRevenue = $safeQuery("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger" . $this->tenantSql());
        $monthRevenue = $safeQuery("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . $this->tenantSql());
        $totalAssociates = $safeQuery("SELECT COUNT(*) as c FROM users WHERE role='associate'" . $this->tenantSql());
        $totalEmployees = $safeQuery("SELECT COUNT(*) as c FROM users WHERE role IN ('employee','telecaller','backoffice_staff')" . $this->tenantSql());
        $activeColonies = $safeQuery("SELECT COUNT(*) as c FROM colonies WHERE status='active'" . $this->tenantSql());
        $availablePlots = $safeQuery("SELECT COUNT(*) as c FROM plots WHERE status='available'" . $this->tenantSql());
        $bookedPlots = $safeQuery("SELECT COUNT(*) as c FROM plots WHERE status='sold'" . $this->tenantSql());
        $totalBookings = $safeQuery("SELECT COUNT(*) as c FROM plot_bookings" . $this->tenantSql());
        $pendingBookings = $safeQuery("SELECT COUNT(*) as c FROM plot_bookings WHERE status='pending'" . $this->tenantSql());
        $convertedLeads = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE status='converted'" . $this->tenantSql());
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;
        $pendingTickets = $safeQuery("SELECT COUNT(*) as c FROM support_tickets WHERE status != 'closed'" . $this->tenantSql());
        $openTasks = $safeQuery("SELECT COUNT(*) as c FROM tasks WHERE status='pending'" . $this->tenantSql());
        $newLeadsToday = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE DATE(created_at) = CURDATE()" . $this->tenantSql());
        $pendingPayments = $safeQuery("SELECT COUNT(*) as c FROM booking_payment_schedules WHERE status='pending'" . $this->tenantSql());

        $context['kpis'] = [
            'Total Revenue' => '₹' . number_format($totalRevenue / 100000, 2) . 'L',
            'Monthly Revenue' => '₹' . number_format($monthRevenue / 100000, 2) . 'L',
            'Total Users' => number_format($totalUsers),
            'Active Properties' => number_format($totalProperties),
            'Total Leads' => number_format($totalLeads),
            'Today Leads' => number_format($todayLeads),
            'Conversion Rate' => $conversionRate . '%',
            'Active Colonies' => $activeColonies,
            'Available Plots' => number_format($availablePlots),
            'Booked Plots' => number_format($bookedPlots),
            'Total Bookings' => number_format($totalBookings),
            'Pending Bookings' => number_format($pendingBookings),
            'Associates' => number_format($totalAssociates),
            'Employees' => number_format($totalEmployees),
            'Open Tickets' => number_format($pendingTickets),
            'Open Tasks' => number_format($openTasks),
            'New Leads Today' => number_format($newLeadsToday),
            'Pending Payments' => number_format($pendingPayments),
        ];

        // Role-specific additional KPIs
        if (in_array($role, ['cfo', 'finance_director', 'finance_manager', 'chartered_accountant', 'senior_accountant'])) {
            $directSale = $safeQuery("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE type='direct_sale'" . $this->tenantSql());
            $overrideEarn = $safeQuery("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE type='override'" . $this->tenantSql());
            $context['kpis']['Direct Sales'] = '₹' . number_format($directSale / 100000, 2) . 'L';
            $context['kpis']['Override Earnings'] = '₹' . number_format($overrideEarn / 100000, 2) . 'L';
        }
        if (in_array($role, ['hr_director', 'hr_manager', 'chro'])) {
            $context['kpis']['Attendance Today'] = $safeQuery("SELECT COUNT(*) as c FROM attendance WHERE DATE(date) = CURDATE() AND status='present'" . $this->tenantSql());
            $context['kpis']['Pending Leaves'] = $safeQuery("SELECT COUNT(*) as c FROM leave_applications WHERE status='pending'" . $this->tenantSql());
        }
        if (in_array($role, ['cmo', 'marketing_director', 'marketing_manager'])) {
            $context['kpis']['Active Campaigns'] = $safeQuery("SELECT COUNT(*) as c FROM marketing_campaigns WHERE status='active'" . $this->tenantSql());
        }

        return $context;
    }

    /**
     * Get suggested actions based on role and query
     */
    private function getSuggestedActions(string $role, string $message): array
    {
        $actions = [];
        $msg = strtolower($message);

        // Role-specific default actions (always show)
        $roleDefaults = [
            'ceo' => [['label' => 'ERP Dashboard', 'url' => '/admin/erp'], ['label' => 'Reports', 'url' => '/admin/reports']],
            'cfo' => [['label' => 'Finance', 'url' => '/admin/dashboard/finance'], ['label' => 'Accounting', 'url' => '/admin/accounting']],
            'cto' => [['label' => 'System Health', 'url' => '/admin/system-health'], ['label' => 'AI Dashboard', 'url' => '/admin/ai']],
            'coo' => [['label' => 'Operations', 'url' => '/admin/dashboard/operations'], ['label' => 'Projects', 'url' => '/admin/projects']],
            'cmo' => [['label' => 'Campaigns', 'url' => '/admin/campaigns'], ['label' => 'Marketing', 'url' => '/admin/dashboard/marketing']],
            'chro' => [['label' => 'HR Dashboard', 'url' => '/admin/dashboard/hr'], ['label' => 'Employees', 'url' => '/admin/hrm/employees']],
            'sales_director' => [['label' => 'Sales', 'url' => '/admin/dashboard/sales'], ['label' => 'Leads', 'url' => '/admin/leads']],
            'marketing_director' => [['label' => 'Campaigns', 'url' => '/admin/campaigns'], ['label' => 'Leads', 'url' => '/admin/leads']],
            'construction_director' => [['label' => 'Projects', 'url' => '/admin/projects'], ['label' => 'Colonies', 'url' => '/admin/plots']],
            'finance_director' => [['label' => 'Finance', 'url' => '/admin/dashboard/finance'], ['label' => 'Payments', 'url' => '/admin/payments']],
            'hr_director' => [['label' => 'HR', 'url' => '/admin/dashboard/hr'], ['label' => 'Employees', 'url' => '/admin/hrm/employees']],
            'department_manager' => [['label' => 'Dashboard', 'url' => '/admin/dashboard'], ['label' => 'Tasks', 'url' => '/admin/tasks']],
            'project_manager' => [['label' => 'Projects', 'url' => '/admin/projects'], ['label' => 'Construction', 'url' => '/admin/dashboard/operations']],
            'sales_manager' => [['label' => 'Sales', 'url' => '/admin/dashboard/sales'], ['label' => 'Leads', 'url' => '/admin/leads']],
            'hr_manager' => [['label' => 'HR', 'url' => '/admin/dashboard/hr'], ['label' => 'Leaves', 'url' => '/admin/hrm/employees']],
            'marketing_manager' => [['label' => 'Campaigns', 'url' => '/admin/campaigns'], ['label' => 'Marketing', 'url' => '/admin/dashboard/marketing']],
            'finance_manager' => [['label' => 'Finance', 'url' => '/admin/dashboard/finance'], ['label' => 'Payments', 'url' => '/admin/payments']],
            'property_manager' => [['label' => 'Properties', 'url' => '/admin/properties'], ['label' => 'Plots', 'url' => '/admin/plots']],
            'it_manager' => [['label' => 'IT Dashboard', 'url' => '/admin/dashboard/it'], ['label' => 'System', 'url' => '/admin/system-health']],
            'operations_manager' => [['label' => 'Operations', 'url' => '/admin/dashboard/operations'], ['label' => 'Tasks', 'url' => '/admin/tasks']],
            'team_lead' => [['label' => 'Dashboard', 'url' => '/admin/dashboard'], ['label' => 'Leads', 'url' => '/admin/leads']],
            'telecalling_lead' => [['label' => 'Telecalling', 'url' => '/admin/dashboard'], ['label' => 'Leads', 'url' => '/admin/leads']],
            'sales_team_lead' => [['label' => 'Sales', 'url' => '/admin/dashboard/sales'], ['label' => 'Leads', 'url' => '/admin/leads']],
            'support_lead' => [['label' => 'Tickets', 'url' => '/admin/support_tickets'], ['label' => 'Dashboard', 'url' => '/admin/dashboard']],
            'admin' => [['label' => 'ERP', 'url' => '/admin/erp'], ['label' => 'Leads', 'url' => '/admin/leads']],
            'super_admin' => [['label' => 'ERP', 'url' => '/admin/erp'], ['label' => 'AI System', 'url' => '/admin/ai']],
        ];

        if (isset($roleDefaults[$role])) {
            $actions = $roleDefaults[$role];
        }

        // Keyword-based extra actions
        if (str_contains($msg, 'lead') || str_contains($msg, 'customer')) {
            $actions[] = ['label' => 'Lead Kanban', 'url' => '/admin/lead-kanban'];
        }
        if (str_contains($msg, 'revenue') || str_contains($msg, 'money') || str_contains($msg, 'sale')) {
            $actions[] = ['label' => 'Commission Ledger', 'url' => '/admin/commission'];
        }
        if (str_contains($msg, 'property') || str_contains($msg, 'plot') || str_contains($msg, 'colony')) {
            $actions[] = ['label' => 'Plots Inventory', 'url' => '/admin/plots'];
        }
        if (str_contains($msg, 'employee') || str_contains($msg, 'team') || str_contains($msg, 'staff')) {
            $actions[] = ['label' => 'Employees', 'url' => '/admin/hrm/employees'];
        }
        if (str_contains($msg, 'payment') || str_contains($msg, 'emi') || str_contains($msg, 'booking')) {
            $actions[] = ['label' => 'Payments', 'url' => '/admin/payments'];
            $actions[] = ['label' => 'EMI Tracker', 'url' => '/admin/emi'];
        }
        if (str_contains($msg, 'campaign') || str_contains($msg, 'marketing')) {
            $actions[] = ['label' => 'Campaigns', 'url' => '/admin/campaigns'];
        }

        return $actions;
    }

    /**
     * Execute AI-suggested action
     */
    public function executeAction(string $action, array $params, string $role, int $userId): array
    {
        // Define allowed actions per role
$allowedActions = [
            'create_campaign' => ['cmo', 'marketing_director', 'marketing_manager', 'ceo', 'admin', 'super_admin'],
            'assign_lead' => ['sales_director', 'sales_manager', 'telecalling_lead', 'sales_team_lead', 'ceo', 'admin'],
            'create_booking' => ['sales_director', 'sales_manager', 'sales_team_lead', 'ceo', 'admin'],
            'schedule_visit' => ['sales_director', 'sales_manager', 'sales_team_lead', 'telecalling_lead', 'ceo', 'admin'],
            'send_notification' => ['ceo', 'cmo', 'chro', 'admin', 'super_admin'],
            'view_report' => ['ceo', 'cfo', 'coo', 'cmo', 'chro', 'cto', 'admin'],
            'approve_booking' => ['sales_director', 'sales_manager', 'ceo', 'admin'],
            'create_task' => ['coo', 'project_manager', 'operations_manager', 'ceo', 'admin'],
            'get_lead_details' => ['sales_director', 'sales_manager', 'telecalling_lead', 'sales_team_lead', 'ceo', 'admin'],
            'create_lead' => ['sales_director', 'sales_manager', 'telecalling_lead', 'sales_team_lead', 'ceo', 'admin'],
            'update_lead_status' => ['sales_director', 'sales_manager', 'telecalling_lead', 'sales_team_lead', 'ceo', 'admin'],
            'send_email' => ['ceo', 'cmo', 'chro', 'admin', 'super_admin'],
            'get_dashboard_stats' => ['ceo', 'cfo', 'coo', 'cmo', 'chro', 'cto', 'admin'],
            'create_payment' => ['cfo', 'finance_director', 'finance_manager', 'ceo', 'admin'],
            'get_user_profile' => ['ceo', 'cmo', 'chro', 'admin', 'super_admin'],
            'get_lead_list' => ['sales_director', 'sales_manager', 'telecalling_lead', 'sales_team_lead', 'ceo', 'admin'],
            'update_booking' => ['sales_director', 'sales_manager', 'ceo', 'admin'],
            'export_leads' => ['ceo', 'cmo', 'sales_director', 'sales_manager', 'admin', 'super_admin'],
            'create_reminder' => ['sales_director', 'sales_manager', 'telecalling_lead', 'sales_team_lead', 'ceo', 'admin'],
            'get_analytics' => ['ceo', 'cfo', 'coo', 'cmo', 'chro', 'cto', 'admin'],
            'bulk_update_leads' => ['sales_director', 'sales_manager', 'ceo', 'admin', 'super_admin'],
            'review_code' => ['cto', 'it_manager', 'senior_developer', 'ceo', 'admin', 'super_admin'],
            'analyze_production' => ['cto', 'it_manager', 'senior_developer', 'ceo', 'admin', 'super_admin'],
        ];

        $actionRoles = $allowedActions[$action] ?? [];
        if (!in_array($role, $actionRoles)) {
            return ['success' => false, 'error' => "Action '$action' not allowed for role '$role'."];
        }

        try {
            switch ($action) {
                case 'create_campaign':
                    return $this->createCampaign($params, $userId);
                case 'assign_lead':
                    return $this->assignLead($params, $userId);
                case 'create_booking':
                    return $this->createBooking($params, $userId);
                case 'schedule_visit':
                    return $this->scheduleVisit($params, $userId);
                case 'send_notification':
                    return $this->sendNotification($params, $userId);
                case 'view_report':
                    return $this->getReport($params);
                case 'approve_booking':
                    return $this->approveBooking($params, $userId);
                case 'create_task':
                    return $this->createTask($params, $userId);
                case 'get_lead_details':
                    return $this->getLeadDetails($params, $userId);
                case 'create_lead':
                    return $this->createLead($params, $userId);
                case 'update_lead_status':
                    return $this->updateLeadStatus($params, $userId);
                case 'send_email':
                    return $this->sendEmail($params, $userId);
                case 'get_dashboard_stats':
                    return $this->getDashboardStats($params, $userId);
                case 'create_payment':
                    return $this->createPayment($params, $userId);
                case 'get_user_profile':
                    return $this->getUserProfile($params, $userId);
                case 'get_lead_list':
                    return $this->getLeadList($params, $userId);
                case 'update_booking':
                    return $this->updateBooking($params, $userId);
                case 'export_leads':
                    return $this->exportLeads($params, $userId);
                case 'create_reminder':
                    return $this->createReminder($params, $userId);
                case 'get_analytics':
                    return $this->getAnalytics($params, $userId);
                case 'bulk_update_leads':
                    return $this->bulkUpdateLeads($params, $userId);
                case 'review_code':
                    return $this->reviewCode($params, $userId);
                case 'analyze_production':
                    return $this->analyzeProduction($params, $userId);
                default:
                    return ['success' => false, 'error' => "Unknown action: $action"];
            }
        } catch (\Throwable $e) {
            error_log("ExecutiveAI executeAction error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Execution failed: ' . $e->getMessage()];
        }
    }

    private function createCampaign(array $params, int $userId): array
    {
        $name = $params['name'] ?? '';
        $type = $params['type'] ?? 'email';
        $budget = (float)($params['budget'] ?? 0);
        
        if (empty($name)) {
            return ['success' => false, 'error' => 'Campaign name is required'];
        }

        $tid = TenantContext::getId();
        $this->db->execute(
            "INSERT INTO marketing_campaigns (name, type, budget, status, created_by, tenant_id, created_at)
             VALUES (?, ?, ?, 'draft', ?, ?, NOW())",
            [$name, $type, $budget, $userId, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => "Campaign '$name' created successfully", 'campaign_id' => $this->db->lastInsertId()];
    }

    private function assignLead(array $params, int $userId): array
    {
        $leadId = (int)($params['lead_id'] ?? 0);
        $assigneeId = (int)($params['assignee_id'] ?? 0);
        
        if (!$leadId || !$assigneeId) {
            return ['success' => false, 'error' => 'lead_id and assignee_id are required'];
        }

        // Verify lead exists
        $tid = TenantContext::getId();
        $lead = $this->db->fetch("SELECT id FROM leads WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$leadId], $tid > 1 ? [$tid] : []));
        if (!$lead) {
            return ['success' => false, 'error' => "Lead #$leadId not found"];
        }

        $this->db->execute(
            "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$assigneeId, $leadId], $tid > 1 ? [$tid] : [])
        );

        // Log activity
        $this->db->execute(
            "INSERT INTO lead_activities (lead_id, subject, activity_type, description, created_by, tenant_id, created_at)
             VALUES (?, 'Lead Assignment', 'assigned', ?, ?, ?, NOW())",
            [$leadId, "Assigned to user $assigneeId", $userId, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => "Lead #$leadId assigned to user $assigneeId"];
    }

    private function createBooking(array $params, int $userId): array
    {
        $leadId = (int)($params['lead_id'] ?? 0);
        $plotId = (int)($params['plot_id'] ?? 0);
        $amount = (float)($params['amount'] ?? 0);
        
        if (!$leadId || !$plotId || !$amount) {
            return ['success' => false, 'error' => 'lead_id, plot_id, and amount are required'];
        }

        $tid = TenantContext::getId();
        $this->db->execute(
            "INSERT INTO plot_bookings (lead_id, plot_id, booking_amount, status, created_by, tenant_id, created_at)
             VALUES (?, ?, ?, 'pending', ?, ?, NOW())",
            [$leadId, $plotId, $amount, $userId, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => 'Booking created successfully', 'booking_id' => $this->db->lastInsertId()];
    }

    private function scheduleVisit(array $params, int $userId): array
    {
        $leadId = (int)($params['lead_id'] ?? 0);
        $plotId = (int)($params['plot_id'] ?? 0);
        $visitDate = $params['visit_date'] ?? '';
        $visitTime = $params['visit_time'] ?? '';
        
        if (!$leadId || !$visitDate) {
            return ['success' => false, 'error' => 'lead_id and visit_date are required'];
        }

        $tid = TenantContext::getId();
        $this->db->execute(
            "INSERT INTO site_visits (lead_id, plot_id, visit_date, visit_time, status, assigned_to, tenant_id, created_at)
             VALUES (?, ?, ?, ?, 'scheduled', ?, ?, NOW())",
            [$leadId, $plotId, $visitDate, $visitTime, $userId, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => 'Site visit scheduled successfully', 'visit_id' => $this->db->lastInsertId()];
    }

    private function sendNotification(array $params, int $userId): array
    {
        $title = $params['title'] ?? '';
        $message = $params['message'] ?? '';
        $recipients = $params['recipients'] ?? 'all';
        
        if (empty($title) || empty($message)) {
            return ['success' => false, 'error' => 'Title and message are required'];
        }

        // Create notification
        $tid = TenantContext::getId();
        $this->db->execute(
            "INSERT INTO notifications (title, message, sender_id, recipient_type, tenant_id, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$title, $message, $userId, $recipients, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => 'Notification sent to ' . $recipients];
    }

    private function getReport(array $params): array
    {
        $reportType = $params['type'] ?? 'sales';
        $period = $params['period'] ?? 'month';
        
        $reports = [
            'sales' => "SELECT COUNT(*) as bookings, COALESCE(SUM(total_plot_value),0) as revenue FROM plot_bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 $period)",
            'leads' => "SELECT COUNT(*) as total, SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 $period)",
            'revenue' => "SELECT COALESCE(SUM(amount),0) as total FROM mlm_commission_ledger WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 $period)",
            'visits' => "SELECT COUNT(*) as total, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed FROM site_visits WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 $period)",
        ];

        if (!isset($reports[$reportType])) {
            return ['success' => false, 'error' => "Unknown report type: $reportType"];
        }

        $tid = TenantContext::getId();
        $sql = $reports[$reportType] . ($tid > 1 ? " AND tenant_id = $tid" : "");
        $result = $this->db->fetch($sql) ?: [];

        return ['success' => true, 'report' => $reportType, 'data' => $result];
    }

    private function approveBooking(array $params, int $userId): array
    {
        $bookingId = (int)($params['booking_id'] ?? 0);
        
        if (!$bookingId) {
            return ['success' => false, 'error' => 'booking_id is required'];
        }

        $tid = TenantContext::getId();
        $this->db->execute(
            "UPDATE plot_bookings SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$userId, $bookingId], $tid > 1 ? [$tid] : [])
        );

        return ['success' => true, 'message' => "Booking #$bookingId approved"];
    }

    private function createTask(array $params, int $userId): array
    {
        $title = $params['title'] ?? '';
        $assigneeId = (int)($params['assignee_id'] ?? $userId);
        $dueDate = $params['due_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $description = $params['description'] ?? '';
        
        if (empty($title)) {
            return ['success' => false, 'error' => 'Task title is required'];
        }

        $tid = TenantContext::getId();
        $this->db->execute(
            "INSERT INTO tasks (title, description, assigned_to, created_by, due_date, status, tenant_id, created_at)
             VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())",
            [$title, $description, $assigneeId, $userId, $dueDate, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => 'Task created successfully', 'task_id' => $this->db->lastInsertId()];
    }

    /**
     * Log AI interaction
     */
    private function logInteraction(int $userId, string $role, string $message, string $response): void
    {
        // NOTE: ai_api_logs has engine_used/service/endpoint (no engine/task/token cols).
        try {
            $logInsertData = $this->tenantInsertData();
            $logCols = 'user_id, engine_used, service, endpoint, status_code, response_time_ms, created_at' . (count($logInsertData) > 0 ? ', tenant_id' : '');
            $logPh = '?, ?, ?, ?, 200, 0, NOW()' . (count($logInsertData) > 0 ? ', ?' : '');
            $logParams = [$userId, 'executive_ai', substr($role, 0, 50), 'chat'];
            if (!empty($logInsertData)) $logParams = array_merge($logParams, array_values($logInsertData));
            $this->db->getConnection()->prepare(
                "INSERT INTO ai_api_logs ($logCols) VALUES ($logPh)"
            )->execute($logParams);
        } catch (\Throwable $e) {
        // Non-critical — don't break the flow
        error_log($e->getMessage());
        }
    }

    /**
     * Get role info for dashboard display
     */
    public function getRoleInfo(string $role): array
    {
        return $this->roleContext[$role] ?? [
            'title' => ucfirst(str_replace('_', ' ', $role)),
            'focus' => [],
            'kpis' => [],
            'persona' => 'You are an AI assistant at APS Dream Home.',
        ];
    }

    /**
     * Get all supported roles
     */
    public function getSupportedRoles(): array
    {
        return array_keys($this->roleContext);
    }

    /**
     * Get lead details
     */
    private function getLeadDetails(array $params, int $userId): array
    {
        $leadId = (int)($params['lead_id'] ?? 0);
        if (!$leadId) {
            return ['success' => false, 'error' => 'lead_id is required'];
        }

        $tid = TenantContext::getId();
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$leadId], $tid > 1 ? [$tid] : []));
        if (!$lead) {
            return ['success' => false, 'error' => "Lead #$leadId not found"];
        }

        // Get lead activities
        $activities = $this->db->fetchAll("SELECT * FROM lead_activities WHERE lead_id = ? ORDER BY created_at DESC LIMIT 10", [$leadId]);

        return ['success' => true, 'lead' => $lead, 'activities' => $activities];
    }

    /**
     * Create a new lead
     */
    private function createLead(array $params, int $userId): array
    {
        $name = $params['name'] ?? '';
        $email = $params['email'] ?? '';
        $phone = $params['phone'] ?? '';
        $budget = (float)($params['budget'] ?? 0);
        $propertyInterest = $params['property_interest'] ?? '';
        $locationPreference = $params['location_preference'] ?? '';
        $source = $params['source'] ?? 'executive_ai';

        if (empty($name) || empty($phone)) {
            return ['success' => false, 'error' => 'Name and phone are required'];
        }

        $tid = TenantContext::getId();
        $this->db->execute(
            "INSERT INTO leads (name, email, phone, budget, property_interest, location_preference, source, status, created_by, tenant_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'new', ?, ?, NOW())",
            [$name, $email, $phone, $budget, $propertyInterest, $locationPreference, $source, $userId, $tid > 1 ? $tid : 1]
        );

        $leadId = $this->db->lastInsertId();
        return ['success' => true, 'message' => 'Lead created successfully', 'lead_id' => $leadId];
    }

    /**
     * Update lead status
     */
    private function updateLeadStatus(array $params, int $userId): array
    {
        $leadId = (int)($params['lead_id'] ?? 0);
        $status = $params['status'] ?? '';
        $notes = $params['notes'] ?? '';

        if (!$leadId || empty($status)) {
            return ['success' => false, 'error' => 'lead_id and status are required'];
        }

        $validStatuses = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture', 'follow_up', 'converted', 'not_interested'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'error' => 'Invalid status. Valid: ' . implode(', ', $validStatuses)];
        }

        $tid = TenantContext::getId();
        $this->db->execute(
            "UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$status, $leadId], $tid > 1 ? [$tid] : [])
        );

        // Log activity
        $this->db->execute(
            "INSERT INTO lead_activities (lead_id, subject, activity_type, description, created_by, tenant_id, created_at)
             VALUES (?, 'Status Update', 'status_change', ?, ?, ?, NOW())",
            [$leadId, "Status changed to $status. $notes", $userId, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => "Lead #$leadId status updated to $status"];
    }

    /**
     * Send email
     */
    private function sendEmail(array $params, int $userId): array
    {
        $to = $params['to'] ?? '';
        $subject = $params['subject'] ?? '';
        $bodyHtml = $params['body_html'] ?? '';
        $bodyText = $params['body_text'] ?? '';
        $fromEmail = $params['from_email'] ?? 'noreply@apsdreamhome.com';
        $fromName = $params['from_name'] ?? 'APS Dream Home';
        $priority = $params['priority'] ?? 'normal';
        $scheduledAt = $params['scheduled_at'] ?? date('Y-m-d H:i:s');

        if (empty($to) || empty($subject) || (empty($bodyHtml) && empty($bodyText))) {
            return ['success' => false, 'error' => 'to, subject, and body (html or text) are required'];
        }

        $tid = TenantContext::getId();
        $queueId = uniqid('eq_', true);
        
        $this->db->execute(
            "INSERT INTO email_queue (queue_id, to_email, to_name, from_email, from_name, subject, body_html, body_text, priority, status, provider, scheduled_at, tenant_id, created_at)
             VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, 'pending', 'smtp', ?, ?, NOW())",
            [$queueId, $to, $fromEmail, $fromName, $subject, $bodyHtml, $bodyText, $priority, $scheduledAt, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => 'Email queued for sending', 'email_id' => $this->db->lastInsertId()];
    }

    /**
     * Get dashboard stats
     */
    private function getDashboardStats(array $params, int $userId): array
    {
        $tid = TenantContext::getId();
        $tenantSql = $tid > 1 ? " AND tenant_id = $tid" : "";

        $stats = [
            'total_users' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM users WHERE status = 'active'" . $tenantSql)['c'],
            'total_leads' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM leads" . $tenantSql)['c'],
            'total_bookings' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM plot_bookings" . $tenantSql)['c'],
            'total_revenue' => (float)$this->db->fetch("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger" . $tenantSql)['c'],
            'pending_leads' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'new'" . $tenantSql)['c'],
            'active_campaigns' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM marketing_campaigns WHERE status = 'active'" . $tenantSql)['c'],
            'pending_visits' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM site_visits WHERE status = 'scheduled'" . $tenantSql)['c'],
            'pending_payments' => (int)$this->db->fetch("SELECT COUNT(*) as c FROM withdrawal_requests WHERE status = 'pending'" . $tenantSql)['c'],
        ];

        return ['success' => true, 'stats' => $stats];
    }

    /**
     * Create payment
     */
    private function createPayment(array $params, int $userId): array
    {
        $amount = (float)($params['amount'] ?? 0);
        $gateway = $params['gateway'] ?? 'cash';
        $reference = $params['reference'] ?? '';
        $bookingId = (int)($params['booking_id'] ?? 0);
        $customerId = (int)($params['customer_id'] ?? 0);
        $paymentType = $params['payment_type'] ?? 'booking';

        if (!$amount || $amount <= 0) {
            return ['success' => false, 'error' => 'Valid amount is required'];
        }

        $tid = TenantContext::getId();
        $this->db->execute(
            "INSERT INTO payments (customer_id, booking_id, amount, total_amount, payment_type, gateway, currency, status, created_by, tenant_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 'INR', 'completed', ?, ?, NOW())",
            [$customerId ?: null, $bookingId ?: null, $amount, $amount, $paymentType, $gateway, $userId, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => 'Payment recorded successfully', 'payment_id' => $this->db->lastInsertId()];
    }

    /**
     * Get user profile
     */
    private function getUserProfile(array $params, int $userId): array
    {
        $targetUserId = (int)($params['user_id'] ?? $userId);
        
        $tid = TenantContext::getId();
        $user = $this->db->fetch("SELECT id, name, email, phone, role, status, referral_code, created_at FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$targetUserId], $tid > 1 ? [$tid] : []));
        
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        // Get wallet info
        $wallet = $this->db->fetch("SELECT points_balance, total_earned, total_used FROM wallet_points WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$targetUserId], $tid > 1 ? [$tid] : []));
        
        // Get bookings
        $bookings = $this->db->fetchAll("SELECT id, plot_id, status, created_at FROM plot_bookings WHERE created_by = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$targetUserId], $tid > 1 ? [$tid] : []));

        return ['success' => true, 'user' => $user, 'wallet' => $wallet, 'bookings' => $bookings];
    }

    /**
     * Get lead list with filters
     */
    private function getLeadList(array $params, int $userId): array
    {
        $status = $params['status'] ?? '';
        $assignedTo = (int)($params['assigned_to'] ?? 0);
        $limit = (int)($params['limit'] ?? 20);
        $offset = (int)($params['offset'] ?? 0);

        $tid = TenantContext::getId();
        $where = "1=1";
        $paramsArr = [];

        if ($status) {
            $where .= " AND status = ?";
            $paramsArr[] = $status;
        }
        if ($assignedTo) {
            $where .= " AND assigned_to = ?";
            $paramsArr[] = $assignedTo;
        }
        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $paramsArr[] = $tid;
        }

        $leads = $this->db->fetchAll("SELECT * FROM leads WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?", array_merge($paramsArr, [$limit, $offset]));
        $total = $this->db->fetch("SELECT COUNT(*) as c FROM leads WHERE $where", $paramsArr)['c'];

        return ['success' => true, 'leads' => $leads, 'total' => $total, 'limit' => $limit, 'offset' => $offset];
    }

    /**
     * Update booking
     */
    private function updateBooking(array $params, int $userId): array
    {
        $bookingId = (int)($params['booking_id'] ?? 0);
        if (!$bookingId) {
            return ['success' => false, 'error' => 'booking_id is required'];
        }

        $tid = TenantContext::getId();
        $updates = [];
        $paramsArr = [];

        $allowedFields = ['status', 'booking_amount', 'agreement_value', 'approval_notes', 'approved_by', 'channel', 'associate_id'];
        foreach ($allowedFields as $field) {
            if (isset($params[$field])) {
                $updates[] = "$field = ?";
                $paramsArr[] = $params[$field];
            }
        }

        if (empty($updates)) {
            return ['success' => false, 'error' => 'No valid fields to update'];
        }

        $paramsArr[] = $bookingId;
        if ($tid > 1) {
            $paramsArr[] = $tid;
        }
        $this->db->execute(
            "UPDATE plot_bookings SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $paramsArr
        );

return ['success' => true, 'message' => "Booking #$bookingId updated successfully"];
    }

    /**
     * Export leads to CSV format data
     */
    private function exportLeads(array $params, int $userId): array
    {
        $status = $params['status'] ?? '';
        $assignedTo = (int)($params['assigned_to'] ?? 0);
        $format = $params['format'] ?? 'csv';

        $tid = TenantContext::getId();
        $where = "1=1";
        $paramsArr = [];

        if ($status) {
            $where .= " AND status = ?";
            $paramsArr[] = $status;
        }
        if ($assignedTo) {
            $where .= " AND assigned_to = ?";
            $paramsArr[] = $assignedTo;
        }
        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $paramsArr[] = $tid;
        }

        $leads = $this->db->fetchAll(
            "SELECT id, lead_number, name, email, phone, status, source, budget, property_interest, 
                    assigned_to, created_by, created_at, updated_at
             FROM leads WHERE $where ORDER BY created_at DESC",
            $paramsArr
        ) ?: [];

        if ($format === 'csv') {
            $headers = ['ID', 'Lead Number', 'Name', 'Email', 'Phone', 'Status', 'Source', 'Budget', 'Property Interest', 'Assigned To', 'Created By', 'Created At', 'Updated At'];
            $rows = [];
            foreach ($leads as $lead) {
                $rows[] = [
                    $lead['id'],
                    $lead['lead_number'] ?? '',
                    $lead['name'],
                    $lead['email'] ?? '',
                    $lead['phone'] ?? '',
                    $lead['status'],
                    $lead['source'],
                    $lead['budget'] ?? 0,
                    $lead['property_interest'] ?? '',
                    $lead['assigned_to'] ?? '',
                    $lead['created_by'] ?? '',
                    $lead['created_at'],
                    $lead['updated_at']
                ];
            }
            
            $output = implode(',', array_map(fn($h) => '"' . str_replace('"', '""', $h) . '"', $headers)) . "\n";
            foreach ($rows as $row) {
                $output .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', $row)) . "\n";
            }
            
            return [
                'success' => true,
                'format' => 'csv',
                'content' => $output,
                'filename' => 'leads_export_' . date('Y-m-d_H-i-s') . '.csv',
                'count' => count($leads)
            ];
        }

        return ['success' => true, 'format' => 'json', 'leads' => $leads, 'count' => count($leads)];
    }

    /**
     * Create a reminder for follow-up
     */
    private function createReminder(array $params, int $userId): array
    {
        $leadId = (int)($params['lead_id'] ?? 0);
        $title = $params['title'] ?? '';
        $description = $params['description'] ?? '';
        $dueDate = $params['due_date'] ?? date('Y-m-d', strtotime('+1 day'));
        $dueTime = $params['due_time'] ?? '09:00:00';
        $reminderType = $params['type'] ?? 'follow_up';

        if (!$leadId || empty($title)) {
            return ['success' => false, 'error' => 'lead_id and title are required'];
        }

        // Verify lead exists
        $tid = TenantContext::getId();
        $lead = $this->db->fetch("SELECT id FROM leads WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$leadId], $tid > 1 ? [$tid] : []));
        if (!$lead) {
            return ['success' => false, 'error' => "Lead #$leadId not found"];
        }

        $tid = TenantContext::getId();
        $this->db->execute(
            "INSERT INTO reminders (lead_id, title, description, due_date, due_time, type, status, created_by, tenant_id, created_at, updated_at, completed_at, snoozed_until)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NULL, NULL)",
            [$leadId, $title, $description, $dueDate, $dueTime, $reminderType, 'pending', $userId, $tid > 1 ? $tid : 1]
        );

        $reminderId = $this->db->lastInsertId();

        // Log activity
        $this->db->execute(
            "INSERT INTO lead_activities (lead_id, subject, activity_type, description, created_by, tenant_id, created_at)
             VALUES (?, 'Reminder Created', 'reminder', ?, ?, ?, NOW())",
            [$leadId, "Reminder: $title - Due: $dueDate $dueTime", $userId, $tid > 1 ? $tid : 1]
        );

        return ['success' => true, 'message' => 'Reminder created successfully', 'reminder_id' => $reminderId];
    }

    /**
     * Get detailed analytics
     */
    private function getAnalytics(array $params, int $userId): array
    {
        $period = $params['period'] ?? '30'; // days
        $tid = TenantContext::getId();
        $tenantSql = $tid > 1 ? " AND tenant_id = $tid" : "";

        $analytics = [];

        // Lead conversion funnel
        $analytics['lead_funnel'] = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL $period DAY) $tenantSql GROUP BY status"
        ) ?: [];

        // Booking conversion
        $analytics['booking_funnel'] = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM plot_bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL $period DAY) $tenantSql GROUP BY status"
        ) ?: [];

        // Revenue trend
        $analytics['revenue_trend'] = $this->db->fetchAll(
            "SELECT DATE(created_at) as date, SUM(amount) as revenue 
             FROM mlm_commission_ledger WHERE created_at >= DATE_SUB(NOW(), INTERVAL $period DAY) $tenantSql GROUP BY DATE(created_at) ORDER BY date"
        ) ?: [];

        // Lead sources
        $analytics['lead_sources'] = $this->db->fetchAll(
            "SELECT source, COUNT(*) as count FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL $period DAY) $tenantSql GROUP BY source ORDER BY count DESC LIMIT 10"
        ) ?: [];

        // Agent performance
        $analytics['agent_performance'] = $this->db->fetchAll(
            "SELECT u.name, u.id, COUNT(l.id) as leads_count, 
                    SUM(CASE WHEN l.status IN ('converted','closed_won') THEN 1 ELSE 0 END) as conversions
             FROM leads l JOIN users u ON l.assigned_to = u.id
             WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL $period DAY) $tenantSql
             GROUP BY u.id ORDER BY conversions DESC LIMIT 10"
        ) ?: [];

        return ['success' => true, 'analytics' => $analytics, 'period_days' => (int)$period];
    }

    /**
     * Bulk update leads
     */
    private function bulkUpdateLeads(array $params, int $userId): array
    {
        $leadIds = $params['lead_ids'] ?? [];
        $updates = [];

        $allowedFields = ['status', 'assigned_to', 'priority', 'notes'];
        foreach ($allowedFields as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $updates[$field] = $params[$field];
            }
        }

        if (empty($leadIds) || empty($updates)) {
            return ['success' => false, 'error' => 'lead_ids and at least one field to update are required'];
        }

        $tid = TenantContext::getId();
        $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
        
        // Build update query
        $setClauses = [];
        $params = [];
        foreach ($updates as $field => $value) {
            $setClauses[] = "$field = ?";
            $params[] = $value;
        }
        $params = array_merge($params, $leadIds);
        if ($tid > 1) $params[] = $tid;

        $sql = "UPDATE leads SET " . implode(', ', $setClauses) . ", updated_at = NOW() 
                WHERE id IN ($placeholders)" . ($tid > 1 ? " AND tenant_id = ?" : "");
        
        $this->db->execute($sql, $params);

        // Get affected rows count using a separate query
        $countSql = "SELECT COUNT(*) as cnt FROM leads WHERE id IN ($placeholders)" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $countParams = array_merge($leadIds, $tid > 1 ? [$tid] : []);
        $affected = (int)($this->db->fetch($countSql, $countParams)['cnt'] ?? 0);

        // Log activities for each lead
        foreach ($leadIds as $leadId) {
            $this->db->execute(
                "INSERT INTO lead_activities (lead_id, subject, activity_type, description, created_by, tenant_id, created_at)
                 VALUES (?, 'Bulk Update', 'bulk_update', ?, ?, ?, NOW())",
                [$leadId, json_encode($updates), $userId, $tid > 1 ? $tid : 1]
            );
        }

        return ['success' => true, 'message' => "Updated $affected leads", 'affected' => $affected];
    }

    /**
     * Senior-dev static review (S306): deterministic detectors + optional AI
     * explanation, findings persisted to `ai_code_findings` (deduped).
     */
    private function reviewCode(array $params, int $userId): array
    {
        $scope = $params['scope'] ?? 'both';
        $withAi = !empty($params['ai']);
        $base = defined('APP_ROOT') ? APP_ROOT . '/app' : null;
        $roots = [];
        if (in_array($scope, ['controllers', 'both'], true)) $roots[] = $base . '/Http/Controllers';
        if (in_array($scope, ['services', 'both'], true)) $roots[] = $base . '/Services';
        if (!empty($params['path']) && is_string($params['path'])) {
            $roots = [$params['path']];
        }
        $runId = 'exec_' . date('YmdHis') . '_' . $userId;
        $agent = new \App\Services\AI\CodeReviewAgent();
        $findings = $agent->scan($roots, true, $runId);
        $byRule = [];
        foreach ($findings as $f) $byRule[$f['rule']] = ($byRule[$f['rule']] ?? 0) + 1;
        $out = ['success' => true, 'run_id' => $runId, 'total' => count($findings), 'by_rule' => $byRule, 'top' => array_slice($findings, 0, 10)];
        if ($withAi && !empty($findings)) {
            $exp = $agent->aiExplain($findings[0]);
            $out['ai_explanation'] = $exp;
        }
        return $out;
    }

    /**
     * Production-log analyst (S306): groups recent PHP fatals/warnings into
     * findings + ai_alerts for criticals. Read-only on logs; writes findings.
     */
    private function analyzeProduction(array $params, int $userId): array
    {
        $lines = max(100, min(2000, (int)($params['tail'] ?? 600)));
        $runId = 'prod_' . date('YmdHis') . '_' . $userId;
        $agent = new \App\Services\AI\CodeReviewAgent();
        $res = $agent->analyzeProductionLog(null, $lines, true, $runId);
        if (!empty($res['ok'])) {
            try {
                $db = \App\Core\Database\Database::getInstance();
                foreach (array_slice($res['top'] ?? [], 0, 3) as $t) {
                    if (($t['severity'] ?? '') === 'high') {
                        $tid = TenantContext::getId();
                        $db->execute(
                            "INSERT INTO ai_alerts (alert_type, severity, message, tenant_id, created_at) VALUES ('prod-log-critical', 'critical', ?, ?, NOW())",
                            [substr($t['message'] ?? '', 0, 400), $tid > 1 ? $tid : 1]
                        );
                    }
                }
            } catch (\Throwable $e) { error_log('analyzeProduction alert: ' . $e->getMessage()); }
        }
        $res['run_id'] = $runId;
        $res['success'] = !empty($res['ok']);
        return $res;
    }
}
