<?php
/**
 * AI Auto-Dialer Cron
 * Runs every 2 minutes during business hours (9AM-8PM)
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\AI\VoiceProviderManager;
use App\Services\Voice\VoiceCallService;
use App\Core\Database\Database;

echo "=== AI Auto-Dialer Cron ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

// Only run during business hours (9AM-8PM)
$hour = (int)date('H');
if ($hour < 9 || $hour >= 20) {
    echo "Outside business hours (9AM-8PM). Skipping.\n";
    exit(0);
}

try {
    $voiceService = new VoiceCallService();
    $providerManager = VoiceProviderManager::getInstance();
    
    // Check provider health
    $providerStatus = $providerManager->getProviderStatus();
    $activeProviders = array_filter($providerStatus, fn($p) => $p['available'] ?? false);
    
    if (empty($activeProviders)) {
        echo "No active voice providers. Skipping dialer.\n";
        exit(0);
    }
    
    echo "Active providers: " . implode(', ', array_keys($activeProviders)) . "\n";
    
    // Process scheduled calls
    $db = Database::getInstance()->getConnection();
    
    // Get due calls (status = scheduled, scheduled_time <= now)
    $stmt = $db->prepare("
        SELECT * FROM ai_calling_schedule 
        WHERE status = 'scheduled' 
        AND scheduled_time <= NOW()
        AND retry_count < 3
        ORDER BY scheduled_time ASC
        LIMIT 10
    ");
    $stmt->execute();
    $dueCalls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($dueCalls)) {
        echo "No due calls to process.\n";
        exit(0);
    }
    
    echo "Processing " . count($dueCalls) . " due calls...\n";
    
    $processed = 0;
    $failed = 0;
    
    foreach ($dueCalls as $call) {
        try {
            $provider = $providerManager->getProvider($call['provider'] ?? 'exotel');
            
            if (!$provider || !($provider->isAvailable() ?? false)) {
                // Try next available provider
                $provider = array_values($activeProviders)[0] ?? null;
                if (!$provider) {
                    throw new Exception("No available provider");
                }
            }
            
            // Initiate call
            $result = $provider->makeCall([
                'to' => $call['phone_number'],
                'from' => $call['caller_id'] ?? '',
                'script' => $call['script'] ?? '',
                'webhook_url' => $call['webhook_url'] ?? '',
                'metadata' => [
                    'schedule_id' => $call['id'],
                    'lead_id' => $call['lead_id'] ?? null,
                ],
            ]);
            
            if ($result['success'] ?? false) {
                // Update schedule status
                $stmt = $db->prepare("
                    UPDATE ai_calling_schedule 
                    SET status = 'in_progress', 
                        call_sid = ?, 
                        provider = ?,
                        started_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $result['call_sid'] ?? '',
                    $provider->getName() ?? 'unknown',
                    $call['id'],
                ]);
                
                $processed++;
                echo "  ✅ Call initiated to {$call['phone_number']} (SID: {$result['call_sid']})\n";
            } else {
                throw new Exception($result['error'] ?? 'Call failed');
            }
            
        } catch (\Throwable $e) {
            $failed++;
            
            // Increment retry count
            $stmt = $db->prepare("
                UPDATE ai_calling_schedule 
                SET retry_count = retry_count + 1,
                    last_error = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$e->getMessage(), $call['id']]);
            
            echo "  ❌ Failed to call {$call['phone_number']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "Processed: $processed, Failed: $failed\n";
    
    // Also check for stuck in-progress calls (timeout after 5 minutes)
    $stmt = $db->prepare("
        UPDATE ai_calling_schedule 
        SET status = 'failed', 
            last_error = 'Call timeout (5 min)',
            updated_at = NOW()
        WHERE status = 'in_progress' 
        AND started_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $stmt->execute();
    $timedOut = $stmt->rowCount();
    
    if ($timedOut > 0) {
        echo "Timed out $timedOut stuck calls\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== AI Auto-Dialer Completed ===\n";