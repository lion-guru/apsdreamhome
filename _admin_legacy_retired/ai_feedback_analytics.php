<?php
// Admin Analytics Dashboard for AI Feedback Trends
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit();
}
require_once(__DIR__ . '/../src/Database/Database.php');
require_once(__DIR__ . '/send_sms_twilio.php');
$db = new Database();
$con = $db->getConnection();

// Filter logic
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to = $_GET['to'] ?? date('Y-m-d');
$role_filter = isset($_GET['role']) && $_GET['role'] ? $_GET['role'] : null;
$date_sql = "created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59'";
$role_sql = $role_filter ? "AND role='".mysqli_real_escape_string($con, $role_filter)."'" : '';

// Get abuse threshold from GET or default
$offender_threshold = isset($_GET['abuse_threshold']) ? max(1, (int)$_GET['abuse_threshold']) : 100;

// Handle drilldown request (AJAX)
if (isset($_GET['drill_ip'])) {
  $drill_ip = mysqli_real_escape_string($con, $_GET['drill_ip']);
  $entries = [];
  $res = mysqli_query($con, "SELECT created_at, action, suggestion_text, feedback, notes FROM ai_interactions WHERE ip_address='$drill_ip' AND $date_sql $role_sql ORDER BY created_at DESC LIMIT 100");
  while($row = mysqli_fetch_assoc($res)) $entries[] = $row;
  header('Content-Type: application/json'); echo json_encode($entries); exit;
}
if (isset($_GET['drill_agent'])) {
  $drill_agent = mysqli_real_escape_string($con, $_GET['drill_agent']);
  $entries = [];
  $res = mysqli_query($con, "SELECT created_at, action, suggestion_text, feedback, notes FROM ai_interactions WHERE user_agent='$drill_agent' AND $date_sql $role_sql ORDER BY created_at DESC LIMIT 100");
  while($row = mysqli_fetch_assoc($res)) $entries[] = $row;
  header('Content-Type: application/json'); echo json_encode($entries); exit;
}
if (isset($_GET['drill_suggestion'])) {
  $drill_suggestion = mysqli_real_escape_string($con, $_GET['drill_suggestion']);
  $entries = [];
  $res = mysqli_query($con, "SELECT created_at, user_id, role, action, suggestion_text, feedback, notes, ip_address, user_agent FROM ai_interactions WHERE suggestion_text='$drill_suggestion' AND $date_sql $role_sql ORDER BY created_at DESC LIMIT 100");
  while($row = mysqli_fetch_assoc($res)) $entries[] = $row;
  header('Content-Type: application/json'); echo json_encode($entries); exit;
}
if (isset($_GET['drill_user'])) {
  $drill_user = (int)$_GET['drill_user'];
  $entries = [];
  $res = mysqli_query($con, "SELECT created_at, user_id, role, action, suggestion_text, feedback, notes, ip_address, user_agent FROM ai_interactions WHERE user_id=$drill_user AND $date_sql $role_sql ORDER BY created_at DESC LIMIT 100");
  while($row = mysqli_fetch_assoc($res)) $entries[] = $row;
  header('Content-Type: application/json'); echo json_encode($entries); exit;
}
if (isset($_GET['drill_feedback'])) {
  $drill_feedback = mysqli_real_escape_string($con, $_GET['drill_feedback']);
  $entries = [];
  $res = mysqli_query($con, "SELECT created_at, user_id, role, action, suggestion_text, feedback, notes, ip_address, user_agent FROM ai_interactions WHERE feedback='$drill_feedback' AND $date_sql $role_sql ORDER BY created_at DESC LIMIT 100");
  while($row = mysqli_fetch_assoc($res)) $entries[] = $row;
  header('Content-Type: application/json'); echo json_encode($entries); exit;
}

// Export anomaly log for a specific user/IP from drilldown modal, with optional date range
if (isset($_GET['anomaly_export']) && $_GET['anomaly_export'] && isset($_GET['type']) && in_array($_GET['type'], ['user_log','ip_log']) && isset($_GET['key']) && $_GET['key']) {
  $type = $_GET['type'];
  $key = $_GET['key'];
  $date_from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : null;
  $date_to = isset($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']) ? $_GET['to'] : null;
  $filename = 'anomaly_' . $type . '_' . preg_replace('/[^\w.\-]/','_',$key)
    . ($date_from ? '_from_' . $date_from : '') . ($date_to ? '_to_' . $date_to : '')
    . '_' . date('Ymd_His') . '.txt';
  header('Content-Type: text/plain');
  header('Content-Disposition: attachment; filename=' . $filename);
  $anomaly_log_file = __DIR__ . '/anomaly_log.txt';
  if (file_exists($anomaly_log_file)) {
    $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      $matches = false;
      if ($type === 'user_log' && preg_match('/User: ([\w@.\-]+)/i', $line, $um) && $um[1] === $key) $matches = true;
      if ($type === 'ip_log' && preg_match('/IP: ([\d.]+)/', $line, $ipm) && $ipm[1] === $key) $matches = true;
      if ($matches && ($date_from || $date_to)) {
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $dm)) {
          $log_date = $dm[1];
          if ($date_from && $log_date < $date_from) $matches = false;
          if ($date_to && $log_date > $date_to) $matches = false;
        }
      }
      if ($matches) echo $line . "\n";
    }
  }
  exit;
}

// Export filtered anomaly log (by date, type, keyword)
if (isset($_GET['anomaly_export']) && $_GET['anomaly_export'] && isset($_GET['type']) && $_GET['type'] === 'filtered_log') {
  $date_from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : null;
  $date_to = isset($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']) ? $_GET['to'] : null;
  $atype = isset($_GET['anomalytype']) ? $_GET['anomalytype'] : '';
  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  $filename = 'anomaly_filtered_log'
    . ($date_from ? '_from_' . $date_from : '')
    . ($date_to ? '_to_' . $date_to : '')
    . ($atype ? '_' . $atype : '')
    . ($search ? '_search_' . preg_replace('/[^\w.\-]/','_',$search) : '')
    . '_' . date('Ymd_His') . '.txt';
  header('Content-Type: text/plain');
  header('Content-Disposition: attachment; filename=' . $filename);
  $anomaly_log_file = __DIR__ . '/anomaly_log.txt';
  if (file_exists($anomaly_log_file)) {
    $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      $ok = true;
      if ($date_from || $date_to) {
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $dm)) {
          $log_date = $dm[1];
          if ($date_from && $log_date < $date_from) $ok = false;
          if ($date_to && $log_date > $date_to) $ok = false;
        }
      }
      if ($ok && $atype) {
        if ($atype === 'admin' && stripos($line, 'ANOMALY DETECTED') === false) $ok = false;
        if ($atype === 'feedback' && stripos($line, 'FEEDBACK ANOMALY') === false) $ok = false;
      }
      if ($ok && $search && stripos($line, $search) === false) $ok = false;
      if ($ok) echo $line . "\n";
    }
  }
  exit;
}

// Export filtered feedback analytics log (by date, type, user, keyword)
if (isset($_GET['feedback_export']) && $_GET['feedback_export']) {
  $date_from = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : null;
  $date_to = isset($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']) ? $_GET['to'] : null;
  $action = isset($_GET['action']) ? $_GET['action'] : '';
  $user = isset($_GET['user']) ? trim($_GET['user']) : '';
  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  $filename = 'feedback_filtered_log'
    . ($date_from ? '_from_' . $date_from : '')
    . ($date_to ? '_to_' . $date_to : '')
    . ($action ? '_' . $action : '')
    . ($user ? '_user_' . preg_replace('/[^\w.\-]/','_',$user) : '')
    . ($search ? '_search_' . preg_replace('/[^\w.\-]/','_',$search) : '')
    . '_' . date('Ymd_His') . '.csv';
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename=' . $filename);
  $sql = "SELECT * FROM ai_interactions WHERE 1=1";
  if ($date_from) $sql .= " AND DATE(ts) >= '" . mysqli_real_escape_string($con, $date_from) . "'";
  if ($date_to) $sql .= " AND DATE(ts) <= '" . mysqli_real_escape_string($con, $date_to) . "'";
  if ($action) $sql .= " AND action = '" . mysqli_real_escape_string($con, $action) . "'";
  if ($user) $sql .= " AND user = '" . mysqli_real_escape_string($con, $user) . "'";
  if ($search) $sql .= " AND (suggestion LIKE '%" . mysqli_real_escape_string($con, $search) . "%' OR feedback LIKE '%" . mysqli_real_escape_string($con, $search) . "%')";
  $sql .= " ORDER BY ts DESC";
  $res = mysqli_query($con, $sql);
  if ($res) {
    $header = false;
    while ($row = mysqli_fetch_assoc($res)) {
      if (!$header) { echo implode(',', array_keys($row)) . "\n"; $header = true; }
      $vals = array_map(function($v) { return '"'.str_replace('"','""',$v).'"'; }, array_values($row));
      echo implode(',', $vals) . "\n";
    }
  }
  exit;
}

// Fetch summary stats for filtered range/role
$summary = [ 'total' => 0, 'likes' => 0, 'dislikes' => 0, 'views' => 0 ];
$res = mysqli_query($con, "SELECT action, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql $role_sql GROUP BY action");
while($row = mysqli_fetch_assoc($res)) {
    $summary['total'] += $row['cnt'];
    if ($row['action'] === 'like') $summary['likes'] = $row['cnt'];
    if ($row['action'] === 'dislike') $summary['dislikes'] = $row['cnt'];
    if ($row['action'] === 'view') $summary['views'] = $row['cnt'];
}

// Fetch feedback by role for filtered range
$roles = ['customer','agent','builder','associate','admin'];
$role_data = [];
foreach ($roles as $role) {
    $r = ['like'=>0,'dislike'=>0,'view'=>0];
    $role_where = $role_sql ? $role_sql : "AND role='$role'";
    $res = mysqli_query($con, "SELECT action, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql $role_where GROUP BY action");
    while($row = mysqli_fetch_assoc($res)) $r[$row['action']] = $row['cnt'];
    $role_data[$role] = $r;
}

// --- Feedback Trends Data for Chart.js ---
$trend_labels = [];
$trend_counts = [];
$trend_likes = [];
$trend_dislikes = [];
for ($i = 29; $i >= 0; $i--) {
  $day = date('Y-m-d', strtotime("-$i days", strtotime($to)));
  $trend_labels[] = $day;
  $res = mysqli_query($con, "SELECT COUNT(*) as total, SUM(action='like') as likes, SUM(action='dislike') as dislikes FROM ai_interactions WHERE DATE(created_at)='$day' $role_sql");
  $row = mysqli_fetch_assoc($res);
  $trend_counts[] = (int)$row['total'];
  $trend_likes[] = (int)$row['likes'];
  $trend_dislikes[] = (int)$row['dislikes'];
}

// Unique IPs and User Agents per day for trend chart
$trend_unique_ips_labels = [];
$trend_unique_ips = [];
$trend_unique_agents = [];
$res = mysqli_query($con, "SELECT DATE(created_at) as day, COUNT(DISTINCT ip_address) as unique_ips, COUNT(DISTINCT user_agent) as unique_agents FROM ai_interactions WHERE $date_sql $role_sql GROUP BY day ORDER BY day");
while($row = mysqli_fetch_assoc($res)) {
  $trend_unique_ips_labels[] = $row['day'];
  $trend_unique_ips[] = $row['unique_ips'];
  $trend_unique_agents[] = $row['unique_agents'];
}

// Top suggestions for filtered range/role
$top_suggestions = mysqli_query($con, "SELECT suggestion_text, 
  SUM(action='like') as likes, 
  SUM(action='dislike') as dislikes 
  FROM ai_interactions 
  WHERE suggestion_text IS NOT NULL AND suggestion_text != '' 
    AND $date_sql $role_sql
  GROUP BY suggestion_text 
  HAVING likes > 0 OR dislikes > 0
  ORDER BY likes DESC, dislikes DESC 
  LIMIT 5");

// Suggestion Drilldown
$suggestion_filter = isset($_GET['suggestion']) && trim($_GET['suggestion']) ? trim($_GET['suggestion']) : '';
$suggestion_sql = $suggestion_filter ? "AND suggestion_text LIKE '%".mysqli_real_escape_string($con, $suggestion_filter)."%'" : '';
$drill_labels = [];
$drill_likes = [];
$drill_dislikes = [];
$drill_views = [];
for ($i=0; $i<=$days; $i++) {
  $date = date('Y-m-d', strtotime($from . "+$i days"));
  $drill_labels[] = $date;
  $where = "$date_sql AND DATE(created_at)='$date' $role_sql $suggestion_sql";
  $res = mysqli_query($con, "SELECT action, COUNT(*) as cnt FROM ai_interactions WHERE $where GROUP BY action");
  $likes=0; $dislikes=0; $views=0;
  while($row = mysqli_fetch_assoc($res)) {
    if ($row['action'] === 'like') $likes = $row['cnt'];
    if ($row['action'] === 'dislike') $dislikes = $row['cnt'];
    if ($row['action'] === 'view') $views = $row['cnt'];
  }
  $drill_likes[] = $likes;
  $drill_dislikes[] = $dislikes;
  $drill_views[] = $views;
}

// Top 5 IPs
$top_ips = [];
$res = mysqli_query($con, "SELECT ip_address, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql $role_sql GROUP BY ip_address ORDER BY cnt DESC LIMIT 5");
while($row = mysqli_fetch_assoc($res)) {
  $top_ips[] = $row;
}
// Top 5 User Agents
$top_agents = [];
$res = mysqli_query($con, "SELECT user_agent, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql $role_sql GROUP BY user_agent ORDER BY cnt DESC LIMIT 5");
while($row = mysqli_fetch_assoc($res)) {
  $top_agents[] = $row;
}

// Find top offenders for security/abuse alerts
$offender_ips = [];
$res = mysqli_query($con, "SELECT ip_address, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql $role_sql GROUP BY ip_address HAVING cnt > $offender_threshold ORDER BY cnt DESC");
while($row = mysqli_fetch_assoc($res)) $offender_ips[] = $row;
$offender_users = [];
$res = mysqli_query($con, "SELECT user_id, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql $role_sql GROUP BY user_id HAVING cnt > $offender_threshold ORDER BY cnt DESC");
while($row = mysqli_fetch_assoc($res)) $offender_users[] = $row;
$offender_agents = [];
$res = mysqli_query($con, "SELECT user_agent, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql $role_sql GROUP BY user_agent HAVING cnt > $offender_threshold ORDER BY cnt DESC");
while($row = mysqli_fetch_assoc($res)) $offender_agents[] = $row;

// Abuse alert email notification config
$admin_email = 'admin@example.com'; // TODO: set real admin email
// Track if new offenders detected (for email alert)
$alert_email_sent = false;
$alert_flag_file = __DIR__ . '/.abuse_alert_flag';
$offender_hash = // SECURITY: Removed potentially dangerous codejson_encode([$offender_ips, $offender_users, $offender_agents, $offender_threshold, $from, $to, $role_filter]));
$last_hash = @file_exists($alert_flag_file) ? trim(@file_get_contents($alert_flag_file)) : '';
if ((count($offender_ips) || count($offender_users) || count($offender_agents)) && $offender_hash !== $last_hash) {
    $subject = '[APS Admin] Abuse Alert Triggered';
    $body = "Abuse alert triggered at ".date('Y-m-d H:i:s').".\n\n";
    $body .= "Threshold: $offender_threshold\n";
    $body .= "Date Range: $from to $to\n";
    $body .= "Role: ".($role_filter ?: 'All')."\n\n";
    if (count($offender_ips)) {
        $body .= "IPs:\n";
        foreach($offender_ips as $ip) $body .= "- {$ip['ip_address']} ({$ip['cnt']})\n";
    }
    if (count($offender_users)) {
        $body .= "Users:\n";
        foreach($offender_users as $user) $body .= "- {$user['user_id']} ({$user['cnt']})\n";
    }
    if (count($offender_agents)) {
        $body .= "User Agents:\n";
        foreach($offender_agents as $ua) $body .= "- {$ua['user_agent']} ({$ua['cnt']})\n";
    }
    @mail($admin_email, $subject, $body);
    file_put_contents($alert_flag_file, $offender_hash);
    $alert_email_sent = true;
}

// --- Slack notification integration ---
function send_slack($msg) {
    $cfg_file = __DIR__ . '/scheduled_report_config.json';
    if (!file_exists($cfg_file)) return;
    $cfg = json_decode(file_get_contents($cfg_file), true);
    if (empty($cfg['slack_enabled']) || empty($cfg['slack_webhook'])) return;
    $payload = json_encode(['text' => $msg]);
    $ch = curl_init($cfg['slack_webhook']);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Content-Length: '.strlen($payload)]);
    curl_exec($ch);
    curl_close($ch);
}

// After abuse alert email, send Slack notification and SMS
if (!empty($offender_ips) || !empty($offender_users) || !empty($offender_agents)) {
    $msg = "[APS Admin] ABUSE ALERT: Threshold: $offender_threshold\nDate Range: $from to $to\nRole: ".($role_filter ?: 'All')."\n\n";
    if (count($offender_ips)) {
        $msg .= "IPs:\n";
        foreach($offender_ips as $ip) $msg .= "- {$ip['ip_address']} ({$ip['cnt']})\n";
    }
    if (count($offender_users)) {
        $msg .= "Users:\n";
        foreach($offender_users as $user) $msg .= "- {$user['user_id']} ({$user['cnt']})\n";
    }
    if (count($offender_agents)) {
        $msg .= "User Agents:\n";
        foreach($offender_agents as $ua) $msg .= "- {$ua['user_agent']} ({$ua['cnt']})\n";
    }
    $msg .= "\nTime: ".date('Y-m-d H:i:s');
    send_slack($msg);
    // SMS alert
    $cfg_file = __DIR__ . '/scheduled_report_config.json';
    if (file_exists($cfg_file)) {
        $cfg = json_decode(file_get_contents($cfg_file), true);
        send_sms_twilio($msg, $cfg);
    }
}

// --- Anomaly Detection for Block/Unblock/Abuse ---
$anomaly = [];
$window = 7;
$today_idx = array_search(date('Y-m-d', strtotime($to)), $event_labels);
$anomaly_msg = '';
if ($today_idx !== false) {
  $types = [
    'Blocks' => $block_counts,
    'Unblocks' => $unblock_counts,
    'Abuse Alerts' => $abuse_counts
  ];
  foreach ($types as $label => $arr) {
    $recent = array_slice($arr, max(0, $today_idx-$window), $window);
    $avg = $recent ? (array_sum($recent)/count($recent)) : 0;
    $today = $arr[$today_idx];
    if ($avg > 0 && $today >= 2*$avg && $today > 0) {
      $anomaly[] = "$label spike: $today today (avg $avg)";
    }
  }
  if (!empty($anomaly)) {
    $anomaly_msg = "[APS Admin] ANOMALY DETECTED: ".implode(' | ', $anomaly)." Time: ".date('Y-m-d H:i:s');
    // Send notifications
    $cfg_file = __DIR__ . '/scheduled_report_config.json';
    if (file_exists($cfg_file)) {
      $cfg = json_decode(file_get_contents($cfg_file), true);
      if (!empty($cfg['slack_enabled'])) send_slack($anomaly_msg);
      if (!empty($cfg['twilio_enabled'])) require_once(__DIR__ . '/send_sms_twilio.php');
      if (!empty($cfg['twilio_enabled'])) send_sms_twilio($anomaly_msg, $cfg);
      if (!empty($cfg['admin_email'])) @mail($cfg['admin_email'], 'APS Admin Anomaly Detected', $anomaly_msg);
    }
    // Log anomaly
    $anomaly_log = __DIR__ . '/anomaly_log.txt';
    file_put_contents($anomaly_log, $anomaly_msg."\n", FILE_APPEND);
  }
}

// --- Anomaly Detection for Feedback Trends ---
$feedback_anomaly = [];
$feedback_anomaly_msg = '';
if ($today_idx !== false) {
  $trend_types = [
    'Feedback' => $trend_counts,
    'Likes' => $trend_likes,
    'Dislikes' => $trend_dislikes
  ];
  foreach ($trend_types as $label => $arr) {
    $recent = array_slice($arr, max(0, $today_idx-$window), $window);
    $avg = $recent ? (array_sum($recent)/count($recent)) : 0;
    $today = $arr[$today_idx];
    if ($avg > 0 && $today >= 2*$avg && $today > 0) {
      $feedback_anomaly[] = "$label spike: $today today (avg $avg)";
    }
  }
  if (!empty($feedback_anomaly)) {
    $feedback_anomaly_msg = "[APS Admin] FEEDBACK ANOMALY: ".implode(' | ', $feedback_anomaly)." Time: ".date('Y-m-d H:i:s');
    $cfg_file = __DIR__ . '/scheduled_report_config.json';
    if (file_exists($cfg_file)) {
      $cfg = json_decode(file_get_contents($cfg_file), true);
      if (!empty($cfg['slack_enabled'])) send_slack($feedback_anomaly_msg);
      if (!empty($cfg['twilio_enabled'])) require_once(__DIR__ . '/send_sms_twilio.php');
      if (!empty($cfg['twilio_enabled'])) send_sms_twilio($feedback_anomaly_msg, $cfg);
      if (!empty($cfg['admin_email'])) @mail($cfg['admin_email'], 'APS Admin Feedback Anomaly', $feedback_anomaly_msg);
    }
    // Log anomaly
    $anomaly_log = __DIR__ . '/anomaly_log.txt';
    file_put_contents($anomaly_log, $feedback_anomaly_msg."\n", FILE_APPEND);
  }
}

// --- Block/Unblock & Abuse Trends Data for Chart.js ---
$event_labels = [];
$block_counts = [];
$unblock_counts = [];
$abuse_counts = [];
for ($i = 29; $i >= 0; $i--) {
  $day = date('Y-m-d', strtotime("-$i days", strtotime($to)));
  $event_labels[] = $day;
  // Block actions
  $block_counts[] = 0;
  $unblock_counts[] = 0;
  $abuse_counts[] = 0;
}
$audit_log = __DIR__ . '/blocklist_audit.log';
if (file_exists($audit_log)) {
  $lines = file($audit_log, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $cols = explode("\t", $line);
    if (count($cols) < 2) continue;
    $date = substr($cols[0], 0, 10);
    $action = $cols[1];
    $idx = array_search($date, $event_labels);
    if ($idx === false) continue;
    if ($action === 'BLOCK') $block_counts[$idx]++;
    if ($action === 'UNBLOCK') $unblock_counts[$idx]++;
    if ($action === 'ABUSE_ALERT') $abuse_counts[$idx]++;
  }
}

// CSV export for filtered anomaly log (all columns)
if (isset($_GET['anomaly_export']) && $_GET['anomaly_export'] == 'log') {
  $filename = 'anomaly_log_filtered_' . date('Ymd_His') . '.csv';
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename=' . $filename);
  $anomaly_log_file = __DIR__ . '/anomaly_log.txt';
  $search = isset($_GET['anomaly_search']) ? strtolower(trim($_GET['anomaly_search'])) : '';
  $type = isset($_GET['anomaly_type']) ? $_GET['anomaly_type'] : '';
  $date = isset($_GET['anomaly_date']) ? $_GET['anomaly_date'] : '';
  echo "DateTime,Type,User,IP,Message\n";
  if (file_exists($anomaly_log_file)) {
    $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $since = strtotime('-30 days');
    foreach ($lines as $line) {
      if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $m)) {
        $log_time = strtotime($m[1]);
        if ($log_time >= $since) {
          if ($search && strpos(strtolower($line), $search) === false) continue;
          if ($type === 'admin' && stripos($line, 'ANOMALY DETECTED') === false) continue;
          if ($type === 'feedback' && stripos($line, 'FEEDBACK ANOMALY') === false) continue;
          if ($date && strpos($m[1], $date) !== 0) continue;
          $log_type = (stripos($line, 'FEEDBACK ANOMALY') !== false) ? 'Feedback' : ((stripos($line, 'ANOMALY DETECTED') !== false) ? 'Admin/Security' : 'Other');
          $user = '';
          $ip = '';
          if (preg_match('/User: ([\w@.\-]+)/i', $line, $um)) $user = $um[1];
          if (preg_match('/IP: ([\d.]+)/', $line, $ipm)) $ip = $ipm[1];
          $msg = trim(preg_replace('/^.*?(ANOMALY DETECTED|FEEDBACK ANOMALY):/i', '', $line));
          echo '"' . $m[1] . '","' . $log_type . '","' . str_replace('"','""',$user) . '","' . str_replace('"','""',$ip) . '","' . str_replace('"','""',$msg) . '"\n';
        }
      }
    }
  }
  exit;
}

// CSV export for user/IP anomaly breakdowns (with type breakdown)
if (isset($_GET['anomaly_export']) && $_GET['anomaly_export'] && isset($_GET['type']) && in_array($_GET['type'], ['user_trend','ip_trend'])) {
  $type = $_GET['type'];
  $filename = 'anomaly_' . $type . '_breakdown_' . date('Ymd_His') . '.csv';
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename=' . $filename);
  $anomaly_log_file = __DIR__ . '/anomaly_log.txt';
  $trend_days = [];
  for ($i = 13; $i >= 0; $i--) $trend_days[] = date('Y-m-d', strtotime("-$i days"));
  $counts = [];
  if (file_exists($anomaly_log_file)) {
    $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $m)) {
        $log_date = $m[1];
        if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $m2)) {
          $log_time = strtotime($m2[1]);
          if ($log_time >= $since) {
            $is_admin = (stripos($line, 'ANOMALY DETECTED') !== false);
            $is_feedback = (stripos($line, 'FEEDBACK ANOMALY') !== false);
            if ($type === 'user' && preg_match('/User: ([\w@.\-]+)/i', $line, $um)) {
              $user = $um[1];
              if (!isset($counts[$user])) $counts[$user] = ['total'=>0,'admin'=>0,'feedback'=>0];
              $counts[$user]['total']++;
              if ($is_admin) $counts[$user]['admin']++;
              if ($is_feedback) $counts[$user]['feedback']++;
            }
            if ($type === 'ip' && preg_match('/IP: ([\d.]+)/', $line, $ipm)) {
              $ip = $ipm[1];
              if (!isset($counts[$ip])) $counts[$ip] = ['total'=>0,'admin'=>0,'feedback'=>0];
              $counts[$ip]['total']++;
              if ($is_admin) $counts[$ip]['admin']++;
              if ($is_feedback) $counts[$ip]['feedback']++;
            }
          }
        }
      }
    }
  }
  echo ($type === 'user' ? "User,Total,Admin/Security,Feedback\n" : "IP,Total,Admin/Security,Feedback\n");
  uasort($counts, function($a,$b){return $b['total']<=>$a['total'];});
  foreach ($counts as $k => $v) {
    echo '"' . str_replace('"','""',$k) . '",' . $v['total'] . ',' . $v['admin'] . ',' . $v['feedback'] . "\n";
  }
  exit;
}

// CSV export for user/IP anomaly trends (last 14 days, top 3)
if (isset($_GET['anomaly_export']) && $_GET['anomaly_export'] && isset($_GET['type']) && in_array($_GET['type'], ['user_trend','ip_trend'])) {
  $type = $_GET['type'];
  $filename = 'anomaly_' . $type . '_trend_' . date('Ymd_His') . '.csv';
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename=' . $filename);
  $anomaly_log_file = __DIR__ . '/anomaly_log.txt';
  $trend_days = [];
  for ($i = 13; $i >= 0; $i--) $trend_days[] = date('Y-m-d', strtotime("-$i days"));
  $counts_admin = $counts_feedback = [];
  if ($type === 'user_trend') {
    global $anomaly_by_user;
    $top = array_slice(array_keys($anomaly_by_user), 0, 3);
    foreach ($top as $u) {
      foreach ($trend_days as $d) {
        $counts_admin[$u][$d] = 0;
        $counts_feedback[$u][$d] = 0;
      }
    }
  } else {
    global $anomaly_by_ip;
    $top = array_slice(array_keys($anomaly_by_ip), 0, 3);
    foreach ($top as $ip) {
      foreach ($trend_days as $d) {
        $counts_admin[$ip][$d] = 0;
        $counts_feedback[$ip][$d] = 0;
      }
    }
  }
  if (file_exists($anomaly_log_file)) {
    $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $m)) {
        $log_date = $m[1];
        if (in_array($log_date, $trend_days)) {
          $is_admin = (stripos($line, 'ANOMALY DETECTED') !== false);
          $is_feedback = (stripos($line, 'FEEDBACK ANOMALY') !== false);
          if ($type === 'user_trend' && preg_match('/User: ([\w@.\-]+)/i', $line, $um)) {
            $user = $um[1];
            if (isset($counts_admin[$user])) {
              if ($is_admin) $counts_admin[$user][$log_date]++;
              if ($is_feedback) $counts_feedback[$user][$log_date]++;
            }
          }
          if ($type === 'ip_trend' && preg_match('/IP: ([\d.]+)/', $line, $ipm)) {
            $ip = $ipm[1];
            if (isset($counts_admin[$ip])) {
              if ($is_admin) $counts_admin[$ip][$log_date]++;
              if ($is_feedback) $counts_feedback[$ip][$log_date]++;
            }
          }
        }
      }
    }
  }
  // Output header
  echo ($type === 'user_trend' ? 'User' : 'IP') . ',Type,' . implode(',', $trend_days) . "\n";
  foreach (($type === 'user_trend' ? $counts_admin : $counts_admin) as $k => $row) {
    echo '"' . str_replace('"','""',$k) . '",Admin/Security,' . implode(',', $row) . "\n";
    echo '"' . str_replace('"','""',$k) . '",Feedback,' . implode(',', $counts_feedback[$k]) . "\n";
  }
  exit;
}

// Output HTML (Bootstrap + Chart.js)
?>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AI Feedback Analytics</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<div class="container my-4">
  <?php if (count($offender_ips) || count($offender_users) || count($offender_agents)): ?>
    <form method="get" class="mb-2 d-inline-block">
      <label for="abuse_threshold" class="form-label mb-0 me-2 fw-bold">Abuse Alert Threshold:</label>
      <input type="number" min="1" step="1" name="abuse_threshold" id="abuse_threshold" value="<?php echo $offender_threshold; ?>" class="form-control form-control-sm d-inline-block w-auto me-2" style="width:90px;display:inline-block;">
      <?php foreach ($_GET as $k=>$v) { if ($k!=='abuse_threshold') echo '<input type="hidden" name="'.htmlspecialchars($k).'" value="'.htmlspecialchars($v).'">'; } ?>
      <button type="submit" class="btn btn-sm btn-outline-danger">Apply</button>
    </form>
    <div class="alert alert-danger d-flex align-items-center mb-4">
      <i class="fa fa-exclamation-triangle fa-2x me-3"></i>
      <div>
        <strong>Potential Abuse Detected!</strong> The following IPs, users, or devices have submitted unusually high feedback (<?php echo $offender_threshold; ?>+ in the selected period):
        <ul class="mb-0">
          <?php foreach ($offender_ips as $ip): ?>
            <li>IP: <a href="#" class="drill-ip fw-bold text-danger" data-ip="<?php echo htmlspecialchars($ip['ip_address']); ?>"><?php echo htmlspecialchars($ip['ip_address']); ?></a> (<?php echo $ip['cnt']; ?>)
              <button class="btn btn-xs btn-outline-danger btn-block-entity ms-2" data-type="ip" data-value="<?php echo htmlspecialchars($ip['ip_address']); ?>"><i class="fa fa-ban"></i> Block</button>
            </li>
          <?php endforeach; ?>
          <?php foreach ($offender_users as $user): ?>
            <li>User ID: <a href="#" class="drill-user fw-bold text-danger" data-user="<?php echo $user['user_id']; ?>"><?php echo $user['user_id']; ?></a> (<?php echo $user['cnt']; ?>)
              <button class="btn btn-xs btn-outline-danger btn-block-entity ms-2" data-type="user" data-value="<?php echo $user['user_id']; ?>"><i class="fa fa-ban"></i> Block</button>
            </li>
          <?php endforeach; ?>
          <?php foreach ($offender_agents as $ua): ?>
            <li>User Agent: <span class="fw-bold text-danger"><?php echo htmlspecialchars($ua['user_agent']); ?></span> (<?php echo $ua['cnt']; ?>)</li>
          <?php endforeach; ?>
        </ul>
        <span class="small">Review and take action if these are not expected.</span>
      </div>
    </div>
  <?php endif; ?>
  <button class="btn btn-sm btn-outline-secondary mb-3" id="manageBlocklistBtn"><i class="fa fa-shield-alt me-1"></i>Manage Blocklist</button>
  <a href="#" class="btn btn-sm btn-outline-dark mb-3 ms-2" id="viewAuditLogBtn"><i class="fa fa-file-alt me-1"></i>View Blocklist Audit Log</a>
  <button class="btn btn-sm btn-outline-success mb-3 ms-2" id="exportAuditLogBtn"><i class="fa fa-download me-1"></i>Export Audit Log (CSV)</button>
  <a href="scheduled_report_settings.php" class="btn btn-sm btn-outline-primary mb-3 ms-2"><i class="fa fa-cog me-1"></i>Scheduled Reports Settings</a>
  <h2 class="mb-3"><i class="fa fa-chart-bar me-2"></i>AI Feedback Analytics</h2>
  <div class="d-flex justify-content-end mb-3">
    <form method="get" action="admin/export_ai_analytics.php" target="_blank">
      <input type="hidden" name="from" value="<?php echo htmlspecialchars($from); ?>">
      <input type="hidden" name="to" value="<?php echo htmlspecialchars($to); ?>">
      <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
      <button type="submit" class="btn btn-outline-info btn-sm">
        <i class="fa fa-download"></i> Export Filtered Analytics (CSV)
      </button>
    </form>
  </div>
  <div class="card mb-4">
    <div class="card-header bg-light"><i class="fa fa-filter me-2"></i>Filter Analytics</div>
    <div class="card-body">
      <form class="row g-2 align-items-end" method="get">
        <div class="col-auto">
          <label for="daterange" class="form-label mb-0">Date Range</label>
          <input type="date" class="form-control form-control-sm" name="from" value="<?php echo htmlspecialchars($_GET['from'] ?? date('Y-m-d', strtotime('-30 days'))); ?>">
        </div>
        <div class="col-auto">
          <input type="date" class="form-control form-control-sm" name="to" value="<?php echo htmlspecialchars($_GET['to'] ?? date('Y-m-d')); ?>">
        </div>
        <div class="col-auto">
          <label for="role" class="form-label mb-0">Role</label>
          <select class="form-select form-select-sm" name="role">
            <option value="">All Roles</option>
            <?php foreach ($roles as $r) echo '<option value="'.$r.'"'.(isset($_GET['role'])&&$_GET['role']==$r?' selected':'').'>'.ucfirst($r).'</option>'; ?>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search me-1"></i>Apply</button>
        </div>
      </form>
    </div>
  </div>
  <div class="row mb-4">
    <div class="col-md-3"><div class="card text-center"><div class="card-body"><div class="fs-2 fw-bold text-primary"><?php echo $summary['total']; ?></div><div>Total Feedback</div></div></div></div>
    <div class="col-md-3"><div class="card text-center"><div class="card-body"><div class="fs-2 fw-bold text-success feedback-count" data-type="like"><?php echo $summary['likes']; ?></div><div>Likes</div></div></div></div>
    <div class="col-md-3"><div class="card text-center"><div class="card-body"><div class="fs-2 fw-bold text-danger feedback-count" data-type="dislike"><?php echo $summary['dislikes']; ?></div><div>Dislikes</div></div></div></div>
    <div class="col-md-3"><div class="card text-center"><div class="card-body"><div class="fs-2 fw-bold text-secondary feedback-count" data-type="view"><?php echo $summary['views']; ?></div><div>Views</div></div></div></div>
  </div>
  <div class="card mb-4">
    <div class="card-header bg-info text-white"><i class="fa fa-users me-2"></i>Feedback by Role</div>
    <div class="card-body">
      <canvas id="roleFeedbackChart" height="80"></canvas>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header bg-secondary text-white"><i class="fa fa-calendar me-2"></i>Feedback Trends (Last 30 Days)</div>
    <div class="card-body">
      <canvas id="trendChart" height="80"></canvas>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header bg-info text-white"><i class="fa fa-chart-line me-2"></i>Unique IPs & User Agents Per Day</div>
    <div class="card-body">
      <canvas id="uniqueTrendsChart" height="80"></canvas>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header bg-dark text-white"><i class="fa fa-bug me-2"></i>Anomaly Log (Last 30 Days)</div>
    <div class="card-body">
      <?php
      // --- Anomaly log statistics ---
      $anomaly_log_file = __DIR__ . '/anomaly_log.txt';
      $search = isset($_GET['anomaly_search']) ? strtolower(trim($_GET['anomaly_search'])) : '';
      $type = isset($_GET['anomaly_type']) ? $_GET['anomaly_type'] : '';
      $date = isset($_GET['anomaly_date']) ? $_GET['anomaly_date'] : '';
      $anomaly_total = $anomaly_admin = $anomaly_feedback = 0;
      $anomaly_by_user = [];
      $anomaly_by_ip = [];
      $anomaly_by_date = $anomaly_admin_by_date = $anomaly_feedback_by_date = [];
      if (file_exists($anomaly_log_file)) {
        $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $since = strtotime('-30 days');
        foreach ($lines as $line) {
          if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $m)) {
            $log_date = $m[1];
            if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $m2)) {
              $log_time = strtotime($m2[1]);
              if ($log_time >= $since) {
                $anomaly_total++;
                // Try to extract user and IP if present (format: ...User: xyz...IP: 1.2.3.4...)
                if (preg_match('/User: ([\w@.\-]+)/i', $line, $um)) {
                  $user = $um[1];
                  $anomaly_by_user[$user] = ($anomaly_by_user[$user] ?? 0) + 1;
                }
                if (preg_match('/IP: ([\d.]+)/', $line, $ipm)) {
                  $ip = $ipm[1];
                  $anomaly_by_ip[$ip] = ($anomaly_by_ip[$ip] ?? 0) + 1;
                }
                if (stripos($line, 'ANOMALY DETECTED') !== false) {
                  $anomaly_admin++;
                  $anomaly_admin_by_date[$log_date] = ($anomaly_admin_by_date[$log_date] ?? 0) + 1;
                }
                if (stripos($line, 'FEEDBACK ANOMALY') !== false) {
                  $anomaly_feedback++;
                  $anomaly_feedback_by_date[$log_date] = ($anomaly_feedback_by_date[$log_date] ?? 0) + 1;
                }
                $anomaly_by_date[$log_date] = ($anomaly_by_date[$log_date] ?? 0) + 1;
              }
            }
          }
        }
      }
      ?>
      <div class="row mb-2">
        <div class="col-sm-4">
          <div class="alert alert-info p-2 mb-1" style="font-size:13px;">
            <strong>Top Users:</strong>
            <?php
            arsort($anomaly_by_user);
            $top = 0;
            echo '<a href="?anomaly_export=1&type=user" class="btn btn-outline-success btn-sm me-2"><i class="fa fa-download me-1"></i>Export CSV</a>';
            foreach ($anomaly_by_user as $user => $cnt) {
              if (++$top > 5) break;
              $url = '?anomaly_search=' . urlencode($user);
              $admin = isset($anomaly_admin_by_user[$user]) ? $anomaly_admin_by_user[$user] : 0;
              $feedback = isset($anomaly_feedback_by_user[$user]) ? $anomaly_feedback_by_user[$user] : 0;
              echo '<a href="' . $url . '" class="badge bg-secondary text-decoration-none me-1">' . htmlspecialchars($user) . ' <span title="Admin/Security" class="badge bg-danger ms-1">' . $admin . '</span> <span title="Feedback" class="badge bg-warning text-dark ms-1">' . $feedback . '</span> ' . $cnt . '</a> ';
            }
            if ($top === 0) echo 'N/A';
            ?>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="alert alert-light p-2 mb-1" style="font-size:13px;">
            <strong>Top IPs:</strong>
            <?php
            arsort($anomaly_by_ip);
            $top = 0;
            echo '<a href="?anomaly_export=1&type=ip" class="btn btn-outline-success btn-sm me-2"><i class="fa fa-download me-1"></i>Export CSV</a>';
            foreach ($anomaly_by_ip as $ip => $cnt) {
              if (++$top > 5) break;
              $url = '?anomaly_search=' . urlencode($ip);
              $admin = isset($anomaly_admin_by_ip[$ip]) ? $anomaly_admin_by_ip[$ip] : 0;
              $feedback = isset($anomaly_feedback_by_ip[$ip]) ? $anomaly_feedback_by_ip[$ip] : 0;
              echo '<a href="' . $url . '" class="badge bg-secondary text-decoration-none me-1">' . htmlspecialchars($ip) . ' <span title="Admin/Security" class="badge bg-danger ms-1">' . $admin . '</span> <span title="Feedback" class="badge bg-warning text-dark ms-1">' . $feedback . '</span> ' . $cnt . '</a> ';
            }
            if ($top === 0) echo 'N/A';
            ?>
          </div>
        </div>
      </div>
      <div class="row mb-2">
        <div class="col-sm-6">
          <a href="?anomaly_export=1&type=user_trend" class="btn btn-outline-success btn-sm mb-2"><i class="fa fa-download"></i> Export User Trend CSV</a>
        </div>
        <div class="col-sm-6">
          <a href="?anomaly_export=1&type=ip_trend" class="btn btn-outline-success btn-sm mb-2"><i class="fa fa-download"></i> Export IP Trend CSV</a>
        </div>
      </div>
      <form method="get" class="mb-2" id="anomalyLogFilterForm">
        <div class="row g-2 align-items-center">
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm" name="anomaly_search" id="anomaly_search" placeholder="Search anomaly log..." value="<?php echo isset($_GET['anomaly_search']) ? htmlspecialchars($_GET['anomaly_search']) : ''; ?>">
          </div>
          <div class="col-auto">
            <select class="form-select form-select-sm" name="anomaly_type" id="anomaly_type">
              <option value="">All Types</option>
              <option value="admin" <?php if(isset($_GET['anomaly_type']) && $_GET['anomaly_type']==='admin') echo 'selected'; ?>>Admin/Security</option>
              <option value="feedback" <?php if(isset($_GET['anomaly_type']) && $_GET['anomaly_type']==='feedback') echo 'selected'; ?>>Feedback</option>
            </select>
          </div>
          <div class="col-auto">
            <input type="date" class="form-control form-control-sm" name="anomaly_date" id="anomaly_date" value="<?php echo isset($_GET['anomaly_date']) ? htmlspecialchars($_GET['anomaly_date']) : ''; ?>">
          </div>
          <div class="col-auto">
            <button class="btn btn-outline-secondary btn-sm" type="submit"><i class="fa fa-search"></i></button>
          </div>
          <div class="col-auto">
            <button class="btn btn-outline-success btn-sm" name="anomaly_export" value="log" type="submit"><i class="fa fa-download me-1"></i>Export Log CSV</button>
          </div>
        </div>
      </form>
      <?php
      // Export filtered anomaly log as download if requested
      if (isset($_GET['anomaly_export']) && $_GET['anomaly_export']) {
        $filename = 'anomaly_log_filtered_' . date('Ymd_His') . '.txt';
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=' . $filename);
        $anomaly_log_file = __DIR__ . '/anomaly_log.txt';
        $search = isset($_GET['anomaly_search']) ? strtolower(trim($_GET['anomaly_search'])) : '';
        $type = isset($_GET['anomaly_type']) ? $_GET['anomaly_type'] : '';
        $date = isset($_GET['anomaly_date']) ? $_GET['anomaly_date'] : '';
        if (file_exists($anomaly_log_file)) {
          $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
          $since = strtotime('-30 days');
          foreach ($lines as $line) {
            if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $m)) {
              $log_time = strtotime($m[1]);
              if ($log_time >= $since) {
                if ($search && strpos(strtolower($line), $search) === false) return false;
                if ($type === 'admin' && stripos($line, 'ANOMALY DETECTED') === false) return false;
                if ($type === 'feedback' && stripos($line, 'FEEDBACK ANOMALY') === false) return false;
                if ($date && strpos($m[1], $date) !== 0) return false;
                echo $line . "\n";
              }
            }
          }
        }
        exit;
      }
      ?>
      <pre style="max-height:260px;overflow:auto;font-size:13px;background:#222;color:#f8f8f2;padding:1em;border-radius:6px;">
<?php
$anomaly_log_file = __DIR__ . '/anomaly_log.txt';
$search = isset($_GET['anomaly_search']) ? strtolower(trim($_GET['anomaly_search'])) : '';
$type = isset($_GET['anomaly_type']) ? $_GET['anomaly_type'] : '';
$date = isset($_GET['anomaly_date']) ? $_GET['anomaly_date'] : '';
if (file_exists($anomaly_log_file)) {
  $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
  $since = strtotime('-30 days');
  $found = false;
  foreach ($lines as $line) {
    if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $m)) {
      $log_date = $m[1];
      if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $m2)) {
        $log_time = strtotime($m2[1]);
        if ($log_time >= $since) {
          if ($search && strpos(strtolower($line), $search) === false) return false;
          if ($type === 'admin' && stripos($line, 'ANOMALY DETECTED') === false) return false;
          if ($type === 'feedback' && stripos($line, 'FEEDBACK ANOMALY') === false) return false;
          if ($date && strpos($m[1], $date) !== 0) return false;
          echo htmlspecialchars($line)."\n";
          $found = true;
        }
      }
    }
  }
  if (!$found) echo "No matching anomaly log entries.";
} else {
  echo "No anomaly log entries.";
}
?>
      </pre>
      <a href="anomaly_log.txt" class="btn btn-sm btn-outline-secondary mt-2" download><i class="fa fa-download"></i> Download Full Anomaly Log</a>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-md-6">
      <canvas id="userAnomalyPie" height="120"></canvas>
      <div class="text-center" style="font-size:13px;">
        <span class="badge bg-danger me-2">Admin/Security</span>
        <span class="badge bg-warning text-dark">Feedback</span>
      </div>
    </div>
    <div class="col-md-6">
      <canvas id="ipAnomalyPie" height="120"></canvas>
      <div class="text-center" style="font-size:13px;">
        <span class="badge bg-danger me-2">Admin/Security</span>
        <span class="badge bg-warning text-dark">Feedback</span>
      </div>
    </div>
  </div>
  <div class="row mb-4">
    <div class="col-md-6">
      <canvas id="userAnomalyTrend" height="120"></canvas>
      <div class="text-center" style="font-size:13px;">
        <span class="badge bg-primary me-2">User Anomaly Trend (Top 3)</span>
      </div>
    </div>
    <div class="col-md-6">
      <canvas id="ipAnomalyTrend" height="120"></canvas>
      <div class="text-center" style="font-size:13px;">
        <span class="badge bg-primary me-2">IP Anomaly Trend (Top 3)</span>
      </div>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header bg-warning text-white"><i class="fa fa-exclamation-triangle me-2"></i>Block/Unblock & Abuse Trends (Last 30 Days)</div>
    <div class="card-body">
      <canvas id="eventTrendChart" height="80"></canvas>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header bg-warning text-white"><i class="fa fa-star me-2"></i>Top Suggestions by Feedback</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Suggestion</th>
              <th>Likes</th>
              <th>Dislikes</th>
            </tr>
          </thead>
          <tbody>
          <?php
            while($row = mysqli_fetch_assoc($top_suggestions)) {
              echo '<tr>';
              echo '<td><a href="#" class="drill-suggestion" data-suggestion="'.htmlspecialchars($row['suggestion_text']).'">'.htmlspecialchars($row['suggestion_text']).'</a></td>';
              echo '<td class="text-success fw-bold">'.$row['likes'].'</td>';
              echo '<td class="text-danger fw-bold">'.$row['dislikes'].'</td>';
              echo '</tr>';
            }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header bg-success text-white"><i class="fa fa-balance-scale me-2"></i>Like/Dislike Ratio (Pie)</div>
    <div class="card-body">
      <canvas id="ratioChart" height="80"></canvas>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header bg-info text-white"><i class="fa fa-search-plus me-2"></i>Suggestion Drilldown (Last 30 Days)</div>
    <div class="card-body">
      <form method="get" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="from" value="<?php echo htmlspecialchars($from); ?>">
        <input type="hidden" name="to" value="<?php echo htmlspecialchars($to); ?>">
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
        <div class="col-auto">
          <label for="suggestion" class="form-label mb-0">Suggestion Text</label>
          <input type="text" class="form-control form-control-sm" name="suggestion" value="<?php echo htmlspecialchars($_GET['suggestion'] ?? ''); ?>" placeholder="Search by text...">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search me-1"></i>Drilldown</button>
        </div>
        <div class="col-auto">
          <form method="get" action="admin/export_ai_drilldown.php" target="_blank">
            <input type="hidden" name="from" value="<?php echo htmlspecialchars($from); ?>">
            <input type="hidden" name="to" value="<?php echo htmlspecialchars($to); ?>">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
            <input type="hidden" name="suggestion" value="<?php echo htmlspecialchars($suggestion_filter); ?>">
            <button type="submit" class="btn btn-outline-info btn-sm">
              <i class="fa fa-download me-1"></i>Export Drilldown (CSV)
            </button>
          </form>
        </div>
      </form>
      <canvas id="drilldownChart" height="80"></canvas>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
          <span><i class="fa fa-globe me-2"></i>Top 5 IP Addresses</span>
          <a href="export_ai_ips_agents.php?from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&role=<?php echo urlencode($role); ?>" class="btn btn-sm btn-outline-light" title="Export All IPs & User Agents as CSV"><i class="fa fa-download"></i> Export All</a>
        </div>
        <div class="card-body p-2">
          <table class="table table-sm table-striped mb-0">
            <thead><tr><th>IP Address</th><th>Count</th></tr></thead>
            <tbody>
            <?php foreach ($top_ips as $ip): ?>
              <tr<?php if ($ip['cnt'] > 50) echo ' class="table-danger" title="High activity: possible abuse"'; ?>>
                <td><a href="#" class="drill-ip" data-ip="<?php echo htmlspecialchars($ip['ip_address']); ?>"><?php echo htmlspecialchars($ip['ip_address']); ?></a><?php if ($ip['cnt'] > 50) echo ' <i class="fa fa-exclamation-triangle text-danger" title="High activity"></i>'; ?></td>
                <td><?php echo $ip['cnt']; ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <div class="alert alert-warning py-2 small mt-2 mb-0"><i class="fa fa-exclamation-triangle me-1"></i> Entries highlighted in red indicate high activity (&gt;50 feedbacks in the selected period), which may warrant review for abuse or bots.</div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header bg-secondary text-white"><i class="fa fa-desktop me-2"></i>Top 5 User Agents</div>
        <div class="card-body p-2">
          <table class="table table-sm table-striped mb-0">
            <thead><tr><th>User Agent</th><th>Count</th></tr></thead>
            <tbody>
            <?php foreach ($top_agents as $ua): ?>
              <tr<?php if ($ua['cnt'] > 50) echo ' class="table-danger" title="High activity: possible abuse"'; ?>>
                <td style="max-width:220px;white-space:nowrap;overflow-x:auto;"><a href="#" class="drill-agent" data-agent="<?php echo htmlspecialchars($ua['user_agent']); ?>"><?php echo htmlspecialchars($ua['user_agent']); ?></a><?php if ($ua['cnt'] > 50) echo ' <i class="fa fa-exclamation-triangle text-danger" title="High activity"></i>'; ?></td>
                <td><?php echo $ua['cnt']; ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <div class="alert alert-warning py-2 small mt-2 mb-0"><i class="fa fa-exclamation-triangle me-1"></i> Entries highlighted in red indicate high activity (&gt;50 feedbacks in the selected period), which may warrant review for abuse or bots.</div>
        </div>
      </div>
    </div>
  </div>
  <!-- Drilldown Modal -->
  <div class="modal fade" id="drilldownModal" tabindex="-1" aria-labelledby="drilldownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="drilldownModalLabel">Drilldown Results</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <a id="drilldownExportBtn" href="#" class="btn btn-outline-primary" target="_blank"><i class="fa fa-download"></i> Export Drilldown (CSV)</a>
          </div>
          <div id="drilldownResults">Loading...</div>
        </div>
      </div>
    </div>
  </div>
  <!-- Blocklist Modal -->
  <div class="modal fade" id="blocklistModal" tabindex="-1" aria-labelledby="blocklistModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="card-header bg-dark text-white"><i class="fa fa-shield-alt me-2"></i>Blocked IPs & Users</div>
        <div class="card-body" id="blocklistModalBody">
          Loading...
        </div>
      </div>
    </div>
  </div>
  <!-- Audit Log Modal -->
  <div class="modal fade" id="auditLogModal" tabindex="-1" aria-labelledby="auditLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="auditLogModalLabel"><i class="fa fa-file-alt me-2"></i>Blocklist Audit Log</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="row g-2 mb-2" id="auditLogFilterForm">
            <div class="col-auto"><input type="text" class="form-control form-control-sm" id="auditSearch" placeholder="Search"></div>
            <div class="col-auto"><select class="form-select form-select-sm" id="auditAction"><option value="">All Actions</option><option value="BLOCK">BLOCK</option><option value="UNBLOCK">UNBLOCK</option></select></div>
            <div class="col-auto"><select class="form-select form-select-sm" id="auditType"><option value="">All Types</option><option value="ip">IP</option><option value="user">User</option></select></div>
            <div class="col-auto"><input type="text" class="form-control form-control-sm" id="auditValue" placeholder="Value"></div>
            <div class="col-auto"><input type="text" class="form-control form-control-sm" id="auditAdmin" placeholder="Admin"></div>
            <div class="col-auto"><input type="date" class="form-control form-control-sm" id="auditDateFrom"></div>
            <div class="col-auto"><input type="date" class="form-control form-control-sm" id="auditDateTo"></div>
          </form>
          <div id="auditLogModalBody">Loading...</div>
        </div>
      </div>
    </div>
  </div>
  <!-- Drilldown modal for user/IP anomaly trends -->
  <div class="modal fade" id="anomalyTrendDrilldownModal" tabindex="-1" aria-labelledby="anomalyTrendDrilldownLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="anomalyTrendDrilldownLabel">Anomaly Trend Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="anomalyTrendDrilldownBody">
          <!-- Populated by JS -->
        </div>
      </div>
    </div>
  </div>
  <!-- ... -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  // ...existing JS...
  let auditLogRawLines = [];
  let auditLogLastFiltered = [];
  $('#viewAuditLogBtn').on('click', function(e){
    e.preventDefault();
    $('#auditLogModalBody').html('Loading...');
    $('#auditLogModal').modal('show');
    $.get('blocklist_audit.log', function(data){
      auditLogRawLines = data.trim() ? data.trim().// SECURITY: Replaced deprecated function'\n') : [];
      renderAuditLogTable();
    });
  });
  function renderAuditLogTable() {
    let search = ($('#auditSearch').val()||'').toLowerCase();
    let action = $('#auditAction').val();
    let type = $('#auditType').val();
    let value = ($('#auditValue').val()||'').toLowerCase();
    let admin = ($('#auditAdmin').val()||'').toLowerCase();
    let dateFrom = $('#auditDateFrom').val();
    let dateTo = $('#auditDateTo').val();
    let html = '<pre style="font-size:13px;max-height:400px;overflow:auto;">';
    html += 'Timestamp           | Action   | Type | Value           | Admin\n';
    html += '--------------------+----------+------+-----------------+--------\n';
    let filtered = auditLogRawLines.filter(function(line){
      var cols = line.// SECURITY: Replaced deprecated function'\t');
      if (!cols[0]) return false;
      let [ts, act, typ, val, adm] = cols;
      if (action && act !== action) return false;
      if (type && typ !== type) return false;
      if (value && (!val || val.toLowerCase().indexOf(value) === false)) return false;
      if (admin && (!adm || adm.toLowerCase().indexOf(admin) === false)) return false;
      if (search && line.toLowerCase().indexOf(search) === false) return false;
      if (dateFrom && ts < dateFrom) return false;
      if (dateTo && ts > dateTo+' 23:59:59') return false;
      return true;
    });
    auditLogLastFiltered = filtered;
    if (!filtered.length) {
      html += '<span class="text-muted">No matching entries.</span>';
    } else {
      filtered.forEach(function(line){
        var cols = line.// SECURITY: Replaced deprecated function'\t');
        html += (cols[0]||'').padEnd(20) + ' | ' + (cols[1]||'').padEnd(8) + ' | ' + (cols[2]||'').padEnd(4) + ' | ' + (cols[3]||'').padEnd(15) + ' | ' + (cols[4]||'') + '\n';
      });
    }
    html += '</pre>';
    $('#auditLogModalBody').html(html);
  }
  $('#auditLogFilterForm input, #auditLogFilterForm select').on('input change', function(){
    renderAuditLogTable();
  });
  // Export filtered audit log as CSV
  $('#exportAuditLogBtn').on('click', function(){
    let csv = 'Timestamp,Action,Type,Value,Admin\n';
    auditLogLastFiltered.forEach(function(line){
      let cols = line.// SECURITY: Replaced deprecated function'\t');
      csv += [cols[0],cols[1],cols[2],cols[3],cols[4]].map(x=>`"${(x||'').replace(/"/g,'""')}"`).join(',')+'\n';
    });
    let blob = new Blob([csv], {type: 'text/csv'});
    let url = URL.createObjectURL(blob);
    let a = document.createElement('a');
    a.href = url;
    a.download = 'blocklist_audit_filtered_'+(new Date().toISOString().slice(0,10))+'.csv';
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });
  // Feedback Trends Chart
  const trendLabels = <?php echo json_encode($trend_labels); ?>;
  const trendCounts = <?php echo json_encode($trend_counts); ?>;
  const trendLikes = <?php echo json_encode($trend_likes); ?>;
  const trendDislikes = <?php echo json_encode($trend_dislikes); ?>;
  const ctxTrend = document.getElementById('trendChart').getContext('2d');
  new Chart(ctxTrend, {
    type: 'line',
    data: {
      labels: trendLabels,
      datasets: [
        { label: 'Total', data: trendCounts, borderColor: '#007bff', backgroundColor: 'rgba(0,123,255,0.1)', fill: true },
        { label: 'Likes', data: trendLikes, borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,0.1)', fill: false },
        { label: 'Dislikes', data: trendDislikes, borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,0.1)', fill: false }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: true } },
      scales: { x: { display: true }, y: { display: true, beginAtZero: true } }
    }
  });
  // Block/Unblock & Abuse Trends Chart
  const eventLabels = <?php echo json_encode($event_labels); ?>;
  const blockCounts = <?php echo json_encode($block_counts); ?>;
  const unblockCounts = <?php echo json_encode($unblock_counts); ?>;
  const abuseCounts = <?php echo json_encode($abuse_counts); ?>;
  const ctxEventTrend = document.getElementById('eventTrendChart').getContext('2d');
  new Chart(ctxEventTrend, {
    type: 'bar',
    data: {
      labels: eventLabels,
      datasets: [
        { label: 'Blocks', data: blockCounts, backgroundColor: 'rgba(220,53,69,0.7)' },
        { label: 'Unblocks', data: unblockCounts, backgroundColor: 'rgba(40,167,69,0.7)' },
        { label: 'Abuse Alerts', data: abuseCounts, backgroundColor: 'rgba(255,193,7,0.7)' }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: true } },
      scales: { x: { display: true }, y: { display: true, beginAtZero: true } }
    }
  });
  // Anomaly stats chart (by date/type)
  const anomalyStatsLabels = <?php echo json_encode(array_keys($anomaly_by_date)); ?>;
  const anomalyStatsCounts = <?php echo json_encode(array_values($anomaly_by_date)); ?>;
  const anomalyAdminCounts = <?php
    $arr = [];
    foreach(array_keys($anomaly_by_date) as $d) $arr[] = $anomaly_admin_by_date[$d] ?? 0;
    echo json_encode($arr);
  ?>;
  const anomalyFeedbackCounts = <?php
    $arr = [];
    foreach(array_keys($anomaly_by_date) as $d) $arr[] = $anomaly_feedback_by_date[$d] ?? 0;
    echo json_encode($arr);
  ?>;
  if (anomalyStatsLabels.length > 0) {
    const ctxAnomalyStats = document.getElementById('anomalyStatsChart').getContext('2d');
    const anomalyStatsChart = new Chart(ctxAnomalyStats, {
      type: 'bar',
      data: {
        labels: anomalyStatsLabels,
        datasets: [
          { label: 'Total', data: anomalyStatsCounts, backgroundColor: 'rgba(52,58,64,0.7)' },
          { label: 'Admin/Security', data: anomalyAdminCounts, backgroundColor: 'rgba(220,53,69,0.6)' },
          { label: 'Feedback', data: anomalyFeedbackCounts, backgroundColor: 'rgba(255,193,7,0.7)' }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: { x: { display: true }, y: { display: true, beginAtZero: true } },
        onClick: function(e, elements) {
          if (elements && elements.length > 0) {
            const idx = elements[0].index;
            const date = anomalyStatsLabels[idx];
            document.getElementById('anomaly_date').value = date;
            document.getElementById('anomalyLogFilterForm').submit();
          }
        }
      }
    });
  }
  // User anomaly type pie chart (top 5 users)
  const userPieCtx = document.getElementById('userAnomalyPie').getContext('2d');
  const userPieLabels = <?php echo json_encode(array_slice(array_keys($anomaly_by_user), 0, 5)); ?>;
  const userPieAdmin = <?php echo json_encode(array_map(function($u) use ($anomaly_admin_by_user){return $anomaly_admin_by_user[$u]??0;}, array_slice(array_keys($anomaly_by_user),0,5))); ?>;
  const userPieFeedback = <?php echo json_encode(array_map(function($u) use ($anomaly_feedback_by_user){return $anomaly_feedback_by_user[$u]??0;}, array_slice(array_keys($anomaly_by_user),0,5))); ?>;
  new Chart(userPieCtx, {
    type: 'doughnut',
    data: {
      labels: userPieLabels,
      datasets: [
        {
          label: 'Admin/Security',
          data: userPieAdmin,
          backgroundColor: 'rgba(220,53,69,0.8)',
          borderColor: 'rgba(220,53,69,1)',
          borderWidth: 1
        },
        {
          label: 'Feedback',
          data: userPieFeedback,
          backgroundColor: 'rgba(255,193,7,0.8)',
          borderColor: 'rgba(255,193,7,1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: true } },
      cutout: '60%'
    }
  });
  // IP anomaly type pie chart (top 5 IPs)
  const ipPieCtx = document.getElementById('ipAnomalyPie').getContext('2d');
  const ipPieLabels = <?php echo json_encode(array_slice(array_keys($anomaly_by_ip), 0, 5)); ?>;
  const ipPieAdmin = <?php echo json_encode(array_map(function($ip) use ($anomaly_admin_by_ip){return $anomaly_admin_by_ip[$ip]??0;}, array_slice(array_keys($anomaly_by_ip),0,5))); ?>;
  const ipPieFeedback = <?php echo json_encode(array_map(function($ip) use ($anomaly_feedback_by_ip){return $anomaly_feedback_by_ip[$ip]??0;}, array_slice(array_keys($anomaly_by_ip),0,5))); ?>;
  new Chart(ipPieCtx, {
    type: 'doughnut',
    data: {
      labels: ipPieLabels,
      datasets: [
        {
          label: 'Admin/Security',
          data: ipPieAdmin,
          backgroundColor: 'rgba(220,53,69,0.8)',
          borderColor: 'rgba(220,53,69,1)',
          borderWidth: 1
        },
        {
          label: 'Feedback',
          data: ipPieFeedback,
          backgroundColor: 'rgba(255,193,7,0.8)',
          borderColor: 'rgba(255,193,7,1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: true } },
      cutout: '60%'
    }
  });
  // Per-user anomaly trend chart (top 3 users, last 14 days)
  const userTrendCtx = document.getElementById('userAnomalyTrend').getContext('2d');
  const userTrendLabels = <?php echo json_encode($trend_days); ?>;
  const userTrendAdmin = <?php echo json_encode($user_trend_admin); ?>;
  const userTrendFeedback = <?php echo json_encode($user_trend_feedback); ?>;
  const userTrendDatasets = Object.keys(userTrendAdmin).flatMap(function(user,i){
    const colors = [
      ['rgba(13,110,253,1)','rgba(13,110,253,0.3)'],
      ['rgba(32,201,151,1)','rgba(32,201,151,0.3)'],
      ['rgba(255,193,7,1)','rgba(255,193,7,0.3)']
    ];
    return [
      {
        label: user + ' (Admin/Security)',
        data: Object.values(userTrendAdmin[user]),
        borderColor: colors[i%colors.length][0],
        backgroundColor: colors[i%colors.length][0],
        fill: false,
        tension: 0.2,
        borderDash: []
      },
      {
        label: user + ' (Feedback)',
        data: Object.values(userTrendFeedback[user]),
        borderColor: colors[i%colors.length][1],
        backgroundColor: colors[i%colors.length][1],
        fill: false,
        tension: 0.2,
        borderDash: [4,4]
      }
    ];
  });
  new Chart(userTrendCtx, {
    type: 'line',
    data: { labels: userTrendLabels, datasets: userTrendDatasets },
    options: {
      responsive: true,
      plugins: { legend: { display: true } },
      scales: { y: { beginAtZero: true } }
    }
  });
  // Per-IP anomaly trend chart (top 3 IPs, last 14 days)
  const ipTrendCtx = document.getElementById('ipAnomalyTrend').getContext('2d');
  const ipTrendLabels = <?php echo json_encode($trend_days); ?>;
  const ipTrendAdmin = <?php echo json_encode($ip_trend_admin); ?>;
  const ipTrendFeedback = <?php echo json_encode($ip_trend_feedback); ?>;
  const ipTrendDatasets = Object.keys(ipTrendAdmin).flatMap(function(ip,i){
    const colors = [
      ['rgba(220,53,69,1)','rgba(220,53,69,0.3)'],
      ['rgba(13,110,253,1)','rgba(13,110,253,0.3)'],
      ['rgba(255,193,7,1)','rgba(255,193,7,0.3)']
    ];
    return [
      {
        label: ip + ' (Admin/Security)',
        data: Object.values(ipTrendAdmin[ip]),
        borderColor: colors[i%colors.length][0],
        backgroundColor: colors[i%colors.length][0],
        fill: false,
        tension: 0.2,
        borderDash: []
      },
      {
        label: ip + ' (Feedback)',
        data: Object.values(ipTrendFeedback[ip]),
        borderColor: colors[i%colors.length][1],
        backgroundColor: colors[i%colors.length][1],
        fill: false,
        tension: 0.2,
        borderDash: [4,4]
      }
    ];
  });
  new Chart(ipTrendCtx, {
    type: 'line',
    data: { labels: ipTrendLabels, datasets: ipTrendDatasets },
    options: {
      responsive: true,
      plugins: { legend: { display: true } },
      scales: { y: { beginAtZero: true } }
    }
  });
  // Drilldown for user/IP anomaly trend
  function showAnomalyTrendDrilldown(type, key) {
    let trendData, trendDays, admin, feedback, html = '';
    if (type === 'user') {
      trendData = {
        admin: <?php echo json_encode($user_trend_admin); ?>,
        feedback: <?php echo json_encode($user_trend_feedback); ?>
      };
      trendDays = <?php echo json_encode($trend_days); ?>;
    } else {
      trendData = {
        admin: <?php echo json_encode($ip_trend_admin); ?>,
        feedback: <?php echo json_encode($ip_trend_feedback); ?>
      };
      trendDays = <?php echo json_encode($trend_days); ?>;
    }
    admin = trendData.admin[key] || {};
    feedback = trendData.feedback[key] || {};
    html += `<h6>${type === 'user' ? 'User' : 'IP'}: <span class='text-primary'>${key}</span></h6>`;
    html += `<table class='table table-sm table-bordered'><thead><tr><th>Date</th><th>Admin/Security</th><th>Feedback</th></tr></thead><tbody>`;
    trendDays.forEach(function(day) {
      html += `<tr><td>${day}</td><td>${admin[day]||0}</td><td>${feedback[day]||0}</td></tr>`;
    });
    html += '</tbody></table>';
    // Add export button
    let exportUrl = `?anomaly_export=1&type=${type === 'user' ? 'user_log' : 'ip_log'}&key=${encodeURIComponent(key)}`;
    html += `<a href='${exportUrl}' class='btn btn-outline-success btn-sm mt-2'><i class='fa fa-download me-1'></i>Export ${type === 'user' ? 'User' : 'IP'} Anomaly Log</a>`;
    html += `<form class='row g-2 align-items-end mt-2' method='get'>
      <input type='hidden' name='anomaly_export' value='1'>
      <input type='hidden' name='type' value='${type === 'user' ? 'user_log' : 'ip_log'}'>
      <input type='hidden' name='key' value='${encodeURIComponent(key)}'>
      <div class='col-auto'>
        <label class='form-label mb-0'>From</label>
        <input type='date' class='form-control form-control-sm' name='from'>
      </div>
      <div class="col-auto">
        <label class='form-label mb-0'>To</label>
        <input type='date' class='form-control form-control-sm' name='to'>
      </div>
      <div class='col-auto'>
        <button type='submit' class='btn btn-outline-success btn-sm'><i class='fa fa-download me-1'></i>Export ${type === 'user' ? 'User' : 'IP'} Anomaly Log</button>
      </div>
    </form>`;
    document.getElementById('anomalyTrendDrilldownBody').innerHTML = html;
    var modal = new bootstrap.Modal(document.getElementById('anomalyTrendDrilldownModal'));
    modal.show();
  }
  // Add click handlers to trend chart legends
  setTimeout(function(){
    const userLegend = document.querySelectorAll('#userAnomalyTrend + .chartjs-render-monitor ~ .chartjs-legend li');
    const ipLegend = document.querySelectorAll('#ipAnomalyTrend + .chartjs-render-monitor ~ .chartjs-legend li');
    userLegend.forEach((li, idx) => {
      li.style.cursor = 'pointer';
      li.onclick = function() {
        const user = li.textContent.replace(/ \(Admin\/Security\)| \(Feedback\)/g, '');
        showAnomalyTrendDrilldown('user', user);
      };
    });
    ipLegend.forEach((li, idx) => {
      li.style.cursor = 'pointer';
      li.onclick = function() {
        const ip = li.textContent.replace(/ \(Admin\/Security\)| \(Feedback\)/g, '');
        showAnomalyTrendDrilldown('ip', ip);
      };
    });
  }, 1000);
  </script>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
<?php
// --- Manual Trigger for Scheduled Feedback Export (admin button) ---
if (isset($_POST['trigger_feedback_export_now'])) {
  // Call the scheduled_report.php script directly (simulate scheduled run)
  $output = null;
  $result = null;
  $php_path = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
  $cmd = escapeshellcmd($php_path . ' "' . __DIR__ . '/scheduled_report.php"');
  exec($cmd, $output, $result);
  $manual_triggered = ($result === 0);
}
?>
<div class="card mb-4">
  <div class="card-header bg-warning text-dark"><i class="fa fa-bolt me-2"></i>Manual Feedback Export Trigger</div>
  <div class="card-body">
    <form method="post">
      <button type="submit" name="trigger_feedback_export_now" class="btn btn-danger btn-sm"><i class="fa fa-play me-1"></i>Run Feedback Export Now</button>
      <?php if (isset($manual_triggered)): ?>
        <span class="ms-3 <?php echo $manual_triggered ? 'text-success' : 'text-danger'; ?>">
          <?php echo $manual_triggered ? 'Export triggered successfully.' : 'Export failed to trigger.'; ?>
        </span>
      <?php endif; ?>
    </form>
    <div class="small text-muted mt-2">This will immediately run the scheduled feedback export with current settings and send the report to the configured recipient.</div>
  </div>
</div>
<?php
// --- Scheduled Feedback Export Settings UI and Save Logic ---
$settings_file = __DIR__ . '/../includes/feedback_export_config.php';
$saved = false;
if (isset($_POST['save_feedback_export_settings'])) {
  $cfg['feedback_export_enabled'] = isset($_POST['feedback_export_enabled']) ? 1 : 0;
  $cfg['feedback_export_from'] = $_POST['feedback_export_from'] ?? '';
  $cfg['feedback_export_to'] = $_POST['feedback_export_to'] ?? '';
  $cfg['feedback_export_action'] = $_POST['feedback_export_action'] ?? '';
  $cfg['feedback_export_user'] = $_POST['feedback_export_user'] ?? '';
  $cfg['feedback_export_search'] = $_POST['feedback_export_search'] ?? '';
  $cfg['feedback_export_recipient'] = $_POST['feedback_export_recipient'] ?? '';
  $cfg['feedback_export_frequency'] = $_POST['feedback_export_frequency'] ?? 'daily';
  file_put_contents($settings_file, '<?php return ' . var_export($cfg, true) . ';');
  $saved = true;
}
if (file_exists($settings_file)) {
  $cfg = include($settings_file);
}
?>
<div class="card mb-4">
  <div class="card-header bg-info text-white"><i class="fa fa-cog me-2"></i>Scheduled Feedback Export Settings</div>
  <div class="card-body">
    <?php if (!empty($saved)): ?><div class="alert alert-success">Settings saved.</div><?php endif; ?>
    <form method="post" class="row g-2 align-items-end">
      <div class="col-auto">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="feedback_export_enabled" id="feedback_export_enabled" value="1" <?php if (!empty($cfg['feedback_export_enabled'])) echo 'checked'; ?>>
          <label class="form-check-label" for="feedback_export_enabled">Enable Scheduled Export</label>
        </div>
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">From</label>
        <input type="date" class="form-control form-control-sm" name="feedback_export_from" value="<?php echo htmlspecialchars($cfg['feedback_export_from'] ?? ''); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">To</label>
        <input type="date" class="form-control form-control-sm" name="feedback_export_to" value="<?php echo htmlspecialchars($cfg['feedback_export_to'] ?? ''); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Type</label>
        <select class="form-select form-select-sm" name="feedback_export_action">
          <option value="" <?php if (empty($cfg['feedback_export_action'])) echo 'selected'; ?>>All</option>
          <option value="like" <?php if (($cfg['feedback_export_action'] ?? '')==='like') echo 'selected'; ?>>Like</option>
          <option value="dislike" <?php if (($cfg['feedback_export_action'] ?? '')==='dislike') echo 'selected'; ?>>Dislike</option>
          <option value="view" <?php if (($cfg['feedback_export_action'] ?? '')==='view') echo 'selected'; ?>>View</option>
        </select>
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">User</label>
        <input type="text" class="form-control form-control-sm" name="feedback_export_user" value="<?php echo htmlspecialchars($cfg['feedback_export_user'] ?? ''); ?>" placeholder="User email or ID">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Keyword</label>
        <input type="text" class="form-control form-control-sm" name="feedback_export_search" value="<?php echo htmlspecialchars($cfg['feedback_export_search'] ?? ''); ?>" placeholder="Search text...">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Recipient Email</label>
        <input type="email" class="form-control form-control-sm" name="feedback_export_recipient" value="<?php echo htmlspecialchars($cfg['feedback_export_recipient'] ?? ''); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Frequency</label>
        <select class="form-select form-select-sm" name="feedback_export_frequency">
          <option value="daily" <?php if (($cfg['feedback_export_frequency'] ?? '')==='daily') echo 'selected'; ?>>Daily</option>
          <option value="weekly" <?php if (($cfg['feedback_export_frequency'] ?? '')==='weekly') echo 'selected'; ?>>Weekly</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" name="save_feedback_export_settings" class="btn btn-primary btn-sm"><i class="fa fa-save me-1"></i>Save Settings</button>
      </div>
    </form>
  </div>
</div>

<?php
// --- Export/Download History with Search/Filter ---
$export_history_file = __DIR__ . '/../includes/export_history.log';
$history = [];
if (file_exists($export_history_file)) {
  $lines = file($export_history_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $parts = explode('|', $line);
    if (count($parts) === 5) {
      $history[] = [
        'type' => $parts[0],
        'time' => $parts[1],
        'user' => $parts[2],
        'recipient' => $parts[3],
        'desc' => $parts[4],
      ];
    }
  }
}
// Filter logic
$filter_type = $_GET['history_type'] ?? '';
$filter_user = $_GET['history_user'] ?? '';
$filter_recipient = $_GET['history_recipient'] ?? '';
$filter_desc = $_GET['history_desc'] ?? '';
$filter_time = $_GET['history_time'] ?? '';
$filtered_history = array_filter($history, function($h) use ($filter_type, $filter_user, $filter_recipient, $filter_desc, $filter_time) {
  if ($filter_type && stripos($h['type'], $filter_type) === false) return false;
  if ($filter_user && stripos($h['user'], $filter_user) === false) return false;
  if ($filter_recipient && stripos($h['recipient'], $filter_recipient) === false) return false;
  if ($filter_desc && stripos($h['desc'], $filter_desc) === false) return false;
  if ($filter_time && stripos($h['time'], $filter_time) === false) return false;
  return true;
});
?>
<div class="card mb-4">
  <div class="card-header bg-dark text-white"><i class="fa fa-history me-2"></i>Export/Download History</div>
  <div class="card-body p-0">
    <form method="get" class="p-2 bg-light border-bottom">
      <div class="row g-2 align-items-end">
        <div class="col-auto">
          <label class="form-label mb-0">Type</label>
          <select class="form-select form-select-sm" name="history_type">
            <option value="">All</option>
            <option value="feedback" <?php if ($filter_type==='feedback') echo 'selected'; ?>>Feedback</option>
            <option value="anomaly" <?php if ($filter_type==='anomaly') echo 'selected'; ?>>Anomaly</option>
          </select>
        </div>
        <div class="col-auto">
          <label class="form-label mb-0">Initiated By</label>
          <input type="text" class="form-control form-control-sm" name="history_user" value="<?php echo htmlspecialchars($filter_user); ?>" placeholder="User">
        </div>
        <div class="col-auto">
          <label class="form-label mb-0">Recipient</label>
          <input type="text" class="form-control form-control-sm" name="history_recipient" value="<?php echo htmlspecialchars($filter_recipient); ?>" placeholder="Recipient">
        </div>
        <div class="col-auto">
          <label class="form-label mb-0">Description</label>
          <input type="text" class="form-control form-control-sm" name="history_desc" value="<?php echo htmlspecialchars($filter_desc); ?>" placeholder="Description">
        </div>
        <div class="col-auto">
          <label class="form-label mb-0">Time</label>
          <input type="text" class="form-control form-control-sm" name="history_time" value="<?php echo htmlspecialchars($filter_time); ?>" placeholder="YYYY-MM-DD">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fa fa-search me-1"></i>Filter</button>
          <a href="?" class="btn btn-outline-secondary btn-sm ms-1"><i class="fa fa-times me-1"></i>Reset</a>
        </div>
        <div class="col-auto">
          <a href="?<?php echo http_build_query(array_merge($_GET, ['export_history_csv'=>1])); ?>" class="btn btn-success btn-sm"><i class="fa fa-download me-1"></i>Export CSV</a>
        </div>
      </div>
    </form>
    <table class="table table-sm table-striped mb-0">
      <thead class="table-light">
        <tr><th>Type</th><th>Time</th><th>Initiated By</th><th>Recipient</th><th>Description</th></tr>
      </thead>
      <tbody>
        <?php foreach (array_reverse($filtered_history) as $h): ?>
        <tr>
          <td><?php echo htmlspecialchars($h['type']); ?></td>
          <td><?php echo htmlspecialchars($h['time']); ?></td>
          <td><?php echo htmlspecialchars($h['user']); ?></td>
          <td><?php echo htmlspecialchars($h['recipient']); ?></td>
          <td><?php echo htmlspecialchars($h['desc']); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($filtered_history)): ?><tr><td colspan="5" class="text-muted">No export/download history found for filter.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
// --- Scheduled Feedback Export Status/Preview ---
$last_run_file = __DIR__ . '/../includes/feedback_export_last_run.txt';
$last_run = file_exists($last_run_file) ? file_get_contents($last_run_file) : null;
$next_run = '';
if (!empty($cfg['feedback_export_frequency'])) {
  $freq = $cfg['feedback_export_frequency'];
  $now = time();
  if ($last_run) {
    $last = strtotime($last_run);
    $next = ($freq === 'weekly') ? strtotime('+1 week', $last) : strtotime('+1 day', $last);
    $next_run = date('Y-m-d H:i:s', $next);
  } else {
    $next_run = 'Not yet run';
  }
}
?>
<div class="card mb-4">
  <div class="card-header bg-secondary text-white"><i class="fa fa-clock me-2"></i>Scheduled Feedback Export Status</div>
  <div class="card-body">
    <ul class="mb-0">
      <li><strong>Status:</strong> <?php echo !empty($cfg['feedback_export_enabled']) ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></li>
      <li><strong>Last Run:</strong> <?php echo $last_run ? htmlspecialchars($last_run) : 'Never'; ?></li>
      <li><strong>Next Scheduled Run:</strong> <?php echo htmlspecialchars($next_run); ?></li>
      <li><strong>Recipient:</strong> <?php echo htmlspecialchars($cfg['feedback_export_recipient'] ?? $admin_email); ?></li>
      <li><strong>Frequency:</strong> <?php echo htmlspecialchars($cfg['feedback_export_frequency'] ?? 'daily'); ?></li>
    </ul>
  </div>
</div>

<?php
// --- Manual and Scheduled Anomaly Log Export Controls ---
$anomaly_export_settings_file = __DIR__ . '/../includes/anomaly_export_config.php';
$anomaly_saved = false;
if (isset($_POST['save_anomaly_export_settings'])) {
  $anomaly_cfg['anomaly_export_enabled'] = isset($_POST['anomaly_export_enabled']) ? 1 : 0;
  $anomaly_cfg['anomaly_export_from'] = $_POST['anomaly_export_from'] ?? '';
  $anomaly_cfg['anomaly_export_to'] = $_POST['anomaly_export_to'] ?? '';
  $anomaly_cfg['anomaly_export_type'] = $_POST['anomaly_export_type'] ?? '';
  $anomaly_cfg['anomaly_export_user'] = $_POST['anomaly_export_user'] ?? '';
  $anomaly_cfg['anomaly_export_ip'] = $_POST['anomaly_export_ip'] ?? '';
  $anomaly_cfg['anomaly_export_search'] = $_POST['anomaly_export_search'] ?? '';
  $anomaly_cfg['anomaly_export_recipient'] = $_POST['anomaly_export_recipient'] ?? '';
  $anomaly_cfg['anomaly_export_frequency'] = $_POST['anomaly_export_frequency'] ?? 'daily';
  file_put_contents($anomaly_export_settings_file, '<?php return ' . var_export($anomaly_cfg, true) . ';');
  $anomaly_saved = true;
}
if (file_exists($anomaly_export_settings_file)) {
  $anomaly_cfg = include($anomaly_export_settings_file);
}
$anomaly_last_run_file = __DIR__ . '/../includes/anomaly_export_last_run.txt';
$anomaly_last_run = file_exists($anomaly_last_run_file) ? file_get_contents($anomaly_last_run_file) : null;
$anomaly_next_run = '';
if (!empty($anomaly_cfg['anomaly_export_frequency'])) {
  $freq = $anomaly_cfg['anomaly_export_frequency'];
  if ($anomaly_last_run) {
    $last = strtotime($anomaly_last_run);
    $next = ($freq === 'weekly') ? strtotime('+1 week', $last) : strtotime('+1 day', $last);
    $anomaly_next_run = date('Y-m-d H:i:s', $next);
  } else {
    $anomaly_next_run = 'Not yet run';
  }
}
if (isset($_POST['trigger_anomaly_export_now'])) {
  $output = null;
  $result = null;
  $php_path = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
  $cmd = escapeshellcmd($php_path . ' "' . __DIR__ . '/scheduled_report.php anomaly"');
  exec($cmd, $output, $result);
  $anomaly_manual_triggered = ($result === 0);
}
?>
<div class="card mb-4">
  <div class="card-header bg-danger text-white"><i class="fa fa-exclamation-triangle me-2"></i>Scheduled Anomaly Log Export Controls</div>
  <div class="card-body">
    <?php if (!empty($anomaly_saved)): ?><div class="alert alert-success">Anomaly export settings saved.</div><?php endif; ?>
    <form method="post" class="row g-2 align-items-end mb-2">
      <div class="col-auto">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="anomaly_export_enabled" id="anomaly_export_enabled" value="1" <?php if (!empty($anomaly_cfg['anomaly_export_enabled'])) echo 'checked'; ?>>
          <label class="form-check-label" for="anomaly_export_enabled">Enable Scheduled Export</label>
        </div>
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">From</label>
        <input type="date" class="form-control form-control-sm" name="anomaly_export_from" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_from'] ?? ''); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">To</label>
        <input type="date" class="form-control form-control-sm" name="anomaly_export_to" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_to'] ?? ''); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Type</label>
        <select class="form-select form-select-sm" name="anomaly_export_type">
          <option value="" <?php if (empty($anomaly_cfg['anomaly_export_type'])) echo 'selected'; ?>>All</option>
          <option value="admin" <?php if (($anomaly_cfg['anomaly_export_type'] ?? '')==='admin') echo 'selected'; ?>>Admin/Security</option>
          <option value="feedback" <?php if (($anomaly_cfg['anomaly_export_type'] ?? '')==='feedback') echo 'selected'; ?>>Feedback</option>
        </select>
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">User</label>
        <input type="text" class="form-control form-control-sm" name="anomaly_export_user" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_user'] ?? ''); ?>" placeholder="User email or ID">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">IP</label>
        <input type="text" class="form-control form-control-sm" name="anomaly_export_ip" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_ip'] ?? ''); ?>" placeholder="IP address">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Keyword</label>
        <input type="text" class="form-control form-control-sm" name="anomaly_export_search" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_search'] ?? ''); ?>" placeholder="Search text...">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Recipient Email</label>
        <input type="email" class="form-control form-control-sm" name="anomaly_export_recipient" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_recipient'] ?? ''); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Frequency</label>
        <select class="form-select form-select-sm" name="anomaly_export_frequency">
          <option value="daily" <?php if (($anomaly_cfg['anomaly_export_frequency'] ?? '')==='daily') echo 'selected'; ?>>Daily</option>
          <option value="weekly" <?php if (($anomaly_cfg['anomaly_export_frequency'] ?? '')==='weekly') echo 'selected'; ?>>Weekly</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" name="save_anomaly_export_settings" class="btn btn-primary btn-sm"><i class="fa fa-save me-1"></i>Save Settings</button>
      </div>
    </form>
    <form method="post" class="d-inline">
      <button type="submit" name="trigger_anomaly_export_now" class="btn btn-danger btn-sm"><i class="fa fa-play me-1"></i>Run Anomaly Export Now</button>
      <?php if (isset($anomaly_manual_triggered)): ?>
        <span class="ms-3 <?php echo $anomaly_manual_triggered ? 'text-success' : 'text-danger'; ?>">
          <?php echo $anomaly_manual_triggered ? 'Export triggered successfully.' : 'Export failed to trigger.'; ?>
        </span>
      <?php endif; ?>
    </form>
    <div class="small text-muted mt-2">Configure scheduled anomaly log export, or run it immediately using the button above.</div>
    <ul class="mt-3 mb-0">
      <li><strong>Status:</strong> <?php echo !empty($anomaly_cfg['anomaly_export_enabled']) ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></li>
      <li><strong>Last Run:</strong> <?php echo $anomaly_last_run ? htmlspecialchars($anomaly_last_run) : 'Never'; ?></li>
      <li><strong>Next Scheduled Run:</strong> <?php echo htmlspecialchars($anomaly_next_run); ?></li>
      <li><strong>Recipient:</strong> <?php echo htmlspecialchars($anomaly_cfg['anomaly_export_recipient'] ?? $admin_email); ?></li>
      <li><strong>Frequency:</strong> <?php echo htmlspecialchars($anomaly_cfg['anomaly_export_frequency'] ?? 'daily'); ?></li>
    </ul>
  </div>
</div>

<?php
// --- Per-user/IP anomaly trend data for last 14 days (top 3), with type breakdown ---
$user_trend_admin = $user_trend_feedback = $ip_trend_admin = $ip_trend_feedback = [];
foreach ($top_users as $u) {
  foreach ($trend_days as $d) {
    $user_trend_admin[$u][$d] = 0;
    $user_trend_feedback[$u][$d] = 0;
  }
}
foreach ($top_ips as $ip) {
  foreach ($trend_days as $d) {
    $ip_trend_admin[$ip][$d] = 0;
    $ip_trend_feedback[$ip][$d] = 0;
  }
}
if (file_exists($anomaly_log_file)) {
  foreach ($lines as $line) {
    if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $m)) {
      $log_date = $m[1];
      if (in_array($log_date, $trend_days)) {
        $is_admin = (stripos($line, 'ANOMALY DETECTED') !== false);
        $is_feedback = (stripos($line, 'FEEDBACK ANOMALY') !== false);
        if (preg_match('/User: ([\w@.\-]+)/i', $line, $um)) {
          $user = $um[1];
          if (isset($user_trend_admin[$user])) {
            if ($is_admin) $user_trend_admin[$user][$log_date]++;
            if ($is_feedback) $user_trend_feedback[$user][$log_date]++;
          }
        }
        if (preg_match('/IP: ([\d.]+)/', $line, $ipm)) {
          $ip = $ipm[1];
          if (isset($ip_trend_admin[$ip])) {
            if ($is_admin) $ip_trend_admin[$ip][$log_date]++;
            if ($is_feedback) $ip_trend_feedback[$ip][$log_date]++;
          }
        }
      }
    }
  }
}
?>

<?php
// --- Scheduled Feedback Export Status/Preview ---
$last_run_file = __DIR__ . '/../includes/feedback_export_last_run.txt';
$last_run = file_exists($last_run_file) ? file_get_contents($last_run_file) : null;
$next_run = '';
if (!empty($cfg['feedback_export_frequency'])) {
  $freq = $cfg['feedback_export_frequency'];
  $now = time();
  if ($last_run) {
    $last = strtotime($last_run);
    $next = ($freq === 'weekly') ? strtotime('+1 week', $last) : strtotime('+1 day', $last);
    $next_run = date('Y-m-d H:i:s', $next);
  } else {
    $next_run = 'Not yet run';
  }
}
?>
<div class="card mb-4">
  <div class="card-header bg-secondary text-white"><i class="fa fa-clock me-2"></i>Scheduled Feedback Export Status</div>
  <div class="card-body">
    <ul class="mb-0">
      <li><strong>Status:</strong> <?php echo !empty($cfg['feedback_export_enabled']) ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></li>
      <li><strong>Last Run:</strong> <?php echo $last_run ? htmlspecialchars($last_run) : 'Never'; ?></li>
      <li><strong>Next Scheduled Run:</strong> <?php echo htmlspecialchars($next_run); ?></li>
      <li><strong>Recipient:</strong> <?php echo htmlspecialchars($cfg['feedback_export_recipient'] ?? $admin_email); ?></li>
      <li><strong>Frequency:</strong> <?php echo htmlspecialchars($cfg['feedback_export_frequency'] ?? 'daily'); ?></li>
    </ul>
  </div>
</div>

<?php
// --- Manual and Scheduled Anomaly Log Export Controls ---
$anomaly_export_settings_file = __DIR__ . '/../includes/anomaly_export_config.php';
$anomaly_saved = false;
if (isset($_POST['save_anomaly_export_settings'])) {
  $anomaly_cfg['anomaly_export_enabled'] = isset($_POST['anomaly_export_enabled']) ? 1 : 0;
  $anomaly_cfg['anomaly_export_from'] = $_POST['anomaly_export_from'] ?? '';
  $anomaly_cfg['anomaly_export_to'] = $_POST['anomaly_export_to'] ?? '';
  $anomaly_cfg['anomaly_export_type'] = $_POST['anomaly_export_type'] ?? '';
  $anomaly_cfg['anomaly_export_user'] = $_POST['anomaly_export_user'] ?? '';
  $anomaly_cfg['anomaly_export_ip'] = $_POST['anomaly_export_ip'] ?? '';
  $anomaly_cfg['anomaly_export_search'] = $_POST['anomaly_export_search'] ?? '';
  $anomaly_cfg['anomaly_export_recipient'] = $_POST['anomaly_export_recipient'] ?? '';
  $anomaly_cfg['anomaly_export_frequency'] = $_POST['anomaly_export_frequency'] ?? 'daily';
  file_put_contents($anomaly_export_settings_file, '<?php return ' . var_export($anomaly_cfg, true) . ';');
  $anomaly_saved = true;
}
if (file_exists($anomaly_export_settings_file)) {
  $anomaly_cfg = include($anomaly_export_settings_file);
}
$anomaly_last_run_file = __DIR__ . '/../includes/anomaly_export_last_run.txt';
$anomaly_last_run = file_exists($anomaly_last_run_file) ? file_get_contents($anomaly_last_run_file) : null;
$anomaly_next_run = '';
if (!empty($anomaly_cfg['anomaly_export_frequency'])) {
  $freq = $anomaly_cfg['anomaly_export_frequency'];
  if ($anomaly_last_run) {
    $last = strtotime($anomaly_last_run);
    $next = ($freq === 'weekly') ? strtotime('+1 week', $last) : strtotime('+1 day', $last);
    $anomaly_next_run = date('Y-m-d H:i:s', $next);
  } else {
    $anomaly_next_run = 'Not yet run';
  }
}
if (isset($_POST['trigger_anomaly_export_now'])) {
  $output = null;
  $result = null;
  $php_path = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
  $cmd = escapeshellcmd($php_path . ' "' . __DIR__ . '/scheduled_report.php anomaly"');
  exec($cmd, $output, $result);
  $anomaly_manual_triggered = ($result === 0);
}
?>
<div class="card mb-4">
  <div class="card-header bg-danger text-white"><i class="fa fa-exclamation-triangle me-2"></i>Scheduled Anomaly Log Export Controls</div>
  <div class="card-body">
    <?php if (!empty($anomaly_saved)): ?><div class="alert alert-success">Anomaly export settings saved.</div><?php endif; ?>
    <form method="post" class="row g-2 align-items-end mb-2">
      <div class="col-auto">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="anomaly_export_enabled" id="anomaly_export_enabled" value="1" <?php if (!empty($anomaly_cfg['anomaly_export_enabled'])) echo 'checked'; ?>>
          <label class="form-check-label" for="anomaly_export_enabled">Enable Scheduled Export</label>
        </div>
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">From</label>
        <input type="date" class="form-control form-control-sm" name="anomaly_export_from" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_from'] ?? ''); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">To</label>
        <input type="date" class="form-control form-control-sm" name="anomaly_export_to" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_to'] ?? ''); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Type</label>
        <select class="form-select form-select-sm" name="anomaly_export_type">
          <option value="" <?php if (empty($anomaly_cfg['anomaly_export_type'])) echo 'selected'; ?>>All</option>
          <option value="admin" <?php if (($anomaly_cfg['anomaly_export_type'] ?? '')==='admin') echo 'selected'; ?>>Admin/Security</option>
          <option value="feedback" <?php if (($anomaly_cfg['anomaly_export_type'] ?? '')==='feedback') echo 'selected'; ?>>Feedback</option>
        </select>
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">User</label>
        <input type="text" class="form-control form-control-sm" name="anomaly_export_user" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_user'] ?? ''); ?>" placeholder="User email or ID">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">IP</label>
        <input type="text" class="form-control form-control-sm" name="anomaly_export_ip" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_ip'] ?? ''); ?>" placeholder="IP address">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Keyword</label>
        <input type="text" class="form-control form-control-sm" name="anomaly_export_search" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_search'] ?? ''); ?>" placeholder="Search text...">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Recipient Email</label>
        <input type="email" class="form-control form-control-sm" name="anomaly_export_recipient" value="<?php echo htmlspecialchars($anomaly_cfg['anomaly_export_recipient'] ?? ''); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-0">Frequency</label>
        <select class="form-select form-select-sm" name="anomaly_export_frequency">
          <option value="daily" <?php if (($anomaly_cfg['anomaly_export_frequency'] ?? '')==='daily') echo 'selected'; ?>>Daily</option>
          <option value="weekly" <?php if (($anomaly_cfg['anomaly_export_frequency'] ?? '')==='weekly') echo 'selected'; ?>>Weekly</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" name="save_anomaly_export_settings" class="btn btn-primary btn-sm"><i class="fa fa-save me-1"></i>Save Settings</button>
      </div>
    </form>
    <form method="post" class="d-inline">
      <button type="submit" name="trigger_anomaly_export_now" class="btn btn-danger btn-sm"><i class="fa fa-play me-1"></i>Run Anomaly Export Now</button>
      <?php if (isset($anomaly_manual_triggered)): ?>
        <span class="ms-3 <?php echo $anomaly_manual_triggered ? 'text-success' : 'text-danger'; ?>">
          <?php echo $anomaly_manual_triggered ? 'Export triggered successfully.' : 'Export failed to trigger.'; ?>
        </span>
      <?php endif; ?>
    </form>
    <div class="small text-muted mt-2">Configure scheduled anomaly log export, or run it immediately using the button above.</div>
    <ul class="mt-3 mb-0">
      <li><strong>Status:</strong> <?php echo !empty($anomaly_cfg['anomaly_export_enabled']) ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></li>
      <li><strong>Last Run:</strong> <?php echo $anomaly_last_run ? htmlspecialchars($anomaly_last_run) : 'Never'; ?></li>
      <li><strong>Next Scheduled Run:</strong> <?php echo htmlspecialchars($anomaly_next_run); ?></li>
      <li><strong>Recipient:</strong> <?php echo htmlspecialchars($anomaly_cfg['anomaly_export_recipient'] ?? $admin_email); ?></li>
      <li><strong>Frequency:</strong> <?php echo htmlspecialchars($anomaly_cfg['anomaly_export_frequency'] ?? 'daily'); ?></li>
    </ul>
  </div>
</div>

```

