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

    <title>Plot Master</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">

    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">

	<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">

    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">

    <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
	 <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">

    
    

</head>
<style>
	body{color: #000;overflow-x: hidden;height: 100%;background-repeat: no-repeat;background-size: 100% 100%}
	.card{padding: 30px 40px;margin-top: 10px;margin-bottom: 30px; background-color: #A7BEAE;border: none !important;box-shadow: 0 6px 12px 0 rgba(0,0,0,0.2)}.blue-text{color: #00BCD4}.form-control-label{margin-bottom: 0}input, textarea, button{padding: 8px 15px;border-radius: 5px !important;margin: 5px 0px;box-sizing: border-box;border: 1px solid #ccc;font-size: 18px !important;font-weight: 300}input:focus, textarea:focus{-moz-box-shadow: none !important;-webkit-box-shadow: none !important;box-shadow: none !important;border: 1px solid #00BCD4;outline-width: 0;font-weight: 400}.btn-block{text-transform: uppercase;font-size: 15px !important;font-weight: 400;height: 43px;cursor: pointer}.btn-block:hover{color: #fff !important}button:focus{-moz-box-shadow: none !important;-webkit-box-shadow: none !important;box-shadow: none !important;outline-width: 0}
</style>
<body>

<style>



</style>
    <?php include("../includes/templates/header.php"); ?>

			<div class="container text-center col-8">
			<div class="table-responsive" style="background-color: #badcca;"> 
							<table id="myTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead style="background-color: #badcca;"> 
							<tr>
							<th>Edit</th>
							<th>Delete</th>
							<th>Sno.</th>
							<th>Plot No</th>
							<th>Total Area</th>
							
							<th>Site Name</th>
							
							<th>Gata A</th>
							<th>Area Gata A</th>
							<th>Gata B</th>
							<th>Area Gata B</th>
							<th>Gata C</th>
							<th>Area Gata C</th>
							<th>Gata D</th>
							<th>Area Gata D</th>
							
							
							
							
							
							
							
						  </tr>
						</thead>
						<tbody>
						<?php
							$plot_qurey = "select * from plot_master order by site_id";
							$plot_result=mysqli_query($con,$plot_qurey);
							$i=1;
							while($row_plot=mysqli_fetch_array($plot_result))
							{
								$site_id = isset($row_plot['site_id']) ? (int)$row_plot['site_id'] : 0;
								$plot_id = $row_plot['plot_id'];
								
								$site_qurey = "select * from site_master where site_id = $site_id";
								$site_result=mysqli_query($con,$site_qurey);
								while($row_site=mysqli_fetch_array($site_result))
								{
									$site_name = $row_site['site_name'];
								}
								
								
								$gata_a = isset($row_plot['gata_a']) ? (int)$row_plot['gata_a'] : 0;
								$gata_qurey = "select * from gata_master where gata_id = $gata_a";
								$gata_result=mysqli_query($con,$gata_qurey);
								while($row_gata=mysqli_fetch_array($gata_result))
							{
								$gata_a = $row_gata['gata_no'];
							}
								$gata_b = isset($row_plot['gata_b']) ? (int)$row_plot['gata_b'] : 0;
								$gata_qureyb = "select * from gata_master where gata_id = $gata_b";
								$gata_resultb=mysqli_query($con,$gata_qureyb);
								while($row_gatab=mysqli_fetch_array($gata_resultb))
							{
								$gata_b = $row_gatab['gata_no'];
							}
								$gata_c = isset($row_plot['gata_c']) ? (int)$row_plot['gata_c'] : 0;
								$gata_qureyc = "select * from gata_master where gata_id = $gata_c";
								$gata_resultc=mysqli_query($con,$gata_qureyc);
								while($row_gatac=mysqli_fetch_array($gata_resultc))
							{
								$gata_c = $row_gatac['gata_no'];
							}
								$gata_d = isset($row_plot['gata_d']) ? (int)$row_plot['gata_d'] : 0;
								$gata_qureyd = "select * from gata_master where gata_id = $gata_d";
								$gata_resultd=mysqli_query($con,$gata_qureyd);
								while($row_gatad=mysqli_fetch_array($gata_resultd))
							{
								$gata_d = $row_gatad['gata_no'];
							}
								$area_a = $row_plot['area_gata_a'];
								$area_b = $row_plot['area_gata_b'];
								$area_c = $row_plot['area_gata_c'];
								$area_d = $row_plot['area_gata_d'];
								$plot_no = $row_plot['plot_no'];
								$area = $row_plot['area'];
								$arg = base64_encode( json_encode($plot_id) );
								
							?>
							
						  <tr>
							<td><a href="plot_edit.php?id=<?php echo $arg; ?>"><button class="btn btn-info">Edit</button></td>
							<td><a href="delete.php?type=plot&id=<?php echo $arg; ?>"><button class="btn btn-info">Delete</button></a></td>
							<td><?php echo $i; ?></td>
							<td><?php echo $plot_no; ?></td>
							<td><?php echo $area; ?></td>
							<td><?php echo $site_name; ?></td>
							<td><?php echo $gata_a; ?></td>
							<td><?php echo $area_a; ?></td>
							<td><?php echo $gata_b; ?></td>
							<td><?php echo $area_b; ?></td>
							<td><?php echo $gata_c; ?></td>
							<td><?php echo $area_c; ?></td>
							<td><?php echo $gata_d; ?></td>										
							<td><?php echo $area_d; ?></td>
							
							
						  </tr>
						  
							<?php $i++;} ?>
						</tbody>
				  </table>
			
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
