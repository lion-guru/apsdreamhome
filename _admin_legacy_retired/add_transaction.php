<?php
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kisan_id = intval($_POST['kisan_id']);
    $amount = floatval($_POST['amount']);
    $date = $_POST['date'];
    $description = htmlspecialchars(trim($_POST['description']));

    $stmt = $con->prepare("INSERT INTO transactions (kisaan_id, amount, date, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $kisan_id, $amount, $date, $description);

    if ($stmt->execute()) {
        header("Location: kissan_land_management.php?msg=".urlencode('Transaction added successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Transaction</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .form-label { font-weight: 500; }
        .form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
        .form-floating input { padding-left: 2.5rem; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="mb-4 text-center">Add Transaction</h2>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="form-floating mb-3 position-relative">
                                <input type="number" class="form-control" id="kisan_id" name="kisan_id" placeholder="Kisan ID" required>
                                <label for="kisan_id"><i class="fa fa-id-card"></i> Kisan ID</label>
                                <div class="invalid-feedback">Please enter the Kisan ID.</div>
                            </div>
                            <div class="form-floating mb-3 position-relative">
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Amount" required>
                                <label for="amount"><i class="fa fa-rupee-sign"></i> Amount</label>
                                <div class="invalid-feedback">Please enter the amount.</div>
                            </div>
                            <div class="form-floating mb-3 position-relative">
                                <input type="date" class="form-control" id="date" name="date" placeholder="Date" required>
                                <label for="date"><i class="fa fa-calendar"></i> Date</label>
                                <div class="invalid-feedback">Please select a date.</div>
                            </div>
                            <div class="form-floating mb-4 position-relative">
                                <input type="text" class="form-control" id="description" name="description" placeholder="Description" required>
                                <label for="description"><i class="fa fa-align-left"></i> Description</label>
                                <div class="invalid-feedback">Please enter a description.</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add Transaction</button>
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
