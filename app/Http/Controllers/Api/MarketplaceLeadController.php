<?php
/**
 * Marketplace Lead Webhook — IndiaMart / JustDial / TradeIndia / Meta
 * Receives push leads from B2B marketplaces and creates CRM leads.
 *
 * Setup: Admin -> Service Configs (group `marketplace`) -> set `webhook_key`,
 * then paste this URL in the marketplace seller panel:
 *   POST {BASE_URL}/api/v2/leads/marketplace/{indiamart|justdial|tradeindia|meta}
 * with header `X-Marketplace-Key: <webhook_key>`.
 * Accepts JSON body, form-encoded POST, or query params (all merged).
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class MarketplaceLeadController extends BaseController
{
    use TenantAwareTrait;

    private const ALLOWED = ['indiamart', 'justdial', 'tradeindia', 'meta'];

    protected function skipCsrfProtection(): bool
    {
        // Server-to-server webhook; authenticated via shared secret header.
        // (Also covered by the router-level `/api/` CSRF exclusion.)
        return true;
    }

    /**
     * POST /api/v2/leads/marketplace/{source}
     */
    public function webhook($source = '')
    {
        $source = strtolower(trim((string)$source));
        if (!in_array($source, self::ALLOWED, true)) {
            $this->respond(['success' => false, 'error' => 'Unknown source. Use: ' . implode(',', self::ALLOWED)], 404);
        }

        // Shared-secret gate (enforced only when a key is configured)
        try {
            $needKey = trim((string)\App\Services\ServiceConfigService::getInstance()->get('marketplace', 'webhook_key', ''));
        } catch (\Throwable $e) {
            error_log('MarketplaceLeadController config: ' . $e->getMessage());
            $needKey = '';
        }
        if ($needKey !== '') {
            // Check multiple possible header locations (Apache may strip custom headers)
            $got = $_SERVER['HTTP_X_MARKETPLACE_KEY']
                ?? $_SERVER['HTTP_X_API_KEY']
                ?? $_SERVER['HTTP_AUTHORIZATION']
                ?? '';
            // Also check query param as fallback
            if ($got === '') {
                $got = $_GET['webhook_key'] ?? $_GET['key'] ?? '';
            }
            // Strip "Bearer " prefix if present
            if (str_starts_with($got, 'Bearer ')) {
                $got = substr($got, 7);
            }
            if ($got === '' || !hash_equals($needKey, (string)$got)) {
                $this->respond(['success' => false, 'error' => 'Invalid webhook key'], 401);
            }
        }

        $input = $this->readInput();
        $lead = $this->normalize($source, $input);

        if ($lead['phone'] === '' && $lead['email'] === '') {
            $this->respond(['success' => false, 'error' => 'Phone or email is required'], 422);
        }
        if ($lead['name'] === '') {
            $lead['name'] = 'Marketplace Lead';
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tid = (int)$this->tenantId();
            $tAnd = $tid > 1 ? ' AND tenant_id = ?' : '';

            // Idempotency: marketplaces retry pushes; same phone/email => same lead
            if ($lead['phone'] !== '') {
                $p = [$lead['phone']];
                if ($tid > 1) $p[] = $tid;
                $dup = $db->fetchOne("SELECT id, lead_number FROM leads WHERE phone = ?{$tAnd} ORDER BY id DESC LIMIT 1", $p);
                if ($dup) $this->respond(['success' => true, 'status' => 'duplicate', 'lead_id' => (int)$dup['id'], 'lead_number' => $dup['lead_number'] ?? null]);
            }
            if ($lead['email'] !== '') {
                $p = [$lead['email']];
                if ($tid > 1) $p[] = $tid;
                $dup = $db->fetchOne("SELECT id, lead_number FROM leads WHERE email = ?{$tAnd} ORDER BY id DESC LIMIT 1", $p);
                if ($dup) $this->respond(['success' => true, 'status' => 'duplicate', 'lead_id' => (int)$dup['id'], 'lead_number' => $dup['lead_number'] ?? null]);
            }

            $sourceId = $this->resolveSourceId($db, $source, $tid);
            $leadNumber = 'MKT-' . strtoupper($source[0]) . strtoupper(substr(uniqid(), -7));

            $cols = 'lead_number, name, phone, email, source, source_detail, source_id, city, budget_range, notes, message, status, priority, lead_score, created_at, updated_at';
            $vals = [$leadNumber, $lead['name'], $lead['phone'], $lead['email'], $source, $lead['source_detail'], $sourceId, $lead['city'], $lead['budget'], $lead['message'], $lead['message'], 'new', 'medium', 0, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')];
            $ph = implode(',', array_fill(0, count($vals), '?'));
            if ($tid > 1) { $cols .= ', tenant_id'; $ph .= ',?'; $vals[] = $tid; }
            $db->execute("INSERT INTO leads ({$cols}) VALUES ({$ph})", $vals);
            $leadId = (int)$db->lastInsertId();

            // Activity trail (same 5-col contract as LeadImportController)
            try {
                $db->execute(
                    "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at) VALUES (?, 'imported', ?, NULL, NOW())",
                    [$leadId, 'Lead pushed from ' . $source . ($lead['source_detail'] !== '' ? ': ' . mb_substr($lead['source_detail'], 0, 150) : '')]
                );
            } catch (\Throwable $e) {
                error_log('MarketplaceLeadController activity: ' . $e->getMessage());
            }

            // Auto-score (non-critical)
            try {
                $scorer = new \App\Services\CRM\LeadScoringService();
                $scorer->calculateScore($leadId);
            } catch (\Throwable $e) {
                error_log('MarketplaceLeadController score: ' . $e->getMessage());
            }

            $this->respond(['success' => true, 'status' => 'created', 'lead_id' => $leadId, 'lead_number' => $leadNumber], 201);
        } catch (\Throwable $e) {
            error_log('MarketplaceLeadController::webhook: ' . $e->getMessage());
            $this->respond(['success' => false, 'error' => 'Failed to save lead'], 500);
        }
    }

    /**
     * GET /api/v2/leads/marketplace/docs — human-readable integration help
     */
    public function docs()
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $this->respond([
            'success' => true,
            'sources' => self::ALLOWED,
            'method' => 'POST',
            'urls' => array_map(fn($s) => $base . '/api/v2/leads/marketplace/' . $s, self::ALLOWED),
            'auth' => 'Header X-Marketplace-Key must match service_configs marketplace.webhook_key (enforced only when set)',
            'accepts' => 'JSON body, form-encoded POST, or query params',
            'fields' => ['name (or sender_name/full_name)', 'phone (or mobile)', 'email (or email_id)', 'city', 'message (or requirement/query)', 'budget'],
        ]);
    }

    private function readInput(): array
    {
        $out = [];
        if (is_array($_GET)) $out = array_merge($out, $_GET);
        if (is_array($_POST)) $out = array_merge($out, $_POST);
        try {
            if (method_exists($this, 'getJsonInput')) {
                $j = $this->getJsonInput();
                if (is_array($j)) $out = array_merge($out, $j);
            } else {
                $raw = file_get_contents('php://input');
                if (is_string($raw) && $raw !== '') {
                    $j = json_decode($raw, true);
                    if (is_array($j)) $out = array_merge($out, $j);
                }
            }
        } catch (\Throwable $e) {
            // php://input may already be consumed upstream; $_POST/$_GET still apply
        }
        // Flatten one level of nested `data`/`lead` wrappers (some panels nest payloads)
        foreach (['data', 'lead', 'payload'] as $wrap) {
            if (isset($out[$wrap]) && is_array($out[$wrap])) {
                $out = array_merge($out[$wrap], $out);
            }
        }
        return $out;
    }

    private function normalize(string $source, array $input): array
    {
        $flat = [];
        foreach ($input as $k => $v) {
            if (is_scalar($v)) {
                $nk = strtolower(preg_replace('/[^a-z0-9]/i', '', (string)$k));
                if ($nk !== '' && !isset($flat[$nk])) $flat[$nk] = trim((string)$v);
            }
        }
        // Debug: write to temp file
        file_put_contents('C:\xampp\htdocs\apsdreamhome\logs\mkt_debug.log', date('H:i:s') . " flat keys: " . json_encode(array_keys($flat)) . "\n", FILE_APPEND);
        file_put_contents('C:\xampp\htdocs\apsdreamhome\logs\mkt_debug.log', date('H:i:s') . " input keys: " . json_encode(array_keys($input)) . "\n", FILE_APPEND);
        $pick = function (array $keys) use ($flat): string {
            foreach ($keys as $k) {
                if (isset($flat[$k]) && $flat[$k] !== '') return (string)$flat[$k];
            }
            return '';
        };
        $message = $pick(['requirement', 'buyrequirement', 'query', 'message', 'enquiry', 'description', 'comment', 'remarks', 'productname', 'subject']);
        $detail = $source . ($message !== '' ? ': ' . mb_substr($message, 0, 180) : '');
        return [
            'name'          => $pick(['sendername', 'fullname', 'name', 'queryname', 'customername', 'contactperson', 'firstname']),
            'phone'         => preg_replace('/[^0-9+]/', '', $pick(['mobile', 'mobileno', 'phone', 'phonenumber', 'contact', 'contactno', 'telephone'])),
            'email'         => $pick(['email', 'emailid', 'emailaddress', 'mail']),
            'city'          => $pick(['city', 'location', 'district', 'town']),
            'budget'        => $pick(['budget', 'budgetrange', 'quantity', 'ordervalue']),
            'message'       => $message,
            'source_detail' => mb_substr($detail, 0, 200),
        ];
    }

    private function resolveSourceId($db, string $source, int $tid): ?int
    {
        try {
            $row = $db->fetchOne("SELECT id FROM lead_sources WHERE LOWER(name) = ? LIMIT 1", [$source]);
            if ($row) return (int)$row['id'];
            $like = $db->fetchOne("SELECT id FROM lead_sources WHERE LOWER(name) LIKE ? LIMIT 1", ['%' . $source . '%']);
            if ($like) return (int)$like['id'];
        } catch (\Throwable $e) {
            error_log('MarketplaceLeadController sourceId: ' . $e->getMessage());
        }
        return null;
    }

    private function respond(array $data, int $status = 200): void
    {
        http_response_code($status);
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
