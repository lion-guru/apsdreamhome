<?php
session_start();
require("config.php");

if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

require_permission('add_booking');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $property_type = $_POST['property_type'];
    $installment_plan = $_POST['installment_plan'];

    // --- Notification utility ---
    require_once __DIR__ . '/../includes/functions/notification_util.php';

    $stmt = $con->prepare("INSERT INTO bookings (customer_name, property_type, installment_plan) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $customer_name, $property_type, $installment_plan);
    if ($stmt->execute()) {
        // Add notification for booking
        addNotification($con, 'Booking', 'New booking added for ' . $customer_name);
        header("Location: bookings.php?msg=".urlencode('Booking added successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}

// Fetch property types for dropdown
$propertyTypes = fetchPropertyTypes($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Booking</title>
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
    <?php include("../includes/templates/dynamic_header.php"); ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="mb-4 text-center">Add New Booking</h2>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="form-floating mb-3 position-relative">
                                <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Customer Name" required>
                                <label for="customer_name"><i class="fa fa-user"></i> Customer Name</label>
                                <div class="invalid-feedback">Please enter the customer's name.</div>
                            </div>
                            <div class="form-floating mb-3 position-relative">
                                <select class="form-select" id="property_type" name="property_type" required>
                                    <option value="" disabled selected>Select property type</option>
                                    <?php while ($type = $propertyTypes->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($type['id']); ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <label for="property_type"><i class="fa fa-building"></i> Property Type</label>
                                <div class="invalid-feedback">Please select a property type.</div>
                            </div>
                            <div class="form-floating mb-4 position-relative">
                                <input type="text" class="form-control" id="installment_plan" name="installment_plan" placeholder="Installment Plan" required>
                                <label for="installment_plan"><i class="fa fa-credit-card"></i> Installment Plan</label>
                                <div class="invalid-feedback">Please enter the installment plan.</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add Booking</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("../includes/templates/new_footer.php"); ?>
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
