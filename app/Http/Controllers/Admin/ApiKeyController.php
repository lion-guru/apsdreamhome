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

    public function edit($id)
    {
        $this->requireAdmin();
        $svc = new ApiKeyService($this->db);
        $keys = $svc->list();
        $key = null;
        foreach ($keys as $k) {
            if ((int)$k['id'] === (int)$id) { $key = $k; break; }
        }
        if (!$key) {
            $_SESSION['flash_error'] = 'API key not found';
            header('Location: ' . BASE_URL . '/admin/api-keys');
            exit;
        }
        $this->data = array_merge($this->data, [
            'page_title' => 'Edit API Key',
            'key' => $key,
        ]);
        return $this->render('admin/features/api_keys_edit', $this->data);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $name = trim($_POST['name'] ?? '');
        $scopes = $_POST['scopes'] ?? [];
        $rateLimit = (int)($_POST['rate_limit'] ?? 60);

        if (!$name) {
            $_SESSION['flash_error'] = 'Name is required';
            header('Location: ' . BASE_URL . '/admin/api-keys/edit/' . $id);
            exit;
        }

        try {
            $st = $this->db->prepare("UPDATE api_keys SET name = :n, scopes = :s, rate_limit_per_minute = :r WHERE id = :id");
            $st->execute([
                ':n' => $name,
                ':s' => is_array($scopes) ? implode(',', $scopes) : $scopes,
                ':r' => $rateLimit,
                ':id' => $id
            ]);
            $_SESSION['flash_success'] = 'API key updated';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to update API key';
        }
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }

    public function toggle($id)
    {
        $this->requireAdmin();
        $svc = new ApiKeyService($this->db);
        $id = (int)$id;
        try {
            $st = $this->db->prepare("SELECT is_active FROM api_keys WHERE id = :id");
            $st->execute([':id' => $id]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $newStatus = $row['is_active'] ? 0 : 1;
                $st2 = $this->db->prepare("UPDATE api_keys SET is_active = :s WHERE id = :id");
                $st2->execute([':s' => $newStatus, ':id' => $id]);
                $_SESSION['flash_success'] = $newStatus ? 'API key activated' : 'API key deactivated';
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to toggle API key';
        }
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }

    public function test($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $st = $this->db->prepare("SELECT id, name, api_key, scopes, is_active, rate_limit_per_minute, last_used_at, expires_at FROM api_keys WHERE id = :id");
            $st->execute([':id' => $id]);
            $key = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$key) {
                $_SESSION['flash_error'] = 'API key not found';
                header('Location: ' . BASE_URL . '/admin/api-keys');
                exit;
            }
            $this->data = array_merge($this->data, [
                'page_title' => 'Test API Key',
                'key' => $key,
                'test_result' => [
                    'status' => $key['is_active'] ? 'active' : 'inactive',
                    'key_preview' => substr($key['api_key'], 0, 12) . '...' . substr($key['api_key'], -4),
                    'scopes' => explode(',', $key['scopes']),
                    'rate_limit' => $key['rate_limit_per_minute'],
                    'expires_at' => $key['expires_at'] ?? 'Never',
                    'last_used' => $key['last_used_at'] ?? 'Never',
                    'valid' => $key['is_active'] && (!$key['expires_at'] || strtotime($key['expires_at']) > time())
                ]
            ]);
            return $this->render('admin/features/api_keys_test', $this->data);
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to test API key';
            header('Location: ' . BASE_URL . '/admin/api-keys');
            exit;
        }
    }
}
