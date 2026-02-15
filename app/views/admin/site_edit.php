<?php

// Include database connection

require_once __DIR__ . '/core/init.php';

use App\Core\Database;

if (!isAuthenticated() || getAuthRole() !== 'admin') {
    header('Location: index.php');
    exit();
}

$db = \App\Core\App::database();
$error = "";
$msg = "";

if (isset($_POST['update_site'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $site_id = filter_var($_POST['site_id'], FILTER_VALIDATE_INT);
    $site_name = h(trim($_POST['site_name']));

    if (!preg_match('/^[A-Za-z\s]+$/', $site_name)) {
        $error = "Site name must contain letters only.";
    } else {
        $district = h(trim($_POST['district']));
        $tehsil = h(trim($_POST['tehsil']));
        $gram = h(trim($_POST['gram']));
        $area = filter_var($_POST['area'], FILTER_VALIDATE_FLOAT);
        $available_area = filter_var($_POST['available_area'], FILTER_VALIDATE_FLOAT);
        $area_edit_type = $_POST['area_edit_type'] ?? '';
        $area_edit_new = filter_var($_POST['area_edit_new'], FILTER_VALIDATE_FLOAT);

        if ($area_edit_type == 'add_area') {
            $area += $area_edit_new;
            $available_area += $area_edit_new;
        } else if ($area_edit_type == 'subs_area') {
            $area -= $area_edit_new;
            $available_area -= $area_edit_new;
        }

        $sql = "UPDATE site_master SET site_name = :site_name, district = :district, tehsil = :tehsil, gram = :gram, area = :area, available_area = :available_area WHERE site_id = :site_id";
        $params = [
            'site_name' => $site_name,
            'district' => $district,
            'tehsil' => $tehsil,
            'gram' => $gram,
            'area' => $area,
            'available_area' => $available_area,
            'site_id' => $site_id
        ];
        if ($db->execute($sql, $params)) {
            echo "<script>alert('Record Updated Successfully'); window.location.href='update_site.php';</script>";
            exit();
        } else {
            $error = "Error while updating record.";
        }
    }
}

// Get site data if ID is provided
$site_data = null;
if (isset($_GET['id'])) {
    $id = json_decode(base64_decode($_GET['id']));
    $site_data = $db->fetch("SELECT * FROM site_master WHERE site_id = :id", ['id' => $id]);
}


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Site Master</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">

    <style>
        .form-floating>.fa {
            position: absolute;
            left: 20px;
            top: 22px;
            color: #aaa;
            pointer-events: none;
        }

        .form-floating input,
        .form-floating select {
            padding-left: 2.5rem;
        }
    </style>

</head>

<body>

    <?php include("../includes/templates/header.php"); ?>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-4 text-center">Update Site</h3>
                        <form method="post" action="" class="needs-validation" novalidate>
                            <?php echo getCsrfField(); ?>
                            <input type="hidden" name="site_id" value="<?php echo h($site_data['site_id'] ?? ''); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="text" class="form-control" id="site_name" name="site_name" placeholder="Site Name" required value="<?php echo h($site_data['site_name'] ?? ''); ?>">
                                        <label for="site_name"><i class="fa fa-home"></i> Site Name</label>
                                        <div class="invalid-feedback">Please enter a valid site name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="text" class="form-control" id="district" name="district" placeholder="District" required value="<?php echo h($site_data['district'] ?? ''); ?>">
                                        <label for="district"><i class="fa fa-map"></i> District</label>
                                        <div class="invalid-feedback">Please enter district.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="text" class="form-control" id="tehsil" name="tehsil" placeholder="Tehsil" required value="<?php echo h($site_data['tehsil'] ?? ''); ?>">
                                        <label for="tehsil"><i class="fa fa-map-pin"></i> Tehsil</label>
                                        <div class="invalid-feedback">Please enter tehsil.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="text" class="form-control" id="gram" name="gram" placeholder="Gram" required value="<?php echo h($site_data['gram'] ?? ''); ?>">
                                        <label for="gram"><i class="fa fa-location-dot"></i> Gram</label>
                                        <div class="invalid-feedback">Please enter gram.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="number" step="0.01" class="form-control" id="area" name="area" placeholder="Area" required value="<?php echo h($site_data['area'] ?? ''); ?>">
                                        <label for="area"><i class="fa fa-chart-area"></i> Total Area</label>
                                        <div class="invalid-feedback">Please enter area.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="number" step="0.01" class="form-control" id="available_area" name="available_area" placeholder="Available Area" required value="<?php echo h($site_data['available_area'] ?? ''); ?>">
                                        <label for="available_area"><i class="fa fa-chart-bar"></i> Available Area</label>
                                        <div class="invalid-feedback">Please enter available area.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <select class="form-select" id="area_edit_type" name="area_edit_type">
                                            <option value="" selected>No Change</option>
                                            <option value="add_area">Add Area</option>
                                            <option value="subs_area">Subtract Area</option>
                                        </select>
                                        <label for="area_edit_type"><i class="fa fa-edit"></i> Adjust Area</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="number" step="0.01" class="form-control" id="area_edit_new" name="area_edit_new" placeholder="Adjustment Value" value="0">
                                        <label for="area_edit_new"><i class="fa fa-plus-minus"></i> Adjustment Value</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" name="update_site" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-save"></i> Update Site</button>
                            </div>
                        </form>
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