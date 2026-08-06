<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\SalariedAgentService;

/**
 * SalariedAgentController — HR / Admin panel for managing salaried sales agents.
 * Routes:
 *   GET  /admin/agents/salaried            — list all salaried agents
 *   GET  /admin/agents/salaried/create     — form to create salary structure
 *   POST /admin/agents/salaried/store      — save salary structure
 *   GET  /admin/agents/salaried/{id}       — view payroll summary for agent
 */
class SalariedAgentController extends AdminController
{
    private SalariedAgentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SalariedAgentService($this->db);
    }

    /* ── List all salaried agents ─────────────────────────────────── */
    public function index(): void
    {
        $this->requireAdmin();
        $agents = $this->service->getAllSalariedAgents();
        $this->render('admin.agents.salaried.index', [
            'page_title' => 'Salaried Agents & Salary Structures',
            'agents'     => $agents,
        ]);
    }

    /* ── Create salary structure form ────────────────────────────── */
    public function create(): void
    {
        $this->requireAdmin();

        // Fetch list of associates (all types) so HR can pick who to salary-ise
        try {
            $stmt = $this->db->prepare("
                SELECT a.user_id, u.name, u.email, a.agent_type
                FROM associates a
                JOIN users u ON u.id = a.user_id
                WHERE a.status = 'active'
                ORDER BY u.name ASC
            ");
            $stmt->execute();
            $allAssociates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('[SalariedAgentController] create: ' . $e->getMessage());
            $allAssociates = [];
        }

        $this->render('admin.agents.salaried.create', [
            'page_title'    => 'Set Salary Structure',
            'all_associates' => $allAssociates,
        ]);
    }

    /* ── Save salary structure ───────────────────────────────────── */
    public function store(): void
    {
        $this->requireAdmin();

        $userId        = (int)($_POST['user_id']         ?? 0);
        $effectiveFrom = $_POST['effective_from']         ?? date('Y-m-d');

        if ($userId <= 0) {
            $this->setFlash('error', 'Please select an agent.');
            $this->redirect('/admin/agents/salaried/create');
            return;
        }

        $data = [
            'basic_salary'    => (float)($_POST['basic_salary']    ?? 0),
            'hra'             => (float)($_POST['hra']             ?? 0),
            'ta_da'           => (float)($_POST['ta_da']           ?? 0),
            'other_allowance' => (float)($_POST['other_allowance'] ?? 0),
            'incentive_type'  => in_array($_POST['incentive_type'] ?? '', ['percentage', 'flat_per_plot'])
                                    ? $_POST['incentive_type'] : 'flat_per_plot',
            'incentive_value' => (float)($_POST['incentive_value'] ?? 0),
            'tds_applicable'  => (int)($_POST['tds_applicable']    ?? 1),
            'effective_from'  => $effectiveFrom,
            'remarks'         => strip_tags($_POST['remarks'] ?? ''),
        ];

        $adminUserId = (int)($_SESSION['user_id'] ?? 0);
        $result      = $this->service->createSalaryStructure($userId, $data, $adminUserId);

        if ($result['success']) {
            $this->setFlash('success', 'Salary structure saved successfully.');
            $this->redirect('/admin/agents/salaried');
        } else {
            $this->setFlash('error', 'Failed to save: ' . ($result['error'] ?? 'Unknown error'));
            $this->redirect('/admin/agents/salaried/create');
        }
    }

    /* ── View agent payroll summary ──────────────────────────────── */
    public function show(int $userId): void
    {
        $this->requireAdmin();

        $structure = $this->service->getSalaryStructure($userId);
        $history   = $this->service->getSalaryHistory($userId);

        // Payroll for current month
        $payroll = $this->service->calculateMonthlyPayroll($userId, (int)date('n'), (int)date('Y'));

        try {
            $stmt = $this->db->prepare("
                SELECT u.name, u.email, a.agent_type, a.is_salary_active
                FROM users u
                JOIN associates a ON a.user_id = u.id
                WHERE u.id = ?
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $agent = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('[SalariedAgentController] show: ' . $e->getMessage());
            $agent = [];
        }

        $this->render('admin.agents.salaried.show', [
            'page_title' => 'Agent Payroll: ' . ($agent['name'] ?? "User #{$userId}"),
            'agent'      => $agent,
            'structure'  => $structure,
            'history'    => $history,
            'payroll'    => $payroll,
            'userId'     => $userId,
        ]);
    }
}
