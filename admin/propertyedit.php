<?php
session_start();
require("config.php");

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

////code
 
//// add code
$error="";
$msg="";
if(isset($_POST['add']))
{
	$pid=$_REQUEST['id'];
	
	$title=$_POST['title'];
	$content=$_POST['content'];
	$ptype=$_POST['ptype'];
	$bhk=$_POST['bhk'];
	$bed=$_POST['bed'];
	$balc=$_POST['balc'];
	$hall=$_POST['hall'];
	$stype=$_POST['stype'];
	$bath=$_POST['bath'];
	$kitc=$_POST['kitc'];
	$floor=$_POST['floor'];
	$price=$_POST['price'];
	$city=$_POST['city'];
	$asize=$_POST['asize'];
	$loc=$_POST['loc'];
	$state=$_POST['state'];
	$status=$_POST['status'];
	$uid=$_POST['uid'];
	$feature=$_POST['feature'];
	
	$totalfloor=$_POST['totalfl'];

	$isFeatured=$_POST['isFeatured'];
	
	$aimage=$_FILES['aimage']['name'];
	$aimage1=$_FILES['aimage1']['name'];
	$aimage2=$_FILES['aimage2']['name'];
	$aimage3=$_FILES['aimage3']['name'];
	$aimage4=$_FILES['aimage4']['name'];
	
	$fimage=$_FILES['fimage']['name'];
	$fimage1=$_FILES['fimage1']['name'];
	$fimage2=$_FILES['fimage2']['name'];
	
	$temp_name  =$_FILES['aimage']['tmp_name'];
	$temp_name1 =$_FILES['aimage1']['tmp_name'];
	$temp_name2 =$_FILES['aimage2']['tmp_name'];
	$temp_name3 =$_FILES['aimage3']['tmp_name'];
	$temp_name4 =$_FILES['aimage4']['tmp_name'];
	
	$temp_name5 =$_FILES['fimage']['tmp_name'];
	$temp_name6 =$_FILES['fimage1']['tmp_name'];
	$temp_name7 =$_FILES['fimage2']['tmp_name'];
	
	move_uploaded_file($temp_name,"property/$aimage");
	move_uploaded_file($temp_name1,"property/$aimage1");
	move_uploaded_file($temp_name2,"property/$aimage2");
	move_uploaded_file($temp_name3,"property/$aimage3");
	move_uploaded_file($temp_name4,"property/$aimage4");
	
	move_uploaded_file($temp_name5,"property/$fimage");
	move_uploaded_file($temp_name6,"property/$fimage1");
	move_uploaded_file($temp_name7,"property/$fimage2");
	
	
	$sql = "UPDATE property SET title= '{$title}', pcontent= '{$content}', type='{$ptype}', bhk='{$bhk}', stype='{$stype}',
	bedroom='{$bed}', bathroom='{$bath}', balcony='{$balc}', kitchen='{$kitc}', hall='{$hall}', floor='{$floor}', 
	size='{$asize}', price='{$price}', location='{$loc}', city='{$city}', state='{$state}', feature='{$feature}',
	pimage='{$aimage}', pimage1='{$aimage1}', pimage2='{$aimage2}', pimage3='{$aimage3}', pimage4='{$aimage4}',
	uid='{$uid}', status='{$status}', mapimage='{$fimage}', topmapimage='{$fimage1}', groundmapimage='{$fimage2}', 
	totalfloor='{$totalfloor}', isFeatured='{$isFeatured}' WHERE pid = {$pid}";
	
	$result=mysqli_query($con,$sql);
	if($result == true)
	{
		$msg="<p class='alert alert-success'>Property Updated</p>";
		header("Location:propertyview.php?msg=$msg");
	}
	else{
		$msg="<p class='alert alert-warning'>Property Not Updated</p>";
		header("Location:propertyview.php?msg=$msg");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>APS DREAM HOMES | Property</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
		
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
		
		<!-- Feathericon CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
		
		<!-- Main CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
		<style>
			.form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
			.form-floating input, .form-floating select, .form-floating textarea { padding-left: 2.5rem; }
		</style>
		<!--[if lt IE 9]>
			<script src="<?php echo get_asset_url('js/html5shiv.min.js', 'js'); ?>"></script>
			<script src="<?php echo get_asset_url('js/respond.min.js', 'js'); ?>"></script>
		<![endif]-->
    </head>
    <body>

		
			<!-- Header -->
			<?php include("../includes/templates/header.php"); ?>
			<!-- /Sidebar -->
			
			<!-- Page Wrapper -->
            <div class="page-wrapper">
                <div class="content container-fluid">
				
					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col">
								<h3 class="page-title">Property</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
									<li class="breadcrumb-item active">Property</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					
					<div class="container py-4">
						<div class="row justify-content-center">
							<div class="col-lg-10">
								<div class="card shadow-sm">
									<div class="card-header bg-primary text-white">
										<h4 class="card-title mb-0"><i class="fa fa-edit"></i> Update Property Details</h4>
									</div>
									<div class="card-body">
										<?php echo $error; ?>
										<?php echo $msg; ?>
										<form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
											<input type="hidden" name="id" value="<?php echo htmlspecialchars($_REQUEST['id']); ?>">
											<div class="row g-3">
												<div class="col-md-6">
													<div class="form-floating position-relative">
														<input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($row['1']); ?>" required placeholder="Title">
														<label for="title"><i class="fa fa-heading"></i> Title</label>
														<div class="invalid-feedback">Please enter the property title.</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-floating position-relative">
														<input type="text" class="form-control" id="ptype" name="ptype" value="<?php echo htmlspecialchars($row['3']); ?>" required placeholder="Property Type">
														<label for="ptype"><i class="fa fa-building"></i> Property Type</label>
														<div class="invalid-feedback">Please enter the property type.</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-floating position-relative">
														<input type="number" class="form-control" id="bhk" name="bhk" value="<?php echo htmlspecialchars($row['4']); ?>" required placeholder="BHK">
														<label for="bhk"><i class="fa fa-bed"></i> BHK</label>
														<div class="invalid-feedback">Please enter the number of BHK.</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-floating position-relative">
														<input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($row['13']); ?>" required placeholder="Price">
														<label for="price"><i class="fa fa-rupee-sign"></i> Price</label>
														<div class="invalid-feedback">Please enter the price.</div>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-floating position-relative">
														<textarea class="form-control" id="content" name="content" style="height: 100px" required placeholder="Description"><?php echo htmlspecialchars($row['2']); ?></textarea>
														<label for="content"><i class="fa fa-align-left"></i> Description</label>
														<div class="invalid-feedback">Please enter a description.</div>
													</div>
												</div>
												<!-- Add more fields as needed following the same pattern -->
												<div class="col-md-6">
													<div class="form-floating position-relative">
														<input type="file" class="form-control" id="aimage" name="aimage" accept="image/*">
														<label for="aimage"><i class="fa fa-image"></i> Main Image</label>
													</div>
												</div>
											</div>
											<div class="d-grid mt-4">
												<button type="submit" name="add" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-save"></i> Update Property</button>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>			
			</div>
			<!-- /Main Wrapper -->

		
		<!-- jQuery -->
        <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>
		<script src="assets/plugins/tinymce/tinymce.min.js"></script>
		<script src="assets/plugins/tinymce/init-tinymce.min.js"></script>
		<!-- Bootstrap Core JS -->
        <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
        <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
		
		<!-- Slimscroll JS -->
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
		
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
    </body>

</html>