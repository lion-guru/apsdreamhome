<?php
require_once(__DIR__ . '/includes/session_manager.php');
require_once(__DIR__ . '/includes/superadmin_helpers.php');
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
if (!isSuperAdmin()) {
    header('Location: index.php');
    exit();
}
session_start();
require_once(__DIR__ . '/../includes/config/ai_settings.php');
require_once(__DIR__ . '/../includes/config/openai.php');
require_once(__DIR__ . '/../includes/classes/Database.php');
$db = new Database();
$con = $db->getConnection();
$ai = include(__DIR__ . '/../includes/config/ai_settings.php');
$openai = include(__DIR__ . '/../includes/config/openai.php');

$insights = [];
$status = [];
$trends = [];
$forecast = '';

// Anomaly: Sudden drop in registrations (last 24h vs avg)
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$reg24h = mysqli_fetch_assoc($res)['cnt'] ?? 0;
$res = mysqli_query($con, "SELECT AVG(regs) as avgreg FROM (SELECT COUNT(*) as regs FROM users GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC LIMIT 7) t");
$avgreg = round(mysqli_fetch_assoc($res)['avgreg'] ?? 0, 1);
if ($reg24h < 0.5 * $avgreg && $avgreg > 2) {
    $insights[] = "Drop detected: Only $reg24h new users in last 24h (avg: $avgreg). Possible registration issue or seasonal dip.";
}

// Anomaly: Spike in support tickets
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM tickets WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$tick24h = mysqli_fetch_assoc($res)['cnt'] ?? 0;
$res = mysqli_query($con, "SELECT AVG(ticks) as avgtick FROM (SELECT COUNT(*) as ticks FROM tickets GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC LIMIT 7) t");
$avgtick = round(mysqli_fetch_assoc($res)['avgtick'] ?? 0, 1);
if ($tick24h > 2 * $avgtick && $tick24h > 5) {
    $insights[] = "Spike detected: $tick24h new support tickets in last 24h (avg: $avgtick). Possible issue or campaign effect.";
}

// Anomaly: Unusually high/low booking rates
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM property WHERE booked=1 AND booked_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$book24h = mysqli_fetch_assoc($res)['cnt'] ?? 0;
$res = mysqli_query($con, "SELECT AVG(books) as avgbook FROM (SELECT COUNT(*) as books FROM property WHERE booked=1 GROUP BY DATE(booked_at) ORDER BY DATE(booked_at) DESC LIMIT 7) t");
$avgbook = round(mysqli_fetch_assoc($res)['avgbook'] ?? 0, 1);
if ($book24h > 2 * $avgbook && $book24h > 3) {
    $insights[] = "High booking rate: $book24h bookings in last 24h (avg: $avgbook).";
}
if ($book24h < 0.5 * $avgbook && $avgbook > 1) {
    $insights[] = "Low booking rate: Only $book24h bookings in last 24h (avg: $avgbook).";
}

// Trend arrays for sparklines (last 14 days)
function get_trend($con, $table, $date_col, $where = '1=1') {
    $trend = [];
    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $q = "SELECT COUNT(*) as cnt FROM $table WHERE DATE($date_col)='$d" . "' AND $where";
        $res = mysqli_query($con, $q);
        $trend[] = (int)(mysqli_fetch_assoc($res)['cnt'] ?? 0);
    }
    return $trend;
}
$trends['registrations'] = get_trend($con, 'users', 'created_at');
$trends['bookings'] = get_trend($con, 'property', 'booked_at', 'booked=1');
$trends['tickets'] = get_trend($con, 'tickets', 'created_at');
$trends['payments'] = get_trend($con, 'payments', 'paid_at', "status='completed'");

// Predictive analytics (AI forecast)
if (($ai['ai_suggestions'] ?? 0) == 1 && !empty($openai['api_key']) && $openai['api_key'] !== 'YOUR_OPENAI_API_KEY_HERE') {
    require_once __DIR__ . '/../includes/log_admin_action_db.php';
    $prompt = "Recent trends: Registrations: ".implode(', ',$trends['registrations'])."; Bookings: ".implode(', ',$trends['bookings'])."; Tickets: ".implode(', ',$trends['tickets'])."; Payments: ".implode(', ',$trends['payments']).". ";
    if($insights) $prompt .= "Insights: ".implode('; ',$insights).". ";
    $prompt .= "Give a short, actionable forecast for the next week for an admin of a real estate portal. Mention likely trends and one suggestion.";
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role'=>'system','content'=>'You are an expert data analyst for a real estate portal.'],
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
        $forecast = trim($resp['choices'][0]['message']['content']);
        log_admin_action_db('ai_forecast', 'AI forecast generated successfully');
    } else {
        log_admin_action_db('ai_forecast_failed', 'AI forecast generation failed: ' . ($result ?: 'No response'));
    }
}

// Other urgent issues (existing)
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM tickets WHERE status='open' AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)");
$old_tickets = mysqli_fetch_assoc($res)['cnt'] ?? 0;
if ($old_tickets > 0) {
    $status[] = "$old_tickets support tickets unresolved for over 3 days.";
}
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM property WHERE status='booked' AND expiry_date >= CURDATE() AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
$expiring = mysqli_fetch_assoc($res)['cnt'] ?? 0;
if ($expiring > 0) {
    $status[] = "$expiring bookings will expire in next 3 days.";
}
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM users WHERE role='lead' AND created_at < DATE_SUB(NOW(), INTERVAL 2 DAY) AND id NOT IN (SELECT user_id FROM property WHERE booked=1)");
$nolead = mysqli_fetch_assoc($res)['cnt'] ?? 0;
if ($nolead > 0) {
    $status[] = "$nolead new leads have not been followed up in 2+ days.";
}

// Example: Detect spike in failed logins (last 24h vs avg)
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM login_attempts WHERE success=0 AND time > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$failed24h = mysqli_fetch_assoc($res)['cnt'] ?? 0;
$res = mysqli_query($con, "SELECT AVG(fails) as avgfail FROM (SELECT COUNT(*) as fails FROM login_attempts WHERE success=0 GROUP BY DATE(time) ORDER BY DATE(time) DESC LIMIT 7) t");
$avgfail = round(mysqli_fetch_assoc($res)['avgfail'] ?? 0, 1);
if ($failed24h > 2 * $avgfail && $failed24h > 10) {
    $insights[] = "Spike detected: $failed24h failed logins in last 24h (avg: $avgfail). Possible brute-force or user confusion.";
}

?>
<html>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<?php echo json_encode([
    'success'=>true,
    'insights'=>$insights,
    'status'=>$status,
    'trends'=>$trends,
    'forecast'=>$forecast
]); ?>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
