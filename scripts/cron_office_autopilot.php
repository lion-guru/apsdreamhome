<?php
/**
 * cron_office_autopilot.php — FULL OFFICE AUTOPILOT (single runner).
 *
 * Runs in order:
 *  1. WA intake: unhandled inbound (no linked inquiry) -> inquiry -> lead -> Vivaan qualify
 *  2. Employees: 3 tasks each, all ACTIVE AI employees (chain + followups)
 *  3. Deal ping: prob 0.8+ leads -> Vivaan closing-wave note
 *  4. CEO digest: Ash report -> admin notification (if Ash active)
 *  5. Escalation: failed tasks >= 3 -> admin notification
 *
 * Usage: php scripts/cron_office_autopilot.php [--dry-run]
 */
require_once dirname(__DIR__) . '/config/bootstrap_cli.php';

use App\Services\AIEmployeeService;

$dryRun = in_array('--dry-run', $argv ?? [], true);
$db = App\Core\Database\Database::getInstance();
$svc = new AIEmployeeService();
$done = [];

if ($dryRun) {
    echo "DRY-RUN mode — no writes\n";
}

// ---- 1. WhatsApp intake -------------------------------------------------
$aira = $db->fetch("SELECT * FROM ai_employees WHERE name = 'Aira' AND status = 'active' LIMIT 1");
$vivaan = $db->fetch("SELECT * FROM ai_employees WHERE name = 'Vivaan' AND status = 'active' LIMIT 1");
if ($aira && $vivaan) {
    $msgs = $db->fetchAll("
        SELECT * FROM whatsapp_messages
        WHERE direction = 'inbound'
          AND NOT EXISTS (SELECT 1 FROM inquiries i WHERE i.phone = whatsapp_messages.contact_phone OR i.phone = whatsapp_messages.phone_number)
        ORDER BY id ASC LIMIT 5
    ") ?: [];
    foreach ($msgs as $m) {
        if ($dryRun) {
            echo "dry-run: WA {$m['id']} -> inquiry\n";
            continue;
        }
        $phone = $m['contact_phone'] ?: $m['phone_number'];
        $db->execute(
            "INSERT INTO inquiries (name, phone, email, message, type, status, is_read, created_at) VALUES (?, ?, '', ?, 'general', 'completed', 1, NOW())",
            [$m['contact_name'] ?? 'WA User', $phone, $m['message']]
        );
        $leadId = App\Services\Lead\LeadService::wireFromInquiry([
            'name' => $m['contact_name'] ?? 'WA User',
            'phone' => $phone,
            'type' => 'whatsapp',
            'message' => $m['message'],
        ]);
        if ($leadId) {
            $svc->enqueue((int) $vivaan['id'], 'qualify_lead', (int) $leadId, ['from_role' => 'whatsapp_intake']);
            echo "WA {$m['id']} -> lead $leadId -> Vivaan queued\n";
            $done[] = 'wa_intake';
        }
    }
}

// ---- 2. Scaling gate: queue pressure auto-activates standby ops staff ----
// If pending queue > 50, activate paused qualifier/followup staff (capacity scale).
$pendingTotal = (int) ($db->fetchOne("SELECT COUNT(*) as n FROM ai_employee_tasks WHERE status = 'pending'") ?: 0);
if ($pendingTotal > 50) {
    $standby = $db->fetch("SELECT * FROM ai_employees WHERE status = 'paused' AND role IN ('qualifier', 'followup') ORDER BY id LIMIT 1");
    if ($standby) {
        if ($dryRun) {
            echo "dry-run: scaling gate would activate {$standby['name']} (pending $pendingTotal > 50)\n";
        } else {
            $svc->setStatus((int) $standby['id'], 'active');
            echo "scaling gate: activated {$standby['name']} (pending $pendingTotal > 50)\n";
            $done[] = 'scaled_up';
        }
    }
}

// ---- 3. Employees --------------------------------------------------------
if (!empty($dryRun)) {
    $active = $db->fetchAll("SELECT name, role FROM ai_employees WHERE status = 'active' ORDER BY id") ?: [];
    foreach ($active as $a) {
        echo "dry-run: {$a['name']} ({$a['role']}) would run 3\n";
    }
} else {
    $active = $db->fetchAll("SELECT * FROM ai_employees WHERE status = 'active' ORDER BY id") ?: [];
    foreach ($active as $emp) {
        echo "=== {$emp['name']} ({$emp['role']}) ===\n";
        for ($i = 1; $i <= 3; $i++) {
            $r = $svc->runNext((int) $emp['id']);
            if (empty($r['success'])) break;
            echo "  task {$r['task_id']} done\n";
            $done[] = 'task';
        }
    }
}

// ---- 3. Deal-closeness ping ----------------------------------------------
$hotLead = $db->fetch("
    SELECT l.id, l.name, l.status
    FROM leads l
    LEFT JOIN lead_scores ls ON ls.id = (SELECT id FROM lead_scores WHERE lead_id = l.id ORDER BY id DESC LIMIT 1)
    WHERE l.deleted_at IS NULL AND l.status IN ('proposal', 'negotiation')
      AND COALESCE(ls.score, 0) >= 0
    ORDER BY COALESCE(ls.score, 0) DESC
    LIMIT 1
");
if ($hotLead && $vivaan && !$dryRun) {
    $svc->enqueue((int) $vivaan['id'], 'qualify_lead', (int) $hotLead['id'], ['from_role' => 'deal_ping']);
    echo "deal ping: lead {$hotLead['id']} ({$hotLead['name']}) -> Vivaan closing note\n";
    $done[] = 'deal_ping';
}

// ---- 4. CEO digest --------------------------------------------------------
$ash = $db->fetch("SELECT * FROM ai_employees WHERE name = 'Ash' LIMIT 1");
$digest = [
    'date' => date('Y-m-d'),
    'plots' => (int) (($db->fetchOne("SELECT COUNT(*) as n FROM plots")['n'] ?? 0)),
    'leads_live' => (int) (($db->fetchOne("SELECT COUNT(*) as n FROM leads WHERE deleted_at IS NULL")['n'] ?? 0)),
    'open_followups' => (int) (($db->fetchOne("SELECT COUNT(*) as n FROM followups WHERE status = 'pending'")['n'] ?? 0)),
    'pending_tasks' => (int) (($db->fetchOne("SELECT COUNT(*) as n FROM ai_employee_tasks WHERE status = 'pending'")['n'] ?? 0)),
    'collected_today' => (float) (($db->fetchOne("SELECT COALESCE(SUM(amount),0) as s FROM payments WHERE DATE(created_at) = CURDATE()")['s'] ?? 0)),
];
echo "CEO digest: " . json_encode($digest) . "\n";
if (!$dryRun) {
    // Morning brief goes to admins daily (Ash active ho ya nahi)
    $notif = new App\Services\NotificationService();
    $notif->notify('system', "AI Morning Brief: {$digest['leads_live']} live leads, {$digest['pending_tasks']} AI tasks pending, Rs {$digest['collected_today']} collected today.", 1, '/admin/ai-employee', 'AI Morning Brief');
    $done[] = 'digest';
}

// ---- 5. Escalation ---------------------------------------------------------
$failed = (int) ($db->fetchOne("SELECT COUNT(*) as n FROM ai_employee_tasks WHERE status = 'failed'") ?: 0);
if ($failed >= 3 && !$dryRun) {
    $notif = new App\Services\NotificationService();
    $notif->notify('system', "$failed AI employee tasks failed — review queue", 1, '/admin/ai-employee', 'AI Employees Escalation');
    echo "escalation: $failed failed tasks -> admin notified\n";
    $done[] = 'escalated';
}

echo "Autopilot complete (" . count($done) . " actions" . ($dryRun ? ", dry-run" : "") . ")\n";
exit(0);
