<?php
session_start();
include 'config.php';
require_once __DIR__ . '/includes/geoip_utils.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }

// Role-based access: Only superadmins can export or drilldown
$is_superadmin = isset($_SESSION['auser']) && $_SESSION['auser'] === 'superadmin';
if ((isset($_GET['export']) || isset($_GET['drilldown'])) && !$is_superadmin) {
    http_response_code(403);
    exit('Access denied: Only superadmins can export or drilldown.');
}

$audit = $conn->query("SELECT * FROM upload_audit_log ORDER BY created_at DESC LIMIT 200");
// Filtering logic
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$drilldown = isset($_GET['drilldown']) ? $_GET['drilldown'] : '';
$drill_val = isset($_GET['val']) ? $_GET['val'] : '';
$where = [];
if ($filter && in_array($filter, ['event_type','uploader','slack_status','telegram_status','entity_table'])) {
    $where[] = "$filter LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if ($date_from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $where[] = "created_at >= '" . $conn->real_escape_string($date_from) . " 00:00:00'";
}
if ($date_to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $where[] = "created_at <= '" . $conn->real_escape_string($date_to) . " 23:59:59'";
}
if ($drilldown && $drill_val) {
    if (in_array($drilldown, ['uploader','event_type','entity_table','entity_id'])) {
        $where[] = "$drilldown = '" . $conn->real_escape_string($drill_val) . "'";
    }
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$audit = $conn->query("SELECT * FROM upload_audit_log $where_sql ORDER BY created_at DESC LIMIT 200");

// Analytics queries
$summary = $conn->query("SELECT COUNT(*) as total, COUNT(DISTINCT uploader) as users, SUM(slack_status='sent') as slack_ok, SUM(telegram_status='sent') as telegram_ok FROM upload_audit_log $where_sql")->fetch_assoc();
$by_type = [];
$res = $conn->query("SELECT event_type, COUNT(*) as c FROM upload_audit_log $where_sql GROUP BY event_type");
while($row = $res->fetch_assoc()) { $by_type[$row['event_type']] = $row['c']; }
$by_day = [];
$res = $conn->query("SELECT DATE(created_at) as day, COUNT(*) as c FROM upload_audit_log $where_sql GROUP BY day ORDER BY day");
while($row = $res->fetch_assoc()) { $by_day[$row['day']] = $row['c']; }

// Log access to audit log views/exports for compliance
function log_audit_access($conn, $user, $action, $details) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $geo = lookup_ip_geolocation($ip);
    $local_time = '';
    if (!empty($geo['timezone'])) {
        $dt = new DateTime('now', new DateTimeZone($geo['timezone']));
        $local_time = $dt->format('Y-m-d H:i:s');
    }
    $stmt = $conn->prepare("INSERT INTO audit_access_log (admin_user, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $user, $action, $details, $ip);
    $stmt->execute();
    // Forward to SIEM
    $row = [
        'admin_user' => $user,
        'action' => $action,
        'details' => $details,
        'ip_address' => $ip,
        'accessed_at' => date('Y-m-d H:i:s'),
        'geo_city' => $geo['city'] ?? '',
        'geo_region' => $geo['region'] ?? '',
        'geo_country' => $geo['country'] ?? '',
        'geo_timezone' => $geo['timezone'] ?? '',
        'geo_lat' => $geo['lat'] ?? '',
        'geo_lon' => $geo['lon'] ?? '',
        'local_time' => $local_time
    ];
    forward_access_log_to_siem($row);
}

// Send audit access log entry to SIEM endpoint, if configured
function forward_access_log_to_siem($row) {
    global $SIEM_ENDPOINT;
    if (!$SIEM_ENDPOINT) return;
    $payload = json_encode($row);
    $ch = curl_init($SIEM_ENDPOINT);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Send incident notification to webhook if configured
function notify_incident_webhook($payload) {
    global $INCIDENT_WEBHOOK_URL;
    if (!$INCIDENT_WEBHOOK_URL) return;
    $ch = curl_init($INCIDENT_WEBHOOK_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

$admin_user = $_SESSION['auser'] ?? '';
if (isset($_GET['export'])) {
    log_audit_access($conn, $admin_user, 'export', json_encode($_GET));
}
if (isset($_GET['drilldown'])) {
    log_audit_access($conn, $admin_user, 'drilldown', json_encode($_GET));
}
log_audit_access($conn, $admin_user, 'view', json_encode($_GET));

// Suspicious access alerting (simple: >5 exports or drilldowns by same user in a day)
function check_and_alert_suspicious_access($conn, $user) {
    $today = date('Y-m-d');
    $res = $conn->query("SELECT SUM(action='export') as exports, SUM(action='drilldown') as drilldowns FROM audit_access_log WHERE admin_user='".$conn->real_escape_string($user)."' AND accessed_at >= '$today 00:00:00'");
    $row = $res ? $res->fetch_assoc() : ['exports'=>0,'drilldowns'=>0];
    if (($row['exports'] ?? 0) > 5 || ($row['drilldowns'] ?? 0) > 5) {
        require_once __DIR__ . '/mail.php';
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'apsdreamhoms44@gmail.com';
        $mail->Password = '128125@Aps';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('apsdreamhoms44@gmail.com', 'Dream Home Security');
        $mail->addAddress('techguruabhay@gmail.com'); // Add more superadmin emails as needed
        $mail->Subject = 'Suspicious Audit Log Access Detected';
        $mail->Body = "User $user performed {$row['exports']} exports and {$row['drilldowns']} drilldowns today (".date('Y-m-d')."). Please review the Audit Access Log.";
        @$mail->send();
        // Incident webhook notification
        $payload = [
            'event' => 'suspicious_audit_access',
            'user' => $user,
            'exports' => $row['exports'],
            'drilldowns' => $row['drilldowns'],
            'date' => $today,
            'timestamp' => date('c'),
            'summary' => "User $user performed {$row['exports']} exports and {$row['drilldowns']} drilldowns today."
        ];
        notify_incident_webhook($payload);
    }
}
check_and_alert_suspicious_access($conn, $admin_user);

// Anomaly detection: Alert on unfamiliar IP address for audit log access
function alert_on_unfamiliar_ip($conn, $user) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $res = $conn->query("SELECT DISTINCT ip_address FROM audit_access_log WHERE admin_user='".$conn->real_escape_string($user)."' ORDER BY accessed_at DESC LIMIT 10");
    $known_ips = [];
    while ($row = $res && $res->num_rows ? $res->fetch_assoc() : false) { $known_ips[] = $row['ip_address']; }
    if ($ip && $user && !in_array($ip, $known_ips) && count($known_ips) > 0) {
        require_once __DIR__ . '/mail.php';
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'apsdreamhoms44@gmail.com';
        $mail->Password = '128125@Aps';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('apsdreamhoms44@gmail.com', 'Dream Home Security');
        $mail->addAddress('techguruabhay@gmail.com'); // Add more superadmin emails as needed
        $mail->Subject = 'Unfamiliar IP Audit Log Access Detected';
        $mail->Body = "User $user accessed the audit log from a new IP: $ip. Last known IPs: " . implode(', ', $known_ips) . ". Please review the Audit Access Log.";
        @$mail->send();
        // Incident webhook notification
        $payload = [
            'event' => 'unfamiliar_ip_audit_access',
            'user' => $user,
            'ip' => $ip,
            'known_ips' => $known_ips,
            'timestamp' => date('c'),
            'summary' => "User $user accessed the audit log from a new IP: $ip."
        ];
        notify_incident_webhook($payload);
    }
}
alert_on_unfamiliar_ip($conn, $admin_user);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Audit Log</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
    <h2>Upload Audit Log</h2>
    <div class="row mb-4">
        <div class="col">
            <div class="card p-3 text-center"><h5>Total Uploads</h5><span style="font-size:2rem;"><?= $summary['total'] ?></span></div>
        </div>
        <div class="col">
            <div class="card p-3 text-center"><h5>Unique Uploaders</h5><span style="font-size:2rem;"><?= $summary['users'] ?></span></div>
        </div>
        <div class="col">
            <div class="card p-3 text-center"><h5>Slack OK</h5><span style="font-size:2rem;"><?= $summary['slack_ok'] ?></span></div>
        </div>
        <div class="col">
            <div class="card p-3 text-center"><h5>Telegram OK</h5><span style="font-size:2rem;"><?= $summary['telegram_ok'] ?></span></div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6"><canvas id="uploadsByType"></canvas></div>
        <div class="col-md-6"><canvas id="uploadsByDay"></canvas></div>
    </div>
    <div class="row mb-4">
        <div class="col">
            <h5>Drilldown:</h5>
            <form method="get" class="row g-2">
                <div class="col-auto">
                    <select name="drilldown" class="form-select">
                        <option value="">Drilldown by...</option>
                        <option value="uploader"<?= $drilldown=="uploader"?' selected':'' ?>>Uploader</option>
                        <option value="event_type"<?= $drilldown=="event_type"?' selected':'' ?>>Event Type</option>
                        <option value="entity_table"<?= $drilldown=="entity_table"?' selected':'' ?>>Entity Table</option>
                        <option value="entity_id"<?= $drilldown=="entity_id"?' selected':'' ?>>Entity ID</option>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" name="val" value="<?= htmlspecialchars($drill_val) ?>" class="form-control" placeholder="Value">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Drilldown</button>
                </div>
            </form>
        </div>
    </div>
    <form class="row g-3 mb-3" method="get">
        <div class="col-auto">
            <select name="filter" class="form-select">
                <option value="">Filter by...</option>
                <option value="event_type"<?= $filter=="event_type"?' selected':'' ?>>Event Type</option>
                <option value="uploader"<?= $filter=="uploader"?' selected':'' ?>>Uploader</option>
                <option value="slack_status"<?= $filter=="slack_status"?' selected':'' ?>>Slack Status</option>
                <option value="telegram_status"<?= $filter=="telegram_status"?' selected':'' ?>>Telegram Status</option>
                <option value="entity_table"<?= $filter=="entity_table"?' selected':'' ?>>Entity Table</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search value...">
        </div>
        <div class="col-auto">
            <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" class="form-control" placeholder="From date">
        </div>
        <div class="col-auto">
            <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" class="form-control" placeholder="To date">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
        <div class="col-auto">
            <a href="upload_audit_log_view.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Event Type</th>
                <th>Entity/Table</th>
                <th>File Name</th>
                <th>Drive File</th>
                <th>Uploader</th>
                <th>Slack</th>
                <th>Telegram</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $audit->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['event_type']) ?></td>
                <td><?= htmlspecialchars($row['entity_table']) ?> #<?= $row['entity_id'] ?></td>
                <td><?= htmlspecialchars($row['file_name']) ?></td>
                <td>
                    <?php if ($row['drive_file_id']): ?>
                        <a href="https://drive.google.com/file/d/<?= htmlspecialchars($row['drive_file_id']) ?>/view" target="_blank">Drive Link</a>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['uploader']) ?></td>
                <td><?= htmlspecialchars($row['slack_status']) ?></td>
                <td><?= htmlspecialchars($row['telegram_status']) ?></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php if ($is_superadmin): ?>
    <form method="get" action="" class="mt-3">
        <button name="export" value="csv" class="btn btn-success">Export CSV</button>
    </form>
    <?php endif; ?>
</div>
<?php
// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="upload_audit_log.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Event Type','Entity Table','Entity ID','File Name','Drive File','Uploader','Slack','Telegram','Date']);
    $audit_export = $conn->query("SELECT * FROM upload_audit_log $where_sql ORDER BY created_at DESC");
    while ($row = $audit_export->fetch_assoc()) {
        fputcsv($out, [
            $row['id'],
            $row['event_type'],
            $row['entity_table'],
            $row['entity_id'],
            $row['file_name'],
            $row['drive_file_id'],
            $row['uploader'],
            $row['slack_status'],
            $row['telegram_status'],
            $row['created_at'],
        ]);
    }
    fclose($out);
    exit;
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const byType = <?= json_encode($by_type) ?>;
const byDay = <?= json_encode($by_day) ?>;
if (document.getElementById('uploadsByType')) {
    new Chart(document.getElementById('uploadsByType').getContext('2d'), {
        type: 'pie',
        data: {
            labels: Object.keys(byType),
            datasets: [{ data: Object.values(byType), backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6c757d'] }]
        },
        options: { title: { display: true, text: 'Uploads by Type' } }
    });
}
if (document.getElementById('uploadsByDay')) {
    new Chart(document.getElementById('uploadsByDay').getContext('2d'), {
        type: 'bar',
        data: {
            labels: Object.keys(byDay),
            datasets: [{ label: 'Uploads per Day', data: Object.values(byDay), backgroundColor: '#007bff' }]
        },
        options: { title: { display: true, text: 'Uploads per Day' }, scales: { x: { title: { display: true, text: 'Date' } }, y: { beginAtZero:true } } }
    });
}
</script>
</body>
</html>
