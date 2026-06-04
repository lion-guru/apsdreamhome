<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\WebhookService;

class WebhookController extends AdminController
{
    public function __construct() { parent::__construct(); }

    public function index()
    {
        $this->requireAdmin();
        $svc = new WebhookService($this->db);
        $endpoints = $svc->listEndpoints();
        $deliveries = $svc->getDeliveries(0, 50);
        $stats = $svc->getStats(7);
        $this->data = array_merge($this->data, [
            'page_title' => 'Webhooks',
            'endpoints' => $endpoints,
            'deliveries' => $deliveries,
            'stats' => $stats,
        ]);
        return $this->render('admin/features/webhooks', $this->data);
    }

    public function create()
    {
        $this->requireAdmin();
        $svc = new WebhookService($this->db);
        $name = trim($_POST['name'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $events = $_POST['events'] ?? [];
        $secret = trim($_POST['secret'] ?? '') ?: bin2hex(random_bytes(16));

        if (!$name || !$url) {
            $_SESSION['flash_error'] = 'Name and URL are required';
            header('Location: ' . BASE_URL . '/admin/webhooks');
            exit;
        }

        $id = $svc->registerEndpoint($name, $url, is_array($events) ? $events : [$events], $secret, (int)($_SESSION['user_id'] ?? 0));
        $_SESSION['flash_success'] = "Webhook endpoint created (ID: $id)";
        header('Location: ' . BASE_URL . '/admin/webhooks');
        exit;
    }

    public function toggle($id)
    {
        $this->requireAdmin();
        $svc = new WebhookService($this->db);
        $active = ($_POST['active'] ?? '0') === '1';
        $svc->toggleEndpoint((int)$id, $active);
        header('Location: ' . BASE_URL . '/admin/webhooks');
        exit;
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $svc = new WebhookService($this->db);
        $svc->deleteEndpoint((int)$id);
        header('Location: ' . BASE_URL . '/admin/webhooks');
        exit;
    }

    public function process()
    {
        $this->requireAdmin();
        $svc = new WebhookService($this->db);
        $result = $svc->processPending(100);
        $_SESSION['flash_success'] = "Processed {$result['processed']} deliveries";
        header('Location: ' . BASE_URL . '/admin/webhooks');
        exit;
    }
}
