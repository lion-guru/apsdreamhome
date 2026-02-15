<?php
session_start();
require("config.php");
require_once __DIR__ . '/../includes/log_admin_activity.php';
require_once __DIR__ . '/../includes/functions/permission_util.php';

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_permission('add_customer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $kyc_status = $_POST['kyc_status'];
    $user_id = $_SESSION['uid'] ?? null;

    $stmt = $con->prepare("INSERT INTO customers (name, kyc_status) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $kyc_status);
    if ($stmt->execute()) {
        log_admin_activity('add_customer', 'Added customer: ' . $name . ', KYC status: ' . $kyc_status);
        require_once __DIR__ . '/../includes/functions/notification_util.php';
        addNotification($con, 'Customer', 'New customer added: ' . $name, $user_id);
        header("Location: customer_management.php?msg=".urlencode('Customer added successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Customer</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .form-label { font-weight: 500; }
        .form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
        .form-floating input, .form-floating select { padding-left: 2.5rem; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="mb-4 text-center">Add New Customer</h2>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="form-floating mb-3 position-relative">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Customer Name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                <label for="name"><i class="fa fa-user"></i> Customer Name</label>
                                <div class="invalid-feedback">Please enter the customer's name.</div>
                            </div>
                            <div class="form-floating mb-4 position-relative">
                                <select class="form-select" id="kyc_status" name="kyc_status" required>
                                    <option value="" disabled selected>Select KYC status</option>
                                    <option value="Pending" <?php if(isset($_POST['kyc_status']) && $_POST['kyc_status']==='Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Verified" <?php if(isset($_POST['kyc_status']) && $_POST['kyc_status']==='Verified') echo 'selected'; ?>>Verified</option>
                                    <option value="Rejected" <?php if(isset($_POST['kyc_status']) && $_POST['kyc_status']==='Rejected') echo 'selected'; ?>>Rejected</option>
                                </select>
                                <label for="kyc_status"><i class="fa fa-id-card"></i> KYC Status</label>
                                <div class="invalid-feedback">Please select a KYC status.</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add Customer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Bootstrap 5 client-side validation
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
