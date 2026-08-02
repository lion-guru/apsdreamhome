<?php

namespace App\Services\Communication;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

class WhatsAppTemplateService
{
    use ServiceTenantTrait;

    /** @var PDO */
    protected $db;

    /** @var string */
    protected $apiUrl = 'https://graph.facebook.com/v18.0';
    
    /** @var string */
    protected $phoneNumberId = '';
    
    /** @var string */
    protected $accessToken = '';

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                $pdo = null;
            }
        }
        if (!$pdo instanceof PDO) {
            $pdo = null;
        }
        $this->db = $pdo;

        // Load config from DB
        $this->loadConfig();
    }

    protected function loadConfig(): void
    {
        if (!$this->db) return;

        try {
            $stmt = $this->db->query("SELECT config_key, config_value FROM whatsapp_config WHERE is_active = 1" . $this->tenantSql());
            $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $this->phoneNumberId = $configs['phone_number_id'] ?? '';
            $this->accessToken = $configs['access_token'] ?? '';
            $this->apiUrl = $configs['api_url'] ?? 'https://graph.facebook.com/v18.0';
        } catch (Exception $e) {
            error_log('[WhatsAppTemplateService::loadConfig] ' . $e->getMessage());
        }
    }

    /**
     * Send template message
     * 
     * @param array $data {
     *     to: string - Phone number with country code (e.g., 919876543210)
     *     template_name: string - Template name approved by Meta
     *     language: string - Language code (e.g., en, hi)
     *     components: array - Template components with parameters
     * }
     * @return array
     */
    public function sendTemplate(array $data): array
    {
        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $to = $data['to'] ?? '';
        $templateName = $data['template_name'] ?? '';
        $language = $data['language'] ?? 'en';
        $components = $data['components'] ?? [];

        if (!$to || !$templateName) {
            return ['success' => false, 'error' => 'to and template_name are required'];
        }

        // Normalize phone number
        $to = $this->normalizePhone($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        $url = "{$this->apiUrl}/{$this->phoneNumberId}/messages";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        // Log the message
        $this->logMessage($to, $templateName, $payload, $result, $httpCode);

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'message_id' => $result['messages'][0]['id'] ?? null, 'response' => $result];
        }

        return ['success' => false, 'error' => $result['error']['message'] ?? 'Failed to send', 'response' => $result];
    }

    /**
     * Send booking confirmation
     */
    public function sendBookingConfirmation(string $phone, array $bookingData): array
    {
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $bookingData['customer_name'] ?? 'Customer'],
                    ['type' => 'text', 'text' => $bookingData['booking_number'] ?? ''],
                    ['type' => 'text', 'text' => $bookingData['plot_code'] ?? ''],
                    ['type' => 'text', 'text' => $bookingData['colony_name'] ?? ''],
                    ['type' => 'text', 'text' => '₹' . number_format((float)($bookingData['amount'] ?? 0), 2)],
                    ['type' => 'text', 'text' => $bookingData['booking_date'] ?? date('d M Y')],
                ],
            ],
        ];

        return $this->sendTemplate([
            'to' => $phone,
            'template_name' => 'booking_confirmation',
            'language' => 'en',
            'components' => $components,
        ]);
    }

    /**
     * Send EMI reminder
     */
    public function sendEmiReminder(string $phone, array $emiData): array
    {
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $emiData['customer_name'] ?? 'Customer'],
                    ['type' => 'text', 'text' => $emiData['emi_number'] ?? ''],
                    ['type' => 'text', 'text' => '₹' . number_format((float)($emiData['amount'] ?? 0), 2)],
                    ['type' => 'text', 'text' => $emiData['due_date'] ?? ''],
                    ['type' => 'text', 'text' => $emiData['booking_number'] ?? ''],
                ],
            ],
        ];

        return $this->sendTemplate([
            'to' => $phone,
            'template_name' => 'emi_reminder',
            'language' => 'en',
            'components' => $components,
        ]);
    }

    /**
     * Send payment receipt
     */
    public function sendPaymentReceipt(string $phone, array $paymentData): array
    {
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $paymentData['customer_name'] ?? 'Customer'],
                    ['type' => 'text', 'text' => $paymentData['receipt_number'] ?? ''],
                    ['type' => 'text', 'text' => '₹' . number_format((float)($paymentData['amount'] ?? 0), 2)],
                    ['type' => 'text', 'text' => $paymentData['payment_date'] ?? date('d M Y')],
                    ['type' => 'text', 'text' => $paymentData['payment_mode'] ?? 'UPI'],
                    ['type' => 'text', 'text' => $paymentData['booking_number'] ?? ''],
                ],
            ],
        ];

        return $this->sendTemplate([
            'to' => $phone,
            'template_name' => 'payment_receipt',
            'language' => 'en',
            'components' => $components,
        ]);
    }

    /**
     * Send agreement signing notification
     */
    public function sendAgreementNotification(string $phone, array $agreementData): array
    {
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $agreementData['customer_name'] ?? 'Customer'],
                    ['type' => 'text', 'text' => $agreementData['agreement_type'] ?? 'Booking Agreement'],
                    ['type' => 'text', 'text' => $agreementData['booking_number'] ?? ''],
                    ['type' => 'text', 'text' => $agreementData['plot_code'] ?? ''],
                ],
            ],
        ];

        return $this->sendTemplate([
            'to' => $phone,
            'template_name' => 'agreement_signing',
            'language' => 'en',
            'components' => $components,
        ]);
    }

    /**
     * Normalize phone number to E.164 format
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-digits
        $phone = preg_replace('/\D/', '', $phone);
        
        // Add country code if missing (assume India +91)
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            $phone = '91' . substr($phone, 1);
        } elseif (strlen($phone) === 12 && substr($phone, 0, 2) === '91') {
            // Already has country code
        } elseif (strlen($phone) === 13 && substr($phone, 0, 3) === '+91') {
            $phone = substr($phone, 1);
        }
        
        return $phone;
    }

    /**
     * Log message to database
     */
    protected function logMessage(string $to, string $template, array $request, array $response, int $httpCode): void
    {
        if (!$this->db) return;

        try {
            $tid = $this->tenantId();
            $columns = "to_number, template_name, request_payload, response_payload, http_status, status, sent_at";
            $values = "?, ?, ?, ?, ?, ?, NOW()";
            $params = [
                $to,
                $template,
                json_encode($request),
                json_encode($response),
                $httpCode,
                $httpCode >= 200 && $httpCode < 300 ? 'sent' : 'failed',
            ];
            if ($tid > 1) {
                $columns .= ", tenant_id";
                $values .= ", ?";
                $params[] = $tid;
            }
            $stmt = $this->db->prepare("
                INSERT INTO whatsapp_message_logs 
                ($columns)
                VALUES ($values)
            ");
            $stmt->execute($params);
        } catch (Exception $e) {
            error_log('[WhatsAppTemplateService::logMessage] ' . $e->getMessage());
        }
    }

    /**
     * Get message history
     */
    public function getHistory(string $phone = '', int $limit = 50): array
    {
        if (!$this->db) return [];

        try {
            $sql = "SELECT * FROM whatsapp_message_logs";
            $params = [];
            $tid = $this->tenantId();
            $tidClause = $tid > 1 ? "tenant_id = {$tid}" : '';

            if ($phone) {
                $phone = $this->normalizePhone($phone);
                $sql .= " WHERE to_number = ?";
                $params[] = $phone;
                if ($tidClause) {
                    $sql .= " AND $tidClause";
                }
            } elseif ($tidClause) {
                $sql .= " WHERE $tidClause";
            }

            $sql .= " ORDER BY sent_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get template status from Meta
     */
    public function getTemplateStatus(string $templateName): array
    {
        if (empty($this->accessToken)) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $url = "{$this->apiUrl}/{$this->phoneNumberId}/message_templates?name={$templateName}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode === 200) {
            return ['success' => true, 'data' => $result];
        }

        return ['success' => false, 'error' => $result['error']['message'] ?? 'Failed to fetch'];
    }
}