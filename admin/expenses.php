<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

// Filtering
$where = [];
$params = [];
$types = '';

if (!empty($_GET['user_id'])) {
    $where[] = 'user_id = ?';
    $params[] = $_GET['user_id'];
    $types .= 'i';
}
if (!empty($_GET['source'])) {
    $where[] = 'source = ?';
    $params[] = $_GET['source'];
    $types .= 's';
}
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $where[] = 'expense_date BETWEEN ? AND ?';
    $params[] = $_GET['from'];
    $params[] = $_GET['to'];
    $types .= 'ss';
}

$sql = 'SELECT * FROM expenses';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY expense_date DESC, id DESC';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Calculate total
$total = 0;
$rows = [];
while ($row = $result->fetch_assoc()) {
    $total += $row['amount'];
    $rows[] = $row;
}

$stmt->close();

// For export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="expenses.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'User', 'Amount', 'Source', 'Date', 'Description']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['user_id'], $r['amount'], $r['source'], $r['expense_date'], $r['description']]);
    }
    fclose($out);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expenses Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4">Expenses Report</h2>
    <form class="row mb-3 g-2" method="get">
        <div class="col-md-2">
            <input type="text" class="form-control" name="user_id" placeholder="User ID" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="source" placeholder="Source" value="<?= htmlspecialchars($_GET['source'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
        <div class="col-md-2 text-end">
            <a href="?export=csv" class="btn btn-success">Export CSV</a>
        </div>
    </form>
    <div class="mb-2"><strong>Total Expenses:</strong> ₹<?= number_format($total, 2) ?></div>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Amount</th>
            <th>Source</th>
            <th>Date</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['user_id'] ?></td>
                <td>₹<?= number_format($row['amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['source']) ?></td>
                <td><?= htmlspecialchars($row['expense_date']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
