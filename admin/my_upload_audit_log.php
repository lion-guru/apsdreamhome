<?php
session_start();
if (!isset($_SESSION['auser'])) { http_response_code(403); exit('Access denied.'); }
include 'config.php';

$user = $_SESSION['auser'];
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$where = "WHERE uploader = '" . $conn->real_escape_string($user) . "'";
if ($filter_date) {
    $where .= " AND DATE(created_at) = '" . $conn->real_escape_string($filter_date) . "'";
}
$sql = "SELECT * FROM upload_audit_log $where ORDER BY created_at DESC LIMIT 100";
$logs = $conn->query($sql);

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="my_upload_audit_log.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Event','Entity Table','Entity ID','Slack','Telegram','Created','Status']);
    $logs_export = $conn->query($sql);
    while ($row = $logs_export->fetch_assoc()) {
        fputcsv($out, [
            $row['id'], $row['event_type'], $row['entity_table'], $row['entity_id'], $row['slack_status'], $row['telegram_status'], $row['created_at'], $row['status']
        ]);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Upload Audit Log</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
    <h2>My Upload Audit Log</h2>
    <form class="row g-2 mb-3" method="get">
        <div class="col-auto">
            <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>" class="form-control">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
        <div class="col-auto">
            <a href="my_upload_audit_log.php" class="btn btn-secondary">Reset</a>
        </div>
        <div class="col-auto">
            <button name="export" value="csv" class="btn btn-success">Export CSV</button>
        </div>
    </form>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Event</th>
                <th>Entity Table</th>
                <th>Entity ID</th>
                <th>Slack</th>
                <th>Telegram</th>
                <th>Created</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $logs->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['event_type']) ?></td>
                <td><?= htmlspecialchars($row['entity_table']) ?></td>
                <td><?= htmlspecialchars($row['entity_id']) ?></td>
                <td><?= htmlspecialchars($row['slack_status']) ?></td>
                <td><?= htmlspecialchars($row['telegram_status']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <form method="get" action="my_upload_audit_log_pdf.php" class="mt-3" target="_blank">
        <input type="hidden" name="date" value="<?= htmlspecialchars($filter_date) ?>">
        <button class="btn btn-danger">Download PDF Report</button>
    </form>
</div>
</body>
</html>
