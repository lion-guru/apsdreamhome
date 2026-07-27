<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use App\Services\Land\LegalColonyDevelopmentService;
use App\Services\Land\PipelineWorkflowService;
use App\Services\Land\ColonyAnalyticsService;
use App\Services\Land\ColonyFeasibilityService;
use App\Services\Land\LandAcquisitionService;
use App\Services\Land\ColonyHealthService;
use Exception;

/**
 * Legal Colony Pipeline Controller
 * 7-phase legal colony development pipeline admin interface.
 * Phases: Land Acquisition → Master Planning → Plot Cutting → RERA → Development → Pricing → Sales Ready
 */
class LegalColonyPipelineController extends AdminController
{
    /** @var LegalColonyDevelopmentService */
    private $service;

    /** @var PipelineWorkflowService */
    private $workflow;

    /** @var ColonyAnalyticsService */
    private $analytics;

    /** @var ColonyFeasibilityService */
    private $feasibility;

    /** @var LandAcquisitionService */
    private $landAcquisition;

    /** @var ColonyHealthService */
    private $health;

    /** @var Database */
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db             = Database::getInstance();
        $this->service        = new LegalColonyDevelopmentService();
        $this->workflow       = new PipelineWorkflowService();
        $this->analytics      = new ColonyAnalyticsService();
        $this->feasibility    = new ColonyFeasibilityService();
        $this->landAcquisition = new LandAcquisitionService();
        $this->health         = new ColonyHealthService();
    }

    // ── Pipeline Overview ──────────────────────────────────────

    /**
     * Pipeline dashboard — all colonies with their pipeline stage, progress, quick stats
     */
    public function index()
    {
        $this->requireAdmin();

        try {
            $filterStage = $_GET['stage'] ?? null;
            $result      = $this->service->getAllColoniesWithPipeline($filterStage);
            $colonies    = $result['colonies'] ?? [];

            // Pipeline stats
            $stats = $this->db->fetchOne("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN pipeline_stage = 'land_acquisition' THEN 1 ELSE 0 END) as land_acq,
                    SUM(CASE WHEN pipeline_stage = 'master_planning' THEN 1 ELSE 0 END) as planning,
                    SUM(CASE WHEN pipeline_stage = 'plot_cutting' THEN 1 ELSE 0 END) as plot_cut,
                    SUM(CASE WHEN pipeline_stage = 'rera_registration' THEN 1 ELSE 0 END) as rera,
                    SUM(CASE WHEN pipeline_stage = 'development' THEN 1 ELSE 0 END) as dev,
                    SUM(CASE WHEN pipeline_stage = 'pricing' THEN 1 ELSE 0 END) as pricing,
                    SUM(CASE WHEN pipeline_stage = 'sales_ready' THEN 1 ELSE 0 END) as sales_ready
                FROM colonies WHERE is_active = 1
            ") ?: [];
        } catch (Exception $e) {
            $colonies = [];
            $stats    = [];
            error_log('LegalPipeline index error: ' . $e->getMessage());
        }

        return $this->render('admin/legal-colony-pipeline/index', [
            'page_title'  => 'Legal Colony Development Pipeline',
            'colonies'    => $colonies,
            'stats'       => $stats,
            'filter_stage' => $filterStage,
            'stages'      => LegalColonyDevelopmentService::STAGE_LAND_ACQUISITION ? [
                'land_acquisition'  => 'Land Acquisition',
                'master_planning'   => 'Master Planning',
                'plot_cutting'      => 'Plot Cutting',
                'rera_registration' => 'RERA Registration',
                'development'       => 'Development',
                'pricing'           => 'Pricing',
                'sales_ready'       => 'Sales Ready',
            ] : [],
        ]);
    }

    // ── Colony Pipeline Detail ─────────────────────────────────

    /**
     * Show full pipeline status for a single colony
     */
    public function detail($colonyId)
    {
        $this->requireAdmin();
        $colonyId = intval($colonyId);

        try {
            $result  = $this->service->getPipelineStatus($colonyId);
            $colony  = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);

            if (!$result['success'] || !$colony) {
                $_SESSION['flash_error'] = $result['error'] ?? 'Colony not found';
                header('Location: /admin/legal-colony-pipeline');
                exit;
            }

            // Get acquisition info
            $acquisition = $this->db->fetchOne(
                "SELECT * FROM land_acquisitions WHERE colony_id = ? ORDER BY id DESC LIMIT 1",
                [$colonyId]
            );

            // Get layout info
            $layout = $this->db->fetchOne(
                "SELECT * FROM colony_layouts WHERE colony_id = ? AND is_current = 1",
                [$colonyId]
            );

            // Get RERA info
            $rera = null;
            if (!empty($colony['rera_number'])) {
                $rera = $this->db->fetchOne(
                    "SELECT * FROM rera_projects WHERE rera_number = ? LIMIT 1",
                    [$colony['rera_number']]
                );
            }

            // Get development costs summary
            $devCosts = $this->db->fetchOne(
                "SELECT COUNT(*) as count, COALESCE(SUM(amount),0) as total,
                        COALESCE(SUM(gst_amount),0) as total_gst,
                        COALESCE(SUM(tds_amount),0) as total_tds,
                        COALESCE(SUM(paid_amount),0) as total_paid
                 FROM colony_development_costs WHERE colony_id = ?",
                [$colonyId]
            );

            // Get milestones if RERA exists
            $milestones = [];
            if ($rera) {
                $milestones = $this->db->fetchAll(
                    "SELECT * FROM rera_milestones WHERE project_id = ? ORDER BY planned_date ASC",
                    [$rera['id']]
                ) ?: [];
            }

            // Get feasibility pricing (ColonyFeasibilityService)
            $feasibility = $this->feasibility->previewFeasibility($colonyId);

            // Get land acquisition leads for this colony
            $landLeads = $this->landAcquisition->listLeads([
                'district' => $colony['district'] ?? '',
            ]);

            // Get colony health score
            $health = $this->health->getColonyHealth($colonyId);
        } catch (Exception $e) {
            error_log('LegalPipeline detail error: ' . $e->getMessage());
            $result = ['success' => false, 'error' => $e->getMessage()];
            $colony = null;
            $acquisition = null;
            $layout = null;
            $rera = null;
            $devCosts = ['count' => 0, 'total' => 0, 'total_gst' => 0, 'total_tds' => 0, 'total_paid' => 0];
            $milestones = [];
            $feasibility = ['success' => false];
            $landLeads = ['success' => false, 'data' => [], 'count' => 0];
            $health = ['success' => false, 'overall_score' => 0, 'grade' => [], 'stages' => [], 'risks' => [], 'recommendations' => []];
        }

        return $this->render('admin/legal-colony-pipeline/detail', [
            'page_title'  => 'Colony Pipeline — ' . ($colony['name'] ?? ''),
            'colony'      => $colony,
            'pipeline'    => $result,
            'acquisition' => $acquisition,
            'layout'      => $layout,
            'rera'        => $rera,
            'dev_costs'   => $devCosts,
            'milestones'  => $milestones,
            'feasibility' => $feasibility,
            'land_leads'  => $landLeads,
            'health'      => $health,
        ]);
    }

    // ── Phase 1: Land Acquisition ──────────────────────────────

    /**
     * Form to start a new land acquisition
     */
    public function startAcquisition()
    {
        $this->requireAdmin();
        return $this->render('admin/legal-colony-pipeline/acquisition_form', [
            'page_title' => 'Start Land Acquisition',
        ]);
    }

    /**
     * Store a new land acquisition
     */
    public function storeAcquisition()
    {
        $this->requireAdmin();

        $result = $this->service->startLandAcquisition([
            'land_owner_name'  => trim($_POST['land_owner_name'] ?? ''),
            'colony_name'      => trim($_POST['colony_name'] ?? ''),
            'location'         => trim($_POST['location'] ?? ''),
            'total_area_acres' => floatval($_POST['total_area_acres'] ?? 0),
            'estimated_cost'   => floatval($_POST['estimated_cost'] ?? 0),
            'advance_paid'     => floatval($_POST['advance_paid'] ?? 0),
        ]);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Land acquisition started. Colony ID: {$result['colony_id']}";
            header("Location: /admin/legal-colony-pipeline/detail/{$result['colony_id']}");
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to start acquisition';
            header('Location: /admin/legal-colony-pipeline/start-acquisition');
        }
        exit;
    }

    /**
     * Update acquisition status (AJAX)
     */
    public function updateAcquisitionStatus()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $acqId  = intval($_POST['acquisition_id'] ?? 0);
        $status = trim($_POST['new_status'] ?? '');
        $details = [
            'registration_date'    => $_POST['registration_date'] ?? '',
            'registration_number'  => $_POST['registration_number'] ?? '',
            'sub_registrar_office' => $_POST['sub_registrar_office'] ?? '',
            'stamp_duty_amount'    => floatval($_POST['stamp_duty_amount'] ?? 0),
            'registration_fee'     => floatval($_POST['registration_fee'] ?? 0),
            'mutation_number'      => $_POST['mutation_number'] ?? '',
            'mutation_date'        => $_POST['mutation_date'] ?? '',
        ];

        echo json_encode($this->service->updateAcquisitionStatus($acqId, $status, $details));
        exit;
    }

    // ── Phase 2: Master Planning ───────────────────────────────

    /**
     * Form to create a master plan
     */
    public function createMasterPlan($colonyId)
    {
        $this->requireAdmin();
        $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [intval($colonyId)]);

        return $this->render('admin/legal-colony-pipeline/master_plan_form', [
            'page_title' => 'Create Master Plan — ' . ($colony['name'] ?? ''),
            'colony'     => $colony,
        ]);
    }

    /**
     * Store a master plan
     */
    public function storeMasterPlan()
    {
        $this->requireAdmin();
        $colonyId = intval($_POST['colony_id'] ?? 0);

        $plan = [
            'layout_name'     => trim($_POST['layout_name'] ?? 'Master Plan v1'),
            'layout_type'     => trim($_POST['layout_type'] ?? 'residential'),
            'total_area_acres' => floatval($_POST['total_area_acres'] ?? 0),
            'road_area_pct'   => floatval($_POST['road_area_pct'] ?? 15),
            'park_area_pct'   => floatval($_POST['park_area_pct'] ?? 10),
            'amenity_area_pct' => floatval($_POST['amenity_area_pct'] ?? 5),
            'min_road_width_ft' => floatval($_POST['min_road_width_ft'] ?? 30),
            'phases'          => json_decode($_POST['phases_json'] ?? '[]', true) ?: [],
            'blocks'          => json_decode($_POST['blocks_json'] ?? '[]', true) ?: [],
            'amenities'       => json_decode($_POST['amenities_json'] ?? '[]', true) ?: [],
            'notes'           => trim($_POST['notes'] ?? ''),
        ];

        $result = $this->service->createMasterPlan($colonyId, $plan);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Master plan created. Layout ID: {$result['layout_id']}";
            header("Location: /admin/legal-colony-pipeline/detail/{$colonyId}");
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to create master plan';
            header("Location: /admin/legal-colony-pipeline/master-plan/{$colonyId}");
        }
        exit;
    }

    // ── Phase 3: Plot Cutting ──────────────────────────────────

    /**
     * Form for legal plot cutting
     */
    public function plotCutting($colonyId)
    {
        $this->requireAdmin();
        $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [intval($colonyId)]);

        return $this->render('admin/legal-colony-pipeline/plot_cutting_form', [
            'page_title' => 'Legal Plot Cutting — ' . ($colony['name'] ?? ''),
            'colony'     => $colony,
        ]);
    }

    /**
     * Generate plots with legal compliance
     */
    public function generatePlots()
    {
        $this->requireAdmin();
        $colonyId = intval($_POST['colony_id'] ?? 0);

        $config = [
            'total_land_sqft'   => floatval($_POST['total_land_sqft'] ?? 0),
            'road_area_pct'     => floatval($_POST['road_area_pct'] ?? 15),
            'park_area_pct'     => floatval($_POST['park_area_pct'] ?? 10),
            'amenity_area_pct'  => floatval($_POST['amenity_area_pct'] ?? 5),
            'road_width_ft'     => floatval($_POST['road_width_ft'] ?? 30),
            'plot_width_ft'     => floatval($_POST['plot_width_ft'] ?? 30),
            'plot_length_ft'    => floatval($_POST['plot_length_ft'] ?? 40),
            'blocks'            => trim($_POST['blocks'] ?? 'A'),
            'plots_per_block'   => intval($_POST['plots_per_block'] ?? 20),
            'created_by'        => $_SESSION['admin_id'] ?? 1,
        ];

        $result = $this->service->generatePlotsLegal($colonyId, $config);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Plots generated legally. Count: {$result['count']}";
            header("Location: /admin/legal-colony-pipeline/detail/{$colonyId}");
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to generate plots';
            if (!empty($result['compliance'])) {
                $_SESSION['flash_compliance'] = $result['compliance'];
            }
            header("Location: /admin/legal-colony-pipeline/plot-cutting/{$colonyId}");
        }
        exit;
    }

    // ── Phase 4: RERA Registration ─────────────────────────────

    /**
     * Form for RERA registration
     */
    public function reraRegistration($colonyId)
    {
        $this->requireAdmin();
        $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [intval($colonyId)]);

        return $this->render('admin/legal-colony-pipeline/rera_form', [
            'page_title' => 'RERA Registration — ' . ($colony['name'] ?? ''),
            'colony'     => $colony,
        ]);
    }

    /**
     * Store RERA registration
     */
    public function storeRera()
    {
        $this->requireAdmin();
        $colonyId = intval($_POST['colony_id'] ?? 0);

        $result = $this->service->registerRERA($colonyId, [
            'rera_number'       => trim($_POST['rera_number'] ?? ''),
            'state_code'        => trim($_POST['state_code'] ?? 'UP'),
            'builder_name'      => trim($_POST['builder_name'] ?? ''),
            'builder_license'   => trim($_POST['builder_license'] ?? ''),
            'project_type'      => trim($_POST['project_type'] ?? 'Residential Plotted'),
            'registration_date' => $_POST['registration_date'] ?? date('Y-m-d'),
            'validity_date'     => $_POST['validity_date'] ?? date('Y-m-d', strtotime('+5 years')),
            'city'              => trim($_POST['city'] ?? 'Gorakhpur'),
            'district'          => trim($_POST['district'] ?? 'Gorakhpur'),
        ]);

        if ($result['success']) {
            $_SESSION['flash_success'] = "RERA registered: {$result['rera_number']}";
            header("Location: /admin/legal-colony-pipeline/detail/{$colonyId}");
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to register RERA';
            header("Location: /admin/legal-colony-pipeline/rera/{$colonyId}");
        }
        exit;
    }

    // ── Phase 5: Development Cost Tracking ──────────────────────

    /**
     * Form to record a development cost
     */
    public function recordCost($colonyId)
    {
        $this->requireAdmin();
        $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [intval($colonyId)]);

        // Get existing costs
        $costs = $this->db->fetchAll(
            "SELECT * FROM colony_development_costs WHERE colony_id = ? ORDER BY created_at DESC",
            [$colonyId]
        ) ?: [];

        return $this->render('admin/legal-colony-pipeline/development_form', [
            'page_title' => 'Development Costs — ' . ($colony['name'] ?? ''),
            'colony'     => $colony,
            'costs'      => $costs,
        ]);
    }

    /**
     * Store a development cost
     */
    public function storeCost()
    {
        $this->requireAdmin();
        $colonyId = intval($_POST['colony_id'] ?? 0);

        $result = $this->service->recordDevelopmentCost($colonyId, [
            'cost_type'        => trim($_POST['cost_type'] ?? ''),
            'amount'           => floatval($_POST['amount'] ?? 0),
            'gst_rate'         => floatval($_POST['gst_rate'] ?? 18),
            'vendor_name'      => trim($_POST['vendor_name'] ?? ''),
            'work_description' => trim($_POST['work_description'] ?? ''),
            'invoice_number'   => trim($_POST['invoice_number'] ?? ''),
            'invoice_date'     => $_POST['invoice_date'] ?? date('Y-m-d'),
            'tds_section'      => trim($_POST['tds_section'] ?? ''),
            'tds_rate'         => floatval($_POST['tds_rate'] ?? 0),
            'paid_amount'      => floatval($_POST['paid_amount'] ?? 0),
            'status'           => trim($_POST['status'] ?? 'planned'),
        ]);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Development cost recorded. Total with GST: ₹" . number_format($result['total_with_gst']);
            header("Location: /admin/legal-colony-pipeline/development/{$colonyId}");
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to record cost';
            header("Location: /admin/legal-colony-pipeline/development/{$colonyId}");
        }
        exit;
    }

    // ── Phase 6: Pricing ───────────────────────────────────────

    /**
     * Apply legal pricing to colony
     */
    public function applyPricing($colonyId)
    {
        $this->requireAdmin();
        $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [intval($colonyId)]);

        return $this->render('admin/legal-colony-pipeline/pricing_form', [
            'page_title' => 'Apply Pricing — ' . ($colony['name'] ?? ''),
            'colony'     => $colony,
        ]);
    }

    /**
     * Calculate and apply pricing
     */
    public function calculatePricing()
    {
        $this->requireAdmin();
        $colonyId = intval($_POST['colony_id'] ?? 0);

        $result = $this->service->calculateAndApplyPricing($colonyId, [
            'corner_premium_pct'      => floatval($_POST['corner_premium_pct'] ?? 10),
            'park_facing_premium_pct' => floatval($_POST['park_facing_premium_pct'] ?? 15),
            'wide_road_premium_pct'   => floatval($_POST['wide_road_premium_pct'] ?? 8),
            'wide_road_threshold'     => floatval($_POST['wide_road_threshold'] ?? 40),
            'override_price_per_sqft' => floatval($_POST['override_price_per_sqft'] ?? 0),
            'force_below_cost'        => !empty($_POST['force_below_cost']),
        ]);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Pricing applied. Base: ₹{$result['base_price']}/sqft";
            header("Location: /admin/legal-colony-pipeline/detail/{$colonyId}");
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Failed to apply pricing';
            header("Location: /admin/legal-colony-pipeline/pricing/{$colonyId}");
        }
        exit;
    }

    // ── Phase 7: Sales Readiness ───────────────────────────────

    /**
     * Show sales readiness checklist
     */
    public function readiness($colonyId)
    {
        $this->requireAdmin();
        $colonyId = intval($colonyId);

        try {
            $readiness = $this->service->getSalesReadinessChecklist($colonyId);
            $colony    = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
        } catch (Exception $e) {
            $readiness = ['success' => false, 'error' => $e->getMessage()];
            $colony    = null;
        }

        return $this->render('admin/legal-colony-pipeline/readiness', [
            'page_title' => 'Sales Readiness — ' . ($colony['name'] ?? ''),
            'colony'     => $colony,
            'readiness'  => $readiness,
        ]);
    }

    // ── Compliance Check (AJAX) ────────────────────────────────

    /**
     * Run compliance checks (AJAX)
     */
    public function complianceCheck()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $colonyId = intval($_POST['colony_id'] ?? 0);
        $config   = [
            'total_land_sqft'  => floatval($_POST['total_land_sqft'] ?? 0),
            'road_area_pct'    => floatval($_POST['road_area_pct'] ?? 15),
            'park_area_pct'    => floatval($_POST['park_area_pct'] ?? 10),
            'amenity_area_pct' => floatval($_POST['amenity_area_pct'] ?? 5),
            'road_width_ft'    => floatval($_POST['road_width_ft'] ?? 30),
        ];

        echo json_encode($this->service->runComplianceChecks($colonyId, $config));
        exit;
    }

    // ── Pipeline Workflow: Auto-Advance ────────────────────────

    /**
     * Check if colony can advance + advance it (POST)
     */
    public function advanceStage()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $colonyId = intval($_POST['colony_id'] ?? 0);
        $reason   = trim($_POST['reason'] ?? '');

        echo json_encode($this->workflow->advanceStage($colonyId, $reason));
        exit;
    }

    /**
     * Get stage readiness for a colony (AJAX)
     */
    public function stageReadiness()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $colonyId = intval($_POST['colony_id'] ?? 0);

        echo json_encode($this->workflow->checkAdvanceReadiness($colonyId));
        exit;
    }

    /**
     * Get stage transition history (AJAX)
     */
    public function stageHistory()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $colonyId = intval($_POST['colony_id'] ?? 0);

        echo json_encode($this->workflow->getStageHistory($colonyId));
        exit;
    }

    // ── Colony Analytics ───────────────────────────────────────

    /**
     * Colony-wise analytics dashboard
     */
    public function analytics($colonyId)
    {
        $this->requireAdmin();
        $colonyId = intval($colonyId);

        $data = $this->analytics->getColonyAnalytics($colonyId);

        if (!$data['success']) {
            $_SESSION['flash_error'] = $data['error'] ?? 'Analytics unavailable';
            header('Location: /admin/legal-colony-pipeline');
            exit;
        }

        return $this->render('admin/legal-colony-pipeline/analytics', [
            'page_title' => 'Analytics — ' . ($data['colony']['name'] ?? ''),
            'data'       => $data,
        ]);
    }

    /**
     * Cross-colony comparison (all colonies)
     */
    public function analyticsComparison()
    {
        $this->requireAdmin();

        $data = $this->analytics->getCrossColonyComparison();

        return $this->render('admin/legal-colony-pipeline/analytics_comparison', [
            'page_title' => 'Colony Analytics Comparison',
            'data'       => $data,
        ]);
    }

    // ── RERA Milestone Tracker ─────────────────────────────────

    /**
     * RERA milestone tracker for a colony
     */
    public function milestones($colonyId)
    {
        $this->requireAdmin();
        $colonyId = intval($colonyId);

        $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
        if (!$colony) {
            $_SESSION['flash_error'] = 'Colony not found';
            header('Location: /admin/legal-colony-pipeline');
            exit;
        }

        // Get RERA project
        $rera = null;
        if (!empty($colony['rera_number'])) {
            $rera = $this->db->fetchOne(
                "SELECT * FROM rera_projects WHERE rera_number = ? LIMIT 1",
                [$colony['rera_number']]
            );
        }

        // Get milestones
        $milestones = [];
        if ($rera) {
            $milestones = $this->db->fetchAll(
                "SELECT * FROM rera_milestones WHERE project_id = ? ORDER BY planned_date ASC",
                [$rera['id']]
            ) ?: [];
        }

        // Milestone stats
        $stats = [
            'total'      => count($milestones),
            'completed'  => 0,
            'in_progress'=> 0,
            'delayed'    => 0,
            'pending'    => 0,
        ];
        foreach ($milestones as $m) {
            $status = $m['status'] ?? 'pending';
            if (isset($stats[$status])) {
                $stats[$status]++;
            } else {
                $stats['pending']++;
            }
        }

        return $this->render('admin/legal-colony-pipeline/milestones', [
            'page_title' => 'RERA Milestones — ' . ($colony['name'] ?? ''),
            'colony'     => $colony,
            'rera'       => $rera,
            'milestones' => $milestones,
            'stats'      => $stats,
        ]);
    }

    /**
     * Update a RERA milestone (POST)
     */
    public function updateMilestone()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $milestoneId = intval($_POST['milestone_id'] ?? 0);
        $status      = trim($_POST['status'] ?? '');
        $notes       = trim($_POST['notes'] ?? '');

        $validStatuses = ['pending', 'in_progress', 'completed', 'delayed', 'on_hold'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
            exit;
        }

        try {
            $updateData = [
                'status' => $status,
            ];
            if ($status === 'completed') {
                $updateData['completion_date'] = date('Y-m-d');
            }
            if ($notes !== '') {
                $updateData['notes'] = $notes;
            }

            $this->db->execute(
                "UPDATE rera_milestones SET status = ?, actual_date = IF(? = 'completed', CURDATE(), actual_date), remarks = IF(? != '', ?, remarks) WHERE id = ?",
                [$status, $status, $notes, $notes, $milestoneId]
            );

            // Log activity
            $this->db->insert('user_activity_logs_unified', [
                'user_id'    => $_SESSION['admin_id'] ?? 0,
                'action'     => 'rera_milestone_updated',
                'context'    => json_encode(['milestone_id' => $milestoneId, 'status' => $status, 'notes' => $notes]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            echo json_encode(['success' => true, 'milestone_id' => $milestoneId, 'status' => $status]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // ── Colony Health Dashboard ──────────────────────────────────

    /**
     * Colony health overview — all colonies with scores, grades, risks
     */
    public function healthOverview()
    {
        $this->requireAdmin();

        $data = $this->health->getAllColoniesHealth();

        return $this->render('admin/legal-colony-pipeline/health_overview', [
            'page_title' => 'Colony Health Dashboard',
            'data'       => $data,
        ]);
    }

    /**
     * Health score API endpoint (JSON)
     */
    public function healthApi()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $colonyId = intval($_GET['colony_id'] ?? 0);

        if ($colonyId > 0) {
            echo json_encode($this->health->getColonyHealth($colonyId));
        } else {
            echo json_encode($this->health->getAllColoniesHealth());
        }
        exit;
    }
}
