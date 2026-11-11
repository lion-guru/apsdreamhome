<?php

namespace App\Http\Controllers\Admin;

use App\Services\Messenger\DeadLetterMonitorService;

class DeadLetterController extends AdminController
{
    private $monitor;

    public function __construct()
    {
        parent::__construct();
        $this->monitor = new DeadLetterMonitorService();
    }

    public function index()
    {
        $this->requireAdmin();
        $stats = $this->monitor->getStats();
        $failed = $this->monitor->getFailedMessages(50, 0);
        $byTransport = $this->monitor->getFailedByTransport();

        $this->render('admin/messenger/dead_letters', [
            'page_title' => 'Dead Letter Queue',
            'stats' => $stats,
            'failed' => $failed,
            'byTransport' => $byTransport,
            'threshold' => $this->monitor->getThresholdCount(),
            'retentionDays' => $this->monitor->getRetentionDays(),
        ]);
    }

    public function retry($id = null)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/messenger/dead-letters');
            return;
        }
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $ok = $this->monitor->retryMessage($id);
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Message retried to original queue' : 'Failed to retry message';
        $this->redirect('/admin/messenger/dead-letters');
    }

    public function delete($id = null)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/messenger/dead-letters');
            return;
        }
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $ok = $this->monitor->deleteMessage($id);
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Message deleted' : 'Failed to delete message';
        $this->redirect('/admin/messenger/dead-letters');
    }

    public function cleanup()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/messenger/dead-letters');
            return;
        }
        $this->validateCsrfOrFail();
        $deleted = $this->monitor->cleanupOldMessages();
        $_SESSION['success'] = 'Cleaned up ' . $deleted . ' old messages (> ' . $this->monitor->getRetentionDays() . ' days)';
        $this->redirect('/admin/messenger/dead-letters');
    }
}
