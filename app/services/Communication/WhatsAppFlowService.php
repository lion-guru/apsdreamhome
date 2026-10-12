<?php

namespace App\Services\Communication;

use App\Core\Database\Database;

/**
 * Dynamic WhatsApp Chatbot Flow & Conversation Logging Service
 */
class WhatsAppFlowService
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Match inbound text against active database flows in priority order.
     */
    public function matchFlow(string $text): ?array
    {
        $clean = mb_strtolower(trim($text));
        if ($clean === '') {
            return null;
        }

        try {
            $st = $this->pdo->query(
                "SELECT * FROM whatsapp_flows
                 WHERE is_active = 1
                 ORDER BY priority ASC, id ASC"
            );
            $flows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($flows as $flow) {
                $type = $flow['match_type'] ?? 'contains';
                $keywords = array_map('trim', explode(',', mb_strtolower($flow['trigger_keyword'])));

                if ($type === 'exact') {
                    if (in_array($clean, $keywords, true)) {
                        return $flow;
                    }
                } elseif ($type === 'regex') {
                    $pattern = '/' . trim($flow['trigger_keyword'], '/') . '/iu';
                    if (@preg_match($pattern, $clean)) {
                        return $flow;
                    }
                } else {
                    // contains with word-boundary or substring check
                    foreach ($keywords as $kw) {
                        if ($kw === '') continue;
                        $escaped = preg_quote($kw, '/');
                        if (preg_match("/\\b{$escaped}\\b/iu", $clean)) {
                            return $flow;
                        }
                        if (mb_strlen($kw) > 2 && str_contains($clean, $kw)) {
                            return $flow;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[WhatsAppFlowService::matchFlow] ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Format and execute action hooks for a matched flow.
     */
    public function formatReply(array $flow, string $from, string $text): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $body = (string)($flow['reply_message'] ?? '');
        $body = str_replace('{{base_url}}', $base, $body);

        $hook = $flow['action_hook'] ?? null;
        $digits = preg_replace('/\D/', '', $from);

        if ($hook === 'fetch_plots') {
            $colonyList = $this->getAvailableTownshipsText();
            if ($colonyList !== '') {
                $body .= "\n\n*Featured Townships:*\n" . $colonyList;
            }
        } elseif ($hook === 'fetch_passbook') {
            $passbookInfo = $this->lookupCustomerBookingInfo($digits);
            if ($passbookInfo !== '') {
                $body .= "\n\n" . $passbookInfo;
            }
        }

        return $body;
    }

    /**
     * Log an inbound or outbound message to whatsapp_message_logs.
     */
    public function logMessage(string $phone, string $direction, string $body, ?string $flowMatched = null, string $status = 'delivered'): void
    {
        try {
            $dir = in_array($direction, ['inbound', 'outbound'], true) ? $direction : 'inbound';
            $validStatus = in_array($status, ['sent', 'delivered', 'read', 'failed'], true) ? $status : ($dir === 'inbound' ? 'delivered' : 'sent');
            $st = $this->pdo->prepare(
                "INSERT INTO whatsapp_messages (tenant_id, phone_number, direction, message, message_type, flow_matched, status, created_at)
                 VALUES (1, ?, ?, ?, 'text', ?, ?, NOW())"
            );
            $st->execute([$phone, $dir, $body, $flowMatched, $validStatus]);
        } catch (\Throwable $e) {
            error_log('[WhatsAppFlowService::logMessage] ' . $e->getMessage());
        }
    }

    /**
     * Fetch recent conversation logs for admin live radar.
     */
    public function getRecentLogs(int $limit = 50): array
    {
        try {
            $st = $this->pdo->prepare(
                "SELECT id, tenant_id, phone_number, direction, message AS message_body, flow_matched, status, created_at
                 FROM whatsapp_messages
                 ORDER BY created_at DESC, id DESC
                 LIMIT ?"
            );
            $st->bindValue(1, $limit, \PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Fetch all configured chatbot flows.
     */
    public function getAllFlows(): array
    {
        try {
            return $this->pdo->query("SELECT * FROM whatsapp_flows ORDER BY priority ASC, id ASC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Create or update a chatbot flow.
     */
    public function saveFlow(array $data): int
    {
        $id = (int)($data['id'] ?? 0);
        $title = trim((string)($data['title'] ?? ''));
        $keywords = trim((string)($data['trigger_keyword'] ?? ''));
        $matchType = in_array($data['match_type'] ?? '', ['exact', 'contains', 'regex'], true) ? $data['match_type'] : 'contains';
        $reply = trim((string)($data['reply_message'] ?? ''));
        $hook = trim((string)($data['action_hook'] ?? '')) ?: null;
        $priority = max(1, (int)($data['priority'] ?? 10));

        if ($id > 0) {
            $st = $this->pdo->prepare(
                "UPDATE whatsapp_flows
                 SET title = ?, trigger_keyword = ?, match_type = ?, action_hook = ?, reply_message = ?, priority = ?, updated_at = NOW()
                 WHERE id = ?"
            );
            $st->execute([$title, $keywords, $matchType, $hook, $reply, $priority, $id]);
            return $id;
        }

        $st = $this->pdo->prepare(
            "INSERT INTO whatsapp_flows (tenant_id, title, trigger_keyword, match_type, action_hook, reply_message, priority, is_active)
             VALUES (1, ?, ?, ?, ?, ?, ?, 1)"
        );
        $st->execute([$title, $keywords, $matchType, $hook, $reply, $priority]);
        return (int)$this->pdo->lastInsertId();
    }

    public function toggleFlow(int $id): bool
    {
        try {
            $this->pdo->prepare("UPDATE whatsapp_flows SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function deleteFlow(int $id): bool
    {
        try {
            $this->pdo->prepare("DELETE FROM whatsapp_flows WHERE id = ?")->execute([$id]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getAvailableTownshipsText(): string
    {
        try {
            $rows = $this->pdo->query(
                "SELECT name, min_price_per_sqft, available_plots
                 FROM colonies WHERE is_active = 1 AND available_plots > 0
                 ORDER BY available_plots DESC LIMIT 3"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $out = [];
            foreach ($rows as $r) {
                $rate = (float)($r['min_price_per_sqft'] ?? 0);
                $out[] = "• " . $r['name'] . ($rate > 0 ? " (from ₹" . number_format($rate) . "/sq.ft)" : "") . " — " . (int)$r['available_plots'] . " vacant";
            }
            return implode("\n", $out);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function lookupCustomerBookingInfo(string $phoneDigits): string
    {
        if (strlen($phoneDigits) < 10) return '';
        $like = '%' . substr($phoneDigits, -10);

        try {
            $booking = $this->pdo->prepare(
                "SELECT b.booking_number, p.plot_number, c.name AS colony_name
                 FROM plot_bookings b
                 JOIN users u ON u.id = b.customer_id
                 JOIN plots p ON p.id = b.plot_id
                 JOIN colonies c ON c.id = p.colony_id
                 WHERE (u.phone LIKE ? OR u.whatsapp LIKE ?) AND b.status != 'cancelled'
                 ORDER BY b.id DESC LIMIT 1"
            );
            $booking->execute([$like, $like]);
            $b = $booking->fetch(\PDO::FETCH_ASSOC);

            if ($b) {
                return "*Found Linked Booking:* Plot #" . $b['plot_number'] . " in " . $b['colony_name'] . " (Ref: " . $b['booking_number'] . ")";
            }
        } catch (\Throwable $e) {
            // Ignore
        }
        return '';
    }
}
