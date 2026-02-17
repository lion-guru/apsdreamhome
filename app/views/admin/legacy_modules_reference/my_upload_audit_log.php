<?php
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

$user = $_SESSION['auser'];
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

$params = [$user];
$query = "SELECT * FROM upload_audit_log WHERE uploader = ?";

if ($filter_date) {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $filter_date;
}

$query .= " ORDER BY created_at DESC LIMIT 100";
$logs = $db->fetchAll($query, $params);

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="my_upload_audit_log.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Event','Entity Table','Entity ID','Slack','Telegram','Created','Status']);

    foreach ($logs as $row) {
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
            <input type="date" name="date" value="<?= h($filter_date) ?>" class="form-control">
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
        <?php foreach($logs as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= h($row['event_type']) ?></td>
                <td><?= h($row['entity_table']) ?></td>
                <td><?= h($row['entity_id']) ?></td>
                <td><?= h($row['slack_status']) ?></td>
                <td><?= h($row['telegram_status']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= h($row['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <form method="get" action="my_upload_audit_log_pdf.php" class="mt-3" target="_blank">
        <input type="hidden" name="date" value="<?= h($filter_date) ?>">
        <button class="btn btn-danger">Download PDF Report</button>
    </form>
</div>
</body>
</html>
