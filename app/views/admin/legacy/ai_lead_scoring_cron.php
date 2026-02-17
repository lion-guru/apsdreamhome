<?php
/**
 * AI Lead Scoring Cron Job
 * Automatically scores new leads using Gemini AI
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../app/services/GeminiService.php';
require_once __DIR__ . '/../includes/notification_manager.php';
require_once __DIR__ . '/../includes/email_service.php';

use App\Services\GeminiService;

// Ensure this script is run via CLI or authorized request
if (php_sapi_name() !== 'cli' && !isset($_GET['token'])) {
    // Basic token check if not CLI
    $config_token = getenv('CRON_TOKEN') ?: 'aps_secret_token';
    if (($_GET['token'] ?? '') !== $config_token) {
        die("Unauthorized access.");
    }
}

$db = \App\Core\App::database();
$gemini = new GeminiService();
$nm = new NotificationManager($db->getConnection(), new EmailService());

try {
    // Find leads that haven't been scored yet or need re-scoring
    // Limit to a small batch to avoid timeouts
    $leads_to_score = $db->fetchAll("SELECT id, name, status, notes, source, budget, assigned_to FROM leads WHERE ai_score IS NULL AND status != 'Converted' LIMIT 20");

    if (empty($leads_to_score)) {
        echo "[" . date('Y-m-d H:i:s') . "] No leads found for scoring.\n";
        exit;
    }

    $scored_count = 0;
    foreach ($leads_to_score as $lead) {
        $prompt = "Analyze this real estate lead and provide a score from 0 to 100 based on their potential to convert. 
        Lead Details:
        Name: {$lead['name']}
        Status: {$lead['status']}
        Source: {$lead['source']}
        Notes: " . ($lead['notes'] ?: 'No notes provided') . "
        Budget: " . ($lead['budget'] ?: 'Not specified') . "
        
        Respond ONLY in JSON format: {\"score\": 85, \"summary\": \"High interest shown in premium properties...\"}";
        
        try {
            $ai_response = $gemini->generateText($prompt);
            
            // Extract JSON
            if (preg_match('/```json\s*(.*?)\s*```/s', $ai_response, $matches)) {
                $json_content = $matches[1];
            } else {
                $json_content = $ai_response;
            }
            
            $data = json_decode($json_content, true);
            
            if ($data && isset($data['score'])) {
                $db->execute("UPDATE leads SET ai_score = :score, ai_summary = :summary, last_scored_at = NOW() WHERE id = :id", [
                    'score' => $data['score'],
                    'summary' => $data['summary'],
                    'id' => $lead['id']
                ]);
                
                // Trigger alert for high score leads (>= 80)
                if ($data['score'] >= 80) {
                    $notify_user_id = $lead['assigned_to'] ?: 1; // Fallback to admin ID 1
                    
                    $nm->send([
                        'user_id' => $notify_user_id,
                        'template' => 'HOT_LEAD_ALERT',
                        'data' => [
                            'name' => $lead['name'],
                            'score' => $data['score'],
                            'budget' => $lead['budget'] ?? 'Not specified'
                        ],
                        'channels' => ['db', 'email']
                    ]);
                }
                
                $scored_count++;
                echo "[" . date('Y-m-d H:i:s') . "] Scored lead: {$lead['name']} (Score: {$data['score']})\n";
            }
        } catch (Exception $e) {
            echo "[" . date('Y-m-d H:i:s') . "] Error scoring lead {$lead['id']}: " . $e->getMessage() . "\n";
            continue;
        }
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Finished. Scored $scored_count leads.\n";

} catch (Exception $e) {
    error_log("AI Scoring Cron Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

