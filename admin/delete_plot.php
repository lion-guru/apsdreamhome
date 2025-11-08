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



    if (isset($_POST['delete_plot'])) {
		
		
		
		$plot_id = htmlspecialchars(trim($_POST['plot_id']));
		
		$gata_a = htmlspecialchars(trim($_POST['gata_del_a']));
		$gata_b = htmlspecialchars(trim($_POST['gata_del_b']));
		$gata_c = htmlspecialchars(trim($_POST['gata_del_c']));
		$gata_d = htmlspecialchars(trim($_POST['gata_del_d']));
		$area_gata_a = htmlspecialchars(trim($_POST['area_gata_a']));
		$area_gata_b = htmlspecialchars(trim($_POST['area_gata_b']));
		$area_gata_c = htmlspecialchars(trim($_POST['area_gata_c']));
		$area_gata_d = htmlspecialchars(trim($_POST['area_gata_d']));

		
		$sql = "delete from plot_master WHERE plot_id=$plot_id";
		
		$result = mysqli_query($con,$sql);
		
        

       if ($result) 
		{
		
			$gata_area_a = "select * from gata_master where gata_id = $gata_a";
			
			$result_gata_area_a = mysqli_query($con,$gata_area_a);
			while($row_gata_area_a=mysqli_fetch_array($result_gata_area_a))
								{
									$available_gata_area_a = $row_gata_area_a['available_area'];
									
								}
			$gata_area_b = "select available_area from gata_master where gata_id = $gata_b";
			$result_gata_area_b = mysqli_query($con,$gata_area_b);
			while($row_gata_area_b=mysqli_fetch_array($result_gata_area_b))
								{
									$available_gata_area_b = $row_gata_area_b['available_area'];
								}
			$gata_area_c = "select available_area from gata_master where gata_id = $gata_c";
			$result_gata_area_c = mysqli_query($con,$gata_area_c);
			while($row_gata_area_a=mysqli_fetch_array($result_gata_area_c))
								{
									$available_gata_area_c = $row_gata_area_c['available_area'];
								}
								
			$gata_area_d = "select available_area from gata_master where gata_id = $gata_d";
			$result_gata_area_d = mysqli_query($con,$gata_area_d);
			while($row_gata_area_d=mysqli_fetch_array($result_gata_area_d))
								{
									$available_gata_area_d = $row_gata_area_d['available_area'];
								}
			
			$available_area_gata_a = $available_gata_area_a + $area_gata_a;
			$available_area_gata_b = $available_gata_area_b + $area_gata_b;
			$available_area_gata_c = $available_gata_area_c + $area_gata_c;
			$available_area_gata_d = $available_gata_area_d + $area_gata_d;
			
			$sql_update_gata_a = "update gata_master set available_area = $available_area_gata_a where gata_id = $gata_a";
			$sql_update_gata_b = "update gata_master set available_area = $available_area_gata_b where gata_id = $gata_b";
			$sql_update_gata_c = "update gata_master set available_area = $available_area_gata_c where gata_id = $gata_c";
			$sql_update_gata_d = "update gata_master set available_area = $available_area_gata_d where gata_id = $gata_d";
			
			$result_update_gata_a = mysqli_query($con,$sql_update_gata_a);
			$result_update_gata_b = mysqli_query($con,$sql_update_gata_b);
			$result_update_gata_c = mysqli_query($con,$sql_update_gata_c);
			$result_update_gata_d = mysqli_query($con,$sql_update_gata_d);
			
			
			
			echo "<script>
			alert('Record Updated Successfully');
			window.location.href='update_plot.php';
			</script>";

           
			

			//header("Location:update_site.php");

        } 
		else {

           echo "<script>
				alert('Error while updating record');
				window.location.href='update_plot.php';
			</script>";

        }

        

    }


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Plot Master</title>

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
		<h3 style="color:green; font-weight:bold; text-decoration:underline">Edit Plot Master</h3>
		<?php 
			$myData = json_decode( base64_decode( htmlspecialchars($_GET['id']) ) );
			$site_qurey = "select * from plot_master where plot_id = $myData";
							$site_result=mysqli_query($con,$site_qurey);
							while($row_site=mysqli_fetch_array($site_result))
							{
								$site_id = $row_site['site_id'];
								$gata_a = $row_site['gata_a'];
								$gata_b = $row_site['gata_b'];
								$gata_c = $row_site['gata_c'];
								$gata_d = $row_site['gata_d'];
								$area_gata_a = $row_site['area_gata_a'];
								$area_gata_b = $row_site['area_gata_b'];
								$area_gata_c = $row_site['area_gata_c'];
								$area_gata_d = $row_site['area_gata_d'];
								$plot_id = $row_site['plot_id'];
								$plot_no = $row_site['plot_no'];
								
								$site_qurey_n = "select * from site_master where site_id = $site_id";
								$site_result_n=mysqli_query($con,$site_qurey_n);
								while($row_site_n=mysqli_fetch_array($site_result_n))
								{
									$site_name = $row_site_n['site_name'];
								}
								$site_qurey_a = "select * from gata_master where gata_id = $gata_a";
								$site_result_a=mysqli_query($con,$site_qurey_a);
								while($row_site_a=mysqli_fetch_array($site_result_a))
								{
									$gata_no_a = $row_site_a['gata_no'];
								}
								$site_qurey_b = "select * from gata_master where gata_id = $gata_b";
								$site_result_b=mysqli_query($con,$site_qurey_b);
								while($row_site_b=mysqli_fetch_array($site_result_b))
								{
									$gata_no_b = $row_site_b['gata_no'];
								}
								$site_qurey_c = "select * from gata_master where gata_id = $gata_c";
								$site_result_c=mysqli_query($con,$site_qurey_c);
								while($row_site_c=mysqli_fetch_array($site_result_c))
								{
									$gata_no_c = $row_site_c['gata_no'];
								}
								$site_qurey_d = "select * from gata_master where gata_id = $gata_d";
								$site_result_d=mysqli_query($con,$site_qurey_d);
								while($row_site_d=mysqli_fetch_array($site_result_d))
								{
									$gata_no_d = $row_site_d['gata_no'];
								}
								
								
								$area = $row_site['area'];
								$available_area = $row_site['available_area'];
							}
		?>
        <form method="post" id="myForm" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
			<input type="hidden" name="gata_id" value="<?php echo $gata_id; ?>">
			<input type="hidden" name="plot_id" value="<?php echo $plot_id; ?>">
			<input type="hidden" name="available_area" value="<?php echo $available_area; ?>">
			<input type="hidden" name="gata_del_a" value="<?php echo $gata_a; ?>">
			
			<input type="hidden" name="gata_del_b" value="<?php echo $gata_b; ?>">
			
			<input type="hidden" name="gata_del_c" value="<?php echo $gata_c; ?>">
			
			<input type="hidden" name="gata_del_d" value="<?php echo $gata_d; ?>">
			
			
             <div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Site Name<span class="text-danger"> *</span></label> 
				   <input type="text" id="site_name" name="site_name" value="<?php echo $site_name; ?>" readonly> 
			   </div>
			 
			</div>
			<div class="row justify-content-between text-left">
			
				 <div class="form-group col-sm-6 flex-column d-flex"> 
				    <label class="form-control-label px-3">Plot No<span class="text-danger"> *</span></label> 
				   <input type="text" id="plot_no" name="plot_no" value="<?php echo $plot_no; ?>" readonly> 
			   </div>
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Plot Area<span class="text-danger"> *</span></label> 
				   <input type="text" id="area" name="area" value="<?php echo $area; ?>" readonly required> 
			   </div>
			  
			  
			</div>
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Gata A<span class="text-danger"> *</span></label> 
				   <input type="text" id="gata_a" name="gata_a" value="<?php echo $gata_no_a; ?>" readonly> 
			   </div>
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				    <label class="form-control-label px-3">Area Gata A<span class="text-danger"> *</span></label> 
				   <input type="text" id="area_gata_a" name="area_gata_a" value="<?php echo $area_gata_a; ?>" readonly> 
			   </div>
			</div>
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Gata B<span class="text-danger"> *</span></label> 
				   <input type="text" id="gata_b" name="gata_b" value="<?php echo $gata_no_b; ?>" readonly> 
			   </div>
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				    <label class="form-control-label px-3">Area Gata B<span class="text-danger"> *</span></label> 
				   <input type="text" id="area_gata_b" name="area_gata_b" value="<?php echo $area_gata_b; ?>" readonly> 
			   </div>
			</div>
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Gata C<span class="text-danger"> *</span></label> 
				   <input type="text" id="gata_c" name="gata_c" value="<?php echo $gata_no_c; ?>" readonly> 
			   </div>
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				    <label class="form-control-label px-3">Area Gata C<span class="text-danger"> *</span></label> 
				   <input type="text" id="area_gata_c" name="area_gata_c" value="<?php echo $area_gata_c; ?>" readonly> 
			   </div>
			</div>
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Gata D<span class="text-danger"> *</span></label> 
				   <input type="text" id="gata_d" name="gata_d" value="<?php echo $gata_no_d; ?>" readonly> 
			   </div>
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				    <label class="form-control-label px-3">Area Gata D<span class="text-danger"> *</span></label> 
				   <input type="text" id="area_gata_d" name="area_gata_d" value="<?php echo $area_gata_d; ?>" readonly> 
			   </div>
			</div>
			 <div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit" name="delete_plot" class="btn-block btn-primary">Click to Delete Plot</button> </div>
            </div>
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
