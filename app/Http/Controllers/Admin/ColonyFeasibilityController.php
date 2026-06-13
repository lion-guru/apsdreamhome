<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use App\Services\Land\ColonyFeasibilityService;
use Exception;

/**
 * Colony Feasibility Controller
 * Full pricing feasibility calculator with audit trail.
 *
 * Routes:
 *   GET  /admin/colony-feasibility                    — index (all colonies comparison)
 *   GET  /admin/colony-feasibility/{id}               — calculator for a colony
 *   POST /admin/colony-feasibility/{id}/calculate     — run calculation with overrides
 *   GET  /admin/colony-feasibility/{id}/history       — audit log for a colony
 *   GET  /admin/colony-feasibility/{id}/preview       — AJAX preview (JSON)
 */
class ColonyFeasibilityController extends AdminController
{
    /** @var ColonyFeasibilityService */
    private $feasibilityService;

    public function __construct()
    {
        parent::__construct();
        $this->feasibilityService = new ColonyFeasibilityService();
    }

    /**
     * Index — comparison table of all colonies with latest feasibility pricing.
     */
    public function index()
    {
        $this->requireAdmin();

        try {
            $result = $this->feasibilityService->getAllColoniesFeasibility();
            $colonies = $result['colonies'] ?? [];

            return $this->render('admin/colony-feasibility/index', [
                'page_title' => 'Colony Feasibility — Pricing Overview',
                'colonies'   => $colonies,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load feasibility data: ' . $e->getMessage());
            $this->redirect('/admin/colony-pipeline');
        }
    }

    /**
     * Calculator — shows full breakdown for a single colony with override inputs.
     */
    public function calculator($id)
    {
        $this->requireAdmin();

        try {
            $colony = $this->getColony($id);

            // Run default feasibility (no overrides)
            $feasibility = $this->feasibilityService->calculateFeasibility((int) $id);

            // Get latest cost breakdown from existing pricing service
            $pricingService = new \App\Services\Land\ColonyPricingService();
            $pricing = $pricingService->calculateColonyPricing((int) $id);

            // Get audit history (last 10)
            $history = $this->feasibilityService->getFeasibilityHistory((int) $id, 10);

            return $this->render('admin/colony-feasibility/calculator', [
                'page_title'  => 'Feasibility Calculator — ' . ($colony['name'] ?? ''),
                'colony'      => $colony,
                'feasibility' => $feasibility,
                'pricing'     => $pricing,
                'history'     => $history,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load calculator: ' . $e->getMessage());
            $this->redirect('/admin/colony-feasibility');
        }
    }

    /**
     * Calculate — run feasibility with custom overrides from form submission.
     */
    public function calculate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $colony = $this->getColony($id);

            $overrides = [
                'total_raw_area_sqft' => floatval($_POST['total_raw_area_sqft'] ?? 0) ?: null,
                'yield_pct'           => floatval($_POST['yield_pct'] ?? 60),
                'target_profit_pct'   => floatval($_POST['target_profit_pct'] ?? 20),
                'office_overhead_pct' => floatval($_POST['office_overhead_pct'] ?? 5),
                'mlm_budget_pct'      => floatval($_POST['mlm_budget_pct'] ?? 25),
                '_notes'              => $_POST['notes'] ?? null,
            ];

            // Remove null overrides (use defaults)
            $overrides = array_filter($overrides, fn($v) => $v !== null);

            $result = $this->feasibilityService->calculateFeasibility((int) $id, $overrides);

            if ($result['success']) {
                $this->setFlash('success', sprintf(
                    'Feasibility calculated: ₹%s/sqft (cost basis: ₹%s, markup: %sx). Total revenue projection: ₹%s',
                    number_format($result['recommended_price_ppsf'], 0),
                    number_format($result['raw_cost_per_sqft'], 0),
                    $result['markup_factor'],
                    number_format($result['total_revenue_projected'], 0)
                ));
            } else {
                $this->setFlash('error', $result['error'] ?? 'Calculation failed');
            }

            $this->redirect('/admin/colony-feasibility/' . $id);
        } catch (Exception $e) {
            $this->setFlash('error', 'Calculation error: ' . $e->getMessage());
            $this->redirect('/admin/colony-feasibility/' . $id);
        }
    }

    /**
     * History — full audit log for a colony.
     */
    public function history($id)
    {
        $this->requireAdmin();

        try {
            $colony = $this->getColony($id);
            $history = $this->feasibilityService->getFeasibilityHistory((int) $id, 50);

            return $this->render('admin/colony-feasibility/history', [
                'page_title' => 'Feasibility Audit Log — ' . ($colony['name'] ?? ''),
                'colony'     => $colony,
                'history'    => $history,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load history: ' . $e->getMessage());
            $this->redirect('/admin/colony-feasibility');
        }
    }

    /**
     * Preview — AJAX endpoint for live pricing preview (JSON).
     */
    public function preview($id)
    {
        $this->requireAdmin();

        header('Content-Type: application/json');

        try {
            $overrides = [
                'total_raw_area_sqft' => floatval($_GET['raw_area'] ?? 0) ?: null,
                'yield_pct'           => floatval($_GET['yield_pct'] ?? 60),
                'target_profit_pct'   => floatval($_GET['profit_pct'] ?? 20),
                'office_overhead_pct' => floatval($_GET['ga_pct'] ?? 5),
                'mlm_budget_pct'      => floatval($_GET['mlm_pct'] ?? 25),
            ];
            $overrides = array_filter($overrides, fn($v) => $v !== null);

            $result = $this->feasibilityService->previewFeasibility((int) $id, $overrides);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function getColony($id): array
    {
        $db = Database::getInstance();
        $colony = $db->fetchOne(
            "SELECT c.*, d.name as district_name
             FROM colonies c LEFT JOIN districts d ON c.district_id = d.id
             WHERE c.id = ?",
            [$id]
        );

        if (!$colony) {
            $this->setFlash('error', 'Colony not found');
            $this->redirect('/admin/colony-feasibility');
        }

        return $colony;
    }
}
