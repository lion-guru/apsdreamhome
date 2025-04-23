<?php
session_start();
include("config.php"); 
if(!isset($_SESSION['auser']))
{
	header("location:index.php");
}
///code
$error="";
$msg="";
if(isset($_POST['insert']))
{
	$cid = $_GET['id'];
	
	$ustate=$_POST['ustate'];
	$ucity=$_POST['ucity'];
	
	if(!empty($ustate) && !empty($ucity))
	{
		$sql="UPDATE city SET cname = '{$ucity}' ,sid = '{$ustate}' WHERE cid = {$cid}";
		$result=mysqli_query($con,$sql);
		if($result)
			{
				$msg="<p class='alert alert-success'>City Updated</p>";
				header("Location:cityadd.php?msg=".urlencode('City updated successfully.'));
				exit();
			}
			else
			{
				$error = "Error: " . htmlspecialchars(mysqli_error($con));
			}
	}
	else{
		$error = "<p class='alert alert-warning'>* Please Fill all the Fields</p>";
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
								<h3 class="page-title">State</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
									<li class="breadcrumb-item active">State</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					
				<!-- city add section --> 
					<div class="row justify-content-center">
						<div class="col-lg-8">
							<div class="card shadow-sm">
								<div class="card-header">
									<h2 class="card-title">Edit City</h2>
								</div>
								<div class="card-body">
									<?php if($error) echo $error; ?>
									<?php if($msg) echo $msg; ?>
									<?php 
									$cid = $_GET['id'];
									$sql = "SELECT * FROM city WHERE cid = {$cid}";
									$result = mysqli_query($con, $sql);
									if($row = mysqli_fetch_assoc($result)):
									?>
									<form method="POST" action="" class="needs-validation" novalidate>
										<div class="row g-3">
											<div class="col-md-6">
												<div class="form-floating position-relative mb-3">
													<select class="form-select" id="ustate" name="ustate" required>
														<option value="">Select State</option>
														<?php
															$query1 = mysqli_query($con, "SELECT * FROM state");
															while($row1 = mysqli_fetch_assoc($query1)) {
																$selected = ($row1['sid'] == $row['sid']) ? 'selected' : '';
														?>
														<option value="<?php echo $row1['sid']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row1['sname']); ?></option>
														<?php } ?>
													</select>
													<label for="ustate"><i class="fa fa-flag"></i> State</label>
													<div class="invalid-feedback">Please select the state.</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-floating position-relative mb-3">
													<input type="text" class="form-control" id="ucity" name="ucity" placeholder="City Name" required value="<?php echo htmlspecialchars($row['cname']); ?>">
													<label for="ucity"><i class="fa fa-city"></i> City Name</label>
													<div class="invalid-feedback">Please enter the city name.</div>
												</div>
											</div>
										</div>
										<div class="d-grid mt-4">
											<button type="submit" name="insert" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-save"></i> Update City</button>
										</div>
									</form>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<!----End City add section  --->

				</div>			
			</div>
			<?php include("../includes/templates/new_footer.php"); ?>
    </body>
</html>
