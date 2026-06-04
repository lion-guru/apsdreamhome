<?php

namespace App\Http\Controllers\Admin;

use App\Services\VisitService;
use App\Services\AuditService;

class VisitController extends AdminController
{
    private $service;
    private $audit;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new VisitService($this->db); } catch (\Throwable $e) { $this->service = null; }
        try { $this->audit = new AuditService($this->db); } catch (\Throwable $e) { $this->audit = null; }
    }

    public function index()
    {
        $stats = $this->service ? $this->service->getStats() : [];
        $visits = $this->service ? $this->service->getAll('', '', 50) : [];
        $slots = $this->service ? $this->service->getAvailableSlots(date('Y-m-d'), date('Y-m-d', strtotime('+14 days'))) : [];
        return $this->render('admin.visits.index', [
            'page_title' => 'Property Visits',
            'page_heading' => 'Property Visit Schedule',
            'stats' => $stats,
            'visits' => $visits,
            'slots' => $slots
        ]);
    }

    public function confirm()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $this->service->updateStatus($id, 'confirmed');
            if ($this->audit) $this->audit->log('visit.confirm', $this->getUserId(), $this->getUserRole(), 'visit', $id, "Confirmed visit #$id");
            $this->setFlash('success', 'Visit confirmed');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function complete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $this->service->updateStatus($id, 'completed');
            $this->setFlash('success', 'Visit marked as completed');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function cancel()
    {
        $id = (int)($_GET['id'] ?? 0);
        $reason = $_POST['reason'] ?? $_GET['reason'] ?? 'Cancelled by admin';
        if ($this->service && $id) {
            $this->service->cancel($id, $reason);
            if ($this->audit) $this->audit->log('visit.cancel', $this->getUserId(), $this->getUserRole(), 'visit', $id, "Cancelled visit #$id: $reason");
            $this->setFlash('success', 'Visit cancelled');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function noshow()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $this->service->updateStatus($id, 'no_show');
            $this->setFlash('warning', 'Marked as no-show');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }
}
