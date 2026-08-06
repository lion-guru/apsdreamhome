<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\ColonyLandCostingService;

/**
 * ColonyLandCostingController
 * Routes:
 *   GET  /admin/colony-costing              — All colonies with costing status
 *   GET  /admin/colony-costing/create/{id}  — Costing input form for a colony
 *   POST /admin/colony-costing/store        — Save costing
 *   GET  /admin/colony-costing/{id}         — View costing breakdown report
 *   POST /admin/colony-costing/calculate    — AJAX live calculator
 *   POST /admin/colony-costing/approve/{id} — Approve costing & set final price
 */
class ColonyLandCostingController extends AdminController
{
    private ColonyLandCostingService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ColonyLandCostingService($this->db);
    }

    /* ── List all colonies ──────────────────────────────────────────── */
    public function index(): void
    {
        $this->requireAdmin();
        $colonies = $this->service->getAllColoniesWithCostingStatus();
        $this->render('admin.colony-costing.index', [
            'page_title' => 'Colony Land Costing & Plot Pricing',
            'colonies'   => $colonies,
        ]);
    }

    /* ── Costing form for a colony ────────────────────────────────── */
    public function create(int $colonyId): void
    {
        $this->requireAdmin();

        // Get existing costing for pre-fill
        $existing = $this->service->getColonyCosting($colonyId);

        try {
            $stmt = $this->db->prepare("SELECT id, name FROM colonies WHERE id = ? LIMIT 1");
            $stmt->execute([$colonyId]);
            $colony = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('[ColonyLandCostingController] create: ' . $e->getMessage());
            $colony = ['id' => $colonyId, 'name' => "Colony #{$colonyId}"];
        }

        $this->render('admin.colony-costing.create', [
            'page_title' => 'Land Costing: ' . ($colony['name'] ?? "Colony #{$colonyId}"),
            'colony'     => $colony,
            'existing'   => $existing,
        ]);
    }

    /* ── Save costing ─────────────────────────────────────────────── */
    public function store(): void
    {
        $this->requireAdmin();

        $colonyId   = (int)($_POST['colony_id'] ?? 0);
        $finalPrice = (float)($_POST['final_price_sqft'] ?? 0);
        $userId     = (int)($_SESSION['user_id'] ?? 0);

        if ($colonyId <= 0) {
            $this->setFlash('error', 'Colony ID is required.');
            $this->redirect('/admin/colony-costing');
            return;
        }

        $inputs = [
            'costing_label'            => strip_tags($_POST['costing_label'] ?? 'Initial Costing'),
            'total_land_sqft'          => (float)($_POST['total_land_sqft']           ?? 0),
            'land_purchase_rate'       => (float)($_POST['land_purchase_rate']        ?? 0),
            'land_registry_cost'       => (float)($_POST['land_registry_cost']        ?? 0),
            'road_wastage_pct'         => (float)($_POST['road_wastage_pct']          ?? 15),
            'drainage_wastage_pct'     => (float)($_POST['drainage_wastage_pct']      ?? 5),
            'park_wastage_pct'         => (float)($_POST['park_wastage_pct']          ?? 5),
            'other_wastage_pct'        => (float)($_POST['other_wastage_pct']         ?? 0),
            'road_dev_cost_sqft'       => (float)($_POST['road_dev_cost_sqft']        ?? 0),
            'drainage_dev_cost_sqft'   => (float)($_POST['drainage_dev_cost_sqft']    ?? 0),
            'electricity_cost_sqft'    => (float)($_POST['electricity_cost_sqft']     ?? 0),
            'water_pipeline_cost_sqft' => (float)($_POST['water_pipeline_cost_sqft']  ?? 0),
            'boundary_wall_cost_sqft'  => (float)($_POST['boundary_wall_cost_sqft']   ?? 0),
            'other_dev_cost_sqft'      => (float)($_POST['other_dev_cost_sqft']       ?? 0),
            'legal_approval_cost'      => (float)($_POST['legal_approval_cost']       ?? 0),
            'admin_overhead_pct'       => (float)($_POST['admin_overhead_pct']        ?? 5),
            'marketing_commission_pct' => (float)($_POST['marketing_commission_pct']  ?? 20),
            'target_profit_pct'        => (float)($_POST['target_profit_pct']         ?? 20),
        ];

        $result = $this->service->saveCosting($colonyId, $inputs, $finalPrice, $userId);

        if ($result['success']) {
            $this->setFlash('success', 'Land costing saved. Suggested price: ₹' . number_format($result['calc']['suggested_price_per_sqft'], 2) . '/SqFt');
            $this->redirect('/admin/colony-costing/' . $result['id']);
        } else {
            $this->setFlash('error', 'Save failed: ' . ($result['error'] ?? 'Unknown error'));
            $this->redirect('/admin/colony-costing/create/' . $colonyId);
        }
    }

    /* ── Detailed breakdown report ────────────────────────────────── */
    public function show(int $costingId): void
    {
        $this->requireAdmin();

        try {
            $stmt = $this->db->prepare("
                SELECT c.*, col.name AS colony_name
                FROM colony_land_costing c
                LEFT JOIN colonies col ON col.id = c.colony_id
                WHERE c.id = ? LIMIT 1
            ");
            $stmt->execute([$costingId]);
            $costing = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('[ColonyLandCostingController] show: ' . $e->getMessage());
            $costing = null;
        }

        if (!$costing) {
            $this->setFlash('error', 'Costing record not found.');
            $this->redirect('/admin/colony-costing');
            return;
        }

        $lineItems = $this->service->getCostingLineItems($costingId);
        $history   = $this->service->getCostingHistory((int)$costing['colony_id']);

        $this->render('admin.colony-costing.show', [
            'page_title' => 'Costing Report: ' . ($costing['colony_name'] ?? ''),
            'costing'    => $costing,
            'line_items' => $lineItems,
            'history'    => $history,
        ]);
    }

    /* ── AJAX Live Calculator ─────────────────────────────────────── */
    public function calculate(): void
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $inputs = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($inputs)) {
            $inputs = $_POST;
        }

        $result = $this->service->calculate($inputs);
        echo json_encode($result);
        exit;
    }

    /* ── Approve costing ──────────────────────────────────────────── */
    public function approve(int $costingId): void
    {
        $this->requireAdmin();

        $finalPrice  = (float)($_POST['final_price_sqft'] ?? 0);
        $adminUserId = (int)($_SESSION['user_id'] ?? 0);

        $ok = $this->service->approveCosting($costingId, $finalPrice, $adminUserId);

        if ($ok) {
            $this->setFlash('success', 'Costing approved. Final price: ₹' . number_format($finalPrice, 2) . '/SqFt');
        } else {
            $this->setFlash('error', 'Approval failed.');
        }
        $this->redirect('/admin/colony-costing/' . $costingId);
    }
}
