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
	
		<!-- Main Wrapper -->
		
		
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
					
					
					
					
					<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="header-title mt-0 mb-4">Property View</h4>
										<?php 
											if(isset($_GET['msg']))	
											echo sanitizeInput($_GET['msg']);	
										?>
                                        <table id="datatable-buttons" class="table table-striped dt-responsive nowrap">
                                            <thead>
                                                <tr>
                                                    <!-- <th>P ID</th> -->
                                                    <th>Title</th>
                                                    <th>Type</th>
                                                    <th>BHK</th>
                                                    <th>S/R</th>
                                                   
													<th>Area</th>
                                                    <th>Price</th>
                                                    <th>Location</th>
													<th>Status</th>
                                                    <th>Main Image (Drive)</th>
                                                    <th>Images (Drive)</th>
                                                    <th>Floorplans (Drive)</th>
                                                    <th>Added Date</th>
                                                    <th>Actions</th>
                                                    
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>
												<?php
													
													$query=mysqli_query($con,"select * from property");
													while($row=mysqli_fetch_row($query))
													{
												?>
											
                                                <tr>
                                                    <!-- <td><?php echo $row['0']; ?></td> -->
                                                    <td><?php echo $row['1']; ?></td>
                                                    <td><?php echo $row['3']; ?></td>
                                                    <td><?php echo $row['4']; ?></td>
                                                    <td><?php echo $row['5']; ?></td>
                                                   
                                                    <td><?php echo $row['12']; ?></td>
                                                    <td><?php echo $row['13']; ?></td>
                                                    <td><?php echo $row['14']; ?></td>
													
                                                   
                                                    <td><?php echo $row['24']; ?></td>
                                                    <td>
                                                        <?php if (!empty($row['pimage_drive_id'])): ?>
                                                            <a href="https://drive.google.com/file/d/<?php echo htmlspecialchars($row['pimage_drive_id']); ?>/view" target="_blank" title="View Main Image on Google Drive">
                                                                <i class="fab fa-google-drive text-success ms-2"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php foreach ([1,2,3,4] as $i): $field = 'pimage'.$i.'_drive_id'; ?>
                                                            <?php if (!empty($row[$field])): ?>
                                                                <a href="https://drive.google.com/file/d/<?php echo htmlspecialchars($row[$field]); ?>/view" target="_blank" title="View Image <?php echo $i; ?> on Google Drive">
                                                                    <i class="fab fa-google-drive text-primary ms-1"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </td>
                                                    <td>
                                                        <?php foreach ([['mapimage_drive_id','Map'],['topmapimage_drive_id','Top'],['groundmapimage_drive_id','Ground']] as $fp): ?>
                                                            <?php if (!empty($row[$fp[0]])): ?>
                                                                <a href="https://drive.google.com/file/d/<?php echo htmlspecialchars($row[$fp[0]]); ?>/view" target="_blank" title="View <?php echo $fp[1]; ?> Floorplan on Google Drive">
                                                                    <i class="fab fa-google-drive text-warning ms-1"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </td>
                                                    <td><?php echo $row['29']; ?></td>
													<td><a href="propertyedit.php?id=<?php echo $row['0'];?>"><button class="btn btn-info">Edit</button></a>
                                                    <a href="delete.php?type=property&id=<?php echo $row['0'];?>"><button class="btn btn-danger">Delete</button></a>
													<?php if ($row['24'] === 'pending'): ?>
														<a href="property_approvals.php?approve=<?php echo $row['0']; ?>" class="btn btn-success">Approve</a>
														<a href="property_approvals.php?reject=<?php echo $row['0']; ?>" class="btn btn-warning">Reject</a>
													<?php endif; ?>
													</td>
                                                </tr>
                                               <?php
												} 
												?>
                                            </tbody>
                                        </table>
                                        
                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
					
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
		
    </body>
</html>
