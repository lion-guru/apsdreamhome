<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\ProgressiveRegistrationService;
use App\Services\PayrollService;
use App\Services\ResellPropertyService;
use App\Services\CommissionService;
use App\Services\NotificationService;
use App\Services\SecurityService;
use App\Services\FinanceService;
use App\Services\AnalyticsService;
use App\Services\AgentOrchestrator;
use App\Services\OcrService;
use App\Services\PropertyMarketplaceService;

class NewFeaturesController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    private function reg(): ProgressiveRegistrationService { return new ProgressiveRegistrationService($this->db); }
    private function pay(): PayrollService { return new PayrollService($this->db); }
    private function resell(): ResellPropertyService { return new ResellPropertyService($this->db); }
    private function comm(): CommissionService { return new CommissionService($this->db); }
    private function notif(): NotificationService { return new NotificationService($this->db); }
    private function sec(): SecurityService { return new SecurityService($this->db); }
    private function fin(): FinanceService { return new FinanceService($this->db); }
    private function analytics(): AnalyticsService { return new AnalyticsService($this->db); }
    private function agent(): AgentOrchestrator { return new AgentOrchestrator($this->db); }
    private function ocr(): OcrService { return new OcrService($this->db); }
    private function mkt(): PropertyMarketplaceService { return new PropertyMarketplaceService($this->db); }

    public function progressiveRegistrations()
    {
        $this->requireAdmin();
        $incomplete = $this->reg()->listIncomplete(100);
        $stats = $this->reg()->abandonmentStats(30);
        $this->data = array_merge($this->data, [
            'page_title' => 'Progressive Registrations',
            'incomplete' => $incomplete,
            'stats' => $stats,
        ]);
        return $this->render('admin/features/progressive_registrations', $this->data);
    }

    public function payroll()
    {
        $this->requireAdmin();
        $advances = $this->pay()->listAdvances();
        $bonuses = $this->pay()->listBonuses();
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        $entries = $this->pay()->listPayroll($currentMonth, $currentYear);
        $settings = $this->pay()->getSettings();
        $this->data = array_merge($this->data, [
            'page_title' => 'Payroll Management',
            'advances' => $advances,
            'bonuses' => $bonuses,
            'entries' => $entries,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'settings' => $settings,
        ]);
        return $this->render('admin/features/payroll', $this->data);
    }

    public function resellProperties()
    {
        $this->requireAdmin();
        $properties = $this->resell()->listProperties([], 50);
        $commissionStructure = $this->resell()->getCommissionStructure();
        $this->data = array_merge($this->data, [
            'page_title' => 'Resell Properties',
            'properties' => $properties,
            'commissionStructure' => $commissionStructure,
        ]);
        return $this->render('admin/features/resell_properties', $this->data);
    }

    public function commissions()
    {
        $this->requireAdmin();
        $agentRates = $this->comm()->getRules();
        $hybridPlans = $this->db->query("SELECT * FROM hybrid_commission_plans ORDER BY created_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);
        $farmerStructures = $this->comm()->getFarmerStructures();
        $mlmRanks = $this->comm()->getMlmRankRates();
        $this->data = array_merge($this->data, [
            'page_title' => 'Commission Engine',
            'agentRates' => $agentRates,
            'hybridPlans' => $hybridPlans,
            'farmerStructures' => $farmerStructures,
            'mlmRanks' => $mlmRanks,
        ]);
        return $this->render('admin/features/commissions', $this->data);
    }

    public function notifications()
    {
        $this->requireAdmin();
        $templates = $this->notif()->listTemplates();
        $smsTemplates = $this->notif()->getSmsTemplates();
        $this->data = array_merge($this->data, [
            'page_title' => 'Notification Center',
            'templates' => $templates,
            'smsTemplates' => $smsTemplates,
        ]);
        return $this->render('admin/features/notifications', $this->data);
    }

    public function security()
    {
        $this->requireAdmin();
        $blocked = $this->sec()->listBlocked();
        $failed = $this->sec()->getFailedAttempts('', 24);
        $this->data = array_merge($this->data, [
            'page_title' => 'Security Center',
            'blocked' => $blocked,
            'failed' => $failed,
        ]);
        return $this->render('admin/features/security', $this->data);
    }

    public function finance()
    {
        $this->requireAdmin();
        $currentYear = (int)date('Y');
        $budgets = $this->fin()->listBudgets(0, $currentYear);
        $expenses = $this->fin()->listExpenses();
        $taxSlabs = $this->fin()->getTaxSlabs();
        $taxTypes = $this->fin()->getTaxTypes();
        $gstReturns = $this->fin()->listGstReturns();
        $summary = $this->fin()->financialSummary($currentYear);
        $this->data = array_merge($this->data, [
            'page_title' => 'Finance Management',
            'budgets' => $budgets,
            'expenses' => $expenses,
            'taxSlabs' => $taxSlabs,
            'taxTypes' => $taxTypes,
            'gstReturns' => $gstReturns,
            'summary' => $summary,
            'currentYear' => $currentYear,
        ]);
        return $this->render('admin/features/finance', $this->data);
    }

    public function analyticsDashboard()
    {
        $this->requireAdmin();
        $kpis = $this->analytics()->listKpis();
        $forecasts = $this->analytics()->listForecasts();
        $dashboards = $this->analytics()->listDashboards();
        $comprehensive = $this->analytics()->comprehensiveDashboard();
        $this->data = array_merge($this->data, [
            'page_title' => 'Analytics & KPIs',
            'kpis' => $kpis,
            'forecasts' => $forecasts,
            'dashboards' => $dashboards,
            'comprehensive' => $comprehensive,
        ]);
        return $this->render('admin/features/analytics', $this->data);
    }

    public function realtimeAnalytics()
    {
        $this->requireAdmin();
        $this->data = array_merge($this->data, [
            'page_title' => 'Real-Time Analytics',
            'page_heading' => 'Real-Time Analytics',
        ]);
        return $this->render('admin/features/realtime_analytics', $this->data);
    }

    public function agentTasks()
    {
        $this->requireAdmin();
        $tasks = $this->agent()->listTasks();
        $executions = $this->agent()->listExecutions();
        $workflows = $this->agent()->listWorkflows();
        $this->data = array_merge($this->data, [
            'page_title' => 'Agent Tasks & Workflows',
            'tasks' => $tasks,
            'executions' => $executions,
            'workflows' => $workflows,
        ]);
        return $this->render('admin/features/agent_tasks', $this->data);
    }

    public function ocrCenter()
    {
        $this->requireAdmin();
        $documents = $this->ocr()->listOcrDocuments(50);
        $classifications = $this->ocr()->listClassifications();
        $templates = $this->ocr()->listTemplates();
        $executions = $this->ocr()->listExecutions(0, 30);
        $this->data = array_merge($this->data, [
            'page_title' => 'OCR & Document Classification',
            'documents' => $documents,
            'classifications' => $classifications,
            'templates' => $templates,
            'executions' => $executions,
        ]);
        return $this->render('admin/features/ocr', $this->data);
    }

    public function propertyMaintenance()
    {
        $this->requireAdmin();
        $maintenance = $this->mkt()->listMaintenance();
        $marketData = $this->mkt()->getMarketAnalytics();
        $this->data = array_merge($this->data, [
            'page_title' => 'Property Maintenance',
            'maintenance' => $maintenance,
            'marketData' => $marketData,
        ]);
        return $this->render('admin/features/maintenance', $this->data);
    }
}
