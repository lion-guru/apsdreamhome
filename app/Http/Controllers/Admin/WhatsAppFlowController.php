<?php

namespace App\Http\Controllers\Admin;

use App\Services\Communication\WhatsAppFlowService;

/**
 * Admin WhatsApp Chatbot Flow Builder & Conversation Monitor
 */
class WhatsAppFlowController extends AdminController
{
    private WhatsAppFlowService $flowService;

    public function __construct()
    {
        parent::__construct();
        $this->flowService = new WhatsAppFlowService();
    }

    public function flows()
    {
        $this->requireAdmin();
        $flows = $this->flowService->getAllFlows();

        $this->render('admin/whatsapp/flows', [
            'page_title' => 'WhatsApp Chatbot Flow Builder',
            'flows' => $flows,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'id' => (int)($_POST['id'] ?? 0),
            'title' => trim((string)($_POST['title'] ?? '')),
            'trigger_keyword' => trim((string)($_POST['trigger_keyword'] ?? '')),
            'match_type' => $_POST['match_type'] ?? 'contains',
            'action_hook' => trim((string)($_POST['action_hook'] ?? '')),
            'reply_message' => trim((string)($_POST['reply_message'] ?? '')),
            'priority' => (int)($_POST['priority'] ?? 10),
        ];

        if ($data['title'] === '' || $data['trigger_keyword'] === '' || $data['reply_message'] === '') {
            $this->setFlash('error', 'Title, trigger keyword, and reply message are mandatory.');
            return $this->redirect('/admin/whatsapp/flows');
        }

        $this->flowService->saveFlow($data);
        $this->setFlash('success', 'WhatsApp bot flow saved successfully.');
        return $this->redirect('/admin/whatsapp/flows');
    }

    public function toggle($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $this->flowService->toggleFlow((int)$id);
        $this->setFlash('success', 'Flow status updated.');
        return $this->redirect('/admin/whatsapp/flows');
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $this->flowService->deleteFlow((int)$id);
        $this->setFlash('success', 'Flow deleted.');
        return $this->redirect('/admin/whatsapp/flows');
    }

    public function logs()
    {
        $this->requireAdmin();
        $logs = $this->flowService->getRecentLogs(100);

        $this->render('admin/whatsapp/logs', [
            'page_title' => 'WhatsApp Conversation Live Stream',
            'logs' => $logs,
        ]);
    }
}
