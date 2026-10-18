<?php
namespace App\Http\Controllers\Admin;

use App\Services\CallRecordingService;

class CallRecordingController extends AdminController
{
    private CallRecordingService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CallRecordingService();
    }

    public function index()
    {
        $this->requireAdmin();
        $items = $this->service->getAll($_GET);
        $stats = $this->service->getStats();
        return $this->render('admin/crm/call_recordings/index', [
            'page_title' => 'Call Recordings',
            'items' => $items,
            'stats' => $stats,
        ]);
    }

    public function show(int $id)
    {
        $this->requireAdmin();
        $item = $this->service->getById($id);
        if (!$item) {
            $this->setFlash('error', 'Recording not found');
            return $this->redirect('/admin/crm/call-recordings');
        }
        return $this->render('admin/crm/call_recordings/show', [
            'page_title' => 'Call Recording Detail',
            'item' => $item,
        ]);
    }

    public function byLead(int $leadId)
    {
        $this->requireAdmin();
        $items = $this->service->getByLead($leadId);
        return $this->render('admin/crm/call_recordings/lead', [
            'page_title' => 'Lead Call Recordings',
            'lead_id' => $leadId,
            'items' => $items,
        ]);
    }
}
