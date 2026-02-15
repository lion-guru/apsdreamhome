<?php
session_start();
include("config.php");
if(!isset($_SESSION['auser']))
{
	header("location:index.php");
}
if(isset($_POST['update']))
{
	$aid = $_GET['id'];
	$title=htmlspecialchars($_POST['utitle']);
	$content=htmlspecialchars($_POST['ucontent']);
	
	$aimage=htmlspecialchars($_FILES['aimage']['name']);
	
	$temp_name1 = $_FILES['aimage']['tmp_name'];

	move_uploaded_file($temp_name1,"upload/$aimage");
	
	$sql = "UPDATE about SET title = '{$title}' , content = '{$content}', image ='{$aimage}' WHERE id = {$aid}";
	$result=mysqli_query($con,$sql);
	if($result == true)
	{
		$msg="<p class='alert alert-success'>About Updated</p>";
		header("Location:aboutview.php?msg=".urlencode($msg));
		exit();
	}
	else{
		$error = "<p class='alert alert-warning'>* Error: " . htmlspecialchars(mysqli_error($con)) . "</p>";
	}
}
?>
 
<!DOCTYPE html>
<html lang="en">

<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>APS DREAM HOMES - Vertical Form</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
		
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
		
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
		
		<!-- Feathericon CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
		
		<!-- // SECURITY: Removed potentially dangerous code>
		<link rel="stylesheet" href="<?php echo get_asset_url('css/select2.min.css', 'css'); ?>">
		
		<link rel="stylesheet" href="assets\plugins\summernote\dist\summernote-bs4.css">
		<!-- Main CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
		
		<!--[if lt IE 9]>
			<script src="<?php echo get_asset_url('js/html5shiv.min.js', 'js'); ?>"></script>
			<script src="<?php echo get_asset_url('js/respond.min.js', 'js'); ?>"></script>
		<![endif]-->
    </head>
    <body>
		<?php include("../includes/templates/dynamic_header.php"); ?>
		<!-- Main Wrapper -->
            <div class="page-wrapper">
			
				<div class="content container-fluid">

					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col">
								<h3 class="page-title">About</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
									<li class="breadcrumb-item active">About</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					
					<div class="row">
						<div class="col-md-12">
							<div class="card">
								<div class="card-header">
									<h2 class="card-title">Edit About Us</h2>
								</div>
								<div class="card-body">
									<?php if($error) echo $error; ?>
									<?php 
									$aid = $_GET['id'];
									$sql = "SELECT * FROM about where id = {$aid}";
									$result = mysqli_query($con, $sql);
									if($row = mysqli_fetch_assoc($result)):
									?>
									<form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
										<div class="row g-3">
											<div class="col-md-6">
												<div class="form-floating position-relative mb-3">
													<input type="text" class="form-control" id="utitle" name="utitle" placeholder="Title" required value="<?php echo htmlspecialchars($row['title']); ?>">
													<label for="utitle"><i class="fa fa-heading"></i> Title</label>
													<div class="invalid-feedback">Please enter the title.</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-floating position-relative mb-3">
													<textarea class="form-control" id="ucontent" name="ucontent" placeholder="Content" style="height: 120px" required><?php echo htmlspecialchars($row['content']); ?></textarea>
													<label for="ucontent"><i class="fa fa-align-left"></i> Content</label>
													<div class="invalid-feedback">Please enter the content.</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="mb-3">
													<label for="aimage" class="form-label"><i class="fa fa-image"></i> Image</label>
													<input type="file" class="form-control" id="aimage" name="aimage" accept="image/*">
													<div class="form-text">Leave blank to keep current image.</div>
												</div>
												<?php if(!empty($row['image'])): ?>
													<img src="upload/<?php echo htmlspecialchars($row['image']); ?>" alt="Current Image" class="img-thumbnail mt-2" style="max-width:120px;">
												<?php endif; ?>
											</div>
										</div>
										<div class="d-grid mt-4">
											<button type="submit" name="update" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-save"></i> Update About Section</button>
										</div>
									</form>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					
					
				</div>
			</div>
			<!-- /Page Wrapper -->
		<?php include("../includes/templates/new_footer.php"); ?>
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
