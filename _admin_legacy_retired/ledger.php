<?php
session_start();
require("config.php"); // Ensure you include your database configuration

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

function fetchCustomerLedger($con) {
    $stmt = $con->prepare("SELECT * FROM customer_ledger");
    $stmt->execute();
    return $stmt->get_result();
}

// Fetch ledger entries from the database
$ledgerEntries = fetchCustomerLedger($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Ledger</title>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>"> <!-- Include Bootstrap for styling -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>"> <!-- Custom styles -->
    <script src="<?php echo get_asset_url('js/jquery.min.js', 'js'); ?>"></script> <!-- Include jQuery -->
    <script src="<?php echo get_asset_url('js/bootstrap.bundle.min.js', 'js'); ?>"></script> <!-- Include Bootstrap JS -->
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <a href="dashboard.php" class="logo">
                <img src="assets/<?php echo get_asset_url('rsadmin1.png', 'images'); ?>" alt="Logo">
            </a>
            <a href="dashboard.php" class="logo logo-small">
                <img src="assets/<?php echo get_asset_url('logo-small.png', 'images'); ?>" alt="Logo" width="30" height="30">
            </a>
        </div>
        <a href="javascript:void(0);" id="toggle_btn">
            <i class="fe fe-text-align-left"></i>
        </a>
        <a class="mobile_btn" id="mobile_btn">
            <i class="fa fa-bars"></i>
        </a>
        <ul class="nav user-menu">
            <li class="nav-item dropdown app-dropdown">
                <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                    <span class="user-img">
                        <img class="rounded-circle" src="assets/<?php echo get_asset_url('profiles/avatar-01.png', 'images'); ?>" width="31" alt="User Image">
                    </span>
                </a>
                <div class="dropdown-menu">
                    <div class="user-header">
                        <div class="avatar avatar-sm">
                            <img src="assets/<?php echo get_asset_url('profiles/avatar-01.png', 'images'); ?>" alt="User Image" class="avatar-img rounded-circle">
                        </div>
                        <div class="user-text">
                            <h6><?php echo htmlspecialchars($_SESSION['admin_logged_in']); ?></h6>
                            <p class="text-muted mb-0">Administrator</p>
                        </div>
                    </div>
                    <a class="dropdown-item" href="profile.php">Profile</a>
                    <a class="dropdown-item" href="logout.php">Logout</a>
                </div>
            </li>
        </ul>
    </div>
    <!-- /Header -->

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-inner slimscroll">
            <div id="sidebar-menu" class="sidebar-menu">
                <ul>
                    <li class="menu-title"><span>Main</span></li>
                    <li><a href="dashboard.php"><i class="fe fe-home"></i> <span>Dashboard</span></a></li>
                    <li class="menu-title"><span>All Users</span></li>
                    <li class="submenu">
                        <a href="#"><i class="fe fe-user"></i> <span>All Users</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="adminlist.php">Admin</a></li>
                            <li><a href="userlist.php">Users</a></li>
                            <li><a href="useragent.php">Agent</a></li>
                            <li><a href="userbuilder.php">Builder</a></li>
                        </ul>
                    </li>
                    <!-- Add other menu items here -->
                </ul>
            </div>
        </div>
    </div>
    <!-- /Sidebar -->

    <div class="container">
        <h1 class="my-4">Customer Ledger</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Transaction Amount</th>
                    <th>Outstanding</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($entry = $ledgerEntries->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($entry['transaction_amount']); ?></td>
                        <td><?php echo htmlspecialchars($entry['outstanding']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
