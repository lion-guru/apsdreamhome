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

    <title>Gata Master</title>

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
<link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">   

<link rel="stylesheet" href="http://cdn.datatables.net/1.10.2/css/jquery.dataTables.min.css"></style>

    <?php include("../includes/templates/header.php"); ?>
			<div class="container text-center col-8">
			<div class="table-responsive" style="background-color: #badcca;"> 
			
			
				  <table id="myTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead style="background-color: #badcca;"> 
						  <tr>
							<th>Sno.</th>
							<th>Site Name</th>
							
							<th>Gata No</th>
							<th>Total Area</th>
							<th>Available Area</th>
							<th>Edit</th>
							
						  </tr>
						</thead>
						<tbody>
						<?php
							$gata_qurey = "select * from gata_master order by site_id";
							$gata_result=mysqli_query($con,$gata_qurey);
							$i=1;
							while($row_gata=mysqli_fetch_array($gata_result))
							{
								$site_id = $row_gata['site_id'];
								
								$site_qurey = "select * from site_master where site_id = $site_id";
								$site_result=mysqli_query($con,$site_qurey);
								while($row_site=mysqli_fetch_array($site_result))
							{
								$site_name = $row_site['site_name'];
							}
								
								
								$gata_id = $row_gata['gata_id'];
								$gata_no = $row_gata['gata_no'];
								$area = $row_gata['area'];
								$available_area = $row_gata['available_area'];
								$arg = base64_encode( json_encode($gata_id) );
							?>
							
						  <tr>
							<td><?php echo $i; ?></td>
							<td><?php echo $site_name; ?></td>
							<td><?php echo $gata_no; ?></td>
							<td><?php echo $area; ?></td>
							<td><?php echo $available_area; ?></td>
							<td><a href="gata_edit.php?id=<?php echo $arg; ?>"><button class="btn btn-info">Edit</button></td>
							
						  </tr>
						  
							<?php $i++; } ?>
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
