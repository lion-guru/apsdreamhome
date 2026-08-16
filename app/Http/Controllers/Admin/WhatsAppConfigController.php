<?php
namespace App\Http\Controllers\Admin;

use App\Services\Communication\WhatsAppSenderService;

class WhatsAppConfigController extends AdminController
{
    private $whatsapp;

    public function __construct()
    {
        parent::__construct();
        $this->whatsapp = new WhatsAppSenderService();
    }

    public function settings()
    {
        $this->requireAdmin();

        $settings = [];
        $msgCount = 0;
        $error = null;

        try {
            $rows = $this->db->fetchAll(
                "SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'whatsapp_%'"
            );
            foreach ($rows as $r) {
                $settings[$r['setting_key']] = $r['setting_value'];
            }

            $row = $this->db->fetch("SELECT COUNT(*) as cnt FROM whatsapp_messages");
            $msgCount = (int)($row['cnt'] ?? 0);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return $this->render('admin/whatsapp/settings', [
            'page_title' => 'WhatsApp Settings',
            'settings' => $settings,
            'message_count' => $msgCount,
            'is_configured' => $this->whatsapp->isConfigured(),
            'error' => $error,
        ]);
    }

    public function saveSettings()
    {
        $this->requireAdmin();

        $keys = [
            'whatsapp_phone_number_id',
            'whatsapp_access_token',
            'whatsapp_business_account_id',
            'whatsapp_webhook_verify_token',
        ];

        foreach ($keys as $key) {
            $value = $_POST[$key] ?? '';
            if ($value === '') continue;

            try {
                $existing = $this->db->fetch(
                    "SELECT id FROM site_settings WHERE setting_key = ?",
                    [$key]
                );
                if ($existing) {
                    $this->db->execute(
                        "UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                        [$value, $key]
                    );
                } else {
                    $this->db->execute(
                        "INSERT INTO site_settings (setting_key, setting_value, category, created_at, updated_at) VALUES (?, ?, 'whatsapp', NOW(), NOW())",
                        [$key, $value]
                    );
                }
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to save ' . $key . ': ' . $e->getMessage());
                $this->redirect('/admin/whatsapp/settings');
                return;
            }
        }

        $this->setFlash('success', 'WhatsApp settings saved successfully');
        $this->redirect('/admin/whatsapp/settings');
    }

    public function testMessage()
    {
        $this->requireAdmin();

        $phone = $_POST['test_phone'] ?? '';
        $message = $_POST['test_message'] ?? '';

        if (empty($phone)) {
            $this->setFlash('error', 'Test phone number is required');
            $this->redirect('/admin/whatsapp/settings');
            return;
        }

        $body = !empty($message) ? $message : 'This is a test message from APS Dream Home WhatsApp system. If you receive this, your WhatsApp integration is working!';

        $result = $this->whatsapp->sendMessage($phone, $body);

        if ($result['success']) {
            $statusMsg = $result['status'] === 'logged'
                ? 'Test message logged in database. Real sending requires Meta API credentials.'
                : 'Test message sent successfully.';
            $this->setFlash('success', $statusMsg . ' (Message ID: ' . $result['message_id'] . ')');
        } else {
            $this->setFlash('error', 'Test message failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $this->redirect('/admin/whatsapp/settings');
    }

    public function templates()
    {
        $this->requireAdmin();

        $templates = [];
        $error = null;

        try {
            $templates = $this->db->fetchAll(
                "SELECT * FROM whatsapp_templates ORDER BY template_name ASC"
            );
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return $this->render('admin/whatsapp/templates', [
            'page_title' => 'WhatsApp Templates',
            'templates' => $templates,
            'error' => $error,
        ]);
    }

    public function syncTemplates()
    {
        $this->requireAdmin();

        if (!$this->whatsapp->isConfigured()) {
            $this->setFlash('error', 'Configure WhatsApp API credentials first');
            $this->redirect('/admin/whatsapp/settings');
            return;
        }

        $url = 'https://graph.facebook.com/v18.0/' . urlencode($_ENV['WHATSAPP_BUSINESS_ACCOUNT_ID'] ?? '') . '/message_templates';
        $accessToken = $_ENV['WHATSAPP_ACCESS_TOKEN'] ?? '';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $errorBody = json_decode($response, true);
            $errorMsg = $errorBody['error']['message'] ?? ($errorBody['error']['error_user_msg'] ?? 'HTTP ' . $httpCode);
            $this->setFlash('error', 'Failed to sync templates from Meta: ' . $errorMsg);
            $this->redirect('/admin/whatsapp/templates');
            return;
        }

        $data = json_decode($response, true);
        $synced = 0;

        try {
            foreach ($data['data'] ?? [] as $tmpl) {
                $name = $tmpl['name'] ?? '';
                if (empty($name)) continue;

                $existing = $this->db->fetch(
                    "SELECT id FROM whatsapp_templates WHERE template_name = ?",
                    [$name]
                );

                $content = $tmpl['components'][0]['text'] ?? '';
                $category = $tmpl['category'] ?? 'MARKETING';
                $language = $tmpl['language'] ?? 'en';
                $status = $tmpl['status'] ?? 'PENDING';

                if ($existing) {
                    $this->db->execute(
                        "UPDATE whatsapp_templates SET template_content = ?, category = ?, language = ?, status = ? WHERE id = ?",
                        [$content, $category, $language, $status === 'APPROVED' ? 'active' : 'inactive', $existing['id']]
                    );
                } else {
                    $this->db->execute(
                        "INSERT INTO whatsapp_templates (template_name, template_content, category, language, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                        [$name, $content, $category, $language, $status === 'APPROVED' ? 'active' : 'inactive']
                    );
                }
                $synced++;
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error saving synced templates: ' . $e->getMessage());
            $this->redirect('/admin/whatsapp/templates');
            return;
        }

        $this->setFlash('success', "Synced $synced templates from Meta Cloud API");
        $this->redirect('/admin/whatsapp/templates');
    }
}
