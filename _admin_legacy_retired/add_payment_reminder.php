<?php
session_start();
require("config.php");

if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $due_date = $_POST['due_date'];
    $amount = $_POST['amount'];

    $stmt = $con->prepare("INSERT INTO payment_reminders (customer_name, due_date, amount) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $customer_name, $due_date, $amount);
    if ($stmt->execute()) {
        header("Location: reminders.php?msg=".urlencode('Payment reminder added successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Payment Reminder</title>
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
                        <h2 class="mb-4 text-center">Add Payment Reminder</h2>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="form-floating mb-3 position-relative">
                                <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Customer Name" required value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>">
                                <label for="customer_name"><i class="fa fa-user"></i> Customer Name</label>
                                <div class="invalid-feedback">Please enter the customer's name.</div>
                            </div>
                            <div class="form-floating mb-3 position-relative">
                                <input type="date" class="form-control" id="due_date" name="due_date" placeholder="Due Date" required value="<?php echo isset($_POST['due_date']) ? htmlspecialchars($_POST['due_date']) : ''; ?>">
                                <label for="due_date"><i class="fa fa-calendar"></i> Due Date</label>
                                <div class="invalid-feedback">Please select a due date.</div>
                            </div>
                            <div class="form-floating mb-4 position-relative">
                                <input type="number" class="form-control" id="amount" name="amount" placeholder="Amount" required value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
                                <label for="amount"><i class="fa fa-rupee-sign"></i> Amount</label>
                                <div class="invalid-feedback">Please enter the amount.</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add Reminder</button>
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
