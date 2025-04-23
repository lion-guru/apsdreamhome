<?php
session_start();
require("config.php");

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$error = "";
$msg = "";

if (isset($_POST['addproperty'])) {
    // Validate and sanitize input
    $pname = filter_input(INPUT_POST, 'pname', FILTER_SANITIZE_STRING);
    $ptype = filter_input(INPUT_POST, 'ptype', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    // Handle file uploads
    $image = [];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $name = basename($_FILES['image']['name']);
        $targetPath = "property/$name";

        // Validate file type
        $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($tmp_name, $targetPath)) {
                $image[] = $name;
            } else {
                $error = "<p class='alert alert-warning'>Error uploading file: $name</p>";
            }
        } else {
            $error = "<p class='alert alert-warning'>Invalid file type for: $name</p>";
        }
    }

    // Prepare SQL statement
    $stmt = $con->prepare("INSERT INTO property (pname, ptype, price, location, description, image) VALUES (?, ?, ?, ?, ?, ?)");

    // Bind parameters
    $stmt->bind_param("ssssss", $pname, $ptype, $price, $location, $description, $image[0]);

    // Execute and check for success
    if ($stmt->execute()) {
        $msg = "<p class='alert alert-success'>Property Inserted Successfully</p>";
    } else {
        $error = "<p class='alert alert-warning'>Something went wrong: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>APS DREAM HOMES | Property</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
</head>
<body>
    <?php include("../includes/templates/header.php"); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Add Property</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Add Property</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h2 class="card-title">Add Property</h2>
                        </div>
                        <div class="card-body">
                            <?php if($error) echo $error; ?>
                            <?php if($msg) echo $msg; ?>
                            <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating position-relative mb-3">
                                            <input type="text" class="form-control" id="pname" name="pname" placeholder="Property Name" required value="<?php echo isset($_POST['pname']) ? htmlspecialchars($_POST['pname']) : ''; ?>">
                                            <label for="pname"><i class="fa fa-home"></i> Property Name</label>
                                            <div class="invalid-feedback">Please enter the property name.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating position-relative mb-3">
                                            <input type="text" class="form-control" id="ptype" name="ptype" placeholder="Property Type" required value="<?php echo isset($_POST['ptype']) ? htmlspecialchars($_POST['ptype']) : ''; ?>">
                                            <label for="ptype"><i class="fa fa-building"></i> Property Type</label>
                                            <div class="invalid-feedback">Please enter the property type.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating position-relative mb-3">
                                            <input type="number" class="form-control" id="price" name="price" placeholder="Price" required value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                                            <label for="price"><i class="fa fa-rupee-sign"></i> Price</label>
                                            <div class="invalid-feedback">Please enter the price.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating position-relative mb-3">
                                            <input type="text" class="form-control" id="location" name="location" placeholder="Location" required value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                                            <label for="location"><i class="fa fa-map-marker-alt"></i> Location</label>
                                            <div class="invalid-feedback">Please enter the location.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="image" class="form-label"><i class="fa fa-image"></i> Property Image</label>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                            <div class="invalid-feedback">Please upload a property image.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-floating position-relative mb-3">
                                            <textarea class="form-control" id="description" name="description" placeholder="Description" style="height: 100px" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                            <label for="description"><i class="fa fa-align-left"></i> Description</label>
                                            <div class="invalid-feedback">Please enter the property description.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid mt-4">
                                    <button type="submit" name="addproperty" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add Property</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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