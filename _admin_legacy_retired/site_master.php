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



    if (isset($_POST['add_site'])) {

        $site_name = htmlspecialchars(trim($_POST['site_name']));
		if (!preg_match('/^[A-Za-z\s]+$/', $site_name)) {

			$error = "Site name must contain letters only.";

			return;

		}

        $district = htmlspecialchars(trim($_POST['district']));

        $tehsil = htmlspecialchars(trim($_POST['tehsil']));

        $gram = htmlspecialchars(trim($_POST['gram']));
		$area = htmlspecialchars(trim($_POST['area']));

        

        // Validate and sanitize numeric inputs

        $area = filter_var(trim($area), FILTER_VALIDATE_FLOAT);

        $stmt = $con->prepare("INSERT INTO site_master (site_name, district, tehsil, gram, area) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $site_name, $district, $tehsil, $gram, $area);   

        if ($stmt->execute()) {

           echo '<script>
				alert("Record added Successfully");
				
			</script>';

        } else {

           echo '<script>
				alert("Error while adding record");
				
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

    <div class="container-fluid px-1 py-5 mx-auto">
		<div class="row d-flex justify-content-center">
			<div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">

        <!-- Form to Add New Kissan Land Details -->
		<div class="card">
		<h3 style="color:green; font-weight:bold; text-decoration:underline">Site Master</h3>
		
        <form method="post" id="myForm" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
			
             <div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Site Name<span class="text-danger"> *</span></label> 
				   <input type="text" id="site_name" name="site_name" required> 
			   </div>
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label for="district" class="form-control-label px-3">District<span class="text-danger"> *</span></label> 
				   <select class="form-control" id="district" name="district" required>
						<option value="">Select</option>
						<option value="Gorakhpur">Gorakhpur</option>				
				  </select> 
			   </div>
			</div>
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Tehsil<span class="text-danger"> *</span></label> 
				   <input type="text" id="tehsil" name="tehsil" required> 
			   </div>
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Gram<span class="text-danger"> *</span></label> 
				   <input type="text" id="gram" name="gram" required> 
			   </div>
			</div>
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Total Land Area (in sqft)<span class="text-danger"> *</span></label> 
				   <input type="text" id="area" name="area" required> 
			   </div>
			  
			</div>
      
			<div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit" name="add_site" class="btn-block btn-primary">Add Site</button> </div>
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
