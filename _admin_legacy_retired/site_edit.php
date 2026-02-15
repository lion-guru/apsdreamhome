<?php

// Include database connection

include("config.php");

session_start();


// Initialize variables

$error = "";

$msg = "";



// CSRF token generation

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

}



    if (isset($_POST['update_site'])) {
		
		
		$site_id = htmlspecialchars(trim($_POST['site_id']));

        $site_name = htmlspecialchars(trim($_POST['site_name']));
		if (!preg_match('/^[A-Za-z\s]+$/', $site_name)) {

			$error = "Site name must contain letters only.";

			return;

		}

        $district = htmlspecialchars(trim($_POST['district']));

        $tehsil = htmlspecialchars(trim($_POST['tehsil']));

        $gram = htmlspecialchars(trim($_POST['gram']));
		$area = htmlspecialchars(trim($_POST['area']));
        $area = filter_var(trim($area), FILTER_VALIDATE_FLOAT);
		
		$available_area = htmlspecialchars(trim($_POST['available_area']));
        $available_area = filter_var(trim($available_area), FILTER_VALIDATE_FLOAT);
		
		$area_edit_type = htmlspecialchars(trim($_POST['area_edit_type']));
		$area_edit_new = htmlspecialchars(trim($_POST['area_edit_new']));
        $area_edit_new = filter_var(trim($area_edit_new), FILTER_VALIDATE_FLOAT);
		
		if($area_edit_type == 'add_area')
		{
			$area = $area + $area_edit_new;
			$available_area = $available_area + $area_edit_new;
		}
		else if($area_edit_type == 'subs_area')
		{
			$area = $area - $area_edit_new;
			$available_area = $available_area - $area_edit_new;
		}
		
		$sql = "UPDATE site_master SET site_name='$site_name', district='$district', tehsil='$tehsil', gram='$gram', area='$area', available_area='$available_area' WHERE site_id=$site_id";
		$result = mysqli_query($con,$sql);
		//$stmt= $con->prepare($sql);
		//echo "here stmt";
		//exit();
		//$stmt->execute(['$site_name', '$district', '$tehsil', '$gram', $area, $id]);

        

        if ($result) 
		{
			
			echo "<script>
			alert('Record Updated Successfully');
			window.location.href='update_site.php';
			</script>";

           
			

			//header("Location:update_site.php");

        } 
		else {

           echo '<script>
				alert("Error while updating record");
				
			</script>';

        }

        $stmt->close();

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
        .form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
        .form-floating input, .form-floating select { padding-left: 2.5rem; }
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
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="text" class="form-control" id="site_name" name="site_name" placeholder="Site Name" required value="<?php echo isset($_POST['site_name']) ? htmlspecialchars($_POST['site_name']) : ''; ?>">
                                        <label for="site_name"><i class="fa fa-home"></i> Site Name</label>
                                        <div class="invalid-feedback">Please enter a valid site name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="text" class="form-control" id="district" name="district" placeholder="District" required value="<?php echo isset($_POST['district']) ? htmlspecialchars($_POST['district']) : ''; ?>">
                                        <label for="district"><i class="fa fa-map"></i> District</label>
                                        <div class="invalid-feedback">Please enter district.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="text" class="form-control" id="tehsil" name="tehsil" placeholder="Tehsil" required value="<?php echo isset($_POST['tehsil']) ? htmlspecialchars($_POST['tehsil']) : ''; ?>">
                                        <label for="tehsil"><i class="fa fa-map-pin"></i> Tehsil</label>
                                        <div class="invalid-feedback">Please enter tehsil.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="text" class="form-control" id="gram" name="gram" placeholder="Gram" required value="<?php echo isset($_POST['gram']) ? htmlspecialchars($_POST['gram']) : ''; ?>">
                                        <label for="gram"><i class="fa fa-location-dot"></i> Gram</label>
                                        <div class="invalid-feedback">Please enter gram.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="number" class="form-control" id="area" name="area" placeholder="Area" required value="<?php echo isset($_POST['area']) ? htmlspecialchars($_POST['area']) : ''; ?>">
                                        <label for="area"><i class="fa fa-chart-area"></i> Area</label>
                                        <div class="invalid-feedback">Please enter area.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="number" class="form-control" id="available_area" name="available_area" placeholder="Available Area" required value="<?php echo isset($_POST['available_area']) ? htmlspecialchars($_POST['available_area']) : ''; ?>">
                                        <label for="available_area"><i class="fa fa-chart-bar"></i> Available Area</label>
                                        <div class="invalid-feedback">Please enter available area.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <select class="form-select" id="area_edit_type" name="area_edit_type" required>
                                            <option value="" disabled selected>Select Edit Type</option>
                                            <option value="add_area">Add Area</option>
                                            <option value="subs_area">Subtract Area</option>
                                        </select>
                                        <label for="area_edit_type"><i class="fa fa-edit"></i> Edit Type</label>
                                        <div class="invalid-feedback">Please select edit type.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="number" class="form-control" id="area_edit_new" name="area_edit_new" placeholder="Edit Value" required value="<?php echo isset($_POST['area_edit_new']) ? htmlspecialchars($_POST['area_edit_new']) : ''; ?>">
                                        <label for="area_edit_new"><i class="fa fa-plus-minus"></i> Edit Value</label>
                                        <div class="invalid-feedback">Please enter edit value.</div>
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
