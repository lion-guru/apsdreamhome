<?php
/**
 * AI Admin Insights - Secured version
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/SecurityUtility.php';

// Superadmin access check (as per original logic)
if (!isSuperAdmin()) {
    header('Location: login.php');
    exit();
}

require_once(__DIR__ . '/../includes/config/ai_settings.php');
require_once(__DIR__ . '/../includes/config/openai.php');

$ai = include(__DIR__ . '/../includes/config/ai_settings.php');
$openai = include(__DIR__ . '/../includes/config/openai.php');

$db = \App\Core\App::database();
$insights = [];
$status = [];
$trends = [];
$forecast = '';

// Anomaly: Sudden drop in registrations (last 24h vs avg)
$row_reg24h = $db->fetchOne("SELECT COUNT(*) as cnt FROM user WHERE join_date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$reg24h = $row_reg24h['cnt'] ?? 0;

$row_avgreg = $db->fetchOne("SELECT AVG(regs) as avgreg FROM (SELECT COUNT(*) as regs FROM user GROUP BY DATE(join_date) ORDER BY DATE(join_date) DESC LIMIT 7) t");
$avgreg = round($row_avgreg['avgreg'] ?? 0, 1);

if ($reg24h < 0.5 * $avgreg && $avgreg > 2) {
    $insights[] = "Drop detected: Only $reg24h new users in last 24h (avg: $avgreg). Possible registration issue or seasonal dip.";
}

// Anomaly: Spike in support tickets
$row_tick24h = $db->fetchOne("SELECT COUNT(*) as cnt FROM tickets WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$tick24h = $row_tick24h['cnt'] ?? 0;

$row_avgtick = $db->fetchOne("SELECT AVG(ticks) as avgtick FROM (SELECT COUNT(*) as ticks FROM tickets GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC LIMIT 7) t");
$avgtick = round($row_avgtick['avgtick'] ?? 0, 1);

if ($tick24h > 2 * $avgtick && $tick24h > 5) {
    $insights[] = "Spike detected: $tick24h new support tickets in last 24h (avg: $avgtick). Possible issue or campaign effect.";
}

// Anomaly: Unusually high/low booking rates
$row_book24h = $db->fetchOne("SELECT COUNT(*) as cnt FROM property WHERE booked=1 AND booked_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$book24h = $row_book24h['cnt'] ?? 0;

$row_avgbook = $db->fetchOne("SELECT AVG(books) as avgbook FROM (SELECT COUNT(*) as books FROM property WHERE booked=1 GROUP BY DATE(booked_at) ORDER BY DATE(booked_at) DESC LIMIT 7) t");
$avgbook = round($row_avgbook['avgbook'] ?? 0, 1);

if ($book24h > 2 * $avgbook && $book24h > 3) {
    $insights[] = "High booking rate: $book24h bookings in last 24h (avg: $avgbook).";
}
if ($book24h < 0.5 * $avgbook && $avgbook > 1) {
    $insights[] = "Low booking rate: Only $book24h bookings in last 24h (avg: $avgbook).";
}

// Trend arrays for sparklines (last 14 days)
/**
 * get_trend function - Refactored for security
 */
function get_trend($db, $table, $date_col, $where = '1=1') {
    $trend = [];
    // Table and column names cannot be parameterized, so we validate them if they were dynamic.
    // Here they are hardcoded from calls below, but it's good practice.
    $allowed_tables = ['user', 'property', 'tickets', 'payments'];
    $allowed_cols = ['join_date', 'booked_at', 'created_at', 'paid_at'];
    
    if (!in_array($table, $allowed_tables) || !in_array($date_col, $allowed_cols)) {
        return array_fill(0, 14, 0);
    }

    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $row = $db->fetchOne("SELECT COUNT(*) as cnt FROM $table WHERE DATE($date_col) = :date AND $where", ['date' => $d]);
        $trend[] = (int)($row['cnt'] ?? 0);
    }
    return $trend;
}

$trends['registrations'] = get_trend($db, 'user', 'join_date');
$trends['bookings'] = get_trend($db, 'property', 'booked_at', 'booked=1');
$trends['tickets'] = get_trend($db, 'tickets', 'created_at');
$trends['payments'] = get_trend($db, 'payments', 'paid_at', "status='completed'");

// Predictive analytics (AI forecast)
if (($ai['ai_suggestions'] ?? 0) == 1 && !empty($openai['// SECURITY: Sensitive information removed']) && $openai['// SECURITY: Sensitive information removed'] !== 'YOUR_OPENAI_// SECURITY: Sensitive information removed_HERE') {
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
        'Authorization: Bearer ' . $openai['// SECURITY: Sensitive information removed']
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $result = curl_exec($ch);
    $resp = json_decode($result, true);
    
    if (isset($resp['choices'][0]['message']['content'])) {
        $forecast = trim($resp['choices'][0]['message']['content']);
        log_admin_action_db('ai_forecast', 'AI forecast generated successfully');
    } else {
        log_admin_action_db('ai_forecast_failed', 'AI forecast generation failed: ' . ($result ?: 'No response'));
    }
}

// Other urgent issues (existing)
$row_old_tickets = $db->fetchOne("SELECT COUNT(*) as cnt FROM tickets WHERE status='open' AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)");
$old_tickets = $row_old_tickets['cnt'] ?? 0;
if ($old_tickets > 0) {
    $status[] = "$old_tickets support tickets unresolved for over 3 days.";
}

$row_expiring = $db->fetchOne("SELECT COUNT(*) as cnt FROM property WHERE status='booked' AND expiry_date >= CURDATE() AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
$expiring = $row_expiring['cnt'] ?? 0;
if ($expiring > 0) {
    $status[] = "$expiring bookings will expire in next 3 days.";
}

$row_nolead = $db->fetchOne("SELECT COUNT(*) as cnt FROM user WHERE job_role='Lead' AND join_date < DATE_SUB(NOW(), INTERVAL 2 DAY) AND uid NOT IN (SELECT user_id FROM property WHERE booked=1)");
$nolead = $row_nolead['cnt'] ?? 0;
if ($nolead > 0) {
    $status[] = "$nolead new leads have not been followed up in 2+ days.";
}

// Example: Detect spike in failed logins (last 24h vs avg)
$row_failed24h = $db->fetchOne("SELECT COUNT(*) as cnt FROM login_attempts WHERE success=0 AND time > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$failed24h = $row_failed24h['cnt'] ?? 0;

$row_avgfail = $db->fetchOne("SELECT AVG(fails) as avgfail FROM (SELECT COUNT(*) as fails FROM login_attempts WHERE success=0 GROUP BY DATE(time) ORDER BY DATE(time) DESC LIMIT 7) t");
$avgfail = round($row_avgfail['avgfail'] ?? 0, 1);

if ($failed24h > 2 * $avgfail && $failed24h > 10) {
    $insights[] = "Spike detected: $failed24h failed logins in last 24h (avg: $avgfail). Possible brute-force or user confusion.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Admin Insights - APS Dream Home</title>
</head>
<body>
    <?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
    
    <div class="container mt-4">
        <?php echo json_encode([
            'success'=>true,
            'insights'=>$insights,
            'status'=>$status,
            'trends'=>$trends,
            'forecast'=>$forecast
        ]); ?>
    </div>

    <?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>

