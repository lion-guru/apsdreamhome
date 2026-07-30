<?php
namespace App\Services\AgenticAI;

use App\Core\Database\Database;
use App\Services\CRM\LeadAssignmentService;
use Exception;

class AgentOrchestrator
{
    use \App\Traits\ServiceTenantTrait;

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
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $newLeads = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE status='new'{$tenantSql}")->fetchColumn();
        $totalLeads = $this->pdo->query("SELECT COUNT(*) FROM leads{$tenantSql}")->fetchColumn();
        $todayLeads = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at)=CURDATE(){$tenantSql}")->fetchColumn();
        $unassigned = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE assigned_to IS NULL AND status='new'{$tenantSql}")->fetchColumn();

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

        $remaining = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE assigned_to IS NULL AND status='new'{$tenantSql}")->fetchColumn();
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
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $overdue = $this->pdo->query("SELECT COUNT(*) FROM booking_payment_schedules WHERE status='pending' AND due_date < CURDATE(){$tenantSql}")->fetchColumn();
        $pendingCommissions = $this->pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger WHERE status='pending'{$tenantSql}")->fetchColumn();
        $todayBookings = $this->pdo->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at)=CURDATE(){$tenantSql}")->fetchColumn();

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

        $totalValue = $this->pdo->query("SELECT COALESCE(SUM(total_price),0) FROM plots WHERE status='available'{$tenantSql}")->fetchColumn();
        $actions[] = $this->logTask('sales', 'inventory_value',
            ['available_inventory_value' => round($totalValue, 2)], 'completed');

        return $actions;
    }

    private function executeFinance(): array
    {
        $actions = [];
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $totalDevCost = $this->pdo->query("SELECT COALESCE(SUM(amount + COALESCE(gst_amount,0)),0) FROM colony_development_costs{$tenantSql}")->fetchColumn();
        $unpaidDevCost = $this->pdo->query("SELECT COALESCE(SUM(balance_amount),0) FROM colony_development_costs WHERE payment_status IN ('unpaid','partial'){$tenantSql}")->fetchColumn();
        $ledgerTotal = $this->pdo->query("SELECT COALESCE(SUM(amount),0) FROM mlm_commission_ledger{$tenantSql}")->fetchColumn();
        $ledgerCount = $this->pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger{$tenantSql}")->fetchColumn();

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
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $totalLeads = $this->pdo->query("SELECT COUNT(*) FROM leads{$tenantSql}")->fetchColumn();
        $newLeads = $this->pdo->query("SELECT COUNT(*) FROM leads WHERE status='new'{$tenantSql}")->fetchColumn();
        $totalPlots = $this->pdo->query("SELECT COUNT(*) FROM plots{$tenantSql}")->fetchColumn();
        $availPlots = $this->pdo->query("SELECT COUNT(*) FROM plots WHERE status='available'{$tenantSql}")->fetchColumn();
        $totalBookings = $this->pdo->query("SELECT COUNT(*) FROM bookings{$tenantSql}")->fetchColumn();
        $activeAssociates = $this->pdo->query("SELECT COUNT(*) FROM associates WHERE status='active'{$tenantSql}")->fetchColumn();

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
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $activeEmployees = 0;
        $pendingLeave = 0;
        try { $activeEmployees = $this->pdo->query("SELECT COUNT(*) FROM employees WHERE status='active'{$tenantSql}")->fetchColumn(); } catch (Exception $e) { error_log($e->getMessage()); }
        try { $pendingLeave = $this->pdo->query("SELECT COUNT(*) FROM employee_leave_requests WHERE status='pending'{$tenantSql}")->fetchColumn(); } catch (Exception $e) { error_log($e->getMessage()); }
        $activeAssociates = $this->pdo->query("SELECT COUNT(*) FROM associates WHERE status='active'{$tenantSql}")->fetchColumn();

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
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $properties = $this->pdo->query("SELECT COUNT(*) FROM properties WHERE status='active'{$tenantSql}")->fetchColumn();
        $incompleteReg = 0;
        try { $incompleteReg = $this->pdo->query("SELECT COUNT(*) FROM incomplete_registrations{$tenantSql}")->fetchColumn(); } catch (Exception $e) { error_log($e->getMessage()); }

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
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $colonies = $this->pdo->query("SELECT COUNT(*) FROM colonies WHERE is_active=1{$tenantSql}")->fetchColumn();
        $totalDevCost = $this->pdo->query("SELECT COALESCE(SUM(balance_amount),0) FROM colony_development_costs WHERE payment_status IN ('unpaid','partial'){$tenantSql}")->fetchColumn();
        $layoutCount = $this->pdo->query("SELECT COUNT(*) FROM colony_layouts WHERE status='draft'{$tenantSql}")->fetchColumn();

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
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $totalBookings = $this->pdo->query("SELECT COUNT(*) FROM bookings{$tenantSql}")->fetchColumn();
        $cancelled = $this->pdo->query("SELECT COUNT(*) FROM bookings WHERE status='cancelled'{$tenantSql}")->fetchColumn();
        $pendingSupport = $this->pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='open'{$tenantSql}")->fetchColumn();

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
        $tenantIns = $this->tenantInsertData();
        $insCols = array_merge(['agent_type', 'task_name', 'task_data', 'status', 'created_at', 'completed_at'], array_keys($tenantIns));
        $insVals = array_merge([$agentType, $taskName, json_encode($data), $status], array_values($tenantIns));
        $colStr = implode(', ', $insCols);
        $placeholders = implode(', ', array_fill(0, count($insVals), '?'));
        $this->pdo->prepare("INSERT INTO agent_task_logs ($colStr) VALUES ($placeholders)")->execute($insVals);
        return ['task_id' => $this->pdo->lastInsertId(), 'task' => $taskName, 'status' => $status, 'data' => $data];
    }

    private function createInsight(string $agentType, string $insightType, string $summary, array $data, string $priority = 'normal'): void
    {
        $tenantIns = $this->tenantInsertData();
        $insCols = array_merge(['agent_type', 'insight_type', 'title', 'summary', 'data', 'priority', 'created_at'], array_keys($tenantIns));
        $insVals = array_merge([$agentType, $insightType, ucwords(str_replace('_', ' ', $insightType)), $summary, json_encode($data), $priority], array_values($tenantIns));
        $colStr = implode(', ', $insCols);
        $placeholders = implode(', ', array_fill(0, count($insVals), '?'));
        $this->pdo->prepare("INSERT INTO agent_insights ($colStr) VALUES ($placeholders)")->execute($insVals);
    }

    private function createEscalation(string $agentType, string $type, string $description, array $context, string $status = 'pending'): void
    {
        $tenantIns = $this->tenantInsertData();
        $insCols = array_merge(['agent_type', 'escalation_type', 'title', 'description', 'context', 'status', 'created_at'], array_keys($tenantIns));
        $insVals = array_merge([$agentType, $type, ucwords(str_replace('_', ' ', $type)), $description, json_encode($context), $status], array_values($tenantIns));
        $colStr = implode(', ', $insCols);
        $placeholders = implode(', ', array_fill(0, count($insVals), '?'));
        $this->pdo->prepare("INSERT INTO agent_escalations ($colStr) VALUES ($placeholders)")->execute($insVals);
    }
}
