<?php
session_start();
include("config.php");
include(__DIR__ . '/includes/functions.php');
error_reporting(E_ERROR | E_PARSE);

// Check for the correct session variable
if(isset($_SESSION['utype'])) {
    $user = $_SESSION['utype'];
} elseif(isset($_SESSION['usertype'])) {
    $user = $_SESSION['usertype'];
} else {
    $user = '';
}

// Get associate ID from session
if(isset($_SESSION['uid'])) {
    $associate_id = $_SESSION['uid'];
} elseif(isset($_SESSION['aid'])) {
    $associate_id = $_SESSION['aid'];
} else {
    $associate_id = 0;
}
$msg = '';

// Check if the user is logged in
if ($user != 'assosiate') {
    // Debug information
    error_log("User type: " . $user);
    error_log("Associate ID: " . $associate_id);
    
    header("location:login.php");
    exit();
}

// Fetch associate details from the database
$query_asso_details = "SELECT * FROM associates WHERE uid = '$associate_id'";
$result_asso_details = mysqli_query($conn, $query_asso_details);

while($row_asso_details = mysqli_fetch_array($result_asso_details)) {
    $uid = $row_asso_details['uid'];
    $asso_name = $row_asso_details['uname'];
    $asso_email = $row_asso_details['uemail'];
    $asso_phone = $row_asso_details['uphone'];
    $sponsor_id = $row_asso_details['sponser_id'];
    $sponsored_by = $row_asso_details['sponsored_by'];
    $bank_name = $row_asso_details['bank_name'];
    $account_number = $row_asso_details['account_number'];
    $ifsc_code = $row_asso_details['ifsc_code'];
    $bank_micr = $row_asso_details['bank_micr'];
    $bank_branch = $row_asso_details['bank_branch'];
    $bank_district = $row_asso_details['bank_district'];
    $bank_state = $row_asso_details['bank_state'];
    $account_type = $row_asso_details['account_type'];
    $pan = $row_asso_details['pan'];
    $adhaar = $row_asso_details['adhaar'];
    $nominee_name = $row_asso_details['nominee_name'];
    $nominee_relation = $row_asso_details['nominee_relation'];
    $nominee_contact = $row_asso_details['nominee_contact'];
    $address = $row_asso_details['address'];
    $date_of_birth = $row_asso_details['date_of_birth'];
    $join_date = $row_asso_details['join_date'];
    $is_updated = $row_asso_details['is_updated'];
}

// Get sponsor information
$sponsorName = "";
$sponsorContact = "";
$sponsorEmail = "";

if($sponsor_id) {
    $query_sponsor = "SELECT uname, uphone, uemail FROM user WHERE uid = '$sponsor_id'";
    $result_sponsor = mysqli_query($conn, $query_sponsor);
    if($row_sponsor = mysqli_fetch_array($result_sponsor)) {
        $sponsorName = $row_sponsor['uname'];
        $sponsorContact = $row_sponsor['uphone'];
        $sponsorEmail = $row_sponsor['uemail'];
    }
}

// Get team count
$query_team = "SELECT COUNT(*) as team_count FROM user WHERE sponsored_by = '$associate_id'";
$result_team = mysqli_query($conn, $query_team);
$team_count = 0;
if($row_team = mysqli_fetch_array($result_team)) {
    $team_count = $row_team['team_count'];
}

// Get direct referrals count
$query_direct = "SELECT COUNT(*) as direct_count FROM user WHERE sponser_id = '$associate_id'";
$result_direct = mysqli_query($conn, $query_direct);
$direct_count = 0;
if($row_direct = mysqli_fetch_array($result_direct)) {
    $direct_count = $row_direct['direct_count'];
}

// Get total earnings
$query_earnings = "SELECT SUM(amount) as total_earnings FROM transactions WHERE user_id = '$associate_id' AND type = 'credit'";
$result_earnings = mysqli_query($conn, $query_earnings);
$total_earnings = 0;
if($row_earnings = mysqli_fetch_array($result_earnings)) {
    $total_earnings = $row_earnings['total_earnings'] ? $row_earnings['total_earnings'] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="APS Dream Homes Dashboard">
    <meta name="author" content="APS Dream Homes">
    <meta name="keywords" content="real estate, property, associate, dashboard">

    <!-- Title Page-->
    <title>Associate Dashboard - APS Dream Homes</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="images/logo/apsico.ico">

    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>" rel="stylesheet">

    <!-- Fontawesome CSS-->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>" rel="stylesheet">

    <!-- Main CSS-->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/theme.css', 'css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2962ff;
            --secondary-color: #4CAF50;
            --danger-color: #f44336;
            --warning-color: #ff9800;
            --info-color: #00bcd4;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Mada', sans-serif;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }
        
        .card-title {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .dash-widget-icon {
            align-items: center;
            border-radius: 10px;
            color: #fff;
            display: flex;
            float: left;
            font-size: 30px;
            height: 60px;
            justify-content: center;
            width: 60px;
        }
        
        .dash-widget-info {
            padding-left: 75px;
        }
        
        .dash-widget-info h3 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .dash-widget-info h6 {
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 10px;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .bg-success {
            background-color: var(--secondary-color) !important;
        }
        
        .bg-danger {
            background-color: var(--danger-color) !important;
        }
        
        .bg-warning {
            background-color: var(--warning-color) !important;
        }
        
        .bg-info {
            background-color: var(--info-color) !important;
        }
        
        .progress-bar {
            background-color: var(--primary-color);
        }
        
        .sidebar {
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            height: 100%;
            position: fixed;
            width: 240px;
            z-index: 1000;
        }
        
        .sidebar-header {
            align-items: center;
            background-color: #fff;
            border-bottom: 1px solid #eee;
            display: flex;
            height: 70px;
            justify-content: center;
            padding: 0 20px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
            position: relative;
        }
        
        .sidebar-menu li a {
            color: #333;
            display: block;
            font-size: 15px;
            font-weight: 500;
            padding: 10px 20px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: rgba(41, 98, 255, 0.1);
            color: var(--primary-color);
        }
        
        .sidebar-menu li a i {
            font-size: 16px;
            margin-right: 10px;
            width: 20px;
        }
        
        .page-wrapper {
            margin-left: 240px;
            padding-top: 70px;
            transition: all 0.3s ease;
        }
        
        .header {
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            height: 70px;
            left: 240px;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 999;
        }
        
        .header-left {
            float: left;
            height: 70px;
            padding: 0 20px;
            position: relative;
            width: 240px;
            z-index: 1;
        }
        
        .header-right {
            float: right;
            height: 70px;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        .top-nav-search {
            float: left;
            margin-top: 15px;
        }
        
        .top-nav-search form {
            margin-top: 10px;
            position: relative;
            width: 230px;
        }
        
        .user-menu {
            float: right;
            margin: 0;
            position: relative;
            z-index: 99;
        }
        
        .user-menu.nav > li > a {
            color: #333;
            font-size: 15px;
            line-height: 70px;
            padding: 0 15px;
        }
        
        .user-menu.nav > li > a:hover,
        .user-menu.nav > li > a:focus {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .user-img {
            display: inline-block;
            position: relative;
        }
        
        .user-img img {
            border-radius: 50%;
            width: 30px;
        }
        
        .main-wrapper {
            width: 100%;
        }
        
        .page-title {
            color: #333;
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .breadcrumb {
            background-color: transparent;
            color: #6c757d;
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 0;
            padding: 0;
        }
        
        .content {
            padding: 30px;
        }
        
        @media (max-width: 991.98px) {
            .sidebar {
                margin-left: -240px;
                transition: all 0.3s ease;
            }
            .page-wrapper {
                margin-left: 0;
                padding-top: 70px;
                transition: all 0.3s ease;
            }
            .header {
                left: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="logo">
                    <img src="assets/<?php echo get_asset_url('logo/restatelg.png', 'images'); ?>" alt="APS Dream Homes" width="180">
                </a>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li>
                        <a href="dash.php" class="active">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="submenu">
                        <a href="#">
                            <i class="fa fa-user"></i> <span>Profile</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="view_profile.php">View Profile</a></li>
                            <li><a href="Edit_profile.php">Edit Profile</a></li>
                            <li><a href="change_password.php">Change Password</a></li>
                            <li><a href="welcome_letter.php">Welcome Letter</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="#">
                            <i class="fa fa-briefcase"></i> <span>Business Details</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="self_business_report.php">Self Business Report</a></li>
                            <li><a href="team_business_report.php">Team Business Report</a></li>
                            <li><a href="agent_payment_collection.php">Agents Payment Collection</a></li>
                            <li><a href="payment_collaction_report.php">Payment Collection Report</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="#">
                            <i class="fa fa-users"></i> <span>Team</span> <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="my_downline.php">My Downline</a></li>
                            <li><a href="my_direct.php">My Direct</a></li>
                            <li><a href="tree_view.php">My Tree View</a></li>
                        </ul>
                    </li>
								 <li>
                                    <a href="my_direct.php">My Direct</a>
                                </li>
								 <li>
                                    <a href="tree_view.php">My Tree View</a>
                                </li>
                                
                            </ul>
                        </li>
						
						 <li class="has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-tachometer-alt"></i>My Account</a>
                            <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                 <li>
                                    <a href="payout_details.php">Payout Details</a>
                                </li>
								 <li>
                                    <a href="cus_ladger.php">customer Ladger</a>
                                </li>
								 <li>
                                    <a href="my_ladger.php">My Ladger</a>
                                </li>
                                
                            </ul>
                        </li>
						
						 <li class="has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-tachometer-alt"></i>Support</a>
                            <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                 <li>
                                    <a href="send_enquiry.php">Send Enquiry</a>
                                </li>
								 <li>
                                    <a href="enquiry_details.php">Enquiry Details</a>
                                </li>
								 <li>
                                    <a href="my_ladger.php">My Ladger</a>
                                </li>
                                
                            </ul>
                        </li>
						
						 
                        
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <!-- END HEADER MOBILE-->

        <!-- MENU SIDEBAR-->
        <aside class="menu-sidebar d-none d-lg-block">
            <div class="logo">
                <a href="#">
                    <img src="assets/<?php echo get_asset_url('logo/restatelg.png', 'images'); ?>" alt="APS Dream Homes" />
                </a>
            </div>
            <div class="menu-sidebar__content js-scrollbar1">
                <nav class="navbar-sidebar">
                    <ul class="list-unstyled navbar__list">
                        <li class="active has-sub">
                            <a class="js-arrow" href="dash.php">
                                <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                            
                        </li>
						<li class="active has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-tachometer-alt"></i>Profile</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li>
                                    <a href="view_profile.php">View Profile</a>
                                </li>
								 <li>
                                    <a href="Edit_profile.php">Edit Profile</a>
                                </li>
								 <li>
                                    <a href="change_password.php">Change Password</a>
                                </li>
								 <li>
                                    <a href="welcome_letter.php">Welcome Letter</a>
                                </li>
                               
                            </ul>
                        </li>
						
						<li class="active has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-tachometer-alt"></i>Business Details</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li>
                                    <a href="self_business_report.php">Self Business Report</a>
                                </li>
								 <li>
                                    <a href="team_business_report.php">Team Business Report</a>
                                </li>
								 <li>
                                    <a href="agent_payment_collection.php">Agents Payment Collaction</a>
                                </li>
								 <li>
                                    <a href="payment_collaction_report.php">Payment Collaction Report</a>
                                </li>
                               
                            </ul>
                        </li>
						<li class="active has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-tachometer-alt"></i>Team</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li>
                                    <a href="my_downline.php">My Downline</a>
                                </li>
								 <li>
                                    <a href="my_direct.php">My Direct</a>
                                </li>
								 <li>
                                    <a href="tree_view.php">My Tree View</a>
                                </li>
								
                               
                            </ul>
                        </li>
						<li class="active has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-tachometer-alt"></i>My Account</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li>
                                    <a href="payout_details.php">Payout Details</a>
                                </li>
								 <li>
                                    <a href="cus_ladger.php">customer Ladger</a>
                                </li>
								 <li>
                                    <a href="my_ladger.php">My Ladger</a>
                                </li>
								
                               
                            </ul>
                        </li>
						<li class="active has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-tachometer-alt"></i>Support</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li>
                                    <a href="send_enquiry.php">Send Enquiry</a>
                                </li>
								 <li>
                                    <a href="enquiry_details.php">Enquiry Details</a>
                                </li>
								 <li>
                                    <a href="my_ladger.php">My Ladger</a>
                                </li>
								
                               
                            </ul>
                        </li>
                        
                       
                        
                    </ul>
                </nav>
            </div>
        </aside>
        <!-- END MENU SIDEBAR-->

        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <!-- HEADER DESKTOP-->
            <header class="header-desktop">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="header-wrap">
                            <form class="form-header" action="" method="POST">
                                <input class="au-input au-input--xl" type="text" name="search" placeholder="Search for datas &amp; reports..." />
                                <button class="au-btn--submit" type="submit">
                                    <i class="zmdi zmdi-search"></i>
                                </button>
                            </form>
                            <div class="header-button">
                                <!--Display user details -->
                                
                                <div class="noti-wrap">
                                    <div class="noti__item js-item-menu">
                                        <i class="zmdi zmdi-comment-more"></i>
                                        <span class="quantity">1</span>
                                        <div class="mess-dropdown js-dropdown">
                                            <div class="mess__title">
                                                <p>You have 2 news message</p>
                                            </div>
                                            <div class="mess__item">
                                                <div class="image img-cir img-40">
                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-06.jpg', 'images'); ?>" alt="Michelle Moreno" />
                                                </div>
                                                <div class="content">
                                                    <h6>Michelle Moreno</h6>
                                                    <p>Have sent a photo</p>
                                                    <span class="time">3 min ago</span>
                                                </div>
                                            </div>
                                            <div class="mess__item">
                                                <div class="image img-cir img-40">
                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-04.jpg', 'images'); ?>" alt="Diane Myers" />
                                                </div>
                                                <div class="content">
                                                    <h6>Diane Myers</h6>
                                                    <p>You are now connected on message</p>
                                                    <span class="time">Yesterday</span>
                                                </div>
                                            </div>
                                            <div class="mess__footer">
                                                <a href="#">View all messages</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="noti__item js-item-menu">
                                        <i class="zmdi zmdi-email"></i>
                                        <span class="quantity">1</span>
                                        <div class="email-dropdown js-dropdown">
                                            <div class="email__title">
                                                <p>You have 3 New Emails</p>
                                            </div>
                                            <div class="email__item">
                                                <div class="image img-cir img-40">
                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-06.jpg', 'images'); ?>" alt="Cynthia Harvey" />
                                                </div>
                                                <div class="content">
                                                    <p>Meeting about new dashboard...</p>
                                                    <span>Cynthia Harvey, 3 min ago</span>
                                                </div>
                                            </div>
                                            <div class="email__item">
                                                <div class="image img-cir img-40">
                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-05.jpg', 'images'); ?>" alt="Cynthia Harvey" />
                                                </div>
                                                <div class="content">
                                                    <p>Meeting about new dashboard...</p>
                                                    <span>Cynthia Harvey, Yesterday</span>
                                                </div>
                                            </div>
                                            <div class="email__item">
                                                <div class="image img-cir img-40">
                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-04.jpg', 'images'); ?>" alt="Cynthia Harvey" />
                                                </div>
                                                <div class="content">
                                                    <p>Meeting about new dashboard...</p>
                                                    <span>Cynthia Harvey, April 12,,2018</span>
                                                </div>
                                            </div>
                                            <div class="email__footer">
                                                <a href="#">See all emails</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="noti__item js-item-menu">
                                        <i class="zmdi zmdi-notifications"></i>
                                        <span class="quantity">3</span>
                                        <div class="notifi-dropdown js-dropdown">
                                            <div class="notifi__title">
                                                <p>You have 3 Notifications</p>
                                            </div>
                                            <div class="notifi__item">
                                                <div class="bg-c1 img-cir img-40">
                                                    <i class="zmdi zmdi-email-open"></i>
                                                </div>
                                                <div class="content">
                                                    <p>You got a email notification</p>
                                                    <span class="date">April 12, 2018 06:50</span>
                                                </div>
                                            </div>
                                            <div class="notifi__item">
                                                <div class="bg-c2 img-cir img-40">
                                                    <i class="zmdi zmdi-account-box"></i>
                                                </div>
                                                <div class="content">
                                                    <p>Your account has been blocked</p>
                                                    <span class="date">April 12, 2018 06:50</span>
                                                </div>
                                            </div>
                                            <div class="notifi__item">
                                                <div class="bg-c3 img-cir img-40">
                                                    <i class="zmdi zmdi-file-text"></i>
                                                </div>
                                                <div class="content">
                                                    <p>You got a new file</p>
                                                    <span class="date">April 12, 2018 06:50</span>
                                                </div>
                                            </div>
                                            <div class="notifi__footer">
                                                <a href="#">All notifications</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="account-wrap">
                                    <div class="account-item clearfix js-item-menu">
                                        <div class="image">
                                            <img src="assets/<?php echo get_asset_url('icon/avatar-01.png', 'images'); ?>" alt="<?php echo $afullname; ?>" />
                                        </div>
                                        <div class="content">
                                            <a class="js-acc-btn" href="#"><?php echo $afullname; ?> </a>
                                        </div>
                                        <div class="account-dropdown js-dropdown">
                                            <div class="info clearfix">
                                                <div class="image">
                                                    <a href="#">
                                                        <img src="assets/<?php echo get_asset_url('icon/avatar-01.png', 'images'); ?>" alt="<?php echo $afullname; ?>" />
                                                    </a>
                                                </div>
                                                <class="content">
                                                     <a class="js-acc-btn" href="#"> <?php echo $afullname; ?></a>
                                                     <div class="content">
                                                         <span class="userid">Your Sponser code: <?php echo $userId; ?></span> <!-- Display associate ID -->
                                                    
                                                <span class="phone">Phone: <?php echo $userDetails['phone']; ?></span> <!-- Add phone number -->
                                                
                                                <span class="email"><?php echo $aemail; ?></span>
                                                
                                                </div>
                                            </div>
                                           <!-- New Sponsor Section -->
                    <div class="sponsor-section">
                        <h6>Sponsor Information</h6>
                        <p>Sponsor Name: <?php echo $sponsorName; ?></p>
                        <p>Sponsor Contact: <?php echo $sponsorContact; ?></p>
                        <!-- <p>Sponsor Email: <?php echo $sponsorEmail; ?></p> -->
                    </div>
                                            <div class="account-dropdown__body">
                                                <div class="account-dropdown__item">
                                                    <a href="#">
                                                        <i class="zmdi zmdi-account"></i>Account</a>
                                                </div>
                                                <div class="account-dropdown__item">
                                                    <a href="#">
                                                        <i class="zmdi zmdi-settings"></i>Setting</a>
                                                </div>
                                                <div class="account-dropdown__item">
                                                    <a href="#">
                                                        <i class="zmdi zmdi-money-box"></i>Billing</a>
                                                </div>
                                            </div>
                                            <div class="account-dropdown__footer">
                                                <a href="#">
                                                    <i class="zmdi zmdi-power"></i>Logout</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
   
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- HEADER DESKTOP-->

            <!-- Main Content -->
            <div class="content container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="page-title">Welcome <?php echo $asso_name; ?>!</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->

                <!-- Dashboard Stats -->
                <div class="row">
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon bg-primary">
                                        <i class="fa fa-users"></i>
                                    </span>
                                    <div class="dash-widget-info">
                                        <h3><?php echo $team_count; ?></h3>
                                        <h6 class="text-muted">Team Members</h6>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary w-50"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon bg-success">
                                        <i class="fa fa-user-plus"></i>
                                    </span>
                                    <div class="dash-widget-info">
                                        <h3><?php echo $direct_count; ?></h3>
                                        <h6 class="text-muted">Direct Referrals</h6>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-success w-50"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon bg-warning">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    <div class="dash-widget-info">
                                        <h3><?php echo date('d M Y', strtotime($join_date)); ?></h3>
                                        <h6 class="text-muted">Join Date</h6>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-warning w-50"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon bg-info">
                                        <i class="fa fa-money"></i>
                                    </span>
                                    <div class="dash-widget-info">
                                        <h3>₹<?php echo number_format($total_earnings, 2); ?></h3>
                                        <h6 class="text-muted">Total Earnings</h6>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-info w-50"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Dashboard Stats -->

                <!-- Profile & Sponsor Info -->
                <div class="row">
                    <div class="col-md-6 d-flex">
                        <div class="card card-table flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Profile Information</h4>
                            </div>
                            <div class="card-

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="au-card recent-report">
                                    <div class="au-card-inner">
                                        <h3 class="title-2">recent reports</h3>
                                        <div class="chart-info">
                                            <div class="chart-info__left">
                                                <div class="chart-note">
                                                    <span class="dot dot--blue"></span>
                                                    <span>products</span>
                                                </div>
                                                <div class="chart-note mr-0">
                                                    <span class="dot dot--green"></span>
                                                    <span>services</span>
                                                </div>
                                            </div>
                                            <div class="chart-info__right">
                                                <div class="chart-statis">
                                                    <span class="index incre">
                                                        <i class="zmdi zmdi-long-arrow-up"></i>25%</span>
                                                    <span class="label">products</span>
                                                </div>
                                                <div class="chart-statis mr-0">
                                                    <span class="index decre">
                                                        <i class="zmdi zmdi-long-arrow-down"></i>10%</span>
                                                    <span class="label">services</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="recent-report__chart">
                                            <canvas id="recent-rep-chart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="au-card chart-percent-card">
                                    <div class="au-card-inner">
                                        <h3 class="title-2 tm-b-5">char by %</h3>
                                        <div class="row no-gutters">
                                            <div class="col-xl-6">
                                                <div class="chart-note-wrap">
                                                    <div class="chart-note mr-0 d-block">
                                                        <span class="dot dot--blue"></span>
                                                        <span>products</span>
                                                    </div>
                                                    <div class="chart-note mr-0 d-block">
                                                        <span class="dot dot--red"></span>
                                                        <span>services</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-6">
                                                <div class="percent-chart">
                                                    <canvas id="percent-chart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-9">
                                <h2 class="title-1 m-b-25">Earnings By Items</h2>
                                <div class="table-responsive table--no-card m-b-40">
                                    <table class="table table-borderless table-striped table-earning">
                                        <thead>
                                            <tr>
                                                <th>date</th>
                                                <th>order ID</th>
                                                <th>name</th>
                                                <th class="text-right">price</th>
                                                <th class="text-right">quantity</th>
                                                <th class="text-right">total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>2018-09-29 05:57</td>
                                                <td>100398</td>
                                                <td>iPhone X 64Gb Grey</td>
                                                <td class="text-right">$999.00</td>
                                                <td class="text-right">1</td>
                                                <td class="text-right">$999.00</td>
                                            </tr>
                                            <tr>
                                                <td>2018-09-28 01:22</td>
                                                <td>100397</td>
                                                <td>Samsung S8 Black</td>
                                                <td class="text-right">$756.00</td>
                                                <td class="text-right">1</td>
                                                <td class="text-right">$756.00</td>
                                            </tr>
                                            <tr>
                                                <td>2018-09-27 02:12</td>
                                                <td>100396</td>
                                                <td>Game Console Controller</td>
                                                <td class="text-right">$22.00</td>
                                                <td class="text-right">2</td>
                                                <td class="text-right">$44.00</td>
                                            </tr>
                                            <tr>
                                                <td>2018-09-26 23:06</td>
                                                <td>100395</td>
                                                <td>iPhone X 256Gb Black</td>
                                                <td class="text-right">$1199.00</td>
                                                <td class="text-right">1</td>
                                                <td class="text-right">$1199.00</td>
                                            </tr>
                                            <tr>
                                                <td>2018-09-25 19:03</td>
                                                <td>100393</td>
                                                <td>USB 3.0 Cable</td>
                                                <td class="text-right">$10.00</td>
                                                <td class="text-right">3</td>
                                                <td class="text-right">$30.00</td>
                                            </tr>
                                            <tr>
                                                <td>2018-09-29 05:57</td>
                                                <td>100392</td>
                                                <td>Smartwatch 4.0 LTE Wifi</td>
                                                <td class="text-right">$199.00</td>
                                                <td class="text-right">6</td>
                                                <td class="text-right">$1494.00</td>
                                            </tr>
                                            <tr>
                                                <td>2018-09-24 19:10</td>
                                                <td>100391</td>
                                                <td>Camera C430W 4k</td>
                                                <td class="text-right">$699.00</td>
                                                <td class="text-right">1</td>
                                                <td class="text-right">$699.00</td>
                                            </tr>
                                            <tr>
                                                <td>2018-09-22 00:43</td>
                                                <td>100393</td>
                                                <td>USB 3.0 Cable</td>
                                                <td class="text-right">$10.00</td>
                                                <td class="text-right">3</td>
                                                <td class="text-right">$30.00</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <h2 class="title-1 m-b-25">Top Leaders</h2>
                                <div class="au-card au-card--bg-blue au-card-top-countries m-b-40">
                                    <div class="au-card-inner">
                                        <div class="table-responsive">
                                            <table class="table table-top-countries">
                                                <tbody>
                                                    <tr>
                                                        <td>United States</td>
                                                        <td class="text-right">$119,366.96</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Australia</td>
                                                        <td class="text-right">$70,261.65</td>
                                                    </tr>
                                                    <tr>
                                                        <td>United Kingdom</td>
                                                        <td class="text-right">$46,399.22</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Turkey</td>
                                                        <td class="text-right">$35,364.90</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Germany</td>
                                                        <td class="text-right">$20,366.96</td>
                                                    </tr>
                                                    <tr>
                                                        <td>France</td>
                                                        <td class="text-right">$10,366.96</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Australia</td>
                                                        <td class="text-right">$5,366.96</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Italy</td>
                                                        <td class="text-right">$1639.32</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="au-card au-card--no-shadow au-card--no-pad m-b-40">
                                    <div class="au-card-title" style="background-image:url('<?php echo get_asset_url('bg-title-01.jpg', 'images'); ?>');">
                                        <div class="bg-overlay bg-overlay--blue"></div>
                                        <h3>
                                            <i class="zmdi zmdi-account-calendar"></i>26 April, 2018</h3>
                                        <button class="au-btn-plus">
                                            <i class="zmdi zmdi-plus"></i>
                                        </button>
                                    </div>
                                    <div class="au-task js-list-load">
                                        <div class="au-task__title">
                                            <p>Tasks for John Doe</p>
                                        </div>
                                        <div class="au-task-list js-scrollbar3">
                                            <div class="au-task__item au-task__item--danger">
                                                <div class="au-task__item-inner">
                                                    <h5 class="task">
                                                        <a href="#">Meeting about</a>
                                                    </h5>
                                                    <span class="time">10:00 AM</span>
                                                </div>
                                            </div>
                                            <div class="au-task__item au-task__item--warning">
                                                <div class="au-task__item-inner">
                                                    <h5 class="task">
                                                        <a href="#">Create new task for Dashboard</a>
                                                    </h5>
                                                    <span class="time">11:00 AM</span>
                                                </div>
                                            </div>
                                            <div class="au-task__item au-task__item--primary">
                                                <div class="au-task__item-inner">
                                                    <h5 class="task">
                                                        <a href="#">Meeting about</a>
                                                    </h5>
                                                    <span class="time">02:00 PM</span>
                                                </div>
                                            </div>
                                            <div class="au-task__item au-task__item--success">
                                                <div class="au-task__item-inner">
                                                    <h5 class="task">
                                                        <a href="#">Create new task for Dashboard</a>
                                                    </h5>
                                                    <span class="time">03:30 PM</span>
                                                </div>
                                            </div>
                                            <div class="au-task__item au-task__item--danger js-load-item">
                                                <div class="au-task__item-inner">
                                                    <h5 class="task">
                                                        <a href="#">Meeting </a>
                                                    </h5>
                                                    <span class="time">10:00 AM</span>
                                                </div>
                                            </div>
                                            <div class="au-task__item au-task__item--warning js-load-item">
                                                <div class="au-task__item-inner">
                                                    <h5 class="task">
                                                        <a href="#">Create new task for Dashboard</a>
                                                    </h5>
                                                    <span class="time">11:00 AM</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="au-task__footer">
                                            <button class="au-btn au-btn-load js-load-btn">load more</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="au-card au-card--no-shadow au-card--no-pad m-b-40">
                                    <div class="au-card-title" style="background-image:url('<?php echo get_asset_url('bg-title-02.jpg', 'images'); ?>');">
                                        <div class="bg-overlay bg-overlay--blue"></div>
                                        <h3>
                                            <i class="zmdi zmdi-comment-text"></i>New Messages</h3>
                                        <button class="au-btn-plus">
                                            <i class="zmdi zmdi-plus"></i>
                                        </button>
                                    </div>
                                    <div class="au-inbox-wrap js-inbox-wrap">
                                        <div class="au-message js-list-load">
                                            <div class="au-message__noti">
                                                <p>You Have
                                                    <span>2</span>

                                                    new messages
                                                </p>
                                            </div>
                                            <div class="au-message-list">
                                                <div class="au-message__item unread">
                                                    <div class="au-message__item-inner">
                                                        <div class="au-message__item-text">
                                                            <div class="avatar-wrap">
                                                                <div class="avatar">
                                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-02.jpg', 'images'); ?>" alt="John Smith">
                                                                </div>
                                                            </div>
                                                            <div class="text">
                                                                <h5 class="name">John Smith</h5>
                                                                <p>Have sent a photo</p>
                                                            </div>
                                                        </div>
                                                        <div class="au-message__item-time">
                                                            <span>12 Min ago</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="au-message__item unread">
                                                    <div class="au-message__item-inner">
                                                        <div class="au-message__item-text">
                                                            <div class="avatar-wrap online">
                                                                <div class="avatar">
                                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-03.jpg', 'images'); ?>" alt="Nicholas Martinez">
                                                                </div>
                                                            </div>
                                                            <div class="text">
                                                                <h5 class="name">Nicholas Martinez</h5>
                                                                <p>You are now connected on message</p>
                                                            </div>
                                                        </div>
                                                        <div class="au-message__item-time">
                                                            <span>11:00 PM</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="au-message__item">
                                                    <div class="au-message__item-inner">
                                                        <div class="au-message__item-text">
                                                            <div class="avatar-wrap online">
                                                                <div class="avatar">
                                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-04.jpg', 'images'); ?>" alt="Michelle Sims">
                                                                </div>
                                                            </div>
                                                            <div class="text">
                                                                <h5 class="name">Michelle Sims</h5>
                                                                <p>Lorem ipsum dolor sit amet</p>
                                                            </div>
                                                        </div>
                                                        <div class="au-message__item-time">
                                                            <span>Yesterday</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="au-message__item">
                                                    <div class="au-message__item-inner">
                                                        <div class="au-message__item-text">
                                                            <div class="avatar-wrap">
                                                                <div class="avatar">
                                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-05.jpg', 'images'); ?>" alt="Michelle Sims">
                                                                </div>
                                                            </div>
                                                            <div class="text">
                                                                <h5 class="name">Michelle Sims</h5>
                                                                <p>Purus feugiat finibus</p>
                                                            </div>
                                                        </div>
                                                        <div class="au-message__item-time">
                                                            <span>Sunday</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="au-message__item js-load-item">
                                                    <div class="au-message__item-inner">
                                                        <div class="au-message__item-text">
                                                            <div class="avatar-wrap online">
                                                                <div class="avatar">
                                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-04.jpg', 'images'); ?>" alt="Michelle Sims">
                                                                </div>
                                                            </div>
                                                            <div class="text">
                                                                <h5 class="name">Michelle Sims</h5>
                                                                <p>Lorem ipsum dolor sit amet</p>
                                                            </div>
                                                        </div>
                                                        <div class="au-message__item-time">
                                                            <span>Yesterday</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="au-message__item js-load-item">
                                                    <div class="au-message__item-inner">
                                                        <div class="au-message__item-text">
                                                            <div class="avatar-wrap">
                                                                <div class="avatar">
                                                                    <img src="assets/<?php echo get_asset_url('icon/avatar-05.jpg', 'images'); ?>" alt="Michelle Sims">
                                                                </div>
                                                            </div>
                                                            <div class="text">
                                                                <h5 class="name">Michelle Sims</h5>
                                                                <p>Purus feugiat finibus</p>
                                                            </div>
                                                        </div>
                                                        <div class="au-message__item-time">
                                                            <span>Sunday</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="au-message__footer">
                                                <button class="au-btn au-btn-load js-load-btn">load more</button>
                                            </div>
                                        </div>
                                        <div class="au-chat">
                                            <div class="au-chat__title">
                                                <div class="au-chat-info">
                                                    <div class="avatar-wrap online">
                                                        <div class="avatar avatar--small">
                                                            <img src="assets/<?php echo get_asset_url('icon/avatar-02.jpg', 'images'); ?>" alt="John Smith">
                                                        </div>
                                                    </div>
                                                    <span class="nick">
                                                        <a href="#">John Smith</a>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="au-chat__content">
                                                <div class="recei-mess-wrap">
                                                    <span class="mess-time">12 Min ago</span>
                                                    <div class="recei-mess__inner">
                                                        <div class="avatar avatar--tiny">
                                                            <img src="assets/<?php echo get_asset_url('icon/avatar-02.jpg', 'images'); ?>" alt="John Smith">
                                                        </div>
                                                        <div class="recei-mess-list">
                                                            <div class="recei-mess">Lorem ipsum dolor sit amet, consectetur adipiscing elit non iaculis</div>
                                                            <div class="recei-mess">Donec tempor, sapien ac viverra</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="send-mess-wrap">
                                                    <span class="mess-time">30 Sec ago</span>
                                                    <div class="send-mess__inner">
                                                        <div class="send-mess-list">
                                                            <div class="send-mess">Lorem ipsum dolor sit amet, consectetur adipiscing elit non iaculis</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="au-chat-textfield">
                                                <form class="au-form-icon">
                                                    <input class="au-input au-input--full au-input--h65" type="text" placeholder="Type a message">
                                                    <button class="au-input-icon">
                                                        <i class="zmdi zmdi-camera"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="copyright">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            
            <!-- Profile Section -->
            <div id="profile-section" class="section-content" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Profile Details</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" id="name" class="form-control" value="<?php echo $asso_name; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" class="form-control" value="<?php echo $asso_email; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" class="form-control" value="<?php echo $asso_phone; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="sponsor">Sponsor</label>
                                    <input type="text" id="sponsor" class="form-control" value="<?php echo $sponsored_by; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pan">PAN</label>
                                    <input type="text" id="pan" class="form-control" value="<?php echo $pan; ?>" onchange="validate_pan()" readonly>
                                    <span id="lblError_pan" style="color: red"></span>
                                </div>
                                <div class="form-group">
                                    <label for="adhaar">Adhaar</label>
                                    <input type="text" id="adhaar" class="form-control" value="<?php echo $adhaar; ?>" onchange="validate_adhaar()" readonly>
                                    <span id="lblError_adhaar" style="color: red"></span>
                                </div>
                                <div class="form-group">
                                    <label for="dob">Date of Birth</label>
                                    <input type="text" id="dob" class="form-control" value="<?php echo $date_of_birth; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="join_date">Join Date</label>
                                    <input type="text" id="join_date" class="form-control" value="<?php echo $join_date; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <a href="Edit_profile.php" class="btn btn-primary">Edit Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Team Section -->
            <div id="team-section" class="section-content" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">My Team</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h4 class="card-title">Direct Members</h4>
                                        <p class="card-text display-4">1</p>
                                        <a href="my_direct.php" class="btn btn-light">View Details</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h4 class="card-title">Team Members</h4>
                                        <p class="card-text display-4">10368</p>
                                        <a href="my_downline.php" class="btn btn-light">View Details</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h4 class="card-title">Active Members</h4>
                                        <p class="card-text display-4">38</p>
                                        <a href="tree_view.php" class="btn btn-light">View Tree</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Business Section -->
            <div id="business-section" class="section-content" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Business Details</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h4 class="card-title">Total Earnings</h4>
                                        <p class="card-text display-4">₹1,060,386</p>
                                        <a href="payout_details.php" class="btn btn-light">View Details</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h4 class="card-title">Outstanding Amount</h4>
                                        <p class="card-text display-4">₹38,000</p>
                                        <a href="my_ladger.php" class="btn btn-light">View Ledger</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <h4>Recent Transactions</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Transaction ID</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>2023-05-15</td>
                                                <td>TRX123456</td>
                                                <td>Commission Payment</td>
                                                <td>₹15,000</td>
                                                <td><span class="badge badge-success">Completed</span></td>
                                            </tr>
                                            <tr>
                                                <td>2023-05-10</td>
                                                <td>TRX123455</td>
                                                <td>Referral Bonus</td>
                                                <td>₹5,000</td>
                                                <td><span class="badge badge-success">Completed</span></td>
                                            </tr>
                                            <tr>
                                                <td>2023-05-05</td>
                                                <td>TRX123454</td>
                                                <td>Team Performance Bonus</td>
                                                <td>₹25,000</td>
                                                <td><span class="badge badge-warning">Pending</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Organization Chart Section -->
            <div id="org-section" class="section-content" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Organization Chart</strong>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <p>Organization chart will be loaded here.</p>
                            <a href="tree_view.php" class="btn btn-primary">View Full Tree</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- END PAGE CONTAINER-->
        </div>

    </div>

    <!-- Jquery JS-->
    <script src="assets/vendor/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="assets/vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="assets/vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Vendor JS       -->
    <script src="assets/vendor/slick/slick.min.js">
    </script>
    <script src="assets/vendor/wow/wow.min.js"></script>
    <script src="assets/vendor/animsition/animsition.min.js"></script>
    <script src="assets/vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
    </script>
    <script src="assets/vendor/counter-up/jquery.waypoints.min.js"></script>
    <script src="assets/vendor/counter-up/jquery.counterup.min.js">
    </script>
    <script src="assets/vendor/circle-progress/circle-progress.min.js"></script>
    <script src="assets/vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/chartjs/Chart.bundle.min.js"></script>
    <script src="assets/vendor/select2/select2.min.js">
    </script>

    <!-- Main JS-->
    <script src="<?php echo get_asset_url('js/main.js', 'js'); ?>"></script>
    
    <!-- Add JavaScript for Tab Navigation -->
    <script>
        // Function to show/hide sections
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(function(section) {
                section.style.display = 'none';
            });
            
            // Show the selected section
            document.getElementById(sectionId).style.display = 'block';
        }
        
        // Add validation functions for PAN and Adhaar
        function validate_pan() {
            var panVal = document.getElementById('pan').value;
            var regpan = /^([A-Z]){5}([0-9]){4}([A-Z]){1}?$/;
            
            if(regpan.test(panVal)){
                document.getElementById('lblError_pan').innerHTML = "";
            } else {
                document.getElementById('lblError_pan').innerHTML = "Invalid PAN Number";
                document.getElementById('pan').value = "";
            }
        }
        
        function validate_adhaar() {
            var adhaarVal = document.getElementById('adhaar').value;
            var regadhaar = /^([0-9]{4}[0-9]{4}[0-9]{4}$)|([0-9]{4}\s[0-9]{4}\s[0-9]{4}$)|([0-9]{4}-[0-9]{4}-[0-9]{4}$)/;
            
            if(regadhaar.test(adhaarVal)){
                document.getElementById('lblError_adhaar').innerHTML = "";
            } else {
                document.getElementById('lblError_adhaar').innerHTML = "Invalid Adhaar Number";
                document.getElementById('adhaar').value = "";
            }
        }
        
        // Add event listeners to section buttons
        document.addEventListener('DOMContentLoaded', function() {
            // Default show dashboard
            document.getElementById('dashboard-section').style.display = 'block';
            
            // Add click handlers for the section buttons
            document.getElementById('show-dashboard').addEventListener('click', function() {
                showSection('dashboard-section');
            });
            
            document.getElementById('show-profile').addEventListener('click', function() {
                showSection('profile-section');
            });
            
            document.getElementById('show-team').addEventListener('click', function() {
                showSection('team-section');
            });
            
            document.getElementById('show-business').addEventListener('click', function() {
                showSection('business-section');
            });
        });
    </script>

</body>

</html>
<!-- end document-->
