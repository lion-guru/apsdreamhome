<?php
session_start();

// Ensure only managers can access this page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'manager') {
    $_SESSION['login_error'] = 'Unauthorized access';
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../includes/db_connection.php';
$con = getDbConnection();

// Fetch manager-specific analytics
$totalProperties = $con->query("SELECT COUNT(*) as count FROM properties")->fetch_assoc()['count'];
$totalBookings = $con->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Dashboard</title>
    <?php include __DIR__ . '/../includes/templates/header.php'; ?>
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manager Dashboard</h1>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Total Properties</h5>
                                <p class="card-text"><?php echo $totalProperties; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Total Bookings</h5>
                                <p class="card-text"><?php echo $totalBookings; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/templates/footer.php'; ?>
</body>
</html>
