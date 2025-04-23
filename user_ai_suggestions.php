<?php
session_start();
require_once(__DIR__ . '/includes/config/ai_settings.php');
require_once(__DIR__ . '/includes/config/openai.php');
require_once(__DIR__ . '/includes/classes/Database.php');
require_once(__DIR__ . '/aps_model/aps_rules_based_model.php');
$db = new Database();
$con = $db->getConnection();
$ai = include(__DIR__ . '/includes/config/ai_settings.php');
$openai = include(__DIR__ . '/includes/config/openai.php');

// Assume $_SESSION['user_id'] is set
$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    echo json_encode(['success'=>false, 'error'=>'Not logged in']);
    exit;
}

// Gather user info
$user = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM users WHERE id=$user_id"));
$suggestions = [];
$status = [];

// Pending profile completion
if (($user['profile_complete'] ?? 1) == 0) {
    $status[] = 'Complete your profile to unlock all features.';
}
// Pending document upload
if (($user['documents_uploaded'] ?? 1) == 0) {
    $status[] = 'Upload your required documents to proceed.';
}
// Upcoming property visits
$res = mysqli_query($con, "SELECT date, property_id FROM property_visits WHERE user_id=$user_id AND date >= CURDATE() ORDER BY date ASC LIMIT 1");
if ($visit = mysqli_fetch_assoc($res)) {
    $status[] = 'You have a property visit scheduled on ' . $visit['date'] . '.';
}
// Expiring booking
$res = mysqli_query($con, "SELECT expiry_date FROM property WHERE user_id=$user_id AND status='booked' AND expiry_date >= CURDATE() AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) LIMIT 1");
if ($row = mysqli_fetch_assoc($res)) {
    $status[] = 'Your booking will expire on ' . $row['expiry_date'] . '. Please complete any pending steps.';
}
// Payment due
$res = mysqli_query($con, "SELECT amount_due, due_date FROM payments WHERE user_id=$user_id AND status='pending' AND due_date <= CURDATE() LIMIT 1");
if ($row = mysqli_fetch_assoc($res)) {
    $status[] = 'You have a pending payment of Rs. ' . $row['amount_due'] . ' due on ' . $row['due_date'] . '.';
}

// Gather context for rules-based model
$context = [];
$role = $_SESSION['role'] ?? 'guest';
if ($role === 'customer') {
    // Pending documents
    $res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM documents WHERE user_id=$user_id AND status='pending'");
    $context['pending_docs'] = mysqli_fetch_assoc($res)['cnt'] ?? 0;
    // Upcoming visit
    $res = mysqli_query($con, "SELECT date FROM property_visits WHERE user_id=$user_id AND date>=CURDATE() ORDER BY date ASC LIMIT 1");
    $row = mysqli_fetch_assoc($res);
    if ($row && $row['date']) $context['upcoming_visit'] = $row['date'];
}
if ($role === 'agent') {
    $res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM leads WHERE agent_id=$user_id AND last_contact < DATE_SUB(CURDATE(), INTERVAL 3 DAY)");
    $context['cold_leads'] = mysqli_fetch_assoc($res)['cnt'] ?? 0;
}
if ($role === 'admin') {
    $res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM tickets WHERE status='open'");
    $context['unresolved_tickets'] = mysqli_fetch_assoc($res)['cnt'] ?? 0;
}

// Prefer OpenAI fine-tuned model if enabled and available
if (
    ($ai['ai_suggestions'] ?? 0) == 1 &&
    !empty($openai['api_key']) &&
    $openai['api_key'] !== 'YOUR_OPENAI_API_KEY_HERE'
) {
    $prompt = "User role: $role\n";
    foreach ($context as $k=>$v) $prompt .= "$k: $v\n";
    $prompt .= "Give 3 actionable, personalized suggestions for this user on the APS Dream Homes portal.";
    $data = [
        'model' => $openai['finetuned_model'] ?? 'gpt-3.5-turbo',
        'messages' => [
            ['role'=>'system','content'=>'You are APS, an expert assistant for a real estate portal.'],
            ['role'=>'user','content'=>$prompt]
        ],
        'max_tokens' => 120,
        'temperature' => 0.6
    ];
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openai['api_key']
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $result = curl_exec($ch);
    curl_close($ch);
    $resp = json_decode($result, true);
    if (isset($resp['choices'][0]['message']['content'])) {
        // Split into suggestions if possible
        $text = trim($resp['choices'][0]['message']['content']);
        if (strpos($text, "\n") !== false) {
            foreach (explode("\n", $text) as $line) {
                $line = trim($line, "- 0123456789.\t\r\n");
                if ($line) $suggestions[] = $line;
            }
        } else {
            $suggestions[] = $text;
        }
    }
}
// Fallback to rules-based model
if (empty($suggestions)) {
    $suggestions = aps_suggest($role, $context);
}

// Example reminders (status)
if ($role === 'customer') {
    $res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM payments WHERE user_id=$user_id AND status='due'");
    $due = mysqli_fetch_assoc($res)['cnt'] ?? 0;
    if ($due > 0) $status[] = "You have $due payment(s) due. Please pay on time to avoid penalties.";
}
if ($role === 'agent') {
    $res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM tickets WHERE agent_id=$user_id AND status='open'");
    $open = mysqli_fetch_assoc($res)['cnt'] ?? 0;
    if ($open > 0) $status[] = "You have $open open support ticket(s) assigned.";
}
if ($role === 'admin') {
    $res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM tickets WHERE status='open' AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)");
    $old = mysqli_fetch_assoc($res)['cnt'] ?? 0;
    if ($old > 0) $status[] = "$old support tickets unresolved for over 3 days.";
}

header('Content-Type: application/json');
echo json_encode([
    'success'=>true,
    'suggestions'=>$suggestions,
    'status'=>$status
]);
