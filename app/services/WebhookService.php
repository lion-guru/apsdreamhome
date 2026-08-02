<?php
namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

class WebhookService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;
    private $maxAttempts = 3;
    private $timeoutSeconds = 10;

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    public function registerEndpoint(string $name, string $url, array $events, ?string $secret = null, ?int $userId = null): int
    {
        $insertData = $this->tenantInsertData();
        $cols = "name, url, secret_key, events, is_active, created_by" . (count($insertData) > 0 ? ', tenant_id' : '');
        $ph = ":n, :u, :s, :e, 1, :uid" . (count($insertData) > 0 ? ', :tid' : '');
        $st = $this->db->prepare("INSERT INTO webhook_endpoints ($cols) VALUES ($ph)");
        $params = [':n' => $name, ':u' => $url, ':s' => $secret, ':e' => implode(',', $events), ':uid' => $userId];
        if (!empty($insertData)) $params = array_merge($params, $insertData);
        $st->execute($params);
        return (int)$this->db->lastInsertId();
    }

    public function listEndpoints(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM webhook_endpoints" . $this->tenantSql();
        if ($activeOnly) $sql .= " WHERE is_active = 1";
        $sql .= " ORDER BY name";
        try {
            $st = $this->db->query($sql);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    public function deleteEndpoint(int $id): bool
    {
        $sql = "DELETE FROM webhook_endpoints WHERE id = :id" . $this->tenantSql();
        $params = [':id' => $id];
        if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
        try {
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function toggleEndpoint(int $id, bool $active): bool
    {
        try {
            $st = $this->db->prepare("UPDATE webhook_endpoints SET is_active = :a WHERE id = :id");
            $st->execute([':a' => $active ? 1 : 0, ':id' => $id]);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function trigger(string $eventType, array $payload): array
    {
        $endpoints = $this->listEndpoints(true);
        $results = [];
        foreach ($endpoints as $ep) {
            $subscribedEvents = array_filter(explode(',', $ep['events'] ?? ''));
            if (!empty($subscribedEvents) && !in_array($eventType, $subscribedEvents) && !in_array('*', $subscribedEvents)) {
                continue;
            }
            $deliveryId = $this->recordDelivery((int)$ep['id'], $eventType, $payload);
            $results[] = ['endpoint' => $ep['name'], 'delivery_id' => $deliveryId, 'result' => 'queued'];
        }
        return ['ok' => true, 'triggered' => count($results), 'results' => $results];
    }

    private function recordDelivery(int $endpointId, string $eventType, array $payload): int
    {
        $insertData = $this->tenantInsertData();
        $cols = "endpoint_id, event_type, payload, status" . (count($insertData) > 0 ? ', tenant_id' : '');
        $ph = ":e, :ev, :p, 'pending'" . (count($insertData) > 0 ? ', :tid' : '');
        $st = $this->db->prepare("INSERT INTO webhook_deliveries ($cols) VALUES ($ph)");
        $params = [':e' => $endpointId, ':ev' => $eventType, ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE)];
        if (!empty($insertData)) $params = array_merge($params, $insertData);
        $st->execute($params);
        return (int)$this->db->lastInsertId();
    }

    public function deliver(int $deliveryId): array
    {
        $sql = "SELECT d.*, e.url, e.secret_key FROM webhook_deliveries d JOIN webhook_endpoints e ON d.endpoint_id = e.id WHERE d.id = :id" . $this->tenantSql();
        $params = [':id' => $deliveryId];
        if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
        $st = $this->db->prepare($sql);
        $st->execute($params);
        $delivery = $st->fetch(PDO::FETCH_ASSOC);
        if (!$delivery) return ['ok' => false, 'error' => 'Delivery not found'];

        $payload = json_decode($delivery['payload'] ?? '{}', true) ?: [];
        $signature = $delivery['secret_key'] ? hash_hmac('sha256', $delivery['payload'] ?? '', $delivery['secret_key']) : null;

        $headers = [
            'Content-Type: application/json',
            'X-Webhook-Event: ' . $delivery['event_type'],
            'X-Webhook-Delivery: ' . $delivery['id'],
            'X-Webhook-Timestamp: ' . time()
        ];
        if ($signature) $headers[] = 'X-Webhook-Signature: ' . $signature;

        $ch = curl_init($delivery['url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $delivery['payload'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $status = ($code >= 200 && $code < 300) ? 'success' : (($delivery['attempt'] ?? 1) < $this->maxAttempts ? 'retrying' : 'failed');
        $error = $err ?: ($status === 'failed' ? "HTTP $code" : null);

        $st = $this->db->prepare("UPDATE webhook_deliveries SET response_code = :c, response_body = :b, status = :s, delivered_at = NOW(), error_message = :e WHERE id = :id" . $this->tenantSql());
        $updParams = [':c' => $code, ':b' => substr((string)$body, 0, 4000), ':s' => $status, ':e' => $error, ':id' => $deliveryId];
        if ($this->tenantId() > 1) $updParams[':stid'] = $this->tenantId();
        $st->execute($updParams);

        return ['ok' => $status === 'success', 'status' => $status, 'code' => $code, 'error' => $error];
    }

    public function processPending(int $maxBatch = 50): array
    {
        $sql = "SELECT id FROM webhook_deliveries WHERE status IN ('pending','retrying')" . $this->tenantSql() . " ORDER BY id ASC LIMIT :lim";
        $st = $this->db->prepare($sql);
        $params = [];
        if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
        $st->bindValue(':lim', $maxBatch, PDO::PARAM_INT);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->execute();
        $ids = $st->fetchAll(PDO::FETCH_COLUMN);
        $results = [];
        foreach ($ids as $id) {
            $r = $this->deliver((int)$id);
            $results[] = ['delivery_id' => (int)$id, 'result' => $r];
        }
        return ['processed' => count($results), 'results' => $results];
    }

    public function getDeliveries(int $endpointId = 0, int $limit = 50, ?string $status = null): array
    {
        $sql = "SELECT d.*, e.name as endpoint_name FROM webhook_deliveries d JOIN webhook_endpoints e ON d.endpoint_id = e.id WHERE 1=1";
        $params = [];
        if ($endpointId) { $sql .= " AND d.endpoint_id = :e"; $params[':e'] = $endpointId; }
        if ($status) { $sql .= " AND d.status = :s"; $params[':s'] = $status; }
        $sql .= " ORDER BY d.id DESC LIMIT :lim";
        try {
            $st = $this->db->prepare($sql);
            foreach ($params as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    public function getStats(int $days = 7): array
    {
        $stats = ['total' => 0, 'success' => 0, 'failed' => 0, 'pending' => 0, 'retrying' => 0, 'by_event' => []];
        try {
            $st = $this->db->prepare("SELECT status, COUNT(*) as cnt FROM webhook_deliveries WHERE created_at >= DATE_SUB(NOW(), INTERVAL :d DAY) GROUP BY status");
            $st->bindValue(':d', $days, PDO::PARAM_INT);
            $st->execute();
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $stats[$r['status']] = (int)$r['cnt'];
                $stats['total'] += (int)$r['cnt'];
            }

            $st = $this->db->prepare("SELECT event_type, COUNT(*) as cnt FROM webhook_deliveries WHERE created_at >= DATE_SUB(NOW(), INTERVAL :d DAY) GROUP BY event_type ORDER BY cnt DESC LIMIT 10");
            $st->bindValue(':d', $days, PDO::PARAM_INT);
            $st->execute();
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $stats['by_event'][$r['event_type']] = (int)$r['cnt'];
            }
        } catch (\Throwable $e) { error_log($e->getMessage()); }
        return $stats;
    }
}
