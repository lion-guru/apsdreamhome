<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser']) || $_SESSION['auser'] !== 'superadmin') { http_response_code(403); exit('Access denied.'); }

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = [];
if ($filter && in_array($filter, ['admin_user','action','ip_address'])) {
    $where[] = "$filter LIKE '%" . $conn->real_escape_string($search) . "%'";
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$logs = $conn->query("SELECT * FROM audit_access_log $where_sql ORDER BY accessed_at DESC LIMIT 200");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Access Log</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
    <h2>Audit Access Log</h2>
    <form class="row g-3 mb-3" method="get">
        <div class="col-auto">
            <select name="filter" class="form-select">
                <option value="">Filter by...</option>
                <option value="admin_user"<?= $filter=="admin_user"?' selected':'' ?>>Admin User</option>
                <option value="action"<?= $filter=="action"?' selected':'' ?>>Action</option>
                <option value="ip_address"<?= $filter=="ip_address"?' selected':'' ?>>IP Address</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search value...">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
        <div class="col-auto">
            <a href="audit_access_log_view.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Admin User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP Address</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $logs->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['admin_user']) ?></td>
                <td><?= htmlspecialchars($row['action']) ?></td>
                <td><pre style="white-space:pre-wrap;max-width:300px;overflow:auto;word-break:break-all;"><?= htmlspecialchars($row['details']) ?></pre></td>
                <td><?= htmlspecialchars($row['ip_address']) ?></td>
                <td><?= $row['accessed_at'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <form method="get" action="" class="mt-3">
        <button name="export" value="csv" class="btn btn-success">Export CSV</button>
    </form>
    <form method="get" action="audit_access_log_report_pdf.php" class="mt-3" target="_blank">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-danger">Download Compliance Report (PDF)</button>
    </form>
</div>
</body>
</html>

<?php
// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="audit_access_log.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Admin User','Action','Details','IP Address','Date']);
    $logs_export = $conn->query("SELECT * FROM audit_access_log $where_sql ORDER BY accessed_at DESC");
    while ($row = $logs_export->fetch_assoc()) {
        fputcsv($out, [
            $row['id'],
            $row['admin_user'],
            $row['action'],
            $row['details'],
            $row['ip_address'],
            $row['accessed_at'],
        ]);
    }
    fclose($out);
    exit;
}
?>
