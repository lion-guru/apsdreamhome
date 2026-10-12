<?php

namespace App\Http\Controllers\Admin;

use App\Services\IoTAlertService;

/**
 * IoT Threshold Alerts desk (Session 222) — complements the existing
 * /admin/iot device & reading stack without touching it.
 */
class IotAlertController extends AdminController
{
    private IoTAlertService $alerts;

    public function __construct()
    {
        parent::__construct();
        $this->alerts = new IoTAlertService();
    }

    public function index()
    {
        $this->requireAdmin();

        // Offline scan runs on every desk load (deduped — open alerts suppress repeats).
        $scanCreated = $this->alerts->scanOffline();

        $view = ($_GET['view'] ?? '') === 'resolved' ? 'resolved' : 'open';
        $colonyId = (int)($_GET['colony_id'] ?? 0);

        $this->render('admin/iot/alerts', [
            'page_title' => 'IoT Alerts & Telemetry Health',
            'alerts'    => $this->alerts->listAlerts([
                'resolved' => $view === 'resolved',
                'colony_id' => $colonyId > 0 ? $colonyId : null,
            ]),
            'stats'     => $this->alerts->stats(),
            // NOTE: key must NOT be 'view' — BaseController::render() extract()s $data
            // AFTER taking the path param, and a 'view' key overwrites $view itself
            // ("View not found: open").
            'filterView' => $view,
            'colonyId'  => $colonyId,
            'scanCreated' => $scanCreated,
            'offlineMinutes' => IoTAlertService::OFFLINE_MINUTES,
        ]);
    }

    public function resolve($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $ok = $this->alerts->resolveAlert((int)$id, (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0));
        $_SESSION[$ok ? 'success' : 'error'] = $ok
            ? "Alert #{$id} resolved."
            : "Alert #{$id} not found or already resolved.";

        $back = (($_POST['view'] ?? $_GET['view'] ?? '') === 'resolved') ? '?view=resolved' : '';
        header('Location: ' . BASE_URL . '/admin/iot/alerts' . $back);
        exit;
    }
}
