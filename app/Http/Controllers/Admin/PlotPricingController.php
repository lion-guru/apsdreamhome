<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Pricing\PlcRuleService;

/**
 * Plot Pricing & PLC Rules Management
 */
class PlotPricingController extends AdminController
{
    private $plcService;

    public function __construct()
    {
        parent::__construct();
        $this->plcService = new \App\Services\Pricing\PlcRuleService();
    }

    /**
     * List PLC rules with colony filter
     */
    public function plcRules()
    {
        $this->requireAdmin();
        $colonyId = (int)($_GET['colony_id'] ?? 0);
        $statusFilter = $_GET['status'] ?? '';

        $rules = $this->plcService->getAllRules();
        if ($colonyId > 0) {
            $rules = array_values(array_filter($rules, function ($r) use ($colonyId) {
                return empty($r['colony_id']) || (int)$r['colony_id'] === $colonyId;
            }));
        }
        if ($statusFilter === '1' || $statusFilter === '0') {
            $wanted = (int)$statusFilter;
            $rules = array_values(array_filter($rules, function ($r) use ($wanted) {
                return (int)$r['is_active'] === $wanted;
            }));
        }
        $colonies = $this->db->fetchAll("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name");

        $this->render('admin/plots/plc_rules', [
            'page_title' => 'PLC Rules Management',
            'rules' => $rules,
            'colonies' => $colonies,
            'selected_colony' => $colonyId,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Create a new PLC rule
     */
    public function createPlcRule()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'colony_id' => (int)($_POST['colony_id'] ?? 0) ?: null,
            'name' => trim((string)($_POST['name'] ?? '')),
            'attribute_key' => trim((string)($_POST['attribute_key'] ?? '')),
            'condition_type' => $_POST['condition_type'] ?? '',
            'condition_value' => trim((string)($_POST['condition_value'] ?? '')),
            'charge_type' => $_POST['charge_type'] ?? '',
            'charge_value' => (float)($_POST['charge_value'] ?? 0),
            'priority' => (int)($_POST['priority'] ?? 0),
        ];

        $result = $this->plcService->createRule($data);

        if (!empty($result['success'])) {
            $this->setFlash('success', 'PLC rule created successfully');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to create PLC rule');
        }

        $this->redirect('/admin/plots/pricing-rules');
    }

    /**
     * Edit PLC rule - return JSON for modal
     */
    public function editPlcRule($id)
    {
        $this->requireAdmin();

        $rule = $this->plcService->getRuleById((int)$id);

        if (!$rule) {
            $this->jsonResponse(['success' => false, 'error' => 'Rule not found'], 404);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'id' => $rule['id'],
            'name' => $rule['name'],
            'colony_id' => $rule['colony_id'],
            'attribute_key' => $rule['attribute_key'],
            'condition_type' => $rule['condition_type'],
            'condition_value' => $rule['condition_value'],
            'charge_type' => $rule['charge_type'],
            'charge_value' => $rule['charge_value'],
            'priority' => $rule['priority'],
            'is_active' => $rule['is_active'],
        ]);
    }

    /**
     * Update a PLC rule
     */
    public function updatePlcRule($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'colony_id' => (int)($_POST['colony_id'] ?? 0) ?: null,
            'name' => trim((string)($_POST['name'] ?? '')),
            'attribute_key' => trim((string)($_POST['attribute_key'] ?? '')),
            'condition_type' => $_POST['condition_type'] ?? '',
            'condition_value' => trim((string)($_POST['condition_value'] ?? '')),
            'charge_type' => $_POST['charge_type'] ?? '',
            'charge_value' => (float)($_POST['charge_value'] ?? 0),
            'priority' => (int)($_POST['priority'] ?? 0),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ];

        $result = $this->plcService->updateRule((int)$id, $data);

        if (!empty($result['success'])) {
            $this->setFlash('success', 'PLC rule updated successfully');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to update PLC rule');
        }

        $this->redirect('/admin/plots/pricing-rules');
    }

    /**
     * Delete (deactivate) a PLC rule
     */
    public function deletePlcRule($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->plcService->deleteRule((int)$id);

        if (!empty($result['success'])) {
            $this->setFlash('success', 'PLC rule deactivated');
        } else {
            $this->setFlash('error', 'Failed to deactivate PLC rule');
        }

        $this->redirect('/admin/plots/pricing-rules');
    }

    /**
     * Branded 1-page quotation PDF for an available plot.
     * Route: GET /admin/plots/{id}/quotation-pdf
     */
    public function quotationPdf($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $svc = new \App\Services\PDF\AgreementPDFService(
                $this->db instanceof \PDO ? $this->db : null
            );
            $me = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'APS Sales Desk';
            $res = $svc->generateQuotation($id, (string)$me);
        } catch (\Throwable $e) {
            error_log('PlotPricingController::quotationPdf: ' . $e->getMessage());
            $res = ['success' => false];
        }
        if (empty($res['success']) || !is_file($res['pdf_path'] ?? '')) {
            $this->setFlash('error', $res['error'] ?? 'Quotation failed (plot must be available).');
            return $this->redirect('/admin/plots/' . $id);
        }
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($res['pdf_path']) . '"');
        header('Content-Length: ' . filesize($res['pdf_path']));
        readfile($res['pdf_path']);
        exit;
    }

    /**
     * Preview PLC calculation for a plot
     */
    public function previewPlc()
    {
        $this->requireAdmin();

        $plotId = (int)($_GET['plot_id'] ?? 0);
        if ($plotId <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid plot ID'], 400);
            return;
        }

        try {
            $plot = $this->db->fetchOne(
                "SELECT p.*, c.name as colony_name 
                 FROM plots p
                 LEFT JOIN colonies c ON p.colony_id = c.id
                 WHERE p.id = ?",
                [$plotId]
            );

            if (!$plot) {
                $this->jsonResponse(['success' => false, 'error' => 'Plot not found'], 404);
                return;
            }

            $plcService = new \App\Services\Pricing\PlcRuleService();
            $result = $plcService->calculatePlotPLC($plot);

            // Combined truth: same math the stored-price calc uses
            // (static service_configs engine + DB rules fold).
            $base = (float)($plot['base_price_per_sqft'] ?? 0) > 0
                ? (float)$plot['base_price_per_sqft']
                : (float)($plot['price_per_sqft'] ?? 0);
            $combined = \App\Services\Pricing\PlcService::calculate(
                $base,
                (float)($plot['area_sqft'] ?? 0),
                !empty($plot['corner_plot']),
                !empty($plot['park_facing']),
                (float)($plot['road_width_ft'] ?? 0),
                [
                    'colony_id' => (int)($plot['colony_id'] ?? 0),
                    'facing' => (string)($plot['facing'] ?? ''),
                    'id' => (int)$plot['id'],
                    'plot_number' => $plot['plot_number'] ?? '',
                ]
            );

            $this->jsonResponse([
                'success' => true,
                'data' => $result,
                'combined' => $combined,
            ]);
        } catch (\Exception $e) {
            error_log('PlotPricingController::previewPlc error: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Failed to calculate PLC'], 500);
        }
    }
}