<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\ApiKeyService;

class ApiKeyController extends AdminController
{
    public function __construct() { parent::__construct(); }

    public function index()
    {
        $this->requireAdmin();
        $svc = new ApiKeyService($this->db);
        $keys = $svc->list();
        $stats = $svc->getStats();
        $newKey = $_SESSION['new_api_key'] ?? null;
        unset($_SESSION['new_api_key']);

        $this->data = array_merge($this->data, [
            'page_title' => 'API Keys',
            'keys' => $keys,
            'stats' => $stats,
            'new_key' => $newKey,
        ]);
        return $this->render('admin/features/api_keys', $this->data);
    }

    public function create()
    {
        $this->requireAdmin();
        $name = trim($_POST['name'] ?? '');
        $scopes = $_POST['scopes'] ?? [];
        $rateLimit = (int)($_POST['rate_limit'] ?? 60);
        $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

        if (!$name) {
            $_SESSION['flash_error'] = 'Name is required';
            header('Location: ' . BASE_URL . '/admin/api-keys');
            exit;
        }

        $svc = new ApiKeyService($this->db);
        $newKey = $svc->create($name, (int)($_SESSION['user_id'] ?? 0), is_array($scopes) ? $scopes : [$scopes], $rateLimit, $expiresAt);
        $_SESSION['new_api_key'] = $newKey;
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }

    public function revoke($id)
    {
        $this->requireAdmin();
        $svc = new ApiKeyService($this->db);
        $svc->revoke((int)$id);
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }

    public function activate($id)
    {
        $this->requireAdmin();
        $svc = new ApiKeyService($this->db);
        $svc->activate((int)$id);
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $svc = new ApiKeyService($this->db);
        $svc->delete((int)$id);
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }
}
