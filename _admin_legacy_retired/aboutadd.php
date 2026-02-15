<?php
session_start();
require("config.php");

if(!isset($_SESSION['auser']))
{
	header("location:index.php");
}

//// add code
$error="";
$msg="";
if(isset($_POST['addabout']))
{
	
	$title=htmlspecialchars($_POST['title']);
	$content=htmlspecialchars($_POST['content']);
	$aimage=htmlspecialchars($_FILES['aimage']['name']);
	
	$temp_name1 = $_FILES['aimage']['tmp_name'];


	move_uploaded_file($temp_name1,"upload/$aimage");
	
	$sql="insert into about (title,content,image) values('$title','$content','$aimage')";
	$result=mysqli_query($con,$sql);
	if($result)
		{
			header("Location: aboutview.php?msg=".urlencode('About section added successfully.'));
			exit();
		}
		else
		{
			$error="<p class='alert alert-warning'>* Error: " . htmlspecialchars(mysqli_error($con)) . "</p>";
		}
}
?>
 
<!DOCTYPE html>
<html lang="en">

<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>APS DREAM HOMES | About</title>
		
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
		
		<!-- Main CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
		
		<!--[if lt IE 9]>
			<script src="<?php echo get_asset_url('js/html5shiv.min.js', 'js'); ?>"></script>
			<script src="<?php echo get_asset_url('js/respond.min.js', 'js'); ?>"></script>
		<![endif]-->
    </head>
    <body>
	
		<!-- Main Wrapper -->
		<?php include("../includes/templates/dynamic_header.php"); ?>
			<!-- Page Wrapper -->
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
									<h2 class="card-title">About Us</h2>
								</div>
								<div class="card-body">
									<?php if($error) echo $error; ?>
									<form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
										<div class="row g-3">
											<div class="col-md-6">
												<div class="form-floating position-relative mb-3">
													<input type="text" class="form-control" id="title" name="title" placeholder="Title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
													<label for="title"><i class="fa fa-heading"></i> Title</label>
													<div class="invalid-feedback">Please enter the title.</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-floating position-relative mb-3">
													<textarea class="form-control" id="content" name="content" placeholder="Content" style="height: 120px" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
													<label for="content"><i class="fa fa-align-left"></i> Content</label>
													<div class="invalid-feedback">Please enter the content.</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="mb-3">
													<label for="aimage" class="form-label"><i class="fa fa-image"></i> Image</label>
													<input type="file" class="form-control" id="aimage" name="aimage" accept="image/*" required>
													<div class="invalid-feedback">Please upload an image.</div>
												</div>
											</div>
										</div>
										<div class="d-grid mt-4">
											<button type="submit" name="addabout" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add About Section</button>
										</div>
									</form>
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
