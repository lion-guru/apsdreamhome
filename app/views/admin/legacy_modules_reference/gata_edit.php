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

if (isset($_POST['update_gata'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $gata_id = filter_var($_POST['gata_id'], FILTER_VALIDATE_INT);
    $site_id_edit = filter_var($_POST['site_id_edit'], FILTER_VALIDATE_INT);
    $gata_no = h(trim($_POST['gata_no']));
    $area = filter_var($_POST['area'], FILTER_VALIDATE_FLOAT);
    $available_area = filter_var($_POST['available_area'], FILTER_VALIDATE_FLOAT);
    $area_edit_type = $_POST['area_edit_type'] ?? '';
    $area_edit_new = filter_var($_POST['area_edit_new'], FILTER_VALIDATE_FLOAT);

    if ($gata_id && $site_id_edit) {
        $db->beginTransaction();
        try {
            if ($area_edit_type == 'add_area') {
                $area += $area_edit_new;
                $available_area += $area_edit_new;
                
                // Update site available area (Subtracting from site available area because more area is now in a gata?)
                $db->execute("UPDATE site_master SET available_area = available_area - :area WHERE site_id = :site_id", [
                    'area' => $area_edit_new,
                    'site_id' => $site_id_edit
                ]);
            } else if ($area_edit_type == 'subs_area') {
                $area -= $area_edit_new;
                $available_area -= $area_edit_new;
                
                // If we remove area from a gata, it goes back to the site's pool.
                $db->execute("UPDATE site_master SET available_area = available_area + :area WHERE site_id = :site_id", [
                    'area' => $area_edit_new,
                    'site_id' => $site_id_edit
                ]);
            }

            $db->execute("UPDATE gata_master SET gata_no = :gata_no, area = :area, available_area = :available_area WHERE gata_id = :gata_id", [
                'gata_no' => $gata_no,
                'area' => $area,
                'available_area' => $available_area,
                'gata_id' => $gata_id
            ]);

            $db->commit();
            echo "<script>alert('Record Updated Successfully'); window.location.href='update_gata.php';</script>";
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error while updating record: " . $e->getMessage();
        }
    }
}

// Get gata data if ID is provided
$gata_data = null;
if (isset($_GET['id'])) {
    $id = json_decode(base64_decode($_GET['id']));
    $sql = "
        SELECT g.*, s.site_name 
        FROM gata_master g 
        LEFT JOIN site_master s ON g.site_id = s.site_id 
        WHERE g.gata_id = ?
    ";
    $gata_data = $db->fetch($sql, [$id]);
}


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Gata Master</title>

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

    <div class="container-fluid px-1 py-5 mx-auto">
		<div class="row d-flex justify-content-center">
			<div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">

        <!-- Form to Add New Kissan Land Details -->
		<div class="card shadow-sm border-0">
		    <h3 class="mb-4 text-center text-primary">Edit Gata Master</h3>
            <form method="post" id="myForm" enctype="multipart/form-data">
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="gata_id" value="<?php echo h($gata_data['gata_id'] ?? ''); ?>">
                <input type="hidden" name="available_area" value="<?php echo h($gata_data['available_area'] ?? ''); ?>">
                <input type="hidden" name="site_id_edit" value="<?php echo h($gata_data['site_id'] ?? ''); ?>">
                
                 <div class="row g-3 text-start">
                   <div class="col-md-6 mb-3"> 
                       <label class="form-label">Site Name</label> 
                       <input type="text" class="form-control bg-light" value="<?php echo h($gata_data['site_name'] ?? 'Unknown'); ?>" readonly> 
                   </div>
                   <div class="col-md-6 mb-3"> 
                        <label class="form-label">Gata No <span class="text-danger">*</span></label> 
                       <input type="text" class="form-control" name="gata_no" value="<?php echo h($gata_data['gata_no'] ?? ''); ?>" required> 
                   </div>
                </div>
                
                <div class="row g-3 text-start">
                   <div class="col-md-6 mb-3"> 
                       <label class="form-label">Total Gata Area (sqft) <span class="text-danger">*</span></label> 
                       <input type="number" step="0.01" class="form-control bg-light" name="area" value="<?php echo h($gata_data['area'] ?? ''); ?>" readonly required> 
                   </div>
                   <div class="col-md-6 mb-3"> 
                       <label class="form-label">Adjust Area</label> 
                       <select class="form-select" id="area_edit_type" name="area_edit_type">
                            <option value="" selected>No Change</option>
                            <option value="add_area">Add Area</option>
                            <option value="subs_area">Subtract Area</option>				
                      </select> 
                   </div>
                </div>

                <div class="row g-3 text-start">
                   <div class="col-md-6 mb-3"> 
                       <label class="form-label">Adjustment Value (sqft)</label> 
                       <input type="number" step="0.01" class="form-control" name="area_edit_new" value="0"> 
                   </div>
                </div>
          
                <div class="mt-4">
                    <button type="submit" name="update_gata" class="btn btn-primary btn-lg w-100 rounded-pill">Update Gata</button>
                </div>
            </form>
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
