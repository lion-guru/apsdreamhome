<?php
session_start();
require("config.php"); // Include your database configuration

function fetchPaymentReminders($con) {
    $stmt = $con->prepare("SELECT * FROM payment_reminders");
    $stmt->execute();
    return $stmt->get_result();
}

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminders</title>
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
                            <h6><?php echo htmlspecialchars($_SESSION['auser']); ?></h6>
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

    <div class="container mt-4">
        <h1>Payment Reminders</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $reminders = fetchPaymentReminders($con);
                while ($reminder = $reminders->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reminder['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($reminder['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($reminder['amount']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
