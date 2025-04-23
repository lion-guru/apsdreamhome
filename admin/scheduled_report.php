<?php
// Scheduled report: email analytics summary and audit log to admin
// To schedule: run via Windows Task Scheduler (php scheduled_report.php)

require_once(__DIR__ . '/send_sms_twilio.php');

$admin_email = 'admin@apsdreamhomes.com'; // Updated to real admin email
$send_summary = true;
$send_audit = true;

// Load config if present
$config_file = __DIR__ . '/scheduled_report_config.json';
if (file_exists($config_file)) {
    $cfg = json_decode(file_get_contents($config_file), true);
    $admin_email = $cfg['admin_email'] ?? $admin_email;
    $send_summary = isset($cfg['send_summary']) ? $cfg['send_summary'] : $send_summary;
    $send_audit = isset($cfg['send_audit']) ? $cfg['send_audit'] : $send_audit;
    $frequency = $cfg['frequency'] ?? 'daily';
} else {
    $frequency = 'daily';
}

// --- Load scheduled feedback export settings from config file if it exists ---
$settings_file = __DIR__ . '/../includes/feedback_export_config.php';
if (file_exists($settings_file)) {
  $cfg = include($settings_file);
}
$feedback_export_enabled = $cfg['feedback_export_enabled'] ?? false;
$feedback_export_from = $cfg['feedback_export_from'] ?? null;
$feedback_export_to = $cfg['feedback_export_to'] ?? null;
$feedback_export_action = $cfg['feedback_export_action'] ?? '';
$feedback_export_user = $cfg['feedback_export_user'] ?? '';
$feedback_export_search = $cfg['feedback_export_search'] ?? '';
$feedback_export_recipient = $cfg['feedback_export_recipient'] ?? $admin_email;
$feedback_export_frequency = $cfg['feedback_export_frequency'] ?? 'daily';

// --- Load scheduled anomaly export settings from config file if it exists ---
$anomaly_export_settings_file = __DIR__ . '/../includes/anomaly_export_config.php';
if (file_exists($anomaly_export_settings_file)) {
  $anomaly_cfg = include($anomaly_export_settings_file);
}
$anomaly_export_enabled = $anomaly_cfg['anomaly_export_enabled'] ?? false;
$anomaly_export_from = $anomaly_cfg['anomaly_export_from'] ?? '';
$anomaly_export_to = $anomaly_cfg['anomaly_export_to'] ?? '';
$anomaly_export_type = $anomaly_cfg['anomaly_export_type'] ?? '';
$anomaly_export_user = $anomaly_cfg['anomaly_export_user'] ?? '';
$anomaly_export_ip = $anomaly_cfg['anomaly_export_ip'] ?? '';
$anomaly_export_search = $anomaly_cfg['anomaly_export_search'] ?? '';
$anomaly_export_recipient = $anomaly_cfg['anomaly_export_recipient'] ?? $admin_email;
$anomaly_export_frequency = $anomaly_cfg['anomaly_export_frequency'] ?? 'daily';

// --- Analytics summary (feedback counts by type, top offenders) ---
require_once(__DIR__ . '/../includes/classes/Database.php');
$db = new Database();
$con = $db->getConnection();

// Adjust date range if weekly
if ($frequency === 'weekly') {
    $from = date('Y-m-d', strtotime('-7 days'));
} else {
    $from = date('Y-m-d', strtotime('-1 day'));
}
$to = date('Y-m-d');
$date_sql = "created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59'";

$summary = [ 'total' => 0, 'likes' => 0, 'dislikes' => 0, 'views' => 0 ];
$res = mysqli_query($con, "SELECT action, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql GROUP BY action");
while($row = mysqli_fetch_assoc($res)) {
    if ($row['action']==='like') $summary['likes'] += $row['cnt'];
    elseif ($row['action']==='dislike') $summary['dislikes'] += $row['cnt'];
    elseif ($row['action']==='view') $summary['views'] += $row['cnt'];
    $summary['total'] += $row['cnt'];
}

$top_offenders = [];
$res = mysqli_query($con, "SELECT ip_address, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql GROUP BY ip_address HAVING cnt > 10 ORDER BY cnt DESC LIMIT 10");
while($row = mysqli_fetch_assoc($res)) $top_offenders[] = $row;

// --- Create summary CSV ---
$summary_csv = "Type,Count\n";
foreach ($summary as $k=>$v) $summary_csv .= ucfirst($k).",$v\n";
$summary_csv .= "\nTop Offenders (IP,Count)\n";
foreach ($top_offenders as $o) $summary_csv .= $o['ip_address'].",{$o['cnt']}\n";

// --- Audit log CSV (last 24h) ---
$audit_csv = "Timestamp,Action,Type,Value,Admin\n";
$audit_file = __DIR__ . '/blocklist_audit.log';
if (file_exists($audit_file)) {
    $lines = file($audit_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $cols = explode("\t", $line);
        if (count($cols)>=5 && $cols[0]>=($from.' 00:00:00') && $cols[0]<=($to.' 23:59:59')) {
            $audit_csv .= '"'.implode('","', array_map('addslashes',$cols))."\n";
        }
    }
}

// --- Include anomaly log summary in scheduled report ---
$anomaly_log_file = __DIR__ . '/anomaly_log.txt';
$anomaly_summary = '';
if (file_exists($anomaly_log_file)) {
    $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $since = ($frequency === 'weekly') ? strtotime('-7 days') : strtotime('-1 day');
    $recent = array_filter($lines, function($line) use ($since) {
        if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $m)) {
            return strtotime($m[1]) >= $since;
        }
        return false;
    });
    if ($recent) {
        $anomaly_summary = "\n\n=== Anomaly Summary ===\n" . implode("\n", $recent) . "\n";
    }
}

// --- User/IP anomaly breakdown for scheduled report ---
$user_breakdown = $ip_breakdown = '';
$anomaly_log_file = __DIR__ . '/anomaly_log.txt';
if (file_exists($anomaly_log_file)) {
    $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $since = ($frequency === 'weekly') ? strtotime('-7 days') : strtotime('-1 day');
    $users = $ips = [];
    foreach ($lines as $line) {
        if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $m)) {
            if (strtotime($m[1]) >= $since) {
                if (preg_match('/User: ([\w@.\-]+)/i', $line, $um)) {
                    $user = $um[1];
                    $users[$user] = ($users[$user] ?? 0) + 1;
                }
                if (preg_match('/IP: ([\d.]+)/', $line, $ipm)) {
                    $ip = $ipm[1];
                    $ips[$ip] = ($ips[$ip] ?? 0) + 1;
                }
            }
        }
    }
    if ($users) {
        arsort($users);
        $user_breakdown = "\nTop Users (anomalies):\n";
        $i = 0;
        foreach ($users as $user => $cnt) {
            if (++$i > 5) break;
            $user_breakdown .= "$user: $cnt\n";
        }
    }
    if ($ips) {
        arsort($ips);
        $ip_breakdown = "\nTop IPs (anomalies):\n";
        $i = 0;
        foreach ($ips as $ip => $cnt) {
            if (++$i > 5) break;
            $ip_breakdown .= "$ip: $cnt\n";
        }
    }
}

// --- Anomaly type breakdown for scheduled report (last 30 days, top 5 users/IPs) ---
$anomaly_type_user = $anomaly_type_ip = [];
if (file_exists($anomaly_log_file)) {
    $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $since = ($frequency === 'weekly') ? strtotime('-7 days') : strtotime('-1 day');
    foreach ($lines as $line) {
        if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $m)) {
            if (strtotime($m[1]) >= $since) {
                $is_admin = (stripos($line, 'ANOMALY DETECTED') !== false);
                $is_feedback = (stripos($line, 'FEEDBACK ANOMALY') !== false);
                if (preg_match('/User: ([\w@.\-]+)/i', $line, $um)) {
                    $user = $um[1];
                    if (!isset($anomaly_type_user[$user])) $anomaly_type_user[$user] = ['admin'=>0,'feedback'=>0];
                    if ($is_admin) $anomaly_type_user[$user]['admin']++;
                    if ($is_feedback) $anomaly_type_user[$user]['feedback']++;
                }
                if (preg_match('/IP: ([\d.]+)/', $line, $ipm)) {
                    $ip = $ipm[1];
                    if (!isset($anomaly_type_ip[$ip])) $anomaly_type_ip[$ip] = ['admin'=>0,'feedback'=>0];
                    if ($is_admin) $anomaly_type_ip[$ip]['admin']++;
                    if ($is_feedback) $anomaly_type_ip[$ip]['feedback']++;
                }
            }
        }
    }
}
$anomaly_type_user_str = $anomaly_type_ip_str = '';
if ($anomaly_type_user) {
    arsort($users);
    $anomaly_type_user_str = "\nTop Users Anomaly Types (Admin/Security | Feedback):\n";
    $i = 0;
    foreach (array_keys($users) as $user) {
        if (++$i > 5) break;
        $a = $anomaly_type_user[$user]['admin'] ?? 0;
        $f = $anomaly_type_user[$user]['feedback'] ?? 0;
        $anomaly_type_user_str .= "$user: $a | $f\n";
    }
}
if ($anomaly_type_ip) {
    arsort($ips);
    $anomaly_type_ip_str = "\nTop IPs Anomaly Types (Admin/Security | Feedback):\n";
    $i = 0;
    foreach (array_keys($ips) as $ip) {
        if (++$i > 5) break;
        $a = $anomaly_type_ip[$ip]['admin'] ?? 0;
        $f = $anomaly_type_ip[$ip]['feedback'] ?? 0;
        $anomaly_type_ip_str .= "$ip: $a | $f\n";
    }
}

// --- Per-user/IP anomaly trend (last 14 days, top 3, text representation) ---
$user_trend = $ip_trend = [];
$trend_days = [];
for ($i = 13; $i >= 0; $i--) $trend_days[] = date('Y-m-d', strtotime("-$i days"));
$top_users = array_slice(array_keys($users), 0, 3);
$top_ips = array_slice(array_keys($ips), 0, 3);
foreach ($top_users as $u) {
  foreach ($trend_days as $d) $user_trend[$u][$d] = 0;
}
foreach ($top_ips as $ip) {
  foreach ($trend_days as $d) $ip_trend[$ip][$d] = 0;
}
if (file_exists($anomaly_log_file)) {
  $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (preg_match('/(\d{4}-\d{2}-\d{2})/', $line, $m)) {
      $log_date = $m[1];
      if (in_array($log_date, $trend_days)) {
        if (preg_match('/User: ([\w@.\-]+)/i', $line, $um)) {
          $user = $um[1];
          if (isset($user_trend[$user])) $user_trend[$user][$log_date]++;
        }
        if (preg_match('/IP: ([\d.]+)/', $line, $ipm)) {
          $ip = $ipm[1];
          if (isset($ip_trend[$ip])) $ip_trend[$ip][$log_date]++;
        }
      }
    }
  }
}
$user_trend_str = $ip_trend_str = '';
if ($user_trend) {
  $user_trend_str = "\nUser Anomaly Trends (last 14 days):\n";
  foreach ($user_trend as $user => $trend) {
    $user_trend_str .= $user . ': ';
    foreach ($trend_days as $d) $user_trend_str .= $trend[$d] . ' ';
    $user_trend_str .= "\n";
  }
}
if ($ip_trend) {
  $ip_trend_str = "\nIP Anomaly Trends (last 14 days):\n";
  foreach ($ip_trend as $ip => $trend) {
    $ip_trend_str .= $ip . ': ';
    foreach ($trend_days as $d) $ip_trend_str .= $trend[$d] . ' ';
    $ip_trend_str .= "\n";
  }
}

// --- Per-user/IP anomaly trend with type breakdown (last 14 days, top 3, text representation) ---
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
  $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
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
$user_trend_type_str = $ip_trend_type_str = '';
if ($user_trend_admin) {
  $user_trend_type_str = "\nUser Anomaly Trends by Type (last 14 days):\n";
  foreach ($user_trend_admin as $user => $trend) {
    $user_trend_type_str .= $user . ' (Admin/Security): ';
    foreach ($trend_days as $d) $user_trend_type_str .= $trend[$d] . ' ';
    $user_trend_type_str .= "\n";
    $user_trend_type_str .= $user . ' (Feedback): ';
    foreach ($trend_days as $d) $user_trend_type_str .= $user_trend_feedback[$user][$d] . ' ';
    $user_trend_type_str .= "\n";
  }
}
if ($ip_trend_admin) {
  $ip_trend_type_str = "\nIP Anomaly Trends by Type (last 14 days):\n";
  foreach ($ip_trend_admin as $ip => $trend) {
    $ip_trend_type_str .= $ip . ' (Admin/Security): ';
    foreach ($trend_days as $d) $ip_trend_type_str .= $trend[$d] . ' ';
    $ip_trend_type_str .= "\n";
    $ip_trend_type_str .= $ip . ' (Feedback): ';
    foreach ($trend_days as $d) $ip_trend_type_str .= $ip_trend_feedback[$ip][$d] . ' ';
    $ip_trend_type_str .= "\n";
  }
}

// --- Scheduled filtered feedback analytics export (CSV attachment) ---
$feedback_csv = '';
if ($feedback_export_enabled) {
  $sql = "SELECT * FROM ai_interactions WHERE 1=1";
  if ($feedback_export_from) $sql .= " AND DATE(ts) >= '" . mysqli_real_escape_string($con, $feedback_export_from) . "'";
  if ($feedback_export_to) $sql .= " AND DATE(ts) <= '" . mysqli_real_escape_string($con, $feedback_export_to) . "'";
  if ($feedback_export_action) $sql .= " AND action = '" . mysqli_real_escape_string($con, $feedback_export_action) . "'";
  if ($feedback_export_user) $sql .= " AND user = '" . mysqli_real_escape_string($con, $feedback_export_user) . "'";
  if ($feedback_export_search) $sql .= " AND (suggestion LIKE '%" . mysqli_real_escape_string($con, $feedback_export_search) . "%' OR feedback LIKE '%" . mysqli_real_escape_string($con, $feedback_export_search) . "%')";
  $sql .= " ORDER BY ts DESC";
  $res = mysqli_query($con, $sql);
  if ($res) {
    $header = false;
    while ($row = mysqli_fetch_assoc($res)) {
      if (!$header) { $feedback_csv .= implode(',', array_keys($row)) . "\n"; $header = true; }
      $vals = array_map(function($v) { return '"'.str_replace('"','""',$v).'"'; }, array_values($row));
      $feedback_csv .= implode(',', $vals) . "\n";
    }
  }
}

// --- Update last run time for scheduled feedback export ---
$last_run_file = __DIR__ . '/../includes/feedback_export_last_run.txt';
file_put_contents($last_run_file, date('Y-m-d H:i:s'));

// --- Anomaly log CSV export ---
$anomaly_csv = '';
if ($anomaly_export_enabled || (isset($argv[1]) && $argv[1] === 'anomaly')) {
  $anomaly_log_file = __DIR__ . '/anomaly_log.txt';
  if (file_exists($anomaly_log_file)) {
    $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $filtered = [];
    foreach ($lines as $line) {
      $include = true;
      if ($anomaly_export_from && strpos($line, $anomaly_export_from) === false) $include = false;
      if ($anomaly_export_to && strpos($line, $anomaly_export_to) === false) $include = false;
      if ($anomaly_export_type === 'admin' && stripos($line, 'ANOMALY DETECTED') === false) $include = false;
      if ($anomaly_export_type === 'feedback' && stripos($line, 'FEEDBACK ANOMALY') === false) $include = false;
      if ($anomaly_export_user && stripos($line, $anomaly_export_user) === false) $include = false;
      if ($anomaly_export_ip && stripos($line, $anomaly_export_ip) === false) $include = false;
      if ($anomaly_export_search && stripos($line, $anomaly_export_search) === false) $include = false;
      if ($include) $filtered[] = $line;
    }
    $anomaly_csv = "Date,Type,User,IP,Details\n";
    foreach ($filtered as $log) {
      // Try to parse log line into columns
      preg_match('/^(\d{4}-\d{2}-\d{2} [^ ]+) - (ANOMALY DETECTED|FEEDBACK ANOMALY) - User: ([^ ]+) - IP: ([^ ]+) - (.*)$/i', $log, $m);
      if ($m) {
        $anomaly_csv .= '"'.implode('","', array_map('addslashes', array_slice($m,1)))."\n";
      } else {
        $anomaly_csv .= '"'.addslashes($log)."\n";
      }
    }
  }
}

// --- Export/Download History Logging ---
function log_export_event($type, $user, $recipient, $desc) {
  $export_history_file = __DIR__ . '/../includes/export_history.log';
  $line = $type . '|' . date('Y-m-d H:i:s') . '|' . $user . '|' . $recipient . '|' . $desc . "\n";
  file_put_contents($export_history_file, $line, FILE_APPEND | LOCK_EX);
}

// Log feedback export
if ($feedback_export_enabled && $feedback_csv) {
  $initiator = isset($argv[0]) ? 'Scheduled' : ($_SERVER['PHP_AUTH_USER'] ?? 'Manual');
  log_export_event('feedback', $initiator, $feedback_export_recipient, 'Feedback export (filters: from=' . $feedback_export_from . ', to=' . $feedback_export_to . ', action=' . $feedback_export_action . ', user=' . $feedback_export_user . ', search=' . $feedback_export_search . ')');
}
// Log anomaly export
if (($anomaly_export_enabled || (isset($argv[1]) && $argv[1] === 'anomaly')) && $anomaly_csv) {
  $initiator = (isset($argv[1]) && $argv[1] === 'anomaly') ? 'Manual' : 'Scheduled';
  log_export_event('anomaly', $initiator, $anomaly_export_recipient, 'Anomaly export (filters: from=' . $anomaly_export_from . ', to=' . $anomaly_export_to . ', type=' . $anomaly_export_type . ', user=' . $anomaly_export_user . ', ip=' . $anomaly_export_ip . ', search=' . $anomaly_export_search . ')');
}

// --- Email with attachments ---
$boundary = "----=_Part_".uniqid();
$subject = "[APS Admin] Scheduled Analytics Report (".date('Y-m-d').")";
$body = "Scheduled analytics report attached.\n\n- Analytics summary: summary.csv\n- Blocklist audit log: audit.csv\n";
$headers = "From: noreply@aps.com\r\n";
$headers .= "MIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
$message = "--$boundary\r\nContent-Type: text/plain; charset=utf-8\r\n\r\n$body\r\n";
if ($send_summary) {
    $message .= "--$boundary\r\nContent-Type: text/csv; name=\"summary.csv\"\r\nContent-Disposition: attachment; filename=\"summary.csv\"\r\n\r\n$summary_csv\r\n";
}
if ($send_audit) {
    $message .= "--$boundary\r\nContent-Type: text/csv; name=\"audit.csv\"\r\nContent-Disposition: attachment; filename=\"audit.csv\"\r\n\r\n$audit_csv\r\n";
}
// Attach feedback CSV if enabled and non-empty
if ($feedback_export_enabled && $feedback_csv) {
  $filename = 'feedback_filtered_log_' . date('Ymd_His') . '.csv';
  $message .= "\n--$boundary\nContent-Type: text/csv; name=\"$filename\"\nContent-Disposition: attachment; filename=\"$filename\"\nContent-Transfer-Encoding: 8bit\n\n";
  $message .= $feedback_csv . "\n--$boundary--\n";
}
$message .= $anomaly_summary . $user_breakdown . $ip_breakdown . $anomaly_type_user_str . $anomaly_type_ip_str . $user_trend_str . $ip_trend_str . $user_trend_type_str . $ip_trend_type_str;
if ($anomaly_export_enabled || (isset($argv[1]) && $argv[1] === 'anomaly')) {
  $filename = 'anomaly_filtered_log_' . date('Ymd_His') . '.csv';
  $message .= "\n--$boundary\nContent-Type: text/csv; name=\"$filename\"\nContent-Disposition: attachment; filename=\"$filename\"\nContent-Transfer-Encoding: 8bit\n\n";
  $message .= $anomaly_csv . "\n--$boundary--\n";
}
if ($send_summary || $send_audit || ($anomaly_export_enabled || (isset($argv[1]) && $argv[1] === 'anomaly'))) {
    @mail($admin_email, $subject, $message, $headers);
    // Slack notification if enabled
    if (!empty($cfg['slack_enabled']) && !empty($cfg['slack_webhook'])) {
        $slack_message = "[APS Admin] Scheduled Analytics Report sent on ".date('Y-m-d')."\n";
        if ($send_summary) $slack_message .= "- Analytics summary included\n";
        if ($send_audit) $slack_message .= "- Blocklist audit log included\n";
        if ($anomaly_summary) $slack_message .= "- Anomaly summary included\n";
        if ($user_breakdown) $slack_message .= "- User anomaly breakdown included\n";
        if ($ip_breakdown) $slack_message .= "- IP anomaly breakdown included\n";
        if ($anomaly_type_user_str) $slack_message .= "- User anomaly type breakdown included\n";
        if ($anomaly_type_ip_str) $slack_message .= "- IP anomaly type breakdown included\n";
        if ($user_trend_str) $slack_message .= "- User anomaly trend included\n";
        if ($ip_trend_str) $slack_message .= "- IP anomaly trend included\n";
        if ($user_trend_type_str) $slack_message .= "- User anomaly trend by type included\n";
        if ($ip_trend_type_str) $slack_message .= "- IP anomaly trend by type included\n";
        if ($feedback_export_enabled && $feedback_csv) $slack_message .= "- Filtered feedback analytics CSV attached\n";
        if ($anomaly_export_enabled || (isset($argv[1]) && $argv[1] === 'anomaly')) $slack_message .= "- Anomaly log CSV attached\n";
        $slack_message .= "Frequency: $frequency\nRecipient: $admin_email";
        $payload = json_encode(['text' => $slack_message]);
        $ch = curl_init($cfg['slack_webhook']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Content-Length: '.strlen($payload)]);
        curl_exec($ch);
        curl_close($ch);
    }
    // SMS notification if enabled
    if (!empty($cfg['twilio_enabled'])) {
        $sms_message = "[APS Admin] Scheduled Analytics Report sent on ".date('Y-m-d')."\n";
        if ($send_summary) $sms_message .= "- Analytics summary included\n";
        if ($send_audit) $sms_message .= "- Blocklist audit log included\n";
        if ($anomaly_summary) $sms_message .= "- Anomaly summary included\n";
        if ($user_breakdown) $sms_message .= "- User anomaly breakdown included\n";
        if ($ip_breakdown) $sms_message .= "- IP anomaly breakdown included\n";
        if ($anomaly_type_user_str) $sms_message .= "- User anomaly type breakdown included\n";
        if ($anomaly_type_ip_str) $sms_message .= "- IP anomaly type breakdown included\n";
        if ($user_trend_str) $sms_message .= "- User anomaly trend included\n";
        if ($ip_trend_str) $sms_message .= "- IP anomaly trend included\n";
        if ($user_trend_type_str) $sms_message .= "- User anomaly trend by type included\n";
        if ($ip_trend_type_str) $sms_message .= "- IP anomaly trend by type included\n";
        if ($feedback_export_enabled && $feedback_csv) $sms_message .= "- Filtered feedback analytics CSV attached\n";
        if ($anomaly_export_enabled || (isset($argv[1]) && $argv[1] === 'anomaly')) $sms_message .= "- Anomaly log CSV attached\n";
        $sms_message .= "Frequency: $frequency\nRecipient: $admin_email";
        send_sms_twilio($sms_message, $cfg);
    }
    // --- After sending report, optionally rotate/clear anomaly log ---
    if (file_exists($anomaly_log_file)) {
        $lines = file($anomaly_log_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        $since = ($frequency === 'weekly') ? strtotime('-7 days') : strtotime('-1 day');
        // Keep only lines newer than window
        $keep = array_filter($lines, function($line) use ($since) {
            if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $m)) {
                return strtotime($m[1]) >= $since;
            }
            return false;
        });
        file_put_contents($anomaly_log_file, implode("\n", $keep) . (count($keep) ? "\n" : ''));
    }
    // Update last run
    $anomaly_last_run_file = __DIR__ . '/../includes/anomaly_export_last_run.txt';
    file_put_contents($anomaly_last_run_file, date('Y-m-d H:i:s'));
}
