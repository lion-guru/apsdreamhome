<?php

namespace App\Http\Controllers\Admin;

use App\Services\Pricing\PricingExperimentService;

/**
 * A/B Pricing Experiment Lab — draft variants → start (assign plots) →
 * apply prices → live results → restore & archive. Extends Dynamic Pricing desk.
 */
class PricingExperimentController extends AdminController
{
    private PricingExperimentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PricingExperimentService();
    }

    public function index()
    {
        $this->requireAdmin();
        $this->render('admin/pricing/experiments', [
            'page_title'  => 'A/B Pricing Experiments',
            'experiments' => $this->service->listExperiments(50),
            'colonies'    => $this->service->colonies(),
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $e = $this->service->getExperiment($id);
        if (!$e) {
            $_SESSION['error'] = "Experiment #{$id} not found";
            header('Location: ' . BASE_URL . '/admin/dynamic-pricing/experiments');
            exit;
        }
        $this->render('admin/pricing/experiment_show', [
            'page_title' => 'Experiment: ' . $e['experiment_name'],
            'experiment' => $e,
            'results'    => $e['status'] === 'draft' ? null : $this->service->results($id),
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $labels  = (array)($_POST['variant_label'] ?? []);
        $prices  = (array)($_POST['variant_pps'] ?? []);
        $splits  = (array)($_POST['variant_split'] ?? []);
        $variants = [];
        foreach ($labels as $i => $label) {
            if (trim((string)$label) === '') {
                continue;
            }
            $variants[] = [
                'label'     => trim((string)$label),
                'base_pps'  => (float)($prices[$i] ?? 0),
                'split_pct' => (float)($splits[$i] ?? 0),
            ];
        }

        $r = $this->service->create([
            'colony_id'        => (int)($_POST['colony_id'] ?? 0),
            'experiment_name'  => (string)($_POST['experiment_name'] ?? ''),
            'control_base_pps' => (float)($_POST['control_base_pps'] ?? 0),
            'min_sample_size'  => (int)($_POST['min_sample_size'] ?? 30),
            'variants'         => $variants,
        ], $this->currentUserId());

        if (!empty($r['success'])) {
            $_SESSION['success'] = "Experiment #{$r['id']} created as draft (control gets {$r['control_split_pct']}% of traffic).";
        } else {
            $_SESSION['error'] = $r['error'] ?? 'Create failed';
        }
        header('Location: ' . BASE_URL . '/admin/dynamic-pricing/experiments');
        exit;
    }

    public function start($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $r = $this->service->start((int)$id, $this->currentUserId());
        if (!empty($r['success'])) {
            $parts = [];
            foreach ($r['by_variant'] as $k => $c) {
                $parts[] = "{$k}:{$c}";
            }
            $_SESSION['success'] = "Started — {$r['assigned']} available plots assigned (" . implode(', ', $parts) . ').';
        } else {
            $_SESSION['error'] = $r['error'] ?? 'Start failed';
        }
        header('Location: ' . BASE_URL . '/admin/dynamic-pricing/experiments/' . (int)$id);
        exit;
    }

    public function apply($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $r = $this->service->applyPrices((int)$id, $this->currentUserId());
        if (!empty($r['success'])) {
            $_SESSION['success'] = "Variant prices applied to {$r['affected']} available plot(s). Rollback trail in price_history (reference_type=pricing_experiment).";
        } else {
            $_SESSION['error'] = $r['error'] ?? 'Apply failed';
        }
        header('Location: ' . BASE_URL . '/admin/dynamic-pricing/experiments/' . (int)$id);
        exit;
    }

    public function restore($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $r = $this->service->restoreOriginalPrices((int)$id, $this->currentUserId());
        if (!empty($r['success'])) {
            $_SESSION['success'] = "Restored {$r['affected']} available plot(s) to their original pre-experiment prices. Experiment archived.";
        } else {
            $_SESSION['error'] = $r['error'] ?? 'Restore failed';
        }
        header('Location: ' . BASE_URL . '/admin/dynamic-pricing/experiments/' . (int)$id);
        exit;
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $r = $this->service->deleteDraft((int)$id);
        $_SESSION[$r['success'] ? 'success' : 'error'] = $r['success'] ? "Draft #{$id} deleted." : ($r['error'] ?? 'Delete failed');
        header('Location: ' . BASE_URL . '/admin/dynamic-pricing/experiments');
        exit;
    }

    private function currentUserId(): int
    {
        return (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);
    }
}
