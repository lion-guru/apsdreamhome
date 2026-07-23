<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use App\Services\Communication\CommunicationAutomationService;

class CommunicationAdminController extends AdminController
{
    private $automationService;

    public function __construct()
    {
        parent::__construct();
        $this->automationService = new CommunicationAutomationService();
    }

    /**
     * Communication Automation Dashboard
     */
    public function automation()
    {
        $this->requireAdmin();
        
        $channelStatus = $this->automationService->getChannelStatus();
        
        // Get communication logs stats
        $db = Database::getInstance();
        try {
            $stats = [
                'total_logs' => (int)($db->query("SELECT COUNT(*) FROM communication_logs")->fetchColumn() ?? 0),
                'inbound_today' => (int)($db->query("SELECT COUNT(*) FROM communication_logs WHERE direction='inbound' AND DATE(created_at)=CURDATE()")->fetchColumn() ?? 0),
                'outbound_today' => (int)($db->query("SELECT COUNT(*) FROM communication_logs WHERE direction='outbound' AND DATE(created_at)=CURDATE()")->fetchColumn() ?? 0),
                'leads_created_today' => (int)($db->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at)=CURDATE() AND source IN ('whatsapp_inbound','telegram_inbound','sms_inbound')")->fetchColumn() ?? 0),
                'automated_messages' => (int)($db->query("SELECT COUNT(*) FROM automated_messages_log WHERE DATE(sent_at)=CURDATE()")->fetchColumn() ?? 0),
                'newsletter_subscribers' => (int)($db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status='active'")->fetchColumn() ?? 0),
            ];
        } catch (\Exception $e) {
            $stats = ['total_logs' => 0, 'inbound_today' => 0, 'outbound_today' => 0, 'leads_created_today' => 0, 'automated_messages' => 0, 'newsletter_subscribers' => 0];
        }

        $this->data['page_title'] = 'Communication Automation';
        $this->data['channel_status'] = $channelStatus;
        $this->data['stats'] = $stats;
        
        $this->render('admin/communication/automation');
    }

    /**
     * WhatsApp Setup Page
     */
    public function whatsappSetup()
    {
        $this->requireAdmin();
        
        $settings = $this->getSettings(['whatsapp_api_enabled', 'whatsapp_business_phone', 'whatsapp_api_token', 'whatsapp_webhook_verified']);
        
        $this->data['page_title'] = 'WhatsApp Business Setup';
        $this->data['settings'] = $settings;
        
        $this->render('admin/communication/whatsapp-setup');
    }

    /**
     * WhatsApp Setup Save
     */
    public function whatsappSetupSave()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'whatsapp_api_enabled' => $_POST['whatsapp_api_enabled'] ?? '0',
            'whatsapp_business_phone' => $_POST['whatsapp_business_phone'] ?? '',
            'whatsapp_api_token' => $_POST['whatsapp_api_token'] ?? '',
            'whatsapp_webhook_verified' => $_POST['whatsapp_webhook_verified'] ?? '0',
        ];

        $this->saveSettings($data);
        $this->setFlash('success', 'WhatsApp settings saved successfully');
        $this->redirect('/admin/communication/whatsapp-setup');
    }

    /**
     * Telegram Setup Page
     */
    public function telegramSetup()
    {
        $this->requireAdmin();
        
        $settings = $this->getSettings(['telegram_bot_token', 'telegram_bot_username', 'telegram_webhook_url', 'telegram_webhook_verified']);
        
        $this->data['page_title'] = 'Telegram Bot Setup';
        $this->data['settings'] = $settings;
        
        $this->render('admin/communication/telegram-setup');
    }

    /**
     * Telegram Setup Save
     */
    public function telegramSetupSave()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'telegram_bot_token' => $_POST['telegram_bot_token'] ?? '',
            'telegram_bot_username' => $_POST['telegram_bot_username'] ?? '',
            'telegram_webhook_url' => $_POST['telegram_webhook_url'] ?? '',
            'telegram_webhook_verified' => $_POST['telegram_webhook_verified'] ?? '0',
        ];

        $this->saveSettings($data);
        $this->setFlash('success', 'Telegram settings saved successfully');
        $this->redirect('/admin/communication/telegram-setup');
    }

    /**
     * SMS Setup Page
     */
    public function smsSetup()
    {
        $this->requireAdmin();
        
        $settings = $this->getSettings(['sms_enabled', 'sms_api_key', 'sms_sender_id', 'sms_route']);
        
        $this->data['page_title'] = 'SMS Gateway Setup';
        $this->data['settings'] = $settings;
        
        $this->render('admin/communication/sms-setup');
    }

    /**
     * SMS Setup Save
     */
    public function smsSetupSave()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'sms_enabled' => $_POST['sms_enabled'] ?? '0',
            'sms_api_key' => $_POST['sms_api_key'] ?? '',
            'sms_sender_id' => $_POST['sms_sender_id'] ?? '',
            'sms_route' => $_POST['sms_route'] ?? 'transactional',
        ];

        $this->saveSettings($data);
        $this->setFlash('success', 'SMS settings saved successfully');
        $this->redirect('/admin/communication/sms-setup');
    }

    /**
     * Email Templates Page
     */
    public function emailTemplates()
    {
        $this->requireAdmin();
        
        $db = Database::getInstance();
        try {
            $templates = $db->fetchAll("SELECT * FROM email_templates ORDER BY created_at DESC");
        } catch (\Exception $e) {
            $templates = [];
        }
        
        $this->data['page_title'] = 'Email Templates';
        $this->data['templates'] = $templates;
        
        $this->render('admin/communication/email-templates');
    }

    /**
     * Email Templates Save
     */
    public function emailTemplatesSave()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $db = Database::getInstance();
        
        try {
            $id = $_POST['template_id'] ?? 0;
            $data = [
                'name' => $_POST['name'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'body_html' => $_POST['body_html'] ?? '',
                'body_text' => $_POST['body_text'] ?? '',
                'category' => $_POST['category'] ?? 'general',
                'variables' => $_POST['variables'] ?? '[]',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($id) {
                $db->update('email_templates', $data, 'id = ?', [$id]);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->insert('email_templates', $data);
            }

            $this->setFlash('success', 'Email template saved successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to save template: ' . $e->getMessage());
        }

        $this->redirect('/admin/communication/email-templates');
    }

    /**
     * Email Templates Delete
     */
    public function emailTemplatesDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $db = Database::getInstance();
        $db->delete('email_templates', 'id = ?', [(int)$id]);

        $this->setFlash('success', 'Email template deleted successfully');
        $this->redirect('/admin/communication/email-templates');
    }

    /**
     * Communication Logs
     */
    public function logs()
    {
        $this->requireAdmin();
        
        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $where = '1=1';
        $params = [];
        
        $channel = $_GET['channel'] ?? '';
        $direction = $_GET['direction'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        
        if ($channel) { $where .= " AND channel = ?"; $params[] = $channel; }
        if ($direction) { $where .= " AND direction = ?"; $params[] = $direction; }
        if ($dateFrom) { $where .= " AND DATE(created_at) >= ?"; $params[] = $dateFrom; }
        if ($dateTo) { $where .= " AND DATE(created_at) <= ?"; $params[] = $dateTo; }
        
        try {
            $countStmt = $db->prepare("SELECT COUNT(*) FROM communication_logs WHERE $where");
            $countStmt->execute($params);
            $totalRows = (int)$countStmt->fetchColumn();
            $totalPages = ceil($totalRows / $perPage);
            
            $sql = "SELECT * FROM communication_logs WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $channels = $db->query("SELECT DISTINCT channel FROM communication_logs")->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            $logs = [];
            $channels = [];
            $totalRows = 0;
            $totalPages = 1;
        }
        
        $this->data['page_title'] = 'Communication Logs';
        $this->data['logs'] = $logs;
        $this->data['channels'] = $channels;
        $this->data['pagination'] = ['page' => $page, 'total_pages' => $totalPages, 'total' => $totalRows, 'per_page' => $perPage];
        $this->data['filters'] = ['channel' => $channel, 'direction' => $direction, 'date_from' => $dateFrom, 'date_to' => $dateTo];
        
        $this->render('admin/communication/logs');
    }

    /**
     * Test Send Message
     */
    public function testSend()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $channel = $_POST['channel'] ?? '';
        $to = $_POST['to'] ?? '';
        $message = $_POST['message'] ?? '';

        if (!$channel || !$to || !$message) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        try {
            $result = $this->automationService->sendMessage($channel, $to, $message);
            
            header('Content-Type: application/json');
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Test message sent successfully', 'message_id' => $result['message_id'] ?? null]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send: ' . ($result['error'] ?? 'Unknown error')]);
            }
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Get settings from site_content
     */
    private function getSettings(array $keys): array
    {
        $db = Database::getInstance();
        $placeholders = str_repeat('?,', count($keys) - 1) . '?';
        
        try {
            $stmt = $db->prepare("SELECT content_key, content_value FROM site_content WHERE section='settings' AND content_key IN ($placeholders)");
            $stmt->execute($keys);
            $results = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        } catch (\Exception $e) {
            $results = [];
        }
        
        return $results;
    }

    /**
     * Save settings to site_content
     */
    private function saveSettings(array $data): void
    {
        $db = Database::getInstance();
        
        foreach ($data as $key => $value) {
            try {
                $existing = $db->fetchOne("SELECT id FROM site_content WHERE section='settings' AND content_key = ?", [$key]);
                
                if ($existing) {
                    $db->update('site_content', ['content_value' => $value, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$existing['id']]);
                } else {
                    $db->insert('site_content', [
                        'section' => 'settings',
                        'content_key' => $key,
                        'content_value' => $value,
                        'content_type' => 'text',
                        'content_group' => 'communication',
                        'sort_order' => 0,
                        'is_active' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } catch (\Exception $e) {
                error_log("Failed to save setting $key: " . $e->getMessage());
            }
        }
    }
}