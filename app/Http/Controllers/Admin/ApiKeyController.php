<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class ApiKeyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    private function checkAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }

    public function index()
    {
        $this->checkAdmin();
        
        $keys = $this->db->query("SELECT * FROM api_keys ORDER BY is_active DESC, service_name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->data['page_title'] = 'API Keys Management';
        $this->data['api_keys'] = $keys;
        $this->data['active_count'] = count(array_filter($keys, fn($k) => $k['is_active']));
        $this->data['inactive_count'] = count(array_filter($keys, fn($k) => !$k['is_active']));
        
        $this->render('admin/api-keys/index', $this->data);
    }

    public function guide()
    {
        $this->checkAdmin();
        $this->data['page_title'] = 'AI API Keys Guide';
        $this->render('admin/api-keys/guide', $this->data);
    }

    public function create()
    {
        $this->checkAdmin();
        $this->data['page_title'] = 'Add API Key';
        $this->data['providers'] = ['OpenAI', 'Google Gemini', 'OpenRouter', 'Anthropic Claude', 'Hugging Face', 'Groq', 'Cohere', 'Azure', 'AWS', 'Other'];
        $this->render('admin/api-keys/create', $this->data);
    }

    public function store()
    {
        $this->checkAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $key_name = trim($_POST['key_name'] ?? '');
            $key_value = trim($_POST['key_value'] ?? '');
            $key_type = trim($_POST['key_type'] ?? 'api_key');
            $service_name = trim($_POST['service_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($key_name) || empty($key_value) || empty($service_name)) {
                $_SESSION['error'] = 'Please fill all required fields';
                header('Location: ' . BASE_URL . '/admin/api-keys/create');
                exit;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO api_keys (key_name, key_value, key_type, service_name, description, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$key_name, $key_value, $key_type, $service_name, $description, $is_active]);
                $_SESSION['success'] = 'API Key added successfully';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Failed to add API key: ' . $e->getMessage();
            }
        }
        
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }

    public function edit($id)
    {
        $this->checkAdmin();
        
        $key = $this->db->prepare("SELECT * FROM api_keys WHERE id = ?");
        $key->execute([$id]);
        $key = $key->fetch(\PDO::FETCH_ASSOC);
        
        if (!$key) {
            $_SESSION['error'] = 'API Key not found';
            header('Location: ' . BASE_URL . '/admin/api-keys');
            exit;
        }
        
        $this->data['page_title'] = 'Edit API Key';
        $this->data['api_key'] = $key;
        $this->data['providers'] = ['OpenAI', 'Google Gemini', 'OpenRouter', 'Anthropic Claude', 'Hugging Face', 'Groq', 'Cohere', 'Azure', 'AWS', 'Other'];
        $this->render('admin/api-keys/edit', $this->data);
    }

    public function update($id)
    {
        $this->checkAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $key_name = trim($_POST['key_name'] ?? '');
            $key_value = trim($_POST['key_value'] ?? '');
            $key_type = trim($_POST['key_type'] ?? 'api_key');
            $service_name = trim($_POST['service_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            try {
                $stmt = $this->db->prepare("UPDATE api_keys SET key_name = ?, key_value = ?, key_type = ?, service_name = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$key_name, $key_value, $key_type, $service_name, $description, $is_active, $id]);
                $_SESSION['success'] = 'API Key updated successfully';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Failed to update API key';
            }
        }
        
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }

    public function delete($id)
    {
        $this->checkAdmin();
        
        try {
            $stmt = $this->db->prepare("DELETE FROM api_keys WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'API Key deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to delete API key';
        }
        
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }

    public function toggle($id)
    {
        $this->checkAdmin();
        
        try {
            $stmt = $this->db->prepare("UPDATE api_keys SET is_active = NOT is_active, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to toggle API key status';
        }
        
        header('Location: ' . BASE_URL . '/admin/api-keys');
        exit;
    }

    public function test($id)
    {
        $this->checkAdmin();
        
        $key = $this->db->prepare("SELECT * FROM api_keys WHERE id = ?");
        $key->execute([$id]);
        $key = $key->fetch(\PDO::FETCH_ASSOC);
        
        if (!$key) {
            echo json_encode(['success' => false, 'message' => 'API Key not found']);
            exit;
        }

        $result = $this->testApiKey($key['key_name'], $key['key_value'], $key['service_name']);
        echo json_encode($result);
    }

    private function testApiKey($name, $value, $service)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        switch (strtolower($service)) {
            case 'openai':
                curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/models');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $value]);
                break;
            case 'google gemini':
                curl_setopt($ch, CURLOPT_URL, 'https://generativelanguage.googleapis.com/v1/models?key=' . $value);
                break;
            case 'openrouter':
                curl_setopt($ch, CURLOPT_URL, 'https://openrouter.ai/api/v1/models');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $value, 'HTTP-Referer: ' . BASE_URL]);
                break;
            case 'anthropic claude':
                curl_setopt($ch, CURLOPT_URL, 'https://api.anthropic.com/v1/models');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['x-api-key: ' . $value, 'anthropic-version: 2023-06-01']);
                break;
            case 'hugging face':
                curl_setopt($ch, CURLOPT_URL, 'https://huggingface.co/api/whoami-v2');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $value]);
                break;
            default:
                return ['success' => false, 'message' => 'Unknown service: ' . $service];
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            // Update usage
            $this->db->prepare("UPDATE api_keys SET last_used_at = NOW(), usage_count = usage_count + 1 WHERE key_name = ?")->execute([$name]);
            return ['success' => true, 'message' => 'API Key is VALID', 'http_code' => $http_code];
        } else {
            return ['success' => false, 'message' => 'API Key is INVALID (HTTP ' . $http_code . ')', 'http_code' => $http_code];
        }
    }
}
