<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\AuditService;

class AuditLogController extends AdminController
{
    public function __construct() { parent::__construct(); }

    public function index()
    {
        $this->requireAdmin();
        $svc = new AuditService($this->db);
        $action = $_GET['action'] ?? null;
        $entity = $_GET['entity'] ?? null;
        $logs = $svc->getRecent(200, $action ?: null, $entity ?: null);
        $stats = $svc->getStats(7);
        $this->data = array_merge($this->data, [
            'page_title' => 'Audit Log',
            'logs' => $logs,
            'stats' => $stats,
            'filter_action' => $action,
            'filter_entity' => $entity,
        ]);
        return $this->render('admin/features/audit_log', $this->data);
    }

    public function api()
    {
        $svc = new AuditService($this->db);
        $logs = $svc->getRecent((int)($_GET['limit'] ?? 50), $_GET['action'] ?? null, $_GET['entity'] ?? null);
        return $this->jsonResponse(['ok' => true, 'data' => $logs, 'stats' => $svc->getStats(7)]);
    }
}
