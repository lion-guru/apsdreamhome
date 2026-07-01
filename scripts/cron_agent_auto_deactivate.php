<?php
/**
 * Agent Auto-Deactivate Cron
 * 
 * Runs weekly to deactivate agents who haven't logged in for 90 days
 * or haven't made any sales/leads in 90 days.
 * 
 * Usage: php scripts/cron_agent_auto_deactivate.php
 * Dry run: php scripts/cron_agent_auto_deactivate.php --dry-run
 */

require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();
$dryRun = in_array('--dry-run', $argv ?? []);
$threshold = date('Y-m-d', strtotime('-90 days'));
$deactivated = 0;
$errors = [];

echo "=== Agent Auto-Deactivate Cron ===\n";
echo "Threshold: 90 days (before $threshold)\n";
echo "Mode: " . ($dryRun ? "DRY RUN (no changes)" : "LIVE") . "\n\n";

try {
    // Find agents who are active but inactive for 90+ days
    $agents = $db->fetchAll(
        "SELECT u.id, u.name, u.email, u.customer_id, u.last_login, u.created_at,
                (SELECT MAX(created_at) FROM inquiries WHERE posted_by = u.id AND posted_by_type = 'agent') as last_lead,
                (SELECT MAX(created_at) FROM commissions WHERE associate_id = u.id) as last_commission,
                (SELECT COUNT(*) FROM inquiries WHERE posted_by = u.id AND posted_by_type = 'agent') as total_leads,
                (SELECT COUNT(*) FROM commissions WHERE associate_id = u.id) as total_commissions
         FROM users u
         WHERE u.role = 'agent' 
           AND u.status = 'active'
           AND u.registration_status = 'approved'
         ORDER BY u.last_login ASC NULLS FIRST"
    );

    echo "Found " . count($agents) . " active agent(s).\n\n";

    foreach ($agents as $agent) {
        // Determine last activity date
        $lastActivity = max(
            $agent['last_login'] ?? '2000-01-01',
            $agent['last_lead'] ?? '2000-01-01',
            $agent['last_commission'] ?? '2000-01-01'
        );

        $daysInactive = (int)((time() - strtotime($lastActivity)) / 86400);

        if ($daysInactive >= 90) {
            $reason = "Inactive for {$daysInactive} days (last activity: $lastActivity)";
            echo "  🔸 #{$agent['id']} {$agent['name']} ({$agent['email']}) — $reason\n";

            if (!$dryRun) {
                try {
                    $db->query(
                        "UPDATE users SET status = 'inactive', updated_at = NOW(), notes = CONCAT(COALESCE(notes, ''), '\n[Auto-Deactivate] $reason') WHERE id = ?",
                        [$agent['id']]
                    );

                    // Log the deactivation
                    $db->insert('activity_logs_unified', [
                        'user_id' => $agent['id'],
                        'user_type' => 'agent',
                        'action' => "Auto-deactivated: $reason",
                        'ip_address' => '127.0.0.1',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    echo "    → DEACTIVATED\n";
                } catch (\Throwable $e) {
                    $errors[] = "Agent #{$agent['id']}: " . $e->getMessage();
                    echo "    → ERROR: " . $e->getMessage() . "\n";
                }
            } else {
                echo "    → Would deactivate (dry run)\n";
            }
            $deactivated++;
        }
    }

} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    $errors[] = $e->getMessage();
}

echo "\n=== Summary ===\n";
echo "Agents to deactivate: $deactivated\n";
echo "Errors: " . count($errors) . "\n";
