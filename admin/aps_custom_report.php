<?php
require("config.php");

?>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="author" content="CodeHim">

 <!-- <link rel="stylesheet" href="./css/style.css">-->
    
      
      <!-- Bootstrap 5 CSS -->
	   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"crossorigin="anonymous">
	     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js">
    </script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<!-- Data Table CSS -->
<link rel='stylesheet' href='https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css'>
<!-- Font Awesome CSS -->
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css'>
    <!-- jquery cdn -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src='https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js'></script>
<script src='https://cdn.datatables.net/responsive/2.1.0/js/dataTables.responsive.min.js'></script>
<script src='https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js'></script>
      <!-- Script JS -->
      <script src="./js/script.js"></script>

    <title>Custom Reports</title>
</head>
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
					$("#plot_no").html('<option value="">Select Plot</option>');
					

				}
			});
			$.ajax({
            method: "POST",
            url: "fetch_farmer.php",
            data: {
                site_id: site_name
            },
            datatype: "html",
            success: function(data) {

				$("#farmer").html(data);

            }

        });
		$.ajax({
            method: "POST",
            url: "fetch_plot.php",
            data: {
                site_id: site_name
            },
            datatype: "html",
            success: function(data) {

				$("#plot_no").html(data);

            }

        });
	
	
	$("#gata_a").on('change', function() {
        var gata_id = $(this).val();
		//alert(gata_id);
        $.ajax({
            method: "POST",
            url: "fetch_gata.php",
            data: {
                sid: gata_id
            },
            datatype: "html",
            success: function(data) {
                $("#plot_no").html(data);
				

            }

        });
		$.ajax({
            method: "POST",
            url: "fetch_farmer.php",
            data: {
                sid: gata_id
            },
            datatype: "html",
            success: function(data) {

				$("#farmer").html(data);

            }

        });
		
		
    });
}
	


</script>
<!doctype html>


<body style="background-color:#3e687e">
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<div class="container">
<div class="row">
        <div class="col-lg-12">
			
        </div>
		<div  class="col-lg-12">
			
		</div>
	</div>
<div class="card-body" style="background-color: #a1b0b0; padding: 10px; color:white">
<form action="#" method="post">

	<div class="row">
	
		<div class="form-group col-md-3">
		<label for="country"> Site Name</label>
		<select class="form-select" id="site_name" name="site_name" onchange="check()">
			<option value=""> Select Site</option>
			<?php
			$query1 = "select * from site_master";
			// $query = mysqli_query($con, $qr);
			$result = $con->query($query1);
			if ($result->num_rows > 0) {
				while ($row = mysqli_fetch_assoc($result)) {

			?>
					<option value="<?php echo $row['site_id']; ?>"><?php echo $row['site_name']; ?></option>
			<?php
				}
			}

			?>

		</select>
		<input type="hidden" id="select_country" value=""/>
		</div>
		
		<div class="form-group col-md-3">
			<label for="site_name" class="form-control-label px-3">Gata No</label> 
				   <select class="form-control" id="gata_a" name="gata_a" >
						<option value="">Select Gata</option>
				    				
				  </select>
		</div>
		<div class="form-group col-md-3">
				<label for="site_name" class="form-control-label px-3">Plot</label> 
				   <select class="form-control" id="plot_no" name="plot_no" >
						
				    				
				  </select>
			</div>
			<div class="form-group col-md-3">
				<label for="order">Select Farmer</label>
				<select class="form-select" id="farmer" name="farmer" >
					<option value="">Select Farmer</option>
					
					
					
				</select>
			</div>
	</div>
	
		<div class="row">
			
			
		
		</div>
		<br/>
		<br/>
		<Center><input class="btn btn-success" type="submit" name="sub"/></Center>
	</form>
	</div>
	
	<style>
	.table-wrapper {
    width: 100%;
    /* max-width: 500px; */
    overflow-x: auto;
  }	
</style> 
	<?php
if(isset($_POST["sub"]))
{
	
	 $site_name = $_POST['site_name'];
	 $gata_a = $_POST['gata_a'];
	 $plot_no = $_POST['plot_no'];
	 $farmer = $_POST['farmer'];
	 $order = $_POST['order'];
	 $limit = $_POST['limit'];
	 $cate = '';
	
	
	 
if($site_name == '' && $gata_a == '' && $plot_no == '' && $farmer == '')
	 {
	?>
	<script>
	alert("Kindly Select atleast one Parameter");
	</script>
	<?php
	 }

	 if($site_name != '')
	 {
			  if($gata_a == '' && $plot_no == '' && $farmer == '')
			 {
				 $sql = "SELECT * from site_master where site_id = $site_name";

					$sql_chk = mysqli_query($con,$sql);
					$row_check_site = mysqli_num_rows($sql_chk);
					if($row_check_site >0)
					{
						
						while($row_fetch_site = mysqli_fetch_array($sql_chk))
						{
								
					?>
	 
	
        
     <main class="cd__main" style="background-color: Tomato;color: white">
	 <div class="table-wrapper">
         <!-- Start DEMO HTML (Use the following code into your project)-->
         <table id="example" class="table table-striped" >
					
					<thead>
				
					<tr>
					  <th>Site Name</th>
					  <th>Site District</th>

					  <th>Gata No</th>
					<!--<th>Total Gata Area</th>
					  <th>Gata Area left for Plot Allocation</th>-->
					  <th>Kissan Under the Gata</th>
					  <th>Plot NO</th>
					  <th>Plot Area in Gata</th>
					  <th>Plot Dimension</th>
					  <th>Plot Facing</th>
					  <th>Plot Rate/Sq.ft</th>
					  <th>Plot Status</th>
					  
					</tr>
					</thead>
					<tbody >
										
								
								<?php 
									$sql_gata = "select * from gata_master where site_id = $site_name";
									$result_gata = mysqli_query($con,$sql_gata);
									
										
									
									while($row_fetch_gata = mysqli_fetch_array($result_gata))
									{ 
										$gata_id = $row_fetch_gata['gata_id'];
										$sql_kissan = "select * from kissan_master where gata_a = $gata_id or gata_b = $gata_id or gata_c = $gata_id or gata_d = $gata_id ";
										$result_kissan = mysqli_query($con,$sql_kissan);
										
										
										?>
										<tr>
										<td scope="row"><?php echo $row_fetch_site['site_name'];?></td>
										<td><?php echo $row_fetch_site['district'];?></td>
										
										<td><?php echo $row_fetch_gata['gata_no'];?></td>
										<!--<td><?php //echo $row_fetch_gata['area']."Sqft";?></td>
										<td><?php //echo $row_fetch_gata['available_area']." Sqft";?></td>-->
										<td><?php while($row_fetch_kissan = mysqli_fetch_array($result_kissan))
												{
													$kissan_name = $row_fetch_kissan['k_name'];
													$area_kissan = $row_fetch_kissan['area_gata_a'];
													if($area_kissan == 0)
													{
														$area_kissan = $row_fetch_kissan['area_gata_b'];
														if($area_kissan == 0)
														{
															$area_kissan = $row_fetch_kissan['area_gata_c'];
															if($area_kissan == 0)
															{
																$area_kissan = $row_fetch_kissan['area_gata_d'];
															}
															
														}
													}
													
													echo $kissan_name."[".$area_kissan." Sqft] </br>";
												}
											
											?>
										</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										
										
									</tr>
																			<?php
										
										
										$sql_plot = "select * from plot_master where gata_a = $gata_id or gata_b = $gata_id or gata_c = $gata_id or gata_d = $gata_id ";
										$result_plot = mysqli_query($con,$sql_plot);
										
										
										$row_check_plot = mysqli_num_rows($result_plot);
										if($row_check_plot >0)
										{
											while($row_fetch_plot= mysqli_fetch_array($result_plot))
											{ 
												$area_plot = $row_fetch_plot['area_gata_a'];
												if($area_plot == 0)
												{
													$area_plot = $row_fetch_plot['area_gata_b'];
													if($area_plot == 0)
													{
														$area_plot = $row_fetch_plot['area_gata_c'];
														if($area_plot == 0)
														{
															$area_plot = $row_fetch_plot['area_gata_d'];
														}
														
													}
												}
																					
									?>
									
									<tr>
										<td ><?php echo $row_fetch_site['site_name'];?></td>
										<td><?php echo $row_fetch_site['district'];?></td>
										
										<td><?php echo $row_fetch_gata['gata_no'];?></td>
										<!--<td><?php //echo $row_fetch_gata['area']."Sqft";?></td>
										<td><?php //echo $row_fetch_gata['available_area']." Sqft";?></td>-->
										<td></td>
										<td><?php echo $row_fetch_plot['plot_no'];?></td>
										<td><?php echo $area_plot." Sqft";?></td>
										<td><?php echo $row_fetch_plot['plot_dimension'];?></td>
										<td><?php echo $row_fetch_plot['plot_facing'];?></td>
										<td><?php echo $row_fetch_plot['plot_price'];?></td>
										
										<?php
											$plot_status = $row_fetch_plot['plot_status'];
											if($plot_status=="Available")
											{?>
												<td style="background-color: Green;color:white"><?php echo $plot_status;?></td>
											<?php
											}
											else{
										?><td><?php echo $plot_status;?></td>
										
											<?php }?>
									</tr>
			<?php 
											}} 
									?>
									

										 <?php
									}
								}
								?>
								</tbody>
					</table>
				</div>
		</main>
			
			
			
		<?php 		
				
				
			 }
			

			else if ($gata_a != '')
			 {
				
				
				 $sql = "SELECT * from gata_master where site_id = $site_name and gata_id = $gata_a";

					$sql_chk = mysqli_query($con,$sql);
					$row_check = mysqli_num_rows($sql_chk);
					if($row_check >0)
					{
						
						
								
					?>
	
			<main class="cd__main" style="background-color: Tomato;color: white">
			<div class="table-wrapper">
         <!-- Start DEMO HTML (Use the following code into your project)-->
			<table id="example" class="table table-striped" style="width:100%">
					
					<thead>
					
					<tr>
					  <th scope="col">Site Name</th>
					  <th scope="col">Site District</th>
					  
					  <th scope="col">Gata No</th>
					  <!--<th scope="col">Tota Gata Area</th>
					  <th scope="col">Gata Area left for Plot Allocation</th>-->
					  <th scope="col">Kissan Under the Gata</th>
					  <th scope="col">Plot NO</th>
					  <th scope="col">Plot Area in Gata</th>
					  <th>Plot Dimension</th>
					  <th>Plot Facing</th>
					  <th>Plot Rate/Sq.ft</th>
					  <th>Plot Status</th>
	  
					</tr>
					</thead>
					<tbody>
										
								
								<?php 
						
									while($row_fetch_gata = mysqli_fetch_array($sql_chk))
									{ 
										$site_id = $row_fetch_gata['site_id'];
										$sql_site = "select * from site_master where site_id = $site_id";
										$result_site = mysqli_query($con,$sql_site);
										while($row_fetch_site_n = mysqli_fetch_array($result_site))
										{
											$site_name = $row_fetch_site_n['site_name'];
											$site_district = $row_fetch_site_n['district'];
											$site_tehsil = $row_fetch_site_n['tehsil'];
											$site_gram = $row_fetch_site_n['gram'];
											$site_area = $row_fetch_site_n['area'];
											$site_avail_area = $row_fetch_site_n['available_area'];
										}
										$gata_id = $row_fetch_gata['gata_id'];
										$sql_kissan = "select * from kissan_master where gata_a = $gata_id or gata_b = $gata_id or gata_c = $gata_id or gata_d = $gata_id ";
										$result_kissan = mysqli_query($con,$sql_kissan);
										
										
										?>
										<tr style="background-color: #008080;color:white">
										<td><?php echo $site_name;?></td>
										<td><?php echo $site_district;?></td>
										
										<td><?php echo $row_fetch_gata['gata_no'];?></td>
										<!--<td><?php //echo $row_fetch_gata['area']." Sqft";?></td>
										<td><?php //echo $row_fetch_gata['available_area']." Sqft";?></td>-->
										<td><?php while($row_fetch_kissan = mysqli_fetch_array($result_kissan))
												{
													$kissan_name = $row_fetch_kissan['k_name'];
													$area_kissan = $row_fetch_kissan['area_gata_a'];
													if($area_kissan == 0)
													{
														$area_kissan = $row_fetch_kissan['area_gata_b'];
														if($area_kissan == 0)
														{
															$area_kissan = $row_fetch_kissan['area_gata_c'];
															if($area_kissan == 0)
															{
																$area_kissan = $row_fetch_kissan['area_gata_d'];
															}
															
														}
													}
													
													echo $kissan_name."[".$area_kissan." Sqft] </br>";
												}
											
											?>
										</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										
									</tr>
										<?php
										
										
										$sql_plot = "select * from plot_master where gata_a = $gata_id or gata_b = $gata_id or gata_c = $gata_id or gata_d = $gata_id ";
										$result_plot = mysqli_query($con,$sql_plot);
										
										
										$row_check_plot = mysqli_num_rows($result_plot);
										if($row_check_plot >0)
										{
											while($row_fetch_plot= mysqli_fetch_array($result_plot))
											{ 
												$area_plot = $row_fetch_plot['area_gata_a'];
												if($area_plot == 0)
												{
													$area_plot = $row_fetch_plot['area_gata_b'];
													if($area_plot == 0)
													{
														$area_plot = $row_fetch_plot['area_gata_c'];
														if($area_plot == 0)
														{
															$area_plot = $row_fetch_plot['area_gata_d'];
														}
														
													}
												}
																					
									?>
									
									<tr>
										<td><?php echo $site_name;?></td>
										<td><?php echo $site_district;?></td>
										
										<td><?php echo $row_fetch_gata['gata_no'];?></td>
										<!-- <td><?php //echo $row_fetch_gata['area']." Sqft";?></td>
										<td><?php// echo $row_fetch_gata['available_area']." Sqft";?></td> -->
										<td></td>
										<td><?php echo $row_fetch_plot['plot_no'];?></td>
										<td><?php echo $area_plot." Sqft";?></td>
										<td><?php echo $row_fetch_plot['plot_dimension'];?></td>
										<td><?php echo $row_fetch_plot['plot_facing'];?></td>
										<td><?php echo $row_fetch_plot['plot_price'];?></td>
										
										<?php
											$plot_status = $row_fetch_plot['plot_status'];
											if($plot_status=="Available")
											{?>
												<td style="background-color: Green;color:white"><?php echo $plot_status;?></td>
											<?php
											}
											else{
										?><td><?php echo $plot_status;?></td>
										
											<?php }?>
										
									</tr>
			<?php 
											}} 
									?>
										 <?php
									}
								}
								?>
								</tbody>
					</table>
				<div class="table-wrapper">
			</main>
			<?php
				
			}
								
				else if ($gata_a != '' && $plot_no != '')
			{
				
				 $sql = "SELECT * from plot_master where site_id = $site_name  and plot_id = $plot_no";
				 
				 $sql_chk = mysqli_query($con,$sql);
					$row_check = mysqli_num_rows($sql_chk);
					if($row_check >0)
					{
						
						
								
					?>
	<main class="cd__main" style="background-color: Tomato;color: white">
	<div class="table-wrapper">
         <!-- Start DEMO HTML (Use the following code into your project)-->
         <table id="example" class="table table-striped" style="width:100%">
					<thead>
				
					
					<tr>
					  <th scope="col">Site Name</th>
					  <th scope="col">Site District</th>
					  
					  <th scope="col">Plot No</th>
					  <th scope="col">Plot Area</th>
					   <th scope="col">Plot Dimension</th>
					  <th scope="col">Plot Facing</th>
					  <th scope="col">Plot Rate/Sq.ft</th>
					  <th scope="col">Plot Status</th>
					  <th scope="col">Gata A</th>
					  <th scope="col">Plot Area in Gata A</th>
					  <th scope="col">Gata B</th>
					  <th scope="col">Plot Area in Gata B</th>
					  <th scope="col">Gata C</th>
					  <th scope="col">Plot Area in Gata C</th>
					  <th scope="col">Gata D</th>
					  <th scope="col">Plot Area in Gata D</th>
					  
					  
					  
					</tr>
					</thead>
					<tbody>
										
								
								<?php 
						
									while($row_fetch_plot = mysqli_fetch_array($sql_chk))
									{ 
										$site_id = $row_fetch_plot['site_id'];
										$plot_no = $row_fetch_plot['plot_no'];
										$plot_area = $row_fetch_plot['area'];
										$plot_avail_area = $row_fetch_plot['available_area'];
										
										$sql_site = "select * from site_master where site_id = $site_id";
										$result_site = mysqli_query($con,$sql_site);
										while($row_fetch_site_n = mysqli_fetch_array($result_site))
										{
											$site_name = $row_fetch_site_n['site_name'];
											$site_district = $row_fetch_site_n['district'];
											$site_tehsil = $row_fetch_site_n['tehsil'];
											$site_gram = $row_fetch_site_n['gram'];
											$site_area = $row_fetch_site_n['area'];
											$site_avail_area = $row_fetch_site_n['available_area'];
										}
										$gata_a = $row_fetch_plot['gata_a'];
										$gata_b = $row_fetch_plot['gata_b'];
										$gata_c = $row_fetch_plot['gata_c'];
										$gata_d = $row_fetch_plot['gata_d'];
										
										$sql_gata = "select * from gata_master where gata_id = $gata_a";
										$result_gata = mysqli_query($con,$sql_gata);
										while($row_fetch_gata_a = mysqli_fetch_array($result_gata))
										{
											$gata_no_a = $row_fetch_gata_a['gata_no'];
											
										}
										
										$sql_gata_b = "select * from gata_master where gata_id = $gata_b";
										$result_gata_b = mysqli_query($con,$sql_gata_b);
										while($row_fetch_gata_b = mysqli_fetch_array($result_gata_b))
										{
											$gata_no_b = $row_fetch_gata_b['gata_no'];
																						
										}
										
										$sql_gata_c = "select * from gata_master where gata_id = $gata_c";
										$result_gata_c = mysqli_query($con,$sql_gata_c);
										while($row_fetch_gata_c = mysqli_fetch_array($result_gata_c))
										{
											$gata_no_c = $row_fetch_gata_c['gata_no'];
											
										}
										
										$sql_gata_d = "select * from gata_master where gata_id = $gata_d";
										$result_gata_d = mysqli_query($con,$sql_gata_d);
										while($row_fetch_gata_d = mysqli_fetch_array($result_gata_d))
										{
											$gata_no_d = $row_fetch_gata_d['gata_no'];	
											
										}
										
										
										
										
										?>
										<tr style="background-color: #008080;color:white">
										<td><?php echo $site_name;?></td>
										<td><?php echo $site_district;?></td>
										
										<td><?php echo $plot_no;?></td>
										<td><?php echo $plot_area." Sqft";?></td>
										<td><?php echo $row_fetch_plot['plot_dimension'];?></td>
										<td><?php echo $row_fetch_plot['plot_facing'];?></td>
										<td><?php echo $row_fetch_plot['plot_price'];?></td>
										
										<?php
											$plot_status = $row_fetch_plot['plot_status'];
											if($plot_status=="Available")
											{?>
												<td style="background-color: Green;color:white"><?php echo $plot_status;?></td>
											<?php
											}
											else{
										?><td><?php echo $plot_status;?></td>
										
											<?php }?>
										<td><?php echo $gata_no_a;?></td>
										<td><?php echo $row_fetch_plot['area_gata_a']." Sqft";?></td>
										<td><?php echo $gata_no_b;?></td>
										<td><?php echo $row_fetch_plot['area_gata_b']." Sqft";?></td>
										<td><?php echo $gata_no_c;?></td>
										<td><?php echo $row_fetch_plot['area_gata_c']." Sqft";?></td>
										<td><?php echo $gata_no_d;?></td>
										<td><?php echo $row_fetch_plot['area_gata_d']." Sqft";?></td>
										
										
										
									</tr>
										
							
										 <?php
									}
								}
								?>
								</tbody>
					</table>
				<div class="table-wrapper">
			</main>
			<?php
				
			}
							else if ($gata_a != '' && $plot_no != '')
			{
				
				 $sql = "SELECT * from plot_master where site_id = $site_name  and plot_id = $plot_no";
				 
				 $sql_chk = mysqli_query($con,$sql);
					$row_check = mysqli_num_rows($sql_chk);
					if($row_check >0)
					{
						
						
								
					?>
	<main class="cd__main" style="background-color: Tomato;color: white">
	<div class="table-wrapper">
         <!-- Start DEMO HTML (Use the following code into your project)-->
         <table id="example" class="table table-striped" style="width:100%">
					<thead>
				
					
					<tr>
					  <th scope="col">Site Name</th>
					  <th scope="col">Site District</th>
					  
					  <th scope="col">Plot No</th>
					  <th scope="col">Plot Area</th>
					   <th scope="col">Plot Dimension</th>
					  <th scope="col">Plot Facing</th>
					  <th scope="col">Plot Rate/Sq.ft</th>
					  <th scope="col">Plot Status</th>
					  <th scope="col">Gata A</th>
					  <th scope="col">Plot Area in Gata A</th>
					  <th scope="col">Gata B</th>
					  <th scope="col">Plot Area in Gata B</th>
					  <th scope="col">Gata C</th>
					  <th scope="col">Plot Area in Gata C</th>
					  <th scope="col">Gata D</th>
					  <th scope="col">Plot Area in Gata D</th>
					  
					  
					  
					</tr>
					</thead>
					<tbody>
										
								
								<?php 
						
									while($row_fetch_plot = mysqli_fetch_array($sql_chk))
									{ 
										$site_id = $row_fetch_plot['site_id'];
										$plot_no = $row_fetch_plot['plot_no'];
										$plot_area = $row_fetch_plot['area'];
										$plot_avail_area = $row_fetch_plot['available_area'];
										
										$sql_site = "select * from site_master where site_id = $site_id";
										$result_site = mysqli_query($con,$sql_site);
										while($row_fetch_site_n = mysqli_fetch_array($result_site))
										{
											$site_name = $row_fetch_site_n['site_name'];
											$site_district = $row_fetch_site_n['district'];
											$site_tehsil = $row_fetch_site_n['tehsil'];
											$site_gram = $row_fetch_site_n['gram'];
											$site_area = $row_fetch_site_n['area'];
											$site_avail_area = $row_fetch_site_n['available_area'];
										}
										$gata_a = $row_fetch_plot['gata_a'];
										$gata_b = $row_fetch_plot['gata_b'];
										$gata_c = $row_fetch_plot['gata_c'];
										$gata_d = $row_fetch_plot['gata_d'];
										
										$sql_gata = "select * from gata_master where gata_id = $gata_a";
										$result_gata = mysqli_query($con,$sql_gata);
										while($row_fetch_gata_a = mysqli_fetch_array($result_gata))
										{
											$gata_no_a = $row_fetch_gata_a['gata_no'];
											
										}
										
										$sql_gata_b = "select * from gata_master where gata_id = $gata_b";
										$result_gata_b = mysqli_query($con,$sql_gata_b);
										while($row_fetch_gata_b = mysqli_fetch_array($result_gata_b))
										{
											$gata_no_b = $row_fetch_gata_b['gata_no'];
																						
										}
										
										$sql_gata_c = "select * from gata_master where gata_id = $gata_c";
										$result_gata_c = mysqli_query($con,$sql_gata_c);
										while($row_fetch_gata_c = mysqli_fetch_array($result_gata_c))
										{
											$gata_no_c = $row_fetch_gata_c['gata_no'];
											
										}
										
										$sql_gata_d = "select * from gata_master where gata_id = $gata_d";
										$result_gata_d = mysqli_query($con,$sql_gata_d);
										while($row_fetch_gata_d = mysqli_fetch_array($result_gata_d))
										{
											$gata_no_d = $row_fetch_gata_d['gata_no'];	
											
										}
										
										
										
										
										?>
										<tr style="background-color: #008080;color:white">
										<td><?php echo $site_name;?></td>
										<td><?php echo $site_district;?></td>
										
										<td><?php echo $plot_no;?></td>
										<td><?php echo $plot_area." Sqft";?></td>
										<td><?php echo $row_fetch_plot['plot_dimension'];?></td>
										<td><?php echo $row_fetch_plot['plot_facing'];?></td>
										<td><?php echo $row_fetch_plot['plot_price'];?></td>
										
										<?php
											$plot_status = $row_fetch_plot['plot_status'];
											if($plot_status=="Available")
											{?>
												<td style="background-color: Green;color:white"><?php echo $plot_status;?></td>
											<?php
											}
											else{
										?><td><?php echo $plot_status;?></td>
										
											<?php }?>
										<td><?php echo $gata_no_a;?></td>
										<td><?php echo $row_fetch_plot['area_gata_a']." Sqft";?></td>
										<td><?php echo $gata_no_b;?></td>
										<td><?php echo $row_fetch_plot['area_gata_b']." Sqft";?></td>
										<td><?php echo $gata_no_c;?></td>
										<td><?php echo $row_fetch_plot['area_gata_c']." Sqft";?></td>
										<td><?php echo $gata_no_d;?></td>
										<td><?php echo $row_fetch_plot['area_gata_d']." Sqft";?></td>
										
										
										
									</tr>
										
							
										 <?php
									}
								}
								?>
								</tbody>
					</table>
				<div class="table-wrapper">
			</main>
			<?php
				
			}
							else if ($gata_a == '' && $plot_no != '')
			{
				
				 $sql = "SELECT * from plot_master where site_id = $site_name  and plot_id = $plot_no";
				 
				 $sql_chk = mysqli_query($con,$sql);
					$row_check = mysqli_num_rows($sql_chk);
					if($row_check >0)
					{
						
						
								
					?>
	<main class="cd__main" style="background-color: Tomato;color: white">
	<div class="table-wrapper">
         <!-- Start DEMO HTML (Use the following code into your project)-->
         <table id="example" class="table table-striped" style="width:100%">
					<thead>
				
					
					<tr>
					  <th scope="col">Site Name</th>
					  <th scope="col">Site District</th>
					  <th scope="col">Plot No</th>
					  <th scope="col">Plot Area</th>
					   <th scope="col">Plot Dimension</th>
					  <th scope="col">Plot Facing</th>
					  <th scope="col">Plot Rate/Sq.ft</th>
					  <th scope="col">Plot Status</th>
					  <th scope="col">Gata A</th>
					  <th scope="col">Plot Area in Gata A</th>
					  <th scope="col">Gata B</th>
					  <th scope="col">Plot Area in Gata B</th>
					  <th scope="col">Gata C</th>
					  <th scope="col">Plot Area in Gata C</th>
					  <th scope="col">Gata D</th>
					  <th scope="col">Plot Area in Gata D</th>
					  
					  
					  
					</tr>
					</thead>
					<tbody>
										
								
								<?php 
						
									while($row_fetch_plot = mysqli_fetch_array($sql_chk))
									{ 
										$site_id = $row_fetch_plot['site_id'];
										$plot_no = $row_fetch_plot['plot_no'];
										$plot_area = $row_fetch_plot['area'];
										$plot_avail_area = $row_fetch_plot['available_area'];
										
										$sql_site = "select * from site_master where site_id = $site_id";
										$result_site = mysqli_query($con,$sql_site);
										while($row_fetch_site_n = mysqli_fetch_array($result_site))
										{
											$site_name = $row_fetch_site_n['site_name'];
											$site_district = $row_fetch_site_n['district'];
											$site_tehsil = $row_fetch_site_n['tehsil'];
											$site_gram = $row_fetch_site_n['gram'];
											$site_area = $row_fetch_site_n['area'];
											$site_avail_area = $row_fetch_site_n['available_area'];
										}
										$gata_a = $row_fetch_plot['gata_a'];
										$gata_b = $row_fetch_plot['gata_b'];
										$gata_c = $row_fetch_plot['gata_c'];
										$gata_d = $row_fetch_plot['gata_d'];
										
										$sql_gata = "select * from gata_master where gata_id = $gata_a";
										$result_gata = mysqli_query($con,$sql_gata);
										while($row_fetch_gata_a = mysqli_fetch_array($result_gata))
										{
											$gata_no_a = $row_fetch_gata_a['gata_no'];
											
										}
										
										$sql_gata_b = "select * from gata_master where gata_id = $gata_b";
										$result_gata_b = mysqli_query($con,$sql_gata_b);
										while($row_fetch_gata_b = mysqli_fetch_array($result_gata_b))
										{
											$gata_no_b = $row_fetch_gata_b['gata_no'];
																						
										}
										
										$sql_gata_c = "select * from gata_master where gata_id = $gata_c";
										$result_gata_c = mysqli_query($con,$sql_gata_c);
										while($row_fetch_gata_c = mysqli_fetch_array($result_gata_c))
										{
											$gata_no_c = $row_fetch_gata_c['gata_no'];
											
										}
										
										$sql_gata_d = "select * from gata_master where gata_id = $gata_d";
										$result_gata_d = mysqli_query($con,$sql_gata_d);
										while($row_fetch_gata_d = mysqli_fetch_array($result_gata_d))
										{
											$gata_no_d = $row_fetch_gata_d['gata_no'];	
											
										}
										
										
										
										
										?>
										<tr style="background-color: #008080;color:white">
										<td><?php echo $site_name;?></td>
										<td><?php echo $site_district;?></td>
										
										<td><?php echo $plot_no;?></td>
										<td><?php echo $plot_area." Sqft";?></td>
										<td><?php echo $row_fetch_plot['plot_dimension'];?></td>
										<td><?php echo $row_fetch_plot['plot_facing'];?></td>
										<td><?php echo $row_fetch_plot['plot_price'];?></td>
										
										<?php
											$plot_status = $row_fetch_plot['plot_status'];
											if($plot_status=="Available")
											{?>
												<td style="background-color: Green;color:white"><?php echo $plot_status;?></td>
											<?php
											}
											else{
										?><td><?php echo $plot_status;?></td>
										
											<?php }?>
										<td><?php echo $gata_no_a;?></td>
										<td><?php echo $row_fetch_plot['area_gata_a']." Sqft";?></td>
										<td><?php echo $gata_no_b;?></td>
										<td><?php echo $row_fetch_plot['area_gata_b']." Sqft";?></td>
										<td><?php echo $gata_no_c;?></td>
										<td><?php echo $row_fetch_plot['area_gata_c']." Sqft";?></td>
										<td><?php echo $gata_no_d;?></td>
										<td><?php echo $row_fetch_plot['area_gata_d']." Sqft";?></td>
										
										
										
									</tr>
										
							
										 <?php
									}
								}
								?>
								</tbody>
					</table>
				<div class="table-wrapper">
			</main>
			<?php
				
			}
					else if ($gata_a == '' && $plot_no == '' && $farmer != '')
			{
				
				 $sql = "SELECT * from kissan_master where site_id = $site_name  and kissan_id = $farmer";
				 
				 $sql_chk = mysqli_query($con,$sql);
					$row_check = mysqli_num_rows($sql_chk);
					if($row_check >0)
					{
						
						
								
					?>
	<main class="cd__main" style="background-color: Tomato;color: white">
	<div class="table-wrapper">
         <!-- Start DEMO HTML (Use the following code into your project)-->
         <table id="example" class="table table-striped" style="width:100%">
					<thead>
				
					
					<tr>
					  <th scope="col">Site Name</th>
					  <th scope="col">Site District</th>
					  <th scope="col">Site Tehsil</th>
					  <th scope="col"> Site Gram</th>
					  <th scope="col">Total Site Area</th>
					  <th scope="col">Area for Gata Allocation</th>
					  <th scope="col">Kissan Name</th>
					  <th scope="col">Kissan total area</th>
					  <th scope="col">Gata A</th>
					  <th scope="col">Area in Gata A</th>
					  <th scope="col">Gata B</th>
					  <th scope="col">Area in Gata B</th>
					  <th scope="col">Gata C</th>
					  <th scope="col">Area in Gata C</th>
					  <th scope="col">Gata D</th>
					  <th scope="col">Area in Gata D</th>
					  
					  
					  
					</tr>
					</thead>
					<tbody>
										
								
								<?php 
						
									while($row_fetch_kissan = mysqli_fetch_array($sql_chk))
									{ 
										$site_id = $row_fetch_kissan['site_id'];
										$kissan_name = $row_fetch_kissan['k_name'];
										$kissan_area = $row_fetch_kissan['area'];
										
										
										$sql_site = "select * from site_master where site_id = $site_id";
										$result_site = mysqli_query($con,$sql_site);
										while($row_fetch_site_n = mysqli_fetch_array($result_site))
										{
											$site_name = $row_fetch_site_n['site_name'];
											$site_district = $row_fetch_site_n['district'];
											$site_tehsil = $row_fetch_site_n['tehsil'];
											$site_gram = $row_fetch_site_n['gram'];
											$site_area = $row_fetch_site_n['area'];
											$site_avail_area = $row_fetch_site_n['available_area'];
										}
										$gata_a = $row_fetch_kissan['gata_a'];
										$gata_b = $row_fetch_kissan['gata_b'];
										$gata_c = $row_fetch_kissan['gata_c'];
										$gata_d = $row_fetch_kissan['gata_d'];
										
										$sql_gata = "select * from gata_master where gata_id = $gata_a";
										$result_gata = mysqli_query($con,$sql_gata);
										while($row_fetch_gata_a = mysqli_fetch_array($result_gata))
										{
											$gata_no_a = $row_fetch_gata_a['gata_no'];
											
										}
										
										$sql_gata_b = "select * from gata_master where gata_id = $gata_b";
										$result_gata_b = mysqli_query($con,$sql_gata_b);
										while($row_fetch_gata_b = mysqli_fetch_array($result_gata_b))
										{
											$gata_no_b = $row_fetch_gata_b['gata_no'];
																						
										}
										
										$sql_gata_c = "select * from gata_master where gata_id = $gata_c";
										$result_gata_c = mysqli_query($con,$sql_gata_c);
										while($row_fetch_gata_c = mysqli_fetch_array($result_gata_c))
										{
											$gata_no_c = $row_fetch_gata_c['gata_no'];
											
										}
										
										$sql_gata_d = "select * from gata_master where gata_id = $gata_d";
										$result_gata_d = mysqli_query($con,$sql_gata_d);
										while($row_fetch_gata_d = mysqli_fetch_array($result_gata_d))
										{
											$gata_no_d = $row_fetch_gata_d['gata_no'];	
											
										}
										
										
										
										
										?>
										<tr style="background-color: #008080;color:white">
										<td><?php echo $site_name;?></td>
										<td><?php echo $site_district;?></td>
										<td><?php echo $site_tehsil;?></td>
										<td><?php echo $site_gram;?></td>
										<td><?php echo $site_area." Sqft";?></td>
										<td><?php echo $site_avail_area." Sqft";?></td>
										<td><?php echo $kissan_name;?></td>
										<td><?php echo $kissan_area." Sqft";?></td>
										<td><?php echo $gata_no_a;?></td>
										<td><?php echo $row_fetch_kissan['area_gata_a']." Sqft";?></td>
										<td><?php echo $gata_no_b;?></td>
										<td><?php echo $row_fetch_kissan['area_gata_b']." Sqft";?></td>
										<td><?php echo $gata_no_c;?></td>
										<td><?php echo $row_fetch_kissan['area_gata_c']." Sqft";?></td>
										<td><?php echo $gata_no_d;?></td>
										<td><?php echo $row_fetch_kissan['area_gata_d']." Sqft";?></td>										
									</tr>
										
							
										 <?php
									}
								}
								?>
								</tbody>
					</table>
				<div class="table-wrapper">
			</main>
			<?php
				
			}
			
			
			
				 
		}
	
}


?>

</div>

</body>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</html>
<?php } ?>