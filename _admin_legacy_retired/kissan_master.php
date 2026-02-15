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



    if (isset($_POST['add_kissan'])) 
	{
						
		$kissan_name = htmlspecialchars(trim($_POST['k_name']));
		$kissan_adhaar = htmlspecialchars(trim($_POST['k_adhaar']));
		$area_kissan = htmlspecialchars(trim($_POST['area']));
        $area_kissan = filter_var(trim($area_kissan), FILTER_VALIDATE_FLOAT);
        $site_name = htmlspecialchars(trim($_POST['site_name']));
        $gata_a = htmlspecialchars(trim($_POST['gata_a']));
		$gata_b = htmlspecialchars(trim($_POST['gata_b']));
		$gata_c = htmlspecialchars(trim($_POST['gata_c']));
		$gata_d = htmlspecialchars(trim($_POST['gata_d']));
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
		$total_area = $area_a + $area_b + $area_c + $area_d;
		if($area_kissan == $total_area)
		{
			$stmt = $con->prepare("INSERT INTO kissan_master(site_id, gata_a, gata_b, gata_c, gata_d, area_gata_a, area_gata_b,area_gata_c, area_gata_d, k_name, k_adhaar, area) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("iiiiisssssis", $site_name, $gata_a, $gata_b, $gata_c, $gata_d, $area_a, $area_b, $area_c, $area_d, $kissan_name, $kissan_adhaar, $area_kissan);   
			 
			if ($stmt->execute())
			{		
			
					echo '<script>
						alert("Kissan details added Successfully");
						
					</script>';
				

			} 
			else
			{

			   echo '<script>
					alert("Error while adding record");
					
				</script>';

			}

				$stmt->close();
		}
		else
		{
			 echo '<script>
					alert("Total area is not equals to area mentioned in Gata No");
					
				</script>';
		}
	}
		
	
	


?>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kissan Master</title>

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

    <?php include("header.php"); ?>
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
		<div class="card">
		<h3 style="color:green; font-weight:bold; text-decoration:underline">Kissan Master</h3>
		
        <form method="post" id="myForm" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
			
			
			<div class="row justify-content-between text-left">
				
				<div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Kissan Name<span class="text-danger"> *</span></label> 
				   <input type="text" id="k_name" name="k_name" required> 
			   </div>		
			   <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Adhaar No<span class="text-danger"> *</span></label> 
				   <input type="text" id="k_adhaar" name="k_adhaar" required> 
			   </div>
			  
			</div>
			
             <div class="row justify-content-between text-left">
			 <div class="form-group col-sm-6 flex-column d-flex"> 
				   <label class="form-control-label px-3">Total Area (in sqft)<span class="text-danger"> *</span></label> 
				   <input type="text" id="area" name="area" required> 
			   </div>
			 
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
                        <div class="form-group col-sm-6"> <button type="submit" name="add_kissan" class="btn-block btn-primary">Add Kissan</button> </div>
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

