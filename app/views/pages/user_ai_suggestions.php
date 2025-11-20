<?php
// Enhanced Security: Strict Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/ai_suggestions_error.log');

// Secure Session Management
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'use_strict_mode' => true
]);

// Dependency Injection and Centralized Configuration
require_once(__DIR__ . '/../../includes/env_loader.php');
require_once(__DIR__ . '/../../includes/db_security_upgrade.php');
require_once(__DIR__ . '/../../includes/classes/AIAssistant.php');

// Sanitize input function
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
}

// Enhanced Authentication Check
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'error' => 'Unauthorized Access',
        'message' => 'Please log in to access AI suggestions.'
    ]);
    exit();
}

// Secure User ID Retrieval
$user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if (!$user_id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Invalid User ID',
        'message' => 'Your session appears to be corrupted.'
    ]);
    exit();
}

try {
    $dbSecurity = new DatabaseSecurityUpgrade();
    $aiAssistant = new AIAssistant($dbSecurity);
} catch (Exception $e) {
    error_log('AI Suggestions Initialization Error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'System Error',
        'message' => 'Unable to initialize AI components at this time.'
    ]);
    exit();
}

// Validate and sanitize input context
$context = [
    'property_type' => $dbSecurity->sanitizeInput($_POST['property_type'] ?? ''),
    'budget' => $dbSecurity->sanitizeInput($_POST['budget'] ?? ''),
    'location' => $dbSecurity->sanitizeInput($_POST['location'] ?? '')
];
// Validate context completeness
$missingFields = array_filter($context, function($value) { return empty($value); });
if (!empty($missingFields)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Incomplete Context',
        'message' => 'Missing required fields: ' . implode(', ', array_keys($missingFields))
    ]);
    exit();
}

// Generate AI Suggestions
$suggestions = $aiAssistant->generateSuggestions($user_id, $context);

// Return Suggestions
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'suggestions' => $suggestions
]);
exit();





global $con;
// Gather user info
$stmt = $con->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

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
$stmt = $con->prepare("SELECT date, property_id FROM property_visits WHERE user_id = ? AND date >= CURDATE() ORDER BY date ASC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();
if ($visit = mysqli_fetch_assoc($res)) {
    $status[] = 'You have a property visit scheduled on ' . $visit['date'] . '.';
}
// Expiring booking
$stmt = $con->prepare("SELECT expiry_date FROM property WHERE user_id = ? AND status = 'booked' AND expiry_date >= CURDATE() AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();
if ($row = mysqli_fetch_assoc($res)) {
    $status[] = 'Your booking will expire on ' . $row['expiry_date'] . '. Please complete any pending steps.';
}
// Payment due
$stmt = $con->prepare("SELECT amount_due, due_date FROM payments WHERE user_id = ? AND status = 'pending' AND due_date <= CURDATE() LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();
if ($row = mysqli_fetch_assoc($res)) {
    $status[] = 'You have a pending payment of Rs. ' . $row['amount_due'] . ' due on ' . $row['due_date'] . '.';
}

// Gather context for rules-based model
$context = [];
$role = $_SESSION['role'] ?? 'guest';
if ($role === 'customer') {
    // Pending documents
    $stmt = $con->prepare("SELECT COUNT(*) as cnt FROM documents WHERE user_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    $context['pending_docs'] = mysqli_fetch_assoc($res)['cnt'] ?? 0;
    // Upcoming visit
    $stmt = $con->prepare("SELECT date FROM property_visits WHERE user_id = ? AND date >= CURDATE() ORDER BY date ASC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    $row = mysqli_fetch_assoc($res);
    if ($row && $row['date']) $context['upcoming_visit'] = $row['date'];
}
if ($role === 'agent') {
    $stmt = $con->prepare("SELECT COUNT(*) as cnt FROM leads WHERE agent_id = ? AND last_contact < DATE_SUB(CURDATE(), INTERVAL 3 DAY)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    $context['cold_leads'] = mysqli_fetch_assoc($res)['cnt'] ?? 0;
}
if ($role === 'admin') {
    $stmt = $con->prepare("SELECT COUNT(*) as cnt FROM tickets WHERE status = 'open'");
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    $context['unresolved_tickets'] = mysqli_fetch_assoc($res)['cnt'] ?? 0;
}

// Prefer OpenAI fine-tuned model if enabled and available
if (
    ($ai['ai_suggestions'] ?? 0) == 1 &&
    !empty($openai['// SECURITY: Sensitive information removed']) &&
    $openai['// SECURITY: Sensitive information removed'] !== 'YOUR_OPENAI_// SECURITY: Sensitive information removed_HERE'
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
        'Authorization: Bearer ' . $openai['// SECURITY: Sensitive information removed']
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
    $stmt = $con->prepare("SELECT COUNT(*) as cnt FROM payments WHERE user_id = ? AND status = 'due'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    $due = mysqli_fetch_assoc($res)['cnt'] ?? 0;
    if ($due > 0) $status[] = "You have $due payment(s) due. Please pay on time to avoid penalties.";
}
if ($role === 'agent') {
    $stmt = $con->prepare("SELECT COUNT(*) as cnt FROM tickets WHERE agent_id = ? AND status = 'open'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    $open = mysqli_fetch_assoc($res)['cnt'] ?? 0;
    if ($open > 0) $status[] = "You have $open open support ticket(s) assigned.";
}
if ($role === 'admin') {
    $stmt = $con->prepare("SELECT COUNT(*) as cnt FROM tickets WHERE status = 'open' AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)");
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    $old = mysqli_fetch_assoc($res)['cnt'] ?? 0;
    if ($old > 0) $status[] = "$old support tickets unresolved for over 3 days.";
}

header('Content-Type: application/json');
echo json_encode([
    'success'=>true,
    'suggestions'=>$suggestions,
    'status'=>$status
]);

