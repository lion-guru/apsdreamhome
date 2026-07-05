<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use App\Services\Land\PlotCutterService;
use Exception;

/**
 * Colony Pipeline Controller
 * Master controller tying the full colony development pipeline together:
 * Land Acquisition → Colony Creation → Layout → Plot Generation → Pricing → Sales Ready
 */
class ColonyPipelineController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Dashboard — all colonies with plot stats, total value, development status
     */
    public function dashboard()
    {
        $this->requireAdmin();

        try {
            $colonies = $this->db->fetchAll(
                "SELECT c.*,
                        d.name as district_name,
                        COUNT(p.id) as total_plots,
                        SUM(CASE WHEN p.status = 'available' THEN 1 ELSE 0 END) as available_plots,
                        SUM(CASE WHEN p.status = 'booked' THEN 1 ELSE 0 END) as booked_plots,
                        SUM(CASE WHEN p.status = 'sold' THEN 1 ELSE 0 END) as sold_plots,
                        SUM(p.total_price) as total_value,
                        (SELECT COUNT(*) FROM colony_layouts cl WHERE cl.colony_id = c.id AND cl.is_current = 1) as has_layout,
                        (SELECT COALESCE(SUM(cdc.amount), 0) FROM colony_development_costs cdc WHERE cdc.colony_id = c.id) as total_dev_cost
                 FROM colonies c
                 LEFT JOIN districts d ON c.district_id = d.id
                 LEFT JOIN plots p ON p.colony_id = c.id
                 GROUP BY c.id
                 ORDER BY c.name ASC"
            );

            $stats = $this->db->selectOne(
                "SELECT COUNT(*) as total_colonies,
                        SUM(total_plots) as total_plots,
                        SUM(available_plots) as total_available
                 FROM colonies WHERE is_active = 1"
            );
        } catch (Exception $e) {
            $colonies = [];
            $stats = ['total_colonies' => 0, 'total_plots' => 0, 'total_available' => 0];
            error_log('ColonyPipeline dashboard error: ' . $e->getMessage());
        }

        return $this->render('admin/colony-pipeline/dashboard', [
            'page_title' => 'Colony Development Pipeline',
            'colonies' => $colonies,
            'stats' => $stats
        ]);
    }

    /**
     * Colony detail — overview with plot stats, dev costs, layout status, financial summary
     */
    public function colonyDetail($id)
    {
        $this->requireAdmin();

        try {
            $colony = $this->db->fetchOne(
                "SELECT c.*, d.name as district_name, s.name as state_name
                 FROM colonies c
                 LEFT JOIN districts d ON c.district_id = d.id
                 LEFT JOIN states s ON d.state_id = s.id
                 WHERE c.id = ?",
                [$id]
            );

            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $plotStats = $this->db->fetchOne(
                "SELECT COUNT(*) as total,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                        SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
                        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                        SUM(CASE WHEN status = 'hold' OR status = 'reserved' THEN 1 ELSE 0 END) as hold,
                        SUM(total_price) as total_value,
                        AVG(area_sqft) as avg_area
                 FROM plots WHERE colony_id = ?",
                [$id]
            );

            $devCost = $this->db->selectOne(
                "SELECT SUM(amount) as total_cost,
                        SUM(gst_amount) as total_gst,
                        SUM(paid_amount) as total_paid,
                        SUM(balance_amount) as total_balance
                 FROM colony_development_costs WHERE colony_id = ?",
                [$id]
            );

            $layout = $this->db->fetchOne(
                "SELECT * FROM colony_layouts WHERE colony_id = ? AND is_current = 1 ORDER BY id DESC LIMIT 1",
                [$id]
            );

            $blocks = $this->db->fetchAll(
                "SELECT block, COUNT(*) as plot_count,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available
                 FROM plots WHERE colony_id = ? AND block IS NOT NULL
                 GROUP BY block ORDER BY block",
                [$id]
            );
        } catch (Exception $e) {
            $colony = [];
            $plotStats = ['total' => 0, 'available' => 0, 'booked' => 0, 'sold' => 0, 'hold' => 0, 'total_value' => 0, 'avg_area' => 0];
            $devCost = ['total_cost' => 0, 'total_gst' => 0, 'total_paid' => 0, 'total_balance' => 0];
            $layout = null;
            $blocks = [];
            error_log('ColonyPipeline colonyDetail error: ' . $e->getMessage());
        }

        return $this->render('admin/colony-pipeline/detail', [
            'page_title' => $colony['name'] ?? 'Colony Detail',
            'colony' => $colony,
            'plot_stats' => $plotStats,
            'dev_cost' => $devCost,
            'layout' => $layout,
            'blocks' => $blocks
        ]);
    }

    /**
     * Layout form — plot cutting configuration
     */
    public function layoutForm($id)
    {
        $this->requireAdmin();

        try {
            $colony = $this->db->fetchOne(
                "SELECT c.*, d.name as district_name
                 FROM colonies c
                 LEFT JOIN districts d ON c.district_id = d.id
                 WHERE c.id = ?",
                [$id]
            );

            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $existingPlots = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM plots WHERE colony_id = ?",
                [$id]
            );

            $currentLayout = $this->db->fetchOne(
                "SELECT * FROM colony_layouts WHERE colony_id = ? AND is_current = 1 ORDER BY id DESC LIMIT 1",
                [$id]
            );

            $totalAreaSqft = $existingPlots['count'] > 0
                ? $this->db->selectOne("SELECT SUM(area_sqft) as total FROM plots WHERE colony_id = ?", [$id])
                : null;
        } catch (Exception $e) {
            $colony = [];
            $existingPlots = ['count' => 0];
            $currentLayout = null;
            $totalAreaSqft = null;
            error_log('ColonyPipeline layoutForm error: ' . $e->getMessage());
        }

        return $this->render('admin/colony-pipeline/layout-form', [
            'page_title' => 'Layout Configuration — ' . ($colony['name'] ?? ''),
            'colony' => $colony,
            'existing_plots' => $existingPlots,
            'current_layout' => $currentLayout,
            'total_area_sqft' => $totalAreaSqft
        ]);
    }

    /**
     * Generate plots — runs the plot cutting algorithm
     */
    public function generatePlots($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$id]);
            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $plotCount = $this->db->selectOne("SELECT COUNT(*) as c FROM plots WHERE colony_id = ?", [$id]);
            if (($plotCount['c'] ?? 0) > 0) {
                $this->setFlash('error', 'Plots already exist for this colony. Delete existing layout first.');
                $this->redirect('/admin/colony-pipeline/' . $id . '/layout');
            }

            $blockName = trim($_POST['block_name'] ?? 'A');
            $roadWidth = floatval($_POST['road_width'] ?? 30);
            $parkPct = floatval($_POST['park_pct'] ?? 10);
            $amenityPct = floatval($_POST['amenity_pct'] ?? 5);
            $plotSizesRaw = $_POST['plot_sizes'] ?? [];
            $plotSizes = is_array($plotSizesRaw) ? $plotSizesRaw : array_filter(explode(',', $plotSizesRaw));

            if (empty($plotSizes)) {
                $plotSizes = [1200, 1500, 1800, 2000];
            }

            $service = new PlotCutterService();

            $config = [
                'colony_id' => $id,
                'total_land_sqft' => floatval($_POST['total_land_sqft'] ?? 0),
                'land_width_ft' => floatval($_POST['land_width_ft'] ?? 0),
                'land_length_ft' => floatval($_POST['land_length_ft'] ?? 0),
                'block_name' => $blockName,
                'road_width_ft' => $roadWidth,
                'park_area_pct' => $parkPct,
                'amenity_area_pct' => $amenityPct,
                'plot_sizes' => [],
                'created_by' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1,
            ];

            foreach ($plotSizes as $size) {
                $area = floatval($size);
                if ($area <= 0) continue;
                $w = round(sqrt($area * 0.75), 2);
                $l = round($area / max($w, 1), 2);
                $config['plot_sizes'][] = ['width' => $w, 'length' => $l, 'area' => $area];
            }

            $result = $service->generatePlots($config);
            if ($result['success']) {
                $persistResult = $service->persistPlots($id, $result['plots'], $config['created_by']);
                $generated = $persistResult['count'] ?? count($result['plots']);
            } else {
                throw new Exception($result['message'] ?? 'Plot generation failed');
            }

            $this->db->query(
                "UPDATE colonies SET total_plots = (SELECT COUNT(*) FROM plots WHERE colony_id = ?),
                        available_plots = (SELECT COUNT(*) FROM plots WHERE colony_id = ? AND status = 'available')
                 WHERE id = ?",
                [$id, $id, $id]
            );

            $this->setFlash('success', $generated . ' plots generated successfully in block ' . $blockName);
            $this->redirect('/admin/colony-pipeline/' . $id . '/layout');
        } catch (Exception $e) {
            error_log('ColonyPipeline generatePlots error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to generate plots: ' . $e->getMessage());
            $this->redirect('/admin/colony-pipeline/' . $id . '/layout');
        }
    }

    /**
     * Preview plots — AJAX handler, returns JSON
     */
    public function previewPlots($id)
    {
        $this->requireAdmin();

        header('Content-Type: application/json');

        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$id]);
            if (!$colony) {
                echo json_encode(['success' => false, 'error' => 'Colony not found']);
                exit;
            }

            $blockName = trim($_POST['block_name'] ?? 'A');
            $roadWidth = floatval($_POST['road_width'] ?? 30);
            $parkPct = floatval($_POST['park_pct'] ?? 10);
            $amenityPct = floatval($_POST['amenity_pct'] ?? 5);
            $plotSizesRaw = $_POST['plot_sizes'] ?? [];
            $plotSizes = is_array($plotSizesRaw) ? $plotSizesRaw : array_filter(explode(',', $plotSizesRaw));

            if (empty($plotSizes)) {
                $plotSizes = [1200, 1500, 1800, 2000];
            }

            $service = new PlotCutterService();
            $config = [
                'colony_id' => $id,
                'total_land_sqft' => floatval($_POST['total_land_sqft'] ?? 0),
                'land_width_ft' => floatval($_POST['land_width_ft'] ?? 0),
                'land_length_ft' => floatval($_POST['land_length_ft'] ?? 0),
                'block_name' => $blockName,
                'road_width_ft' => $roadWidth,
                'park_area_pct' => $parkPct,
                'amenity_area_pct' => $amenityPct,
                'plot_sizes' => [],
                'created_by' => 1,
            ];
            foreach ($plotSizes as $size) {
                $area = floatval($size);
                if ($area <= 0) continue;
                $w = round(sqrt($area * 0.75), 2);
                $l = round($area / max($w, 1), 2);
                $config['plot_sizes'][] = ['width' => $w, 'length' => $l, 'area' => $area];
            }
            $result = $service->getPlotPreview($config);
            echo json_encode($result);
        } catch (Exception $e) {
            error_log('ColonyPipeline previewPlots error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Preview failed']);
        }
        exit;
    }

    /**
     * Delete plots — removes all plots for a colony
     */
    public function deletePlots($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$id]);
            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $bookedCount = $this->db->selectOne(
                "SELECT COUNT(*) as c FROM plots WHERE colony_id = ? AND status NOT IN ('available', 'hold')",
                [$id]
            );

            if (($bookedCount['c'] ?? 0) > 0) {
                $this->setFlash('error', 'Cannot delete: ' . $bookedCount['c'] . ' plots are booked/sold.');
                $this->redirect('/admin/colony-pipeline/' . $id . '/layout');
            }

            $this->db->query("DELETE FROM plots WHERE colony_id = ?", [$id]);

            $this->db->query(
                "UPDATE colonies SET total_plots = 0, available_plots = 0 WHERE id = ?",
                [$id]
            );

            $this->setFlash('success', 'All available plots deleted for ' . ($colony['name'] ?? 'colony'));
            $this->redirect('/admin/colony-pipeline/' . $id . '/layout');
        } catch (Exception $e) {
            error_log('ColonyPipeline deletePlots error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to delete plots: ' . $e->getMessage());
            $this->redirect('/admin/colony-pipeline/' . $id . '/layout');
        }
    }

    /**
     * Save layout — persists layout config to colony_layouts
     */
    public function saveLayout($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$id]);
            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $plotMap = $this->db->fetchAll("SELECT id, plot_number, block, area_sqft, width_ft, length_ft, status FROM plots WHERE colony_id = ?", [$id]);

            $this->db->query(
                "UPDATE colony_layouts SET is_current = 0 WHERE colony_id = ?",
                [$id]
            );

            $this->db->query(
                "INSERT INTO colony_layouts (
                    colony_id, layout_name, version, layout_type,
                    road_area_pct, common_area_pct, is_current,
                    plot_map_json, notes, total_plots, total_area_sqft,
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, 'draft', NOW())",
                [
                    $id,
                    trim($_POST['layout_name'] ?? 'Layout v1'),
                    trim($_POST['version'] ?? '1.0'),
                    trim($_POST['layout_type'] ?? 'residential'),
                    floatval($_POST['road_area_pct'] ?? 15),
                    floatval($_POST['common_area_pct'] ?? 8),
                    json_encode($plotMap),
                    trim($_POST['notes'] ?? ''),
                    count($plotMap),
                    array_sum(array_column($plotMap, 'area_sqft'))
                ]
            );

            $this->setFlash('success', 'Layout saved successfully');
            $this->redirect('/admin/colony-pipeline/' . $id);
        } catch (Exception $e) {
            error_log('ColonyPipeline saveLayout error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to save layout: ' . $e->getMessage());
            $this->redirect('/admin/colony-pipeline/' . $id . '/layout');
        }
    }

    /**
     * Pricing dashboard — land cost, dev costs breakdown, current plot prices, approvals
     */
    public function pricingDashboard($id)
    {
        $this->requireAdmin();

        try {
            $colony = $this->db->fetchOne(
                "SELECT c.*, d.name as district_name
                 FROM colonies c LEFT JOIN districts d ON c.district_id = d.id
                 WHERE c.id = ?",
                [$id]
            );

            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $plotStats = $this->db->fetchOne(
                "SELECT COUNT(*) as total,
                        AVG(price_per_sqft) as avg_ppsf,
                        MIN(total_price) as min_price,
                        MAX(total_price) as max_price,
                        SUM(total_price) as total_value,
                        AVG(area_sqft) as avg_area
                 FROM plots WHERE colony_id = ?",
                [$id]
            );

            $devCosts = $this->db->fetchAll(
                "SELECT cost_type, SUM(amount) as total_amount, SUM(gst_amount) as total_gst
                 FROM colony_development_costs WHERE colony_id = ?
                 GROUP BY cost_type ORDER BY total_amount DESC",
                [$id]
            );

            $totalDevCost = $this->db->selectOne(
                "SELECT COALESCE(SUM(amount + gst_amount), 0) as total
                 FROM colony_development_costs WHERE colony_id = ?",
                [$id]
            );

            $priceBands = $this->db->fetchAll(
                "SELECT
                    CASE
                        WHEN total_price < 1000000 THEN 'Under 10L'
                        WHEN total_price < 2000000 THEN '10L - 20L'
                        WHEN total_price < 3000000 THEN '20L - 30L'
                        WHEN total_price < 5000000 THEN '30L - 50L'
                        ELSE 'Above 50L'
                    END as price_band,
                    COUNT(*) as plot_count
                 FROM plots WHERE colony_id = ?
                 GROUP BY price_band ORDER BY MIN(total_price)",
                [$id]
            );

            // Blocks list for block-premium
            $blockList = $this->db->fetchAll(
                "SELECT DISTINCT block FROM plots WHERE colony_id = ? AND block IS NOT NULL AND block != '' ORDER BY block",
                [$id]
            );

            // Phases list for phase-premium
            $phaseList = $this->db->fetchAll(
                "SELECT DISTINCT phase FROM plots WHERE colony_id = ? AND phase IS NOT NULL AND phase != '' ORDER BY phase",
                [$id]
            );

            // Pending approvals for this colony
            $pendingApprovals = $this->db->fetchAll(
                "SELECT pa.*, p.plot_number, p.block, p.area_sqft,
                        u1.name AS requested_by_name
                 FROM pricing_approvals pa
                 LEFT JOIN plots p ON pa.plot_id = p.id
                 LEFT JOIN users u1 ON pa.requested_by = u1.id
                 WHERE pa.colony_id = ? AND pa.status = 'pending'
                 ORDER BY pa.requested_at DESC LIMIT 50",
                [$id]
            );

            // All approvals history
            $approvalHistory = $this->db->fetchAll(
                "SELECT pa.*, p.plot_number, p.block, p.area_sqft,
                        u1.name AS requested_by_name,
                        u2.name AS approved_by_name
                 FROM pricing_approvals pa
                 LEFT JOIN plots p ON pa.plot_id = p.id
                 LEFT JOIN users u1 ON pa.requested_by = u1.id
                 LEFT JOIN users u2 ON pa.approved_by = u2.id
                 WHERE pa.colony_id = ?
                 ORDER BY pa.requested_at DESC LIMIT 50",
                [$id]
            );

            // Plots that could have price overrides
            $recentlyOverridden = $this->db->fetchAll(
                "SELECT id, plot_number, block, area_sqft, price_per_sqft, total_price,
                        negotiated_price, price_override_reason, price_overridden_at,
                        negotiated_price_approved
                 FROM plots WHERE colony_id = ? AND price_override_reason IS NOT NULL AND price_override_reason != ''
                 ORDER BY price_overridden_at DESC LIMIT 20",
                [$id]
            );
        } catch (Exception $e) {
            $colony = [];
            $plotStats = ['total' => 0, 'avg_ppsf' => 0, 'min_price' => 0, 'max_price' => 0, 'total_value' => 0, 'avg_area' => 0];
            $devCosts = [];
            $totalDevCost = ['total' => 0];
            $priceBands = [];
            $blockList = [];
            $phaseList = [];
            $pendingApprovals = [];
            $approvalHistory = [];
            $recentlyOverridden = [];
            error_log('ColonyPipeline pricingDashboard error: ' . $e->getMessage());
        }

        return $this->render('admin/colony-pipeline/pricing', [
            'page_title' => 'Pricing — ' . ($colony['name'] ?? ''),
            'colony' => $colony,
            'plot_stats' => $plotStats,
            'dev_costs' => $devCosts,
            'total_dev_cost' => $totalDevCost['total'] ?? 0,
            'price_bands' => $priceBands,
            'block_list' => $blockList,
            'phase_list' => $phaseList,
            'pending_approvals' => $pendingApprovals,
            'approval_history' => $approvalHistory,
            'recently_overridden' => $recentlyOverridden,
        ]);
    }

    /**
     * Calculate pricing — calls ColonyPricingService
     */
    public function calculatePricing($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$id]);
            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $service = new \App\Services\Land\ColonyPricingService();
            $result = $service->calculateColonyPricing($id);

            if ($result['success']) {
                $this->setFlash('success', 'Pricing calculated. Recommended base: ₹' . number_format($result['base_price_per_sqft'] ?? 0) . '/sqft (Cost basis: ₹' . number_format($result['raw_cost_basis'] ?? 0) . ', Markup: ' . round(($result['markup_factor'] ?? 1) * 100) . '%)');
            } else {
                $this->setFlash('error', $result['message'] ?? 'Pricing calculation failed');
            }

            $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
        } catch (Exception $e) {
            error_log('ColonyPipeline calculatePricing error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to calculate pricing: ' . $e->getMessage());
            $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
        }
    }

    /**
     * Apply pricing — sets base_price_per_sqft on all available plots
     * Also handles sub-actions: update_single_plot, request_discount, approve_discount, reject_discount
     */
    public function applyPricing($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $subAction = $_POST['sub_action'] ?? 'apply_all';

        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$id]);
            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $pricingService = new \App\Services\Land\ColonyPricingService();

            // ── Sub-action: Update single plot price ─────────────
            if ($subAction === 'update_single_plot') {
                $plotId = (int)($_POST['plot_id'] ?? 0);
                $newPpsf = floatval($_POST['new_price_per_sqft'] ?? 0);
                $reason = trim($_POST['price_reason'] ?? '');
                if ($plotId <= 0 || $newPpsf <= 0) {
                    $this->setFlash('error', 'Plot ID and valid price are required');
                    $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
                }
                $result = $pricingService->updatePlotPrice($plotId, $newPpsf, $reason);
                if ($result['success']) {
                    $this->setFlash('success', 'Plot #' . $plotId . ' price updated to ₹' . number_format($newPpsf) . '/sqft');
                } else {
                    $this->setFlash('error', $result['error'] ?? 'Update failed');
                }
                $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
            }

            // ── Sub-action: Request discount ────────────────────
            if ($subAction === 'request_discount') {
                $plotId = (int)($_POST['plot_id'] ?? 0);
                $requestedPpsf = floatval($_POST['requested_price_per_sqft'] ?? 0);
                $reason = trim($_POST['discount_reason'] ?? '');
                if ($plotId <= 0 || $requestedPpsf <= 0) {
                    $this->setFlash('error', 'Plot ID and requested price are required');
                    $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
                }
                $result = $pricingService->requestDiscount($plotId, $requestedPpsf, $reason);
                if ($result['success']) {
                    $this->setFlash('success', $result['message'] ?? 'Discount request submitted');
                } else {
                    $this->setFlash('error', $result['error'] ?? 'Discount request failed');
                }
                $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
            }

            // ── Sub-action: Approve discount ────────────────────
            if ($subAction === 'approve_discount') {
                $approvalId = (int)($_POST['approval_id'] ?? 0);
                $notes = trim($_POST['approval_notes'] ?? '');
                if ($approvalId <= 0) {
                    $this->setFlash('error', 'Invalid approval ID');
                    $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
                }
                $result = $pricingService->approveDiscount($approvalId, $notes);
                if ($result['success']) {
                    $this->setFlash('success', $result['message'] ?? 'Discount approved');
                } else {
                    $this->setFlash('error', $result['error'] ?? 'Approval failed');
                }
                $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
            }

            // ── Sub-action: Reject discount ─────────────────────
            if ($subAction === 'reject_discount') {
                $approvalId = (int)($_POST['approval_id'] ?? 0);
                $notes = trim($_POST['approval_notes'] ?? '');
                if ($approvalId <= 0) {
                    $this->setFlash('error', 'Invalid approval ID');
                    $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
                }
                $result = $pricingService->rejectDiscount($approvalId, $notes);
                if ($result['success']) {
                    $this->setFlash('success', $result['message'] ?? 'Discount rejected');
                } else {
                    $this->setFlash('error', $result['error'] ?? 'Rejection failed');
                }
                $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
            }

            // ── Default: Apply pricing to ALL plots ─────────────
            $basePpsf = floatval($_POST['base_price_per_sqft'] ?? 0);
            if ($basePpsf <= 0) {
                $this->setFlash('error', 'Base price per sqft must be greater than 0');
                $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
            }

            $cornerPremium = floatval($_POST['corner_premium_pct'] ?? 10) / 100;
            $parkFacingPremium = floatval($_POST['park_facing_premium_pct'] ?? 5) / 100;
            $wideRoadPremium = floatval($_POST['wide_road_premium_pct'] ?? 8) / 100;
            $wideRoadThreshold = floatval($_POST['wide_road_threshold'] ?? 40);

            // Per-block premiums
            $blockPremiums = [];
            foreach ($_POST as $key => $val) {
                if (strpos($key, 'block_premium_') === 0 && !empty($val)) {
                    $blockName = substr($key, strlen('block_premium_'));
                    $blockPremiums[$blockName] = floatval($val);
                }
            }

            // Per-phase premiums
            $phasePremiums = [];
            foreach ($_POST as $key => $val) {
                if (strpos($key, 'phase_premium_') === 0 && !empty($val)) {
                    $phaseName = substr($key, strlen('phase_premium_'));
                    $phasePremiums[$phaseName] = floatval($val);
                }
            }

            $result = $pricingService->applyPricingToColony($id, $basePpsf, [
                'corner_plot'          => $cornerPremium,
                'park_facing'          => $parkFacingPremium,
                'road_width_ft'        => $wideRoadPremium,
                'wide_road_threshold'  => $wideRoadThreshold,
                'block'                => $blockPremiums,
                'phase'                => $phasePremiums,
            ]);

            if ($result['success']) {
                $this->setFlash('success', $result['plots_updated'] . ' plots priced at ₹' . number_format($basePpsf) . '/sqft base. Total inventory value: ₹' . number_format($result['total_value'] ?? 0, 2));
            } else {
                $this->setFlash('error', $result['error'] ?? 'Failed to apply pricing');
            }

            $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
        } catch (Exception $e) {
            error_log('ColonyPipeline applyPricing error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to apply pricing: ' . $e->getMessage());
            $this->redirect('/admin/colony-pipeline/' . $id . '/pricing');
        }
    }

    /**
     * Development costs — lists costs + add cost form
     */
    public function developmentCosts($id)
    {
        $this->requireAdmin();

        try {
            $colony = $this->db->fetchOne(
                "SELECT c.*, d.name as district_name
                 FROM colonies c LEFT JOIN districts d ON c.district_id = d.id
                 WHERE c.id = ?",
                [$id]
            );

            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $costs = $this->db->fetchAll(
                "SELECT cdc.*, v.name as vendor_name_lookup
                 FROM colony_development_costs cdc
                 LEFT JOIN vendors v ON cdc.vendor_id = v.id
                 WHERE cdc.colony_id = ?
                 ORDER BY cdc.cost_type ASC, cdc.created_at DESC",
                [$id]
            );

            $summary = $this->db->selectOne(
                "SELECT
                    SUM(amount) as total_amount,
                    SUM(gst_amount) as total_gst,
                    SUM(paid_amount) as total_paid,
                    SUM(balance_amount) as total_balance,
                    COUNT(*) as cost_count
                 FROM colony_development_costs WHERE colony_id = ?",
                [$id]
            );

            $byType = $this->db->fetchAll(
                "SELECT cost_type, COUNT(*) as cnt, SUM(amount) as amt
                 FROM colony_development_costs WHERE colony_id = ?
                 GROUP BY cost_type ORDER BY amt DESC",
                [$id]
            );
        } catch (Exception $e) {
            $colony = [];
            $costs = [];
            $summary = ['total_amount' => 0, 'total_gst' => 0, 'total_paid' => 0, 'total_balance' => 0, 'cost_count' => 0];
            $byType = [];
            error_log('ColonyPipeline developmentCosts error: ' . $e->getMessage());
        }

        return $this->render('admin/colony-pipeline/development-costs', [
            'page_title' => 'Development Costs — ' . ($colony['name'] ?? ''),
            'colony' => $colony,
            'costs' => $costs,
            'summary' => $summary,
            'by_type' => $byType
        ]);
    }

    /**
     * Store a development cost entry
     */
    public function storeCost($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$id]);
            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $costType = trim($_POST['cost_type'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $gstAmount = floatval($_POST['gst_amount'] ?? 0);

            if (empty($costType) || $amount <= 0) {
                $this->setFlash('error', 'Cost type and valid amount are required');
                $this->redirect('/admin/colony-pipeline/' . $id . '/costs');
            }

            $balanceAmount = $amount + $gstAmount - floatval($_POST['paid_amount'] ?? 0);

            $this->db->query(
                "INSERT INTO colony_development_costs (
                    colony_id, cost_type, vendor_id, vendor_name, work_description,
                    invoice_number, invoice_date, amount, gst_amount, tds_section,
                    payment_status, paid_amount, balance_amount, completion_date,
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $id,
                    $costType,
                    !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : null,
                    trim($_POST['vendor_name'] ?? ''),
                    trim($_POST['work_description'] ?? ''),
                    trim($_POST['invoice_number'] ?? ''),
                    !empty($_POST['invoice_date']) ? $_POST['invoice_date'] : null,
                    $amount,
                    $gstAmount,
                    trim($_POST['tds_section'] ?? ''),
                    $balanceAmount <= 0 ? 'paid' : (floatval($_POST['paid_amount'] ?? 0) > 0 ? 'partial' : 'unpaid'),
                    floatval($_POST['paid_amount'] ?? 0),
                    max($balanceAmount, 0),
                    !empty($_POST['completion_date']) ? $_POST['completion_date'] : null,
                    trim($_POST['status'] ?? 'planned')
                ]
            );

            $this->setFlash('success', 'Development cost recorded: ' . $costType . ' — ₹' . number_format($amount));
            $this->redirect('/admin/colony-pipeline/' . $id . '/costs');
        } catch (Exception $e) {
            error_log('ColonyPipeline storeCost error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to record cost: ' . $e->getMessage());
            $this->redirect('/admin/colony-pipeline/' . $id . '/costs');
        }
    }

    /**
     * Plot list — colony-scoped plot inventory with stats, filters, pagination
     */
    public function plotList($id)
    {
        $this->requireAdmin();

        try {
            $colony = $this->db->fetchOne("SELECT c.*, d.name as district_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id WHERE c.id = ?", [$id]);
            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Error loading colony');
            $this->redirect('/admin/colony-pipeline');
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        $status = trim($_GET['status'] ?? '');
        $block = trim($_GET['block'] ?? '');

        try {
            $sql = "SELECT p.* FROM plots p WHERE p.colony_id = ?";
            $params = [$id];

            if (!empty($status)) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }
            if (!empty($block)) {
                $sql .= " AND p.block = ?";
                $params[] = $block;
            }

            $countSql = "SELECT COUNT(*) as total FROM plots WHERE colony_id = ?";
            $countParams = [$id];
            if (!empty($status)) { $countSql .= " AND status = ?"; $countParams[] = $status; }
            if (!empty($block))  { $countSql .= " AND block = ?"; $countParams[] = $block; }
            $total = (int)($this->db->selectOne($countSql, $countParams)['total'] ?? 0);
            $totalPages = max(1, ceil($total / $perPage));

            $sql .= " ORDER BY p.block ASC, p.plot_number ASC LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;
            $plots = $this->db->fetchAll($sql, $params);

            $plotStats = $this->db->fetchOne(
                "SELECT COUNT(*) as total,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                        SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
                        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                        SUM(CASE WHEN status IN ('hold','reserved') THEN 1 ELSE 0 END) as hold,
                        SUM(total_price) as total_value,
                        AVG(price_per_sqft) as avg_ppsf
                 FROM plots WHERE colony_id = ?",
                [$id]
            );

            $filters = ['status' => $status, 'block' => $block];
        } catch (Exception $e) {
            $plots = [];
            $total = 0;
            $totalPages = 1;
            $plotStats = ['total' => 0, 'available' => 0, 'booked' => 0, 'sold' => 0, 'hold' => 0, 'total_value' => 0, 'avg_ppsf' => 0];
            $filters = ['status' => '', 'block' => ''];
            error_log('ColonyPipeline plotList query error: ' . $e->getMessage());
        }

        return $this->render('admin/colony-pipeline/plots', [
            'page_title' => 'Plots — ' . ($colony['name'] ?? ''),
            'colony' => $colony,
            'plots' => $plots,
            'plot_stats' => $plotStats,
            'total_plots' => $total,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage,
            'filters' => $filters
        ]);
    }

    /**
     * Plot stats API — JSON endpoint for colony-scoped plot statistics
     */
    public function plotStats($id)
    {
        $this->requireAdmin();

        try {
            $stats = $this->db->fetchOne(
                "SELECT COUNT(*) as total,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                        SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
                        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                        SUM(CASE WHEN status IN ('hold','reserved') THEN 1 ELSE 0 END) as hold,
                        SUM(total_price) as total_value,
                        AVG(price_per_sqft) as avg_ppsf,
                        AVG(area_sqft) as avg_area_sqft,
                        SUM(CASE WHEN corner_plot = 1 THEN 1 ELSE 0 END) as corner_plots,
                        SUM(CASE WHEN park_facing = 1 THEN 1 ELSE 0 END) as park_facing_plots
                 FROM plots WHERE colony_id = ?",
                [$id]
            );

            $blocks = $this->db->fetchAll(
                "SELECT block, COUNT(*) as count,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                        AVG(price_per_sqft) as avg_ppsf
                 FROM plots WHERE colony_id = ? GROUP BY block ORDER BY block",
                [$id]
            );

            $statusBreakdown = $this->db->fetchAll(
                "SELECT status, COUNT(*) as count
                 FROM plots WHERE colony_id = ? GROUP BY status",
                [$id]
            );

            $this->json([
                'success' => true,
                'colony_id' => $id,
                'stats' => $stats,
                'blocks' => $blocks,
                'status_breakdown' => $statusBreakdown
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Failed to load plot stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Plot Map View — Leaflet interactive map for a colony
     */
    public function plotMap($id)
    {
        $this->requireAdmin();

        try {
            $colony = $this->db->fetchOne(
                "SELECT c.*, d.name as district_name
                 FROM colonies c LEFT JOIN districts d ON c.district_id = d.id
                 WHERE c.id = ?",
                [$id]
            );

            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/admin/colony-pipeline');
            }

            $plots = $this->db->fetchAll(
                "SELECT id, plot_number, block, area_sqft, width_ft, length_ft,
                        status, price_per_sqft, total_price, corner_plot, park_facing,
                        road_facing, gata_number, khasra_number
                 FROM plots WHERE colony_id = ?
                 ORDER BY block, plot_number",
                [$id]
            );
        } catch (Exception $e) {
            $colony = [];
            $plots = [];
            error_log('ColonyPipeline plotMap error: ' . $e->getMessage());
        }

        return $this->render('admin/colony-pipeline/plot-map', [
            'page_title' => 'Plot Map — ' . ($colony['name'] ?? ''),
            'colony' => $colony,
            'plots' => $plots
        ]);
    }

    /**
     * Plot map GeoJSON data — returns plot geometries as GeoJSON FeatureCollection
     */
    public function plotMapGeoJson($id)
    {
        $this->requireAdmin();

        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$id]);
            if (!$colony) {
                $this->json(['type' => 'FeatureCollection', 'features' => []]);
                return;
            }

            $plots = $this->db->fetchAll(
                "SELECT id, plot_number, block, area_sqft, width_ft, length_ft,
                        status, price_per_sqft, total_price, corner_plot, park_facing,
                        road_facing, gata_number
                 FROM plots WHERE colony_id = ?
                 ORDER BY block, plot_number",
                [$id]
            );

            $features = [];
            $centerLat = $colony['latitude'] ? (float)$colony['latitude'] : 26.76;
            $centerLng = $colony['longitude'] ? (float)$colony['longitude'] : 83.37;
            $blocksMap = [];
            foreach ($plots as $p) {
                $block = $p['block'] ?? 'A';
                if (!isset($blocksMap[$block])) {
                    $blocksMap[$block] = ['plots' => [], 'y' => count($blocksMap)];
                }
                $blocksMap[$block]['plots'][] = $p;
            }

            foreach ($blocksMap as $block => $bData) {
                $x = 0;
                foreach ($bData['plots'] as $p) {
                    $w = max((float)($p['width_ft'] ?? 30), 20);
                    $l = max((float)($p['length_ft'] ?? 50), 30);
                    $scale = 0.000008;
                    $x1 = $centerLng + ($x * $scale * 1.1);
                    $y1 = $centerLat + ($bData['y'] * $scale * 1.1);
                    $x2 = $x1 + $w * $scale;
                    $y2 = $y1 + $l * $scale;

                    $statusColors = [
                        'available' => '#22c55e',
                        'booked' => '#eab308',
                        'sold' => '#ef4444',
                        'hold' => '#6b7280',
                        'reserved' => '#f97316',
                    ];
                    $color = $statusColors[$p['status']] ?? '#94a3b8';

                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [[
                                [$x1, $y1], [$x2, $y1], [$x2, $y2], [$x1, $y2], [$x1, $y1]
                            ]]
                        ],
                        'properties' => [
                            'id' => $p['id'],
                            'plot_number' => $p['plot_number'],
                            'block' => $block,
                            'area_sqft' => $p['area_sqft'],
                            'width_ft' => $p['width_ft'],
                            'length_ft' => $p['length_ft'],
                            'status' => $p['status'],
                            'price_per_sqft' => $p['price_per_sqft'],
                            'total_price' => $p['total_price'],
                            'corner_plot' => $p['corner_plot'],
                            'park_facing' => $p['park_facing'],
                            'road_facing' => $p['road_facing'],
                            'gata_number' => $p['gata_number'],
                            'fill' => $color,
                            'stroke' => '#1e293b',
                            'stroke-width' => 1,
                        ]
                    ];
                    $x += $w / 30 + 0.5;
                }
            }

            $this->json([
                'type' => 'FeatureCollection',
                'features' => $features,
                'metadata' => [
                    'colony_id' => $id,
                    'colony_name' => $colony['name'],
                    'total_plots' => count($plots),
                    'center' => [$centerLat, $centerLng]
                ]
            ]);
        } catch (Exception $e) {
            error_log('plotMapGeoJson error: ' . $e->getMessage());
            $this->json(['type' => 'FeatureCollection', 'features' => []], 500);
        }
    }
}
