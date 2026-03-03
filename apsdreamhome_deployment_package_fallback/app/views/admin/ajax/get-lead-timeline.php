<?php
/**
 * AJAX - Get Lead Activity Timeline & AI Summary
 */
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../../app/services/GeminiService.php';

use App\Services\GeminiService;

header('Content-Type: application/json');

if (!hasRole("Admin")) {
    echo json_encode(['success' => false, 'error' => h($mlSupport->translate('Access denied'))]);
    exit();
}

if (!verifyCSRFToken()) {
    echo json_encode(['success' => false, 'error' => h($mlSupport->translate('Security validation failed'))]);
    exit();
}

$lead_id = intval($_GET['lead_id'] ?? 0);

if ($lead_id <= 0) {
    echo json_encode(['success' => false, 'error' => h($mlSupport->translate('Invalid lead ID'))]);
    exit();
}

try {
    $db = \App\Core\App::database();
    // 1. Fetch Timeline
    $timeline = $db->fetchAll("SELECT a.*, adm.auser as admin_name
                           FROM lead_activities a
                           LEFT JOIN admin adm ON a.created_by = adm.id
                           WHERE a.lead_id = :lead_id
                           ORDER BY a.created_at DESC", ['lead_id' => $lead_id]);

    // 2. Fetch Lead Context for AI
    $lead_data = $db->fetchOne("SELECT * FROM leads WHERE id = :lead_id", ['lead_id' => $lead_id]);

    // 3. Generate AI Summary if there's history
    $ai_summary = "No history available for AI analysis yet.";
    if (!empty($timeline)) {
        $gemini = new GeminiService();
        $history_text = "";
        foreach (array_reverse($timeline) as $act) {
            $display_date = $act['activity_date'] ?: $act['created_at'];
            $display_subject = $act['subject'] ?: ucwords(str_replace('_', ' ', $act['activity_type']));
            $history_text .= "- [{$display_date}] {$display_subject}: {$act['description']}\n";
        }

        $prompt = "Analyze the following activity history for a real estate lead named '{$lead_data['name']}'.
        Provide a 2-sentence summary of their journey and a 1-sentence strategic recommendation for the next step.

        Lead Current Status: {$lead_data['status']}
        Activity History:
        {$history_text}

        Tone: Professional and insightful.";

        $ai_summary = $gemini->generateText($prompt);
    }

    $ai_summary_display = (strpos($ai_summary, 'Error:') === 0) ? h($mlSupport->translate("AI analysis currently unavailable.")) : h(trim($ai_summary));

    // Sanitize timeline data
    foreach ($timeline as &$item) {
        $item['subject'] = h($item['subject']);
        $item['description'] = h($item['description']);
        $item['admin_name'] = h($item['admin_name']);
    }

    echo json_encode([
        'success' => true,
        'timeline' => $timeline,
        'ai_summary' => $ai_summary_display,
        'lead_name' => h($lead_data['name'])
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => h($e->getMessage())]);
}
?>
