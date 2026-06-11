<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminController;

class CspReportController extends AdminController
{
    protected function skipCsrfProtection(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/csp-report') !== false) {
            return true;
        }
        return false;
    }

    public function report()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid JSON']);
            return;
        }

        $reports = isset($payload['csp-report']) ? [$payload['csp-report']] : (isset($payload[0]) ? $payload : [$payload]);
        $saved = 0;

        foreach ($reports as $r) {
            if (!is_array($r)) {
                continue;
            }
            $documentUri = substr((string)($r['document-uri'] ?? ''), 0, 2048);
            $violatedDirective = substr((string)($r['violated-directive'] ?? ''), 0, 255);
            $blockedUri = substr((string)($r['blocked-uri'] ?? ''), 0, 2048);
            $originalPolicy = $r['original-policy'] ?? null;
            $effectiveDirective = substr((string)($r['effective-directive'] ?? ($r['violated-directive'] ?? '')), 0, 255);
            $sourceFile = substr((string)($r['source-file'] ?? ''), 0, 2048);
            $lineNumber = isset($r['line-number']) ? (int)$r['line-number'] : null;
            $columnNumber = isset($r['column-number']) ? (int)$r['column-number'] : null;
            $sample = isset($r['script-sample']) ? substr((string)$r['script-sample'], 0, 1024) : null;
            $disposition = $r['disposition'] ?? null;
            $referrer = $_SERVER['HTTP_REFERER'] ?? null;
            $userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 1024);
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;

            try {
                $this->db->query(
                    "INSERT INTO csp_violations
                     (document_uri, violated_directive, effective_directive, blocked_uri, original_policy,
                      source_file, line_number, column_number, script_sample, disposition,
                      referrer, user_agent, ip, raw_payload, received_at)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())",
                    [
                        $documentUri, $violatedDirective, $effectiveDirective, $blockedUri, $originalPolicy,
                        $sourceFile, $lineNumber, $columnNumber, $sample, $disposition,
                        $referrer, $userAgent, $ip, $raw
                    ]
                );
                $saved++;
            } catch (\Throwable $e) {
                error_log('[CSP] insert failed: ' . $e->getMessage());
            }
        }

        http_response_code(204);
        header('Content-Type: application/json');
        echo json_encode(['saved' => $saved]);
    }

    public function list($request = null)
    {
        $this->requireAdmin();
        $limit = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 50;
        $rows = $this->db->fetchAll(
            "SELECT id, document_uri, violated_directive, blocked_uri, source_file, line_number, ip, received_at
             FROM csp_violations
             ORDER BY id DESC
             LIMIT $limit"
        ) ?: [];
        $stats = $this->db->fetchOne(
            "SELECT COUNT(*) AS total,
                    COUNT(DISTINCT violated_directive) AS directives,
                    COUNT(DISTINCT ip) AS unique_ips
             FROM csp_violations"
        ) ?: ['total' => 0, 'directives' => 0, 'unique_ips' => 0];
        $this->render('admin/csp_violations', [
            'rows' => $rows,
            'stats' => $stats,
            'limit' => $limit,
        ]);
    }
}
