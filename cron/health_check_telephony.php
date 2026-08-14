<?php
/**
 * Telephony Stack Health Check
 * Tests all services: Asterisk, Ollama, Whisper, WhatsApp, Database
 * 
 * Run: php cron/health_check_telephony.php
 * Or access: /admin/telephony/health (if wired as route)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;
use App\Services\Voice\AsteriskService;
use App\Services\Voice\AIVoicePipeline;
use App\Services\Communication\WhatsAppWebService;

set_time_limit(30);

$results = [];
$allOk = true;

// â”€â”€ 1. Database â”€â”€
try {
    $db = Database::getInstance();
    $db->query("SELECT 1");
    $results['database'] = ['status' => 'ok', 'message' => 'Connected'];
} catch (\Exception $e) {
    $results['database'] = ['status' => 'error', 'message' => $e->getMessage()];
    $allOk = false;
}

// â”€â”€ 2. Asterisk AMI â”€â”€
try {
    $asterisk = new AsteriskService();
    $connected = $asterisk->ping();
    if ($connected) {
        $channels = $asterisk->getActiveChannels();
        $results['asterisk'] = [
            'status' => 'ok',
            'message' => 'Connected',
            'active_channels' => count($channels),
        ];
    } else {
        $results['asterisk'] = ['status' => 'warning', 'message' => 'Not reachable (Docker not running?)'];
    }
} catch (\Exception $e) {
    $results['asterisk'] = ['status' => 'error', 'message' => $e->getMessage()];
}

// â”€â”€ 3. Ollama LLM â”€â”€
$ollamaUrl = getenv('OLLAMA_URL') ?: 'http://localhost:11434';
$ch = curl_init("{$ollamaUrl}/api/tags");
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $models = array_column($data['models'] ?? [], 'name');
    $results['ollama'] = [
        'status' => 'ok',
        'message' => 'Connected',
        'models' => $models,
        'has_llama' => in_array('llama3.2:3b', $models) || !empty($models),
    ];
} else {
    $results['ollama'] = ['status' => 'warning', 'message' => 'Not reachable (run: docker start aps_ollama)'];
}

// â”€â”€ 4. Whisper STT â”€â”€
$whisperUrl = getenv('WHISPER_URL') ?: 'http://localhost:8080';
$ch = curl_init("{$whisperUrl}/health");
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$results['whisper'] = $httpCode === 200
    ? ['status' => 'ok', 'message' => 'Connected']
    : ['status' => 'warning', 'message' => 'Not reachable (run: docker start aps_whisper)'];

// â”€â”€ 5. WhatsApp Service â”€â”€
try {
    $wa = new WhatsAppWebService();
    $status = $wa->isConnected();
    if (isset($status['error'])) {
        $results['whatsapp'] = ['status' => 'warning', 'message' => 'Not connected: ' . $status['error']];
    } else {
        $results['whatsapp'] = [
            'status' => 'ok',
            'message' => isset($status['connected']) ? 'Connected' : 'Running',
            'details' => $status,
        ];
    }
} catch (\Exception $e) {
    $results['whatsapp'] = ['status' => 'warning', 'message' => 'Service not running'];
}

// â”€â”€ 6. Calling Schedule Stats â”€â”€
try {
    $pending = $db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'pending'")['c'] ?? 0;
    $processing = $db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'processing'")['c'] ?? 0;
    $completedToday = $db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'completed' AND DATE(updated_at) = CURDATE()")['c'] ?? 0;
    
    $results['schedule'] = [
        'status' => 'ok',
        'pending' => (int)$pending,
        'processing' => (int)$processing,
        'completed_today' => (int)$completedToday,
    ];
} catch (\Exception $e) {
    $results['schedule'] = ['status' => 'error', 'message' => $e->getMessage()];
}

// â”€â”€ 7. EMI Reminders â”€â”€
try {
    $overdue = $db->fetch("SELECT COUNT(*) as c FROM booking_payment_schedules WHERE status IN ('pending','overdue') AND due_date < CURDATE()")['c'] ?? 0;
    $upcoming3d = $db->fetch("SELECT COUNT(*) as c FROM booking_payment_schedules WHERE status IN ('pending','overdue') AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)")['c'] ?? 0;
    
    $results['emi'] = [
        'status' => 'ok',
        'overdue' => (int)$overdue,
        'upcoming_3_days' => (int)$upcoming3d,
    ];
} catch (\Exception $e) {
    $results['emi'] = ['status' => 'error', 'message' => $e->getMessage()];
}

// â”€â”€ Output â”€â”€
echo "=== APS Dream Home - Telephony Health Check ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($results as $service => $info) {
    $icon = match ($info['status']) {
        'ok' => 'âœ“',
        'warning' => 'âš ',
        'error' => 'âœ—',
        default => '?',
    };
    
    $label = strtoupper($service);
    $msg = $info['message'] ?? '';
    echo "{$icon} {$label}: {$msg}\n";
    
    // Extra details
    if ($service === 'ollama' && !empty($info['models'])) {
        echo "  Models: " . implode(', ', $info['models']) . "\n";
    }
    if ($service === 'asterisk' && isset($info['active_channels'])) {
        echo "  Active channels: {$info['active_channels']}\n";
    }
    if ($service === 'schedule') {
        echo "  Pending: {$info['pending']}, Processing: {$info['processing']}, Done today: {$info['completed_today']}\n";
    }
    if ($service === 'emi') {
        echo "  Overdue: {$info['overdue']}, Due in 3 days: {$info['upcoming_3_days']}\n";
    }
}

$okCount = count(array_filter($results, fn($r) => $r['status'] === 'ok'));
$total = count($results);

echo "\n=== {$okCount}/{$total} services OK ===\n";

if (!$allOk) {
    echo "\nSome services have issues. Check above for details.\n";
}

// Return as JSON if requested
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode(['overall' => $allOk ? 'ok' : 'degraded', 'services' => $results], JSON_PRETTY_PRINT);
}?>