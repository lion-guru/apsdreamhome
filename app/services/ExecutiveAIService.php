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

class ExecutiveAIService
{
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

        $totalUsers = $safeQuery("SELECT COUNT(*) as c FROM users");
        $totalProperties = $safeQuery("SELECT COUNT(*) as c FROM properties WHERE status='active'");
        $totalLeads = $safeQuery("SELECT COUNT(*) as c FROM leads");
        $todayLeads = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE DATE(created_at) = CURDATE()");
        $totalRevenue = $safeQuery("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger");
        $monthRevenue = $safeQuery("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $totalAssociates = $safeQuery("SELECT COUNT(*) as c FROM users WHERE role='associate'");
        $totalEmployees = $safeQuery("SELECT COUNT(*) as c FROM users WHERE role IN ('employee','telecaller','backoffice_staff')");
        $activeColonies = $safeQuery("SELECT COUNT(*) as c FROM colonies WHERE status='active'");
        $availablePlots = $safeQuery("SELECT COUNT(*) as c FROM plots WHERE status='available'");
        $bookedPlots = $safeQuery("SELECT COUNT(*) as c FROM plots WHERE status='sold'");
        $totalBookings = $safeQuery("SELECT COUNT(*) as c FROM plot_bookings");
        $pendingBookings = $safeQuery("SELECT COUNT(*) as c FROM plot_bookings WHERE status='pending'");
        $convertedLeads = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE status='converted'");
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;
        $pendingTickets = $safeQuery("SELECT COUNT(*) as c FROM support_tickets WHERE status != 'closed'");
        $openTasks = $safeQuery("SELECT COUNT(*) as c FROM tasks WHERE status='pending'");
        $newLeadsToday = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE DATE(created_at) = CURDATE()");
        $pendingPayments = $safeQuery("SELECT COUNT(*) as c FROM booking_payment_schedules WHERE status='pending'");

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
            $directSale = $safeQuery("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE type='direct_sale'");
            $overrideEarn = $safeQuery("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE type='override'");
            $context['kpis']['Direct Sales'] = '₹' . number_format($directSale / 100000, 2) . 'L';
            $context['kpis']['Override Earnings'] = '₹' . number_format($overrideEarn / 100000, 2) . 'L';
        }
        if (in_array($role, ['hr_director', 'hr_manager', 'chro'])) {
            $context['kpis']['Attendance Today'] = $safeQuery("SELECT COUNT(*) as c FROM attendance WHERE DATE(date) = CURDATE() AND status='present'");
            $context['kpis']['Pending Leaves'] = $safeQuery("SELECT COUNT(*) as c FROM leave_applications WHERE status='pending'");
        }
        if (in_array($role, ['cmo', 'marketing_director', 'marketing_manager'])) {
            $context['kpis']['Active Campaigns'] = $safeQuery("SELECT COUNT(*) as c FROM marketing_campaigns WHERE status='active'");
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
     * Log AI interaction
     */
    private function logInteraction(int $userId, string $role, string $message, string $response): void
    {
        try {
            $this->db->getConnection()->prepare("
                INSERT INTO ai_api_logs (user_id, engine, task, input_tokens, output_tokens, response_time_ms, created_at)
                VALUES (?, 'executive_ai', 'chat', ?, ?, 0, NOW())
            ")->execute([$userId, mb_strlen($message), mb_strlen($response)]);
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
}
