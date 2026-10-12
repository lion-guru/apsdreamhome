<?php
/**
 * Cron: 3-stage WhatsApp drip for attended-but-unconverted site visits.
 *  Stage 1 (+1 day, Mon): thank-you + layout plan link.
 *  Stage 2 (+3 days, Wed): social proof + 24h hold nudge.
 *  Stage 3 (+6 days, Sat): Sunday spot-booking discount invite.
 * Idempotent: sent/skipped stages are never retried; converted leads skip.
 * Usage: php scripts/cron_site_visit_drip.php [--dry-run]
 */
require_once __DIR__ . '/../config/bootstrap.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);
$db = \App\Core\Database\Database::getInstance();

$stages = [
    1 => ['offset' => 1, 'text' => "Site visit ke liye dhanyavaad! Colony layout plan: %s Block %s. Koi sawal ho to reply karein. - APS Dream Home"],
    2 => ['offset' => 3, 'text' => "Aapke dekhe block me 2 nayi inquiries aayi hain. 24h free hold lagayein: %s - APS Dream Home"],
    3 => ['offset' => 6, 'text' => "Is Sunday spot booking par vishesh chhoot! Free cab ke liye reply YES karein: %s - APS Dream Home"],
];

$base = defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome';
$sent = $skipped = $failed = 0;

try {
    // Completed visits in the last 14 days whose lead never converted.
    $visits = $db->fetchAll(
        "SELECT v.id, v.visit_date, v.visitor_name, v.visitor_phone, v.colony_id,
                c.name AS colony_name, c.slug AS colony_slug, v.lead_id,
                l.status AS lead_status
         FROM site_visits v
         LEFT JOIN colonies c ON c.id = v.colony_id
         LEFT JOIN leads l ON l.id = v.lead_id
         WHERE v.status = 'completed' AND v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)"
    ) ?: [];

    $wa = new \App\Services\Communication\WhatsAppService();
    $waReady = $wa->isConfigured();

    foreach ($visits as $v) {
        $vid = (int)$v['id'];
        if (in_array($v['lead_status'] ?? '', ['converted', 'closed_won'], true)) {
            continue; // booked already — no drip
        }
        $phone = preg_replace('/\D/', '', (string)($v['visitor_phone'] ?? ''));
        foreach ($stages as $stage => $cfg) {
            $due = date('Y-m-d', strtotime($v['visit_date'] . ' +' . $cfg['offset'] . ' days'));
            if ($due > date('Y-m-d')) {
                continue; // not due yet
            }
            $row = $db->fetchOne(
                "SELECT id, status FROM site_visit_drips WHERE visit_id = ? AND stage = ? LIMIT 1",
                [$vid, $stage]
            );
            if ($row && $row['status'] !== 'pending') {
                continue; // already handled
            }
            if (!$row) {
                $db->execute(
                    "INSERT INTO site_visit_drips (tenant_id, visit_id, stage, due_date, status) VALUES (1, ?, ?, ?, 'pending')",
                    [$vid, $stage, $due]
                );
                $row = ['id' => (int)$db->fetchOne("SELECT id FROM site_visit_drips WHERE visit_id = ? AND stage = ? LIMIT 1", [$vid, $stage])['id'], 'status' => 'pending'];
            }
            if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
                $db->execute("UPDATE site_visit_drips SET status = 'skipped', provider_response = 'bad-phone' WHERE id = ? LIMIT 1", [$row['id']]);
                $skipped++;
                continue;
            }
            $layoutUrl = $base . '/colony/' . ($v['colony_id'] ?? 0) . '/live-layout';
            $text = sprintf($cfg['text'], $v['colony_name'] ?? '', $layoutUrl);
            if ($dryRun) {
                echo "DRY stage=$stage visit=$vid phone=$phone\n";
                continue;
            }
            $ok = false;
            $resp = 'wa-unconfigured';
            if ($waReady) {
                try {
                    $res = $wa->sendTextMessage($phone, $text);
                    $ok = (bool)($res['success'] ?? false);
                    $resp = substr(json_encode($res), 0, 400);
                } catch (\Throwable $e) {
                    $resp = substr($e->getMessage(), 0, 400);
                }
            }
            $db->execute(
                "UPDATE site_visit_drips SET status = ?, sent_at = NOW(), provider_response = ? WHERE id = ? LIMIT 1",
                [$ok ? 'sent' : 'failed', $resp, $row['id']]
            );
            if ($ok) $sent++; else $failed++;
        }
    }
} catch (\Throwable $e) {
    error_log('[cron_site_visit_drip] ' . $e->getMessage());
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo ($dryRun ? 'dry-run ' : '') . "sent=$sent failed=$failed skipped=$skipped\n";
