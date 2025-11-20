<?php
// Land Purchase Management Module
session_start();
require_once(__DIR__ . '/../includes/db_config.php');
require_once(__DIR__ . '/../includes/SessionManager.php');
$sessionManager = new SessionManager();
if (!isset($_SESSION['utype']) || !in_array($_SESSION['utype'], ['admin', 'super_admin'])) {
    header('Location: ../login.php');
    exit;
}

$conn = $con;
if (!$conn) {
    die('Database connection failed.');
}

// Handle Add/Edit/Delete
$errors = [];
$success = '';

// Add new land purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_land'])) {
    $seller_name = trim($_POST['seller_name'] ?? '');
    $purchase_date = $_POST['purchase_date'] ?? '';
    $payment_amount = $_POST['payment_amount'] ?? '';
    $registry_no = trim($_POST['registry_no'] ?? '');
    $agreement_doc = trim($_POST['agreement_doc'] ?? '');
    $site_location = trim($_POST['site_location'] ?? '');
    $engineer = trim($_POST['engineer'] ?? '');
    $map_doc = trim($_POST['map_doc'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$seller_name || !$purchase_date || !$payment_amount) {
        $errors[] = 'Seller name, purchase date, and payment amount are required.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO land_purchases (seller_name, purchase_date, payment_amount, registry_no, agreement_doc, site_location, engineer, map_doc, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssdssssss', $seller_name, $purchase_date, $payment_amount, $registry_no, $agreement_doc, $site_location, $engineer, $map_doc, $notes);
        if ($stmt->execute()) {
            $success = 'Land purchase added successfully!';
        } else {
            $errors[] = 'Failed to add land purchase.';
        }
        $stmt->close();
    }
}

// Fetch all land purchases
$purchases = [];
$result = $conn->query('SELECT * FROM land_purchases ORDER BY purchase_date DESC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $purchases[] = $row;
    }
}

// Use standardized header
include __DIR__ . '/../includes/templates/dynamic_header.php';
?>
<div class="container mt-4">
    <h2 class="mb-4">Land Purchases</h2>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo $e . '<br>'; ?>
        </div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header">Add New Land Purchase</div>
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Seller Name *</label>
                        <input type="text" class="form-control" name="seller_name" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Purchase Date *</label>
                        <input type="date" class="form-control" name="purchase_date" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Payment Amount *</label>
                        <input type="number" class="form-control" name="payment_amount" step="0.01" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Registry No</label>
                        <input type="text" class="form-control" name="registry_no">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Agreement Doc (URL or Path)</label>
                        <input type="text" class="form-control" name="agreement_doc">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Site Location</label>
                        <input type="text" class="form-control" name="site_location">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Engineer</label>
                        <input type="text" class="form-control" name="engineer">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Map Doc (URL or Path)</label>
                        <input type="text" class="form-control" name="map_doc">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Notes</label>
                        <input type="text" class="form-control" name="notes">
                    </div>
                </div>
                <button type="submit" name="add_land" class="btn btn-primary">Add Land Purchase</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">All Land Purchases</div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Seller</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Registry No</th>
                        <th>Agreement Doc</th>
                        <th>Site Location</th>
                        <th>Engineer</th>
                        <th>Map Doc</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['seller_name']); ?></td>
                            <td><?php echo htmlspecialchars($p['purchase_date']); ?></td>
                            <td><?php echo htmlspecialchars($p['payment_amount']); ?></td>
                            <td><?php echo htmlspecialchars($p['registry_no']); ?></td>
                            <td><?php echo htmlspecialchars($p['agreement_doc']); ?></td>
                            <td><?php echo htmlspecialchars($p['site_location']); ?></td>
                            <td><?php echo htmlspecialchars($p['engineer']); ?></td>
                            <td><?php echo htmlspecialchars($p['map_doc']); ?></td>
                            <td><?php echo htmlspecialchars($p['notes']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
