<?php
// payouts_report.php
session_start();
require_once '../config.php';
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

require_permission('view_payout_report');

// Handle payout status update using prepared statements
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE payouts SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    require_once __DIR__ . '/../includes/functions/notification_util.php';
    addNotification($conn, 'Payout', 'Payout report generated or approved.', $_SESSION['auser'] ?? null);
}
if (isset($_GET['pay']) && is_numeric($_GET['pay'])) {
    $id = intval($_GET['pay']);
    $stmt = $conn->prepare("UPDATE payouts SET status='paid' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    require_once __DIR__ . '/../includes/functions/notification_util.php';
    addNotification($conn, 'Payout', 'Payout report generated or approved.', $_SESSION['auser'] ?? null);
}

// Filters using prepared statements
$where_conditions = [];
$params = [];
$types = "";

if (!empty($_GET['status'])) {
    $status = $_GET['status'];
    $where_conditions[] = "p.status=?";
    $params[] = $status;
    $types .= "s";
}
if (!empty($_GET['associate_id'])) {
    $aid = intval($_GET['associate_id']);
    $where_conditions[] = "p.associate_id=?";
    $params[] = $aid;
    $types .= "i";
}
if (!empty($_GET['period'])) {
    $period = $_GET['period'];
    $where_conditions[] = "p.period=?";
    $params[] = $period;
    $types .= "s";
}

$where_clause = empty($where_conditions) ? "1=1" : implode(" AND ", $where_conditions);

// Fetch payouts using prepared statement
$query = "SELECT p.*, a.name AS associate_name, s.amount AS sale_amount FROM payouts p JOIN associates a ON p.associate_id=a.id JOIN sales s ON p.sale_id=s.id WHERE $where_clause ORDER BY p.generated_on DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$payouts = $stmt->get_result();
$stmt->close();

// Fetch associates using prepared statement
$stmt = $conn->prepare("SELECT id, name FROM associates ORDER BY name");
$stmt->execute();
$associates = $stmt->get_result();
$stmt->close();
?>
<?php include '../includes/templates/dynamic_header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payouts Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>Payouts Report & Approval</h3>
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-2">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="pending" <?= (@$_GET['status']==='pending')?'selected':'' ?>>Pending</option>
                <option value="approved" <?= (@$_GET['status']==='approved')?'selected':'' ?>>Approved</option>
                <option value="paid" <?= (@$_GET['status']==='paid')?'selected':'' ?>>Paid</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="associate_id" class="form-control">
                <option value="">All Associates</option>
                <?php while($row = $associates->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= (@$_GET['associate_id']==$row['id'])?'selected':'' ?>><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" name="period" class="form-control" placeholder="YYYY-MM" value="<?= htmlspecialchars(@$_GET['period']) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <table class="table table-bordered table-hover">
        <thead><tr>
            <th>Date</th><th>Associate</th><th>Sale Amount</th><th>Payout %</th><th>Payout Amount</th><th>Period</th><th>Status</th><th>Actions</th><th>Details</th>
        </tr></thead>
        <tbody>
        <?php while($row = $payouts->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['generated_on']) ?></td>
                <td><?= htmlspecialchars($row['associate_name']) ?></td>
                <td>₹<?= number_format($row['sale_amount'],2) ?></td>
                <td><?= htmlspecialchars($row['payout_percent']) ?>%</td>
                <td>₹<?= number_format($row['payout_amount'],2) ?></td>
                <td><?= htmlspecialchars($row['period']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <?php if($row['status']==='pending'): ?>
                        <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Approve</a>
                    <?php endif; ?>
                    <?php if($row['status']==='approved'): ?>
                        <a href="?pay=<?= $row['id'] ?>" class="btn btn-sm btn-success">Mark Paid</a>
                    <?php endif; ?>
                </td>
                <td>
                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal<?= $row['sale_id'] ?>">Details</button>
                </td>
            </tr>
            <!-- Details Modal for this sale -->
            <div class="modal fade" id="detailsModal<?= $row['sale_id'] ?>" tabindex="-1" aria-labelledby="detailsModalLabel<?= $row['sale_id'] ?>" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel<?= $row['sale_id'] ?>">Payout Breakdown for Sale #<?= $row['sale_id'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <table class="table table-sm table-bordered">
                      <thead><tr><th>Associate</th><th>Payout %</th><th>Payout Amount</th><th>Status</th></tr></thead>
                      <tbody>
                      <?php
                        $saleId = intval($row['sale_id']);
                        $breakdown_stmt = $conn->prepare("SELECT p.*, a.name AS associate_name FROM payouts p JOIN associates a ON p.associate_id=a.id WHERE p.sale_id=? ORDER BY p.payout_percent DESC");
                        $breakdown_stmt->bind_param("i", $saleId);
                        $breakdown_stmt->execute();
                        $breakdown = $breakdown_stmt->get_result();
                        $breakdown_stmt->close();
                        while($b = $breakdown->fetch_assoc()):
                      ?>
                        <tr>
                          <td><?= htmlspecialchars($b['associate_name']) ?></td>
                          <td><?= htmlspecialchars($b['payout_percent']) ?>%</td>
                          <td>₹<?= number_format($b['payout_amount'],2) ?></td>
                          <td><?= htmlspecialchars($b['status']) ?></td>
                        </tr>
                      <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/templates/new_footer.php'; ?>
</body>
</html>
