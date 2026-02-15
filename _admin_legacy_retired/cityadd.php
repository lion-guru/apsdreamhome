<?php
session_start();
include("config.php"); 
require_once __DIR__ . '/../includes/log_admin_activity.php';
// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
///code
$error="";
$msg="";
if(isset($_POST['insert']))
{
	$state=$_POST['state'];
	$city=$_POST['city'];
	
	if(!empty($state) && !empty($city)){
		$sql="insert into city (cname,sid) values('$city','$state')";
		$result=mysqli_query($con,$sql);
		if($result)
			{
				log_admin_activity('add_city', 'Added city: ' . $city . ', state ID: ' . $state);
				$msg="<p class='alert alert-success'>City Inserted Successfully</p>";
						
			}
			else
			{
				$error="<p class='alert alert-warning'>* City Not Inserted</p>";
			}
	}
	else{
		$error = "<p class='alert alert-warning'>* Fill all the Fields</p>";
	}
	
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>APS Dream Homes - Data Tables</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
		
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
		
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
		
		<!-- Feathericon CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
		
		<!-- Datatables CSS -->
		<link rel="stylesheet" href="assets/plugins/datatables/dataTables.bootstrap4.min.css">
		<link rel="stylesheet" href="assets/plugins/datatables/responsive.bootstrap4.min.css">
		<link rel="stylesheet" href="assets/plugins/datatables/select.bootstrap4.min.css">
		<link rel="stylesheet" href="assets/plugins/datatables/buttons.bootstrap4.min.css">
		
		<!-- Main CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
		
		<!--[if lt IE 9]>
			<script src="<?php echo get_asset_url('js/html5shiv.min.js', 'js'); ?>"></script>
			<script src="<?php echo get_asset_url('js/respond.min.js', 'js'); ?>"></script>
		<![endif]-->
    </head>
    <body>
		<?php include("../includes/templates/dynamic_header.php"); ?>
		<!-- Page Wrapper -->
            <div class="page-wrapper">
                <div class="content container-fluid">

					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col">
								<h3 class="page-title">City</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
									<li class="breadcrumb-item active">City</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					<div class="row justify-content-center">
						<div class="col-lg-8">
							<div class="card shadow-sm">
								<div class="card-header">
									<h2 class="card-title">Add City</h2>
								</div>
								<div class="card-body">
									<?php if($error) echo $error; ?>
									<?php if($msg) echo $msg; ?>
									<form method="POST" action="" class="needs-validation" novalidate>
										<div class="row g-3">
											<div class="col-md-6">
												<div class="form-floating position-relative mb-3">
													<select class="form-select" id="state" name="state" required>
														<option value="">Select State</option>
														<?php
															$query1=mysqli_query($con,"select * from state");
															while($row1=mysqli_fetch_row($query1))
																{
														?>
														<option value="<?php echo $row1['0']; ?>" class="text-captalize"><?php echo $row1['1']; ?></option>
														<?php } ?>
													</select>
													<label for="state"><i class="fa fa-flag"></i> State</label>
													<div class="invalid-feedback">Please select the state.</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-floating position-relative mb-3">
													<input type="text" class="form-control" id="city" name="city" placeholder="City Name" required value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
													<label for="city"><i class="fa fa-city"></i> City Name</label>
													<div class="invalid-feedback">Please enter the city name.</div>
												</div>
											</div>
										</div>
										<div class="d-grid mt-4">
											<button type="submit" name="insert" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add City</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<!-- view city  --->
					<div class="row">
						<div class="col-sm-12">
							<div class="card">
								<div class="card-header">
									<h4 class="card-title">City List</h4>
									
								</div>
								<div class="card-body">

									<table id="basic-datatable" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>City</th>
													<!-- <th>State ID</th> -->
													<th>State</th>
													<th>Actions</th>
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>
											<?php
													
												$query=mysqli_query($con,"select city.*,state.sname from city,state where city.sid=state.sid");
												$cnt=1;
												while($row=mysqli_fetch_array($query))
													{
											?>
                                                <tr>
                                                    
                                                    <td><?php echo $cnt; ?></td>
                                                    <td><?php echo $row['1']; ?></td>
													<!-- <td><?php echo $row['2']; ?></td> -->
													<td><?php echo $row['sname']; ?></td>
													<td><a href="cityedit.php?id=<?php echo $row['0']; ?>"><button class="btn btn-info">Edit</button></a>
                                                   <a href="citydelete.php?id=<?php echo $row['0']; ?>"><button class="btn btn-danger">Delete</button></a></td>
                                                </tr>
                                                <?php $cnt=$cnt+1; } ?>

                                            </tbody>
                                        </table>
								</div>
							</div>
						</div>
					</div>
				<!-- view City -->
				</div>			
			</div>
			<!-- /Main Wrapper -->
			<!---
			
			
			
			---->

		<!-- jQuery -->
        <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>
		
		<!-- Bootstrap Core JS -->
        <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
        <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
		
		<!-- Slimscroll JS -->
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
		
		<!-- Datatables JS -->
		<!-- Datatables JS -->
		<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
		<script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
		<script src="assets/plugins/datatables/dataTables.responsive.min.js"></script>
		<script src="assets/plugins/datatables/responsive.bootstrap4.min.js"></script>
		
		<script src="assets/plugins/datatables/dataTables.select.min.js"></script>
		
		<script src="assets/plugins/datatables/dataTables.buttons.min.js"></script>
		<script src="assets/plugins/datatables/buttons.bootstrap4.min.js"></script>
		<script src="assets/plugins/datatables/buttons.html5.min.js"></script>
		<script src="assets/plugins/datatables/buttons.flash.min.js"></script>
		<script src="assets/plugins/datatables/buttons.print.min.js"></script>
		
		<!-- Custom JS -->
		<script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>
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
		<?php include("../includes/templates/new_footer.php"); ?>
    </body>
</html>
