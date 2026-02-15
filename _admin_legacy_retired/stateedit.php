<?php
session_start();
require("config.php"); 
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
	$sid = $_GET['id'];
	$ustate=$_POST['ustate'];

	$sql="UPDATE state SET sname = ? WHERE sid = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $ustate, $sid);
    $result = mysqli_stmt_execute($stmt);
	if($result)
		{
			log_admin_activity('edit_state', 'Edited state ID: ' . $sid . ', new name: ' . $ustate);
			$msg="<p class='alert alert-success'>State Updated</p>";
			header("Location:stateadd.php?msg=$msg");
		}
		else
		{
			$msg="<p class='alert alert-warning'>State Not Updated</p>";
			header("Location:stateadd.php?msg=$msg");
		}	
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>APS DREAM HOMES - Data Tables</title>
		
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
	
		<!-- Main Wrapper -->

		
			<!-- Header -->
			<?php include("header.php");?>	
			<!-- /Sidebar -->
			
			<!-- Page Wrapper -->
            <div class="page-wrapper">
                <div class="content container-fluid">

					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col">
								<h3 class="page-title">Edit State</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
									<li class="breadcrumb-item active">Edit State</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					<div class="row justify-content-center">
						<div class="col-lg-8">
							<div class="card shadow-sm">
								<div class="card-header">
									<h2 class="card-title">Edit State</h2>
								</div>
								<div class="card-body">
									<?php if($error) echo $error; ?>
									<?php if($msg) echo $msg; ?>
									<?php 
									$sid = $_GET['id'];
									$sql = "SELECT * FROM state WHERE sid = ?";
									$stmt = mysqli_prepare($con, $sql);
									mysqli_stmt_bind_param($stmt, "i", $sid);
									mysqli_stmt_execute($stmt);
									$result = mysqli_stmt_get_result($stmt);
									if($row = mysqli_fetch_assoc($result)):
									?>
									<form method="POST" action="" class="needs-validation" novalidate>
										<div class="form-floating position-relative mb-3">
											<input type="text" class="form-control" id="ustate" name="ustate" placeholder="State Name" required value="<?php echo htmlspecialchars($row['sname']); ?>">
											<label for="ustate"><i class="fa fa-flag"></i> State Name</label>
											<div class="invalid-feedback">Please enter the state name.</div>
										</div>
										<div class="d-grid mt-4">
											<button type="submit" name="insert" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-save"></i> Update State</button>
										</div>
									</form>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>			
			</div>
			<!-- /Main Wrapper -->

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
    </body>
</html>
