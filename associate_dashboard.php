<?php
// Secure session initialization and associate authentication
require_once(__DIR__ . "/includes/session.php");
require_once(__DIR__ . "/includes/config/config.php");
// require_once(__DIR__ . "/includes/functions/asset_helper.php"); // Deprecated, use get_asset_url() from common-functions.php or updated-config-paths.php instead

// Check if associate is logged in using session 'uid' and 'utype'
if (!isAuthenticated() || getUserType() !== 'associate') {
    header("Location: login.php");
    exit();
}

// Get associate UID from session
$aid = $_SESSION['uid'];

// Get associate details
$sql = "SELECT a.*, u.name, u.email, u.phone, al.level_name, al.commission_rate 
        FROM associates a 
        JOIN users u ON a.user_id = u.id 
        LEFT JOIN associate_levels al ON a.level_id = al.id
        WHERE a.uid = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $aid);
$stmt->execute();
$result = $stmt->get_result();
$associate = $result->fetch_assoc();

// Get team members (downline)
$sql_team = "SELECT a.*, u.name, u.email, u.phone, al.level_name, 
            (SELECT COUNT(*) FROM team_hierarchy th WHERE th.upline_id = a.uid) as downline_count
            FROM associates a 
            JOIN users u ON a.user_id = u.id 
            LEFT JOIN associate_levels al ON a.level_id = al.id
            WHERE EXISTS (SELECT 1 FROM team_hierarchy th WHERE th.upline_id = ? AND th.downline_id = a.uid)";
$stmt_team = $con->prepare($sql_team);
$stmt_team->bind_param("s", $aid);
$stmt_team->execute();
$team_members = $stmt_team->get_result();

// Get total business
$sql_business = "SELECT 
                SUM(ct.amount) as total_business,
                SUM(ct.commission_amount) as total_commission,
                COUNT(DISTINCT ct.id) as total_transactions
                FROM commission_transactions ct
                WHERE ct.associate_id = ?";
$stmt_business = $con->prepare($sql_business);
$stmt_business->bind_param("s", $aid);
$stmt_business->execute();
$business_result = $stmt_business->get_result();
$business_data = $business_result->fetch_assoc();
$total_business = $business_data['total_business'] ?? 0;
$total_commission = $business_data['total_commission'] ?? 0;
$total_transactions = $business_data['total_transactions'] ?? 0;

// Get monthly business data
$sql_monthly = "SELECT 
                DATE_FORMAT(ct.transaction_date, '%b') as month,
                SUM(ct.amount) as monthly_amount
                FROM commission_transactions ct
                WHERE ct.associate_id = ? 
                AND ct.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY MONTH(ct.transaction_date)
                ORDER BY ct.transaction_date ASC";
$stmt_monthly = $con->prepare($sql_monthly);
$stmt_monthly->bind_param("s", $aid);
$stmt_monthly->execute();
$monthly_result = $stmt_monthly->get_result();
$monthly_data = array();
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[$row['month']] = $row['monthly_amount'];
}

// Get enhanced team performance data
$sql_team_performance = "SELECT 
                        'Direct Sales' as category,
                        COUNT(DISTINCT ct.id) as transaction_count,
                        SUM(ct.amount) as total_amount,
                        SUM(ct.commission_amount) as commission_earned,
                        AVG(ct.amount) as avg_transaction
                        FROM commission_transactions ct
                        WHERE ct.associate_id = ? AND ct.transaction_type = 'direct'
                        UNION ALL
                        SELECT 
                        'Team Sales',
                        COUNT(DISTINCT ct.id),
                        SUM(ct.amount),
                        SUM(ct.commission_amount),
                        AVG(ct.amount)
                        FROM commission_transactions ct
                        WHERE ct.associate_id = ? AND ct.transaction_type = 'team'
                        UNION ALL
                        SELECT 
                        'Referral Sales',
                        COUNT(DISTINCT ct.id),
                        SUM(ct.amount),
                        SUM(ct.commission_amount),
                        AVG(ct.amount)
                        FROM commission_transactions ct
                        WHERE ct.associate_id = ? AND ct.transaction_type = 'referral'";
$stmt_team_performance = $con->prepare($sql_team_performance);
$stmt_team_performance->bind_param("sss", $aid, $aid, $aid);
$stmt_team_performance->execute();
$team_performance_result = $stmt_team_performance->get_result();
$team_performance_data = array();
while ($row = $team_performance_result->fetch_assoc()) {
    $team_performance_data[$row['category']] = $row['total_amount'] ?? 0;
}

// Get customer details with EMI tracking
$sql_customers = "SELECT 
                b.*, 
                bp.amount as total_amount,
                COALESCE(SUM(bp.paid_amount), 0) as paid_amount,
                (bp.amount - COALESCE(SUM(bp.paid_amount), 0)) as remaining_amount,
                p.name as project_name,
                COUNT(DISTINCT e.id) as total_emis,
                SUM(CASE WHEN e.status = 'paid' THEN 1 ELSE 0 END) as paid_emis,
                MIN(CASE WHEN e.status = 'pending' THEN e.due_date END) as next_emi_date
                FROM bookings b
                JOIN booking_payments bp ON b.id = bp.booking_id
                JOIN projects p ON b.project_id = p.id
                LEFT JOIN emi_schedules e ON b.id = e.booking_id
                WHERE b.associate_id = ?
                GROUP BY b.id
                ORDER BY next_emi_date ASC";
$stmt_customers = $con->prepare($sql_customers);
$stmt_customers->bind_param("s", $aid);
$stmt_customers->execute();
$customers = $stmt_customers->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Associate Dashboard - APS Dream Homes</title>
    
    <!-- CSS Links -->
    <link href="<?php echo get_asset_url('admin/assets/css/font-awesome.min.css', 'css'); ?>" rel="stylesheet">
    <link href="<?php echo get_asset_url('admin/assets/css/bootstrap.min.css', 'css'); ?>" rel="stylesheet">
    <link href="<?php echo get_asset_url('admin/assets/css/feathericon.min.css', 'css'); ?>" rel="stylesheet">
    <link href="<?php echo get_asset_url('admin/assets/css/select2.min.css', 'css'); ?>" rel="stylesheet">
    <link href="<?php echo get_asset_url('admin/assets/css/style.css', 'css'); ?>" rel="stylesheet">
    <link href="<?php echo get_asset_url('admin/assets/plugins/datatables/dataTables.bootstrap4.min.css', 'css'); ?>" rel="stylesheet">
    <link href="<?php echo get_asset_url('admin/assets/plugins/datatables/responsive.bootstrap4.min.css', 'css'); ?>" rel="stylesheet">
    <link href="<?php echo get_asset_url('admin/assets/plugins/morris/morris.css', 'css'); ?>" rel="stylesheet">
</head>

<body class="animsition">
    <div class="page-wrapper">
        <!-- Header Mobile-->
        <header class="header-mobile d-block d-lg-none">
            <div class="header-mobile__bar">
                <div class="container-fluid">
                    <div class="header-mobile-inner">
                        <a class="logo" href="index.php">
                            <img src="<?php echo get_asset_url('logo/restatelg.png', 'images'); ?>" alt="APS Dream Homes" />
                        </a>
                        <button class="hamburger hamburger--slider" type="button">
                            <span class="hamburger-box">
                                <span class="hamburger-inner"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            <nav class="navbar-mobile">
                <div class="container-fluid">
                    <ul class="navbar-mobile__list list-unstyled">
                        <li><a href="assosiate_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                        
                        <!-- Profile Management -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-user"></i>Profile</a>
                            <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                <li><a href="edit-profile.php">Edit Profile</a></li>
                                <li><a href="update_password.php">Change Password</a></li>
                                <li><a href="bank.php">Bank Details</a></li>
                                <li><a href="documents.php">Upload Documents</a></li>
                            </ul>
                        </li>

                        <!-- Business Details -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-chart-line"></i>Business Details</a>
                            <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                <li><a href="self_business_report.php">Self Business Report</a></li>
                                <li><a href="team_business_report.php">Team Business Report</a></li>
                                <li><a href="agent_payment_collection.php">Payment Collection</a></li>
                                <li><a href="payment_collection_report.php">Collection Report</a></li>
                            </ul>
                        </li>

                        <!-- Team Management -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-users"></i>Team</a>
                            <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                <li><a href="my_downline.php">My Downline</a></li>
                                <li><a href="my_direct.php">My Direct</a></li>
                                <li><a href="tree_view.php">Tree View</a></li>
                            </ul>
                        </li>

                        <!-- My Account -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-wallet"></i>My Account</a>
                            <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                <li><a href="payout_details.php">Payout Details</a></li>
                                <li><a href="cus_ladger.php">Customer Ledger</a></li>
                                <li><a href="my_ladger.php">My Ledger</a></li>
                            </ul>
                        </li>

                        <!-- Support -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-headset"></i>Support</a>
                            <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                <li><a href="send_enquiry.php">Send Enquiry</a></li>
                                <li><a href="enquiry_details.php">Enquiry Details</a></li>
                            </ul>
                        </li>

                        <li><a href="logout.php"><i class="fas fa-power-off"></i>Logout</a></li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- MENU SIDEBAR-->
        <aside class="menu-sidebar d-none d-lg-block">
            <div class="logo">
                <a href="index.php">
                    <img src="<?php echo get_asset_url('logo/restatelg.png', 'images'); ?>" alt="APS Dream Homes" />
                </a>
            </div>
            <div class="menu-sidebar__content js-scrollbar1">
                <nav class="navbar-sidebar">
                    <ul class="list-unstyled navbar__list">
                        <li><a href="assosiate_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                        
                        <!-- Profile Management -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-user"></i>Profile</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li><a href="edit-profile.php">Edit Profile</a></li>
                                <li><a href="update_password.php">Change Password</a></li>
                                <li><a href="bank.php">Bank Details</a></li>
                                <li><a href="documents.php">Upload Documents</a></li>
                            </ul>
                        </li>

                        <!-- Business Details -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-chart-line"></i>Business Details</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li><a href="self_business_report.php">Self Business Report</a></li>
                                <li><a href="team_business_report.php">Team Business Report</a></li>
                                <li><a href="agent_payment_collection.php">Payment Collection</a></li>
                                <li><a href="payment_collection_report.php">Collection Report</a></li>
                            </ul>
                        </li>

                        <!-- Team Management -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-users"></i>Team</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li><a href="my_downline.php">My Downline</a></li>
                                <li><a href="my_direct.php">My Direct</a></li>
                                <li><a href="tree_view.php">Tree View</a></li>
                            </ul>
                        </li>

                        <!-- My Account -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-wallet"></i>My Account</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li><a href="payout_details.php">Payout Details</a></li>
                                <li><a href="cus_ladger.php">Customer Ledger</a></li>
                                <li><a href="my_ladger.php">My Ledger</a></li>
                            </ul>
                        </li>

                        <!-- Support -->
                        <li class="has-sub">
                            <a class="js-arrow" href="#"><i class="fas fa-headset"></i>Support</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li><a href="send_enquiry.php">Send Enquiry</a></li>
                                <li><a href="enquiry_details.php">Enquiry Details</a></li>
                            </ul>
                        </li>

                        <li><a href="logout.php"><i class="fas fa-power-off"></i>Logout</a></li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <!-- HEADER DESKTOP-->
            <header class="header-desktop">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="header-wrap">
                            <div class="header-button ml-auto">
                                <div class="account-wrap">
                                    <div class="account-item clearfix js-item-menu">
                                        <div class="content">
                                            <a class="js-acc-btn" href="#"><?php echo $associate['name']; ?></a>
                                        </div>
                                        <div class="account-dropdown js-dropdown">
                                            <div class="info clearfix">
                                                <div class="content">
                                                    <h5 class="name"><?php echo $associate['name']; ?></h5>
                                                    <span class="email"><?php echo $associate['email']; ?></span>
                                                </div>
                                            </div>
                                            <div class="account-dropdown__body">
                                                <div class="account-dropdown__item">
                                                    <a href="edit-profile.php"><i class="zmdi zmdi-account"></i>Profile</a>
                                                </div>
                                                <div class="account-dropdown__item">
                                                    <a href="update_password.php"><i class="zmdi zmdi-settings"></i>Change Password</a>
                                                </div>
                                            </div>
                                            <div class="account-dropdown__footer">
                                                <a href="logout.php"><i class="zmdi zmdi-power"></i>Logout</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <!-- Overview Cards -->
                        <div class="row m-t-25">
                            <div class="col-sm-6 col-lg-3">
                                <div class="overview-item overview-item--c1">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-account-o"></i>
                                            </div>
                                            <div class="text">
                                                <h2><?php echo $team_members->num_rows; ?></h2>
                                                <span>Team Members</span>
                                                <p class="small text-muted mt-2">Level: <?php echo $associate['level_name'] ?? 'N/A'; ?></p>
                                                <p class="small text-muted">Commission Rate: <?php echo $associate['commission_rate']; ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="overview-item overview-item--c2">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-shopping-cart"></i>
                                            </div>
                                            <div class="text">
                                                <h2>₹<?php echo number_format($total_business); ?></h2>
                                                <span>Total Business</span>
                                                <p class="small text-muted mt-2">Commission: ₹<?php echo number_format($total_commission); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="overview-item overview-item--c3">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-calendar-note"></i>
                                            </div>
                                            <div class="text">
                                                <h2><?php echo $customers->num_rows; ?></h2>
                                                <span>Total Customers</span>
                                                <p class="small text-muted mt-2">Transactions: <?php echo $total_transactions; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="overview-item overview-item--c4">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-money"></i>
                                            </div>
                                            <div class="text">
                                                <h2>₹<?php echo number_format($total_commission); ?></h2>
                                                <span>Total Earnings</span>
                                                <p class="small text-muted mt-2"><?php echo $total_transactions; ?> Transactions</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Business Analytics -->
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="au-card m-b-30">
                                    <div class="au-card-inner">
                                        <h3 class="title-2 m-b-40">Monthly Business</h3>
                                        <canvas id="monthlyBusinessChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="au-card m-b-30">
                                    <div class="au-card-inner">
                                        <h3 class="title-2 m-b-40">Team Performance</h3>
                                        <canvas id="teamPerformanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Team Members -->
                        <div class="row">
                            <div class="col-lg-12">
                                <h2 class="title-1 m-b-25">Recent Team Members</h2>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" id="teamSearch" class="form-control" placeholder="Search members...">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive table--no-card m-b-40">
                                    <table class="table table-borderless table-striped table-earning" id="teamMembersTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Level</th>
                                                <th>Downline</th>
                                                <th>Join Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($member = $team_members->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $member['name']; ?></td>
                                                <td><?php echo $member['email']; ?></td>
                                                <td><?php echo $member['phone']; ?></td>
                                                <td><?php echo $member['level_name'] ?? 'N/A'; ?></td>
                                                <td><?php echo $member['downline_count']; ?></td>
                                                <td><?php echo date('d M Y', strtotime($member['join_date'])); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $member['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($member['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Business Analytics -->
                        <div class="row">
                            <div class="col-lg-6">
                                <h2 class="title-1 m-b-25">Monthly Business Performance</h2>
                                <div class="card">
                                    <div class="card-body">
                                        <canvas id="monthlyBusinessChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <h2 class="title-1 m-b-25">Team Performance</h2>
                                <div class="card">
                                    <div class="card-body">
                                        <canvas id="teamPerformanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Business Analytics Section -->
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="au-card m-b-30">
                                    <div class="au-card-inner">
                                        <h3 class="title-2 m-b-40">Monthly Business Trend</h3>
                                        <canvas id="monthlyBusinessChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="au-card m-b-30">
                                    <div class="au-card-inner">
                                        <h3 class="title-2 m-b-40">Team Performance</h3>
                                        <canvas id="teamPerformanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Team Members Section -->
                        <div class="row">
                            <div class="col-lg-12">
                                <h2 class="title-1 m-b-25">Team Members</h2>
                                <div class="table-responsive table--no-card m-b-40">
                                    <table class="table table-borderless table-striped table-earning" id="teamMembersTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Level</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Downline Count</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($member = $team_members->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $member['name']; ?></td>
                                                <td><?php echo $member['level_name'] ?? 'N/A'; ?></td>
                                                <td><?php echo $member['email']; ?></td>
                                                <td><?php echo $member['phone']; ?></td>
                                                <td><?php echo $member['downline_count']; ?></td>
                                                <td>
                                                    <?php 
                                                    $performance = rand(60, 100); // This should be replaced with actual performance calculation
                                                    $status_class = $performance >= 80 ? 'success' : ($performance >= 60 ? 'warning' : 'danger');
                                                    ?>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-<?php echo $status_class; ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $performance; ?>%" 
                                                             aria-valuenow="<?php echo $performance; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?php echo $performance; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Customers -->
                        <div class="row">
                            <div class="col-lg-12">
                                <h2 class="title-1 m-b-25">Recent Customers</h2>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" id="customerSearch" class="form-control" placeholder="Search customers...">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive table--no-card m-b-40">
                                    <table class="table table-borderless table-striped table-earning" id="customersTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Project</th>
                                                <th>Total Amount</th>
                                                <th>Paid Amount</th>
                                                <th>Remaining Amount</th>
                                                <th>EMI Status</th>
                                                <th>Next EMI</th>
                                                <th>Payment Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($customer = $customers->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $customer['customer_name']; ?></td>
                                                <td><?php echo $customer['project_name']; ?></td>
                                                <td>₹<?php echo number_format($customer['total_amount']); ?></td>
                                                <td>₹<?php echo number_format($customer['paid_amount']); ?></td>
                                                <td>₹<?php echo number_format($customer['remaining_amount']); ?></td>
                                                <td>
                                                    <?php 
                                                    $emi_percentage = ($customer['paid_emis'] / $customer['total_emis']) * 100;
                                                    echo "{$customer['paid_emis']}/{$customer['total_emis']} EMIs";
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($customer['next_emi_date']) {
                                                        $next_emi = new DateTime($customer['next_emi_date']);
                                                        $today = new DateTime();
                                                        $interval = $today->diff($next_emi);
                                                        $days_remaining = $interval->days;
                                                        $status_class = $days_remaining <= 7 ? 'danger' : ($days_remaining <= 15 ? 'warning' : 'success');
                                                        echo "<span class='badge badge-{$status_class}'>{$next_emi->format('d M Y')}</span>";
                                                    } else {
                                                        echo "<span class='badge badge-success'>Complete</span>";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $percentage = ($customer['paid_amount'] / $customer['total_amount']) * 100;
                                                    $status_class = $percentage == 100 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                                                    ?>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-<?php echo $status_class; ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $percentage; ?>%" 
                                                             aria-valuenow="<?php echo $percentage; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?php echo round($percentage); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jquery JS-->
    <script src="<?php echo get_asset_url('vendor/jquery/jquery.min.js', 'js'); ?>"></script>
    <!-- Bootstrap JS-->
    <script src="<?php echo get_asset_url('vendor/bootstrap/js/popper.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/bootstrap/js/bootstrap.min.js', 'js'); ?>"></script>
    <!-- Vendor JS       -->
    <script src="<?php echo get_asset_url('vendor/slick/slick.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/wow/wow.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/animsition/animsition.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/bootstrap-progressbar/bootstrap-progressbar.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/counter-up/jquery.waypoints.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/counter-up/jquery.counterup.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/circle-progress/circle-progress.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/perfect-scrollbar/perfect-scrollbar.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/chart.js/Chart.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('vendor/select2/js/select2.min.js', 'js'); ?>"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">

    <!-- Main JS-->
    <script src="<?php echo get_asset_url('js/main.js', 'js'); ?>"></script>
    <!-- Custom Scripts -->
    <script>
    $(document).ready(function() {
        // Initialize DataTables for Team Members
        $('#teamMembersTable').DataTable({
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            order: [[5, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search members..."
            }
        });

        // Initialize DataTables for Customers
        $('#customersTable').DataTable({
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            order: [[6, 'asc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search customers..."
            }
        });

        // Team Performance Chart
        var teamData = <?php echo json_encode(array_values($team_performance_data)); ?>;
        var teamLabels = <?php echo json_encode(array_keys($team_performance_data)); ?>;
        var teamPerformanceCtx = document.getElementById('teamPerformanceChart');
        var teamPerformanceChart = new Chart(teamPerformanceCtx, {
            type: 'doughnut',
            data: {
                labels: teamLabels,
                datasets: [{
                    data: teamData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Sales Distribution'
                    }
                }
            }
        });


        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize select2
        $('.select2').select2();
        
        // Initialize perfect scrollbar
        $('.js-scrollbar1').each(function(){
            new PerfectScrollbar(this);
        });

        // Monthly Business Chart
        var monthlyData = <?php echo json_encode(array_values($monthly_data)); ?>;
        var monthlyLabels = <?php echo json_encode(array_keys($monthly_data)); ?>;
        var monthlyBusinessCtx = document.getElementById('monthlyBusinessChart');
        var monthlyBusinessChart = new Chart(monthlyBusinessCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Business Amount',
                    data: monthlyData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.parsed.y.toLocaleString('en-IN');
                            }
                        }
                    }
                }
            }
        });

        // Team Performance Chart
        var teamPerformanceData = {
            transactionCounts: <?php echo json_encode(array_column($team_performance_data, 'transaction_count')); ?>,
            totalAmounts: <?php echo json_encode(array_column($team_performance_data, 'total_amount')); ?>,
            commissionEarned: <?php echo json_encode(array_column($team_performance_data, 'commission_earned')); ?>,
            avgTransaction: <?php echo json_encode(array_column($team_performance_data, 'avg_transaction')); ?>
        };
        var teamPerformanceLabels = <?php echo json_encode(array_keys($team_performance_data)); ?>;
        var teamPerformanceCtx = document.getElementById('teamPerformanceChart');
        var teamPerformanceChart = new Chart(teamPerformanceCtx, {
            type: 'bar',
            data: {
                labels: teamPerformanceLabels,
                datasets: [{
                    label: 'Total Amount',
                    data: teamPerformanceData.totalAmounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Commission Earned',
                    data: teamPerformanceData.commissionEarned,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Average Transaction',
                    data: teamPerformanceData.avgTransaction,
                    backgroundColor: 'rgba(255, 206, 86, 0.8)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Amount (₹)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString('en-IN');
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Average Transaction (₹)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString('en-IN');
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                var value = context.raw || 0;
                                return label + ': ₹' + value.toLocaleString('en-IN');
                            }
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
    </script>
</body>
</html>