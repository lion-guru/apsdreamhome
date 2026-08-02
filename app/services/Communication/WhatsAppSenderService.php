<?php
namespace App\Services\Communication;

use App\Core\Database;
use App\Traits\ServiceTenantTrait;

class WhatsAppSenderService
{
    use ServiceTenantTrait;

    private $db;
    private $apiUrl;
    private $accessToken;
    private $phoneNumberId;
    private $businessAccountId;
    private $webhookVerifyToken;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $this->apiUrl = 'https://graph.facebook.com/' . ($_ENV['WHATSAPP_API_VERSION'] ?? 'v18.0') . '/';
        $this->accessToken = $_ENV['WHATSAPP_ACCESS_TOKEN'] ?? '';
        $this->phoneNumberId = $_ENV['WHATSAPP_PHONE_NUMBER_ID'] ?? '';
        $this->businessAccountId = $_ENV['WHATSAPP_BUSINESS_ACCOUNT_ID'] ?? '';
        $this->webhookVerifyToken = $_ENV['WHATSAPP_WEBHOOK_VERIFY_TOKEN'] ?? 'apsdreamhome_webhook_2026';

        try {
            $rows = $this->db->fetchAll("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'whatsapp_%'");
        } catch (\Exception $e) {
            $rows = [];
        }
        foreach ($rows as $row) {
            $key = str_replace('whatsapp_', '', $row['setting_key']);
            switch ($key) {
                case 'access_token': $this->accessToken = $row['setting_value']; break;
                case 'phone_number_id': $this->phoneNumberId = $row['setting_value']; break;
                case 'business_account_id': $this->businessAccountId = $row['setting_value']; break;
                case 'webhook_verify_token': $this->webhookVerifyToken = $row['setting_value']; break;
            }
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->accessToken) && !empty($this->phoneNumberId);
    }

    public function sendMessage($to, $message, $templateName = null)
    {
        $phone = $this->cleanPhone($to);
        $messageId = 'MSG' . time() . rand(1000, 9999);

        try {
            $tid = $this->tenantId();
            $columns = "phone_number, message, direction, message_type, status, whatsapp_message_id, created_at";
            $values = "?, ?, 'outbound', ?, 'pending', ?, NOW()";
            $params = [$phone, $message, $templateName ? 'template' : 'text', $messageId];
            if ($tid > 1) {
                $columns .= ", tenant_id";
                $values .= ", ?";
                $params[] = $tid;
            }
            $this->db->execute(
                "INSERT INTO whatsapp_messages ($columns) VALUES ($values)",
                $params
            );
        } catch (\Exception $e) {
            error_log("WhatsAppSenderService: insert failed: " . $e->getMessage());
        }

        $apiResult = $this->callMetaApi($phone, $message, $templateName);

        $status = $apiResult['status'] ?? 'failed';
        $errorMsg = $apiResult['error'] ?? null;

        try {
            $statusCol = $status === 'sent' ? 'sent' : 'failed';
            $this->db->execute(
                "UPDATE whatsapp_messages SET status = ?, whatsapp_message_id = COALESCE(?, whatsapp_message_id), updated_at = NOW() WHERE phone_number = ? AND created_at >= NOW() - INTERVAL 5 SECOND" . $this->tenantSql(),
                [$statusCol, $apiResult['meta_message_id'] ?? null, $phone]
            );
        } catch (\Exception $e) {
            error_log("WhatsAppSenderService: update failed: " . $e->getMessage());
        }

        return [
            'success' => $status === 'sent',
            'message_id' => $messageId,
            'meta_message_id' => $apiResult['meta_message_id'] ?? null,
            'status' => $status,
            'error' => $errorMsg,
        ];
    }

    public function sendTemplate($to, $templateName, $parameters = [])
    {
        $phone = $this->cleanPhone($to);
        $messageId = 'TMPL' . time() . rand(1000, 9999);

        try {
            $tid = $this->tenantId();
            $columns = "phone_number, message, direction, message_type, status, whatsapp_message_id, created_at";
            $values = "?, ?, 'outbound', 'template', 'pending', ?, NOW()";
            $params = [$phone, 'Template: ' . $templateName . ' | Params: ' . json_encode($parameters), $messageId];
            if ($tid > 1) {
                $columns .= ", tenant_id";
                $values .= ", ?";
                $params[] = $tid;
            }
            $this->db->execute(
                "INSERT INTO whatsapp_messages ($columns) VALUES ($values)",
                $params
            );
        } catch (\Exception $e) {
            error_log("WhatsAppSenderService: insert failed: " . $e->getMessage());
        }

        $apiResult = $this->callMetaApiTemplate($phone, $templateName, $parameters);

        $status = $apiResult['status'] ?? 'failed';

        try {
            $this->db->execute(
                "UPDATE whatsapp_messages SET status = ?, updated_at = NOW() WHERE phone_number = ? AND created_at >= NOW() - INTERVAL 5 SECOND" . $this->tenantSql(),
                [$status === 'sent' ? 'sent' : 'failed', $phone]
            );
        } catch (\Exception $e) {
            error_log("WhatsAppSenderService: update template status failed: " . $e->getMessage());
        }

        return [
            'success' => $status === 'sent',
            'message_id' => $messageId,
            'meta_message_id' => $apiResult['meta_message_id'] ?? null,
            'status' => $status,
            'error' => $apiResult['error'] ?? null,
        ];
    }

    public function processQueue($limit = 10)
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT * FROM whatsapp_messages WHERE status = 'pending' AND direction = 'outbound' ORDER BY created_at ASC LIMIT ?" . $this->tenantSql(),
                [$limit]
            );
        } catch (\Exception $e) {
            error_log("WhatsAppSenderService: processQueue fetch failed: " . $e->getMessage());
            return ['processed' => 0, 'errors' => [$e->getMessage()]];
        }

        $processed = 0;
        $errors = [];

        foreach ($rows as $row) {
            $result = $row['message_type'] === 'template'
                ? $this->callMetaApiTemplate($row['phone_number'], $row['message'], [])
                : $this->callMetaApi($row['phone_number'], $row['message']);

            $newStatus = $result['status'] === 'sent' ? 'sent' : 'failed';
            try {
                $this->db->execute(
                    "UPDATE whatsapp_messages SET status = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
                    [$newStatus, $row['id']]
                );
                $processed++;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return ['processed' => $processed, 'errors' => $errors];
    }

    public function checkDeliveryStatus($messageId)
    {
        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            return ['status' => 'unknown', 'error' => 'WhatsApp not configured'];
        }

        $url = $this->apiUrl . $this->phoneNumberId . '/messages/' . urlencode($messageId);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $status = $data['messages'][0]['status'] ?? 'unknown';

            try {
                $this->db->execute(
                    "UPDATE whatsapp_messages SET status = ?, updated_at = NOW() WHERE whatsapp_message_id = ?" . $this->tenantSql(),
                    [$status, $messageId]
                );
            } catch (\Exception $e) {
                error_log("WhatsAppSenderService: status update failed: " . $e->getMessage());
            }

            return ['status' => $status, 'data' => $data];
        }

        try {
            $row = $this->db->fetch("SELECT status FROM whatsapp_messages WHERE whatsapp_message_id = ?" . $this->tenantSql(), [$messageId]);
            return ['status' => $row['status'] ?? 'unknown', 'http_code' => $httpCode];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'http_code' => $httpCode];
        }
    }

    public function getWebhookPayload($request)
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($_GET['hub_verify_token'])) {
            if ($_GET['hub_verify_token'] === $this->webhookVerifyToken) {
                return ['type' => 'verify', 'challenge' => $_GET['hub_challenge'] ?? ''];
            }
            return ['type' => 'verify', 'error' => 'Invalid verify token'];
        }

        if (!$input) {
            return ['type' => 'error', 'error' => 'Invalid payload'];
        }

        $messages = [];
        if (isset($input['entry'])) {
            foreach ($input['entry'] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    $value = $change['value'] ?? [];
                    foreach ($value['messages'] ?? [] as $msg) {
                        $messages[] = [
                            'from' => $msg['from'] ?? '',
                            'id' => $msg['id'] ?? '',
                            'type' => $msg['type'] ?? '',
                            'text' => $msg['text']['body'] ?? '',
                            'timestamp' => $msg['timestamp'] ?? '',
                        ];

                        $statusUpdate = $value['statuses'][0] ?? null;
                        if ($statusUpdate) {
                            try {
                $this->db->execute(
                    "UPDATE whatsapp_messages SET status = ?, updated_at = NOW() WHERE whatsapp_message_id = ?" . $this->tenantSql(),
                                    [$statusUpdate['status'] ?? 'unknown', $statusUpdate['id'] ?? '']
                                );
                            } catch (\Exception $e) {
                                error_log("WhatsAppSenderService: webhook status update failed: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }

        return ['type' => 'message', 'messages' => $messages];
    }

    private function callMetaApi($phone, $message, $templateName = null)
    {
        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            error_log("WhatsAppSenderService: Meta API not configured - message logged (not sent)");
            return ['status' => 'logged', 'meta_message_id' => null, 'error' => null];
        }

        $url = $this->apiUrl . $this->phoneNumberId . '/messages';

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        try {
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 201) {
                $respData = json_decode($response, true);
                $metaId = $respData['messages'][0]['id'] ?? null;
                return ['status' => 'sent', 'meta_message_id' => $metaId, 'error' => null];
            }

            $errorBody = json_decode($response, true);
            $errorMsg = $errorBody['error']['message'] ?? ($errorBody['error']['error_user_msg'] ?? 'HTTP ' . $httpCode);
            error_log("WhatsAppSenderService: Meta API error ($httpCode): $errorMsg");
            return ['status' => 'failed', 'meta_message_id' => null, 'error' => $errorMsg];

        } catch (\Exception $e) {
            curl_close($ch);
            error_log("WhatsAppSenderService: Meta API exception: " . $e->getMessage());
            return ['status' => 'failed', 'meta_message_id' => null, 'error' => $e->getMessage()];
        }
    }

    private function callMetaApiTemplate($phone, $templateName, $parameters)
    {
        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            error_log("WhatsAppSenderService: Meta API not configured - template logged (not sent)");
            return ['status' => 'logged', 'meta_message_id' => null, 'error' => null];
        }

        $url = $this->apiUrl . $this->phoneNumberId . '/messages';

        $templateComponents = [];
        if (!empty($parameters)) {
            $bodyParams = [];
            foreach (array_values($parameters) as $value) {
                $bodyParams[] = ['type' => 'text', 'text' => $value];
            }
            $templateComponents[] = ['type' => 'body', 'parameters' => $bodyParams];
        }

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'en'],
                'components' => $templateComponents,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        try {
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 201) {
                $respData = json_decode($response, true);
                $metaId = $respData['messages'][0]['id'] ?? null;
                return ['status' => 'sent', 'meta_message_id' => $metaId, 'error' => null];
            }

            $errorBody = json_decode($response, true);
            $errorMsg = $errorBody['error']['message'] ?? ($errorBody['error']['error_user_msg'] ?? 'HTTP ' . $httpCode);
            error_log("WhatsAppSenderService: Meta API template error ($httpCode): $errorMsg");
            return ['status' => 'failed', 'meta_message_id' => null, 'error' => $errorMsg];

        } catch (\Exception $e) {
            curl_close($ch);
            error_log("WhatsAppSenderService: Meta API template exception: " . $e->getMessage());
            return ['status' => 'failed', 'meta_message_id' => null, 'error' => $e->getMessage()];
        }
    }

    private function cleanPhone($phone)
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);
        if (strpos($phone, '+') !== 0 && strlen($phone) === 10) {
            $phone = '+91' . $phone;
        }
        if (strpos($phone, '+') !== 0 && strlen($phone) === 12 && substr($phone, 0, 2) === '91') {
            $phone = '+' . $phone;
        }
        return $phone;
    }
}
