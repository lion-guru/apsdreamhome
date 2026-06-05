<?php

namespace App\Http\Controllers\Admin;

use App\Services\MaintenanceService;
use App\Services\AuditService;

/**
 * Admin controller for toggling maintenance mode and managing the
 * allow-list of IPs that can still reach the site during maintenance.
 */
class MaintenanceController extends AdminController
{
    private $svc;
    private $audit;

    public function __construct()
    {
        parent::__construct();
        try { $this->svc = new MaintenanceService($this->db); } catch (\Throwable $e) { $this->svc = null; }
        try { $this->audit = new AuditService($this->db); } catch (\Throwable $e) { $this->audit = null; }
    }

    /**
     * POST /admin/settings/maintenance/toggle
     * Flips maintenance mode and returns a JSON ack.
     */
    public function toggle()
    {
        $this->requireAdmin();
        if (!$this->svc) {
            return $this->jsonError('Service unavailable', 503);
        }
        $nowEnabled = $this->svc->toggle();
        if ($this->audit) {
            $this->audit->log(
                'maintenance.toggle',
                $_SESSION['admin_id'] ?? null,
                $_SESSION['role'] ?? 'admin',
                'system',
                null,
                $nowEnabled ? 'Maintenance mode enabled' : 'Maintenance mode disabled'
            );
        }
        return $this->jsonResponse([
            'success' => true,
            'enabled' => $nowEnabled,
            'message' => $nowEnabled ? 'Maintenance mode enabled' : 'Maintenance mode disabled',
        ]);
    }

    /**
     * POST /admin/settings/maintenance/ips/add
     * body: ip=1.2.3.4
     */
    public function addIp()
    {
        $this->requireAdmin();
        if (!$this->svc) return $this->jsonError('Service unavailable', 503);
        $ip = trim($_POST['ip'] ?? '');
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->jsonError('Invalid IP address', 400);
        }
        $this->svc->addAllowedIp($ip);
        if ($this->audit) {
            $this->audit->log('maintenance.ip.add', $_SESSION['admin_id'] ?? null, $_SESSION['role'] ?? 'admin', 'system', null, "Added IP $ip to maintenance allow-list");
        }
        return $this->jsonResponse(['success' => true, 'ip' => $ip, 'ips' => $this->svc->getAllowedIps()]);
    }

    /**
     * POST /admin/settings/maintenance/ips/remove
     * body: ip=1.2.3.4
     */
    public function removeIp()
    {
        $this->requireAdmin();
        if (!$this->svc) return $this->jsonError('Service unavailable', 503);
        $ip = trim($_POST['ip'] ?? '');
        $this->svc->removeAllowedIp($ip);
        if ($this->audit) {
            $this->audit->log('maintenance.ip.remove', $_SESSION['admin_id'] ?? null, $_SESSION['role'] ?? 'admin', 'system', null, "Removed IP $ip from maintenance allow-list");
        }
        return $this->jsonResponse(['success' => true, 'ip' => $ip, 'ips' => $this->svc->getAllowedIps()]);
    }

    /**
     * GET /admin/settings/maintenance/status (JSON)
     */
    public function status()
    {
        $this->requireAdmin();
        if (!$this->svc) return $this->jsonError('Service unavailable', 503);
        return $this->jsonResponse([
            'success' => true,
            'enabled' => $this->svc->isEnabled(),
            'ips'     => $this->svc->getAllowedIps(),
            'message' => $this->svc->getMessage(),
            'eta'     => $this->svc->getEta(),
        ]);
    }
}
