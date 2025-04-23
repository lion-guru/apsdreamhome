<?php

// Include database connection

include("config.php");

session_start();

require_permission('manage_plots');

// Initialize variables

$error = "";

$msg = "";



// CSRF token generation

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

}



    if (isset($_POST['add_plot'])) 
	{
		$plot_dimension = htmlspecialchars(trim($_POST['plot_dimension']));
		$plot_facing = htmlspecialchars(trim($_POST['plot_facing']));
		$plot_price = htmlspecialchars(trim($_POST['plot_price']));
		$plot_status = htmlspecialchars(trim($_POST['plot_status']));		

        $site_name = htmlspecialchars(trim($_POST['site_name']));
        $gata_a = isset($_POST['gata_a']) ? (int)$_POST['gata_a'] : 0;
		$gata_b = isset($_POST['gata_b']) ? (int)$_POST['gata_b'] : 0;
		$gata_c = isset($_POST['gata_c']) ? (int)$_POST['gata_c'] : 0;
		$gata_d = isset($_POST['gata_d']) ? (int)$_POST['gata_d'] : 0;
		$area_a = htmlspecialchars(trim($_POST['area_a']));
		$area_a = filter_var(trim($area_a), FILTER_VALIDATE_FLOAT);
		
		$area_b = htmlspecialchars(trim($_POST['area_b']));
		$area_b = filter_var(trim($area_b), FILTER_VALIDATE_FLOAT);
		if($area_b == '')
		{
			$area_b = 0;
		}
		$area_c = htmlspecialchars(trim($_POST['area_c']));
		$area_c = filter_var(trim($area_c), FILTER_VALIDATE_FLOAT);
		if($area_c == '')
		{
			$area_c = 0;
		}
		
		$area_d = htmlspecialchars(trim($_POST['area_d']));
		$area_d = filter_var(trim($area_d), FILTER_VALIDATE_FLOAT);
		if($area_d == '')
		{
			$area_d = 0;
		}
		
		$plot_no = htmlspecialchars(trim($_POST['plot_no']));
		$area_plot = htmlspecialchars(trim($_POST['area']));
		
        $area_plot = filter_var(trim($area_plot), FILTER_VALIDATE_FLOAT);
		
						$gata_qurey_a = "select * from gata_master where gata_id = $gata_a";
						$gata_result_a=mysqli_query($con,$gata_qurey_a);
						while($row_gata_a=mysqli_fetch_array($gata_result_a))
						{
							$available_area_a = $row_gata_a['available_area'];
						}
						$gata_qurey_b = "select * from gata_master where gata_id = $gata_b";
						$gata_result_b=mysqli_query($con,$gata_qurey_b);
						while($row_gata_b=mysqli_fetch_array($gata_result_b))
						{
							$available_area_b = $row_gata_b['available_area'];
						}
						$gata_qurey_c = "select * from gata_master where gata_id = $gata_c";
						$gata_result_c=mysqli_query($con,$gata_qurey_c);
						while($row_gata_c=mysqli_fetch_array($gata_result_c))
						{
							$available_area_c = $row_gata_c['available_area'];
						}
						$gata_qurey_d = "select * from gata_master where gata_id = $gata_d";
						$gata_result_d=mysqli_query($con,$gata_qurey_d);
						while($row_gata_d=mysqli_fetch_array($gata_result_d))
						{
							$available_area_d = $row_gata_d['available_area'];
						}
						$available_area = $available_area_a + $available_area_b + $available_area_c + $available_area_d;
						$total_gata_area_mentioned = $area_a + $area_b + $area_c + $area_d;
						
						$sql_chk_plot_no = "select * from plot_master where plot_no = '$plot_no'";
						$result_chk_plot_no = mysqli_query($con,$sql_chk_plot_no);
						$row_chk_plot_no = mysqli_num_rows($result_chk_plot_no);
	if($row_chk_plot_no < 0)
	{		
	if($area_plot == $total_gata_area_mentioned)
	{
		if($area_plot <= $available_area)	
		{
			
			$stmt = $con->prepare("INSERT INTO plot_master(site_id, gata_a, gata_b, gata_c, gata_d, area_gata_a, area_gata_b,area_gata_c, area_gata_d, plot_no, area, plot_dimension, plot_facing,plot_price, plot_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("iiiiissssssssis", $site_name, $gata_a, $gata_b, $gata_c, $gata_d, $area_a, $area_b, $area_c, $area_d, $plot_no, $area_plot,$plot_dimension,$plot_facing,$plot_price,$plot_status);   
			 
			if ($stmt->execute())
			{
				$new_gata_available_area_a = $available_area_a - $area_a;
				$update_gata_area_a = "update gata_master set available_area = $new_gata_available_area_a where gata_id = $gata_a ";
				$res_update_gata_area_a =  mysqli_query($con, $update_gata_area_a);
				
				$new_gata_available_area_b = $available_area_b - $area_b;
				$update_gata_area_b = "update gata_master set available_area = $new_gata_available_area_b where gata_id = $gata_b ";
				$res_update_gata_area_b =  mysqli_query($con, $update_gata_area_b);
				
				$new_gata_available_area_c = $available_area_c - $area_c;
				$update_gata_area_c = "update gata_master set available_area = $new_gata_available_area_c where gata_id = $gata_c ";
				$res_update_gata_area_a =  mysqli_query($con, $update_gata_area_a);
				
				$new_gata_available_area_d = $available_area_d - $area_d;
				$update_gata_area_d = "update gata_master set available_area = $new_gata_available_area_d where gata_id = $gata_d ";
				$res_update_gata_area_d =  mysqli_query($con, $update_gata_area_d);
				
			/*	echo "plot ".$area_plot."</br>";
				echo " gata_a".$area_a."</br>";
				echo "gata_b ".$area_b."</br>";
				echo "gata_c ".$area_c."</br>";
				echo "gata_d ".$area_d."</br>";
				echo "total gata area ".$available_area."</br>";
				
				echo "avail_gata_a ".$available_area_a."</br>";	
				echo "new_gata_a ".$new_gata_available_area_a."</br>";
				
				echo "avail_gata_b ".$available_area_b."</br>";
				echo "new_gata_b ".$new_gata_available_area_b."</br>";
				
				echo "avail_gata_c ".$available_area_c."</br>";
				echo "new_gata_ c".$new_gata_available_area_c."</br>";
				
				echo "avail_gata_d".$available_area_d."</br>";
				echo "new_gata_d ".$new_gata_available_area_d."</br>";
				
				
				exit(); */
				
					
						
				
				if($res_update_gata_area_a)
				{	
					if($res_update_gata_area_b)
					{
						if($res_update_gata_area_c)
						{
							if($res_update_gata_area_d)
							{
					
								   echo '<script>
										alert("Record updated Successfully");
										
									</script>';
							}
						}
					}
				}
				else
				{
					echo '<script>
						alert("plot details added Successfully");
						
					</script>';
				}

			} 
			else
			{

			   echo '<script>
					alert("Error while adding record");
					
				</script>';

			}

				$stmt->close();
				require_once __DIR__ . '/../includes/functions/notification_util.php';
				addNotification($con, 'Plot', 'Plot added or updated.', $_SESSION['auser'] ?? null);
		}
		
		else 
		{
			 echo '<script>
					alert("Plot Area is larger than total available Gata Area");
					
				</script>';
		}
	}
	else
	{
		echo '<script>
					alert("Plot area mentioned is not equals to Total area mentioned in Gata");
					
				</script>';
	}

	}
	else
	{
		echo '<script>
					alert("Plot Name Already Exists");
					
				</script>';
	}

	}
	


?>

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

</head>
<style>
	body{color: #000;overflow-x: hidden;height: 100%;background-repeat: no-repeat;background-size: 100% 100%}
	.card{padding: 30px 40px;margin-top: 10px;margin-bottom: 30px; background-color: #A7BEAE;border: none !important;box-shadow: 0 6px 12px 0 rgba(0,0,0,0.2)}.blue-text{color: #00BCD4}.form-control-label{margin-bottom: 0}input, textarea, button{padding: 8px 15px;border-radius: 5px !important;margin: 5px 0px;box-sizing: border-box;border: 1px solid #ccc;font-size: 18px !important;font-weight: 300}input:focus, textarea:focus{-moz-box-shadow: none !important;-webkit-box-shadow: none !important;box-shadow: none !important;border: 1px solid #00BCD4;outline-width: 0;font-weight: 400}.btn-block{text-transform: uppercase;font-size: 15px !important;font-weight: 400;height: 43px;cursor: pointer}.btn-block:hover{color: #fff !important}button:focus{-moz-box-shadow: none !important;-webkit-box-shadow: none !important;box-shadow: none !important;outline-width: 0}
</style>



<body>

    <?php include("../includes/templates/header.php"); ?>
	<script>
	function check(){
			var site_name = document.getElementById("site_name").value;
			
			$.ajax({
				
				method: "POST",
				url: "fetch_gata.php",
				data: {
					id: site_name
				},
				datatype: "html",
				success: function(data) {
					$("#gata_a").html(data);
					$("#gata_b").html(data);
					$("#gata_c").html(data);
					$("#gata_d").html(data);
					$("#plot_no").html('<option value="">Select Plot</option');

				}
			});
    
	
/*	$("#gata_no").on('change', function() {
        var gata_id = $(this).val();
        $.ajax({
            method: "POST",
            url: "fetch_gata.php",
            data: {
                sid: gata_no
            },
            datatype: "html",
            success: function(data) {
                $("#plot_no").html(data);

            }

        });
    }); */
}

</script>

    <div class="container-fluid px-1 py-5 mx-auto">
		<div class="row d-flex justify-content-center">
			<div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">

        <!-- Form to Add New Kissan Land Details -->
		<div class="card" style="background-color:LightGray">
		<h3 style="color:green; font-weight:bold; text-decoration:underline">Plot Master</h3>
		
        <form method="post" id="myForm" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
			
             <div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label for="site_name" class="form-control-label px-3">Site Name<span class="text-danger"> *</span></label> 
				   <select class="form-control" id="site_name" name="site_name" onchange="check()" required>
						<option value="">Select</option>
				   <?php 
						$site_qurey = "select * from site_master";
						$site_result=mysqli_query($con,$site_qurey);
						while($row_site=mysqli_fetch_array($site_result))
						{
							$site_id = $row_site['site_id'];
							$site_name = $row_site['site_name'];
				   ?>
						<option value="<?php echo $site_id; ?>"><?php echo $site_name; ?></option>
						<?php } ?>	
				    				
				  </select>
			   </div>
			   </div>
			    
			   <div class="row justify-content-between text-left">
				
				<div class="form-group col-sm-4 flex-column d-flex"> 
				   <label class="form-control-label px-3">Plot No<span class="text-danger"> *</span></label> 
				   <input type="text" id="plot_no" name="plot_no" style="text-transform:uppercase" required> 
			   </div>		
			   <div class="form-group col-sm-4 flex-column d-flex"> 
				   <label class="form-control-label px-3">Plot Area (in sqft)<span class="text-danger"> *</span></label> 
				   <input type="text" id="area" name="area" required> 
			   </div>
			   <div class="form-group col-sm-4 flex-column d-flex"> 
				   <label class="form-control-label px-3">Plot Dimension<span class="text-danger"> *</span></label> 
				   <select class="form-control" id="plot_dimension" name="plot_dimension" required>
						<option value="">Select</option>
						<option value="20x50">20x50</option>
						<option value="20x60">20x60</option>
						<option value="30x50">30x50</option>
						<option value="40x60">40x60</option>
						
				    				
				  </select> 
			   </div>	
			  
			</div>
			
			<div class="row justify-content-between text-left">
			
				<div class="form-group col-sm-4 flex-column d-flex"> 
				   <label class="form-control-label px-3">Plot Facing<span class="text-danger"> *</span></label> 
				   <select class="form-control" id="plot_facing" name="plot_facing" required>
						<option value="">Select</option>
						<option value="East">East</option>
						<option value="West">West</option>
						<option value="North">North</option>
						<option value="South">South</option>
						<option value="North-East">North-East</option>
						<option value="North-West">North-West</option>
				    				
				  </select> 
			   </div>
				
				<div class="form-group col-sm-4 flex-column d-flex"> 
				   <label class="form-control-label px-3">Plot Price<span class="text-danger"> *</span></label> 
				   <input type="number" id="plot_price" name="plot_price" required> 
			   </div>		
			   <div class="form-group col-sm-4 flex-column d-flex"> 
				   <label class="form-control-label px-3">Plot Status<span class="text-danger"> *</span></label> 
				   <select class="form-control" id="plot_status" name="plot_status" required>
						<option value="">Select</option>
						<option value="Available">Available</option>
						<option value="Booked">Booked</option>
						<option value="Hold">Hold</option>
						<option value="Sold">Sold</option>
				    				
				  </select> 
			   </div>
			  
			</div>
		
			   <div class="row justify-content-between text-left">
				<div class="form-group col-sm-12 flex-column d-flex"> 
				   <p><Center>x--------Kindly Fill Gata-wise Bifreucation of Plot Area below--------x</Center></p>
			   </div>
				</div>
			   <div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label for="site_name" class="form-control-label px-3">Gata A<span class="text-danger"> *</span></label> 
				   <select class="form-control" id="gata_a" name="gata_a" required>
						<option value="">Select</option>
				    				
				  </select>
			   </div>
			    <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Total Area from Gata A ( in sqft)<span class="text-danger"> *</span></label> 
				   <input type="text" id="area_a" name="area_a" required> 
			   </div>
			</div>
			 <div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label for="site_name" class="form-control-label px-3">Gata B</label> 
				   <select class="form-control" id="gata_b" name="gata_b" >
						<option value="">Select</option>
				    				
				  </select>
			   </div>
			    <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Total Area from Gata B ( in sqft)</label> 
				   <input type="text" id="area_b" name="area_b" > 
			   </div>
			</div>
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label for="site_name" class="form-control-label px-3">Gata C</label> 
				   <select class="form-control" id="gata_c" name="gata_c" >
						<option value="">Select</option>
				    				
				  </select>
			   </div>
			    <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Total Area from Gata C ( in sqft)</label> 
				   <input type="text" id="area_c" name="area_c" > 
			   </div>
			</div>
			<div class="row justify-content-between text-left">
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label for="site_name" class="form-control-label px-3">Gata D</label> 
				   <select class="form-control" id="gata_d" name="gata_d" >
						<option value="">Select</option>
				    				
				  </select>
			   </div>
			    <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Total Area from Gata D ( in sqft)</label> 
				   <input type="text" id="area_d" name="area_d" > 
			   </div>
			</div>
			
      
			<div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit" name="add_plot" class="btn-block btn-primary">Add Plot</button> </div>
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
