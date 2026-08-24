<?php
namespace App\Services\Communication;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

class SmsSenderService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $provider;
    private $apiKey;
    private $senderId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->provider = $_ENV['SMS_PROVIDER'] ?? 'log';
        $this->apiKey = $_ENV['SMS_API_KEY'] ?? ($_ENV['MSG91_AUTH_KEY'] ?? '');
        $this->senderId = $_ENV['SMS_SENDER_ID'] ?? 'APSDHM';
    }

    public function send($phone, $message)
    {
        $phone = $this->cleanPhone($phone);

        try {
            if ($this->provider === 'twilio') {
                $result = $this->sendViaTwilio($phone, $message);
            } elseif ($this->provider === 'msg91') {
                $result = $this->sendViaMsg91($phone, $message);
            } else {
                $result = $this->sendViaLog($phone, $message);
            }

            $this->logToQueue($phone, $message, $result['status'] ?? 'sent');
            return $result;
        } catch (\Exception $e) {
            error_log("SmsSenderService: Send failed: " . $e->getMessage());
            $this->logToQueue($phone, $message, 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendFromQueue($queueId)
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM sms_queue WHERE id = ? AND status = 'pending'" . $tenantFilter;
        $params = [$queueId];
        if ($tid > 1) $params[] = $tid;
        $sms = $this->db->fetchOne($sql, $params);
        if (!$sms) return false;

        $updParams = [$queueId];
        if ($tid > 1) $updParams[] = $tid;
        $this->db->query("UPDATE sms_queue SET attempts = attempts + 1 WHERE id = ?" . $tenantFilter, $updParams);

        try {
            $result = $this->send($sms['recipient'], $sms['message']);
            $status = ($result['success'] ?? false) ? 'sent' : 'failed';
            $updParams2 = [$status, $result['error'] ?? null, $queueId];
            if ($tid > 1) $updParams2[] = $tid;
            $this->db->query(
                "UPDATE sms_queue SET status = ?, sent_at = NOW() WHERE id = ?" . $tenantFilter,
                [$updParams2[0], $updParams2[2]]
            );
            return $result['success'] ?? false;
        } catch (\Exception $e) {
            $updParams3 = [$e->getMessage(), $queueId];
            if ($tid > 1) $updParams3[] = $tid;
            $this->db->query(
                "UPDATE sms_queue SET status = 'failed' WHERE id = ?" . $tenantFilter,
                [$updParams3[1]]
            );
            return false;
        }
    }

    public function processQueue($limit = 10)
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM sms_queue 
                WHERE status = 'pending' AND attempts < 3" . $tenantFilter . "
                ORDER BY created_at ASC LIMIT ?";
        $params = [$limit];
        if ($tid > 1) $params[] = $tid;
        $items = $this->db->fetchAll($sql, $params);
        $results = ['processed' => 0, 'sent' => 0, 'failed' => 0];
        foreach ($items as $item) {
            $ok = $this->sendFromQueue($item['id']);
            $results['processed']++;
            if ($ok) $results['sent']++; else $results['failed']++;
        }
        return $results;
    }

    private function sendViaLog($phone, $message)
    {
        $logFile = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs/sms.log' : __DIR__ . '/../../../storage/logs/sms.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $line = date('Y-m-d H:i:s') . " | TO: $phone | MSG: " . substr($message, 0, 100) . "\n";
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        return ['success' => true, 'status' => 'sent', 'provider' => 'log'];
    }

    private function sendViaTwilio($phone, $message)
    {
        try {
            $twilio = new \App\Services\Gateway\TwilioService();
            $result = $twilio->sendSms($phone, $message);
            if (!empty($result['success'])) {
                return ['success' => true, 'status' => 'sent', 'provider' => 'twilio', 'sid' => $result['sid'] ?? null];
            }
            return ['success' => false, 'status' => 'failed', 'error' => $result['error'] ?? 'Twilio send failed', 'provider' => 'twilio'];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 'failed', 'error' => 'TwilioService unavailable: ' . $e->getMessage(), 'provider' => 'twilio'];
        }
    }

    private function sendViaMsg91($phone, $message)
    {
        if (!$this->apiKey) return ['success' => false, 'status' => 'failed', 'error' => 'MSG91 not configured'];
        $flowId = $_ENV['MSG91_FLOW_ID'] ?? '';
        $payload = [
            'sender' => $this->senderId,
            'route' => '4',
            'country' => '91',
            'sms' => [['message' => $message, 'to' => [$phone]]],
        ];
        $url = 'https://api.msg91.com/api/v5/flow/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authkey: ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http === 200) return ['success' => true, 'status' => 'sent', 'provider' => 'msg91'];
        return ['success' => false, 'status' => 'failed', 'error' => "MSG91 HTTP $http: $resp"];
    }

    private function logToQueue($phone, $message, $status, $error = null)
    {
        try {
            $this->db->insert('sms_queue', [
                
                'tenant_id' => TenantContext::getId(),
                'recipient' => $phone,
                'message' => substr($message, 0, 500),
                'status' => $status,
                'provider' => $this->provider,
                'attempts' => $status === 'sent' ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
            ]);
        } catch (\Exception $e) {
            error_log("SmsSenderService: Queue log failed: " . $e->getMessage());
        }
    }

    private function cleanPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10) $phone = '91' . $phone;
        return $phone;
    }

    public function getQueueStats()
    {
        $stats = ['pending' => 0, 'sent' => 0, 'failed' => 0];
        try {
            $tid = $this->tenantId();
            $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
            $rows = $this->db->fetchAll("SELECT status, COUNT(*) as cnt FROM sms_queue WHERE 1=1" . $tenantFilter . " GROUP BY status", $tid > 1 ? [$tid] : []);
            foreach ($rows as $r) $stats[$r['status']] = (int)$r['cnt'];
        } catch (\Exception $e) {
                    error_log("SmsSenderService.php: " . $e->getMessage());
        }
        return $stats;
    }

    public function getQueueItems($status = null, $limit = 50, $offset = 0)
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $where = $status ? "WHERE status = ?" : "";
        $params = $status ? [$status] : [];
        if ($tid > 1) $params[] = $tid;
        try {
            return $this->db->fetchAll(
                "SELECT * FROM sms_queue $where" . $tenantFilter . " ORDER BY created_at DESC LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset])
            );
        } catch (\Exception $e) { return []; }
    }
}
