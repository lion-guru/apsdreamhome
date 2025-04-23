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
	$state=$_POST['state'];
	
	if(!empty($state)){
		$sql="insert into state (sname) values('$state')";
		$result=mysqli_query($con,$sql);
		if($result)
			{
				log_admin_activity('add_state', 'Added state: ' . $state);
				$msg="<p class='alert alert-success'>State Inserted Successfully</p>";
						
			}
			else
			{
				$error="<p class='alert alert-warning'>* State Not Inserted</p>";
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
					
				<!-- state add section --> 
					<div class="row justify-content-center">
						<div class="col-lg-8">
							<div class="card shadow-sm">
								<div class="card-header">
									<h2 class="card-title">Add State</h2>
								</div>
								<div class="card-body">
									<?php if($error) echo $error; ?>
									<?php if($msg) echo $msg; ?>
									<form method="POST" action="" class="needs-validation" novalidate>
										<div class="form-floating position-relative mb-3">
											<input type="text" class="form-control" id="state" name="state" placeholder="State Name" required value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>">
											<label for="state"><i class="fa fa-flag"></i> State Name</label>
											<div class="invalid-feedback">Please enter the state name.</div>
										</div>
										<div class="d-grid mt-4">
											<button type="submit" name="insert" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add State</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				<!----End state add section  --->
				
				<!----view state  --->
				<div class="row">
						<div class="col-sm-12">
							<div class="card">
								<div class="card-header">
									<h4 class="card-title">State List</h4>
									
								</div>
								<div class="card-body">

									<table id="basic-datatable " class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>State</th>
													<th>Actions</th>
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>
											<?php
													
												$query=mysqli_query($con,"select * from state");
												$cnt=1;
												while($row=mysqli_fetch_row($query))
													{
											?>
                                                <tr>
                                                    
                                                    <td><?php echo $cnt; ?></td>
                                                    <td><?php echo $row['1']; ?></td>
													<td><a href="stateedit.php?id=<?php echo $row['0']; ?>"><button class="btn btn-info">Edit</button></a>
                                                    <a href="statedelete.php?id=<?php echo $row['0']; ?>"><button class="btn btn-danger">Delete</button></a></td>
                                                </tr>
                                                <?php $cnt=$cnt+1; } ?>

                                            </tbody>
                                        </table>
								</div>
							</div>
						</div>
					</div>
				<!-- view state -->
				</div>			
			</div>
			<?php include("../includes/templates/new_footer.php"); ?>
    </body>
</html>
