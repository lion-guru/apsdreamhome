<?php
/**
 * AI Auto-Dialer Cron Job
 * Processes scheduled calls every 2 minutes during working hours (9AM-8PM)
 * Usage: php scripts/cron_ai_autodialer.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/bootstrap.php';

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

echo "[" . date('Y-m-d H:i:s') . "] Starting AI Auto-Dialer...\n";

try {
    // Check working hours (9 AM - 8 PM IST)
    $hour = (int)date('H');
    if ($hour < 9 || $hour >= 20) {
        echo "  ⏰ Outside working hours (9AM-8PM), skipping.\n";
        exit(0);
    }
    
    TenantContext::setById(1);
    $db = Database::getInstance();
    
    // Get pending scheduled calls for now
    $now = date('Y-m-d H:i:s');
    $today = date('Y-m-d');
    $currentTime = date('H:i:s');
    
    $pendingCalls = $db->fetchAll(
        "SELECT acs.*, l.name as lead_name, l.phone as lead_phone
         FROM ai_calling_schedule acs
         LEFT JOIN leads l ON acs.lead_id = l.id
         WHERE acs.scheduled_date = ? 
         AND acs.scheduled_time <= ?
         AND acs.status = 'pending'
         AND acs.attempt_count < acs.max_attempts
         ORDER BY acs.priority DESC, acs.scheduled_time ASC
         LIMIT 10",
        [$today, $currentTime]
    ) ?: [];
    
    if (empty($pendingCalls)) {
        echo "  📭 No pending calls to process.\n";
        exit(0);
    }
    
    echo "  📞 Found " . count($pendingCalls) . " calls to process.\n";
    
    $processed = 0;
    $failed = 0;
    
    foreach ($pendingCalls as $call) {
        try {
            // Update attempt count
            $db->execute(
                "UPDATE ai_calling_schedule SET attempt_count = attempt_count + 1, last_attempt_at = NOW(), status = 'processing' WHERE id = ?",
                [$call['id']]
            );
            
            // TODO: Integrate with actual voice provider (Exotel/Twilio/Plivo)
            // For now, simulate call creation in ai_call_sessions
            $sessionId = 'sess_' . uniqid();
            $callSid = 'sim_' . uniqid();
            
            $db->execute(
                "INSERT INTO ai_call_sessions (
                    campaign_id, tenant_id, session_id, lead_id, agent_id, agent_type,
                    phone, call_type, status, ai_agent_id, script_code, script_template,
                    scheduled_at, started_at, created_at, call_sid
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'outbound', 'in_progress', ?, ?, ?, ?, NOW(), NOW(), ?)",
                [
                    $call['campaign_id'] ?? 1,
                    1,
                    $sessionId,
                    $call['lead_id'],
                    $call['user_id'] ?? 1,
                    'ai_agent',
                    $call['phone'],
                    $call['ai_agent_id'] ?? 'auto_agent_1',
                    $call['script_code'] ?? 'default',
                    $call['script_template'] ?? 'property_introduction',
                    $call['scheduled_date'] . ' ' . $call['scheduled_time'],
                    $callSid
                ]
            );
            
            // Mark schedule as completed (simulated)
            $db->execute(
                "UPDATE ai_calling_schedule SET status = 'completed', call_session_id = LAST_INSERT_ID(), updated_at = NOW() WHERE id = ?",
                [$call['id']]
            );
            
            $processed++;
            echo "    ✅ Call initiated: {$call['lead_name']} ({$call['phone']})\n";
            
        } catch (\Throwable $e) {
            $failed++;
            echo "    ❌ Failed: {$call['lead_name']} - " . $e->getMessage() . "\n";
            
            // Reset schedule status for retry
            $db->execute(
                "UPDATE ai_calling_schedule SET status = 'pending', updated_at = NOW() WHERE id = ?",
                [$call['id']]
            );
        }
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Auto-Dialer: $processed processed, $failed failed\n";
    exit($failed > 0 ? 1 : 0);
    
} catch (\Throwable $e) {
    $errorMsg = "Auto-Dialer Cron Fatal: " . $e->getMessage();
    echo "[" . date('Y-m-d H:i:s') . "] $errorMsg\n";
    error_log($errorMsg);
    exit(1);
}