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

if (isset($_POST['add_site'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $site_name = h(trim($_POST['site_name']));
    if (!preg_match('/^[A-Za-z\s]+$/', $site_name)) {
        $error = "Site name must contain letters only.";
    } else {
        $district = h(trim($_POST['district']));
        $tehsil = h(trim($_POST['tehsil']));
        $gram = h(trim($_POST['gram']));
        $area = filter_var(trim($_POST['area']), FILTER_VALIDATE_FLOAT);

        $sql = "INSERT INTO site_master (site_name, district, tehsil, gram, area, available_area) VALUES (?, ?, ?, ?, ?, ?)";
        if ($db->execute($sql, [$site_name, $district, $tehsil, $gram, $area, $area])) {
            $msg = "Record added successfully";
        } else {
            $error = "Error while adding record.";
        }
    }
}


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Site Master</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">

    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">

	<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">

    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">

    <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">

</head>
<style>
	body{color: #000;overflow-x: hidden;height: 100%;background-repeat: no-repeat;background-size: 100% 100%}
	.card{padding: 30px 40px;margin-top: 10px;margin-bottom: 30px; background-color: #A7BEAE;border: none !important;box-shadow: 0 6px 12px 0 rgba(0,0,0,0.2)}.blue-text{color: #00BCD4}.form-control-label{margin-bottom: 0}input, textarea, button{padding: 8px 15px;border-radius: 5px !important;margin: 5px 0px;box-sizing: border-box;border: 1px solid #ccc;font-size: 18px !important;font-weight: 300}input:focus, textarea:focus{-moz-box-shadow: none !important;-webkit-box-shadow: none !important;box-shadow: none !important;border: 1px solid #00BCD4;outline-width: 0;font-weight: 400}.btn-block{text-transform: uppercase;font-size: 15px !important;font-weight: 400;height: 43px;cursor: pointer}.btn-block:hover{color: #fff !important}button:focus{-moz-box-shadow: none !important;-webkit-box-shadow: none !important;box-shadow: none !important;outline-width: 0}
</style>
<body>

    <?php include("../includes/templates/header.php"); ?>
    
    <div class="container mt-4">
        <div class="row">
            <!-- Add Site Form -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Add New Site</h4>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo h($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($msg): ?>
                            <div class="alert alert-success"><?php echo h($msg); ?></div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <?php echo getCsrfField(); ?>
                            <div class="mb-3">
                                <label class="form-label">Site Name</label>
                                <input type="text" name="site_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">District</label>
                                <input type="text" name="district" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tehsil</label>
                                <input type="text" name="tehsil" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gram</label>
                                <input type="text" name="gram" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Area</label>
                                <input type="number" step="0.01" name="area" class="form-control" required>
                            </div>
                            <button type="submit" name="add_site" class="btn btn-primary w-100">Add Site</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Site List -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Site List</h4>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Site Name</th>
                                        <th>Location</th>
                                        <th>Area (Total/Avail)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $site_records = $db->fetchAll("SELECT * FROM site_master ORDER BY site_id DESC");
                                    foreach ($site_records as $row_site) {
                                        $arg = base64_encode(json_encode($row_site['site_id']));
                                    ?>
                                        <tr>
                                            <td><?php echo (int)$row_site['site_id']; ?></td>
                                            <td><strong><?php echo h($row_site['site_name']); ?></strong></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo h($row_site['gram'] . ', ' . $row_site['tehsil'] . ', ' . $row_site['district']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php echo h($row_site['area']); ?> / 
                                                <span class="text-success fw-bold"><?php echo h($row_site['available_area']); ?></span>
                                            </td>
                                            <td>
                                                <a href="site_edit.php?id=<?php echo h($arg); ?>" class="btn btn-sm btn-info text-white">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>

	<script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>

    <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>

    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>

</body>

</html>

