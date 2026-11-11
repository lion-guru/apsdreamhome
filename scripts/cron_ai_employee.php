<?php
/**
 * cron_ai_employee.php — AI employee worker.
 * Claims up to N pending tasks for the active "Aira" employee.
 * Usage: php scripts/cron_ai_employee.php [--tasks=3] [--dry-run]
 */
require_once dirname(__DIR__) . '/config/bootstrap_cli.php';

use App\Services\AIEmployeeService;

$tasks = 3;
$dryRun = false;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--tasks=')) {
        $tasks = max(1, (int) substr($arg, 8));
    }
    if ($arg === '--dry-run') {
        $dryRun = true;
    }
}

$svc = new AIEmployeeService();
$db = App\Core\Database\Database::getInstance();

// Ecosystem pre-pass: inquiry capture -> lead -> qualifier chain (Aira→Vivaan)
// + due followup drafts (Sahil). Runs before task execution.
$aira = $db->fetch("SELECT * FROM ai_employees WHERE name = 'Aira' AND status = 'active' LIMIT 1");
$vivaan = $db->fetch("SELECT * FROM ai_employees WHERE name = 'Vivaan' AND status = 'active' LIMIT 1");
$sahil = $db->fetch("SELECT * FROM ai_employees WHERE name = 'Sahil' AND status = 'active' LIMIT 1");

if ($aira && $vivaan) {
    $inqs = $db->fetchAll("SELECT * FROM inquiries WHERE is_read = 0 OR status IN ('new','pending') ORDER BY id ASC LIMIT 5") ?: [];
    foreach ($inqs as $q) {
        $leadId = App\Services\Lead\LeadService::wireFromInquiry([
            'name' => $q['name'] ?? '',
            'phone' => $q['phone'] ?? '',
            'email' => $q['email'] ?? '',
            'type' => $q['type'] ?? 'property_inquiry',
            'message' => $q['message'] ?? '',
        ]);
        if ($leadId) {
            $svc->enqueue((int) $vivaan['id'], 'qualify_lead', (int) $leadId, ['from_role' => 'telecaller', 'inquiry_id' => $q['id']]);
            $db->execute("UPDATE inquiries SET is_read = 1, status = 'completed' WHERE id = ?", [$q['id']]);
            echo "inquiry {$q['id']} -> lead $leadId -> Vivaan queued\n";

            // Speed-to-lead SLA: inquiry age at capture
            $waitMin = 0;
            if (!empty($q['created_at'])) {
                $waitMin = round((time() - strtotime($q['created_at'])) / 60, 1);
            }
            $db->execute(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by) VALUES (?, 'note', ?, '0')",
                [$leadId, "[speed-to-lead] Inquiry->lead in {$waitMin} min (SLA 15 min)" . ($waitMin > 15 ? ' — SLA BREACH' : '')]
            );
            if ($waitMin > 15) {
                echo "  SLA breach: {$waitMin} min (>15)\n";
            }
        }
    }
}

if ($sahil) {
    $due = $db->fetchAll("SELECT id, lead_id FROM followups WHERE status = 'pending' AND followup_date <= CURDATE() ORDER BY followup_date ASC LIMIT 3") ?: [];
    foreach ($due as $fu) {
        $svc->enqueue((int) $sahil['id'], 'followup_draft', (int) ($fu['lead_id'] ?? 0), ['followup_id' => $fu['id']]);
        echo "followup {$fu['id']} (lead {$fu['lead_id']}) -> Sahil queued\n";
    }
}

// Zoya EMI nudges (data-ready guard: only overdue WITH user)
$zoya = $db->fetch("SELECT * FROM ai_employees WHERE name = 'Zoya' AND status = 'active' LIMIT 1");
if ($zoya) {
    $overdue = $db->fetchAll("SELECT id, user_id, amount FROM emi_payments WHERE status = 'overdue' AND user_id IS NOT NULL ORDER BY due_date ASC LIMIT 3") ?: [];
    foreach ($overdue as $od) {
        $svc->enqueue((int) $zoya['id'], 'notify_stale', 0, ['emi_payment_id' => $od['id'], 'user_id' => $od['user_id'], 'amount' => $od['amount']]);
        echo "emi overdue {$od['id']} (user {$od['user_id']}, Rs {$od['amount']}) -> Zoya queued\n";
    }
}

$employees = $db->fetchAll("SELECT * FROM ai_employees WHERE status = 'active' ORDER BY id");
if (empty($employees)) {
    echo "No active AI employees — activate via /admin/ai-employee\n";
    exit(0);
}

foreach ($employees as $emp) {
    echo "=== {$emp['name']} ({$emp['role']}) ===\n";
    for ($i = 1; $i <= $tasks; $i++) {
        if ($dryRun) {
            $next = $db->fetch("SELECT id, type, lead_id FROM ai_employee_tasks WHERE employee_id = ? AND status = 'pending' ORDER BY id LIMIT 1", [$emp['id']]);
            if (!$next) {
                echo "dry-run: queue empty\n";
                break;
            }
            echo "dry-run: would run task {$next['id']} ({$next['type']}, lead {$next['lead_id']})\n";
            continue;
        }
        $r = $svc->runNext((int) $emp['id']);
        if (empty($r['success'])) {
            echo "run $i: " . ($r['error'] ?? 'no work') . "\n";
            break;
        }
        echo "task {$r['task_id']} done\n";
    }
}
exit(0);
