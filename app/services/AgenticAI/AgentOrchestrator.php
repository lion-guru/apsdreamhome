<?php
namespace App\Services\AgenticAI;

use App\Core\Database\Database;
use App\Services\CRM\LeadAssignmentService;
use Exception;

class AgentOrchestrator
{
    private $db;
    private $pdo;
    private $agents = [
        'lead_gen' => ['name' => 'Lead Generation Agent', 'enabled' => true],
        'sales' => ['name' => 'Sales Agent', 'enabled' => true],
        'marketing' => ['name' => 'Marketing Agent', 'enabled' => true],
        'ceo' => ['name' => 'CEO Dashboard Agent', 'enabled' => true],
        'hr' => ['name' => 'HR Agent', 'enabled' => true],
        'finance' => ['name' => 'Finance Agent', 'enabled' => true],
        'operations' => ['name' => 'Operations Agent', 'enabled' => true],
        'customer' => ['name' => 'Customer Success Agent', 'enabled' => true],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    public function runAll(): array
    {
        $results = [];
        foreach ($this->agents as $type => $config) {
            try {
                $results[$type] = $this->runAgent($type);
            } catch (Exception $e) {
                $results[$type] = ['error' => $e->getMessage()];
                $this->logTask($type, 'agent_run', ['error' => $e->getMessage()], 'failed');
            }
        }
        return $results;
    }

    public function runAgent(string $agentType): array
    {
        $method = 'execute' . str_replace('_', '', ucwords($agentType, '_'));
        if (!method_exists($this, $method)) {
            return ['error' => "No handler for agent: $agentType"];
        }
        return $this->$method();
    }

    private function executeLeadgen(): array
    {
        $actions = [];
        $newLeads = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn();
        $totalLeads = $this->pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
        $todayLeads = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at)=CURDATE()")->fetchColumn();
        $unassigned = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE assigned_to IS NULL AND status='new'")->fetchColumn();

        if ($newLeads > 0) {
            $actions[] = $this->logTask('lead_gen', 'lead_pipeline_scan',
                ['new_leads' => $newLeads, 'unassigned' => $unassigned, 'total' => $totalLeads], 'completed');
        }

        if ($unassigned > 0) {
            $assigner = new LeadAssignmentService();
            $result = $assigner->autoAssign(100);
            if ($result['assigned'] > 0) {
                $actions[] = $this->logTask('lead_gen', 'auto_assign',
                    ['assigned' => $result['assigned'], 'handlers' => $result['handlers_used']], 'completed');
            }
        }

        $remaining = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE assigned_to IS NULL AND status='new'")->fetchColumn();
        if ($remaining > 0) {
            $this->createEscalation('lead_gen', 'lead_backlog',
                "Lead backlog: $remaining unassigned leads (assigned what we could)",
                ['remaining_unassigned' => $remaining], 'high');
        }

        if ($newLeads > 100) {
            $pctStuck = round($newLeads / max($totalLeads, 1) * 100, 1);
            $this->createInsight('lead_gen', 'lead_stagnation',
                "Lead pipeline stagnation: $newLeads leads stuck at 'new' ($pctStuck%)",
                ['new_leads' => $newLeads, 'total_leads' => $totalLeads, 'stagnation_pct' => $pctStuck], 'high');
        }

        if ($todayLeads > 0) {
            $actions[] = $this->logTask('lead_gen', 'new_leads_today',
                ['count' => $todayLeads], 'completed');
        }

        return $actions;
    }

    private function executeSales(): array
    {
        $actions = [];
        $overdue = $this->pdo->query("SELECT COUNT(*) FROM booking_payment_schedules WHERE status='pending' AND due_date < CURDATE()")->fetchColumn();
        $pendingCommissions = $this->pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger WHERE status='pending'")->fetchColumn();
        $todayBookings = $this->pdo->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at)=CURDATE()")->fetchColumn();

        if ($overdue > 0) {
            $actions[] = $this->logTask('sales', 'overdue_check',
                ['overdue_installments' => $overdue], 'completed');
            $this->createInsight('sales', 'overdue_alert',
                "$overdue overdue installments requiring dunning follow-up",
                ['overdue_count' => $overdue], 'high');
        }

        if ($pendingCommissions > 0) {
            $actions[] = $this->logTask('sales', 'commission_review',
                ['pending_commissions' => $pendingCommissions], 'completed');
        }

        if ($todayBookings > 0) {
            $actions[] = $this->logTask('sales', 'new_booking',
                ['count' => $todayBookings], 'completed');
        }

        $totalValue = $this->pdo->query("SELECT COALESCE(SUM(total_price),0) FROM plots WHERE status='available'")->fetchColumn();
        $actions[] = $this->logTask('sales', 'inventory_value',
            ['available_inventory_value' => round($totalValue, 2)], 'completed');

        return $actions;
    }

    private function executeFinance(): array
    {
        $actions = [];
        $totalDevCost = $this->pdo->query("SELECT COALESCE(SUM(amount + COALESCE(gst_amount,0)),0) FROM colony_development_costs")->fetchColumn();
        $unpaidDevCost = $this->pdo->query("SELECT COALESCE(SUM(balance_amount),0) FROM colony_development_costs WHERE payment_status IN ('unpaid','partial')")->fetchColumn();
        $ledgerTotal = $this->pdo->query("SELECT COALESCE(SUM(amount),0) FROM mlm_commission_ledger")->fetchColumn();
        $ledgerCount = $this->pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger")->fetchColumn();

        $actions[] = $this->logTask('finance', 'cost_summary',
            ['dev_cost_total' => round($totalDevCost, 2), 'unpaid_balance' => round($unpaidDevCost, 2)], 'completed');

        if ($unpaidDevCost > 500000) {
            $this->createInsight('finance', 'vendor_outstanding',
                "Unpaid development costs: ₹" . number_format($unpaidDevCost, 0),
                ['unpaid_amount' => $unpaidDevCost], 'medium');
        }

        $actions[] = $this->logTask('finance', 'ledger_summary',
            ['commission_ledger_entries' => $ledgerCount, 'total_amount' => round($ledgerTotal, 2)], 'completed');

        return $actions;
    }

    private function executeCeo(): array
    {
        $actions = [];
        $totalLeads = $this->pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
        $newLeads = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn();
        $totalPlots = $this->pdo->query("SELECT COUNT(*) FROM plots")->fetchColumn();
        $availPlots = $this->pdo->query("SELECT COUNT(*) FROM plots WHERE status='available'")->fetchColumn();
        $totalBookings = $this->pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
        $activeAssociates = $this->pdo->query("SELECT COUNT(*) FROM associates WHERE status='active'")->fetchColumn();

        $insights = [];
        $leadConvPct = $totalLeads > 0 ? round(($totalLeads - $newLeads) / $totalLeads * 100, 1) : 0;
        $inventorySold = $totalPlots > 0 ? round(($totalPlots - $availPlots) / $totalPlots * 100, 1) : 0;

        $insights[] = "Lead conversion rate: $leadConvPct% ($newLeads of $totalLeads still new)";
        $insights[] = "Inventory sold: $inventorySold% ($availPlots of $totalPlots available)";
        $insights[] = "Total bookings: $totalBookings | Active associates: $activeAssociates";

        $actions[] = $this->logTask('ceo', 'executive_summary',
            ['lead_conversion_pct' => $leadConvPct, 'inventory_sold_pct' => $inventorySold,
             'total_bookings' => $totalBookings, 'active_associates' => $activeAssociates], 'completed');

        if ($leadConvPct < 2) {
            $this->createEscalation('ceo', 'critical_lead_stagnation',
                "CRITICAL: Lead conversion rate is $leadConvPct%. 99%+ leads never convert. Immediate process review needed.",
                ['conversion_rate' => $leadConvPct, 'new_leads' => $newLeads, 'total_leads' => $totalLeads], 'critical');
        }

        $this->createInsight('ceo', 'weekly_business_review',
            implode(' | ', $insights),
            ['lead_conversion' => $leadConvPct, 'inventory_sold' => $inventorySold,
             'bookings' => $totalBookings, 'associates' => $activeAssociates], 'normal');

        return $actions;
    }

    private function executeHr(): array
    {
        $actions = [];
        $activeEmployees = 0;
        $pendingLeave = 0;
        try { $activeEmployees = $this->pdo->query("SELECT COUNT(*) FROM employees WHERE status='active'")->fetchColumn(); } catch (Exception $e) { error_log($e->getMessage()); }
        try { $pendingLeave = $this->pdo->query("SELECT COUNT(*) FROM employee_leave_requests WHERE status='pending'")->fetchColumn(); } catch (Exception $e) { error_log($e->getMessage()); }
        $activeAssociates = $this->pdo->query("SELECT COUNT(*) FROM associates WHERE status='active'")->fetchColumn();

        $actions[] = $this->logTask('hr', 'workforce_summary',
            ['active_employees' => $activeEmployees, 'active_associates' => $activeAssociates,
             'pending_leave_requests' => $pendingLeave], 'completed');

        if ($pendingLeave > 5) {
            $this->createInsight('hr', 'leave_backlog',
                "$pendingLeave pending leave requests awaiting approval",
                ['pending_count' => $pendingLeave], 'low');
        }

        return $actions;
    }

    private function executeMarketing(): array
    {
        $actions = [];
        $properties = $this->pdo->query("SELECT COUNT(*) FROM properties WHERE status='active'")->fetchColumn();
        $incompleteReg = 0;
        try { $incompleteReg = $this->pdo->query("SELECT COUNT(*) FROM incomplete_registrations")->fetchColumn(); } catch (Exception $e) { error_log($e->getMessage()); }

        $actions[] = $this->logTask('marketing', 'inventory_marketing',
            ['active_properties' => $properties, 'incomplete_registrations' => $incompleteReg], 'completed');

        if ($incompleteReg > 100) {
            $this->createInsight('marketing', 'abandoned_carts',
                "$incompleteReg incomplete registrations — abandoned cart recovery campaign needed",
                ['count' => $incompleteReg], 'medium');
        }

        return $actions;
    }

    private function executeOperations(): array
    {
        $actions = [];
        $colonies = $this->pdo->query("SELECT COUNT(*) FROM colonies WHERE is_active=1")->fetchColumn();
        $totalDevCost = $this->pdo->query("SELECT COALESCE(SUM(balance_amount),0) FROM colony_development_costs WHERE payment_status IN ('unpaid','partial')")->fetchColumn();
        $layoutCount = $this->pdo->query("SELECT COUNT(*) FROM colony_layouts WHERE status='draft'")->fetchColumn();

        $actions[] = $this->logTask('operations', 'site_overview',
            ['active_colonies' => $colonies, 'outstanding_dev_payments' => round($totalDevCost, 2),
             'draft_layouts' => $layoutCount], 'completed');

        if ($layoutCount > 0) {
            $this->createInsight('operations', 'pending_layouts',
                "$layoutCount colony layouts in draft status — pending finalization",
                ['draft_count' => $layoutCount], 'low');
        }

        return $actions;
    }

    private function executeCustomer(): array
    {
        $actions = [];
        $totalBookings = $this->pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
        $cancelled = $this->pdo->query("SELECT COUNT(*) FROM bookings WHERE status='cancelled'")->fetchColumn();
        $pendingSupport = $this->pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='open'")->fetchColumn();

        $actions[] = $this->logTask('customer', 'customer_health',
            ['total_bookings' => $totalBookings, 'cancelled' => $cancelled,
             'cancellation_rate' => $totalBookings > 0 ? round($cancelled / $totalBookings * 100, 1) : 0,
             'open_tickets' => $pendingSupport], 'completed');

        if ($pendingSupport > 10) {
            $this->createInsight('customer', 'support_backlog',
                "$pendingSupport open support tickets requiring response",
                ['open_tickets' => $pendingSupport], 'medium');
        }

        if ($cancelled > 0) {
            $this->createInsight('customer', 'cancellation_analysis',
                "$cancelled bookings cancelled out of $totalBookings total",
                ['cancelled' => $cancelled, 'total' => $totalBookings], 'low');
        }

        return $actions;
    }

    private function logTask(string $agentType, string $taskName, array $data, string $status = 'completed'): array
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO agent_task_logs (agent_type, task_name, task_data, status, created_at, completed_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([$agentType, $taskName, json_encode($data), $status]);
        return ['task_id' => $this->pdo->lastInsertId(), 'task' => $taskName, 'status' => $status, 'data' => $data];
    }

    private function createInsight(string $agentType, string $insightType, string $summary, array $data, string $priority = 'normal'): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO agent_insights (agent_type, insight_type, title, summary, data, priority, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );
        $title = ucwords(str_replace('_', ' ', $insightType));
        $stmt->execute([$agentType, $insightType, $title, $summary, json_encode($data), $priority]);
    }

    private function createEscalation(string $agentType, string $type, string $description, array $context, string $status = 'pending'): void
    {
        $title = ucwords(str_replace('_', ' ', $type));
        $stmt = $this->pdo->prepare(
            "INSERT INTO agent_escalations (agent_type, escalation_type, title, description, context, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$agentType, $type, $title, $description, json_encode($context), $status]);
    }
}
