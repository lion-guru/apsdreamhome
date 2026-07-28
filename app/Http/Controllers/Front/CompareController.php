<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\ComparisonService;
use App\Traits\TenantAwareTrait;

class CompareController extends BaseController
{
    use TenantAwareTrait;
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        parent::__construct();
    }

    /**
     * Skip CSRF for AJAX comparison endpoints (session-based, no DB mutation)
     */
    protected function skipCsrfProtection(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, '/compare/add') !== false
            || strpos($uri, '/compare/remove') !== false
            || strpos($uri, '/compare/clear') !== false
            || strpos($uri, '/compare/count') !== false;
    }

    /**
     * Show comparison page with selected plots side by side
     */
    public function index()
    {
        $service = new ComparisonService();
        $plots = $service->getComparisonData();
        $this->render('pages/compare', [
            'plots' => $plots,
            'count' => count($plots),
            'page_title' => 'Compare Plots - APS Dream Home',
        ]);
    }

    /**
     * AJAX: Add plot to comparison
     */
    public function add()
    {
        $plotId = (int)($_POST['plot_id'] ?? 0);
        if ($plotId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid plot ID']);
            return;
        }
        $service = new ComparisonService();
        $result = $service->add($plotId);
        $this->json($result);
    }

    /**
     * AJAX: Remove plot from comparison
     */
    public function remove()
    {
        $plotId = (int)($_POST['plot_id'] ?? 0);
        $service = new ComparisonService();
        $result = $service->remove($plotId);
        $this->json($result);
    }

    /**
     * AJAX: Clear comparison list
     */
    public function clear()
    {
        $service = new ComparisonService();
        $service->clear();
        $this->json(['success' => true]);
    }

    /**
     * AJAX: Get count
     */
    public function count()
    {
        $service = new ComparisonService();
        $this->json(['count' => $service->getCount()]);
    }
}
