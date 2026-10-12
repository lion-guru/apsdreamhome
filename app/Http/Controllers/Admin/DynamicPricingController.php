<?php

namespace App\Http\Controllers\Admin;

use App\Services\Pricing\DynamicPricingService;
use App\Core\Database\Database;
use PDO;

/**
 * AI Dynamic Pricing & Yield Optimization Engine — admin desk.
 */
class DynamicPricingController extends AdminController
{
    private DynamicPricingService $pricing;

    public function __construct()
    {
        parent::__construct();
        $this->pricing = new DynamicPricingService();
        DynamicPricingService::ensureConfigRows(Database::getInstance()->getConnection());
    }

    public function index()
    {
        $this->requireAdmin();

        $status = $_GET['status'] ?? null;
        if (!in_array($status, ['pending', 'approved', 'rejected', 'applied'], true)) {
            $status = null;
        }

        $this->render('admin/pricing/dynamic_pricing', [
            'page_title' => 'AI Dynamic Pricing & Yield Optimizer',
            'stats'      => $this->pricing->dashboardStats(),
            'suggestions'=> $this->pricing->listSuggestions($status, 50),
            'models'     => $this->pricing->getModels(),
            'colonies'   => $this->pricing->colonies(),
            'statusFilter' => $status,
            'config'     => DynamicPricingService::config(),
        ]);
    }

    public function generate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $colonyId = (int)($_POST['colony_id'] ?? 0);
        if ($colonyId > 0) {
            $r = $this->pricing->suggestForColony($colonyId);
            if (!empty($r['success'])) {
                $_SESSION['success'] = "Suggestion #{$r['id']} generated: {$r['current']} -> {$r['suggested']} ({$r['delta_pct']}%, confidence {$r['confidence']})";
            } else {
                $_SESSION['error'] = 'Skipped: ' . ($r['skipped'] ?? 'unknown') . (isset($r['error']) ? ' — ' . $r['error'] : '');
            }
        } else {
            $sum = $this->pricing->generateAll();
            $err = $sum['details']['error'] ?? null;
            if ($err) {
                $_SESSION['error'] = 'Generation failed: ' . $err;
            } else {
                $_SESSION['success'] = "Generated {$sum['generated']} suggestion(s), skipped {$sum['skipped']} colony(ies).";
            }
        }

        header('Location: ' . BASE_URL . '/admin/dynamic-pricing');
        exit;
    }

    public function train()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $colonyId = (int)($_POST['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            $_SESSION['error'] = 'Select a colony to train';
        } else {
            $r = $this->pricing->trainModel($colonyId);
            if (!empty($r['success'])) {
                $_SESSION['success'] = "Model trained: {$r['samples']} samples, RMSE {$r['rmse']}, R2 {$r['r2']}";
            } else {
                $_SESSION['error'] = $r['error'] ?? 'Training failed';
            }
        }

        header('Location: ' . BASE_URL . '/admin/dynamic-pricing#models');
        exit;
    }

    public function approve($id)
    {
        $this->flip($id, 'approve');
    }

    public function reject($id)
    {
        $this->flip($id, 'reject');
    }

    public function apply($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;

        $r = $this->pricing->apply($id, $this->currentUserId());
        if (!empty($r['success'])) {
            $_SESSION['success'] = "Applied to {$r['affected']} available plot(s) — new base Rs.{$r['new_base']}/sqft. Rollback trail in price_history.";
        } else {
            $_SESSION['error'] = $r['error'] ?? 'Apply failed';
        }

        header('Location: ' . BASE_URL . '/admin/dynamic-pricing#suggestions');
        exit;
    }

    private function flip($id, string $action)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $r = $action === 'approve'
            ? $this->pricing->approve((int)$id, $this->currentUserId())
            : $this->pricing->reject((int)$id, $this->currentUserId());

        if (!empty($r['success'])) {
            $_SESSION['success'] = "Suggestion #{$id} " . $r['status'];
        } else {
            $_SESSION['error'] = $r['error'] ?? 'Update failed';
        }

        header('Location: ' . BASE_URL . '/admin/dynamic-pricing#suggestions');
        exit;
    }

    private function currentUserId(): int
    {
        return (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);
    }
}
