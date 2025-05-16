<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser']) || $_SESSION['auser'] !== 'superadmin') { http_response_code(403); exit('Access denied.'); }
$conn = getDbConnection();

// Summary metrics
$last_archive = $conn->query("SELECT MAX(created_at) as last FROM upload_audit_log")->fetch_assoc()['last'] ?? 'N/A';
$last_verif = $conn->query("SELECT MAX(accessed_at) as last FROM audit_access_log WHERE action='archive_verify'")->fetch_assoc()['last'] ?? 'N/A';
$last_incident = $conn->query("SELECT MAX(accessed_at) as last FROM audit_access_log WHERE action LIKE '%failed%'")->fetch_assoc()['last'] ?? 'N/A';
$failed_verif = $conn->query("SELECT COUNT(*) as c FROM audit_access_log WHERE action='archive_verify' AND JSON_EXTRACT(details, '$.ok')=false")->fetch_assoc()['c'] ?? 0;
$failed_upload = $conn->query("SELECT COUNT(*) as c FROM audit_access_log WHERE action='cloud_upload_failed'")->fetch_assoc()['c'] ?? 0;
$failed_download = $conn->query("SELECT COUNT(*) as c FROM audit_access_log WHERE action='cloud_download_failed'")->fetch_assoc()['c'] ?? 0;

// Chart data: archive creation per month (last 12 months)
$archive_trend = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as c FROM upload_audit_log GROUP BY ym ORDER BY ym DESC LIMIT 12");
$archive_labels = [];
$archive_counts = [];
while ($row = $archive_trend->fetch_assoc()) {
    array_unshift($archive_labels, $row['ym']);
    array_unshift($archive_counts, $row['c']);
}
// Chart data: verification failures per month
$verif_trend = $conn->query("SELECT DATE_FORMAT(accessed_at, '%Y-%m') as ym, SUM(JSON_EXTRACT(details, '$.ok')=false) as fails FROM audit_access_log WHERE action='archive_verify' GROUP BY ym ORDER BY ym DESC LIMIT 12");
$verif_labels = [];
$verif_fails = [];
while ($row = $verif_trend->fetch_assoc()) {
    array_unshift($verif_labels, $row['ym']);
    array_unshift($verif_fails, $row['fails']);
}
// Chart data: incidents per month
$incident_trend = $conn->query("SELECT DATE_FORMAT(accessed_at, '%Y-%m') as ym, COUNT(*) as c FROM audit_access_log WHERE action LIKE '%failed%' GROUP BY ym ORDER BY ym DESC LIMIT 12");
$incident_labels = [];
$incident_counts = [];
while ($row = $incident_trend->fetch_assoc()) {
    array_unshift($incident_labels, $row['ym']);
    array_unshift($incident_counts, $row['c']);
}

// Recent events
$events = $conn->query("SELECT * FROM audit_access_log ORDER BY accessed_at DESC LIMIT 30");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Compliance Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
<h2>Compliance Dashboard</h2>
<div class="row mb-4">
  <div class="col">
    <div class="card text-bg-success mb-3"><div class="card-body">
      <h5 class="card-title">Last Archive</h5><p class="card-text"><?= htmlspecialchars($last_archive) ?></p>
    </div></div>
  </div>
  <div class="col">
    <div class="card text-bg-info mb-3"><div class="card-body">
      <h5 class="card-title">Last Verification</h5><p class="card-text"><?= htmlspecialchars($last_verif) ?></p>
    </div></div>
  </div>
  <div class="col">
    <div class="card text-bg-warning mb-3"><div class="card-body">
      <h5 class="card-title">Last Incident</h5><p class="card-text"><?= htmlspecialchars($last_incident) ?></p>
    </div></div>
  </div>
</div>
<div class="row mb-4">
  <div class="col">
    <div class="card text-bg-danger mb-3"><div class="card-body">
      <h5 class="card-title">Failed Verifications</h5><p class="card-text"><?= $failed_verif ?></p>
    </div></div>
  </div>
  <div class="col">
    <div class="card text-bg-danger mb-3"><div class="card-body">
      <h5 class="card-title">Failed Uploads</h5><p class="card-text"><?= $failed_upload ?></p>
    </div></div>
  </div>
  <div class="col">
    <div class="card text-bg-danger mb-3"><div class="card-body">
      <h5 class="card-title">Failed Cloud Downloads</h5><p class="card-text"><?= $failed_download ?></p>
    </div></div>
  </div>
</div>
<h4>Recent Audit/Incident Events</h4>
<table class="table table-bordered table-striped">
<thead><tr><th>Time</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
<tbody>
<?php while ($row = $events->fetch_assoc()): ?>
<tr>
  <td><?= htmlspecialchars($row['accessed_at']) ?></td>
  <td><?= htmlspecialchars($row['admin_user']) ?></td>
  <td><?= htmlspecialchars($row['action']) ?></td>
  <td><code><?= htmlspecialchars($row['details']) ?></code></td>
  <td><?= htmlspecialchars($row['ip_address']) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<a href="log_archive_view.php" class="btn btn-secondary mt-3">View Log Archives</a>
<h4 class="mt-5">Compliance Trends (Last 12 Months)</h4>
<div class="row mb-4">
  <div class="col-md-4"><canvas id="archiveTrend"></canvas></div>
  <div class="col-md-4"><canvas id="verifTrend"></canvas></div>
  <div class="col-md-4"><canvas id="incidentTrend"></canvas></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const archiveTrend = new Chart(document.getElementById('archiveTrend').getContext('2d'), {
    type: 'bar',
    data: {labels: <?= json_encode($archive_labels) ?>, datasets: [{label: 'Archives', data: <?= json_encode($archive_counts) ?>, backgroundColor: '#198754'}]},
    options: {plugins: {title: {display: true, text: 'Archives Created'}}}
});
const verifTrend = new Chart(document.getElementById('verifTrend').getContext('2d'), {
    type: 'bar',
    data: {labels: <?= json_encode($verif_labels) ?>, datasets: [{label: 'Failed Verifications', data: <?= json_encode($verif_fails) ?>, backgroundColor: '#dc3545'}]},
    options: {plugins: {title: {display: true, text: 'Verification Failures'}}}
});
const incidentTrend = new Chart(document.getElementById('incidentTrend').getContext('2d'), {
    type: 'bar',
    data: {labels: <?= json_encode($incident_labels) ?>, datasets: [{label: 'Incidents', data: <?= json_encode($incident_counts) ?>, backgroundColor: '#ffc107'}]},
    options: {plugins: {title: {display: true, text: 'Incidents/Alerts'}}}
});
</script>
</div>
</body>
</html>
