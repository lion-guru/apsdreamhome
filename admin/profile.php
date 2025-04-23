<?php
session_start();
require("config.php");

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
       !-->
 <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>APS DREAM HOMES | Profile</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
		
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
		
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
		
		<!-- Feathericon CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
		
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
            <?php include("../includes/templates/dynamic_header.php");?>
			<!-- /Header -->
			
			<!-- Page Wrapper -->
            <div class="page-wrapper">
                <div class="content container-fluid">
					
					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col">
								<h3 class="page-title">Profile</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
									<li class="breadcrumb-item active">Profile</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					
					<div class="row">
						<?php
						
						$id=$_SESSION['admin_logged_in'];
						$sql="select * from admin where auser='$id'";
						$result=mysqli_query($con,$sql);
						while($row=mysqli_fetch_array($result))
						{
						?>
						<div class="col-md-12">
							<div class="profile-header">
								<div class="row align-items-center">
									<div class="col-auto profile-image">
										<a href="#">
											<img class="rounded-circle" alt="User Image" src="assets/<?php echo get_asset_url('profiles/avatar-01.png', 'images'); ?>">
										</a>
									</div>
									<div class="col ml-md-n2 profile-user-info">
										<h4 class="user-name mb-2 text-uppercase"><?php echo $row['1']; ?></h4>
										<h6 class="text-muted"><?php echo $row['2']; ?></h6>
										<div class="user-Location"><i class="fa fa-id-badge" aria-hidden="true"></i>
											<?php echo $row['4']; ?></div>
										<div class="about-text"></div>
									</div>

								</div>
							</div>
							<div class="profile-menu">
								<ul class="nav nav-tabs nav-tabs-solid">
									<li class="nav-item">
										<a class="nav-link active" data-toggle="tab" href="#per_details_tab">About</a>
									</li>
								<!--	<li class="nav-item">
										<a class="nav-link" data-toggle="tab" href="#password_tab">Password</a>
									</li>  -->
								</ul>
							</div>	
							<div class="tab-content profile-tab-cont">
								
								<!-- Personal Details Tab -->
								<div class="tab-pane fade show active" id="per_details_tab">
								
									<!-- Personal Details -->
									<div class="row">
										<div class="col-lg-9">
											<div class="card">
												<div class="card-body">
													<div class="row">
														<p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Name</p>
														<p class="col-sm-9"><?php echo $row['1']; ?></p>
													</div>
													<div class="row">
														<p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Date of Birth</p>
														<p class="col-sm-9"><?php echo $row['4']; ?></p>
													</div>
													<div class="row">
														<p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Email ID</p>
														<p class="col-sm-9"><a href="#"><?php echo $row['2']; ?></a></p>
													</div>
													<div class="row">
														<p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Mobile</p>
														<p class="col-sm-9"><?php echo $row['5']; ?></p>
													</div>
													
												</div>
											</div>
										</div>

										<div class="col-lg-3">
											
											<!-- Account Status -->
											<div class="card">
												<div class="card-body">
													<h5 class="card-title d-flex justify-content-between">
														<span>Account Status</span>
														
													</h5>
													<button class="btn btn-success" type="button"><i class="fe fe-check-verified"></i> Active</button>
												</div>
											</div>
											<!-- /Account Status -->

											
										</div>
									</div>
									<!-- /Personal Details -->

								</div>
								<!-- /Personal Details Tab -->

								<!-- Change Password Tab -->
								<!-- Update Password Form -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Update Password</h4>
                <form method="post">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input class="form-control" type="password" name="current_password" placeholder="Current Password">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input class="form-control" type="password" name="new_password" placeholder="New Password">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input class="form-control" type="password" name="confirm_password" placeholder="Confirm Password">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" name="update_password" type="submit">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
if(isset($_POST['update_password']))
{
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $user = $_SESSION['admin_logged_in'];
    $query = "SELECT apass FROM admin WHERE auser='$user'";
    $result = mysqli_query($con,$query)or die(mysqli_error());
    $row=mysqli_fetch_array($result);
    $hashed_password = $row['apass'];

    $sha1_current_password = sha1($current_password);
    if($sha1_current_password == $hashed_password)
    {
        if($new_password == $confirm_password)
        {
            $sha1_new_password = sha1($new_password);
            $query = "UPDATE admin SET apass='$sha1_new_password' WHERE auser='$user'";
            if(mysqli_query($con,$query))
            {
                echo "Password updated successfully!";
            }
            else
            {
                echo "Error updating password: ". mysqli_error($con);
            }
        }
        else
        {
            echo "New password and confirm password do not match.";
        }
    }
    else
    {
        echo "Current password is incorrect.";
    }
}
?>
								<!-- /Change Password Tab -->

							</div>
						</div>
					</div>
				<?php } ?>
				</div>
			</div>
			<!-- /Page Wrapper -->

		<!-- /Main Wrapper -->
		
		<!-- jQuery -->
        <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>
           <script>
    $(".toggle-password").click(function() {
        $(this).toggleClass("fa-eye fa-eye-slash");
        var input = $($(this).attr("toggle"));
        if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });
</script>
		
		<!-- Bootstrap Core JS -->
        <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
        <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
		
		<!-- Slimscroll JS -->
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
		
		<!-- Custom JS -->
		<script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>
		
    </body>
    <?php include("../includes/templates/new_footer.php");?>
</html>