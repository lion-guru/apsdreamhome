<?php 
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");
if(!isset($_SESSION['uemail']))
{
	header("location:login.php");
}

////// code
$error='';
$msg='';
if(isset($_POST['insert']))
{
	$name=$_POST['name'];
	$phone=$_POST['phone'];

	$content=$_POST['content'];
	
	$uid=$_SESSION['uid'];
	
	if(!empty($name) && !empty($phone) && !empty($content))
	{
		
		$sql="INSERT INTO feedback (uid,fdescription,status) VALUES ('$uid','$content','0')";
		   $result=mysqli_query($con, $sql);
		   if($result){
			   $msg = "<p class='alert alert-success'>Feedback Send Successfully</p> ";
		   }
		   else{
			   $error = "<p class='alert alert-warning'>Feedback Not Send Successfully</p> ";
		   }
	}else{
		$error = "<p class='alert alert-warning'>Please Fill all the fields</p>";
	}
}								
?>
<!DOCTYPE html>
<html lang="en">

<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<!-- Meta Tags -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="shortcut icon" href="images/favicon.ico">

<!--	Fonts
	========================================================-->
<link href="https://fonts.googleapis.com/css?family=Muli:400,400i,500,600,700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Comfortaa:400,700" rel="stylesheet">

<!--	Css Link
	========================================================-->
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap-slider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/jquery-ui.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/layerslider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/color.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/owl.carousel.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
<link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
<link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/login.css', 'css'); ?>">

<!--	Title
	=========================================================-->
<title>APS Dream Homes</title>
</head>
<body>

<!--	Page Loader
=============================================================
<div class="page-loader position-fixed z-index-9999 w-100 bg-white vh-100">
	<div class="d-flex justify-content-center y-middle position-relative">
	  <div class="spinner-border" role="status">
		<span class="sr-only">Loading...</span>
	  </div>
	</div>
</div>
--> 


<div id="page-wrapper">
    <div class="row"> 
        <!--	Header start  -->
		<?php include(__DIR__ . '/includes/header.php');?>
        <!--	Header end  -->
        
        <!--	Banner   
        <div class="banner-full-row page-banner" style="background-image:url('<?php echo get_asset_url('breadcromb.jpg', 'images'); ?>');">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Profile</b></h2>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="breadcrumb" class="float-left float-md-right">
                            <ol class="breadcrumb bg-transparent m-0 p-0">
                                <li class="breadcrumb-item text-white"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Profile</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div> --->
         <!--	Banner   --->
		 
		 
		<!--	Submit property   -->
        <div class="full-row">
            <div class="container">
                    <div class="row">
						<div class="col-lg-12">
							<h2 class="text-secondary double-down-line text-center">Profile</h2>
                        </div>
					</div>
                <div class="dashboard-personal-info p-5 bg-white">
                    <form action="#" method="post">
                        <h5 class="text-secondary border-bottom-on-white pb-3 mb-4">Feedback Form</h5>
						<?php echo $msg; ?><?php echo $error; ?>
                        <div class="row">
                            <div class="col-lg-6 col-md-12">
                                <div class="form-group">
                                    <label for="user-id">Full Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter Full Name">
                                </div>                
                                
                                <div class="form-group">
                                    <label for="phone">Contact Number</label>
                                    <input type="number" name="phone"  class="form-control" placeholder="Enter Phone" maxlength="10">
                                </div>

                                <div class="form-group">
                                    <label for="about-me">Your Feedback</label>
                                    <textarea class="form-control" name="content" rows="7" placeholder="Enter Text Here...."></textarea>
                                </div>
                                <input type="submit" class="btn btn-info mb-4" name="insert" value="Send Feedback">
                            </div>
							</form>
                            <div class="col-lg-1"></div>
                            <div class="col-lg-5 col-md-12">
								<?php 
									$uid=$_SESSION['uid'];
									$query=mysqli_query($con,"SELECT * FROM `users` WHERE id='$uid'");
									while($row=mysqli_fetch_array($query))
									{
								?>
                                <div class="user-info mt-md-50"> <img src=images/user"" alt="userimage">
                                    <div class="mb-4 mt-3">
                                        
                                    </div>
									
                                    <div class="font-18">
                                        <div class="mb-1"><b>Your Sponsor ID:</b> <?php echo $row['1'];?></div>
                                        <div class="mb-1 text-capitalize"><b>Name:</b> <?php echo $row['3'];?></div>
                                        <div class="mb-1"><b>Email:</b> <?php echo $row['4'];?></div>
                                        <div class="mb-1"><b>Contact:</b> <?php echo $row['5'];?></div>
                                        <div class="mb-1"><b>Sponsor By:</b> <?php echo $row['2'];?></div>
                                        <div class="mb-1"><b>Your D.O.B:</b> <?php echo $row['23']; ?></div>
										<div class="mb-1 text-capitalize"><b>Job Role:</b> <?php echo $row['7'];?></div>
									    <div class="mb-1"><b>Address:</b> <?php echo $row['22']; ?></div>
									    <div class="mb-1"><b>BANK NAME:</b> <?php echo $row['9']; ?></div>
                                        <div class="mb-1"><b>ACCOUNT NUMBER:</b> <?php echo $row['10']; ?></div>
                                         <div class="mb-1"><b>IFSC CODE:</b> <?php echo $row['11']; ?></div>
                                        <div class="mb-1"><b>BANK BRANCH:</b> <?php echo $row['13']; ?></div>
                                      <div class="mb-1"><b>BANK DISTRICT:</b> <?php echo $row['14']; ?></div>
                                     <div class="mb-1"><b>BANK STATE:</b> <?php echo $row['15']; ?></div>
                                       <div class="mb-1"><b>ACCOUNT TYPE:</b> <?php echo $row['16']; ?></div>
                                       <div class="mb-1"><b>PAN:</b> <?php echo $row['17']; ?></div>
                                       <div class="mb-1"><b>AADHAR:</b> <?php echo $row['18']; ?></div>
                                       <div class="mb-1"><b>NOMINEE NAME:</b> <?php echo $row['19']; ?></div>
                                       <div class="mb-1"><b>NOMINEE RELATION:</b> <?php echo $row['20']; ?></div>
                                      <div class="mb-1"><b>NOMINEE CONTACT:</b> <?php echo $row['21']; ?></div>
                                      
                                      <div class="mb-1"><b>JOIN DATE:</b> <?php echo $row['24']; ?></div>
                                      <div class="mb-1"><b>PROFILE UPDATED:</b> <?php echo $row['25']; ?></div>
										 <!-- Profile Edit Option -->
                                         <div class="mb-1">
                                              <a href="edit-profile.php?uid=<?php echo $row['0']; ?>" class="btn btn-primary">Edit Profile</a>
                                            </div>
        
                                    </div>
									<?php } ?>
                                </div>
                            </div>
                        </div>
                    
                </div>            
            </div>
        </div>
	<!--	Submit property   -->
          
        
        <!--	Footer   start-->
		<?php include(__DIR__ . '/includes/footer.php');?>
		<!--	Footer   start-->
        
        <!-- Scroll to top --> 
        <a href="#" class="bg-secondary text-white hover-text-secondary" id="scroll"><i class="fas fa-angle-up"></i></a> 
        <!-- End Scroll To top --> 
    </div>
</div>
<!-- Wrapper End --> 
  
<!--	Js Link
============================================================--> 
<script src="<?php echo get_asset_url('js/jquery.min.js', 'js'); ?>"></script> 
<!--jQuery Layer Slider --> 
<script src="<?php echo get_asset_url('js/greensock.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/layerslider.transitions.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/layerslider.kreaturamedia.jquery.js', 'js'); ?>"></script> 
<!--jQuery Layer Slider --> 
<script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/owl.carousel.min.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/tmpl.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/jquery.dependClass-0.1.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/draggable-0.1.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/jquery.slider.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/wow.js', 'js'); ?>"></script> 
<script src="<?php echo get_asset_url('js/custom.js', 'js'); ?>"></script>
</body>
</html>