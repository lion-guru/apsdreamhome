<?php
/**
 * AJAX - Generate AI Follow-up Message
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

$lead_id = intval($_POST['lead_id'] ?? 0);

if ($lead_id <= 0) {
    echo json_encode(['success' => false, 'error' => h($mlSupport->translate('Invalid lead ID'))]);
    exit();
}

try {
    $db = \App\Core\App::database();
    // Fetch lead details
    $lead = $db->fetchOne("SELECT * FROM leads WHERE id = :lead_id", ['lead_id' => $lead_id]);

    if (!$lead) {
        echo json_encode(['success' => false, 'error' => h($mlSupport->translate('Lead not found'))]);
        exit();
    }

    // Fetch recent activities for context
    $activities = $db->fetchAll("SELECT activity_type, subject, description, activity_date, created_at FROM lead_activities WHERE lead_id = :lead_id ORDER BY created_at DESC LIMIT 5", ['lead_id' => $lead_id]);
    $activity_context = "";
    foreach ($activities as $act) {
        $display_date = $act['activity_date'] ?: $act['created_at'];
        $display_subject = $act['subject'] ?: ucwords(str_replace('_', ' ', $act['activity_type']));
        $activity_context .= "- [{$display_date}] {$display_subject}: {$act['description']}\n";
    }

    $gemini = new GeminiService();

    // Prepare prompt
    $prompt = "You are a professional real estate follow-up assistant for APS Dream Homes.
    Write a short, friendly, and persuasive follow-up message (under 100 words) for the following lead:
    Name: {$lead['name']}
    Status: {$lead['status']}
    AI Score: " . ($lead['ai_score'] ?? 'N/A') . "%
    Property Interest: " . ($lead['property_interest'] ?? 'N/A') . "
    Budget: " . ($lead['budget_range'] ?? $lead['budget'] ?? 'N/A') . "
    Location Preference: " . ($lead['location_preference'] ?? 'N/A') . "
    Notes: " . ($lead['notes'] ?? 'None') . "
    Tags: " . ($lead['tags'] ?? 'None') . "
    Source: " . ($lead['source'] ?? 'Unknown') . "

    Recent Interactions:
    " . ($activity_context ?: "No previous interactions recorded.") . "

    The message should be suitable for WhatsApp or Email.
    If the status is 'New', be welcoming. If 'Interested' or 'Qualified', be more direct about booking a site visit.
    Use the specific property interest, budget, and location preferences to make the message highly personalized.
    Reference recent interactions to show we are paying attention to their journey.
    Mention APS Dream Homes and keep the tone professional but warm.

    Return ONLY the message text.";

    $message = $gemini->generateText($prompt);

    if ($message && strpos($message, 'Error:') !== 0) {
        echo json_encode([
            'success' => true,
            'message' => h(trim($message)),
            'phone' => preg_replace('/[^0-9]/', '', $lead['phone'] ?? '')
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => h($mlSupport->translate('AI failed to generate message'))]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => h($e->getMessage())]);
}
?>
