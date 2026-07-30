<?php
namespace App\Services;

use PDO;

class WebhookService
{
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
        $st = $this->db->prepare("INSERT INTO webhook_endpoints (name, url, secret_key, events, is_active, created_by) VALUES (:n, :u, :s, :e, 1, :uid)");
        $st->execute([
            ':n' => $name,
            ':u' => $url,
            ':s' => $secret,
            ':e' => implode(',', $events),
            ':uid' => $userId
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function listEndpoints(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM webhook_endpoints";
        if ($activeOnly) $sql .= " WHERE is_active = 1";
        $sql .= " ORDER BY name";
        try {
            $st = $this->db->query($sql);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    public function deleteEndpoint(int $id): bool
    {
        try {
            $st = $this->db->prepare("DELETE FROM webhook_endpoints WHERE id = :id");
            $st->execute([':id' => $id]);
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
        $st = $this->db->prepare("INSERT INTO webhook_deliveries (endpoint_id, event_type, payload, status) VALUES (:e, :ev, :p, 'pending')");
        $st->execute([
            ':e' => $endpointId,
            ':ev' => $eventType,
            ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deliver(int $deliveryId): array
    {
        $st = $this->db->prepare("SELECT d.*, e.url, e.secret_key FROM webhook_deliveries d JOIN webhook_endpoints e ON d.endpoint_id = e.id WHERE d.id = :id");
        $st->execute([':id' => $deliveryId]);
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

        $st = $this->db->prepare("UPDATE webhook_deliveries SET response_code = :c, response_body = :b, status = :s, delivered_at = NOW(), error_message = :e WHERE id = :id");
        $st->execute([':c' => $code, ':b' => substr((string)$body, 0, 4000), ':s' => $status, ':e' => $error, ':id' => $deliveryId]);

        return ['ok' => $status === 'success', 'status' => $status, 'code' => $code, 'error' => $error];
    }

    public function processPending(int $maxBatch = 50): array
    {
        $st = $this->db->prepare("SELECT id FROM webhook_deliveries WHERE status IN ('pending','retrying') ORDER BY id ASC LIMIT :lim");
        $st->bindValue(':lim', $maxBatch, PDO::PARAM_INT);
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
