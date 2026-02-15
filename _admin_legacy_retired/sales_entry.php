<?php
// sales_entry.php
session_start();
require_once '../config.php';
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

function get_slab_percent($conn, $post, $business) {
    $percent = 0;
    $stmt = $conn->prepare("SELECT percent FROM payout_slabs WHERE post = ? AND min_business <= ? AND max_business >= ? LIMIT 1");
    $stmt->bind_param('sdd', $post, $business, $business);
    $stmt->execute();
    $stmt->bind_result($percent);
    $stmt->fetch();
    $stmt->close();
    return $percent ? $percent : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'], 'sales_entry')) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $associate_id = intval($_POST['associate_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $date = $_POST['date'] ?? '';
        $booking_id = $_POST['booking_id'] ?? '';
        if ($associate_id <= 0 || $amount <= 0 || !$date) {
            $error = 'All fields are required and must be valid.';
        } else {
            // Insert sale
            $stmt = $conn->prepare("INSERT INTO sales (associate_id, amount, date, booking_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('idss', $associate_id, $amount, $date, $booking_id);
            $stmt->execute();
            $sale_id = $stmt->insert_id;
            $stmt->close();

            // Traverse tree and assign payouts
            $chain = [];
            $curr_id = $associate_id;
            $prev_percent = 0;
            while ($curr_id) {
                // Get associate info (fetch commission_percent directly)
                $stmt = $conn->prepare("SELECT associate_id, commission_percent, parent_id FROM associates WHERE associate_id = ?");
                $stmt->bind_param('i', $curr_id);
                $stmt->execute();
                $stmt->bind_result($aid, $commission_percent, $parent_id);
                if ($stmt->fetch()) {
                    $percent = floatval($commission_percent);
                    $diff_percent = $percent - $prev_percent;
                    if ($diff_percent > 0) {
                        $payout = $amount * $diff_percent / 100.0;
                        // Insert payout
                        $pstmt = $conn->prepare("INSERT INTO payouts (associate_id, sale_id, payout_amount, payout_percent, period) VALUES (?, ?, ?, ?, ?)");
                        $period = date('Y-m');
                        $pstmt->bind_param('iidds', $aid, $sale_id, $payout, $diff_percent, $period);
                        $pstmt->execute();
                        $pstmt->close();
                    }
                    $prev_percent = $percent;
                    $curr_id = $parent_id;
                } else {
                    break;
                }
                $stmt->close();
            }
            $success = true;
        }
    }
}

// Fetch associates for dropdown
$associates = $conn->query("SELECT id, name FROM associates WHERE status='active'");
?>
<?php include_once '../includes/csrf.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sales Entry</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
        .form-floating input, .form-floating select { padding-left: 2.5rem; }
    </style>
</head>
<body>
<?php include 'includes/admin_sidebar.php'; ?>
<div class="container mt-4">
    <h3>Sales Entry</h3>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Sale and payouts recorded successfully!</div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post" action="" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRFProtection::generateToken('sales_entry')) ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="form-floating position-relative">
                    <select name="associate_id" id="associate_id" class="form-select" required>
                        <option value="">Select Associate</option>
                        <?php while($row = $associates->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="associate_id"><i class="fa fa-user"></i> Associate</label>
                    <div class="invalid-feedback">Please select an associate.</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating position-relative">
                    <input type="number" class="form-control" id="amount" name="amount" placeholder="Amount (₹)" required>
                    <label for="amount"><i class="fa fa-rupee-sign"></i> Amount (₹)</label>
                    <div class="invalid-feedback">Please enter a valid amount.</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating position-relative">
                    <input type="date" class="form-control" id="date" name="date" placeholder="Date" required>
                    <label for="date"><i class="fa fa-calendar"></i> Date</label>
                    <div class="invalid-feedback">Please select a date.</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-floating position-relative">
                    <input type="text" class="form-control" id="booking_id" name="booking_id" placeholder="Booking ID">
                    <label for="booking_id"><i class="fa fa-id-badge"></i> Booking ID</label>
                </div>
            </div>
        </div>
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-save"></i> Save Entry</button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
</body>
</html>
