<?php
// payouts_report.php
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

require_permission('view_payout_report');

// Handle payout status update
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['approve']);
    $db->execute("UPDATE payouts SET status='approved' WHERE id=:id", ['id' => $id]);

    // Fetch payout details for notification
    $p_data = $db->fetchOne("SELECT p.amount, a.name as associate_name FROM payouts p JOIN associates a ON p.associate_id = a.id WHERE p.id = :id", ['id' => $id]);

    require_once __DIR__ . '/../includes/notification_manager.php';
    require_once __DIR__ . '/../includes/email_service.php';
    $nm = new NotificationManager(null, new EmailService());
    $nm->send([
        'user_id' => 1,
        'template' => 'PAYOUT_PROCESSED',
        'data' => [
            'amount' => $p_data['amount'] ?? 0,
            'associate_name' => $p_data['associate_name'] ?? 'Associate',
            'payout_id' => $id
        ],
        'channels' => ['db']
    ]);
}
if (isset($_GET['pay']) && is_numeric($_GET['pay'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['pay']);
    $db->execute("UPDATE payouts SET status='paid' WHERE id=:id", ['id' => $id]);

    // Fetch payout details for notification
    $p_data = $db->fetchOne("SELECT p.amount, a.name as associate_name FROM payouts p JOIN associates a ON p.associate_id = a.id WHERE p.id = :id", ['id' => $id]);

    require_once __DIR__ . '/../includes/notification_manager.php';
    require_once __DIR__ . '/../includes/email_service.php';
    $nm = new NotificationManager(null, new EmailService());
    $nm->send([
        'user_id' => 1,
        'template' => 'PAYOUT_PROCESSED',
        'data' => [
            'amount' => $p_data['amount'] ?? 0,
            'associate_name' => $p_data['associate_name'] ?? 'Associate',
            'payout_id' => $id
        ],
        'channels' => ['db']
    ]);
}

// Filters
$where = "1=1";
$params = [];
$types = "";

if (!empty($_GET['status'])) {
    $status = $_GET['status'];
    $where .= " AND p.status=:status";
    $params['status'] = $status;
}
if (!empty($_GET['associate_id'])) {
    $aid = intval($_GET['associate_id']);
    $where .= " AND p.associate_id=:aid";
    $params['aid'] = $aid;
}
if (!empty($_GET['period'])) {
    $period = $_GET['period'];
    $where .= " AND p.period=:period";
    $params['period'] = $period;
}

$sql = "SELECT p.*, a.name AS associate_name, s.amount AS sale_amount 
        FROM payouts p 
        JOIN associates a ON p.associate_id=a.id 
        JOIN sales s ON p.sale_id=s.id 
        WHERE $where 
        ORDER BY p.generated_on DESC";

$payouts = $db->fetchAll($sql, $params);

$associates = $db->fetchAll("SELECT id, name FROM associates ORDER BY name");
?>
<?php include '../includes/templates/dynamic_header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Payouts Report</title>
    <link rel="stylesheet" href="<?= get_admin_asset_url('bootstrap.min.css', 'css') ?>">
</head>

<body>
    <div class="container mt-4">
        <h3>Payouts Report & Approval</h3>
        <form class="row g-2 mb-3" method="get">
            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" <?= (@$_GET['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= (@$_GET['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                    <option value="paid" <?= (@$_GET['status'] === 'paid') ? 'selected' : '' ?>>Paid</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="associate_id" class="form-control">
                    <option value="">All Associates</option>
                    <?php foreach ($associates as $row): ?>
                        <option value="<?= $row['id'] ?>" <?= (@$_GET['associate_id'] == $row['id']) ? 'selected' : '' ?>><?= h($row['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="period" class="form-control" placeholder="YYYY-MM" value="<?= h(@$_GET['period']) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Associate</th>
                    <th>Sale Amount</th>
                    <th>Payout %</th>
                    <th>Payout Amount</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Actions</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payouts as $row): ?>
                    <tr>
                        <td><?= h($row['generated_on']) ?></td>
                        <td><?= h($row['associate_name']) ?></td>
                        <td>₹<?= number_format($row['sale_amount'], 2) ?></td>
                        <td><?= h($row['payout_percent']) ?>%</td>
                        <td>₹<?= number_format($row['payout_amount'], 2) ?></td>
                        <td><?= h($row['period']) ?></td>
                        <td><?= h($row['status']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <a href="?approve=<?= $row['id'] ?>&csrf_token=<?= generateCSRFToken() ?>" class="btn btn-sm btn-warning">Approve</a>
                            <?php endif; ?>
                            <?php if ($row['status'] === 'approved'): ?>
                                <a href="?pay=<?= $row['id'] ?>&csrf_token=<?= generateCSRFToken() ?>" class="btn btn-sm btn-success">Mark Paid</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal<?= (int)$row['sale_id'] ?>">Details</button>
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
                                        <thead>
                                            <tr>
                                                <th>Associate</th>
                                                <th>Payout %</th>
                                                <th>Payout Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $saleId = intval($row['sale_id']);
                                            $breakdown = $db->fetchAll("SELECT p.*, a.name AS associate_name FROM payouts p JOIN associates a ON p.associate_id=a.id WHERE p.sale_id=:sale_id ORDER BY p.payout_percent DESC", ['sale_id' => $saleId]);
                                            foreach ($breakdown as $b):
                                            ?>
                                                <tr>
                                                    <td><?= h($b['associate_name']) ?></td>
                                                    <td><?= h($b['payout_percent']) ?>%</td>
                                                    <td>₹<?= number_format($b['payout_amount'], 2) ?></td>
                                                    <td><?= h($b['status']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="<?= get_admin_asset_url('bootstrap.bundle.min.js', 'js') ?>"></script>
    <?php include '../includes/templates/new_footer.php'; ?>
</body>

</html>