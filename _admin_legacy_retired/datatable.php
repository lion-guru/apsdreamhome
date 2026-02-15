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
        <title>Ventura - Data Tables</title>
		
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
								<h3 class="page-title">User</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
									<li class="breadcrumb-item active">User</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					
					<div class="row">
						<div class="col-sm-12">
							<div class="card">
								<div class="card-header">
									<h4 class="card-title">Default Datatable</h4>
									<p class="card-text">
										This is the most basic example of the datatables with zero configuration. Use the <code>.datatable</code> class to initialize datatables.
									</p>
								</div>
								<div class="card-body">

									<table id="basic-datatable" class="table dt-responsive nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Office</th>
                                                    <th>Age</th>
                                                    <th>Start date</th>
                                                    <th>Salary</th>
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Tiger Nixon"); ?></td>
                                                    <td><?php echo htmlspecialchars("System Architect"); ?></td>
                                                    <td><?php echo htmlspecialchars("Edinburgh"); ?></td>
                                                    <td><?php echo htmlspecialchars("61"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/04/25"); ?></td>
                                                    <td><?php echo htmlspecialchars("$320,800"); ?></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Colleen Hurst"); ?></td>
                                                    <td><?php echo htmlspecialchars("Javascript Developer"); ?></td>
                                                    <td><?php echo htmlspecialchars("San Francisco"); ?></td>
                                                    <td><?php echo htmlspecialchars("39"); ?></td>
                                                    <td><?php echo htmlspecialchars("2009/09/15"); ?></td>
                                                    <td><?php echo htmlspecialchars("$205,500"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Sonya Frost"); ?></td>
                                                    <td><?php echo htmlspecialchars("Software Engineer"); ?></td>
                                                    <td><?php echo htmlspecialchars("Edinburgh"); ?></td>
                                                    <td><?php echo htmlspecialchars("23"); ?></td>
                                                    <td><?php echo htmlspecialchars("2008/12/13"); ?></td>
                                                    <td><?php echo htmlspecialchars("$103,600"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Jena Gaines"); ?></td>
                                                    <td><?php echo htmlspecialchars("Office Manager"); ?></td>
                                                    <td><?php echo htmlspecialchars("London"); ?></td>
                                                    <td><?php echo htmlspecialchars("30"); ?></td>
                                                    <td><?php echo htmlspecialchars("2008/12/19"); ?></td>
                                                    <td><?php echo htmlspecialchars("$90,560"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Quinn Flynn"); ?></td>
                                                    <td><?php echo htmlspecialchars("Support Lead"); ?></td>
                                                    <td><?php echo htmlspecialchars("Edinburgh"); ?></td>
                                                    <td><?php echo htmlspecialchars("22"); ?></td>
                                                    <td><?php echo htmlspecialchars("2013/03/03"); ?></td>
                                                    <td><?php echo htmlspecialchars("$342,000"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Charde Marshall"); ?></td>
                                                    <td><?php echo htmlspecialchars("Regional Director"); ?></td>
                                                    <td><?php echo htmlspecialchars("San Francisco"); ?></td>
                                                    <td><?php echo htmlspecialchars("36"); ?></td>
                                                    <td><?php echo htmlspecialchars("2008/10/16"); ?></td>
                                                    <td><?php echo htmlspecialchars("$470,600"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Haley Kennedy"); ?></td>
                                                    <td><?php echo htmlspecialchars("Senior Marketing Designer"); ?></td>
                                                    <td><?php echo htmlspecialchars("London"); ?></td>
                                                    <td><?php echo htmlspecialchars("43"); ?></td>
                                                    <td><?php echo htmlspecialchars("2012/12/18"); ?></td>
                                                    <td><?php echo htmlspecialchars("$313,500"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Tatyana Fitzpatrick"); ?></td>
                                                    <td><?php echo htmlspecialchars("Regional Director"); ?></td>
                                                    <td><?php echo htmlspecialchars("London"); ?></td>
                                                    <td><?php echo htmlspecialchars("19"); ?></td>
                                                    <td><?php echo htmlspecialchars("2010/03/17"); ?></td>
                                                    <td><?php echo htmlspecialchars("$385,750"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Michael Silva"); ?></td>
                                                    <td><?php echo htmlspecialchars("Marketing Designer"); ?></td>
                                                    <td><?php echo htmlspecialchars("London"); ?></td>
                                                    <td><?php echo htmlspecialchars("66"); ?></td>
                                                    <td><?php echo htmlspecialchars("2012/11/27"); ?></td>
                                                    <td><?php echo htmlspecialchars("$198,500"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Paul Byrd"); ?></td>
                                                    <td><?php echo htmlspecialchars("Chief Financial Officer (CFO)"); ?></td>
                                                    <td><?php echo htmlspecialchars("New York"); ?></td>
                                                    <td><?php echo htmlspecialchars("64"); ?></td>
                                                    <td><?php echo htmlspecialchars("2010/06/09"); ?></td>
                                                    <td><?php echo htmlspecialchars("$725,000"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Gloria Little"); ?></td>
                                                    <td><?php echo htmlspecialchars("Systems Administrator"); ?></td>
                                                    <td><?php echo htmlspecialchars("New York"); ?></td>
                                                    <td><?php echo htmlspecialchars("59"); ?></td>
                                                    <td><?php echo htmlspecialchars("2009/04/10"); ?></td>
                                                    <td><?php echo htmlspecialchars("$237,500"); ?></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Bradley Greer"); ?></td>
                                                    <td><?php echo htmlspecialchars("Software Engineer"); ?></td>
                                                    <td><?php echo htmlspecialchars("London"); ?></td>
                                                    <td><?php echo htmlspecialchars("41"); ?></td>
                                                    <td><?php echo htmlspecialchars("2012/10/13"); ?></td>
                                                    <td><?php echo htmlspecialchars("$132,000"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Dai Rios"); ?></td>
                                                    <td><?php echo htmlspecialchars("Personnel Lead"); ?></td>
                                                    <td><?php echo htmlspecialchars("Edinburgh"); ?></td>
                                                    <td><?php echo htmlspecialchars("35"); ?></td>
                                                    <td><?php echo htmlspecialchars("2012/09/26"); ?></td>
                                                    <td><?php echo htmlspecialchars("$217,500"); ?></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Michael Bruce"); ?></td>
                                                    <td><?php echo htmlspecialchars("Javascript Developer"); ?></td>
                                                    <td><?php echo htmlspecialchars("Singapore"); ?></td>
                                                    <td><?php echo htmlspecialchars("29"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/06/27"); ?></td>
                                                    <td><?php echo htmlspecialchars("$183,000"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Donna Snider"); ?></td>
                                                    <td><?php echo htmlspecialchars("Customer Support"); ?></td>
                                                    <td><?php echo htmlspecialchars("New York"); ?></td>
                                                    <td><?php echo htmlspecialchars("27"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/01/25"); ?></td>
                                                    <td><?php echo htmlspecialchars("$112,000"); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
								</div>
							</div>
						</div>
					</div>
					
					
					
					<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="header-title mt-0 mb-1">Buttons example</h4>
                                        <p class="sub-header">
                                            The Buttons extension for DataTables provides a common set of options, API methods and styling to display buttons on a page
                                            that will interact with a DataTable. The core library provides the based framework upon which plug-ins can built.
                                        </p>


                                        <table id="datatable-buttons" class="table table-striped dt-responsive nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Office</th>
                                                    <th>Age</th>
                                                    <th>Start date</th>
                                                    <th>Salary</th>
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Tiger Nixon"); ?></td>
                                                    <td><?php echo htmlspecialchars("System Architect"); ?></td>
                                                    <td><?php echo htmlspecialchars("Edinburgh"); ?></td>
                                                    <td><?php echo htmlspecialchars("61"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/04/25"); ?></td>
                                                    <td><?php echo htmlspecialchars("$320,800"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Garrett Winters"); ?></td>
                                                    <td><?php echo htmlspecialchars("Accountant"); ?></td>
                                                    <td><?php echo htmlspecialchars("Tokyo"); ?></td>
                                                    <td><?php echo htmlspecialchars("63"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/07/25"); ?></td>
                                                    <td><?php echo htmlspecialchars("$170,750"); ?></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Tatyana Fitzpatrick"); ?></td>
                                                    <td><?php echo htmlspecialchars("Regional Director"); ?></td>
                                                    <td><?php echo htmlspecialchars("London"); ?></td>
                                                    <td><?php echo htmlspecialchars("19"); ?></td>
                                                    <td><?php echo htmlspecialchars("2010/03/17"); ?></td>
                                                    <td><?php echo htmlspecialchars("$385,750"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Michael Silva"); ?></td>
                                                    <td><?php echo htmlspecialchars("Marketing Designer"); ?></td>
                                                    <td><?php echo htmlspecialchars("London"); ?></td>
                                                    <td><?php echo htmlspecialchars("66"); ?></td>
                                                    <td><?php echo htmlspecialchars("2012/11/27"); ?></td>
                                                    <td><?php echo htmlspecialchars("$198,500"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Paul Byrd"); ?></td>
                                                    <td><?php echo htmlspecialchars("Chief Financial Officer (CFO)"); ?></td>
                                                    <td><?php echo htmlspecialchars("New York"); ?></td>
                                                    <td><?php echo htmlspecialchars("64"); ?></td>
                                                    <td><?php echo htmlspecialchars("2010/06/09"); ?></td>
                                                    <td><?php echo htmlspecialchars("$725,000"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Gloria Little"); ?></td>
                                                    <td><?php echo htmlspecialchars("Systems Administrator"); ?></td>
                                                    <td><?php echo htmlspecialchars("New York"); ?></td>
                                                    <td><?php echo htmlspecialchars("59"); ?></td>
                                                    <td><?php echo htmlspecialchars("2009/04/10"); ?></td>
                                                    <td><?php echo htmlspecialchars("$237,500"); ?></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Donna Snider"); ?></td>
                                                    <td><?php echo htmlspecialchars("Customer Support"); ?></td>
                                                    <td><?php echo htmlspecialchars("New York"); ?></td>
                                                    <td><?php echo htmlspecialchars("27"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/01/25"); ?></td>
                                                    <td><?php echo htmlspecialchars("$112,000"); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
					
					
					<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="header-title mt-0 mb-1">Multi item selection</h4>
                                        <p class="sub-header">
                                            This example shows the multi option. Note how a click on a row will toggle its selected state without effecting other rows,
                                            unlike the os and single options shown in other examples.
                                        </p>

                                        <table id="selection-datatable" class="table dt-responsive nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Office</th>
                                                    <th>Age</th>
                                                    <th>Start date</th>
                                                    <th>Salary</th>
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Tiger Nixon"); ?></td>
                                                    <td><?php echo htmlspecialchars("System Architect"); ?></td>
                                                    <td><?php echo htmlspecialchars("Edinburgh"); ?></td>
                                                    <td><?php echo htmlspecialchars("61"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/04/25"); ?></td>
                                                    <td><?php echo htmlspecialchars("$320,800"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Garrett Winters"); ?></td>
                                                    <td><?php echo htmlspecialchars("Accountant"); ?></td>
                                                    <td><?php echo htmlspecialchars("Tokyo"); ?></td>
                                                    <td><?php echo htmlspecialchars("63"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/07/25"); ?></td>
                                                    <td><?php echo htmlspecialchars("$170,750"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Ashton Cox"); ?></td>
                                                    <td><?php echo htmlspecialchars("Junior Technical Author"); ?></td>
                                                    <td><?php echo htmlspecialchars("San Francisco"); ?></td>
                                                    <td><?php echo htmlspecialchars("66"); ?></td>
                                                    <td><?php echo htmlspecialchars("2009/01/12"); ?></td>
                                                    <td><?php echo htmlspecialchars("$86,000"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Cedric Kelly"); ?></td>
                                                    <td><?php echo htmlspecialchars("Senior Javascript Developer"); ?></td>
                                                    <td><?php echo htmlspecialchars("Edinburgh"); ?></td>
                                                    <td><?php echo htmlspecialchars("22"); ?></td>
                                                    <td><?php echo htmlspecialchars("2012/03/29"); ?></td>
                                                    <td><?php echo htmlspecialchars("$433,060"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Airi Satou"); ?></td>
                                                    <td><?php echo htmlspecialchars("Accountant"); ?></td>
                                                    <td><?php echo htmlspecialchars("Tokyo"); ?></td>
                                                    <td><?php echo htmlspecialchars("33"); ?></td>
                                                    <td><?php echo htmlspecialchars("2008/11/28"); ?></td>
                                                    <td><?php echo htmlspecialchars("$162,700"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Brielle Williamson"); ?></td>
                                                    <td><?php echo htmlspecialchars("Integration Specialist"); ?></td>
                                                    <td><?php echo htmlspecialchars("New York"); ?></td>
                                                    <td><?php echo htmlspecialchars("61"); ?></td>
                                                    <td><?php echo htmlspecialchars("2012/12/02"); ?></td>
                                                    <td><?php echo htmlspecialchars("$372,000"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Herrod Chandler"); ?></td>
                                                    <td><?php echo htmlspecialchars("Sales Assistant"); ?></td>
                                                    <td><?php echo htmlspecialchars("San Francisco"); ?></td>
                                                    <td><?php echo htmlspecialchars("59"); ?></td>
                                                    <td><?php echo htmlspecialchars("2012/08/06"); ?></td>
                                                    <td><?php echo htmlspecialchars("$137,500"); ?></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Michael Bruce"); ?></td>
                                                    <td><?php echo htmlspecialchars("Javascript Developer"); ?></td>
                                                    <td><?php echo htmlspecialchars("Singapore"); ?></td>
                                                    <td><?php echo htmlspecialchars("29"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/06/27"); ?></td>
                                                    <td><?php echo htmlspecialchars("$183,000"); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo htmlspecialchars("Donna Snider"); ?></td>
                                                    <td><?php echo htmlspecialchars("Customer Support"); ?></td>
                                                    <td><?php echo htmlspecialchars("New York"); ?></td>
                                                    <td><?php echo htmlspecialchars("27"); ?></td>
                                                    <td><?php echo htmlspecialchars("2011/01/25"); ?></td>
                                                    <td><?php echo htmlspecialchars("$112,000"); ?></td>
                                                </tr>
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
