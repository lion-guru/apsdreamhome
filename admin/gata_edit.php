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



    if (isset($_POST['update_gata'])) {
		
		
		
		$gata_id = htmlspecialchars(trim($_POST['gata_id']));
		$site_id_edit = htmlspecialchars(trim($_POST['site_id_edit']));
		$site_id_edit = isset($site_id_edit) ? (int)$site_id_edit : 0;
		$gata_id = isset($gata_id) ? (int)$gata_id : 0;
		$gata_no = htmlspecialchars(trim($_POST['gata_no']));
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
			/* ------ update site avialable area ---------------*/
			$site_area = "select available_area from site_master where site_id = $site_id_edit";
			$result_site_area = mysqli_query($con,$site_area);
			while($row_site_area_edit=mysqli_fetch_array($result_site_area))
								{
									$available_area_site = $row_site_area_edit['available_area'];
								}
			$new_avialable_site_area = 	$available_area_site - $area_edit_new;
			$sql_new_site_available_area = "update site_master set available_area = $new_avialable_site_area where site_id = $site_id_edit";
			$exe_update_site_avial_area = mysqli_query($con,$sql_new_site_available_area);
			//echo $sql_new_site_available_area;
			//exit();
			
		}
		else if($area_edit_type == 'subs_area')
		{
			$area = $area - $area_edit_new;
			$available_area = $available_area - $area_edit_new;
			
			/* ------ update site avialable area ---------------*/
			$site_area = "select available_area from site_master where site_id = $site_id_edit";
			$result_site_area = mysqli_query($con,$site_area);
			while($row_site_area_edit=mysqli_fetch_array($result_site_area))
								{
									$available_area_site = $row_site_area_edit['available_area'];
								}
			$new_avialable_site_area = 	$available_area_site + $area_edit_new;
			$sql_new_site_available_area = "update site_master set available_area = $new_avialable_site_area where site_id = $site_id_edit";
			$exe_update_site_avial_area = mysqli_query($con,$sql_new_site_available_area);
			//echo $sql_new_site_available_area;
			//exit();
		}
		
		$sql = "UPDATE gata_master SET gata_no='$gata_no', area='$area', available_area='$available_area' WHERE gata_id=$gata_id";
		$result = mysqli_query($con,$sql);
		
        

        if ($result) 
		{
			
			echo "<script>
			alert('Record Updated Successfully');
			window.location.href='update_gata.php';
			</script>";

           
			

			//header("Location:update_site.php");

        } 
		else {

           echo "<script>
				alert('Error while updating record');
				window.location.href='update_gata.php';
			</script>";

        }

        $stmt->close();

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
		<div class="card">
		<h3 style="color:green; font-weight:bold; text-decoration:underline">Edit Gata Master</h3>
		<?php 
			$myData = json_decode( base64_decode( $_GET['id'] ) );
			$myData = isset($myData) ? (int)$myData : 0;
			$site_qurey = "select * from gata_master where gata_id = $myData";
							$site_result=mysqli_query($con,$site_qurey);
							while($row_site=mysqli_fetch_array($site_result))
							{
								$site_id = $row_site['site_id'];
								$site_qurey_n = "select * from site_master where site_id = $site_id";
								$site_result_n=mysqli_query($con,$site_qurey_n);
								while($row_site_n=mysqli_fetch_array($site_result_n))
								{
									$site_name = $row_site_n['site_name'];
								}
								$gata_id = $row_site['gata_id'];
								$gata_no = $row_site['gata_no'];
								$area = $row_site['area'];
								$available_area = $row_site['available_area'];
							}
		?>
        <form method="post" id="myForm" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
			<input type="hidden" name="gata_id" value="<?php echo $gata_id; ?>">
			<input type="hidden" name="available_area" value="<?php echo $available_area; ?>">
			<input type="hidden" name="site_id_edit" value="<?php echo $site_id; ?>">
			
             <div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Site Name<span class="text-danger"> *</span></label> 
				   <input type="text" id="site_name" name="site_name" value="<?php echo $site_name; ?>" readonly> 
			   </div>
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				    <label class="form-control-label px-3">Gata No<span class="text-danger"> *</span></label> 
				   <input type="text" id="gata_no" name="gata_no" value="<?php echo $gata_no; ?>" required> 
			   </div>
			</div>
			
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Total Gata Area (in sqft)<span class="text-danger"> *</span></label> 
				   <input type="text" id="area" name="area" value="<?php echo $area; ?>" readonly required> 
			   </div>
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label for="district" class="form-control-label px-3">Add/Substract Original Area</label> 
				   <select class="form-control" id="area_edit_type" name="area_edit_type">
						<option value="">Select Option</option>
						<option value="add_area">Add area</option>
						<option value="subs_area">Substract Area</option>				
				  </select> 
			   </div>
			  
			</div>
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Land Area (in sqft) to be Added/Substracted</label> 
				   <input type="text" id="area_edit_new" name="area_edit_new"> 
			   </div>
			</div>
      
			<div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit" name="update_gata" class="btn-block btn-primary">Update Gata</button> </div>
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
